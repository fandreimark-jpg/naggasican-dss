<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Student Model
 *
 * Represents a Senior High School student at Naggasican NHS.
 * Each student belongs to one section and has grades per subject per term.
 */
class Student extends Model
{
    // Fields that can be mass-assigned
    protected $fillable = [
        'lrn',          // Learner Reference Number — 12 digits, unique
        'last_name',
        'first_name',
        'middle_name',
        'section_id',   // Which section the student belongs to
        'gender',       // 'male' or 'female'
        'birthdate',
    ];

    // =============================================
    // ACCESSORS
    // =============================================

    /**
     * Returns formatted full name: Last Name, First Name Middle Name
     * Accessible as $student->full_name
     */
    public function getFullNameAttribute()
    {
        return $this->last_name . ', ' . $this->first_name . ' ' . $this->middle_name;
    }

    // =============================================
    // RELATIONSHIPS
    // =============================================

    /** A student belongs to one section */
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    /** A student has many grade records (one per subject per term) */
    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    /** A student has many risk classification results */
    public function riskResults()
    {
        return $this->hasMany(RiskResult::class);
    }

    /**
     * Returns the most recent risk result
     * Uses latestOfMany() — more efficient than orderBy()->first()
     */
    public function latestRisk()
    {
        return $this->hasOne(RiskResult::class)->latestOfMany();
    }
}