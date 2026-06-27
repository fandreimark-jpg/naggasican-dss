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

class AdviserController extends Controller
{
    // =============================================
    // HELPER — Kunin ang tamang subjects ng section
    // Core subjects + Electives ng track/spec
    // =============================================
    private function getSectionSubjects(Section $section)
    {
        return Subject::where('grade_level', $section->grade_level)
            ->where(function ($query) use ($section) {
                // Core subjects — para sa lahat
                $query->where('type', 'core')
                    // Electives — specific sa track/specialization ng section
                    ->orWhere(function ($q) use ($section) {
                        $q->where('type', 'elective')
                          ->where('track_id', $section->track_id)
                          ->where(function ($q2) use ($section) {
                              // Kung walang specific specialization ang subject
                              // available sa buong track
                              $q2->whereNull('specialization_id')
                                 ->orWhere('specialization_id', $section->specialization_id);
                          });
                    });
            })
            ->get();
    }

    // =============================================
    // DASHBOARD
    // =============================================
    public function dashboard()
    {
        $section = Section::where('adviser_id', auth()->id())
                        ->with(['track', 'specialization'])
                        ->first();

        $totalStudents = $section
            ? Student::where('section_id', $section->id)->count()
            : 0;

        // ✅ Subjects base sa track/specialization ng section
        $subjects = $section ? $this->getSectionSubjects($section) : collect();

        // ✅ Total expected grades = students × subjects × 3 terms
        $totalExpectedPerTerm = $totalStudents * $subjects->count();

        // ✅ Grades encoded per term
        $term1Count = $section ? Grade::where('section_id', $section->id)
            ->where('grading_period', 1)
            ->where('school_year', $section->school_year)
            ->count() : 0;

        $term2Count = $section ? Grade::where('section_id', $section->id)
            ->where('grading_period', 2)
            ->where('school_year', $section->school_year)
            ->count() : 0;

        $term3Count = $section ? Grade::where('section_id', $section->id)
            ->where('grading_period', 3)
            ->where('school_year', $section->school_year)
            ->count() : 0;

        $totalGradesEncoded = $term1Count + $term2Count + $term3Count;

        // ✅ Submissions per term
        $submissions = $section ? ReportSubmission::where('section_id', $section->id)
            ->where('school_year', $section->school_year)
            ->get()
            ->keyBy('grading_period') : collect();

        // ✅ Pending = terms na kumpleto ang grades pero hindi pa na-submit
        $pendingCount = 0;
        foreach ([1, 2, 3] as $term) {
            $termCount = match($term) {
                1 => $term1Count,
                2 => $term2Count,
                3 => $term3Count,
            };
            $isComplete  = $totalExpectedPerTerm > 0 && $termCount >= $totalExpectedPerTerm;
            $isSubmitted = isset($submissions[$term]);
            if ($isComplete && !$isSubmitted) {
                $pendingCount++;
            }
        }

        // ✅ Students with their latest risk level
        $students = $section
            ? Student::where('section_id', $section->id)
                ->with(['riskResults' => function ($q) use ($section) {
                    $q->where('school_year', $section->school_year)
                    ->orderBy('grading_period', 'desc');
                }])
                ->orderBy('last_name')
                ->get()
            : collect();

        return view('adviser.dashboard', compact(
            'section', 'totalStudents', 'totalGradesEncoded',
            'pendingCount', 'submissions', 'students',
            'totalExpectedPerTerm',
            'term1Count', 'term2Count', 'term3Count'
        ));
    }

    // =============================================
    // STUDENTS
    // =============================================
    public function students()
    {
        $section  = Section::where('adviser_id', auth()->id())->first();
        $students = $section
            ? Student::where('section_id', $section->id)->orderBy('last_name')->get()
            : collect();

        return view('adviser.students', compact('students', 'section'));
    }

    public function editStudent($id)
    {
        $section = Section::where('adviser_id', auth()->id())->first();
        $student = Student::where('id', $id)
            ->where('section_id', $section->id)
            ->firstOrFail();

        return view('adviser.students-edit', compact('student', 'section'));
    }

    public function updateStudent(Request $request, $id)
    {
        $section = Section::where('adviser_id', auth()->id())->first();
        $student = Student::where('id', $id)
            ->where('section_id', $section->id)
            ->firstOrFail();

        $request->validate([
            'last_name'   => 'required|string|max:255',
            'first_name'  => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'gender'      => 'required|in:male,female',
            'birthdate'   => 'nullable|date',
        ]);

        $student->update($request->only([
            'last_name', 'first_name', 'middle_name', 'gender', 'birthdate'
        ]));

        return redirect()->route('adviser.students')
            ->with('success', 'Student updated successfully!');
    }

    // =============================================
    // GRADES
    // =============================================
    public function grades()
    {
        $section  = Section::where('adviser_id', auth()->id())
                        ->with(['track', 'specialization'])
                        ->first();

        $students = $section
            ? Student::where('section_id', $section->id)
                ->with(['grades' => function ($q) use ($section) {
                    $q->where('school_year', $section->school_year);
                }])
                ->orderBy('last_name')
                ->get()
            : collect();

        // ✅ Core subjects + Electives ng track/specialization ng section
        $subjects = $section
            ? $this->getSectionSubjects($section)
            : collect();

        return view('adviser.grades', compact('students', 'subjects', 'section'));
    }

    public function storeGrade(Request $request)
    {
        $section = Section::where('adviser_id', auth()->id())->first();

        foreach ($request->grades as $studentId => $subjects) {
            foreach ($subjects as $subjectId => $grade) {
                if ($grade === null || $grade === '') continue;

                Grade::updateOrCreate(
                    [
                        'student_id'     => $studentId,
                        'subject_id'     => $subjectId,
                        'grading_period' => $request->grading_period,
                        'school_year'    => $section->school_year,
                    ],
                    [
                        'section_id' => $section->id,
                        'grade'      => $grade,
                        'encoded_by' => auth()->id(),
                    ]
                );
            }
        }

        // ✅ Log the activity
       LogActivity::log(
            'encode_grades',
            'Encoded grades for Term ' . $request->grading_period . ' — Section ' . $section->name,
            'grades',
            $section->id
        );

        return back()->with('success', 'Grades saved successfully!');
    }

    // =============================================
    // SUBMIT REPORT
    // =============================================
    public function submitReport(Request $request)
    {
        $section = Section::where('adviser_id', auth()->id())->first();

        if (!$section) {
            return back()->with('error', 'No section assigned.');
        }

        $request->validate([
            'grading_period' => 'required|in:1,2,3',
        ]);

        $students      = Student::where('section_id', $section->id)->get();
        $subjects      = $this->getSectionSubjects($section);
        $totalExpected = $students->count() * $subjects->count();

        $gradeCount = Grade::where('section_id', $section->id)
            ->where('grading_period', $request->grading_period)
            ->where('school_year', $section->school_year)
            ->count();

        if ($gradeCount < $totalExpected) {
            return back()->with('error', 'Please complete all grades before submitting.');
        }

        // ✅ Allow re-submission — i-update lang ang existing record
        ReportSubmission::updateOrCreate(
            [
                'section_id'     => $section->id,
                'grading_period' => $request->grading_period,
                'school_year'    => $section->school_year,
            ],
            [
                'submitted_by' => auth()->id(),
                'status'       => 'submitted',
                'submitted_at' => now(),
            ]
        );

        // Re-run analytics para ma-update ang risk levels
        $this->runAnalytics($section->id, $request->grading_period);

        $isResubmit = $request->has('resubmit');
        $message    = $isResubmit
            ? 'Term ' . $request->grading_period . ' report re-submitted successfully!'
            : 'Term ' . $request->grading_period . ' report submitted successfully!';

        return back()->with('success', $message);
    }

    public function showSubmitReport()
    {
        $section = Section::where('adviser_id', auth()->id())->first();

        if (!$section) {
            return view('adviser.submit-report', [
                'section'     => null,
                'students'    => collect(),
                'subjects'    => collect(),
                'grades'      => collect(),
                'submissions' => collect(),
            ]);
        }

        $students    = Student::where('section_id', $section->id)->orderBy('last_name')->get();
        $subjects    = $this->getSectionSubjects($section);
        $grades      = Grade::where('section_id', $section->id)
                            ->where('school_year', $section->school_year)
                            ->get();
        $submissions = ReportSubmission::where('section_id', $section->id)
                            ->where('school_year', $section->school_year)
                            ->get();

        return view('adviser.submit-report', compact(
            'section', 'students', 'subjects', 'grades', 'submissions'
        ));
    }

    // =============================================
    // ANALYTICS — Python RF Classifier
    // =============================================
    private function runAnalytics($sectionId, $gradingPeriod)
    {
        $section  = Section::find($sectionId);
        $students = Student::where('section_id', $sectionId)->get();
        $subjects = $this->getSectionSubjects($section);

        $submittedPeriods = ReportSubmission::where('section_id', $sectionId)
            ->where('school_year', $section->school_year)
            ->where('status', 'submitted')
            ->pluck('grading_period')
            ->toArray();

        if (empty($submittedPeriods)) return;

        $gradesData = [];

        foreach ($students as $student) {
            $periodAverages = [];

            foreach ($submittedPeriods as $period) {
                $periodAvg = Grade::where('student_id', $student->id)
                    ->where('grading_period', $period)
                    ->where('school_year', $section->school_year)
                    ->whereIn('subject_id', $subjects->pluck('id'))
                    ->avg('grade');

                if ($periodAvg !== null) {
                    $periodAverages[] = $periodAvg;
                }
            }

            if (!empty($periodAverages)) {
                $overallAvg   = array_sum($periodAverages) / count($periodAverages);
                $gradesData[] = [
                    'student_id'    => $student->id,
                    'average_grade' => round($overallAvg, 2),
                ];
            }
        }

        if (empty($gradesData)) {
            \Log::error('runAnalytics: gradesData is empty');
            return;
        }

        $tempFile   = storage_path('app/temp_grades.json');
        $outputFile = storage_path('app/temp_results.json');

        file_put_contents($tempFile, json_encode($gradesData));

        $pythonPath  = 'C:\\Users\\kimbe\\AppData\\Local\\Programs\\Python\\Python314\\python.exe';
        $scriptPath  = base_path('analytics\\classify.py');
        $command     = "\"{$pythonPath}\" \"{$scriptPath}\" \"{$tempFile}\" \"{$outputFile}\" 2>&1";
        $shellOutput = shell_exec($command);

\Log::info('Python command: ' . $command);
\Log::info('Python output: ' . $shellOutput);

// PALITAN NG:
if (!file_exists($outputFile)) {
    \Log::error('Output file missing. Python said: ' . $shellOutput);
    @unlink($tempFile);
    return;
}

$raw = file_get_contents($outputFile);
\Log::info('Raw JSON from Python: ' . $raw);

@unlink($tempFile);
@unlink($outputFile);

$results = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    \Log::error('JSON decode error: ' . json_last_error_msg());
    return;
}

if (!$results) return;
        \Log::info('Raw results from Python: ' . json_encode($results));
        foreach ($results as $result) {
            \Log::info('Saving result: ' . json_encode($result));
            RiskResult::updateOrCreate(
                [
                    'student_id'     => $result['student_id'],
                    'grading_period' => $gradingPeriod,
                    'school_year'    => $section->school_year,
                ],
                [
                    'average_grade' => $result['average_grade'],
                    'risk_level'    => $result['risk_level'],
                    'confidence'    => $result['confidence'] ?? null,
                    'generated_at'  => now(),
                ]
            );
        }
    }
}