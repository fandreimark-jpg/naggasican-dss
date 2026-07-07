<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\Section;

/**
 * ReportController (Principal)
 *
 * Shows the reports page — grade overview and risk levels per section.
 * The principal can see all submitted reports from all advisers.
 */
class ReportController extends Controller
{
    /**
     * Show all sections with their submission status and student grades.
     * Loads all related data using eager loading to avoid N+1 queries.
     */
    public function index()
    {
        $sections = Section::with([
            'adviser',
            'track',
            'specialization',
            'students.grades',       // all grades per student
            'students.riskResults',  // risk classification results per student
            'reportSubmissions',     // which terms have been submitted
        ])
        ->orderBy('grade_level')
        ->get();

        return view('principal.reports', compact('sections'));
    }
}