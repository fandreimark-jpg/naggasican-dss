@extends('layouts.app')

@section('title', 'Students')
@section('subtitle', 'Manage student records')

@section('content')

<div class="flex justify-end mb-4">
    <button type="button" onclick="openAddStudentModal()"
        class="bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-800">
        + Add Student
    </button>
</div>

@if(session('error'))
    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
@endif

{{-- Filter by Section --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-4">
    <form method="GET" class="flex gap-4 items-end">
        <div>
            <label class="block text-sm text-gray-600 mb-1">Filter by Section</label>
            <select name="section_id" onchange="this.form.submit()"
                    class="border rounded-lg px-3 py-2 text-sm">
                <option value="">All Sections</option>
                @foreach($sections as $section)
                    <option value="{{ $section->id }}"
                        {{ request('section_id') == $section->id ? 'selected' : '' }}>
                        {{ $section->name }} — Grade {{ $section->grade_level }}
                    </option>
                @endforeach
            </select>
        </div>
    </form>
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
                <th class="text-left px-6 py-3">Section</th>
                <th class="px-6 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($students as $student)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-3 text-gray-600">{{ $student->lrn }}</td>
                <td class="px-6 py-3 font-medium text-gray-800">{{ $student->last_name }}</td>
                <td class="px-6 py-3 text-gray-800">{{ $student->first_name }}</td>
                <td class="px-6 py-3 text-gray-600">{{ $student->middle_name ?? '—' }}</td>
                <td class="px-6 py-3 capitalize text-gray-600">{{ $student->gender }}</td>
                <td class="px-6 py-3 text-gray-600">
                    {{ $student->section->name ?? '—' }}
                    <span class="text-gray-400 text-xs">
                        {{ $student->section ? '(Grade ' . $student->section->grade_level . ')' : '' }}
                    </span>
                </td>
                <td class="px-6 py-3 text-right space-x-2">
                    <button type="button"
                        onclick='openEditStudentModal(@json($student))'
                        class="text-blue-600 hover:underline">Edit</button>

                    <form method="POST"
                          action="{{ route('principal.students.destroy', $student->id) }}"
                          class="inline"
                          onsubmit="return confirm('Remove this student?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-6 text-center text-gray-400">No students yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ADD / EDIT STUDENT MODAL --}}
<div id="principalStudentModal"
     class="{{ $errors->any() ? '' : 'hidden' }} fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-lg p-6">

        <div class="flex justify-between items-center mb-4">
            <h3 id="studentModalTitle" class="text-lg font-semibold text-gray-800">Add Student</h3>
            <button type="button" onclick="closeStudentModal()"
                    class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        @if($errors->any())
            <div class="bg-red-100 text-red-700 text-sm p-3 rounded-lg mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="principalStudentForm" method="POST"
              data-store-url="{{ route('principal.students.store') }}"
              class="space-y-4">
            @csrf
            <input type="hidden" name="_method" id="studentMethod" value="POST">

            <div>
                <label class="block text-sm text-gray-600 mb-1">LRN (12 digits)</label>
                <input type="text" name="lrn" id="ps_lrn" maxlength="12" required
                       value="{{ old('lrn') }}"
                       class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Last Name</label>
                    <input type="text" name="last_name" id="ps_last_name" required
                           value="{{ old('last_name') }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">First Name</label>
                    <input type="text" name="first_name" id="ps_first_name" required
                           value="{{ old('first_name') }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Middle Name</label>
                <input type="text" name="middle_name" id="ps_middle_name"
                       value="{{ old('middle_name') }}"
                       class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Gender</label>
                    <select name="gender" id="ps_gender" required
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                        <option value="">Select</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Birthdate</label>
                    <input type="date" name="birthdate" id="ps_birthdate"
                           value="{{ old('birthdate') }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Section</label>
                <select name="section_id" id="ps_section_id" required
                        class="w-full border rounded-lg px-3 py-2 text-sm">
                    <option value="">Select Section</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}">
                            {{ $section->name }} — Grade {{ $section->grade_level }}
                            ({{ $section->school_year }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeStudentModal()"
                        class="px-4 py-2 text-sm text-gray-500">Cancel</button>
                <button type="submit" id="studentSubmitBtn"
                        class="bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium">
                    Save Student
                </button>
            </div>
        </form>
    </div>
</div>

@endsection