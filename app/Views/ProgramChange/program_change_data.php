<style>
    .form-check-input:disabled~.form-check-label,
    .form-check-input[disabled]~.form-check-label {
        cursor: default;
        opacity: 1;
    }
</style>
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
                                <input type="number" class="form-control bg-light border-0" id="c_yes" name="c_yes" minlength="5" maxlength="6" placeholder="" required="" value="<?= $program_change->consecutive_criteria ? $program_change->consecutive_criteria : "" ?>">

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
                            <div class="mb-2">
                                <div class="">
                                    <b> Program changed by</b> <?= $program_change->first_name . " " .  $program_change->last_name ?> <b>at</b> <?= app_date($program_change->created_at, true) ?>
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
                                <label for="billingName" class="text-uppercase fw-semibold">Antecedent</label>
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
                                <textarea class="form-control bg-light border-0" id="other_ant" name="other_antecedent" rows="3" placeholder="" disabled><?= $program_change->other_ant ?></textarea>
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
                                <textarea class="form-control bg-light border-0" id="other_con" name="other_consequence" rows="19" placeholder="" disabled><?= $program_change->other_con ?></textarea>
                            </div>
                        </div>

                    </div>
                    <!--end row-->
                </div>

                <div class="card-body p-4">

                    <div class="row g-3">
                        <div class="col-lg-12">
                            <label for="exampleFormControlTextarea1" class="form-label  ">Describe the incorrect response:</label>
                            <textarea class="form-control alert alert-info" id="incorrect_response" name="incorrect_response" placeholder="" rows="3" disabled><?= $program_change->incorrect_response ?></textarea>
                        </div>
                        <!--end col-->
                        <div class="col-lg-12">
                            <label for="exampleFormControlTextarea1" class="form-label ">What are the behavioral variables in terms of the basic principles and historical contingencies of the individual that would account for the incorrect response:</label>
                            <textarea class="form-control alert alert-info" id="behavioral_variables" name="behavioral_variables" placeholder="" rows="3" disabled><?= $program_change->behavioral_variables ?></textarea>
                        </div>
                        <!--end col-->
                        <div class="col-lg-12">
                            <label for="exampleFormControlTextarea1" class="form-label  ">Description of the Program Change:</label>
                            <textarea class="form-control alert alert-info" id="description" name="description" placeholder="" rows="3" disabled><?= $program_change->description ?></textarea>
                        </div>
                        <!--end col-->
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
        $('#program_change_form :input').prop('disabled', true);
        var antArray = <?= json_encode($ant) ?>;
        var conArray = <?= json_encode($con) ?>;

        // Loop through each checkbox input for antecedent and check if its value exists in antArray
        $('input[name="ant[]"]').each(function() {
            var checkboxValue = $(this).val();
            if (antArray.some(item => item.ant_id == checkboxValue)) {
                $(this).prop('checked', true);
            }
        });

        // Loop through each checkbox input for consequence and check if its value exists in conArray
        $('input[name="consequence[]"]').each(function() {
            var checkboxValue = $(this).val();
            if (conArray.some(item => item.con_id == checkboxValue)) {
                $(this).prop('checked', true);
            }
        });

    });
</script>