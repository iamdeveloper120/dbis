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
<?= view('ClientSessionsReview/_common_header', ['section_name' => 'Problem Behavior']) ?>
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body border-bottom-dashed border-bottom">
                <?= view('ClientSessionsReview/_tabs', ['tab' => 'pb_data']) ?>
                <div class="tab-content">
                    <div class="tab-pane fade show active" role="tabpanel" aria-labelledby="info-tab">
                        <div class="table-responsive">
                            <table class="table table-bordered" style="width: 100%;" id="pb_dataTable">
                                <thead>
                                    <tr>
                                        <th class="dt-nowrap">Start Time</th>
                                        <th class="dt-nowrap">End Time</th>
                                        <th>Antecedent (A)</th>
                                        <th>Behavior (B)</th>
                                        <th>Consequence (C)</th>
                                        <th>Comments</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (isset($pbDailyData) && !empty($pbDailyData)) : ?>
                                        <?php foreach ($pbDailyData as $data) : ?>
                                            <tr>
                                                <td class="dt-nowrap"><?= esc($data['start_time']) ?></td>
                                                <td class="dt-nowrap"><?= esc($data['end_time']) ?></td>

                                                <!-- Antecedent (A): Display directly -->
                                                <td class=""><?= esc($data['antecedent']) ?></td>

                                                <!-- Behavior (B): Parse JSON and display behaviors with intensities -->
                                                <td class="">
                                                    <?php
                                                    $existing_behaviors = json_decode($data['behavior'], true); // Decode the JSON string
                                                    $behavior_display = [];
                                                    if ($existing_behaviors) {
                                                        foreach ($existing_behaviors as $behavior) {
                                                            //$behavior_display[] = esc($behavior['behavior']) . " (Intensity: " . esc($behavior['intensity']) . ")";
                                                            $behavior_display[] = esc($behavior['behavior']);
                                                        }
                                                    }
                                                    echo implode(', ', $behavior_display); // Display behaviors with intensities
                                                    ?>
                                                </td>

                                                <!-- Consequence (C): Display directly -->
                                                <td class=""><?= esc($data['consequence']) ?></td>
                                                <td class=""><?= esc($data['abc_comments']) ?></td>

                                                <td class="dt-nowrap">
                                                    <button data-duration-id="<?= esc($data['duration_id']); ?>" data-record-id="<?= esc($data['record_id']); ?>" type="button" class="btn btn-sm btn-outline-warning update">
                                                        <i class="ri-edit-line align-bottom me-1"></i>Edit
                                                    </button>
                                                    <button data-duration-id="<?= esc($data['duration_id']); ?>" data-record-id="<?= esc($data['record_id']); ?>" type="button" class="btn btn-sm btn-outline-danger delete">
                                                        <i class="ri-delete-bin-line align-bottom me-1"></i>Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>


            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section("page_modal") ?>
<!-- Update Modal -->
<div class="modal fade" id="add_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title">Add Problem Behavior Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createPBRecordForm">
                    <input type="hidden" name="a_session_id" id="a_session_id" value="<?= $session->id ?>">
                    <div class="row">
                        <!-- Start Time and End Time -->
                        <div class="col-md-6 mb-3">
                            <label for="start_time">Start Time</label>
                            <input type="text" name="a_start_time" id="a_start_time" class="form-control flatpickr">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="end_time">End Time</label>
                            <input type="text" name="a_end_time" id="a_end_time" class="form-control flatpickr">
                        </div>

                        <!-- Antecedent Section -->
                        <div class="col-md-4 mb-3">
                            <div class="card border card-border-primary">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Antecedent</h6>
                                </div>
                                <div class="card-body">
                                    <div id="a_antecedent-list">
                                        <div class="form-check" style="padding-left: 0px;">
                                            <?php foreach ($antecedents as $index => $item): ?>
                                                <div class="form-check">
                                                    <input id="a_antecedent_<?= $index ?>" class="form-check-input" type="radio" name="a_antecedent" value="<?= $item['value'] ?>">
                                                    <label for="a_antecedent_<?= $index ?>" class="form-check-label"><?= $item['value'] ?></label>
                                                </div>
                                            <?php endforeach; ?>
                                            <div class="form-check">
                                                <input id="a_antecedent_other_option" class="form-check-input" type="radio" name="a_antecedent" value="Other">
                                                <label for="a_antecedent_other_option" class="form-check-label">Other</label>
                                            </div>
                                            <input name="a_antecedent_other" id="a_antecedent_other" class="form-control mt-2" style="display:none;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Behavior Section -->
                        <div class="col-md-4 mb-3">
                            <div class="card border card-border-primary">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Behavior</h6>
                                </div>
                                <div class="card-body">
                                    <div id="a-behavior-list" class="d-flex flex-wrap">
                                        <?php foreach ($behaviors as $index => $item): ?>
                                            <div class="form-check mb-2" style="flex: 1 0 50%; max-width: 50%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                <input id="a_behavior_<?= $index ?>" class="form-check-input" type="checkbox" name="a_behavior[]" value="<?= $item['value'] ?>">
                                                <label for="a_behavior_<?= $index ?>" class="form-check-label"><?= $item['value'] ?></label>
                                            </div>
                                            <input type="hidden" name="a_intensity[]" value="1">

                                        <?php endforeach; ?>
                                        <!-- Add dynamic behaviors -->
                                    </div>
                                    <button type="button" id="a-add-behavior" class="btn btn-sm btn-secondary mt-2">Add More Behavior</button>
                                </div>
                            </div>
                        </div>

                        <!-- Consequence Section -->
                        <div class="col-md-4 mb-3">
                            <div class="card border card-border-primary">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Consequence</h6>
                                </div>
                                <div class="card-body">
                                    <div id="a_consequence-list">
                                        <div class="form-check" style="padding-left: 0px;">
                                            <?php foreach ($consequences as $index => $item): ?>
                                                <div class="form-check">
                                                    <input id="a_consequence_<?= $index ?>" class="form-check-input" type="radio" name="a_consequence" value="<?= $item['value'] ?>">
                                                    <label for="a_consequence_<?= $index ?>" class="form-check-label"><?= $item['value'] ?></label>
                                                </div>
                                            <?php endforeach; ?>
                                            <div class="form-check">
                                                <input id="a_consequence_other_option" class="form-check-input" type="radio" name="a_consequence" value="Other">
                                                <label for="a_consequence_other_option" class="form-check-label">Other</label>
                                            </div>
                                            <input name="a_consequence_other" id="a_consequence_other" class="form-control mt-2" style="display:none;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Comment Section -->
                        <div class="col-md-12 col-sm-12 mb-3">
                            <div class="card border card-border-primary">
                                <div class="card-body">
                                    <div class="form-group" id="comments-section">
                                        <div class="row">
                                            <div class="col-lg-12 col-12">
                                                <!-- Text Area -->
                                                <div class="mb-3">
                                                    <label for="a_abc_comments" class="form-label">Comments</label>
                                                    <textarea name="a_abc_comments" id="a_abc_comments" class="form-control" rows="3"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btn_add">Save</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="update_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title">Edit Problem Behavior Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="pbRecordForm">
                    <input type="hidden" name="pb_duration_id" id="pb_duration_id">
                    <input type="hidden" name="pb_record_id" id="pb_record_id">
                    <div class="row">
                        <!-- Start Time and End Time -->
                        <div class="col-md-6 mb-3">
                            <label for="start_time">Start Time</label>
                            <input type="text" name="start_time" id="start_time" class="form-control flatpickr">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="end_time">End Time</label>
                            <input type="text" name="end_time" id="end_time" class="form-control flatpickr">
                        </div>

                        <!-- Antecedent Section -->
                        <div class="col-md-4 mb-3">
                            <div class="card border card-border-primary">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Antecedent</h6>
                                </div>
                                <div class="card-body">
                                    <div id="antecedent-list">
                                        <div class="form-check" style="padding-left: 0px;">
                                            <?php foreach ($antecedents as $index => $item): ?>
                                                <div class="form-check">
                                                    <input id="antecedent_<?= $index ?>" class="form-check-input" type="radio" name="antecedent" value="<?= $item['value'] ?>">
                                                    <label for="antecedent_<?= $index ?>" class="form-check-label"><?= $item['value'] ?></label>
                                                </div>
                                            <?php endforeach; ?>
                                            <div class="form-check">
                                                <input id="antecedent_other_option" class="form-check-input" type="radio" name="antecedent" value="Other">
                                                <label for="antecedent_other_option" class="form-check-label">Other</label>
                                            </div>
                                            <input name="antecedent_other" id="antecedent_other" class="form-control mt-2" style="display:none;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Behavior Section -->
                        <div class="col-md-4 mb-3">
                            <div class="card border card-border-primary">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Behavior</h6>
                                </div>
                                <div class="card-body">
                                    <div id="behavior-list" class="d-flex flex-wrap">
                                        <?php foreach ($behaviors as $index => $item): ?>
                                            <div class="form-check mb-2" style="flex: 1 0 50%; max-width: 50%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                <input id="behavior_<?= $index ?>" class="form-check-input" type="checkbox" name="behavior[]" value="<?= $item['value'] ?>">
                                                <label for="behavior_<?= $index ?>" class="form-check-label"><?= $item['value'] ?></label>
                                            </div>
                                            <input type="hidden" name="intensity[]" value="1">

                                        <?php endforeach; ?>
                                        <!-- Add dynamic behaviors -->
                                    </div>
                                    <button type="button" id="add-behavior" class="btn btn-sm btn-secondary mt-2">Add More Behavior</button>
                                </div>
                            </div>
                        </div>

                        <!-- Consequence Section -->
                        <div class="col-md-4 mb-3">
                            <div class="card border card-border-primary">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Consequence</h6>
                                </div>
                                <div class="card-body">
                                    <div id="consequence-list">
                                        <div class="form-check" style="padding-left: 0px;">
                                            <?php foreach ($consequences as $index => $item): ?>
                                                <div class="form-check">
                                                    <input id="consequence_<?= $index ?>" class="form-check-input" type="radio" name="consequence" value="<?= $item['value'] ?>">
                                                    <label for="consequence_<?= $index ?>" class="form-check-label"><?= $item['value'] ?></label>
                                                </div>
                                            <?php endforeach; ?>
                                            <div class="form-check">
                                                <input id="consequence_other_option" class="form-check-input" type="radio" name="consequence" value="Other">
                                                <label for="consequence_other_option" class="form-check-label">Other</label>
                                            </div>
                                            <input name="consequence_other" id="consequence_other" class="form-control mt-2" style="display:none;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Comment Section -->
                        <div class="col-md-12 col-sm-12 mb-3">
                            <div class="card border card-border-primary">
                                <div class="card-body">
                                    <div class="form-group" id="comments-section">
                                        <div class="row">
                                            <div class="col-lg-12 col-12">
                                                <!-- Text Area -->
                                                <div class="mb-3">
                                                    <label for="abc_comments" class="form-label">Comments</label>
                                                    <textarea name="abc_comments" id="abc_comments" class="form-control" rows="3"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btn_update">Update</button>
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
        table = $('#pb_dataTable').DataTable({
            lengthChange: false,
            "ordering": false,
            lengthMenu: [
                [10, 25, 50, -1],
                ['10 rows', '25 rows', '50 rows', 'Show all']
            ],
            layout: {
                topStart: {
                    buttons: [{
                            text: '<i class="ri-add-line align-bottom me-1"></i>Add PB Manually',
                            className: 'btn btn-soft-info waves-effect waves-light material-shadow-none',
                            attr: {
                                id: 'add_executed_session_pb_manually'
                            },
                            action: function(e, dt, node, config) {
                                // Add your action for the button here
                                show_add_pb_canvas();
                            }
                        }, // If status is NOT 2, no button is added

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

        function show_add_pb_canvas() {
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
            $('#add_modal').modal('show');

        };
        /***************************************************************************************** */
        // Handle the click event on the Edit button
        $("#pb_dataTable").on('click', '.update', function(e) {
            var btn = $(this);
            var record_id = $(this).attr('data-record-id');
            var duration_id = $(this).attr('data-duration-id');
            current_row = $(this).parents('tr');
            if (current_row.hasClass('child')) {
                current_row = current_row.prev();
            }

            // Fetch the record data via AJAX
            $.ajax({
                url: '<?php echo base_url() ?>/sessions/review/getPBRecord',
                type: 'post',
                data: {
                    "record_id": record_id,
                    "duration_id": duration_id
                },
                beforeSend: function() {
                    btn.prop("disabled", true);
                },
                success: function(response) {
                    if (response.status === 'success') {
                        // Fill the modal fields with the fetched data
                        $('#update_modal #pb_duration_id').val(response.data.pb_duration.id);
                        $('#update_modal #start_time').val(response.data.pb_duration.start_time);
                        $('#update_modal #end_time').val(response.data.pb_duration.end_time);

                        if (response.data.pb_record) {
                            $('#update_modal #abc_comments').val(response.data.pb_record.abc_comments);
                            // Set antecedent
                            // Loop through the antecedent radio buttons and check the appropriate one
                            $('#update_modal #pb_record_id').val(response.data.pb_record.id);
                            let antecedent = response.data.pb_record.antecedent;
                            let antecedentFound = false;

                            $('input[name="antecedent"]').each(function() {
                                let value = $(this).val();
                                if (value === antecedent) {
                                    $(this).prop('checked', true);
                                    antecedentFound = true;
                                } else {
                                    $(this).prop('checked', false);
                                }
                            });

                            // If the antecedent was not found in the predefined list, treat it as 'Other'
                            if (!antecedentFound) {
                                $('#antecedent_other').val(antecedent).show(); // Set the antecedent as "Other" and show the text box
                                $('input[name="antecedent"][value="Other"]').prop('checked', true); // Check the 'Other' option
                            } else {
                                $('#antecedent_other').hide().val(''); // Hide the 'Other' text box and clear its value
                            }


                            // Set behaviors and intensities
                            // Set behaviors and intensities
                            let behaviors = JSON.parse(response.data.pb_record.behavior);
                            let behaviorFound;

                            // Loop through each behavior from the response data
                            behaviors.forEach(function(behaviorObj) {
                                behaviorFound = false; // Reset flag for each behavior

                                // Check if the behavior exists in the predefined list
                                $('input[name="behavior[]"]').each(function() {
                                    let value = $(this).val();
                                    if (value === behaviorObj.behavior) {
                                        // If the behavior is found in the predefined list, check it and set the intensity
                                        $(this).prop('checked', true);
                                        //$('input[name="intensity[' + behaviorObj.behavior + ']"]').val(behaviorObj.intensity).show();
                                        behaviorFound = true;
                                    }
                                });

                                // If the behavior is not found, dynamically add it to the behavior list
                                if (!behaviorFound) {
                                    $('#behavior-list').append(`
            <div class="row mb-2 behavior-row">
                <div class="col-8">
                    <input type="text" name="behavior[]" class="form-control form-control-sm" value="${behaviorObj.behavior}" placeholder="Enter behavior">
                </div>
                <div class="col-4">
                    <button type="button" class="btn btn-danger btn-sm remove-behavior">Remove</button>
                </div>
                 <input type="hidden" name="intensity[]" value="1"> <!-- Default intensity value -->
            </div>
        `);
                                }
                            });


                            // Set consequence
                            // Loop through the consequence radio buttons and check the appropriate one
                            let consequence = response.data.pb_record.consequence;
                            let consequenceFound = false;

                            $('input[name="consequence"]').each(function() {
                                let value = $(this).val();
                                if (value === consequence) {
                                    $(this).prop('checked', true);
                                    consequenceFound = true;
                                } else {
                                    $(this).prop('checked', false);
                                }
                            });

                            // If the consequence was not found in the predefined list, treat it as 'Other'
                            if (!consequenceFound) {
                                $('#consequence_other').val(consequence).show(); // Set the consequence as "Other" and show the text box
                                $('input[name="consequence"][value="Other"]').prop('checked', true); // Check the 'Other' option
                            } else {
                                $('#consequence_other').hide().val(''); // Hide the 'Other' text box and clear its value
                            }

                        }
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

                        // Show the modal
                        $('#update_modal').modal('show');
                    } else {
                        showAlert(response.statusText, response.message, 'error');
                    }
                },
                complete: function() {
                    btn.prop("disabled", false);
                }
            });
        });

        $('#update_modal').on('hidden.bs.modal', function() {
            // Reset the form fields to clear any user input
            $('#pbRecordForm')[0].reset();

            // Clear dynamically added behaviors but keep the predefined ones
            $('#behavior-list .behavior-row').each(function() {
                let isPredefined = $(this).find('input[type="checkbox"]').length > 0; // If it has a predefined checkbox
                if (!isPredefined) {
                    $(this).remove(); // Remove only dynamically added behaviors
                }
            });

            // Hide 'Other' text areas and clear their values
            $('#antecedent_other, #consequence_other').hide().val('');

            // Uncheck the previously checked antecedent and consequence radio buttons and check the default
            $('input[name="antecedent"], input[name="consequence"]').prop('checked', false);

            // Hide all intensity select boxes related to predefined behaviors
            $('select[name^="intensity"]').hide();
        });

        $('#add_modal').on('hidden.bs.modal', function() {
            // Reset the form fields to clear any user input
            $('#createPBRecordForm')[0].reset();

            // Clear dynamically added behaviors but keep the predefined ones
            $('#a-behavior-list .a-behavior-row').each(function() {
                let isPredefined = $(this).find('input[type="checkbox"]').length > 0; // If it has a predefined checkbox
                if (!isPredefined) {
                    $(this).remove(); // Remove only dynamically added behaviors
                }
            });

            // Hide 'Other' text areas and clear their values
            $('#a_antecedent_other, #a_consequence_other').hide().val('');

            // Uncheck the previously checked antecedent and consequence radio buttons and check the default
            $('input[name="a_antecedent"], input[name="a_consequence"]').prop('checked', false);

            // Hide all intensity select boxes related to predefined behaviors
            $('select[name^="a_intensity"]').hide();
        });
        /***************************************************************************************** */
        // Save PB record form
        $('#btn_add').on('click', function(e) {
            e.preventDefault();

            $.ajax({
                type: 'POST',
                url: '<?= site_url('sessions/review/createPBRecord') ?>',
                data: $('#createPBRecordForm').serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success === 'Yes') {
                        // Get the updated record from the response 
                        let new_record = response.record;
                        // Handle behaviors as JSON and display them with intensities
                        let behaviors = JSON.parse(new_record.behavior);
                        let behavior_display = [];
                        behaviors.forEach(function(behaviorObj) {
                            behavior_display.push(behaviorObj.behavior); // Just display the behavior names, handle intensities if needed
                        });
                        behavior_display = behavior_display.join(', '); // Concatenate multiple behaviors                        

                        // Update the current row with new data
                        table.row.add([
                            new_record.start_time,
                            new_record.end_time,
                            new_record.antecedent,
                            behavior_display,
                            new_record.consequence,
                            new_record.abc_comments,
                            `<button data-duration-id="${new_record.duration_id}" data-record-id="${new_record.record_id}" type="button" class="btn btn-sm btn-outline-warning update"><i class="ri-edit-line align-bottom me-1"></i>Edit</button>
     <button data-duration-id="${new_record.duration_id}" data-record-id="${new_record.record_id}" type="button" class="btn btn-sm btn-outline-danger delete"><i class="ri-delete-bin-line align-bottom me-1"></i>Delete</button>`
                        ]).draw(false); // Redraw the table without affecting the pagination or ordering


                        $('#add_modal').modal('hide');
                        Toast.fire({
                            icon: "success",
                            title: "" + response.message
                        });
                    } else {
                        let errors = Object.values(response.message);
                        displayValidationErrors(errors);
                    }
                },
                error: function(xhr, status, error) {
                    showAlert(xhr.status, "Request failed: " + status + '<br>' + error, 'error');
                }
            });
        });
        /***************************************************************************************** */
        // Save PB record form
        $('#btn_update').on('click', function(e) {
            e.preventDefault();

            $.ajax({
                type: 'POST',
                url: '<?= site_url('sessions/review/updatePBRecord') ?>',
                data: $('#pbRecordForm').serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success === 'Yes') {
                        // Get the updated record from the response 
                        let updated_record = response.updated_record;
                        // Handle behaviors as JSON and display them with intensities
                        let behaviors = JSON.parse(updated_record.behavior);
                        let behavior_display = [];
                        behaviors.forEach(function(behaviorObj) {
                            behavior_display.push(behaviorObj.behavior); // Just display the behavior names, handle intensities if needed
                        });
                        behavior_display = behavior_display.join(', '); // Concatenate multiple behaviors                        

                        // Update the current row with new data
                        table.row(current_row).data([
                            updated_record.start_time,
                            updated_record.end_time,
                            updated_record.antecedent,
                            behavior_display,
                            updated_record.consequence,
                             updated_record.abc_comments,
                            `<button data-duration-id="${updated_record.duration_id}" data-record-id="${updated_record.record_id}" type="button" class="btn btn-sm btn-outline-warning update"><i class="ri-edit-line align-bottom me-1"></i>Edit</button>
     <button data-duration-id="${updated_record.duration_id}" data-record-id="${updated_record.record_id}" type="button" class="btn btn-sm btn-outline-danger delete"><i class="ri-delete-bin-line align-bottom me-1"></i>Delete</button>`
                        ]).draw(false); // Redraw the table without affecting the pagination or ordering

                        $('#update_modal').modal('hide');
                        Toast.fire({
                            icon: "success",
                            title: "" + response.message
                        });
                    } else {
                        let errors = Object.values(response.message);
                        displayValidationErrors(errors);
                    }
                },
                error: function(xhr, status, error) {
                    showAlert(xhr.status, "Request failed: " + status + '<br>' + error, 'error');
                }
            });
        });
        /***************************************************************************************** */
        $("#pb_dataTable").on('click', '.delete', function(e) {
            var record_id = $(this).attr('data-record-id');
            var duration_id = $(this).attr('data-duration-id');
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
                        url: '<?php echo base_url() ?>sessions/review/deletePBRecord',
                        type: 'post',
                        data: {
                            "record_id": record_id,
                            "duration_id": duration_id,
                        },
                        beforeSend: function(xhr) {

                        }
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
                    current_row = ''
                }
            });

        });

        // Show/Hide 'Other' text areas dynamically
        $('input[name="antecedent"]').on('change', function() {
            if ($(this).val() === 'Other') {
                $('#antecedent_other').show();
            } else {
                $('#antecedent_other').hide();
            }
        });

        $('input[name="consequence"]').on('change', function() {
            if ($(this).val() === 'Other') {
                $('#consequence_other').show();
            } else {
                $('#consequence_other').hide();
            }
        });

        $('#add-behavior').on('click', function() {
            $('#behavior-list').append(`
            <div class="row mb-2 behavior-row">
                <div class="col-8">
                    <input type="text" name="behavior[]" class="form-control form-control-sm" placeholder="Enter behavior">
                </div>
                <div class="col-4">
                    <button type="button" class="btn btn-danger btn-sm remove-behavior">Remove</button>
                </div>
                 <input type="hidden" name="intensity[]" value="1"> <!-- Default intensity value -->
            </div>
        `);
        });
        // Remove dynamically added behavior
        $("#update_modal").on('click', '.remove-behavior', function() {
            $(this).closest('.behavior-row').remove();
        });

        /*************************************** */
        // Show/Hide 'Other' text areas dynamically
        $('input[name="a_antecedent"]').on('change', function() {
            if ($(this).val() === 'Other') {
                $('#a_antecedent_other').show();
            } else {
                $('#a_antecedent_other').hide();
            }
        });

        $('input[name="a_consequence"]').on('change', function() {
            if ($(this).val() === 'Other') {
                $('#a_consequence_other').show();
            } else {
                $('#a_consequence_other').hide();
            }
        });

        $('#a-add-behavior').on('click', function() {
            $('#a-behavior-list').append(`
            <div class="row mb-2 a-behavior-row">
                <div class="col-8">
                    <input type="text" name="a_behavior[]" class="form-control form-control-sm" placeholder="Enter behavior">
                </div>
                <div class="col-4">
                    <button type="button" class="btn btn-danger btn-sm a-remove-behavior">Remove</button>
                </div>
                 <input type="hidden" name="a_intensity[]" value="1"> <!-- Default intensity value -->
            </div>
        `);
        });
        // Remove dynamically added behavior
        $("#add_modal").on('click', '.a-remove-behavior', function() {
            $(this).closest('.a-behavior-row').remove();
        });
        /**************************************** */

    });
</script>
<?= $this->endSection() ?>