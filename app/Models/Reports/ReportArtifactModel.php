<?php

namespace App\Models\Reports;

use CodeIgniter\Model;

class ReportArtifactModel extends Model
{
    protected $table = 'report_artifact';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'report_version_id',
        'artifact_type',
        'storage_driver',
        'storage_path',
        'file_name',
        'mime_type',
        'file_size',
        'sha256',
        'created_at',
        'created_by',
    ];

    protected $useTimestamps = false;
}

