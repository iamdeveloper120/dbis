<div class="row">
    <div class="col-sm-12 col-md-12 col-lg-12">
        <!-- Clickable Header Row (full-width toggle) -->
        <ul class="list-group">
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <h5 class="">Trial</h5>
                <h5 class="">Response</h5>
            </li>
        </ul>
        <ul class="list-group  transition-list">
            <?php if (isset($transitions)): ?>
                <?php foreach ($transitions as $item): ?>
                    <li class=" list-group-item d-flex justify-content-between align-items-center">
                        <span><?= esc($item['transition']) ?></span>
                        <span class="text-muted small"><?= esc($item['answer']) ?></span>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>

    </div>
</div>