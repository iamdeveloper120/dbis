<?php

namespace App\Libraries\Reports;

class ReportStorage
{
    public static function templatePath(string $reportType, int $versionNo): string
    {
        $type = strtolower($reportType);
        return WRITEPATH . "reports/templates/{$type}/v{$versionNo}/template.docx";
    }

    public static function artifactDir(
        string $reportType,
        string $subjectType,
        int $subjectId,
        string $periodKey,
        int $versionNo
    ): string {
        $type = strtolower($reportType);
        $sub = strtolower($subjectType);

        return WRITEPATH . "reports/artifacts/{$type}/{$sub}/{$subjectId}/{$periodKey}/v{$versionNo}/";
    }

    public static function ensureDir(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
    }
}
