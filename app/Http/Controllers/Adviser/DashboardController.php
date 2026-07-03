<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Grade;
use App\Models\Section;
use App\Models\ReportSubmission;

class DashboardController extends Controller
{
    public function index()
    {
        $section = Section::where('adviser_id', auth()->id())
                        ->with(['track', 'specialization'])
                        ->first();

        $totalStudents = $section
            ? Student::where('section_id', $section->id)->count()
            : 0;

        $subjects = $section
            ? $this->getSectionSubjects($section)
            : collect();

        $totalExpectedPerTerm = $totalStudents * $subjects->count();

        $term1Count = $section ? Grade::where('section_id', $section->id)
            ->where('grading_period', 1)
            ->where('school_year', $section->school_year)
            ->count() : 0;

        $term2Count = $section ? Grade::where('section_id', $section->id)
            ->where('grading_period', 2)
            ->where('school_year', $section->school_year)
            ->count() : 0;

        $term3Count = $section ? Grade::where('section_id', $section->id)
            ->where('grading_period', 3)
            ->where('school_year', $section->school_year)
            ->count() : 0;

        $totalGradesEncoded = $term1Count + $term2Count + $term3Count;

        $submissions = $section ? ReportSubmission::where('section_id', $section->id)
            ->where('school_year', $section->school_year)
            ->get()
            ->keyBy('grading_period') : collect();

        $pendingCount = 0;
        foreach ([1, 2, 3] as $term) {
            $termCount = match($term) {
                1 => $term1Count,
                2 => $term2Count,
                3 => $term3Count,
            };
            $isComplete  = $totalExpectedPerTerm > 0 && $termCount >= $totalExpectedPerTerm;
            $isSubmitted = isset($submissions[$term]);
            if ($isComplete && !$isSubmitted) {
                $pendingCount++;
            }
        }

        $students = $section
            ? Student::where('section_id', $section->id)
                ->with(['riskResults' => function ($q) use ($section) {
                    $q->where('school_year', $section->school_year)
                      ->orderBy('grading_period', 'desc');
                }])
                ->orderBy('last_name')
                ->get()
            : collect();

        return view('adviser.dashboard', compact(
            'section', 'totalStudents', 'totalGradesEncoded',
            'pendingCount', 'submissions', 'students',
            'totalExpectedPerTerm',
            'term1Count', 'term2Count', 'term3Count'
        ));
    }

    private function getSectionSubjects(Section $section)
    {
        return Subject::where('grade_level', $section->grade_level)
            ->where(function ($query) use ($section) {
                $query->where('type', 'core')
                    ->orWhere(function ($q) use ($section) {
                        $q->where('type', 'elective')
                          ->where('track_id', $section->track_id)
                          ->where(function ($q2) use ($section) {
                              $q2->whereNull('specialization_id')
                                 ->orWhere('specialization_id', $section->specialization_id);
                          });
                    });
            })
            ->get();
    }
}