<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Grade;
use App\Models\Section;
use App\Models\RiskResult;
use App\Models\ReportSubmission;
use App\Helpers\LogActivity;
use Illuminate\Http\Request;

/**
 * ReportController (Adviser)
 *
 * Handles report submission and grade summary display.
 * When a report is submitted — triggers the Python risk classifier
 * to generate Low/Moderate/High risk levels for each student.
 */
class ReportController extends Controller
{
    /**
     * Show the Submit Report page.
     * Displays term submission status and grade summary per student.
     * Uses a single pre-loaded grades collection to avoid N+1 queries.
     */
    public function show()
    {
        $section = Section::where('adviser_id', auth()->id())
            ->with(['track', 'specialization'])
            ->first();

        if (!$section) {
            return view('adviser.submit-report', [
                'section'       => null,
                'submissions'   => collect(),
                'gradeSummary'  => collect(),
                'subjects'      => collect(),
                'termStatus'    => [],
                'totalExpected' => 0,
            ]);
        }

        $subjects = $this->getSectionSubjects($section);
        $students = Student::where('section_id', $section->id)->orderBy('last_name')->get();

        // Total expected grades = students × subjects per term
        $totalExpected = $students->count() * $subjects->count();

        // Load all submissions for this section — keyed by grading_period
        $submissions = ReportSubmission::where('section_id', $section->id)
            ->where('school_year', $section->school_year)
            ->get()
            ->keyBy('grading_period');

        // Single query — load ALL grades for this section and school year
        // Avoids separate queries per student per term
        $allGrades = Grade::where('section_id', $section->id)
            ->where('school_year', $section->school_year)
            ->get();

        // Build grade summary per student using the pre-loaded collection
        $gradeSummary = $students->map(function ($student) use ($allGrades) {
            $studentGrades = $allGrades->where('student_id', $student->id);

            // Average per term — null if no grades yet for that term
            $term1 = $studentGrades->where('grading_period', 1)->avg('grade');
            $term2 = $studentGrades->where('grading_period', 2)->avg('grade');
            $term3 = $studentGrades->where('grading_period', 3)->avg('grade');

            // Flag if student has any failing grade (below 75)
            $hasFailingGrade = $studentGrades->where('grade', '<', 75)->count() > 0;

            return [
                'student'     => $student,
                'term1'       => $term1 ? round($term1, 2) : null,
                'term2'       => $term2 ? round($term2, 2) : null,
                'term3'       => $term3 ? round($term3, 2) : null,
                'has_failing' => $hasFailingGrade,
            ];
        });

        // Build term status for each of the 3 terms
        $termStatus = [];
        foreach ([1, 2, 3] as $term) {
            $encoded = $allGrades->where('grading_period', $term)->count();

            $termStatus[$term] = [
                'encoded'    => $encoded,
                'expected'   => $totalExpected,
                'complete'   => $totalExpected > 0 && $encoded >= $totalExpected,
                'submitted'  => isset($submissions[$term]),
                'submission' => $submissions[$term] ?? null,
            ];
        }

        return view('adviser.submit-report', compact(
            'section', 'submissions', 'gradeSummary',
            'subjects', 'termStatus', 'totalExpected'
        ));
    }

    /**
     * Submit a term report.
     * Checks if all grades are complete, then runs Python risk classification.
     * Records the submission and logs the action.
     */
    public function submit(Request $request)
    {
        $section = Section::where('adviser_id', auth()->id())->firstOrFail();

        $request->validate([
            'grading_period' => 'required|in:1,2,3',
        ]);

        $gradingPeriod = (int) $request->grading_period;
        $subjects      = $this->getSectionSubjects($section);
        $students      = Student::where('section_id', $section->id)->get();
        $totalExpected = $students->count() * $subjects->count();

        // Check if all grades are encoded before allowing submission
        $encoded = Grade::where('section_id', $section->id)
            ->where('grading_period', $gradingPeriod)
            ->where('school_year', $section->school_year)
            ->count();

        if ($encoded < $totalExpected) {
            return back()->with('error',
                'Please complete all grades before submitting. ' .
                ($totalExpected - $encoded) . ' grade(s) remaining.'
            );
        }

        // Load grades for this term — grouped by student for easy averaging
        $termGrades = Grade::where('section_id', $section->id)
            ->where('grading_period', $gradingPeriod)
            ->where('school_year', $section->school_year)
            ->get()
            ->groupBy('student_id');

        // Build the data payload for the Python classifier
        // Format: [{ student_id: X, average_grade: Y.YY }, ...]
        $gradesData = $students->map(function ($student) use ($termGrades) {
            $studentGrades = $termGrades->get($student->id, collect());
            $avg = $studentGrades->count() > 0
                ? round($studentGrades->avg('grade'), 2)
                : 0;
            return ['student_id' => $student->id, 'average_grade' => $avg];
        })->toArray();

        // Run the Python Random Forest classifier
        $this->runAnalytics($gradesData, $section, $gradingPeriod);

        // Record the submission — updateOrCreate for re-submissions
        ReportSubmission::updateOrCreate(
            [
                'section_id'     => $section->id,
                'grading_period' => $gradingPeriod,
                'school_year'    => $section->school_year,
            ],
            [
                'submitted_by' => auth()->id(),
                'submitted_at' => now(),
                'status'       => 'submitted',
            ]
        );

        // Different log label for first submission vs re-submission
        $action = $request->resubmit ? 'resubmit_report' : 'submit_report';
        $label  = $request->resubmit ? 'Re-submitted' : 'Submitted';

        LogActivity::log(
            $action,
            $label . ' Term ' . $gradingPeriod . ' report — Section ' . $section->name,
            'report_submissions',
            null
        );

        return redirect()->route('adviser.submit.report')
            ->with('success', 'Term ' . $gradingPeriod . ' report submitted successfully!');
    }

    /**
     * Run the Python Random Forest classifier.
     *
     * Flow:
     * 1. Write grades data as JSON to a temp file
     * 2. Call Python script via exec()
     * 3. Read the output JSON file
     * 4. Save risk results to the database
     * 5. Clean up temp files
     *
     * Note: exec() is used instead of Laravel Process facade
     * because Process facade causes WinError 10106 on Windows
     * when scikit-learn (joblib/asyncio) is involved.
     */
    private function runAnalytics(array $gradesData, Section $section, int $gradingPeriod): void
    {
        // Use section ID in filename to prevent conflicts if multiple advisers submit simultaneously
        $tempFile   = storage_path('app/temp_grades_' . $section->id . '.json');
        $outputFile = storage_path('app/temp_results_' . $section->id . '.json');

        // Write input data for Python
        file_put_contents($tempFile, json_encode($gradesData));

        // Python path from .env — not hardcoded to support different environments
        $pythonPath = env('PYTHON_PATH', 'python');
        $scriptPath = base_path('analytics' . DIRECTORY_SEPARATOR . 'classify.py');

        $command    = "\"{$pythonPath}\" \"{$scriptPath}\" \"{$tempFile}\" \"{$outputFile}\"";
        $execOutput = [];
        $exitCode   = 0;

        exec($command, $execOutput, $exitCode);

        // If Python failed or output file missing — log and return
        if ($exitCode !== 0 || !file_exists($outputFile)) {
            \Log::error('Analytics failed (exit: ' . $exitCode . '). Output: ' . implode("\n", $execOutput));
            @unlink($tempFile);
            return;
        }

        $raw = file_get_contents($outputFile);

        // Clean up temp files after reading
        @unlink($tempFile);
        @unlink($outputFile);

        $results = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE || !$results) {
            \Log::error('JSON decode error: ' . json_last_error_msg());
            return;
        }

        // Save each student's risk result to the database
        // updateOrCreate — handles re-submissions by updating existing records
        foreach ($results as $studentResult) {
            RiskResult::updateOrCreate(
                [
                    'student_id'     => $studentResult['student_id'],
                    'grading_period' => $gradingPeriod,
                    'school_year'    => $section->school_year,
                ],
                [
                    'average_grade' => $studentResult['average_grade'],
                    'risk_level'    => $studentResult['risk_level'],   // 'low', 'moderate', or 'high'
                    'confidence'    => $studentResult['confidence'] ?? null, // 0-100%
                    'generated_at'  => now(),
                ]
            );
        }
    }

    /**
     * Get subjects for a section based on grade level, track, and specialization.
     * Core subjects appear for all sections of the same grade level.
     * Elective subjects are filtered by track and specialization.
     */
    private function getSectionSubjects(Section $section)
    {
        return Subject::where('grade_level', $section->grade_level)
            ->where(function ($query) use ($section) {
                $query->where('type', 'core')
                    ->orWhere(function ($q) use ($section) {
                        $q->where('type', 'elective')
                          ->where('track_id', $section->track_id)
                          ->where(function ($q2) use ($section) {
                              $q2->whereNull('specialization_id')
                                 ->orWhere('specialization_id', $section->specialization_id);
                          });
                    });
            })
            ->get();
    }
}