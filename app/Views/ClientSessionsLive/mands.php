<?php

use App\Libraries\MandsOptionMetadata;

$mandPromptLevels = MandsOptionMetadata::promptLevelOptions();
$mandErrorOptions = MandsOptionMetadata::mandErrorOptions();
$vocalResponseOptions = MandsOptionMetadata::vocalResponseOptions();
?>
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
<div class="row" id="MandsSessionInactiveAlert" <?= $isMandActive == 1 ? 'hidden="hidden"' : '' ?>>
    <div class="col-md-12">
        <!-- warning Alert -->
        <div class="alert alert-warning" role="alert">
            <strong>Press button on left to Start/Stop mands data collection! </strong>
        </div>
    </div>
</div>
<?php if ($topReinforcer != null): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <?php foreach ($topReinforcer as $reinforcer): ?>
                        <button type="button" class="btn btn-soft-secondary btn-sm material-shadow-none" onclick="setReinforcerInput('<?php echo htmlspecialchars($reinforcer, ENT_QUOTES, 'UTF-8'); ?>')">
                            <?php echo htmlspecialchars($reinforcer, ENT_QUOTES, 'UTF-8'); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <button
                    type="button"
                    class="btn btn-link p-0 text-info"
                    onclick="openMandsHelpModal('prompt_level', 'mandsHelpModalLive', 'mandsHelpTemplatesLive')"
                    aria-label="Controlling Variable help">
                    <i class="ri-information-line"></i>
                </button>
            </div>
        </div>
    </div>
<?php endif; ?>
<div class="alert alert-dark  alert-top-border  fade show material-shadow" role="alert">
    <div class="row">
        <div class="col-sm-12 col-md-12 col-lg-8 ">

            <div class="input-group pt-3 pb-2 mands-reinforcer-group">
                <span class="input-group-text" id="inputGroup-sizing-default">Reinforcer</span>
                <input <?= $isMandActive == 0 ? "disabled" : "" ?> id="reinforcerInput" name="reinforcerInput" type="text" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default" dir="ltr" spellcheck=false autocomplete="off" autocapitalize="off">
                <input <?= $isMandActive == 0 ? "disabled" : "" ?> id="utteranceInput" name="utteranceInput" type="text" class="form-control" aria-label="Utterance" placeholder="Utterance" dir="ltr" spellcheck=false autocomplete="off" autocapitalize="off">
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
                <div class="d-flex flex-wrap align-items-center gap-3 mt-2">
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
                onclick="openMandsHelpModal('vocal_response_box', 'mandsHelpModalLive', 'mandsHelpTemplatesLive')"
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
                onclick="openMandsHelpModal('echoic', 'mandsHelpModalLive', 'mandsHelpTemplatesLive')"
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
<div class="hstack gap-2 justify-content-end">
    <button <?= $isMandActive == 'inactive' ? "disabled" : "" ?> data-client-id="<?= $client_id; ?>" data-session-id="<?= $session_id; ?>" class="btn btn-primary save-button" id="save_mands">Save</button>
</div>
<!-- Spinner container -->
<div id="spinner-container" class="position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center d-none" style="z-index: 9999; background-color: rgba(0, 0, 0, 0.5); display: none;">
    <div class="spinner-border text-primary" role="status">
        <span class="sr-only">Loading...</span>
    </div>
</div>

<script>
    $(document).ready(function() {

        /***************************************************************************************** */
        var csrfToken = "<?= csrf_hash() ?>";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        });
        // Object to store the previous values for each radio button group

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
        // Autocomplete
        var autoCompleteFruit = new autoComplete({
            selector: "#reinforcerInput",
            placeHolder: "Search for Reinforcer...",
            data: {
                src: async (query) => {
                    try {
                        // Fetch Data from external Source
                        //const source = await fetch(`<?= site_url('mands/reinforcer/search') ?>/${query}`);
                        const source = await fetch(`<?= site_url('mands/reinforcer/search') ?>?query=${encodeURIComponent(query)}&client_id=<?= $client_id ?>`);


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
                url: '<?= site_url('sessions/live/mands/save') ?>',
                data: {
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
                    Toast.fire({
                        icon: "success",
                        title: "" + response.message
                    });
                    reset_mands_form();
                    $('#mandsCount').html(response.mandsCount);
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

    });

    $(document).ready(function() {
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

    });

    function reset_mands_form() {
        // Reset Reinforcer input and results
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
        prev_checked_radio_for_initial_attempt = null;
        prev_checked_radio_for_prompt_delay = null;
        prev_checked_radio_for_echoic_1 = null;
        prev_checked_radio_for_echoic_2 = null;
        prev_checked_radio_for_echoic_3 = null;
    }

    function setReinforcerInput(value) {
        document.getElementById('reinforcerInput').value = value;
    }
</script>