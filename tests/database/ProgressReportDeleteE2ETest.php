<?php

use App\Models\Reports\ProgressReportVersionDataModel;
use App\Models\Reports\ReportArtifactModel;
use App\Models\Reports\ReportModel;
use App\Models\Reports\ReportVersionModel;
use App\Services\Reports\ProgressReportService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

/**
 * @internal
 */
final class ProgressReportDeleteE2ETest extends CIUnitTestCase
{
    private array $createdReportIds = [];
    private array $createdVersionIds = [];
    private array $createdArtifactIds = [];
    private array $createdFiles = [];

    protected function tearDown(): void
    {
        try {
            $db = Database::connect('default');

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
        $this->ensureProgressSetup($db);

        $fixture = $this->createProgressFixture($db, 2, [2]);
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
        $this->ensureProgressSetup($db);

        $fixture = $this->createProgressFixture($db, 2, []);
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
        $this->ensureProgressSetup($db);

        $fixture = $this->createProgressFixture($db, 2, [1, 2]);
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

        $prvdCount = $db->table('progress_report_version_data')->whereIn('report_version_id', $versionIds)->countAllResults();
        $this->assertSame(0, $prvdCount);

        foreach ($artifactFiles as $file) {
            $this->assertFalse(is_file($file));
        }
    }

    private function ensureProgressSetup($db): void
    {
        if (!$db->tableExists('progress_report_version_data')) {
            $this->markTestSkipped('progress_report_version_data table not found.');
        }
    }

    private function createProgressFixture($db, int $versionCount, array $artifactVersionNos): array
    {
        $now = date('Y-m-d H:i:s');
        $suffix = $this->fixtureSuffix();
        $subjectId = random_int(700000, 799999);
        $periodStart = '2026-01-01';
        $periodEnd = '2026-01-31';
        $periodKey = 'del_' . $suffix;

        $db->table('report')->insert([
            'report_type' => 'PROGRESS',
            'subject_type' => 'LEARNER',
            'subject_id' => $subjectId,
            'period_type' => 'RANGE',
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'period_key' => $periodKey,
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

            $db->table('progress_report_version_data')->insert([
                'report_version_id' => $versionId,
                'workflow_status' => 'DRAFT',
                'is_locked' => 0,
                'manual_json' => '{}',
                'snapshot_json' => '{}',
                'section_status_json' => '{"sections":{}}',
                'created_at' => $now,
                'created_by' => 1001,
                'updated_at' => $now,
                'updated_by' => 1001,
            ]);

            if (in_array($i, $artifactVersionNos, true)) {
                $relativePath = 'reports/artifacts/progress/test/delete_' . $suffix . '_v' . $i . '.pdf';
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

    private function fixtureSuffix(): string
    {
        return substr(md5(uniqid('prdelete', true)), 0, 12);
    }

    private function createServiceForDb($db): ProgressReportService
    {
        $service = new ProgressReportService();

        $serviceDb = new ReflectionProperty($service, 'db');
        $serviceDb->setAccessible(true);
        $serviceDb->setValue($service, $db);

        $this->injectModelWithDb($service, 'reportModel', new ReportModel(), $db);
        $this->injectModelWithDb($service, 'reportVersionModel', new ReportVersionModel(), $db);
        $this->injectModelWithDb($service, 'reportArtifactModel', new ReportArtifactModel(), $db);
        $this->injectModelWithDb($service, 'progressReportVersionDataModel', new ProgressReportVersionDataModel(), $db);

        $hasTableFlag = new ReflectionProperty($service, 'hasProgressVersionDataTable');
        $hasTableFlag->setAccessible(true);
        $hasTableFlag->setValue($service, null);

        return $service;
    }

    private function injectModelWithDb(ProgressReportService $service, string $serviceProperty, object $model, $db): void
    {
        $modelDb = new ReflectionProperty($model, 'db');
        $modelDb->setAccessible(true);
        $modelDb->setValue($model, $db);

        $serviceModel = new ReflectionProperty($service, $serviceProperty);
        $serviceModel->setAccessible(true);
        $serviceModel->setValue($service, $model);
    }
}
