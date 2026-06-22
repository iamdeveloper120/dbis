<?php

use App\Models\ClientConfiguration\ClientModel;
use App\Models\Reports\ProgressReportVersionDataModel;
use App\Models\Reports\ReportArtifactModel;
use App\Models\Reports\ReportModel;
use App\Models\Reports\ReportVersionModel;
use App\Services\Reports\ProgressReportService;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class ProgressReportServiceCoverageDouble extends ProgressReportService
{
    protected function resolveActiveTemplate(string $reportType): ?array
    {
        return [
            'id' => 1,
            'report_type' => $reportType,
            'template_name' => 'Progress Test Template',
            'file_path' => 'tests/progress/template.html',
        ];
    }
}

final class ProgressReportFakeUploadedFile extends UploadedFile
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
            $destination = $targetPath . uniqid('progress_img_', true) . '_' . $name;
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
final class ProgressReportWorkflowCoverageE2ETest extends CIUnitTestCase
{
    private array $createdReportIds = [];
    private array $createdVersionIds = [];
    private array $createdFiles = [];

    protected function tearDown(): void
    {
        try {
            $db = Database::connect('default');
            $reportIds = array_values(array_unique(array_map('intval', $this->createdReportIds)));
            $versionIds = array_values(array_unique(array_map('intval', $this->createdVersionIds)));

            if (!empty($reportIds)) {
                $artifactRows = $db->table('report_artifact ra')
                    ->select('ra.storage_path')
                    ->join('report_version rv', 'rv.id = ra.report_version_id', 'inner')
                    ->whereIn('rv.report_id', $reportIds)
                    ->get()
                    ->getResultArray();

                foreach ($artifactRows as $row) {
                    $storagePath = trim((string) ($row['storage_path'] ?? ''));
                    if ($storagePath === '') {
                        continue;
                    }
                    $this->createdFiles[] = $this->storageToFullPath($storagePath);
                }
            }

            if (!empty($versionIds)) {
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

    public function testOverlapCheckAllowsCreateButExactPeriodIsBlocked(): void
    {
        $db = Database::connect('default');
        if (!$db->tableExists('progress_report_version_data')) {
            $this->markTestSkipped('progress_report_version_data table not found.');
        }

        $clientId = $this->pickAnyClientId($db);
        if ($clientId <= 0) {
            $this->markTestSkipped('No client found for Progress Report overlap test.');
        }

        $baseDay = random_int(1, 18);
        $exactFrom = sprintf('2098-03-%02d', $baseDay);
        $exactTo = sprintf('2098-03-%02d', $baseDay + 5);
        $overlapFrom = sprintf('2098-03-%02d', $baseDay + 3);
        $overlapTo = sprintf('2098-03-%02d', $baseDay + 10);

        $existing = $this->createDraftFixture($db, $clientId, $exactFrom, $exactTo);

        $service = $this->createServiceForDb($db);

        $exactCheck = $service->checkGenerateDraft($clientId, $exactFrom, $exactTo);
        $this->assertFalse((bool) ($exactCheck['success'] ?? true));
        $this->assertSame('EXACT_PERIOD_EXISTS', (string) ($exactCheck['code'] ?? ''));

        $overlapCheck = $service->checkGenerateDraft($clientId, $overlapFrom, $overlapTo);
        $this->assertTrue((bool) ($overlapCheck['success'] ?? false), (string) ($overlapCheck['message'] ?? 'checkGenerateDraft failed.'));
        $this->assertTrue((bool) ($overlapCheck['data']['overlap_exists'] ?? false));
        $this->assertSame($exactFrom, (string) ($overlapCheck['data']['overlap_period_from'] ?? ''));
        $this->assertSame($exactTo, (string) ($overlapCheck['data']['overlap_period_to'] ?? ''));

        $overlapCreate = $service->createDraft($clientId, $overlapFrom, $overlapTo, 1001);
        $this->assertTrue((bool) ($overlapCreate['success'] ?? false), (string) ($overlapCreate['message'] ?? 'createDraft failed.'));
        $newReportId = (int) ($overlapCreate['data']['report_id'] ?? 0);
        $newVersionId = (int) ($overlapCreate['data']['version_id'] ?? 0);
        $this->assertGreaterThan(0, $newReportId);
        $this->assertGreaterThan(0, $newVersionId);
        $this->createdReportIds[] = $newReportId;
        $this->createdVersionIds[] = $newVersionId;

        $newReport = $db->table('report')->where('id', $newReportId)->get()->getRowArray();
        $this->assertIsArray($newReport);
        $this->assertSame($overlapFrom, (string) ($newReport['period_start'] ?? ''));
        $this->assertSame($overlapTo, (string) ($newReport['period_end'] ?? ''));
        $this->assertNotSame((int) $existing['report_id'], $newReportId);

        $exactCreate = $service->createDraft($clientId, $exactFrom, $exactTo, 1001);
        $this->assertFalse((bool) ($exactCreate['success'] ?? true));
        $this->assertSame('EXACT_PERIOD_EXISTS', (string) ($exactCreate['code'] ?? ''));
    }

    public function testInstructionalImageLifecycleUploadListReplaceDelete(): void
    {
        $db = Database::connect('default');
        if (!$db->tableExists('progress_report_version_data')) {
            $this->markTestSkipped('progress_report_version_data table not found.');
        }

        $clientId = $this->pickAnyClientId($db);
        if ($clientId <= 0) {
            $this->markTestSkipped('No client found for Progress Report image lifecycle test.');
        }

        $baseDay = random_int(1, 20);
        $periodFrom = sprintf('2098-04-%02d', $baseDay);
        $periodTo = sprintf('2098-04-%02d', $baseDay + 5);
        $fixture = $this->createDraftFixture($db, $clientId, $periodFrom, $periodTo);
        $versionId = (int) $fixture['version_id'];

        $service = $this->createServiceForDb($db);

        $listBefore = $service->listInstructionalImages($versionId);
        $this->assertTrue((bool) ($listBefore['success'] ?? false), (string) ($listBefore['message'] ?? 'listInstructionalImages failed.'));
        $this->assertSame([], $listBefore['data']['images'] ?? []);

        $tmp1 = $this->createTempFile('upload_1.png', 2048);
        $tmp2 = $this->createTempFile('upload_2.png', 3072);
        $uploadResult = $service->uploadInstructionalImages(
            $versionId,
            [
                new ProgressReportFakeUploadedFile($tmp1, 'upload_1.png', 'image/png', null, UPLOAD_ERR_OK),
                new ProgressReportFakeUploadedFile($tmp2, 'upload_2.png', 'image/png', null, UPLOAD_ERR_OK),
            ],
            1001
        );

        $this->assertTrue((bool) ($uploadResult['success'] ?? false), (string) ($uploadResult['message'] ?? 'uploadInstructionalImages failed.'));
        $uploadedImages = $uploadResult['data']['images'] ?? [];
        $this->assertCount(2, $uploadedImages);

        $manualRow = $db->table('progress_report_version_data')
            ->select('manual_json')
            ->where('report_version_id', $versionId)
            ->get()
            ->getRowArray();
        $manual = json_decode((string) ($manualRow['manual_json'] ?? '{}'), true);
        $this->assertIsArray($manual);
        $this->assertCount(2, $manual['instructional_programmes_images'] ?? []);

        $listAfterUpload = $service->listInstructionalImages($versionId);
        $this->assertTrue((bool) ($listAfterUpload['success'] ?? false));
        $this->assertCount(2, $listAfterUpload['data']['images'] ?? []);

        $oldArtifactId = (int) ($uploadedImages[0]['artifact_id'] ?? 0);
        $this->assertGreaterThan(0, $oldArtifactId);
        $oldArtifactRow = $db->table('report_artifact')->where('id', $oldArtifactId)->get()->getRowArray();
        $this->assertIsArray($oldArtifactRow);
        $oldStoragePath = (string) ($oldArtifactRow['storage_path'] ?? '');
        $oldFullPath = $this->storageToFullPath($oldStoragePath);
        $this->assertTrue(is_file($oldFullPath));

        $replaceTemp = $this->createTempFile('replace.png', 4096);
        $replaceResult = $service->replaceInstructionalImage(
            $versionId,
            $oldArtifactId,
            new ProgressReportFakeUploadedFile($replaceTemp, 'replace.png', 'image/png', null, UPLOAD_ERR_OK),
            1001
        );
        $this->assertTrue((bool) ($replaceResult['success'] ?? false), (string) ($replaceResult['message'] ?? 'replaceInstructionalImage failed.'));
        $imagesAfterReplace = $replaceResult['data']['images'] ?? [];
        $this->assertCount(2, $imagesAfterReplace);
        $this->assertFalse(in_array($oldArtifactId, array_map(static fn(array $row): int => (int) ($row['artifact_id'] ?? 0), $imagesAfterReplace), true));

        $oldArtifactAfterReplace = $db->table('report_artifact')->where('id', $oldArtifactId)->get()->getRowArray();
        $this->assertNull($oldArtifactAfterReplace);
        $this->assertFalse(is_file($oldFullPath));

        $deleteArtifactId = (int) ($imagesAfterReplace[0]['artifact_id'] ?? 0);
        $this->assertGreaterThan(0, $deleteArtifactId);
        $deleteArtifactRow = $db->table('report_artifact')->where('id', $deleteArtifactId)->get()->getRowArray();
        $this->assertIsArray($deleteArtifactRow);
        $deleteStoragePath = (string) ($deleteArtifactRow['storage_path'] ?? '');
        $deleteFullPath = $this->storageToFullPath($deleteStoragePath);
        $this->assertTrue(is_file($deleteFullPath));

        $deleteResult = $service->deleteInstructionalImage($versionId, $deleteArtifactId, 1001);
        $this->assertTrue((bool) ($deleteResult['success'] ?? false), (string) ($deleteResult['message'] ?? 'deleteInstructionalImage failed.'));
        $this->assertCount(1, $deleteResult['data']['images'] ?? []);
        $this->assertSame([], $deleteResult['data']['file_delete_failures'] ?? []);

        $deletedArtifact = $db->table('report_artifact')->where('id', $deleteArtifactId)->get()->getRowArray();
        $this->assertNull($deletedArtifact);
        $this->assertFalse(is_file($deleteFullPath));
    }

    private function createServiceForDb($db): ProgressReportService
    {
        $service = new ProgressReportServiceCoverageDouble();

        $serviceDb = new ReflectionProperty($service, 'db');
        $serviceDb->setAccessible(true);
        $serviceDb->setValue($service, $db);

        $this->injectModelWithDb($service, 'clientModel', new ClientModel(), $db);
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

    private function pickAnyClientId($db): int
    {
        $row = $db->table('clients')->select('id')->orderBy('id', 'ASC')->limit(1)->get()->getRowArray();
        return is_array($row) ? (int) ($row['id'] ?? 0) : 0;
    }

    private function createDraftFixture($db, int $subjectId, string $periodFrom, string $periodTo): array
    {
        $now = date('Y-m-d H:i:s');
        $periodKey = $periodFrom . '_' . $periodTo;

        $db->table('report')->insert([
            'report_type' => 'PROGRESS',
            'subject_type' => 'LEARNER',
            'subject_id' => $subjectId,
            'period_type' => 'RANGE',
            'period_start' => $periodFrom,
            'period_end' => $periodTo,
            'period_key' => $periodKey,
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

        return [
            'report_id' => $reportId,
            'version_id' => $versionId,
        ];
    }

    private function createTempFile(string $name, int $bytes): string
    {
        $dir = rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'progress' . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $path = $dir . uniqid('progress_', true) . '_' . $name;
        file_put_contents($path, str_repeat('a', max(1, $bytes)));
        $this->createdFiles[] = $path;
        return $path;
    }

    private function storageToFullPath(string $storagePath): string
    {
        return rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($storagePath, '/'));
    }
}
