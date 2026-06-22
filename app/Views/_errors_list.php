<?php if (!empty($errors)) : ?>
    <div class="list-group">
        <?php foreach ($errors as $error) : ?>
            <span class="list-group-item"><?= esc($error) ?></span>
        <?php endforeach ?>
        </div>
<?php endif ?>