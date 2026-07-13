@extends('layouts.app')

@section('title', 'My Students')
@section('subtitle', $section ? 'Section ' . $section->name . ' — Grade ' . $section->grade_level : 'No section assigned')

@section('content')


<div class="bg-white rounded-xl shadow-sm overflow-x-auto">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 px-6 py-4 border-b">
        <div>
            <h2 class="text-sm font-semibold text-gray-800">Students</h2>
            <p class="text-xs text-gray-400">{{ $students->count() }} student(s) in your section</p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            {{-- Search box--}}
            <div class="relative">
                <input type="text" id="adviserStudentSearch"
                    placeholder="Search students..."
                    class="border rounded-lg pl-9 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 w-56">
                <i class="bi bi-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
            </div>
            <button type="button" onclick="openAdviserAddStudent()"
                class="bg-brand-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand-800 whitespace-nowrap">
                <i class="bi bi-plus-lg"></i> Add Student
            </button>
        </div>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500">
            <tr>
                <th class="text-left px-6 py-3">LRN</th>
                <th class="text-left px-6 py-3">Last Name</th>
                <th class="text-left px-6 py-3">First Name</th>
                <th class="text-left px-6 py-3">Middle Name</th>
                <th class="text-left px-6 py-3">Birthdate</th>
                <th class="text-left px-6 py-3">Gender</th>
                <th class="px-6 py-3 text-right">Actions</th>
            </tr>
        </thead>

        <tbody id="adviserStudentTableBody" class="divide-y divide-gray-100">
            @forelse($students as $student)

            <tr class="student-row hover:bg-gray-50">
                <td class="px-6 py-3 text-gray-600">{{ $student->lrn }}</td>
                <td class="px-6 py-3 font-medium text-gray-800">{{ $student->last_name }}</td>
                <td class="px-6 py-3 text-gray-800">{{ $student->first_name }}</td>
                <td class="px-6 py-3 text-gray-800">{{ $student->middle_name ?? '—' }}</td>
                <td class="px-6 py-3 capitalize text-gray-600">{{ $student->formatted_birthdate }}</td>
                <td class="px-6 py-3 capitalize text-gray-600">{{ $student->gender }}</td>
                <td class="px-6 py-3 text-right">
                    <button type="button"
                        onclick='openEditModal(@json($student))'
                        class="text-brand-600 hover:underline text-sm"><i class="bi bi-pencil-square"></i>Edit</button>
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
     class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50 transition-opacity duration-200 opacity-0">
    <div class="modal-box bg-white rounded-xl shadow-lg w-full max-w-lg p-6 transition-all duration-200 scale-95 opacity-0">

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
                        class="bg-brand-700 text-white px-6 py-2 rounded-lg text-sm font-medium">
                    Update Student
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ADD STUDENT MODAL --}}
<div id="adviserAddStudentModal"
     class="{{ $errors->any() ? 'opacity-100' : 'hidden opacity-0' }} fixed inset-0 bg-black/40 flex items-center justify-center z-50 transition-opacity duration-200">
    <div class="modal-box {{ $errors->any() ? 'scale-100 opacity-100' : 'scale-95 opacity-0' }} bg-white rounded-xl shadow-lg w-full max-w-lg p-6 transition-all duration-200">

        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Add Student</h3>
            <button type="button" onclick="closeAdviserAddStudent()"
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

        <form method="POST" action="{{ route('adviser.students.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm text-gray-600 mb-1">LRN (12 digits)</label>
                <input type="text" name="lrn" maxlength="12" required
                       value="{{ old('lrn') }}"
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Last Name</label>
                    <input type="text" name="last_name" required
                           value="{{ old('last_name') }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">First Name</label>
                    <input type="text" name="first_name" required
                           value="{{ old('first_name') }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Middle Name</label>
                <input type="text" name="middle_name"
                       value="{{ old('middle_name') }}"
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Gender</label>
                    <select name="gender" required
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        <option value="">Select</option>
                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Birthdate</label>
                    <input type="date" name="birthdate"
                           value="{{ old('birthdate') }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>
            </div>

            <p class="text-xs text-gray-400">
                <i class="bi bi-info-circle"></i>
                Automatically added to your section: <strong>{{ $section->name ?? '—' }}</strong>
            </p>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeAdviserAddStudent()"
                        class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700">Cancel</button>
                <button type="submit"
                        class="bg-brand-700 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-brand-800">
                    Save Student
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        initTableSearch('adviserStudentSearch', '#adviserStudentTableBody .student-row');
    });
</script>
@endpush

@endsection