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

@if(!$section)
    <div class="bg-white rounded-xl shadow-sm p-8 text-center text-gray-400">
        <i class="bi bi-exclamation-circle text-2xl block mb-2"></i>
        No section assigned to your account.
    </div>
@else

<div class="bg-white rounded-xl shadow-sm overflow-x-auto">

    {{-- Header: Term Selector + Save Button --}}
    <form method="GET" id="termForm">
        <div class="flex items-center justify-between px-6 py-4 border-b gap-4">
            <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-gray-700">Term:</span>
                <div class="flex gap-2">
                    @foreach([1, 2, 3] as $t)
                    <button type="submit" name="period" value="{{ $t }}"
                        class="px-4 py-1.5 rounded-full text-sm font-medium border transition
                            {{ request('period', 1) == $t
                                ? 'bg-blue-700 text-white border-blue-700'
                                : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50' }}">
                        Term {{ $t }}
                    </button>
                    @endforeach
                </div>
            </div>
        </div>
    </form>

    {{-- Grade Table --}}
    <form method="POST" action="{{ route('adviser.grades.store') }}">
        @csrf
        <input type="hidden" name="grading_period" value="{{ request('period', 1) }}">

        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-left px-6 py-3 sticky left-0 bg-gray-50">Student</th>
                    @foreach($subjects as $subject)
                        <th class="px-4 py-3 text-center min-w-[100px]">
                            <span class="block font-medium text-gray-600">{{ $subject->name }}</span>
                            @if($subject->type === 'elective')
                                <span class="block text-xs text-orange-500 font-normal normal-case">Elective</span>
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($students as $student)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 font-medium text-gray-800 sticky left-0 bg-white whitespace-nowrap">
                        {{ $student->last_name }}, {{ $student->first_name }}
                    </td>
                    @foreach($subjects as $subject)
                        @php
                            $existing = $student->grades
                                ->where('subject_id', $subject->id)
                                ->where('grading_period', request('period', 1))
                                ->first();
                            $isFailing = $existing && $existing->grade < 75;
                        @endphp
                        <td class="px-4 py-3 text-center">
                            <input
                                type="number"
                                name="grades[{{ $student->id }}][{{ $subject->id }}]"
                                value="{{ $existing ? number_format($existing->grade, 2) : '' }}"
                                min="60" max="100" step="0.01"
                                class="w-20 border rounded-lg px-2 py-1.5 text-center text-sm
                                       focus:outline-none focus:ring-2 focus:ring-blue-400
                                       {{ $isFailing
                                            ? 'border-red-300 bg-red-50 text-red-600'
                                            : 'border-gray-200 hover:border-gray-300' }}"
                                placeholder="—"
                            >
                        </td>
                    @endforeach
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $subjects->count() + 1 }}"
                        class="px-6 py-8 text-center text-gray-400">
                        <i class="bi bi-people text-2xl block mb-2"></i>
                        No students found in this section.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Footer: Legend + Save Button --}}
        <div class="px-6 py-4 border-t flex items-center justify-between">
            <div class="flex items-center gap-4 text-xs text-gray-400">
                
            
            </div>
            <button type="submit"
                class="bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-blue-800">
                <i class="bi bi-floppy"></i> Save All Grades
            </button>
        </div>
    </form>
</div>

@endif

@endsection