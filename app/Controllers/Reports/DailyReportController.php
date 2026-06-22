<?php

namespace App\Controllers\Reports;

use App\Controllers\AdminController;
use App\Libraries\Reports\ReportEmailStatus;
use App\Models\ClientConfiguration\ClientModel;
use App\Models\Reports\DailyReportQueryModel;
use App\Services\Reports\DailyReportEmailService;
use App\Services\Reports\DailyReportService;
use Config\Services;
use CodeIgniter\Exceptions\PageNotFoundException;
use Throwable;

class DailyReportController extends AdminController
{
    private const STATE_TOKEN_VERSION = 1;
    private const STATE_TOKEN_TTL_SECONDS = 2592000; // 30 days

    protected DailyReportService $dailyReportService;

    public function __construct()
    {
        $this->dailyReportService = new DailyReportService();
    }

    public function index()
    {
        $this->page_title = 'Reporting | Daily Reports';
        $clientModel = new ClientModel();
        $clients = $clientModel->get_active_client_list();
        $initialState = $this->resolveDailyListStateFromRequest();

        return view('Reports/Daily/index', [
            'page_title' => $this->page_title,
            'clients' => $clients,
            'initial_state' => $initialState,
        ]);
    }

    public function stateToken()
    {
        $state = $this->sanitizeDailyListState([
            'client_id' => $this->request->getPost('client_id'),
            'start_date' => $this->request->getPost('start_date'),
            'end_date' => $this->request->getPost('end_date'),
            'dt_page' => $this->request->getPost('dt_page'),
        ]);

        $stateQuery = $this->buildDailyListStateQueryFromState($state);
        if ($this->hasDailyListState($state) && $stateQuery === '') {
            $response = $this->getResponseObject('error', 'Daily Report', 'Failed to encode URL state.', [], []);
            return $this->response->setJSON($response);
        }

        $response = $this->getResponseObject('success', 'Daily Report', 'State encoded successfully.', [], [
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
            $response = $this->getResponseObject('success', 'DailyReports', 'Listed successfully', [], []);
            return $this->response->setJSON($response);
        }
        if (($startDate !== '' && !$this->isValidYmd($startDate)) || ($endDate !== '' && !$this->isValidYmd($endDate))) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid date range.', [], []);
            return $this->response->setJSON($response);
        }

        $queryModel = new DailyReportQueryModel();
        $rows = $queryModel->listBySubject($subjectId, $startDate ?: null, $endDate ?: null);

        $data = [];
        foreach ($rows as $row) {
            $emailStatus = $row['email_status'] ?? ReportEmailStatus::NOT_SENT;
            $latestStatus = strtoupper(trim((string) ($row['latest_status'] ?? 'FINAL')));
            if ($latestStatus === '') {
                $latestStatus = 'FINAL';
            }

            $data[] = [
                'report_id' => (int) ($row['report_id'] ?? 0),
                'subject_id' => (int) ($row['subject_id'] ?? 0),
                'internal_mrn' => $row['internal_mrn'] ?? '',
                'learner_name' => trim((string) ($row['learner_name'] ?? '')),
                'report_date' => $row['report_date'] ?? '',
                'report_date_display' => !empty($row['report_date']) ? app_date($row['report_date']) : '',
                'latest_version_no' => (int) ($row['latest_version_no'] ?? 0),
                'latest_version_id' => isset($row['version_id']) ? (int) $row['version_id'] : null,
                'latest_artifact_id' => isset($row['latest_artifact_id']) ? (int) $row['latest_artifact_id'] : null,
                'latest_status' => $latestStatus,
                'generated_at' => $row['generated_at'] ?? null,
                'generated_at_display' => !empty($row['generated_at']) ? app_date($row['generated_at'], true) : '',
                'generated_by_name' => trim((string) ($row['generated_by_name'] ?? '')),
                'email_status' => $emailStatus,
                'email_status_label' => ReportEmailStatus::label($emailStatus),
                'email_sent_at' => $row['email_sent_at'] ?? null,
                'email_action_by_name' => trim((string) ($row['email_action_by_name'] ?? '')),
            ];
        }

        $response = $this->getResponseObject('success', 'DailyReports', 'Listed successfully', [], $data);
        return $this->response->setJSON($response);
    }

    public function checkGenerate()
    {
        $subjectId = (int) $this->request->getPost('subject_id');
        $reportDate = trim((string) $this->request->getPost('report_date'));

        if ($subjectId <= 0 || !$this->isValidYmd($reportDate)) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'subject_id and valid report_date are required.', [], []);
            return $this->response->setJSON($response);
        }

        $result = $this->dailyReportService->checkGenerateDraft($subjectId, $reportDate);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'NO_SESSION' => 'NoSession',
                'ACTIVE_DRAFT_EXISTS' => 'ActiveDraftExists',
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'TEMPLATE_SETUP_REQUIRED' => 'TemplateSetupRequired',
                default => 'Validation_Error',
            };
            $data = $result['data'] ?? [];
            $draftVersionId = (int) ($data['version_id'] ?? 0);
            if ($draftVersionId > 0) {
                $data['draft_url'] = base_url('reports/daily/version/' . $draftVersionId . '/draft');
            }
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $data);
            return $this->response->setJSON($response);
        }

        $response = $this->getResponseObject('success', 'GenerateCheck', $result['message'], [], $result['data'] ?? []);
        return $this->response->setJSON($response);
    }

    public function generate()
    {
        $subjectId = (int) $this->request->getPost('subject_id');
        $reportDate = trim((string) $this->request->getPost('report_date'));

        if ($subjectId <= 0 || !$this->isValidYmd($reportDate)) {
            $response = $this->getResponseObject(
                'error',
                'Validation_Error',
                'subject_id and report_date(YYYY-MM-DD) are required.',
                ['subject_id' => 'Invalid subject_id', 'report_date' => 'Invalid report_date'],
                []
            );
            return $this->response->setJSON($response);
        }

        $result = $this->dailyReportService->createDraft($subjectId, $reportDate, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'NO_SESSION' => 'NoSession',
                'ACTIVE_DRAFT_EXISTS' => 'ActiveDraftExists',
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'TEMPLATE_SETUP_REQUIRED' => 'TemplateSetupRequired',
                default => 'DailyReport',
            };
            $data = $result['data'] ?? [];
            $draftVersionId = (int) ($data['version_id'] ?? 0);
            if ($draftVersionId > 0) {
                $data['draft_url'] = base_url('reports/daily/version/' . $draftVersionId . '/draft');
            }
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $data);
            return $this->response->setJSON($response);
        }

        $data = $result['data'] ?? [];
        $versionId = (int) ($data['version_id'] ?? 0);
        $data['draft_url'] = $versionId > 0 ? base_url('reports/daily/version/' . $versionId . '/draft') : null;

        $response = $this->getResponseObject('success', 'DailyReport', 'Draft generated successfully.', [], $data);
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

        $queryModel = new DailyReportQueryModel();
        $rows = $queryModel->listVersionsByReportId($reportId);
        $data = [];
        foreach ($rows as $row) {
            $emailStatus = $row['email_status'] ?? ReportEmailStatus::NOT_SENT;
            $status = strtoupper(trim((string) ($row['status'] ?? 'FINAL')));
            if ($status === '') {
                $status = 'FINAL';
            }

            $data[] = [
                'version_id' => (int) ($row['version_id'] ?? 0),
                'version_no' => (int) ($row['version_no'] ?? 0),
                'status' => $status,
                'generated_at' => $row['generated_at'] ?? null,
                'generated_at_display' => !empty($row['generated_at']) ? app_date($row['generated_at'], true) : '',
                'generated_by_name' => trim((string) ($row['generated_by_name'] ?? '')),
                'artifact_id' => isset($row['artifact_id']) ? (int) $row['artifact_id'] : null,
                'file_name' => (string) ($row['file_name'] ?? ''),
                'email_status' => $emailStatus,
                'email_status_label' => ReportEmailStatus::label($emailStatus),
                'email_sent_at' => $row['email_sent_at'] ?? null,
                'email_action_by_name' => trim((string) ($row['email_action_by_name'] ?? '')),
            ];
        }

        $response = $this->getResponseObject('success', 'DailyReportVersions', 'Listed successfully.', [], $data);
        return $this->response->setJSON($response);
    }

    public function draft($versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            throw new PageNotFoundException('Invalid version id');
        }

        $version = $this->dailyReportService->getVersionContext($versionId);
        if (!$version) {
            throw new PageNotFoundException('Daily Report draft not found.');
        }

        $workflowStatus = strtoupper(trim((string) ($version['workflow_status'] ?? 'FINAL')));
        if ($workflowStatus === '' || $workflowStatus === 'FINAL') {
            return redirect()->to(base_url('reports/daily/version/' . $versionId . '/pdf'));
        }

        $manualData = $this->decodeJsonObject((string) ($version['manual_json'] ?? '{}'));
        $snapshot = $this->decodeJsonObject((string) ($version['snapshot_json'] ?? '{}'));
        $sectionStatus = $this->decodeJsonObject((string) ($version['section_status_json'] ?? '{}'));
        $dailySectionData = [];
        if (isset($snapshot['sections']['daily_content']['data']) && is_array($snapshot['sections']['daily_content']['data'])) {
            $dailySectionData = $snapshot['sections']['daily_content']['data'];
        }

        $this->page_title = 'Reporting | Daily Report Draft';
        $listState = $this->resolveDailyListStateFromRequest();

        return view('Reports/Daily/draft', [
            'page_title' => $this->page_title,
            'version' => $version,
            'manual_data' => $manualData,
            'draft_section_data' => $dailySectionData,
            'section_status' => $sectionStatus,
            'daily_image_limits' => $this->dailyReportService->getDailyImageLimits(),
            'daily_list_url' => base_url('reports/daily'),
            'list_state_query' => $this->buildDailyListStateQueryFromState($listState),
        ]);
    }

    public function saveDraft($versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version id.', [], []);
            return $this->response->setJSON($response);
        }

        $manualData = [];
        $manualJson = trim((string) $this->request->getPost('manual_json'));
        if ($manualJson !== '') {
            $decoded = json_decode($manualJson, true);
            if (!is_array($decoded)) {
                $response = $this->getResponseObject('error', 'Validation_Error', 'manual_json must be valid JSON object.', [], []);
                return $this->response->setJSON($response);
            }
            $manualData = $decoded;
        }

        $result = $this->dailyReportService->saveDraft($versionId, $manualData, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'DRAFT_LOCKED' => 'DraftLocked',
                'NOT_FOUND' => 'NotFound',
                default => 'DailyReport',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $response = $this->getResponseObject('success', 'DailyReport', $result['message'], [], $result['data'] ?? []);
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

        $result = $this->dailyReportService->pullSectionData($versionId, $sectionKey, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'DRAFT_LOCKED' => 'DraftLocked',
                'NOT_FOUND' => 'NotFound',
                'VALIDATION_ERROR' => 'Validation_Error',
                default => 'DailyReport',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $response = $this->getResponseObject('success', 'DailyReport', $result['message'], [], $result['data'] ?? []);
        return $this->response->setJSON($response);
    }

    public function images($versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version id.', [], []);
            return $this->response->setJSON($response);
        }

        $result = $this->dailyReportService->listDailyImages($versionId);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'NOT_FOUND' => 'NotFound',
                'VALIDATION_ERROR' => 'Validation_Error',
                default => 'DailyReport',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $data = $result['data'] ?? [];
        $data['images'] = $this->decorateDailyImages($versionId, is_array($data['images'] ?? null) ? $data['images'] : []);
        $response = $this->getResponseObject('success', 'DailyReport', $result['message'], [], $data);
        return $this->response->setJSON($response);
    }

    public function uploadImages($versionId)
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

        $result = $this->dailyReportService->uploadDailyImages($versionId, $files, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'DRAFT_LOCKED' => 'DraftLocked',
                'NOT_FOUND' => 'NotFound',
                'VALIDATION_ERROR' => 'Validation_Error',
                default => 'DailyReport',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $data = $result['data'] ?? [];
        $data['images'] = $this->decorateDailyImages($versionId, is_array($data['images'] ?? null) ? $data['images'] : []);
        $response = $this->getResponseObject('success', 'DailyReport', $result['message'], [], $data);
        return $this->response->setJSON($response);
    }

    public function deleteImage($versionId, $artifactId)
    {
        $versionId = (int) $versionId;
        $artifactId = (int) $artifactId;
        if ($versionId <= 0 || $artifactId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version or image id.', [], []);
            return $this->response->setJSON($response);
        }

        $result = $this->dailyReportService->deleteDailyImage($versionId, $artifactId, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'DRAFT_LOCKED' => 'DraftLocked',
                'NOT_FOUND' => 'NotFound',
                'VALIDATION_ERROR' => 'Validation_Error',
                default => 'DailyReport',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $data = $result['data'] ?? [];
        $data['images'] = $this->decorateDailyImages($versionId, is_array($data['images'] ?? null) ? $data['images'] : []);
        $response = $this->getResponseObject('success', 'DailyReport', $result['message'], [], $data);
        return $this->response->setJSON($response);
    }

    public function replaceImage($versionId, $artifactId)
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

        $result = $this->dailyReportService->replaceDailyImage($versionId, $artifactId, $file, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'DRAFT_LOCKED' => 'DraftLocked',
                'NOT_FOUND' => 'NotFound',
                'VALIDATION_ERROR' => 'Validation_Error',
                default => 'DailyReport',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $data = $result['data'] ?? [];
        $data['images'] = $this->decorateDailyImages($versionId, is_array($data['images'] ?? null) ? $data['images'] : []);
        $response = $this->getResponseObject('success', 'DailyReport', $result['message'], [], $data);
        return $this->response->setJSON($response);
    }

    public function viewImage($versionId, $artifactId)
    {
        $versionId = (int) $versionId;
        $artifactId = (int) $artifactId;
        if ($versionId <= 0 || $artifactId <= 0) {
            throw new PageNotFoundException('Invalid image reference.');
        }

        $artifact = $this->dailyReportService->getDailyImageArtifact($versionId, $artifactId);
        if (!$artifact) {
            throw new PageNotFoundException('Daily image not found.');
        }

        $storagePath = trim((string) ($artifact['storage_path'] ?? ''));
        $mimeType = trim((string) ($artifact['mime_type'] ?? 'application/octet-stream'));
        if ($storagePath === '') {
            throw new PageNotFoundException('Daily image path missing.');
        }

        $fullPath = WRITEPATH . str_replace('/', DIRECTORY_SEPARATOR, ltrim($storagePath, '/'));
        if (!is_file($fullPath)) {
            throw new PageNotFoundException('Daily image file not found.');
        }

        $content = file_get_contents($fullPath);
        if ($content === false) {
            throw new \RuntimeException('Unable to read daily image file.');
        }

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Cache-Control', 'private, max-age=300')
            ->setBody($content);
    }

    public function finalize($versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version id.', [], []);
            return $this->response->setJSON($response);
        }

        $result = $this->dailyReportService->finalizeDraft($versionId, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'TEMPLATE_SETUP_REQUIRED' => 'TemplateSetupRequired',
                'TEMPLATE_FILE_MISSING' => 'TemplateFileMissing',
                'DRAFT_LOCKED' => 'DraftLocked',
                'FINALIZE_VALIDATION_ERROR' => 'DailyReport',
                'NOT_FOUND' => 'NotFound',
                default => 'DailyReport',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $data = $result['data'] ?? [];
        $data['pdf_url'] = base_url('reports/daily/version/' . $versionId . '/pdf');
        $response = $this->getResponseObject('success', 'DailyReport', $result['message'], [], $data);
        return $this->response->setJSON($response);
    }

    public function regenerate($versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version id.', [], []);
            return $this->response->setJSON($response);
        }

        $result = $this->dailyReportService->regenerateDraft($versionId, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'DB_SETUP_REQUIRED' => 'DbSetupRequired',
                'TEMPLATE_SETUP_REQUIRED' => 'TemplateSetupRequired',
                'ACTIVE_DRAFT_EXISTS' => 'ActiveDraftExists',
                'NOT_FINAL' => 'NotFinal',
                'NOT_FOUND' => 'NotFound',
                default => 'DailyReport',
            };
            $data = $result['data'] ?? [];
            $draftVersionId = (int) ($data['version_id'] ?? 0);
            if ($draftVersionId > 0) {
                $data['draft_url'] = base_url('reports/daily/version/' . $draftVersionId . '/draft');
            }
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $data);
            return $this->response->setJSON($response);
        }

        $data = $result['data'] ?? [];
        $newVersionId = (int) ($data['version_id'] ?? 0);
        $data['draft_url'] = $newVersionId > 0 ? base_url('reports/daily/version/' . $newVersionId . '/draft') : null;
        $response = $this->getResponseObject('success', 'DailyReport', $result['message'], [], $data);
        return $this->response->setJSON($response);
    }

    public function deleteVersion($versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid version id.', [], []);
            return $this->response->setJSON($response);
        }

        $result = $this->dailyReportService->deleteLatestVersion($versionId, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'NOT_FOUND' => 'NotFound',
                'NOT_LATEST' => 'NotLatestVersion',
                'VALIDATION_ERROR' => 'Validation_Error',
                default => 'DailyReport',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $response = $this->getResponseObject('success', 'DailyReport', $result['message'], [], $result['data'] ?? []);
        return $this->response->setJSON($response);
    }

    public function deleteAll($reportId)
    {
        $reportId = (int) $reportId;
        if ($reportId <= 0) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'Invalid report id.', [], []);
            return $this->response->setJSON($response);
        }

        $result = $this->dailyReportService->deleteAllVersions($reportId, auth()->user()->id ?? null);
        if (!$result['success']) {
            $statusText = match ($result['code'] ?? '') {
                'NOT_FOUND' => 'NotFound',
                'VALIDATION_ERROR' => 'Validation_Error',
                default => 'DailyReport',
            };
            $response = $this->getResponseObject('error', $statusText, $result['message'], [], $result['data'] ?? []);
            return $this->response->setJSON($response);
        }

        $response = $this->getResponseObject('success', 'DailyReport', $result['message'], [], $result['data'] ?? []);
        return $this->response->setJSON($response);
    }

    public function pdf($versionId)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            throw new PageNotFoundException('Invalid version id');
        }

        $queryModel = new DailyReportQueryModel();
        $artifact = $queryModel->getLatestPdfArtifactByVersion($versionId);

        if (!$artifact) {
            throw new PageNotFoundException('PDF artifact not found.');
        }

        $fullPath = $this->dailyReportService->resolvePdfPathForDownload($versionId, $artifact);
        if ($fullPath === null || !is_file($fullPath)) {
            throw new PageNotFoundException('PDF file not found.');
        }

        return $this->response->download($fullPath, null)->setFileName((string) ($artifact['file_name'] ?? 'daily-report.pdf'));
    }

    public function send()
    {
        $versionId = (int) $this->request->getPost('version_id');
        $toEmail = trim((string) $this->request->getPost('to_email'));
        $ccEmail = trim((string) $this->request->getPost('cc_email'));

        if ($versionId <= 0 || $toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            $response = $this->getResponseObject('error', 'Validation_Error', 'version_id and valid to_email are required.', ['version_id' => 'Invalid version_id', 'to_email' => 'Invalid email'], []);
            return $this->response->setJSON($response);
        }

        $service = new DailyReportEmailService();
        $result = $service->send($versionId, $toEmail, $ccEmail, auth()->user()->id ?? null);
        if (!$result['success']) {
            $response = $this->getResponseObject('error', 'DailyReportEmail', $result['message'], [], []);
            return $this->response->setJSON($response);
        }

        $response = $this->getResponseObject('success', 'DailyReportEmail', 'Email request logged successfully.', [], $result['data']);
        return $this->response->setJSON($response);
    }

    private function isValidYmd(string $date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }
        $dt = \DateTime::createFromFormat('Y-m-d', $date);
        return $dt && $dt->format('Y-m-d') === $date;
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

    private function resolveDailyListStateFromRequest(): array
    {
        $token = trim((string) $this->request->getGet('s'));
        if ($token !== '') {
            $decodedState = $this->decodeDailyListStateToken($token);
            if (is_array($decodedState)) {
                return $this->sanitizeDailyListState($decodedState);
            }
        }

        return $this->sanitizeDailyListState([
            'client_id' => $this->request->getGet('client_id'),
            'start_date' => $this->request->getGet('start_date'),
            'end_date' => $this->request->getGet('end_date'),
            'dt_page' => $this->request->getGet('dt_page'),
        ]);
    }

    private function sanitizeDailyListState(array $state): array
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

    private function hasDailyListState(array $state): bool
    {
        return
            trim((string) ($state['client_id'] ?? '')) !== ''
            || trim((string) ($state['start_date'] ?? '')) !== ''
            || trim((string) ($state['end_date'] ?? '')) !== ''
            || (int) ($state['dt_page'] ?? 0) > 0;
    }

    private function buildDailyListStateQueryFromState(array $state): string
    {
        if (!$this->hasDailyListState($state)) {
            return '';
        }

        $token = $this->encodeDailyListStateToken($state);
        if ($token === '') {
            return '';
        }

        return '?s=' . urlencode($token);
    }

    private function encodeDailyListStateToken(array $state): string
    {
        $payload = [
            'v' => self::STATE_TOKEN_VERSION,
            'exp' => time() + self::STATE_TOKEN_TTL_SECONDS,
            'state' => $this->sanitizeDailyListState($state),
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
            $signature = hash_hmac('sha256', $payloadB64, $this->getDailyStateTokenSecret(), true);
            return 's1.' . $payloadB64 . '.' . $this->base64UrlEncode($signature);
        }
    }

    private function decodeDailyListStateToken(string $token): ?array
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

            $expectedSig = hash_hmac('sha256', $payloadB64, $this->getDailyStateTokenSecret(), true);
            $providedSig = $this->base64UrlDecode($sigB64);
            if (!is_string($providedSig) || $providedSig === '' || !hash_equals($expectedSig, $providedSig)) {
                return null;
            }

            $json = $this->base64UrlDecode($payloadB64);
            if (!is_string($json) || $json === '') {
                return null;
            }

            return $this->decodeDailyStatePayloadJson($json);
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

            return $this->decodeDailyStatePayloadJson($json);
        } catch (Throwable $e) {
            return null;
        }
    }

    private function decodeDailyStatePayloadJson(string $json): ?array
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

    private function getDailyStateTokenSecret(): string
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
        $baseSecret = $encKey !== '' ? $encKey : (string) (config('App')->baseURL ?? 'daily-report');
        $material = $baseSecret . '|' . $userId . '|' . $sessionId . '|daily-list-state';

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

    private function decorateDailyImages(int $versionId, array $images): array
    {
        foreach ($images as &$image) {
            $artifactId = (int) ($image['artifact_id'] ?? 0);
            if ($artifactId <= 0) {
                continue;
            }

            $cacheVersion = trim((string) ($image['file_name'] ?? ''));
            if ($cacheVersion === '') {
                $cacheVersion = 'artifact-' . $artifactId;
            }

            $image['view_url'] = base_url('reports/daily/version/' . $versionId . '/images/' . $artifactId . '/view')
                . '?v=' . rawurlencode($cacheVersion);
        }
        unset($image);

        return $images;
    }
}
