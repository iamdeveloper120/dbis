<?php if (!empty($mandsData)): ?>
    <?php foreach ($mandsData as $index => $mand): ?>
        <tr>
            <td><?= $index + 1; ?></td>
            <td><?= esc($mand->reinforcer_input ?? ''); ?></td>
<td><span data-bs-toggle="tooltip" data-bs-placement="top" title="<?= esc($mand->get_prompt_level_tooltip()); ?>"><?= esc($mand->get_prompt_level_label()); ?></span></td>
            <td><?= esc($mand->utterance_input ?? ''); ?></td>
            <td>
                <?php if ($mand->mands_error != 1): ?>
                    <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?= esc($mand->get_mand_error_tooltip()); ?>"><?= esc($mand->get_mand_error_label()); ?></span>
                <?php endif; ?>
            </td>
            <td><?= esc($mand->get_peer_manding_label()); ?></td>
            <td><?= esc($mand->get_eye_contact_label()); ?></td>
            <td><?= esc($mand->initial_attempt_input ?? ''); ?></td>
            <td><?= esc($mand->prompt_delay_input ?? ''); ?></td>
            <td><?= esc($mand->echoic_1_input ?? ''); ?></td>
            <td><?= esc($mand->echoic_2_input ?? ''); ?></td>
            <td><?= esc($mand->echoic_3_input ?? ''); ?></td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="12" class="text-center text-muted">No mands data found for this session.</td>
    </tr>
<?php endif; ?>
