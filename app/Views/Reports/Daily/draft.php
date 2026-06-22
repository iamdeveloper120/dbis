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

    .daily-meta-table,
    .daily-table-grid,
    .daily-split-table,
    .daily-kv-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .daily-meta-table td {
        border: 1px solid #dfe3ea;
        padding: 8px 10px;
        vertical-align: top;
    }

    .daily-meta-label {
        width: 14%;
        font-size: 12px;
        color: #495057;
        font-weight: 600;
        white-space: nowrap;
    }

    .daily-meta-value {
        width: 26%;
        font-size: 13px;
        color: #212529;
        white-space: normal;
        word-break: break-word;
        line-height: 1.25;
        min-height: 20px;
    }

    .daily-meta-label-2 {
        width: 22%;
        font-size: 12px;
        color: #495057;
        font-weight: 600;
        white-space: nowrap;
    }

    .daily-meta-value-2 {
        width: 38%;
        font-size: 13px;
        color: #212529;
        white-space: normal;
        word-break: break-word;
        line-height: 1.25;
        min-height: 20px;
    }

    .daily-section-title {
        margin: 12px 0 6px 0;
        font-size: 15px;
        font-weight: 600;
        color: #1f2937;
    }

    .daily-table-grid th,
    .daily-table-grid td,
    .daily-kv-table td {
        border: 1px solid #dfe3ea;
        padding: 8px 10px;
        vertical-align: top;
        font-size: 13px;
        word-break: break-word;
    }

    .daily-table-grid th {
        font-weight: 600;
        text-align: left;
        background: #f8f9fb;
    }

    .daily-split-table td {
        vertical-align: top;
        width: 50%;
        padding: 0;
    }

    .daily-split-left {
        padding-right: 12px;
    }

    .daily-split-right {
        padding-left: 12px;
    }

    .daily-kv-label {
        width: 34%;
        font-weight: 600;
        color: #495057;
    }

    .daily-kv-value {
        width: 66%;
        white-space: normal;
        word-break: break-word;
        color: #212529;
    }

    .daily-comment-box {
        border: 1px solid #dfe3ea;
        min-height: 58px;
        padding: 8px;
        font-size: 13px;
        color: #1f2937;
        line-height: 1.4;
        white-space: pre-wrap;
        word-break: break-word;
        background: #fff;
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

    .daily-image-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 12px;
    }

    .daily-image-item {
        border: 1px solid #dfe3ea;
        border-radius: 6px;
        padding: 10px;
        background: #fff;
    }

    .daily-image-thumb {
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

    .daily-image-thumb img {
        max-width: 100%;
        max-height: 100%;
        display: block;
    }

    .daily-image-meta {
        font-size: 12px;
        color: #6c757d;
        line-height: 1.45;
        word-break: break-word;
    }

    @media (max-width: 991.98px) {
        .progress-paper {
            padding: 12px;
        }

        .daily-split-table td {
            display: block;
            width: 100%;
        }

        .daily-split-left {
            padding-right: 0;
            margin-bottom: 12px;
        }

        .daily-split-right {
            padding-left: 0;
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

    $workflowStatus = strtoupper(trim((string) ($version['workflow_status'] ?? 'DRAFT')));
    if ($workflowStatus === '') {
        $workflowStatus = 'DRAFT';
    }
    $pulledAtDisplay = '';
    if (isset($section_status['sections']['daily_content']['pulled_at'])) {
        $pulledAtDisplay = $formatDate((string) $section_status['sections']['daily_content']['pulled_at'], true);
    }
    $dailyImageMaxSizeMb = (int) ($daily_image_limits['max_size_mb'] ?? 1);
    $dailyImageMaxCount = (int) ($daily_image_limits['max_count'] ?? 4);
    $dailyListUrl = base_url('reports/daily') . (string) ($list_state_query ?? '');
?>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h5 class="mb-sm-0">Daily Report Draft</h5>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="<?= esc($dailyListUrl) ?>">Daily Reports</a></li>
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
                        <span class="badge bg-warning-subtle text-warning text-uppercase me-2"><?= esc($workflowStatus) ?></span>
                        <span class="text-muted">Version v<?= (int) ($version['version_no'] ?? 0) ?></span>
                    </div>
                    <div>
                        <a href="<?= esc($dailyListUrl) ?>" class="btn btn-secondary btn-sm">
                            <i class="ri-arrow-left-line align-bottom me-1"></i>Back to List
                        </a>
                    </div>
                </div>

                <input type="hidden" id="version_id" value="<?= (int) ($version['version_id'] ?? 0) ?>">
                <input type="hidden" id="daily_image_max_size_mb" value="<?= (int) $dailyImageMaxSizeMb ?>">
                <input type="hidden" id="daily_image_max_count" value="<?= (int) $dailyImageMaxCount ?>">

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

                    <div class="pr-title">DAILY DATA SHEET</div>

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div class="small text-muted" id="daily_content_pulled_at_wrap">
                            Last pulled:
                            <span id="daily_content_pulled_at"><?= esc($pulledAtDisplay !== '' ? $pulledAtDisplay : 'Not pulled yet') ?></span>
                        </div>
                        <div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="btn_fetch_daily_content">
                                <i class="ri-download-2-line align-bottom me-1"></i>Fetch Latest Details
                            </button>
                        </div>
                    </div>

                    <table class="daily-meta-table">
                        <tr>
                            <td class="daily-meta-label">Learner:</td>
                            <td class="daily-meta-value"><?= esc((string) ($version['learner_name'] ?? '-')) ?></td>
                            <td class="daily-meta-label-2">Date and Session Time</td>
                            <td class="daily-meta-value-2" data-token="report_date">Data not pulled yet.</td>
                        </tr>
                        <tr>
                            <td class="daily-meta-label">Tutor:</td>
                            <td class="daily-meta-value" data-token="tutor_names">Data not pulled yet.</td>
                            <td class="daily-meta-label-2">NET vs DTI:</td>
                            <td class="daily-meta-value-2" data-token="net_vs_dti">Data not pulled yet.</td>
                        </tr>
                    </table>

                    <h6 class="daily-section-title">Program probes</h6>
                    <div id="daily_program_probes_wrap" class="table-responsive">
                        <table class="daily-table-grid">
                            <thead>
                                <tr>
                                    <th>Domain</th>
                                    <th>Goal</th>
                                    <th>Target</th>
                                    <th>Probe</th>
                                </tr>
                            </thead>
                            <tbody id="daily_program_probes_body">
                                <tr><td colspan="4" class="text-center text-muted">Data not pulled yet.</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <table class="daily-split-table mt-3">
                        <tr>
                            <td class="daily-split-left">
                                <h6 class="daily-section-title mt-0">Mand Data</h6>
                                <table class="daily-kv-table">
                                    <tr>
                                        <td class="daily-kv-label">Frequency</td>
                                        <td class="daily-kv-value"><span data-token="mands_frequency">-</span></td>
                                    </tr>
                                    <tr>
                                        <td class="daily-kv-label">Variety</td>
                                        <td class="daily-kv-value"><span data-token="mands_variety">-</span></td>
                                    </tr>
                                </table>
                            </td>
                            <td class="daily-split-right">
                                <h6 class="daily-section-title mt-0">Problem Behaviour Data</h6>
                                <table class="daily-kv-table">
                                    <tr>
                                        <td class="daily-kv-label">Frequency</td>
                                        <td class="daily-kv-value"><span data-token="problem_behavior_frequency">-</span></td>
                                    </tr>
                                    <tr>
                                        <td class="daily-kv-label">Duration</td>
                                        <td class="daily-kv-value"><span data-token="problem_behavior_duration">-</span></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                    <div class="daily-comment-section">
                        <h6 class="daily-section-title">Comments</h6>
                        <div class="daily-comment-box" data-token="tutor_comments">Data not pulled yet.</div>
                    </div>

                    <div class="daily-comment-section">
                        <h6 class="daily-section-title">Upload Images</h6>
                        <div class="small text-muted mb-2">This section is added in draft and will appear between Comments and Wow Moments in generated PDF.</div>

                        <div class="input-group mb-2">
                            <input type="file" class="form-control" id="daily_image_upload" accept=".jpg,.jpeg,.png,image/jpeg,image/png" multiple <?= auth()->user()->can('reporting.daily.save-draft') ? '' : 'disabled' ?>>
                            <?php if (auth()->user()->can('reporting.daily.save-draft')): ?>
                                <button type="button" class="btn btn-outline-secondary" id="daily_image_add_btn">
                                    <i class="ri-upload-2-line align-bottom me-1"></i>Add
                                </button>
                            <?php endif; ?>
                        </div>
                        <input type="file" class="d-none" id="daily_image_replace_input" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                        <div class="small text-muted mb-3" id="daily_image_limit_note">
                            Allowed: JPG/JPEG/PNG, max <?= (int) $dailyImageMaxCount ?> images, <?= (int) $dailyImageMaxSizeMb ?> MB each.
                        </div>

                        <div id="daily_images_list" class="daily-image-list">
                            <div class="text-muted">Loading images...</div>
                        </div>
                    </div>

                    <div class="daily-comment-section">
                        <h6 class="daily-section-title">Wow Moments</h6>
                        <div class="daily-comment-box" data-token="wow_moments">Data not pulled yet.</div>
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
                    <?php if (auth()->user()->can('reporting.daily.save-draft')): ?>
                        <button type="button" class="btn btn-primary" id="btn_save_draft">
                            <i class="ri-save-line align-bottom me-1"></i>Save Draft
                        </button>
                    <?php endif; ?>
                    <?php if (auth()->user()->can('reporting.daily.finalize')): ?>
                        <button type="button" class="btn btn-success" id="btn_finalize_draft">
                            <i class="ri-check-double-line align-bottom me-1"></i>Generate PDF
                        </button>
                    <?php endif; ?>
                </div>
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

        var loaderCounter = 0;
        var canSaveDraft = <?= auth()->user()->can('reporting.daily.save-draft') ? 'true' : 'false' ?>;
        var canManageImages = canSaveDraft;
        var dailyImageMaxSizeMb = parseInt($('#daily_image_max_size_mb').val() || '1', 10);
        var dailyImageMaxCount = parseInt($('#daily_image_max_count').val() || '4', 10);
        var dailyImageAllowedExtensions = ['jpg', 'jpeg', 'png'];
        var dailyImagesStore = [];
        var dailyReplaceArtifactId = 0;
        var dailyReplaceTriggerButton = null;
        var currentSectionData = <?= json_encode($draft_section_data ?? [], JSON_UNESCAPED_UNICODE) ?>;
        var currentPulledAt = <?= json_encode($pulledAtDisplay !== '' ? $pulledAtDisplay : '', JSON_UNESCAPED_UNICODE) ?>;
        var backToListUrl = '<?= esc($dailyListUrl, 'js') ?>';

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

        function htmlEscape(text) {
            return $('<div/>').text(text == null ? '' : String(text)).html();
        }

        function formatBytes(bytes) {
            var size = parseInt(bytes || '0', 10);
            if (!size) return '-';
            if (size >= 1024 * 1024) return (size / (1024 * 1024)).toFixed(2) + ' MB';
            if (size >= 1024) return (size / 1024).toFixed(2) + ' KB';
            return size + ' B';
        }

        function extractTokenValues(sectionData) {
            if (sectionData && typeof sectionData === 'object') {
                if (sectionData.token_values && typeof sectionData.token_values === 'object') {
                    return sectionData.token_values;
                }
                if (sectionData.render_payload && typeof sectionData.render_payload === 'object') {
                    return sectionData.render_payload;
                }
            }
            return {};
        }

        function setTokenValue(tokenKey, value) {
            $('[data-token="' + tokenKey + '"]').each(function() {
                $(this).text(value == null ? '' : String(value));
            });
        }

        function getTokenValue(tokenValues, key, fallback) {
            if (tokenValues && Object.prototype.hasOwnProperty.call(tokenValues, key)) {
                var value = tokenValues[key];
                return value == null ? '' : String(value);
            }
            return fallback == null ? '' : String(fallback);
        }

        function renderProgramProbesTable(tokenValues) {
            var html = tokenValues.program_probes_table || '';
            if (!html) {
                $('#daily_program_probes_body').html('<tr><td colspan="4" class="text-center text-muted">No program probe data available.</td></tr>');
                return;
            }
            $('#daily_program_probes_body').html(html);
        }

        function renderDailySection(sectionData) {
            var tokenValues = extractTokenValues(sectionData);
            if (!tokenValues || !Object.keys(tokenValues).length) {
                setTokenValue('report_date', 'Data not pulled yet.');
                setTokenValue('tutor_names', 'Data not pulled yet.');
                setTokenValue('net_vs_dti', 'Data not pulled yet.');
                setTokenValue('tutor_comments', 'Data not pulled yet.');
                setTokenValue('wow_moments', 'Data not pulled yet.');
                $('[data-token="mands_frequency"]').text('-');
                $('[data-token="mands_variety"]').text('-');
                $('[data-token="problem_behavior_frequency"]').text('-');
                $('[data-token="problem_behavior_duration"]').text('-');
                $('#daily_program_probes_body').html('<tr><td colspan="4" class="text-center text-muted">Data not pulled yet.</td></tr>');
                $('#daily_content_pulled_at').text(currentPulledAt || 'Not pulled yet');
                return;
            }

            setTokenValue('report_date', getTokenValue(tokenValues, 'report_date', ''));
            setTokenValue('tutor_names', getTokenValue(tokenValues, 'tutor_names', ''));
            setTokenValue('net_vs_dti', getTokenValue(tokenValues, 'net_vs_dti', ''));
            setTokenValue('tutor_comments', getTokenValue(tokenValues, 'tutor_comments', ''));
            setTokenValue('wow_moments', getTokenValue(tokenValues, 'wow_moments', ''));
            $('[data-token="mands_frequency"]').text(getTokenValue(tokenValues, 'mands_frequency', ''));
            $('[data-token="mands_variety"]').text(getTokenValue(tokenValues, 'mands_variety', ''));
            $('[data-token="problem_behavior_frequency"]').text(getTokenValue(tokenValues, 'problem_behavior_frequency', ''));
            $('[data-token="problem_behavior_duration"]').text(getTokenValue(tokenValues, 'problem_behavior_duration', ''));
            renderProgramProbesTable(tokenValues);
            $('#daily_content_pulled_at').text(currentPulledAt || 'Just now');
        }

        function buildManualJson() {
            return {
                daily_images: dailyImagesStore.map(function(image) {
                    return {
                        artifact_id: parseInt(image.artifact_id || '0', 10),
                        file_name: image.file_name || '',
                        mime_type: image.mime_type || '',
                        file_size: image.file_size != null ? parseInt(image.file_size, 10) : null
                    };
                }).filter(function(image) {
                    return image.artifact_id > 0;
                })
            };
        }

        function renderDailyImages(images) {
            dailyImagesStore = Array.isArray(images) ? images.slice() : [];
            if (!dailyImagesStore.length) {
                $('#daily_images_list').html('<div class="text-muted">No images uploaded yet.</div>');
                return;
            }

            var html = '';
            dailyImagesStore.forEach(function(image) {
                var artifactId = parseInt(image.artifact_id || '0', 10);
                var viewUrl = image.view_url || '#';
                html += '<div class="daily-image-item">';
                html += '<div class="daily-image-thumb">';
                if (image.view_url) {
                    html += '<img src="' + htmlEscape(viewUrl) + '" alt="' + htmlEscape(image.file_name || 'Daily Image') + '">';
                } else {
                    html += '<span class="text-muted small">Preview unavailable</span>';
                }
                html += '</div>';
                html += '<div class="daily-image-meta"><strong>' + htmlEscape(image.file_name || 'Image') + '</strong><br>';
                html += 'Type: ' + htmlEscape(image.mime_type || '-') + '<br>';
                html += 'Size: ' + htmlEscape(formatBytes(image.file_size)) + '</div>';
                html += '<div class="d-flex gap-1 flex-wrap mt-2">';
                if (image.view_url) {
                    html += '<button type="button" class="btn btn-sm btn-light daily-image-view" data-view-url="' + htmlEscape(viewUrl) + '" data-file-name="' + htmlEscape(image.file_name || 'Daily Image') + '"><i class="ri-eye-line me-1"></i>View</button>';
                }
                if (canManageImages && artifactId > 0) {
                    html += '<button type="button" class="btn btn-sm btn-soft-info daily-image-replace" data-artifact-id="' + artifactId + '"><i class="ri-refresh-line me-1"></i>Replace</button>';
                    html += '<button type="button" class="btn btn-sm btn-soft-danger daily-image-delete" data-artifact-id="' + artifactId + '"><i class="ri-delete-bin-line me-1"></i>Delete</button>';
                }
                html += '</div></div>';
            });

            $('#daily_images_list').html(html);
        }

        function applyImageLimits(limits) {
            if (!limits || typeof limits !== 'object') return;

            var maxSizeMb = parseInt(limits.max_size_mb || dailyImageMaxSizeMb, 10);
            var maxCount = parseInt(limits.max_count || dailyImageMaxCount, 10);
            if (Number.isFinite(maxSizeMb) && maxSizeMb > 0) {
                dailyImageMaxSizeMb = maxSizeMb;
            }
            if (Number.isFinite(maxCount) && maxCount > 0) {
                dailyImageMaxCount = maxCount;
            }

            $('#daily_image_limit_note').text('Allowed: JPG/JPEG/PNG, max ' + dailyImageMaxCount + ' images, ' + dailyImageMaxSizeMb + ' MB each.');
        }

        function validateImageFiles(files, replaceMode) {
            if (!files || !files.length) {
                return { ok: false, message: 'Please select at least one image file.' };
            }

            if (!replaceMode && (dailyImagesStore.length + files.length) > dailyImageMaxCount) {
                return {
                    ok: false,
                    message: 'Maximum allowed images is ' + dailyImageMaxCount + '.'
                };
            }

            var maxBytes = dailyImageMaxSizeMb * 1024 * 1024;
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                var parts = String(file.name || '').split('.');
                var ext = parts.length > 1 ? parts.pop().toLowerCase() : '';
                if ($.inArray(ext, dailyImageAllowedExtensions) === -1) {
                    return { ok: false, message: 'Only JPG, JPEG, and PNG images are allowed.' };
                }
                if (!Number.isFinite(file.size) || file.size <= 0) {
                    return { ok: false, message: 'Invalid file selected.' };
                }
                if (file.size > maxBytes) {
                    return { ok: false, message: 'File "' + file.name + '" exceeds ' + dailyImageMaxSizeMb + ' MB limit.' };
                }
            }

            return { ok: true };
        }

        function fetchDailyImages(useLoader) {
            var versionId = $('#version_id').val();
            if (!versionId) return $.Deferred().resolve().promise();

            var request = useLoader
                ? postWithLoader('<?= base_url('reports/daily/version') ?>/' + versionId + '/images', {})
                : $.post('<?= base_url('reports/daily/version') ?>/' + versionId + '/images', {});

            return request.done(function(response) {
                if (!response || response.status !== 'success') {
                    renderDailyImages([]);
                    return;
                }

                var data = response.data && typeof response.data === 'object' ? response.data : {};
                applyImageLimits(data.limits || {});
                renderDailyImages(Array.isArray(data.images) ? data.images : []);
            }).fail(function() {
                renderDailyImages([]);
            });
        }

        function uploadDailyImages(files) {
            var validation = validateImageFiles(files, false);
            if (!validation.ok) {
                showAlert('Validation_Error', validation.message, 'error');
                return;
            }

            var versionId = $('#version_id').val();
            if (!versionId) {
                showAlert('Validation_Error', 'Missing version id.', 'error');
                return;
            }

            var addBtn = $('#daily_image_add_btn');
            var formData = new FormData();
            for (var i = 0; i < files.length; i++) {
                formData.append('images[]', files[i]);
            }

            setButtonBusy(addBtn, true, 'Uploading...');
            showPageLoader();
            $.ajax({
                url: '<?= base_url('reports/daily/version') ?>/' + versionId + '/images/upload',
                type: 'post',
                data: formData,
                processData: false,
                contentType: false
            }).done(function(response) {
                if (!response || response.status !== 'success') {
                    showAlert(
                        (response && response.statusText) ? response.statusText : 'Daily Report',
                        (response && response.message) ? response.message : 'Unable to upload images.',
                        (response && response.status) ? response.status : 'error'
                    );
                    return;
                }

                var data = response.data && typeof response.data === 'object' ? response.data : {};
                applyImageLimits(data.limits || {});
                renderDailyImages(Array.isArray(data.images) ? data.images : []);
                showAlert(response.statusText || 'Daily Report', response.message || 'Images uploaded.', response.status || 'success');
            }).fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, 'Request failed: ' + textStatus + '<br>' + error, 'error');
            }).always(function() {
                hidePageLoader();
                setButtonBusy(addBtn, false);
            });
        }

        function deleteDailyImage(artifactId) {
            var versionId = $('#version_id').val();
            if (!versionId || !artifactId) {
                showAlert('Validation_Error', 'Missing version or image id.', 'error');
                return;
            }

            postWithLoader('<?= base_url('reports/daily/version') ?>/' + versionId + '/images/' + artifactId + '/delete', {})
                .done(function(response) {
                    if (!response || response.status !== 'success') {
                        showAlert(
                            (response && response.statusText) ? response.statusText : 'Daily Report',
                            (response && response.message) ? response.message : 'Unable to delete image.',
                            (response && response.status) ? response.status : 'error'
                        );
                        return;
                    }

                    var data = response.data && typeof response.data === 'object' ? response.data : {};
                    applyImageLimits(data.limits || {});
                    renderDailyImages(Array.isArray(data.images) ? data.images : []);
                    showAlert(response.statusText || 'Daily Report', response.message || 'Image deleted.', response.status || 'success');
                })
                .fail(function(jqXHR, textStatus, error) {
                    showAlert(jqXHR.status, 'Request failed: ' + textStatus + '<br>' + error, 'error');
                });
        }

        function replaceDailyImage(file) {
            if (!dailyReplaceArtifactId) {
                showAlert('Validation_Error', 'Missing image id for replacement.', 'error');
                return;
            }

            var validation = validateImageFiles([file], true);
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

            if (dailyReplaceTriggerButton && dailyReplaceTriggerButton.length) {
                setButtonBusy(dailyReplaceTriggerButton, true, 'Replacing...');
            }
            showPageLoader();
            $.ajax({
                url: '<?= base_url('reports/daily/version') ?>/' + versionId + '/images/' + dailyReplaceArtifactId + '/replace',
                type: 'post',
                data: formData,
                processData: false,
                contentType: false
            }).done(function(response) {
                if (!response || response.status !== 'success') {
                    showAlert(
                        (response && response.statusText) ? response.statusText : 'Daily Report',
                        (response && response.message) ? response.message : 'Unable to replace image.',
                        (response && response.status) ? response.status : 'error'
                    );
                    return;
                }

                var data = response.data && typeof response.data === 'object' ? response.data : {};
                applyImageLimits(data.limits || {});
                renderDailyImages(Array.isArray(data.images) ? data.images : []);
                showAlert(response.statusText || 'Daily Report', response.message || 'Image replaced.', response.status || 'success');
            }).fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, 'Request failed: ' + textStatus + '<br>' + error, 'error');
            }).always(function() {
                hidePageLoader();
                if (dailyReplaceTriggerButton && dailyReplaceTriggerButton.length) {
                    setButtonBusy(dailyReplaceTriggerButton, false);
                }
                dailyReplaceArtifactId = 0;
                dailyReplaceTriggerButton = null;
                $('#daily_image_replace_input').val('');
            });
        }

        function hasPulledDailyContent() {
            var tokenValues = extractTokenValues(currentSectionData);
            return tokenValues && typeof tokenValues === 'object' && Object.keys(tokenValues).length > 0;
        }

        renderDailySection(currentSectionData);
        fetchDailyImages(false);

        $('#btn_fetch_daily_content').on('click', function() {
            var versionId = $('#version_id').val();
            var button = $(this);
            if (!versionId) {
                showAlert('Validation_Error', 'Missing version id.', 'error');
                return;
            }

            setButtonBusy(button, true, 'Fetching...');
            postWithLoader('<?= base_url('reports/daily/version') ?>/' + versionId + '/pull-section', {
                section_key: 'daily_content'
            }).done(function(response) {
                if (!response || response.status !== 'success') {
                    showAlert(response.statusText || '', response.message || 'Unable to pull Daily Content.', response.status || 'error');
                    return;
                }

                currentSectionData = response.data && response.data.section_data ? response.data.section_data : {};
                currentPulledAt = response.data && response.data.pulled_at ? response.data.pulled_at : '';
                renderDailySection(currentSectionData);
                showAlert(response.statusText || 'Daily Report', response.message || 'Daily Content pulled.', response.status || 'success');
            }).fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, 'Request failed: ' + textStatus + '<br>' + error, 'error');
            }).always(function() {
                setButtonBusy(button, false);
            });
        });

        $('#daily_image_add_btn').on('click', function() {
            if (!canManageImages) {
                showAlert('Validation_Error', 'You do not have permission to upload images.', 'error');
                return;
            }
            if (dailyImagesStore.length >= dailyImageMaxCount) {
                showAlert('Validation_Error', 'Maximum allowed images is ' + dailyImageMaxCount + '.', 'error');
                return;
            }
            $('#daily_image_upload').trigger('click');
        });

        $('#daily_image_upload').on('change', function() {
            var files = this.files || [];
            if (!files.length) return;
            uploadDailyImages(files);
            $(this).val('');
        });

        $('#daily_images_list').on('click', '.daily-image-view', function() {
            var viewUrl = String($(this).data('view-url') || '').trim();
            if (viewUrl === '') {
                showAlert('Daily Report', 'Image preview is unavailable.', 'error');
                return;
            }

            var fileName = String($(this).data('file-name') || 'Daily Image');
            Swal.fire({
                titleText: fileName,
                imageUrl: viewUrl,
                imageAlt: fileName,
                showCloseButton: true,
                showConfirmButton: false,
                width: 900
            });
        });

        $('#daily_images_list').on('click', '.daily-image-delete', function() {
            if (!canManageImages) return;
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
                deleteDailyImage(artifactId);
            });
        });

        $('#daily_images_list').on('click', '.daily-image-replace', function() {
            if (!canManageImages) return;
            var artifactId = parseInt($(this).data('artifact-id') || '0', 10);
            if (!artifactId) return;
            dailyReplaceArtifactId = artifactId;
            dailyReplaceTriggerButton = $(this);
            $('#daily_image_replace_input').trigger('click');
        });

        $('#daily_image_replace_input').on('change', function() {
            var files = this.files || [];
            if (!files.length) {
                dailyReplaceArtifactId = 0;
                dailyReplaceTriggerButton = null;
                return;
            }
            replaceDailyImage(files[0]);
            $(this).val('');
        });

        $('#btn_save_draft').on('click', function() {
            var versionId = $('#version_id').val();
            var button = $(this);
            setButtonBusy(button, true, 'Saving...');
            postWithLoader('<?= base_url('reports/daily/version') ?>/' + versionId + '/save-draft', {
                manual_json: JSON.stringify(buildManualJson())
            }).done(function(response) {
                showAlert(response.statusText || '', response.message || 'Saved.', response.status || 'success');
            }).fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, 'Request failed: ' + textStatus + '<br>' + error, 'error');
            }).always(function() {
                setButtonBusy(button, false);
            });
        });

        $('#btn_finalize_draft').on('click', function() {
            var versionId = $('#version_id').val();
            var button = $(this);
            if (!hasPulledDailyContent()) {
                showAlert('Daily Report', 'Please pull Daily Content before generating PDF.', 'error');
                return;
            }

            Swal.fire({
                title: 'Generate PDF',
                text: 'This will lock the draft and generate the Daily Report PDF. Continue?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="ri-check-double-line align-bottom me-1"></i>Generate',
                cancelButtonText: '<i class="ri-close-line align-bottom me-1"></i>Cancel',
                customClass: {
                    confirmButton: 'btn btn-primary w-xs me-2 mt-2',
                    cancelButton: 'btn btn-secondary w-xs mt-2'
                },
                buttonsStyling: false
            }).then(function(result) {
                if (!result.isConfirmed) return;

                setButtonBusy(button, true, 'Generating PDF...');
                postWithLoader('<?= base_url('reports/daily/version') ?>/' + versionId + '/finalize', {})
                    .done(function(response) {
                        if (!response || response.status !== 'success') {
                            showAlert(response.statusText || '', response.message || 'Unable to finalize.', response.status || 'error');
                            return;
                        }

                        showAlert(response.statusText || '', response.message || 'Finalized.', response.status || 'success');
                        if (response.data && response.data.pdf_url) {
                            window.open(response.data.pdf_url, '_blank');
                        }
                        window.location.href = backToListUrl;
                    }).fail(function(jqXHR, textStatus, error) {
                        showAlert(jqXHR.status, 'Request failed: ' + textStatus + '<br>' + error, 'error');
                    }).always(function() {
                        setButtonBusy(button, false);
                    });
            });
        });
    });
</script>
<?= $this->endSection() ?>
