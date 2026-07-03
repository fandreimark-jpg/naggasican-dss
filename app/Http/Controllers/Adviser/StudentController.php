<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Section;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        $section  = Section::where('adviser_id', auth()->id())->first();
        $students = $section
            ? Student::where('section_id', $section->id)->orderBy('last_name')->get()
            : collect();

        return view('adviser.students', compact('students', 'section'));
    }

    public function edit($id)
    {
        $section = Section::where('adviser_id', auth()->id())->first();
        $student = Student::where('id', $id)
            ->where('section_id', $section->id)
            ->firstOrFail();

        return view('adviser.students-edit', compact('student', 'section'));
    }

    public function update(Request $request, $id)
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
}