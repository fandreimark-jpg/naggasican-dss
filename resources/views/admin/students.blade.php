@extends('layouts.app')

@section('title', 'Students')
@section('subtitle', 'Manage student records')

@section('content')

<div class="bg-white rounded-xl shadow-sm mb-0">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 px-6 py-4 border-b">
        <div>
            <h2 class="text-sm font-semibold text-gray-800">All Students</h2>
            <p class="text-xs text-gray-400">{{ $students->count() }} total students</p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <form method="GET">
                <select name="section_id" onchange="this.form.submit()"
                    class="border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                    <option value="">All Sections</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}"
                            {{ request('section_id') == $section->id ? 'selected' : '' }}>
                            {{ $section->name }} — Grade {{ $section->grade_level }}
                        </option>
                    @endforeach
                </select>
            </form>
            <div class="relative">
                <input type="text" id="studentSearch"
                    placeholder="Search students..."
                    class="border rounded-lg pl-9 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 w-56">
                <i class="bi bi-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
            </div>
            {{-- "Add Student" button removed — adding new students is now
                 exclusively done by Advisers on their own Students page.
                 The Admin can still Edit (correct info) and Delete below. --}}
        </div>
    </div>

    <table class="w-full text-sm" id="studentTable">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
            <tr>
                <th class="text-left px-6 py-3">LRN</th>
                <th class="text-left px-6 py-3">Last Name</th>
                <th class="text-left px-6 py-3">First Name</th>
                <th class="text-left px-6 py-3">Middle Name</th>
                <th class="text-left px-6 py-3">Birthdate</th>
                <th class="text-left px-6 py-3">Gender</th>
                <th class="text-left px-6 py-3">Section</th>
                <th class="px-6 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100" id="studentTableBody">
            @forelse($students as $student)
            <tr class="hover:bg-gray-50 student-row">
                <td class="px-6 py-3 text-gray-600">{{ $student->lrn }}</td>
                <td class="px-6 py-3 font-medium text-gray-800">{{ $student->last_name }}</td>
                <td class="px-6 py-3 text-gray-800">{{ $student->first_name }}</td>
                <td class="px-6 py-3 text-gray-500">{{ $student->middle_name ?? '—' }}</td>
                <td class="px-6 py-3 capitalize text-gray-600">{{ $student->formatted_birthdate }}</td>
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
                        class="inline-flex items-center gap-1 text-brand-600 hover:text-brand-800 text-xs font-medium border border-brand-200 rounded px-2 py-1 hover:bg-brand-50">
                        <i class="bi bi-pencil-square"></i> Edit
                    </button>
                    <form method="POST"
                          action="{{ route('admin.students.destroy', $student->id) }}"
                          class="inline"
                          data-confirm="Remove student {{ $student->last_name }}, {{ $student->first_name }}?">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center gap-1 text-red-600 hover:text-red-800 text-xs font-medium border border-red-200 rounded px-2 py-1 hover:bg-red-50">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                    <i class="bi bi-people text-2xl block mb-2"></i>
                    No students yet.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div id="noStudentResults" class="hidden px-6 py-8 text-center text-gray-400">
        <i class="bi bi-search text-2xl block mb-2"></i>
        No students found matching your search.
    </div>

    @if($students->hasPages())
    <div class="px-6 py-4 border-t flex flex-col items-center gap-2 text-sm text-gray-500">
        <div class="flex items-center gap-1">
            @if($students->onFirstPage())
                <span class="px-3 py-1 rounded border text-gray-300 cursor-not-allowed">← Prev</span>
            @else
                <a href="{{ $students->previousPageUrl() }}"
                   class="px-3 py-1 rounded border hover:bg-gray-50 text-gray-600">← Prev</a>
            @endif
            <span class="px-3 py-1 rounded border bg-brand-700 text-white font-medium">
                {{ $students->currentPage() }}
            </span>
            @if($students->hasMorePages())
                <a href="{{ $students->nextPageUrl() }}"
                   class="px-3 py-1 rounded border hover:bg-gray-50 text-gray-600">Next →</a>
            @else
                <span class="px-3 py-1 rounded border text-gray-300 cursor-not-allowed">Next →</span>
            @endif
        </div>
        <span class="text-xs">Showing {{ $students->firstItem() }}–{{ $students->lastItem() }} of {{ $students->total() }} students</span>
    </div>
    @endif
</div>

{{-- EDIT STUDENT MODAL
     ("Add" mode was removed — see note above. This modal is now
     only ever opened via openEditStudentModal() in modal.js.) --}}
<div id="adminStudentModal"
     class="{{ $errors->any() ? 'opacity-100' : 'hidden opacity-0' }} fixed inset-0 bg-black/40 flex items-center justify-center z-50 transition-opacity duration-200">
    <div class="modal-box {{ $errors->any() ? 'scale-100 opacity-100' : 'scale-95 opacity-0' }} bg-white rounded-xl shadow-lg w-full max-w-lg p-6 transition-all duration-200">

        <div class="flex justify-between items-center mb-4">
            <h3 id="studentModalTitle" class="text-lg font-semibold text-gray-800">Edit Student</h3>
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

        <form id="adminStudentForm" method="POST"
              class="space-y-4">
            @csrf
            <input type="hidden" name="_method" id="studentMethod" value="PUT">

            <div>
                <label class="block text-sm text-gray-600 mb-1">LRN (12 digits)</label>
                <input type="text" name="lrn" id="ps_lrn" maxlength="12" required
                       value="{{ old('lrn') }}"
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Last Name</label>
                    <input type="text" name="last_name" id="ps_last_name" required
                           value="{{ old('last_name') }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">First Name</label>
                    <input type="text" name="first_name" id="ps_first_name" required
                           value="{{ old('first_name') }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Middle Name</label>
                <input type="text" name="middle_name" id="ps_middle_name"
                       value="{{ old('middle_name') }}"
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Gender</label>
                    <select name="gender" id="ps_gender" required
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        <option value="">Select</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Birthdate</label>
                    <input type="date" name="birthdate" id="ps_birthdate"
                           value="{{ old('birthdate') }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Section</label>
                <select name="section_id" id="ps_section_id" required
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
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
                        class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700">Cancel</button>
                <button type="submit" id="studentSubmitBtn"
                        class="bg-brand-700 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-brand-800">
                    Update Student
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        initTableSearch('studentSearch', '.student-row', 'noStudentResults');
    });
</script>
@endpush

@endsection