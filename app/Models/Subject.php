<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Subject Model
 *
 * Represents a SHS subject at Naggasican NHS.
 *
 * Two types:
 * - 'core'     → applies to ALL sections of the same grade level
 *                (e.g. Effective Communication, General Mathematics)
 * - 'elective' → specific to a track and optionally a specialization
 *                (e.g. Programming — ICT only)
 *
 * Grade levels: 11 or 12 only (Senior High School).
 */
class Subject extends Model
{
    // Fields that can be mass-assigned
    protected $fillable = [
        'name',               // e.g. 'General Mathematics'
        'type',               // 'core' or 'elective'
        'grade_level',        // 11 or 12
        'track_id',           // null for core, required for elective
        'specialization_id',  // null if applies to whole track, specific if specialization-only
    ];

    // =============================================
    // RELATIONSHIPS
    // =============================================

    /**
     * Subject belongs to a track (only for elective subjects)
     * Core subjects have track_id = null
     */
    public function track()
    {
        return $this->belongsTo(Track::class);
    }

    /**
     * Subject belongs to a specialization (optional, even for electives)
     * If specialization_id is null — applies to all specializations in the track
     */
    public function specialization()
    {
        return $this->belongsTo(Specialization::class);
    }

    /** Subject has many grade records (one per student per term) */
    public function grades()
    {
        return $this->hasMany(Grade::class);
    }
}