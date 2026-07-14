<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Section;

/**
 * ReportController (Admin)
 *
 * Shows the reports page — grade overview and risk levels per section.
 * The admin can see all submitted reports from all advisers.
 */
class ReportController extends Controller
{

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

        return view('admin.reports', compact('sections'));
    }
}