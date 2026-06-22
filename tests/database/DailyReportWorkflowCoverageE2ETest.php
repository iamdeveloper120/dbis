<?php

use App\Models\ClientConfiguration\ClientModel;
use App\Models\Reports\DailyReportQueryModel;
use App\Models\Reports\DailyReportVersionDataModel;
use App\Models\Reports\ReportArtifactModel;
use App\Models\Reports\ReportModel;
use App\Models\Reports\ReportVersionModel;
use App\Services\Reports\DailyReportService;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class DailyReportQueryModelStub extends DailyReportQueryModel
{
    public int $sessionCount = 1;
    public string $tutorNames = 'Test Tutor';
    public array $dailyNotes = [
        [
            'start_time' => '09:00 AM',
            'end_time' => '10:00 AM',
            'instructor_comments' => 'Good responding today.',
            'comments' => 'Strong requesting during play.',
        ],
    ];
    public array $programRows = [];
    public ?array $mandsSummary = [
        'total_mands' => 5,
        'variety_of_mands' => 3,
    ];
    public array $problemBehaviorSummary = [
        'frequency' => 2,
        'total_duration_seconds' => 120,
    ];
    public array $netVsDtiSummary = [
        'net_percentage' => 60,
        'dti_percentage' => 40,
    ];

    public function countSessionsForDate(int $subjectId, string $reportDate): int
    {
        return $this->sessionCount;
    }

    public function getTutorNames(int $subjectId, string $reportDate): string
    {
        return $this->tutorNames;
    }

    public function getDailySessionCommentsAndWow(int $subjectId, string $reportDate): array
    {
        return $this->dailyNotes;
    }

    public function getProcessedProgramProbeRows(int $subjectId, string $reportDate): array
    {
        return $this->programRows;
    }

    public function getMandsSummaryByDate(int $subjectId, string $reportDate): ?array
    {
        return $this->mandsSummary;
    }

    public function getProblemBehaviorSummaryByDate(int $subjectId, string $reportDate): array
    {
        return $this->problemBehaviorSummary;
    }

    public function getNetVsDtiSummaryByDate(int $subjectId, string $reportDate): array
    {
        return $this->netVsDtiSummary;
    }
}

final class DailyReportServiceCoverageDouble extends DailyReportService
{
    protected function resolveActiveTemplate(string $reportType): ?array
    {
        return [
            'id' => 1,
            'report_type' => $reportType,
            'storage_path' => 'reports/templates/daily/v1/template.html',
        ];
    }

    protected function createArtifact(
        int $subjectId,
        string $reportDate,
        int $versionNo,
        array $tokenValues,
        int $versionId,
        ?int $userId,
        string $now,
        array $template
    ): array {
        $artifactDir = WRITEPATH . 'reports/artifacts/daily/test/' . $subjectId . '/' . $reportDate . '/v' . $versionNo . '/';
        if (!is_dir($artifactDir)) {
            mkdir($artifactDir, 0775, true);
        }

        $fileName = sprintf('DailyReport_%d_%s_v%d.pdf', $subjectId, $reportDate, $versionNo);
        $fullPath = $artifactDir . $fileName;
        file_put_contents($fullPath, "fake-daily-pdf\n" . json_encode($tokenValues, JSON_UNESCAPED_UNICODE));

        $relativePath = str_replace('\\', '/', str_replace(WRITEPATH, '', $fullPath));
        $size = filesize($fullPath);
        $sha = hash_file('sha256', $fullPath);

        $this->reportArtifactModel->insert([
            'report_version_id' => $versionId,
            'artifact_type' => 'PDF',
            'storage_driver' => 'LOCAL',
            'storage_path' => $relativePath,
            'file_name' => $fileName,
            'mime_type' => 'application/pdf',
            'file_size' => $size !== false ? $size : null,
            'sha256' => $sha ?: null,
            'created_at' => $now,
            'created_by' => $userId,
        ]);

        return [
            'storage_path' => $relativePath,
            'file_name' => $fileName,
        ];
    }
}

final class DailyReportFakeUploadedFile extends UploadedFile
{
    private string $extensionForTest;
    private string $mimeTypeForTest;
    private int $sizeForTest;
    private bool $validForTest;

    public function __construct(
        string $path,
        string $originalName,
        ?string $mimeType = null,
        ?int $size = null,
        ?int $error = null,
        ?string $clientPath = null
    ) {
        $resolvedSize = $size;
        if ($resolvedSize === null) {
            $resolvedSize = is_file($path) ? (int) (filesize($path) ?: 0) : 0;
        }

        $resolvedMimeType = $mimeType ?? 'application/octet-stream';
        $resolvedError = $error ?? UPLOAD_ERR_OK;
        parent::__construct($path, $originalName, $resolvedMimeType, $resolvedSize, $resolvedError, $clientPath);
        $this->extensionForTest = strtolower(trim((string) pathinfo($originalName, PATHINFO_EXTENSION)));
        $this->mimeTypeForTest = strtolower(trim((string) $resolvedMimeType));
        $this->sizeForTest = (int) $resolvedSize;
        $this->validForTest = $resolvedError === UPLOAD_ERR_OK;
    }

    public function isValid(): bool
    {
        return $this->validForTest && is_file($this->getPathname()) && !$this->hasMoved;
    }

    public function getClientExtension(): string
    {
        return $this->extensionForTest;
    }

    public function getExtension(): string
    {
        return $this->extensionForTest;
    }

    public function getMimeType(): string
    {
        return $this->mimeTypeForTest;
    }

    public function getSize(): int
    {
        return $this->sizeForTest;
    }

    public function getErrorString(): string
    {
        return $this->validForTest ? '' : 'Invalid test upload.';
    }

    public function move(string $targetPath, ?string $name = null, bool $overwrite = false)
    {
        if ($this->hasMoved) {
            throw new RuntimeException('File already moved.');
        }

        if (!$this->isValid()) {
            throw new RuntimeException('Invalid file.');
        }

        $targetPath = rtrim($targetPath, '/\\') . DIRECTORY_SEPARATOR;
        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0775, true);
        }

        $name = $name ?? $this->getName();
        $destination = $targetPath . $name;
        if (!$overwrite && is_file($destination)) {
            $destination = $targetPath . uniqid('daily_img_', true) . '_' . $name;
        }

        if (!copy($this->getPathname(), $destination)) {
            throw new RuntimeException('Failed to move test file.');
        }

        $this->hasMoved = true;
        return true;
    }
}

/**
 * @internal
 */
final class DailyReportWorkflowCoverageE2ETest extends CIUnitTestCase
{
    private array $createdReportIds = [];
    private array $createdVersionIds = [];
    private array $createdFiles = [];

    protected function tearDown(): void
    {
        try {
            $db = Database::connect('default');
            $versionIds = array_values(array_unique(array_map('intval', $this->createdVersionIds)));
            $reportIds = array_values(array_unique(array_map('intval', $this->createdReportIds)));

            if (!empty($versionIds)) {
                $artifactRows = $db->table('report_artifact')
                    ->select('storage_path')
                    ->whereIn('report_version_id', $versionIds)
                    ->get()
                    ->getResultArray();

                foreach ($artifactRows as $row) {
                    $storagePath = trim((string) ($row['storage_path'] ?? ''));
                    if ($storagePath !== '') {
                        $this->createdFiles[] = $this->storageToFullPath($storagePath);
                    }
                }

                if ($db->tableExists('daily_report_version_data')) {
                    $db->table('daily_report_version_data')->whereIn('report_version_id', $versionIds)->delete();
                }
                $db->table('report_artifact')->whereIn('report_version_id', $versionIds)->delete();
                $db->table('report_version')->whereIn('id', $versionIds)->delete();
            }

            if (!empty($reportIds)) {
                $db->table('report')->whereIn('id', $reportIds)->delete();
            }
        } catch (\Throwable $e) {
            // Best-effort cleanup for test data.
        }

        foreach (array_unique($this->createdFiles) as $file) {
            if (is_string($file) && $file !== '' && is_file($file)) {
                @unlink($file);
            }
        }

        parent::tearDown();
    }

    public function testCreateDraftUsesExistingDailyReportAndAddsNewVersion(): void
    {
        $db = Database::connect('default');
        $this->ensureDailyVersionDataTable($db);

        $clientId = $this->pickAnyClientId($db);
        if ($clientId <= 0) {
            $this->markTestSkipped('No client found for Daily Report draft test.');
        }

        $reportDate = '2098-08-' . str_pad((string) random_int(10, 20), 2, '0', STR_PAD_LEFT);
        $legacy = $this->createDailyFixture($db, $clientId, $reportDate, false, 'FINAL', true);

        $service = $this->createServiceForDb($db);
        $result = $service->createDraft($clientId, $reportDate, 1001);

        $this->assertTrue((bool) ($result['success'] ?? false), (string) ($result['message'] ?? 'createDraft failed.'));
        $this->assertSame((int) $legacy['report_id'], (int) ($result['data']['report_id'] ?? 0));
        $this->assertSame(2, (int) ($result['data']['version_no'] ?? 0));

        $newVersionId = (int) ($result['data']['version_id'] ?? 0);
        $this->assertGreaterThan(0, $newVersionId);
        $this->createdVersionIds[] = $newVersionId;

        $dailyData = $db->table('daily_report_version_data')
            ->where('report_version_id', $newVersionId)
            ->get()
            ->getRowArray();
        $this->assertIsArray($dailyData);
        $this->assertSame('DRAFT', (string) ($dailyData['workflow_status'] ?? ''));

        $report = $db->table('report')->where('id', $legacy['report_id'])->get()->getRowArray();
        $this->assertIsArray($report);
        $this->assertSame(2, (int) ($report['latest_version_no'] ?? 0));
    }

    public function testCheckGenerateBlocksWhenActiveDraftExists(): void
    {
        $db = Database::connect('default');
        $this->ensureDailyVersionDataTable($db);

        $clientId = $this->pickAnyClientId($db);
        if ($clientId <= 0) {
            $this->markTestSkipped('No client found for Daily Report active draft test.');
        }

        $activeDate = '2098-09-' . str_pad((string) random_int(10, 20), 2, '0', STR_PAD_LEFT);
        $checkDate = '2098-09-' . str_pad((string) random_int(21, 25), 2, '0', STR_PAD_LEFT);
        $active = $this->createDailyFixture($db, $clientId, $activeDate, true, 'DRAFT', false);

        $service = $this->createServiceForDb($db);
        $result = $service->checkGenerateDraft($clientId, $checkDate);

        $this->assertFalse((bool) ($result['success'] ?? true));
        $this->assertSame('ACTIVE_DRAFT_EXISTS', (string) ($result['code'] ?? ''));
        $this->assertSame((int) $active['report_id'], (int) ($result['data']['report_id'] ?? 0));
        $this->assertSame((int) $active['version_id'], (int) ($result['data']['version_id'] ?? 0));
    }

    public function testPullUploadSaveAndFinalizeDraftUsesStoredSnapshot(): void
    {
        $db = Database::connect('default');
        $this->ensureDailyVersionDataTable($db);

        $clientId = $this->pickAnyClientId($db);
        if ($clientId <= 0) {
            $this->markTestSkipped('No client found for Daily Report finalize test.');
        }

        $reportDate = '2098-10-' . str_pad((string) random_int(10, 20), 2, '0', STR_PAD_LEFT);
        $fixture = $this->createDailyFixture($db, $clientId, $reportDate, true, 'DRAFT', false);
        $versionId = (int) $fixture['version_id'];

        $queryStub = new DailyReportQueryModelStub();
        $service = $this->createServiceForDb($db, $queryStub);

        $pull = $service->pullSectionData($versionId, 'daily_content', 1001);
        $this->assertTrue((bool) ($pull['success'] ?? false), (string) ($pull['message'] ?? 'pullSectionData failed.'));

        $rowAfterPull = $db->table('daily_report_version_data')
            ->where('report_version_id', $versionId)
            ->get()
            ->getRowArray();
        $this->assertIsArray($rowAfterPull);
        $snapshot = json_decode((string) ($rowAfterPull['snapshot_json'] ?? '{}'), true);
        $this->assertIsArray($snapshot);
        $this->assertArrayHasKey('daily_content', $snapshot['sections'] ?? []);

        $tmpUpload = $this->createTempFile('daily_upload.png', 2048);
        $upload = $service->uploadDailyImages(
            $versionId,
            [new DailyReportFakeUploadedFile($tmpUpload, 'daily_upload.png', 'image/png', null, UPLOAD_ERR_OK)],
            1001
        );
        $this->assertTrue((bool) ($upload['success'] ?? false), (string) ($upload['message'] ?? 'uploadDailyImages failed.'));
        $this->assertCount(1, $upload['data']['images'] ?? []);

        $save = $service->saveDraft($versionId, ['daily_images' => $upload['data']['images'] ?? []], 1001);
        $this->assertTrue((bool) ($save['success'] ?? false), (string) ($save['message'] ?? 'saveDraft failed.'));

        $finalize = $service->finalizeDraft($versionId, 1001);
        $this->assertTrue((bool) ($finalize['success'] ?? false), (string) ($finalize['message'] ?? 'finalizeDraft failed.'));

        $dailyData = $db->table('daily_report_version_data')
            ->where('report_version_id', $versionId)
            ->get()
            ->getRowArray();
        $this->assertIsArray($dailyData);
        $this->assertSame('FINAL', (string) ($dailyData['workflow_status'] ?? ''));
        $this->assertSame(1, (int) ($dailyData['is_locked'] ?? 0));

        $manual = json_decode((string) ($dailyData['manual_json'] ?? '{}'), true);
        $this->assertIsArray($manual);
        $this->assertCount(1, $manual['daily_images'] ?? []);

        $artifact = $db->table('report_artifact')
            ->where('report_version_id', $versionId)
            ->where('artifact_type', 'PDF')
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();
        $this->assertIsArray($artifact);
        $fullPath = $this->storageToFullPath((string) ($artifact['storage_path'] ?? ''));
        $this->assertTrue(is_file($fullPath));

        $payload = file_get_contents($fullPath);
        $this->assertNotFalse($payload);
        $payload = (string) $payload;

        $payloadParts = explode("\n", $payload, 2);
        $this->assertCount(2, $payloadParts);
        $this->assertSame('fake-daily-pdf', $payloadParts[0]);

        $tokenValues = json_decode($payloadParts[1], true);
        $this->assertIsArray($tokenValues);
        $uploadedImagesHtml = (string) ($tokenValues['uploaded_images_html'] ?? '');
        $this->assertNotSame('', $uploadedImagesHtml);
        $this->assertStringNotContainsString('Upload Images', $uploadedImagesHtml);
        $this->assertStringNotContainsString('daily-image-caption', $uploadedImagesHtml);
    }

    public function testRegenerateDraftFromLegacyFinalCreatesNewDraftVersion(): void
    {
        $db = Database::connect('default');
        $this->ensureDailyVersionDataTable($db);

        $clientId = $this->pickAnyClientId($db);
        if ($clientId <= 0) {
            $this->markTestSkipped('No client found for Daily Report regenerate test.');
        }

        $reportDate = '2098-11-' . str_pad((string) random_int(10, 20), 2, '0', STR_PAD_LEFT);
        $legacy = $this->createDailyFixture($db, $clientId, $reportDate, false, 'FINAL', true);

        $service = $this->createServiceForDb($db);
        $result = $service->regenerateDraft((int) $legacy['version_id'], 1001);

        $this->assertTrue((bool) ($result['success'] ?? false), (string) ($result['message'] ?? 'regenerateDraft failed.'));
        $this->assertSame((int) $legacy['report_id'], (int) ($result['data']['report_id'] ?? 0));
        $this->assertSame(2, (int) ($result['data']['version_no'] ?? 0));

        $newVersionId = (int) ($result['data']['version_id'] ?? 0);
        $this->assertGreaterThan(0, $newVersionId);
        $this->createdVersionIds[] = $newVersionId;

        $dailyData = $db->table('daily_report_version_data')
            ->where('report_version_id', $newVersionId)
            ->get()
            ->getRowArray();
        $this->assertIsArray($dailyData);
        $this->assertSame('DRAFT', (string) ($dailyData['workflow_status'] ?? ''));
    }

    public function testDeleteAllRemovesDailyVersionDataArtifactsAndFiles(): void
    {
        $db = Database::connect('default');
        $this->ensureDailyVersionDataTable($db);

        $clientId = $this->pickAnyClientId($db);
        if ($clientId <= 0) {
            $this->markTestSkipped('No client found for Daily Report delete-all test.');
        }

        $reportDate = '2098-12-' . str_pad((string) random_int(10, 20), 2, '0', STR_PAD_LEFT);
        $fixture = $this->createDailyFixture($db, $clientId, $reportDate, true, 'DRAFT', true);
        $reportId = (int) $fixture['report_id'];
        $versionId = (int) $fixture['version_id'];

        $imagePath = $this->createArtifactFile($db, $versionId, 'DAILY_IMAGE', 'daily_image_' . uniqid() . '.png', 'image/png', 'image-bytes');
        $pdfPath = $fixture['pdf_full_path'] ?? '';

        $service = $this->createServiceForDb($db);
        $result = $service->deleteAllVersions($reportId, 1001);

        $this->assertTrue((bool) ($result['success'] ?? false), (string) ($result['message'] ?? 'deleteAllVersions failed.'));
        $this->assertSame([], $result['data']['file_delete_failures'] ?? []);

        $report = $db->table('report')->where('id', $reportId)->get()->getRowArray();
        $this->assertNull($report);

        $version = $db->table('report_version')->where('id', $versionId)->get()->getRowArray();
        $this->assertNull($version);

        $dailyData = $db->table('daily_report_version_data')->where('report_version_id', $versionId)->get()->getRowArray();
        $this->assertNull($dailyData);

        $this->assertFalse(is_file($imagePath));
        if (is_string($pdfPath) && $pdfPath !== '') {
            $this->assertFalse(is_file($pdfPath));
        }
    }

    private function createServiceForDb($db, ?DailyReportQueryModelStub $queryModel = null): DailyReportService
    {
        $service = new DailyReportServiceCoverageDouble();

        $serviceDb = new ReflectionProperty($service, 'db');
        $serviceDb->setAccessible(true);
        $serviceDb->setValue($service, $db);

        $this->injectModelWithDb($service, 'clientModel', new ClientModel(), $db);
        $this->injectModelWithDb($service, 'dailyReportQueryModel', $queryModel ?? new DailyReportQueryModelStub(), $db);
        $this->injectModelWithDb($service, 'dailyReportVersionDataModel', new DailyReportVersionDataModel(), $db);
        $this->injectModelWithDb($service, 'reportModel', new ReportModel(), $db);
        $this->injectModelWithDb($service, 'reportVersionModel', new ReportVersionModel(), $db);
        $this->injectModelWithDb($service, 'reportArtifactModel', new ReportArtifactModel(), $db);

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

    private function pickAnyClientId($db): int
    {
        $row = $db->table('clients')->select('id')->orderBy('id', 'ASC')->limit(1)->get()->getRowArray();
        return is_array($row) ? (int) ($row['id'] ?? 0) : 0;
    }

    private function createDailyFixture($db, int $subjectId, string $reportDate, bool $insertVersionData, string $status, bool $createPdf): array
    {
        $now = date('Y-m-d H:i:s');

        $db->table('report')->insert([
            'report_type' => 'DAILY',
            'subject_type' => 'LEARNER',
            'subject_id' => $subjectId,
            'period_type' => 'DAY',
            'period_start' => $reportDate,
            'period_end' => $reportDate,
            'period_key' => $reportDate,
            'latest_version_no' => 1,
            'created_at' => $now,
            'created_by' => 1001,
            'updated_at' => $now,
            'updated_by' => 1001,
        ]);
        $reportId = (int) $db->insertID();
        $this->createdReportIds[] = $reportId;

        $db->table('report_version')->insert([
            'report_id' => $reportId,
            'version_no' => 1,
            'template_id' => null,
            'generation_source' => 'MANUAL',
            'generated_at' => $now,
            'generated_by' => 1001,
            'created_at' => $now,
            'created_by' => 1001,
        ]);
        $versionId = (int) $db->insertID();
        $this->createdVersionIds[] = $versionId;

        if ($insertVersionData) {
            $db->table('daily_report_version_data')->insert([
                'report_version_id' => $versionId,
                'workflow_status' => $status,
                'is_locked' => strtoupper($status) === 'FINAL' ? 1 : 0,
                'manual_json' => '{}',
                'snapshot_json' => '{}',
                'section_status_json' => '{"sections":{}}',
                'finalized_at' => strtoupper($status) === 'FINAL' ? $now : null,
                'finalized_by' => strtoupper($status) === 'FINAL' ? 1001 : null,
                'created_at' => $now,
                'created_by' => 1001,
                'updated_at' => $now,
                'updated_by' => 1001,
            ]);
        }

        $pdfFullPath = '';
        if ($createPdf) {
            $pdfFullPath = $this->createArtifactFile(
                $db,
                $versionId,
                'PDF',
                'DailyReport_' . $subjectId . '_' . $reportDate . '_v1.pdf',
                'application/pdf',
                'legacy-pdf'
            );
        }

        return [
            'report_id' => $reportId,
            'version_id' => $versionId,
            'pdf_full_path' => $pdfFullPath,
        ];
    }

    private function createArtifactFile($db, int $versionId, string $artifactType, string $fileName, string $mimeType, string $content): string
    {
        $relativePath = 'reports/artifacts/daily/test/' . uniqid('daily_', true) . '_' . $fileName;
        $fullPath = rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        file_put_contents($fullPath, $content);
        $this->createdFiles[] = $fullPath;

        $db->table('report_artifact')->insert([
            'report_version_id' => $versionId,
            'artifact_type' => $artifactType,
            'storage_driver' => 'LOCAL',
            'storage_path' => $relativePath,
            'file_name' => $fileName,
            'mime_type' => $mimeType,
            'file_size' => filesize($fullPath) ?: null,
            'sha256' => hash_file('sha256', $fullPath) ?: null,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => 1001,
        ]);

        return $fullPath;
    }

    private function createTempFile(string $name, int $bytes): string
    {
        $dir = rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'daily' . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $path = $dir . uniqid('daily_', true) . '_' . $name;
        file_put_contents($path, str_repeat('a', max(1, $bytes)));
        $this->createdFiles[] = $path;
        return $path;
    }

    private function storageToFullPath(string $storagePath): string
    {
        return rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($storagePath, '/'));
    }
}
