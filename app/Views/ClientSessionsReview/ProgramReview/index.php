<?= $this->extend("layout/master") ?>
<?= $this->section("head_tag") ?>
<style>
    .offcanvas,
    .offcanvas-lg,
    .offcanvas-md,
    .offcanvas-sm,
    .offcanvas-xl,
    .offcanvas-xxl {

        --vz-offcanvas-width: 100%;

    }
</style>
<?= $this->endSection() ?>
<?= $this->section("page_content") ?>
<?= view('ClientSessionsReview/_common_header', ['section_name' => 'Programs']) ?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body border-bottom-dashed border-bottom">
                <?= view('ClientSessionsReview/_tabs', ['tab' => 'program_data']) ?>
                <div class="tab-content">
                    <div class="tab-pane fade show active" role="tabpanel" aria-labelledby="info-tab">

                        <input type="hidden" name="session_id" value="<?= $session->id; ?>">
                        <table id="session_review_dataTable" class="table table-bordered align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Domain</th>
                                    <th>Goal</th>
                                    <th>Target</th>
                                    <th>Probe Set</th>
                                    <th>Target Phase</th>
                                    <th>Result</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($collectedData)) foreach ($collectedData as $data): ?>
                                    <?php
                                    $collectedData = json_decode($data->collected_data);
                                    $inputs = $collectedData->inputs;
                                    $result = $collectedData->result;
                                    $phaseName = $collectedData->phase->name;
                                    $percentageSign = '';

                                    if ($inputs->type == 'percentage_yes_no') {
                                        $percentageSign = "%";
                                    }
                                    $step_value = '';
                                    if ($inputs->type == 'stimulus_program') {
                                        if ($collectedData->method == 'baseline') {
                                            $percentageSign = "%";
                                        }
                                        if ($collectedData->method == 'forward') {
                                            $step_value = $collectedData->statistics->probe_value;
                                        }
                                        if ($collectedData->method == 'backward') {
                                            $step_value = $collectedData->statistics->probe_value;
                                        }
                                        if ($collectedData->method == 'total_task') {
                                            $percentageSign = "%";
                                        }
                                    }
                                    if ($inputs->type != 'stimulus_program') {
                                        $collectedData->method = null;
                                    }



                                    $status = '';
                                    if ($data->is_processed) {
                                        $status = '<span class="badge border border-success text-success">Processed</span>';
                                    } elseif ($data->is_conflicted) {
                                        $status = '<span class="badge border border-danger text-danger">Conflict</span>';
                                    } else {
                                        $status = '<span class="badge border border-info text-info">Pending</span>';
                                    }
                                    ?>
                                    <tr>
                                        <td><?= $data->domain_code; ?></td>
                                        <td><?= $data->goal_code; ?></td>
                                        <td><?= $data->target_name; ?></td>
                                        <td><?= $data->probe_set_name; ?></td>
                                        <td><?= $phaseName; ?></td>
                                        <td>
                                            <?php if ($collectedData->method == 'forward' || $collectedData->method == 'backward'): ?> <!-- Treat 0 as valid, check for null or empty string -->
                                                <div class="d-inline-flex flex-nowrap gap-1">
                                                    <div class="rounded-circle d-flex justify-content-center align-items-center"
                                                        style="width: 40px; height: 40px; background-color: #e0e0e0; font-size: 14px;">

                                                        <?= htmlspecialchars($percentageSign === '%' && is_numeric($step_value) ? (int) round((float) $step_value) : $step_value) . $percentageSign; // Display value 
                                                        ?>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="d-inline-flex flex-nowrap gap-1">
                                                    <?php foreach ($result as $value): ?>
                                                        <div class="rounded-circle d-flex justify-content-center align-items-center"
                                                            style="width: 40px; height: 40px; background-color: #e0e0e0; font-size: 14px;">

                                                            <?php if ($value !== null && $value !== ''): ?> <!-- Treat 0 as valid, check for null or empty string -->
                                                                <?= htmlspecialchars($percentageSign === '%' && is_numeric($value) ? (int) round((float) $value) : $value) . $percentageSign; // Display value 
                                                                ?>
                                                            <?php else: ?>
                                                                <i class="ri-close-line text-danger"></i> <!-- Display cross icon for null or empty values -->
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>

                                        </td>



                                        <td><?= $status; ?></td> <!-- Display the status -->
                                        <td>
                                            <?php if ($data->is_conflicted): ?>
                                                <button data-id="<?= $data->id; ?>" type="button" class="btn btn-sm btn-outline-primary resolve-conflict"><i class="ri-git-merge-line align-bottom me-1"></i>Resolve</button>

                                            <?php endif; ?>
                                            <button data-id="<?= $data->id; ?>" data-master-probe-set-id="<?= $data->master_probe_set_id; ?>" type="button" class="btn btn-sm btn-outline-warning update"><i class="ri-edit-line align-bottom me-1"></i>Edit</button>
                                            <button data-id="<?= $data->id; ?>" type="button" class="btn btn-sm btn-outline-danger delete"><i class="ri-delete-bin-line align-bottom me-1"></i>Delete</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="offcanvas offcanvas-end" tabindex="-1" id="manuallyTargetCanvas" aria-labelledby="offcanvasRightLabel">
    <div class="offcanvas-body " id='manuallyTargetCanvasDetail'> </div>
</div>
<?= $this->endSection() ?>
<?= $this->section("page_modal") ?>
<div class="modal fade" id="update_modal" tabindex="-1" aria-hidden="true">>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="update_modal_title"> </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <input type="text" class="form-control " hidden="hidden" name="id" id="id">
                </div>
                <div class="row">
                    <!-- Logic to handle input detail. need to display inputs need show current selected. and user should be able to select other. and save. -->
                    <div class="col-md-12">
                        <div id="inputs_container"></div>
                    </div>
                </div>



            </div>
            <div class="modal-footer">
                <div class="hstack gap-2 justify-content-end">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="ri-close-line align-bottom me-1"></i>Close</button>
                    <button type="button" class="btn btn-primary" id="btn_update"><i class="ri-save-line align-bottom me-1"></i>Update</button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="update_modal_p_yes_no" tabindex="-1" aria-hidden="true">>
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="update_modal_p_yes_no_title"> </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <input type="text" class="form-control " hidden="hidden" name="id" id="id">
                </div>
                <div class="row">
                    <!-- Logic to handle input detail. need to display inputs need show current selected. and user should be able to select other. and save. -->
                    <div class="col-md-12">
                        <div id="inputs_container_p_yes_no"></div>
                    </div>
                </div>
                <div class="mt-3 text-end">
                    <button class="btn btn-sm btn-outline-primary" id="add_new_transition_pair">
                        <i class="ri-add-line"></i> Add New Trial Data
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <div class="hstack gap-2 justify-content-end">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="ri-close-line align-bottom me-1"></i>Close</button>
                    <button type="button" class="btn btn-primary" id="btn_update_p_yes_no"><i class="ri-save-line align-bottom me-1"></i>Update</button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="update_modal_stimulus" tabindex="-1" aria-hidden="true">>
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="update_modal_stimulus_title"> </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <input type="text" class="form-control " hidden="hidden" name="id" id="id">
                </div>
                <div class="row">
                    <!-- Logic to handle input detail. need to display inputs need show current selected. and user should be able to select other. and save. -->
                    <div class="col-md-12">
                        <div id="inputs_container_stimulus"></div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <div class="hstack gap-2 justify-content-end">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="ri-close-line align-bottom me-1"></i>Close</button>

                </div>
            </div>
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
        /***************************************************************************************** */
        table = $('#session_review_dataTable').DataTable({
            lengthChange: false,
            "ordering": false,
            lengthMenu: [
                [10, 25, 50, -1],
                ['10 rows', '25 rows', '50 rows', 'Show all']
            ],
            layout: {
                topStart: {
                    buttons: [{
                            text: '<i class="ri-add-line align-bottom me-1"></i>Add Target Data Manually',
                            className: 'btn btn-soft-info waves-effect waves-light material-shadow-none',
                            attr: {
                                id: 'add_executed_session_target_manually'
                            },
                            action: function(e, dt, node, config) {
                                // Add your action for the button here
                                show_add_target_canvas();
                            }
                        },
                        {
                            extend: 'pageLength',
                            className: 'btn btn-light bg-gradient waves-effect waves-light'
                        },
                        {
                            extend: 'copy',
                            className: 'btn btn-light bg-gradient waves-effect waves-light'
                        }, {
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




        });
        var manuallyTargetCanvas = document.getElementById('manuallyTargetCanvas')
        var manuallyTargetCanvasID = new bootstrap.Offcanvas(manuallyTargetCanvas)

        function show_add_target_canvas() {
            const baseUrl = '<?= base_url('sessions/review/manual-target-entry') ?>';
            const sessionId = "<?= $session->id; ?>";

            // Create the URL with query parameters
            const redirectUrl = `${baseUrl}/${sessionId}`;

            // Redirect to the constructed URL
            window.location.href = redirectUrl;
            //console.log('get add target screen' + "<?= $session->id; ?>");
            //manuallyTargetCanvasID.show()

            //console.log(pg_alert_id, pg_change_id, client_id, target_id);
            /*var ajaxRequest = $.ajax({
                type: 'POST',
                url: '<?= base_url('sessions/review/getTargetScreenForManuallyEntry') ?>',
                data: {
                    session_id: "<?= $session->id; ?>",
                },
                dataType: 'html',
                beforeSend: function(xhr) {

                }
            });
            ajaxRequest.done(function(response) {
                $('#manuallyTargetCanvasDetail').html(response);
                manuallyTargetCanvasID.show();
            });

            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
            ajaxRequest.always(function() {

            });*/

        }

        $('#manuallyTargetCanvas').on('hidden.bs.offcanvas', function() {
            // Trigger a custom event when the offcanvas is hidden
            $('#manuallyTargetCanvasDetail').html('');
            console.log('refresh page');
        });
        /***************************************************************************************** */

        $("#session_review_dataTable").on('click', '.update', function(e) {
            var btn = $(this);
            var id = $(this).attr('data-id');
            var master_probe_set_id = $(this).attr('data-master-probe-set-id');
            current_row = $(this).parents('tr');
            if (current_row.hasClass('child')) {
                current_row = current_row.prev();
            }
            var ajaxRequest = $.ajax({
                url: '<?php echo base_url() ?>/sessions/review/single',
                type: 'post',
                data: {
                    "id": id,
                },
                beforeSend: function(xhr) {
                    btn.prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    row_data = response.data;
                    if (master_probe_set_id == 6) {
                        $('#update_modal_p_yes_no #update_modal_p_yes_no__title').html('Update Percentage Probe data');
                        $('#update_modal_p_yes_no #id').val(row_data.id);
                        // Render the inputs HTML into the modal
                        $('#inputs_container_p_yes_no').html(row_data.inputs_html);
                        $('#update_modal_p_yes_no').modal('show');
                    }
                    if (master_probe_set_id == 7) {
                        $('#update_modal_stimulus #update_modal_stimulus_title').html('Update Stimulus Probe data');
                        $('#update_modal_stimulus #id').val(row_data.id);
                        // Render the inputs HTML into the modal
                        $('#inputs_container_stimulus').html(row_data.inputs_html);
                        $('#update_modal_stimulus').modal('show');
                    } else {
                        $('#update_modal #update_modal_title').html('Update Probe data');
                        $('#update_modal #id').val(row_data.id);
                        // Render the inputs HTML into the modal
                        $('#inputs_container').html(row_data.inputs_html);
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
        /***************************************************************************************** */
        $('#update_modal').on('hidden.bs.modal', function(e) {
            $('#update_modal #update_modal_title').val('');
            $('#update_modal #id').val('');

        });
        $('#update_modal_stimulus').on('hidden.bs.modal', function(e) {
            location.reload();

        });
        /***************************************************************************************** */
        $('#btn_update').on('click', function() {
            var btn = $(this);
            // Get the record ID from the hidden input field in the modal
            var dataId = $('#update_modal #id').val();

            // Get the checked radio button's value
            // Initialize an empty array to store selected values
            var selectedValues = [];

            // Loop through each set of radio buttons by index and collect the checked values
            $('#update_modal input[type="radio"]:checked').each(function() {
                selectedValues.push($(this).val());
            });

            // Prepare data for the AJAX request
            var data = {
                id: dataId,
                selected_value: selectedValues // Pass the array of selected values
            };
            console.log(data);
            var ajaxRequest = $.ajax({
                url: '<?php echo base_url() ?>sessions/review/update',
                type: 'post',
                data: data,
                beforeSend: function(xhr) {
                    btn.prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    //updateTableRow(response.data);
                    table
                        .row(current_row)
                        .data(response.data)
                        .draw(false);
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
        $('#btn_update_p_yes_no').on('click', function() {
            var btn = $(this);
            var dataId = $('#update_modal_p_yes_no #id').val(); // Note the correct modal ID

            var transitions = [];
            var hasError = false;

            $('#update_transition_list li').each(function(index) {
                var transitionText = $(this).find('.transition-text').val().trim();
                var answer = $(this).find('input[type="radio"]:checked').val();

                // Validate: if either is filled and the other is missing, it's an error
                if ((transitionText !== '' && !answer) || (transitionText === '' && answer)) {
                    showAlert('Validation Error', `Please complete both transition and answer for item #${index + 1}.`, 'warning');
                    hasError = true;
                    return false; // Break out of each loop
                }

                // If both are filled, push to array
                if (transitionText !== '' && (answer === 'Y' || answer === 'N')) {
                    transitions.push({
                        transition: transitionText,
                        answer: answer
                    });
                }
            });

            // Stop if error occurred
            if (hasError) return;

            if (transitions.length === 0) {
                showAlert('Warning', 'Please enter at least one valid transition with a selected answer.', 'warning');
                return;
            }

            var data = {
                id: dataId,
                transitions: transitions
            };

            console.log(data); // Debug

            var ajaxRequest = $.ajax({
                url: '<?php echo base_url() ?>sessions/review/update_p_yes_no',
                type: 'post',
                data: data,
                beforeSend: function(xhr) {
                    btn.prop("disabled", true);
                }
            });

            ajaxRequest.done(function(response) {
                if (response.status === 'success') {
                    table.row(current_row).data(response.data).draw(false);
                    $('#update_modal_p_yes_no').modal('hide');
                    showAlert(response.statusText, response.message, response.status);
                } else if (response.status === 'error' && response.statusText === 'Validation_Error') {
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

        $('#add_new_transition_pair').on('click', function() {
            var index = $('#update_transition_list li').length;

            var newItem = `
                <li class="list-group-item d-flex align-items-center justify-content-between gap-2 flex-wrap" data-index="${index}">
                    <input type="text" class="form-control transition-text" placeholder="Enter transition..." style="max-width: 80%;">
                    
                    <div class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="answer_${index}" id="answer_y_${index}" value="Y">
                        <label class="btn btn-outline-primary" for="answer_y_${index}">Y</label>

                        <input type="radio" class="btn-check" name="answer_${index}" id="answer_n_${index}" value="N">
                        <label class="btn btn-outline-primary" for="answer_n_${index}">N</label>
                    </div>

                    <button class="btn btn-outline-danger delete-entry" title="Remove">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </li>
            `;

            $('#update_transition_list').append(newItem);
        });
        $(document).on('click', '.delete-entry', function() {
            $(this).closest('li').remove();
        });

        /***************************************************************************************** */
        $("#session_review_dataTable").on('click', '.delete', function(e) {
            var id = $(this).data('id');
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
                customClass: {
                    confirmButton: 'btn btn-danger w-xs me-2 mt-2',
                    cancelButton: 'btn btn-primary w-xs me-2 mt-2',
                },

                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var ajaxRequest = $.ajax({
                        url: '<?php echo base_url() ?>sessions/review/delete',
                        type: 'post',
                        data: {
                            "id": id,
                        },
                        beforeSend: function(xhr) {

                        }
                    });
                    ajaxRequest.done(function(response) {
                        if (response.status == 'success') {
                            showAlert(response.statusText, response.message, response.status);
                            location.reload(); // Reload the page after hiding
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
                    current_row = ''
                }
            });

        });
        /***************************************************************************************** */

        $("#session_review_dataTable").on('click', '.resolve-conflict', function(e) {
            var btn = $(this);
            var id = $(this).data('id');
            current_row = $(this).parents('tr');

            if (current_row.hasClass('child')) {
                current_row = current_row.prev();
            }

            $.ajax({
                url: '<?= base_url() ?>/sessions/review/viewTargetConflictDetail',
                type: 'POST',
                data: {
                    "id": id
                },
                beforeSend: function() {
                    btn.prop("disabled", true);
                },
                success: function(response) {
                    if (response.success) {
                        // Load the HTML content into the modal body
                        $('#manuallyTargetCanvasDetail').html(response.html);

                        // Set modal title (optional)
                        $('#rulesCanvasTitle').text("Resolve Target Conflict");

                        // Show the modal (Full-screen Offcanvas)
                        manuallyTargetCanvasID.show();
                    } else {
                        showAlert('Error', response.message, 'error');
                    }
                },
                error: function(jqXHR, textStatus, error) {
                    showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
                },
                complete: function() {
                    btn.prop("disabled", false);
                }
            });
        });


        // Handle Resolve Conflict Button Click (Replacement for `.process`)
        $("#manuallyTargetCanvas").on('click', '#resolveConflictBtn', function(e) {
            var btn = $(this);
            var id = btn.attr('data-id');

            if (!$('#confirmResolution').prop('checked')) {
                showAlert('Warning', 'You must confirm the resolution before proceeding.', 'warning');
                return;
            }

            $.ajax({
                url: '<?= base_url() ?>/sessions/process/conflict',
                type: 'POST',
                data: {
                    "dataCollectionId": id
                },
                beforeSend: function() {
                    btn.prop("disabled", true);
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            position: 'top-end',
                            icon: 'success',
                            title: response.message,
                            showConfirmButton: false,
                            timer: 1500,
                            showCloseButton: true
                        }).then(() => {
                            manuallyTargetCanvasID.hide(); // Hide the offcanvas
                            location.reload(); // Reload the page after hiding
                        });
                    } else {
                        showAlert('Error', response.message, 'error');
                    }
                },
                error: function(jqXHR, textStatus, error) {
                    showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
                },
                complete: function() {
                    btn.prop("disabled", false);
                }
            });
        });

        $(document).off('click', '.save-stimulus-button').on('click', '.save-stimulus-button', function() {
            let mode = $(this).data('method');
            switch (mode) {
                case 'baseline':
                    return handleBaselineSave($(this));
                case 'forward':
                    return handleForwardSave($(this));
                case 'backward':
                    return handleBackwardSave($(this));
                case 'total_task':
                    return handleTotalTaskSave($(this));
                default:
                    Swal.fire('Error', 'Invalid chaining method.', 'error');
            }
        });

        function handleBaselineSave(button) {
            let target_id = button.data('target-id');
            let phase_id = button.data('phase-id');
            let attempt_no = button.data('attempt');
            let goal_id = button.data('goal-id');
            let domain_id = button.data('domain-id');
            let session_id = button.data('session-id');
            let client_id = button.data('client-id');
            let client_probe_set_id = button.data('probe-set-id');
            let method = button.data('method');

            let step_data = [];

            // For each step row in the current baseline tab
            $(`#baseline-content-${target_id}-${attempt_no} .btn-check`).each(function() {
                let name = $(this).attr('name');
                let nameParts = name.match(/stimulus\[(\d+)]\[(\d+)]\[(\d+)]/);
                if (!nameParts) return;

                let step_id = nameParts[2];
                let input = $(`input[name="${name}"]:checked`).val() || null;

                // Avoid duplicates for same step_id
                if (!step_data.some(s => s.step_id === step_id)) {
                    step_data.push({
                        step_id: step_id,
                        input_result: input
                    });
                }
            });

            const hasAnyInput = step_data.some(step => step.input_result !== null);

            if (!hasAnyInput) {
                Swal.fire('No input', 'Please select at least one response.', 'warning');
                return;
            }

            $.ajax({
                url: '<?= site_url('sessions/review/target/save_stimulus_baseline_attempt') ?>',
                method: 'POST',
                data: {
                    client_id: client_id,
                    target_id: target_id,
                    goal_id: goal_id,
                    domain_id: domain_id,
                    session_id: session_id,
                    current_phase_id: phase_id,
                    client_probe_set_id: client_probe_set_id,
                    attempt_no: attempt_no,
                    step_data: step_data,
                    method: method
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success === 'Yes') {
                        Swal.fire('Success', response.message, 'success');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Server Error', xhr.responseText, 'error');
                }
            });
        }

        function handleForwardSave(button) {
            let target_id = button.data('target-id');
            let phase_id = button.data('phase-id');
            let goal_id = button.data('goal-id');
            let domain_id = button.data('domain-id');
            let session_id = button.data('session-id');
            let client_id = button.data('client-id');
            let client_probe_set_id = button.data('probe-set-id');
            let method = button.data('method'); // forward

            let step_data = [];

            $(`.btn-check[name^="stimulus[${target_id}]"]`).each(function() {
                let name = $(this).attr('name');
                let nameParts = name.match(/stimulus\[(\d+)]\[(\d+)]/);
                if (!nameParts) return;

                let step_id = nameParts[2];

                // Avoid duplicates if multiple radios in group
                if (step_data.some(s => s.step_id === step_id)) return;

                let selected = $(`input[name="stimulus[${target_id}][${step_id}]"]:checked`).val();
                if (selected) {
                    step_data.push({
                        step_id: step_id,
                        input_result: selected
                    });
                }
            });

            if (step_data.length === 0) {
                Swal.fire('No input', 'Please select a response.', 'warning');
                return;
            }

            $.ajax({
                url: '<?= site_url('sessions/review/target/save_stimulus_forward_attempt') ?>',
                method: 'POST',
                data: {
                    client_id,
                    target_id,
                    goal_id,
                    domain_id,
                    session_id,
                    current_phase_id: phase_id,
                    client_probe_set_id,
                    step_data,
                    method
                },
                dataType: 'json',
                success: function(res) {
                    if (res.success === 'Yes') {
                        Swal.fire('Saved', res.message, 'success');
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Server Error', xhr.responseText, 'error');
                }
            });
        }


        function handleBackwardSave(button) {
            let target_id = button.data('target-id');
            let phase_id = button.data('phase-id');
            let goal_id = button.data('goal-id');
            let domain_id = button.data('domain-id');
            let session_id = button.data('session-id');
            let client_id = button.data('client-id');
            let client_probe_set_id = button.data('probe-set-id');
            let method = button.data('method'); // backward

            let step_data = [];

            $(`.btn-check[name^="stimulus[${target_id}]"]`).each(function() {
                let name = $(this).attr('name');
                let nameParts = name.match(/stimulus\[(\d+)]\[(\d+)]/);
                if (!nameParts) return;

                let step_id = nameParts[2];
                if (step_data.some(s => s.step_id === step_id)) return;

                let selected = $(`input[name="stimulus[${target_id}][${step_id}]"]:checked`).val();
                if (selected) {
                    step_data.push({
                        step_id: step_id,
                        input_result: selected
                    });
                }
            });

            if (step_data.length === 0) {
                Swal.fire('No input', 'Please select a response.', 'warning');
                return;
            }

            $.ajax({
                url: '<?= site_url('sessions/review/target/save_stimulus_backward_attempt') ?>',
                method: 'POST',
                data: {
                    client_id,
                    target_id,
                    goal_id,
                    domain_id,
                    session_id,
                    current_phase_id: phase_id,
                    client_probe_set_id,
                    step_data,
                    method
                },
                dataType: 'json',
                success: function(res) {
                    if (res.success === 'Yes') {
                        Swal.fire('Saved', res.message, 'success');
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Server Error', xhr.responseText, 'error');
                }
            });
        }


        function handleTotalTaskSave(button) {
            let target_id = button.data('target-id');
            let phase_id = button.data('phase-id');
            let goal_id = button.data('goal-id');
            let domain_id = button.data('domain-id');
            let session_id = button.data('session-id');
            let client_id = button.data('client-id');
            let client_probe_set_id = button.data('probe-set-id');
            let method = button.data('method'); // total_task

            let step_data = [];

            // Collect all step radio groups
            $(`#total-task-content-${target_id} .btn-check`).each(function() {
                let name = $(this).attr('name');
                let nameParts = name.match(/stimulus\[(\d+)]\[(\d+)]/);
                if (!nameParts) return;

                let step_id = nameParts[2];

                // Avoid duplicates if multiple radios in group
                if (step_data.some(s => s.step_id === step_id)) return;

                let selected = $(`input[name="stimulus[${target_id}][${step_id}]"]:checked`).val();
                step_data.push({
                    step_id: step_id,
                    input_result: selected ?? null
                });
            });

            // Fix: Ensure we detect at least one selected radio
            const hasAnyInput = step_data.some(s => s.input_result !== null && s.input_result !== undefined && s.input_result !== '');

            if (!hasAnyInput) {
                Swal.fire('No input', 'Please select at least one response.', 'warning');
                return;
            }

            $.ajax({
                url: '<?= site_url('sessions/review/target/save_stimulus_total_task_attempt') ?>',
                method: 'POST',
                data: {
                    client_id,
                    target_id,
                    goal_id,
                    domain_id,
                    session_id,
                    current_phase_id: phase_id,
                    client_probe_set_id,
                    step_data,
                    method
                },
                dataType: 'json',
                success: function(res) {
                    if (res.success === 'Yes') {
                        Swal.fire('Saved', res.message, 'success');
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Server Error', xhr.responseText, 'error');
                }
            });
        }



    });
</script>
<?= $this->endSection() ?>