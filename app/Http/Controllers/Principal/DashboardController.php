<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Section;
use App\Models\Grade;
use App\Models\RiskResult;
use App\Models\User;

/**
 * DashboardController (Principal)
 *
 * Handles the Principal Dashboard — the main Decision Support System interface.
 * Shows risk distribution, performance trends, at-risk students,
 * academic honors, and intervention recommendations.
 */
class DashboardController extends Controller
{
    public function index()
    {
        // Summary counts for the top cards
        $totalStudents = Student::count();
        $totalSections = Section::count();
        $totalAdvisers = User::where('role', 'adviser')->count();

        // Get the latest risk result per student using MAX(id) grouping
        // This avoids duplicate counts when students have results for multiple terms
        $latestPerStudent = RiskResult::whereIn('id',
            RiskResult::selectRaw('MAX(id) as id')
                ->groupBy('student_id')
                ->pluck('id')
        )->get();

        // Count students per risk level from the latest results
        $lowRisk      = $latestPerStudent->where('risk_level', 'low')->count();
        $moderateRisk = $latestPerStudent->where('risk_level', 'moderate')->count();
        $highRisk     = $latestPerStudent->where('risk_level', 'high')->count();

        // Load sections with only the relationships needed for charts
        // Avoids loading unnecessary grade data for dashboard
        $sections = Section::with([
            'students.riskResults',
            'adviser',
            'track',
            'specialization',
        ])->get();

        // Performance Trend — average grade per term across all sections
        // Single query with GROUP BY instead of 3 separate queries
        $termTrends = [];
        $allTermAvgs = Grade::selectRaw('grading_period, AVG(grade) as avg_grade')
            ->groupBy('grading_period')
            ->pluck('avg_grade', 'grading_period');

        foreach ([1, 2, 3] as $term) {
            $termTrends[] = isset($allTermAvgs[$term])
                ? round($allTermAvgs[$term], 2)
                : null;
        }

        // At-risk students — only moderate and high risk
        // Used for the at-risk list on the dashboard
        $atRiskStudents = Student::with(['section', 'riskResults'])
            ->whereHas('riskResults', fn($q) =>
                $q->whereIn('risk_level', ['moderate', 'high'])
            )
            ->get()
            ->map(function ($student) {
                // Get the latest risk result for each at-risk student
                $latestRisk = $student->riskResults->sortByDesc('grading_period')->first();
                return [
                    'name'       => $student->last_name . ', ' . $student->first_name,
                    'section'    => $student->section->name ?? '—',
                    'average'    => $latestRisk->average_grade ?? '—',
                    'risk_level' => $latestRisk->risk_level ?? '—',
                ];
            });

        // Risk per section — used for the bar chart
        // Computed from pre-loaded data to avoid extra queries
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

        // Academic Honors — based on latest average_grade from risk_results
        // DepEd honors thresholds: Highest (98+), High (95-97), With Honors (90-94)
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