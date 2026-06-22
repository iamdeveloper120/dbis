<div id="probe_set_container">
    <form id="probe_set_form" onsubmit="return false;">
        <input type="hidden" name="goal_id" value="<?= $goalId ?>">
        <input type="hidden" name="client_id" value="<?= $clientId ?>">
        <div class="row pb-2">
            <div class="col-md-12">
                <?=  $infoString ?> 
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <select name="probe_set_id" id="probe_set_dropdown" class="form-control">
                        <option value="">Select Probe Set</option>
                        <?php foreach ($probeSets as $probeSet) : ?>
                            <option value="<?= $probeSet['id'] ?>"><?= $probeSet['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <select name="combination_id" id="combination_dropdown" class="form-control">
                        <option value="">Select Phase Combination</option>
                    </select>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-12" id="probeSetContainer"></div>
        </div>

        <div class="row">
            <div class="col-md-12" id="rulesContainer"></div>
        </div>

        <div class="row mt-3">
            <div class="col-md-12 text-right">
                <button type="button" id="save_probe_set" class="btn btn-primary">Save Probe Set and Rules</button>
            </div>
        </div>
    </form>
</div>

<script>
    $(document).ready(function() {
        $('#probe_set_dropdown').on('change', function(e) {
            var probe_set_id = $(this).val();
            let goal_id = <?= $goalId ?>;
            let client_id = <?= $clientId ?>;
            if (probe_set_id != '') {
                $.ajax({
                    url: '<?php echo base_url() ?>client-program/goal/get-probe-set-phase-combinations',
                    type: 'post',
                    data: {
                        "probe_set_id": probe_set_id,
                        "goal_id": goal_id,
                        "client_id": client_id,
                    },
                    beforeSend: function(xhr) {
                        $('#probe_set_dropdown').prop("disabled", true);
                        $("#combination_dropdown").prop("disabled", true);
                    },
                    success: function(response) {
                        if (response.status == 'success') {
                            var combinations = response.data;
                            var combinationDropdown = $('#combination_dropdown');
                            combinationDropdown.empty();
                            combinationDropdown.append('<option value="">Select Phase Combination</option>');
                            $.each(combinations, function(index, combination) {
                                combinationDropdown.append('<option value="' + combination.id + '">' + combination.name + '</option>');
                            });
                            combinationDropdown.trigger('change');
                        } else {
                            showAlert(response.statusText, response.message, response.status);
                        }
                    },
                    error: function(jqXHR, textStatus, error) {
                        showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
                    },
                    complete: function() {
                        $('#probe_set_dropdown').prop("disabled", false);
                        $("#combination_dropdown").prop("disabled", false);
                    }
                });
            } else {
                $('#probeSetContainer').empty();
                $('#rulesContainer').empty();
            }
        });
        /***************************************************************************************** */

        $('#combination_dropdown').on('change', function(e) {
            var probe_set_id = $('#probe_set_dropdown').val();
            var combination_id = $('#combination_dropdown').val();
            let goal_id = <?= $goalId ?>;
            let client_id = <?= $clientId ?>;
            if (combination_id != '') {
                $.ajax({
                    url: '<?php echo base_url() ?>client-program/goal/get-probe-set-detail-and-rules',
                    type: 'post',
                    data: {
                        "probe_set_id": probe_set_id,
                        "combination_id": combination_id,
                        "goal_id": goal_id,
                        "client_id": client_id,
                    },
                    beforeSend: function(xhr) {
                        $('#probe_set_dropdown').prop("disabled", true);
                        $("#combination_dropdown").prop("disabled", true);
                    },
                    success: function(response) {
                        if (response.status == 'success') {
                            $('#probeSetContainer').html(response.data.probeSetHtml);
                            $('#rulesContainer').html(response.data.rulesHtml);
                        } else {
                            showAlert(response.statusText, response.message, response.status);
                        }
                    },
                    error: function(jqXHR, textStatus, error) {
                        showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
                    },
                    complete: function() {
                        $('#probe_set_dropdown').prop("disabled", false);
                        $("#combination_dropdown").prop("disabled", false);
                    }
                });
            } else {
                $('#probeSetContainer').empty();
                $('#rulesContainer').empty();
            }
        });

        /***************************************************************************************** */

        $('#save_probe_set').on('click', function(e) {
            e.preventDefault();
            if ($('#probeSetContainer').is(':empty') || $('#rulesContainer').is(':empty')) {
                showAlert('Error', 'Please select a probe set and phase combination to proceed.', 'error');
                return;
            }

            var formData = $('#probe_set_form').serialize();
            $.ajax({
                url: '<?php echo base_url() ?>client-program/goal/save-probe-set-and-rules',
                type: 'post',
                data: formData,
                beforeSend: function(xhr) {
                    $('#save_probe_set').prop("disabled", true); // Disable the button
                },
                success: function(response) {
                    if (response.status == 'success') {
                        showAlert('Success', 'Probe set and rules have been saved successfully', 'success');
                    } else {
                        showAlert('Error', response.message, 'error');
                    }
                },
                error: function(jqXHR, textStatus, error) {
                    showAlert('Error', "Request failed: " + textStatus + '<br>' + error, 'error');
                },
                complete: function() {
                    $('#save_probe_set').prop("disabled", false); // Re-enable the button after request completion
                }
            });
        });
        /***************************************************************************************** */

    });
</script>