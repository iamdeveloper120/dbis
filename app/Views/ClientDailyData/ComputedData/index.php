<?= $this->extend("layout/master") ?>
<?= $this->section("head_tag") ?>

<?= $this->endSection() ?>
<?= $this->section("page_content") ?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
            <h5 class="mb-sm-0">Client Daily Data (Live & Manual)</h5>


        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card">
                <div class="card-header border-bottom-dashed">

                    <div class="row g-4 align-items-center">
                        <div class="col-sm">
                            Daily Data
                        </div>
                        <div class="col-sm-auto">
                            <div class="d-flex flex-wrap align-items-start gap-2">
                                <button type="button" class="btn btn-outline-primary" id="add_daily_data"><i class="ri-add-line align-bottom me-1"></i>Add Manually Daily Session Data</button>
                                <button type="button" class="btn btn-outline-primary" id="add_no_session"><i class="ri-add-line align-bottom me-1"></i> Add No Session</button>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body border-bottom-dashed border-bottom">
                    <div class="row justify-content-end">
                        <div class="col-lg-4 col-md-12 col-sm-12">

                            <select class="form-control " id="client_dropdown_list">
                                <option value="">SELECT CLIENT ID</option>
                                <?php foreach ($clients as $client) {  ?>
                                    <option value="<?= $client->id ?>"><?= esc($client->internal_mrn . ' - ' . $client->name()) ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-12 col-sm-12">

                            <input id="start_date" type="text" placeholder="Start Date" class="form-control">
                        </div>
                        <div class="col-lg-2 col-md-12 col-sm-12">

                            <input id="end_date" type="text" placeholder="End Date" class="form-control">
                        </div>
                        <div class="col-lg-4 col-md-12 col-sm-12 align-self-end">
                            <div class="gap-2 float-end">
                                <button type="button" id="clear_search" class="btn btn-success bg-gradient waves-effect waves-light btn-label right" title="Clear dates"><i class="ri-eraser-line label-icon align-middle fs-16 ms-2"></i>Clear</button>

                                <button type="button" id="search" class="btn btn-info bg-gradient waves-effect waves-light btn-label right " title="Search"><i class="ri-search-line label-icon align-middle fs-16 ms-2"></i>Search</button>

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
                        <table id="client_daily_data_computed" class="table table-bordered  align-middle" style="width:100%"> </table>
                    </div>
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
            <!--end col-->
        </div>
    </div>
    <?= $this->endSection() ?>

    <?= $this->section("page_modal") ?>
    <div class="modal fade" id="add_wd_modal" tabindex="-1" aria-hidden="true">>
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="add_wd_modal_title">Add Daily Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> </button>
                </div>
                <form action="#" method="post" autocomplete="off">
                    <div class="modal-body">
                        <div class="row">
                            <input type="text" name="client_id" id="client_id" hidden="hidden" value="">
                            <div class="col-md-12 mb-3">
                                <label class="form-label" for="week_date">Date *</label>
                                <input id="week_date" type="text" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="hours">Number of hours *</label>
                                <input type="number" class="form-control " name="hours" id="hours">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="skills_retained">Skills Retained *</label>
                                <input type="number" class="form-control " name="skills_retained" id="skills_retained">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="doi">Degrees of Independence</label>
                                <input type="number" class="form-control " name="doi" id="doi">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="total_mands">Total Mands</label>
                                <input type="number" class="form-control " name="total_mands" id="total_mands">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="variety_of_mands">Variety of Mands</label>
                                <input type="number" class="form-control " name="variety_of_mands" id="variety_of_mands">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="frequency_of_problem_behavior">Frequency of Problem Behavior</label>
                                <input type="number" class="form-control " name="frequency_of_problem_behavior" id="frequency_of_problem_behavior">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="total_duration_of_problem_behavior">Total Duration of Problem Behavior</label>
                                <input type="text" class="form-control time_duration" name="total_duration_of_problem_behavior" id="total_duration_of_problem_behavior" placeholder="hh:mm:ss">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="session_quality_rating">Session Quality Rating (1-3)</label>
                                <select class="form-control" name="session_quality_rating" id="session_quality_rating">
                                    <option value="">SELECT</option>
                                    <option value="1">Poor</option>
                                    <option value="2">Good</option>
                                    <option value="3">Excellent</option>
                                </select>
                            </div>

                        </div>
                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="instructor_id">Instructor*</label>
                                <select class="form-control" name="instructor_id" id="instructor_id">
                                    <option value="">SELECT Instructor</option>

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

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label" for="comments">Comments</label>
                                <textarea class="form-control" id="comments" name="comments" rows="2" cols="100"></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="form-check form-check-right mb-2">
                                    <input class="form-check-input" type="checkbox" name="program_change_made" id="program_change_made">
                                    <label class="form-check-label" for="program_change_made">
                                        Program change made
                                    </label>
                                </div>
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

    <div class="modal fade" id="update_wd_modal" tabindex="-1" aria-hidden="true">>
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="update_wd_modal_title">Update Daily Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">

                    <div class="row">
                        <input type="text" name="session_id" id="session_id" hidden="hidden" value="">
                        <input type="text" name="client_id" id="client_id" hidden="hidden" value="">
                        <input type="text" name="status" id="status" hidden="hidden" value="">
                        <div class="col-md-12  mb-3">
                            <label class="form-label" for="u_week_date">Date *</label>
                            <input id="u_week_date" type="text" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="hours">Number of hours *</label>
                            <input type="number" class="form-control " name="hours" id="hours">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="skills_retained">Skills Retained *</label>
                            <input type="number" class="form-control " name="skills_retained" id="skills_retained">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="doi">Degrees of Independence</label>
                            <input type="number" class="form-control " name="doi" id="doi">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="total_mands">Total Mands </label>
                            <input type="number" class="form-control " name="total_mands" id="total_mands">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="variety_of_mands">Variety of Mands</label>
                            <input type="number" class="form-control " name="variety_of_mands" id="variety_of_mands">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="frequency_of_problem_behavior">Frequency of Problem Behavior</label>
                            <input type="number" class="form-control " name="frequency_of_problem_behavior" id="frequency_of_problem_behavior">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="total_duration_of_problem_behavior_u">Total Duration of Problem Behavior</label>
                            <input type="text" class="form-control time_duration" name="total_duration_of_problem_behavior_u" id="total_duration_of_problem_behavior_u" placeholder="hh:mm:ss">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="session_quality_rating">Session Quality Rating (1-3)</label>
                            <select class="form-control" name="session_quality_rating" id="session_quality_rating">
                                <option value="">SELECT</option>
                                <option value="1">Poor</option>
                                <option value="2">Good</option>
                                <option value="3">Excellent</option>
                            </select>
                        </div>





                    </div>
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="instructor_id">Instructor*</label>
                            <select class="form-control" name="instructor_id" id="instructor_id">
                                <option value="">SELECT Instructor</option>

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
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="comments">Comments</label>
                            <textarea class="form-control" id="comments" name="comments" rows="2" cols="100"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="form-check form-check-right mb-2">
                                <input class="form-check-input" type="checkbox" name="program_change_made_u" id="program_change_made_u">
                                <label class="form-check-label" for="program_change_made_u">
                                    Program change made
                                </label>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="ri-close-line align-bottom me-1"></i>Close</button>
                        <button type="button" class="btn btn-primary" id="btn_update"><i class="ri-save-line align-bottom me-1"></i>Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="nosession_wd_modal" tabindex="-1" aria-hidden="true">>
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="nosession_wd_modal_title">No Session Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">

                    <div class="row">
                        <input type="text" name="client_id" id="client_id" hidden="hidden" value="">
                        <div class="col-md-12 ">
                            <label class="form-label" for="week_date">Date *</label>
                            <input id="nosession_week_date" type="text" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="ri-close-line align-bottom me-1"></i>Close</button>
                        <button type="button" class="btn btn-primary" id="btn_add_nosession"><i class="ri-save-line align-bottom me-1"></i>Save</button>
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
            $("#week_date").flatpickr({
                dateFormat: dateFormat,
                maxDate: "today",
                weekNumbers: true,
            });
            $("#u_week_date").flatpickr({
                dateFormat: dateFormat,
                maxDate: "today",
                weekNumbers: true,
            });
            $("#nosession_week_date").flatpickr({
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
            $('#client_dropdown_list').select2();
            var dataSet = [];

            table = $('#client_daily_data_computed').DataTable({
                response: false,
                data: dataSet,
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
                        targets: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 16],
                        className: 'dt-nowrap'
                    }, {
                        targets: [0, 1, 15],
                        visible: false,
                    },
                    {
                        targets: [16], // Action column
                        render: function(data, type, row) {
                            if (row.data_source === 'manual') {
                                return '<div class="btn-group" role="group" aria-label="Small button">' +
                                    '<button no_session="' + row.status + '" id="' + row.id + '" mrn="' + row.mrn + '" type="button" class="btn btn-outline-warning  btn-icon waves-effect waves-light update-session btn-sm"><i class="ri-edit-line"></i></button>&nbsp;' +
                                    '<button id="' + row.id + '" mrn="' + row.mrn + '" type="button" class="btn btn-outline-danger btn-icon waves-effect waves-light delete-session btn-sm"><i class="ri-delete-bin-line"></i></button>' +
                                    '</div>'
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
                        title: 'Action'
                    } // Action column (Edit/Delete buttons or "Live Data")
                ]


            });

            $('#client_daily_data_computed').on('click', '.readMore', function(e) {
                e.preventDefault();
                const fullComment = $(this).data('full-comment'); // get the full comment from the data attribute
                $('#full_comment_modal .modal-body').html(fullComment);
                $('#full_comment_modal').modal('show');
            });
            /****************************************************************************************  */

            $("#client_dropdown_list").on('change', function(e) {
                table.clear();
                table.draw();
            });

            $('#next').on('click', function() {
                var dropdown = $('#client_dropdown_list');
                var selectedOption = dropdown.val();
                var optionsCount = dropdown.find('option').length;
                if (optionsCount > 0) {
                    var currentIndex = dropdown.find('option').index(dropdown.find('option:selected'));
                    var nextIndex = currentIndex + 1;

                    // Wrap around to the first option if the last option is selected
                    if (nextIndex >= optionsCount) {
                        nextIndex = 1;
                    }

                    // Set the next option as selected
                    dropdown.prop('selectedIndex', nextIndex).trigger('change');
                    $('#search').click();
                } else {
                    showAlert('', 'Client not exist', 'info');
                }

            });
            $('#back').on('click', function() {
                var dropdown = $('#client_dropdown_list');
                var selectedOption = dropdown.val();
                var optionsCount = dropdown.find('option').length;
                if (optionsCount > 0) {
                    var currentIndex = dropdown.find('option').index(dropdown.find('option:selected'));
                    var nextIndex = currentIndex - 1;

                    // Wrap around to the first option if the last option is selected
                    if (nextIndex <= 0) {
                        nextIndex = optionsCount - 1;
                    }

                    // Set the next option as selected
                    dropdown.prop('selectedIndex', nextIndex).trigger('change');
                    $('#search').click();
                } else {
                    showAlert('', 'Client not exist', 'info');
                }

            });
            $("#clear_search").click(function() {
                $('#start_date').flatpickr(dateConfig).clear();
                $('#end_date').flatpickr(dateConfig).clear();
                $('#search').click();
            });
            $("#search").on('click', function(e) {
                e.preventDefault;
                search = $(this);
                let client_id = $("#client_dropdown_list").val();
                let start_date = $("#start_date").val();
                let end_date = $("#end_date").val();

                var ajaxRequest = $.ajax({
                    url: '/dailyData/computedData/list',
                    type: 'post',
                    data: {
                        "action": 'list',
                        "client_id": client_id,
                        "start_date": start_date,
                        "end_date": end_date
                    },
                    beforeSend: function(xhr) {
                        $('#client_dropdown_list').prop("disabled", true);
                        search.prop("disabled", true);
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
                ajaxRequest.always(function() {
                    $('#client_dropdown_list').prop("disabled", false);
                    search.prop("disabled", false);
                });

            }); //On change function ends


            /***************************************************************************************** */

            $('#add_daily_data').on('click', function(e) {
                e.preventDefault;
                let client_id = $("#client_dropdown_list").val();

                if (client_id == '') {
                    showAlert('', 'Select Client', 'error');

                } else {
                    let client_detail = $("#client_dropdown_list :selected").html();
                    $("#add_wd_modal_title").html('Add Daily data [' + client_detail + ']');
                    $("#add_wd_modal #client_id").val(client_id);
                    $('#add_wd_modal').modal('show');
                }

            });

            /***************************************************************************************** */
            $('#btn_create').on('click', function() {
                var btn = $(this);
                var program_change_made = 0;
                if ($('#add_wd_modal #program_change_made').is(":checked")) {
                    program_change_made = 1;
                }
                var data = {
                    'week_date': $('#add_wd_modal #week_date').val(),
                    'client_id': $('#add_wd_modal #client_id').val(),
                    'instructor_id': $('#add_wd_modal #instructor_id').val(),
                    'supervisor_id': $('#add_wd_modal #supervisor_id').val(),
                    'hours': $('#add_wd_modal #hours').val(),
                    'skills_retained': $('#add_wd_modal #skills_retained').val(),
                    'doi': $('#add_wd_modal #doi').val(),
                    'total_mands': $('#add_wd_modal #total_mands').val(),
                    'variety_of_mands': $('#add_wd_modal #variety_of_mands').val(),
                    'frequency_of_problem_behavior': $('#add_wd_modal #frequency_of_problem_behavior').val(),
                    'total_duration_of_problem_behavior': $('#add_wd_modal #total_duration_of_problem_behavior').val(),
                    'session_quality_rating': $('#add_wd_modal #session_quality_rating').val(),
                    'program_change_made': program_change_made,
                    'comments': $('#add_wd_modal #comments').val(),

                };

                var ajaxRequest = $.ajax({
                    url: '<?php echo base_url() ?>sessions/manual/new',
                    type: 'post',
                    data: data,
                    beforeSend: function(xhr) {
                        btn.prop("disabled", true);
                    }
                });
                ajaxRequest.done(function(response) {
                    if (response.status == 'success') {

                        // Get current table data
                        var currentData = table.data().toArray();

                        // Add the new row data to the beginning of the array
                        currentData.unshift(response.data);

                        // Clear the table and re-add the data with the new row at the top
                        table.clear().rows.add(currentData).draw(false);

                        // Always switch back to the first page to show the new row
                        table.page('first').draw(false);

                        // Get the newly added row (which is now the first row)
                        var newRow = table.row(0).node();

                        // Apply CSS to the new row to highlight it in green
                        $(newRow).css({
                            'background-color': '#d4f8d4', // Green color for new row
                            'color': '#000'
                        });


                        $('#add_wd_modal').modal('hide');
                        showAlert(response.statusText, response.message, response.status);

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
            $('#add_wd_modal').on('hidden.bs.modal', function(e) {
                $('#add_wd_modal #add_wd_modal_title').val('');
                $('#add_wd_modal #week_date').val('');
                $('#add_wd_modal #client_id').val('');
                $('#add_wd_modal #instructor_id').val('');
                $('#add_wd_modal #supervisor_id').val('');
                $('#add_wd_modal #hours').val('');
                $('#add_wd_modal #skills_retained').val('');
                $('#add_wd_modal #doi').val('');
                $('#add_wd_modal #total_mands').val('');
                $('#add_wd_modal #variety_of_mands').val('');
                $('#add_wd_modal #frequency_of_problem_behavior').val('');
                $('#add_wd_modal #total_duration_of_problem_behavior').val('');
                $('#add_wd_modal #session_quality_rating').val('');
                $('#add_wd_modal #comments').val('');
                $('#add_wd_modal #program_change_made').prop("checked", false);

            });


            /**************************************************************************************** */
            $("#client_daily_data_computed").on('click', '.update-session', function(e) {
                var btn = $(this);
                var session_id = $(this).attr('id');
                var no_session = $(this).attr('no_session');
                var mrn = $(this).attr('mrn');

                current_row = $(this).parents('tr');
                selectedRowIndex = current_row.index();

                if (current_row.hasClass('child')) {
                    current_row = current_row.prev();
                    selectedRowIndex = current_row.index();
                }

                if (no_session == 0) {
                    showAlert('No Session', 'No Session can not be edited. Delete entry and add new one', 'warning');
                    current_row = '';
                } else {
                    var ajaxRequest = $.ajax({
                        url: '<?php echo base_url() ?>sessions/manual/get-selected',
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
                            row_data = response.data;
                            $('#update_wd_modal #update_wd_modal_title').html('Update Daily Data (' + mrn + ')');
                            $('#update_wd_modal #u_week_date').val(row_data.week_date);
                            $("#u_week_date").flatpickr({
                                defaultDate: row_data.week_date,
                                dateFormat: dateFormat,
                                maxDate: "today",
                                weekNumbers: true,
                            });
                            $('#update_wd_modal #session_id').val(row_data.id);
                            $('#update_wd_modal #client_id').val(row_data.client_id);
                            $('#update_wd_modal #instructor_id').val(row_data.instructor_id).change();
                            $('#update_wd_modal #supervisor_id').val(row_data.supervisor_id).change()
                            $('#update_wd_modal #hours').val(row_data.hours);
                            $('#update_wd_modal #skills_retained').val(row_data.skills_retained);
                            $('#update_wd_modal #doi').val(row_data.doi);
                            $('#update_wd_modal #total_mands').val(row_data.total_mands);
                            $('#update_wd_modal #variety_of_mands').val(row_data.variety_of_mands);
                            $('#update_wd_modal #frequency_of_problem_behavior').val(row_data.frequency_of_problem_behavior);
                            $('#update_wd_modal #total_duration_of_problem_behavior_u').val(row_data.total_duration_of_problem_behavior);
                            $('#update_wd_modal #session_quality_rating').val(row_data.session_quality_rating);
                            $('#update_wd_modal #status').val(row_data.status);
                            $('#update_wd_modal #comments').val(row_data.comments);
                            if (row_data.program_change_made == 1) {
                                $('#update_wd_modal #program_change_made_u').prop("checked", true);
                            } else {
                                $('#update_wd_modal #program_change_made_u').prop("checked", false);
                            }
                            $('#update_wd_modal').modal('show');
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
                }
            });
            /***************************************************************************************** */
            $('#btn_update').on('click', function() {
                var btn = $(this);
                var program_change_made = 0;
                if ($('#update_wd_modal #program_change_made_u').is(":checked")) {
                    program_change_made = 1;
                }
                var data = {
                    'id': $('#update_wd_modal #session_id').val(),
                    'week_date': $('#update_wd_modal #u_week_date').val(),
                    'client_id': $('#update_wd_modal #client_id').val(),
                    'instructor_id': $('#update_wd_modal #instructor_id').val(),
                    'supervisor_id': $('#update_wd_modal #supervisor_id').val(),
                    'hours': $('#update_wd_modal #hours').val(),
                    'skills_retained': $('#update_wd_modal #skills_retained').val(),
                    'doi': $('#update_wd_modal #doi').val(),
                    'total_mands': $('#update_wd_modal #total_mands').val(),
                    'variety_of_mands': $('#update_wd_modal #variety_of_mands').val(),
                    'frequency_of_problem_behavior': $('#update_wd_modal #frequency_of_problem_behavior').val(),
                    'total_duration_of_problem_behavior': $('#update_wd_modal #total_duration_of_problem_behavior_u').val(),
                    'session_quality_rating': $('#update_wd_modal #session_quality_rating').val(),
                    'no_session': $('#update_wd_modal #status').val(),
                    'program_change_made': program_change_made,
                    'comments': $('#update_wd_modal #comments').val(),

                };
                var ajaxRequest = $.ajax({
                    url: '<?php echo base_url() ?>sessions/manual/update',
                    type: 'post',
                    data: data,
                    beforeSend: function(xhr) {
                        btn.prop("disabled", true);
                    }
                });
                ajaxRequest.done(function(response) {
                    if (response.status == 'success') {

                        var updatedRow = table.row(current_row).data(response.data).draw(false).node();

                        // Highlight the updated row in blue
                        //$(updatedRow).addClass('table-info');
                        $(updatedRow).css({
                            'background-color': '#d4ebf8',
                            'color': '#000'
                        });

                        $('#update_wd_modal').modal('hide');
                        showAlert(response.statusText, response.message, response.status);
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
            $('#update_wd_modal').on('hidden.bs.modal', function(e) {
                $('#update_wd_modal #update_wd_modal_title').val('');
                $('#update_wd_modal #week_date').val('');
                $('#update_wd_modal #client_id').val('');
                $('#update_wd_modal #instructor_id').val('');
                $('#update_wd_modal #supervisor_id').val('');
                $('#update_wd_modal #hours').val('');
                $('#update_wd_modal #skills_retained').val('');
                $('#update_wd_modal #doi').val('');
                $('#update_wd_modal #total_mands').val('');
                $('#update_wd_modal #variety_of_mands').val('');
                $('#update_wd_modal #frequency_of_problem_behavior').val('');
                $('#update_wd_modal #total_duration_of_problem_behavior').val('');
                $('#update_wd_modal #session_quality_rating').val('');
                $('#update_wd_modal #comments').val('');
                $('#update_wd_modal #program_change_made_u').prop("checked", false);

            });

            /***************************************************************************************** */
            $('#add_no_session').on('click', function(e) {
                e.preventDefault;
                let client_id = $("#client_dropdown_list").val();

                if (client_id == '') {
                    showAlert('', 'Select Client', 'error');
                } else {
                    let client_detail = $("#client_dropdown_list :selected").html();
                    $("#nosession_wd_modal_title").html('Add No Session [' + client_detail + ']');
                    $("#nosession_wd_modal #client_id").val(client_id);
                    $('#nosession_wd_modal').modal('show');
                }
            });
            /***************************************************************************************** */
            $('#nosession_wd_modal').on('hidden.bs.modal', function(e) {
                $('#nosession_wd_modal #nosession_wd_modal_title').val('');
                $('#nosession_wd_modal #nosession_week_date').val('');
                $('#nosession_wd_modal #client_id').val('');
            });
            /***************************************************************************************** */
            $('#btn_add_nosession').on('click', function() {
                var btn = $(this);
                var data = {
                    'week_date': $('#nosession_wd_modal #nosession_week_date').val(),
                    'client_id': $('#nosession_wd_modal #client_id').val()
                };
                var ajaxRequest = $.ajax({
                    url: '<?php echo base_url() ?>sessions/manual/create_no_session',
                    type: 'post',
                    data: data,
                    beforeSend: function(xhr) {
                        btn.prop("disabled", true);
                    }
                });
                ajaxRequest.done(function(response) {
                    if (response.status == 'success') {
                        // Get current table data
                        var currentData = table.data().toArray();

                        // Add the new row data to the beginning of the array
                        currentData.unshift(response.data);

                        // Clear the table and re-add the data with the new row at the top
                        table.clear().rows.add(currentData).draw(false);

                        // Always switch back to the first page to show the new row
                        table.page('first').draw(false);

                        // Get the newly added row (which is now the first row)
                        var newRow = table.row(0).node();

                        // Apply CSS to the new row to highlight it in green
                        $(newRow).css({
                            'background-color': '#d4f8d4', // Green color for new row
                            'color': '#000'
                        });


                        $('#nosession_wd_modal').modal('hide');
                        showAlert(response.statusText, response.message, response.status);
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

            /*************************************************************************************** */
            $("#client_daily_data_computed").on('click', '.delete-session', function(e) {

                var session_id = $(this).attr('id');
                var mrn = $(this).attr('mrn');
                current_row = $(this).parents('tr');

                if (current_row.hasClass('child')) {
                    current_row = current_row.prev();
                }
                Swal.fire({
                    title: "Are you sure?",
                    text: "Once deleted, you will not be able to recover this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    confirmButtonClass: 'btn btn-primary w-xs me-2 mt-2',
                    cancelButtonClass: 'btn btn-danger w-xs me-2 mt-2',
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        var ajaxRequest = $.ajax({
                            url: '<?php echo base_url() ?>sessions/manual/delete',
                            type: 'post',
                            data: {
                                "id": session_id
                            },
                            beforeSend: function(xhr) {}
                        });
                        ajaxRequest.done(function(response) {
                            if (response.status == 'success') {
                                table.row(current_row).remove().draw(false);
                                showAlert(response.statusText, response.message, response.status);
                            } else {
                                showAlert(response.statusText, response.message, response.status);
                            }
                        });
                        ajaxRequest.fail(function(jqXHR, textStatus, error) {
                            showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
                        });
                        ajaxRequest.always(function() {

                        });
                    } else {
                        current_row = '';
                    }
                });
            });

            /***************************************************************************************** */

        });
    </script>
    <?= $this->endSection() ?>
