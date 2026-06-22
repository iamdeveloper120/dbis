<?php

namespace App\Services\Reports;

use App\Models\ClientConfiguration\ClientModel;
use App\Models\ClientGraphs\CumulativeGraphsModel;
use App\Models\ClientGraphs\DailyDataGraphsModel;
use App\Models\ClientGraphs\MandsGraphsModel;
use App\Models\Reports\ProgressReportVersionDataModel;
use App\Models\Reports\ReportArtifactModel;
use App\Models\Reports\ReportModel;
use App\Models\Reports\ReportTemplateModel;
use App\Models\Reports\ReportVersionModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use RuntimeException;

class ProgressReportService
{
    private const RAW_HTML_TOKENS = [
        'pm_current_programmes_html',
        'programme_management_comment_block_html',
        'progress_intro_html',
        'progress_cumulative_all_time_graph_html',
        'progress_cumulative_period_graph_html',
        'instructional_domain_blocks_html',
        'instructional_images_html',
        'instructional_domain_graphs_html',
        'instructional_goals_rows_html',
        'manding_graphs_html',
        'manding_section_html',
        'problem_behaviour_graphs_html',
        'problem_behaviour_comment_block_html',
    ];

    private const GRAPH_FALLBACK_COLORS = [
        '#2074ba',
        '#16a34a',
        '#ff9f40',
        '#4bc0c0',
        '#9966ff',
        '#ff6384',
        '#36a2eb',
        '#ffce56',
    ];

    private const INSTRUCTIONAL_IMAGE_ARTIFACT_TYPE = 'PRG_INS_IMAGE';
    private const DEFAULT_PROGRESS_IMAGE_MAX_SIZE_MB = 1;
    private const DEFAULT_PROGRESS_IMAGE_MAX_COUNT = 4;
    private const ABSOLUTE_PROGRESS_IMAGE_MAX_SIZE_MB = 10;
    private const ABSOLUTE_PROGRESS_IMAGE_MAX_COUNT = 20;
    private const INSTRUCTIONAL_IMAGE_ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png'];
    private const INSTRUCTIONAL_IMAGE_ALLOWED_MIME_TYPES = ['image/jpeg', 'image/jpg', 'image/pjpeg', 'image/png'];
    private const REQUIRED_PULL_SECTIONS_FOR_FINALIZE = [
        'current_programme_management',
        'progress',
        'instructional_programmes',
        'manding',
        'problem_behaviour_reduction',
    ];

    private const FINALIZE_SECTION_LABELS = [
        'current_programme_management' => 'Current Program Management',
        'progress' => 'Progress',
        'instructional_programmes' => 'Instructional Programs',
        'manding' => 'Manding',
        'problem_behaviour_reduction' => 'Problem Behaviour Reduction',
    ];

    protected $db;
    protected ClientModel $clientModel;
    protected ReportModel $reportModel;
    protected ReportVersionModel $reportVersionModel;
    protected ReportArtifactModel $reportArtifactModel;
    protected ReportTemplateModel $reportTemplateModel;
    protected ProgressReportVersionDataModel $progressReportVersionDataModel;
    protected CumulativeGraphsModel $cumulativeGraphsModel;
    protected DailyDataGraphsModel $dailyDataGraphsModel;
    protected MandsGraphsModel $mandsGraphsModel;
    protected HtmlToPdfConverter $htmlToPdfConverter;
    protected ?bool $hasProgressVersionDataTable = null;
    protected ?bool $hasClientInformationAgeColumn = null;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->clientModel = new ClientModel();
        $this->reportModel = new ReportModel();
        $this->reportVersionModel = new ReportVersionModel();
        $this->reportArtifactModel = new ReportArtifactModel();
        $this->reportTemplateModel = new ReportTemplateModel();
        $this->progressReportVersionDataModel = new ProgressReportVersionDataModel();
        $this->cumulativeGraphsModel = new CumulativeGraphsModel();
        $this->dailyDataGraphsModel = new DailyDataGraphsModel();
        $this->mandsGraphsModel = new MandsGraphsModel();
        $this->htmlToPdfConverter = new HtmlToPdfConverter();
    }

    public function listBySubject(int $subjectId, ?string $startDate = null, ?string $endDate = null): array
    {
        if ($subjectId <= 0) {
            return ['success' => true, 'data' => [], 'message' => 'No subject selected.'];
        }

        $setup = $this->validateProgressDataSetup();
        if (!$setup['success']) {
            return $setup;
        }

        $builder = $this->db->table('report r');
        $builder->select([
            'r.id AS report_id',
            'r.subject_id',
            'r.period_start',
            'r.period_end',
            'r.latest_version_no',
            'r.created_at',
            'r.updated_at',
            'rv.id AS latest_version_id',
            'rv.generated_at',
            "CONCAT(gen.first_name, ' ', gen.last_name) AS generated_by_name",
            "COALESCE(prvd.workflow_status, 'DRAFT') AS latest_status",
        ]);
        $builder->join('report_version rv', 'rv.report_id = r.id AND rv.version_no = r.latest_version_no', 'left');
        $builder->join('users gen', 'gen.id = rv.generated_by', 'left');
        $builder->join('progress_report_version_data prvd', 'prvd.report_version_id = rv.id', 'left');
        $builder->where('r.report_type', 'PROGRESS');
        $builder->where('r.subject_type', 'LEARNER');
        $builder->where('r.subject_id', $subjectId);
        if (!empty($startDate)) {
            $builder->where('r.period_end >=', $startDate);
        }
        if (!empty($endDate)) {
            $builder->where('r.period_start <=', $endDate);
        }
        $builder->orderBy('r.period_start', 'DESC');
        $builder->orderBy('r.id', 'DESC');

        return ['success' => true, 'data' => $builder->get()->getResultArray(), 'message' => 'Listed successfully.'];
    }

    public function checkGenerateDraft(int $subjectId, string $periodFrom, string $periodTo): array
    {
        $setup = $this->validateProgressDataSetup();
        if (!$setup['success']) {
            return $setup;
        }

        $period = $this->validatePeriod($periodFrom, $periodTo);
        if (!$period['success']) {
            return $period;
        }
        if (!$this->subjectExists($subjectId)) {
            return ['success' => false, 'code' => 'VALIDATION_ERROR', 'message' => 'Invalid client selected.', 'data' => []];
        }
        if ($this->resolveActiveTemplate('PROGRESS') === null) {
            return ['success' => false, 'code' => 'TEMPLATE_SETUP_REQUIRED', 'message' => 'Progress template is missing. Add active PROGRESS template in report_template first.', 'data' => []];
        }

        $existingReport = $this->findReportByExactPeriod($subjectId, $period['period_from'], $period['period_to']);
        if ($existingReport !== null) {
            return [
                'success' => false,
                'code' => 'EXACT_PERIOD_EXISTS',
                'message' => 'A Progress Report already exists for the exact selected period for this client.',
                'data' => [
                    'report_id' => (int) $existingReport['id'],
                    'period_from' => $period['period_from'],
                    'period_to' => $period['period_to'],
                    'latest_version_no' => (int) ($existingReport['latest_version_no'] ?? 0),
                ],
            ];
        }

        $overlap = $this->findOverlappingReport($subjectId, $period['period_from'], $period['period_to']);

        return [
            'success' => true,
            'code' => 'OK',
            'message' => 'Check completed.',
            'data' => [
                'subject_id' => $subjectId,
                'period_from' => $period['period_from'],
                'period_to' => $period['period_to'],
                'overlap_exists' => $overlap !== null,
                'overlap_report_id' => $overlap ? (int) $overlap['id'] : null,
                'overlap_period_from' => $overlap['period_start'] ?? null,
                'overlap_period_to' => $overlap['period_end'] ?? null,
            ],
        ];
    }

    public function createDraft(int $subjectId, string $periodFrom, string $periodTo, ?int $userId): array
    {
        $setup = $this->validateProgressDataSetup();
        if (!$setup['success']) {
            return $setup;
        }

        $period = $this->validatePeriod($periodFrom, $periodTo);
        if (!$period['success']) {
            return $period;
        }
        if (!$this->subjectExists($subjectId)) {
            return ['success' => false, 'code' => 'VALIDATION_ERROR', 'message' => 'Invalid client selected.', 'data' => []];
        }

        $template = $this->resolveActiveTemplate('PROGRESS');
        if ($template === null) {
            return ['success' => false, 'code' => 'TEMPLATE_SETUP_REQUIRED', 'message' => 'Progress template is missing. Add active PROGRESS template in report_template first.', 'data' => []];
        }

        $now = date('Y-m-d H:i:s');
        $this->db->transBegin();
        try {
            $this->lockClientRow($subjectId);

            $existingReport = $this->findReportByExactPeriod($subjectId, $period['period_from'], $period['period_to'], true);
            if ($existingReport !== null) {
                $this->db->transRollback();
                return [
                    'success' => false,
                    'code' => 'EXACT_PERIOD_EXISTS',
                    'message' => 'A Progress Report already exists for the exact selected period for this client.',
                    'data' => [
                        'report_id' => (int) $existingReport['id'],
                        'period_from' => $period['period_from'],
                        'period_to' => $period['period_to'],
                        'latest_version_no' => (int) ($existingReport['latest_version_no'] ?? 0),
                    ],
                ];
            }

            $this->reportModel->insert([
                'report_type' => 'PROGRESS',
                'subject_type' => 'LEARNER',
                'subject_id' => $subjectId,
                'period_type' => 'RANGE',
                'period_start' => $period['period_from'],
                'period_end' => $period['period_to'],
                'period_key' => $this->buildPeriodKey($period['period_from'], $period['period_to']),
                'latest_version_no' => 0,
                'created_at' => $now,
                'created_by' => $userId,
                'updated_at' => $now,
                'updated_by' => $userId,
            ]);
            $reportId = (int) $this->reportModel->getInsertID();
            $latestVersionNo = 0;

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

            $this->progressReportVersionDataModel->insert([
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

            $this->reportModel->update($reportId, ['latest_version_no' => $newVersionNo, 'updated_at' => $now, 'updated_by' => $userId]);
            if (!$this->db->transStatus()) {
                throw new RuntimeException('Failed to create Progress Report draft.');
            }

            $this->db->transCommit();
            return [
                'success' => true,
                'message' => 'Draft created successfully.',
                'data' => [
                    'report_id' => $reportId,
                    'version_id' => $versionId,
                    'version_no' => $newVersionNo,
                    'period_from' => $period['period_from'],
                    'period_to' => $period['period_to'],
                ],
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return ['success' => false, 'code' => 'SERVER_ERROR', 'message' => $e->getMessage(), 'data' => []];
        }
    }

    public function listVersions(int $reportId): array
    {
        $setup = $this->validateProgressDataSetup();
        if (!$setup['success']) {
            return $setup;
        }

        $report = $this->reportModel->where('id', $reportId)->where('report_type', 'PROGRESS')->first();
        if (!$report) {
            return ['success' => false, 'code' => 'NOT_FOUND', 'message' => 'Progress Report not found.', 'data' => []];
        }

        $builder = $this->db->table('report_version rv');
        $builder->select([
            'rv.id AS version_id',
            'rv.version_no',
            'rv.generated_at',
            "CONCAT(gen.first_name, ' ', gen.last_name) AS generated_by_name",
            'ra.id AS artifact_id',
            'ra.file_name',
            "COALESCE(prvd.workflow_status, 'DRAFT') AS status",
        ]);
        $builder->join('users gen', 'gen.id = rv.generated_by', 'left');
        $builder->join('progress_report_version_data prvd', 'prvd.report_version_id = rv.id', 'left');
        $builder->join(
            '(SELECT a.report_version_id, MAX(a.id) AS max_pdf_id FROM report_artifact a WHERE a.artifact_type = "PDF" GROUP BY a.report_version_id) rap',
            'rap.report_version_id = rv.id',
            'left'
        );
        $builder->join('report_artifact ra', 'ra.id = rap.max_pdf_id', 'left');
        $builder->where('rv.report_id', $reportId);
        $builder->orderBy('rv.version_no', 'DESC');

        return ['success' => true, 'data' => $builder->get()->getResultArray(), 'message' => 'Listed successfully.'];
    }

    public function getVersionContext(int $versionId): ?array
    {
        $setup = $this->validateProgressDataSetup();
        if (!$setup['success']) {
            return null;
        }

        return $this->fetchVersionContext($versionId);
    }

    public function getInstructionalImageLimits(): array
    {
        $maxSizeMb = $this->readProgressImageMaxSizeMb();
        $maxCount = $this->readProgressImageMaxCount();

        return [
            'max_size_mb' => $maxSizeMb,
            'max_size_bytes' => $maxSizeMb * 1024 * 1024,
            'max_count' => $maxCount,
            'allowed_extensions' => self::INSTRUCTIONAL_IMAGE_ALLOWED_EXTENSIONS,
        ];
    }

    public function listInstructionalImages(int $versionId): array
    {
        $setup = $this->validateProgressDataSetup();
        if (!$setup['success']) {
            return $setup;
        }
        if ($versionId <= 0) {
            return ['success' => false, 'code' => 'VALIDATION_ERROR', 'message' => 'Invalid version id.', 'data' => []];
        }

        $context = $this->fetchVersionContext($versionId);
        if (!$context) {
            return ['success' => false, 'code' => 'NOT_FOUND', 'message' => 'Progress report version not found.', 'data' => []];
        }

        $artifacts = $this->getInstructionalImageArtifactsByVersion($versionId);

        return [
            'success' => true,
            'message' => 'Instructional images listed successfully.',
            'data' => [
                'version_id' => $versionId,
                'images' => $this->formatInstructionalImagesForResponse($artifacts),
                'limits' => $this->getInstructionalImageLimits(),
            ],
        ];
    }

    public function uploadInstructionalImages(int $versionId, array $files, ?int $userId): array
    {
        $setup = $this->validateProgressDataSetup();
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

        $limits = $this->getInstructionalImageLimits();
        $maxSizeBytes = (int) ($limits['max_size_bytes'] ?? (self::DEFAULT_PROGRESS_IMAGE_MAX_SIZE_MB * 1024 * 1024));
        $maxCount = (int) ($limits['max_count'] ?? self::DEFAULT_PROGRESS_IMAGE_MAX_COUNT);
        $now = date('Y-m-d H:i:s');
        $movedFilePaths = [];

        $this->db->transBegin();
        try {
            $this->lockProgressVersionDataRow($versionId);
            $context = $this->fetchVersionContext($versionId);
            if (!$context) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'NOT_FOUND', 'message' => 'Progress report version not found.', 'data' => []];
            }
            if (!$this->isDraftEditable($context)) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'DRAFT_LOCKED', 'message' => 'This draft is locked or already finalized.', 'data' => []];
            }

            $existingArtifacts = $this->getInstructionalImageArtifactsByVersion($versionId);
            if ((count($existingArtifacts) + count($uploadedFiles)) > $maxCount) {
                $this->db->transRollback();
                return [
                    'success' => false,
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Maximum allowed images for this draft is ' . $maxCount . '.',
                    'data' => ['max_count' => $maxCount, 'current_count' => count($existingArtifacts)],
                ];
            }

            $artifactDir = $this->buildInstructionalImageArtifactDirectory($context);
            if (!is_dir($artifactDir)) {
                mkdir($artifactDir, 0775, true);
            }

            foreach ($uploadedFiles as $file) {
                $validation = $this->validateInstructionalImageUpload($file, $maxSizeBytes);
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
                    'instructional_%d_%s_%s.%s',
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
                    'artifact_type' => self::INSTRUCTIONAL_IMAGE_ARTIFACT_TYPE,
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

            $existingManualData = $this->decodeJsonObject((string) ($context['manual_json'] ?? '{}'));
            $updatedArtifacts = $this->syncInstructionalImagesInManualJson($versionId, $existingManualData, $now, $userId);

            if (!$this->db->transStatus()) {
                throw new RuntimeException('Failed to upload instructional images.');
            }

            $this->db->transCommit();
            return [
                'success' => true,
                'message' => 'Instructional images uploaded successfully.',
                'data' => [
                    'version_id' => $versionId,
                    'images' => $this->formatInstructionalImagesForResponse($updatedArtifacts),
                    'limits' => $limits,
                ],
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            foreach ($movedFilePaths as $path) {
                if (is_file($path)) {
                    @unlink($path);
                }
            }
            return ['success' => false, 'code' => 'SERVER_ERROR', 'message' => $e->getMessage(), 'data' => []];
        }
    }

    public function deleteInstructionalImage(int $versionId, int $artifactId, ?int $userId): array
    {
        $setup = $this->validateProgressDataSetup();
        if (!$setup['success']) {
            return $setup;
        }
        if ($versionId <= 0 || $artifactId <= 0) {
            return ['success' => false, 'code' => 'VALIDATION_ERROR', 'message' => 'Invalid version or image id.', 'data' => []];
        }

        $now = date('Y-m-d H:i:s');
        $deletedStoragePath = '';
        $updatedArtifacts = [];

        $this->db->transBegin();
        try {
            $this->lockProgressVersionDataRow($versionId);
            $context = $this->fetchVersionContext($versionId);
            if (!$context) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'NOT_FOUND', 'message' => 'Progress report version not found.', 'data' => []];
            }
            if (!$this->isDraftEditable($context)) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'DRAFT_LOCKED', 'message' => 'This draft is locked or already finalized.', 'data' => []];
            }

            $artifact = $this->getInstructionalImageArtifactRaw($versionId, $artifactId);
            if (!$artifact) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'NOT_FOUND', 'message' => 'Instructional image not found.', 'data' => []];
            }

            $deletedStoragePath = (string) ($artifact['storage_path'] ?? '');
            $this->reportArtifactModel->where('id', $artifactId)->delete();

            $existingManualData = $this->decodeJsonObject((string) ($context['manual_json'] ?? '{}'));
            $updatedArtifacts = $this->syncInstructionalImagesInManualJson($versionId, $existingManualData, $now, $userId);

            if (!$this->db->transStatus()) {
                throw new RuntimeException('Failed to delete instructional image.');
            }

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return ['success' => false, 'code' => 'SERVER_ERROR', 'message' => $e->getMessage(), 'data' => []];
        }

        $fileDeleteFailures = [];
        if ($deletedStoragePath !== '') {
            $fullPath = $this->resolveLocalArtifactPath($deletedStoragePath);
            if ($fullPath !== null && is_file($fullPath) && !@unlink($fullPath)) {
                $fileDeleteFailures[] = $deletedStoragePath;
            }
        }

        $message = empty($fileDeleteFailures)
            ? 'Instructional image deleted successfully.'
            : 'Instructional image deleted, but file cleanup failed.';

        return [
            'success' => true,
            'message' => $message,
            'data' => [
                'version_id' => $versionId,
                'artifact_id' => $artifactId,
                'images' => $this->formatInstructionalImagesForResponse($updatedArtifacts),
                'file_delete_failures' => $fileDeleteFailures,
                'limits' => $this->getInstructionalImageLimits(),
            ],
        ];
    }

    public function replaceInstructionalImage(int $versionId, int $artifactId, UploadedFile $file, ?int $userId): array
    {
        $setup = $this->validateProgressDataSetup();
        if (!$setup['success']) {
            return $setup;
        }
        if ($versionId <= 0 || $artifactId <= 0) {
            return ['success' => false, 'code' => 'VALIDATION_ERROR', 'message' => 'Invalid version or image id.', 'data' => []];
        }

        $limits = $this->getInstructionalImageLimits();
        $maxSizeBytes = (int) ($limits['max_size_bytes'] ?? (self::DEFAULT_PROGRESS_IMAGE_MAX_SIZE_MB * 1024 * 1024));
        $validation = $this->validateInstructionalImageUpload($file, $maxSizeBytes);
        if (!$validation['success']) {
            return [
                'success' => false,
                'code' => 'VALIDATION_ERROR',
                'message' => (string) ($validation['message'] ?? 'Invalid image file.'),
                'data' => $validation['data'] ?? [],
            ];
        }

        $now = date('Y-m-d H:i:s');
        $oldStoragePath = '';
        $newStoredFilePath = null;
        $updatedArtifacts = [];

        $this->db->transBegin();
        try {
            $this->lockProgressVersionDataRow($versionId);
            $context = $this->fetchVersionContext($versionId);
            if (!$context) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'NOT_FOUND', 'message' => 'Progress report version not found.', 'data' => []];
            }
            if (!$this->isDraftEditable($context)) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'DRAFT_LOCKED', 'message' => 'This draft is locked or already finalized.', 'data' => []];
            }

            $existingArtifact = $this->getInstructionalImageArtifactRaw($versionId, $artifactId);
            if (!$existingArtifact) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'NOT_FOUND', 'message' => 'Instructional image not found.', 'data' => []];
            }
            $oldStoragePath = (string) ($existingArtifact['storage_path'] ?? '');

            $artifactDir = $this->buildInstructionalImageArtifactDirectory($context);
            if (!is_dir($artifactDir)) {
                mkdir($artifactDir, 0775, true);
            }

            $ext = (string) ($validation['data']['extension'] ?? 'jpg');
            $mimeType = (string) ($validation['data']['mime_type'] ?? 'image/jpeg');
            $newFileName = sprintf(
                'instructional_%d_%s_%s.%s',
                $versionId,
                date('Ymd_His'),
                bin2hex(random_bytes(4)),
                $ext
            );

            $file->move($artifactDir, $newFileName, true);
            $newStoredFilePath = $artifactDir . $newFileName;
            if (!is_file($newStoredFilePath)) {
                throw new RuntimeException('Failed to store replacement image.');
            }

            $relativePath = $this->toWriteRelativePath($newStoredFilePath);
            $fileSize = filesize($newStoredFilePath);
            $sha = hash_file('sha256', $newStoredFilePath);

            $this->reportArtifactModel->insert([
                'report_version_id' => $versionId,
                'artifact_type' => self::INSTRUCTIONAL_IMAGE_ARTIFACT_TYPE,
                'storage_driver' => 'LOCAL',
                'storage_path' => $relativePath,
                'file_name' => $newFileName,
                'mime_type' => $mimeType,
                'file_size' => $fileSize !== false ? $fileSize : null,
                'sha256' => $sha ?: null,
                'created_at' => $now,
                'created_by' => $userId,
            ]);

            $this->reportArtifactModel->where('id', $artifactId)->delete();

            $existingManualData = $this->decodeJsonObject((string) ($context['manual_json'] ?? '{}'));
            $updatedArtifacts = $this->syncInstructionalImagesInManualJson($versionId, $existingManualData, $now, $userId);

            if (!$this->db->transStatus()) {
                throw new RuntimeException('Failed to replace instructional image.');
            }

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            if ($newStoredFilePath !== null && is_file($newStoredFilePath)) {
                @unlink($newStoredFilePath);
            }
            return ['success' => false, 'code' => 'SERVER_ERROR', 'message' => $e->getMessage(), 'data' => []];
        }

        $fileDeleteFailures = [];
        if ($oldStoragePath !== '') {
            $fullPath = $this->resolveLocalArtifactPath($oldStoragePath);
            if ($fullPath !== null && is_file($fullPath) && !@unlink($fullPath)) {
                $fileDeleteFailures[] = $oldStoragePath;
            }
        }

        $message = empty($fileDeleteFailures)
            ? 'Instructional image replaced successfully.'
            : 'Instructional image replaced, but old file cleanup failed.';

        return [
            'success' => true,
            'message' => $message,
            'data' => [
                'version_id' => $versionId,
                'artifact_id' => $artifactId,
                'images' => $this->formatInstructionalImagesForResponse($updatedArtifacts),
                'file_delete_failures' => $fileDeleteFailures,
                'limits' => $limits,
            ],
        ];
    }

    public function getInstructionalImageArtifact(int $versionId, int $artifactId): ?array
    {
        if ($versionId <= 0 || $artifactId <= 0) {
            return null;
        }

        return $this->getInstructionalImageArtifactRaw($versionId, $artifactId);
    }

    public function saveDraft(int $versionId, array $manualData, ?int $userId): array
    {
        $setup = $this->validateProgressDataSetup();
        if (!$setup['success']) {
            return $setup;
        }
        if ($versionId <= 0) {
            return ['success' => false, 'code' => 'VALIDATION_ERROR', 'message' => 'Invalid version id.', 'data' => []];
        }

        $now = date('Y-m-d H:i:s');
        $this->db->transBegin();
        try {
            $this->lockProgressVersionDataRow($versionId);
            $context = $this->fetchVersionContext($versionId);
            if (!$context) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'NOT_FOUND', 'message' => 'Progress report version not found.', 'data' => []];
            }
            if (!$this->isDraftEditable($context)) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'DRAFT_LOCKED', 'message' => 'This draft is locked or already finalized.', 'data' => []];
            }

            $existingManualData = $this->decodeJsonObject($context['manual_json'] ?? '{}');
            $mergedManualData = array_replace_recursive($existingManualData, $manualData);

            $this->progressReportVersionDataModel
                ->where('report_version_id', $versionId)
                ->set([
                    'manual_json' => json_encode($mergedManualData, JSON_UNESCAPED_UNICODE),
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
        $setup = $this->validateProgressDataSetup();
        if (!$setup['success']) {
            return $setup;
        }
        if ($versionId <= 0) {
            return ['success' => false, 'code' => 'VALIDATION_ERROR', 'message' => 'Invalid version id.', 'data' => []];
        }

        $sectionKey = trim($sectionKey);
        $allowedSectionKeys = [
            'current_programme_management',
            'progress',
            'instructional_programmes',
            'manding',
            'problem_behaviour_reduction',
        ];
        if ($sectionKey === '' || !in_array($sectionKey, $allowedSectionKeys, true)) {
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
            $this->lockProgressVersionDataRow($versionId);
            $context = $this->fetchVersionContext($versionId);
            if (!$context) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'NOT_FOUND', 'message' => 'Progress report version not found.', 'data' => []];
            }
            if (!$this->isDraftEditable($context)) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'DRAFT_LOCKED', 'message' => 'This draft is locked or already finalized.', 'data' => []];
            }

            $sectionData = $this->buildDummySectionData($context, $sectionKey);

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

            $manualData = $this->decodeJsonObject($context['manual_json'] ?? '{}');
            if (!isset($manualData['pulled_sections']) || !is_array($manualData['pulled_sections'])) {
                $manualData['pulled_sections'] = [];
            }
            $manualData['pulled_sections'][$sectionKey] = [
                'pulled_at' => $now,
                'data' => $sectionData,
            ];

            $this->progressReportVersionDataModel
                ->where('report_version_id', $versionId)
                ->set([
                    'manual_json' => json_encode($manualData, JSON_UNESCAPED_UNICODE),
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

    public function updateSectionState(
        int $versionId,
        string $sectionKey,
        array $sectionData,
        string $pulledAt,
        array $manualPatch,
        ?int $userId
    ): array {
        $setup = $this->validateProgressDataSetup();
        if (!$setup['success']) {
            return $setup;
        }
        if ($versionId <= 0) {
            return ['success' => false, 'code' => 'VALIDATION_ERROR', 'message' => 'Invalid version id.', 'data' => []];
        }

        $sectionKey = trim($sectionKey);
        $allowedSectionKeys = [
            'current_programme_management',
            'progress',
            'instructional_programmes',
            'manding',
            'problem_behaviour_reduction',
        ];
        if ($sectionKey === '' || !in_array($sectionKey, $allowedSectionKeys, true)) {
            return [
                'success' => false,
                'code' => 'VALIDATION_ERROR',
                'message' => 'Invalid section key.',
                'data' => ['section_key' => $sectionKey],
            ];
        }

        $now = date('Y-m-d H:i:s');
        $pulledAt = trim($pulledAt);
        $effectivePulledAt = $now;
        if ($pulledAt !== '') {
            $pulledTs = strtotime($pulledAt);
            if ($pulledTs !== false) {
                $effectivePulledAt = date('Y-m-d H:i:s', $pulledTs);
            }
        }

        $this->db->transBegin();
        try {
            $this->lockProgressVersionDataRow($versionId);
            $context = $this->fetchVersionContext($versionId);
            if (!$context) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'NOT_FOUND', 'message' => 'Progress report version not found.', 'data' => []];
            }
            if (!$this->isDraftEditable($context)) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'DRAFT_LOCKED', 'message' => 'This draft is locked or already finalized.', 'data' => []];
            }

            $snapshot = $this->decodeJsonObject($context['snapshot_json'] ?? '{}');
            if (!isset($snapshot['sections']) || !is_array($snapshot['sections'])) {
                $snapshot['sections'] = [];
            }
            $snapshot['sections'][$sectionKey] = [
                'pulled_at' => $effectivePulledAt,
                'data' => $sectionData,
            ];

            $sectionStatus = $this->decodeJsonObject($context['section_status_json'] ?? '{}');
            if (!isset($sectionStatus['sections']) || !is_array($sectionStatus['sections'])) {
                $sectionStatus['sections'] = [];
            }
            $sectionStatus['sections'][$sectionKey] = [
                'status' => 'PULLED',
                'pulled_at' => $effectivePulledAt,
            ];

            $manualData = $this->decodeJsonObject($context['manual_json'] ?? '{}');
            if (!isset($manualData['pulled_sections']) || !is_array($manualData['pulled_sections'])) {
                $manualData['pulled_sections'] = [];
            }
            $manualData['pulled_sections'][$sectionKey] = [
                'pulled_at' => $effectivePulledAt,
                'data' => $sectionData,
            ];

            if (array_key_exists('instructional_programmes_domain_comments', $manualPatch)) {
                $domainComments = $manualPatch['instructional_programmes_domain_comments'];
                if (!is_array($domainComments)) {
                    $this->db->transRollback();
                    return [
                        'success' => false,
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'Invalid instructional domain comments payload.',
                        'data' => ['field' => 'instructional_programmes_domain_comments'],
                    ];
                }
                $manualData['instructional_programmes_domain_comments'] = $domainComments;
            }

            $this->progressReportVersionDataModel
                ->where('report_version_id', $versionId)
                ->set([
                    'manual_json' => json_encode($manualData, JSON_UNESCAPED_UNICODE),
                    'snapshot_json' => json_encode($snapshot, JSON_UNESCAPED_UNICODE),
                    'section_status_json' => json_encode($sectionStatus, JSON_UNESCAPED_UNICODE),
                    'updated_at' => $now,
                    'updated_by' => $userId,
                ])
                ->update();

            if (!$this->db->transStatus()) {
                throw new RuntimeException('Failed to update section state.');
            }

            $this->db->transCommit();
            return [
                'success' => true,
                'message' => 'Section state updated successfully.',
                'data' => [
                    'version_id' => $versionId,
                    'section_key' => $sectionKey,
                    'pulled_at' => $effectivePulledAt,
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
        $setup = $this->validateProgressDataSetup();
        if (!$setup['success']) {
            return $setup;
        }

        $template = $this->resolveActiveTemplate('PROGRESS');
        if ($template === null) {
            return ['success' => false, 'code' => 'TEMPLATE_SETUP_REQUIRED', 'message' => 'Progress template is missing. Add active PROGRESS template in report_template first.', 'data' => []];
        }

        $templatePath = WRITEPATH . str_replace('/', DIRECTORY_SEPARATOR, ltrim((string) $template['storage_path'], '/'));
        if (!is_file($templatePath)) {
            return [
                'success' => false,
                'code' => 'TEMPLATE_FILE_MISSING',
                'message' => 'Progress template file not found at: ' . $template['storage_path'],
                'data' => ['storage_path' => $template['storage_path']],
            ];
        }

        $now = date('Y-m-d H:i:s');
        $this->db->transBegin();
        try {
            $this->lockProgressVersionDataRow($versionId);
            $context = $this->fetchVersionContext($versionId);
            if (!$context) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'NOT_FOUND', 'message' => 'Progress report version not found.', 'data' => []];
            }
            if (!$this->isDraftEditable($context)) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'DRAFT_LOCKED', 'message' => 'This draft is locked or already finalized.', 'data' => []];
            }

            $manualData = $this->decodeJsonObject($context['manual_json'] ?? '{}');
            if (
                (!isset($manualData['pulled_sections']) || !is_array($manualData['pulled_sections']))
                && trim((string) ($context['snapshot_json'] ?? '')) !== ''
            ) {
                $snapshot = $this->decodeJsonObject($context['snapshot_json'] ?? '{}');
                if (isset($snapshot['sections']) && is_array($snapshot['sections']) && !empty($snapshot['sections'])) {
                    $manualData['pulled_sections'] = $snapshot['sections'];
                }
            }

            $finalizeValidation = $this->validateFinalizeReadiness($manualData);
            if (!$finalizeValidation['success']) {
                $this->db->transRollback();
                return $finalizeValidation;
            }

            $tokenValues = $this->buildProgressTokenValues($context, $manualData);
            $html = $this->renderProgressTemplate($templatePath, $tokenValues);

            $artifact = $this->createProgressArtifact(
                (int) $context['subject_id'],
                (string) $context['period_start'],
                (string) $context['period_end'],
                (int) $context['version_no'],
                $versionId,
                $userId,
                $now,
                $html,
                [
                    'left' => (string) ($tokenValues['report_footer_company'] ?? ''),
                    'right_line_1' => (string) ($tokenValues['report_footer_address_line_1'] ?? ''),
                    'right_line_2' => (string) ($tokenValues['report_footer_address_line_2'] ?? ''),
                ]
            );

            $this->progressReportVersionDataModel
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
            return ['success' => true, 'message' => 'Draft finalized successfully.', 'data' => ['version_id' => $versionId, 'storage_path' => $artifact['storage_path'], 'file_name' => $artifact['file_name']]];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return ['success' => false, 'code' => 'SERVER_ERROR', 'message' => $e->getMessage(), 'data' => []];
        }
    }

    public function regenerateDraft(int $versionId, ?int $userId): array
    {
        $setup = $this->validateProgressDataSetup();
        if (!$setup['success']) {
            return $setup;
        }

        $source = $this->fetchVersionContext($versionId);
        if (!$source) {
            return ['success' => false, 'code' => 'NOT_FOUND', 'message' => 'Progress report version not found.', 'data' => []];
        }
        if (strtoupper((string) ($source['workflow_status'] ?? 'DRAFT')) !== 'FINAL') {
            return ['success' => false, 'code' => 'NOT_FINAL', 'message' => 'Only finalized versions can be regenerated.', 'data' => []];
        }
        $template = $this->resolveActiveTemplate('PROGRESS');
        if ($template === null) {
            return ['success' => false, 'code' => 'TEMPLATE_SETUP_REQUIRED', 'message' => 'Progress template is missing. Add active PROGRESS template in report_template first.', 'data' => []];
        }

        $subjectId = (int) $source['subject_id'];
        $reportId = (int) $source['report_id'];
        $now = date('Y-m-d H:i:s');

        $this->db->transBegin();
        try {
            $this->lockClientRow($subjectId);

            $report = $this->db->query('SELECT id, latest_version_no FROM report WHERE id = ? AND report_type = "PROGRESS" AND subject_type = "LEARNER" LIMIT 1 FOR UPDATE', [$reportId])->getRowArray();
            if (!$report) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'NOT_FOUND', 'message' => 'Progress report not found.', 'data' => []];
            }

            $newVersionNo = ((int) $report['latest_version_no']) + 1;
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

            $this->progressReportVersionDataModel->insert([
                'report_version_id' => $newVersionId,
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

            $this->reportModel->update($reportId, ['latest_version_no' => $newVersionNo, 'updated_at' => $now, 'updated_by' => $userId]);
            if (!$this->db->transStatus()) {
                throw new RuntimeException('Failed to regenerate draft.');
            }

            $this->db->transCommit();
            return ['success' => true, 'message' => 'Draft regenerated successfully.', 'data' => ['report_id' => $reportId, 'version_id' => $newVersionId, 'version_no' => $newVersionNo]];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return ['success' => false, 'code' => 'SERVER_ERROR', 'message' => $e->getMessage(), 'data' => []];
        }
    }

    public function deleteLatestVersion(int $versionId, ?int $userId): array
    {
        $setup = $this->validateProgressDataSetup();
        if (!$setup['success']) {
            return $setup;
        }
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
                   AND r.report_type = "PROGRESS"
                   AND r.subject_type = "LEARNER"
                 LIMIT 1 FOR UPDATE',
                [$versionId]
            )->getRowArray();

            if (!$version) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'NOT_FOUND', 'message' => 'Progress report version not found.', 'data' => []];
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
                throw new RuntimeException('Failed to delete progress report version.');
            }

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return ['success' => false, 'code' => 'SERVER_ERROR', 'message' => $e->getMessage(), 'data' => []];
        }

        $fileDeleteFailures = $this->deleteArtifactFiles($artifacts);
        $message = empty($fileDeleteFailures)
            ? 'Progress report version deleted successfully.'
            : 'Progress report version deleted, but some media files could not be removed.';

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
        $setup = $this->validateProgressDataSetup();
        if (!$setup['success']) {
            return $setup;
        }
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
                   AND report_type = "PROGRESS"
                   AND subject_type = "LEARNER"
                 LIMIT 1 FOR UPDATE',
                [$reportId]
            )->getRowArray();

            if (!$report) {
                $this->db->transRollback();
                return ['success' => false, 'code' => 'NOT_FOUND', 'message' => 'Progress report not found.', 'data' => []];
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
                $this->reportArtifactModel->whereIn('report_version_id', $versionIds)->delete();
                $this->db->table('report_version')->where('report_id', $reportId)->delete();
            }

            $this->reportModel->delete($reportId);
            if (!$this->db->transStatus()) {
                throw new RuntimeException('Failed to delete progress report.');
            }

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return ['success' => false, 'code' => 'SERVER_ERROR', 'message' => $e->getMessage(), 'data' => []];
        }

        $fileDeleteFailures = $this->deleteArtifactFiles($artifacts);
        $message = empty($fileDeleteFailures)
            ? 'Progress report deleted successfully.'
            : 'Progress report deleted, but some media files could not be removed.';

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

    public function getLatestPdfArtifactByVersion(int $versionId): ?array
    {
        return $this->db->table('report_artifact')
            ->where('report_version_id', $versionId)
            ->where('artifact_type', 'PDF')
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();
    }

    private function readProgressImageMaxSizeMb(): int
    {
        $stored = setting('Report.progressImageMaxSizeMb');
        $value = is_numeric($stored) ? (int) $stored : self::DEFAULT_PROGRESS_IMAGE_MAX_SIZE_MB;
        if ($value < 1) {
            $value = self::DEFAULT_PROGRESS_IMAGE_MAX_SIZE_MB;
        }
        return min($value, self::ABSOLUTE_PROGRESS_IMAGE_MAX_SIZE_MB);
    }

    private function readProgressImageMaxCount(): int
    {
        $stored = setting('Report.progressImageMaxCount');
        $value = is_numeric($stored) ? (int) $stored : self::DEFAULT_PROGRESS_IMAGE_MAX_COUNT;
        if ($value < 1) {
            $value = self::DEFAULT_PROGRESS_IMAGE_MAX_COUNT;
        }
        return min($value, self::ABSOLUTE_PROGRESS_IMAGE_MAX_COUNT);
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

    private function validateInstructionalImageUpload(UploadedFile $file, int $maxSizeBytes): array
    {
        if (!$file->isValid() || $file->hasMoved()) {
            return [
                'success' => false,
                'message' => 'Invalid uploaded file.',
                'data' => ['error' => $file->getErrorString()],
            ];
        }

        $extension = strtolower(trim((string) ($file->getClientExtension() ?: $file->getExtension())));
        if ($extension === '' || !in_array($extension, self::INSTRUCTIONAL_IMAGE_ALLOWED_EXTENSIONS, true)) {
            return [
                'success' => false,
                'message' => 'Only JPG, JPEG, and PNG files are allowed.',
                'data' => ['allowed_extensions' => self::INSTRUCTIONAL_IMAGE_ALLOWED_EXTENSIONS],
            ];
        }

        $mimeType = strtolower(trim((string) $file->getMimeType()));
        if ($mimeType === '' || !in_array($mimeType, self::INSTRUCTIONAL_IMAGE_ALLOWED_MIME_TYPES, true)) {
            return [
                'success' => false,
                'message' => 'Only JPG, JPEG, and PNG image types are allowed.',
                'data' => ['allowed_mime_types' => self::INSTRUCTIONAL_IMAGE_ALLOWED_MIME_TYPES],
            ];
        }

        $size = (int) $file->getSize();
        if ($size <= 0 || $size > $maxSizeBytes) {
            return [
                'success' => false,
                'message' => 'Image size exceeds the allowed limit.',
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

    private function buildInstructionalImageArtifactDirectory(array $context): string
    {
        $subjectId = (int) ($context['subject_id'] ?? 0);
        $versionNo = (int) ($context['version_no'] ?? 0);
        $periodFrom = trim((string) ($context['period_start'] ?? ''));
        $periodTo = trim((string) ($context['period_end'] ?? ''));
        $periodKey = $this->buildPeriodKey($periodFrom, $periodTo);

        return WRITEPATH
            . 'reports/artifacts/progress/learner/'
            . $subjectId
            . '/'
            . $periodKey
            . '/v'
            . $versionNo
            . '/instructional-images/';
    }

    private function toWriteRelativePath(string $fullPath): string
    {
        $writeRoot = rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR;
        $relative = str_replace($writeRoot, '', $fullPath);
        return ltrim(str_replace('\\', '/', $relative), '/');
    }

    private function getInstructionalImageArtifactsByVersion(int $versionId): array
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
            ->where('artifact_type', self::INSTRUCTIONAL_IMAGE_ARTIFACT_TYPE)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function getInstructionalImageArtifactRaw(int $versionId, int $artifactId): ?array
    {
        $row = $this->db->table('report_artifact')
            ->where('id', $artifactId)
            ->where('report_version_id', $versionId)
            ->where('artifact_type', self::INSTRUCTIONAL_IMAGE_ARTIFACT_TYPE)
            ->get()
            ->getRowArray();

        return is_array($row) ? $row : null;
    }

    private function formatInstructionalImagesForResponse(array $artifacts): array
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

    private function mapInstructionalImagesForManualJson(array $artifacts): array
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

    private function syncInstructionalImagesInManualJson(int $versionId, array $manualData, string $now, ?int $userId): array
    {
        $artifacts = $this->getInstructionalImageArtifactsByVersion($versionId);
        $manualData['instructional_programmes_images'] = $this->mapInstructionalImagesForManualJson($artifacts);

        $this->progressReportVersionDataModel
            ->where('report_version_id', $versionId)
            ->set([
                'manual_json' => json_encode($manualData, JSON_UNESCAPED_UNICODE),
                'updated_at' => $now,
                'updated_by' => $userId,
            ])
            ->update();

        return $artifacts;
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

        $fullPath = rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $normalized);
        return $fullPath;
    }

    protected function resolveActiveTemplate(string $reportType): ?array
    {
        return $this->reportTemplateModel
            ->where('report_type', $reportType)
            ->where('is_active', 1)
            ->orderBy('version_no', 'DESC')
            ->first();
    }

    private function fetchVersionContext(int $versionId): ?array
    {
        $hasClientInformationTable = $this->db->tableExists('client_information');
        $hasClientAge = $this->clientInformationHasAgeColumn();

        $builder = $this->db->table('report_version rv');
        $select = [
            'rv.id AS version_id',
            'rv.report_id',
            'rv.version_no',
            'rv.template_id',
            'rv.generated_at',
            'rv.generated_by',
            'r.subject_id',
            'r.period_start',
            'r.period_end',
            'r.period_key',
            "CONCAT(c.first_name, ' ', c.last_name) AS learner_name",
            "TRIM(CONCAT(COALESCE(gen.first_name, ''), ' ', COALESCE(gen.last_name, ''))) AS reported_by_name",
            'prvd.workflow_status',
            'prvd.is_locked',
            'prvd.manual_json',
            'prvd.snapshot_json',
            'prvd.section_status_json',
            'prvd.finalized_at',
            'prvd.finalized_by',
        ];

        if ($hasClientInformationTable) {
            $select[] = 'ci.date_of_birth AS client_date_of_birth';
            $select[] = $hasClientAge ? 'ci.age AS client_age' : 'NULL AS client_age';
        } else {
            $select[] = 'NULL AS client_date_of_birth';
            $select[] = 'NULL AS client_age';
        }

        $builder->select($select);
        $builder->join('report r', 'r.id = rv.report_id', 'inner');
        $builder->join('clients c', 'c.id = r.subject_id', 'left');
        $builder->join('users gen', 'gen.id = rv.generated_by', 'left');
        if ($hasClientInformationTable) {
            $builder->join('client_information ci', 'ci.client_id = r.subject_id', 'left');
        }
        $builder->join('progress_report_version_data prvd', 'prvd.report_version_id = rv.id', 'left');
        $builder->where('rv.id', $versionId);
        $builder->where('r.report_type', 'PROGRESS');
        $builder->where('r.subject_type', 'LEARNER');
        return $builder->get()->getRowArray();
    }

    private function validatePeriod(string $periodFrom, string $periodTo): array
    {
        $periodFrom = trim($periodFrom);
        $periodTo = trim($periodTo);
        if (!$this->isValidYmd($periodFrom) || !$this->isValidYmd($periodTo)) {
            return ['success' => false, 'code' => 'VALIDATION_ERROR', 'message' => 'period_from and period_to must be valid dates (YYYY-MM-DD).', 'data' => []];
        }
        if ($periodFrom > $periodTo) {
            return ['success' => false, 'code' => 'VALIDATION_ERROR', 'message' => 'period_from must be less than or equal to period_to.', 'data' => []];
        }
        return ['success' => true, 'period_from' => $periodFrom, 'period_to' => $periodTo];
    }

    private function isValidYmd(string $date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }
        $dt = \DateTime::createFromFormat('Y-m-d', $date);
        return $dt && $dt->format('Y-m-d') === $date;
    }

    private function subjectExists(int $subjectId): bool
    {
        return $subjectId > 0 && $this->clientModel->where('id', $subjectId)->countAllResults() > 0;
    }

    private function findReportByExactPeriod(int $subjectId, string $periodFrom, string $periodTo, bool $forUpdate = false): ?array
    {
        $sql = 'SELECT id, latest_version_no FROM report
                WHERE report_type = "PROGRESS"
                  AND subject_type = "LEARNER"
                  AND subject_id = ?
                  AND period_start = ?
                  AND period_end = ?
                LIMIT 1';
        if ($forUpdate) {
            $sql .= ' FOR UPDATE';
        }
        return $this->db->query($sql, [$subjectId, $periodFrom, $periodTo])->getRowArray();
    }

    private function findOverlappingReport(int $subjectId, string $periodFrom, string $periodTo): ?array
    {
        $sql = 'SELECT id, period_start, period_end FROM report
                WHERE report_type = "PROGRESS"
                  AND subject_type = "LEARNER"
                  AND subject_id = ?
                  AND NOT (period_end < ? OR period_start > ?)
                ORDER BY period_start ASC
                LIMIT 1';
        return $this->db->query($sql, [$subjectId, $periodFrom, $periodTo])->getRowArray();
    }

    private function findActiveDraft(int $subjectId): ?array
    {
        $sql = 'SELECT r.id AS report_id, r.period_start, r.period_end, rv.id AS version_id, rv.version_no
                FROM report r
                INNER JOIN report_version rv
                    ON rv.report_id = r.id
                   AND rv.version_no = r.latest_version_no
                INNER JOIN progress_report_version_data prvd
                    ON prvd.report_version_id = rv.id
                WHERE r.report_type = "PROGRESS"
                  AND r.subject_type = "LEARNER"
                  AND r.subject_id = ?
                  AND prvd.workflow_status = "DRAFT"
                  AND prvd.is_locked = 0
                ORDER BY rv.id DESC
                LIMIT 1';
        return $this->db->query($sql, [$subjectId])->getRowArray();
    }

    private function lockClientRow(int $subjectId): void
    {
        $this->db->query('SELECT id FROM clients WHERE id = ? LIMIT 1 FOR UPDATE', [$subjectId]);
    }

    private function lockProgressVersionDataRow(int $versionId): void
    {
        $this->db->query('SELECT id FROM progress_report_version_data WHERE report_version_id = ? LIMIT 1 FOR UPDATE', [$versionId]);
    }

    private function buildPeriodKey(string $periodFrom, string $periodTo): string
    {
        return $periodFrom . '_' . $periodTo;
    }

    private function buildDummySectionData(array $context, string $sectionKey): array
    {
        return match ($sectionKey) {
            'current_programme_management' => $this->buildCurrentProgrammeManagementSectionData($context),
            'progress' => $this->buildProgressSectionData($context),
            'instructional_programmes' => $this->buildInstructionalSectionData($context),
            'manding' => $this->buildMandingSectionData($context),
            'problem_behaviour_reduction' => $this->buildProblemBehaviourSectionData($context),
            default => [],
        };
    }

    private function buildCurrentProgrammeManagementSectionData(array $context): array
    {
        $subjectId = (int) ($context['subject_id'] ?? 0);
        $periodStart = trim((string) ($context['period_start'] ?? ''));
        $periodEnd = trim((string) ($context['period_end'] ?? ''));

        if ($subjectId <= 0 || !$this->isValidYmd($periodStart) || !$this->isValidYmd($periodEnd)) {
            return [
                'pm.sessions_count' => 'N/A',
                'pm.hours_of_instruction' => 'N/A',
                'pm.dti_net_ratio' => 'N/A',
                'pm.schedule_of_reinforcement' => 'N/A',
                'pm.current_programmes' => 'N/A',
            ];
        }

        try {
            $sessionCount = $this->countProcessedSessionsInPeriod($subjectId, $periodStart, $periodEnd);
            $netVsDtiSummary = $this->getNetVsDtiSummaryByPeriod($subjectId, $periodStart, $periodEnd);
            $hoursOfInstruction = $this->formatHoursFromSeconds((int) ($netVsDtiSummary['teaching_seconds'] ?? 0));
            $schedule = $this->getScheduleOfReinforcement($subjectId);
            $currentProgrammes = $this->getCurrentProgrammesSummaryByEndDate($subjectId, $periodEnd, $periodStart);

            return [
                'pm.sessions_count' => (string) $sessionCount,
                'pm.hours_of_instruction' => $hoursOfInstruction,
                'pm.dti_net_ratio' => $this->buildNetVsDtiTextFromSummary($netVsDtiSummary),
                'pm.schedule_of_reinforcement' => $schedule !== '' ? $schedule : 'N/A',
                'pm.current_programmes' => $currentProgrammes !== '' ? $currentProgrammes : 'N/A',
            ];
        } catch (\Throwable $e) {
            return [
                'pm.sessions_count' => 'N/A',
                'pm.hours_of_instruction' => 'N/A',
                'pm.dti_net_ratio' => 'N/A',
                'pm.schedule_of_reinforcement' => 'N/A',
                'pm.current_programmes' => 'N/A',
            ];
        }
    }

    private function countProcessedSessionsInPeriod(int $subjectId, string $periodStart, string $periodEnd): int
    {
        return (int) ($this->db->table('daily_sessions ds')
            ->where('ds.client_id', $subjectId)
            ->where('DATE(ds.session_date) >= ' . $this->db->escape($periodStart), null, false)
            ->where('DATE(ds.session_date) <= ' . $this->db->escape($periodEnd), null, false)
            ->whereIn('ds.status', [3, 4])
            ->countAllResults());
    }

    private function getNetVsDtiSummaryByPeriod(int $subjectId, string $periodStart, string $periodEnd): array
    {
        $totalSession = (int) ($this->db->table('daily_sessions ds')
            ->select('SUM(CASE WHEN ds.start_time IS NOT NULL AND ds.end_time IS NOT NULL THEN TIMESTAMPDIFF(SECOND, ds.start_time, ds.end_time) ELSE 0 END) AS seconds', false)
            ->where('ds.client_id', $subjectId)
            ->where('DATE(ds.session_date) >= ' . $this->db->escape($periodStart), null, false)
            ->where('DATE(ds.session_date) <= ' . $this->db->escape($periodEnd), null, false)
            ->whereIn('ds.status', [3, 4])
            ->get()
            ->getRowArray()['seconds'] ?? 0);

        $teaching = (int) ($this->db->table('daily_sessions_teaching_duration d')
            ->select('SUM(CASE WHEN d.start_time IS NOT NULL AND d.end_time IS NOT NULL THEN TIMESTAMPDIFF(SECOND, d.start_time, d.end_time) ELSE 0 END) AS seconds', false)
            ->join('daily_sessions ds', 'ds.id = d.session_id', 'inner')
            ->where('d.client_id', $subjectId)
            ->where('DATE(d.session_date) >= ' . $this->db->escape($periodStart), null, false)
            ->where('DATE(d.session_date) <= ' . $this->db->escape($periodEnd), null, false)
            ->whereIn('ds.status', [3, 4])
            ->get()
            ->getRowArray()['seconds'] ?? 0);

        $mands = (int) ($this->db->table('daily_sessions_mands_duration d')
            ->select('SUM(CASE WHEN d.start_time IS NOT NULL AND d.end_time IS NOT NULL THEN TIMESTAMPDIFF(SECOND, d.start_time, d.end_time) ELSE 0 END) AS seconds', false)
            ->join('daily_sessions ds', 'ds.id = d.session_id', 'inner')
            ->where('d.client_id', $subjectId)
            ->where('DATE(d.session_date) >= ' . $this->db->escape($periodStart), null, false)
            ->where('DATE(d.session_date) <= ' . $this->db->escape($periodEnd), null, false)
            ->whereIn('ds.status', [3, 4])
            ->get()
            ->getRowArray()['seconds'] ?? 0);

        $pb = (int) ($this->db->table('daily_sessions_pb_duration d')
            ->select('SUM(CASE WHEN d.start_time IS NOT NULL AND d.end_time IS NOT NULL THEN TIMESTAMPDIFF(SECOND, d.start_time, d.end_time) ELSE 0 END) AS seconds', false)
            ->join('daily_sessions ds', 'ds.id = d.session_id', 'inner')
            ->where('d.client_id', $subjectId)
            ->where('DATE(d.session_date) >= ' . $this->db->escape($periodStart), null, false)
            ->where('DATE(d.session_date) <= ' . $this->db->escape($periodEnd), null, false)
            ->whereIn('ds.status', [3, 4])
            ->get()
            ->getRowArray()['seconds'] ?? 0);

        $net = max(0, $mands);
        $dti = max(0, $teaching - $mands);
        $netPercentage = $teaching > 0 ? round(($net / $teaching) * 100, 2) : null;
        $dtiPercentage = $teaching > 0 ? round(($dti / $teaching) * 100, 2) : null;

        return [
            'total_session_seconds' => $totalSession,
            'teaching_seconds' => $teaching,
            'mands_seconds' => $mands,
            'pb_seconds' => $pb,
            'net_seconds' => $net,
            'dti_seconds' => $dti,
            'net_percentage' => $netPercentage,
            'dti_percentage' => $dtiPercentage,
        ];
    }

    private function buildNetVsDtiTextFromSummary(array $summary): string
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

    private function formatHoursFromSeconds(int $seconds): string
    {
        if ($seconds <= 0) {
            return '0.0 hours';
        }

        return number_format($seconds / 3600, 1, '.', '') . ' hours';
    }

    private function getScheduleOfReinforcement(int $subjectId): string
    {
        if (!$this->db->tableExists('client_effective_teaching_procedures')) {
            return '';
        }

        $row = $this->db->table('client_effective_teaching_procedures')
            ->select('schedule_of_reinforcement')
            ->where('client_id', $subjectId)
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        return trim((string) ($row['schedule_of_reinforcement'] ?? ''));
    }

    private function getCurrentProgrammesSummaryByEndDate(int $subjectId, string $periodEnd, ?string $periodStart = null): string
    {
        $periodStart = is_string($periodStart) ? trim($periodStart) : '';
        if (!$this->isValidYmd($periodStart)) {
            $periodStart = $periodEnd;
        }

        $sql = 'WITH latest_dates AS (
                    SELECT target_id, MAX(session_date) AS latest_session_date
                    FROM daily_session_data_processed
                    WHERE client_id = ?
                      AND session_date <= ?
                    GROUP BY target_id
                ),
                latest_processed AS (
                    SELECT dp.target_id, MAX(dp.id) AS latest_id
                    FROM daily_session_data_processed dp
                    INNER JOIN latest_dates ld
                        ON ld.target_id = dp.target_id
                       AND ld.latest_session_date = dp.session_date
                    WHERE dp.client_id = ?
                    GROUP BY dp.target_id
                ),
                active_goal_scope AS (
                    SELECT DISTINCT
                        d.domain_code,
                        d.name AS domain_name,
                        g.goal_code,
                        g.name AS goal_name
                    FROM latest_processed lp
                    INNER JOIN daily_session_data_processed dp
                        ON dp.id = lp.latest_id
                    INNER JOIN client_program_targets t
                        ON t.id = dp.target_id
                    INNER JOIN client_program_goals g
                        ON g.id = t.goal_id
                       AND g.client_id = dp.client_id
                    INNER JOIN client_program_domains d
                        ON d.id = g.domain_id
                       AND d.client_id = dp.client_id
                    WHERE dp.next_phase_id != 4
                ),
                retained_goal_scope AS (
                    SELECT DISTINCT
                        d.domain_code,
                        d.name AS domain_name,
                        g.goal_code,
                        g.name AS goal_name
                    FROM client_program_targets_retained r
                    INNER JOIN client_program_goals g
                        ON g.id = r.goal_id
                       AND g.client_id = r.client_id
                    INNER JOIN client_program_domains d
                        ON d.id = r.domain_id
                       AND d.client_id = r.client_id
                    WHERE r.client_id = ?
                      AND r.session_date >= ?
                      AND r.session_date <= ?
                ),
                combined_goal_scope AS (
                    SELECT domain_code, domain_name, goal_code, goal_name
                    FROM active_goal_scope
                    UNION
                    SELECT domain_code, domain_name, goal_code, goal_name
                    FROM retained_goal_scope
                )
                SELECT
                    d.domain_code,
                    d.domain_name,
                    d.goal_code,
                    d.goal_name
                FROM combined_goal_scope d
                ORDER BY
                    CAST(COALESCE(NULLIF(REGEXP_SUBSTR(d.domain_code, "[0-9]+"), ""), "0") AS UNSIGNED),
                    d.domain_code,
                    CAST(COALESCE(NULLIF(REGEXP_SUBSTR(d.goal_code, "[0-9]+"), ""), "0") AS UNSIGNED),
                    d.goal_code';

        $rows = $this->db->query(
            $sql,
            [$subjectId, $periodEnd, $subjectId, $subjectId, $periodStart, $periodEnd]
        )->getResultArray();
        if (empty($rows)) {
            return 'None';
        }

        $domainGoals = [];
        foreach ($rows as $row) {
            $domainCode = trim((string) ($row['domain_code'] ?? ''));
            $domainName = trim((string) ($row['domain_name'] ?? ''));
            $goalCode = trim((string) ($row['goal_code'] ?? ''));
            $goalName = trim((string) ($row['goal_name'] ?? ''));

            $domainLabel = $domainName !== '' ? $domainName : 'N/A';

            $goalLabel = trim($goalCode . ($goalName !== '' ? ' - ' . $goalName : ''));
            if ($goalLabel === '') {
                $goalLabel = 'N/A';
            }

            if (!isset($domainGoals[$domainLabel])) {
                $domainGoals[$domainLabel] = [];
            }
            $domainGoals[$domainLabel][$goalLabel] = true;
        }

        $lines = [];
        foreach ($domainGoals as $domainLabel => $goalsMap) {
            $goals = array_keys($goalsMap);
            $lines[] = $domainLabel . ': ' . implode(', ', $goals);
        }

        return implode("\n", $lines);
    }

    private function buildProblemBehaviourSectionData(array $context): array
    {
        $subjectId = (int) ($context['subject_id'] ?? 0);
        $periodStart = trim((string) ($context['period_start'] ?? ''));
        $periodEnd = trim((string) ($context['period_end'] ?? ''));

        if ($subjectId <= 0 || !$this->isValidYmd($periodStart) || !$this->isValidYmd($periodEnd)) {
            return ['problem_behaviour.graphs' => []];
        }

        try {
            $rawGraphs = $this->dailyDataGraphsModel->get_client_session_data_for_graphs($subjectId, $periodStart, $periodEnd);
            if (!is_array($rawGraphs) || empty($rawGraphs)) {
                return ['problem_behaviour.graphs' => []];
            }

            $definitions = [
                [
                    'source_key' => 'frequency_of_problem_behavior',
                    'key' => 'pb_frequency',
                    'title' => 'Frequency of Problem Behaviour',
                    'y_axis_label' => 'Frequency of Problem Behaviour',
                ],
                [
                    'source_key' => 'total_duration_of_problem_behavior',
                    'key' => 'pb_duration',
                    'title' => 'Duration of Problem Behaviour',
                    'y_axis_label' => 'Duration of Problem Behaviour (Minutes)',
                ],
            ];

            $graphs = [];
            foreach ($definitions as $definition) {
                $sourceKey = $definition['source_key'];
                $payload = $this->normalizeProblemBehaviourGraphPayload(
                    is_array($rawGraphs[$sourceKey] ?? null) ? $rawGraphs[$sourceKey] : null,
                    $definition['y_axis_label']
                );
                if ($payload === null) {
                    continue;
                }

                $graphs[] = [
                    'key' => $definition['key'],
                    'title' => $definition['title'],
                    'graph' => $payload,
                ];
            }

            return ['problem_behaviour.graphs' => $graphs];
        } catch (\Throwable $e) {
            return ['problem_behaviour.graphs' => []];
        }
    }

    private function normalizeProblemBehaviourGraphPayload(?array $rawGraph, string $yAxisLabel): ?array
    {
        if ($rawGraph === null) {
            return null;
        }

        $labels = $rawGraph['labels'] ?? null;
        $datasets = $rawGraph['datasets'] ?? null;
        if (!is_array($labels) || !is_array($datasets) || empty($labels) || empty($datasets)) {
            return null;
        }

        $normalizedLabels = array_values(array_map(
            static fn($label): string => trim((string) $label),
            $labels
        ));

        $normalizedDatasets = [];
        foreach ($datasets as $dataset) {
            if (!is_array($dataset)) {
                continue;
            }

            $series = $dataset['data'] ?? null;
            if (!is_array($series)) {
                continue;
            }

            $normalized = $dataset;
            $normalized['data'] = array_values($series);
            $normalizedDatasets[] = $normalized;
        }

        if (empty($normalizedDatasets)) {
            return null;
        }

        return [
            'chart_type' => 'line',
            'labels' => $normalizedLabels,
            'datasets' => $normalizedDatasets,
            'options' => [
                'y_axis_label' => $yAxisLabel,
                'x_axis_label' => 'Dates',
            ],
        ];
    }

    private function buildMandingSectionData(array $context): array
    {
        $subjectId = (int) ($context['subject_id'] ?? 0);
        $periodStart = trim((string) ($context['period_start'] ?? ''));
        $periodEnd = trim((string) ($context['period_end'] ?? ''));

        if ($subjectId <= 0 || !$this->isValidYmd($periodStart) || !$this->isValidYmd($periodEnd)) {
            return ['manding.graphs' => []];
        }

        $definitions = [
            ['key' => 'total_mands', 'title' => 'Total Mands', 'y_axis_label' => 'Total number of mands'],
            ['key' => 'variety_of_mands', 'title' => 'Variety of Mands', 'y_axis_label' => 'Variety of Mands'],
            ['key' => 'prompt_level_data', 'title' => 'Prompt Level', 'y_axis_label' => 'Number of mands by prompt level'],
            ['key' => 'mand_errors_data', 'title' => 'Mand Errors', 'y_axis_label' => '% of mand errors'],
            ['key' => 'vocal_response_data', 'title' => 'Vocal Response', 'y_axis_label' => '% of vocalisations across vocal categories'],
            ['key' => 'prompt_delay_trial_data', 'title' => 'Prompt Delay', 'y_axis_label' => '% of Change - Prompt Delay'],
            ['key' => 'echoic_trial_data', 'title' => 'Echoic Trial', 'y_axis_label' => '% of Change - Echoic Trial'],
            ['key' => 'peer_mands_data', 'title' => 'Peer Mands', 'y_axis_label' => 'Total number of peer mands'],
            ['key' => 'eye_contact_mands_data', 'title' => 'Eye Contact', 'y_axis_label' => 'Total number of eye contact mands'],
        ];

        try {
            $rawGraphs = $this->mandsGraphsModel->getMandsSummaryDataForGraphs($subjectId, $periodStart, $periodEnd);
            if (!is_array($rawGraphs) || empty($rawGraphs)) {
                return ['manding.graphs' => []];
            }

            $graphs = [];
            foreach ($definitions as $definition) {
                $key = $definition['key'];
                $payload = $this->normalizeMandingGraphPayload(
                    is_array($rawGraphs[$key] ?? null) ? $rawGraphs[$key] : null,
                    $definition['y_axis_label']
                );
                if ($payload === null) {
                    continue;
                }
                if (!$this->graphPayloadHasAnyNonZeroValue($payload)) {
                    continue;
                }

                $graphs[] = [
                    'key' => $key,
                    'title' => $definition['title'],
                    'graph' => $payload,
                ];
            }

            return ['manding.graphs' => $graphs];
        } catch (\Throwable $e) {
            return ['manding.graphs' => []];
        }
    }

    private function normalizeMandingGraphPayload(?array $rawGraph, string $yAxisLabel): ?array
    {
        if ($rawGraph === null) {
            return null;
        }

        $labels = $rawGraph['labels'] ?? null;
        $datasets = $rawGraph['datasets'] ?? null;
        if (!is_array($labels) || !is_array($datasets) || empty($labels) || empty($datasets)) {
            return null;
        }

        $normalizedLabels = array_values(array_map(
            static fn($label): string => trim((string) $label),
            $labels
        ));

        $normalizedDatasets = [];
        foreach ($datasets as $dataset) {
            if (!is_array($dataset)) {
                continue;
            }

            $series = $dataset['data'] ?? null;
            if (!is_array($series)) {
                continue;
            }

            $normalized = $dataset;
            $normalized['data'] = array_values($series);
            $normalizedDatasets[] = $normalized;
        }

        if (empty($normalizedDatasets)) {
            return null;
        }

        return [
            'chart_type' => 'line',
            'labels' => $normalizedLabels,
            'datasets' => $normalizedDatasets,
            'options' => [
                'y_axis_label' => $yAxisLabel,
                'x_axis_label' => 'Dates',
            ],
        ];
    }

    private function graphPayloadHasAnyNonZeroValue(array $payload): bool
    {
        $datasets = $payload['datasets'] ?? null;
        if (!is_array($datasets) || empty($datasets)) {
            return false;
        }

        foreach ($datasets as $dataset) {
            if (!is_array($dataset)) {
                continue;
            }

            $series = $dataset['data'] ?? null;
            if (!is_array($series)) {
                continue;
            }

            foreach ($series as $point) {
                if ($point === null) {
                    continue;
                }

                if (is_string($point) && trim($point) === '') {
                    continue;
                }

                if (is_numeric($point)) {
                    if ((float) $point != 0.0) {
                        return true;
                    }
                    continue;
                }

                $text = strtolower(trim((string) $point));
                if ($text !== '' && $text !== 'null') {
                    return true;
                }
            }
        }

        return false;
    }

    private function buildDummySeries(
        int $count,
        float $start,
        float $deltaA,
        float $deltaB,
        float $min = 0,
        ?float $max = null
    ): array {
        $values = [];
        $current = $start;
        for ($i = 0; $i < $count; $i++) {
            if ($current < $min) {
                $current = $min;
            }
            if ($max !== null && $current > $max) {
                $current = $max;
            }
            $values[] = round($current, 2);
            $current += ($i % 2 === 0) ? $deltaA : $deltaB;
        }

        return $values;
    }

    private function buildInstructionalSectionData(array $context): array
    {
        $subjectId = (int) ($context['subject_id'] ?? 0);
        $periodStart = trim((string) ($context['period_start'] ?? ''));
        $periodEnd = trim((string) ($context['period_end'] ?? ''));

        if ($subjectId <= 0 || !$this->isValidYmd($periodStart) || !$this->isValidYmd($periodEnd)) {
            return ['instructional.domains' => []];
        }

        try {
            $domains = $this->getInstructionalDomainsActiveByEndDate($subjectId, $periodStart, $periodEnd);
            if (empty($domains)) {
                return ['instructional.domains' => []];
            }

            $result = [];
            foreach ($domains as $domain) {
                $domainId = (int) ($domain['domain_id'] ?? 0);
                if ($domainId <= 0) {
                    continue;
                }

                $domainCode = trim((string) ($domain['domain_code'] ?? ''));
                $domainName = trim((string) ($domain['domain_name'] ?? ''));
                $domainTitle = trim($domainCode . ($domainName !== '' ? ' - ' . $domainName : ''));
                if ($domainTitle === '') {
                    $domainTitle = $domainName !== '' ? $domainName : ('Domain ' . $domainId);
                }

                $result[] = [
                    'key' => 'd' . $domainId,
                    'title' => $domainTitle,
                    'period_graph' => $this->buildInstructionalDomainPeriodGraphPayload(
                        $subjectId,
                        $domainId,
                        $periodStart,
                        $periodEnd,
                        $domainCode
                    ),
                    'goals' => $this->getInstructionalGoalMasteredTargetsByDomainAndPeriod(
                        $subjectId,
                        $domainId,
                        $periodStart,
                        $periodEnd,
                        $domainCode
                    ),
                    'domain_total_target_count' => 0,
                ];

                $lastIndex = array_key_last($result);
                if ($lastIndex !== null) {
                    $domainTotal = 0;
                    $goals = $result[$lastIndex]['goals'] ?? [];
                    if (is_array($goals)) {
                        foreach ($goals as $goalItem) {
                            $domainTotal += (int) ($goalItem['goal_target_count'] ?? 0);
                        }
                    }
                    $result[$lastIndex]['domain_total_target_count'] = max(0, $domainTotal);
                }
            }

            return ['instructional.domains' => $result];
        } catch (\Throwable $e) {
            return ['instructional.domains' => []];
        }
    }

    private function getInstructionalDomainsActiveByEndDate(int $subjectId, string $periodStart, string $periodEnd): array
    {
        $sql = 'WITH latest_dates AS (
                    SELECT target_id, MAX(session_date) AS latest_session_date
                    FROM daily_session_data_processed
                    WHERE client_id = ?
                      AND session_date <= ?
                    GROUP BY target_id
                ),
                latest_processed AS (
                    SELECT dp.target_id, MAX(dp.id) AS latest_id
                    FROM daily_session_data_processed dp
                    INNER JOIN latest_dates ld
                        ON ld.target_id = dp.target_id
                       AND ld.latest_session_date = dp.session_date
                    WHERE dp.client_id = ?
                    GROUP BY dp.target_id
                ),
                active_domains AS (
                    SELECT DISTINCT
                        d.id AS domain_id,
                        d.domain_code,
                        d.name AS domain_name
                    FROM latest_processed lp
                    INNER JOIN daily_session_data_processed dp
                        ON dp.id = lp.latest_id
                    INNER JOIN client_program_targets t
                        ON t.id = dp.target_id
                    INNER JOIN client_program_goals g
                        ON g.id = t.goal_id
                       AND g.client_id = dp.client_id
                    INNER JOIN client_program_domains d
                        ON d.id = g.domain_id
                       AND d.client_id = dp.client_id
                    WHERE dp.next_phase_id != 4
                ),
                retained_domains AS (
                    SELECT DISTINCT
                        d.id AS domain_id,
                        d.domain_code,
                        d.name AS domain_name
                    FROM client_program_targets_retained r
                    INNER JOIN client_program_domains d
                        ON d.id = r.domain_id
                       AND d.client_id = r.client_id
                    WHERE r.client_id = ?
                      AND r.session_date >= ?
                      AND r.session_date <= ?
                ),
                combined_domains AS (
                    SELECT domain_id, domain_code, domain_name
                    FROM active_domains
                    UNION
                    SELECT domain_id, domain_code, domain_name
                    FROM retained_domains
                )
                SELECT
                    d.domain_id,
                    d.domain_code,
                    d.domain_name
                FROM combined_domains d
                ORDER BY
                    CAST(COALESCE(NULLIF(REGEXP_SUBSTR(d.domain_code, "[0-9]+"), ""), "0") AS UNSIGNED),
                    d.domain_code,
                    d.domain_name';

        return $this->db->query(
            $sql,
            [$subjectId, $periodEnd, $subjectId, $subjectId, $periodStart, $periodEnd]
        )->getResultArray();
    }

    private function buildInstructionalDomainPeriodGraphPayload(
        int $subjectId,
        int $domainId,
        string $periodStart,
        string $periodEnd,
        string $domainCode = ''
    ): ?array {
        $baseline = $this->getInstructionalDomainBaselineCounts($subjectId, $domainId, $periodStart);

        $skillsDaily = $this->db->table('client_program_targets_retained')
            ->select('session_date, COUNT(*) AS count')
            ->where('client_id', $subjectId)
            ->where('domain_id', $domainId)
            ->where('session_date >=', $periodStart)
            ->where('session_date <=', $periodEnd)
            ->groupBy('session_date')
            ->orderBy('session_date', 'ASC')
            ->get()
            ->getResultArray();

        $doiDaily = $this->db->table('client_program_targets_doi')
            ->select('session_date, COUNT(*) AS count')
            ->where('client_id', $subjectId)
            ->where('domain_id', $domainId)
            ->where('session_date >=', $periodStart)
            ->where('session_date <=', $periodEnd)
            ->groupBy('session_date')
            ->orderBy('session_date', 'ASC')
            ->get()
            ->getResultArray();

        $skillsWeekly = $this->groupDailyCountsByWeekEnd($skillsDaily);
        $doiWeekly = $this->groupDailyCountsByWeekEnd($doiDaily);
        $periodWeekEnds = $this->buildWeekEndBucketsForPeriod($periodStart, $periodEnd);
        $graphData = $this->buildCumulativeSkillsDoiGraphData(
            $skillsWeekly,
            $doiWeekly,
            (int) ($baseline['skills'] ?? 0),
            (int) ($baseline['doi'] ?? 0),
            $periodWeekEnds
        );
        if ($graphData === null) {
            return null;
        }

        return [
            'chart_type' => 'line',
            'labels' => $graphData['labels'],
            'datasets' => $graphData['datasets'],
            'options' => [
                'y_axis_label' => 'Cumulative Skills Retained Across ' . ($domainCode !== '' ? $domainCode : 'Domain'),
                'x_axis_label' => 'Week Ending',
            ],
        ];
    }

    private function getInstructionalDomainBaselineCounts(int $subjectId, int $domainId, string $periodStart): array
    {
        $skills = (int) ($this->db->table('client_program_targets_retained')
            ->select('COUNT(*) AS total', false)
            ->where('client_id', $subjectId)
            ->where('domain_id', $domainId)
            ->where('session_date <', $periodStart)
            ->get()
            ->getRowArray()['total'] ?? 0);

        $doi = (int) ($this->db->table('client_program_targets_doi')
            ->select('COUNT(*) AS total', false)
            ->where('client_id', $subjectId)
            ->where('domain_id', $domainId)
            ->where('session_date <', $periodStart)
            ->get()
            ->getRowArray()['total'] ?? 0);

        return [
            'skills' => max(0, $skills),
            'doi' => max(0, $doi),
        ];
    }

    private function buildWeekEndBucketsForPeriod(string $periodStart, string $periodEnd): array
    {
        if (!$this->isValidYmd($periodStart) || !$this->isValidYmd($periodEnd)) {
            return [];
        }

        try {
            $start = new \DateTimeImmutable($periodStart);
            $end = new \DateTimeImmutable($periodEnd);
        } catch (\Throwable $e) {
            return [];
        }

        if ($start > $end) {
            return [];
        }

        $weekEnds = [];
        $cursor = $start;
        while ($cursor <= $end) {
            $date = $cursor->format('Y-m-d');
            $weekEnd = function_exists('get_week_end_date')
                ? get_week_end_date($date, 'Y-m-d')
                : $date;
            $weekEnds[$weekEnd] = true;
            $cursor = $cursor->modify('+1 day');
        }

        ksort($weekEnds);
        return array_keys($weekEnds);
    }

    private function groupDailyCountsByWeekEnd(array $rows): array
    {
        $weekly = [];
        foreach ($rows as $row) {
            $sessionDate = trim((string) ($row['session_date'] ?? ''));
            if (!$this->isValidYmd($sessionDate)) {
                continue;
            }

            $weekEnd = function_exists('get_week_end_date')
                ? get_week_end_date($sessionDate, 'Y-m-d')
                : $sessionDate;
            $count = (int) ($row['count'] ?? 0);
            if (!isset($weekly[$weekEnd])) {
                $weekly[$weekEnd] = 0;
            }
            $weekly[$weekEnd] += max(0, $count);
        }

        ksort($weekly);
        return $weekly;
    }

    private function buildCumulativeSkillsDoiGraphData(
        array $skillsWeekly,
        array $doiWeekly,
        int $skillsBaseline = 0,
        int $doiBaseline = 0,
        array $periodWeekEnds = []
    ): ?array
    {
        $allWeekEnds = array_values(array_unique(array_merge(
            $periodWeekEnds,
            array_keys($skillsWeekly),
            array_keys($doiWeekly)
        )));
        sort($allWeekEnds);

        if (empty($allWeekEnds)) {
            return null;
        }

        $labels = [];
        $skillsData = [];
        $doiData = [];
        $skillsCumulative = max(0, $skillsBaseline);
        $doiCumulative = max(0, $doiBaseline);
        $skillsStarted = $skillsCumulative > 0;
        $doiStarted = $doiCumulative > 0;

        foreach ($allWeekEnds as $weekEnd) {
            if (isset($skillsWeekly[$weekEnd])) {
                $skillsCumulative += (int) $skillsWeekly[$weekEnd];
            }
            if (isset($doiWeekly[$weekEnd])) {
                $doiCumulative += (int) $doiWeekly[$weekEnd];
            }

            if ($skillsCumulative > 0) {
                $skillsStarted = true;
            }
            if ($doiCumulative > 0) {
                $doiStarted = true;
            }

            $labels[] = stringToDate($weekEnd, CC_DATE_FORMAT);
            $skillsData[] = $skillsStarted ? $skillsCumulative : null;
            $doiData[] = $doiStarted ? $doiCumulative : null;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Skills Retained',
                    'data' => $skillsData,
                    'lineTension' => 0,
                    'backgroundColor' => 'rgba(255, 255, 255, 0.0)',
                    'borderColor' => 'rgba(0, 0, 0, 1)',
                    'pointBorderColor' => 'rgba(0, 0, 0, 1)',
                    'pointBackgroundColor' => 'rgba(0, 0, 0, 1)',
                    'pointBorderWidth' => 2,
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Degrees Of Independence',
                    'data' => $doiData,
                    'lineTension' => 0,
                    'backgroundColor' => 'rgba(255, 255, 255, 0.0)',
                    'borderColor' => 'rgba(32, 116, 186, 1)',
                    'pointBorderColor' => 'rgba(32, 116, 186, 1)',
                    'pointBackgroundColor' => 'rgba(32, 116, 186, 1)',
                    'pointBorderWidth' => 2,
                    'borderWidth' => 2,
                ],
            ],
        ];
    }

    private function getInstructionalGoalMasteredTargetsByDomainAndPeriod(
        int $subjectId,
        int $domainId,
        string $periodStart,
        string $periodEnd,
        string $domainCode = ''
    ): array {
        $rows = $this->db->table('client_program_targets_retained r')
            ->select([
                'g.id AS goal_id',
                'g.goal_code',
                'g.name AS goal_name',
                't.id AS target_id',
                't.name AS target_name',
            ])
            ->join('client_program_goals g', 'g.id = r.goal_id AND g.client_id = r.client_id', 'inner')
            ->join('client_program_targets t', 't.id = r.target_id AND t.client_id = r.client_id', 'inner')
            ->where('r.client_id', $subjectId)
            ->where('r.domain_id', $domainId)
            ->where('r.session_date >=', $periodStart)
            ->where('r.session_date <=', $periodEnd)
            ->orderBy('g.goal_code', 'ASC')
            ->orderBy('g.id', 'ASC')
            ->orderBy('t.name', 'ASC')
            ->orderBy('t.id', 'ASC')
            ->get()
            ->getResultArray();

        if (empty($rows)) {
            return [];
        }

        $goalMap = [];
        foreach ($rows as $row) {
            $goalId = (int) ($row['goal_id'] ?? 0);
            if ($goalId <= 0) {
                continue;
            }

            $goalCode = trim((string) ($row['goal_code'] ?? ''));
            $goalName = trim((string) ($row['goal_name'] ?? ''));
            $goalCore = '';
            if ($goalCode !== '' && $goalName !== '') {
                $goalCore = $goalCode . '-' . $goalName;
            } elseif ($goalCode !== '') {
                $goalCore = $goalCode;
            } elseif ($goalName !== '') {
                $goalCore = $goalName;
            } else {
                $goalCore = 'Goal ' . $goalId;
            }
            $goalLabel = trim(($domainCode !== '' ? ($domainCode . ' ') : '') . $goalCore);

            if (!isset($goalMap[$goalId])) {
                $goalMap[$goalId] = [
                    'goal_name' => $goalLabel,
                    'targets_mastered' => [],
                    'goal_target_count' => 0,
                    '_seen' => [],
                ];
            }

            $targetId = (int) ($row['target_id'] ?? 0);
            $targetName = trim((string) ($row['target_name'] ?? ''));
            $targetLabel = $targetName !== '' ? $targetName : ($targetId > 0 ? ('Target ' . $targetId) : 'N/A');
            $targetKey = $targetId > 0 ? (string) $targetId : ('name:' . strtolower($targetLabel));
            if (isset($goalMap[$goalId]['_seen'][$targetKey])) {
                continue;
            }

            $goalMap[$goalId]['_seen'][$targetKey] = true;
            $goalMap[$goalId]['targets_mastered'][] = $targetLabel;
            $goalMap[$goalId]['goal_target_count'] = count($goalMap[$goalId]['targets_mastered']);
        }

        $result = [];
        foreach ($goalMap as $goal) {
            unset($goal['_seen']);
            $result[] = $goal;
        }

        return $result;
    }

    private function buildProgressSectionData(array $context): array
    {
        $subjectId = (int) ($context['subject_id'] ?? 0);
        $periodStart = trim((string) ($context['period_start'] ?? ''));
        $periodEnd = trim((string) ($context['period_end'] ?? ''));

        if ($subjectId <= 0 || !$this->isValidYmd($periodStart) || !$this->isValidYmd($periodEnd)) {
            return [
                'progress.program_start_date_text' => 'N/A',
                'progress.cumulative_all_time_graph' => null,
                'progress.cumulative_period_graph' => null,
            ];
        }

        try {
            $allTime = $this->cumulativeGraphsModel->get_cumulative_data($subjectId, null, null);
            $period = $this->cumulativeGraphsModel->get_cumulative_data($subjectId, $periodStart, $periodEnd);

            return [
                'progress.program_start_date_text' => $this->resolveCumulativeProgramStartDateText($subjectId),
                'progress.cumulative_all_time_graph' => $this->mapCumulativeGraphPayload($allTime),
                'progress.cumulative_period_graph' => $this->mapCumulativeGraphPayload($period),
            ];
        } catch (\Throwable $e) {
            return [
                'progress.program_start_date_text' => 'N/A',
                'progress.cumulative_all_time_graph' => null,
                'progress.cumulative_period_graph' => null,
            ];
        }
    }

    private function resolveCumulativeProgramStartDateText(int $subjectId): string
    {
        try {
            $row = $this->db->query(
                'SELECT MIN(week_date) AS first_week_date FROM view_cumulative_graph_data WHERE client_id = ?',
                [$subjectId]
            )->getRowArray();
        } catch (\Throwable $e) {
            return 'N/A';
        }

        $firstWeekDate = trim((string) ($row['first_week_date'] ?? ''));
        if ($firstWeekDate === '') {
            return 'N/A';
        }

        $display = $this->formatDateForDisplay($firstWeekDate, false);
        return $display !== '' ? $display : $firstWeekDate;
    }

    private function mapCumulativeGraphPayload($result): ?array
    {
        if (!is_array($result)) {
            return null;
        }

        $graphData = $result['graph_data'] ?? null;
        if (!is_array($graphData)) {
            return null;
        }

        $labels = $graphData['labels'] ?? null;
        $datasets = $graphData['datasets'] ?? null;
        if (!is_array($labels) || !is_array($datasets) || empty($labels) || empty($datasets)) {
            return null;
        }

        $normalizedLabels = [];
        foreach ($labels as $label) {
            $labelText = trim((string) $label);
            $normalizedLabels[] = $labelText !== '' ? $labelText : '-';
        }

        $normalizedDatasets = [];
        foreach ($datasets as $dataset) {
            if (!is_array($dataset)) {
                continue;
            }
            $normalizedDatasets[] = $dataset;
        }
        if (empty($normalizedDatasets)) {
            return null;
        }

        $phaseLines = [];
        if (isset($result['phaseline']) && is_array($result['phaseline'])) {
            foreach ($result['phaseline'] as $annotation) {
                if (is_array($annotation)) {
                    $phaseLines[] = $annotation;
                }
            }
        }

        return [
            'chart_type' => 'line',
            'labels' => array_values($normalizedLabels),
            'datasets' => array_values($normalizedDatasets),
            'phaseline' => array_values($phaseLines),
            'options' => [
                'y_axis_label' => 'Cumulative Skills Retained Across All Domains',
                'x_axis_label' => 'Week Ending',
            ],
        ];
    }

    private function buildDummyProgressGraphPayload(array $labels, int $skillsStart, int $doiStart): array
    {
        $skills = [];
        $doi = [];
        $skillsValue = $skillsStart;
        $doiValue = $doiStart;

        foreach ($labels as $index => $label) {
            $skills[] = $skillsValue;
            $doi[] = $doiValue;
            $skillsValue += ($index % 2 === 0) ? 2 : 1;
            $doiValue += ($index % 3 === 0) ? 2 : 1;
        }

        return [
            'chart_type' => 'line',
            'labels' => array_values($labels),
            'datasets' => [
                [
                    'label' => 'Skills Retained',
                    'data' => $skills,
                    'borderColor' => '#000000',
                    'backgroundColor' => '#000000',
                    'fill' => false,
                    'lineTension' => 0,
                    'pointRadius' => 3,
                    'pointHoverRadius' => 4,
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Degrees of Independence',
                    'data' => $doi,
                    'borderColor' => '#1f78ff',
                    'backgroundColor' => '#1f78ff',
                    'fill' => false,
                    'lineTension' => 0,
                    'pointRadius' => 3,
                    'pointHoverRadius' => 4,
                    'borderWidth' => 2,
                ],
            ],
            'options' => [
                'y_axis_label' => 'Cumulative Skills Retained Across All Domains',
                'x_axis_label' => 'Week Ending',
            ],
        ];
    }

    private function buildWeeklyLabels(string $startDate, string $endDate, int $maxPoints = 8): array
    {
        $startTs = strtotime($startDate);
        $endTs = strtotime($endDate);
        if ($startTs === false || $endTs === false || $endTs < $startTs) {
            return [];
        }

        $labels = [];
        $current = $startTs;
        $count = 0;
        while ($current <= $endTs && $count < $maxPoints) {
            $labels[] = date('d-M-y', $current);
            $current = strtotime('+7 days', $current);
            $count++;
        }

        $endLabel = date('d-M-y', $endTs);
        if (empty($labels) || end($labels) !== $endLabel) {
            $labels[] = $endLabel;
        }

        return $labels;
    }

    private function progressVersionDataTableExists(): bool
    {
        if ($this->hasProgressVersionDataTable === null) {
            $this->hasProgressVersionDataTable = $this->db->tableExists('progress_report_version_data');
        }
        return $this->hasProgressVersionDataTable;
    }

    private function clientInformationHasAgeColumn(): bool
    {
        if ($this->hasClientInformationAgeColumn === null) {
            $this->hasClientInformationAgeColumn = $this->db->tableExists('client_information')
                && $this->db->fieldExists('age', 'client_information');
        }

        return $this->hasClientInformationAgeColumn;
    }

    private function validateProgressDataSetup(): array
    {
        if (!$this->progressVersionDataTableExists()) {
            return ['success' => false, 'code' => 'DB_SETUP_REQUIRED', 'message' => 'Database setup required. Run database/progress_report_version_data_manual.sql first.', 'data' => []];
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
            if (!$this->db->fieldExists($column, 'progress_report_version_data')) {
                return [
                    'success' => false,
                    'code' => 'DB_SETUP_REQUIRED',
                    'message' => 'Database setup is outdated. Re-run database/progress_report_version_data_manual.sql with latest schema.',
                    'data' => ['missing_column' => $column],
                ];
            }
        }

        return ['success' => true];
    }

    private function validateFinalizeReadiness(array $manualData): array
    {
        $missingRequirements = [];

        $pulledSections = $manualData['pulled_sections'] ?? [];
        if (!is_array($pulledSections)) {
            $pulledSections = [];
        }

        foreach (self::REQUIRED_PULL_SECTIONS_FOR_FINALIZE as $sectionKey) {
            $sectionLabel = self::FINALIZE_SECTION_LABELS[$sectionKey] ?? ucwords(str_replace('_', ' ', $sectionKey));
            $section = $pulledSections[$sectionKey] ?? null;
            if (!is_array($section)) {
                $missingRequirements[] = 'Please pull section: ' . $sectionLabel . '.';
                continue;
            }

            $data = $section['data'] ?? $section;
            if (!is_array($data)) {
                $missingRequirements[] = 'Pulled data is missing for section: ' . $sectionLabel . '.';
            }
        }

        $approvedBy = trim((string) ($manualData['approved_by'] ?? ''));
        if ($approvedBy === '') {
            $missingRequirements[] = 'Approved By is required.';
        }

        $conclusionComment = trim((string) ($manualData['conclusion_comment'] ?? ($manualData['draft_notes'] ?? '')));
        if ($conclusionComment === '') {
            $missingRequirements[] = 'Conclusion comment is required.';
        }

        if (!empty($missingRequirements)) {
            return [
                'success' => false,
                'code' => 'FINALIZE_VALIDATION_ERROR',
                'message' => 'Finalize validation failed. Complete required fields and pull all sections before generating PDF.',
                'data' => [
                    'missing_requirements' => $missingRequirements,
                    'required_sections' => array_map(
                        static fn(string $key): string => self::FINALIZE_SECTION_LABELS[$key] ?? ucwords(str_replace('_', ' ', $key)),
                        self::REQUIRED_PULL_SECTIONS_FOR_FINALIZE
                    ),
                ],
            ];
        }

        return ['success' => true];
    }

    private function isDraftEditable(array $context): bool
    {
        return strtoupper((string) ($context['workflow_status'] ?? 'DRAFT')) === 'DRAFT'
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

    private function buildProgressTokenValues(array $context, array $manualData): array
    {
        $pulledSections = $manualData['pulled_sections'] ?? [];
        if (!is_array($pulledSections)) {
            $pulledSections = [];
        }

        $getPulledData = static function (array $sections, string $sectionKey): array {
            $section = $sections[$sectionKey] ?? [];
            if (!is_array($section)) {
                return [];
            }
            if (isset($section['data']) && is_array($section['data'])) {
                return $section['data'];
            }
            return $section;
        };

        $pmData = $getPulledData($pulledSections, 'current_programme_management');
        $progressData = $getPulledData($pulledSections, 'progress');
        $instructionalData = $getPulledData($pulledSections, 'instructional_programmes');
        $mandingData = $getPulledData($pulledSections, 'manding');
        $problemBehaviourData = $getPulledData($pulledSections, 'problem_behaviour_reduction');

        $periodStartRaw = trim((string) ($context['period_start'] ?? ''));
        $periodEndRaw = trim((string) ($context['period_end'] ?? ''));
        $periodStartDisplay = $this->formatDateForDisplay($periodStartRaw, false);
        $periodEndDisplay = $this->formatDateForDisplay($periodEndRaw, false);
        $periodLabel = trim($periodStartDisplay . ' to ' . $periodEndDisplay);
        if ($periodLabel === 'to' || $periodLabel === '') {
            $periodLabel = trim($periodStartRaw . ' to ' . $periodEndRaw);
        }

        $generatedAtRaw = trim((string) ($context['generated_at'] ?? ''));
        $dateOfReportDisplay = $this->formatDateForDisplay($generatedAtRaw, false);
        if ($dateOfReportDisplay === '') {
            $dateOfReportDisplay = date('Y-m-d');
        }

        $clientDobRaw = trim((string) ($context['client_date_of_birth'] ?? ''));
        $dateOfBirthDisplay = $clientDobRaw === '' ? 'N/A' : $this->displayValueOrNa($this->formatDateForDisplay($clientDobRaw, false));
        $ageAtEndOfPeriodDisplay = $this->buildAgeAtDateText(
            $clientDobRaw,
            $generatedAtRaw !== '' ? $generatedAtRaw : $periodEndRaw,
            $context['client_age'] ?? null
        );

        $instructionalDomains = $instructionalData['instructional.domains'] ?? [];
        if (!is_array($instructionalDomains)) {
            $instructionalDomains = [];
        }
        $instructionalDomainComments = $manualData['instructional_programmes_domain_comments'] ?? [];
        if (!is_array($instructionalDomainComments)) {
            $instructionalDomainComments = [];
        }

        $instructionalDomainTitles = [];
        foreach ($instructionalDomains as $domain) {
            if (!is_array($domain)) {
                continue;
            }
            $domainTitle = trim((string) ($domain['title'] ?? ''));
            if ($domainTitle !== '') {
                $instructionalDomainTitles[] = $domainTitle;
            }
        }

        $pmCurrentProgrammesRaw = trim((string) ($pmData['pm.current_programmes'] ?? ''));
        $progressProgramStartDate = $this->displayValueOrNa(trim((string) ($progressData['progress.program_start_date_text'] ?? '')));
        $progressLegendBlackUri = $this->resolvePublicImageDataUri('assets/images/legend-black.png');
        $progressLegendBlueUri = $this->resolvePublicImageDataUri('assets/images/legend-blue.png');
        $instructionalDomainBlocksHtml = $this->buildInstructionalDomainBlocksPdfHtml(
            $instructionalDomains,
            $instructionalDomainComments,
            $progressLegendBlackUri,
            $progressLegendBlueUri
        );
        $instructionalImagesHtml = $this->buildInstructionalImagesHtml(
            (int) ($context['version_id'] ?? 0),
            $manualData['instructional_programmes_images'] ?? null
        );

        $mandingGraphs = is_array($mandingData['manding.graphs'] ?? null) ? $mandingData['manding.graphs'] : [];
        $problemBehaviourGraphs = is_array($problemBehaviourData['problem_behaviour.graphs'] ?? null)
            ? $problemBehaviourData['problem_behaviour.graphs']
            : [];

        $programmeManagementComment = (string) ($manualData['programme_management_comment'] ?? '');
        $mandingComment = (string) ($manualData['manding_comment'] ?? '');
        $problemBehaviourComment = (string) ($manualData['problem_behaviour_reduction_comment'] ?? '');
        $conclusionComment = (string) ($manualData['conclusion_comment'] ?? ($manualData['draft_notes'] ?? ''));

        $headerLine1 = $this->readReportSetting('Report.headerLine1');
        $headerLine2 = $this->readReportSetting('Report.headerLine2');
        $headerLine3 = $this->readReportSetting('Report.headerLine3');
        $headerLine4 = $this->readReportSetting('Report.headerLine4');
        $headerCenterCaption = preg_replace('/\s+/', ' ', $this->readReportSetting('Report.headerCenterCaption')) ?? '';
        $reportPhone = $this->readReportSetting('Report.phone');
        $reportWebsite = $this->readReportSetting('Report.website');
        $reportLocationLine = $this->readReportSetting('Report.locationLine');
        $footerCompany = $this->readReportSetting('Report.footerCompany');
        $footerAddressLine1 = $this->readReportSetting('Report.footerAddressLine1');
        $footerAddressLine2 = $this->readReportSetting('Report.footerAddressLine2');

        $learnerNameForDisplay = $this->displayValueOrNa(trim((string) ($context['learner_name'] ?? '')));
        $learnerFirstNameForIntro = $this->extractFirstName($learnerNameForDisplay, 'Learner');

        return [
            'report_header_line_1' => $headerLine1,
            'report_header_line_2' => $headerLine2,
            'report_header_line_3' => $headerLine3,
            'report_header_line_4' => $headerLine4,
            'report_header_center_caption' => $headerCenterCaption,
            'report_phone' => $reportPhone,
            'report_website' => $reportWebsite,
            'report_location_line' => $reportLocationLine,
            'report_logo_data_uri' => $this->resolveReportLogoDataUri(),
            'report_footer_company' => $footerCompany,
            'report_footer_address_line_1' => $footerAddressLine1,
            'report_footer_address_line_2' => $footerAddressLine2,
            'learner_name' => $learnerNameForDisplay,
            'date_of_birth' => $dateOfBirthDisplay,
            'age_at_end_of_period' => $ageAtEndOfPeriodDisplay,
            'date_of_report' => $this->displayValueOrNa($dateOfReportDisplay),
            'period_label' => $this->displayValueOrNa($periodLabel),
            'period_start_display' => $this->displayValueOrNa($periodStartDisplay),
            'period_end_display' => $this->displayValueOrNa($periodEndDisplay),
            'reported_by_name' => $this->displayValueOrNa(trim((string) ($context['reported_by_name'] ?? ''))),
            'version_no' => 'v' . (int) ($context['version_no'] ?? 0),
            'approved_by' => $this->displayValueOrNa(trim((string) ($manualData['approved_by'] ?? ''))),
            'pm_sessions_count' => $this->displayValueOrNa(trim((string) ($pmData['pm.sessions_count'] ?? ''))),
            'pm_hours_of_instruction' => $this->displayValueOrNa(trim((string) ($pmData['pm.hours_of_instruction'] ?? ''))),
            'pm_dti_net_ratio' => $this->displayValueOrNa(trim((string) ($pmData['pm.dti_net_ratio'] ?? ''))),
            'pm_schedule_of_reinforcement' => $this->displayValueOrNa(trim((string) ($pmData['pm.schedule_of_reinforcement'] ?? ''))),
            'pm_current_programmes' => $this->displayValueOrNa($pmCurrentProgrammesRaw),
            'pm_current_programmes_html' => $this->buildCurrentProgrammesHtml($pmCurrentProgrammesRaw),
            'programme_management_comment' => $programmeManagementComment,
            'programme_management_comment_block_html' => $this->buildOptionalCommentBlockHtml('Comments', $programmeManagementComment),
            'progress_program_start_date_text' => $progressProgramStartDate,
            'progress_intro_html' => $this->buildProgressIntroHtml(
                $learnerFirstNameForIntro,
                $progressProgramStartDate,
                $this->displayValueOrNa($periodStartDisplay),
                $this->displayValueOrNa($periodEndDisplay)
            ),
            'progress_legend_black_uri' => $progressLegendBlackUri,
            'progress_legend_blue_uri' => $progressLegendBlueUri,
            'progress_cumulative_all_time_graph_html' => $this->buildPdfGraphImageFromPayloadHtml(
                $progressData['progress.cumulative_all_time_graph'] ?? null,
                'No cumulative graph data available for overall period.',
                'Cumulative skills graph',
                'default'
            ),
            'progress_cumulative_period_graph_html' => $this->buildPdfGraphImageFromPayloadHtml(
                $progressData['progress.cumulative_period_graph'] ?? null,
                'No cumulative graph data available for selected report period.',
                'Period cumulative skills graph',
                'default'
            ),
            'instructional_domains_list' => $this->displayValueOrNa(implode(', ', $instructionalDomainTitles)),
            'instructional_domain_blocks_html' => $instructionalDomainBlocksHtml,
            'instructional_domain_graphs_html' => $this->buildInstructionalDomainGraphsHtml($instructionalDomains),
            'instructional_goals_rows_html' => $this->buildInstructionalGoalRowsHtml($instructionalDomains),
            'instructional_images_summary' => $this->formatInstructionalImagesSummary($manualData['instructional_programmes_images'] ?? null),
            'instructional_images_html' => $instructionalImagesHtml,
            'instructional_programmes_comment' => (string) ($manualData['instructional_programmes_comment'] ?? ''),
            'manding_comment' => $mandingComment,
            'manding_graphs_html' => $this->buildGraphGridHtml($mandingGraphs, 'No manding graphs available.'),
            'manding_section_html' => $this->buildMandingSectionPdfHtml($mandingGraphs, $mandingComment),
            'problem_behaviour_reduction_comment' => $problemBehaviourComment,
            'problem_behaviour_graphs_html' => $this->buildProblemBehaviourGraphsPdfHtml($problemBehaviourGraphs),
            'problem_behaviour_comment_block_html' => $this->buildOptionalCommentBlockHtml('Comments', $problemBehaviourComment),
            'conclusion_comment' => $conclusionComment,
            'pulled_sections_json' => json_encode($pulledSections, JSON_UNESCAPED_UNICODE),
            'generated_at' => date('Y-m-d H:i:s'),
        ];
    }

    private function readReportSetting(string $settingKey): string
    {
        return trim((string) (setting($settingKey) ?? ''));
    }

    private function displayValueOrNa(string $value): string
    {
        $value = trim($value);
        return $value === '' ? 'N/A' : $value;
    }

    private function formatDateForDisplay(?string $value, bool $withTime = false): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        if (function_exists('app_date')) {
            return (string) app_date($value, $withTime);
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return $value;
        }

        return $withTime ? date('d-M-Y H:i:s', $timestamp) : date('d-M-Y', $timestamp);
    }

    private function buildAgeAtDateText(?string $dateOfBirthRaw, ?string $referenceDateRaw, $storedAgeRaw = null): string
    {
        $dateOfBirthRaw = trim((string) $dateOfBirthRaw);
        $referenceDateRaw = trim((string) $referenceDateRaw);

        $dobTs = $dateOfBirthRaw !== '' ? strtotime($dateOfBirthRaw) : false;
        $referenceTs = $referenceDateRaw !== '' ? strtotime($referenceDateRaw) : false;
        if ($dobTs !== false && $referenceTs !== false) {
            $dob = new \DateTimeImmutable(date('Y-m-d', $dobTs));
            $reference = new \DateTimeImmutable(date('Y-m-d', $referenceTs));
            if ($reference >= $dob) {
                $diff = $dob->diff($reference);
                return $this->formatAgeText((int) $diff->y, (int) $diff->m, (int) $diff->d);
            }
        }

        if ($storedAgeRaw !== null && trim((string) $storedAgeRaw) !== '' && is_numeric($storedAgeRaw)) {
            $years = max(0, (int) $storedAgeRaw);
            return sprintf('%d year%s 0 months', $years, $years === 1 ? '' : 's');
        }

        return 'N/A';
    }

    private function formatAgeText(int $years, int $months, int $days): string
    {
        $years = max(0, $years);
        $months = max(0, $months);

        return sprintf(
            '%d year%s %d month%s',
            $years,
            $years === 1 ? '' : 's',
            $months,
            $months === 1 ? '' : 's'
        );
    }

    private function formatInstructionalImagesSummary($images): string
    {
        if (is_array($images)) {
            $names = [];
            foreach ($images as $image) {
                if (is_string($image)) {
                    $name = trim($image);
                    if ($name !== '') {
                        $names[] = $name;
                    }
                } elseif (is_array($image)) {
                    $name = trim((string) ($image['name'] ?? $image['file_name'] ?? ''));
                    if ($name !== '') {
                        $names[] = $name;
                    }
                }
            }
            if (!empty($names)) {
                return implode(', ', $names);
            }
        }

        $raw = trim((string) $images);
        return $raw !== '' ? $raw : 'No image uploaded.';
    }

    private function buildProgressIntroHtml(
        string $learnerName,
        string $programStartDate,
        string $periodStartDisplay,
        string $periodEndDisplay
    ): string {
        return 'The following graph shows the cumulative number of skills that '
            . $this->escapeHtml($learnerName)
            . ' has acquired since '
            . '<span class="pr-date-emphasis">' . $this->escapeHtml($programStartDate) . '</span>'
            . '. This report will provide further information on the period '
            . '<span class="pr-date-emphasis">' . $this->escapeHtml($periodStartDisplay) . '</span>'
            . ' to '
            . '<span class="pr-date-emphasis">' . $this->escapeHtml($periodEndDisplay) . '</span>'
            . '.';
    }

    private function extractFirstName(string $fullName, string $fallback = 'Learner'): string
    {
        $fullName = trim($fullName);
        if ($fullName === '' || strtoupper($fullName) === 'N/A') {
            return $fallback;
        }

        $parts = preg_split('/\s+/', $fullName);
        if (is_array($parts) && !empty($parts[0])) {
            $first = trim((string) $parts[0]);
            if ($first !== '') {
                return $first;
            }
        }

        return $fullName;
    }

    private function buildOptionalCommentBlockHtml(string $label, string $comment): string
    {
        $comment = trim($comment);
        if ($comment === '') {
            return '';
        }

        return '<div class="pr-comment-block">'
            . '<div class="pr-field-label">' . $this->escapeHtml($label) . '</div>'
            . '<div class="pr-auto-text-box">' . $this->escapeHtml($comment) . '</div>'
            . '</div>';
    }

    private function buildCurrentProgrammesHtml(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '' || strtoupper($raw) === 'N/A' || strtolower($raw) === 'none') {
            return '<div class="pm-current-programmes">N/A</div>';
        }

        $lines = preg_split('/\r?\n/', $raw) ?: [];
        $blocks = [];
        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }

            $splitAt = strpos($line, ':');
            $domainText = $splitAt !== false ? trim(substr($line, 0, $splitAt)) : $line;
            $domainText = preg_replace('/^[A-Za-z]{1,10}[0-9]*\s*-\s*/', '', (string) $domainText);
            $domainText = trim((string) $domainText);
            $goalsText = $splitAt !== false ? trim(substr($line, $splitAt + 1)) : '';
            if ($domainText === '') {
                $domainText = 'N/A';
            }

            $block = '<div class="pm-programme-item"><strong class="pm-domain-label">' . $this->escapeHtml($domainText) . '</strong>';
            if ($goalsText !== '') {
                $block .= '<strong class="pm-domain-separator">:</strong> ' . $this->escapeHtml($goalsText);
            }
            $block .= '</div>';
            $blocks[] = $block;
        }

        if (empty($blocks)) {
            return '<div class="pm-current-programmes">N/A</div>';
        }

        return '<div class="pm-current-programmes">' . implode('', $blocks) . '</div>';
    }

    private function buildPdfGraphPlaceholderHtml(string $title): string
    {
        $title = trim($title);
        if ($title === '') {
            $title = 'Graph Placeholder';
        }

        return '<div class="pr-pdf-graph-placeholder">'
            . '<div class="pr-pdf-graph-icon"></div>'
            . '<div class="pr-pdf-graph-title">' . $this->escapeHtml($title) . '</div>'
            . '</div>';
    }

    private function buildPdfGraphImageFromPayloadHtml($payload, string $emptyMessage, string $altText, string $profile = 'default'): string
    {
        if (!is_array($payload)) {
            return '<div class="pr-empty-info">' . $this->escapeHtml($emptyMessage) . '</div>';
        }

        $svg = $this->buildLineChartSvg($payload, $profile);
        if (strpos($svg, '<svg') === false) {
            return '<div class="pr-empty-info">' . $this->escapeHtml($emptyMessage) . '</div>';
        }

        $dataUri = 'data:image/svg+xml;base64,' . base64_encode($svg);

        return '<img class="pr-pdf-graph-image" src="' . $dataUri . '" alt="' . $this->escapeHtml($altText) . '">';
    }

    private function buildInstructionalDomainGoalRowsPdfHtml(array $goals): string
    {
        if (empty($goals)) {
            return '<tr><td class="text-muted">N/A</td><td class="text-muted">N/A</td></tr>';
        }

        $rows = [];
        foreach ($goals as $goal) {
            if (!is_array($goal)) {
                continue;
            }

            $goalName = trim((string) ($goal['goal_name'] ?? ''));
            if ($goalName === '') {
                $goalName = 'N/A';
            }

            $targets = $goal['targets_mastered'] ?? [];
            if (!is_array($targets)) {
                $targets = [];
            }

            $targetLabels = [];
            foreach ($targets as $target) {
                $targetText = trim((string) $target);
                if ($targetText !== '') {
                    $targetLabels[] = $this->escapeHtml($targetText);
                }
            }

            $goalTargetCount = isset($goal['goal_target_count']) ? (int) $goal['goal_target_count'] : count($targetLabels);
            if ($goalTargetCount < 0) {
                $goalTargetCount = 0;
            }
            $goalDisplay = $goalName . ' (' . $goalTargetCount . ')';

            $rows[] = '<tr><td>' . $this->escapeHtml($goalDisplay) . '</td><td>'
                . (!empty($targetLabels) ? implode(', ', $targetLabels) : 'N/A')
                . '</td></tr>';
        }

        if (empty($rows)) {
            return '<tr><td class="text-muted">N/A</td><td class="text-muted">N/A</td></tr>';
        }

        return implode('', $rows);
    }

    private function buildInstructionalDomainBlocksPdfHtml(
        array $domains,
        array $domainComments,
        string $legendBlackUri,
        string $legendBlueUri
    ): string
    {
        if (empty($domains)) {
            return '<div class="pr-graph-placeholder">No instructional program data available for the selected period.</div>';
        }

        $blocks = [];
        foreach ($domains as $index => $domain) {
            if (!is_array($domain)) {
                continue;
            }

            $domainKey = trim((string) ($domain['key'] ?? ('d' . ($index + 1))));
            if ($domainKey === '') {
                $domainKey = 'd' . ($index + 1);
            }

            $domainTitle = trim((string) ($domain['title'] ?? ('Domain ' . ($index + 1))));
            if ($domainTitle === '') {
                $domainTitle = 'Domain ' . ($index + 1);
            }

            $goals = $domain['goals'] ?? [];
            if (!is_array($goals)) {
                $goals = [];
            }
            $domainTotalTargetCount = isset($domain['domain_total_target_count'])
                ? (int) $domain['domain_total_target_count']
                : 0;
            if ($domainTotalTargetCount < 0) {
                $domainTotalTargetCount = 0;
            }
            if ($domainTotalTargetCount === 0 && !empty($goals)) {
                foreach ($goals as $goal) {
                    if (!is_array($goal)) {
                        continue;
                    }
                    if (isset($goal['goal_target_count'])) {
                        $domainTotalTargetCount += max(0, (int) $goal['goal_target_count']);
                        continue;
                    }
                    $targets = $goal['targets_mastered'] ?? [];
                    $domainTotalTargetCount += is_array($targets) ? count($targets) : 0;
                }
            }
            $goalRowsHtml = $this->buildInstructionalDomainGoalRowsPdfHtml($goals);
            $domainComment = trim((string) ($domainComments[$domainKey] ?? ''));
            $domainCommentBlock = $this->buildOptionalCommentBlockHtml('Comments', $domainComment);
            $domainGraphHtml = $this->buildPdfGraphImageFromPayloadHtml(
                $domain['period_graph'] ?? null,
                'No domain cumulative graph data available for this period.',
                $domainTitle . ' cumulative graph',
                'default'
            );
            $domainLegendHtml = '<div class="pr-progress-legend">'
                . '<span style="margin-right: 16px;"><img src="' . $this->escapeHtml($legendBlackUri) . '" alt="Skills Retained"> Skills Retained</span>'
                . '<span><img src="' . $this->escapeHtml($legendBlueUri) . '" alt="Degrees of Independence"> Degrees of independence</span>'
                . '</div>';

            $domainBlockClass = 'pr-domain-block' . ($index > 0 ? ' pr-page-break-before' : '');
            $blocks[] = '<div class="' . $domainBlockClass . '">'
                . '<div class="pr-domain-title">' . $this->escapeHtml($domainTitle) . '</div>'
                . '<div style="margin:4px 0 10px 0;"><strong>Total Targets Retained:</strong> ' . $this->escapeHtml((string) $domainTotalTargetCount) . '</div>'
                . '<div class="pr-domain-layout">'
                . '  <div>' . $domainLegendHtml . '<div class="pr-chart-wrap">' . $domainGraphHtml . '</div></div>'
                . '  <div><table class="pr-grid pr-domain-goals-table"><thead><tr><th style="width:45%;">Goal</th><th>Targets Mastered (' . $this->escapeHtml((string) $domainTotalTargetCount) . ')</th></tr></thead><tbody>'
                . $goalRowsHtml
                . '</tbody></table></div>'
                . '</div>'
                . ($domainCommentBlock !== '' ? '<div class="pr-domain-comment-row">' . $domainCommentBlock . '</div>' : '')
                . '</div>';
        }

        if (empty($blocks)) {
            return '<div class="pr-graph-placeholder">No instructional program data available for the selected period.</div>';
        }

        return implode('', $blocks);
    }

    private function buildMandingSectionPdfHtml(array $graphs, string $comment): string
    {
        if (empty($graphs)) {
            return '';
        }

        $cards = [];
        foreach ($graphs as $index => $graph) {
            if (!is_array($graph)) {
                continue;
            }

            $title = trim((string) ($graph['title'] ?? ('Graph ' . ($index + 1))));
            if ($title === '') {
                $title = 'Graph ' . ($index + 1);
            }
            $graphHtml = $this->buildPdfGraphImageFromPayloadHtml(
                $graph['graph'] ?? null,
                'No manding graph data available for this metric.',
                $title,
                'compact_wide'
            );
            $datasetLegendHtml = $this->buildGraphDatasetLegendPdfHtml($graph['graph'] ?? null);

            $cards[] = '<div class="pr-graph-card">'
                . '<div class="pr-graph-title">' . $this->escapeHtml($title) . '</div>'
                . $datasetLegendHtml
                . '<div class="pr-chart-wrap">' . $graphHtml . '</div>'
                . '</div>';
        }

        if (empty($cards)) {
            return '';
        }

        $rows = [];
        foreach ($cards as $index => $cardHtml) {
            $rowClass = (($index > 0) && ($index % 3 === 0)) ? ' class="pr-page-break-before"' : '';
            $rows[] = '<tr' . $rowClass . '>'
                . '<td style="width:100%;vertical-align:top;">' . $cardHtml . '</td>'
                . '</tr>';
        }

        $commentBlock = $this->buildOptionalCommentBlockHtml('Comments', $comment);

        return '<div class="pr-section pr-page-break-before" id="section_manding_pdf">'
            . '<div class="pr-section-header"><h3 class="pr-section-title">Manding</h3></div>'
            . '<table class="pr-one-col-grid"><tbody>' . implode('', $rows) . '</tbody></table>'
            . ($commentBlock !== '' ? $commentBlock : '')
            . '</div>';
    }

    private function buildGraphDatasetLegendPdfHtml($payload): string
    {
        if (!is_array($payload)) {
            return '';
        }

        $datasets = $payload['datasets'] ?? null;
        if (!is_array($datasets) || empty($datasets)) {
            return '';
        }

        $items = [];
        $fallbackIndex = 0;
        foreach ($datasets as $index => $dataset) {
            if (!is_array($dataset)) {
                continue;
            }

            $label = trim((string) ($dataset['label'] ?? ('Series ' . ($index + 1))));
            if ($label === '') {
                $label = 'Series ' . ($index + 1);
            }

            $color = $this->resolveDatasetLegendColorForPdf($dataset, $fallbackIndex);
            $items[] = '<span class="pr-dataset-legend-item">'
                . '<span class="pr-dataset-legend-swatch" style="background-color:' . $this->escapeHtml($color) . ';"></span>'
                . '<span class="pr-dataset-legend-label">' . $this->escapeHtml($label) . '</span>'
                . '</span>';
            $fallbackIndex++;
        }

        if (empty($items)) {
            return '';
        }

        return '<div class="pr-dataset-legend">' . implode('', $items) . '</div>';
    }

    private function resolveDatasetLegendColorForPdf(array $dataset, int $fallbackIndex): string
    {
        foreach (['borderColor', 'backgroundColor'] as $field) {
            $value = $dataset[$field] ?? null;
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }

            if (is_array($value)) {
                foreach ($value as $item) {
                    if (is_string($item) && trim($item) !== '') {
                        return trim($item);
                    }
                }
            }
        }

        return self::GRAPH_FALLBACK_COLORS[$fallbackIndex % count(self::GRAPH_FALLBACK_COLORS)];
    }

    private function buildProblemBehaviourGraphsPdfHtml(array $graphs): string
    {
        if (empty($graphs)) {
            return '<div class="pr-empty-info">No problem behaviour was recorded in the selected period.</div>';
        }

        $cards = [];
        $hasRenderableGraph = false;
        foreach ($graphs as $index => $graph) {
            if (!is_array($graph)) {
                continue;
            }

            $title = trim((string) ($graph['title'] ?? ('Graph ' . ($index + 1))));
            if ($title === '') {
                $title = 'Graph ' . ($index + 1);
            }

            $payload = $graph['graph'] ?? null;
            if (is_array($payload) && $this->graphPayloadHasAnyNonZeroValue($payload)) {
                $hasRenderableGraph = true;
                $graphHtml = $this->buildPdfGraphImageFromPayloadHtml(
                    $payload,
                    'No problem behaviour graph data available for this metric.',
                    $title,
                    'compact_wide'
                );
                $cards[] = '<div class="pr-graph-card">'
                    . '<div class="pr-graph-title">' . $this->escapeHtml($title) . '</div>'
                    . '<div class="pr-chart-wrap">' . $graphHtml . '</div>'
                    . '</div>';
            } else {
                $cards[] = '<div class="pr-graph-card pr-graph-card-message">'
                    . '<div class="pr-graph-title">' . $this->escapeHtml($title) . '</div>'
                    . '<div class="pr-empty-info">No problem behaviour was recorded for this metric in the selected period.</div>'
                    . '</div>';
            }
        }

        if (!$hasRenderableGraph) {
            return '<div class="pr-empty-info">No problem behaviour was recorded in the selected period.</div>';
        }

        $rows = [];
        foreach ($cards as $cardHtml) {
            $rows[] = '<tr><td style="width:100%;vertical-align:top;">' . $cardHtml . '</td></tr>';
        }

        return '<table class="pr-one-col-grid"><tbody>' . implode('', $rows) . '</tbody></table>';
    }

    private function resolvePublicImageDataUri(string $publicRelativePath): string
    {
        $publicRelativePath = trim($publicRelativePath);
        if ($publicRelativePath === '') {
            return '';
        }

        $normalized = str_replace(['\\', '..'], ['/', ''], ltrim($publicRelativePath, '/'));
        if ($normalized === '') {
            return '';
        }

        $publicRoot = defined('FCPATH')
            ? rtrim((string) FCPATH, '/\\') . DIRECTORY_SEPARATOR
            : rtrim(ROOTPATH, '/\\') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR;
        $fullPath = $publicRoot . str_replace('/', DIRECTORY_SEPARATOR, $normalized);
        if (!is_file($fullPath)) {
            return '';
        }

        $ext = strtolower((string) pathinfo($fullPath, PATHINFO_EXTENSION));
        $mimeMap = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
        ];
        $mime = $mimeMap[$ext] ?? '';
        if ($mime === '') {
            return '';
        }

        $content = file_get_contents($fullPath);
        if ($content === false) {
            return '';
        }

        return 'data:' . $mime . ';base64,' . base64_encode($content);
    }

    private function buildInstructionalImagesHtml(int $versionId, $manualImages): string
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

        $artifacts = $this->getInstructionalImageArtifactsByVersion($versionId);
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
            $fileName = trim((string) ($artifact['file_name'] ?? 'Instructional Image'));
            $items[] = '<div class="pr-instructional-image-item"><img src="' . $dataUri . '" alt="' . $this->escapeHtml($fileName) . '"></div>';
        }

        if (empty($items)) {
            return '';
        }

        return '<div class="pr-instructional-images">' . implode('', $items) . '</div>';
    }

    private function buildInstructionalGoalRowsHtml(array $domains): string
    {
        $rows = [];
        foreach ($domains as $domain) {
            if (!is_array($domain)) {
                continue;
            }
            $goals = $domain['goals'] ?? [];
            if (!is_array($goals)) {
                continue;
            }
            foreach ($goals as $goal) {
                if (!is_array($goal)) {
                    continue;
                }
                $goalName = trim((string) ($goal['goal_name'] ?? ''));
                $targetsMastered = $goal['targets_mastered'] ?? [];
                if (!is_array($targetsMastered)) {
                    $targetsMastered = [];
                }

                $targetLines = [];
                foreach ($targetsMastered as $target) {
                    $targetText = trim((string) $target);
                    if ($targetText !== '') {
                        $targetLines[] = $this->escapeHtml($targetText);
                    }
                }
                if (empty($targetLines)) {
                    $targetLines[] = 'N/A';
                }

                $goalTargetCount = isset($goal['goal_target_count']) ? (int) $goal['goal_target_count'] : count($targetLines);
                if ($goalTargetCount < 0) {
                    $goalTargetCount = 0;
                }

                $goalDisplay = ($goalName !== '' ? $goalName : 'N/A') . ' (' . $goalTargetCount . ')';
                $rows[] = '<tr><td>' . $this->escapeHtml($goalDisplay) . '</td><td>' . implode(', ', $targetLines) . '</td></tr>';
            }
        }

        if (empty($rows)) {
            return '<tr><td class="text-muted">N/A</td><td class="text-muted">N/A</td></tr>';
        }

        return implode('', $rows);
    }

    private function buildInstructionalDomainGraphsHtml(array $domains): string
    {
        if (empty($domains)) {
            return '<div class="pr-graph-placeholder">No domain graphs available.</div>';
        }

        $blocks = [];
        foreach ($domains as $index => $domain) {
            if (!is_array($domain)) {
                continue;
            }
            $title = trim((string) ($domain['title'] ?? ('Domain ' . ($index + 1))));
            if ($title === '') {
                $title = 'Domain ' . ($index + 1);
            }
            $graphPayload = $domain['period_graph'] ?? null;
            $graphHtml = $this->buildGraphHtmlFromPayload($graphPayload, 'Domain cumulative graph placeholder for report period.');
            $blocks[] = '<div class="pr-domain-graph-block"><div class="pr-domain-title">' . $this->escapeHtml($title) . '</div><div class="pr-chart-wrap">' . $graphHtml . '</div></div>';
        }

        if (empty($blocks)) {
            return '<div class="pr-graph-placeholder">No domain graphs available.</div>';
        }

        $rows = [];
        $total = count($blocks);
        for ($i = 0; $i < $total; $i += 2) {
            $left = $blocks[$i];
            $right = $blocks[$i + 1] ?? '';
            $rows[] = '<tr>'
                . '<td style="width:50%;vertical-align:top;padding-right:8px;">' . $left . '</td>'
                . '<td style="width:50%;vertical-align:top;padding-left:8px;">' . $right . '</td>'
                . '</tr>';
        }

        return '<table style="width:100%;border-collapse:separate;border-spacing:0 10px;"><tbody>'
            . implode('', $rows)
            . '</tbody></table>';
    }

    private function buildGraphGridHtml(array $graphs, string $emptyMessage): string
    {
        if (empty($graphs)) {
            return '<div class="pr-graph-placeholder">' . $this->escapeHtml($emptyMessage) . '</div>';
        }

        $items = [];
        foreach ($graphs as $index => $graph) {
            if (!is_array($graph)) {
                continue;
            }
            $title = trim((string) ($graph['title'] ?? ('Graph ' . ($index + 1))));
            if ($title === '') {
                $title = 'Graph ' . ($index + 1);
            }
            $graphHtml = $this->buildGraphHtmlFromPayload($graph['graph'] ?? null, 'Graph data unavailable.');
            $items[] = '<div class="pr-graph-col"><div class="pr-graph-title">' . $this->escapeHtml($title) . '</div><div class="pr-chart-wrap">' . $graphHtml . '</div></div>';
        }

        if (empty($items)) {
            return '<div class="pr-graph-placeholder">' . $this->escapeHtml($emptyMessage) . '</div>';
        }

        return implode('', $items);
    }

    private function buildGraphHtmlFromPayload($payload, string $emptyMessage): string
    {
        if (!is_array($payload)) {
            return '<div class="pr-chart-empty">' . $this->escapeHtml($emptyMessage) . '</div>';
        }

        $labels = $payload['labels'] ?? [];
        $datasets = $payload['datasets'] ?? [];
        if (!is_array($labels) || empty($labels) || !is_array($datasets) || empty($datasets)) {
            return '<div class="pr-chart-empty">' . $this->escapeHtml($emptyMessage) . '</div>';
        }

        return $this->buildLineChartSvg($payload);
    }

    private function buildLineChartSvg(array $payload, string $profile = 'default'): string
    {
        $labelsRaw = $payload['labels'] ?? [];
        $datasetsRaw = $payload['datasets'] ?? [];
        if (!is_array($labelsRaw) || !is_array($datasetsRaw) || empty($labelsRaw) || empty($datasetsRaw)) {
            return '<div class="pr-chart-empty">Graph data unavailable.</div>';
        }

        $labels = array_values(array_map(static fn($label) => trim((string) $label), $labelsRaw));
        $pointCount = count($labels);
        if ($pointCount === 0) {
            return '<div class="pr-chart-empty">Graph data unavailable.</div>';
        }

        $normalizedDatasets = [];
        $allValues = [];
        $datasetIndex = 0;
        foreach ($datasetsRaw as $dataset) {
            if (!is_array($dataset)) {
                continue;
            }
            $seriesRaw = $dataset['data'] ?? [];
            if (!is_array($seriesRaw)) {
                continue;
            }

            $series = [];
            foreach ($seriesRaw as $value) {
                $series[] = is_numeric($value) ? (float) $value : null;
            }
            if (count($series) < $pointCount) {
                $series = array_pad($series, $pointCount, null);
            } elseif (count($series) > $pointCount) {
                $series = array_slice($series, 0, $pointCount);
            }

            $hasPoint = false;
            foreach ($series as $value) {
                if ($value !== null) {
                    $hasPoint = true;
                    $allValues[] = $value;
                }
            }
            if (!$hasPoint) {
                continue;
            }

            $color = trim((string) ($dataset['borderColor'] ?? $dataset['backgroundColor'] ?? ''));
            if ($color === '') {
                $color = self::GRAPH_FALLBACK_COLORS[$datasetIndex % count(self::GRAPH_FALLBACK_COLORS)];
            }
            $label = trim((string) ($dataset['label'] ?? ('Series ' . ($datasetIndex + 1))));
            if ($label === '') {
                $label = 'Series ' . ($datasetIndex + 1);
            }

            $normalizedDatasets[] = [
                'label' => $label,
                'color' => $color,
                'series' => $series,
            ];
            $datasetIndex++;
        }

        if (empty($normalizedDatasets) || empty($allValues)) {
            return '<div class="pr-chart-empty">Graph data unavailable.</div>';
        }

        $minValue = min($allValues);
        $maxValue = max($allValues);
        $yMin = (int) floor(min(0, $minValue));
        $yMax = (int) ceil($maxValue);
        if ($yMax <= $yMin) {
            $yMax = $yMin + 1;
        }
        $range = $yMax - $yMin;
        $pad = max(1, (int) ceil($range * 0.1));
        $yMax += $pad;
        $yRange = $yMax - $yMin;

        $isCompactWide = $profile === 'compact_wide';
        $width = 680;
        $height = $isCompactWide ? 210 : 300;
        $left = 88;
        $right = 20;
        $top = $isCompactWide ? 14 : 20;
        $bottom = $isCompactWide ? 44 : 56;
        $plotWidth = $width - $left - $right;
        $plotHeight = $height - $top - $bottom;
        if ($plotWidth <= 0 || $plotHeight <= 0) {
            return '<div class="pr-chart-empty">Graph data unavailable.</div>';
        }

        $xDenominator = max(1, $pointCount - 1);
        $mapX = static fn(int $index) => $left + ($plotWidth * ($index / $xDenominator));
        $mapY = static fn(float $value) => $top + (($yMax - $value) / $yRange) * $plotHeight;

        $svg = [];
        $svg[] = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '">';
        $svg[] = '<rect x="0" y="0" width="' . $width . '" height="' . $height . '" fill="#ffffff" />';

        $gridLines = 4;
        for ($i = 0; $i <= $gridLines; $i++) {
            $lineY = $top + ($plotHeight * ($i / $gridLines));
            $valueAtTick = (int) round($yMax - (($yRange * $i) / $gridLines));
            $svg[] = '<line x1="' . ($left - 4) . '" y1="' . round($lineY, 2) . '" x2="' . $left . '" y2="' . round($lineY, 2) . '" stroke="#6b7280" stroke-width="1" />';
            $svg[] = '<text x="' . ($left - 8) . '" y="' . round($lineY + 4, 2) . '" text-anchor="end" fill="#374151" font-size="' . ($isCompactWide ? '9' : '10') . '">' . $this->escapeHtml((string) $valueAtTick) . '</text>';
        }

        $svg[] = '<line x1="' . $left . '" y1="' . $top . '" x2="' . $left . '" y2="' . ($top + $plotHeight) . '" stroke="#6b7280" stroke-width="1.2" />';
        $svg[] = '<line x1="' . $left . '" y1="' . ($top + $plotHeight) . '" x2="' . ($left + $plotWidth) . '" y2="' . ($top + $plotHeight) . '" stroke="#6b7280" stroke-width="1.2" />';

        $xTickIndexes = $this->resolveXAxisTickIndexes($pointCount);
        foreach ($xTickIndexes as $tickIndex) {
            if (!isset($labels[$tickIndex])) {
                continue;
            }
            $x = $mapX($tickIndex);
            $svg[] = '<line x1="' . round($x, 2) . '" y1="' . ($top + $plotHeight) . '" x2="' . round($x, 2) . '" y2="' . ($top + $plotHeight + 4) . '" stroke="#6b7280" stroke-width="1" />';
            $svg[] = '<text x="' . round($x, 2) . '" y="' . ($top + $plotHeight + ($isCompactWide ? 14 : 16)) . '" text-anchor="middle" fill="#4b5563" font-size="' . ($isCompactWide ? '9' : '10') . '">' . $this->escapeHtml((string) $labels[$tickIndex]) . '</text>';
        }

        foreach ($normalizedDatasets as $dataset) {
            $color = $this->escapeHtml((string) $dataset['color']);
            $series = $dataset['series'];

            $segment = [];
            foreach ($series as $index => $value) {
                if ($value === null) {
                    if (count($segment) > 1) {
                        $svg[] = '<polyline points="' . implode(' ', $segment) . '" fill="none" stroke="' . $color . '" stroke-width="2" />';
                    }
                    $segment = [];
                    continue;
                }

                $x = round($mapX($index), 2);
                $y = round($mapY($value), 2);
                $segment[] = $x . ',' . $y;
                $svg[] = '<circle cx="' . $x . '" cy="' . $y . '" r="2.8" fill="' . $color . '" />';
            }
            if (count($segment) > 1) {
                $svg[] = '<polyline points="' . implode(' ', $segment) . '" fill="none" stroke="' . $color . '" stroke-width="2" />';
            }
        }

        $yAxisLabel = trim((string) ($payload['options']['y_axis_label'] ?? ''));
        if ($yAxisLabel !== '') {
            $yCenter = $top + ($plotHeight / 2);
            $svg[] = '<text x="22" y="' . round($yCenter, 2) . '" text-anchor="middle" fill="#374151" font-size="' . ($isCompactWide ? '7' : '8') . '" font-weight="400" transform="rotate(-90 22 ' . round($yCenter, 2) . ')">' . $this->escapeHtml($yAxisLabel) . '</text>';
        }
        $xAxisLabel = trim((string) ($payload['options']['x_axis_label'] ?? ''));
        if ($xAxisLabel !== '') {
            $svg[] = '<text x="' . ($left + ($plotWidth / 2)) . '" y="' . ($height - ($isCompactWide ? 8 : 10)) . '" text-anchor="middle" fill="#374151" font-size="' . ($isCompactWide ? '10' : '11') . '">' . $this->escapeHtml($xAxisLabel) . '</text>';
        }

        $svg[] = '</svg>';

        return implode('', $svg);
    }

    private function resolveXAxisTickIndexes(int $pointCount): array
    {
        if ($pointCount <= 0) {
            return [];
        }
        if ($pointCount <= 6) {
            return range(0, $pointCount - 1);
        }

        $lastIndex = $pointCount - 1;
        $step = (int) ceil($pointCount / 5);
        $indexes = [0];
        for ($i = $step; $i < $lastIndex; $i += $step) {
            $indexes[] = $i;
        }
        $indexes[] = $lastIndex;

        $unique = array_values(array_unique($indexes));
        sort($unique);
        return $unique;
    }

    private function resolveReportLogoDataUri(): string
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
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80">'
            . '<rect width="100%" height="100%" fill="#f2f2f2" stroke="#a3a3a3" />'
            . '<text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#666" font-size="12">Logo</text>'
            . '</svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    private function escapeHtml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    private function renderProgressTemplate(string $templatePath, array $tokenValues): string
    {
        $html = file_get_contents($templatePath);
        if ($html === false) {
            throw new RuntimeException('Unable to read progress template.');
        }

        $replacements = [];
        foreach ($tokenValues as $key => $value) {
            if (in_array($key, self::RAW_HTML_TOKENS, true)) {
                $replacements['{{' . $key . '}}'] = (string) $value;
            } else {
                $replacements['{{' . $key . '}}'] = nl2br(htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'));
            }
        }

        return strtr($html, $replacements);
    }

    private function createProgressArtifact(
        int $subjectId,
        string $periodFrom,
        string $periodTo,
        int $versionNo,
        int $versionId,
        ?int $userId,
        string $now,
        string $html,
        array $footerLines = []
    ): array {
        $periodKey = $this->buildPeriodKey($periodFrom, $periodTo);
        $artifactDir = WRITEPATH . 'reports/artifacts/progress/learner/' . $subjectId . '/' . $periodKey . '/v' . $versionNo . '/';
        if (!is_dir($artifactDir)) {
            mkdir($artifactDir, 0775, true);
        }

        $pdfFileName = sprintf('ProgressReport_%d_%s_to_%s_v%d.pdf', $subjectId, $periodFrom, $periodTo, $versionNo);
        $pdfPath = $artifactDir . $pdfFileName;
        $this->htmlToPdfConverter->convert($html, $pdfPath, $footerLines);

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

        return ['storage_path' => $relativePath, 'file_name' => $pdfFileName];
    }
}

