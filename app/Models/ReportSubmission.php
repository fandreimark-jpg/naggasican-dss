<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportSubmission extends Model
{
    protected $fillable = [
        'section_id', 'submitted_by', 'grading_period',
        'status', 'school_year', 'submitted_at', 'approved_at'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at'  => 'datetime',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function adviser()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}