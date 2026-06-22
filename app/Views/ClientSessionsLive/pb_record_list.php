<!-- app/Views/ClientSessionsLive/pb_record_list.php -->
<ul class="list-unstyled ps-3 vstack gap-2 mb-2">
    <?php if (empty($pb_records)): ?>
        <li>No problem behaviors recorded yet.</li>
    <?php else: ?>
        <?php foreach ($pb_records as $index => $record): ?>
            <li>
                <a href="#!" class="pb-record-link" data-pb-timer-id="<?= $record->id ?>" data-session-id="<?= $record->session_id ?>" data-client-id="<?= $record->client_id ?>">
                    <i class="ri-stop-mini-fill align-middle fs-15 text-secondary"></i>
                    PB<i class="ri-arrow-drop-right-line text-primary"></i><?= $record->start_time ?> <i class="ri-arrow-right-fill text-primary"></i> <?= $record->end_time ? $record->end_time : 'Active' ?>
                </a>
            </li>
        <?php endforeach; ?>
    <?php endif; ?>
</ul>