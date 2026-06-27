@extends('layouts.app')

@section('title', 'Submit Report')
@section('subtitle', $section
    ? 'Section ' . $section->name . ' — Grade ' . $section->grade_level
    : 'No section assigned')

@section('content')

@if(session('success'))
    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg text-sm">
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg text-sm">
        {{ session('error') }}
    </div>
@endif

{{-- Submission Status per Term --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    @foreach([1, 2, 3] as $period)
    @php
        $submission    = $submissions->firstWhere('grading_period', $period);
        $gradeCount    = $grades->where('grading_period', $period)->count();
        $totalExpected = $students->count() * $subjects->count();
        $isComplete    = $gradeCount >= $totalExpected && $totalExpected > 0;
    @endphp
    <div class="bg-white rounded-xl shadow-sm p-5 border-t-4
        {{ $submission ? 'border-green-500' : 'border-gray-300' }}">

        <div class="flex justify-between items-start mb-3">
            <div>
                <p class="text-sm font-semibold text-gray-700">Term {{ $period }}</p>
                <p class="text-xs text-gray-400 mt-0.5">
                    {{ $gradeCount }}/{{ $totalExpected }} grades encoded
                </p>
            </div>
            @if($submission)
                <span class="text-xs px-2 py-1 rounded-full font-medium bg-green-100 text-green-700">
                    Submitted
                </span>
            @else
                <span class="text-xs px-2 py-1 rounded-full font-medium bg-gray-100 text-gray-500">
                    Not Submitted
                </span>
            @endif
        </div>

        @if($submission)
            <p class="text-xs text-gray-400 mb-3">
                Submitted: {{ $submission->submitted_at->format('M d, Y h:i A') }}
            </p>

            {{-- ✅ Re-submit option kapag may mali sa grades --}}
            @if($isComplete)
                <div class="border-t pt-3 mt-1">
                    <p class="text-xs text-gray-400 mb-2">
                        Found an error? You can correct grades and re-submit.
                    </p>
                    <div class="flex gap-2">
                        {{-- Edit Grades link --}}
                        <a href="{{ route('adviser.grades') }}?period={{ $period }}"
                           class="flex-1 text-center border border-blue-600 text-blue-600 px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-blue-50">
                            Edit Grades
                        </a>
                        {{-- Re-submit button --}}
                        <form method="POST"
                              action="{{ route('adviser.submit.report') }}"
                              class="flex-1"
                              onsubmit="return confirm('Re-submit Term {{ $period }} report? This will update the risk classification.');">
                            @csrf
                            <input type="hidden" name="grading_period" value="{{ $period }}">
                            <input type="hidden" name="resubmit" value="1">
                            <button type="submit"
                                    class="w-full bg-yellow-500 text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-yellow-600">
                                Re-submit
                            </button>
                        </form>
                    </div>
                </div>
            @endif

        @else
            {{-- First time submission --}}
            @if($isComplete)
                <form method="POST" action="{{ route('adviser.submit.report') }}">
                    @csrf
                    <input type="hidden" name="grading_period" value="{{ $period }}">
                    <button type="submit"
                            class="w-full mt-2 bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-800">
                        Submit Term {{ $period }} Report
                    </button>
                </form>
            @else
                <p class="text-xs text-yellow-600 mt-2">
                    ⚠ Complete all grades before submitting
                    ({{ $totalExpected - $gradeCount }} remaining)
                </p>
            @endif
        @endif
    </div>
    @endforeach
</div>

{{-- Grade Summary Table --}}
<div class="bg-white rounded-xl shadow-sm">
    <div class="px-6 py-4 border-b">
        <h3 class="font-semibold text-gray-800">Grade Summary</h3>
        <p class="text-sm text-gray-500">Overview of encoded grades per student</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500">
                <tr>
                    <th class="text-left px-6 py-3">Student</th>
                    <th class="text-center px-4 py-3">Term 1</th>
                    <th class="text-center px-4 py-3">Term 2</th>
                    <th class="text-center px-4 py-3">Term 3</th>
                    <th class="text-center px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($students as $student)
                @php
                    $studentGrades = $grades->where('student_id', $student->id);
                    $avg1 = $studentGrades->where('grading_period', 1)->avg('grade');
                    $avg2 = $studentGrades->where('grading_period', 2)->avg('grade');
                    $avg3 = $studentGrades->where('grading_period', 3)->avg('grade');
                    $hasAll = $avg1 && $avg2 && $avg3;
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 font-medium text-gray-800">
                        {{ $student->last_name }}, {{ $student->first_name }}
                    </td>
                    <td class="px-4 py-3 text-center
                        {{ $avg1 && $avg1 < 75 ? 'text-red-600 font-semibold' : 'text-gray-700' }}">
                        {{ $avg1 ? number_format($avg1, 2) : '—' }}
                    </td>
                    <td class="px-4 py-3 text-center
                        {{ $avg2 && $avg2 < 75 ? 'text-red-600 font-semibold' : 'text-gray-700' }}">
                        {{ $avg2 ? number_format($avg2, 2) : '—' }}
                    </td>
                    <td class="px-4 py-3 text-center
                        {{ $avg3 && $avg3 < 75 ? 'text-red-600 font-semibold' : 'text-gray-700' }}">
                        {{ $avg3 ? number_format($avg3, 2) : '—' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($hasAll)
                            <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700">
                                Complete
                            </span>
                        @else
                            <span class="text-xs px-2 py-1 rounded-full bg-yellow-100 text-yellow-700">
                                Incomplete
                            </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-6 text-center text-gray-400">
                        No students found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection