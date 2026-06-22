<div class="row justify-content-center">
    <div class="col-xxl-9">
        <div class="card">
            <form class="needs-validation" novalidate="" id="program_change_form">
                <div class="card-body border-bottom border-bottom-dashed p-4">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="profile-user mx-auto  mb-3">
                                <h4>Program Change Form</h4>
                            </div>
                            <input type="hidden" name="alert_id" id="alert_id" value="<?= $pg_alert_id ?>">

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="">
                                        <b> Domain: </b><?= $target->domain_code . '-' . $target->domain_name ?>
                                    </div>
                                    <div class="">
                                        <b> Goal:</b> <?= $target->goal_code . ' ' . $target->goal_name ?>
                                    </div>
                                    <div class="">
                                        <b> Target:</b> <?= $target->name ?>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div>
                                <label for="companyAddress">Increase the number of teaching trials</label>
                            </div>
                            <div class="mb-2">
                                <input type="number" class="form-control bg-light border-0" id="c_yes" name="c_yes" minlength="5" maxlength="6" placeholder="" required="" value="">

                            </div>

                        </div>
                        <!--end col-->
                        <div class="col-lg-4 ms-auto" style="padding-top:40px;">

                            <div class="mb-2">
                                <div class="">
                                    <b> Client:</b> <?= $client->name() . ' (' . $client->internal_mrn . ')' ?>
                                </div>
                            </div>

                            <div class="mb-2">
                                <div class="">
                                    <b> Supervisor: </b><?= $session->supervisor_name() ?>
                                </div>
                            </div>
                            <div class="mb-2">
                                <div class="">
                                    <b>Instructor:</b> <?= $session->instructor_name() ?>
                                </div>
                            </div>
                            <div class="mb-2">
                                <div class="">
                                    <b>Program Change Frequency:</b> <?= $changeCount ?>
                                </div>
                            </div>
                            <div class="mb-2">
                                <div class="">
                                    <b>Program Alert Frequency:</b> <?= $alertCount ?>
                                </div>
                            </div>
                            <div class="mb-2">
                                <div class="">
                                    <b>Program Change Date:</b> <?= app_date($session->session_date) ?>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!--end row-->
                </div>

                <div class="card-body p-4">
                    <div class="row g-3">

                        <div class="col-lg-6 col-sm-6">
                            <div>
                                <label for="billingName" class="text-uppercase fw-semibold">Antecedent: Stimulus Control/Motivation</label>
                            </div>
                            <div class="mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="increase_pairing" name="ant[]" value="1">
                                    <label class="form-check-label" for="increase_pairing">
                                        Increase pairing
                                    </label>
                                </div>
                            </div>
                            <div class="mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="reduce_demands" name="ant[]" value="2">
                                    <label class="form-check-label" for="reduce_demands">
                                        Reduce the number of demands (Decrease VR)
                                    </label>
                                </div>
                            </div>
                            <!-- Add more checkboxes -->
                            <div class="mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="intersperse_skills" name="ant[]" value="3">
                                    <label class="form-check-label" for="intersperse_skills">
                                        Provide higher rate of interspersing mastered skills with target skills
                                    </label>
                                </div>
                            </div>
                            <!-- Repeat for other checkboxes -->
                            <div class="mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="response_effort" name="ant[]" value="4">
                                    <label class="form-check-label" for="response_effort">
                                        Decrease the response effort
                                    </label>
                                </div>
                            </div>
                            <div class="mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="reduce_errors" name="ant[]" value="5">
                                    <label class="form-check-label" for="reduce_errors">
                                        Further reduce errors: Modify prompt procedure and prompt fade procedure
                                    </label>
                                </div>
                            </div>
                            <!-- Add more checkboxes -->
                            <!-- Ensure each checkbox has a unique id and incremental value -->
                            <div class="mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="change_pace_instruction" name="ant[]" value="6">
                                    <label class="form-check-label" for="change_pace_instruction">
                                        Change the pace of instruction (ITI)
                                    </label>
                                </div>
                            </div>
                            <!-- Repeat for other checkboxes -->
                            <div class="mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="change_session_time" name="ant[]" value="7">
                                    <label class="form-check-label" for="change_session_time">
                                        Decrease/increase session time
                                    </label>
                                </div>
                            </div>
                            <!-- Add more checkboxes -->
                            <div class="mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="sr_assessment" name="ant[]" value="8">
                                    <label class="form-check-label" for="sr_assessment">
                                        Conduct a Sr+ assessment
                                    </label>
                                </div>
                            </div>
                            <!-- Repeat for other checkboxes -->
                            <!-- Add more checkboxes -->
                            <div class="mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="increase_saliency_sr" name="ant[]" value="9">
                                    <label class="form-check-label" for="increase_saliency_sr">
                                        Increase the saliency of Sr+
                                    </label>
                                </div>
                            </div>
                            <!-- Add more checkboxes -->
                            <div class="mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="change_field_stimuli" name="ant[]" value="10">
                                    <label class="form-check-label" for="change_field_stimuli">
                                        Change the field of the stimuli
                                    </label>
                                </div>
                            </div>
                            <!-- Add more checkboxes -->
                            <div class="mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="increase_teaching_trials" name="ant[]" value="11">
                                    <label class="form-check-label" for="increase_teaching_trials">
                                        Increase the number of teaching trials
                                    </label>
                                </div>
                            </div>
                            <!-- Add more checkboxes -->
                            <!-- Ensure each checkbox has a unique id and incremental value -->
                            <!-- Repeat for other checkboxes -->
                            <div class="mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="change_physical_environment" name="ant[]" value="12">
                                    <label class="form-check-label" for="change_physical_environment">
                                        Change the physical environment
                                    </label>
                                </div>
                            </div>
                            <!-- Repeat for other checkboxes -->
                            <div class="mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="change_aim" name="ant[]" value="13">
                                    <label class="form-check-label" for="change_aim">
                                        Change the aim
                                    </label>
                                </div>
                            </div>
                            <!-- Repeat for other checkboxes -->
                            <!-- Add more checkboxes -->
                            <div class="mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="teach_prerequisites" name="ant[]" value="14">
                                    <label class="form-check-label" for="teach_prerequisites">
                                        Teach pre-requisites skills
                                    </label>
                                </div>
                            </div>
                            <!-- Repeat for other checkboxes -->
                            <div class="mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="decrease_number_goals" name="ant[]" value="15">
                                    <label class="form-check-label" for="decrease_number_goals">
                                        Decrease the number of goals/objectives
                                    </label>
                                </div>
                            </div>
                            <!-- Add more checkboxes -->
                            <!-- Ensure each checkbox has a unique id and incremental value -->
                            <div class="mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="deprivation_reinforcers" name="ant[]" value="16">
                                    <label class="form-check-label" for="deprivation_reinforcers">
                                        Build MO by deprivation of specific reinforcers
                                    </label>
                                </div>
                            </div>
                            <!-- Repeat for other checkboxes -->
                            <div class="mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="change_teaching_procedure" name="ant[]" value="17">
                                    <label class="form-check-label" for="change_teaching_procedure">
                                        Change the teaching procedure
                                    </label>
                                </div>
                            </div>
                            <!-- Repeat for other checkboxes -->
                            <div class="mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="other_antecedent" name="ant[]" value="18">
                                    <label class="form-check-label" for="other_antecedent">
                                        Other:
                                    </label>
                                </div>
                            </div>
                            <div class="mb-2">
                                <textarea class="form-control bg-light border-0" id="other_ant" name="other_antecedent" rows="3" placeholder="" required=""></textarea>
                            </div>
                        </div>

                        <!--end col-->
                        <div class="col-lg-6 col-sm-6">
                            <div>
                                <label for="shippingName" class="text-uppercase fw-semibold">Consequence</label>
                            </div>
                            <div class="mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="reinforcer_1" name="consequence[]" value="1">
                                    <label class="form-check-label" for="reinforcer_1">
                                        Provide more valuable reinforcer
                                    </label>
                                </div>
                            </div>
                            <div class="mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="reinforcer_2" name="consequence[]" value="2">
                                    <label class="form-check-label" for="reinforcer_2">
                                        Provide higher rate of reinforcement (lower VR)
                                    </label>
                                </div>
                            </div>
                            <div class="mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="reinforcer_3" name="consequence[]" value="3">
                                    <label class="form-check-label" for="reinforcer_3">
                                        Reinforce immediately
                                    </label>
                                </div>
                            </div>
                            <div class="mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="reinforcer_4" name="consequence[]" value="4">
                                    <label class="form-check-label" for="reinforcer_4">
                                        Provide greater magnitude of reinforcement
                                    </label>
                                </div>
                            </div>
                            <div class="mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="reinforcer_5" name="consequence[]" value="5">
                                    <label class="form-check-label" for="reinforcer_5">
                                        Reinforce on transfer trials
                                    </label>
                                </div>
                            </div>
                        
                            <div class="mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="reinforcer_7" name="consequence[]" value="7">
                                    <label class="form-check-label" for="reinforcer_7">
                                        Improved implementation of Differential Reinforcement
                                    </label>
                                </div>
                            </div>
                        
                            <div class="mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="reinforcer_9" name="consequence[]" value="9">
                                    <label class="form-check-label" for="reinforcer_9">
                                        Other:
                                    </label>
                                </div>
                            </div>
                            <div class="mb-2">
                                <textarea class="form-control bg-light border-0" id="other_con" name="other_consequence" rows="19" placeholder="" required=""></textarea>
                            </div>
                        </div>

                    </div>
                    <!--end row-->
                </div>

                <div class="card-body p-4">

                    <div class="row g-3">
                        <div class="col-lg-12">
                            <label for="exampleFormControlTextarea1" class="form-label  ">Describe the incorrect response:</label>
                            <textarea class="form-control alert alert-info" id="incorrect_response" name="incorrect_response" placeholder="" rows="3" required=""></textarea>
                        </div>
                        <!--end col-->
                        <div class="col-lg-12">
                            <label for="exampleFormControlTextarea1" class="form-label ">What are the behavioral variables in terms of the basic principles and historical contingencies of the individual that would account for the incorrect response:</label>
                            <textarea class="form-control alert alert-info" id="behavioral_variables" name="behavioral_variables" placeholder="" rows="3" required=""></textarea>
                        </div>
                        <!--end col-->
                        <div class="col-lg-12">
                            <label for="exampleFormControlTextarea1" class="form-label  ">Description of the Program Change:</label>
                            <textarea class="form-control alert alert-info" id="description" name="description" placeholder="" rows="3" required=""></textarea>
                        </div>
                        <!--end col-->
                    </div>
                    <!--end row-->

                    <div class="hstack gap-2 justify-content-end d-print-none mt-4">
                        <button type="submit" class="btn btn-primary"><i class="ri-save-line align-bottom me-1"></i> Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!--end col-->
</div>
<script>
    $(document).ready(function() {
        // Submit form via AJAX
        $('#program_change_form').on('submit', function(e) {
            e.preventDefault(); // Prevent form submission

            // Perform validation
            if (validateForm()) {
                // If validation succeeds, prepare form data
                var formData = $(this).serialize();

                // Send AJAX request
                $.ajax({
                    url: '<?= base_url('sessions/programChange/saveProgramChange') ?>',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.status === 'success') {

                            // Ensure response data structure is correctly accessed
                            const targetId = response.data.targetId;
                            const overrideCriteriaHtml = response.data.overrideCriteriaHtml;
                            const alertFrequencyHtml = response.data.alertFrequencyHtml;

                            // 1. Update Override Criteria in <h5> if present, or add it
                            const targetHeader = $(`#target-header-${targetId}`);
                            if (targetHeader.find('.override-criteria').length > 0) {
                                // Replace existing override criteria
                                targetHeader.find('.override-criteria').replaceWith(overrideCriteriaHtml);
                            } else {
                                // Insert after the title in <h5> if not present
                                targetHeader.append(overrideCriteriaHtml);
                            }

                            // 2. Update or Add Program Alert Frequency <p> if present
                            const alertFrequencyElement = $(`#target-alert-frequency-${targetId}`);
                            if (alertFrequencyElement.length > 0) {
                                // Replace existing alert frequency element
                                alertFrequencyElement.replaceWith(alertFrequencyHtml);
                            } else {
                                // Insert after the first <p> within the same container
                                targetHeader.siblings('p').first().after(alertFrequencyHtml);
                            }

                            // 3. Change the background color of the last clicked cell
                            if (lastClickedCell) {
                                $(lastClickedCell).css('background-color', '#2074BA');
                            }

                            console.log(response.message);
                            bsc.hide();

                            Swal.fire({
                                title: response.statusText, // The status text, e.g., "Success" or "Error"
                                text: response.message, // The message to be shown in the toast
                                icon: response.status === 'success' ? 'success' : 'error', // Set the icon based on the status
                                toast: true, // Enable toast mode
                                position: 'top-end', // Set the position to top-right
                                showConfirmButton: false, // Hide the confirm button
                                timer: 3000, // Show for 3 seconds
                                timerProgressBar: true, // Show progress bar
                                background: '#f9f9f9' // Optional: Customize background color
                            });



                        } else if (response.status === 'error' && response.statusText === 'Validation_Error') {
                            let errors = Object.values(response.validationErrors);
                            displayValidationErrors(errors);

                        } else {
                            showAlert(response.statusText, response.message, response.status);
                        }
                    },
                    error: function(xhr, status, error) {
                        // Handle error response
                        console.error("AJAX Error:", xhr.responseText);
                        showAlert('Error', xhr.responseText, 'error');
                    }
                });
            }
        });

        // Function to validate the form
        function validateForm() {
            var antecedentCheckboxes = $('input[name="ant[]"]:checked');
            var consequenceCheckboxes = $('input[name="consequence[]"]:checked');
            var otherAntecedent = $('#other_ant').val().trim();
            var otherConsequence = $('#other_con').val().trim();
            var incorrectResponse = $('#incorrect_response').val().trim();
            var behavioralVariables = $('#behavioral_variables').val().trim();
            var description = $('#description').val().trim();

            var isIncreaseTeachingTrialsChecked = $('#increase_teaching_trials').is(':checked');
            var cYesValue = $('#c_yes').val().trim();

            // Validate at least one checkbox selected in each section
            if (antecedentCheckboxes.length === 0 || consequenceCheckboxes.length === 0) {
                showAlert('Error', 'Please select at least one checkbox in both sections.', 'error');
                return false;
            }

            // Validate if "Increase the number of teaching trials" is checked, then "c_yes" should be mandatory
            if (isIncreaseTeachingTrialsChecked && (cYesValue === '' || isNaN(cYesValue))) {
                showAlert('Error', 'Please provide a valid number for "Increase the number of teaching trials" when it is selected.', 'error');
                return false;
            }

            // Validate other antecedent text area
            if (antecedentCheckboxes.filter('[value="18"]').prop('checked') && otherAntecedent.length < 3) {
                showAlert('Error', 'Other antecedent text area must have at least 3 characters.', 'error');
                return false;
            }

            // Validate other consequence text area
            if (consequenceCheckboxes.filter('[value="9"]').prop('checked') && otherConsequence.length < 3) {
                showAlert('Error', 'Other consequence text area must have at least 3 characters.', 'error');
                return false;
            }

            // Validate incorrect response field
            if (incorrectResponse === '') {
                showAlert('Error', 'Please provide a description of the incorrect response.', 'error');
                return false;
            }

            // Validate behavioral variables field
            if (behavioralVariables === '') {
                showAlert('Error', 'Please provide behavioral variables.', 'error');
                return false;
            }

            // Validate description field
            if (description === '') {
                showAlert('Error', 'Please provide a description of the program change.', 'error');
                return false;
            }

            // All validations passed
            return true;
        }
    });
</script>