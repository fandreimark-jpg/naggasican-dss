<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Section;
use App\Models\Grade;
use App\Models\RiskResult;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalStudents = Student::count();
        $totalSections = Section::count();
        $totalAdvisers = User::where('role', 'adviser')->count();

        $latestPerStudent = RiskResult::whereIn('id',
            RiskResult::selectRaw('MAX(id) as id')
                ->groupBy('student_id')
                ->pluck('id')
        )->get();

        $lowRisk      = $latestPerStudent->where('risk_level', 'low')->count();
        $moderateRisk = $latestPerStudent->where('risk_level', 'moderate')->count();
        $highRisk     = $latestPerStudent->where('risk_level', 'high')->count();

        $sections = Section::with([
            'students.grades',
            'students.riskResults',
            'adviser',
            'track',
            'specialization',
        ])->get();

        // Performance Trend — average per term
        $termTrends = [];
        foreach ([1, 2, 3] as $term) {
            $avg = Grade::where('grading_period', $term)->avg('grade');
            $termTrends[] = $avg ? round($avg, 2) : null;
        }

        // At-risk students — moderate + high only
        $atRiskStudents = Student::with(['section', 'riskResults'])
            ->whereHas('riskResults', function ($q) {
                $q->whereIn('risk_level', ['moderate', 'high']);
            })
            ->get()
            ->map(function ($student) {
                $latestRisk = $student->riskResults->sortByDesc('grading_period')->first();
                return [
                    'name'       => $student->last_name . ', ' . $student->first_name,
                    'section'    => $student->section->name ?? '—',
                    'average'    => $latestRisk->average_grade ?? '—',
                    'risk_level' => $latestRisk->risk_level ?? '—',
                ];
            });

        // Risk distribution per section — para sa bar chart
        $sectionRiskData = $sections->map(function ($section) {
            $low = $moderate = $high = 0;
            foreach ($section->students as $student) {
                $latest = $student->riskResults->sortByDesc('grading_period')->first();
                if ($latest) {
                    match($latest->risk_level) {
                        'low'      => $low++,
                        'moderate' => $moderate++,
                        'high'     => $high++,
                        default    => null,
                    };
                }
            }
            return [
                'section'  => $section->name,
                'low'      => $low,
                'moderate' => $moderate,
                'high'     => $high,
            ];
        });

        // Honors — base sa latest average_grade ng risk_results
        $allStudentsWithRisk = Student::with(['section', 'riskResults'])
            ->whereHas('riskResults')
            ->get()
            ->map(function ($student) {
                $latest = $student->riskResults->sortByDesc('grading_period')->first();
                return [
                    'name'    => $student->last_name . ', ' . $student->first_name,
                    'section' => $student->section->name ?? '—',
                    'average' => $latest->average_grade ?? null,
                ];
            })
            ->filter(fn($s) => $s['average'] !== null);

        $highestHonors = $allStudentsWithRisk->filter(fn($s) => $s['average'] >= 98)->values();
        $highHonors    = $allStudentsWithRisk->filter(fn($s) => $s['average'] >= 95 && $s['average'] < 98)->values();
        $withHonors    = $allStudentsWithRisk->filter(fn($s) => $s['average'] >= 90 && $s['average'] < 95)->values();

        return view('principal.dashboard', compact(
            'totalStudents', 'totalSections', 'totalAdvisers',
            'lowRisk', 'moderateRisk', 'highRisk',
            'sections', 'termTrends', 'atRiskStudents', 'sectionRiskData',
            'highestHonors', 'highHonors', 'withHonors'
        ));
    }
}