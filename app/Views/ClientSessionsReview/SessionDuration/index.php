<?= $this->extend("layout/master") ?>
<?= $this->section("head_tag") ?>
<style>
    .form-check-input[type=radio] {
        border-radius: .25em !important;
    }

    .form-check-input:checked[type=radio] {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 10'%3e%3cpolyline points='1 5 4 8 10 1' fill='none' stroke='%23fff' stroke-width='3'/%3e%3c/svg%3e") !important;
        background-position: center;
        background-repeat: no-repeat;
        background-size: 60%;
    }
</style>
<?= $this->endSection() ?>
<?= $this->section("page_content") ?>
<?= view('ClientSessionsReview/_common_header', ['section_name' => 'Session Duration']) ?>
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body border-bottom-dashed border-bottom">
                <?= view('ClientSessionsReview/_tabs', ['tab' => 'session_duration']) ?>
                <div class="tab-content pt-2">
                    <div class="tab-pane fade show active" role="tabpanel" aria-labelledby="info-tab">
                        <div class="row">
                            <!-- Teaching Duration Card -->
                            <div class="col-lg-12">
                                <div class="card border card-border-info">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Teaching Duration</h5>
                                        <button id="add_teaching_duration" class="btn btn-sm btn-soft-info waves-effect waves-light">
                                            <i class="ri-add-line align-bottom me-1"></i> Add Teaching Duration
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered nowrap" style="width: 100%;" id="teaching_duration_dataTable"></table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Mands Duration Card -->
                            <div class="col-lg-12">
                                <div class="card border card-border-info">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Mands Duration</h5>
                                        <button id="add_mands_duration" class="btn btn-sm btn-soft-info waves-effect waves-light">
                                            <i class="ri-add-line align-bottom me-1"></i> Add Mands Duration
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered nowrap" style="width: 100%;" id="mands_duration_dataTable"></table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- PB Duration Card -->
                            <div class="col-lg-12">
                                <div class="card border card-border-info">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Problem Behavior Duration</h5>
                                        <div style="height: 28px;"></div> <!-- Empty space for alignment -->
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered nowrap" style="width: 100%;" id="pb_duration_dataTable"></table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section("page_modal") ?>
<!-- Add Duration Modal -->
<div class="modal fade" id="durationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_title">Add Duration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="durationForm">
                <div class="modal-body">
                    <input type="hidden" id="duration_id" name="duration_id">
                    <input type="hidden" id="session_id" name="session_id" value="<?= $session->id ?>">
                    <input type="hidden" id="duration_type" name="duration_type">

                    <div class="mb-3">
                        <label for="start_time" class="form-label">Start Time:</label>
                        <input type="text" id="start_time" name="start_time" class="form-control flatpickr">
                    </div>

                    <div class="mb-3">
                        <label for="end_time" class="form-label">End Time:</label>
                        <input type="text" id="end_time" name="end_time" class="form-control flatpickr">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btn_save">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section("page_js") ?>
<script>
    $(document).ready(function() {
        /***************************************************************************************** */
        var csrfToken = "<?= csrf_hash() ?>";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        });
        var sessionStatus = <?= $session->status ?>; // Get session status from PHP

        // Define the columns array
        var teachingColumns = [{
                data: 'start_time',
                title: 'Start Time'
            },
            {
                data: 'end_time',
                title: 'End Time'
            },
            {
                data: 'duration_time_format',
                title: 'Duration (T)',
                render: function(data, type, row) {
                    if (row.duration_time_format != null) {
                        return row.duration_time_format + ' (' + row.duration_decimal_format + ')';
                    } else {
                        return '';
                    }

                }
            }
        ];

        // Add "Action" column only if sessionStatus is 2
        teachingColumns.push({
            data: null,
            title: 'Action',
            render: function(data, type, row) {
                let actionButtons = ``;
                if (data.id !== null) {
                    actionButtons += `<button id="${row.id}" type="button" class="btn btn-sm btn-outline-warning btn-icon wave-effect waves-light update_duration" data-type="teaching">
                    <i class="ri-edit-line"></i>
                </button>`;
                    actionButtons += `&nbsp;`;
                    actionButtons += `<button id="${row.id}" type="button" class="btn btn-sm btn-outline-danger btn-icon wave-effect waves-light delete_duration" data-type="teaching">
                    <i class="ri-delete-bin-line"></i>
                </button>`;
                }
                return actionButtons;
            }
        });

        // Teaching Duration DataTable
        var teaching_duration_table = $('#teaching_duration_dataTable').DataTable({
            ajax: {
                url: '<?= base_url("sessions/review/teachingDurationList") ?>',
                type: 'POST',
                data: {
                    session_id: <?= $session->id ?>
                }
            },
            columns: teachingColumns,
            lengthChange: false,
            ordering: false,
            lengthMenu: [
                [10, 25, 50, -1],
                ['10 rows', '25 rows', '50 rows', 'Show all']
            ],
            layout: {
                topStart: {},
                topEnd: {}
            },
            rowCallback: function(row, data) {
                if (data.id === null) {
                    $(row).css({
                        'font-weight': 'bold'
                    });
                }
            }
        });

        // Repeat the same logic for Mands Duration DataTable
        var mandsColumns = [{
                data: 'start_time',
                title: 'Start Time'
            },
            {
                data: 'end_time',
                title: 'End Time'
            },
            {
                data: 'duration_time_format',
                title: 'Duration (T)',
                render: function(data, type, row) {
                    if (row.duration_time_format != null) {
                        return row.duration_time_format + ' (' + row.duration_decimal_format + ')';
                    } else {
                        return '';
                    }

                }
            }
        ];

        mandsColumns.push({
            data: null,
            title: 'Action',
            render: function(data, type, row) {
                let actionButtons = ``;
                if (data.id !== null) {
                    actionButtons += `<button id="${row.id}" type="button" class="btn btn-sm btn-outline-warning btn-icon wave-effect waves-light update_duration" data-type="mands">
                    <i class="ri-edit-line"></i>
                </button>`;
                    actionButtons += `&nbsp;`;
                    actionButtons += `<button id="${row.id}" type="button" class="btn btn-sm btn-outline-danger btn-icon wave-effect waves-light delete_duration" data-type="mands">
                    <i class="ri-delete-bin-line"></i>
                </button>`;
                }
                return actionButtons;
            }
        });

        var mands_duration_table = $('#mands_duration_dataTable').DataTable({
            ajax: {
                url: '<?= base_url("sessions/review/mandsDurationList") ?>',
                type: 'POST',
                data: {
                    session_id: <?= $session->id ?>
                }
            },
            columns: mandsColumns,
            lengthChange: false,
            ordering: false,
            lengthMenu: [
                [10, 25, 50, -1],
                ['10 rows', '25 rows', '50 rows', 'Show all']
            ],
            layout: {
                topStart: {},
                topEnd: {}
            },
            rowCallback: function(row, data) {
                if (data.id === null) {
                    $(row).css({
                        'font-weight': 'bold'
                    });
                }
            }
        });

        // Mands Duration DataTable
        var pb_duration_table = $('#pb_duration_dataTable').DataTable({
            ajax: {
                url: '<?= base_url("sessions/review/pbDurationList") ?>',
                type: 'POST',
                data: {
                    session_id: <?= $session->id ?>
                }
            },
            columns: [{
                    data: 'start_time',
                    title: 'Start Time'
                },
                {
                    data: 'end_time',
                    title: 'End Time'
                },
                {
                    data: 'duration_time_format',
                    title: 'Duration (T)',
                    render: function(data, type, row) {
                        if (row.duration_time_format != null) {
                            return row.duration_time_format + ' (' + row.duration_decimal_format + ')';
                        } else {
                            return '';
                        }

                    }
                }
            ],
            lengthChange: false,
            ordering: false,
            lengthMenu: [
                [10, 25, 50, -1],
                ['10 rows', '25 rows', '50 rows', 'Show all']
            ],
            layout: {
                topStart: {},
                topEnd: {}
            },
            rowCallback: function(row, data) {
                if (data.id === null) { // Ensure strict comparison to null
                    $(row).css({
                        'font-weight': 'bold'
                    });
                }
            }
        });
    });


    // Open Modal for Adding New Duration
    $(document).on('click', '#add_teaching_duration, #add_mands_duration', function() {
        let type = $(this).attr('id') === 'add_teaching_duration' ? 'teaching' : 'mands';

        $('#durationForm')[0].reset();
        $('#duration_id').val('');
        $('#duration_type').val(type);
        $('#modal_title').text(type === 'teaching' ? 'Add Teaching Duration' : 'Add Mands Duration');
        $('#btn_save').text('Save').data('action', 'add');

        // ✅ Destroy Flatpickr instances before reinitializing
        $('.flatpickr').each(function() {
            if ($(this)._flatpickr) {
                $(this)._flatpickr.destroy();
            }
        });
        // ✅ Reinitialize Flatpickr with proper settings
        $('.flatpickr').flatpickr({
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i:S', // ✅ Ensures selection of H:M:S
            time_24hr: true,
            enableSeconds: true, // ✅ Allows users to select seconds
            minuteIncrement: 1, // ✅ Allows minute adjustments         
        });

        $('#durationModal').modal('show');

    });
    // Handle Save/Update Button
    $('#btn_save').click(function() {
        let action = $(this).data('action');
        let formData = {
            id: $('#duration_id').val(),
            session_id: $('#session_id').val(),
            duration_type: $('#duration_type').val(),
            start_time: $('#start_time').val(),
            end_time: $('#end_time').val()
        };

        let url = action === 'add' ?
            '<?= site_url("sessions/review/createDuration") ?>' :
            '<?= site_url("sessions/review/updateDuration") ?>';

        $.ajax({
            type: 'POST',
            url: url,
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#durationModal').modal('hide');
                    let table = formData.duration_type === 'teaching' ? '#teaching_duration_dataTable' : '#mands_duration_dataTable';
                    $(table).DataTable().ajax.reload(null, false);
                    showAlert('Success!', action === 'add' ? 'Added successfully!' : 'Updated successfully!', 'success');
                } else {
                    showAlert('Error! ', response.message, 'error');
                }
            }
        });
    });

    // Open Modal for Updating Duration (POST Method with `id` and `duration_type`)
    $(document).on('click', '.update_duration', function() {
        let duration_id = $(this).attr('id');
        let type = $(this).data('type'); // Now we send this
        $('#durationForm')[0].reset();

        $.ajax({
            type: 'POST', // **POST instead of GET**
            url: '<?= site_url("sessions/review/getDuration") ?>',
            data: {
                id: duration_id,
                duration_type: type
            }, // Send both
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    let record = response.record;

                    $('#duration_id').val(record.id);
                    $('#duration_type').val(type);
                    $('#modal_title').text(type === 'teaching' ? 'Edit Teaching Duration' : 'Edit Mands Duration');
                    $('#start_time').val(record.start_time);
                    $('#end_time').val(record.end_time);
                    $('#btn_save').text('Update').data('action', 'update');
                    // ✅ Destroy Flatpickr instances before reinitializing
                    $('.flatpickr').each(function() {
                        if ($(this)._flatpickr) {
                            $(this)._flatpickr.destroy();
                        }
                    });
                    // ✅ Reinitialize Flatpickr with proper settings
                    $('.flatpickr').flatpickr({
                        enableTime: true,
                        noCalendar: true,
                        dateFormat: 'H:i:S', // ✅ Ensures selection of H:M:S
                        time_24hr: true,
                        enableSeconds: true, // ✅ Allows users to select seconds
                        minuteIncrement: 1, // ✅ Allows minute adjustments         
                    });
                    $('#durationModal').modal('show');

                }
            }
        });
    });
    $(document).on('click', '.delete_duration', function() {
        let duration_id = $(this).attr('id');
        let type = $(this).data('type');
        let table = type === 'teaching' ? '#teaching_duration_dataTable' : '#mands_duration_dataTable';

        Swal.fire({
            title: 'Are you sure?',
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            customClass: {
                confirmButton: 'btn btn-danger w-xs me-2 mt-2',
                cancelButton: 'btn btn-primary w-xs me-2 mt-2',
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'POST',
                    url: '<?= site_url("sessions/review/deleteDuration") ?>',
                    data: {
                        id: duration_id,
                        duration_type: type
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $(table).DataTable().ajax.reload(null, false);
                            showAlert('Success! ', response.message, 'success');
                        } else {
                            showAlert('Error!', response.message, 'error');
                        }
                    }
                });
            }
        });
    });
</script>
<?= $this->endSection() ?>