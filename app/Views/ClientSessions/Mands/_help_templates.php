<?php
$templateContainerId = $template_container_id ?? 'mandsHelpTemplates';
?>
<div id="<?= esc($templateContainerId); ?>" class="d-none">
    <div data-help-key="mand_count" data-help-title="Mand Count">
        <p class="mb-0">Tracks mand trials completed so far in the current session. Review this section to confirm all targets have been run, identify gaps, and ensure prompt levels are systematically faded.</p>
    </div>

    <div data-help-key="prompt_level" data-help-title="Controlling Variable (Prompt Level)">
        <p>Record the prompt that evoked the correct mand, not the level used to shape the response.</p>

        <div class="mb-3">
            <strong>FPP - Full Physical Prompt</strong>
            <div>Hand-over-hand guidance for the full sign.</div>
        </div>

        <div class="mb-3">
            <strong>PPP - Partial Physical Prompt</strong>
            <div>Partial physical guidance or physical prompt for part of the sign.</div>
        </div>

        <div class="mb-3">
            <strong>GP - Gestural Prompt</strong>
            <div>Instructor models all or part of the sign.</div>
        </div>

        <div class="mb-3">
            <strong>V - Vocal Prompt</strong>
            <div>Instructor vocally models all or part of the word (echoic or phonemic).</div>
        </div>

        <div class="mb-3">
            <strong>IV - Intraverbal Prompt</strong>
            <div>Response evoked by a verbal antecedent without point-to-point correspondence (e.g., "What do you want?", "Ready, set...").</div>
        </div>

        <div class="mb-3">
            <strong>Item</strong>
            <div>Mand is evoked by the presence of the item, activity, or demonstrated action.</div>
        </div>

        <div class="mb-3">
            <strong>MO - Motivating Operation</strong>
            <div>Mand occurs when the item/action is not present or visible in the current environment.</div>
        </div>

        <div class="mb-0">
            <strong>TMO - Transitive MO</strong>
            <div>Mand occurs for a missing item needed to complete a chain (e.g., "open", "glue", "rewind").</div>
        </div>
    </div>

    <div data-help-key="vocal_response_box" data-help-title="Vocal Response Help">
        <div class="mb-3">
            <strong>Initial Mand Attempt</strong>
            <div>The initial mand attempt is the response emitted without a vocal prompt.</div>
            <div>Only transcribe vocal productions that occur when the mand is under the control of IV, Item, MO, or TMO.</div>
            <div>If a vocal prompt was provided, leave this field blank.</div>
        </div>

        <div class="mb-3">
            <strong>Prompt Delay</strong>
            <div>The Prompt Delay feature allows recording when a brief pause is used to create an opportunity for additional or more independent vocal responding.</div>
        </div>

        <div class="mb-3">
            <strong>Antecedent Prompt Delay</strong>
            <div>Used when a pause of up to 5 seconds occurs after a sign to allow the learner the opportunity to emit a vocal response.</div>
        </div>

        <div class="mb-3">
            <strong>Consequence Prompt Delay</strong>
            <div>Used when a pause of up to 5 seconds occurs after an initial vocal response to allow the learner the opportunity to emit a more complete or clearer vocal response.</div>
        </div>

        <hr class="my-3">

        <div class="mb-3">
            <strong>Speech Sounds</strong>
            <div>Any vocal production containing at least one speech sound (consonant or vowel), including combinations not found in the adult form of the word.</div>
        </div>

        <div class="mb-3">
            <strong>Word Approximations</strong>
            <div>A vocal production containing at least two speech sounds that are part of the adult word form and occurs more than once during the session.</div>
        </div>

        <div class="mb-3">
            <strong>Intelligible Word</strong>
            <div>A word that would be understood by an unfamiliar listener without needing context, but does not include all speech sounds of the adult form.</div>
        </div>

        <div class="mb-0">
            <strong>Adult Form</strong>
            <div>A word containing all speech sounds of the adult form.</div>
        </div>
    </div>

    <div data-help-key="echoic" data-help-title="Echoic">
        <p class="mb-0">These fields allow tracking of vocal responses that occur after a vocal (echoic) prompt.</p>
    </div>
</div>
