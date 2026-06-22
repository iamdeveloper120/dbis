<div class="col-sm-12 col-md-12 col-lg-12 ">
    <div class="p-2 border border-dashed rounded">
        <div class="d-flex align-items-center">
            <div class="flex-grow-1">
                <p class="mb-1">Probe # <?= $index + 1 ?></p>
                <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                    <?php for ($j = $range['min']; $j <= $range['max']; $j++): ?>
                        <input data-set-id="<?= $index ?>" data-target-id="<?= $target['target_id'] ?>" type="radio" class="btn-check" name="prob_<?= $target['target_id'] ?>_<?= $index ?>" id="prob_option_<?= $target['target_id'] ?>_<?= $j ?>_<?= $index ?>" autocomplete="off" value="<?= $j ?>" <?= $index == 0 ? '' : 'disabled' ?>>
                        <label style="width: 55px;" class="btn btn-outline-primary" for="prob_option_<?= $target['target_id'] ?>_<?= $j ?>_<?= $index ?>"><?= $j ?></label>
                        &nbsp;
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
</div>
