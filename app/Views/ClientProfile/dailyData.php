<?= $this->extend("layout/master-profile") ?>
<?= $this->section("head_tag") ?>
<style>
    .file-manager-content-scroll {
        padding-bottom: 120px !important;
        min-height: calc(100vh - 150px);
        box-sizing: border-box;
    }

    #client_daily_data_computed {
        width: 100% !important;
    }

    #client_daily_data_computed thead th {
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
        font-weight: 600;
    }

    #client_daily_data_computed tbody td {
        vertical-align: middle;
    }

    #client_daily_data_computed tbody td.text-start {
        white-space: normal;
        word-break: break-word;
    }
</style>
<?= $this->endSection() ?>
<?= $this->section("page_content") ?>
<div class="mx-n3 pt-2 px-2 file-manager-content-scroll">
    <div class="row g-3 align-items-center border-bottom pb-3 mb-3">
        <div class="col-md-4">
            <h5 class="card-title mb-0">Daily Data (Live &amp; Manual)</h5>
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
        <table id="client_daily_data_computed" class="table table-bordered  align-middle" style="width:100%"> </table>
    </div>
    <div class="card">
        <div class="card-header border-bottom-dashed">
            <div class="row g-4 align-items-center">
                <div class="col-sm">
                    <div>
                        <h5 class="card-title mb-0">Daily Data Column Abbreviations</h5>
                    </div>
                </div>

            </div>
        </div>
        <div class="card-body border-bottom-dashed border-bottom">
            <ul class="list-group">
                <li class="list-group-item"><span class="text-info">Skills Retained</span> <i class="mdi mdi-chevron-right"></i> Skills Retained </li>
                <li class="list-group-item"><span class="text-info">DOI</span> <i class="mdi mdi-chevron-right"></i> Degrees of Independence </li>
                <li class="list-group-item"><span class="text-info"># Mands</span> <i class="mdi mdi-chevron-right"></i> Total Mands </li>
                <li class="list-group-item"><span class="text-info">Variety</span> <i class="mdi mdi-chevron-right"></i> Variety of Mands </li>
                <li class="list-group-item"><span class="text-info">F of PB</span> <i class="mdi mdi-chevron-right"></i> Frequency of problem behaviour</li>
                <li class="list-group-item"><span class="text-info">T D of PB</span> <i class="mdi mdi-chevron-right"></i> Total duration of problem behaviour</li>
                <li class="list-group-item"><span class="text-info">QR</span> <i class="mdi mdi-chevron-right"></i> Session quality rating</li>
                <li class="list-group-item"><span class="text-info">Prog ch</span> <i class="mdi mdi-chevron-right"></i> Program change made</li>

            </ul>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section("page_modal") ?>
<div class="modal fade" id="full_comment_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="nosession_wd_modal_title">Comment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                </button>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <div class="hstack gap-2 justify-content-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="ri-close-line align-bottom me-1"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>


<?= $this->section("page_js") ?>
<script src="/assets/libs/cleave.js/cleave.min.js"></script>
<script>
    $(document).ready(function() {
        // Page-level loader binding (only for this page)
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


        /**************************************************************************************** */
        if (document.querySelector("#total_duration_of_problem_behavior")) {
            var cleaveTime = new Cleave('#total_duration_of_problem_behavior', {
                time: true,
                timePattern: ['h', 'm', 's']
            });
        }
        if (document.querySelector("#total_duration_of_problem_behavior_u")) {
            var cleaveTime = new Cleave('#total_duration_of_problem_behavior_u', {
                time: true,
                timePattern: ['h', 'm', 's']
            });
        }

        /***************************************************************************************** */
        var csrfToken = "<?= csrf_hash() ?>";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        });
        /***************************************************************************************** */

        var dataSet = [];

        table = $('#client_daily_data_computed').DataTable({
            response: false,
            data: dataSet,
            lengthChange: false,
            ordering: false,
            lengthMenu: [
                [5, 10, 25, 50, -1],
                ['5 rows', '10 rows', '25 rows', '50 rows', 'Show all']
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
                            // Return the original date format as a sortable value
                            return moment(data, 'YYYY-MM-DD').format('YYYYMMDD');
                        }
                        // Return the formatted date for display
                        return moment(data, 'YYYY-MM-DD').format(momentDateFormat);
                    }
                },
                {
                    "targets": [14],
                    "render": function(data, type, row) {
                        if (data !== null) {
                            if (type === 'display' && data.length > 25) { // Change 50 to your desired limit
                                var escapedString = data.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                                return data.substr(0, 25) + '... <a href="#" class="readMore" data-full-comment="' + escapedString + '"><span class="badge bg-info-subtle text-info">Read more</span></a>';
                            }
                        }

                        return data;
                    }
                },
                {
                    targets: [12, 13, 14],
                    className: 'align-middle text-start'
                },
                {
                    targets: [0, 1, 15],
                    visible: false,
                },
                {
                    targets: [16], // Action column
                    render: function(data, type, row) {
                        if (row.data_source === 'manual') {
                            return '<span>Manual Data</span>';
                        } else {
                            return '<span>Live Data</span>';
                        }
                    }
                },
            ],
            columns: [{
                    data: 'mrn',
                    title: 'MRN'
                }, // MRN (hidden by default)
                {
                    data: 'internal_mrn',
                    title: 'Internal MRN'
                }, // Internal MRN (hidden by default)
                {
                    data: 'date',
                    title: 'Date'
                }, // Date
                {
                    data: 'hours',
                    title: 'Hours'
                }, // Hours
                {
                    data: 'skills_retained',
                    title: 'Skills Retained'
                }, // Skills Retained
                {
                    data: 'doi',
                    title: 'DOI'
                }, // DOI (Degrees of Independence)
                {
                    data: 'total_mands',
                    title: '# Mands'
                }, // Total Mands
                {
                    data: 'variety_of_mands',
                    title: 'Variety'
                }, // Variety of Mands
                {
                    data: 'frequency_of_problem_behavior',
                    title: 'F of PB'
                }, // Frequency of PB
                {
                    data: 'total_duration_of_problem_behavior',
                    title: 'T D of PB'
                }, // Total Duration of PB
                {
                    data: 'rating',
                    title: 'QR'
                }, // Session Quality Rating
                {
                    data: 'is_program_change',
                    title: 'Prog ch'
                }, // Program Change Made
                {
                    data: 'instructor_name',
                    title: 'Instructor'
                }, // Instructor Name
                {
                    data: 'supervisor_name',
                    title: 'Supervisor'
                }, // Supervisor Name
                {
                    data: 'comments',
                    title: 'Comments'
                }, // Comments
                {
                    data: 'is_session',
                    title: 'Status'
                }, // Status (hidden by default)
                {
                    data: null,
                    title: 'Live/Manual'
                } // Action column (Edit/Delete buttons or "Live Data")
            ]


        });
        $('#client_daily_data_computed thead th').addClass('text-center align-middle');

        $('#client_daily_data_computed').on('click', '.readMore', function(e) {
            e.preventDefault();
            const fullComment = $(this).data('full-comment'); // get the full comment from the data attribute
            $('#full_comment_modal .modal-body').html(fullComment);
            $('#full_comment_modal').modal('show');
        });
        /****************************************************************************************  */
        function loadDailyData(start_date = null, end_date = null) {
            var ajaxRequest = $.ajax({
                url: '/dailyData/computedData/list',
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
                } else if (response.status == 'validation_error') {
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

            loadDailyData(start_date, end_date);
        });

        $("#clear_search").on('click', function() {
            if ($("#start_date")[0]._flatpickr) {
                $("#start_date")[0]._flatpickr.clear();
            }
            if ($("#end_date")[0]._flatpickr) {
                $("#end_date")[0]._flatpickr.clear();
            }

            loadDailyData(null, null);
        });

        // auto-load on page load
        loadDailyData();


        /***************************************************************************************** */

    });
</script>
<?= $this->endSection() ?>
