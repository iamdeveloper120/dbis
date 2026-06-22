<?php

namespace App\Models\ClientSessions;

use CodeIgniter\Model;

class DailySessionTargetConflictResolutionLog extends Model
{
    protected $table = 'daily_session_target_conflict_resolution_log';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'session_id',
        'target_id',
        'client_id',
        'client_probe_set_id',
        'conflicted_data',
        'existing_data',
        'modifications',
        'resolved_by',
        'resolved_at'
    ];
}
