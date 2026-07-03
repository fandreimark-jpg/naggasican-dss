<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\Section;

class ReportController extends Controller
{
    public function index()
    {
        $sections = Section::with([
            'adviser',
            'track',
            'specialization',
            'students.grades',
            'students.riskResults',
            'reportSubmissions',
        ])
        ->orderBy('grade_level')
        ->get();

        return view('principal.reports', compact('sections'));
    }
}