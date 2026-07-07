<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Grade;
use App\Models\Section;
use App\Helpers\LogActivity;
use Illuminate\Http\Request;

/**
 * GradeController (Adviser)
 *
 * Handles grade encoding for the adviser's assigned section.
 * Advisers can only encode grades for students in their own section.
 * Grades are organized by term (grading period 1, 2, or 3).
 */
class GradeController extends Controller
{
    /**
     * Show the grade encoding page.
     * Loads subjects filtered by the section's grade level, track, and specialization.
     * Grades are pre-loaded as a keyed collection to avoid N+1 queries.
     */
    public function index()
    {
        // Get the section assigned to the currently logged-in adviser
        $section = Section::where('adviser_id', auth()->id())
            ->with(['track', 'specialization'])
            ->first();

        // If no section assigned — show empty state
        if (!$section) {
            return view('adviser.grades', [
                'section'        => null,
                'students'       => collect(),
                'subjects'       => collect(),
                'grades'         => collect(),
                'selectedPeriod' => 1,
            ]);
        }

        // Get students in this section ordered alphabetically
        $students = Student::where('section_id', $section->id)
            ->orderBy('last_name')
            ->get();

        // Get subjects for this section
        // Core subjects: apply to all sections of the same grade level
        // Elective subjects: filtered by track and specialization
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
            ->orderBy('type') // core subjects first
            ->orderBy('name')
            ->get();

        // Get the selected term from URL — defaults to Term 1
        // URL parameter: ?period=1, ?period=2, or ?period=3
        $selectedPeriod = (int) request('period', 1);

        // Load grades for selected term as a keyed collection
        // Key format: "student_id_subject_id" for O(1) lookup in the blade
        // This avoids N+1 queries — one query for all grades
        $grades = Grade::where('section_id', $section->id)
            ->where('grading_period', $selectedPeriod)
            ->where('school_year', $section->school_year)
            ->get()
            ->keyBy(fn($g) => $g->student_id . '_' . $g->subject_id);

        return view('adviser.grades', compact(
            'section', 'students', 'subjects', 'grades', 'selectedPeriod'
        ));
    }

    /**
     * Save grades submitted from the encoding form.
     * Uses updateOrCreate() to handle both new and existing grade records.
     * Validates that all students belong to the adviser's section for security.
     */
    public function store(Request $request)
    {
        // Get the adviser's section
        $section = Section::where('adviser_id', auth()->id())->firstOrFail();

        $request->validate([
            'grading_period'          => 'required|in:1,2,3',
            'grades'                  => 'required|array',
            'grades.*.student_id'     => 'required|exists:students,id',
            'grades.*.subject_id'     => 'required|exists:subjects,id',
            'grades.*.grade'          => 'nullable|numeric|min:60|max:100',
        ]);

        // Pre-load valid student IDs — one query before the loop
        // Prevents N+1 queries from checking each student individually
        $validStudentIds = Student::where('section_id', $section->id)
            ->pluck('id')
            ->toArray();

        foreach ($request->grades as $gradeData) {
            // Skip empty grade fields — adviser may not have filled all grades
            if (!isset($gradeData['grade']) || $gradeData['grade'] === null || $gradeData['grade'] === '') {
                continue;
            }

            // Security check — ensure student belongs to adviser's section
            if (!in_array($gradeData['student_id'], $validStudentIds)) continue;

            // updateOrCreate — updates if exists, creates if new
            // Unique key: student + subject + section + term + school year
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
                    'encoded_by' => auth()->id(), // track who encoded the grade
                ]
            );
        }

        // Log the grade encoding action for audit trail
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