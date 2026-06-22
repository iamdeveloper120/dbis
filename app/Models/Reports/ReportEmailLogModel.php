<?php

namespace App\Models\Reports;

use CodeIgniter\Model;

class ReportEmailLogModel extends Model
{
    protected $table = 'report_email_log';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'report_version_id',
        'to_email',
        'cc_email',
        'subject',
        'status',
        'provider_message_id',
        'error_message',
        'requested_by',
        'created_at',
        'sent_at',
    ];

    protected $useTimestamps = false;
}

