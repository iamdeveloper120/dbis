<?= $this->extend("layout/master") ?>

<?= $this->section("head_tag") ?>
<style>
    .page-loader-overlay {
        position: fixed;
        inset: 0;
        z-index: 2000;
        background: rgba(255, 255, 255, 0.75);
        display: none;
        align-items: center;
        justify-content: center;
    }

    .progress-paper {
        background: #fff;
        border: 1px solid #dfe3ea;
        border-radius: 8px;
        padding: 18px 18px 14px 18px;
    }

    .pr-header-table,
    .pr-meta-table,
    .pr-grid {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .pr-header-table td {
        vertical-align: top;
        padding: 0;
    }

    .pr-header-left .line-title {
        font-size: 12px;
        font-weight: 600;
        line-height: 1.2;
    }

    .pr-header-left .line-sub {
        font-size: 12px;
        line-height: 1.2;
    }

    .pr-header-center {
        text-align: center;
    }

    .pr-logo {
        max-width: 78px;
        max-height: 78px;
        width: auto;
        height: auto;
        display: inline-block;
        margin-bottom: 4px;
    }

    .pr-logo-placeholder {
        width: 78px;
        height: 78px;
        border: 1px dashed #b8bfca;
        border-radius: 6px;
        margin: 0 auto 4px auto;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        color: #6c757d;
    }

    .pr-header-right {
        text-align: right;
        font-size: 12px;
        line-height: 1.25;
    }

    .pr-title {
        text-align: center;
        margin: 12px 0 12px 0;
        font-size: 18px;
        font-weight: 600;
        letter-spacing: 0.2px;
    }

    .pr-meta-table td {
        border: 1px solid #dfe3ea;
        padding: 8px 10px;
        vertical-align: top;
    }

    .pr-meta-label {
        display: block;
        font-size: 12px;
        color: #495057;
        font-weight: 600;
        margin-bottom: 2px;
    }

    .pr-meta-value {
        font-size: 13px;
        color: #212529;
        line-height: 1.25;
        min-height: 20px;
    }

    .pr-section {
        margin-top: 16px;
    }

    .pr-section-header {
        border-bottom: 1px solid #dfe3ea;
        margin-bottom: 10px;
        padding-bottom: 6px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .pr-section-title {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
        color: #1f2937;
    }

    .pr-graph-placeholder {
        border: 1px dashed #b8bfca;
        border-radius: 6px;
        min-height: 170px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 12px;
        color: #6c757d;
        background: #fcfcfd;
        text-align: center;
        font-size: 13px;
    }

    .pr-progress-legend {
        text-align: center;
        font-size: 13px;
        color: #1f2937;
        margin-bottom: 8px;
    }

    .pr-progress-legend img {
        width: 30px;
        height: auto;
        vertical-align: middle;
        margin-right: 4px;
    }

    .pr-dataset-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 12px;
        align-items: center;
        min-height: 22px;
    }

    .pr-dataset-legend-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: #374151;
    }

    .pr-dataset-legend-swatch {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 1px solid rgba(0, 0, 0, 0.2);
        display: inline-block;
        flex-shrink: 0;
    }

    .pr-dataset-legend-label {
        line-height: 1.2;
        max-width: 210px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .pr-chart-wrap {
        border: 1px dashed #b8bfca;
        border-radius: 6px;
        padding: 10px;
        min-height: 308px;
        background: #fcfcfd;
        position: relative;
    }

    .pr-chart-empty {
        min-height: 275px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: #6c757d;
        font-size: 13px;
        padding: 8px;
    }

    .pr-chart-canvas {
        width: 100% !important;
        height: 275px !important;
    }

    .pr-figure-caption {
        margin-top: 6px;
        font-size: 12px;
        color: #495057;
    }

    .pr-grid th,
    .pr-grid td {
        border: 1px solid #dfe3ea;
        padding: 8px 10px;
        vertical-align: top;
        font-size: 13px;
    }

    .pr-target-line {
        margin: 0;
        line-height: 1.35;
    }

    .pr-grid th {
        background: #f8f9fb;
        font-weight: 600;
    }

    .pm-current-programmes {
        margin-top: 6px;
    }

    .pm-programme-item {
        line-height: 1.45;
        color: #212529;
    }

    .pm-domain-label {
        color: #4b5563;
        font-weight: 700;
    }

    .pm-domain-separator {
        font-weight: 700;
        color: #4b5563;
    }

    .pr-textarea-label {
        font-size: 12px;
        color: #495057;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .pr-section-note {
        font-size: 12px;
        color: #6c757d;
    }

    .pr-domain-title {
        font-size: 14px;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 10px;
    }

    .pr-auto-text-box {
        border: 1px dashed #b8bfca;
        border-radius: 6px;
        padding: 8px 10px;
        min-height: 38px;
        font-size: 13px;
        color: #1f2937;
        line-height: 1.4;
        white-space: pre-wrap;
        word-break: break-word;
        background: #fcfcfd;
    }

    #instructional_domains_container {
        display: block;
    }

    .pr-domain-block {
        border: 1px solid #d9dfe9;
        border-radius: 6px;
        padding: 10px;
        margin-bottom: 12px;
        background: #fff;
    }

    .pr-domain-layout {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        align-items: start;
    }

    .pr-domain-goals-table {
        margin-bottom: 0;
    }

    .pr-domain-comment-row {
        margin-top: 8px;
    }

    .pr-footer {
        border-top: 1px solid #cfd6e2;
        margin-top: 20px;
        padding-top: 6px;
    }

    .pr-footer table {
        width: 100%;
        border-collapse: collapse;
    }

    .pr-footer td {
        font-size: 12px;
        color: #1f2937;
        vertical-align: top;
        padding: 0;
    }

    .pr-footer-left {
        width: 45%;
        text-align: left;
    }

    .pr-footer-right {
        width: 55%;
        text-align: right;
    }

    .progress-image-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 12px;
    }

    .progress-image-item {
        border: 1px solid #dfe3ea;
        border-radius: 6px;
        padding: 10px;
        background: #fff;
    }

    .progress-image-thumb {
        height: 140px;
        border: 1px dashed #c9d0dc;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fcfcfd;
        overflow: hidden;
        margin-bottom: 8px;
    }

    .progress-image-thumb img {
        max-width: 100%;
        max-height: 100%;
        display: block;
    }

    .progress-image-meta {
        font-size: 12px;
        color: #6c757d;
        line-height: 1.45;
        word-break: break-word;
    }

    @media (max-width: 991.98px) {
        .progress-paper {
            padding: 12px;
        }

        .pr-domain-layout {
            grid-template-columns: 1fr;
        }

        .pr-header-right,
        .pr-header-left .line-title,
        .pr-header-left .line-sub {
            font-size: 11px;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section("page_content") ?>
<?php
    $headerLine1 = trim((string) (setting('Report.headerLine1') ?? ''));
    $headerLine2 = trim((string) (setting('Report.headerLine2') ?? ''));
    $headerLine3 = trim((string) (setting('Report.headerLine3') ?? ''));
    $headerLine4 = trim((string) (setting('Report.headerLine4') ?? ''));
    $headerCenterCaption = trim((string) (setting('Report.headerCenterCaption') ?? ''));
    $reportPhone = trim((string) (setting('Report.phone') ?? ''));
    $reportWebsite = trim((string) (setting('Report.website') ?? ''));
    $reportLocationLine = trim((string) (setting('Report.locationLine') ?? ''));
    $footerCompany = trim((string) (setting('Report.footerCompany') ?? ''));
    $footerAddressLine1 = trim((string) (setting('Report.footerAddressLine1') ?? ''));
    $footerAddressLine2 = trim((string) (setting('Report.footerAddressLine2') ?? ''));
    $hasLogo = trim((string) (setting('Report.logoPath') ?? '')) !== '';

    $manualFieldMap = [
        'approved_by' => 'approved_by',
        'programme_management_comment' => 'programme_management_comment',
        'instructional_programmes_comment' => 'instructional_programmes_comment',
        'instructional_programmes_domain_comments' => 'instructional_programmes_domain_comments',
        'instructional_programmes_images' => 'instructional_programmes_images',
        'manding_comment' => 'manding_comment',
        'problem_behaviour_reduction_comment' => 'problem_behaviour_reduction_comment',
        'conclusion_comment' => 'conclusion_comment',
    ];

    $autoSectionMap = [
        'current_programme_management' => [
            'sessions_count' => 'pm.sessions_count',
            'hours_of_instruction' => 'pm.hours_of_instruction',
            'dti_net_ratio' => 'pm.dti_net_ratio',
            'schedule_of_reinforcement' => 'pm.schedule_of_reinforcement',
            'current_programmes' => 'pm.current_programmes',
        ],
        'progress' => [
            'cumulative_all_time_graph' => 'progress.cumulative_all_time_graph',
            'cumulative_period_graph' => 'progress.cumulative_period_graph',
        ],
        'instructional_programmes' => [
            'domains' => 'instructional.domains',
            'domain_graphs' => 'instructional.domain_graphs',
            'goal_mastered_targets' => 'instructional.goal_mastered_targets',
        ],
        'manding' => [
            'mand_graphs' => 'manding.graphs',
        ],
        'problem_behaviour_reduction' => [
            'pb_graphs' => 'problem_behaviour.graphs',
        ],
    ];

    $approvedBy = (string) ($manual_data['approved_by'] ?? '');
    $reportedByName = trim((string) ($version['reported_by_name'] ?? ''));
    $programmeManagementComment = (string) ($manual_data['programme_management_comment'] ?? '');
    $instructionalDomainComments = $manual_data['instructional_programmes_domain_comments'] ?? [];
    if (!is_array($instructionalDomainComments)) {
        $instructionalDomainComments = [];
    }
    $mandingComment = (string) ($manual_data['manding_comment'] ?? '');
    $problemBehaviourComment = (string) ($manual_data['problem_behaviour_reduction_comment'] ?? '');
    $conclusionComment = (string) ($manual_data['conclusion_comment'] ?? ($manual_data['draft_notes'] ?? ''));
    $canManageInstructionalImages = auth()->user()->can('client-profile.reports.progress.save-draft');
    $pulledSections = $manual_data['pulled_sections'] ?? [];
    if (!is_array($pulledSections)) {
        $pulledSections = [];
    }
    $progressImageMaxSizeMb = (int) (setting('Report.progressImageMaxSizeMb') ?? 1);
    if ($progressImageMaxSizeMb < 1) {
        $progressImageMaxSizeMb = 1;
    } elseif ($progressImageMaxSizeMb > 10) {
        $progressImageMaxSizeMb = 10;
    }
    $progressImageMaxCount = (int) (setting('Report.progressImageMaxCount') ?? 4);
    if ($progressImageMaxCount < 1) {
        $progressImageMaxCount = 4;
    } elseif ($progressImageMaxCount > 20) {
        $progressImageMaxCount = 20;
    }

    $formatDate = static function (?string $value, bool $withTime = false): string {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }
        if (function_exists('app_date')) {
            return (string) app_date($value, $withTime);
        }
        $ts = strtotime($value);
        if ($ts === false) {
            return $value;
        }
        return $withTime ? date('d-M-Y H:i:s', $ts) : date('d-M-Y', $ts);
    };

    $formatAgeText = static function (int $years, int $months, int $days): string {
        $years = max(0, $years);
        $months = max(0, $months);
        return sprintf(
            '%d year%s %d month%s',
            $years,
            $years === 1 ? '' : 's',
            $months,
            $months === 1 ? '' : 's'
        );
    };

    $periodStartDisplay = $formatDate((string) ($version['period_start'] ?? ''), false);
    $periodEndDisplay = $formatDate((string) ($version['period_end'] ?? ''), false);

    $learnerNameRaw = trim((string) ($version['learner_name'] ?? ''));
    $learnerFirstName = 'Learner';
    if ($learnerNameRaw !== '') {
        $nameParts = preg_split('/\s+/', $learnerNameRaw);
        if (is_array($nameParts) && !empty($nameParts[0])) {
            $learnerFirstName = (string) $nameParts[0];
        } else {
            $learnerFirstName = $learnerNameRaw;
        }
    }

    $generatedAtRaw = trim((string) ($version['generated_at'] ?? ''));
    $dateOfReportDate = $formatDate($generatedAtRaw, false);
    $clientDateOfBirthRaw = trim((string) ($version['client_date_of_birth'] ?? ''));
    $dateOfBirthDisplay = $clientDateOfBirthRaw !== '' ? $formatDate($clientDateOfBirthRaw, false) : 'N/A';

    $ageAtEndOfPeriodDisplay = 'N/A';
    $dobTs = $clientDateOfBirthRaw !== '' ? strtotime($clientDateOfBirthRaw) : false;
    $reportTs = $generatedAtRaw !== '' ? strtotime($generatedAtRaw) : false;
    if ($dobTs !== false && $reportTs !== false) {
        $dobDate = new \DateTimeImmutable(date('Y-m-d', $dobTs));
        $reportDate = new \DateTimeImmutable(date('Y-m-d', $reportTs));
        if ($reportDate >= $dobDate) {
            $ageDiff = $dobDate->diff($reportDate);
            $ageAtEndOfPeriodDisplay = $formatAgeText((int) $ageDiff->y, (int) $ageDiff->m, (int) $ageDiff->d);
        }
    }

    if ($ageAtEndOfPeriodDisplay === 'N/A') {
        $storedAgeRaw = $version['client_age'] ?? null;
        if ($storedAgeRaw !== null && trim((string) $storedAgeRaw) !== '' && is_numeric($storedAgeRaw)) {
            $storedYears = max(0, (int) $storedAgeRaw);
            $ageAtEndOfPeriodDisplay = sprintf('%d year%s 0 months', $storedYears, $storedYears === 1 ? '' : 's');
        }
    }

    $progressListUrl = $progress_list_url ?? base_url('client-profile');
    $progressVersionBaseUrl = base_url('client-profile/reports/progress/' . esc($encodedClientId ?? '') . '/version');
?>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h5 class="mb-sm-0">Progress Report Draft</h5>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="<?= esc($progressListUrl) ?>">Progress Reports</a></li>
            <li class="breadcrumb-item active">Draft</li>
        </ol>
    </div>
</div>

<div id="page_loader_overlay" class="page-loader-overlay">
    <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
                    <div>
                        <span class="badge bg-warning-subtle text-warning text-uppercase me-2">Draft</span>
                        <span class="text-muted">Version v<?= (int) ($version['version_no'] ?? 0) ?></span>
                    </div>
                    <div>
                        <a href="<?= esc($progressListUrl) ?>" id="back_to_list_btn" class="btn btn-secondary btn-sm">
                            <i class="ri-arrow-left-line align-bottom me-1"></i>Back to List
                        </a>
                    </div>
                </div>
                <form id="progress_draft_form">
                    <input type="hidden" id="version_id" value="<?= (int) ($version['version_id'] ?? 0) ?>">
                    <input type="hidden" id="progress_manual_field_map" value='<?= esc(json_encode($manualFieldMap, JSON_UNESCAPED_UNICODE), 'attr') ?>'>
                    <input type="hidden" id="progress_auto_section_map" value='<?= esc(json_encode($autoSectionMap, JSON_UNESCAPED_UNICODE), 'attr') ?>'>
                    <input type="hidden" id="progress_pulled_sections" value='<?= esc(json_encode($pulledSections, JSON_UNESCAPED_UNICODE), 'attr') ?>'>
                    <input type="hidden" id="progress_instructional_domain_comments" value='<?= esc(json_encode($instructionalDomainComments, JSON_UNESCAPED_UNICODE), 'attr') ?>'>
                    <input type="hidden" id="progress_instructional_image_max_size_mb" value="<?= (int) $progressImageMaxSizeMb ?>">
                    <input type="hidden" id="progress_instructional_image_max_count" value="<?= (int) $progressImageMaxCount ?>">

                    <div class="progress-paper">
                            <table class="pr-header-table">
                                <tr>
                                    <td class="pr-header-left" style="width: 38%;">
                                        <?php if ($headerLine1 !== ''): ?><div class="line-title"><?= esc($headerLine1) ?></div><?php endif; ?>
                                        <?php if ($headerLine2 !== ''): ?><div class="line-sub"><?= esc($headerLine2) ?></div><?php endif; ?>
                                        <?php if ($headerLine3 !== ''): ?><div class="line-title mt-2"><?= esc($headerLine3) ?></div><?php endif; ?>
                                        <?php if ($headerLine4 !== ''): ?><div class="line-sub"><?= esc($headerLine4) ?></div><?php endif; ?>
                                    </td>
                                    <td class="pr-header-center" style="width: 24%;">
                                        <?php if ($hasLogo): ?>
                                            <img class="pr-logo" src="<?= base_url('app-configuration/report-settings/logo') ?>" alt="Report Logo">
                                        <?php else: ?>
                                            <div class="pr-logo-placeholder">Logo</div>
                                        <?php endif; ?>
                                        <?php if ($headerCenterCaption !== ''): ?><div class="small"><?= esc($headerCenterCaption) ?></div><?php endif; ?>
                                    </td>
                                    <td class="pr-header-right" style="width: 38%;">
                                        <?php if ($reportPhone !== ''): ?><div><?= esc($reportPhone) ?></div><?php endif; ?>
                                        <?php if ($reportWebsite !== ''): ?><div><?= esc($reportWebsite) ?></div><?php endif; ?>
                                        <?php if ($reportLocationLine !== ''): ?><div><?= esc($reportLocationLine) ?></div><?php endif; ?>
                                    </td>
                                </tr>
                            </table>

                            <div class="pr-title">PROGRESS REPORT</div>

                            <table class="pr-meta-table">
                                <tr>
                                    <td style="width: 50%;" colspan="2">
                                        <span class="pr-meta-label">Learner</span>
                                        <div class="pr-meta-value"><?= esc((string) ($version['learner_name'] ?? '')) ?></div>
                                    </td>
                                    <td style="width: 25%;">
                                        <span class="pr-meta-label">Date of Birth</span>
                                        <div class="pr-meta-value"><?= esc($dateOfBirthDisplay) ?></div>
                                    </td>
                                    <td style="width: 25%;">
                                        <span class="pr-meta-label">Age At End Of Period</span>
                                        <div class="pr-meta-value"><?= esc($ageAtEndOfPeriodDisplay) ?></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 25%;">
                                        <span class="pr-meta-label">Date Of Report</span>
                                        <div class="pr-meta-value">
                                            <div><?= esc($dateOfReportDate) ?></div>
                                        </div>
                                    </td>
                                    <td style="width: 25%;">
                                        <span class="pr-meta-label">Progress Period</span>
                                        <div class="pr-meta-value">
                                            <?= esc($periodStartDisplay) ?> to <?= esc($periodEndDisplay) ?>
                                        </div>
                                    </td>
                                    <td style="width: 25%;">
                                        <span class="pr-meta-label">Reported By</span>
                                        <div class="pr-meta-value"><?= esc($reportedByName !== '' ? $reportedByName : '-') ?></div>
                                    </td>
                                    <td style="width: 25%;">
                                        <span class="pr-meta-label">Approved By</span>
                                        <input type="text" class="form-control form-control-sm" id="approved_by" value="<?= esc($approvedBy) ?>" data-manual-key="approved_by">
                                    </td>
                                </tr>
                            </table>

                            <div class="pr-section" id="section_current_programme_management">
                                <div class="pr-section-header">
                                    <h6 class="pr-section-title mb-0">Current Program Management</h6>
                                    <button type="button" class="btn btn-outline-primary btn-sm btn-pull-placeholder" data-section="current_programme_management">
                                        <i class="ri-download-2-line align-bottom me-1"></i>Fetch Latest Details
                                    </button>
                                </div>
                                <table class="pr-grid">
                                    <tbody>
                                        <tr>
                                            <th style="width: 35%;">No. of Sessions In Selected Period</th>
                                            <td class="text-muted" data-auto-key="pm.sessions_count">Data not pulled yet. Click "Fetch Latest Details" to load this section.</td>
                                        </tr>
                                        <tr>
                                            <th>Hours of instruction between <?= esc($periodStartDisplay) ?> to <?= esc($periodEndDisplay) ?></th>
                                            <td class="text-muted" data-auto-key="pm.hours_of_instruction">Data not pulled yet. Click "Fetch Latest Details" to load this section.</td>
                                        </tr>
                                        <tr>
                                            <th>DTI / NET Ratio</th>
                                            <td class="text-muted" data-auto-key="pm.dti_net_ratio">Data not pulled yet. Click "Fetch Latest Details" to load this section.</td>
                                        </tr>
                                        <tr>
                                            <th>Schedule Of Reinforcement</th>
                                            <td class="text-muted" data-auto-key="pm.schedule_of_reinforcement">Data not pulled yet. Click "Fetch Latest Details" to load this section.</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="text-muted" data-auto-key="pm.current_programmes">
                                                <strong>Current Programs:</strong> Data not pulled yet. Click "Fetch Latest Details" to load this section.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="mt-2">
                                    <label class="pr-textarea-label" for="programme_management_comment">Comments</label>
                                    <textarea id="programme_management_comment" class="form-control" rows="3" data-manual-key="programme_management_comment"><?= esc($programmeManagementComment) ?></textarea>
                                </div>
                            </div>

                            <div class="pr-section" id="section_progress">
                                <div class="pr-section-header">
                                    <h6 class="pr-section-title mb-0">Progress</h6>
                                    <button type="button" class="btn btn-outline-primary btn-sm btn-pull-placeholder" data-section="progress">
                                        <i class="ri-download-2-line align-bottom me-1"></i>Fetch Latest Details
                                    </button>
                                </div>
                                <p class="mb-2">
                                    The following graph shows the cumulative number of skills that <?= esc($learnerFirstName) ?> has acquired since <span data-auto-key="progress.program_start_date_text">Data not pulled yet. Click "Fetch Latest Details" to load this section.</span>. This report will provide further information on the period <?= esc($periodStartDisplay) ?> to <?= esc($periodEndDisplay) ?>.
                                </p>
                                <div class="pr-progress-legend">
                                    <span class="me-3">
                                        <img src="/assets/images/legend-black.png" alt="Skills Retained">
                                        Skills Retained
                                    </span>
                                    <span>
                                        <img src="/assets/images/legend-blue.png" alt="Degrees of Independence">
                                        Degrees of independence
                                    </span>
                                </div>

                                <div class="pr-chart-wrap" data-auto-key="progress.cumulative_all_time_graph">
                                    <div class="pr-chart-empty">Data not pulled yet. Click "Fetch Latest Details" to load this section.</div>
                                    <canvas id="progress_cumulative_all_time_chart" class="pr-chart-canvas d-none"></canvas>
                                </div>
                                <div class="pr-figure-caption">Figure 1: Graph shows cumulative progress across all skills.</div>

                                <div class="mt-3 pr-chart-wrap" data-auto-key="progress.cumulative_period_graph">
                                    <div class="pr-chart-empty">Data not pulled yet. Click "Fetch Latest Details" to load this section.</div>
                                    <canvas id="progress_cumulative_period_chart" class="pr-chart-canvas d-none"></canvas>
                                </div>
                                <div class="pr-figure-caption">
                                    Figure 2: Graph shows the cumulative progress across all skills between the dates <?= esc($periodStartDisplay) ?> to <?= esc($periodEndDisplay) ?>
                                </div>
                            </div>

                            <div class="pr-section" id="section_instructional_programmes">
                                <div class="pr-section-header">
                                    <h6 class="pr-section-title mb-0">Instructional Programs</h6>
                                    <button type="button" class="btn btn-outline-primary btn-sm btn-pull-placeholder" data-section="instructional_programmes">
                                        <i class="ri-download-2-line align-bottom me-1"></i>Fetch Latest Details
                                    </button>
                                </div>

                                <div class="mb-3">
                                    <div id="instructional_domains_container"></div>

                                    <div>
                                        <label class="pr-textarea-label mb-1">Upload Images</label>
                                        <div class="input-group">
                                            <input type="file" class="form-control" id="instructional_image_upload" accept=".jpg,.jpeg,.png,image/jpeg,image/png" multiple <?= $canManageInstructionalImages ? '' : 'disabled' ?>>
                                            <?php if ($canManageInstructionalImages): ?>
                                                <button type="button" class="btn btn-outline-secondary" id="instructional_image_add_btn">
                                                    <i class="ri-upload-2-line align-bottom me-1"></i>Add
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                        <input type="file" class="d-none" id="instructional_image_replace_input" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                                        <div class="pr-section-note mt-1" id="instructional_image_limit_note">
                                            Allowed: JPG/JPEG/PNG, max <?= (int) $progressImageMaxCount ?> images, <?= (int) $progressImageMaxSizeMb ?> MB each.
                                        </div>
                                        <div class="progress-image-list mt-1" id="instructional_images_list">
                                            <div class="text-muted">Loading images...</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="pr-section" id="section_manding">
                                <div class="pr-section-header">
                                    <h6 class="pr-section-title mb-0">Manding</h6>
                                    <button type="button" class="btn btn-outline-primary btn-sm btn-pull-placeholder" data-section="manding">
                                        <i class="ri-download-2-line align-bottom me-1"></i>Fetch Latest Details
                                    </button>
                                </div>
                                <div class="pr-graph-placeholder mb-2" data-auto-key="manding.graphs">
                                    Data not pulled yet. Click "Fetch Latest Details" to load this section.
                                </div>
                                <div id="manding_graphs_container" class="row g-3 mb-2"></div>
                                <label class="pr-textarea-label" for="manding_comment">Comments</label>
                                <textarea id="manding_comment" class="form-control" rows="3" data-manual-key="manding_comment"><?= esc($mandingComment) ?></textarea>
                            </div>

                            <div class="pr-section" id="section_problem_behaviour_reduction">
                                <div class="pr-section-header">
                                    <h6 class="pr-section-title mb-0">Problem Behaviour Reduction</h6>
                                    <button type="button" class="btn btn-outline-primary btn-sm btn-pull-placeholder" data-section="problem_behaviour_reduction">
                                        <i class="ri-download-2-line align-bottom me-1"></i>Fetch Latest Details
                                    </button>
                                </div>
                                <div class="pr-graph-placeholder mb-2" data-auto-key="problem_behaviour.graphs">
                                    Data not pulled yet. Click "Fetch Latest Details" to load this section.
                                </div>
                                <div id="problem_behaviour_graphs_container" class="row g-3 mb-2"></div>
                                <label class="pr-textarea-label" for="problem_behaviour_reduction_comment">Comments</label>
                                <textarea id="problem_behaviour_reduction_comment" class="form-control" rows="3" data-manual-key="problem_behaviour_reduction_comment"><?= esc($problemBehaviourComment) ?></textarea>
                            </div>

                            <div class="pr-section" id="section_conclusion">
                                <div class="pr-section-header">
                                    <h6 class="pr-section-title mb-0">Conclusion</h6>
                                </div>
                                <textarea id="conclusion_comment" class="form-control" rows="4" data-manual-key="conclusion_comment"><?= esc($conclusionComment) ?></textarea>
                                <textarea id="draft_notes" class="d-none"><?= esc($conclusionComment) ?></textarea>
                            </div>

                            <div class="pr-footer">
                                <table>
                                    <tr>
                                        <td class="pr-footer-left"><?= esc($footerCompany) ?></td>
                                        <td class="pr-footer-right"><?= esc($footerAddressLine1) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="pr-footer-left"></td>
                                        <td class="pr-footer-right"><?= esc($footerAddressLine2) ?></td>
                                    </tr>
                                </table>
                            </div>
                    </div>

                    <div class="mt-4 d-flex gap-2 flex-wrap">
                        <?php if (auth()->user()->can('client-profile.reports.progress.save-draft')): ?>
                            <button type="button" class="btn btn-primary" id="btn_save_draft">
                                <i class="ri-save-line align-bottom me-1"></i>Save Draft
                            </button>
                        <?php endif; ?>
                        <?php if (auth()->user()->can('client-profile.reports.progress.finalize')): ?>
                            <button type="button" class="btn btn-success" id="btn_finalize_draft">
                                <i class="ri-check-double-line align-bottom me-1"></i>Save Draft & Generate PDF
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section("page_js") ?>
<script>
    $(document).ready(function() {
        var csrfToken = "<?= csrf_hash() ?>";
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrfToken } });
        var backToListUrl = '<?= esc($progressListUrl, 'js') ?>';
        var loaderCounter = 0;
        var progressAllTimeChart = null;
        var progressPeriodChart = null;
        var instructionalDomainCharts = {};
        var mandingCharts = {};
        var problemBehaviourCharts = {};
        var sectionNotPulledMessage = 'Data not pulled yet. Click "Fetch Latest Details" to load this section.';
        var sectionPulledNoDataMessage = 'Data pulled. No data available for this section in selected period.';
        var noSessionPointImage = new Image();
        noSessionPointImage.src = '<?= base_url('assets/images/legend-black-8.jpg') ?>';
        var canSaveDraft = <?= auth()->user()->can('client-profile.reports.progress.save-draft') ? 'true' : 'false' ?>;
        var canManageInstructionalImages = <?= $canManageInstructionalImages ? 'true' : 'false' ?>;
        var instructionalImageMaxSizeMb = parseInt($('#progress_instructional_image_max_size_mb').val() || '1', 10);
        var instructionalImageMaxCount = parseInt($('#progress_instructional_image_max_count').val() || '4', 10);
        if (!Number.isFinite(instructionalImageMaxSizeMb) || instructionalImageMaxSizeMb < 1) {
            instructionalImageMaxSizeMb = 1;
        }
        if (!Number.isFinite(instructionalImageMaxCount) || instructionalImageMaxCount < 1) {
            instructionalImageMaxCount = 4;
        }
        var instructionalImageAllowedExtensions = ['jpg', 'jpeg', 'png'];
        var instructionalImagesStore = [];
        var instructionalReplaceArtifactId = 0;
        var instructionalReplaceTriggerButton = null;
        var finalizeRequiredSections = [
            'current_programme_management',
            'progress',
            'instructional_programmes',
            'manding',
            'problem_behaviour_reduction'
        ];
        var finalizeSectionLabels = {
            current_programme_management: 'Current Program Management',
            progress: 'Progress',
            instructional_programmes: 'Instructional Programs',
            manding: 'Manding',
            problem_behaviour_reduction: 'Problem Behaviour Reduction'
        };
        $('#back_to_list_btn').attr('href', backToListUrl);

        function showPageLoader() {
            loaderCounter++;
            $('#page_loader_overlay').css('display', 'flex');
        }

        function hidePageLoader() {
            loaderCounter = Math.max(0, loaderCounter - 1);
            if (loaderCounter === 0) {
                $('#page_loader_overlay').hide();
            }
        }

        function setButtonBusy(button, isBusy, busyText) {
            if (!button || !button.length) return;
            if (isBusy) {
                if (button.data('original-html') == null) {
                    button.data('original-html', button.html());
                }
                button.prop('disabled', true);
                button.html('<span class="spinner-border spinner-border-sm align-middle me-1" role="status" aria-hidden="true"></span>' + (busyText || 'Please wait...'));
                return;
            }

            var originalHtml = button.data('original-html');
            if (originalHtml != null) {
                button.html(originalHtml);
                button.removeData('original-html');
            }
            button.prop('disabled', false);
        }

        function postWithLoader(url, payload) {
            showPageLoader();
            return $.post(url, payload).always(function() {
                hidePageLoader();
            });
        }

        function setNestedValue(target, path, value) {
            var parts = String(path || '').split('.');
            var ref = target;
            for (var i = 0; i < parts.length; i++) {
                var part = $.trim(parts[i]);
                if (!part) continue;
                if (i === parts.length - 1) {
                    ref[part] = value;
                } else {
                    if (typeof ref[part] !== 'object' || ref[part] === null || Array.isArray(ref[part])) {
                        ref[part] = {};
                    }
                    ref = ref[part];
                }
            }
        }

        function collectManualData() {
            var data = {};
            $('[data-manual-key]').each(function() {
                var key = $(this).data('manual-key');
                if (!key) return;
                var value = $(this).val();
                setNestedValue(data, key, value == null ? '' : String(value));
            });
            data.draft_notes = data.conclusion_comment || ($('#conclusion_comment').val() || '');
            return data;
        }

        function payload() {
            var manualData = collectManualData();
            $('#draft_notes').val(manualData.draft_notes || '');
            return {
                approved_by: manualData.approved_by || '',
                draft_notes: manualData.draft_notes || '',
                manual_json: JSON.stringify(manualData)
            };
        }

        function clonePlainData(value, fallback) {
            try {
                return JSON.parse(JSON.stringify(value));
            } catch (e) {
                return fallback;
            }
        }

        function htmlEscape(text) {
            return $('<div/>').text(text == null ? '' : String(text)).html();
        }

        function parseJsonInput(selector, fallback) {
            var raw = $(selector).val();
            if (!raw) return fallback;
            try {
                var parsed = JSON.parse(raw);
                return parsed == null ? fallback : parsed;
            } catch (e) {
                return fallback;
            }
        }

        var pulledSectionsStore = parseJsonInput('#progress_pulled_sections', {});
        if (!pulledSectionsStore || typeof pulledSectionsStore !== 'object' || Array.isArray(pulledSectionsStore)) {
            pulledSectionsStore = {};
        }
        var instructionalDomainCommentsStore = parseJsonInput('#progress_instructional_domain_comments', {});
        if (!instructionalDomainCommentsStore || typeof instructionalDomainCommentsStore !== 'object' || Array.isArray(instructionalDomainCommentsStore)) {
            instructionalDomainCommentsStore = {};
        }

        function getPulledSectionsStoreSnapshot() {
            var latest = parseJsonInput('#progress_pulled_sections', {});
            if (!latest || typeof latest !== 'object' || Array.isArray(latest)) {
                latest = {};
            }
            pulledSectionsStore = latest;
            return latest;
        }

        function syncInstructionalDomainCommentsInput() {
            $('#progress_instructional_domain_comments').val(
                JSON.stringify(
                    (instructionalDomainCommentsStore && typeof instructionalDomainCommentsStore === 'object' && !Array.isArray(instructionalDomainCommentsStore))
                        ? instructionalDomainCommentsStore
                        : {}
                )
            );
        }

        function persistPulledSectionState(sectionKey, sectionData, pulledAt) {
            if (!sectionKey) return;
            var current = getPulledSectionsStoreSnapshot();
            var safeSectionData = sectionData;
            try {
                safeSectionData = JSON.parse(JSON.stringify(sectionData && typeof sectionData === 'object' ? sectionData : {}));
            } catch (e) {
                // Fallback for circular references introduced by chart libs.
                safeSectionData = {};
                if (sectionData && typeof sectionData === 'object') {
                    Object.keys(sectionData).forEach(function(key) {
                        var value = sectionData[key];
                        if (value == null || typeof value === 'string' || typeof value === 'number' || typeof value === 'boolean') {
                            safeSectionData[key] = value;
                            return;
                        }
                        if (Array.isArray(value)) {
                            try {
                                safeSectionData[key] = JSON.parse(JSON.stringify(value));
                            } catch (ignored) {
                                safeSectionData[key] = [];
                            }
                            return;
                        }
                        if (typeof value === 'object') {
                            try {
                                safeSectionData[key] = JSON.parse(JSON.stringify(value));
                            } catch (ignoredObj) {
                                safeSectionData[key] = {};
                            }
                            return;
                        }
                        safeSectionData[key] = String(value);
                    });
                }
            }
            current[String(sectionKey)] = {
                pulled_at: String(pulledAt || ''),
                data: (safeSectionData && typeof safeSectionData === 'object') ? safeSectionData : {}
            };
            pulledSectionsStore = current;
            $('#progress_pulled_sections').val(JSON.stringify(current));
        }

        function getMandingGraphsFromStore() {
            var current = getPulledSectionsStoreSnapshot();
            var sectionPayload = current.manding;
            if (!sectionPayload || typeof sectionPayload !== 'object') {
                return [];
            }

            var sectionData = (sectionPayload.data && typeof sectionPayload.data === 'object')
                ? sectionPayload.data
                : sectionPayload;
            var graphs = sectionData['manding.graphs'];
            return Array.isArray(graphs) ? graphs : [];
        }

        function setMandingGraphsToStore(graphs) {
            var current = getPulledSectionsStoreSnapshot();
            var sectionPayload = current.manding;
            var sectionPulledAt = '';
            var sectionData = {};

            if (sectionPayload && typeof sectionPayload === 'object') {
                sectionPulledAt = String(sectionPayload.pulled_at || '');
                sectionData = (sectionPayload.data && typeof sectionPayload.data === 'object')
                    ? clonePlainData(sectionPayload.data, {})
                    : clonePlainData(sectionPayload, {});
            }
            sectionData['manding.graphs'] = Array.isArray(graphs) ? graphs : [];
            current.manding = {
                pulled_at: sectionPulledAt,
                data: sectionData
            };
            pulledSectionsStore = current;
            $('#progress_pulled_sections').val(JSON.stringify(current));
        }

        function getInstructionalDomainsFromStore() {
            var current = getPulledSectionsStoreSnapshot();
            var sectionPayload = current.instructional_programmes;
            if (!sectionPayload || typeof sectionPayload !== 'object') {
                return [];
            }

            var sectionData = (sectionPayload.data && typeof sectionPayload.data === 'object')
                ? sectionPayload.data
                : sectionPayload;
            var domains = sectionData['instructional.domains'];
            return Array.isArray(domains) ? domains : [];
        }

        function setInstructionalDomainsToStore(domains) {
            var current = getPulledSectionsStoreSnapshot();
            var sectionPayload = current.instructional_programmes;
            var sectionPulledAt = '';
            var sectionData = {};

            if (sectionPayload && typeof sectionPayload === 'object') {
                sectionPulledAt = String(sectionPayload.pulled_at || '');
                sectionData = (sectionPayload.data && typeof sectionPayload.data === 'object')
                    ? clonePlainData(sectionPayload.data, {})
                    : clonePlainData(sectionPayload, {});
            }
            sectionData['instructional.domains'] = Array.isArray(domains) ? domains : [];
            current.instructional_programmes = {
                pulled_at: sectionPulledAt,
                data: sectionData
            };
            pulledSectionsStore = current;
            $('#progress_pulled_sections').val(JSON.stringify(current));
        }

        function removeInstructionalDomainCommentFromStore(domainKey) {
            if (!domainKey) return;
            if (!instructionalDomainCommentsStore || typeof instructionalDomainCommentsStore !== 'object' || Array.isArray(instructionalDomainCommentsStore)) {
                instructionalDomainCommentsStore = {};
            }
            if (Object.prototype.hasOwnProperty.call(instructionalDomainCommentsStore, domainKey)) {
                delete instructionalDomainCommentsStore[domainKey];
            }
            syncInstructionalDomainCommentsInput();
        }

        function getSectionPulledAtFromStore(sectionKey) {
            var current = getPulledSectionsStoreSnapshot();
            if (!sectionKey || !current || typeof current !== 'object') {
                return '';
            }

            var payload = current[sectionKey];
            if (!payload || typeof payload !== 'object') {
                return '';
            }

            return String(payload.pulled_at || '');
        }

        function getSectionDataFromStore(sectionKey) {
            var current = getPulledSectionsStoreSnapshot();
            if (!sectionKey || !current || typeof current !== 'object') {
                return {};
            }

            var payload = current[sectionKey];
            if (!payload || typeof payload !== 'object') {
                return {};
            }

            var sectionData = (payload.data && typeof payload.data === 'object')
                ? payload.data
                : payload;
            return clonePlainData(sectionData, {});
        }

        function postSectionStateUpdate(sectionKey, sectionData, extraPayload) {
            var versionId = $('#version_id').val();
            var requestPayload = {
                section_key: String(sectionKey || ''),
                pulled_at: getSectionPulledAtFromStore(sectionKey),
                section_data_json: JSON.stringify(clonePlainData(
                    (sectionData && typeof sectionData === 'object') ? sectionData : {},
                    {}
                ))
            };

            if (extraPayload && typeof extraPayload === 'object') {
                Object.keys(extraPayload).forEach(function(key) {
                    requestPayload[key] = extraPayload[key];
                });
            }

            return postWithLoader(
                '<?= esc($progressVersionBaseUrl, 'js') ?>/' + versionId + '/update-section-state',
                requestPayload
            );
        }

        function normalizeDatasetColor(rawColor, fallbackColor) {
            var fallback = String(fallbackColor || '#6c757d');
            if (typeof rawColor === 'string') {
                var direct = $.trim(rawColor);
                return direct !== '' ? direct : fallback;
            }

            if (Array.isArray(rawColor)) {
                for (var i = 0; i < rawColor.length; i++) {
                    if (typeof rawColor[i] !== 'string') {
                        continue;
                    }
                    var candidate = $.trim(rawColor[i]);
                    if (candidate !== '') {
                        return candidate;
                    }
                }
            }

            return fallback;
        }

        function buildDatasetLegendHtml(datasets) {
            if (!Array.isArray(datasets) || !datasets.length) {
                return '';
            }

            var items = [];
            datasets.forEach(function(dataset, index) {
                if (!dataset || typeof dataset !== 'object') {
                    return;
                }
                var label = $.trim(String(dataset.label || ''));
                if (label === '') {
                    label = 'Series ' + (index + 1);
                }
                var color = normalizeDatasetColor(
                    dataset.borderColor || dataset.backgroundColor,
                    '#6c757d'
                );

                items.push(
                    '<span class="pr-dataset-legend-item" title="' + htmlEscape(label) + '">'
                    + '<span class="pr-dataset-legend-swatch" style="background-color:' + htmlEscape(color) + ';"></span>'
                    + '<span class="pr-dataset-legend-label">' + htmlEscape(label) + '</span>'
                    + '</span>'
                );
            });

            if (!items.length) {
                return '';
            }

            return '<div class="pr-dataset-legend mb-2">' + items.join('') + '</div>';
        }

        function isNoDataValue(value) {
            if (value == null) return true;
            if (Array.isArray(value)) return value.length === 0;
            if (typeof value === 'object') return Object.keys(value).length === 0;
            var normalized = String(value).trim().toLowerCase();
            return normalized === '' || normalized === 'n/a' || normalized === 'none' || normalized === 'null';
        }

        function formatFileSize(bytes) {
            var value = Number(bytes || 0);
            if (!Number.isFinite(value) || value <= 0) {
                return 'N/A';
            }
            if (value >= (1024 * 1024)) {
                return (value / (1024 * 1024)).toFixed(2) + ' MB';
            }
            return (value / 1024).toFixed(1) + ' KB';
        }

        function getFileExtension(fileName) {
            var parts = String(fileName || '').toLowerCase().split('.');
            return parts.length > 1 ? parts.pop() : '';
        }

        function updateInstructionalImageLimitNote() {
            var note = $('#instructional_image_limit_note');
            if (!note.length) return;
            note.text(
                'Allowed: JPG/JPEG/PNG, max ' +
                String(instructionalImageMaxCount) +
                ' images, ' +
                String(instructionalImageMaxSizeMb) +
                ' MB each.'
            );
        }

        function applyInstructionalImageLimits(limits) {
            if (!limits || typeof limits !== 'object') return;

            var sizeMb = parseInt(limits.max_size_mb, 10);
            var maxCount = parseInt(limits.max_count, 10);
            if (Number.isFinite(sizeMb) && sizeMb > 0) {
                instructionalImageMaxSizeMb = sizeMb;
            }
            if (Number.isFinite(maxCount) && maxCount > 0) {
                instructionalImageMaxCount = maxCount;
            }
            updateInstructionalImageLimitNote();
        }

        function renderInstructionalImages(images) {
            var container = $('#instructional_images_list');
            if (!container.length) return;

            var rows = Array.isArray(images) ? images.filter(function(item) {
                return item && typeof item === 'object';
            }) : [];
            instructionalImagesStore = rows;

            if (!rows.length) {
                container.html('<div class="text-muted">No instructional images uploaded.</div>');
                return;
            }

            var html = rows.map(function(item, index) {
                var artifactId = parseInt(item.artifact_id || 0, 10);
                var fileName = htmlEscape(String(item.file_name || ('image_' + (index + 1))));
                var viewUrl = htmlEscape(String(item.view_url || ''));
                var mimeType = htmlEscape(String(item.mime_type || 'image'));
                var fileSize = formatFileSize(item.file_size);

                var actionsHtml = '';
                if (viewUrl !== '') {
                    actionsHtml += ''
                        + '<button type="button" class="btn btn-sm btn-light instructional-image-view me-1" data-view-url="' + viewUrl + '" data-file-name="' + fileName + '">'
                        + '<i class="ri-eye-line align-bottom me-1"></i>View'
                        + '</button>';
                }
                if (canManageInstructionalImages && artifactId > 0) {
                    actionsHtml += ''
                        + '<button type="button" class="btn btn-sm btn-soft-info instructional-image-replace me-1" data-artifact-id="' + artifactId + '">'
                        + '<i class="ri-refresh-line align-bottom me-1"></i>Replace'
                        + '</button>'
                        + '<button type="button" class="btn btn-sm btn-soft-danger instructional-image-delete" data-artifact-id="' + artifactId + '">'
                        + '<i class="ri-delete-bin-line align-bottom me-1"></i>Delete'
                        + '</button>';
                }

                return ''
                    + '<div class="progress-image-item">'
                    + '  <div class="progress-image-thumb">'
                    + '      <img src="' + viewUrl + '" alt="' + fileName + '">'
                    + '  </div>'
                    + '  <div class="progress-image-meta"><strong>' + fileName + '</strong><br>'
                    + '      Type: ' + mimeType + '<br>'
                    + '      Size: ' + htmlEscape(fileSize) + '</div>'
                    + '  <div class="d-flex gap-1 flex-wrap mt-2">' + actionsHtml + '</div>'
                    + '</div>';
            }).join('');

            container.html(html);
        }

        function validateInstructionalImageFiles(files, isReplace) {
            if (!files || !files.length) {
                return { ok: false, message: 'Please select image file(s).' };
            }

            if (isReplace && files.length !== 1) {
                return { ok: false, message: 'Please select exactly one image to replace.' };
            }

            var maxBytes = instructionalImageMaxSizeMb * 1024 * 1024;
            var currentCount = instructionalImagesStore.length;
            if (!isReplace && (currentCount + files.length) > instructionalImageMaxCount) {
                return {
                    ok: false,
                    message: 'Maximum allowed images is ' + instructionalImageMaxCount + '.'
                };
            }

            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                var ext = getFileExtension(file.name);
                if (!instructionalImageAllowedExtensions.includes(ext)) {
                    return { ok: false, message: 'Only JPG, JPEG, and PNG files are allowed.' };
                }
                if (!Number.isFinite(file.size) || file.size <= 0) {
                    return { ok: false, message: 'Invalid file selected.' };
                }
                if (file.size > maxBytes) {
                    return {
                        ok: false,
                        message: 'File "' + file.name + '" exceeds ' + instructionalImageMaxSizeMb + ' MB limit.'
                    };
                }
            }

            return { ok: true };
        }

        function fetchInstructionalImages(useLoader) {
            var versionId = $('#version_id').val();
            if (!versionId) return $.Deferred().resolve().promise();

            var request = useLoader
                ? postWithLoader('<?= esc($progressVersionBaseUrl, 'js') ?>/' + versionId + '/instructional-images', {})
                : $.post('<?= esc($progressVersionBaseUrl, 'js') ?>/' + versionId + '/instructional-images', {});

            return request.done(function(response) {
                if (!response || response.status !== 'success') {
                    renderInstructionalImages([]);
                    return;
                }

                var data = response.data && typeof response.data === 'object' ? response.data : {};
                applyInstructionalImageLimits(data.limits || {});
                renderInstructionalImages(Array.isArray(data.images) ? data.images : []);
            }).fail(function() {
                renderInstructionalImages([]);
            });
        }

        function uploadInstructionalImages(files) {
            var validation = validateInstructionalImageFiles(files, false);
            if (!validation.ok) {
                showAlert('Validation_Error', validation.message, 'error');
                return;
            }

            var versionId = $('#version_id').val();
            if (!versionId) {
                showAlert('Validation_Error', 'Missing version id.', 'error');
                return;
            }

            var addBtn = $('#instructional_image_add_btn');
            var formData = new FormData();
            for (var i = 0; i < files.length; i++) {
                formData.append('images[]', files[i]);
            }

            setButtonBusy(addBtn, true, 'Uploading...');
            showPageLoader();
            $.ajax({
                url: '<?= esc($progressVersionBaseUrl, 'js') ?>/' + versionId + '/instructional-images/upload',
                type: 'post',
                data: formData,
                processData: false,
                contentType: false
            }).done(function(response) {
                if (!response || response.status !== 'success') {
                    showAlert(
                        (response && response.statusText) ? response.statusText : 'Progress Report',
                        (response && response.message) ? response.message : 'Unable to upload images.',
                        (response && response.status) ? response.status : 'error'
                    );
                    return;
                }

                var data = response.data && typeof response.data === 'object' ? response.data : {};
                applyInstructionalImageLimits(data.limits || {});
                renderInstructionalImages(Array.isArray(data.images) ? data.images : []);
                showAlert(response.statusText || 'Progress Report', response.message || 'Images uploaded.', response.status || 'success');
            }).fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, 'Request failed: ' + textStatus + '<br>' + error, 'error');
            }).always(function() {
                hidePageLoader();
                setButtonBusy(addBtn, false);
            });
        }

        function deleteInstructionalImage(artifactId) {
            var versionId = $('#version_id').val();
            if (!versionId || !artifactId) {
                showAlert('Validation_Error', 'Missing version or image id.', 'error');
                return;
            }

            postWithLoader('<?= esc($progressVersionBaseUrl, 'js') ?>/' + versionId + '/instructional-images/' + artifactId + '/delete', {})
                .done(function(response) {
                    if (!response || response.status !== 'success') {
                        showAlert(
                            (response && response.statusText) ? response.statusText : 'Progress Report',
                            (response && response.message) ? response.message : 'Unable to delete image.',
                            (response && response.status) ? response.status : 'error'
                        );
                        return;
                    }

                    var data = response.data && typeof response.data === 'object' ? response.data : {};
                    applyInstructionalImageLimits(data.limits || {});
                    renderInstructionalImages(Array.isArray(data.images) ? data.images : []);
                    showAlert(response.statusText || 'Progress Report', response.message || 'Image deleted.', response.status || 'success');
                })
                .fail(function(jqXHR, textStatus, error) {
                    showAlert(jqXHR.status, 'Request failed: ' + textStatus + '<br>' + error, 'error');
                });
        }

        function replaceInstructionalImage(file) {
            if (!instructionalReplaceArtifactId) {
                showAlert('Validation_Error', 'Missing image id for replacement.', 'error');
                return;
            }

            var validation = validateInstructionalImageFiles([file], true);
            if (!validation.ok) {
                showAlert('Validation_Error', validation.message, 'error');
                return;
            }

            var versionId = $('#version_id').val();
            if (!versionId) {
                showAlert('Validation_Error', 'Missing version id.', 'error');
                return;
            }

            var formData = new FormData();
            formData.append('image', file);

            if (instructionalReplaceTriggerButton && instructionalReplaceTriggerButton.length) {
                setButtonBusy(instructionalReplaceTriggerButton, true, 'Replacing...');
            }
            showPageLoader();
            $.ajax({
                url: '<?= esc($progressVersionBaseUrl, 'js') ?>/' + versionId + '/instructional-images/' + instructionalReplaceArtifactId + '/replace',
                type: 'post',
                data: formData,
                processData: false,
                contentType: false
            }).done(function(response) {
                if (!response || response.status !== 'success') {
                    showAlert(
                        (response && response.statusText) ? response.statusText : 'Progress Report',
                        (response && response.message) ? response.message : 'Unable to replace image.',
                        (response && response.status) ? response.status : 'error'
                    );
                    return;
                }

                var data = response.data && typeof response.data === 'object' ? response.data : {};
                applyInstructionalImageLimits(data.limits || {});
                renderInstructionalImages(Array.isArray(data.images) ? data.images : []);
                showAlert(response.statusText || 'Progress Report', response.message || 'Image replaced.', response.status || 'success');
            }).fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, 'Request failed: ' + textStatus + '<br>' + error, 'error');
            }).always(function() {
                hidePageLoader();
                if (instructionalReplaceTriggerButton && instructionalReplaceTriggerButton.length) {
                    setButtonBusy(instructionalReplaceTriggerButton, false);
                }
                instructionalReplaceArtifactId = 0;
                instructionalReplaceTriggerButton = null;
                $('#instructional_image_replace_input').val('');
            });
        }

        function collectFinalizeValidationErrors() {
            var errors = [];
            var latestPulledSections = getPulledSectionsStoreSnapshot();

            finalizeRequiredSections.forEach(function(sectionKey) {
                var sectionLabel = finalizeSectionLabels[sectionKey] || sectionKey;
                var sectionPayload = latestPulledSections && typeof latestPulledSections === 'object'
                    ? latestPulledSections[sectionKey]
                    : null;
                if (!sectionPayload || typeof sectionPayload !== 'object') {
                    errors.push('Please pull section: ' + sectionLabel + '.');
                    return;
                }

                var sectionData = (sectionPayload.data && typeof sectionPayload.data === 'object')
                    ? sectionPayload.data
                    : sectionPayload;
                if (!sectionData || typeof sectionData !== 'object') {
                    errors.push('Pulled data is missing for section: ' + sectionLabel + '.');
                }
            });

            var approvedBy = $.trim($('#approved_by').val() || '');
            if (approvedBy === '') {
                errors.push('Approved By is required.');
            }

            var conclusionComment = $.trim($('#conclusion_comment').val() || '');
            if (conclusionComment === '') {
                errors.push('Conclusion comment is required.');
            }

            return errors;
        }

        function buildProgressChartOptions(payload) {
            var yAxisLabel = (
                payload &&
                payload.options &&
                payload.options.y_axis_label
            ) ? payload.options.y_axis_label : 'Cumulative Skills Retained Across All Domains';

            var xAxisLabel = (
                payload &&
                payload.options &&
                payload.options.x_axis_label
            ) ? payload.options.x_axis_label : 'Week Ending';

            var annotations = [];
            if (payload && Array.isArray(payload.phaseline)) {
                annotations = payload.phaseline;
            }

            return {
                tooltips: {
                    intersect: false
                },
                annotation: {
                    annotations: annotations
                },
                legend: {
                    display: false
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            suggestedMin: 0
                        },
                        afterDataLimits: function(scale) {
                            scale.max += 10;
                        },
                        scaleLabel: {
                            display: true,
                            labelString: yAxisLabel
                        },
                        gridLines: {
                            display: true,
                            drawBorder: true,
                            drawOnChartArea: false
                        }
                    }],
                    xAxes: [{
                        ticks: {
                            autoSkip: true
                        },
                        scaleLabel: {
                            display: true,
                            labelString: xAxisLabel
                        },
                        gridLines: {
                            display: true,
                            drawBorder: true,
                            drawOnChartArea: false
                        }
                    }]
                },
                elements: {
                    point: {
                        radius: 3
                    }
                },
                layout: {
                    padding: {
                        left: 8,
                        right: 8,
                        top: 4,
                        bottom: 4
                    }
                },
                maintainAspectRatio: false
            };
        }

        function renderProgressChart(autoKey, payload, sectionKey) {
            var container = $('[data-auto-key="' + autoKey + '"]');
            if (!container.length) return;

            var emptyBox = container.find('.pr-chart-empty');
            var canvas = container.find('canvas')[0];
            if (!canvas || !window.Chart) {
                if (emptyBox.length) {
                    emptyBox.text('Chart library not available.');
                }
                return;
            }

            if (!payload || !Array.isArray(payload.labels) || !Array.isArray(payload.datasets)) {
                if (emptyBox.length) {
                    emptyBox.text(sectionKey ? sectionPulledNoDataMessage : 'Chart data unavailable.');
                    emptyBox.removeClass('d-none');
                }
                $(canvas).addClass('d-none');
                return;
            }

            if (autoKey === 'progress.cumulative_all_time_graph' && progressAllTimeChart) {
                progressAllTimeChart.destroy();
                progressAllTimeChart = null;
            }
            if (autoKey === 'progress.cumulative_period_graph' && progressPeriodChart) {
                progressPeriodChart.destroy();
                progressPeriodChart = null;
            }

            var chartDatasets = payload.datasets.map(function(dataset) {
                return $.extend(true, {}, dataset);
            });
            var noSessionIndex = chartDatasets.findIndex(function(dataset) {
                return String((dataset && dataset.label) || '').toLowerCase().indexOf('no session') !== -1;
            });
            if (noSessionIndex > -1) {
                chartDatasets[noSessionIndex].pointStyle = noSessionPointImage;
            }

            var chartConfig = {
                type: (payload.chart_type || 'line'),
                data: {
                    labels: payload.labels,
                    datasets: chartDatasets
                },
                options: buildProgressChartOptions(payload)
            };

            var ctx = canvas.getContext('2d');
            var chartInstance = new Chart(ctx, chartConfig);
            if (autoKey === 'progress.cumulative_all_time_graph') {
                progressAllTimeChart = chartInstance;
            } else if (autoKey === 'progress.cumulative_period_graph') {
                progressPeriodChart = chartInstance;
            }

            if (emptyBox.length) {
                emptyBox.addClass('d-none');
            }
            $(canvas).removeClass('d-none');
        }

        function destroyInstructionalDomainCharts() {
            Object.keys(instructionalDomainCharts).forEach(function(domainKey) {
                if (instructionalDomainCharts[domainKey] && typeof instructionalDomainCharts[domainKey].destroy === 'function') {
                    instructionalDomainCharts[domainKey].destroy();
                }
            });
            instructionalDomainCharts = {};
        }

        function destroyMandingCharts() {
            Object.keys(mandingCharts).forEach(function(chartKey) {
                if (mandingCharts[chartKey] && typeof mandingCharts[chartKey].destroy === 'function') {
                    mandingCharts[chartKey].destroy();
                }
            });
            mandingCharts = {};
        }

        function destroyProblemBehaviourCharts() {
            Object.keys(problemBehaviourCharts).forEach(function(chartKey) {
                if (problemBehaviourCharts[chartKey] && typeof problemBehaviourCharts[chartKey].destroy === 'function') {
                    problemBehaviourCharts[chartKey].destroy();
                }
            });
            problemBehaviourCharts = {};
        }

        function buildInstructionalGoalRows(goals) {
            if (!Array.isArray(goals) || !goals.length) {
                return '';
            }

            var rows = goals.map(function(goal) {
                var goalName = (goal && goal.goal_name != null) ? String(goal.goal_name) : 'N/A';
                var targets = (goal && Array.isArray(goal.targets_mastered)) ? goal.targets_mastered : [];
                var goalTargetCount = (goal && goal.goal_target_count != null)
                    ? parseInt(goal.goal_target_count, 10)
                    : targets.length;
                if (!Number.isFinite(goalTargetCount) || goalTargetCount < 0) {
                    goalTargetCount = 0;
                }
                var goalDisplay = goalName + ' (' + goalTargetCount + ')';
                var targetHtml = targets.length
                    ? targets.map(function(item) { return htmlEscape(item); }).join(', ')
                    : 'N/A';
                return '<tr><td>' + htmlEscape(goalDisplay) + '</td><td>' + targetHtml + '</td></tr>';
            });

            return rows.join('');
        }

        function captureInstructionalDomainCommentsFromDom() {
            var container = $('#instructional_domains_container');
            if (!container.length) return;

            container.find('[data-domain-comment-key]').each(function() {
                var key = $(this).data('domain-comment-key');
                if (!key) return;
                instructionalDomainCommentsStore[String(key)] = String($(this).val() || '');
            });
            syncInstructionalDomainCommentsInput();
        }

        function getInstructionalDomainComment(domainKey) {
            if (!domainKey) return '';
            if (!instructionalDomainCommentsStore || typeof instructionalDomainCommentsStore !== 'object') {
                return '';
            }
            if (!Object.prototype.hasOwnProperty.call(instructionalDomainCommentsStore, domainKey)) {
                return '';
            }
            return String(instructionalDomainCommentsStore[domainKey] || '');
        }

        function renderInstructionalDomainChart(domainKey, payload) {
            var autoKey = 'instructional.domain_graphs.' + domainKey;
            var container = $('[data-auto-key="' + autoKey + '"]');
            if (!container.length) return;

            var emptyBox = container.find('.pr-chart-empty');
            var canvas = container.find('canvas')[0];
            if (!canvas || !window.Chart) {
                if (emptyBox.length) {
                    emptyBox.text('Chart library not available.');
                }
                return;
            }

            if (!payload || !Array.isArray(payload.labels) || !Array.isArray(payload.datasets)) {
                if (emptyBox.length) {
                    emptyBox.text(sectionPulledNoDataMessage);
                    emptyBox.removeClass('d-none');
                }
                $(canvas).addClass('d-none');
                return;
            }

            if (instructionalDomainCharts[domainKey]) {
                instructionalDomainCharts[domainKey].destroy();
                instructionalDomainCharts[domainKey] = null;
            }

            var chartConfig = {
                type: (payload.chart_type || 'line'),
                data: {
                    labels: payload.labels,
                    datasets: payload.datasets
                },
                options: buildProgressChartOptions(payload)
            };

            var ctx = canvas.getContext('2d');
            instructionalDomainCharts[domainKey] = new Chart(ctx, chartConfig);

            if (emptyBox.length) {
                emptyBox.addClass('d-none');
            }
            $(canvas).removeClass('d-none');
        }

        function resolveInstructionalDomainKey(domain, index) {
            var rawDomainKey = (domain && domain.key != null) ? String(domain.key) : ('d' + (index + 1));
            return rawDomainKey.trim() !== '' ? rawDomainKey : ('d' + (index + 1));
        }

        function renderInstructionalDomains(domains, sectionKey, options) {
            var container = $('#instructional_domains_container');
            if (!container.length) return;

            var skipCaptureComments = !!(options && options.skipCaptureComments);
            if (!skipCaptureComments) {
                captureInstructionalDomainCommentsFromDom();
            }
            destroyInstructionalDomainCharts();
            container.empty();

            var emptyStateMessage = sectionKey === 'instructional_programmes'
                ? sectionPulledNoDataMessage
                : sectionNotPulledMessage;

            if (!Array.isArray(domains) || !domains.length) {
                container.html('<div class="text-muted">' + htmlEscape(emptyStateMessage) + '</div>');
                return;
            }

            domains.forEach(function(domain, index) {
                var domainKey = resolveInstructionalDomainKey(domain, index);
                var domainKeyId = domainKey.replace(/[^a-zA-Z0-9_-]/g, '_');
                var domainTitle = (domain && domain.title != null) ? String(domain.title) : ('Domain ' + (index + 1));
                var goals = (domain && Array.isArray(domain.goals)) ? domain.goals : [];
                var domainTargetCount = (domain && domain.domain_total_target_count != null)
                    ? parseInt(domain.domain_total_target_count, 10)
                    : 0;
                if (!Number.isFinite(domainTargetCount) || domainTargetCount < 0) {
                    domainTargetCount = goals.reduce(function(sum, goal) {
                        var targets = (goal && Array.isArray(goal.targets_mastered)) ? goal.targets_mastered : [];
                        return sum + targets.length;
                    }, 0);
                }
                var goalRowsHtml = buildInstructionalGoalRows(goals);
                var noGoalsMessage = sectionKey === 'instructional_programmes'
                    ? sectionPulledNoDataMessage
                    : sectionNotPulledMessage;
                if (goalRowsHtml === '') {
                    goalRowsHtml = '<tr><td class="text-muted">' + htmlEscape(noGoalsMessage) + '</td><td class="text-muted">' + htmlEscape(noGoalsMessage) + '</td></tr>';
                }
                var domainComment = getInstructionalDomainComment(domainKey);
                instructionalDomainCommentsStore[domainKey] = domainComment;
                var deleteButtonHtml = '';
                if (canSaveDraft) {
                    deleteButtonHtml = '<button type="button" class="btn btn-sm btn-outline-danger btn-delete-instructional-domain" data-instructional-domain-key="' + htmlEscape(domainKey) + '" title="Delete domain from this draft"><i class="ri-delete-bin-line"></i></button>';
                }

                var cardHtml = ''
                    + '<div class="pr-domain-block" data-domain-key="' + htmlEscape(domainKey) + '">'
                    + '  <div class="d-flex align-items-center justify-content-between mb-2">'
                    + '      <div class="pr-domain-title mb-0">' + htmlEscape(domainTitle) + '</div>'
                    + '      ' + deleteButtonHtml
                    + '  </div>'
                    + '  <div class="small text-muted mb-2"><strong>Total Targets Retained:</strong> ' + htmlEscape(String(domainTargetCount)) + '</div>'
                    + '  <div class="pr-domain-layout">'
                    + '      <div>'
                    + '          <div class="pr-progress-legend mb-2">'
                    + '              <span class="me-3">'
                    + '                  <img src="/assets/images/legend-black.png" alt="Skills Retained">'
                    + '                  Skills Retained'
                    + '              </span>'
                    + '              <span>'
                    + '                  <img src="/assets/images/legend-blue.png" alt="Degrees of Independence">'
                    + '                  Degrees of independence'
                    + '              </span>'
                    + '          </div>'
                    + '          <div class="pr-chart-wrap" data-auto-key="instructional.domain_graphs.' + htmlEscape(domainKey) + '">'
                    + '              <div class="pr-chart-empty">' + htmlEscape(sectionPulledNoDataMessage) + '</div>'
                    + '              <canvas id="instructional_domain_chart_' + htmlEscape(domainKeyId) + '" class="pr-chart-canvas d-none"></canvas>'
                    + '          </div>'
                    + '      </div>'
                    + '      <div>'
                    + '          <table class="pr-grid pr-domain-goals-table">'
                    + '              <thead>'
                    + '                  <tr>'
                    + '                      <th style="width: 45%;">Goal</th>'
                    + '                      <th>Targets Mastered (' + htmlEscape(String(domainTargetCount)) + ')</th>'
                    + '                  </tr>'
                    + '              </thead>'
                    + '              <tbody>'
                    + goalRowsHtml
                    + '              </tbody>'
                    + '          </table>'
                    + '      </div>'
                    + '  </div>'
                    + '  <div class="pr-domain-comment-row">'
                    + '      <label class="pr-textarea-label" for="instructional_programmes_comment_' + htmlEscape(domainKeyId) + '">Comments</label>'
                    + '      <textarea id="instructional_programmes_comment_' + htmlEscape(domainKeyId) + '" class="form-control" rows="3" data-manual-key="instructional_programmes_domain_comments.' + htmlEscape(domainKey) + '" data-domain-comment-key="' + htmlEscape(domainKey) + '">' + htmlEscape(domainComment) + '</textarea>'
                    + '  </div>'
                    + '</div>';

                container.append(cardHtml);
                renderInstructionalDomainChart(domainKey, domain.period_graph || null);
            });
        }

        function renderMandingGraphs(graphs, sectionKey) {
            var placeholder = $('[data-auto-key="manding.graphs"]');
            var container = $('#manding_graphs_container');
            if (!container.length) return;

            destroyMandingCharts();
            container.empty();

            if (!Array.isArray(graphs) || !graphs.length) {
                if (placeholder.length) {
                    placeholder.removeClass('d-none text-muted').addClass('text-muted')
                        .text(sectionKey === 'manding'
                            ? sectionPulledNoDataMessage
                            : sectionNotPulledMessage);
                }
                return;
            }

            if (placeholder.length) {
                placeholder.addClass('d-none');
            }

            graphs.forEach(function(item, index) {
                var graphKey = (item && item.key != null) ? String(item.key) : ('graph_' + (index + 1));
                var graphKeyId = graphKey.replace(/[^a-zA-Z0-9_-]/g, '_');
                var graphTitle = (item && item.title != null) ? String(item.title) : 'Mand Graph';
                var graphPayload = (item && item.graph && typeof item.graph === 'object') ? item.graph : null;
                var datasetLegendHtml = buildDatasetLegendHtml(
                    graphPayload && Array.isArray(graphPayload.datasets) ? graphPayload.datasets : []
                );
                var deleteButtonHtml = '';
                if (canSaveDraft) {
                    deleteButtonHtml = '<button type="button" class="btn btn-sm btn-outline-danger btn-delete-manding-graph" data-manding-graph-key="' + htmlEscape(graphKey) + '" title="Delete graph from this draft"><i class="ri-delete-bin-line"></i></button>';
                }

                var cardHtml = ''
                    + '<div class="col-lg-6 col-md-6 col-12">'
                    + '  <div class="h-100">'
                    + '      <div class="d-flex align-items-center justify-content-between mb-2">'
                    + '          <div class="fw-semibold small mb-0">' + htmlEscape(graphTitle) + '</div>'
                    + '          ' + deleteButtonHtml
                    + '      </div>'
                    +        datasetLegendHtml
                    + '      <div class="pr-chart-wrap" data-manding-key="' + htmlEscape(graphKey) + '">'
                    + '          <div class="pr-chart-empty">Graph data unavailable.</div>'
                    + '          <canvas id="manding_graph_canvas_' + htmlEscape(graphKeyId) + '" class="pr-chart-canvas d-none"></canvas>'
                    + '      </div>'
                    + '  </div>'
                    + '</div>';
                container.append(cardHtml);

                if (!graphPayload || !Array.isArray(graphPayload.labels) || !Array.isArray(graphPayload.datasets)) {
                    return;
                }

                var chartWrap = container.find('[data-manding-key="' + graphKey + '"]');
                var canvas = chartWrap.find('canvas')[0];
                var emptyBox = chartWrap.find('.pr-chart-empty');
                if (!canvas || !window.Chart) {
                    if (emptyBox.length) {
                        emptyBox.text('Chart library not available.');
                    }
                    return;
                }

                var chartConfig = {
                    type: (graphPayload.chart_type || 'line'),
                    data: {
                        labels: graphPayload.labels,
                        datasets: graphPayload.datasets
                    },
                    options: buildProgressChartOptions(graphPayload)
                };
                var ctx = canvas.getContext('2d');
                mandingCharts[graphKey] = new Chart(ctx, chartConfig);
                if (emptyBox.length) {
                    emptyBox.addClass('d-none');
                }
                $(canvas).removeClass('d-none');
            });
        }

        function renderProblemBehaviourGraphs(graphs, sectionKey) {
            var placeholder = $('[data-auto-key="problem_behaviour.graphs"]');
            var container = $('#problem_behaviour_graphs_container');
            if (!container.length) return;

            destroyProblemBehaviourCharts();
            container.empty();

            if (!Array.isArray(graphs) || !graphs.length) {
                if (placeholder.length) {
                    placeholder.removeClass('d-none text-muted').addClass('text-muted')
                        .text(sectionKey === 'problem_behaviour_reduction'
                            ? sectionPulledNoDataMessage
                            : sectionNotPulledMessage);
                }
                return;
            }

            if (placeholder.length) {
                placeholder.addClass('d-none');
            }

            graphs.forEach(function(item, index) {
                var graphKey = (item && item.key != null) ? String(item.key) : ('pb_graph_' + (index + 1));
                var graphKeyId = graphKey.replace(/[^a-zA-Z0-9_-]/g, '_');
                var graphTitle = (item && item.title != null) ? String(item.title) : 'Problem Behaviour Graph';
                var graphPayload = (item && item.graph && typeof item.graph === 'object') ? item.graph : null;

                var cardHtml = ''
                    + '<div class="col-lg-6 col-md-6 col-12">'
                    + '  <div class="h-100">'
                    + '      <div class="fw-semibold small mb-2">' + htmlEscape(graphTitle) + '</div>'
                    + '      <div class="pr-chart-wrap" data-pb-key="' + htmlEscape(graphKey) + '">'
                    + '          <div class="pr-chart-empty">Graph data unavailable.</div>'
                    + '          <canvas id="pb_graph_canvas_' + htmlEscape(graphKeyId) + '" class="pr-chart-canvas d-none"></canvas>'
                    + '      </div>'
                    + '  </div>'
                    + '</div>';
                container.append(cardHtml);

                if (!graphPayload || !Array.isArray(graphPayload.labels) || !Array.isArray(graphPayload.datasets)) {
                    return;
                }

                var chartWrap = container.find('[data-pb-key="' + graphKey + '"]');
                var canvas = chartWrap.find('canvas')[0];
                var emptyBox = chartWrap.find('.pr-chart-empty');
                if (!canvas || !window.Chart) {
                    if (emptyBox.length) {
                        emptyBox.text('Chart library not available.');
                    }
                    return;
                }

                var chartConfig = {
                    type: (graphPayload.chart_type || 'line'),
                    data: {
                        labels: graphPayload.labels,
                        datasets: graphPayload.datasets
                    },
                    options: buildProgressChartOptions(graphPayload)
                };
                var ctx = canvas.getContext('2d');
                problemBehaviourCharts[graphKey] = new Chart(ctx, chartConfig);
                if (emptyBox.length) {
                    emptyBox.addClass('d-none');
                }
                $(canvas).removeClass('d-none');
            });
        }

        function applyPulledSectionData(sectionData, sectionKey) {
            if (!sectionData || typeof sectionData !== 'object') return;

            if (sectionKey === 'current_programme_management') {
                var pmKeys = [
                    'pm.sessions_count',
                    'pm.hours_of_instruction',
                    'pm.dti_net_ratio',
                    'pm.schedule_of_reinforcement',
                    'pm.current_programmes'
                ];
                var pmHasData = pmKeys.some(function(key) {
                    return !isNoDataValue(sectionData[key]);
                });
                if (!pmHasData) {
                    var pmMessage = sectionPulledNoDataMessage;
                    pmKeys.forEach(function(key) {
                        var target = $('[data-auto-key="' + key + '"]');
                        if (!target.length) return;
                        target.addClass('text-muted');
                        if (key === 'pm.current_programmes') {
                            target.html('<strong>Current Programs:</strong><div class="pm-current-programmes">' + htmlEscape(pmMessage) + '</div>');
                        } else {
                            target.text(pmMessage);
                        }
                    });
                    return;
                }
            }

            Object.keys(sectionData).forEach(function(key) {
                var value = sectionData[key];
                if (key === 'instructional.domains') {
                    renderInstructionalDomains(value, sectionKey);
                    return;
                }
                if (key === 'manding.graphs') {
                    renderMandingGraphs(value, sectionKey);
                    return;
                }
                if (key === 'problem_behaviour.graphs') {
                    renderProblemBehaviourGraphs(value, sectionKey);
                    return;
                }

                var target = $('[data-auto-key="' + key + '"]');
                if (!target.length) return;

                if (key === 'progress.cumulative_all_time_graph' || key === 'progress.cumulative_period_graph') {
                    renderProgressChart(key, value, sectionKey);
                    return;
                }

                var displayValue = 'N/A';
                if (!isNoDataValue(value)) {
                    displayValue = String(value);
                } else if (sectionKey === 'progress' && key === 'progress.program_start_date_text') {
                    displayValue = sectionPulledNoDataMessage;
                }
                target.removeClass('text-muted');
                if (key === 'pm.current_programmes') {
                    var raw = String(displayValue || '').trim();
                    if (raw === '' || raw === 'N/A' || raw.toLowerCase() === 'none') {
                        target.html(
                            '<strong>Current Programs:</strong>' +
                            '<div class="pm-current-programmes">' + htmlEscape(raw === '' ? 'N/A' : raw) + '</div>'
                        );
                        return;
                    }

                    var lines = raw.split(/\r?\n/).map(function(line) {
                        return String(line || '').trim();
                    }).filter(function(line) {
                        return line !== '';
                    });

                    var blocks = [];
                    lines.forEach(function(line, index) {
                        var splitAt = line.indexOf(':');
                        var domainText = splitAt >= 0 ? line.substring(0, splitAt).trim() : line.trim();
                        domainText = domainText.replace(/^[A-Za-z]{1,10}[0-9]*\s*-\s*/, '').trim();
                        var goalsText = splitAt >= 0 ? line.substring(splitAt + 1).trim() : '';
                        if (domainText === '') {
                            domainText = 'N/A';
                        }

                        var blockHtml = '<div class="pm-programme-item"><strong class="pm-domain-label">' + htmlEscape(domainText) + '</strong>';
                        if (goalsText !== '') {
                            blockHtml += '<strong class="pm-domain-separator">:</strong> ' + htmlEscape(goalsText);
                        }
                        blockHtml += '</div>';
                        blocks.push(blockHtml);
                    });

                    target.html(
                        '<strong>Current Programs:</strong>' +
                        '<div class="pm-current-programmes">' + blocks.join('') + '</div>'
                    );
                } else {
                    target.text(displayValue);
                }
            });
        }

        renderInstructionalDomains([], '');
        if (pulledSectionsStore && typeof pulledSectionsStore === 'object') {
            Object.keys(pulledSectionsStore).forEach(function(sectionKey) {
                var sectionPayload = pulledSectionsStore[sectionKey];
                if (!sectionPayload || typeof sectionPayload !== 'object') return;
                var sectionData = sectionPayload.data && typeof sectionPayload.data === 'object'
                    ? sectionPayload.data
                    : sectionPayload;
                applyPulledSectionData(sectionData, sectionKey);
            });
        }
        updateInstructionalImageLimitNote();
        renderInstructionalImages([]);
        fetchInstructionalImages(false);

        $('.btn-pull-placeholder').on('click', function() {
            var sectionKey = $(this).data('section') || '';
            var versionId = $('#version_id').val();
            var button = $(this);

            if (!sectionKey || !versionId) {
                showAlert('Validation_Error', 'Missing section or version id.', 'error');
                return;
            }

            setButtonBusy(button, true, 'Fetching...');
            postWithLoader('<?= esc($progressVersionBaseUrl, 'js') ?>/' + versionId + '/pull-section', {
                section_key: sectionKey
            }).done(function(response) {
                if (response.status !== 'success') {
                    showAlert(response.statusText || '', response.message || 'Unable to pull section.', response.status || 'error');
                    return;
                }

                var sectionData = (response.data && response.data.section_data) ? response.data.section_data : {};
                persistPulledSectionState(
                    sectionKey,
                    sectionData,
                    response && response.data && response.data.pulled_at ? response.data.pulled_at : ''
                );
                applyPulledSectionData(sectionData, sectionKey);
                showAlert(
                    response.statusText || 'Progress Report',
                    response.message || ('Section pulled: ' + sectionKey),
                    response.status || 'success'
                );
            }).fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, 'Request failed: ' + textStatus + '<br>' + error, 'error');
            }).always(function() {
                setButtonBusy(button, false);
            });
        });

        $('#manding_graphs_container').on('click', '.btn-delete-manding-graph', function() {
            if (!canSaveDraft) {
                showAlert('Validation_Error', 'You do not have permission to delete graphs.', 'error');
                return;
            }

            var trigger = $(this);
            var graphKey = String(trigger.data('manding-graph-key') || '');
            if (!graphKey) {
                return;
            }

            var graphs = getMandingGraphsFromStore();
            if (!Array.isArray(graphs) || !graphs.length) {
                showAlert('Progress Report', 'No manding graph found for deletion.', 'error');
                return;
            }

            var graphTitle = graphKey;
            var filteredGraphs = graphs.filter(function(item, index) {
                var key = (item && item.key != null) ? String(item.key) : ('graph_' + (index + 1));
                if (key === graphKey) {
                    graphTitle = (item && item.title != null) ? String(item.title) : graphTitle;
                    return false;
                }
                return true;
            });

            if (filteredGraphs.length === graphs.length) {
                showAlert('Progress Report', 'Selected graph is no longer available.', 'error');
                return;
            }

            var beforeDeleteStore = clonePlainData(getPulledSectionsStoreSnapshot(), {});
            Swal.fire({
                title: 'Delete Graph',
                text: 'Remove "' + graphTitle + '" from this draft?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="ri-delete-bin-line align-bottom me-1"></i>Delete',
                cancelButtonText: '<i class="ri-close-line align-bottom me-1"></i>Cancel',
                customClass: {
                    confirmButton: 'btn btn-danger w-xs me-2 mt-2',
                    cancelButton: 'btn btn-secondary w-xs mt-2'
                },
                buttonsStyling: false
            }).then(function(result) {
                if (!result.isConfirmed) {
                    return;
                }

                setMandingGraphsToStore(filteredGraphs);
                renderMandingGraphs(filteredGraphs, 'manding');

                postSectionStateUpdate(
                    'manding',
                    getSectionDataFromStore('manding')
                )
                    .done(function(response) {
                        if (response.status !== 'success') {
                            pulledSectionsStore = beforeDeleteStore;
                            $('#progress_pulled_sections').val(JSON.stringify(beforeDeleteStore));
                            renderMandingGraphs(getMandingGraphsFromStore(), 'manding');
                            showAlert(response.statusText || '', response.message || 'Unable to delete graph.', response.status || 'error');
                            return;
                        }
                        showAlert(response.statusText || 'Progress Report', 'Graph deleted from this draft.', response.status || 'success');
                    })
                    .fail(function(jqXHR, textStatus, error) {
                        pulledSectionsStore = beforeDeleteStore;
                        $('#progress_pulled_sections').val(JSON.stringify(beforeDeleteStore));
                        renderMandingGraphs(getMandingGraphsFromStore(), 'manding');
                        showAlert(jqXHR.status, 'Request failed: ' + textStatus + '<br>' + error, 'error');
                    });
            });
        });

        $('#instructional_domains_container').on('click', '.btn-delete-instructional-domain', function() {
            if (!canSaveDraft) {
                showAlert('Validation_Error', 'You do not have permission to delete domains.', 'error');
                return;
            }

            var trigger = $(this);
            var domainKey = String(trigger.data('instructional-domain-key') || '');
            if (!domainKey) {
                return;
            }

            var domains = getInstructionalDomainsFromStore();
            if (!Array.isArray(domains) || !domains.length) {
                showAlert('Progress Report', 'No instructional domain found for deletion.', 'error');
                return;
            }

            var domainTitle = domainKey;
            var filteredDomains = domains.filter(function(domain, index) {
                var key = resolveInstructionalDomainKey(domain, index);
                if (key === domainKey) {
                    domainTitle = (domain && domain.title != null) ? String(domain.title) : domainTitle;
                    return false;
                }
                return true;
            });

            if (filteredDomains.length === domains.length) {
                showAlert('Progress Report', 'Selected domain is no longer available.', 'error');
                return;
            }

            var beforeDeleteStore = clonePlainData(getPulledSectionsStoreSnapshot(), {});
            var beforeCommentStore = clonePlainData(instructionalDomainCommentsStore, {});
            Swal.fire({
                title: 'Delete Domain',
                text: 'Remove "' + domainTitle + '" from this draft?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="ri-delete-bin-line align-bottom me-1"></i>Delete',
                cancelButtonText: '<i class="ri-close-line align-bottom me-1"></i>Cancel',
                customClass: {
                    confirmButton: 'btn btn-danger w-xs me-2 mt-2',
                    cancelButton: 'btn btn-secondary w-xs mt-2'
                },
                buttonsStyling: false
            }).then(function(result) {
                if (!result.isConfirmed) {
                    return;
                }

                captureInstructionalDomainCommentsFromDom();
                removeInstructionalDomainCommentFromStore(domainKey);
                setInstructionalDomainsToStore(filteredDomains);
                renderInstructionalDomains(filteredDomains, 'instructional_programmes', { skipCaptureComments: true });

                postSectionStateUpdate(
                    'instructional_programmes',
                    getSectionDataFromStore('instructional_programmes'),
                    {
                        instructional_domain_comments_json: JSON.stringify(clonePlainData(instructionalDomainCommentsStore, {}))
                    }
                )
                    .done(function(response) {
                        if (response.status !== 'success') {
                            pulledSectionsStore = beforeDeleteStore;
                            $('#progress_pulled_sections').val(JSON.stringify(beforeDeleteStore));
                            instructionalDomainCommentsStore = beforeCommentStore;
                            syncInstructionalDomainCommentsInput();
                            renderInstructionalDomains(getInstructionalDomainsFromStore(), 'instructional_programmes', { skipCaptureComments: true });
                            showAlert(response.statusText || '', response.message || 'Unable to delete domain.', response.status || 'error');
                            return;
                        }
                        showAlert(response.statusText || 'Progress Report', 'Domain deleted from this draft.', response.status || 'success');
                    })
                    .fail(function(jqXHR, textStatus, error) {
                        pulledSectionsStore = beforeDeleteStore;
                        $('#progress_pulled_sections').val(JSON.stringify(beforeDeleteStore));
                        instructionalDomainCommentsStore = beforeCommentStore;
                        syncInstructionalDomainCommentsInput();
                        renderInstructionalDomains(getInstructionalDomainsFromStore(), 'instructional_programmes', { skipCaptureComments: true });
                        showAlert(jqXHR.status, 'Request failed: ' + textStatus + '<br>' + error, 'error');
                    });
            });
        });

        $('#instructional_image_add_btn').on('click', function() {
            if (!canManageInstructionalImages) {
                showAlert('Validation_Error', 'You do not have permission to upload images.', 'error');
                return;
            }
            if (instructionalImagesStore.length >= instructionalImageMaxCount) {
                showAlert('Validation_Error', 'Maximum allowed images is ' + instructionalImageMaxCount + '.', 'error');
                return;
            }
            $('#instructional_image_upload').trigger('click');
        });

        $('#instructional_image_upload').on('change', function() {
            var files = this.files || [];
            if (!files.length) {
                return;
            }
            uploadInstructionalImages(files);
            $(this).val('');
        });

        $('#instructional_images_list').on('click', '.instructional-image-view', function() {
            var viewUrl = String($(this).data('view-url') || '').trim();
            if (viewUrl === '') {
                showAlert('Progress Report', 'Image preview is unavailable.', 'error');
                return;
            }

            var fileName = String($(this).data('file-name') || 'Image');
            Swal.fire({
                titleText: fileName,
                imageUrl: viewUrl,
                imageAlt: fileName,
                showCloseButton: true,
                showConfirmButton: false,
                width: 900
            });
        });

        $('#instructional_images_list').on('click', '.instructional-image-delete', function() {
            if (!canManageInstructionalImages) return;
            var artifactId = parseInt($(this).data('artifact-id') || '0', 10);
            if (!artifactId) return;

            Swal.fire({
                title: 'Delete Image',
                text: 'This will permanently remove the selected image. Continue?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="ri-delete-bin-line align-bottom me-1"></i>Delete',
                cancelButtonText: '<i class="ri-close-line align-bottom me-1"></i>Cancel',
                customClass: {
                    confirmButton: 'btn btn-danger w-xs me-2 mt-2',
                    cancelButton: 'btn btn-secondary w-xs mt-2'
                },
                buttonsStyling: false
            }).then(function(result) {
                if (!result.isConfirmed) return;
                deleteInstructionalImage(artifactId);
            });
        });

        $('#instructional_images_list').on('click', '.instructional-image-replace', function() {
            if (!canManageInstructionalImages) return;
            var artifactId = parseInt($(this).data('artifact-id') || '0', 10);
            if (!artifactId) return;
            instructionalReplaceArtifactId = artifactId;
            instructionalReplaceTriggerButton = $(this);
            $('#instructional_image_replace_input').trigger('click');
        });

        $('#instructional_image_replace_input').on('change', function() {
            var files = this.files || [];
            if (!files.length) {
                instructionalReplaceArtifactId = 0;
                instructionalReplaceTriggerButton = null;
                return;
            }
            replaceInstructionalImage(files[0]);
            $(this).val('');
        });

        $('#btn_save_draft').on('click', function() {
            var versionId = $('#version_id').val();
            var button = $(this);
            setButtonBusy(button, true, 'Saving...');
            postWithLoader('<?= esc($progressVersionBaseUrl, 'js') ?>/' + versionId + '/save-draft', payload())
                .done(function(response) {
                    showAlert(response.statusText || '', response.message || 'Saved.', response.status || 'success');
                })
                .fail(function(jqXHR, textStatus, error) {
                    showAlert(jqXHR.status, 'Request failed: ' + textStatus + '<br>' + error, 'error');
                })
                .always(function() {
                    setButtonBusy(button, false);
                });
        });

        $('#btn_finalize_draft').on('click', function() {
            var versionId = $('#version_id').val();
            var button = $(this);
            var finalizeValidationErrors = collectFinalizeValidationErrors();
            if (finalizeValidationErrors.length) {
                var listHtml = finalizeValidationErrors.map(function(line, index) {
                    return (index + 1) + '. ' + htmlEscape(line);
                }).join('<br>');
                showAlert(
                    'Progress Report',
                    listHtml,
                    'error'
                );
                return;
            }
            Swal.fire({
                title: 'Finalize Draft',
                text: 'This will lock the draft and generate PDF. Continue?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="ri-check-double-line align-bottom me-1"></i>Finalize',
                cancelButtonText: '<i class="ri-close-line align-bottom me-1"></i>Cancel',
                customClass: {
                    confirmButton: 'btn btn-primary w-xs me-2 mt-2',
                    cancelButton: 'btn btn-secondary w-xs mt-2'
                },
                buttonsStyling: false
            }).then(function(result) {
                if (!result.isConfirmed) return;
                setButtonBusy(button, true, 'Saving & Generating PDF...');
                showPageLoader();

                var finishFinalizeAction = function() {
                    hidePageLoader();
                    setButtonBusy(button, false);
                };

                $.post('<?= esc($progressVersionBaseUrl, 'js') ?>/' + versionId + '/save-draft', payload())
                    .done(function(saveResponse) {
                        if (saveResponse.status !== 'success') {
                            showAlert(saveResponse.statusText || '', saveResponse.message || 'Unable to save draft.', saveResponse.status || 'error');
                            finishFinalizeAction();
                            return;
                        }
                        $.post('<?= esc($progressVersionBaseUrl, 'js') ?>/' + versionId + '/finalize', {})
                            .done(function(response) {
                                if (response.status !== 'success') {
                                    showAlert(response.statusText || '', response.message || 'Unable to finalize.', response.status || 'error');
                                    finishFinalizeAction();
                                    return;
                                }
                                showAlert(response.statusText || '', response.message || 'Finalized.', response.status || 'success');
                                if (response.data && response.data.pdf_url) {
                                    window.open(response.data.pdf_url, '_blank');
                                }
                                finishFinalizeAction();
                                window.location.href = backToListUrl;
                            })
                            .fail(function(jqXHR, textStatus, error) {
                                showAlert(jqXHR.status, 'Request failed: ' + textStatus + '<br>' + error, 'error');
                                finishFinalizeAction();
                            });
                    })
                    .fail(function(jqXHR, textStatus, error) {
                        showAlert(jqXHR.status, 'Request failed: ' + textStatus + '<br>' + error, 'error');
                        finishFinalizeAction();
                    });
            });
        });
    });
</script>
<?= $this->endSection() ?>

