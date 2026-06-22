<?php

namespace App\Services\Reports;

use App\Libraries\Reports\DailyReportRenderModel;
use App\Libraries\Reports\DailyReportTokenMap;
use App\Libraries\Reports\HtmlTemplateRenderer;
use App\Models\ClientConfiguration\ClientModel;
use App\Models\Reports\DailyReportQueryModel;
use App\Models\Reports\DailyReportVersionDataModel;
use App\Models\Reports\ReportArtifactModel;
use App\Models\Reports\ReportModel;
use App\Models\Reports\ReportTemplateModel;
use App\Models\Reports\ReportVersionModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use RuntimeException;

class DailyReportService
{
    private const DAILY_CONTENT_SECTION_KEY = 'daily_content';
    private const DAILY_IMAGE_ARTIFACT_TYPE = 'DAILY_IMAGE';
    private const DEFAULT_DAILY_IMAGE_MAX_SIZE_MB = 1;
    private const DEFAULT_DAILY_IMAGE_MAX_COUNT = 4;
    private const ABSOLUTE_DAILY_IMAGE_MAX_SIZE_MB = 10;
    private const ABSOLUTE_DAILY_IMAGE_MAX_COUNT = 20;
    private const DAILY_IMAGE_ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png'];
    private const DAILY_IMAGE_ALLOWED_MIME_TYPES = ['image/jpeg', 'image/jpg', 'image/pjpeg', 'image/png'];

    protected $db;
    protected ClientModel $clientModel;
    protected DailyReportQueryModel $dailyReportQueryModel;
    protected DailyReportVersionDataModel $dailyReportVersionDataModel;
    protected ReportModel $reportModel;
    protected ReportVersionModel $reportVersionModel;
    protected ReportArtifactModel $reportArtifactModel;
    protected ReportTemplateModel $reportTemplateModel;
    protected HtmlTemplateRenderer $htmlTemplateRenderer;
    protected HtmlToPdfConverter $htmlToPdfConverter;
    protected ?bool $hasDailyVersionDataTable = null;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->clientModel = new ClientModel();
        $this->dailyReportQueryModel = new DailyReportQueryModel();
        $this->dailyReportVersionDataModel = new DailyReportVersionDataModel();
        $this->reportModel = new ReportModel();
        $this->reportVersionModel = new ReportVersionModel();
        $this->reportArtifactModel = new ReportArtifactModel();
        $this->reportTemplateModel = new ReportTemplateModel();
        $this->htmlTemplateRenderer = new HtmlTemplateRenderer();
        $this->htmlToPdfConverter = new HtmlToPdfConverter();
    }

    public function checkGenerateDraft(int $subjectId, string $reportDate): array
    {
        $setup = $this->validateDailyDataSetup();
        if (!$setup['success']) {
            return $setup;
        }
        if ($subjectId <= 0 || !$this->subjectExists($subjectId)) {
            return ['success' => false, 'code' => 'VALIDATION_ERROR', 'message' => 'Invalid client selected.', 'data' => []];
        }
        if ($this->resolveActiveTemplate('DAILY') === null) {
            return ['success' => false, 'code' => 'TEMPLATE_SETUP_REQUIRED', 'message' => 'Daily template is missing. Add active DAILY template in report_template first.', 'data' => []];
        }

        $sessionCount = $this->dailyReportQueryModel->countSessionsForDate($subjectId, $reportDate);
        if ($sessionCount <= 0) {
            return [
                'success' => false,
                'code' => 'NO_SESSION',
                'message' => 'No session exists for selected date.',
                'data' => ['session_count' => 0],
            ];
        }

        $activeDraft = $this->findActiveDraft($subjectId);
        if ($activeDraft) {
            return [
                'success' => false,
                'code' => 'ACTIVE_DRAFT_EXISTS',
                'message' => 'An active Daily Report draft already exists for this learner.',
                'data' => [
                    'report_id' => (int) ($activeDraft['report_id'] ?? 0),
                    'version_id' => (int) ($activeDraft['version_id'] ?? 0),
                    'version_no' => (int) ($activeDraft['version_no'] ?? 0),
                    'report_date' => (string) ($activeDraft['report_date'] ?? ''),
                ],
            ];
        }

        $report = $this->findReportBySubjectAndDate($subjectId, $reportDate, false);

        return [
            'success' => true,
            'code' => 'OK',
            'message' => 'Check completed.',
            'data' => [
                'subject_id' => $subjectId,
                'report_date' => $reportDate,
                'session_count' => $sessionCount,
                'report_exists' => $report !== null,
                'latest_version_no' => $report ? (int) ($report['latest_version_no'] ?? 0) : 0,
                'report_id' => $report ? (int) ($report['id'] ?? 0) : null,
            ],
        ];
    }

    public function generate(int $subjectId, string $reportDate, ?int $userId): array
    {
        return $this->createDraft($subjectId, $reportDate, $userId);
    }

    public function createDraft(int $subjectId, string $reportDate, ?int $userId): array
    {
        $setup = $this->validateDailyDataSetup();
        if (!$setup['success']) {
            return $setup;
        }
        if ($subjectId <= 0 || !$this->subjectExists($subjectId)) {
            return ['success' => false, 'code' => 'VALIDATION_ERROR', 'message' => 'Invalid client selected.', 'data' => []];
        }

        $template = $this->resolveActiveTemplate('DAILY');
        if ($template === null) {
            return ['success' => false, 'code' => 'TEMPLATE_SETUP_REQUIRED', 'message' => 'Daily template is missing. Add active DAILY template in report_template first.', 'data' => []];
        }

        $sessionCount = $this->dailyReportQueryModel->countSessionsForDate($subjectId, $reportDate);
        if ($sessionCount <= 0) {
            return ['success' => false, 'code' => 'NO_SESSION', 'message' => 'No session exists for selected date.', 'data' => []];
        }

        $now = date('Y-m-d H:i:s');
        $this->db->transBegin();
        try {
            $this->lockClientRow($subjectId);

            $activeDraft = $this->findActiveDraft($subjectId);
            if ($activeDraft) {
                $this->db->transRollback();
                return [
                    'success' => false,
                    'code' => 'ACTIVE_DRAFT_EXISTS',
                    'message' => 'An active Daily Report draft already exists for this learner.',
                    'data' => [
                        'report_id' => (int) ($activeDraft['report_id'] ?? 0),
                        'version_id' => (int) ($activeDraft['version_id'] ?? 0),
                        'version_no' => (int) ($activeDraft['version_no'] ?? 0),
                        'report_date' => (string) ($activeDraft['report_date'] ?? ''),
                    ],
                ];
            }

            $report = $this->findReportBySubjectAndDate($subjectId, $reportDate, true);
            if (!$report) {
                $this->reportModel->insert([
                    'report_type' => 'DAILY',
                    'subject_type' => 'LEARNER',
                    'subject_id' => $subjectId,
                    'period_type' => 'DAY',
                    'period_start' => $reportDate,
                    'period_end' => $reportDate,
                    'period_key' => $reportDate,
                    'latest_version_no' => 0,
                    'created_at' => $now,
                    'created_by' => $userId,
                    'updated_at' => $now,
                    'updated_by' => $userId,
                ]);
                $reportId = (int) $this->reportModel->getInsertID();
                $latestVersionNo = 0;
            } else {
                $reportId = (int) ($report['id'] ?? 0);
                $latestVersionNo = (int) ($report['latest_version_no'] ?? 0);
            }

            $newVersionNo = $latestVersionNo + 1;
            $this->reportVersionModel->insert([
                'report_id' => $reportId,
                'version_no' => $newVersionNo,
                'template_id' => (int) $template['id'],
                'generation_source' => 'MANUAL',
                'generated_at' => $now,
                'generated_by' => $userId,
                'created_at' => $now,
                'created_by' => $userId,
            ]);
            $versionId = (int) $this->reportVersionModel->getInsertID();

            $this->createVersionDataRow($versionId, $now, $userId);

            $this->reportModel->update($reportId, [
                'latest_version_no' => $newVersionNo,
                'updated_at' => $now,
                'updated_by' => $userId,
            ]);

            if (!$this->db->transStatus()) {
                throw new RuntimeException('Failed to create Daily Report draft.');
            }

            $this->db->transCommit();
            return [
                'success' => true,
                'message' => 'Draft created successfully.',
                'data' => [
                    'report_id' => $reportId,
                    'version_id' => $versionId,
                    'version_no' => $newVersionNo,
                    'report_date' => $reportDate,
                ],
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return ['success' => false, 'code' => 'SERVER_ERROR', 'message' => $e->getMessage(), 'data' => []];
        }
    }

    public function getVersionContext(int $versionId): ?array
    {
        return $this->fetchVersionContext($versionId);
    }

    public function getLatestPdfArtifactByVersion(int $versionId): ?array
    {
        return $this->db->table('report_artifact')
            ->where('report_version_id', $versionId)
            ->where('artifact_type', 'PDF')
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();
    }

    public function resolvePdfPathForDownload(int $versionId, array $artifact): ?string
    {
        $storagePath = trim((string) ($artifact['storage_path'] ?? ''));
        $resolved = $this->resolveLocalArtifactPath($storagePath);
        if ($resolved !== null && is_file($resolved)) {
            return $resolved;
        }

        $fileName = trim((string) ($artifact['file_name'] ?? ''));
        $foundByName = $this->findDailyPdfByFileName($fileName);
        if ($foundByName !== null) {
            return $foundByName;
        }

        $rebuilt = $this->rebuildLegacyPdfFileIfPossible($versionId, $artifact);
        if ($rebuilt !== null && is_file($rebuilt)) {
            return $rebuilt;
        }

        return null;
    }

    public function getDailyImageLimits(): array
    {
        $maxSizeMb = $this->readDailyImageMaxSizeMb();
        $maxCount = $this->readDailyImageMaxCount();

        return [
            'max_size_mb' => $maxSizeMb,
            'max_size_bytes' => $maxSizeMb * 1024 * 1024,
            'max_count' => $maxCount,
            'allowed_extensions' => self::DAILY_IMAGE_ALLOWED_EXTENSIONS,
        ];
    }

    public function listDailyImages(int $versionId): array
    {
        $setup = $this->validateDailyDataSetup();
        if (!$setup['success']) {
            return $setup;
        }
        if ($versionId <= 0) {
            return ['success' => false, 'code' => 'VALIDATION_ERROR', 'message' => 'Invalid version id.', 'data' => []];
        }

        $context = $this->fetchVersionContext($versionId);
        if (!$context) {
            return ['success' => false, 'code' => 'NOT_FOUND', 'message' => 'Daily report version not found.', 'data' => []];
        }

        $artifacts = $this->getDailyImageArtifactsByVersion($versionId);
        return [
            'success' => true,
            'message' => 'Images listed successfully.',
            'data' => [
                'version_id' => $versionId,
                'images' => $this->formatDailyImagesForResponse($artifacts),
                'limits' => $this->getDailyImageLimits(),
            ],
        ];
    }

    public function uploadDailyImages(int $versionId, array $files, ?int $userId): array
    {
        $setup = $this->validateDailyDataSetup();
        if (!$setup['success']) {
            return $setup;
        }
        if ($versionId <= 0) {
            return ['success' => false, 'code' => 'VALIDATION_ERROR', 'message' => 'Invalid version id.', 'data' => []];
        }

        $uploadedFiles = $this->normalizeUploadedFiles($files);
        if (empty($uploadedFiles)) {
            return ['success' => false, 'code' => 'VALIDATION_ERROR', 'message' => 'Please select at least one image file.', 'data' => []];
        }

        $limits = $this->getDailyImageLimits();
        $maxSizeBytes = (int) ($limits['max_size_bytes'] ?? (self::DEFAULT_DAILY_IMAGE_MAX_SIZE_MB * 1024 * 1024));
        $maxCount = (int) ($limits['max_count'] ?? self::DEFAULT_DAILY_IMAGE_MAX_COUNT);
        $now = date('Y-m-d H:i:s');
        $movedFilePaths = [];

        $this->db->transBegin();
        try {
            $this->lockDailyVersionDataRow($versionId);
            $context = $this->fetchVersionContext($versionId);
            if (!$context) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'NOT_FOUND', 'message' => 'Daily report version not found.', 'data' => []];
            }
            if (!$this->isDraftEditable($context)) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'DRAFT_LOCKED', 'message' => 'This draft is locked or already finalized.', 'data' => []];
            }

            $existingArtifacts = $this->getDailyImageArtifactsByVersion($versionId);
            if ((count($existingArtifacts) + count($uploadedFiles)) > $maxCount) {
                $this->db->transRollback();
                return [
                    'success' => false,
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Maximum allowed images for this draft is ' . $maxCount . '.',
                    'data' => ['max_count' => $maxCount, 'current_count' => count($existingArtifacts)],
                ];
            }

            $artifactDir = $this->buildDailyImageArtifactDirectory($context);
            if (!is_dir($artifactDir)) {
                mkdir($artifactDir, 0775, true);
            }

            foreach ($uploadedFiles as $file) {
                $validation = $this->validateDailyImageUpload($file, $maxSizeBytes);
                if (!$validation['success']) {
                    $this->db->transRollback();
                    return [
                        'success' => false,
                        'code' => 'VALIDATION_ERROR',
                        'message' => (string) ($validation['message'] ?? 'Invalid image file.'),
                        'data' => $validation['data'] ?? [],
                    ];
                }

                $ext = (string) ($validation['data']['extension'] ?? 'jpg');
                $mimeType = (string) ($validation['data']['mime_type'] ?? 'image/jpeg');
                $newFileName = sprintf(
                    'daily_%d_%s_%s.%s',
                    $versionId,
                    date('Ymd_His'),
                    bin2hex(random_bytes(4)),
                    $ext
                );

                $file->move($artifactDir, $newFileName, true);
                $fullPath = $artifactDir . $newFileName;
                $movedFilePaths[] = $fullPath;

                if (!is_file($fullPath)) {
                    throw new RuntimeException('Failed to store uploaded image.');
                }

                $relativePath = $this->toWriteRelativePath($fullPath);
                $fileSize = filesize($fullPath);
                $sha = hash_file('sha256', $fullPath);

                $this->reportArtifactModel->insert([
                    'report_version_id' => $versionId,
                    'artifact_type' => self::DAILY_IMAGE_ARTIFACT_TYPE,
                    'storage_driver' => 'LOCAL',
                    'storage_path' => $relativePath,
                    'file_name' => $newFileName,
                    'mime_type' => $mimeType,
                    'file_size' => $fileSize !== false ? $fileSize : null,
                    'sha256' => $sha ?: null,
                    'created_at' => $now,
                    'created_by' => $userId,
                ]);
            }

            $existingManualData = $this->decodeJsonObject($context['manual_json'] ?? '{}');
            $updatedArtifacts = $this->syncDailyImagesInManualJson($versionId, $existingManualData, $now, $userId);

            if (!$this->db->transStatus()) {
                throw new RuntimeException('Failed to upload daily images.');
            }

            $this->db->transCommit();
            return [
                'success' => true,
                'message' => 'Images uploaded successfully.',
                'data' => [
                    'version_id' => $versionId,
                    'images' => $this->formatDailyImagesForResponse($updatedArtifacts),
                    'limits' => $this->getDailyImageLimits(),
                ],
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            foreach ($movedFilePaths as $filePath) {
                if (is_file($filePath)) {
                    @unlink($filePath);
                }
            }
            return ['success' => false, 'code' => 'SERVER_ERROR', 'message' => $e->getMessage(), 'data' => []];
        }
    }

    public function deleteDailyImage(int $versionId, int $artifactId, ?int $userId): array
    {
        $setup = $this->validateDailyDataSetup();
        if (!$setup['success']) {
            return $setup;
        }
        if ($versionId <= 0 || $artifactId <= 0) {
            return ['success' => false, 'code' => 'VALIDATION_ERROR', 'message' => 'Invalid version or image id.', 'data' => []];
        }

        $this->db->transBegin();
        try {
            $this->lockDailyVersionDataRow($versionId);
            $context = $this->fetchVersionContext($versionId);
            if (!$context) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'NOT_FOUND', 'message' => 'Daily report version not found.', 'data' => []];
            }
            if (!$this->isDraftEditable($context)) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'DRAFT_LOCKED', 'message' => 'This draft is locked or already finalized.', 'data' => []];
            }

            $artifact = $this->getDailyImageArtifactRaw($versionId, $artifactId);
            if (!$artifact) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'NOT_FOUND', 'message' => 'Image not found.', 'data' => []];
            }

            $storagePath = (string) ($artifact['storage_path'] ?? '');
            $this->reportArtifactModel->delete($artifactId);

            $now = date('Y-m-d H:i:s');
            $existingManualData = $this->decodeJsonObject($context['manual_json'] ?? '{}');
            $updatedArtifacts = $this->syncDailyImagesInManualJson($versionId, $existingManualData, $now, $userId);

            if (!$this->db->transStatus()) {
                throw new RuntimeException('Failed to delete daily image.');
            }

            $this->db->transCommit();
            $fileDeleteFailures = [];
            $fullPath = $this->resolveLocalArtifactPath($storagePath);
            if ($fullPath !== null && is_file($fullPath) && !@unlink($fullPath)) {
                $fileDeleteFailures[] = $storagePath;
            }

            return [
                'success' => true,
                'message' => empty($fileDeleteFailures) ? 'Image deleted successfully.' : 'Image deleted, but file cleanup was incomplete.',
                'data' => [
                    'version_id' => $versionId,
                    'images' => $this->formatDailyImagesForResponse($updatedArtifacts),
                    'limits' => $this->getDailyImageLimits(),
                    'file_delete_failures' => $fileDeleteFailures,
                ],
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return ['success' => false, 'code' => 'SERVER_ERROR', 'message' => $e->getMessage(), 'data' => []];
        }
    }

    public function replaceDailyImage(int $versionId, int $artifactId, UploadedFile $file, ?int $userId): array
    {
        $setup = $this->validateDailyDataSetup();
        if (!$setup['success']) {
            return $setup;
        }
        if ($versionId <= 0 || $artifactId <= 0) {
            return ['success' => false, 'code' => 'VALIDATION_ERROR', 'message' => 'Invalid version or image id.', 'data' => []];
        }

        $limits = $this->getDailyImageLimits();
        $maxSizeBytes = (int) ($limits['max_size_bytes'] ?? (self::DEFAULT_DAILY_IMAGE_MAX_SIZE_MB * 1024 * 1024));
        $validation = $this->validateDailyImageUpload($file, $maxSizeBytes);
        if (!$validation['success']) {
            return [
                'success' => false,
                'code' => 'VALIDATION_ERROR',
                'message' => (string) ($validation['message'] ?? 'Invalid image file.'),
                'data' => $validation['data'] ?? [],
            ];
        }

        $movedFilePath = '';
        $oldStoragePath = '';
        $this->db->transBegin();
        try {
            $this->lockDailyVersionDataRow($versionId);
            $context = $this->fetchVersionContext($versionId);
            if (!$context) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'NOT_FOUND', 'message' => 'Daily report version not found.', 'data' => []];
            }
            if (!$this->isDraftEditable($context)) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'DRAFT_LOCKED', 'message' => 'This draft is locked or already finalized.', 'data' => []];
            }

            $artifact = $this->getDailyImageArtifactRaw($versionId, $artifactId);
            if (!$artifact) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'NOT_FOUND', 'message' => 'Image not found.', 'data' => []];
            }

            $oldStoragePath = (string) ($artifact['storage_path'] ?? '');
            $artifactDir = $this->buildDailyImageArtifactDirectory($context);
            if (!is_dir($artifactDir)) {
                mkdir($artifactDir, 0775, true);
            }

            $ext = (string) ($validation['data']['extension'] ?? 'jpg');
            $mimeType = (string) ($validation['data']['mime_type'] ?? 'image/jpeg');
            $newFileName = sprintf(
                'daily_%d_%s_%s.%s',
                $versionId,
                date('Ymd_His'),
                bin2hex(random_bytes(4)),
                $ext
            );

            $file->move($artifactDir, $newFileName, true);
            $movedFilePath = $artifactDir . $newFileName;
            if (!is_file($movedFilePath)) {
                throw new RuntimeException('Failed to store replacement image.');
            }

            $relativePath = $this->toWriteRelativePath($movedFilePath);
            $fileSize = filesize($movedFilePath);
            $sha = hash_file('sha256', $movedFilePath);

            $this->reportArtifactModel->update($artifactId, [
                'storage_path' => $relativePath,
                'file_name' => $newFileName,
                'mime_type' => $mimeType,
                'file_size' => $fileSize !== false ? $fileSize : null,
                'sha256' => $sha ?: null,
            ]);

            $now = date('Y-m-d H:i:s');
            $existingManualData = $this->decodeJsonObject($context['manual_json'] ?? '{}');
            $updatedArtifacts = $this->syncDailyImagesInManualJson($versionId, $existingManualData, $now, $userId);

            if (!$this->db->transStatus()) {
                throw new RuntimeException('Failed to replace daily image.');
            }

            $this->db->transCommit();
            $oldFullPath = $this->resolveLocalArtifactPath($oldStoragePath);
            if ($oldFullPath !== null && $oldFullPath !== $movedFilePath && is_file($oldFullPath)) {
                @unlink($oldFullPath);
            }

            return [
                'success' => true,
                'message' => 'Image replaced successfully.',
                'data' => [
                    'version_id' => $versionId,
                    'images' => $this->formatDailyImagesForResponse($updatedArtifacts),
                    'limits' => $this->getDailyImageLimits(),
                ],
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            if ($movedFilePath !== '' && is_file($movedFilePath)) {
                @unlink($movedFilePath);
            }
            return ['success' => false, 'code' => 'SERVER_ERROR', 'message' => $e->getMessage(), 'data' => []];
        }
    }

    public function getDailyImageArtifact(int $versionId, int $artifactId): ?array
    {
        $setup = $this->validateDailyDataSetup();
        if (!$setup['success']) {
            return null;
        }

        return $this->getDailyImageArtifactRaw($versionId, $artifactId);
    }

    public function saveDraft(int $versionId, array $manualData, ?int $userId): array
    {
        $setup = $this->validateDailyDataSetup();
        if (!$setup['success']) {
            return $setup;
        }
        if ($versionId <= 0) {
            return ['success' => false, 'code' => 'VALIDATION_ERROR', 'message' => 'Invalid version id.', 'data' => []];
        }

        $now = date('Y-m-d H:i:s');
        $this->db->transBegin();
        try {
            $this->lockDailyVersionDataRow($versionId);
            $context = $this->fetchVersionContext($versionId);
            if (!$context) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'NOT_FOUND', 'message' => 'Daily report version not found.', 'data' => []];
            }
            if (!$this->isDraftEditable($context)) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'DRAFT_LOCKED', 'message' => 'This draft is locked or already finalized.', 'data' => []];
            }

            $existingManualData = $this->decodeJsonObject($context['manual_json'] ?? '{}');
            $cleanManualData = $this->sanitizeDailyManualData($manualData, $existingManualData);

            $this->dailyReportVersionDataModel
                ->where('report_version_id', $versionId)
                ->set([
                    'manual_json' => json_encode($cleanManualData, JSON_UNESCAPED_UNICODE),
                    'updated_at' => $now,
                    'updated_by' => $userId,
                ])
                ->update();

            if (!$this->db->transStatus()) {
                throw new RuntimeException('Failed to save draft.');
            }

            $this->db->transCommit();
            return ['success' => true, 'message' => 'Draft saved successfully.', 'data' => ['version_id' => $versionId]];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return ['success' => false, 'code' => 'SERVER_ERROR', 'message' => $e->getMessage(), 'data' => []];
        }
    }

    public function pullSectionData(int $versionId, string $sectionKey, ?int $userId): array
    {
        $setup = $this->validateDailyDataSetup();
        if (!$setup['success']) {
            return $setup;
        }
        if ($versionId <= 0) {
            return ['success' => false, 'code' => 'VALIDATION_ERROR', 'message' => 'Invalid version id.', 'data' => []];
        }

        $sectionKey = trim($sectionKey);
        if ($sectionKey !== self::DAILY_CONTENT_SECTION_KEY) {
            return [
                'success' => false,
                'code' => 'VALIDATION_ERROR',
                'message' => 'Invalid section key.',
                'data' => ['section_key' => $sectionKey],
            ];
        }

        $now = date('Y-m-d H:i:s');
        $this->db->transBegin();
        try {
            $this->lockDailyVersionDataRow($versionId);
            $context = $this->fetchVersionContext($versionId);
            if (!$context) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'NOT_FOUND', 'message' => 'Daily report version not found.', 'data' => []];
            }
            if (!$this->isDraftEditable($context)) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'DRAFT_LOCKED', 'message' => 'This draft is locked or already finalized.', 'data' => []];
            }

            $renderModel = $this->buildRenderModel((int) $context['subject_id'], (string) $context['report_date']);
            $sectionData = [
                'render_payload' => $renderModel->toArray(),
                'token_values' => $renderModel->tokenValues(),
            ];

            $snapshot = $this->decodeJsonObject($context['snapshot_json'] ?? '{}');
            if (!isset($snapshot['sections']) || !is_array($snapshot['sections'])) {
                $snapshot['sections'] = [];
            }
            $snapshot['sections'][$sectionKey] = [
                'pulled_at' => $now,
                'data' => $sectionData,
            ];

            $sectionStatus = $this->decodeJsonObject($context['section_status_json'] ?? '{}');
            if (!isset($sectionStatus['sections']) || !is_array($sectionStatus['sections'])) {
                $sectionStatus['sections'] = [];
            }
            $sectionStatus['sections'][$sectionKey] = [
                'status' => 'PULLED',
                'pulled_at' => $now,
            ];

            $this->dailyReportVersionDataModel
                ->where('report_version_id', $versionId)
                ->set([
                    'snapshot_json' => json_encode($snapshot, JSON_UNESCAPED_UNICODE),
                    'section_status_json' => json_encode($sectionStatus, JSON_UNESCAPED_UNICODE),
                    'updated_at' => $now,
                    'updated_by' => $userId,
                ])
                ->update();

            if (!$this->db->transStatus()) {
                throw new RuntimeException('Failed to save pulled section data.');
            }

            $this->db->transCommit();
            return [
                'success' => true,
                'message' => 'Section pulled successfully.',
                'data' => [
                    'version_id' => $versionId,
                    'section_key' => $sectionKey,
                    'pulled_at' => $now,
                    'section_data' => $sectionData,
                ],
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return ['success' => false, 'code' => 'SERVER_ERROR', 'message' => $e->getMessage(), 'data' => []];
        }
    }

    public function finalizeDraft(int $versionId, ?int $userId): array
    {
        $setup = $this->validateDailyDataSetup();
        if (!$setup['success']) {
            return $setup;
        }

        $template = $this->resolveActiveTemplate('DAILY');
        if ($template === null) {
            return ['success' => false, 'code' => 'TEMPLATE_SETUP_REQUIRED', 'message' => 'Daily template is missing. Add active DAILY template in report_template first.', 'data' => []];
        }

        $templatePath = WRITEPATH . str_replace('/', DIRECTORY_SEPARATOR, ltrim((string) $template['storage_path'], '/'));
        if (!is_file($templatePath)) {
            return [
                'success' => false,
                'code' => 'TEMPLATE_FILE_MISSING',
                'message' => 'Daily template file not found at: ' . $template['storage_path'],
                'data' => ['storage_path' => $template['storage_path']],
            ];
        }

        $now = date('Y-m-d H:i:s');
        $this->db->transBegin();
        try {
            $this->lockDailyVersionDataRow($versionId);
            $context = $this->fetchVersionContext($versionId);
            if (!$context) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'NOT_FOUND', 'message' => 'Daily report version not found.', 'data' => []];
            }
            if (!$this->isDraftEditable($context)) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'DRAFT_LOCKED', 'message' => 'This draft is locked or already finalized.', 'data' => []];
            }

            $sectionData = $this->extractDailyContentSectionData($context);
            $validation = $this->validateFinalizeReadiness($sectionData);
            if (!$validation['success']) {
                $this->db->transRollback();
                return $validation;
            }

            $manualData = $this->decodeJsonObject($context['manual_json'] ?? '{}');
            $tokenValues = $this->buildDailyTokenValuesFromSectionData($sectionData, $versionId, $manualData);
            $artifact = $this->createArtifact(
                (int) $context['subject_id'],
                (string) $context['report_date'],
                (int) $context['version_no'],
                $tokenValues,
                $versionId,
                $userId,
                $now,
                $template
            );

            $this->dailyReportVersionDataModel
                ->where('report_version_id', $versionId)
                ->set([
                    'workflow_status' => 'FINAL',
                    'is_locked' => 1,
                    'finalized_at' => $now,
                    'finalized_by' => $userId,
                    'updated_at' => $now,
                    'updated_by' => $userId,
                ])
                ->update();

            if (!$this->db->transStatus()) {
                throw new RuntimeException('Failed to finalize draft.');
            }

            $this->db->transCommit();
            return [
                'success' => true,
                'message' => 'Draft finalized successfully.',
                'data' => [
                    'version_id' => $versionId,
                    'storage_path' => $artifact['storage_path'],
                    'file_name' => $artifact['file_name'],
                ],
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return ['success' => false, 'code' => 'SERVER_ERROR', 'message' => $e->getMessage(), 'data' => []];
        }
    }

    public function regenerateDraft(int $versionId, ?int $userId): array
    {
        $setup = $this->validateDailyDataSetup();
        if (!$setup['success']) {
            return $setup;
        }

        $source = $this->fetchVersionContext($versionId);
        if (!$source) {
            return ['success' => false, 'code' => 'NOT_FOUND', 'message' => 'Daily report version not found.', 'data' => []];
        }
        if ($this->resolveWorkflowStatus($source) !== 'FINAL') {
            return ['success' => false, 'code' => 'NOT_FINAL', 'message' => 'Only finalized versions can be regenerated.', 'data' => []];
        }

        $template = $this->resolveActiveTemplate('DAILY');
        if ($template === null) {
            return ['success' => false, 'code' => 'TEMPLATE_SETUP_REQUIRED', 'message' => 'Daily template is missing. Add active DAILY template in report_template first.', 'data' => []];
        }

        $subjectId = (int) $source['subject_id'];
        $reportId = (int) $source['report_id'];
        $now = date('Y-m-d H:i:s');

        $this->db->transBegin();
        try {
            $this->lockClientRow($subjectId);

            $activeDraft = $this->findActiveDraft($subjectId);
            if ($activeDraft) {
                $this->db->transRollback();
                return [
                    'success' => false,
                    'code' => 'ACTIVE_DRAFT_EXISTS',
                    'message' => 'An active Daily Report draft already exists for this learner.',
                    'data' => [
                        'report_id' => (int) ($activeDraft['report_id'] ?? 0),
                        'version_id' => (int) ($activeDraft['version_id'] ?? 0),
                        'version_no' => (int) ($activeDraft['version_no'] ?? 0),
                        'report_date' => (string) ($activeDraft['report_date'] ?? ''),
                    ],
                ];
            }

            $report = $this->db->query(
                'SELECT id, latest_version_no FROM report WHERE id = ? AND report_type = "DAILY" AND subject_type = "LEARNER" LIMIT 1 FOR UPDATE',
                [$reportId]
            )->getRowArray();

            if (!$report) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'NOT_FOUND', 'message' => 'Daily report not found.', 'data' => []];
            }

            $newVersionNo = ((int) ($report['latest_version_no'] ?? 0)) + 1;
            $this->reportVersionModel->insert([
                'report_id' => $reportId,
                'version_no' => $newVersionNo,
                'template_id' => (int) $template['id'],
                'generation_source' => 'MANUAL',
                'generated_at' => $now,
                'generated_by' => $userId,
                'created_at' => $now,
                'created_by' => $userId,
            ]);
            $newVersionId = (int) $this->reportVersionModel->getInsertID();

            $this->createVersionDataRow($newVersionId, $now, $userId);

            $this->reportModel->update($reportId, [
                'latest_version_no' => $newVersionNo,
                'updated_at' => $now,
                'updated_by' => $userId,
            ]);

            if (!$this->db->transStatus()) {
                throw new RuntimeException('Failed to regenerate draft.');
            }

            $this->db->transCommit();
            return [
                'success' => true,
                'message' => 'Draft regenerated successfully.',
                'data' => [
                    'report_id' => $reportId,
                    'version_id' => $newVersionId,
                    'version_no' => $newVersionNo,
                    'report_date' => (string) ($source['report_date'] ?? ''),
                ],
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return ['success' => false, 'code' => 'SERVER_ERROR', 'message' => $e->getMessage(), 'data' => []];
        }
    }

    public function deleteLatestVersion(int $versionId, ?int $userId): array
    {
        if ($versionId <= 0) {
            return ['success' => false, 'code' => 'VALIDATION_ERROR', 'message' => 'Invalid version id.', 'data' => []];
        }

        $now = date('Y-m-d H:i:s');
        $artifacts = [];
        $reportDeleted = false;
        $newLatestVersionNo = 0;

        $this->db->transBegin();
        try {
            $version = $this->db->query(
                'SELECT rv.id AS version_id, rv.report_id, rv.version_no, r.latest_version_no
                 FROM report_version rv
                 INNER JOIN report r ON r.id = rv.report_id
                 WHERE rv.id = ?
                   AND r.report_type = "DAILY"
                   AND r.subject_type = "LEARNER"
                 LIMIT 1 FOR UPDATE',
                [$versionId]
            )->getRowArray();

            if (!$version) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'NOT_FOUND', 'message' => 'Daily report version not found.', 'data' => []];
            }

            if ((int) ($version['version_no'] ?? 0) !== (int) ($version['latest_version_no'] ?? 0)) {
                $this->db->transRollback();
                return [
                    'success' => false,
                    'code' => 'NOT_LATEST',
                    'message' => 'Only latest version can be deleted.',
                    'data' => [
                        'report_id' => (int) ($version['report_id'] ?? 0),
                        'latest_version_no' => (int) ($version['latest_version_no'] ?? 0),
                        'requested_version_no' => (int) ($version['version_no'] ?? 0),
                    ],
                ];
            }

            $reportId = (int) ($version['report_id'] ?? 0);
            $artifacts = $this->listArtifactsByVersionIds([$versionId]);

            if ($this->dailyVersionDataTableExists()) {
                $this->dailyReportVersionDataModel->where('report_version_id', $versionId)->delete();
            }
            $this->reportArtifactModel->where('report_version_id', $versionId)->delete();
            $this->reportVersionModel->delete($versionId);

            $remaining = $this->db->table('report_version')
                ->select('MAX(version_no) AS max_version_no', false)
                ->where('report_id', $reportId)
                ->get()
                ->getRowArray();
            $newLatestVersionNo = (int) ($remaining['max_version_no'] ?? 0);

            if ($newLatestVersionNo > 0) {
                $this->reportModel->update($reportId, [
                    'latest_version_no' => $newLatestVersionNo,
                    'updated_at' => $now,
                    'updated_by' => $userId,
                ]);
            } else {
                $this->reportModel->delete($reportId);
                $reportDeleted = true;
            }

            if (!$this->db->transStatus()) {
                throw new RuntimeException('Failed to delete daily report version.');
            }

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return ['success' => false, 'code' => 'SERVER_ERROR', 'message' => $e->getMessage(), 'data' => []];
        }

        $fileDeleteFailures = $this->deleteArtifactFiles($artifacts);
        $message = empty($fileDeleteFailures)
            ? 'Daily report version deleted successfully.'
            : 'Daily report version deleted, but some media files could not be removed.';

        return [
            'success' => true,
            'message' => $message,
            'data' => [
                'version_id' => $versionId,
                'report_deleted' => $reportDeleted,
                'latest_version_no' => $newLatestVersionNo,
                'file_delete_failures' => $fileDeleteFailures,
            ],
        ];
    }

    public function deleteAllVersions(int $reportId, ?int $userId): array
    {
        if ($reportId <= 0) {
            return ['success' => false, 'code' => 'VALIDATION_ERROR', 'message' => 'Invalid report id.', 'data' => []];
        }

        $versionIds = [];
        $artifacts = [];

        $this->db->transBegin();
        try {
            $report = $this->db->query(
                'SELECT id FROM report
                 WHERE id = ?
                   AND report_type = "DAILY"
                   AND subject_type = "LEARNER"
                 LIMIT 1 FOR UPDATE',
                [$reportId]
            )->getRowArray();

            if (!$report) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'NOT_FOUND', 'message' => 'Daily report not found.', 'data' => []];
            }

            $versions = $this->db->table('report_version')
                ->select('id')
                ->where('report_id', $reportId)
                ->get()
                ->getResultArray();
            foreach ($versions as $row) {
                $id = (int) ($row['id'] ?? 0);
                if ($id > 0) {
                    $versionIds[] = $id;
                }
            }

            $artifacts = $this->listArtifactsByVersionIds($versionIds);
            if (!empty($versionIds)) {
                if ($this->dailyVersionDataTableExists()) {
                    $this->dailyReportVersionDataModel->whereIn('report_version_id', $versionIds)->delete();
                }
                $this->reportArtifactModel->whereIn('report_version_id', $versionIds)->delete();
                $this->db->table('report_version')->where('report_id', $reportId)->delete();
            }

            $this->reportModel->delete($reportId);
            if (!$this->db->transStatus()) {
                throw new RuntimeException('Failed to delete daily report.');
            }

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return ['success' => false, 'code' => 'SERVER_ERROR', 'message' => $e->getMessage(), 'data' => []];
        }

        $fileDeleteFailures = $this->deleteArtifactFiles($artifacts);
        $message = empty($fileDeleteFailures)
            ? 'Daily report deleted successfully.'
            : 'Daily report deleted, but some media files could not be removed.';

        return [
            'success' => true,
            'message' => $message,
            'data' => [
                'report_id' => $reportId,
                'deleted_version_count' => count($versionIds),
                'file_delete_failures' => $fileDeleteFailures,
                'updated_by' => $userId,
            ],
        ];
    }

    private function createVersionDataRow(int $versionId, string $now, ?int $userId): void
    {
        $this->dailyReportVersionDataModel->insert([
            'report_version_id' => $versionId,
            'workflow_status' => 'DRAFT',
            'is_locked' => 0,
            'manual_json' => '{}',
            'snapshot_json' => '{}',
            'section_status_json' => json_encode(['sections' => new \stdClass()]),
            'created_at' => $now,
            'created_by' => $userId,
            'updated_at' => $now,
            'updated_by' => $userId,
        ]);
    }

    private function readDailyImageMaxSizeMb(): int
    {
        $stored = setting('Report.progressImageMaxSizeMb');
        $value = is_numeric($stored) ? (int) $stored : self::DEFAULT_DAILY_IMAGE_MAX_SIZE_MB;
        if ($value < 1) {
            $value = self::DEFAULT_DAILY_IMAGE_MAX_SIZE_MB;
        }

        return min($value, self::ABSOLUTE_DAILY_IMAGE_MAX_SIZE_MB);
    }

    private function readDailyImageMaxCount(): int
    {
        $stored = setting('Report.progressImageMaxCount');
        $value = is_numeric($stored) ? (int) $stored : self::DEFAULT_DAILY_IMAGE_MAX_COUNT;
        if ($value < 1) {
            $value = self::DEFAULT_DAILY_IMAGE_MAX_COUNT;
        }

        return min($value, self::ABSOLUTE_DAILY_IMAGE_MAX_COUNT);
    }

    /**
     * @param array<int, mixed> $files
     * @return array<int, UploadedFile>
     */
    private function normalizeUploadedFiles(array $files): array
    {
        $normalized = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $normalized[] = $file;
            }
        }

        return $normalized;
    }

    private function validateDailyImageUpload(UploadedFile $file, int $maxSizeBytes): array
    {
        if (!$file->isValid() || $file->hasMoved()) {
            return [
                'success' => false,
                'message' => 'Invalid uploaded file.',
                'data' => ['error' => $file->getErrorString()],
            ];
        }

        $extension = strtolower(trim((string) ($file->getClientExtension() ?: $file->getExtension())));
        if ($extension === '' || !in_array($extension, self::DAILY_IMAGE_ALLOWED_EXTENSIONS, true)) {
            return [
                'success' => false,
                'message' => 'Only JPG, JPEG, and PNG images are allowed.',
                'data' => ['extension' => $extension],
            ];
        }

        $mimeType = strtolower(trim((string) $file->getMimeType()));
        if ($mimeType === '' || !in_array($mimeType, self::DAILY_IMAGE_ALLOWED_MIME_TYPES, true)) {
            return [
                'success' => false,
                'message' => 'Unsupported image type.',
                'data' => ['mime_type' => $mimeType],
            ];
        }

        $size = (int) $file->getSize();
        if ($size <= 0 || $size > $maxSizeBytes) {
            return [
                'success' => false,
                'message' => 'Image exceeds maximum allowed size.',
                'data' => ['max_size_bytes' => $maxSizeBytes],
            ];
        }

        return [
            'success' => true,
            'data' => [
                'extension' => $extension,
                'mime_type' => $mimeType,
                'size' => $size,
            ],
        ];
    }

    private function buildDailyImageArtifactDirectory(array $context): string
    {
        $subjectId = (int) ($context['subject_id'] ?? 0);
        $versionNo = (int) ($context['version_no'] ?? 0);
        $reportDate = trim((string) ($context['report_date'] ?? ''));

        return WRITEPATH
            . 'reports/artifacts/daily/learner/'
            . $subjectId
            . '/'
            . $reportDate
            . '/v'
            . $versionNo
            . '/images/';
    }

    private function toWriteRelativePath(string $fullPath): string
    {
        $writeRoot = rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR;
        $relative = str_replace($writeRoot, '', $fullPath);
        return ltrim(str_replace('\\', '/', $relative), '/');
    }

    private function getDailyImageArtifactsByVersion(int $versionId): array
    {
        return $this->db->table('report_artifact')
            ->select([
                'id',
                'report_version_id',
                'storage_driver',
                'storage_path',
                'file_name',
                'mime_type',
                'file_size',
                'created_at',
                'created_by',
            ])
            ->where('report_version_id', $versionId)
            ->where('artifact_type', self::DAILY_IMAGE_ARTIFACT_TYPE)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function getDailyImageArtifactRaw(int $versionId, int $artifactId): ?array
    {
        $row = $this->db->table('report_artifact')
            ->where('id', $artifactId)
            ->where('report_version_id', $versionId)
            ->where('artifact_type', self::DAILY_IMAGE_ARTIFACT_TYPE)
            ->get()
            ->getRowArray();

        return is_array($row) ? $row : null;
    }

    private function formatDailyImagesForResponse(array $artifacts): array
    {
        $rows = [];
        foreach ($artifacts as $artifact) {
            if (!is_array($artifact)) {
                continue;
            }

            $rows[] = [
                'artifact_id' => (int) ($artifact['id'] ?? 0),
                'file_name' => (string) ($artifact['file_name'] ?? ''),
                'mime_type' => (string) ($artifact['mime_type'] ?? ''),
                'file_size' => isset($artifact['file_size']) ? (int) $artifact['file_size'] : null,
                'created_at' => (string) ($artifact['created_at'] ?? ''),
            ];
        }

        return $rows;
    }

    private function mapDailyImagesForManualJson(array $artifacts): array
    {
        $rows = [];
        foreach ($artifacts as $artifact) {
            if (!is_array($artifact)) {
                continue;
            }

            $rows[] = [
                'artifact_id' => (int) ($artifact['id'] ?? 0),
                'file_name' => (string) ($artifact['file_name'] ?? ''),
                'mime_type' => (string) ($artifact['mime_type'] ?? ''),
                'file_size' => isset($artifact['file_size']) ? (int) $artifact['file_size'] : null,
            ];
        }

        return $rows;
    }

    private function syncDailyImagesInManualJson(int $versionId, array $manualData, string $now, ?int $userId): array
    {
        $artifacts = $this->getDailyImageArtifactsByVersion($versionId);
        $manualData['daily_images'] = $this->mapDailyImagesForManualJson($artifacts);

        $this->dailyReportVersionDataModel
            ->where('report_version_id', $versionId)
            ->set([
                'manual_json' => json_encode($this->sanitizeDailyManualData($manualData), JSON_UNESCAPED_UNICODE),
                'updated_at' => $now,
                'updated_by' => $userId,
            ])
            ->update();

        return $artifacts;
    }

    private function sanitizeDailyManualData(array $manualData, array $existingManualData = []): array
    {
        $images = $manualData['daily_images'] ?? ($existingManualData['daily_images'] ?? []);
        return [
            'daily_images' => $this->normalizeManualImageRows(is_array($images) ? $images : []),
        ];
    }

    private function normalizeManualImageRows(array $rows): array
    {
        $clean = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $artifactId = (int) ($row['artifact_id'] ?? 0);
            if ($artifactId <= 0) {
                continue;
            }

            $clean[] = [
                'artifact_id' => $artifactId,
                'file_name' => trim((string) ($row['file_name'] ?? '')),
                'mime_type' => trim((string) ($row['mime_type'] ?? '')),
                'file_size' => isset($row['file_size']) && is_numeric($row['file_size']) ? (int) $row['file_size'] : null,
            ];
        }

        return $clean;
    }

    private function fetchVersionContext(int $versionId): ?array
    {
        $builder = $this->db->table('report_version rv');
        $select = [
            'rv.id AS version_id',
            'rv.report_id',
            'rv.version_no',
            'rv.template_id',
            'rv.generated_at',
            'rv.generated_by',
            'r.subject_id',
            'r.period_key AS report_date',
            'r.period_start',
            'r.period_end',
            'r.period_key',
            "CONCAT(c.first_name, ' ', c.last_name) AS learner_name",
            "TRIM(CONCAT(COALESCE(gen.first_name, ''), ' ', COALESCE(gen.last_name, ''))) AS generated_by_name",
        ];

        if ($this->dailyVersionDataTableExists()) {
            $select = array_merge($select, [
                'drvd.workflow_status',
                'drvd.is_locked',
                'drvd.manual_json',
                'drvd.snapshot_json',
                'drvd.section_status_json',
                'drvd.finalized_at',
                'drvd.finalized_by',
            ]);
        } else {
            $select = array_merge($select, [
                'NULL AS workflow_status',
                '0 AS is_locked',
                'NULL AS manual_json',
                'NULL AS snapshot_json',
                'NULL AS section_status_json',
                'NULL AS finalized_at',
                'NULL AS finalized_by',
            ]);
        }

        $builder->select($select);
        $builder->join('report r', 'r.id = rv.report_id', 'inner');
        $builder->join('clients c', 'c.id = r.subject_id', 'left');
        $builder->join('users gen', 'gen.id = rv.generated_by', 'left');
        if ($this->dailyVersionDataTableExists()) {
            $builder->join('daily_report_version_data drvd', 'drvd.report_version_id = rv.id', 'left');
        }
        $builder->where('rv.id', $versionId);
        $builder->where('r.report_type', 'DAILY');
        $builder->where('r.subject_type', 'LEARNER');

        return $builder->get()->getRowArray();
    }

    private function dailyVersionDataTableExists(): bool
    {
        if ($this->hasDailyVersionDataTable === null) {
            $this->hasDailyVersionDataTable = $this->db->tableExists('daily_report_version_data');
        }

        return $this->hasDailyVersionDataTable;
    }

    private function validateDailyDataSetup(): array
    {
        if (!$this->dailyVersionDataTableExists()) {
            return ['success' => false, 'code' => 'DB_SETUP_REQUIRED', 'message' => 'Database setup required. Run database/daily_report_version_data_manual.sql first.', 'data' => []];
        }

        $requiredColumns = [
            'report_version_id',
            'workflow_status',
            'is_locked',
            'manual_json',
            'snapshot_json',
            'section_status_json',
            'finalized_at',
            'finalized_by',
        ];

        foreach ($requiredColumns as $column) {
            if (!$this->db->fieldExists($column, 'daily_report_version_data')) {
                return [
                    'success' => false,
                    'code' => 'DB_SETUP_REQUIRED',
                    'message' => 'Database setup is outdated. Re-run database/daily_report_version_data_manual.sql with latest schema.',
                    'data' => ['missing_column' => $column],
                ];
            }
        }

        return ['success' => true];
    }

    private function subjectExists(int $subjectId): bool
    {
        return $subjectId > 0 && $this->clientModel->find($subjectId) !== null;
    }

    private function findActiveDraft(int $subjectId): ?array
    {
        if (!$this->dailyVersionDataTableExists()) {
            return null;
        }

        return $this->db->query(
            'SELECT r.id AS report_id, r.period_key AS report_date, rv.id AS version_id, rv.version_no
             FROM report r
             INNER JOIN report_version rv
                ON rv.report_id = r.id
               AND rv.version_no = r.latest_version_no
             INNER JOIN daily_report_version_data drvd
                ON drvd.report_version_id = rv.id
             WHERE r.report_type = "DAILY"
               AND r.subject_type = "LEARNER"
               AND r.subject_id = ?
               AND drvd.workflow_status = "DRAFT"
               AND drvd.is_locked = 0
             ORDER BY rv.id DESC
             LIMIT 1',
            [$subjectId]
        )->getRowArray();
    }

    private function findReportBySubjectAndDate(int $subjectId, string $reportDate, bool $forUpdate = false): ?array
    {
        $sql = 'SELECT id, latest_version_no FROM report
                WHERE report_type = "DAILY"
                  AND subject_type = "LEARNER"
                  AND subject_id = ?
                  AND period_key = ?
                LIMIT 1';
        if ($forUpdate) {
            $sql .= ' FOR UPDATE';
        }

        return $this->db->query($sql, [$subjectId, $reportDate])->getRowArray();
    }

    private function lockClientRow(int $subjectId): void
    {
        $this->db->query('SELECT id FROM clients WHERE id = ? LIMIT 1 FOR UPDATE', [$subjectId]);
    }

    private function lockDailyVersionDataRow(int $versionId): void
    {
        $this->db->query('SELECT id FROM daily_report_version_data WHERE report_version_id = ? LIMIT 1 FOR UPDATE', [$versionId]);
    }

    private function resolveWorkflowStatus(array $context): string
    {
        $status = strtoupper(trim((string) ($context['workflow_status'] ?? '')));
        return $status !== '' ? $status : 'FINAL';
    }

    private function isDraftEditable(array $context): bool
    {
        return $this->resolveWorkflowStatus($context) === 'DRAFT'
            && (int) ($context['is_locked'] ?? 0) === 0;
    }

    private function decodeJsonObject(?string $json): array
    {
        if ($json === null || trim($json) === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function extractDailyContentSectionData(array $context): array
    {
        $snapshot = $this->decodeJsonObject($context['snapshot_json'] ?? '{}');
        $sections = $snapshot['sections'] ?? [];
        if (!is_array($sections)) {
            return [];
        }

        $section = $sections[self::DAILY_CONTENT_SECTION_KEY] ?? [];
        if (!is_array($section)) {
            return [];
        }

        $data = $section['data'] ?? [];
        return is_array($data) ? $data : [];
    }

    private function validateFinalizeReadiness(array $sectionData): array
    {
        if (empty($sectionData)) {
            return [
                'success' => false,
                'code' => 'FINALIZE_VALIDATION_ERROR',
                'message' => 'Finalize validation failed. Pull Daily Content before generating PDF.',
                'data' => [
                    'missing_requirements' => ['Please pull section: Daily Content.'],
                    'required_sections' => ['Daily Content'],
                ],
            ];
        }

        $tokenValues = $sectionData['token_values'] ?? [];
        $renderPayload = $sectionData['render_payload'] ?? [];
        if (!is_array($tokenValues) && !is_array($renderPayload)) {
            return [
                'success' => false,
                'code' => 'FINALIZE_VALIDATION_ERROR',
                'message' => 'Finalize validation failed. Pulled Daily Content is missing required snapshot data.',
                'data' => [
                    'missing_requirements' => ['Pulled data is missing for section: Daily Content.'],
                    'required_sections' => ['Daily Content'],
                ],
            ];
        }

        return ['success' => true];
    }

    private function buildDailyTokenValuesFromSectionData(array $sectionData, int $versionId, array $manualData): array
    {
        $defaults = array_fill_keys(DailyReportTokenMap::keys(), '');
        $tokenValues = [];

        if (isset($sectionData['token_values']) && is_array($sectionData['token_values'])) {
            foreach ($sectionData['token_values'] as $key => $value) {
                $tokenValues[(string) $key] = (string) $value;
            }
        } elseif (isset($sectionData['render_payload']) && is_array($sectionData['render_payload'])) {
            $renderModel = new DailyReportRenderModel($sectionData['render_payload']);
            $tokenValues = $renderModel->tokenValues();
        } else {
            foreach (DailyReportTokenMap::keys() as $key) {
                if (array_key_exists($key, $sectionData)) {
                    $tokenValues[$key] = (string) $sectionData[$key];
                }
            }
        }

        $tokenValues = array_merge($defaults, $tokenValues);
        $tokenValues['uploaded_images_html'] = $this->buildUploadedImagesHtml($versionId, $manualData['daily_images'] ?? null);

        return $tokenValues;
    }

    private function buildUploadedImagesHtml(int $versionId, $manualImages): string
    {
        if ($versionId <= 0) {
            return '';
        }

        $artifactIds = [];
        if (is_array($manualImages)) {
            foreach ($manualImages as $image) {
                if (!is_array($image)) {
                    continue;
                }
                $artifactId = (int) ($image['artifact_id'] ?? 0);
                if ($artifactId > 0) {
                    $artifactIds[] = $artifactId;
                }
            }
        }
        $artifactIds = array_values(array_unique($artifactIds));

        $artifacts = $this->getDailyImageArtifactsByVersion($versionId);
        if (!empty($artifactIds)) {
            $artifacts = array_values(array_filter(
                $artifacts,
                static fn(array $artifact): bool => in_array((int) ($artifact['id'] ?? 0), $artifactIds, true)
            ));
        }

        if (empty($artifacts)) {
            return '';
        }

        $items = [];
        foreach ($artifacts as $artifact) {
            $storagePath = trim((string) ($artifact['storage_path'] ?? ''));
            if ($storagePath === '') {
                continue;
            }

            $fullPath = $this->resolveLocalArtifactPath($storagePath);
            if ($fullPath === null || !is_file($fullPath)) {
                continue;
            }

            $mimeType = trim((string) ($artifact['mime_type'] ?? ''));
            if ($mimeType === '') {
                $ext = strtolower((string) pathinfo($fullPath, PATHINFO_EXTENSION));
                $mimeMap = ['png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'webp' => 'image/webp'];
                $mimeType = $mimeMap[$ext] ?? '';
            }
            if ($mimeType === '') {
                continue;
            }

            $content = file_get_contents($fullPath);
            if ($content === false) {
                continue;
            }

            $dataUri = 'data:' . $mimeType . ';base64,' . base64_encode($content);
            $fileName = trim((string) ($artifact['file_name'] ?? 'Uploaded Image'));
            $items[] = '<div class="daily-image-item"><div class="daily-image-frame"><img src="' . $dataUri . '" alt="' . htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8') . '"></div></div>';
        }

        if (empty($items)) {
            return '';
        }

        return '<div class="daily-images-section"><div class="daily-images-grid">' . implode('', $items) . '</div></div>';
    }

    private function listArtifactsByVersionIds(array $versionIds): array
    {
        $versionIds = array_values(array_unique(array_map('intval', $versionIds)));
        $versionIds = array_values(array_filter($versionIds, static fn(int $id): bool => $id > 0));
        if (empty($versionIds)) {
            return [];
        }

        return $this->db->table('report_artifact')
            ->select(['id', 'report_version_id', 'storage_driver', 'storage_path'])
            ->whereIn('report_version_id', $versionIds)
            ->get()
            ->getResultArray();
    }

    private function deleteArtifactFiles(array $artifacts): array
    {
        $failures = [];

        foreach ($artifacts as $artifact) {
            $storageDriver = strtoupper(trim((string) ($artifact['storage_driver'] ?? 'LOCAL')));
            if ($storageDriver !== 'LOCAL') {
                continue;
            }

            $fullPath = $this->resolveLocalArtifactPath((string) ($artifact['storage_path'] ?? ''));
            if ($fullPath === null || !is_file($fullPath)) {
                continue;
            }

            if (!@unlink($fullPath)) {
                $failures[] = (string) ($artifact['storage_path'] ?? '');
            }
        }

        return $failures;
    }

    private function resolveLocalArtifactPath(string $storagePath): ?string
    {
        $storagePath = trim($storagePath);
        if ($storagePath === '') {
            return null;
        }

        $normalized = str_replace('\\', '/', $storagePath);
        $normalized = ltrim($normalized, '/');
        if ($normalized === '' || str_contains($normalized, '..')) {
            return null;
        }

        return rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $normalized);
    }

    private function findDailyPdfByFileName(string $fileName): ?string
    {
        $fileName = trim($fileName);
        if ($fileName === '') {
            return null;
        }

        $dailyRoot = rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR . 'artifacts' . DIRECTORY_SEPARATOR . 'daily';
        if (!is_dir($dailyRoot)) {
            return null;
        }

        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dailyRoot, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $fileInfo) {
                if (!$fileInfo instanceof \SplFileInfo || !$fileInfo->isFile()) {
                    continue;
                }
                if (strcasecmp($fileInfo->getFilename(), $fileName) === 0) {
                    return $fileInfo->getPathname();
                }
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    private function rebuildLegacyPdfFileIfPossible(int $versionId, array $artifact): ?string
    {
        $storagePath = trim((string) ($artifact['storage_path'] ?? ''));
        $targetPath = $this->resolveLocalArtifactPath($storagePath);
        if ($targetPath === null) {
            return null;
        }
        if (is_file($targetPath)) {
            return $targetPath;
        }

        $context = $this->fetchVersionContext($versionId);
        if (!$context) {
            return null;
        }

        $workflowStatusRaw = trim((string) ($context['workflow_status'] ?? ''));
        $isLegacyFinal = $workflowStatusRaw === '' || strtoupper($workflowStatusRaw) === 'FINAL';
        if (!$isLegacyFinal) {
            return null;
        }

        // Only rebuild for legacy finals that do not have Daily workflow snapshot rows.
        if ($workflowStatusRaw !== '') {
            return null;
        }

        $subjectId = (int) ($context['subject_id'] ?? 0);
        $reportDate = trim((string) ($context['report_date'] ?? ''));
        if ($subjectId <= 0 || !$this->isValidYmd($reportDate)) {
            return null;
        }

        $templateId = (int) ($context['template_id'] ?? 0);
        $template = $templateId > 0 ? $this->reportTemplateModel->find($templateId) : null;
        if (!$template || !is_array($template)) {
            $template = $this->resolveActiveTemplate('DAILY');
        }
        if (!$template || !is_array($template)) {
            return null;
        }

        $templateStoragePath = trim((string) ($template['storage_path'] ?? ''));
        if ($templateStoragePath === '') {
            return null;
        }

        $templatePath = WRITEPATH . str_replace('/', DIRECTORY_SEPARATOR, ltrim($templateStoragePath, '/'));
        if (!is_file($templatePath)) {
            return null;
        }

        $targetDir = dirname($targetPath);
        if (!is_dir($targetDir) && !@mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            return null;
        }

        try {
            $renderModel = $this->buildRenderModel($subjectId, $reportDate);
            $tokenValues = $renderModel->tokenValues();
            $tokenValues['uploaded_images_html'] = '';

            $html = $this->htmlTemplateRenderer->render($templatePath, $tokenValues);
            $html = $this->removeHtmlFooterBlock($html);
            $this->htmlToPdfConverter->convert($html, $targetPath, [
                'left' => (string) ($tokenValues['report_footer_company'] ?? ''),
                'right_line_1' => (string) ($tokenValues['report_footer_address_line_1'] ?? ''),
                'right_line_2' => (string) ($tokenValues['report_footer_address_line_2'] ?? ''),
            ]);
        } catch (\Throwable $e) {
            return null;
        }

        return is_file($targetPath) ? $targetPath : null;
    }

    protected function resolveActiveTemplate(string $reportType): ?array
    {
        return $this->reportTemplateModel
            ->where('report_type', $reportType)
            ->where('is_active', 1)
            ->orderBy('version_no', 'DESC')
            ->first();
    }

    protected function buildRenderModel(int $subjectId, string $reportDate): DailyReportRenderModel
    {
        $client = $this->clientModel->find($subjectId);
        $learnerName = $client ? trim($client->first_name . ' ' . $client->last_name) : ('Client #' . $subjectId);

        $tutorNames = $this->dailyReportQueryModel->getTutorNames($subjectId, $reportDate);

        $defaults = array_fill_keys(DailyReportTokenMap::keys(), '');
        $defaults = array_merge($defaults, $this->resolveBrandingTokens());
        $defaults['learner_name'] = $learnerName;
        $defaults['tutor_names'] = $this->formatTutorNamesForReport($tutorNames);
        $netVsDti = $this->dailyReportQueryModel->getNetVsDtiSummaryByDate($subjectId, $reportDate);
        $defaults['net_vs_dti'] = $this->buildNetVsDtiText($netVsDti);
        $programRows = $this->dailyReportQueryModel->getProcessedProgramProbeRows($subjectId, $reportDate);
        $defaults['program_probes_table'] = $this->buildProgramProbesTableRows($programRows);
        $mandsSummary = $this->dailyReportQueryModel->getMandsSummaryByDate($subjectId, $reportDate);
        $defaults['mands_frequency'] = $this->formatMandsMetric($mandsSummary['total_mands'] ?? null);
        $defaults['mands_variety'] = $this->formatMandsMetric($mandsSummary['variety_of_mands'] ?? null);
        $defaults['mand_data_table'] = $this->buildMandsDataSummary($mandsSummary);
        $pbSummary = $this->dailyReportQueryModel->getProblemBehaviorSummaryByDate($subjectId, $reportDate);
        $defaults['problem_behavior_frequency'] = (string) ($pbSummary['frequency'] ?? 0);
        $defaults['problem_behavior_duration'] = $this->formatDurationFromSeconds((int) ($pbSummary['total_duration_seconds'] ?? 0));
        $defaults['problem_behavior_table'] = 'Placeholder data (Step 5).';
        $dailySessionNotes = $this->dailyReportQueryModel->getDailySessionCommentsAndWow($subjectId, $reportDate);
        $defaults['report_date'] = $this->buildReportDateHeader($reportDate, $dailySessionNotes);
        $defaults['tutor_comments'] = $this->buildCombinedSessionText($dailySessionNotes, 'instructor_comments') ?: 'N/A';
        $defaults['uploaded_images_html'] = '';
        $defaults['wow_moments'] = $this->buildCombinedSessionText($dailySessionNotes, 'comments') ?: 'N/A';

        return new DailyReportRenderModel($defaults);
    }

    private function buildCombinedSessionText(array $rows, string $field): string
    {
        if (empty($rows)) {
            return '';
        }

        $sections = [];
        foreach ($rows as $row) {
            $text = $this->normalizeSessionNote((string) ($row[$field] ?? ''));
            if ($text === '') {
                continue;
            }

            $sections[] = $text;
        }

        if (empty($sections)) {
            return '';
        }

        $value = implode("\n", $sections);
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        return $value;
    }

    private function buildReportDateHeader(string $reportDate, array $rows): string
    {
        $header = app_date($reportDate);
        $timeSpans = $this->collectSessionTimeSpans($rows);
        if (empty($timeSpans)) {
            return $header;
        }

        foreach ($timeSpans as $timeSpan) {
            $header .= "\n[" . $timeSpan . ']';
        }

        return $header;
    }

    private function collectSessionTimeSpans(array $rows): array
    {
        $timeSpans = [];
        foreach ($rows as $row) {
            $startTime = trim((string) ($row['start_time'] ?? ''));
            $endTime = trim((string) ($row['end_time'] ?? ''));
            $timeLabel = '';
            if ($startTime !== '' || $endTime !== '') {
                $timeLabel = trim($startTime . ($endTime !== '' ? ' - ' . $endTime : ''));
            }

            if ($timeLabel === '') {
                continue;
            }

            $timeSpans[strtolower($timeLabel)] = $timeLabel;
        }

        return array_values($timeSpans);
    }

    private function formatTutorNamesForReport(string $tutorNames): string
    {
        $parts = array_filter(array_map('trim', explode(',', $tutorNames)), static fn($name) => $name !== '');
        if (empty($parts)) {
            return '';
        }

        return implode("\n", $parts);
    }

    private function compactMultilineText(string $text): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $clean = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $clean[] = $line;
        }

        return implode("\n", $clean);
    }

    private function normalizeSessionNote(string $text): string
    {
        $text = preg_replace('/<br\s*\/?>/i', "\n", $text) ?? $text;
        $text = strip_tags($text);
        if ($text === '') {
            return '';
        }

        $parts = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $parts = array_values(array_filter(array_map('trim', $parts), static fn($p) => $p !== ''));
        if (empty($parts)) {
            return '';
        }

        return trim(implode(' ', $parts));
    }

    private function formatSessionTimeLabel(array $row): string
    {
        $startTime = trim((string) ($row['start_time'] ?? ''));
        $endTime = trim((string) ($row['end_time'] ?? ''));
        if ($startTime === '' && $endTime === '') {
            return '';
        }

        return trim($startTime . ($endTime !== '' ? ' - ' . $endTime : ''));
    }

    private function buildMandsDataSummary(?array $summary): string
    {
        $totalMands = $this->formatMandsMetric($summary['total_mands'] ?? null);
        $variety = $this->formatMandsMetric($summary['variety_of_mands'] ?? null);

        return implode("\n", [
            'Total Mands: ' . $totalMands,
            'Variety: ' . $variety,
            'Rate: ?',
        ]);
    }

    private function formatMandsMetric($value): string
    {
        $text = trim((string) ($value ?? ''));
        return $text === '' ? 'N/A' : $text;
    }

    private function formatDurationFromSeconds(int $seconds): string
    {
        if ($seconds <= 0) {
            return '00:00:00';
        }

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remainingSeconds = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);
    }

    private function buildNetVsDtiText(array $summary): string
    {
        $netPercentage = $summary['net_percentage'] ?? null;
        $dtiPercentage = $summary['dti_percentage'] ?? null;

        if ($netPercentage === null || $dtiPercentage === null) {
            return 'N/A';
        }

        $netRounded = (int) round((float) $netPercentage);
        $netRounded = max(0, min(100, $netRounded));
        $dtiRounded = 100 - $netRounded;

        return sprintf('%d%% vs %d%%', $netRounded, $dtiRounded);
    }

    private function buildProgramProbesTableRows(array $rows): string
    {
        if (empty($rows)) {
            return '<tr><td colspan="4">No processed program probe data.</td></tr>';
        }

        $html = '';
        foreach ($rows as $row) {
            $domainCode = trim((string) ($row['domain_code'] ?? ''));
            $domainName = trim((string) ($row['domain_name'] ?? ''));
            $domainLabel = trim($domainCode . ($domainName !== '' ? ' - ' . $domainName : ''));
            if ($domainLabel === '') {
                $domainLabel = '-';
            }
            $domain = htmlspecialchars($domainLabel, ENT_QUOTES, 'UTF-8');
            $goalCode = trim((string) ($row['goal_code'] ?? ''));
            $goalName = trim((string) ($row['goal_name'] ?? ''));
            $goalLabel = trim($goalCode . ($goalName !== '' ? ' - ' . $goalName : ''));
            if ($goalLabel === '') {
                $goalLabel = '-';
            }
            $goal = htmlspecialchars($goalLabel, ENT_QUOTES, 'UTF-8');
            $target = htmlspecialchars((string) ($row['target_name'] ?? ''), ENT_QUOTES, 'UTF-8');
            $probe = htmlspecialchars($this->formatProbeFromCollectedData($row['collected_data'] ?? null), ENT_QUOTES, 'UTF-8');

            $html .= '<tr>'
                . '<td>' . $domain . '</td>'
                . '<td>' . $goal . '</td>'
                . '<td>' . $target . '</td>'
                . '<td>' . $probe . '</td>'
                . '</tr>';
        }

        return $html;
    }

    private function formatProbeFromCollectedData(?string $collectedDataJson): string
    {
        if ($collectedDataJson === null || trim($collectedDataJson) === '') {
            return '-';
        }

        $data = json_decode($collectedDataJson, true);
        if (!is_array($data)) {
            return '-';
        }

        $inputsType = (string) ($data['inputs']['type'] ?? '');
        $method = strtolower((string) ($data['method'] ?? ''));

        if (in_array($method, ['forward', 'backward'], true)) {
            $probeValue = $data['statistics']['probe_value'] ?? null;
            if ($probeValue !== null && $probeValue !== '') {
                return (string) $probeValue;
            }
        }

        $result = $data['result'] ?? [];
        if (!is_array($result) || empty($result)) {
            return '-';
        }

        $isPercentage = $inputsType === 'percentage_yes_no'
            || ($inputsType === 'stimulus_program' && in_array($method, ['baseline', 'total_task'], true));

        $values = [];
        foreach ($result as $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if ($isPercentage && is_numeric($value)) {
                $values[] = (string) $value . '%';
            } else {
                $values[] = (string) $value;
            }
        }

        return empty($values) ? '-' : implode(', ', $values);
    }

    private function resolveBrandingTokens(): array
    {
        return [
            'report_logo_data_uri' => $this->resolveLogoDataUri(),
            'report_header_line_1' => $this->readSettingOrPlaceholder('Report.headerLine1', ''),
            'report_header_line_2' => $this->readSettingOrPlaceholder('Report.headerLine2', ''),
            'report_header_line_3' => $this->readSettingOrPlaceholder('Report.headerLine3', ''),
            'report_header_line_4' => $this->readSettingOrPlaceholder('Report.headerLine4', ''),
            'report_header_center_caption' => $this->readSettingOrPlaceholder('Report.headerCenterCaption', ''),
            'report_phone' => $this->readSettingOrPlaceholder('Report.phone', 'Missing information: Report phone'),
            'report_website' => $this->readSettingOrPlaceholder('Report.website', 'Missing information: Report website'),
            'report_location_line' => $this->readSettingOrPlaceholder('Report.locationLine', 'Missing information: Report location'),
            'report_footer_company' => $this->readSettingOrPlaceholder('Report.footerCompany', 'Missing information: Report footer company'),
            'report_footer_address_line_1' => $this->readSettingOrPlaceholder('Report.footerAddressLine1', 'Missing information: Report footer address line 1'),
            'report_footer_address_line_2' => $this->readSettingOrPlaceholder('Report.footerAddressLine2', 'Missing information: Report footer address line 2'),
        ];
    }

    private function readSettingOrPlaceholder(string $settingKey, string $placeholder): string
    {
        $value = trim((string) (setting($settingKey) ?? ''));
        return $value !== '' ? $value : $placeholder;
    }

    private function resolveLogoDataUri(): string
    {
        $relativePath = trim((string) (setting('Report.logoPath') ?? ''));
        if ($relativePath === '') {
            return $this->missingLogoDataUri();
        }

        $normalized = str_replace(['\\', '..'], ['/', ''], ltrim($relativePath, '/'));
        $fullPath = WRITEPATH . $normalized;
        if (!is_file($fullPath)) {
            return $this->missingLogoDataUri();
        }

        $ext = strtolower((string) pathinfo($fullPath, PATHINFO_EXTENSION));
        $mimeMap = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
        ];
        if (!isset($mimeMap[$ext])) {
            return $this->missingLogoDataUri();
        }

        $content = file_get_contents($fullPath);
        if ($content === false) {
            return $this->missingLogoDataUri();
        }

        return 'data:' . $mimeMap[$ext] . ';base64,' . base64_encode($content);
    }

    private function missingLogoDataUri(): string
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="220" height="70">'
            . '<rect width="100%" height="100%" fill="#f2f2f2" stroke="#999" />'
            . '<text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#666" font-size="12">Missing logo</text>'
            . '</svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
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
        $reportType = strtolower((string) $template['report_type']);
        $artifactDir = WRITEPATH . 'reports/artifacts/' . $reportType . '/learner/' . $subjectId . '/' . $reportDate . '/v' . $versionNo . '/';
        if (!is_dir($artifactDir)) {
            mkdir($artifactDir, 0775, true);
        }

        $templatePath = WRITEPATH . str_replace('/', DIRECTORY_SEPARATOR, ltrim((string) $template['storage_path'], '/'));
        if (!is_file($templatePath)) {
            throw new RuntimeException('Template file missing at: ' . $templatePath);
        }

        $pdfFileName = sprintf('DailyReport_%d_%s_v%d.pdf', $subjectId, $reportDate, $versionNo);
        $pdfPath = $artifactDir . $pdfFileName;

        $html = $this->htmlTemplateRenderer->render($templatePath, $tokenValues);
        $html = $this->removeHtmlFooterBlock($html);
        $this->htmlToPdfConverter->convert($html, $pdfPath, [
            'left' => (string) ($tokenValues['report_footer_company'] ?? ''),
            'right_line_1' => (string) ($tokenValues['report_footer_address_line_1'] ?? ''),
            'right_line_2' => (string) ($tokenValues['report_footer_address_line_2'] ?? ''),
        ]);

        $relativePath = str_replace('\\', '/', str_replace(WRITEPATH, '', $pdfPath));
        $size = filesize($pdfPath);
        $sha = hash_file('sha256', $pdfPath);

        $this->reportArtifactModel->insert([
            'report_version_id' => $versionId,
            'artifact_type' => 'PDF',
            'storage_driver' => 'LOCAL',
            'storage_path' => $relativePath,
            'file_name' => $pdfFileName,
            'mime_type' => 'application/pdf',
            'file_size' => $size !== false ? $size : null,
            'sha256' => $sha ?: null,
            'created_at' => $now,
            'created_by' => $userId,
        ]);

        return [
            'storage_path' => $relativePath,
            'file_name' => $pdfFileName,
        ];
    }

    private function removeHtmlFooterBlock(string $html): string
    {
        $pattern = '/<div class="footer">[\s\S]*?<\/div>\s*<\/body>/i';
        $replacement = '</body>';
        return preg_replace($pattern, $replacement, $html) ?? $html;
    }

    private function isValidYmd(string $date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }

        $dt = \DateTime::createFromFormat('Y-m-d', $date);
        return $dt && $dt->format('Y-m-d') === $date;
    }
}
