<?php foreach ($results as $index => $selected): ?> <!-- Iterate over each result -->
    <div class="col-sm-12 col-md-12 col-lg-12 ">
        <div class="p-2 border border-dashed rounded">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                        <?php foreach ($choices as $key => $choice): ?>
                            <input class="btn-check" type="radio" autocomplete="off" id="prob_option_<?= $index ?>_<?= $key ?>" name="prob_option_<?= $index ?>" value="<?= $choice['value']; ?>" <?= $selected == $choice['value'] ? 'checked' : ''; ?>>
                            <label class="btn btn-outline-primary" for="prob_option_<?= $index ?>_<?= $key ?>"><?= $choice['value']; ?></label>
                            &nbsp;
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>