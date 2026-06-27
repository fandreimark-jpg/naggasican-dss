<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = [
        'name',
        'grade_level',
        'track_id',
        'specialization_id',
        'adviser_id',
        'school_year',
    ];

    public function adviser()
    {
        return $this->belongsTo(User::class, 'adviser_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function track()
    {
        return $this->belongsTo(Track::class);
    }

    public function specialization()
    {
        return $this->belongsTo(Specialization::class);
    }

    public function reportSubmissions()
    {
        return $this->hasMany(ReportSubmission::class);
    }
}