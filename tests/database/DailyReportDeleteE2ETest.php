<?php

use App\Models\Reports\ReportArtifactModel;
use App\Models\Reports\ReportModel;
use App\Models\Reports\ReportVersionModel;
use App\Models\Reports\DailyReportVersionDataModel;
use App\Services\Reports\DailyReportService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

/**
 * @internal
 */
final class DailyReportDeleteE2ETest extends CIUnitTestCase
{
    private array $createdReportIds = [];
    private array $createdVersionIds = [];
    private array $createdArtifactIds = [];
    private array $createdFiles = [];

    protected function tearDown(): void
    {
        try {
            $db = Database::connect('default');

            if (!empty($this->createdVersionIds) && $db->tableExists('daily_report_version_data')) {
                $db->table('daily_report_version_data')
                    ->whereIn('report_version_id', array_values(array_unique(array_map('intval', $this->createdVersionIds))))
                    ->delete();
            }

            if (!empty($this->createdArtifactIds)) {
                $db->table('report_artifact')
                    ->whereIn('id', array_values(array_unique(array_map('intval', $this->createdArtifactIds))))
                    ->delete();
            }

            if (!empty($this->createdVersionIds)) {
                $db->table('report_version')
                    ->whereIn('id', array_values(array_unique(array_map('intval', $this->createdVersionIds))))
                    ->delete();
            }

            if (!empty($this->createdReportIds)) {
                $db->table('report')
                    ->whereIn('id', array_values(array_unique(array_map('intval', $this->createdReportIds))))
                    ->delete();
            }
        } catch (\Throwable $e) {
            // Best-effort cleanup for test fixture data.
        }

        foreach ($this->createdFiles as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }

        parent::tearDown();
    }

    public function testDeleteLatestVersionRemovesLatestVersionAndKeepsPrevious(): void
    {
        $db = Database::connect('default');
        $this->ensureDailyVersionDataTable($db);
        $fixture = $this->createDailyFixture($db, 2, [2]);
        $latestVersionId = (int) $fixture['versions'][2];
        $previousVersionId = (int) $fixture['versions'][1];
        $reportId = (int) $fixture['report_id'];
        $artifactId = (int) $fixture['artifacts'][2]['id'];
        $artifactFile = (string) $fixture['artifacts'][2]['full_path'];

        $service = $this->createServiceForDb($db);
        $result = $service->deleteLatestVersion($latestVersionId, 1001);

        $this->assertTrue((bool) ($result['success'] ?? false), (string) ($result['message'] ?? 'deleteLatestVersion failed.'));
        $this->assertSame([], $result['data']['file_delete_failures'] ?? []);

        $deletedVersion = $db->table('report_version')->where('id', $latestVersionId)->get()->getRowArray();
        $this->assertNull($deletedVersion);

        $keptVersion = $db->table('report_version')->where('id', $previousVersionId)->get()->getRowArray();
        $this->assertIsArray($keptVersion);

        $report = $db->table('report')->where('id', $reportId)->get()->getRowArray();
        $this->assertIsArray($report);
        $this->assertSame(1, (int) ($report['latest_version_no'] ?? 0));

        $artifact = $db->table('report_artifact')->where('id', $artifactId)->get()->getRowArray();
        $this->assertNull($artifact);
        $this->assertFalse(is_file($artifactFile));
    }

    public function testDeleteLatestVersionRejectsNonLatestVersion(): void
    {
        $db = Database::connect('default');
        $this->ensureDailyVersionDataTable($db);
        $fixture = $this->createDailyFixture($db, 2, []);
        $nonLatestVersionId = (int) $fixture['versions'][1];
        $latestVersionId = (int) $fixture['versions'][2];
        $reportId = (int) $fixture['report_id'];

        $service = $this->createServiceForDb($db);
        $result = $service->deleteLatestVersion($nonLatestVersionId, 1001);

        $this->assertFalse((bool) ($result['success'] ?? true));
        $this->assertSame('NOT_LATEST', (string) ($result['code'] ?? ''));

        $report = $db->table('report')->where('id', $reportId)->get()->getRowArray();
        $this->assertIsArray($report);
        $this->assertSame(2, (int) ($report['latest_version_no'] ?? 0));

        $version1 = $db->table('report_version')->where('id', $nonLatestVersionId)->get()->getRowArray();
        $version2 = $db->table('report_version')->where('id', $latestVersionId)->get()->getRowArray();
        $this->assertIsArray($version1);
        $this->assertIsArray($version2);
    }

    public function testDeleteAllVersionsRemovesReportVersionsArtifactsAndFiles(): void
    {
        $db = Database::connect('default');
        $this->ensureDailyVersionDataTable($db);
        $fixture = $this->createDailyFixture($db, 2, [1, 2]);
        $reportId = (int) $fixture['report_id'];
        $versionIds = array_values(array_map('intval', $fixture['versions']));
        $artifactIds = array_map(static fn(array $row): int => (int) ($row['id'] ?? 0), $fixture['artifacts']);
        $artifactFiles = array_map(static fn(array $row): string => (string) ($row['full_path'] ?? ''), $fixture['artifacts']);

        $service = $this->createServiceForDb($db);
        $result = $service->deleteAllVersions($reportId, 1001);

        $this->assertTrue((bool) ($result['success'] ?? false), (string) ($result['message'] ?? 'deleteAllVersions failed.'));
        $this->assertSame([], $result['data']['file_delete_failures'] ?? []);

        $report = $db->table('report')->where('id', $reportId)->get()->getRowArray();
        $this->assertNull($report);

        $versionCount = $db->table('report_version')->whereIn('id', $versionIds)->countAllResults();
        $this->assertSame(0, $versionCount);

        $artifactCount = $db->table('report_artifact')->whereIn('id', $artifactIds)->countAllResults();
        $this->assertSame(0, $artifactCount);

        foreach ($artifactFiles as $file) {
            $this->assertFalse(is_file($file));
        }
    }

    private function createDailyFixture($db, int $versionCount, array $artifactVersionNos): array
    {
        $now = date('Y-m-d H:i:s');
        $suffix = $this->fixtureSuffix();
        $subjectId = random_int(800000, 899999);
        $reportDate = '2026-02-15';

        $db->table('report')->insert([
            'report_type' => 'DAILY',
            'subject_type' => 'LEARNER',
            'subject_id' => $subjectId,
            'period_type' => 'DAY',
            'period_start' => $reportDate,
            'period_end' => $reportDate,
            'period_key' => $reportDate . '_' . $suffix,
            'latest_version_no' => $versionCount,
            'created_at' => $now,
            'created_by' => 1001,
            'updated_at' => $now,
            'updated_by' => 1001,
        ]);
        $reportId = (int) $db->insertID();
        $this->createdReportIds[] = $reportId;

        $versions = [];
        $artifacts = [];
        for ($i = 1; $i <= $versionCount; $i++) {
            $db->table('report_version')->insert([
                'report_id' => $reportId,
                'version_no' => $i,
                'template_id' => null,
                'generation_source' => 'MANUAL',
                'generated_at' => $now,
                'generated_by' => 1001,
                'created_at' => $now,
                'created_by' => 1001,
            ]);
            $versionId = (int) $db->insertID();
            $this->createdVersionIds[] = $versionId;
            $versions[$i] = $versionId;

            if (in_array($i, $artifactVersionNos, true)) {
                $relativePath = 'reports/artifacts/daily/test/delete_' . $suffix . '_v' . $i . '.pdf';
                $fullPath = rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
                $dir = dirname($fullPath);
                if (!is_dir($dir)) {
                    mkdir($dir, 0775, true);
                }
                file_put_contents($fullPath, 'test-pdf-' . $suffix . '-v' . $i);
                $this->createdFiles[] = $fullPath;

                $db->table('report_artifact')->insert([
                    'report_version_id' => $versionId,
                    'artifact_type' => 'PDF',
                    'storage_driver' => 'LOCAL',
                    'storage_path' => $relativePath,
                    'file_name' => 'delete_' . $suffix . '_v' . $i . '.pdf',
                    'mime_type' => 'application/pdf',
                    'file_size' => filesize($fullPath) ?: null,
                    'sha256' => hash_file('sha256', $fullPath) ?: null,
                    'created_at' => $now,
                    'created_by' => 1001,
                ]);
                $artifactId = (int) $db->insertID();
                $this->createdArtifactIds[] = $artifactId;

                $artifacts[$i] = [
                    'id' => $artifactId,
                    'full_path' => $fullPath,
                    'storage_path' => $relativePath,
                ];
            }
        }

        return [
            'report_id' => $reportId,
            'versions' => $versions,
            'artifacts' => $artifacts,
        ];
    }

    private function ensureDailyVersionDataTable($db): void
    {
        $db->query(
            'CREATE TABLE IF NOT EXISTS `daily_report_version_data` (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `report_version_id` BIGINT(20) UNSIGNED NOT NULL,
                `workflow_status` VARCHAR(16) NOT NULL DEFAULT "DRAFT",
                `is_locked` TINYINT(1) NOT NULL DEFAULT 0,
                `manual_json` LONGTEXT NULL,
                `snapshot_json` LONGTEXT NULL,
                `section_status_json` LONGTEXT NULL,
                `finalized_at` DATETIME NULL,
                `finalized_by` BIGINT(20) UNSIGNED NULL,
                `created_at` DATETIME NULL,
                `created_by` BIGINT(20) UNSIGNED NULL,
                `updated_at` DATETIME NULL,
                `updated_by` BIGINT(20) UNSIGNED NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_daily_report_version_data_report_version` (`report_version_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'
        );
    }

    private function fixtureSuffix(): string
    {
        return substr(md5(uniqid('drdelete', true)), 0, 12);
    }

    private function createServiceForDb($db): DailyReportService
    {
        $service = new DailyReportService();

        $serviceDb = new ReflectionProperty($service, 'db');
        $serviceDb->setAccessible(true);
        $serviceDb->setValue($service, $db);

        $this->injectModelWithDb($service, 'reportModel', new ReportModel(), $db);
        $this->injectModelWithDb($service, 'reportVersionModel', new ReportVersionModel(), $db);
        $this->injectModelWithDb($service, 'reportArtifactModel', new ReportArtifactModel(), $db);
        $this->injectModelWithDb($service, 'dailyReportVersionDataModel', new DailyReportVersionDataModel(), $db);

        $hasTableFlag = new ReflectionProperty($service, 'hasDailyVersionDataTable');
        $hasTableFlag->setAccessible(true);
        $hasTableFlag->setValue($service, null);

        return $service;
    }

    private function injectModelWithDb(DailyReportService $service, string $serviceProperty, object $model, $db): void
    {
        $modelDb = new ReflectionProperty($model, 'db');
        $modelDb->setAccessible(true);
        $modelDb->setValue($model, $db);

        $serviceModel = new ReflectionProperty($service, $serviceProperty);
        $serviceModel->setAccessible(true);
        $serviceModel->setValue($service, $model);
    }
}

