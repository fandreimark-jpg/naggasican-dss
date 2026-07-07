<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;

/**
 * ActivityLogController (Principal)
 *
 * Shows the system activity log — a complete audit trail
 * of all actions performed by users in the system.
 * Only the Principal can view activity logs.
 */
class ActivityLogController extends Controller
{
    /**
     * Show all activity logs ordered by most recent first.
     * Paginated at 20 per page.
     * Includes the user who performed each action.
     */
    public function index()
    {
        $logs = ActivityLog::with('user')       // eager load user to avoid N+1
            ->orderBy('created_at', 'desc')     // most recent first
            ->paginate(20);

        return view('principal.activity-logs', compact('logs'));
    }
}