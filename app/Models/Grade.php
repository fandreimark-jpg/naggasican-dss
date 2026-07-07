<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Grade Model
 *
 * Stores the grade of a student for a specific subject in a specific term.
 * One record = one student + one subject + one grading period.
 *
 * DepEd SHS Grading Periods:
 * - grading_period 1 = First Term
 * - grading_period 2 = Second Term
 * - grading_period 3 = Third Term
 */
class Grade extends Model
{
    // Fields that can be mass-assigned
    protected $fillable = [
        'student_id',       // Which student
        'subject_id',       // Which subject
        'section_id',       // Which section (for faster querying)
        'encoded_by',       // User ID of the adviser who encoded this grade
        'grading_period',   // 1, 2, or 3
        'grade',            // Numeric grade (60.00 - 100.00)
        'school_year',      // e.g. '2026-2027'
    ];

    // =============================================
    // RELATIONSHIPS
    // =============================================

    /** Grade belongs to a student */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /** Grade belongs to a subject */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}