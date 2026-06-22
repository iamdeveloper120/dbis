<div class="card border card-border-primary">
    <div class="card-header">
        <h6 class="card-title mb-0"><?= esc($probeSet['name']) ?></h6>
    </div>
    <div class="card-body">
        <p class="card-text"><?= esc($probeSet['description']) ?> <strong>Consecutive Criteria Check Key: <?= $inputs['key'] ?></strong> <small>(This is set to the last prompt level selected in the list below.)</small></p>

        <p>
            <strong>Existing Inputs:</strong>
            <small>Individualise the prompts to be probed by removing any that are not relevant and adding any that are specific to the learner.</small>
            <?= $inputsHtml ?>
        </p>

        <div id="inputContainer">
            <?php if ($inputs['type'] == 'count') : ?>
                <div><strong>Update Range:</strong></div>
                <div class="row">
                    <div class="col-md-4">
                        <!-- Multiple Inputs -->
                        <div class="input-group mb-3">
                            <span class="input-group-text">Min & Max Value</span>
                            <input type="number" aria-label="Minimum Number" class="form-control" name="min" value="<?= $inputs['range']['min'] ?>">
                            <input type="number" aria-label="Maximum Number" class="form-control" name="max" value="<?= $inputs['range']['max'] ?>">
                        </div>
                    </div>
                </div>

            <?php elseif ($inputs['type'] == 'duration') : ?>
                <div id="durationInputsContainer">
                    <?php foreach ($inputs['choices'] as $choice) : ?>
                        <div class="row duration-input-row">
                            <div class="col-md-4">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">Value(Seconds) and Label</span>
                                    <input type="text" aria-label="Value" class="form-control" name="duration_values[]" value="<?= esc($choice['value']) ?>">
                                    <input type="text" aria-label="Label" class="form-control" name="duration_labels[]" value="<?= esc($choice['label']) ?>">
                                    <button class="btn btn-sm btn-danger removeDurationInput" type="button"><i class="ri-close-line"></i>Remove</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="btn btn-sm btn-primary" type="button" id="addDurationInput"><i class="ri-add-line"></i>Add Duration</button>

            <?php elseif ($inputs['type'] == 'prompt_level') : ?>
                <div id="promptLevelInputsContainer">
                    <?php foreach ($inputs['choices'] as $choice) : ?>
                        <div class="row prompt-input-row">
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">Value and Label</span>
                                    <input type="text" aria-label="Value" class="form-control" name="prompt_level_values[]" value="<?= esc($choice['value']) ?>">
                                    <input type="text" aria-label="Label" class="form-control" name="prompt_level_labels[]" value="<?= esc($choice['label']) ?>">
                                    <button class="btn btn-sm btn-outline-secondary movePromptLevelUp" type="button" title="Move up"><i class="ri-arrow-up-line"></i></button>
                                    <button class="btn btn-sm btn-outline-secondary movePromptLevelDown" type="button" title="Move down"><i class="ri-arrow-down-line"></i></button>
                                    <button class="btn btn-sm btn-danger removePromptLevelInput" type="button"><i class="ri-close-line"></i>Remove</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="btn btn-sm btn-primary" type="button" id="addPromptLevelInput"><i class="ri-add-line"></i>Add Prompt Level</button>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#addDurationInput').off('click.probeInputs').on('click.probeInputs', function() {
            $('#durationInputsContainer').append(`
                <div class="row duration-input-row">
                    <div class="col-md-4">
                        <div class="input-group mb-3">
                            <span class="input-group-text">Value(Seconds) and Label</span>
                            <input type="text" aria-label="Value" class="form-control" name="duration_values[]" value="">
                            <input type="text" aria-label="Label" class="form-control" name="duration_labels[]" value="">
                            <button class="btn btn-sm btn-danger removeDurationInput" type="button"><i class="ri-close-line"></i>Remove</button>
                        </div>
                    </div>
                </div>
            `);
            toggleRemoveButtons();
        });

        $(document).off('click.probeInputs', '.removeDurationInput').on('click.probeInputs', '.removeDurationInput', function() {
            $(this).closest('.duration-input-row').remove();
            toggleRemoveButtons();
        });

        function toggleRemoveButtons() {
            if ($('#durationInputsContainer .duration-input-row').length <= 2) {
                $('.removeDurationInput').prop('disabled', true);
            } else {
                $('.removeDurationInput').prop('disabled', false);
            }
        }

        // Initial check to disable remove buttons if necessary
        toggleRemoveButtons();

        $('#addPromptLevelInput').off('click.probeInputs').on('click.probeInputs', function() {
            $('#promptLevelInputsContainer').append(`
                <div class="row prompt-input-row">
                    <div class="col-md-6">
                        <div class="input-group mb-3">
                            <span class="input-group-text">Value and Label</span>
                            <input type="text" aria-label="Value" class="form-control" name="prompt_level_values[]" value="">
                            <input type="text" aria-label="Label" class="form-control" name="prompt_level_labels[]" value="">
                            <button class="btn btn-sm btn-outline-secondary movePromptLevelUp" type="button" title="Move up"><i class="ri-arrow-up-line"></i></button>
                            <button class="btn btn-sm btn-outline-secondary movePromptLevelDown" type="button" title="Move down"><i class="ri-arrow-down-line"></i></button>
                            <button class="btn btn-sm btn-danger removePromptLevelInput" type="button"><i class="ri-close-line"></i>Remove</button>
                        </div>
                    </div>
                </div>
            `);
            toggleRemovePromptButtons();
            refreshPromptLevelMoveButtons();
        });

        $(document).off('click.probeInputs', '.removePromptLevelInput').on('click.probeInputs', '.removePromptLevelInput', function() {
            $(this).closest('.prompt-input-row').remove();
            toggleRemovePromptButtons();
            refreshPromptLevelMoveButtons();
        });

        function toggleRemovePromptButtons() {
            if ($('#promptLevelInputsContainer .prompt-input-row').length <= 2) {
                $('.removePromptLevelInput').prop('disabled', true);
            } else {
                $('.removePromptLevelInput').prop('disabled', false);
            }
        }

        $(document).off('click.probeInputs', '.movePromptLevelUp').on('click.probeInputs', '.movePromptLevelUp', function() {
            var row = $(this).closest('.prompt-input-row');
            row.prev('.prompt-input-row').before(row);
            refreshPromptLevelMoveButtons();
        });

        $(document).off('click.probeInputs', '.movePromptLevelDown').on('click.probeInputs', '.movePromptLevelDown', function() {
            var row = $(this).closest('.prompt-input-row');
            row.next('.prompt-input-row').after(row);
            refreshPromptLevelMoveButtons();
        });

        function refreshPromptLevelMoveButtons() {
            var rows = $('#promptLevelInputsContainer .prompt-input-row');
            rows.find('.movePromptLevelUp, .movePromptLevelDown').prop('disabled', false);
            rows.first().find('.movePromptLevelUp').prop('disabled', true);
            rows.last().find('.movePromptLevelDown').prop('disabled', true);
            if (rows.length <= 1) {
                rows.find('.movePromptLevelUp, .movePromptLevelDown').prop('disabled', true);
            }
        }

        // Initial check to disable remove buttons if necessary
        toggleRemovePromptButtons();
        refreshPromptLevelMoveButtons();
    });
</script>
