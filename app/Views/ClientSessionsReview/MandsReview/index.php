<?php

use App\Libraries\MandsOptionMetadata;

$mandPromptLevels = MandsOptionMetadata::promptLevelOptions();
$mandErrorOptions = MandsOptionMetadata::mandErrorOptions();
$vocalResponseOptions = MandsOptionMetadata::vocalResponseOptions();
?>
<?= $this->extend("layout/master") ?>
<?= $this->section("head_tag") ?>

<style>
    .autoComplete_wrapper {
        display: block !important;
        width: 70% !important;
    }

    .mands-reinforcer-group .autoComplete_wrapper {
        width: auto !important;
        flex: 1 1 0%;
        min-width: 0;
    }

    @media (min-width: 768px) {
        .mands-reinforcer-group {
            flex-wrap: nowrap !important;
        }
    }

    .mands-reinforcer-group #utteranceInput {
        flex: 1 1 0%;
        min-width: 0;
    }

    .mands-reinforcer-group .input-group-text {
        white-space: nowrap;
    }

    .autoComplete_wrapper>input {
        display: block;
        width: 100% !important;
        height: auto;
        padding: 0.5rem 0.9rem;
        font-size: 0.8125rem;
        font-weight: 400;
        line-height: 1.5;
        color: var(--vz-body-color);
        background-color: var(--vz-input-bg);
        background-clip: padding-box;
        /* border: 1px solid var(--vz-input-border);*/
        border: var(--vz-border-width) solid var(--vz-input-border-custom);
        border-radius: 0px 4px 4px 0px;
        background-image: none;

        background-size: 1.4rem;
        background-position: left 1.05rem top 0.8rem;
        background-repeat: no-repeat;
        background-origin: border-box;
        outline: none;
        text-overflow: ellipsis;
        margin: 0;
        box-sizing: border-box;
        -webkit-box-sizing: border-box;
    }

    .autoComplete_wrapper>input::-webkit-input-placeholder {
        padding: 0 !important;
        color: #878a99 !important;
        font-size: 0.8125rem !important;
    }

    .autoComplete_wrapper>input::-moz-placeholder {
        padding: 0 !important;
        color: #878a99 !important;
        font-size: 0.8125rem !important;
    }

    .autoComplete_wrapper>input:-ms-input-placeholder {
        padding: 0 !important;
        color: #878a99 !important;
        font-size: 0.8125rem !important;
    }

    .autoComplete_wrapper>input::-ms-input-placeholder {
        padding: 0 !important;
        color: #878a99 !important;
        font-size: 0.8125rem !important;
    }

    .autoComplete_wrapper>input::placeholder {
        padding: 0 !important;
        color: #878a99 !important;
        font-size: 0.8125rem !important;
    }

    .autoComplete_wrapper>input:focus {
        /*border: 1px solid var(--vz-input-focus-border);*/
        color: var(--vz-body-color);
    }

    .autoComplete_wrapper>input:hover {
        color: var(--vz-body-color);
    }

    .autoComplete_wrapper>ul {
        border-radius: 0px 4px 4px 0px;
        border-color: var(--vz-border-color);
        background-color: #fff;
        -webkit-box-shadow: 0 5px 10px rgba(30, 32, 37, 0.12);
        box-shadow: 0 5px 10px rgba(30, 32, 37, 0.12);
        padding: 0;
        overflow: auto;
        max-height: 160px;
        margin: 0;
        -webkit-animation-name: DropDownSlide;
        animation-name: DropDownSlide;
        -webkit-animation-duration: 0.3s;
        animation-duration: 0.3s;
        -webkit-animation-fill-mode: both;
        animation-fill-mode: both;
    }

    .autoComplete_wrapper>ul>li {
        font-size: 0.8125rem;
        margin: 0;
        padding: 0.35rem 1.2rem;
        border-radius: 0;
        background-color: #fff;
        color: var(--vz-body-color);
    }

    .autoComplete_wrapper>ul>li mark {
        color: #000000;
        font-weight: 600;
        padding: 1px;
    }

    .autoComplete_wrapper>ul>li[aria-selected=true],
    .autoComplete_wrapper>ul>li:hover {
        color: #1e2125;
        background-color: #f3f6f9;
    }

    .autoComplete_wrapper>ul .no_result {
        padding: 0.7rem 1.2rem;
        font-style: italic;
        font-weight: 500;
    }



    /* Ensure spinner is centered */
    .spinner-border {
        position: absolute;
        top: 50%;
        left: 50%;
    }

    .probe-box-size {
        width: 44px;
    }

    /* Popup container */
    .popup {
        display: none;
        position: absolute;
        z-index: 1000;
        background-color: white;
        border: 1px solid #ccc;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        max-width: 600px;
    }

    /* Container for relative positioning */
    .position-relative {
        position: relative;
    }

    /* Popup container */
    .popup {
        display: none;
        position: absolute;
        z-index: 1000;
        background-color: white;
        border: 1px solid #ccc;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 600px;
    }
</style>
<?= $this->endSection() ?>
<?= $this->section("page_content") ?>
<?= view('ClientSessionsReview/_common_header', ['section_name' => 'Mands']) ?>
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body border-bottom-dashed border-bottom">
                <?= view('ClientSessionsReview/_tabs', ['tab' => 'mands_data']) ?>
                <div class="tab-content">
                    <div class="tab-pane fade show active" role="tabpanel" aria-labelledby="info-tab">
                        <div class="table-responsive">
                            <div id="client_mands_session_data">
                                <table class="table table-bordered" style="width: 100%;" id="client_mands_session_data_dataTable">
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
                                            <th>Probe</th>
                                            <th>Prompt Delay</th>
                                            <th>Probe</th>
                                            <th>Echoic 1</th>
                                            <th>Probe</th>
                                            <th>Echoic 2</th>
                                            <th>Probe</th>
                                            <th>Echoic 3</th>
                                            <th>Probe</th>
                                            <th>Comparison Prompt Delay</th>
                                            <th>Comparison Echoic Trial</th>
                                            <th>Action</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($mandsData as $index => $mand) : ?>
                                            <tr>
                                                <td><?= $index + 1; ?> </td>
                                                <td><?= $mand->reinforcer_input; ?> </td>
                                                <td class="dt-nowrap"><span data-bs-toggle="tooltip" data-bs-placement="top" title="<?= esc($mand->get_prompt_level_tooltip()); ?>"><?= esc($mand->get_prompt_level_label()); ?></span></td>
                                                <td class="dt-nowrap"><?= $mand->utterance_input ?? ''; ?> </td>
                                                <td class="dt-nowrap">
                                                    <?php if ($mand->mands_error != 1): ?>
                                                        <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?= esc($mand->get_mand_error_tooltip()); ?>"><?= esc($mand->get_mand_error_label()); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="dt-nowrap"><?= $mand->get_peer_manding_label(); ?> </td>
                                                <td class="dt-nowrap"><?= $mand->get_eye_contact_label(); ?> </td>
                                                <td class="dt-nowrap"><?= $mand->initial_attempt_input; ?> </td>
                                                <td class="dt-nowrap">
                                                    <?php if ($mand->initial_attempt != 1): ?>
                                                        <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?= esc($mand->get_initial_input_response_tooltip()); ?>"><?= esc($mand->get_initial_input_response_label()); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="dt-nowrap"><?= $mand->prompt_delay_input; ?> </td>
                                                <td class="dt-nowrap">
                                                    <?php if ($mand->prompt_delay != 1): ?>
                                                        <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?= esc($mand->get_prompt_delay_response_tooltip()); ?>"><?= esc($mand->get_prompt_delay_response_label()); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="dt-nowrap"><?= $mand->echoic_1_input; ?> </td>
                                                <td class="dt-nowrap">
                                                    <?php if ($mand->echoic_1 != 1): ?>
                                                        <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?= esc($mand->get_echoic_1_response_tooltip()); ?>"><?= esc($mand->get_echoic_1_response_label()); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="dt-nowrap"><?= $mand->echoic_2_input; ?> </td>
                                                <td class="dt-nowrap">
                                                    <?php if ($mand->echoic_2 != 1): ?>
                                                        <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?= esc($mand->get_echoic_2_response_tooltip()); ?>"><?= esc($mand->get_echoic_2_response_label()); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="dt-nowrap"><?= $mand->echoic_3_input; ?> </td>
                                                <td class="dt-nowrap">
                                                    <?php if ($mand->echoic_3 != 1): ?>
                                                        <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?= esc($mand->get_echoic_3_response_tooltip()); ?>"><?= esc($mand->get_echoic_3_response_label()); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="dt-nowrap"><?= $mand->get_prompt_delay_comparison_label(); ?> </td>
                                                <td class="dt-nowrap"><?= $mand->get_echoic_comparison_label(); ?> </td>
                                                <td class="dt-nowrap">
                                                    <button data-mands-id="<?= $mand->id; ?>" type="button" class="btn btn-sm btn-outline-warning update"><i class="ri-edit-line align-bottom me-1"></i>Edit</button>
                                                    <button data-mands-id="<?= $mand->id; ?>" type="button" class="btn btn-sm btn-outline-danger delete"><i class="ri-delete-bin-line align-bottom me-1"></i>Delete</button>

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
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section("page_modal") ?>
<div class="modal fade" id="update_modal" tabindex="-1" aria-hidden="true">>
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="update_modal_title"> </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="mands_id" id="mands_id">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="d-flex align-items-center justify-content-between">
                            <strong></strong>
                            <button
                                type="button"
                                class="btn btn-link p-0 text-info"
                                onclick="openMandsHelpModal('prompt_level', 'mandsHelpModalReviewEdit', 'mandsHelpTemplatesReviewEdit')"
                                aria-label="Controlling Variable help">
                                <i class="ri-information-line"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="alert alert-dark  alert-top-border  fade show material-shadow" role="alert">
                    <div class="row">
                        <div class="col-sm-12 col-md-12 col-lg-8 ">

                            <div class="input-group pt-3 pb-2 mands-reinforcer-group">
                                <span class="input-group-text" id="inputGroup-sizing-default">Reinforcer</span>
                                <input id="reinforcerInput" name="reinforcerInput" type="text" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default" dir="ltr" spellcheck=false autocomplete="off" autocapitalize="off">
                                <input id="utteranceInput" name="utteranceInput" type="text" class="form-control" aria-label="Utterance" placeholder="Utterance" dir="ltr" spellcheck=false autocomplete="off" autocapitalize="off">
                            </div>
                            <!-- Popup for search results, within the same container -->

                            <div class="btn-group btn-group-sm gap-1" role="group" aria-label="">
                                <?= view('ClientSessions/Mands/_radio_options', [
                                    'options' => $mandPromptLevels,
                                    'name' => 'prompt_level',
                                    'id_prefix' => 'prompt_level_',
                                ]) ?>
                            </div>
                        </div>


                        <div class="col-sm-12 col-md-12 col-lg-4">
                            <div class=" ">
                                <p class="mb-1"><b>Mand Errors</b></p>
                                <!-- Radio Buttons -->
                                <div class="btn-group btn-group-sm gap-1" role="group" aria-label="">
                                    <?= view('ClientSessions/Mands/_radio_options', [
                                        'options' => $mandErrorOptions,
                                        'name' => 'mands_error',
                                        'id_prefix' => 'mands_error_',
                                    ]) ?>
                                </div>
                                <div class="d-flex flex-wrap gap-3 mt-2">
                                    <div class="form-check form-check-primary mb-0">
                                        <input class="form-check-input" type="checkbox" id="is_peer_manding" name="is_peer_manding" value="1">
                                        <label class="form-check-label" for="is_peer_manding">Peer Manding</label>
                                    </div>
                                    <div class="form-check form-check-primary mb-0">
                                        <input class="form-check-input" type="checkbox" id="is_eye_contact" name="is_eye_contact" value="1">
                                        <label class="form-check-label" for="is_eye_contact">Eye Contact</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12">
                        <div class="d-flex align-items-center justify-content-between">
                            <strong>Vocal Response</strong>
                            <button
                                type="button"
                                class="btn btn-link p-0 text-info"
                                onclick="openMandsHelpModal('vocal_response_box', 'mandsHelpModalReviewEdit', 'mandsHelpTemplatesReviewEdit')"
                                aria-label="Vocal Response help">
                                <i class="ri-information-line"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="alert alert-info  alert-top-border  fade show material-shadow" role="alert">

                    <div class="row">
                        <div class="col-sm-12 col-md-12 col-lg-4 initial-attempt-section">
                            <div class=" ">
                                <div class="input-group input-group-md pb-2">
                                    <span class="input-group-text" id="inputGroup-sizing-md">Initial Attempt</span>
                                    <input name="initial_attempt_input" type="text" class="form-control initial-attempt-input" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-md">
                                </div>
                                <!-- Radio Buttons -->
                                <div class="btn-group  btn-group-sm initial-attempt-radio-group gap-1" role="group" aria-label="">
                                    <?= view('ClientSessions/Mands/_radio_options', [
                                        'options' => $vocalResponseOptions,
                                        'name' => 'initial_attempt',
                                        'id_prefix' => 'initial_attempt_',
                                        'input_class' => 'initial-attempt-radio',
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-12 col-lg-8 prompt-delay-section">
                            <div class=" ">
                                <div class="input-group input-group-md pb-2">
                                    <span class="input-group-text" id="inputGroup-sizing-md">Prompt Delay</span>
                                    <input name="prompt_delay_input" type="text" class="form-control prompt-delay-input" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-md">
                                </div>
                                <!-- Radio Buttons -->
                                <div class="btn-group  btn-group-sm prompt-delay-radio-group gap-1" role="group" aria-label="">
                                    <?= view('ClientSessions/Mands/_radio_options', [
                                        'options' => $vocalResponseOptions,
                                        'name' => 'prompt_delay',
                                        'id_prefix' => 'prompt_delay_',
                                        'input_class' => 'prompt-delay-radio',
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12">
                        <div class="d-flex align-items-center justify-content-between">
                            <strong>Echoic</strong>
                            <button
                                type="button"
                                class="btn btn-link p-0 text-info"
                                onclick="openMandsHelpModal('echoic', 'mandsHelpModalReviewEdit', 'mandsHelpTemplatesReviewEdit')"
                                aria-label="Echoic help">
                                <i class="ri-information-line"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="alert alert-success alert-top-border fade show material-shadow" role="alert">
                    <div class="row">
                        <div class="col-sm-12 col-md-12 col-lg-4  echoic-section-1">
                            <div class=" ">
                                <div class="input-group input-group-md pb-2">
                                    <span class="input-group-text" id="inputGroup-sizing-md">Echoic 1</span>
                                    <input name="echoic_1_input" type="text" class="form-control echoic-input" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-md">
                                </div>
                                <!-- Radio Buttons -->
                                <div class="btn-group btn-group-sm echoic-radio-group gap-1" role="group" aria-label="">
                                    <?= view('ClientSessions/Mands/_radio_options', [
                                        'options' => $vocalResponseOptions,
                                        'name' => 'echoic_1',
                                        'id_prefix' => 'echoic_1_',
                                        'input_class' => 'echoic-radio',
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-12 col-lg-4  echoic-section-2">
                            <div class=" ">
                                <div class="input-group input-group-md pb-2">
                                    <span class="input-group-text" id="inputGroup-sizing-md">Echoic 2</span>
                                    <input name="echoic_2_input" type="text" class="form-control echoic-input" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-md" disabled>
                                </div>
                                <!-- Radio Buttons -->
                                <div class="btn-group btn-group-sm echoic-radio-group gap-1" role="group" aria-label="" disabled>
                                    <?= view('ClientSessions/Mands/_radio_options', [
                                        'options' => $vocalResponseOptions,
                                        'name' => 'echoic_2',
                                        'id_prefix' => 'echoic_2_',
                                        'input_class' => 'echoic-radio',
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-12 col-lg-4  echoic-section-3">
                            <div class=" ">
                                <div class="input-group input-group-md pb-2">
                                    <span class="input-group-text" id="inputGroup-sizing-md">Echoic 3</span>
                                    <input name="echoic_3_input" type="text" class="form-control echoic-input" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-md" disabled>
                                </div>
                                <!-- Radio Buttons -->
                                <div class="btn-group btn-group-sm echoic-radio-group gap-1" role="group" aria-label="" disabled>
                                    <?= view('ClientSessions/Mands/_radio_options', [
                                        'options' => $vocalResponseOptions,
                                        'name' => 'echoic_3',
                                        'id_prefix' => 'echoic_3_',
                                        'input_class' => 'echoic-radio',
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Spinner container -->
                <div id="spinner-container" class="position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center d-none" style="z-index: 9999; background-color: rgba(0, 0, 0, 0.5); display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>



            </div>
            <div class="modal-footer">
                <div class="hstack gap-2 justify-content-end">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="ri-close-line align-bottom me-1"></i>Close</button>
                    <button data-client-id="<?= $client->id; ?>" data-session-id="<?= $session->id; ?>" type="button" class="btn btn-primary" id="save_mands"><i class="ri-save-line align-bottom me-1"></i>Update</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?= view('ClientSessions/Mands/_help_templates', ['template_container_id' => 'mandsHelpTemplatesReviewEdit']) ?>
<?= view('ClientSessions/Mands/_help_modal', ['modal_id' => 'mandsHelpModalReviewEdit']) ?>
<?= $this->endSection() ?>
<?= $this->section("page_js") ?>
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
        /***************************************************************************************** */
        var csrfToken = "<?= csrf_hash() ?>";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        });
        var sessionStatus = <?= $session->status ?>; // Get session status from PHP

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
        initMandsTooltips();
        /***************************************************************************************** */
        table = $('#client_mands_session_data_dataTable').DataTable({
            lengthChange: false,
            "ordering": false,
            lengthMenu: [
                [10, 25, 50, -1],
                ['10 rows', '25 rows', '50 rows', 'Show all']
            ],
            layout: {
                topStart: {
                    buttons: [{
                            text: '<i class="ri-add-line align-bottom me-1"></i>Add Mands Data Manually',
                            className: 'btn btn-soft-info waves-effect waves-light material-shadow-none',
                            attr: {
                                id: 'add_executed_session_mands_manually'
                            },
                            action: function(e, dt, node, config) {
                                // Add your action for the button here
                                show_add_mands_canvas();
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

        function show_add_mands_canvas() {
            const baseUrl = '<?= base_url('sessions/review/manually-mands-entry') ?>';
            const sessionId = "<?= $session->id; ?>";

            // Create the URL with query parameters
            const redirectUrl = `${baseUrl}/${sessionId}`;

            // Redirect to the constructed URL
            window.location.href = redirectUrl;

        };
        /***************************************************************************************** */
        $("#client_mands_session_data_dataTable").on('click', '.update', function(e) {
            var btn = $(this);
            var mands_id = $(this).attr('data-mands-id');
            current_row = $(this).parents('tr');
            if (current_row.hasClass('child')) {
                current_row = current_row.prev();
            }
            console.log(current_row);
            var ajaxRequest = $.ajax({
                url: '<?php echo base_url() ?>/sessions/review/getMandsRecord',
                type: 'post',
                data: {
                    "mands_id": mands_id,
                },
                beforeSend: function(xhr) {
                    btn.prop("disabled", true);
                }
            });

            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    const data = response.data;
                    reset_mands_form(); // Ensure the form is reset before populating new data
                    // **Step 1**: First, update radio buttons and trigger logic for enabling/disabling fields.
                    if (data.prompt_level) {
                        $('input[name="prompt_level"][value="' + data.prompt_level + '"]').prop('checked', true);
                    }

                    if (data.mands_error) {
                        $('input[name="mands_error"][value="' + data.mands_error + '"]').prop('checked', true);
                    }
                    $('#is_peer_manding').prop('checked', parseInt(data.is_peer_manding) === 1);
                    $('#is_eye_contact').prop('checked', parseInt(data.is_eye_contact) === 1);

                    if (data.initial_attempt) {
                        $('input[name="initial_attempt"][value="' + data.initial_attempt + '"]').prop('checked', true).trigger('click');
                    }

                    if (data.prompt_delay) {
                        $('input[name="prompt_delay"][value="' + data.prompt_delay + '"]').prop('checked', true).trigger('click');
                    }

                    if (data.echoic_1) {
                        $('input[name="echoic_1"][value="' + data.echoic_1 + '"]').prop('checked', true).trigger('click');
                    }

                    if (data.echoic_2) {
                        $('input[name="echoic_2"][value="' + data.echoic_2 + '"]').prop('checked', true).trigger('click');
                    }

                    if (data.echoic_3) {
                        $('input[name="echoic_3"][value="' + data.echoic_3 + '"]').prop('checked', true).trigger('click');
                    }

                    // **Step 2**: Now that fields are correctly enabled/disabled, update the input fields with the correct data.
                    $('#mands_id').val(data.id);
                    $('#reinforcerInput').val(data.reinforcer_input);
                    $('#utteranceInput').val(data.utterance_input ?? '');
                    $('input[name="initial_attempt_input"]').val(data.initial_attempt_input);
                    $('input[name="prompt_delay_input"]').val(data.prompt_delay_input);
                    // Set values and trigger the input event for echoic inputs to ensure logic for enabling/disabling fields is triggered
                    if (data.echoic_1_input) {
                        $('input[name="echoic_1_input"]').val(data.echoic_1_input).trigger('input');
                    }

                    if (data.echoic_2_input) {
                        $('input[name="echoic_2_input"]').val(data.echoic_2_input).trigger('input');
                    }

                    $('input[name="echoic_3_input"]').val(data.echoic_3_input);



                    // Show the modal with the populated data.
                    $('#update_modal #update_modal_title').html('Update Mand Reinforcer: ' + data.reinforcer_input);
                    $('#update_modal').modal('show');
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

        $('#update_modal').on('hidden.bs.modal', function(e) {
            reset_mands_form();

        });


        /***************************************************************************************** */
        $("#client_mands_session_data_dataTable").on('click', '.delete', function(e) {
            var mands_id = $(this).attr('data-mands-id');
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
                        url: '<?php echo base_url() ?>sessions/review/deleteMandsRecord',
                        type: 'post',
                        data: {
                            "mands_id": mands_id,
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

        /********************************************************************* */
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });
        // Autocomplete
        var autoCompleteFruit = new autoComplete({
            selector: "#reinforcerInput",
            placeHolder: "Search for Reinforcer...",
            data: {
                src: async (query) => {
                    try {
                        // Fetch Data from external Source

                        const source = await fetch(`<?= site_url('mands/reinforcer/search') ?>?query=${encodeURIComponent(query)}&client_id=<?= $client->id ?>`);

                        // Data should be an array of `Objects` or `Strings`
                        const data = await source.json();

                        return data;
                    } catch (error) {
                        return error;
                    }
                },
                keys: ["name"],
                cache: false
            },
            resultsList: {
                element: function element(list, data) {
                    if (!data.results.length) {
                        // Create "No Results" message element
                        var message = document.createElement("div");
                        // Add class to the created element
                        message.setAttribute("class", "no_result");
                        // Add message text content
                        message.innerHTML = "<span>Found No Results for \"" + data.query + "\"</span>";
                        // Append message element to the results list
                        list.prepend(message);
                    }
                },
                maxResults: 50,
                noResults: false
            },
            resultItem: {
                highlight: true
            },
            events: {
                input: {
                    selection: function selection(event) {
                        var selection = event.detail.selection.value.name;
                        autoCompleteFruit.input.value = selection;
                    }
                }
            }
        });

        /**************************************************************************************** */

        $("#save_mands").on('click', function(event) {
            // Prevent default behavior to avoid accidental double-clicks    

            event.preventDefault();
            var spinnerContainer = $('#spinner-container');
            var button = $(this);
            if (button.prop('disabled')) {
                return;
            }
            button.prop('disabled', true).text('Processing...');

            // Retrieve values from the clicked Save button
            var client_id = button.attr('data-client-id');
            var session_id = button.attr('data-session-id');

            // Gather input values
            var mands_id = $('#mands_id').val();
            var reinforcerInputValue = $('#reinforcerInput').val();
            var utteranceInputValue = $('#utteranceInput').val();
            var promptLevelValue = $('input[name="prompt_level"]:checked').val();
            var mandsErrorValue = $('input[name="mands_error"]:checked').val();
            var initialAttemptInputValue = $('input[name="initial_attempt_input"]').val();
            var initialAttemptValue = $('input[name="initial_attempt"]:checked').val();
            var timeDelayInputValue = $('input[name="prompt_delay_input"]').val();
            var timeDelayValue = $('input[name="prompt_delay"]:checked').val();
            var echoic1InputValue = $('input[name="echoic_1_input"]').val();
            var echoic1Value = $('input[name="echoic_1"]:checked').val();
            var echoic2InputValue = $('input[name="echoic_2_input"]').val();
            var echoic2Value = $('input[name="echoic_2"]:checked').val();
            var echoic3InputValue = $('input[name="echoic_3_input"]').val();
            var echoic3Value = $('input[name="echoic_3"]:checked').val();
            var isPeerMandingValue = $('#is_peer_manding').is(':checked') ? 1 : 0;
            var isEyeContactValue = $('#is_eye_contact').is(':checked') ? 1 : 0;

            // AJAX request to send data to server
            var ajaxRequest = $.ajax({
                type: 'POST',
                url: '<?= site_url('sessions/review/updateMandsRecord') ?>',
                data: {
                    "id": mands_id,
                    "client_id": client_id,
                    "session_id": session_id,
                    "reinforcer_input": reinforcerInputValue,
                    "utterance_input": utteranceInputValue,
                    "prompt_level": promptLevelValue,
                    "mands_error": mandsErrorValue,
                    "initial_attempt_input": initialAttemptInputValue,
                    "initial_attempt": initialAttemptValue,
                    "prompt_delay_input": timeDelayInputValue,
                    "prompt_delay": timeDelayValue,
                    "echoic_1_input": echoic1InputValue,
                    "echoic_1": echoic1Value,
                    "echoic_2_input": echoic2InputValue,
                    "echoic_2": echoic2Value,
                    "echoic_3_input": echoic3InputValue,
                    "echoic_3": echoic3Value,
                    "is_peer_manding": isPeerMandingValue,
                    "is_eye_contact": isEyeContactValue
                },
                dataType: 'json',
                beforeSend: function(xhr) {
                    spinnerContainer.removeClass('d-none');
                }
            });

            ajaxRequest.done(function(response) {
                if (response.success == 'Yes') {
                    // Find the current active card

                    const newRow = $(response.data);
                    // Find the index of the current row in DataTable
                    var rowIndex = table.row(current_row).index();

                    if (rowIndex !== -1) {
                        var currentIndex = current_row.find('td:first').text(); // Preserve row number
                        newRow.find('td:first').text(currentIndex);

                        // Replace the row in DataTable
                        table.row(rowIndex).data(newRow.children("td").map(function() {
                            return $(this).html();
                        }).get()).draw(false);
                        initMandsTooltips();
                    }

                    $('#update_modal').modal('hide');
                    Toast.fire({
                        icon: "success",
                        title: "" + response.message
                    });


                } else {
                    button.prop('disabled', false).text('Update');
                    showAlert('Error', response.message, 'error');
                }
            });

            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });

            ajaxRequest.always(function() {
                button.prop('disabled', false).text('Update'); // Enable the button on error
                spinnerContainer.addClass('d-none');
            });
        });



        $('.echoic-section-2').find('.echoic-radio').prop('disabled', true);
        $('.echoic-section-3').find('.echoic-radio').prop('disabled', true);

        var prev_checked_radio_for_mands_error = null;
        var prev_checked_radio_for_initial_attempt = null;
        var prev_checked_radio_for_prompt_delay = null;
        var prev_checked_radio_for_echoic_1 = null;
        var prev_checked_radio_for_echoic_2 = null;
        var prev_checked_radio_for_echoic_3 = null;


        $('input[name="mands_error"]').click(function() {
            // Check if the radio button is already checked
            var isChecked = $(this).prop('checked');

            // If already checked, uncheck it
            if (isChecked && this === prev_checked_radio_for_mands_error) {
                $(this).prop('checked', false);
                prev_checked_radio_for_mands_error = null;
            } else {
                prev_checked_radio_for_mands_error = this;
            }
        });


        $('input[name="initial_attempt"]').click(function() {
            // Check if the radio button is already checked
            var isChecked = $(this).prop('checked');
            var initialAttemptValue = $('input[name="initial_attempt"]:checked').val();

            // If already checked, uncheck it
            if (isChecked && this === prev_checked_radio_for_initial_attempt) {
                $(this).prop('checked', false);
                var prevValueIs5 = prev_checked_radio_for_initial_attempt != null && $(prev_checked_radio_for_initial_attempt).val() == 5;
                if (prevValueIs5) {
                    $('input[name="initial_attempt_input"]').prop('disabled', false);
                    $('input[name="initial_attempt_input"]').val('');
                    $('input[name="initial_attempt_input"]').attr('placeholder', '');
                    /** */
                    $('input[name="prompt_delay_input"]').prop('disabled', false);
                    $('input[name="prompt_delay_input"]').val('');
                    $('input[name="prompt_delay"]').prop('checked', false);
                    $('input[name="prompt_delay"]').prop('disabled', false);
                    $('input[name="prompt_delay_input"]').attr('placeholder', '');
                    /** */
                    $('input[name="echoic_1_input"]').prop('disabled', false);
                    $('input[name="echoic_1_input"]').val('');
                    $('input[name="echoic_1"]').prop('checked', false);
                    $('input[name="echoic_1"]').prop('disabled', false);
                    $('input[name="echoic_1_input"]').attr('placeholder', '');

                    /** */
                    $('input[name="echoic_2_input"]').prop('disabled', true);
                    $('input[name="echoic_2_input"]').val('');
                    $('input[name="echoic_2"]').prop('checked', false);
                    $('input[name="echoic_2"]').prop('disabled', true);
                    $('input[name="echoic_2_input"]').attr('placeholder', '');
                    /** */
                    $('input[name="echoic_3_input"]').prop('disabled', true);
                    $('input[name="echoic_3_input"]').val('');
                    $('input[name="echoic_3"]').prop('checked', false);
                    $('input[name="echoic_3"]').prop('disabled', true);
                    $('input[name="echoic_3_input"]').attr('placeholder', '');
                    /** */
                    prev_checked_radio_for_initial_attempt = null;
                    prev_checked_radio_for_prompt_delay = null;
                    prev_checked_radio_for_echoic_1 = null;
                    prev_checked_radio_for_echoic_2 = null;
                    prev_checked_radio_for_echoic_3 = null;
                } else {
                    $('input[name="initial_attempt_input"]').prop('disabled', false);
                    //$('input[name="initial_attempt_input"]').val('');
                    $('input[name="initial_attempt_input"]').attr('placeholder', '');
                    prev_checked_radio_for_initial_attempt = null;
                }


            } else {

                // Check if previous value is 5
                var prevValueIs5 = prev_checked_radio_for_initial_attempt != null && $(prev_checked_radio_for_initial_attempt).val() == 5;

                prev_checked_radio_for_initial_attempt = this;

                if (prevValueIs5 && (initialAttemptValue == 2 || initialAttemptValue == 3 || initialAttemptValue == 4)) {
                    $('input[name="initial_attempt_input"]').prop('disabled', false);
                    $('input[name="initial_attempt_input"]').attr('placeholder', '');
                    /** */
                    $('input[name="prompt_delay_input"]').prop('disabled', false);
                    $('input[name="prompt_delay_input"]').val('');
                    $('input[name="prompt_delay"]').prop('checked', false);
                    $('input[name="prompt_delay"]').prop('disabled', false);
                    $('input[name="prompt_delay_input"]').attr('placeholder', '');
                    /** */
                    $('input[name="echoic_1_input"]').prop('disabled', false);
                    $('input[name="echoic_1_input"]').val('');
                    $('input[name="echoic_1"]').prop('checked', false);
                    $('input[name="echoic_1"]').prop('disabled', false);
                    $('input[name="echoic_1_input"]').attr('placeholder', '');

                    /** */
                    $('input[name="echoic_2_input"]').prop('disabled', true);
                    $('input[name="echoic_2_input"]').val('');
                    $('input[name="echoic_2"]').prop('checked', false);
                    $('input[name="echoic_2"]').prop('disabled', true);
                    $('input[name="echoic_2_input"]').attr('placeholder', '');
                    /** */
                    $('input[name="echoic_3_input"]').prop('disabled', true);
                    $('input[name="echoic_3_input"]').val('');
                    $('input[name="echoic_3"]').prop('checked', false);
                    $('input[name="echoic_3"]').prop('disabled', true);
                    $('input[name="echoic_3_input"]').attr('placeholder', '');
                    /** */
                    // Add your logic here for when the previous value was 5
                }
                if (!prevValueIs5 && (initialAttemptValue == 2 || initialAttemptValue == 3 || initialAttemptValue == 4)) {
                    $('input[name="initial_attempt_input"]').prop('disabled', false);
                    $('input[name="initial_attempt_input"]').attr('placeholder', '');

                }
                if ((initialAttemptValue == 5)) {
                    /** */
                    $('input[name="initial_attempt_input"]').prop('disabled', true);
                    $('input[name="initial_attempt_input"]').val('');
                    $('input[name="initial_attempt_input"]').attr('placeholder', 'Not required on AF');
                    /** */
                    $('input[name="prompt_delay_input"]').prop('disabled', true);
                    $('input[name="prompt_delay_input"]').val('');
                    $('input[name="prompt_delay"]').prop('checked', false);
                    $('input[name="prompt_delay"]').prop('disabled', true);
                    $('input[name="prompt_delay_input"]').attr('placeholder', '');
                    /** */
                    $('input[name="echoic_1_input"]').prop('disabled', true);
                    $('input[name="echoic_1_input"]').val('');
                    $('input[name="echoic_1"]').prop('checked', false);
                    $('input[name="echoic_1"]').prop('disabled', true);
                    $('input[name="echoic_1_input"]').attr('placeholder', '');

                    /** */
                    $('input[name="echoic_2_input"]').prop('disabled', true);
                    $('input[name="echoic_2_input"]').val('');
                    $('input[name="echoic_2"]').prop('checked', false);
                    $('input[name="echoic_2"]').prop('disabled', true);
                    $('input[name="echoic_2_input"]').attr('placeholder', '');
                    /** */
                    $('input[name="echoic_3_input"]').prop('disabled', true);
                    $('input[name="echoic_3_input"]').val('');
                    $('input[name="echoic_3"]').prop('checked', false);
                    $('input[name="echoic_3"]').prop('disabled', true);
                    $('input[name="echoic_3_input"]').attr('placeholder', '');
                    /** */

                    prev_checked_radio_for_prompt_delay = null;
                    prev_checked_radio_for_echoic_1 = null;
                    prev_checked_radio_for_echoic_2 = null;
                    prev_checked_radio_for_echoic_3 = null;

                }

            }


        });

        $('input[name="prompt_delay"]').click(function() {
            // Check if the radio button is already checked
            var isChecked = $(this).prop('checked');
            var promptDelayValue = $('input[name="prompt_delay"]:checked').val();

            // If already checked, uncheck it
            if (isChecked && this === prev_checked_radio_for_prompt_delay) {
                $(this).prop('checked', false);

                var prevValueIs5 = prev_checked_radio_for_prompt_delay != null && $(prev_checked_radio_for_prompt_delay).val() == 5;

                if (prevValueIs5) {
                    $('input[name="prompt_delay_input"]').prop('disabled', false);
                    $('input[name="prompt_delay_input"]').val('');
                    $('input[name="prompt_delay_input"]').attr('placeholder', '');
                    /** */
                    $('input[name="echoic_1_input"]').prop('disabled', false);
                    $('input[name="echoic_1_input"]').val('');
                    $('input[name="echoic_1"]').prop('checked', false);
                    $('input[name="echoic_1"]').prop('disabled', false);
                    $('input[name="echoic_1_input"]').attr('placeholder', '');

                    /** */
                    $('input[name="echoic_2_input"]').prop('disabled', true);
                    $('input[name="echoic_2_input"]').val('');
                    $('input[name="echoic_2"]').prop('checked', false);
                    $('input[name="echoic_2"]').prop('disabled', true);
                    $('input[name="echoic_2_input"]').attr('placeholder', '');
                    /** */
                    $('input[name="echoic_3_input"]').prop('disabled', true);
                    $('input[name="echoic_3_input"]').val('');
                    $('input[name="echoic_3"]').prop('checked', false);
                    $('input[name="echoic_3"]').prop('disabled', true);
                    $('input[name="echoic_3_input"]').attr('placeholder', '');
                    /** */
                    prev_checked_radio_for_prompt_delay = null;
                    prev_checked_radio_for_echoic_1 = null;
                    prev_checked_radio_for_echoic_2 = null;
                    prev_checked_radio_for_echoic_3 = null;
                } else {
                    $('input[name="prompt_delay_input"]').prop('disabled', false);
                    //$('input[name="prompt_delay_input"]').val('');
                    $('input[name="prompt_delay_input"]').attr('placeholder', '');
                    prev_checked_radio_for_prompt_delay = null;
                }

            } else {
                // Check if previous value is 5
                var prevValueIs5 = prev_checked_radio_for_prompt_delay != null && $(prev_checked_radio_for_prompt_delay).val() == 5;
                prev_checked_radio_for_prompt_delay = this;

                if (prevValueIs5 && (promptDelayValue == 2 || promptDelayValue == 3 || promptDelayValue == 4)) {

                    $('input[name="prompt_delay_input"]').prop('disabled', false);
                    $('input[name="prompt_delay_input"]').attr('placeholder', '');

                    /** */
                    $('input[name="echoic_1_input"]').prop('disabled', true);
                    $('input[name="echoic_1_input"]').val('');
                    $('input[name="echoic_1"]').prop('checked', false);
                    $('input[name="echoic_1"]').prop('disabled', false);
                    $('input[name="echoic_1_input"]').attr('placeholder', '');

                    /** */
                    $('input[name="echoic_2_input"]').prop('disabled', true);
                    $('input[name="echoic_2_input"]').val('');
                    $('input[name="echoic_2"]').prop('checked', false);
                    $('input[name="echoic_2"]').prop('disabled', true);
                    $('input[name="echoic_2_input"]').attr('placeholder', '');
                    /** */
                    $('input[name="echoic_3_input"]').prop('disabled', true);
                    $('input[name="echoic_3_input"]').val('');
                    $('input[name="echoic_3"]').prop('checked', false);
                    $('input[name="echoic_3"]').prop('disabled', true);
                    $('input[name="echoic_3_input"]').attr('placeholder', '');
                    /** */
                }
                if (!prevValueIs5 && (promptDelayValue == 2 || promptDelayValue == 3 || promptDelayValue == 4)) {

                    $('input[name="prompt_delay_input"]').prop('disabled', false);
                    $('input[name="prompt_delay_input"]').attr('placeholder', '');
                    /** */
                }
                if (promptDelayValue == 5) {
                    /** */
                    $('input[name="prompt_delay_input"]').prop('disabled', true);
                    $('input[name="prompt_delay_input"]').val('');
                    $('input[name="prompt_delay_input"]').attr('placeholder', 'Not required on AF');

                    /** */
                    $('input[name="echoic_1_input"]').prop('disabled', true);
                    $('input[name="echoic_1_input"]').val('');
                    $('input[name="echoic_1"]').prop('checked', false);
                    $('input[name="echoic_1"]').prop('disabled', true);
                    $('input[name="echoic_1_input"]').attr('placeholder', '');

                    /** */
                    $('input[name="echoic_2_input"]').prop('disabled', true);
                    $('input[name="echoic_2_input"]').val('');
                    $('input[name="echoic_2"]').prop('checked', false);
                    $('input[name="echoic_2"]').prop('disabled', true);
                    $('input[name="echoic_2_input"]').attr('placeholder', '');
                    /** */
                    $('input[name="echoic_3_input"]').prop('disabled', true);
                    $('input[name="echoic_3_input"]').val('');
                    $('input[name="echoic_3"]').prop('checked', false);
                    $('input[name="echoic_3"]').prop('disabled', true);
                    $('input[name="echoic_3_input"]').attr('placeholder', '');
                    /** */
                    prev_checked_radio_for_echoic_1 = null;
                    prev_checked_radio_for_echoic_2 = null;
                    prev_checked_radio_for_echoic_3 = null;

                }
            }



        });

        $('input[name="echoic_1"]').click(function() {
            // Check if the radio button is already checked
            var isChecked = $(this).prop('checked');
            var echoic1Value = $('input[name="echoic_1"]:checked').val();

            // If already checked, uncheck it
            if (isChecked && this === prev_checked_radio_for_echoic_1) {
                $(this).prop('checked', false);
                var prevValueIs5 = prev_checked_radio_for_echoic_1 != null && $(prev_checked_radio_for_echoic_1).val() == 5;
                if (prevValueIs5) {
                    $('input[name="echoic_1_input"]').prop('disabled', false);
                    $('input[name="echoic_1_input"]').val('');
                    $('input[name="echoic_1_input"]').attr('placeholder', '');
                    /** */
                    $('input[name="echoic_2_input"]').prop('disabled', true);
                    $('input[name="echoic_2_input"]').val('');
                    $('input[name="echoic_2"]').prop('checked', false);
                    $('input[name="echoic_2"]').prop('disabled', true);
                    $('input[name="echoic_2_input"]').attr('placeholder', '');
                    /** */
                    $('input[name="echoic_3_input"]').prop('disabled', true);
                    $('input[name="echoic_3_input"]').val('');
                    $('input[name="echoic_3"]').prop('checked', false);
                    $('input[name="echoic_3"]').prop('disabled', true);
                    $('input[name="echoic_3_input"]').attr('placeholder', '');
                    /** */
                    prev_checked_radio_for_echoic_1 = null;
                    prev_checked_radio_for_echoic_2 = null;
                    prev_checked_radio_for_echoic_3 = null;
                } else {
                    $('input[name="echoic_1_input"]').prop('disabled', false);
                    //$('input[name="echoic_1_input"]').val('');
                    $('input[name="echoic_1_input"]').attr('placeholder', '');
                    prev_checked_radio_for_echoic_1 = null;
                }

            } else {
                var prevValueIs5 = prev_checked_radio_for_echoic_1 != null && $(prev_checked_radio_for_echoic_1).val() == 5;
                prev_checked_radio_for_echoic_1 = this;

                if (prevValueIs5 && (echoic1Value == 2 || echoic1Value == 3 || echoic1Value == 4)) {

                    $('input[name="echoic_1_input"]').prop('disabled', false);
                    $('input[name="echoic_1_input"]').attr('placeholder', '');

                    /** */
                    $('input[name="echoic_2_input"]').prop('disabled', true);
                    $('input[name="echoic_2_input"]').val('');
                    $('input[name="echoic_2"]').prop('checked', false);
                    $('input[name="echoic_2"]').prop('disabled', true);
                    $('input[name="echoic_2_input"]').attr('placeholder', '');
                    /** */
                    $('input[name="echoic_3_input"]').prop('disabled', true);
                    $('input[name="echoic_3_input"]').val('');
                    $('input[name="echoic_3"]').prop('checked', false);
                    $('input[name="echoic_3"]').prop('disabled', true);
                    $('input[name="echoic_3_input"]').attr('placeholder', '');
                    /** */
                }
                if (!prevValueIs5 && (echoic1Value == 2 || echoic1Value == 3 || echoic1Value == 4)) {

                    $('input[name="echoic_1_input"]').prop('disabled', false);
                    $('input[name="echoic_1_input"]').attr('placeholder', '');

                }
                if (echoic1Value == 5) {
                    /** */
                    $('input[name="echoic_1_input"]').prop('disabled', true);
                    $('input[name="echoic_1_input"]').val('');
                    $('input[name="echoic_1_input"]').attr('placeholder', 'Not required on AF');


                    /** */
                    $('input[name="echoic_2_input"]').prop('disabled', true);
                    $('input[name="echoic_2_input"]').val('');
                    $('input[name="echoic_2"]').prop('checked', false);
                    $('input[name="echoic_2"]').prop('disabled', true);
                    $('input[name="echoic_2_input"]').attr('placeholder', '');
                    /** */
                    $('input[name="echoic_3_input"]').prop('disabled', true);
                    $('input[name="echoic_3_input"]').val('');
                    $('input[name="echoic_3"]').prop('checked', false);
                    $('input[name="echoic_3"]').prop('disabled', true);
                    $('input[name="echoic_3_input"]').attr('placeholder', '');
                    /** */

                    prev_checked_radio_for_echoic_2 = null;
                    prev_checked_radio_for_echoic_3 = null;

                }
            }



        });

        $('input[name="echoic_2"]').click(function() {
            // Check if the radio button is already checked
            var isChecked = $(this).prop('checked');
            var echoic2Value = $('input[name="echoic_2"]:checked').val();

            // If already checked, uncheck it
            if (isChecked && this === prev_checked_radio_for_echoic_2) {
                $(this).prop('checked', false);
                var prevValueIs5 = prev_checked_radio_for_echoic_2 != null && $(prev_checked_radio_for_echoic_2).val() == 5;

                if (prevValueIs5) {
                    $('input[name="echoic_2_input"]').prop('disabled', true);
                    $('input[name="echoic_2_input"]').val('');
                    $('input[name="echoic_2_input"]').attr('placeholder', '');
                    /** */
                    $('input[name="echoic_3_input"]').prop('disabled', true);
                    $('input[name="echoic_3_input"]').val('');
                    $('input[name="echoic_3"]').prop('checked', false);
                    $('input[name="echoic_3"]').prop('disabled', true);
                    $('input[name="echoic_3_input"]').attr('placeholder', '');
                    /** */
                    prev_checked_radio_for_echoic_2 = null;
                    prev_checked_radio_for_echoic_3 = null;
                } else {
                    $('input[name="echoic_2_input"]').prop('disabled', false);
                    //$('input[name="echoic_2_input"]').val('');
                    $('input[name="echoic_2_input"]').attr('placeholder', '');
                    /** */
                    prev_checked_radio_for_echoic_2 = null;
                }


            } else {
                var prevValueIs5 = prev_checked_radio_for_echoic_2 != null && $(prev_checked_radio_for_echoic_2).val() == 5;
                prev_checked_radio_for_echoic_2 = this;


                if (prevValueIs5 && (echoic2Value == 2 || echoic2Value == 3 || echoic2Value == 4)) {

                    $('input[name="echoic_2_input"]').prop('disabled', false);
                    $('input[name="echoic_2_input"]').attr('placeholder', '');
                    /** */
                    $('input[name="echoic_3_input"]').prop('disabled', true);
                    $('input[name="echoic_3_input"]').val('');
                    $('input[name="echoic_3"]').prop('checked', false);
                    $('input[name="echoic_3"]').prop('disabled', true);
                    $('input[name="echoic_3_input"]').attr('placeholder', '');
                    /** */
                }
                if (!prevValueIs5 && (echoic2Value == 2 || echoic2Value == 3 || echoic2Value == 4)) {

                    $('input[name="echoic_2_input"]').prop('disabled', false);
                    $('input[name="echoic_2_input"]').attr('placeholder', '');

                }
                if (echoic2Value == 5) {
                    /** */
                    $('input[name="echoic_2_input"]').prop('disabled', true);
                    $('input[name="echoic_2_input"]').val('');
                    $('input[name="echoic_2_input"]').attr('placeholder', 'Not required on AF');
                    /** */
                    $('input[name="echoic_3_input"]').prop('disabled', true);
                    $('input[name="echoic_3_input"]').val('');
                    $('input[name="echoic_3"]').prop('checked', false);
                    $('input[name="echoic_3"]').prop('disabled', true);
                    $('input[name="echoic_3_input"]').attr('placeholder', '');
                    /** */
                    prev_checked_radio_for_echoic_3 = null;

                }
            }


        });

        $('input[name="echoic_3"]').click(function() {
            // Check if the radio button is already checked
            var isChecked = $(this).prop('checked');
            var echoic3Value = $('input[name="echoic_3"]:checked').val();

            // If already checked, uncheck it
            if (isChecked && this === prev_checked_radio_for_echoic_3) {
                $(this).prop('checked', false);
                prev_checked_radio_for_echoic_3 = null;
                $('input[name="echoic_3_input"]').prop('disabled', false);
                //$('input[name="echoic_3_input"]').val('');
                $('input[name="echoic_3_input"]').attr('placeholder', '');
            } else {
                var prevValueIs5 = prev_checked_radio_for_echoic_3 != null && $(prev_checked_radio_for_echoic_3).val() == 5;
                prev_checked_radio_for_echoic_3 = this;
                if (prevValueIs5 && (echoic3Value == 2 || echoic3Value == 3 || echoic3Value == 4)) {

                    $('input[name="echoic_3_input"]').prop('disabled', false);
                    $('input[name="echoic_3_input"]').attr('placeholder', '');

                }
                if (!prevValueIs5 && (echoic3Value == 2 || echoic3Value == 3 || echoic3Value == 4)) {
                    $('input[name="echoic_3_input"]').prop('disabled', false);
                    $('input[name="echoic_3_input"]').attr('placeholder', '');

                }
                if (echoic3Value == 5) {
                    /** */
                    $('input[name="echoic_3_input"]').prop('disabled', true);
                    $('input[name="echoic_3_input"]').val('');
                    $('input[name="echoic_3_input"]').attr('placeholder', 'Not required on AF');

                }
            }


        });

        $('input[name="echoic_1_input"]').on('input', function() {
            var currentValue = $(this).val();
            if (currentValue != '') {
                $('input[name="echoic_2"]').prop('disabled', false);
                $('input[name="echoic_2_input"]').prop('disabled', false);

            } else {
                $('input[name="echoic_2_input"]').val('');
                $('input[name="echoic_2_input"]').prop('disabled', true);
                $('input[name="echoic_2"]').prop('checked', false);
                $('input[name="echoic_2"]').prop('disabled', true);

                $('input[name="echoic_3_input"]').val('');
                $('input[name="echoic_3_input"]').prop('disabled', true);
                $('input[name="echoic_3"]').prop('checked', false);
                $('input[name="echoic_3"]').prop('disabled', true);

            }
        });
        $('input[name="echoic_2_input"]').on('input', function() {
            var currentValue = $(this).val();
            if (currentValue != '') {
                $('input[name="echoic_3"]').prop('disabled', false);
                $('input[name="echoic_3_input"]').prop('disabled', false);
            } else {
                $('input[name="echoic_3_input"]').val('');
                $('input[name="echoic_3_input"]').prop('disabled', true);
                $('input[name="echoic_3"]').prop('checked', false);
                $('input[name="echoic_3"]').prop('disabled', true);


            }
        });

        function reset_mands_form() {
            // Reset Reinforcer input and results
            $('#mands_id').val('');
            $('#reinforcerInput').val('');
            $('#utteranceInput').val('');
            $('#reinforcerResults').empty();

            // Reset Prompt Level radio buttons
            $('input[name="prompt_level"]').prop('checked', false);

            // Reset Mand Errors radio buttons
            $('input[name="mands_error"]').prop('checked', false);
            $('#is_peer_manding').prop('checked', false);
            $('#is_eye_contact').prop('checked', false);

            $('input[name="initial_attempt_input"]').prop('disabled', false);
            $('input[name="initial_attempt_input"]').val('');
            $('input[name="initial_attempt"]').prop('checked', false);
            /** */
            $('input[name="prompt_delay_input"]').prop('disabled', false);
            $('input[name="prompt_delay_input"]').val('');
            $('input[name="prompt_delay"]').prop('checked', false);
            $('input[name="prompt_delay"]').prop('disabled', false);
            /** */
            $('input[name="echoic_1_input"]').prop('disabled', false);
            $('input[name="echoic_1_input"]').val('');
            $('input[name="echoic_1"]').prop('checked', false);
            $('input[name="echoic_1"]').prop('disabled', false);
            $('input[name="echoic_1_input"]').attr('placeholder', '');

            /** */
            $('input[name="echoic_2_input"]').prop('disabled', true);
            $('input[name="echoic_2_input"]').val('');
            $('input[name="echoic_2"]').prop('checked', false);
            $('input[name="echoic_2"]').prop('disabled', true);
            $('input[name="echoic_2_input"]').attr('placeholder', '');
            /** */
            $('input[name="echoic_3_input"]').prop('disabled', true);
            $('input[name="echoic_3_input"]').val('');
            $('input[name="echoic_3"]').prop('checked', false);
            $('input[name="echoic_3"]').prop('disabled', true);
            $('input[name="echoic_3_input"]').attr('placeholder', '');
            /** */
            $('input[type="radio"]').prop('checked', false);
            prev_checked_radio_for_initial_attempt = null;
            prev_checked_radio_for_prompt_delay = null;
            prev_checked_radio_for_echoic_1 = null;
            prev_checked_radio_for_echoic_2 = null;
            prev_checked_radio_for_echoic_3 = null;
        }

    });
</script>
<?= $this->endSection() ?>