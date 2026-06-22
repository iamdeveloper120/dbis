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

    .versions-action-wrap .btn i {
        vertical-align: -1px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section("page_content") ?>
<div id="page_loader_overlay" class="page-loader-overlay">
    <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
</div>

<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h5 class="mb-sm-0">Progress Reports</h5>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active">Reporting</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body border-bottom-dashed border-bottom">
                <div class="row justify-content-end g-2">
                    <div class="col-lg-7 col-md-7 col-sm-12">
                        <select class="form-control" id="client_dropdown_list">
                            <option value="">SELECT CLIENT ID</option>
                            <?php foreach (($clients ?? []) as $client): ?>
                                <option value="<?= (int) $client->id ?>"><?= esc($client->internal_mrn . ' - ' . $client->name()) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-5 col-md-5 col-sm-12 align-self-end">
                        <div class="gap-2 float-end">
                            <button type="button" id="search" class="btn btn-info bg-gradient waves-effect waves-light btn-label right" title="Search by filters"><i class="ri-search-line label-icon align-middle fs-16 ms-2"></i>Search</button>
                            <button type="button" id="clear_search" class="btn btn-success btn-icon waves-effect waves-light" title="Clear Filters"><i class="ri-eraser-line"></i></button>
                            <div class="btn-group mt-4 mt-md-0" role="group" aria-label="Basic example">
                                <button type="button" class="btn btn-warning bg-gradient waves-effect waves-light" id="back" title="Previous client"><i class="ri-arrow-left-line"></i></button>&nbsp;
                                <button type="button" class="btn btn-warning bg-gradient waves-effect waves-light" id="next" title="Next client"><i class="ri-arrow-right-line"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body border-bottom-dashed border-bottom">
                <div class="table-responsive">
                    <table id="progress_report_datatable" class="table table-bordered align-middle nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th>Report ID</th>
                                <th>Period From</th>
                                <th>Period To</th>
                                <th>Version</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section("page_modal") ?>
<div class="modal fade" id="generate_report_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title">Generate Draft</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" for="generate_period_from">Report Period From *</label>
                    <input type="text" class="form-control" id="generate_period_from" placeholder="Select Start Date">
                </div>
                <div class="mb-0">
                    <label class="form-label" for="generate_period_to">Report Period To *</label>
                    <input type="text" class="form-control" id="generate_period_to" placeholder="Select End Date">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="ri-close-line align-bottom me-1"></i>Close</button>
                <button type="button" class="btn btn-primary" id="btn_confirm_generate"><i class="ri-file-add-line align-bottom me-1"></i>Continue</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="versions_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="versions_modal_title">Report Versions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="versions_table">
                        <thead>
                            <tr>
                                <th>Version</th>
                                <th>Status</th>
                                <th>Generated At</th>
                                <th>Generated By</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="ri-close-line align-bottom me-1"></i>Close</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section("page_js") ?>
<script>
    $(document).ready(function() {
        var canGenerateReport = <?= auth()->user()->can('reporting.progress.generate') ? 'true' : 'false' ?>;
        var canViewPdf = <?= auth()->user()->can('reporting.progress.view-pdf') ? 'true' : 'false' ?>;
        var canRegenerate = <?= auth()->user()->can('reporting.progress.regenerate') ? 'true' : 'false' ?>;
        var canDeleteVersion = <?= auth()->user()->can('reporting.progress.delete-version') ? 'true' : 'false' ?>;
        var canDeleteAll = <?= auth()->user()->can('reporting.progress.delete-all') ? 'true' : 'false' ?>;
        var table = null;
        var activeVersionsReportId = 0;
        var activeVersionsPeriodLabel = '';
        var csrfToken = "<?= csrf_hash() ?>";
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrfToken } });
        var stateTokenUrl = '<?= base_url('reports/progress/state-token') ?>';
        var initialListState = <?= json_encode($initial_state ?? ['client_id' => '', 'start_date' => '', 'end_date' => '', 'dt_page' => 0], JSON_UNESCAPED_UNICODE) ?>;

        var loaderCounter = 0;

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

        function toServerDate(value, selector) {
            if (selector) {
                var input = document.querySelector(selector);
                if (input && input._flatpickr && input._flatpickr.selectedDates && input._flatpickr.selectedDates.length > 0) {
                    return moment(input._flatpickr.selectedDates[0]).format('YYYY-MM-DD');
                }
            }

            if (!value) return '';
            var parsed = moment(value, [momentDateFormat, 'YYYY-MM-DD', 'DD-MM-YYYY', 'MM-DD-YYYY'], true);
            if (!parsed.isValid()) return '';
            return parsed.format('YYYY-MM-DD');
        }

        function formatDisplayDateFromYmd(ymd) {
            if (!ymd) return '';
            var parsed = moment(ymd, 'YYYY-MM-DD', true);
            if (!parsed.isValid()) return ymd;
            return parsed.format(momentDateFormat);
        }

        function collectCurrentListState() {
            var clientId = $('#client_dropdown_list').val() || '';
            var startDate = '';
            var endDate = '';
            var dtPage = table ? table.page() : 0;

            return {
                client_id: clientId,
                start_date: startDate || '',
                end_date: endDate || '',
                dt_page: Number.isFinite(dtPage) && dtPage > 0 ? dtPage : 0
            };
        }

        function setFlatpickrFromYmd(selector, ymd) {
            if (!ymd) return;
            var parsed = moment(ymd, 'YYYY-MM-DD', true);
            if (!parsed.isValid()) return;

            var input = document.querySelector(selector);
            if (input && input._flatpickr) {
                input._flatpickr.setDate(parsed.toDate(), true);
                return;
            }

            $(selector).val(parsed.format(momentDateFormat));
        }

        function buildStateTokenQuery(state) {
            return $.post(stateTokenUrl, state).then(function(response) {
                if (!response || response.status !== 'success') {
                    return '';
                }
                return (response.data && response.data.state_query) ? String(response.data.state_query) : '';
            }, function() {
                return '';
            });
        }
        function buildRawStateQuery(state) {
            var params = [];
            if (state.client_id) params.push('client_id=' + encodeURIComponent(state.client_id));
            if (state.start_date) params.push('start_date=' + encodeURIComponent(state.start_date));
            if (state.end_date) params.push('end_date=' + encodeURIComponent(state.end_date));
            if (Number.isFinite(state.dt_page) && state.dt_page > 0) {
                params.push('dt_page=' + encodeURIComponent(String(state.dt_page)));
            }
            return params.join('&');
        }

        function withCurrentListState(url) {
            var state = collectCurrentListState();
            return buildStateTokenQuery(state).then(function(stateQuery) {
                var tokenSuffix = stateQuery ? stateQuery.replace(/^\?/, '') : '';
                var rawSuffix = buildRawStateQuery(state);
                var suffix = tokenSuffix;
                if (rawSuffix) {
                    suffix = suffix ? (suffix + '&' + rawSuffix) : rawSuffix;
                }
                if (!suffix) return url;
                return url + (url.indexOf('?') === -1 ? '?' : '&') + suffix;
            }, function() {
                var rawSuffix = buildRawStateQuery(state);
                if (!rawSuffix) return url;
                return url + (url.indexOf('?') === -1 ? '?' : '&') + rawSuffix;
            });
        }

        function postWithLoader(url, payload) {
            showPageLoader();
            return $.post(url, payload).always(function() {
                hidePageLoader();
            });
        }

        function validateSelection() {
            var clientId = $('#client_dropdown_list').val();
            if (!clientId) {
                showAlert('', 'Select Client', 'error');
                return false;
            }
            return true;
        }

        function reloadTableWithLoader(resetPaging) {
            showPageLoader();
            table.ajax.reload(function() {
                hidePageLoader();
            }, resetPaging);
        }

        $('#client_dropdown_list').select2();
        flatpickr('#generate_period_from', {
            dateFormat: dateFormat,
            maxDate: 'today'
        });
        flatpickr('#generate_period_to', {
            dateFormat: dateFormat,
            maxDate: 'today'
        });

        var buttonsConfig = [
            {
                extend: 'pageLength',
                className: 'btn btn-light bg-gradient waves-effect waves-light'
            },
            {
                extend: 'copy',
                className: 'btn btn-light bg-gradient waves-effect waves-light'
            },
            {
                extend: 'excel',
                className: 'btn btn-light bg-gradient waves-effect waves-light'
            },
            {
                extend: 'colvis',
                className: 'btn btn-light bg-gradient waves-effect waves-light'
            }
        ];

        if (canGenerateReport) {
            buttonsConfig.unshift({
                text: '<i class="ri-file-add-line align-bottom me-1"></i>Generate Draft',
                className: 'btn btn-soft-info waves-effect waves-light material-shadow-none',
                attr: {
                    id: 'btn_generate_report'
                },
                action: function() {
                    openGenerateModal('', '');
                }
            });
        }

        table = $('#progress_report_datatable').DataTable({
            ajax: {
                url: '<?= base_url('reports/progress/data') ?>',
                type: 'POST',
                data: function(d) {
                    d.subject_id = $('#client_dropdown_list').val();
                    d.start_date = '';
                    d.end_date = '';
                },
                dataSrc: function(json) {
                    return (json && json.data) ? json.data : [];
                }
            },
            lengthChange: false,
            layout: {
                topStart: {
                    buttons: buttonsConfig
                },
                topEnd: {
                    search: {
                        placeholder: 'Search'
                    }
                }
            },
            order: [[0, 'desc']],
            columns: [
                { data: 'report_id' },
                {
                    data: 'period_from',
                    render: function(data, type, row) {
                        if (!data) return '';
                        if (type === 'sort' || type === 'type') {
                            return moment(data, 'YYYY-MM-DD').format('YYYYMMDD');
                        }
                        return row.period_from_display || data;
                    }
                },
                {
                    data: 'period_to',
                    render: function(data, type, row) {
                        if (!data) return '';
                        if (type === 'sort' || type === 'type') {
                            return moment(data, 'YYYY-MM-DD').format('YYYYMMDD');
                        }
                        return row.period_to_display || data;
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        return row.latest_version_id ? ('v' + row.latest_version_no) : '-';
                    }
                },
                {
                    data: 'latest_status',
                    render: function(data) {
                        if (data === 'FINAL') {
                            return '<span class="badge bg-success-subtle text-success text-uppercase">Final</span>';
                        }
                        return '<span class="badge bg-warning-subtle text-warning text-uppercase">Draft</span>';
                    }
                },
                {
                    data: 'created_at',
                    render: function(data, type, row) {
                        if (!data) return '';
                        if (type === 'sort' || type === 'type') {
                            return moment(data).format('YYYYMMDDHHmmss');
                        }
                        return row.created_at_display || data;
                    }
                },
                {
                    data: 'updated_at',
                    render: function(data, type, row) {
                        if (!data) return '';
                        if (type === 'sort' || type === 'type') {
                            return moment(data).format('YYYYMMDDHHmmss');
                        }
                        return row.updated_at_display || data;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    render: function(data, type, row) {
                        var menu = '<div class="dropdown d-inline-block float-end">';
                        menu += '<a class="btn btn-soft-secondary btn-sm" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="ri-settings-3-fill align-middle"></i></a>';
                        menu += '<ul class="dropdown-menu dropdown-menu-end">';

                        if (canGenerateReport) {
                            if (!row.report_id) {
                                menu += '<li><a class="dropdown-item generate-report" href="#" data-period-from="' + row.period_from + '" data-period-to="' + row.period_to + '"><i class="ri-file-add-line align-bottom me-2 text-muted"></i>Generate Draft</a></li>';
                            } else if (canRegenerate && row.latest_version_id && row.latest_status === 'FINAL') {
                                menu += '<li><a class="dropdown-item regenerate-version" href="#" data-version-id="' + row.latest_version_id + '"><i class="ri-refresh-line align-bottom me-2 text-muted"></i>Regenerate Draft</a></li>';
                            }
                        }

                        menu += '<li><a class="dropdown-item versions" href="#" data-report-id="' + row.report_id + '" data-period-label="' + (row.period_from_display || row.period_from) + ' to ' + (row.period_to_display || row.period_to) + '"><i class="ri-history-line align-bottom me-2 text-muted"></i>View Versions</a></li>';

                        if (row.latest_version_id && row.latest_status === 'DRAFT') {
                            menu += '<li><a class="dropdown-item open-draft-link" href="#" data-version-id="' + row.latest_version_id + '"><i class="ri-draft-line align-bottom me-2 text-muted"></i>Open Active Draft</a></li>';
                        }

                        if (row.latest_version_id && row.latest_status === 'FINAL' && canViewPdf) {
                                menu += '<li><a class="dropdown-item" target="_blank" href="<?= base_url('reports/progress/version') ?>/' + row.latest_version_id + '/pdf"><i class="ri-file-pdf-line align-bottom me-2 text-muted"></i>Download Latest PDF</a></li>';
                        }

                        if (canDeleteVersion && row.latest_version_id) {
                            menu += '<li><a class="dropdown-item delete-latest-version" href="#" data-version-id="' + row.latest_version_id + '" data-report-id="' + row.report_id + '" data-version-label="v' + row.latest_version_no + '" data-version-status="' + row.latest_status + '"><i class="ri-delete-bin-line align-bottom me-2 text-muted"></i>Delete Latest Version</a></li>';
                        }

                        if (canDeleteAll && row.report_id) {
                            menu += '<li><a class="dropdown-item text-danger delete-all-versions" href="#" data-report-id="' + row.report_id + '"><i class="ri-delete-bin-6-line align-bottom me-2 text-danger"></i>Delete All Versions</a></li>';
                        }
                        menu += '</ul></div>';
                        return menu;
                    }
                }
            ]
        });

        function openGenerateModal(periodFrom, periodTo) {
            if (!validateSelection()) return;

            $('#generate_period_from').val('');
            $('#generate_period_to').val('');

            if (periodFrom) {
                $('#generate_period_from').val(moment(periodFrom, 'YYYY-MM-DD').format(momentDateFormat));
            }
            if (periodTo) {
                $('#generate_period_to').val(moment(periodTo, 'YYYY-MM-DD').format(momentDateFormat));
            }

            $('#generate_report_modal').modal('show');
        }

        function runGenerate(clientId, periodFrom, periodTo) {
            postWithLoader('<?= base_url('reports/progress/generate') ?>', {
                subject_id: clientId,
                period_from: periodFrom,
                period_to: periodTo
            }).done(function(response) {
                if (response.status !== 'success') {
                    showAlert(response.statusText || '', response.message || 'Unable to generate draft.', response.status || 'error');
                    return;
                }

                $('#generate_report_modal').modal('hide');
                showAlert(response.statusText, response.message, response.status);
                reloadTableWithLoader(false);

                if (response.data && response.data.draft_url) {
                    withCurrentListState(response.data.draft_url).then(function(nextUrl) {
                        window.location.href = nextUrl;
                    });
                }
            }).fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, 'Request failed: ' + textStatus + '<br>' + error, 'error');
            });
        }

        function checkAndGenerate(clientId, periodFrom, periodTo) {
            postWithLoader('<?= base_url('reports/progress/check-generate') ?>', {
                subject_id: clientId,
                period_from: periodFrom,
                period_to: periodTo
            }).done(function(response) {
                if (response.status === 'error') {
                    if (response.statusText === 'ExactPeriodExists') {
                        showAlert(
                            response.statusText || '',
                            response.message || 'A report already exists for this exact period for this client.',
                            response.status || 'error'
                        );
                        return;
                    }
                    showAlert(response.statusText || '', response.message || 'Generate check failed.', response.status);
                    return;
                }

                var overlapExists = response.data && response.data.overlap_exists;
                if (overlapExists) {
                    var overlapFrom = response.data.overlap_period_from || '';
                    var overlapTo = response.data.overlap_period_to || '';
                    var overlapFromDisplay = formatDisplayDateFromYmd(overlapFrom);
                    var overlapToDisplay = formatDisplayDateFromYmd(overlapTo);
                    var overlapLabel = (overlapFromDisplay && overlapToDisplay) ? ('Existing overlap: ' + overlapFromDisplay + ' to ' + overlapToDisplay + '.') : '';
                    Swal.fire({
                        title: 'Period Overlap',
                        text: 'Selected period overlaps an existing period. Would you like to generate anyway? ' + overlapLabel,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: '<i class="ri-file-add-line align-bottom me-1"></i>Generate',
                        cancelButtonText: '<i class="ri-close-line align-bottom me-1"></i>Cancel',
                        customClass: {
                            confirmButton: 'btn btn-primary w-xs me-2 mt-2',
                            cancelButton: 'btn btn-secondary w-xs mt-2'
                        },
                        buttonsStyling: false
                    }).then(function(confirmResult) {
                        if (confirmResult.isConfirmed) {
                            runGenerate(clientId, periodFrom, periodTo);
                        }
                    });
                    return;
                }

                runGenerate(clientId, periodFrom, periodTo);
            }).fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, 'Request failed: ' + textStatus + '<br>' + error, 'error');
            });
        }

        function runRegenerate(versionId) {
            if (!versionId) return;
            postWithLoader('<?= base_url('reports/progress/version') ?>/' + versionId + '/regenerate', {})
                .done(function(response) {
                    if (response.status !== 'success') {
                        showAlert(response.statusText || '', response.message || 'Unable to regenerate draft.', response.status || 'error');
                        return;
                    }
                    showAlert(response.statusText || '', response.message || 'Regenerated.', response.status || 'success');
                    reloadTableWithLoader(false);
                    if (response.data && response.data.draft_url) {
                        withCurrentListState(response.data.draft_url).then(function(nextUrl) {
                            window.location.href = nextUrl;
                        });
                    }
                })
                .fail(function(jqXHR, textStatus, error) {
                    showAlert(jqXHR.status, 'Request failed: ' + textStatus + '<br>' + error, 'error');
                });
        }

        function runDeleteLatest(versionId, reportId) {
            if (!versionId) return;
            postWithLoader('<?= base_url('reports/progress/version') ?>/' + versionId + '/delete', {})
                .done(function(response) {
                    if (response.status !== 'success') {
                        showAlert(response.statusText || '', response.message || 'Unable to delete latest version.', response.status || 'error');
                        return;
                    }

                    showAlert(response.statusText || '', response.message || 'Deleted.', response.status || 'success');
                    reloadTableWithLoader(false);

                    var reportDeleted = response.data && response.data.report_deleted;
                    if (reportDeleted) {
                        activeVersionsReportId = 0;
                        activeVersionsPeriodLabel = '';
                        $('#versions_modal').modal('hide');
                        return;
                    }

                    if (activeVersionsReportId && reportId && activeVersionsReportId === reportId && $('#versions_modal').hasClass('show')) {
                        loadVersions(activeVersionsReportId, activeVersionsPeriodLabel);
                    }
                })
                .fail(function(jqXHR, textStatus, error) {
                    showAlert(jqXHR.status, 'Request failed: ' + textStatus + '<br>' + error, 'error');
                });
        }

        function runDeleteAll(reportId) {
            if (!reportId) return;
            postWithLoader('<?= base_url('reports/progress/report') ?>/' + reportId + '/delete-all', {})
                .done(function(response) {
                    if (response.status !== 'success') {
                        showAlert(response.statusText || '', response.message || 'Unable to delete all versions.', response.status || 'error');
                        return;
                    }

                    showAlert(response.statusText || '', response.message || 'Deleted.', response.status || 'success');
                    activeVersionsReportId = 0;
                    activeVersionsPeriodLabel = '';
                    $('#versions_modal').modal('hide');
                    reloadTableWithLoader(false);
                })
                .fail(function(jqXHR, textStatus, error) {
                    showAlert(jqXHR.status, 'Request failed: ' + textStatus + '<br>' + error, 'error');
                });
        }

        function loadVersions(reportId, periodLabel) {
            activeVersionsReportId = parseInt(reportId, 10) || 0;
            activeVersionsPeriodLabel = periodLabel || '';
            $('#versions_modal_title').text('Report Versions [' + activeVersionsPeriodLabel + ']');

            postWithLoader('<?= base_url('reports/progress/versions') ?>', { report_id: activeVersionsReportId })
                .done(function(response) {
                    if (response.status !== 'success') {
                        showAlert(response.statusText, response.message, response.status);
                        return;
                    }

                    var rows = response.data || [];
                    var html = '';
                    rows.forEach(function(row, idx) {
                        var isLatest = idx === 0;
                        var actionParts = [];
                        if (row.status === 'DRAFT') {
                            actionParts.push('<a class="btn btn-sm btn-soft-primary open-draft-link" href="#" data-version-id="' + row.version_id + '"><i class="ri-draft-line me-1"></i>Open Draft</a>');
                        } else if (row.status === 'FINAL') {
                            if (canViewPdf && row.artifact_id) {
                                actionParts.push('<a target="_blank" class="btn btn-sm btn-soft-danger" href="<?= base_url('reports/progress/version') ?>/' + row.version_id + '/pdf"><i class="ri-file-pdf-line me-1"></i>Download PDF</a>');
                            }
                        }
                        if (row.status === 'FINAL' && canRegenerate) {
                            actionParts.push('<button type="button" class="btn btn-sm btn-soft-info regenerate-version" data-version-id="' + row.version_id + '"><i class="ri-refresh-line me-1"></i>Regenerate</button>');
                        }
                        if (canDeleteVersion && isLatest) {
                            actionParts.push('<button type="button" class="btn btn-sm btn-soft-danger delete-latest-version" data-version-id="' + row.version_id + '" data-report-id="' + activeVersionsReportId + '" data-version-label="v' + row.version_no + '" data-version-status="' + row.status + '"><i class="ri-delete-bin-line me-1"></i>Delete</button>');
                        }

                        var actionHtml = actionParts.length > 0
                            ? '<div class="versions-action-wrap d-flex flex-wrap gap-1">' + actionParts.join('') + '</div>'
                            : '-';

                        html += '<tr>' +
                            '<td>v' + row.version_no + '</td>' +
                            '<td>' + row.status + '</td>' +
                            '<td>' + (row.generated_at_display || row.generated_at || '-') + '</td>' +
                            '<td>' + (row.generated_by_name || '-') + '</td>' +
                            '<td>' + actionHtml + '</td>' +
                            '</tr>';
                    });

                    if (html === '') {
                        html = '<tr><td colspan="5" class="text-center">No versions found.</td></tr>';
                    }

                    $('#versions_table tbody').html(html);
                    $('#versions_modal').modal('show');
                })
                .fail(function(jqXHR, textStatus, error) {
                    showAlert(jqXHR.status, 'Request failed: ' + textStatus + '<br>' + error, 'error');
                });
        }

        $('#progress_report_datatable').on('click', '.generate-report', function(e) {
            e.preventDefault();
            if (!canGenerateReport) return;
            if (!validateSelection()) return;

            var periodFrom = $(this).data('period-from') || '';
            var periodTo = $(this).data('period-to') || '';
            openGenerateModal(periodFrom, periodTo);
        });

        $('#btn_confirm_generate').on('click', function() {
            if (!canGenerateReport) return;
            if (!validateSelection()) return;

            var clientId = $('#client_dropdown_list').val();
            var periodFrom = toServerDate($('#generate_period_from').val(), '#generate_period_from');
            var periodTo = toServerDate($('#generate_period_to').val(), '#generate_period_to');

            if (!periodFrom || !periodTo) {
                showAlert('', 'Both period dates are required.', 'error');
                return;
            }

            if (periodFrom > periodTo) {
                showAlert('', 'Period From must be before or equal to Period To.', 'error');
                return;
            }

            checkAndGenerate(clientId, periodFrom, periodTo);
        });

        $('#progress_report_datatable').on('click', '.versions', function(e) {
            e.preventDefault();
            var reportId = $(this).data('report-id');
            var periodLabel = $(this).data('period-label') || '';
            loadVersions(reportId, periodLabel);
        });

        $('#progress_report_datatable, #versions_table').on('click', '.open-draft-link', function(e) {
            e.preventDefault();
            var versionId = parseInt($(this).data('version-id'), 10) || 0;
            if (!versionId) return;
            var draftUrl = '<?= base_url('reports/progress/version') ?>/' + versionId + '/draft';
            withCurrentListState(draftUrl).then(function(nextUrl) {
                window.location.href = nextUrl;
            });
        });


        $('#progress_report_datatable, #versions_table').on('click', '.regenerate-version', function(e) {
            e.preventDefault();
            if (!canRegenerate) return;
            var versionId = parseInt($(this).data('version-id'), 10) || 0;
            if (!versionId) return;

            Swal.fire({
                title: 'Regenerate Draft',
                text: 'This will create a new draft version using the same report period.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="ri-refresh-line align-bottom me-1"></i>Regenerate',
                cancelButtonText: '<i class="ri-close-line align-bottom me-1"></i>Cancel',
                customClass: {
                    confirmButton: 'btn btn-primary w-xs me-2 mt-2',
                    cancelButton: 'btn btn-secondary w-xs mt-2'
                },
                buttonsStyling: false
            }).then(function(result) {
                if (!result.isConfirmed) return;
                runRegenerate(versionId);
            });
        });

        $('#progress_report_datatable, #versions_table').on('click', '.delete-latest-version', function(e) {
            e.preventDefault();
            if (!canDeleteVersion) return;

            var versionId = parseInt($(this).data('version-id'), 10) || 0;
            var reportId = parseInt($(this).data('report-id'), 10) || 0;
            var versionLabel = $(this).data('version-label') || ('v' + versionId);
            var versionStatus = ($(this).data('version-status') || '').toString().toUpperCase();
            if (!versionId) return;

            Swal.fire({
                title: 'Delete Latest Version',
                text: 'Delete ' + versionLabel + ' (' + (versionStatus || 'DRAFT') + ')? You can only delete the latest version.',
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
                runDeleteLatest(versionId, reportId);
            });
        });

        $('#progress_report_datatable').on('click', '.delete-all-versions', function(e) {
            e.preventDefault();
            if (!canDeleteAll) return;

            var reportId = parseInt($(this).data('report-id'), 10) || 0;
            if (!reportId) return;

            Swal.fire({
                title: 'Delete All Versions',
                text: 'This will permanently delete all versions and related PDF media for this report.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="ri-delete-bin-6-line align-bottom me-1"></i>Delete All',
                cancelButtonText: '<i class="ri-close-line align-bottom me-1"></i>Cancel',
                customClass: {
                    confirmButton: 'btn btn-danger w-xs me-2 mt-2',
                    cancelButton: 'btn btn-secondary w-xs mt-2'
                },
                buttonsStyling: false
            }).then(function(result) {
                if (!result.isConfirmed) return;
                runDeleteAll(reportId);
            });
        });

        $('#search').on('click', function(e) {
            e.preventDefault();
            if (!validateSelection()) return;
            reloadTableWithLoader(true);
        });

        $('#clear_search').on('click', function() {
            $('#client_dropdown_list').val('').change();
            table.clear().draw();
        });

        $('#client_dropdown_list').on('change', function() {
            table.clear().draw();
        });

        $('#next').on('click', function() {
            var dropdown = $('#client_dropdown_list');
            var selectedIndex = dropdown.prop('selectedIndex');
            var optionsCount = dropdown.find('option').length;
            if (selectedIndex + 1 < optionsCount) {
                dropdown.prop('selectedIndex', selectedIndex + 1).trigger('change');
            }
            reloadTableWithLoader(true);
        });

        $('#back').on('click', function() {
            var dropdown = $('#client_dropdown_list');
            var selectedIndex = dropdown.prop('selectedIndex');
            if (selectedIndex > 1) {
                dropdown.prop('selectedIndex', selectedIndex - 1).trigger('change');
            }
            reloadTableWithLoader(true);
        });

        var restoredState = initialListState || {};
        if (restoredState.client_id) {
            $('#client_dropdown_list').val(restoredState.client_id).trigger('change');

            showPageLoader();
            table.ajax.reload(function() {
                if (restoredState.dt_page > 0) {
                    var pageInfo = table.page.info();
                    var maxPage = Math.max(0, pageInfo.pages - 1);
                    table.page(Math.min(restoredState.dt_page, maxPage)).draw('page');
                }
                hidePageLoader();
            }, false);
        }
    });
</script>
<?= $this->endSection() ?>

