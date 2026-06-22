<div class="row pb-2">
    <div class="col-md-12">
        <?= $infoString ?>
    </div>
</div>
<div id="existing_probe_set_container">
    <div id="existing_probe_sets_table">
        <?= view('ClientProgram/client_existing_probe_sets_table') ?>
    </div>
    <div id="existing_probe_sets_update" style="display: none;">
        <form id="update_probe_set_form" onsubmit="return false;">
            <input type="hidden" name="update_form_goal_id" id="update_form_goal_id" value="">
            <input type="hidden" name="update_form_client_id" id="update_form_client_id" value="">
            <input type="hidden" name="update_form_client_probe_set_id" id="update_form_client_probe_set_id" value="">
            <input type="hidden" name="update_form_probe_set_id" id="update_form_probe_set_id" value="">
            <input type="hidden" name="update_form_combination_id" id="update_form_combination_id" value="">
            <div class="row">
                <div class="col-md-12" id="editProbeSetContainer"></div>
                <div class="col-md-12" id="editRulesContainer"></div>
                <!-- Update and Close Buttons -->
                <div class="col-md-12 text-center">
                    <button class="btn btn-primary" id="btnUpdateProbeSet"><i class="ri-save-line"></i>Update</button>
                    <button class="btn btn-light" id="btnCloseEdit"><i class="ri-close-line"></i>Close</button>
                </div>
            </div>
        </form>
    </div>

</div>

<script>
    $(document).ready(function() {
        // Event listener for the "Edit" button
        $(document).on('click', '.edit-probe-set', function() {
            var clientId = $(this).data('client-id');
            var goalId = $(this).data('goal-id');
            var probeSetId = $(this).data('probe-set-id');
            var clientProbeSetID = $(this).data('client-probe-set-id');
            var combinationId = $(this).data('combination-id');

            $.ajax({
                url: '<?= base_url("client-program/goal/load-client-existing-probe-sets-edit-form") ?>',
                type: 'post',
                data: {
                    client_id: clientId,
                    goal_id: goalId,
                    probe_set_id: probeSetId,
                    client_probe_set_id: clientProbeSetID,
                    combination_id: combinationId
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#update_form_goal_id').val(goalId);
                        $('#update_form_client_id').val(clientId);
                        $('#update_form_client_probe_set_id').val(clientProbeSetID);
                        $('#update_form_probe_set_id').val(probeSetId);
                        $('#update_form_combination_id').val(combinationId);

                        $('#editProbeSetContainer').html(response.data.probeSetHtml);
                        $('#editRulesContainer').html(response.data.rulesHtml);

                        // Hide the table and show the edit section
                        $('#existing_probe_sets_table').hide();
                        $('#existing_probe_sets_update').show();
                    } else {
                        alert('Failed to load probe set details.');
                    }
                },
                error: function(jqXHR, textStatus, error) {
                    alert('Request failed: ' + textStatus + ' ' + error);
                }
            });
        });

        // Event listener for the "Close" button
        $(document).on('click', '#btnCloseEdit', function() {
            // Clear the content in the edit containers
            $('#editProbeSetContainer').html('');
            $('#editRulesContainer').html('');

            $('#update_form_goal_id').val('');
            $('#update_form_client_id').val('');
            $('#update_form_client_probe_set_id').val('');
            $('#update_form_probe_set_id').val('');
            $('#update_form_combination_id').val('');


            // Hide the edit section and show the table
            $('#existing_probe_sets_update').hide();
            $('#existing_probe_sets_table').show();
        });

        // Event listener for the "Update" button
        $(document).on('click', '#btnUpdateProbeSet', function(e) {
            e.preventDefault();
            if ($('#editProbeSetContainer').is(':empty') || $('#editRulesContainer').is(':empty')) {
                showAlert('Error', 'Please select a probe set and phase combination to proceed.', 'error');
                return;
            }

            var formData = $('#update_probe_set_form').serialize();

            $.ajax({
                url: '<?= base_url("client-program/goal/update-client-existing-probe-set") ?>',
                type: 'post',
                data: formData,
                beforeSend: function(xhr) {
                    $('#btnUpdateProbeSet').prop("disabled", true); // Disable the button
                },
                success: function(response) {
                    if (response.status == 'success') {
                        $('#btnCloseEdit').click(); // Close the edit form
                        showAlert('Success', 'Probe set and rules have been updated successfully', 'success');
                    } else {
                        showAlert('Error', response.message, 'error');
                    }
                },
                error: function(jqXHR, textStatus, error) {
                    showAlert('Error', "Request failed: " + textStatus + '<br>' + error, 'error');
                },
                complete: function() {
                    $('#btnUpdateProbeSet').prop("disabled", false); // Re-enable the button after request completion
                }
            });
        });

        // Event listener for the "Activate" button
        $(document).on('click', '.activate-probe-set', function() {
            var clientProbeSetId = $(this).data('id');
            var clientId = $(this).data('client-id');
            var goalId = $(this).data('goal-id');

            $.ajax({
                url: '<?= base_url("client-program/goal/activate-client-probe-set") ?>',
                type: 'post',
                data: {
                    client_probe_set_id: clientProbeSetId,
                    client_id: clientId,
                    goal_id: goalId
                },
                success: function(response) {
                    if (response.status === 'success') {
                        // Replace the existing table with the updated one
                        $('#existing_probe_sets_table').html(response.html);
                        showAlert('Success', 'Probe set activated successfully.', 'success');
                    } else {
                        showAlert('Error', response.message, 'error');
                    }
                },
                error: function(jqXHR, textStatus, error) {
                    showAlert('Error', "Request failed: " + textStatus + ' ' + error, 'error');
                }
            });
        });

        // Event listener for the "Delete" button
        $(document).on('click', '.delete-probe-set', function() {
            var clientProbeSetId = $(this).data('id');
            var clientId = $(this).data('client-id');
            var goalId = $(this).data('goal-id');

            $.ajax({
                url: '<?= base_url("client-program/goal/delete-client-probe-set") ?>',
                type: 'post',
                data: {
                    client_probe_set_id: clientProbeSetId,
                    client_id: clientId,
                    goal_id: goalId
                },
                success: function(response) {
                    if (response.status === 'success') {
                        // Replace the entire table, not just the rows
                        $('#existing_probe_sets_table').html(response.html);
                        showAlert('Success', 'Probe set deleted successfully.', 'success');
                    } else {
                        showAlert('Error', response.message, 'error');
                    }
                },
                error: function(jqXHR, textStatus, error) {
                    showAlert('Error', "Request failed: " + textStatus + ' ' + error, 'error');
                }
            });
        });

    });
</script>