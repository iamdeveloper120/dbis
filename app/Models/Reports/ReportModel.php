<?php

namespace App\Models\Reports;

use CodeIgniter\Model;

class ReportModel extends Model
{
    protected $table = 'report';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'report_type',
        'subject_type',
        'subject_id',
        'period_type',
        'period_start',
        'period_end',
        'period_key',
        'latest_version_no',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    protected $useTimestamps = false;
}

