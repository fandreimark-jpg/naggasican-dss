@extends('layouts.app')

@section('title', 'Sections')
@section('subtitle', 'Manage sections and assign advisers')

@section('content')

@if(session('success'))
    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
@endif

<div class="flex justify-end mb-4">
    <button type="button" onclick="openAddModal()"
        class="bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-800">
        <i class="bi bi-plus-lg"></i> Add Section
    </button>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500">
            <tr>
                <th class="text-left px-6 py-3">Section Name</th>
                <th class="text-left px-6 py-3">Grade Level</th>
                <th class="text-left px-6 py-3">Track</th>
                <th class="text-left px-6 py-3">Specialization</th>
                <th class="text-left px-6 py-3">School Year</th>
                <th class="text-left px-6 py-3">Adviser</th>
                <th class="text-left px-6 py-3">Students</th>
                <th class="px-6 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($sections as $section)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-3 font-medium text-gray-800">{{ $section->name }}</td>
                <td class="px-6 py-3 text-gray-600">Grade {{ $section->grade_level }}</td>
                <td class="px-6 py-3 text-gray-600">
                    {{ $section->track->name ?? '—' }}
                </td>
                <td class="px-6 py-3 text-gray-600">
                    {{ $section->specialization->name ?? '—' }}
                </td>
                <td class="px-6 py-3 text-gray-600">{{ $section->school_year }}</td>
                <td class="px-6 py-3 text-gray-600">
                    @if($section->adviser)
                        {{ $section->adviser->last_name }}, {{ $section->adviser->first_name }}
                    @else
                        <span class="text-yellow-500 text-xs font-medium">Unassigned</span>
                    @endif
                </td>
                <td class="px-6 py-3 text-gray-600">
                    {{ $section->students->count() }} students
                </td>
                <td class="px-6 py-3 text-right space-x-2">
                    <button type="button"
                        onclick='openEditModal(@json($section->load(["track", "specialization"])))'
                        class="text-blue-600 hover:underline text-sm">Edit</button>

                    <form method="POST"
                          action="{{ route('principal.sections.destroy', $section->id) }}"
                          class="inline"
                          onsubmit="return confirm('Delete this section?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline text-sm">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-6 py-6 text-center text-gray-400">
                    No sections yet.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ADD / EDIT MODAL --}}
<div id="sectionModal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-lg p-6">

        <div class="flex justify-between items-center mb-4">
            <h3 id="modalTitle" class="text-lg font-semibold text-gray-800">Add Section</h3>
            <button type="button" onclick="closeModal()"
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

        <form id="sectionForm" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="_method" id="sectionMethod" value="POST">

            {{-- Section Name + Grade Level --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Section Name</label>
                    <input type="text" name="name" id="sectionName" required
                           placeholder="e.g. Narra"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Grade Level</label>
                    <select name="grade_level" id="sectionGrade" required
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="">— Select Grade —</option>
                        <option value="11">Grade 11</option>
                        <option value="12">Grade 12</option>
                    </select>
                </div>
            </div>

            {{-- Track --}}
            <div>
                <label class="block text-sm text-gray-600 mb-1">Track</label>
                <select name="track_id" id="sectionTrack" required
                        onchange="loadSpecializations(this.value)"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">— Select Track —</option>
                    @foreach($tracks as $track)
                        <option value="{{ $track->id }}">{{ $track->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Specialization — dynamic via AJAX --}}
            <div>
                <label class="block text-sm text-gray-600 mb-1">Specialization</label>
                <select name="specialization_id" id="sectionSpec" required
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">— Select Track First —</option>
                </select>
            </div>

            {{-- School Year --}}
            <div>
                <label class="block text-sm text-gray-600 mb-1">School Year</label>
                <input type="text" name="school_year" id="sectionSchoolYear" required
                       placeholder="e.g. 2026-2027"
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            {{-- Adviser --}}
            <div>
                <label class="block text-sm text-gray-600 mb-1">Assign Adviser</label>
                <select name="adviser_id" id="sectionAdviser"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">— No Adviser —</option>
                    @foreach($availableAdvisers as $adviser)
                        <option value="{{ $adviser->id }}"
                            data-section-id="{{ $adviser->section->id ?? '' }}">
                            {{ $adviser->last_name }}, {{ $adviser->first_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeModal()"
                        class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700">Cancel</button>
                <button type="submit"
                        class="bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-blue-800">
                    Save Section
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const SECTION_STORE_URL = "{{ route('principal.sections.store') }}";
    const SPEC_BY_TRACK_URL = "{{ url('principal/specializations-by-track') }}";
</script>
<script src="{{ asset('js/principal/sections.js') }}"></script>
@endpush

@endsection