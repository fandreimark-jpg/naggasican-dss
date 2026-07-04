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
                'termStatus'   => [],
                'totalExpected'=> 0,
            ]);
        }

        $subjects  = $this->getSectionSubjects($section);
        $students  = Student::where('section_id', $section->id)
            ->orderBy('last_name')
            ->get();

        $totalExpected = $students->count() * $subjects->count();

        $submissions = ReportSubmission::where('section_id', $section->id)
            ->where('school_year', $section->school_year)
            ->get()
            ->keyBy('grading_period');

        $allGrades = Grade::where('section_id', $section->id)
            ->where('school_year', $section->school_year)
            ->get();

        $gradeSummary = $students->map(function ($student) use ($allGrades) {
            $studentGrades = $allGrades->where('student_id', $student->id);

            $term1 = $studentGrades->where('grading_period', 1)->avg('grade');
            $term2 = $studentGrades->where('grading_period', 2)->avg('grade');
            $term3 = $studentGrades->where('grading_period', 3)->avg('grade');

            $hasFailingGrade = $studentGrades->where('grade', '<', 75)->count() > 0;

            return [
                'student'         => $student,
                'term1'           => $term1 ? round($term1, 2) : null,
                'term2'           => $term2 ? round($term2, 2) : null,
                'term3'           => $term3 ? round($term3, 2) : null,
                'has_failing'     => $hasFailingGrade,
            ];
        });

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

        $termGrades = Grade::where('section_id', $section->id)
            ->where('grading_period', $gradingPeriod)
            ->where('school_year', $section->school_year)
            ->get()
            ->groupBy('student_id');

        $gradesData = $students->map(function ($student) use ($termGrades) {
            $studentGrades = $termGrades->get($student->id, collect());
            $avg = $studentGrades->count() > 0
                ? round($studentGrades->avg('grade'), 2)
                : 0;

            return [
                'student_id'    => $student->id,
                'average_grade' => $avg,
            ];
        })->toArray();

        $this->runAnalytics($gradesData, $section, $gradingPeriod);

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
        $tempFile   = storage_path('app/temp_grades_' . $section->id . '.json');
        $outputFile = storage_path('app/temp_results_' . $section->id . '.json');

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
            \Log::error('JSON decode error: ' . json_last_error_msg());
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