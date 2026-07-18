@extends('layouts.app')

@section('title', 'Sections')
@section('subtitle', 'Manage sections and assign advisers')

@section('content')

<div class="bg-white rounded-xl shadow-sm mb-0">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 px-6 py-4 border-b">
        <div>
            <h2 class="text-sm font-semibold text-gray-800">All Sections</h2>
            <p class="text-xs text-gray-400">{{ $sections->count() }} total sections</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="relative">
                <input type="text" id="sectionSearch"
                    placeholder="Search sections..."
                    class="border rounded-lg pl-9 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 w-56">
                <i class="bi bi-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
            </div>
            <button type="button" onclick="openAddSectionModal()"
                class="bg-brand-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand-800 whitespace-nowrap">
                <i class="bi bi-plus-lg"></i> Add Section
            </button>
        </div>
    </div>

    <table class="w-full text-sm" id="sectionTable">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
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
            <tr class="hover:bg-gray-50 section-row">
                <td class="px-6 py-3 font-medium text-gray-800">{{ $section->name }}</td>
                <td class="px-6 py-3 text-gray-600">Grade {{ $section->grade_level }}</td>
                <td class="px-6 py-3 text-gray-600">{{ $section->track->name ?? '—' }}</td>
                <td class="px-6 py-3 text-gray-600">{{ $section->specialization->name ?? '—' }}</td>
                <td class="px-6 py-3 text-gray-600">{{ $section->school_year }}</td>
                <td class="px-6 py-3 text-gray-600">
                    @if($section->adviser)
                        {{ $section->adviser->last_name }}, {{ $section->adviser->first_name }}
                    @else
                        <span class="text-yellow-500 text-xs font-medium">Unassigned</span>
                    @endif
                </td>
                <td class="px-6 py-3 text-gray-600">{{ $section->students->count() }} students</td>
                <td class="px-6 py-3 text-right space-x-2">
                    <button type="button"
                        onclick='openEditSectionModal(@json($section->load(["track", "specialization"])))'
                        class="inline-flex items-center gap-1 text-brand-600 hover:text-brand-800 text-xs font-medium border border-brand-200 rounded px-2 py-1 hover:bg-brand-50">
                        <i class="bi bi-pencil-square"></i> Edit
                    </button>
                    <form method="POST"
                          action="{{ route('admin.sections.destroy', $section->id) }}"
                          class="inline"
                          data-confirm="Delete section {{ $section->name }}?">
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
                <td colspan="8" class="px-6 py-8 text-center text-gray-400">
                    <i class="bi bi-grid text-2xl block mb-2"></i>
                    No sections yet.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div id="noSectionResults" class="hidden px-6 py-8 text-center text-gray-400">
        <i class="bi bi-search text-2xl block mb-2"></i>
        No sections found matching your search.
    </div>
</div>

{{-- ADD / EDIT MODAL --}}
<div id="sectionModal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50 transition-opacity duration-200 opacity-0">
    <div class="modal-box bg-white rounded-xl shadow-lg w-full max-w-lg p-6 transition-all duration-200 scale-95 opacity-0">

        <div class="flex justify-between items-center mb-4">
            <h3 id="modalTitle" class="text-lg font-semibold text-gray-800">Add Section</h3>
            <button type="button" onclick="closeSectionModal()"
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

        <form id="sectionForm" method="POST" class="space-y-4"
              data-store-url="{{ route('admin.sections.store') }}"
              data-spec-url="{{ url('admin/specializations-by-track') }}">
            @csrf
            <input type="hidden" name="_method" id="sectionMethod" value="POST">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Section Name</label>
                    <input type="text" name="name" id="sectionName" required
                           placeholder="e.g. Narra"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Grade Level</label>
                    <select name="grade_level" id="sectionGrade" required
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        <option value="">— Select Grade —</option>
                        <option value="11">Grade 11</option>
                        <option value="12">Grade 12</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Track</label>
                <select name="track_id" id="sectionTrack" required
                        onchange="loadSpecializations(this.value, 'sectionSpec', document.getElementById('sectionForm').dataset.specUrl)"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                    <option value="">— Select Track —</option>
                    @foreach($tracks as $track)
                        <option value="{{ $track->id }}">{{ $track->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Specialization</label>
                <select name="specialization_id" id="sectionSpec" required
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                    <option value="">— Select Track First —</option>
                </select>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">School Year</label>
                <input type="text" name="school_year" id="sectionSchoolYear" required
                       placeholder="e.g. 2026-2027"
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Assign Adviser</label>
                <select name="adviser_id" id="sectionAdviser"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                    <option value="">— No Adviser —</option>
                    @foreach($allAdvisers as $adviser)
                        <option value="{{ $adviser->id }}"
                            data-section-id="{{ $adviser->section->id ?? '' }}">
                            {{ $adviser->last_name }}, {{ $adviser->first_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeSectionModal()"
                        class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700">Cancel</button>
                <button type="submit"
                        class="bg-brand-700 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-brand-800">
                    Save Section
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        initTableSearch('sectionSearch', '.section-row', 'noSectionResults');
    });
</script>
@endpush