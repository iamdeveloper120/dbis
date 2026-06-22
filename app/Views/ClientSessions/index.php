<?= $this->extend("layout/master") ?>
<?= $this->section("head_tag") ?>

<?= $this->endSection() ?>
<?= $this->section("page_content") ?>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h5 class="mb-sm-0">Clients Completed Sessions</h5>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active">Session List</li>
        </ol>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body border-bottom-dashed border-bottom">
                <div class="row justify-content-end">
                    <div class="col-lg-3 col-md-3 col-sm-12">
                        <select class="form-control " id="client_dropdown_list">
                            <option value="">SELECT CLIENT ID</option>
                            <?php foreach ($clients as $client) {  ?>
                               <option value="<?= $client->id ?>"><?= esc($client->internal_mrn . ' - ' . $client->name()) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-2 col-sm-12">
                        <select class="form-control" name="instructor_id" id="instructor_id">
                            <option value="">SELECT Instructor</option>

                            <?php foreach ($instructor_list as $instructor) {  ?>
                                <option value="<?php echo $instructor->id; ?>">
                                    <?php echo $instructor->first_name . ' ' . $instructor->last_name; ?></option>
                            <?php } ?>

                        </select>
                    </div>
                    <div class="col-lg-2 col-md-2 col-sm-12">
                        <select class="form-control" name="supervisor_id" id="supervisor_id">
                            <option value="">SELECT Supervisor</option>
                            <?php foreach ($supervisor_list as $supervisor) {  ?>
                                <option value="<?php echo $supervisor->id; ?>">
                                    <?php echo $supervisor->first_name . ' ' . $supervisor->last_name; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-2 col-sm-12">
                        <select class="form-control" name="session_status" id="session_status">
                            <option value="">All Status</option>
                            <option value="1">In Progress</option>
                            <option value="2">In Review</option>
                            <option value="3">Processed</option>
                            <option value="4">Partially Processed</option>

                        </select>
                    </div>

                    <!--<div class="col-lg-1 col-md-12 col-sm-12">
                        <input id="start_date" type="text" class="form-control" placeholder="Start Date">
                    </div>
                    <div class="col-lg-1 col-md-12 col-sm-12">
                        <input id="end_date" type="text" class="form-control" placeholder="End Date">
                    </div>-->
                    <div class="col-lg-3 col-md-2 col-sm-12 align-self-end">
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
                    <table id="client_executed_sessions_datatable" class="table table-bordered align-middle nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Instructor</th>
                                <th>Supervisor</th>
                                <th class="dt-nowrap">Date</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Teaching Duration</th>
                                <th>PB Duration</th>
                                <th>Mands Duration</th>
                                <th>Total Mands</th>
                                <th>Variety of Mands</th>
                                <th>Frequency/M</th>
                                <th>QR</th>
                                <th>Instructor Comments</th>
                                <th>Wow moments!</th>
                                <th>Status</th>
                                <th>Status Note</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody> </tbody>
                    </table>
                </div>
            </div>



        </div>
        <!--end col-->
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section("page_modal") ?>

<div class="modal fade" id="add_modal" tabindex="-1" aria-hidden="true">>
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="add_modal_title">Add Completed Session Manually</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> </button>
            </div>
            <form action="#" method="post" autocomplete="off">
                <div class="modal-body">
                    <div class="row">
                        <input type="text" name="client_id" id="client_id" hidden="hidden" value="">
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="session_date">Date *</label>
                            <input id="session_date" type="text" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="start_time">Start Time *</label>
                            <input type="text" name="start_time" id="start_time" class="form-control flatpickr-input active" data-provider="timepickr" data-enable-seconds="true" data-time-hrs="true" id="timepicker-24hrs" readonly="readonly">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="end_time">End Time *</label>
                            <input type="text" name="end_time" id="end_time" class="form-control flatpickr-input active" data-provider="timepickr" data-enable-seconds="true" data-time-hrs="true" id="timepicker-24hrs" readonly="readonly">

                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="instructor_id">Instructor*</label>
                            <select class="form-control" name="instructor_id" id="instructor_id">
                                <option value="">SELECT Therapist</option>

                                <?php foreach ($instructor_list as $instructor) {  ?>
                                    <option value="<?php echo $instructor->id; ?>">
                                        <?php echo $instructor->first_name . ' ' . $instructor->last_name; ?></option>
                                <?php } ?>

                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="supervisor_id">Supervisor*</label>
                            <select class="form-control" name="supervisor_id" id="supervisor_id">
                                <option value="">SELECT Supervisor</option>
                                <?php foreach ($supervisor_list as $supervisor) {  ?>
                                    <option value="<?php echo $supervisor->id; ?>">
                                        <?php echo $supervisor->first_name . ' ' . $supervisor->last_name; ?></option>
                                <?php } ?>
                            </select>
                        </div>


                    </div>



                </div>
                <div class="modal-footer">
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="ri-close-line align-bottom me-1"></i>Close</button>
                        <button type="button" class="btn btn-primary" id="btn_create"><i class="ri-save-line align-bottom me-1"></i>Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="update_modal" tabindex="-1" aria-hidden="true">>
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="update_modal_title">Update completed session</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> </button>
            </div>
            <form action="#" method="post" autocomplete="off">
                <div class="modal-body">
                    <div class="row">
                        <input type="text" name="u_session_id" id="u_session_id" hidden="hidden" value="">
                        <input type="text" name="u_client_id" id="u_client_id" hidden="hidden" value="">
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="u_session_date">Date *</label>
                            <input id="u_session_date" name="u_session_date" type="text" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="u_start_time">Start Time *</label>
                            <input type="text" name="u_start_time" id="u_start_time" class="form-control flatpickr-input active" data-provider="timepickr" data-enable-seconds="true" data-time-hrs="true" id="timepicker-24hrs" readonly="readonly">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="u_end_time">End Time *</label>
                            <input type="text" name="u_end_time" id="u_end_time" class="form-control flatpickr-input active" data-provider="timepickr" data-enable-seconds="true" data-time-hrs="true" id="timepicker-24hrs" readonly="readonly">

                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="u_instructor_id">Instructor*</label>
                            <select class="form-control" name="u_instructor_id" id="u_instructor_id">
                                <option value="">SELECT Therapist</option>

                                <?php foreach ($instructor_list as $instructor) {  ?>
                                    <option value="<?php echo $instructor->id; ?>">
                                        <?php echo $instructor->first_name . ' ' . $instructor->last_name; ?></option>
                                <?php } ?>

                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="u_supervisor_id">Supervisor*</label>
                            <select class="form-control" name="u_supervisor_id" id="u_supervisor_id">
                                <option value="">SELECT Supervisor</option>
                                <?php foreach ($supervisor_list as $supervisor) {  ?>
                                    <option value="<?php echo $supervisor->id; ?>">
                                        <?php echo $supervisor->first_name . ' ' . $supervisor->last_name; ?></option>
                                <?php } ?>
                            </select>
                        </div>


                    </div>



                </div>
                <div class="modal-footer">
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="ri-close-line align-bottom me-1"></i>Close</button>
                        <button type="button" class="btn btn-primary" id="btn_update"><i class="ri-save-line align-bottom me-1"></i>Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="end_session_modal" tabindex="-1" aria-hidden="true">>
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="end_session_modal_title">End Session Manually </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <input type="text" name="e_session_id" id="e_session_id" hidden="hidden" value="">
                    <input type="text" name="e_client_id" id="e_client_id" hidden="hidden" value="">
                    <div class="col-md-12  mb-3">
                        <label class="form-label" for="e_session_date">Date *</label>
                        <input id="e_session_date" name="e_session_date" type="text" class="form-control" disabled='disabled'>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="e_start_time">Start Time *</label>
                        <input disabled='disabled' type="text" name="e_start_time" id="e_start_time" class="form-control flatpickr-input active" data-provider="timepickr" data-enable-seconds="true" data-time-hrs="true" id="timepicker-24hrs" readonly="readonly">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="e_end_time">End Time *</label>
                        <input type="text" name="e_end_time" id="e_end_time" class="form-control flatpickr-input active" data-provider="timepickr" data-enable-seconds="true" data-time-hrs="true" id="timepicker-24hrs" readonly="readonly">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3 invisible">
                        <label class="form-label" for="e_instructor_id">Instructor*</label>
                        <select class="form-control" name="e_instructor_id" id="e_instructor_id">
                            <option value="">SELECT Therapist</option>

                            <?php foreach ($instructor_list as $instructor) {  ?>
                                <option value="<?php echo $instructor->id; ?>">
                                    <?php echo $instructor->first_name . ' ' . $instructor->last_name; ?></option>
                            <?php } ?>

                        </select>
                    </div>
                    <div class="col-md-6 mb-3 invisible">
                        <label class="form-label" for="e_supervisor_id">Supervisor*</label>
                        <select class="form-control" name="e_supervisor_id" id="e_supervisor_id">
                            <option value="">SELECT Supervisor</option>
                            <?php foreach ($supervisor_list as $supervisor) {  ?>
                                <option value="<?php echo $supervisor->id; ?>">
                                    <?php echo $supervisor->first_name . ' ' . $supervisor->last_name; ?></option>
                            <?php } ?>
                        </select>
                    </div>


                </div>

                <div class="modal-footer">
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="ri-close-line align-bottom me-1"></i>Close</button>
                        <button type="button" class="btn btn-primary" id="btn_end_session"><i class="ri-save-line align-bottom me-1"></i>Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="full_comment_modal" tabindex="-1" aria-hidden="true">>
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

        // Check the allowed past days from PHP
        var allowedPastDays = <?= json_encode(defined('SESSION_PROCESSING_RESOLUTION_DAYS') ? SESSION_PROCESSING_RESOLUTION_DAYS : 1); ?>;

        // Check if the user has the permission
        var hasPermission = '<?= auth()->user()->can('sessions.review.modification') ? "true" : "false"; ?>';

        // Determine the minDate based on the permission
        var minAllowedDate = hasPermission == 'true' ? null : new Date(new Date().setDate(new Date().getDate() - allowedPastDays));

        $("#session_date").flatpickr({
            dateFormat: "Y-m-d",
            weekNumbers: true,
            maxDate: "today", // No future date selection
            minDate: minAllowedDate, // Restrict past days based on permission
        });

        $("#u_session_date").flatpickr({
            dateFormat: "Y-m-d",
            weekNumbers: true,
            maxDate: "today", // No future date selection
            minDate: minAllowedDate, // Restrict past days based on permission
        });

        $("#e_session_date").flatpickr({
            dateFormat: "Y-m-d",
            weekNumbers: true,
            maxDate: "today", // No future date selection
            minDate: minAllowedDate, // Restrict past days based on permission
        });

        flatpickr('#start_time', {
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i:S',
            time_24hr: true
        });
        flatpickr('#end_time', {
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i:S',
            time_24hr: true
        });

        flatpickr('#u_start_time', {
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i:S',
            time_24hr: true
        });
        flatpickr('#u_end_time', {
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i:S',
            time_24hr: true
        });

        flatpickr('#e_start_time', {
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i:S',
            time_24hr: true
        });

        flatpickr('#e_end_time', {
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i:S',
            time_24hr: true
        });




        /***************************************************************************************** */
        var csrfToken = "<?= csrf_hash() ?>";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        });
        /***************************************************************************************** */
        $('#client_dropdown_list').select2();


        table = $('#client_executed_sessions_datatable').DataTable({
            ajax: {
                url: '/sessions/daily/list', // Change to the actual URL for fetching data
                type: 'POST',
                data: function(d) {
                    d.action = 'list';
                    d.client_id = $("#client_dropdown_list").val();
                    d.instructor_id = $("#instructor_id").val();
                    d.supervisor_id = $("#supervisor_id").val();
                    d.status = $("#session_status").val();
                    d.start_date = null;
                    d.end_date = null;

                }
            },
            lengthChange: false,
            ordering: true,
            lengthMenu: [
                [10, 25, 50, -1],
                ['10 rows', '25 rows', '50 rows', 'Show all']
            ],
            layout: {
                topStart: {
                    buttons: [{
                            text: '<i class="ri-add-line align-bottom me-1"></i>Add Completed Session Manually',
                            className: 'btn btn-soft-info waves-effect waves-light material-shadow-none',
                            attr: {
                                id: 'add_executed_session_manually'
                            },
                            action: function(e, dt, node, config) {
                                // Add your action for the button here
                                show_add_modal();
                            }
                        }, {
                            extend: 'pageLength',
                            className: 'btn btn-light bg-gradient waves-effect waves-light'
                        },
                        {
                            extend: 'copy',
                            className: 'btn btn-light bg-gradient waves-effect waves-light',
                            exportOptions: {
                                orthogonal: 'export'
                            }
                        }, {
                            extend: 'excel',
                            className: 'btn btn-light bg-gradient waves-effect waves-light',
                            exportOptions: {
                                orthogonal: 'export'
                            }
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
            order: [
                [3, 'desc']
            ],

            columns: [{
                    data: 'internal_mrn'
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        return row.instructor_first_name + ' ' + row.instructor_last_name


                    }
                },
                {
                    data: null,
                    visible: true, // Hide this column
                    render: function(data, type, row) {
                        return row.supervisor_first_name + ' ' + row.supervisor_last_name


                    }
                },
                {
                    data: 'session_date',
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
                    data: 'start_time'
                },
                {
                    data: 'end_time'
                },
                {
                    data: 'teaching_duration',
                    visible: false, // Hide this column
                },
                {
                    data: 'total_duration_of_problem_behavior',
                    visible: false, // Hide this column
                    render: function(data, type, row) {
                        return convertToDecimalHours(data);
                    }
                },
                {
                    data: 'total_duration_of_mands',
                    visible: false, // Hide this column
                    render: function(data, type, row) {
                        return data;
                    }
                },
                {
                    data: 'total_mands',
                    visible: false, // Hide this column
                    render: function(data, type, row) {
                        return data;
                    }
                },
                {
                    data: 'variety_of_mands',
                    visible: false, // Hide this column
                    render: function(data, type, row) {
                        return data;
                    }
                },
                {
                    data: 'frequency_of_mands_per_minute',
                    visible: false, // Hide this column
                    render: function(data, type, row) {
                        return data;
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        var session_rating = '';
                        if (row.session_rating == 1) session_rating = 'Poor';
                        if (row.session_rating == 2) session_rating = 'Good';
                        if (row.session_rating == 3) session_rating = 'Excellent';

                        return session_rating;
                    }
                },
                {
                    data: null,
                    visible: false, // Hide this column
                    render: function(data, type, row) {
                        const full = row.instructor_comments || '';

                        if (type === 'display' && full.length > 10) {
                            const escaped = full
                                .replace(/"/g, '&quot;')
                                .replace(/'/g, '&#39;');

                            return full.substr(0, 10) +
                                '... <a href="#" class="readMore" data-full-comment="' + escaped + '">' +
                                '<span class="badge bg-info-subtle text-info">Read more</span></a>';
                        }

                        // ✅ THIS is what Excel will receive
                        return full;
                    }

                },
                {
                    data: null,
                    visible: false, // Hide this column
                    render: function(data, type, row) {
                        const full = row.comments || '';

                        if (type === 'display' && full.length > 10) {
                            const escaped = full
                                .replace(/"/g, '&quot;')
                                .replace(/'/g, '&#39;');

                            return full.substr(0, 10) +
                                '... <a href="#" class="readMore" data-full-comment="' + escaped + '">' +
                                '<span class="badge bg-info-subtle text-info">Read more</span></a>';
                        }

                        // ✅ THIS is what Excel will receive
                        return full;
                    }
                },

                {
                    data: null,
                    render: function(data, type, row) {
                        var status = row.status;
                        if (row.status == 1) status = '<span class="badge border border-warning text-warning">In Progress</span>';
                        if (row.status == 2) status = '<span class="badge border border-info text-info">In Review</span>';
                        if (row.status == 3) status = '<span class="badge border border-success text-success">Processed</span>';
                        if (row.status == 4) status = '<span class="badge border border-info text-info">Partially Processed</span>';
                        return status;
                    }
                },
                {
                    data: 'note',
                    visible: false, // Hide this column
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        let actionButtons = `
            <div class="dropdown d-inline-block float-end">
                <a class="btn btn-soft-secondary btn-sm" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-settings-3-fill align-middle"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
        `;
                        if (row.status == 1)
                            actionButtons += `<li><a id="${row.id}" class="dropdown-item end-session"><i class="ri-stop-circle-line align-bottom me-2 text-muted"></i> End Session Manually</a></li>`;

                        if (['2'].includes(row.status) && row.flag == 1)
                            actionButtons += `<li><a id="${row.id}" class="dropdown-item update"><i class="ri-edit-line align-bottom me-2 text-muted"></i> Edit Session</a></li>`;

                        if (['2', '4'].includes(row.status))
                            actionButtons += `<li><a href="<?= base_url() ?>sessions/review/${row.id}" class="dropdown-item"><i class="ri-check-double-fill align-bottom me-2 text-muted"></i> Review & Process Data</a></li>`;

                        if (row.status == 3)
                            actionButtons += `<li><a href="<?= base_url() ?>sessions/review/${row.id}" class="dropdown-item"><i class="ri-eye-fill align-bottom me-2 text-muted"></i> View Processed Session</a></li>`;

                        if (['2'].includes(row.status))
                            actionButtons += `<li><a id="${row.id}" class="dropdown-item delete"><i class="ri-delete-bin-line align-bottom me-2 text-muted"></i> Delete Session</a></li>`;


                        actionButtons += `
                </ul>
            </div>
        `;

                        return actionButtons;
                    }
                }

            ],


        });

        function show_add_modal() {
            let client_id = $("#client_dropdown_list").val();

            if (client_id == '') {
                showAlert('', 'Select Client', 'error');

            } else {
                let client_detail = $("#client_dropdown_list :selected").html();
                $("#add_modal_title").html('Add Completed Session Manually  [' + client_detail + ']');
                $("#add_modal #client_id").val(client_id);
                $('#add_modal').modal('show');
            }

        };
        // Function to convert HH:MM:SS to decimal format (e.g., 4.23)
        function convertToDecimalHours(timeStr) {
            if (!timeStr) return ''; // Handle empty or null values

            const [hours, minutes, seconds] = timeStr.split(':').map(Number);
            const decimalHours = hours + (minutes / 60) + (seconds / 3600);

            return decimalHours.toFixed(2); // Format to 2 decimal places
        }
        // Populate table when search filters are applied
        $("#search").on('click', function(e) {
            e.preventDefault();
            table.ajax.reload();
        });

        $('#client_executed_sessions_datatable').on('click', '.readMore', function(e) {
            e.preventDefault();
            const fullComment = $(this).attr('data-full-comment'); // get the full comment from the data attribute
            $('#full_comment_modal .modal-body').html(fullComment);
            $('#full_comment_modal').modal('show');
        });

        /****************************************************************************************  */

        $("#client_dropdown_list").on('change', function(e) {
            table.clear();
            table.draw();
        });

        // Next button click event
        $('#next').on('click', function() {
            var dropdown = $('#client_dropdown_list');
            var selectedIndex = dropdown.prop('selectedIndex');
            var optionsCount = dropdown.find('option').length;
            if (selectedIndex + 1 < optionsCount) {
                dropdown.prop('selectedIndex', selectedIndex + 1).trigger('change');
            }
            table.ajax.reload();
        });

        // Previous button click event
        $('#back').on('click', function() {
            var dropdown = $('#client_dropdown_list');
            var selectedIndex = dropdown.prop('selectedIndex');
            if (selectedIndex > 1) {
                dropdown.prop('selectedIndex', selectedIndex - 1).trigger('change');
            }
            table.ajax.reload();
        });
        // Clear search filters
        $("#clear_search").on('click', function() {
            $('#client_dropdown_list').val('').change();
            $('#supervisor_id').val('').change();
            $('#instructor_id').val('').change();
            $('#session_status').val('').change();
            table.ajax.reload();
        });


        /**************************************************************************************** */
        $("#client_executed_sessions_datatable").on('click', '.end-session', function(e) {
            var btn = $(this);
            var session_id = $(this).attr('id');
            //let mrn = $("#client_dropdown_list :selected").html();

            current_row = $(this).parents('tr');
            selectedRowIndex = current_row.index();

            if (current_row.hasClass('child')) {
                current_row = current_row.prev();
                selectedRowIndex = current_row.index();
            }

            var ajaxRequest = $.ajax({
                url: '<?php echo base_url() ?>sessions/daily/single',
                type: 'post',
                data: {
                    "id": session_id
                },
                beforeSend: function(xhr) {
                    btn.prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    row_data = response.data[0];
                    if (row_data.status != 1) {
                        showAlert('', 'Session is already ended', 'info');
                    } else {
                        $('#end_session_modal_title').html('End session manually [' + row_data.client_first_name + ' ' + row_data.client_last_name + ' - ' + row_data.internal_mrn + ']');
                        $('#e_session_date').val(row_data.session_date);
                        $("#e_session_date").flatpickr({
                            defaultTime: row_data.session_date,
                            dateFormat: dateFormat,
                            weekNumbers: true,
                            maxDate: "today", // No future date selection
                            minDate: minAllowedDate, // Restrict past days based on permission
                        });
                        $("#e_start_time").flatpickr({
                            defaultTime: row_data.start_time,
                            enableTime: true,
                            noCalendar: true,
                            dateFormat: 'H:i:S',
                            time_24hr: true
                        });
                        $("#e_end_time").flatpickr({
                            defaultTime: row_data.end_time,
                            enableTime: true,
                            noCalendar: true,
                            dateFormat: 'H:i:S',
                            time_24hr: true
                        });
                        $('#end_session_modal #e_session_id').val(row_data.id);
                        $('#end_session_modal #e_start_time').val(row_data.start_time);
                        $('#end_session_modal #e_end_time').val(row_data.end_time);

                        $('#end_session_modal').modal('show');
                    }

                } else {
                    showAlert(response.statusText, response.message, response.status);
                }
            });
            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
            ajaxRequest.always(function() {
                btn.prop("disabled", false);
            });
        });
        $("#client_executed_sessions_datatable").on('click', '.update', function(e) {
            var btn = $(this);
            var session_id = $(this).attr('id');
            let mrn = $("#client_dropdown_list :selected").html();

            current_row = $(this).parents('tr');
            selectedRowIndex = current_row.index();

            if (current_row.hasClass('child')) {
                current_row = current_row.prev();
                selectedRowIndex = current_row.index();
            }

            var ajaxRequest = $.ajax({
                url: '<?php echo base_url() ?>sessions/daily/single',
                type: 'post',
                data: {
                    "id": session_id
                },
                beforeSend: function(xhr) {
                    btn.prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    row_data = response.data[0];
                    if (row_data.status != 2) {
                        showAlert('', 'session can be updated in review only', 'info');
                    } else {
                        $('#update_modal #update_modal_title').html('Update Completed session [' + row_data.client_first_name + ' ' + row_data.client_last_name + ' - ' + row_data.internal_mrn + ']');
                        $('#update_modal #u_session_date').val(row_data.session_date);
                        $("#u_session_date").flatpickr({
                            defaultTime: row_data.session_date,
                            dateFormat: dateFormat,
                            weekNumbers: true,
                            maxDate: "today", // No future date selection
                            minDate: minAllowedDate, // Restrict past days based on permission
                        });
                        $("#u_start_time").flatpickr({
                            defaultTime: row_data.start_time,
                            enableTime: true,
                            noCalendar: true,
                            dateFormat: 'H:i:S',
                            time_24hr: true
                        });
                        $("#u_end_time").flatpickr({
                            defaultTime: row_data.end_time,
                            enableTime: true,
                            noCalendar: true,
                            dateFormat: 'H:i:S',
                            time_24hr: true
                        });
                        $('#update_modal #u_session_id').val(row_data.id);
                        $('#update_modal #u_client_id').val(row_data.client_id);
                        $('#update_modal #u_start_time').val(row_data.start_time);
                        $('#update_modal #u_end_time').val(row_data.end_time);

                        $('#update_modal #u_instructor_id').val(row_data.instructor_id);
                        $('#update_modal #u_supervisor_id').val(row_data.supervisor_id);

                        $('#update_modal').modal('show');
                    }

                } else {
                    showAlert(response.statusText, response.message, response.status);
                }
            });
            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
            ajaxRequest.always(function() {
                btn.prop("disabled", false);
            });
        });
        $("#client_executed_sessions_datatable").on('click', '.delete', function(e) {
            e.preventDefault();

            var btn = $(this);
            var session_id = btn.attr('id');
            current_row = btn.parents('tr');
            selectedRowIndex = current_row.index();

            // SweetAlert2 Confirmation
            Swal.fire({
                title: "Are you sure?",
                text: "All data related to this session will be deleted and it cannot be reversed! This includes session data collection, duration, problem behaviors, mands, and mands duration.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: 'Delete',
                customClass: {
                    confirmButton: 'btn btn-danger w-xs me-2 mt-2',
                    cancelButton: 'btn btn-primary w-xs me-2 mt-2',
                },

                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send AJAX Request to Delete Session
                    $.ajax({
                        url: '<?php echo base_url() ?>sessions/daily/delete',
                        type: 'POST',
                        data: {
                            id: session_id
                        },
                        beforeSend: function() {
                            btn.prop("disabled", true);
                        },
                        success: function(response) {
                            if (response.success) {
                                // Remove Row from DataTable
                                $('#client_executed_sessions_datatable').DataTable().row(current_row).remove().draw();

                                // Show Success Message
                                showAlert("Deleted!", response.message, "success");
                            } else {
                                // Show Error Message
                                showAlert(response.statusText, response.message, "error");
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            showAlert("Error!", "Request failed: " + textStatus + " - " + errorThrown, "error");
                        },
                        complete: function() {
                            btn.prop("disabled", false);
                        }
                    });
                }
            });
        });
        /***************************************************************************************** */
        $('#btn_create').on('click', function() {
            var btn = $(this);
            var data = {
                'id': null,
                'session_date': $('#add_modal #session_date').val(),
                'client_id': $('#add_modal #client_id').val(),
                'instructor_id': $('#add_modal #instructor_id').val(),
                'supervisor_id': $('#add_modal #supervisor_id').val(),
                'start_time': $('#add_modal #start_time').val(),
                'end_time': $('#add_modal #end_time').val(),
            };
            var ajaxRequest = $.ajax({
                url: '<?php echo base_url() ?>sessions/daily/create',
                type: 'post',
                data: data,
                beforeSend: function(xhr) {
                    btn.prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    // Step 1: Add the new row
                    console.log(response.data);
                    table.row.add(response.data).draw();
                    console.log(1);


                    // Step 2: Get all table data in the applied order
                    let allData = table.rows({
                        order: 'current'
                    }).data().toArray(); // Ensure the data is in the sorted order
                    console.log("All data after sorting:", allData);

                    // Find the index of the new row using the unique row.id
                    var newRowIndex = allData.findIndex(row => row.id === response.data.id);
                    console.log(newRowIndex);
                    // Step 4: Get the page info after sorting
                    var pageInfo = table.page.info();

                    // Find the page number where the new row is located
                    var newRowPage = Math.floor(newRowIndex / pageInfo.length);

                    // Move to the page where the new row is located
                    table.page(newRowPage).draw(false);

                    // Get the row node and add the custom class
                    // Step 6: Apply CSS to the new row
                    let rowNode = table.row(':eq(' + newRowIndex + ')', {
                        order: 'current'
                    }).node();
                    if (rowNode) {
                        console.log("Applying CSS to row:", rowNode);
                        $(rowNode).addClass('dt-new-row');
                    } else {
                        console.error("Failed to retrieve the new row node for CSS application.");
                    }

                    $('#add_modal').modal('hide');
                    showAlert(response.statusText, response.message, response.status);
                } else if (response.status == 'error' && response.statusText == 'Validation_Error') {
                    let errors = Object.values(response.validationErrors);
                    displayValidationErrors(errors);

                } else {
                    showAlert(response.statusText, response.message, response.status);
                }
            });
            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
            ajaxRequest.always(function() {
                btn.prop("disabled", false);
            });

        });
        $('#btn_update').on('click', function() {
            var btn = $(this);
            var data = {
                'id': $('#update_modal #u_session_id').val(),
                'client_id': $('#update_modal #u_client_id').val(),
                'session_date': $('#update_modal #u_session_date').val(),
                'instructor_id': $('#update_modal #u_instructor_id').val(),
                'supervisor_id': $('#update_modal #u_supervisor_id').val(),
                'start_time': $('#update_modal #u_start_time').val(),
                'end_time': $('#update_modal #u_end_time').val(),
            };
            var ajaxRequest = $.ajax({
                url: '<?php echo base_url() ?>sessions/daily/update',
                type: 'post',
                data: data,
                beforeSend: function(xhr) {
                    btn.prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    updateTableRow(response.data);
                    $('#update_modal').modal('hide');
                    showAlert(response.statusText, response.message, response.status);
                } else if (response.status == 'error' && response.statusText == 'Validation_Error') {
                    let errors = Object.values(response.validationErrors);
                    displayValidationErrors(errors);

                } else {
                    showAlert(response.statusText, response.message, response.status);
                }
            });
            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
            ajaxRequest.always(function() {
                btn.prop("disabled", false);
            });

        });
        $('#btn_end_session').on('click', function() {
            var btn = $(this);
            var data = {
                'id': $('#end_session_modal #e_session_id').val(),
                'start_time': $('#end_session_modal #e_start_time').val(),
                'end_time': $('#end_session_modal #e_end_time').val(),
            };
            var ajaxRequest = $.ajax({
                url: '<?php echo base_url() ?>sessions/daily/end-session-manually',
                type: 'post',
                data: data,
                beforeSend: function(xhr) {
                    btn.prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    updateTableRow(response.data);
                    $('#update_modal').modal('hide');
                    showAlert(response.statusText, response.message, response.status);
                } else if (response.status == 'error' && response.statusText == 'Validation_Error') {
                    let errors = Object.values(response.validationErrors);
                    displayValidationErrors(errors);

                } else {
                    showAlert(response.statusText, response.message, response.status);
                }
            });
            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
            ajaxRequest.always(function() {
                btn.prop("disabled", false);
            });

        });
        /***************************************************************************************** */
        $('#add_modal').on('hidden.bs.modal', function(e) {
            $('#add_modal #add_modal_title').val('');
            $('#add_modal #session_date').val('');
            $('#add_modal #start_time').val('');
            $('#add_modal #end_time').val('');
            $('#add_modal #instructor_id').val('').trigger('change');
            $('#add_modal #supervisor').val('').trigger('change');
        });
        $('#update_modal').on('hidden.bs.modal', function(e) {
            $('#update_modal #add_modal_title').val('');
            $('#update_modal #u_session_id').val('');
            $('#update_modal #u_session_date').val('');
            $('#update_modal #u_start_time').val('');
            $('#update_modal #u_end_time').val('');
            $('#update_modal #u_instructor_id').val('').trigger('change');
            $('#update_modal #u_supervisor').val('').trigger('change');
        });
        $('#end_session_modal').on('hidden.bs.modal', function(e) {
            $('#end_session_modal #end_session_modal_title').val('');
            $('#end_session_modal #e_session_date').val('');
            $('#end_session_modal #e_start_time').val('');
            $('#end_session_modal #e_end_time').val('');
        });
        /***************************************************************************************** */

    });
</script>
<?= $this->endSection() ?>