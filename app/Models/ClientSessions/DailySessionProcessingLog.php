<?php

namespace App\Models\ClientSessions;

use CodeIgniter\Model;

class DailySessionProcessingLog extends Model
{
    protected $table = 'daily_session_processing_log';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'session_id',
        'processed_at',
        'processed_by',
        'process_count',
        'session_status',
        'total_targets',
        'processed_success',
        'conflicted_targets',
        'deleted_targets',
        'details',
        'session_details',
    ];
}
