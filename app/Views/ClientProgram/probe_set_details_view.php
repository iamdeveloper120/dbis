<div class="row pb-2">
    <div class="col-md-12">
        <?= $infoString ?>
    </div>
</div>
<div class="card border card-border-primary" id="probeSetView">
    <div class="card-header">
        <h6 class="card-title mb-0"><?= esc($probeSet['name']) ?></h6>
    </div>
    <div class="card-body">
        <p class="card-text"><?= esc($probeSet['description']) ?> <strong>Consecutive Criteria Check Key: <?= isset($inputs['key']) ? $inputs['key'] : 'NA' ?></strong> <small>(This is set to the last prompt level selected in the list below.)</small></p>

        <p>
            <strong>Existing Inputs:</strong>
            <small>Individualise the prompts to be probed by removing any that are not relevant and adding any that are specific to the learner.</small>
            <?= $inputsHtml ?>
        </p>

        <div id="inputContainer">
            <?php if ($inputs['type'] == 'count') : ?>
                <div><strong>Range:</strong></div>
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
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        // Disable all input elements within the .card-border-primary div
        $("#probe_set_and_rules_area input").prop("disabled", true);
    });
</script>
