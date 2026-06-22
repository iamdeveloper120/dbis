<?= $this->extend("layout/master-profile") ?>
<?= $this->section("head_tag") ?>
<style>
    .file-manager-content-scroll {
        padding-bottom: 120px !important;
        min-height: calc(100vh - 150px);
        box-sizing: border-box;
    }

    #client_weekly_data_table {
        width: 100% !important;
    }

    #client_weekly_data_table thead th {
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
        font-weight: 600;
    }

    #client_weekly_data_table tbody td {
        vertical-align: middle;
    }
</style>
<?= $this->endSection() ?>
<?= $this->section("page_content") ?>
<div class="mx-n3 pt-2 px-2 file-manager-content-scroll">
    <div class="row g-3 align-items-center border-bottom pb-3 mb-3">
        <div class="col-md-4">
            <h5 class="card-title mb-0">Weekly Data Manual</h5>
        </div>
        <div class="col-md-8">
            <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
                <div class="input-group" style="width: 170px;">
                    <span class="input-group-text">
                        <i class="ri-calendar-line"></i>
                    </span>
                    <input id="start_date"
                        type="text"
                        class="form-control"
                        placeholder="Start Date"
                        data-provider="flatpickr"
                        data-date-format="d-M-Y"
                        data-maxDate="today">
                </div>

                <div class="input-group" style="width: 170px;">
                    <span class="input-group-text">
                        <i class="ri-calendar-line"></i>
                    </span>
                    <input id="end_date"
                        type="text"
                        class="form-control"
                        placeholder="End Date"
                        data-provider="flatpickr"
                        data-date-format="d-M-Y"
                        data-maxDate="today">
                </div>

                <button type="button"
                    id="clear_search"
                    class="btn btn-success bg-gradient waves-effect waves-light btn-label right">
                    <i class="ri-calendar-event-line label-icon align-middle fs-16 ms-2"></i>
                    Clear
                </button>

                <button type="button"
                    id="search"
                    class="btn btn-info bg-gradient waves-effect waves-light btn-label right">
                    <i class="ri-search-line label-icon align-middle fs-16 ms-2"></i>
                    Search
                </button>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table id="client_weekly_data_table" class="table table-bordered align-middle" style="width:100%"></table>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section("page_modal") ?>
<?= $this->endSection() ?>

<?= $this->section("page_js") ?>
<script>
    $(document).ready(function() {
        $(document).ajaxStart(function() {
            showPageLoader();
        });
        $(document).ajaxStop(function() {
            hidePageLoader();
        });

        let client_id = "<?= $client->id ?>";

        $("#start_date").flatpickr({
            dateFormat: dateFormat,
            maxDate: "today",
            weekNumbers: true,
        });

        $("#end_date").flatpickr({
            dateFormat: dateFormat,
            maxDate: "today",
            weekNumbers: true,
        });

        var csrfToken = "<?= csrf_hash() ?>";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        });

        table = $('#client_weekly_data_table').DataTable({
            response: false,
            data: [],
            lengthChange: false,
            ordering: false,
            lengthMenu: [
                [10, 25, 50, -1],
                ['10 rows', '25 rows', '50 rows', 'Show all']
            ],
            layout: {
                topStart: {
                    buttons: [{
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
                    ]
                },
                topEnd: {
                    search: {
                        placeholder: 'Search'
                    }
                }
            },
            columnDefs: [{
                    targets: "_all",
                    className: 'dt-nowrap align-middle text-center'
                },
                {
                    targets: [2],
                    render: function(data, type, row) {
                        if (type === 'sort' || type === 'type') {
                            return moment(data, 'YYYY-MM-DD').format('YYYYMMDD');
                        }
                        return moment(data, 'YYYY-MM-DD').format(momentDateFormat);
                    }
                },
                {
                    targets: [0, 1],
                    visible: false,
                },
                {
                    targets: [7],
                    className: 'align-middle text-start'
                },
            ],
            columns: [{
                    data: 'mrn',
                    title: 'MRN'
                },
                {
                    data: 'internal_mrn',
                    title: 'Internal MRN'
                },
                {
                    data: 'week_date',
                    title: 'Weekly Date'
                },
                {
                    data: 'hours',
                    title: 'Hours'
                },
                {
                    data: 'skills_retained',
                    title: 'Skills Retained'
                },
                {
                    data: 'doi',
                    title: 'DOI'
                },
                {
                    data: 'is_session',
                    title: 'Status'
                },
                {
                    data: 'supervisor_name',
                    title: 'Supervisor'
                }
            ]
        });

        $('#client_weekly_data_table thead th').addClass('text-center align-middle');

        function loadWeeklyData(start_date = null, end_date = null) {
            var ajaxRequest = $.ajax({
                url: '/sessions/weekly/list',
                type: 'post',
                data: {
                    "action": 'list',
                    "client_id": client_id,
                    "start_date": start_date,
                    "end_date": end_date
                }
            });

            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    table.clear();
                    table.rows.add(response.data);
                    table.draw();
                } else if (response.status == 'validation_error' || response.status == 'error') {
                    let errors = Object.values(response.message);
                    displayValidationErrors(errors);
                } else {
                    showAlert(response.statusText, response.message, response.status);
                }
            });

            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
        }

        $("#search").on('click', function(e) {
            e.preventDefault();

            let start_date = $("#start_date").val();
            let end_date = $("#end_date").val();

            if (start_date && end_date) {
                let start = new Date(start_date);
                let end = new Date(end_date);

                if (end < start) {
                    showAlert('', 'End date must be greater than Start date', 'warning');
                    return;
                }
            }

            loadWeeklyData(start_date, end_date);
        });

        $("#clear_search").on('click', function() {
            if ($("#start_date")[0]._flatpickr) {
                $("#start_date")[0]._flatpickr.clear();
            }
            if ($("#end_date")[0]._flatpickr) {
                $("#end_date")[0]._flatpickr.clear();
            }
            loadWeeklyData(null, null);
        });

        loadWeeklyData();
    });
</script>
<?= $this->endSection() ?>
