<style>
    .form-check-input[type=radio] {
        border-radius: .25em !important;
    }

    .form-check-input:checked[type=radio] {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 10'%3e%3cpolyline points='1 5 4 8 10 1' fill='none' stroke='%23fff' stroke-width='3'/%3e%3c/svg%3e") !important;
        background-position: center;
        background-repeat: no-repeat;
        background-size: 60%;
    }
</style>
<div class="card">
    <div class="card-header">
        <div class="d-flex align-items-center">
            <div class="flex-grow-1">
                <h6 class="card-title mb-0">
                    <i class="ri-stop-mini-fill align-middle fs-15 text-secondary"></i>
                    PB <i class="ri-arrow-drop-right-line text-primary"></i> <?= $pb_duration->start_time ?>
                    <i class="ri-arrow-right-fill text-primary"></i> <?= $pb_duration->end_time ? $pb_duration->end_time : 'Active' ?>
                </h6>
            </div>
            <div class="flex-shrink-0">
                <span class="text-end fst-italic pb-record-status">
                    <?= isset($pb_record) && $pb_record ? '(Existing record, update)' : '(No record yet, add one)' ?>
                </span>
            </div>
        </div>
    </div>

    <div class="card-body">
        <form id="pbRecordForm">
            <input type="hidden" name="pb_timer_id" value="<?= $pb_timer_id ?>">
            <input type="hidden" name="session_id" value="<?= $session_id ?>">
            <input type="hidden" name="client_id" value="<?= $client_id ?>">

            <div class="row">
                <!-- Antecedent Section -->
                <div class="col-md-4 col-sm-12 mb-3">
                    <div class="card border card-border-primary">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Antecedent </h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group" id="antecedent-list">
                                <div class="row">
                                    <?php
                                    $antecedent_other = true; // Assume the value is 'Other' by default
                                    if (isset($pb_record['antecedent'])) {
                                        foreach ($antecedents as $index => $item) {
                                            if ($pb_record['antecedent'] === $item['value']) {
                                                $antecedent_other = false; // It's not 'Other', it matches one of the predefined values
                                                break;
                                            }
                                        }
                                    }
                                    ?>
                                    <!-- Predefined Antecedent Options -->
                                    <?php foreach ($antecedents as $index => $item): ?>
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input id="antecedent_<?= $index ?>" class="form-check-input" type="radio" name="antecedent" value="<?= $item['value'] ?> "
                                                    <?= isset($pb_record['antecedent']) && $pb_record['antecedent'] === $item['value'] ? 'checked' : '' ?>>
                                                <label for="antecedent_<?= $index ?>" class="form-check-label"><?= $item['value'] ?></label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <!-- Other Antecedent Option -->
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input id="antecedent_other_option" class="form-check-input" type="radio" name="antecedent" value="Other"
                                                <?= $antecedent_other ? 'checked' : '' ?>>
                                            <label for="antecedent_other_option" class="form-check-label">Other</label>
                                        </div>
                                        <input
                                            name="antecedent_other"
                                            id="antecedent_other"
                                            class="form-control mt-2"
                                            style="display: <?= $antecedent_other ? 'block' : 'none' ?>;"
                                            value="<?= $antecedent_other && isset($pb_record['antecedent']) ? esc($pb_record['antecedent']) : '' ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Behavior Section -->
                <div class="col-md-4 col-sm-12 mb-3">
                    <div class="card border card-border-primary">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Behavior</h6>
                        </div>
                        <div class="card-body">
                            <!-- Flex container for predefined behaviors -->
                            <div id="behavior-list" class="d-flex flex-wrap">
                                <!-- Predefined behaviors -->
                                <?php
                                $existing_behaviors = isset($pb_record['behavior']) ? json_decode($pb_record['behavior'], true) : [];
                                foreach ($behaviors as $index => $item):
                                    $selected_behavior = in_array($item['value'], array_column($existing_behaviors, 'behavior'));
                                ?>
                                    <!-- The following div ensures that the checkbox and its label are treated as a single unit that moves to the next line together -->
                                    <div class="form-check mb-2" style="flex: 1 0 50%; max-width: 50%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"> <!-- Prevent text wrapping -->
                                        <input id="behavior_<?= $index ?>" class="form-check-input" type="checkbox" name="behavior[]" value="<?= $item['value'] ?>"
                                            <?= $selected_behavior ? 'checked' : '' ?>>
                                        <label for="behavior_<?= $index ?>" class="form-check-label"><?= $item['value'] ?></label>
                                    </div>
                                    <input type="hidden" name="intensity[<?= $item['value'] ?>]" value="1">
                                <?php endforeach; ?>
                            </div>

                            <!-- Dynamically added behaviors -->
                            <div id="added-behaviors">
                                <?php
                                foreach ($existing_behaviors as $index => $behavior):
                                    if (!in_array($behavior['behavior'], array_column($behaviors, 'value'))):
                                ?>
                                        <div class="row mb-2 behavior-row">
                                            <div class="col-8">
                                                <input type="text" name="behavior[]" class="form-control form-control-sm" value="<?= $behavior['behavior'] ?>">
                                            </div>
                                            <div class="col-4">
                                                <button type="button" class="btn btn-danger btn-sm remove-behavior">Remove</button>
                                            </div>
                                            <input type="hidden" name="intensity[<?= $behavior['behavior'] ?>]" value="1">
                                        </div>
                                <?php endif;
                                endforeach; ?>
                            </div>

                            <!-- Button to add more behaviors -->
                            <button type="button" id="add-behavior" class="btn btn-sm btn-secondary mt-2">Add More Behavior</button>
                        </div>
                    </div>
                </div>
                <!-- Consequence Section -->
                <div class="col-md-4 col-sm-12 mb-3">
                    <div class="card border card-border-primary">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Consequence </h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group" id="consequence-list">
                                <div class="row">
                                    <?php
                                    $consequence_other = true; // Assume the value is 'Other' by default
                                    if (isset($pb_record['consequence'])) {
                                        foreach ($consequences as $index => $item) {
                                            if ($pb_record['consequence'] === $item['value']) {
                                                $consequence_other = false; // It's not 'Other', it matches one of the predefined values
                                                break;
                                            }
                                        }
                                    }
                                    ?>
                                    <!-- Predefined Consequence Options -->
                                    <?php foreach ($consequences as $index => $item): ?>
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input id="consequence_<?= $index ?>" class="form-check-input" type="radio" name="consequence" value="<?= $item['value'] ?>"
                                                    <?= isset($pb_record['consequence']) && $pb_record['consequence'] === $item['value'] ? 'checked' : '' ?>>
                                                <label for="consequence_<?= $index ?>" class="form-check-label"><?= $item['value'] ?></label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <!-- Other Consequence Option -->
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input id="consequence_other_option" class="form-check-input" type="radio" name="consequence" value="Other"
                                                <?= $consequence_other ? 'checked' : '' ?>>
                                            <label for="consequence_other_option" class="form-check-label">Other</label>
                                        </div>
                                        <input
                                            name="consequence_other"
                                            id="consequence_other"
                                            class="form-control mt-2"
                                            style="display: <?= $consequence_other ? 'block' : 'none' ?>;"
                                            value="<?= $consequence_other && isset($pb_record['consequence']) ? esc($pb_record['consequence']) : '' ?>">
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comment Section -->
                <div class="col-md-12 col-sm-12 mb-3">
                    <div class="card border card-border-primary">
                        <div class="card-body">
                            <div class="form-group" id="comments-section">
                                <div class="row">
                                    <div class="col-lg-12 col-12">
                                        <!-- Text Area -->
                                        <div class="mb-3">
                                            <label for="abc_comments" class="form-label">Comments</label>
                                            <textarea name="abc_comments" id="abc_comments" class="form-control" rows="3"><?= isset($pb_record) ? htmlspecialchars($pb_record['abc_comments'], ENT_QUOTES, 'UTF-8') : '' ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>

    <div class="card-footer text-end">
        <button type="submit" form="pbRecordForm" class="btn btn-sm btn-primary">
            <i class="ri-save-3-line align-middle"></i> Save
        </button>
    </div>
</div>