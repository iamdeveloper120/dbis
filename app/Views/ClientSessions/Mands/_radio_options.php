<?php
$buttonClass = $label_class ?? 'btn btn-outline-primary probe-box-size';
$buttonInputClass = trim('btn-check ' . ($input_class ?? ''));
$optionDataSetId = $data_set_id ?? '0';
?>
<?php foreach ($options as $option): ?>
    <?php $inputId = $id_prefix . $option['dom_id_suffix']; ?>
    <input data-set-id='<?= esc((string) $optionDataSetId); ?>' type="radio" class="<?= esc($buttonInputClass); ?>" name="<?= esc($name); ?>" id="<?= esc($inputId); ?>" autocomplete="off" value="<?= esc((string) $option['value']); ?>">
    <label class="<?= esc($buttonClass); ?>" for="<?= esc($inputId); ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= esc($option['tooltip']); ?>"><?= esc($option['label']); ?></label>
<?php endforeach; ?>
