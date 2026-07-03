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

class ReportController extends Controller
{
    public function show()
    {
        $section = Section::where('adviser_id', auth()->id())
            ->with(['track', 'specialization'])
            ->first();

        if (!$section) {
            return view('adviser.submit-report', [
                'section'      => null,
                'submissions'  => collect(),
                'gradeSummary' => collect(),
                'subjects'     => collect(),
            ]);
        }

        $subjects = $this->getSectionSubjects($section);

        $submissions = ReportSubmission::where('section_id', $section->id)
            ->where('school_year', $section->school_year)
            ->get()
            ->keyBy('grading_period');

        $students = Student::where('section_id', $section->id)
            ->orderBy('last_name')
            ->get();

        $totalExpected = $students->count() * $subjects->count();

        $gradeSummary = $students->map(function ($student) use ($section) {
            $termAverages = [];
            foreach ([1, 2, 3] as $term) {
                $grades = Grade::where('student_id', $student->id)
                    ->where('section_id', $section->id)
                    ->where('grading_period', $term)
                    ->where('school_year', $section->school_year)
                    ->pluck('grade');

                $termAverages[$term] = $grades->count() > 0
                    ? round($grades->avg(), 2)
                    : null;
            }

            $allGrades   = array_filter($termAverages);
            $overallAvg  = count($allGrades) > 0
                ? round(array_sum($allGrades) / count($allGrades), 2)
                : null;

            $hasFailingGrade = Grade::where('student_id', $student->id)
                ->where('section_id', $section->id)
                ->where('school_year', $section->school_year)
                ->where('grade', '<', 75)
                ->exists();

            return [
                'student'          => $student,
                'term1'            => $termAverages[1],
                'term2'            => $termAverages[2],
                'term3'            => $termAverages[3],
                'overall_average'  => $overallAvg,
                'has_failing'      => $hasFailingGrade,
            ];
        });

        $termStatus = [];
        foreach ([1, 2, 3] as $term) {
            $encoded = Grade::where('section_id', $section->id)
                ->where('grading_period', $term)
                ->where('school_year', $section->school_year)
                ->count();

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

    public function submit(Request $request)
    {
        $section = Section::where('adviser_id', auth()->id())->firstOrFail();

        $request->validate([
            'grading_period' => 'required|in:1,2,3',
        ]);

        $gradingPeriod = $request->grading_period;
        $subjects      = $this->getSectionSubjects($section);
        $students      = Student::where('section_id', $section->id)->get();
        $totalExpected = $students->count() * $subjects->count();

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

        // Build grades data for Python classifier
        $gradesData = $students->map(function ($student) use ($section, $gradingPeriod) {
            $grades = Grade::where('student_id', $student->id)
                ->where('section_id', $section->id)
                ->where('grading_period', $gradingPeriod)
                ->where('school_year', $section->school_year)
                ->pluck('grade');

            return [
                'student_id'    => $student->id,
                'average_grade' => $grades->count() > 0
                    ? round($grades->avg(), 2)
                    : 0,
            ];
        })->toArray();

        // Run Python risk classifier
        $this->runAnalytics($gradesData, $section, $gradingPeriod);

        // Record the submission
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

    private function runAnalytics(array $gradesData, Section $section, int $gradingPeriod): void
    {
        $tempFile   = storage_path('app/temp_grades.json');
        $outputFile = storage_path('app/temp_results.json');

        file_put_contents($tempFile, json_encode($gradesData));

        $pythonPath = env('PYTHON_PATH', 'python');
        $scriptPath = base_path('analytics' . DIRECTORY_SEPARATOR . 'classify.py');

        $command    = "\"{$pythonPath}\" \"{$scriptPath}\" \"{$tempFile}\" \"{$outputFile}\"";
        $execOutput = [];
        $exitCode   = 0;
        exec($command, $execOutput, $exitCode);

        if ($exitCode !== 0 || !file_exists($outputFile)) {
            \Log::error('Analytics failed (exit: ' . $exitCode . '). Output: ' . implode("\n", $execOutput));
            @unlink($tempFile);
            return;
        }

        $raw = file_get_contents($outputFile);

        @unlink($tempFile);
        @unlink($outputFile);

        $results = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE || !$results) {
            \Log::error('JSON decode error from Python output: ' . json_last_error_msg());
            return;
        }

        foreach ($results as $studentResult) {
            RiskResult::updateOrCreate(
                [
                    'student_id'     => $studentResult['student_id'],
                    'grading_period' => $gradingPeriod,
                    'school_year'    => $section->school_year,
                ],
                [
                    'average_grade' => $studentResult['average_grade'],
                    'risk_level'    => $studentResult['risk_level'],
                    'confidence'    => $studentResult['confidence'] ?? null,
                    'generated_at'  => now(),
                ]
            );
        }
    }

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