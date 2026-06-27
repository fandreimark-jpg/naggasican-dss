<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskResult extends Model
{
    protected $fillable = [
        'student_id', 'grading_period', 'average_grade', 'risk_level', 'school_year', 'generated_at'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}