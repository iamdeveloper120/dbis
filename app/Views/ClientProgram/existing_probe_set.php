<div id="probe_set_container">
    <h4>Existing Probe Set Configuration</h4>
    <form id="probe_set_form">
        <input type="hidden" name="goal_id" value="<?= $goalId ?>">
        <input type="hidden" name="client_id" value="<?= $clientId ?>">
        <input type="hidden" name="probe_set_id" value="<?= $probeSetDetails['id'] ?>">

        <!-- Display existing probe set details -->
        <p>Probe Set: <?= $probeSetDetails['name'] ?></p>
        <p>Description: <?= $probeSetDetails['description'] ?></p>

        <!-- Render existing rules -->
        <?php foreach ($rules as $rule) : ?>
            <div class="rule">
                <h5>Rule for Combination: <?= $rule['combination_name'] ?></h5>
                <input type="hidden" name="rules[<?= $rule['id'] ?>][combination_id]" value="<?= $rule['combination_id'] ?>">
                <input type="hidden" name="rules[<?= $rule['id'] ?>][phase_id]" value="<?= $rule['phase_id'] ?>">

                <!-- Edit JSON rules -->
                <textarea name="rules[<?= $rule['id'] ?>][rules]"><?= json_encode($rule['json_rules']) ?></textarea>
            </div>
        <?php endforeach; ?>

        <button type="submit" class="btn btn-primary">Update Configuration</button>
    </form>
</div>