<?= $this->extend("layout/master-closed-menu") ?>
<?= $this->section("head_tag") ?>
<!-- Dragula css -->
<link rel="stylesheet" href="/assets/libs/dragula/dragula.min.css" />
<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
<style>
    .main-content {
        margin-left: 15px !important;
        margin-right: 15px !important;
    }

    .page-content {
        padding: calc(10px + 1.5rem) calc(1.5rem* .5) 20px calc(1.5rem* .5);
    }

    @media (min-width: 992px) {
        .file-manager-sidebar {
            min-width: 300px;
            max-width: 300px;
            height: calc(100vh - 15px - 15px - 15px);
        }
    }

    @media (min-width: 1200px) {}

    legend {
        font-size: 12px;
    }

    .mands_sidebar {
        background-image: url("/assets/images/user-illustarator-2.png");
        background-repeat: no-repeat;
        background-position: center;
        height: calc(100vh - 300px);
        background-size: 250px auto;
    }

    .dev_prog_main_bg {
        background-image: url("/assets/images/task.png");
        background-repeat: no-repeat;
        background-position: center;
        height: calc(100vh - 300px);
        background-size: 250px auto;
    }

    .pb_prog_main_bg {
        background-image: url("/assets/images/file.png");
        background-repeat: no-repeat;
        background-position: center;
        height: calc(100vh - 300px);
        background-size: 250px auto;
    }

    .mands_prog_main_bg {
        background-image: url("/assets/images/verification-img.png");
        background-repeat: no-repeat;
        background-position: center;
        height: calc(100vh - 300px);
        background-size: 250px auto;
    }


    .offcanvas,
    .offcanvas-lg,
    .offcanvas-md,
    .offcanvas-sm,
    .offcanvas-xl,
    .offcanvas-xxl {

        --vz-offcanvas-width: 100%;

    }

    /* Ensure spinner is centered */
    .spinner-border {
        position: absolute;
        top: 50%;
        left: 50%;
    }

    ul.transition-list li:nth-child(even) {
        background-color: #f8f9fa;
    }
</style>
<?= $this->endSection() ?>
<?= $this->section("page_content") ?>

<div class="chat-wrapper d-lg-flex gap-1 mx-n4 mt-n4 p-1">
    <div class="file-manager-sidebar alert-info alert-top-border">
        <div class="p-4 d-flex flex-column h-100">
            <div class="px-2 mx-n4" data-simplebar data-simplebar-auto-hide="false" data-simplebar-track="primary" style="height: calc(100vh - 150px);">
                <div class="mb-1 text-center">
                    <h5 class="mb-0 fw-semibold">Program List</h5>
                </div>
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <h6 class="mt-0 mb-2 text-info"> </h6>
                        <ul class="to-do-menu list-unstyled" id="projectlist-data">
                            <?php foreach ($program as $domainId => $domainData) : ?>
                                <li>
                                    <a data-bs-toggle="collapse" href="#domain<?= $domainId ?>" class="nav-link fs-13 "><?= $domainData['domain_code'] . '-' . $domainData['domain_name'] ?></a>
                                    <div class="collapse" id="domain<?= $domainId ?>">
                                        <ul class="mb-0 sub-menu list-unstyled ps-3 vstack gap-2 mb-2">
                                            <?php foreach ($domainData['goals'] as $goalId => $goalData) : ?>
                                                <li>
                                                    <a href="#!" data-session-id="<?= $sessionDetail->id; ?>" data-client-id="<?= $client->id   ?>" data-goal-id="<?= $goalId ?>" data-domain-id="<?= $domainId ?>" class="goal-link">
                                                        <i class="ri-stop-mini-fill align-middle fs-15 text-secondary"></i> <?= $goalData['goal_code'] . '-' . $goalData['goal_name'] ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

            </div>
            <div class="mt-auto text-center alert-info alert-top-border">
                <div class="row pt-2">
                    <div class="col-lg-12">
                        <a href="<?= base_url('sessions/review/' . $sessionDetail->id) ?>" class="btn btn-soft-info"><i class=" ri-arrow-left-s-line align-middle me-1"></i>Back to Review Program Session Data</a>
                    </div>
                </div>

            </div>

        </div>
    </div><!--end side content-->
    <div class="file-manager-content w-100 p-4 pb-0 ">
        <div class="row">
            <div class="col-lg-12">
                <div class="card mt-n4 mx-n4" style="border-radius:0px">
                    <div class="bg-soft-info alert-info alert-top-border">
                        <div class="card-body pb-0 px-4">
                            <div class="row mb-3">
                                <div class="col-auto order-1 d-block d-lg-none pt-1">
                                    <button type="button" class="btn btn-soft-primary btn-icon btn-sm fs-16 file-menu-btn">
                                        <i class="ri-menu-2-fill align-bottom"></i>
                                    </button>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <div>
                                        <h4 class="fw-bold"><i class="mdi mdi-account-child-outline"></i>&nbsp;<?= $client->internal_mrn; //.' - ' . $client->first_name . ' ' . $client->last_name 
                                                                                                                ?></h4>
                                        <div class="hstack gap-1 flex-wrap">
                                            <div><i class="ri-calendar-event-line align-bottom me-1"></i><span class="fw-medium"><?= app_date($sessionDetail->session_date) ?></span></div>
                                            <div class="vr"></div>
                                            <div><span class="fw-medium"><i class="mdi mdi-account-clock-outline align-bottom me-1"></i><?= $sessionDetail->instructor_first_name . ' ' . $sessionDetail->instructor_last_name; ?></span></div>
                                            <div class="vr"></div>
                                            <div><span class="fw-medium"><i class="mdi mdi-account-group-outline align-middle me-1"></i><?= $sessionDetail->supervisor_first_name . ' ' . $sessionDetail->supervisor_last_name ?></span></div>
                                        </div>
                                    </div>
                                </div>


                            </div>

                        </div>
                        <!-- end card body -->
                    </div>
                </div>
                <!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <div class="dev_prog_main_bg" id='target_table'> </div>

    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section("page_modal") ?>
<?= $this->endSection() ?>

<?= $this->section("page_js") ?>
<script src="/assets/libs/cleave.js/cleave.min.js"></script>
<!-- dragula init js -->
<script src="/assets/libs/dragula/dragula.min.js"></script>
<script src="/assets/libs/dom-autoscroller/dom-autoscroller.min.js"></script>

<script src="/assets/libs/prismjs/prism.js"></script>

<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>

<script>
    $(document).ready(function() {
        /*************************************************************************************** */
        var csrfToken = "<?= csrf_hash() ?>";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        });

        var isShowMenu = false;
        var todoMenuSidebar = document.getElementsByClassName('file-manager-sidebar');
        Array.from(document.querySelectorAll(".file-menu-btn")).forEach(function(item) {
            item.addEventListener("click", function() {
                Array.from(todoMenuSidebar).forEach(function(elm) {
                    elm.classList.add("menubar-show");
                    isShowMenu = true;
                });
            });
        });
        /***************************************************************************************** */
        window.addEventListener('click', function(e) {
            if (document.querySelector(".file-manager-sidebar").classList.contains('menubar-show')) {
                if (!isShowMenu) {
                    document.querySelector(".file-manager-sidebar").classList.remove("menubar-show");
                }
                isShowMenu = false;
            }
        });
        /***************************************************************************************** */


        var session_id = "<?= $sessionDetail->id ?>";
        console.log('Session ID: ' + session_id);




        /***************************************************************************************** */
        $(document).on('click', '.goal-link', function() {
            // Retrieve goal and domain IDs
            var goal_id = $(this).attr('data-goal-id');
            var domain_id = $(this).attr('data-domain-id');
            var client_id = $(this).attr('data-client-id');


            console.log(goal_id + '-' + domain_id + '-' + client_id + '-' + session_id)
            // Use goalId and domainId to fetch and render targets
            // You can make an AJAX request or reload the page with the selected IDs


            $.ajax({
                type: 'POST',
                url: '<?= site_url('sessions/review/get-target-list') ?>',
                data: {
                    "client_id": client_id,
                    "goal_id": goal_id,
                    "domain_id": domain_id,
                    "session_id": session_id,
                },
                dataType: 'html',
                success: function(response) {
                    $('#target_table').removeClass();
                    unbindAllEvens();
                    $('#target_table').empty().html(response);

                    const viewType = $('#target_table').find('#view-identifier').data('view');

                    switch (viewType) {
                        case 'target_list':
                            bindTargetListEvents();
                            break;
                        case 'percentage_probe_yes_no':
                            bindPercentageYesNoEvents();
                            break;
                        case 'stimulus_probe':
                            bindStimulusProbeEvents();
                            break;
                            // Add more cases as you create them
                        default:
                            console.log('Unknown view type. No events bound.');
                            break;
                    }
                },
                error: function(xhr, status, error) {
                    //$('#target_table').html(xhr.responseText);
                    console.error(xhr.responseText);
                }
            });

        });

        /***************************************************************************************** */
        $(document).on('click', '.save-stimulus-button', function() {
            
            let mode = $(this).attr('data-method');
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
        $(document).on('click', '.save-button-all-probe', function(event) {
            // Prevent default behavior to avoid accidental double-clicks          
            event.preventDefault();
            var spinnerContainer = $('#spinner-container');
            var button = $(this);
            if (button.prop('disabled')) {
                return;
            }
            button.prop('disabled', true).text('Processing...');

            // Retrieve values from the clicked Save button
            var client_id = button.data('client-id');
            var session_id = button.data('session-id');
            var domain_id = button.data('domain-id');
            var goal_id = button.data('goal-id');
            var target_id = button.data('target-id');
            var client_probe_set_id = button.data('probe-set-id'); // New
            var current_phase_id = button.data('current-phase-id'); // New

            // Get the status of radio buttons for the corresponding target
            var radioData = [];
            var isRadioComplete = false; // Set to true initially

            // Find the maximum set ID dynamically
            var totalSets = 0;
            $('input[name^="prob_' + target_id + '_"]').each(function() {
                var setId = parseInt($(this).data('set-id'));
                totalSets = Math.max(totalSets, setId);
            });

            var ctt = 0;
            // Loop through each set of radio buttons
            for (var setId = 0; setId <= totalSets; setId++) {
                var setResult = null;

                $('input[name="prob_' + target_id + '_' + setId + '"]:checked').each(function() {
                    ctt++;
                    setResult = $(this).val();
                    return false; // Exit the loop when a checked radio button is found
                });

                // Include the set in the row data
                radioData.push({
                    domain_id: domain_id,
                    goal_id: goal_id,
                    session_id: session_id,
                    client_id: client_id,
                    target_id: target_id,
                    client_probe_set_id: client_probe_set_id,
                    current_phase_id: current_phase_id,
                    result: setResult
                });
            }

            console.log(radioData);
            if (ctt == 0) {
                showAlert('Pending Probes', 'Completion of at least one probe is required.', 'warning');
                button.prop('disabled', false).text('Save'); // Enable the button
                return;
            }


            var ajaxRequest = $.ajax({
                type: 'POST',
                url: '<?= site_url('sessions/review/target/save') ?>',
                data: {
                    "radio_data": JSON.stringify(radioData), // Convert array to JSON string
                    "client_id": client_id,
                    "session_id": session_id,
                    "target_id": target_id,
                    "domain_id": domain_id,
                    "goal_id": goal_id,
                    "target_id": target_id,
                    "client_probe_set_id": client_probe_set_id,
                    "current_phase_id": current_phase_id,
                },
                dataType: 'json',
                beforeSend: function(xhr) {
                    spinnerContainer.removeClass('d-none');
                }
            });
            ajaxRequest.done(function(response) {
                if (response.success == 'Yes') {
                    // Find the current active card
                    Toast.fire({
                        icon: "success",
                        title: "" + response.message
                    });
                    var currentActiveCard = $('.carousel-item.active');

                    // Remove the current active card


                    // Find the next card and make it active
                    var nextCard = currentActiveCard.next('.carousel-item');
                    if (!nextCard.length) {
                        // If there is no next card, go to the first card
                        nextCard = $('.carousel-item:first');
                    }
                    currentActiveCard.remove();
                    nextCard.addClass('active');

                } else {
                    button.prop('disabled', false).text('Save');
                    showAlert('Error', response.message, 'error');
                }
            });

            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
            ajaxRequest.always(function() {
                button.prop('disabled', false).text('Save'); // Enable the button on error
                spinnerContainer.addClass('d-none');
            });



        });
        $(document).on('click', '.save-button-percentage-yes-no', function(event) {
            // Prevent default behavior to avoid accidental double-clicks          
            event.preventDefault();
            var spinnerContainer = $('#spinner-container');
            var button = $(this);
            if (button.prop('disabled')) {
                return;
            }
            button.prop('disabled', true).text('Processing...');

            // Retrieve values from the clicked Save button
            var client_id = button.data('client-id');
            var session_id = button.data('session-id');
            var domain_id = button.data('domain-id');
            var goal_id = button.data('goal-id');
            var target_id = button.data('target-id');
            var client_probe_set_id = button.data('probe-set-id'); // New
            var current_phase_id = button.data('current-phase-id'); // New
            var transitionInput = $('input[name="input_transition_' + target_id + '"]').val().trim();

            // Get the status of radio buttons for the corresponding target
            var radioData = [];
            var isRadioComplete = false; // Set to true initially

            // Find the maximum set ID dynamically
            var totalSets = 0;
            $('input[name^="prob_' + target_id + '_"]').each(function() {
                var setId = parseInt($(this).data('set-id'));
                totalSets = Math.max(totalSets, setId);
            });

            var ctt = 0;
            // Loop through each set of radio buttons
            for (var setId = 0; setId <= totalSets; setId++) {
                var setResult = null;

                $('input[name="prob_' + target_id + '_' + setId + '"]:checked').each(function() {
                    ctt++;
                    setResult = $(this).val();
                    return false; // Exit the loop when a checked radio button is found
                });

                // Include the set in the row data
                radioData.push({
                    domain_id: domain_id,
                    goal_id: goal_id,
                    session_id: session_id,
                    client_id: client_id,
                    target_id: target_id,
                    client_probe_set_id: client_probe_set_id,
                    current_phase_id: current_phase_id,
                    result: setResult
                });
            }
            answer = radioData[0].result;
            /*if (transitionInput == '') {
                showAlert('Missing Input', 'Trial data required.', 'warning');
                button.prop('disabled', false).text('Save');
                return;
            }*/
            if (ctt == 0) {
                showAlert('Pending Probes', 'Completion of at least one probe is required.', 'warning');
                button.prop('disabled', false).text('Save'); // Enable the button
                return;
            }


            var ajaxRequest = $.ajax({
                type: 'POST',
                url: '<?= site_url('sessions/review/target/save_percentage_probe_yes_no') ?>',
                data: {
                    "radio_data": JSON.stringify(radioData), // Convert array to JSON string
                    "client_id": client_id,
                    "session_id": session_id,
                    "target_id": target_id,
                    "domain_id": domain_id,
                    "goal_id": goal_id,
                    "target_id": target_id,
                    "client_probe_set_id": client_probe_set_id,
                    "current_phase_id": current_phase_id,
                    "input_transition": transitionInput,
                },
                dataType: 'json',
                beforeSend: function(xhr) {
                    spinnerContainer.removeClass('d-none');
                }
            });
            ajaxRequest.done(function(response) {
                if (response.success == 'Yes') {

                    var currentActiveCard = $('.carousel-item.active');
                    // Clear transition input
                    $('input[name="input_transition_' + target_id + '"]').val('');

                    // Deselect all radio buttons
                    $('input[type="radio"]').prop('checked', false);

                    // Remove Bootstrap .active styling
                    $('label.btn').removeClass('active');

                    // Reset custom toggle logic
                    previousValues = {};

                    appendToTransitionList(target_id, transitionInput, answer);
                    $('input[name="input_transition_' + target_id + '"]').focus();
                    // Find the current active card
                    Toast.fire({
                        icon: "success",
                        title: "" + response.message
                    });

                } else {
                    button.prop('disabled', false).text('Save');
                    showAlert('Error', response.message, 'error');
                }
            });

            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
            ajaxRequest.always(function() {
                button.prop('disabled', false).text('Save'); // Enable the button on error
                spinnerContainer.addClass('d-none');
            });



        });
        /***************************************************************************************** */
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

    // when loading target_list.php view 
    function bindTargetListEvents() {
        let previousValues = {};

        // Object to store the current active group for each target         
        function enableNextGroup(targetId, setId) {
            $('input[name^="prob_' + targetId + '_' + (setId + 1) + '"]').prop('disabled', false);
        }

        // Function to disable the next group for a specific target
        function disableNextGroup(targetId, setId) {
            // Iterate over all sets starting from the next one after startSetId
            for (var setId = setId + 1;; setId++) {
                // Check if there are radio buttons in the current set
                if ($('input[name^="prob_' + targetId + '_' + setId + '"]').length === 0) {
                    break; // No more sets, exit the loop
                }

                // Disable all radio buttons in the current set
                $('input[name^="prob_' + targetId + '_' + setId + '"]').prop('disabled', true);

                // Uncheck all radio buttons in the current set
                $('input[name^="prob_' + targetId + '_' + setId + '"]').prop('checked', false);
            }
        }

        // Disable all groups except the first one initially
        //$('input[name^="prob_"]').not('input[name="prob_1"]').prop('disabled', true);

        // Radio button click event handler
        $(document).on('click', 'input[type="radio"]', function() {
            // Get the value of the clicked radio button
            var clickedValue = $(this).val();
            var targetId = $(this).data('target-id');
            var setId = $(this).data('set-id');

            // Get the name of the radio button group
            var groupName = $(this).attr('name');

            // Check if the clicked radio button has the same value as the previously selected one
            if (previousValues.hasOwnProperty(groupName)) {
                if (clickedValue === previousValues[groupName]) {
                    // Reset both radio buttons in the group
                    $('input[name="' + groupName + '"]').prop('checked', false);
                    // Update the previous value for the group
                    previousValues[groupName] = undefined;

                    // Disable the next group for this target
                    disableNextGroup(targetId, setId);
                } else {
                    // Keep the clicked radio button selected
                    $('input[name="' + groupName + '"][value="' + clickedValue + '"]').prop('checked', true);
                    // Update the previous value for the group
                    previousValues[groupName] = clickedValue;
                    // Check if the next group is already enabled
                    enableNextGroup(targetId, setId);
                }
            } else {
                // If there is no previous value for the group, set the clicked value as the previous value
                previousValues[groupName] = clickedValue;
                enableNextGroup(targetId, setId);
            }
        });


        /***************************************************************************************** */


    }

    // when loading percentage yes no probes 
    function bindPercentageYesNoEvents() {

        let previousValues = {};
        // Object to store the current active group for each target         
        function enableNextGroup(targetId, setId) {
            $('input[name^="prob_' + targetId + '_' + (setId + 1) + '"]').prop('disabled', false);
        }

        // Function to disable the next group for a specific target
        function disableNextGroup(targetId, setId) {
            // Iterate over all sets starting from the next one after startSetId
            for (var setId = setId + 1;; setId++) {
                // Check if there are radio buttons in the current set
                if ($('input[name^="prob_' + targetId + '_' + setId + '"]').length === 0) {
                    break; // No more sets, exit the loop
                }

                // Disable all radio buttons in the current set
                $('input[name^="prob_' + targetId + '_' + setId + '"]').prop('disabled', true);

                // Uncheck all radio buttons in the current set
                $('input[name^="prob_' + targetId + '_' + setId + '"]').prop('checked', false);
            }
        }

        // Disable all groups except the first one initially
        //$('input[name^="prob_"]').not('input[name="prob_1"]').prop('disabled', true);

        // Radio button click event handler
        $(document).on('click', 'input[type="radio"]', function() {
            // Get the value of the clicked radio button
            var clickedValue = $(this).val();
            var targetId = $(this).data('target-id');
            var setId = $(this).data('set-id');

            // Get the name of the radio button group
            var groupName = $(this).attr('name');

            // Check if the clicked radio button has the same value as the previously selected one
            if (previousValues.hasOwnProperty(groupName)) {
                if (clickedValue === previousValues[groupName]) {
                    // Reset both radio buttons in the group
                    $('input[name="' + groupName + '"]').prop('checked', false);
                    // Update the previous value for the group
                    previousValues[groupName] = undefined;

                    // Disable the next group for this target
                    disableNextGroup(targetId, setId);
                } else {
                    // Keep the clicked radio button selected
                    $('input[name="' + groupName + '"][value="' + clickedValue + '"]').prop('checked', true);
                    // Update the previous value for the group
                    previousValues[groupName] = clickedValue;
                    // Check if the next group is already enabled
                    enableNextGroup(targetId, setId);
                }
            } else {
                // If there is no previous value for the group, set the clicked value as the previous value
                previousValues[groupName] = clickedValue;
                enableNextGroup(targetId, setId);
            }
        });

        /***************************************************************************************** */


    };

    // when loading  stimulus  
    function bindStimulusProbeEvents() {

        let previousValues = {};
        // Object to store the current active group for each target         
        function enableNextGroup(targetId, setId) {
            $('input[name^="prob_' + targetId + '_' + (setId + 1) + '"]').prop('disabled', false);
        }

        // Function to disable the next group for a specific target
        function disableNextGroup(targetId, setId) {
            // Iterate over all sets starting from the next one after startSetId
            for (var setId = setId + 1;; setId++) {
                // Check if there are radio buttons in the current set
                if ($('input[name^="prob_' + targetId + '_' + setId + '"]').length === 0) {
                    break; // No more sets, exit the loop
                }

                // Disable all radio buttons in the current set
                $('input[name^="prob_' + targetId + '_' + setId + '"]').prop('disabled', true);

                // Uncheck all radio buttons in the current set
                $('input[name^="prob_' + targetId + '_' + setId + '"]').prop('checked', false);
            }
        }

        // Disable all groups except the first one initially
        //$('input[name^="prob_"]').not('input[name="prob_1"]').prop('disabled', true);

        // Radio button click event handler
        $(document).on('click', 'input[type="radio"]', function() {
            // Get the value of the clicked radio button
            var clickedValue = $(this).val();
            var targetId = $(this).data('target-id');
            var setId = $(this).data('set-id');

            // Get the name of the radio button group
            var groupName = $(this).attr('name');

            // Check if the clicked radio button has the same value as the previously selected one
            if (previousValues.hasOwnProperty(groupName)) {
                if (clickedValue === previousValues[groupName]) {
                    // Reset both radio buttons in the group
                    $('input[name="' + groupName + '"]').prop('checked', false);
                    // Update the previous value for the group
                    previousValues[groupName] = undefined;

                    // Disable the next group for this target
                    disableNextGroup(targetId, setId);
                } else {
                    // Keep the clicked radio button selected
                    $('input[name="' + groupName + '"][value="' + clickedValue + '"]').prop('checked', true);
                    // Update the previous value for the group
                    previousValues[groupName] = clickedValue;
                    // Check if the next group is already enabled
                    enableNextGroup(targetId, setId);
                }
            } else {
                // If there is no previous value for the group, set the clicked value as the previous value
                previousValues[groupName] = clickedValue;
                enableNextGroup(targetId, setId);
            }
        });



    };

    function appendToTransitionList(targetId, transitionText, answer) {
        const listId = '#transition_list_' + targetId;
        let list = $(listId);


        const safeTransition = $('<div>').text(transitionText).html();
        const safeAnswer = $('<div>').text(answer).html();

        list.prepend(`
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>${safeTransition}</span>
                        <span class="text-muted small">${safeAnswer}</span>
                    </li>
                `);
    }

    function unbindAllEvens() {
        $(document).off('click', 'input[type="radio"]');
    }
</script>
<?= $this->endSection() ?>