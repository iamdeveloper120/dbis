<?php

namespace App\Models\Reports;

use CodeIgniter\Model;

class ReportTemplateModel extends Model
{
    protected $table = 'report_template';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'report_type',
        'template_code',
        'version_no',
        'storage_driver',
        'storage_path',
        'is_active',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    protected $useTimestamps = false;
}

