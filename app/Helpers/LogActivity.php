<?php

namespace App\Helpers;

use App\Models\ActivityLog;

class LogActivity
{
    public static function log(
        string $action,
        string $description,
        ?string $tableName = null,
        ?int $recordId = null
    ): void {
        ActivityLog::create([
            'user_id'    => auth()->id(),
            'action'     => $action,
            'table_name' => $tableName,
            'record_id'  => $recordId,
            'description'=> $description,
            'ip_address' => request()->ip(),
        ]);
    }
}