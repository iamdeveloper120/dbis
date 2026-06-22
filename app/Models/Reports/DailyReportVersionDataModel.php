<?php

namespace App\Models\Reports;

use CodeIgniter\Model;

class DailyReportVersionDataModel extends Model
{
    protected $table = 'daily_report_version_data';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'report_version_id',
        'workflow_status',
        'is_locked',
        'manual_json',
        'snapshot_json',
        'section_status_json',
        'finalized_at',
        'finalized_by',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    protected $useTimestamps = false;
}
