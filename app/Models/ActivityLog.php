<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ActivityLog Model
 *
 * Stores a complete audit trail of all significant actions
 * performed by users in the system.
 *
 * Recorded actions include:
 * - login / logout
 * - create_user, create_section, add_student, delete_student
 * - encode_grades
 * - submit_report, resubmit_report
 * - create_section, and other management actions
 *
 * Only the Principal can view the activity logs.
 */
class ActivityLog extends Model
{
    // Fields that can be mass-assigned
    protected $fillable = [
        'user_id',      // Who performed the action
        'action',       // Action code e.g. 'encode_grades', 'submit_report'
        'table_name',   // Which database table was affected e.g. 'grades', 'users'
        'record_id',    // ID of the specific record affected (nullable)
        'description',  // Human-readable description e.g. 'Encoded grades for Term 1'
        'ip_address',   // IP address of the user (for security audit)
    ];

    // =============================================
    // RELATIONSHIPS
    // =============================================

    /** Log belongs to the user who performed the action */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}