@extends('layouts.app')

@section('title', 'My Students')
@section('subtitle', $section ? 'Section ' . $section->name . ' — Grade ' . $section->grade_level : 'No section assigned')

@section('content')

{{-- Info notice --}}
<div class="bg-blue-50 border border-blue-200 text-blue-700 text-sm px-4 py-3 rounded-lg mb-4">
    You can view and edit student information. Contact the principal to add or remove students.
</div>

<div class="bg-white rounded-xl shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500">
            <tr>
                <th class="text-left px-6 py-3">LRN</th>
                <th class="text-left px-6 py-3">Last Name</th>
                <th class="text-left px-6 py-3">First Name</th>
                <th class="text-left px-6 py-3">Middle Name</th>
                <th class="text-left px-6 py-3">Gender</th>
                <th class="px-6 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($students as $student)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-3 text-gray-600">{{ $student->lrn }}</td>
                <td class="px-6 py-3 font-medium text-gray-800">{{ $student->last_name }}</td>
                <td class="px-6 py-3 text-gray-800">{{ $student->first_name }}</td>
                <td class="px-6 py-3 text-gray-800">{{ $student->middle_name ?? '—' }}</td>
                <td class="px-6 py-3 capitalize text-gray-600">{{ $student->gender }}</td>
                <td class="px-6 py-3 text-right">
                    <button type="button"
                        onclick='openEditModal(@json($student))'
                        class="text-blue-600 hover:underline text-sm">Edit</button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-6 text-center text-gray-400">
                    No students in your section yet.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- EDIT MODAL ONLY --}}
<div id="editModal"
     class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-lg p-6">

        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Edit Student</h3>
            <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        <form id="editForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Last Name</label>
                    <input type="text" name="last_name" id="edit_last_name" required
                           class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">First Name</label>
                    <input type="text" name="first_name" id="edit_first_name" required
                           class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Middle Name</label>
                <input type="text" name="middle_name" id="edit_middle_name"
                       class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Gender</label>
                    <select name="gender" id="edit_gender" required
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Birthdate</label>
                    <input type="date" name="birthdate" id="edit_birthdate"
                           class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeEditModal()"
                        class="px-4 py-2 text-sm text-gray-500">Cancel</button>
                <button type="submit"
                        class="bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium">
                    Update Student
                </button>
            </div>
        </form>
    </div>
</div>

@endsection