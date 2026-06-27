@extends('layouts.app')

@section('title', 'Encode Grades')
@section('subtitle', $section
    ? 'Section ' . $section->name . ' — Grade ' . $section->grade_level .
      ' | ' . ($section->track->name ?? '') .
      ' — ' . ($section->specialization->name ?? '')
    : 'No section assigned')

@section('content')

@if(session('success'))
    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif

{{-- Filter Form --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <form method="GET" class="flex gap-4 items-end">
        <div>
            <label class="block text-sm text-gray-600 mb-1">Term</label>
            <select name="period" class="border rounded-lg px-3 py-2 text-sm">
                <option value="1" {{ request('period', 1) == 1 ? 'selected' : '' }}>Term 1</option>
                <option value="2" {{ request('period') == 2 ? 'selected' : '' }}>Term 2</option>
                <option value="3" {{ request('period') == 3 ? 'selected' : '' }}>Term 3</option>
            </select>
        </div>
        <button type="submit"
            class="bg-blue-700 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-800">
            Load
        </button>
    </form>
</div>

{{-- Grade Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-x-auto">
    <form method="POST" action="{{ route('adviser.grades.store') }}">
        @csrf
        <input type="hidden" name="grading_period" value="{{ request('period', 1) }}">

        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="text-left px-6 py-3">Student</th>
                    @foreach($subjects as $subject)
                        <th class="px-4 py-3 text-center">
                            {{ $subject->name }}
                            @if($subject->type === 'elective')
                                <span class="block text-xs text-orange-500 font-normal">Elective</span>
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($students as $student)
                <tr>
                    <td class="px-6 py-3 font-medium text-gray-800">
                        {{ $student->last_name }}, {{ $student->first_name }}
                    </td>
                    @foreach($subjects as $subject)
                        @php
                            $existing = $student->grades
                                ->where('subject_id', $subject->id)
                                ->where('grading_period', request('period', 1))
                                ->first();
                        @endphp
                        <td class="px-4 py-3 text-center">
                            <input
                                type="number"
                                name="grades[{{ $student->id }}][{{ $subject->id }}]"
                                value="{{ $existing ? $existing->grade : '' }}"
                                min="60" max="100" step="0.01"
                                class="w-20 border rounded px-2 py-1 text-center text-sm
                                       focus:outline-none focus:ring-2 focus:ring-blue-400
                                       {{ $existing && $existing->grade < 75 ? 'border-red-400 bg-red-50' : '' }}"
                                placeholder="—"
                            >
                        </td>
                    @endforeach
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $subjects->count() + 1 }}"
                        class="px-6 py-6 text-center text-gray-400">
                        No students found in this section.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4 border-t flex justify-end">
            <button type="submit"
                class="bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-blue-800">
                Save All Grades
            </button>
        </div>
    </form>
</div>

@endsection