<?= $this->extend("layout/master-closed-menu") ?>
<?= $this->section("head_tag") ?>
<!-- Dragula css -->
<link rel="stylesheet" href="/assets/libs/dragula/dragula.min.css" />
<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
<style>
    .target-history-modal-dialog {
        max-width: 90vw;
        width: 90vw;
    }

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
                <ul id="tabs" class="nav nav-pills arrow-navtabs nav-info bg-light nav-justified mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" data-bs-toggle="tab" href="#dev_prog" role="tab" aria-selected="true" style="width: 95px" data-client-id="<?= $client->id ?>">
                            <span data-bs-toggle="tooltip" data-bs-placement="top" title="Developmental Program">Program</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#mands" role="tab" aria-selected="false" tabindex="-1" data-client-id="<?= $client->id ?>">
                            Mands
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#pb_prog" role="tab" aria-selected="false" tabindex="-1" style="width: 100px" data-client-id="<?= $client->id ?>">
                            <span data-bs-toggle="tooltip" data-bs-placement="top" title="Functional Program">PB</span>
                        </a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active show" id="dev_prog" role="tabpanel">
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
                                                            <a href="#!" data-session-id="<?= ''; ?>" data-client-id="<?= $client->id   ?>" data-goal-id="<?= $goalId ?>" data-domain-id="<?= $domainId ?>" class="goal-link">
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
                    <div class="tab-pane mands_sidebar" id="mands" role="tabpanel">
                        <div class="text-center" style="padding-top: 20%;">
                            <div class="flex-grow-1">
                                <button id="mandsStatus" data-session-id="" data-session-status="<?= $isMandActive ? 1 : 0 ?>" data-client-id="<?= $client->id; ?>" type="button" class="btn btn-sm btn-soft-primary waves-effect waves-light align-center">
                                    <i class="<?= $isMandActive ? ' ri-stop-circle-line' : 'ri-play-circle-line' ?> align-bottom me-1"></i> <span><?= $isMandActive ? 'Stop mands data collection' : 'Start mands data collection' ?></span>
                                </button>
                            </div>
                            <br>
                            <div class="flex-grow-1 d-inline-flex align-items-center justify-content-center gap-2">
                                <button type="button" class="btn btn-outline-primary" id="viewMandsListBtn">
                                    Mands Count <span class="badge bg-info ms-1"><span id="mandsCount"><?= isset($mandsCount) ? $mandsCount : '0' ?></span></span>
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-link p-0 text-info"
                                    onclick="openMandsHelpModal('mand_count', 'mandsHelpModalLive', 'mandsHelpTemplatesLive')"
                                    aria-label="Mand Count help">
                                    <i class="ri-information-line"></i>
                                </button>
                            </div>

                        </div>
                    </div>
                    <div class="tab-pane" id="pb_prog" role="tabpanel">
                        <div class="d-flex">
                            <div class="flex-grow-1">

                            </div>
                        </div>
                    </div>

                </div>

            </div>
            <div class="mt-auto text-center alert-info alert-top-border">
                <div class="row pt-2">
                    <div class="col-lg-12">
                        <a href="/sessions/live/" class="btn btn-soft-info"><i class=" ri-arrow-left-s-line align-middle me-1"></i>Back to client list</a>
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
                                            <div><i class="ri-calendar-event-line align-bottom me-1"></i><span class="fw-medium"><?= currentDate(setting('App.dateFormat')) ?></span></div>
                                            <div class="vr"></div>
                                            <div><span class="fw-medium"><i class="mdi mdi-account-clock-outline align-bottom me-1"></i><?= $instructor->first_name . ' ' . $instructor->last_name; ?></span></div>
                                            <div class="vr"></div>
                                            <div><span class="fw-medium"><i class="mdi mdi-account-group-outline align-middle me-1"></i><?= isset($supervisor) ? $supervisor->first_name . ' ' . $supervisor->last_name : 'No supervisor'; ?></span></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-2 col-md-2 col-sm-12">
                                    <fieldset class="border border-primary rounded-3 p-1">
                                        <legend class="float-none w-auto px-1 mb-0">
                                            <span class="fw-medium">Problem Behavior</span>
                                        </legend>
                                        <div class="p-1 gap">
                                            <span class="fw-medium align-center"><i class="ri-timer-line align-bottom me-1"></i> <span id="pbTimerText"></span></span>
                                            <button
                                                id="pbTimer"
                                                data-session-id=""
                                                data-timer-status="<?= $isPBTimer ? 'active' : 'inactive' ?>"
                                                data-client-id="<?= $client->id; ?>"
                                                type="button"
                                                class="btn btn-sm btn-soft-primary btn-icon waves-effect waves-light align-center float-end">
                                                <i class="<?= $isPBTimer ? 'ri-pause-fill' : 'ri-play-fill' ?>"></i>
                                            </button>
                                        </div>

                                    </fieldset>
                                </div>
                                <div class="col-lg-2 col-md-2 col-sm-12">
                                    <fieldset class="border border-primary rounded-3 p-1">
                                        <legend class="float-none w-auto px-1 mb-0">
                                            <span class="fw-medium"> Teaching</span>
                                        </legend>
                                        <div class="p-1 gap">
                                            <span class="fw-medium align-center"><i class="ri-timer-line align-bottom me-1"></i> <span id="teachingTimer"></span></span>
                                            <button
                                                id="sessionTimer"
                                                data-session-id=""
                                                data-timer-status="<?= $isSessionTimer ? 'active' : 'inactive' ?>"
                                                data-client-id="<?= $client->id; ?>"
                                                type="button"
                                                class="btn btn-sm btn-soft-primary waves-effect waves-light align-center float-end">
                                                <i class="<?= $isSessionTimer ? 'ri-pause-fill' : 'ri-play-fill' ?>"></i>
                                            </button>
                                        </div>

                                    </fieldset>
                                </div>
                                <div class="col-lg-2 col-md-2 col-sm-12">
                                    <fieldset class="border border-primary rounded-3 p-1">
                                        <legend class="float-none w-auto px-1 mb-0">
                                            <span class="fw-medium">Session</span>
                                        </legend>
                                        <div class="p-1 gap">
                                            <span class="fw-medium align-center"><i class="ri-timer-line align-bottom me-1"></i><span id='sessionStatusText' class="badge bg-<?= $isSession ? 'info' : 'primary' ?>-subtle text-<?= $isSession ? 'info' : 'primary' ?>"><?= $isSession ? '' . $session->start_time : 'Inactive' ?></span></span>

                                            <button id="sessionStatus" data-session-id="" data-session-status="<?= $isSession ? 'active' : 'inactive' ?>" data-client-id="<?= $client->id; ?>" type="button" class="btn btn-sm btn-soft-primary waves-effect waves-light align-center float-end">
                                                <i class="<?= $isSession ? ' ri-stop-circle-line' : 'ri-play-circle-line' ?> align-bottom me-1"></i> <span><?= $isSession ? 'End' : 'Start' ?></span>
                                            </button>
                                        </div>

                                    </fieldset>
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

<div class="offcanvas offcanvas-end" tabindex="-1" id="pb_canvas" aria-labelledby="pb_canvas_label">

    <div class="offcanvas-body " id='pb_area' style="background-color: lightgrey;">
        ...
    </div>
    <div class="offcanvas-footer border p-3 text-center">
        <a href="javascript:void(0);" class="link-primary" data-bs-dismiss="offcanvas">End of Problem Behavior <i class="ri-arrow-right-s-line align-middle ms-1"></i></a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section("page_modal") ?>
<div class="modal fade" id="mandsListModal" tabindex="-1" aria-labelledby="mandsListModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="mandsListModalLabel">Mands List (Current Session)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Reinforcer</th>
                                <th>Prompt Level</th>
                                <th>Utterance</th>
                                <th>Mand Error</th>
                                <th>Peer Manding</th>
                                <th>Eye Contact</th>
                                <th>Initial Attempt</th>
                                <th>Prompt Delay</th>
                                <th>Echoic 1</th>
                                <th>Echoic 2</th>
                                <th>Echoic 3</th>
                            </tr>
                        </thead>
                        <tbody id="mandsListModalBody">
                            <tr>
                                <td colspan="12" class="text-center text-muted">No data loaded.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    <i class="ri-close-line align-bottom me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>
<?= view('ClientSessions/Mands/_help_templates', ['template_container_id' => 'mandsHelpTemplatesLive']) ?>
<?= view('ClientSessions/Mands/_help_modal', ['modal_id' => 'mandsHelpModalLive']) ?>

<!-- Target History Modal -->
<div class="modal fade" id="targetHistoryModal" tabindex="-1" aria-labelledby="targetHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable target-history-modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="targetHistoryModalLabel">Target Processing History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3" id="targetHistoryModalBody">
                <div class="text-center text-muted p-4">
                    <i class="ri-time-line fs-1"></i>
                    <p class="mt-2 mb-0">Target history will load here.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    <i class="ri-close-line align-bottom me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section("page_js") ?>
<script src="/assets/libs/cleave.js/cleave.min.js"></script>
<!-- dragula init js -->
<script src="/assets/libs/dragula/dragula.min.js"></script>
<script src="/assets/libs/dom-autoscroller/dom-autoscroller.min.js"></script>

<script src="/assets/libs/prismjs/prism.js"></script>

<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>

<script>
    function openMandsHelpModal(helpKey, modalId, templateContainerId) {
        if (typeof bootstrap === 'undefined' || typeof bootstrap.Modal === 'undefined') {
            return;
        }

        const modalEl = document.getElementById(modalId);
        const templateContainer = document.getElementById(templateContainerId);

        if (!modalEl || !templateContainer) {
            return;
        }

        const templateEl = templateContainer.querySelector('[data-help-key="' + helpKey + '"]');

        if (!templateEl) {
            return;
        }

        modalEl.querySelector('.mands-help-modal-title').textContent = templateEl.dataset.helpTitle || 'Information';
        modalEl.querySelector('.mands-help-modal-body').innerHTML = templateEl.innerHTML;

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    $(document).ready(function() {
        /*************************************************************************************** */
        var csrfToken = "<?= csrf_hash() ?>";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        });

        function initMandsTooltips() {
            if (typeof bootstrap === 'undefined' || typeof bootstrap.Tooltip === 'undefined') {
                return;
            }
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
                if (!bootstrap.Tooltip.getInstance(el)) {
                    new bootstrap.Tooltip(el, {
                        trigger: 'hover focus'
                    });
                }
            });
        }


        /*************************************************************************************** */
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

        var isSession = <?= $isSession ? 1 : 0; ?>;
        var session_id = "<?= $isSession ? $session->id : '' ?>";
        console.log('Session ID: ' + session_id);
        if (session_id) {
            $('#sessionStatus').attr('data-session-id', session_id);
            $('#sessionTimer').attr('data-session-id', session_id);
            $('#pbTimer').attr('data-session-id', session_id);
        }
        /***************************************************************************************** */
        // Teaching Time Related variables
        var timer; // Variable to store the timer
        var isTimerRunning = false;
        var isSessionTimer = <?= $isSessionTimer ? 1 : 0; ?>;
        var timerFromServer = '<?= $teachingDuration; ?>';
        var totalSeconds = timeStringToSeconds(timerFromServer); // Convert timerFromServer to total seconds

        if (timerFromServer != '00:00:00') {
            updateTimer(); // Initial update
            isTimerRunning = false;
        }

        if (isSession && isSessionTimer) {
            updateTimer(); // Initial update
            timer = setInterval(updateTimer, 1000);
            isTimerRunning = true;
        }

        var isMandActive = <?= $isMandActive ? 1 : 0 ?>;
        /***************************************************************************************** */
        // PB Time Related variables
        var pbTimer; // Variable to store the timer
        var isPBTimerRunning = false;
        var isPBTimer = <?= $isPBTimer ? 1 : 0; ?>;
        var pbTimerFromServer = '<?= $pbDuration; ?>';
        var pbTotalSeconds = pbTimeStringToSeconds(pbTimerFromServer); // Convert timerFromServer to total seconds
        var client_id = <?= $client->id ?>;

        console.log(isPBTimer + '-' + pbTimerFromServer);

        if (pbTimerFromServer != '00:00:00') {
            pbUpdateTimer(); // Initial update
            isPBTimerRunning = false;
        }

        if (isSession && isPBTimer) {
            pbUpdateTimer(); // Initial update
            pbTimer = setInterval(pbUpdateTimer, 1000);
            isPBTimerRunning = true;
        }


        /***************************************************************************************** */
        $(document).on('click', '.goal-link', function() {
            // Retrieve goal and domain IDs
            var goal_id = $(this).attr('data-goal-id');
            var domain_id = $(this).attr('data-domain-id');
            var client_id = $(this).attr('data-client-id');


            console.log(goal_id + '-' + domain_id + '-' + client_id + '-' + session_id)
            // Use goalId and domainId to fetch and render targets
            // You can make an AJAX request or reload the page with the selected IDs
            if (session_id === '') {
                showAlert('Error', 'Session not started. Start the session first to proceed.', 'error');
                return;
            }

            if (isPBTimerRunning == true && isTimerRunning == false) {
                showAlert('Error', 'Problem behavior is active. Stop the problem behavior to proceed with teaching.', 'error');
                return;
            }

            if (isPBTimerRunning == false && isTimerRunning == false) {
                showAlert('Error', 'Teaching session timer is not active. Start the teaching timer to proceed.', 'error');
                return;
            }

            $.ajax({
                type: 'POST',
                url: '<?= site_url('sessions/live/get-target-list') ?>',
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
        /*$(document).on('click', '.goal-link', function() {
            var goal_id = $(this).data('goal-id');
            var domain_id = $(this).data('domain-id');
            var client_id = $(this).data('client-id');
            var session_id = $(this).data('session-id');
            // Fetch probe sets for the selected goal and client
            $.ajax({
                type: 'POST',
                url: '<?= site_url('sessions/live/get-probe-sets') ?>',
                data: {
                    "client_id": client_id,
                    "goal_id": goal_id
                },
                dataType: 'json',
                success: function(probeSets) {
                    // Display Swal pop-up to choose probe set
                    Swal.fire({
                        title: 'Select Probe Set',
                        input: 'select',
                        inputOptions: probeSets.reduce((options, set) => {
                            options[set.probe_set_id] = set.probe_set_name;
                            return options;
                        }, {}),
                        inputPlaceholder: 'Select a probe set',
                        showCancelButton: true,
                        customClass: {
                            input: 'swal2-select',
                            confirmButton: 'btn btn-primary w-xs me-2 mt-2',
                            cancelButton: 'btn btn-light w-xs me-2 mt-2',
                            closeButton: 'btn btn-light w-xs me-2 mt-2',
                        },

                        inputValidator: function(value) {
                            return new Promise(function(resolve, reject) {
                                if (value == '') {
                                    resolve('You need to select a probe set!');
                                } else {
                                    resolve();
                                }
                            });
                        }
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            var probe_set_id = result.value;

                            // Fetch and display target list based on the selected probe set
                            $.ajax({
                                type: 'POST',
                                url: '<?= site_url('sessions/live/get-target-list') ?>',
                                data: {
                                    "client_id": client_id,
                                    "goal_id": goal_id,
                                    "domain_id": domain_id,
                                    "session_id": session_id,
                                    "probe_set_id": probe_set_id
                                },
                                dataType: 'html',
                                success: function(response) {
                                    $('#target_table').html(response);
                                },
                                error: function(xhr, status, error) {
                                    console.error(xhr.responseText);
                                }
                            });
                        }
                    });
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });*/

        /***************************************************************************************** */
        $('#sessionStatus').on('click', function() {
            var btn = $(this);
            var client_id = btn.attr('data-client-id');
            // Check if the button has the 'active' class
            var isSessionActive = btn.attr('data-session-status');

            // Determine the action based on the session state
            //var action = isSessionActive ? 'stop' : 'start';
            var text = '';
            if (isSessionActive == 'active') {
                text = 'You want to end the session.';
            } else if (isSessionActive == 'inactive') {
                text = 'You want to start the session.'
            } else {
                text = 'Session Completed.'
            }
            if (isSessionActive == 'active' || isSessionActive == 'inactive') {
                // Use Swal to confirm the action
                Swal.fire({
                    title: 'Are you sure?',
                    text: text,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                    confirmButtonClass: 'btn btn-primary w-xs me-2 mt-2',
                    cancelButtonClass: 'btn btn-light w-xs me-2 mt-2',
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Additional logic for starting or stopping the session
                        console.log(isSessionActive);
                        if (isSessionActive == 'active') {
                            // Handle start session logic
                            stopSession(client_id);
                        } else if (isSessionActive == 'inactive') {
                            // Handle stop session logic
                            startSession(client_id);
                        } else {
                            Swal.fire({
                                title: '',
                                text: text,
                                icon: 'info',
                                confirmButtonText: 'Ok',
                                confirmButtonClass: 'btn btn-primary w-xs me-2 mt-2',
                                buttonsStyling: false
                            });
                        }
                    }
                });
            } else {
                Swal.fire({
                    title: '',
                    text: text,
                    icon: 'info',
                    confirmButtonText: 'Ok',
                    confirmButtonClass: 'btn btn-primary w-xs me-2 mt-2',
                    buttonsStyling: false
                });
            }


        });

        /*********************************************************************/



        $('#sessionTimer').on('click', function() {

            var btn = $(this);

            if (session_id === '') {
                showAlert('Error', 'Session not started. Start the session first to proceed.', 'error');
                return;
            }

            if (isPBTimerRunning) {
                showAlert('Error', 'Problem behavior is active. Stop the problem behavior to proceed with teaching.', 'error');
                return;
            }

            var client_id = btn.attr('data-client-id');
            // Check if the button has the 'active' class
            var isTimerActive = btn.attr('data-timer-status');


            var text = '';
            var action = '';
            if (isTimerActive === 'active') {
                text = 'You want to pause the teaching?';
                action = 'stop';
            }
            if (isTimerActive === 'inactive') {
                text = 'You want to start the teaching?'
                action = 'start';
            }

            // Use Swal to confirm the action
            Swal.fire({
                title: 'Are you sure?',
                text: text,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                confirmButtonClass: 'btn btn-primary w-xs me-2 mt-2',
                cancelButtonClass: 'btn btn-light w-xs me-2 mt-2',
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    updateDuration(client_id, action);
                }
            });
        });

        /******************************************************* */

        $('#pbTimer').on('click', function() {
            var btn = $(this);

            if (session_id === '') {
                showAlert('Error', 'Session not started. Start the session first to proceed.', 'error');
                return;
            }


            client_id = btn.attr('data-client-id');
            // Check if the button has the 'active' class
            var isTimerActive = btn.attr('data-timer-status');

            var text = '';
            var action = '';
            if (isTimerActive === 'active') {
                text = 'You want to stop problem behavior timer.';
                action = 'stop';
            }
            if (isTimerActive === 'inactive') {
                text = 'You want to start problem behavior timer.'
                action = 'start';
            }

            // Use Swal to confirm the action
            Swal.fire({
                title: 'Are you sure?',
                text: text,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                confirmButtonClass: 'btn btn-primary w-xs me-2 mt-2',
                cancelButtonClass: 'btn btn-light w-xs me-2 mt-2',
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    updatePB(client_id, action);
                    //pb_canvas_bsc.show();
                }
            });
        });
        /*********************************************************************/


        var previousTab = "#dev_prog"; // Initially set to the first tab

        if (isPBTimerRunning == true) {
            // Trigger the PB tab to load its content and make it active
            $('a[href="#pb_prog"]').tab('show'); // Switch to PB tab
            previousTab = "#pb_prog";
            loadPbContent(client_id, session_id); // Load PB content
        } else if (isMandActive == 1) {
            $('a[href="#mands"]').tab('show'); // Switch to PB tab
            previousTab = "#mands";
            loadMandsContent(client_id, session_id); // Load PB content
        }



        // When a tab is shown
        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            var newTab = $(e.target).attr("href");
            var client_id = $(e.target).attr("data-client-id");
            console.log("Tab changed from", previousTab, "to", newTab);

            if (session_id === '') {
                showAlert('Error', 'Session not started. Start the session first to proceed.', 'error');
                revertToPreviousTab(e); // Revert tab change
                return;
            }

            // If PB timer is running, prevent switching to any other tab except PB
            if (isPBTimerRunning == true && newTab != '#pb_prog') {
                showAlert('Error', 'Problem behavior is active. You cannot access other tabs until it is resolved.', 'error');
                revertToPreviousTab(e); // Revert to the PB tab
                return;
            }

            // If Mands is running, prevent switching to any other tab except PB
            if (isMandActive == 1 && newTab != '#mands') {
                showAlert('Error', 'Mands data collection is active. You cannot access other tabs until stop mands data collection.', 'error');
                revertToPreviousTab(e); // Revert to the PB tab
                return;
            }
            if (isPBTimerRunning == false && isTimerRunning == false) {
                showAlert('Error', 'Teaching timer is not active. Start the teaching timer to proceed.', 'error');
                revertToPreviousTab(e); // Revert tab change
                return;
            }


            if (newTab == '#mands') {
                $('#target_table').removeClass();
                $.ajax({
                    type: 'POST',
                    url: '<?= site_url('sessions/live/mands') ?>',
                    data: {
                        "client_id": client_id,
                        'isMandActive': isMandActive,
                        "session_id": session_id,

                    },
                    dataType: 'html',
                    success: function(response) {
                        // Update program list content
                        unbindAllEvens();
                        $('#target_table').html(response);
                        if (response == 'Session is not started yet or completed.') {
                            $('#target_table').addClass('mands_prog_main_bg');
                        }
                    },
                    error: function(xhr, status, error) {
                        //$('#target_table').html(xhr.responseText);
                        console.error(xhr.responseText);
                    }
                });
            } else if (newTab == '#dev_prog') {
                $('#target_table').html("");
                $('#target_table').removeClass();
                $('#target_table').addClass('dev_prog_main_bg');
            } else if (newTab == '#pb_prog') {
                $('#target_table').html("");
                $('#target_table').removeClass();
                $('#target_table').addClass('pb_prog_main_bg');

                $.ajax({
                    type: 'POST',
                    url: '<?= site_url('sessions/live/getPbRecordList') ?>',
                    data: {
                        "session_id": session_id,
                        "client_id": client_id
                    },
                    dataType: 'html',
                    success: function(response) {

                        $('#pb_prog .flex-grow-1').html(response);
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            }
            previousTab = newTab;
        });

        /***************************************************************************************** */
        // Handle button click to toggle status
        $('#mandsStatus').on('click', function() {
            var btn = $(this);
            var client_id = btn.attr('data-client-id');
            var currentStatus = btn.attr('data-session-status');
            // Prepare the new status based on the current status
            var newStatus = (currentStatus == 1) ? 0 : 1;
            var action = (newStatus == 0) ? 'stop' : 'start';
            startStopMandsDuration(btn, action, newStatus, client_id);
        });

        function startStopMandsDuration(btn, action, newStatus, client_id) {

            if (action == 'start') {
                if (isPBTimerRunning == true) {
                    showAlert('Error', 'Active problem behavior detected. Stop the behavior before ending the session.', 'error');
                    // revertToPreviousTab(e); // Revert tab change
                    return;
                }
                if (isTimerRunning == false) {
                    showAlert('Error', 'Teaching timer is not active. Start the teaching timer to proceed.', 'error');
                    //revertToPreviousTab(e); // Revert tab change
                    return;
                }
            }

            var ajaxRequest = $.ajax({
                url: '<?= site_url('sessions/live/mands/updateMandsDuration') ?>', // Server endpoint to process status change
                type: 'post',
                data: {
                    "client_id": client_id,
                    'action': action,
                    'session_id': session_id
                },
                beforeSend: function(xhr) {
                    btn.prop("disabled", true);
                }
            });

            ajaxRequest.done(function(response) {
                if (response.status == 'success') {

                    // Update isMandActive globally
                    isMandActive = newStatus;

                    // Toggle button icon and text
                    btn.attr('data-session-status', newStatus);
                    btn.find('i').attr('class', newStatus == 1 ? 'ri-stop-circle-line align-bottom me-1' : 'ri-play-circle-line align-bottom me-1');
                    btn.find('span').text(newStatus == 1 ? 'Stop mands data collection' : 'Start mands data collection');

                    // Show/hide the MandsSessionInactiveAlert row based on the new status
                    $('#MandsSessionInactiveAlert').attr('hidden', newStatus == 1);

                    // Enable/disable the reinforcer input field based on the new status
                    $('#reinforcerInput').prop('disabled', newStatus == 0);
                    $('#utteranceInput').prop('disabled', newStatus == 0);

                    // Enable/disable the save button based on the new status
                    $('#save_mands').prop('disabled', newStatus == 0);
                    $('#mandsCount').html(response.mandsCount);

                    console.log("Status updated successfully");


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

        $('#viewMandsListBtn').on('click', function() {
            if (!session_id) {
                showAlert('Error', 'Session not started. Start the session first to proceed.', 'error');
                return;
            }

            var tbody = $('#mandsListModalBody');
            tbody.html('<tr><td colspan="12" class="text-center text-muted">Loading...</td></tr>');

            $.ajax({
                type: 'POST',
                url: '<?= site_url('sessions/live/mands/list') ?>',
                data: {
                    client_id: client_id,
                    session_id: session_id
                },
                dataType: 'html',
                success: function(response) {
                    tbody.html(response);
                    initMandsTooltips();

                    var modalEl = document.getElementById('mandsListModal');
                    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                },
                error: function(xhr, status, error) {
                    showAlert(xhr.status, 'Request failed: ' + status + '<br>' + error, 'error');
                }
            });
        });

        /******************************************************************************************************** */
        // Event to load PB form when a PB record is clicked
        $(document).on('click', '.pb-record-link', function(e) {
            e.preventDefault(); // Prevent default link behavior

            var pb_timer_id = $(this).attr('data-pb-timer-id');
            var session_id = $(this).attr('data-session-id');
            var client_id = $(this).attr('data-client-id');

            $.ajax({
                type: 'POST',
                url: '<?= site_url('sessions/live/getPbRecordForm') ?>', // URL to the controller method
                data: {
                    "pb_timer_id": pb_timer_id,
                    "session_id": session_id,
                    "client_id": client_id
                },
                dataType: 'html',
                success: function(response) {
                    $('#target_table').removeClass();
                    unbindAllEvens();
                    $('#target_table').empty();
                    $('#target_table').html(response); // Load the form into the target table
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });
        /******************************************************************************************************** */
        // Delegate form-related event handlers within a parent container

        // Show/Hide Antecedent Other textarea (delegated)
        $(document).on('change', 'input[name="antecedent"]', function() {
            if ($(this).val() === 'Other') {
                $('#antecedent_other').show();
            } else {
                $('#antecedent_other').hide();
            }
        });

        // Show/Hide Consequence Other textarea (delegated)
        $(document).on('change', 'input[name="consequence"]', function() {
            if ($(this).val() === 'Other') {
                $('#consequence_other').show();
            } else {
                $('#consequence_other').hide();
            }
        });

        // Add more behaviors dynamically
        $(document).on('click', '#add-behavior', function() {
            const behaviorCount = $('#behavior-list .row').length;
            const newBehavior = `
            <div class="row mb-2 behavior-row">
                <div class="col-md-8">
                    <input type="text" name="behavior[]" class="form-control form-control-sm" placeholder="Enter behavior">
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-danger btn-sm remove-behavior">Remove</button>
                </div>
                <input type="hidden" name="intensity[${behaviorCount}]" value="1"> <!-- Default intensity value -->
            </div>`;
            $('#added-behaviors').append(newBehavior); // Append to the added-behaviors div
        });

        // Remove behavior rows (delegated)
        $(document).on('click', '.remove-behavior', function() {
            $(this).closest('.behavior-row').remove();
        });

        // Submit PB record form (delegated)
        $(document).on('submit', '#pbRecordForm', function(e) {
            e.preventDefault();

            $.ajax({
                type: 'POST',
                url: '<?= site_url("sessions/live/saveProblemBehaviorRecord") ?>',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success === 'Yes') {
                        Toast.fire({
                            icon: 'success',
                            title: '' + response.message
                        });
                        $('.pb-record-status').text('(Existing record, update)');
                    } else {
                        let errors = Object.values(response.message);
                        displayValidationErrors(errors);
                    }
                },
                error: function(xhr, status, error) {
                    showAlert(xhr.status, 'Request failed: ' + status + '<br>' + error, 'error');
                }
            });
        });

        /**************************************************************************************** */

        function loadMandsContent(client_id, session_id) {
            $('#target_table').removeClass();
            $.ajax({
                type: 'POST',
                url: '<?= site_url('sessions/live/mands') ?>',
                data: {
                    "client_id": client_id,
                    'isMandActive': isMandActive,
                    "session_id": session_id,
                },
                dataType: 'html',
                success: function(response) {
                    // Update program list content
                    unbindAllEvens();
                    $('#target_table').empty();
                    $('#target_table').html(response);
                    if (response == 'Session is not started yet or completed.') {
                        $('#target_table').addClass('mands_prog_main_bg');
                    }
                },
                error: function(xhr, status, error) {
                    //$('#target_table').html(xhr.responseText);
                    console.error(xhr.responseText);
                }
            });
        }

        function loadPbContent(client_id, session_id) {
            console.log('I am in loadPbContent', client_id, session_id);
            $.ajax({
                type: 'POST',
                url: '<?= site_url('sessions/live/getPbRecordList') ?>',
                data: {
                    "session_id": session_id,
                    "client_id": client_id,
                    "session_id": session_id,
                },
                dataType: 'html',
                success: function(response) {
                    $('#pb_prog .flex-grow-1').html(response);
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        }

        // Function to revert to the previous tab
        function revertToPreviousTab(e) {
            e.preventDefault(); // Prevent the default tab switch behavior
            $('a[href="' + previousTab + '"]').tab('show'); // Manually trigger the previous tab
        }

        // Function to format time
        function formatTime(seconds) {
            var hours = Math.floor(seconds / 3600);
            var minutes = Math.floor((seconds % 3600) / 60);
            var seconds = seconds % 60;

            return (
                (hours < 10 ? '0' : '') +
                hours +
                ':' +
                (minutes < 10 ? '0' : '') +
                minutes +
                ':' +
                (seconds < 10 ? '0' : '') +
                seconds
            );
        }
        // Function to convert time string to total seconds
        function timeStringToSeconds(timeString) {
            var timeArray = timeString.split(':');
            return parseInt(timeArray[0]) * 3600 + parseInt(timeArray[1]) * 60 + parseInt(timeArray[2]);
        }

        // Function to format time
        function pbFormatTime(seconds) {
            var hours = Math.floor(seconds / 3600);
            var minutes = Math.floor((seconds % 3600) / 60);
            var seconds = seconds % 60;

            return (
                (hours < 10 ? '0' : '') +
                hours +
                ':' +
                (minutes < 10 ? '0' : '') +
                minutes +
                ':' +
                (seconds < 10 ? '0' : '') +
                seconds
            );
        }

        // Function to convert time string to total seconds
        function pbTimeStringToSeconds(timeString) {
            var timeArray = timeString.split(':');
            return parseInt(timeArray[0]) * 3600 + parseInt(timeArray[1]) * 60 + parseInt(timeArray[2]);
        }

        // Function to handle starting the session
        function startSession(client_id) {
            var btn = $('#sessionStatus');
            var ajaxRequest = $.ajax({
                url: '/sessions/live/start',
                type: 'post',
                data: {
                    "client_id": client_id,
                },
                beforeSend: function(xhr) {
                    btn.prop("disabled", true);
                }
            });

            ajaxRequest.done(function(response) {
                if (response.status == 'success') {

                    var badgeElement = $('#sessionStatusText');
                    badgeElement.html('' + response.data.start_time);

                    // Update the button icon and text
                    btn.find('i').removeClass(' ri-play-circle-line').addClass(' ri-stop-circle-line');
                    btn.find('span').text('End');
                    btn.attr('data-session-status', 'active');

                    session_id = response.data.session_id;
                    $('#sessionStatus').attr('data-session-id', session_id);
                    $('#sessionTimer').attr('data-session-id', session_id);
                    $('#pbTimer').attr('data-session-id', session_id);


                    updateTimer(); // Initial update
                    timer = setInterval(updateTimer, 1000);
                    isTimerRunning = true;
                    $('#sessionTimer').attr('data-timer-status', 'active');
                    $('#sessionTimer').find('i').removeClass('ri-play-fill').addClass('ri-pause-fill');
                    $('#sessionTimer').find('span').text('Pause');

                    $('a[href="#dev_prog"]').tab('show');


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
        // Function to handle stopping the session
        function stopSession(client_id) {

            var btn = $('#sessionStatus');
            var pbTimerCheckOnStopSession = $('#pbTimer').attr('data-timer-status');
            if (pbTimerCheckOnStopSession == 'active') {
                showAlert('Error', 'Active problem behavior detected. Stop the behavior before ending the session.', 'error');
                return;
            }
            // If Mands is running, prevent switching to any other tab except PB
            if (isMandActive == 1) {
                showAlert('Error', 'Mands data collection is active. You cannot stop session until stop mands data collection.', 'error');
                return;
            }

            var ajaxRequest = $.ajax({
                url: '/sessions/live/end', // Update the URL to match your endpoint for stopping the session
                type: 'post',
                data: {
                    "client_id": client_id,
                    "session_id": session_id,
                },
                beforeSend: function(xhr) {
                    btn.prop("disabled", true);
                }
            });

            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    window.location.replace("<?= base_url() ?>sessions/review/" + session_id);
                    // Update the button icon and text
                    btn.find('i').removeClass(' ri-play-circle-line').addClass(' ri-stop-circle-line');
                    btn.find('span').text('Completed');
                    btn.attr('data-session-status', 'completed');

                    session_id = '';
                    $('#sessionStatus').attr('data-session-id', session_id);
                    $('#sessionTimer').attr('data-session-id', session_id);
                    $('#pbTimer').attr('data-session-id', session_id);


                    clearInterval(timer); // Pause the timer
                    isTimerRunning = false;
                    $('#sessionTimer').attr('data-timer-status', 'inactive');
                    $('#sessionTimer').find('i').removeClass('ri-pause-fill').addClass('ri-play-fill');
                    $('#sessionTimer').find('span').text('Start');
                    $('#target_table').html('Session closed.');



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
        // Function to handle start/stop the session
        function updateDuration(client_id, action) {
            var btn = $('#sessionTimer');
            var session_id = btn.attr('data-session-id');
            var ajaxRequest = $.ajax({
                url: '/sessions/live/teaching/updateDuration',
                type: 'post',
                data: {
                    "client_id": client_id,
                    "session_id": session_id,
                    "action": action,
                },
                beforeSend: function(xhr) {
                    btn.prop("disabled", true);
                }
            });

            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    if (action === 'stop') {
                        if (isMandActive == 1) {
                            startStopMandsDuration($('#mandsStatus'), 'stop', 0, client_id);
                        }
                        clearInterval(timer); // Pause the timer
                        isTimerRunning = false;
                        btn.attr('data-timer-status', 'inactive');
                        btn.find('i').removeClass('ri-pause-fill').addClass('ri-play-fill');
                        btn.find('span').text('Start');
                    }
                    if (action === 'start') {
                        updateTimer(); // Initial update
                        timer = setInterval(updateTimer, 1000);
                        isTimerRunning = true;
                        btn.attr('data-timer-status', 'active');
                        btn.find('i').removeClass('ri-play-fill').addClass('ri-pause-fill');
                        btn.find('span').text('Pause');


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
        }

        // Function to update the timer
        function updateTimer() {
            $('#teachingTimer').text(formatTime(totalSeconds));
            totalSeconds++;
        }

        // Function to handle start/stop the session
        function updatePB(client_id, action) {
            var btn = $('#pbTimer');
            var session_id = btn.attr('data-session-id');

            var ajaxRequest = $.ajax({
                url: '/sessions/live/teaching/updatePBDuration',
                type: 'post',
                data: {
                    "client_id": client_id,
                    "session_id": session_id,
                    "action": action,
                },
                beforeSend: function(xhr) {
                    btn.prop("disabled", true);
                }
            });

            ajaxRequest.done(function(response) {
                if (response.status == 'success') {


                    if (action === 'stop') {
                        clearInterval(pbTimer); // Pause the timer
                        isPBTimerRunning = false;
                        btn.attr('data-timer-status', 'inactive');
                        btn.find('i').removeClass('ri-pause-fill').addClass('ri-play-fill');
                        btn.find('span').text('Start');

                        var $pbTab = $('a[href="#pb_prog"]');
                        // Check if the PB tab is already active
                        if ($pbTab.hasClass('active')) {
                            // Trigger the click event to refresh the tab content
                            loadPbContent(client_id, session_id);
                        } else {
                            // If the tab is not active, simply show it
                            $pbTab.tab('show');
                        }

                        if (!isTimerRunning) {
                            updateDuration(client_id, 'start');
                        }

                    }
                    if (action === 'start') {
                        if (isMandActive == 1) {
                            startStopMandsDuration($('#mandsStatus'), 'stop', 0, client_id);
                            isMandActive = 0;
                        }
                        pbUpdateTimer(); // Initial update
                        pbTimer = setInterval(pbUpdateTimer, 1000);
                        isPBTimerRunning = true;
                        btn.attr('data-timer-status', 'active');
                        btn.find('i').removeClass('ri-play-fill').addClass('ri-pause-fill');
                        btn.find('span').text('Pause');

                        // Trigger the PB tab to refresh or select it if not selected
                        var $pbTab = $('a[href="#pb_prog"]');
                        // Check if the PB tab is already active
                        if ($pbTab.hasClass('active')) {
                            // Trigger the click event to refresh the tab content
                            loadPbContent(client_id, session_id);
                        } else {
                            // If the tab is not active, simply show it
                            $pbTab.tab('show');
                        }

                        if (isTimerRunning) {
                            updateDuration(client_id, 'stop');
                        }

                        // Load a fresh form in the target_table
                        $.ajax({
                            type: 'POST',
                            url: '<?= site_url('sessions/live/getPbRecordForm') ?>',
                            data: {
                                "pb_timer_id": response.pb_timer_id, // Use the returned pb_timer_id
                                "session_id": session_id,
                                "client_id": client_id
                            },
                            dataType: 'html',
                            success: function(formResponse) {
                                $('#target_table').removeClass();
                                $('#target_table').html(formResponse); // Load the form into the target table
                            },
                            error: function(xhr, status, error) {
                                console.error(xhr.responseText);
                            }
                        });

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
        }
        // Function to update the timer
        function pbUpdateTimer() {
            $('#pbTimerText').text(pbFormatTime(pbTotalSeconds));
            pbTotalSeconds++;
        }
        /***************************************************************************************** */
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
                url: '<?= site_url('sessions/live/target/save_percentage_probe_yes_no') ?>',
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
                url: '<?= site_url('sessions/live/target/save') ?>',
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
        /**************************************************************************************** */

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
            url: '<?= site_url('sessions/live/target/save_stimulus_baseline_attempt') ?>',
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
            url: '<?= site_url('sessions/live/target/save_stimulus_forward_attempt') ?>',
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
            url: '<?= site_url('sessions/live/target/save_stimulus_backward_attempt') ?>',
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
            url: '<?= site_url('sessions/live/target/save_stimulus_total_task_attempt') ?>',
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


    };

    // when loading  stimulus  
    function bindStimulusProbeEvents() {
        console.log('hello');
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
        // For target_list
        $(document).off('click', 'input[type="radio"]');
        $(document).off('click', '#save_mands');
        $(document).off('click', 'input[name="mands_error"]');
        $(document).off('click', 'input[name="initial_attempt"]');
        $(document).off('click', 'input[name="prompt_delay"]');
        $(document).off('click', 'input[name="echoic_1"]');
        $(document).off('click', 'input[name="echoic_2"]');
        $(document).off('click', 'input[name="echoic_3"]');
        $(document).off('input', 'input[name="echoic_1_input"]');
        $(document).off('input', 'input[name="echoic_2_input"]');
    }

    /********************************* */

    // Target History modal (placeholder until endpoint is wired)
    $(document).on('click', '.view-target-history', function () {
        const btn = $(this);
        const targetName = btn.data('target-name') || '';
        const clientId = btn.data('client-id');
        const domainId = btn.data('domain-id');
        const goalId = btn.data('goal-id');
        const targetId = btn.data('target-id');
        const probeSetId = btn.data('probe-set-id');

        $('#targetHistoryModalLabel').text('Target History -> ' + targetName);
        $('#targetHistoryModalBody').html(
            '<div class="text-center text-muted p-4">' +
            '<div class="spinner-border text-primary" role="status"></div>' +
            '<p class="mt-2 mb-0">Loading target history…</p>' +
            '</div>'
        );

        const modalEl = document.getElementById('targetHistoryModal');
        bootstrap.Modal.getOrCreateInstance(modalEl).show();

        $.ajax({
            url: '<?= site_url('sessions/live/viewTargetHistory') ?>',
            type: 'POST',
            data: {
                client_id: clientId,
                domain_id: domainId,
                goal_id: goalId,
                target_id: targetId,
                client_probe_set_id: probeSetId
            },
            success: function (response) {
                if (response && response.success) {
                    $('#targetHistoryModalBody').html(response.html);
                } else {
                    $('#targetHistoryModalBody').html(
                        '<div class="alert alert-danger mb-0">Failed to load target history.</div>'
                    );
                }
            },
            error: function (jqXHR, textStatus, error) {
                $('#targetHistoryModalBody').html(
                    '<div class="alert alert-danger mb-0">Request failed: ' + textStatus + ' — ' + error + '</div>'
                );
            }
        });
    });
</script>
<?= $this->endSection() ?>
