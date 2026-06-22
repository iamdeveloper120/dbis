<ul class="list-group transition-list" id="update_transition_list">
    <?php foreach ($transitions as $index => $item): ?>
        <li class="list-group-item d-flex align-items-center justify-content-between gap-2 flex-wrap" data-index="<?= $index ?>">
            <input type="text" class="form-control transition-text" value="<?= esc($item['transition']) ?>" style="max-width: 80%;">
            
            <div class="btn-group" role="group">
                <input type="radio" class="btn-check" name="answer_<?= $index ?>" id="answer_y_<?= $index ?>" value="Y" <?= $item['answer'] === 'Y' ? 'checked' : '' ?>>
                <label class="btn btn-outline-primary" for="answer_y_<?= $index ?>">Y</label>

                <input type="radio" class="btn-check" name="answer_<?= $index ?>" id="answer_n_<?= $index ?>" value="N" <?= $item['answer'] === 'N' ? 'checked' : '' ?>>
                <label class="btn btn-outline-primary" for="answer_n_<?= $index ?>">N</label>
            </div>

            <button class="btn btn-outline-danger delete-entry" title="Remove">
                <i class="ri-delete-bin-line"></i>
            </button>
        </li>
    <?php endforeach; ?>
</ul>
