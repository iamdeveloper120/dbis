<?php

namespace App\Models\Reports;

use CodeIgniter\Model;

class ReportVersionModel extends Model
{
    protected $table = 'report_version';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'report_id',
        'version_no',
        'template_id',
        'generation_source',
        'data_signature_hash',
        'generated_at',
        'generated_by',
        'created_at',
        'created_by',
    ];

    protected $useTimestamps = false;
}

