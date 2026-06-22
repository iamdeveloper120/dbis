<?php

namespace App\Services\Reports;

use App\Libraries\Reports\ReportEmailStatus;
use App\Models\Reports\ReportEmailLogModel;
use App\Models\Reports\ReportVersionModel;

class DailyReportEmailService
{
    protected ReportVersionModel $reportVersionModel;
    protected ReportEmailLogModel $reportEmailLogModel;

    public function __construct()
    {
        $this->reportVersionModel = new ReportVersionModel();
        $this->reportEmailLogModel = new ReportEmailLogModel();
    }

    public function send(int $versionId, string $toEmail, ?string $ccEmail, ?int $requestedBy): array
    {
        $version = $this->reportVersionModel->find($versionId);
        if (!$version) {
            return ['success' => false, 'message' => 'Report version not found.'];
        }

        $now = date('Y-m-d H:i:s');

        $this->reportEmailLogModel->insert([
            'report_version_id' => $versionId,
            'to_email' => $toEmail,
            'cc_email' => $ccEmail !== '' ? $ccEmail : null,
            'subject' => 'Daily Report',
            'status' => ReportEmailStatus::PENDING,
            'requested_by' => $requestedBy,
            'created_at' => $now,
            'sent_at' => null,
        ]);

        return [
            'success' => true,
            'data' => [
                'version_id' => $versionId,
                'to_email' => $toEmail,
            ],
        ];
    }
}
