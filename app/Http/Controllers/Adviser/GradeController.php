<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Grade;
use App\Models\Section;
use App\Helpers\LogActivity;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function index()
    {
        $section = Section::where('adviser_id', auth()->id())
            ->with(['track', 'specialization'])
            ->first();

        if (!$section) {
            return view('adviser.grades', [
                'section'  => null,
                'students' => collect(),
                'subjects' => collect(),
                'grades'   => collect(),
            ]);
        }

        $students = Student::where('section_id', $section->id)
            ->orderBy('last_name')
            ->get();

        $subjects = Subject::where('grade_level', $section->grade_level)
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
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        // ✅ FIXED: URL uses ?period= not ?grading_period=
        $selectedPeriod = (int) request('period', 1);

        // ✅ keyBy instead of groupBy — para mag-return ng single Grade model
        // hindi collection, para ma-access ang ->grade directly sa blade
        $grades = Grade::where('section_id', $section->id)
            ->where('grading_period', $selectedPeriod)
            ->where('school_year', $section->school_year)
            ->get()
            ->keyBy(fn($g) => $g->student_id . '_' . $g->subject_id);

        return view('adviser.grades', compact(
            'section', 'students', 'subjects', 'grades', 'selectedPeriod'
        ));
    }

    public function store(Request $request)
    {
        $section = Section::where('adviser_id', auth()->id())->firstOrFail();

        $request->validate([
            'grading_period'          => 'required|in:1,2,3',
            'grades'                  => 'required|array',
            'grades.*.student_id'     => 'required|exists:students,id',
            'grades.*.subject_id'     => 'required|exists:subjects,id',
            'grades.*.grade'          => 'nullable|numeric|min:60|max:100',
        ]);

        // ✅ FIXED: Pre-load valid student IDs — isang query lang
        // Hindi na nagtatanong sa database sa loob ng loop (no N+1)
        $validStudentIds = Student::where('section_id', $section->id)
            ->pluck('id')
            ->toArray();

        foreach ($request->grades as $gradeData) {
            // Skip kung walang grade value
            if (!isset($gradeData['grade']) || $gradeData['grade'] === null || $gradeData['grade'] === '') {
                continue;
            }

            // Verify student belongs to adviser's section — security check
            if (!in_array($gradeData['student_id'], $validStudentIds)) continue;

            Grade::updateOrCreate(
                [
                    'student_id'     => $gradeData['student_id'],
                    'subject_id'     => $gradeData['subject_id'],
                    'section_id'     => $section->id,
                    'grading_period' => $request->grading_period,
                    'school_year'    => $section->school_year,
                ],
                [
                    'grade'      => $gradeData['grade'],
                    'encoded_by' => auth()->id(),
                ]
            );
        }

        LogActivity::log(
            'encode_grades',
            'Encoded grades for Term ' . $request->grading_period . ' — Section ' . $section->name,
            'grades',
            null
        );

        return redirect()
            ->route('adviser.grades')
            ->with('success', 'Grades for Term ' . $request->grading_period . ' saved successfully!');
    }
}