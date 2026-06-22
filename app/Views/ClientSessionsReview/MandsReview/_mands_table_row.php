<tr>
    <td></td>
    <td><?= $mand->reinforcer_input; ?> </td>
<td class="dt-nowrap"><span data-bs-toggle="tooltip" data-bs-placement="top" title="<?= esc($mand->get_prompt_level_tooltip()); ?>"><?= esc($mand->get_prompt_level_label()); ?></span></td>
    <td class="dt-nowrap"><?= $mand->utterance_input ?? ''; ?> </td>
    <td class="dt-nowrap">
        <?php if ($mand->mands_error != 1): ?>
            <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?= esc($mand->get_mand_error_tooltip()); ?>"><?= esc($mand->get_mand_error_label()); ?></span>
        <?php endif; ?>
    </td>
    <td class="dt-nowrap"><?= $mand->get_peer_manding_label(); ?> </td>
    <td class="dt-nowrap"><?= $mand->get_eye_contact_label(); ?> </td>
    <td class="dt-nowrap"><?= $mand->initial_attempt_input; ?> </td>
    <td class="dt-nowrap">
        <?php if ($mand->initial_attempt != 1): ?>
            <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?= esc($mand->get_initial_input_response_tooltip()); ?>"><?= esc($mand->get_initial_input_response_label()); ?></span>
        <?php endif; ?>
    </td>
    <td class="dt-nowrap"><?= $mand->prompt_delay_input; ?> </td>
    <td class="dt-nowrap">
        <?php if ($mand->prompt_delay != 1): ?>
            <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?= esc($mand->get_prompt_delay_response_tooltip()); ?>"><?= esc($mand->get_prompt_delay_response_label()); ?></span>
        <?php endif; ?>
    </td>
    <td class="dt-nowrap"><?= $mand->echoic_1_input; ?> </td>
    <td class="dt-nowrap">
        <?php if ($mand->echoic_1 != 1): ?>
            <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?= esc($mand->get_echoic_1_response_tooltip()); ?>"><?= esc($mand->get_echoic_1_response_label()); ?></span>
        <?php endif; ?>
    </td>
    <td class="dt-nowrap"><?= $mand->echoic_2_input; ?> </td>
    <td class="dt-nowrap">
        <?php if ($mand->echoic_2 != 1): ?>
            <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?= esc($mand->get_echoic_2_response_tooltip()); ?>"><?= esc($mand->get_echoic_2_response_label()); ?></span>
        <?php endif; ?>
    </td>
    <td class="dt-nowrap"><?= $mand->echoic_3_input; ?> </td>
    <td class="dt-nowrap">
        <?php if ($mand->echoic_3 != 1): ?>
            <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?= esc($mand->get_echoic_3_response_tooltip()); ?>"><?= esc($mand->get_echoic_3_response_label()); ?></span>
        <?php endif; ?>
    </td>
    <td class="dt-nowrap"><?= $mand->get_prompt_delay_comparison_label(); ?> </td>
    <td class="dt-nowrap"><?= $mand->get_echoic_comparison_label(); ?> </td>
    <td class="dt-nowrap">
        <?php if (false): ?>
            <!-- No actions if processed -->
        <?php else: ?>
            <button data-mands-id="<?= $mand->id; ?>" type="button" class="btn btn-sm btn-outline-warning update"><i class="ri-edit-line align-bottom me-1"></i>Edit</button>
            <button data-mands-id="<?= $mand->id; ?>" type="button" class="btn btn-sm btn-outline-danger delete"><i class="ri-delete-bin-line align-bottom me-1"></i>Delete</button>
        <?php endif; ?>
    </td>
</tr>
