<div class="col-sm-12 col-md-8 col-lg-10">
    <div class="d-flex align-items-center">
        <input
            id="input_transition_<?= $target['target_id'] ?>"
            name="input_transition_<?= $target['target_id'] ?>" 
            data-target-id="<?= $target['target_id'] ?>"
            placeholder="Trial data"
            class="form-control mb-2 transition-input">
    </div>
</div>
<div class="col-sm-12 col-md-2 col-lg-2 text-end ">
    <div class="d-flex align-items-center">
        <div class="flex-grow-1">
            <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                <?php foreach ($choices as $key => $choice): ?>
                    <input data-set-id="<?= $index ?>" data-target-id="<?= $target['target_id'] ?>" type="radio" class="btn-check" name="prob_<?= $target['target_id'] ?>_<?= $index ?>" id="prob_option_<?= $target['target_id'] ?>_<?= $key ?>_<?= $index ?>" autocomplete="off" value="<?= esc($choice['value']) ?>" <?= $index == 0 ? '' : 'disabled' ?>>
                    <label style="width: 55px;" class="btn btn-outline-primary" for="prob_option_<?= $target['target_id'] ?>_<?= $key ?>_<?= $index ?>"><?= esc($choice['label']) ?></label>
                    &nbsp;
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>