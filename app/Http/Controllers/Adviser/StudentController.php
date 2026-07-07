<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Section;
use Illuminate\Http\Request;

/**
 * StudentController (Adviser)
 *
 * Allows the adviser to view and edit students in their own section.
 * Advisers CANNOT add or remove students — only the Principal can do that.
 * Scoped to the adviser's section for security.
 */
class StudentController extends Controller
{
    /**
     * Show all students in the adviser's section.
     */
    public function index()
    {
        // Get only the section assigned to this adviser
        $section  = Section::where('adviser_id', auth()->id())->first();
        $students = $section
            ? Student::where('section_id', $section->id)->orderBy('last_name')->get()
            : collect();

        return view('adviser.students', compact('students', 'section'));
    }

    /**
     * Show edit form for a specific student.
     * Verifies the student belongs to the adviser's section before showing.
     */
    public function edit($id)
    {
        $section = Section::where('adviser_id', auth()->id())->first();

        // Security check — ensure student belongs to adviser's section
        $student = Student::where('id', $id)
            ->where('section_id', $section->id)
            ->firstOrFail();

        return view('adviser.students-edit', compact('student', 'section'));
    }

    /**
     * Update a student's basic information.
     * Advisers can only update personal info — not LRN or section.
     */
    public function update(Request $request, $id)
    {
        $section = Section::where('adviser_id', auth()->id())->first();

        // Security check — ensure student belongs to adviser's section
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
}