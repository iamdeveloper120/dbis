<?php

namespace App\Controllers\Reports;

use App\Controllers\AdminController;
use App\Models\ClientConfiguration\ClientModel;
use App\Services\Reports\ProgressReportService;
use Config\Services;
use CodeIgniter\Exceptions\PageNotFoundException;
use Throwable;

class ProgressReportController extends AdminController
{
    private const STATE_TOKEN_VERSION = 1;
    private const STATE_TOKEN_TTL_SECONDS = 2592000; // 30 days

    protected ProgressReportService $progressReportService;

    public function __construct()
    {
        $this->progressReportService = new ProgressReportService();
    }

    public function index()
    {
        $this->page_title = 'Reporting | Progress Reports';
        $clientModel = new ClientModel();
        $clients = $clientModel->get_active_client_list();
        $initialState = $this->resolveProgressListStateFromRequest();

        return view('Reports/Progress/index', [
            'page_title' => $this->page_title,
            'clients' => $clients,
            'initial_state' => $initialState,
        ]);
    }

    public function stateToken()
    {
        $state = $this->sanitizeProgressListState([
            'client_id' => $this->request->getPost('client_id'),
            'start_date' => $this->request->getPost('start_date'),
            'end_date' => $this->request->getPost('end_date'),
            'dt_page' => $this->request->getPost('dt_page'),
        ]);

        $stateQuery = $this->buildProgressListStateQueryFromState($state);
        if ($this->hasProgressListState($state) && $stateQuery === '') {
            $response = $this->getResponseObject('error', 'Progress Report', 'Failed to encode URL state.', [], []);
            return $this->response->setJSON($response);
        }

        $response = $this->getResponseObject('success', 'Progress Report', 'State encoded successfully.', [], [
            'state_query' => $stateQuery,
            'state' => $state,
        ]);
        return $this->response->setJSON($response);
    }

    public function data()
    {
        helper('custom');

        $subjectId = (int) $this->request->getPost('subject_id');
        $startDate = trim((string) $this->request->getPost('start_date'));
        $endDate = trim((string) $this->request->getPost('end_date'));

        if ($subjectId <= 0) {
            $response = $this->getResponseObject('success', 'Progress Reports', 'Listed successfully', [], []);
            return $this->response->setJSON($response);
        }

        if (($startDate !== '' && !$this->isValidYmd($startDate)) || ($endDate !== '' && !$this->isValidYmd($endDate))) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid date range.', [], []);
            return $this->response->setJSON($response);
        }

        $result = $this->progressReportService->listBySubject($subjectId, $startDate ?: null, $endDate ?: null);
        if (!$result['success']) {
            $statusText = ($result['code'] ?? '') === 'DB_SETUP_REQUIRED' ? 'DbSetupRequired' : 'Progress Report';
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $data = [];
        foreach ($result['data'] as $row) {
            $data[] = [
                'report_id' => (int) ($row['report_id'] ?? 0),
                'subject_id' => (int) ($row['subject_id'] ?? 0),
                'period_from' => (string) ($row['period_start'] ?? ''),
                'period_to' => (string) ($row['period_end'] ?? ''),
                'period_from_display' => !empty($row['period_start']) ? app_date($row['period_start']) : '',
                'period_to_display' => !empty($row['period_end']) ? app_date($row['period_end']) : '',
                'latest_version_no' => (int) ($row['latest_version_no'] ?? 0),
                'latest_version_id' => isset($row['latest_version_id']) ? (int) $row['latest_version_id'] : null,
                'latest_status' => strtoupper((string) ($row['latest_status'] ?? 'DRAFT')),
                'created_at' => $row['created_at'] ?? null,
                'created_at_display' => !empty($row['created_at']) ? app_date($row['created_at'], true) : '',
                'updated_at' => $row['updated_at'] ?? null,
                'updated_at_display' => !empty($row['updated_at']) ? app_date($row['updated_at'], true) : '',
                'generated_at' => $row['generated_at'] ?? null,
                'generated_at_display' => !empty($row['generated_at']) ? app_date($row['generated_at'], true) : '',
                'generated_by_name' => trim((string) ($row['generated_by_name'] ?? '')),
            ];
        }

        $response = $this->getResponseObject('success', 'Progress Reports', 'Listed successfully', [], $data);
        return $this->response->setJSON($response);
    }

    public function checkGenerate()
    {
        $subjectId = (int) $this->request->getPost('subject_id');
        $periodFrom = trim((string) $this->request->getPost('period_from'));
        $periodTo = trim((string) $this->request->getPost('period_to'));

        if ($subjectId <= 0 || !$this->isValidYmd($periodFrom) || !$this->isValidYmd($periodTo)) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'subject_id, period_from, and period_to are required.', [], []);
            return $this->response->setJSON($response);
        }

        $result = $this->progressReportService->checkGenerateDraft($subjectId, $periodFrom, $periodTo);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'EXACT_PERIOD_EXISTS' => 'ExactPeriodExists',
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'TEMPLATE_SETUP_REQUIRED' => 'TemplateSetupRequired',
                default => 'Validation_Error',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $response = $this->getResponseObject('success', 'GenerateCheck', $result['message'], [], $result['data'] ?? []);
        return $this->response->setJSON($response);
    }

    public function generate()
    {
        $subjectId = (int) $this->request->getPost('subject_id');
        $periodFrom = trim((string) $this->request->getPost('period_from'));
        $periodTo = trim((string) $this->request->getPost('period_to'));

        if ($subjectId <= 0 || !$this->isValidYmd($periodFrom) || !$this->isValidYmd($periodTo)) {
            $response = $this->getResponseObject(
                'error',
                'Validation_Error',
                'subject_id, period_from, and period_to are required.',
                [
                    'subject_id' => 'Invalid subject_id',
                    'period_from' => 'Invalid period_from',
                    'period_to' => 'Invalid period_to',
                ],
                []
            );
            return $this->response->setJSON($response);
        }

        $result = $this->progressReportService->createDraft($subjectId, $periodFrom, $periodTo, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'EXACT_PERIOD_EXISTS' => 'ExactPeriodExists',
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'TEMPLATE_SETUP_REQUIRED' => 'TemplateSetupRequired',
                default => 'Progress Report',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $data = $result['data'] ?? [];
        $versionId = (int) ($data['version_id'] ?? 0);
        $data['draft_url'] = $versionId > 0 ? base_url('reports/progress/version/' . $versionId . '/draft') : null;

        $response = $this->getResponseObject('success', 'Progress Report', 'Draft generated successfully.', [], $data);
        return $this->response->setJSON($response);
    }

    public function versions()
    {
        helper('custom');

        $reportId = (int) $this->request->getPost('report_id');
        if ($reportId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'report_id is required.', [], []);
            return $this->response->setJSON($response);
        }

        $result = $this->progressReportService->listVersions($reportId);
        if (!$result['success']) {
            $statusText = ($result['code'] ?? '') === 'DB_SETUP_REQUIRED' ? 'DbSetupRequired' : 'Progress Report Versions';
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $data = [];
        foreach ($result['data'] as $row) {
            $data[] = [
                'version_id' => (int) ($row['version_id'] ?? 0),
                'version_no' => (int) ($row['version_no'] ?? 0),
                'status' => strtoupper((string) ($row['status'] ?? 'DRAFT')),
                'generated_at' => $row['generated_at'] ?? null,
                'generated_at_display' => !empty($row['generated_at']) ? app_date($row['generated_at'], true) : '',
                'generated_by_name' => trim((string) ($row['generated_by_name'] ?? '')),
                'artifact_id' => isset($row['artifact_id']) ? (int) $row['artifact_id'] : null,
                'file_name' => (string) ($row['file_name'] ?? ''),
            ];
        }

        $response = $this->getResponseObject('success', 'Progress Report Versions', 'Listed successfully.', [], $data);
        return $this->response->setJSON($response);
    }

    public function draft($versionId)
    {
        helper('custom');

        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            throw new PageNotFoundException('Invalid version id');
        }

        $version = $this->progressReportService->getVersionContext($versionId);
        if (!$version) {
            throw new PageNotFoundException('Progress Report draft not found.');
        }

        $workflowStatus = strtoupper((string) ($version['workflow_status'] ?? 'DRAFT'));
        if ($workflowStatus === 'FINAL') {
            return redirect()->to(base_url('reports/progress/version/' . $versionId . '/pdf'));
        }

        $this->page_title = 'Reporting | Progress Report Draft';
        $listState = $this->resolveProgressListStateFromRequest();

        return view('Reports/Progress/draft', [
            'page_title' => $this->page_title,
            'version' => $version,
            'manual_data' => $this->decodeJsonObject((string) ($version['manual_json'] ?? '{}')),
            'instructional_image_limits' => $this->progressReportService->getInstructionalImageLimits(),
            'list_state_query' => $this->buildProgressListStateQueryFromState($listState),
        ]);
    }

    public function saveDraft($versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version id.', [], []);
            return $this->response->setJSON($response);
        }

        $manualData = [
            'approved_by' => trim((string) $this->request->getPost('approved_by')),
            'draft_notes' => trim((string) $this->request->getPost('draft_notes')),
        ];

        $manualJson = trim((string) $this->request->getPost('manual_json'));
        if ($manualJson !== '') {
            $decoded = json_decode($manualJson, true);
            if (!is_array($decoded)) {
                $response = $this->getResponseObject('error', 'Validation_Error', 'manual_json must be valid JSON object.', [], []);
                return $this->response->setJSON($response);
            }
            $manualData = array_merge($manualData, $decoded);
        }

        $result = $this->progressReportService->saveDraft($versionId, $manualData, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'DRAFT_LOCKED' => 'DraftLocked',
                'NOT_FOUND' => 'NotFound',
                default => 'Progress Report',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $response = $this->getResponseObject('success', 'Progress Report', $result['message'], [], $result['data'] ?? []);
        return $this->response->setJSON($response);
    }

    public function pullSection($versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version id.', [], []);
            return $this->response->setJSON($response);
        }

        $sectionKey = trim((string) $this->request->getPost('section_key'));
        if ($sectionKey === '') {
            $response = $this->getResponseObject('error', 'Validation_Error', 'section_key is required.', [], []);
            return $this->response->setJSON($response);
        }

        $result = $this->progressReportService->pullSectionData($versionId, $sectionKey, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'DRAFT_LOCKED' => 'DraftLocked',
                'NOT_FOUND' => 'NotFound',
                'VALIDATION_ERROR' => 'Validation_Error',
                default => 'Progress Report',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $response = $this->getResponseObject('success', 'Progress Report', $result['message'], [], $result['data'] ?? []);
        return $this->response->setJSON($response);
    }

    public function updateSectionState($versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version id.', [], []);
            return $this->response->setJSON($response);
        }

        $sectionKey = trim((string) $this->request->getPost('section_key'));
        if ($sectionKey === '') {
            $response = $this->getResponseObject('error', 'Validation_Error', 'section_key is required.', [], []);
            return $this->response->setJSON($response);
        }

        $sectionDataJson = trim((string) $this->request->getPost('section_data_json'));
        if ($sectionDataJson === '') {
            $response = $this->getResponseObject('error', 'Validation_Error', 'section_data_json is required.', [], []);
            return $this->response->setJSON($response);
        }
        $sectionData = json_decode($sectionDataJson, true);
        if (!is_array($sectionData)) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'section_data_json must be valid JSON object.', [], []);
            return $this->response->setJSON($response);
        }

        $pulledAt = trim((string) $this->request->getPost('pulled_at'));
        $manualPatch = [];

        $instructionalDomainCommentsJson = trim((string) $this->request->getPost('instructional_domain_comments_json'));
        if ($instructionalDomainCommentsJson !== '') {
            $instructionalDomainComments = json_decode($instructionalDomainCommentsJson, true);
            if (!is_array($instructionalDomainComments)) {
                $response = $this->getResponseObject('error', 'Validation_Error', 'instructional_domain_comments_json must be valid JSON object.', [], []);
                return $this->response->setJSON($response);
            }
            $manualPatch['instructional_programmes_domain_comments'] = $instructionalDomainComments;
        }

        $result = $this->progressReportService->updateSectionState(
            $versionId,
            $sectionKey,
            $sectionData,
            $pulledAt,
            $manualPatch,
            auth()->user()->id ?? null
        );
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'DRAFT_LOCKED' => 'DraftLocked',
                'NOT_FOUND' => 'NotFound',
                'VALIDATION_ERROR' => 'Validation_Error',
                default => 'Progress Report',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $response = $this->getResponseObject('success', 'Progress Report', $result['message'], [], $result['data'] ?? []);
        return $this->response->setJSON($response);
    }

    public function finalize($versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version id.', [], []);
            return $this->response->setJSON($response);
        }

        $result = $this->progressReportService->finalizeDraft($versionId, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'TEMPLATE_SETUP_REQUIRED' => 'TemplateSetupRequired',
                'TEMPLATE_FILE_MISSING' => 'TemplateFileMissing',
                'DRAFT_LOCKED' => 'DraftLocked',
                'FINALIZE_VALIDATION_ERROR' => 'Progress Report',
                'NOT_FOUND' => 'NotFound',
                default => 'Progress Report',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $data = $result['data'] ?? [];
        $data['pdf_url'] = base_url('reports/progress/version/' . $versionId . '/pdf');
        $response = $this->getResponseObject('success', 'Progress Report', $result['message'], [], $data);
        return $this->response->setJSON($response);
    }

    public function regenerate($versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version id.', [], []);
            return $this->response->setJSON($response);
        }

        $result = $this->progressReportService->regenerateDraft($versionId, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'TEMPLATE_SETUP_REQUIRED' => 'TemplateSetupRequired',
                'NOT_FINAL' => 'NotFinal',
                'NOT_FOUND' => 'NotFound',
                default => 'Progress Report',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $data = $result['data'] ?? [];
        $newVersionId = (int) ($data['version_id'] ?? 0);
        $data['draft_url'] = $newVersionId > 0 ? base_url('reports/progress/version/' . $newVersionId . '/draft') : null;
        $response = $this->getResponseObject('success', 'Progress Report', $result['message'], [], $data);
        return $this->response->setJSON($response);
    }

    public function deleteVersion($versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version id.', [], []);
            return $this->response->setJSON($response);
        }

        $result = $this->progressReportService->deleteLatestVersion($versionId, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'NOT_FOUND' => 'NotFound',
                'NOT_LATEST' => 'NotLatestVersion',
                'VALIDATION_ERROR' => 'Validation_Error',
                default => 'Progress Report',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $response = $this->getResponseObject('success', 'Progress Report', $result['message'], [], $result['data'] ?? []);
        return $this->response->setJSON($response);
    }

    public function instructionalImages($versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version id.', [], []);
            return $this->response->setJSON($response);
        }

        $result = $this->progressReportService->listInstructionalImages($versionId);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'NOT_FOUND' => 'NotFound',
                'VALIDATION_ERROR' => 'Validation_Error',
                default => 'Progress Report',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $data = $result['data'] ?? [];
        $data['images'] = $this->decorateInstructionalImages($versionId, is_array($data['images'] ?? null) ? $data['images'] : []);
        $response = $this->getResponseObject('success', 'Progress Report', $result['message'], [], $data);
        return $this->response->setJSON($response);
    }

    public function uploadInstructionalImages($versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version id.', [], []);
            return $this->response->setJSON($response);
        }

        $files = $this->request->getFileMultiple('images');
        if (!is_array($files) || empty($files)) {
            $single = $this->request->getFile('images');
            $files = $single ? [$single] : [];
        }

        $result = $this->progressReportService->uploadInstructionalImages($versionId, $files, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'DRAFT_LOCKED' => 'DraftLocked',
                'NOT_FOUND' => 'NotFound',
                'VALIDATION_ERROR' => 'Validation_Error',
                default => 'Progress Report',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $data = $result['data'] ?? [];
        $data['images'] = $this->decorateInstructionalImages($versionId, is_array($data['images'] ?? null) ? $data['images'] : []);
        $response = $this->getResponseObject('success', 'Progress Report', $result['message'], [], $data);
        return $this->response->setJSON($response);
    }

    public function deleteInstructionalImage($versionId, $artifactId)
    {
        $versionId = (int) $versionId;
        $artifactId = (int) $artifactId;
        if ($versionId <= 0 || $artifactId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version or image id.', [], []);
            return $this->response->setJSON($response);
        }

        $result = $this->progressReportService->deleteInstructionalImage($versionId, $artifactId, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'DRAFT_LOCKED' => 'DraftLocked',
                'NOT_FOUND' => 'NotFound',
                'VALIDATION_ERROR' => 'Validation_Error',
                default => 'Progress Report',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $data = $result['data'] ?? [];
        $data['images'] = $this->decorateInstructionalImages($versionId, is_array($data['images'] ?? null) ? $data['images'] : []);
        $response = $this->getResponseObject('success', 'Progress Report', $result['message'], [], $data);
        return $this->response->setJSON($response);
    }

    public function replaceInstructionalImage($versionId, $artifactId)
    {
        $versionId = (int) $versionId;
        $artifactId = (int) $artifactId;
        if ($versionId <= 0 || $artifactId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version or image id.', [], []);
            return $this->response->setJSON($response);
        }

        $file = $this->request->getFile('image');
        if (!$file) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Image file is required.', [], []);
            return $this->response->setJSON($response);
        }

        $result = $this->progressReportService->replaceInstructionalImage($versionId, $artifactId, $file, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'DRAFT_LOCKED' => 'DraftLocked',
                'NOT_FOUND' => 'NotFound',
                'VALIDATION_ERROR' => 'Validation_Error',
                default => 'Progress Report',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $data = $result['data'] ?? [];
        $data['images'] = $this->decorateInstructionalImages($versionId, is_array($data['images'] ?? null) ? $data['images'] : []);
        $response = $this->getResponseObject('success', 'Progress Report', $result['message'], [], $data);
        return $this->response->setJSON($response);
    }

    public function viewInstructionalImage($versionId, $artifactId)
    {
        $versionId = (int) $versionId;
        $artifactId = (int) $artifactId;
        if ($versionId <= 0 || $artifactId <= 0) {
            throw new PageNotFoundException('Invalid image reference.');
        }

        $artifact = $this->progressReportService->getInstructionalImageArtifact($versionId, $artifactId);
        if (!$artifact) {
            throw new PageNotFoundException('Instructional image not found.');
        }

        $storagePath = trim((string) ($artifact['storage_path'] ?? ''));
        $mimeType = trim((string) ($artifact['mime_type'] ?? 'application/octet-stream'));
        if ($storagePath === '') {
            throw new PageNotFoundException('Instructional image path missing.');
        }

        $fullPath = WRITEPATH . str_replace('/', DIRECTORY_SEPARATOR, ltrim($storagePath, '/'));
        if (!is_file($fullPath)) {
            throw new PageNotFoundException('Instructional image file not found.');
        }

        $content = file_get_contents($fullPath);
        if ($content === false) {
            throw new \RuntimeException('Unable to read instructional image file.');
        }

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Cache-Control', 'private, max-age=300')
            ->setBody($content);
    }

    public function deleteAll($reportId)
    {
        $reportId = (int) $reportId;
        if ($reportId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid report id.', [], []);
            return $this->response->setJSON($response);
        }

        $result = $this->progressReportService->deleteAllVersions($reportId, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'NOT_FOUND' => 'NotFound',
                'VALIDATION_ERROR' => 'Validation_Error',
                default => 'Progress Report',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $response = $this->getResponseObject('success', 'Progress Report', $result['message'], [], $result['data'] ?? []);
        return $this->response->setJSON($response);
    }

    public function pdf($versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            throw new PageNotFoundException('Invalid version id');
        }

        $artifact = $this->progressReportService->getLatestPdfArtifactByVersion($versionId);
        if (!$artifact) {
            throw new PageNotFoundException('PDF artifact not found.');
        }

        $fullPath = WRITEPATH . str_replace('/', DIRECTORY_SEPARATOR, ltrim((string) $artifact['storage_path'], '/'));
        if (!is_file($fullPath)) {
            throw new PageNotFoundException('PDF file not found.');
        }

        return $this->response->download($fullPath, null)->setFileName((string) ($artifact['file_name'] ?? 'progress-report.pdf'));
    }

    private function isValidYmd(string $date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }

        $dt = \DateTime::createFromFormat('Y-m-d', $date);
        return $dt && $dt->format('Y-m-d') === $date;
    }

    private function pendingActionResponse(string $action, array $data = [])
    {
        $response = $this->getResponseObject(
            'error',
            'Progress Report',
            'Progress Report action is pending implementation.',
            [],
            array_merge(['action' => $action], $data)
        );

        return $this->response->setJSON($response);
    }

    private function decodeJsonObject(string $json): array
    {
        $json = trim($json);
        if ($json === '') {
            return [];
        }
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function resolveProgressListStateFromRequest(): array
    {
        $token = trim((string) $this->request->getGet('s'));
        if ($token !== '') {
            $decodedState = $this->decodeProgressListStateToken($token);
            if (is_array($decodedState)) {
                return $this->sanitizeProgressListState($decodedState);
            }
        }

        return $this->sanitizeProgressListState([
            'client_id' => $this->request->getGet('client_id'),
            'start_date' => $this->request->getGet('start_date'),
            'end_date' => $this->request->getGet('end_date'),
            'dt_page' => $this->request->getGet('dt_page'),
        ]);
    }

    private function sanitizeProgressListState(array $state): array
    {
        $clientId = (int) ($state['client_id'] ?? 0);
        $clientId = $clientId > 0 ? $clientId : 0;

        $startDate = trim((string) ($state['start_date'] ?? ''));
        if ($startDate !== '' && !$this->isValidYmd($startDate)) {
            $startDate = '';
        }

        $endDate = trim((string) ($state['end_date'] ?? ''));
        if ($endDate !== '' && !$this->isValidYmd($endDate)) {
            $endDate = '';
        }

        $dtPage = (int) ($state['dt_page'] ?? 0);
        if ($dtPage < 0) {
            $dtPage = 0;
        }

        return [
            'client_id' => $clientId > 0 ? (string) $clientId : '',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'dt_page' => $dtPage,
        ];
    }

    private function hasProgressListState(array $state): bool
    {
        return
            trim((string) ($state['client_id'] ?? '')) !== ''
            || trim((string) ($state['start_date'] ?? '')) !== ''
            || trim((string) ($state['end_date'] ?? '')) !== ''
            || (int) ($state['dt_page'] ?? 0) > 0;
    }

    private function buildProgressListStateQueryFromState(array $state): string
    {
        if (!$this->hasProgressListState($state)) {
            return '';
        }

        $token = $this->encodeProgressListStateToken($state);
        if ($token === '') {
            return '';
        }

        return '?s=' . urlencode($token);
    }

    private function encodeProgressListStateToken(array $state): string
    {
        $payload = [
            'v' => self::STATE_TOKEN_VERSION,
            'exp' => time() + self::STATE_TOKEN_TTL_SECONDS,
            'state' => $this->sanitizeProgressListState($state),
        ];

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if (!is_string($json) || $json === '') {
            return '';
        }

        try {
            $encrypted = Services::encrypter()->encrypt($json);
            return 'e1.' . $this->base64UrlEncode($encrypted);
        } catch (Throwable $e) {
            // Fallback to signed token when encrypter key/config is not available.
            $payloadB64 = $this->base64UrlEncode($json);
            $signature = hash_hmac('sha256', $payloadB64, $this->getProgressStateTokenSecret(), true);
            return 's1.' . $payloadB64 . '.' . $this->base64UrlEncode($signature);
        }
    }

    private function decodeProgressListStateToken(string $token): ?array
    {
        $token = trim($token);
        if ($token === '') {
            return null;
        }

        if (str_starts_with($token, 's1.')) {
            $parts = explode('.', $token, 3);
            if (count($parts) !== 3) {
                return null;
            }

            $payloadB64 = trim((string) ($parts[1] ?? ''));
            $sigB64 = trim((string) ($parts[2] ?? ''));
            if ($payloadB64 === '' || $sigB64 === '') {
                return null;
            }

            $expectedSig = hash_hmac('sha256', $payloadB64, $this->getProgressStateTokenSecret(), true);
            $providedSig = $this->base64UrlDecode($sigB64);
            if (!is_string($providedSig) || $providedSig === '' || !hash_equals($expectedSig, $providedSig)) {
                return null;
            }

            $json = $this->base64UrlDecode($payloadB64);
            if (!is_string($json) || $json === '') {
                return null;
            }

            return $this->decodeProgressStatePayloadJson($json);
        }

        $encryptedToken = $token;
        if (str_starts_with($token, 'e1.')) {
            $encryptedToken = substr($token, 3);
        }

        $decoded = $this->base64UrlDecode($encryptedToken);
        if (!is_string($decoded) || $decoded === '') {
            return null;
        }

        try {
            $json = Services::encrypter()->decrypt($decoded);
            if (!is_string($json) || $json === '') {
                return null;
            }

            return $this->decodeProgressStatePayloadJson($json);
        } catch (Throwable $e) {
            return null;
        }
    }

    private function decodeProgressStatePayloadJson(string $json): ?array
    {
        $payload = json_decode($json, true);
        if (!is_array($payload)) {
            return null;
        }
        if ((int) ($payload['v'] ?? 0) !== self::STATE_TOKEN_VERSION) {
            return null;
        }
        $exp = (int) ($payload['exp'] ?? 0);
        if ($exp > 0 && $exp < time()) {
            return null;
        }

        $state = $payload['state'] ?? null;
        return is_array($state) ? $state : null;
    }

    private function getProgressStateTokenSecret(): string
    {
        $sessionId = session_id();
        if ($sessionId === '' && function_exists('session')) {
            try {
                $session = session();
                if ($session !== null && method_exists($session, 'getId')) {
                    $sessionId = (string) $session->getId();
                }
            } catch (Throwable $e) {
                $sessionId = '';
            }
        }

        $userId = '';
        try {
            if (function_exists('auth') && auth()->user() !== null) {
                $userId = (string) (auth()->user()->id ?? '');
            }
        } catch (Throwable $e) {
            $userId = '';
        }

        $encKey = trim((string) (config('Encryption')->key ?? ''));
        $baseSecret = $encKey !== '' ? $encKey : (string) (config('App')->baseURL ?? 'progress-report');
        $material = $baseSecret . '|' . $userId . '|' . $sessionId . '|progress-list-state';

        return hash('sha256', $material, true);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $base64 = strtr($value, '-_', '+/');
        $padding = strlen($base64) % 4;
        if ($padding > 0) {
            $base64 .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($base64, true);
        return is_string($decoded) ? $decoded : null;
    }

    private function decorateInstructionalImages(int $versionId, array $images): array
    {
        $decorated = [];
        foreach ($images as $image) {
            if (!is_array($image)) {
                continue;
            }

            $artifactId = (int) ($image['artifact_id'] ?? 0);
            if ($artifactId <= 0) {
                continue;
            }

            $image['view_url'] = base_url('reports/progress/version/' . $versionId . '/instructional-images/' . $artifactId . '/view');
            $decorated[] = $image;
        }

        return $decorated;
    }
}
