<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Section;
use App\Helpers\LogActivity;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        $query = Student::with(['section'])->orderBy('last_name');

        if (request('section_id')) {
            $query->where('section_id', request('section_id'));
        }

        $students = $query->paginate(10);
        $sections = Section::with(['track', 'specialization'])
            ->orderBy('grade_level')
            ->get();

        return view('principal.students', compact('students', 'sections'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'lrn'         => 'required|digits:12|unique:students,lrn',
            'last_name'   => 'required|string|max:255',
            'first_name'  => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'gender'      => 'required|in:male,female',
            'birthdate'   => 'nullable|date',
            'section_id'  => 'required|exists:sections,id',
        ]);

        Student::create($request->only([
            'lrn', 'last_name', 'first_name',
            'middle_name', 'gender', 'birthdate', 'section_id'
        ]));

        LogActivity::log(
            'add_student',
            'Added student: ' . $request->last_name . ', ' . $request->first_name,
            'students',
            null
        );

        return redirect()->route('principal.students')
            ->with('success', 'Student added successfully!');
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $request->validate([
            'lrn'         => 'required|digits:12|unique:students,lrn,' . $student->id,
            'last_name'   => 'required|string|max:255',
            'first_name'  => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'gender'      => 'required|in:male,female',
            'birthdate'   => 'nullable|date',
            'section_id'  => 'required|exists:sections,id',
        ]);

        $student->update($request->only([
            'lrn', 'last_name', 'first_name',
            'middle_name', 'gender', 'birthdate', 'section_id'
        ]));

        return redirect()->route('principal.students')
            ->with('success', 'Student updated successfully!');
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        LogActivity::log(
            'delete_student',
            'Removed student: ' . $student->last_name . ', ' . $student->first_name,
            'students',
            $id
        );

        return redirect()->route('principal.students')
            ->with('success', 'Student removed successfully!');
    }
}