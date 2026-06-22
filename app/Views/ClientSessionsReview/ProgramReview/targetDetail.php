<?php

$phaseClassArray = [
    '1' => 'dark',
    '2' => 'primary',
    '3' => 'info',
    '4' => 'success',
];
$frameArray = [
    '1' => 'Frame Set: 1',
    '2' => 'Frame Set: 2',
    '' => '',
];
$sr_no = 0;
?>
<!-- 🟢 Target History Section -->
<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0">Target Processing History</h5>
    </div>
    <div class="card-body">
        <div class="list-group" id="listContainer" style="cursor: default !important;">
            <?php foreach ($clientProgramData as $domainId => $domain) : ?>
                <?php foreach ($domain['goals'] as $goalId => $goal) : ?>
                    <?php foreach ($goal['targets'] as $targetId => $target) : ?>
                        <?php $sr_no++ ?>
                        <?php $phaseClass = $target['current_phase_id'] ? $phaseClassArray[$target['current_phase_id']] : '';  ?>
                        <?php
                        $statusText = (!$target['phase_name'])
                            ? 'Not introduced yet'
                            : (($target['phase_name'] == 'Acquisition' || $target['phase_name'] == 'Retention')
                                ? 'On ' . $target['phase_name']
                                : $target['phase_name']);
                        ?>
                        <a href="javascript:void(0);"
                            class="list-group-item list-group-item-action"
                            data-latest-date="<?= $target['latest_session_date'] ?>"
                            data-domain-code="<?= $domain['domain_code'] ?>"
                            data-goal-code="<?= $goal['goal_code'] ?>"
                            data-target-name="<?= $target['target_name'] ?>"
                            data-status="<?= $statusText ?>">

                            <div class="float-end">
                                <?= $statusText ?>
                            </div>
                            <div class="d-flex mb-2 align-items-center">
                                <div class="flex-grow-1 ms-0">
                                    <h5 id="target-header-<?= $targetId ?>" class="list-title fs-15 mb-1 text-primary"><?= $target['target_name'] ?> <?= $target['override_consecutive_criteria'] ? '<span class="override-criteria"><em class="link-warning fs-6 text"> (Override teaching trials: ' . $target['override_consecutive_criteria'] . ')</em></span>' : '' ?></h5>
                                    <p class="list-text mb-0 fs-12"><b class="text-info">Goal</b> (<?= $goal['goal_code'] . '-' . $goal['goal_name']  ?>) - <b class="text-info">Domain</b> (<?= $domain['domain_code'] . '-' . $domain['domain_name']  ?>)</p>
                                    <?php if ($target['program_alert_count']): ?>
                                        <p id="target-alert-frequency-<?= $targetId ?>" class="list-text mb-0 fs-12 fs-6 text">
                                            <b>Program Alert Frequency:</b> <span class="badge bg-danger-subtle text-danger"><?= $target['program_alert_count'] ?></span>&nbsp;&nbsp;&nbsp;&nbsp;<b>Program Change Frequency:</b> <span class="badge bg-danger-subtle text-danger"><?= $target['program_change_count'] ?></span>
                                        </p>
                                    <?php endif; ?>
                                    <p class="list-text mb-0 fs-12">
                                        <?php if (!empty($goal['probe_set_name'])): ?>
                                            <?php
                                            $masteryText = '';

                                            if (isset($goal['teaching_phase_rule']->consecutive_criteria)) {
                                                $masteryText .= "Mastery criteria: " . $goal['teaching_phase_rule']->consecutive_criteria;
                                            }

                                            if (isset($goal['probe_set_key']->key)) {
                                                $masteryText .= " consecutive " . $goal['probe_set_key']->key;
                                            }

                                            if (isset($goal['retention_phase_rule']) && isset($goal['retention_phase_rule']->activation_days)) {
                                                $masteryText .= " and retention " . $goal['retention_phase_rule']->activation_days . " days";
                                            }

                                            $chainLabel = '';
                                            if (!empty($target['target_chain_method'])) {
                                                $methodText = match ($target['target_chain_method']) {
                                                    'backward' => 'Backward Chain',
                                                    'forward' => 'Forward Chain',
                                                    'total_task' => 'Total Task Chain',
                                                    default => ucfirst($target['target_chain_method']) . ' Chain'
                                                };

                                                $badgeClass = match ($target['target_chain_method']) {
                                                    'backward' => 'info',
                                                    'forward' => 'info',
                                                    'total_task' => 'info',
                                                    default => 'secondary'
                                                };

                                                $chainLabel = "<span class='badge bg-$badgeClass'>$methodText</span>";
                                            }
                                            ?>

                                            <button class="btn btn-link waves-effect p-0 active-probe-detail"
                                                goal-id="<?= $goalId ?>"
                                                client-id="">
                                                <b><?= $goal['probe_set_name'] ?></b> (<?= $goal['combination_name'] ?>)
                                                <?php if ($masteryText): ?>
                                                    <span><?= $masteryText ?></span>
                                                <?php endif; ?>
                                                <?= $chainLabel ?>
                                            </button>


                                        <?php else: ?>
                                            <b class="text-warning">Probe set is not linked</b>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <p class="list-text mb-0">
                                <?php

                                $phasesArray = [];
                                $frames = [];
                                $dates = [];
                                $values = [];
                                $pg = [];
                                $session_ids = [];
                                $previousDate = null;
                                $prog_ch_alert = [];
                                $prog_ch_made = [];
                                $valuesByDate = [];
                                foreach ($target['session_data'] as $session) {
                                    $phasesArray[] = $session['data_phase_id'];
                                    $frames[] = $session['data_frame_set'];
                                    $dates[] = $session['session_date'];
                                    $pg[] = $session['prog_ch'];
                                    $session_ids[] = $session['session_id'];
                                    $prog_ch_alert[] = $session['prog_ch_alert'];
                                    $prog_ch_made[] = $session['prog_ch_made'];
                                    $probe_type = $session['probe_type'];
                                    $v = '';
                                    if ($session['data_phase_id'] == 1) {
                                        $baslineResultLoop = 0;
                                        foreach ($session['result'] as $entry) {
                                            $result = "";
                                            $label = "";

                                            // Check and set $result and $label based on the value of $entry
                                            if ($probe_type == "yes_no") {
                                                if ($entry == '') {
                                                    $result = '<i class="ri-forbid-line"></i>';
                                                    $label = "primary";
                                                } elseif ($entry === 'Y') {
                                                    $result = "Y";
                                                    $label = "success";
                                                } elseif ($entry === 'N') {
                                                    $result = "N";
                                                    $label = "danger";
                                                } else {
                                                    $result = $entry;
                                                    $label = "success";
                                                }
                                            } else if ($probe_type == "traffic_light") {

                                                if ($entry == '') {
                                                    $result = '<i class="ri-forbid-line"></i>';
                                                    $label = "primary";
                                                } else if ($entry == 'Y') {
                                                    $result = $entry;
                                                    $label = "warning";
                                                } else if ($entry == 'R') {
                                                    $result = $entry;
                                                    $label = "danger";
                                                } else if ($entry == 'G') {
                                                    $result = $entry;
                                                    $label = "success";
                                                }
                                            } else if ($probe_type == "percentage_yes_no") {
                                                if (isset($session['statistics']) && isset($session['statistics']['total_yes']) && isset($session['statistics']['total_no']) && isset($session['success_key'])) {
                                                    $total_y_n = $session['statistics']['total_yes'] . "/" . ($session['statistics']['total_yes'] + $session['statistics']['total_no']);

                                                    if ($entry < $session['success_key']) {
                                                        $result = $total_y_n . "<br>" . (int) round((float) $entry) . "%";
                                                        $label = "danger";
                                                    } else {
                                                        $result = $total_y_n . "<br>" . (int) round((float) $entry) . "%";
                                                        $label = "success";
                                                    }
                                                } else {
                                                    // Handle missing statistics safely (optional)
                                                    $result = "N/A"; // or some fallback value
                                                    $label = "secondary"; // optional neutral label
                                                }
                                            } else if ($probe_type == "stimulus_program") {
                                                $chain_method = $session['chain_method'];
                                                if ($chain_method == 'baseline' || $chain_method == 'total_task') {
                                                    $result = (int) round((float) $entry) . "%";
                                                    $label =  $entry == 100 ? "success" : 'danger';
                                                    $steps = $session['statistics']['attempt_' . $baslineResultLoop]['total_steps'] ?? null;
                                                    if (isset($steps) && $steps === 0) {
                                                        $result = '<i class="ri-forbid-line"></i>';
                                                        $label = "primary";
                                                    }
                                                } else {
                                                    if (isset($session['statistics']) && isset($session['statistics']['probe_value'])) {
                                                        $result = $session['statistics']['probe_value'] . "<br>" . (int) round((float) $entry) . "%";
                                                        $label = "success";
                                                    } else {
                                                        $result = "NULL";
                                                        $label = "danger";
                                                    }
                                                }
                                            } else {
                                                // Check and set $result and $label based on the value of $entry
                                                if ($entry == '') {
                                                    $result = '<i class="ri-forbid-line"></i>';
                                                    $label = "primary";
                                                } else {
                                                    $result = $entry;
                                                    if ($session['success_key'] == $entry) {
                                                        $label = "success";
                                                    } else {
                                                        $label = "primary";
                                                    }
                                                }
                                            }

                                            if ($probe_type == "percentage_yes_no") {
                                                // Generate the HTML and add to $values array
                                                $v .= '<label data-collection-id="' . $session['session_data_id'] . '" class="percentage-yes-no btn btn-soft-custom-' . ($label) . '   rounded-circle p-0 d-flex justify-content-center align-items-center"  style="width: 60px; height: 60px; font-size: 12px;">' . $result . '</label>';
                                            } else if ($probe_type == "stimulus_program") {
                                                // Generate the HTML and add to $values array
                                                $v .= '<label data-collection-id="' . $session['session_data_id'] . '" class="stimulus-program btn btn-soft-custom-' . ($label) . '   rounded-circle p-0 d-flex justify-content-center align-items-center"  style="width: 60px; height: 60px; font-size: 12px;">' . $result . '</label>';
                                            } else {
                                                // Generate the HTML and add to $values array
                                                $v .= '<label class="btn btn-soft-custom-' . ($label) . ' avatar-xs rounded-circle p-0 d-flex justify-content-center align-items-center no-hover"  style="width: 40px; height: 40px; font-size: 12px;">' . $result . '</label>';
                                            }


                                            // Generate the HTML and add to $values array
                                            //$v .= '<label class="btn btn-soft-custom-' . ($label) . ' avatar-xs rounded-circle p-0 d-flex justify-content-center align-items-center">' . $result . '</label>';
                                            $baslineResultLoop = $baslineResultLoop + 1;
                                        }
                                    } else {

                                        $entry = $session['result'][0];
                                        $result = "";
                                        $label = "";

                                        // Check and set $result and $label based on the value of $entry
                                        if ($probe_type == "yes_no") {
                                            if ($entry == '') {
                                                $result = '<i class="ri-forbid-line"></i>';
                                                $label = "primary";
                                            } elseif ($entry === 'Y') {
                                                $result = "Y";
                                                $label = "success";
                                            } elseif ($entry === 'N') {
                                                $result = "N";
                                                $label = "danger";
                                            } else {
                                                $result = $entry;
                                                $label = "success";
                                            }
                                        } else if ($probe_type == "traffic_light") {

                                            if ($entry == '') {
                                                $result = '<i class="ri-forbid-line"></i>';
                                                $label = "primary";
                                            } else if ($entry == 'Y') {
                                                $result = $entry;
                                                $label = "warning";
                                            } else if ($entry == 'R') {
                                                $result = $entry;
                                                $label = "danger";
                                            } else if ($entry == 'G') {
                                                $result = $entry;
                                                $label = "success";
                                            }
                                        } else if ($probe_type == "percentage_yes_no") {
                                            if (isset($session['statistics']['total_yes'], $session['statistics']['total_no'], $session['success_key'])) {
                                                $total_y_n = $session['statistics']['total_yes'] . "/" . ($session['statistics']['total_yes'] + $session['statistics']['total_no']);

                                                if ($entry < $session['success_key']) {
                                                    $result = $total_y_n . "<br>" . (int) round((float) $entry) . "%";
                                                    $label = "danger";
                                                } else {
                                                    $result = $total_y_n . "<br>" . (int) round((float) $entry) . "%";
                                                    $label = "success";
                                                }
                                            } else {
                                                // Safe fallback if statistics not available
                                                $result = "N/A";
                                                $label = "secondary";
                                            }
                                        } else if ($probe_type == "stimulus_program") {
                                            $chain_method = $session['chain_method'];
                                            if ($chain_method == 'baseline' || $chain_method == 'total_task') {
                                                $result = (int) round((float) $entry) . "%";
                                                $label =  $entry == 100 ? "success" : 'danger';
                                            } else {
                                                /*if (isset($session['statistics']) && isset($session['statistics']['probe_value'])) {
                                                    $result = $session['statistics']['probe_value'] . "<br>" . (int) round((float) $entry) . "%";
                                                    $label = "success";
                                                } else {
                                                    $result = "NULL";
                                                    $label = "danger";
                                                }*/
                                                if (isset($session['statistics']) && isset($session['statistics']['probe_value'])) {
                                                    $result = $session['statistics']['probe_value'] . "<br>" . $session['statistics']['mastered_steps'] . "/" . $session['statistics']['total_steps'];
                                                    $label = "success";
                                                } else {
                                                    $result = "NULL";
                                                    $label = "danger";
                                                }
                                            }
                                        } else {
                                            // Check and set $result and $label based on the value of $entry
                                            if ($entry == '') {
                                                $result = '<i class="ri-forbid-line"></i>';
                                                $label = "primary";
                                            } else {
                                                $result = $entry;
                                                if ($session['success_key'] == $entry) {
                                                    $label = "success";
                                                } else {
                                                    $label = "primary";
                                                }
                                            }
                                        }

                                        if ($probe_type == "percentage_yes_no") {
                                            // Generate the HTML and add to $values array
                                            $v .= '<label data-collection-id="' . $session['session_data_id'] . '" class="percentage-yes-no btn btn-soft-custom-' . ($label) . '   rounded-circle p-0 d-flex justify-content-center align-items-center"  style="width: 60px; height: 60px; font-size: 12px;">' . $result . '</label>';
                                        } else if ($probe_type == "stimulus_program") {
                                            // Generate the HTML and add to $values array
                                            $v .= '<label data-collection-id="' . $session['session_data_id'] . '" class="stimulus-program btn btn-soft-custom-' . ($label) . '   rounded-circle p-0 d-flex justify-content-center align-items-center"  style="width: 60px; height: 60px; font-size: 12px;">' . $result . '</label>';
                                        } else {
                                            // Generate the HTML and add to $values array
                                            $v .= '<label class="btn btn-soft-custom-' . ($label) . ' avatar-xs rounded-circle p-0 d-flex justify-content-center align-items-center no-hover"  style="width: 40px; height: 40px; font-size: 12px;">' . $result . '</label>';
                                        }
                                    }



                                    $values[] = $v;

                                    if (!isset($valuesByDate[$session['session_date']])) {
                                        $valuesByDate[$session['session_date']] = [
                                            'values' => [],
                                            'phase' => $session['data_phase_id'],
                                            'pg' => $session['prog_ch'],
                                            'session_id' => $session['session_id']
                                        ];
                                    }
                                    $valuesByDate[$session['session_date']]['values'][] = $v;
                                }
                                // Calculate the number of columns and table width
                                $maxColumns = 0;
                                foreach ($valuesByDate as $date => $data) {
                                    $maxColumns = max($maxColumns, count($data['values']));
                                }
                                $baseColumnWidth = 150; // Base width for a single value column
                                $groupedColumnWidth = 150; // Width for a column with grouped values
                                $tableWidth = $baseColumnWidth * $maxColumns + ($maxColumns - 1) * 50; // Adjust for grouping

                                ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered border-primary nowrap   <?= $target['current_phase_id'] == 4 ? 'table-red-right-border' : '' ?> <?= $target['current_phase_id'] == 3 ? 'table-black-right-border' : '' ?>" style="width: <?= $tableWidth ?>px;">

                                        <tr>
                                            <?php
                                            $previousPhase = null;
                                            $phaseColspan = 1;
                                            foreach ($phasesArray as $index => $p) {
                                                if ($previousPhase != $p) {
                                                    if ($previousPhase !== null) {
                                                        // Initialize the class
                                                        $cellClass = '';
                                                        // Check if phase is 3 for black border
                                                        if ($previousPhase == 3) {
                                                            $cellClass .= ' black-right-border';
                                                        }
                                                        echo "<th colspan=\"$phaseColspan\" class=\"" . $cellClass . " " . ($previousPhase == 1 ? 'phase-1' : '') . "\" style=\"white-space: nowrap; padding-top: 5px; padding-bottom: 5px; " . ($previousPhase == 1 ? 'width:150px; border-right-width: 3px;' : '') . "\"><span class=\"fs-6\">{$phases[$previousPhase]}</span></th>";
                                                    }
                                                    $previousPhase = $p;
                                                    $phaseColspan = 1;
                                                } else {
                                                    $phaseColspan++;
                                                }
                                            }
                                            if ($previousPhase !== null) {
                                                // Initialize the class
                                                $cellClass = '';
                                                // Check if phase is 3 for black border
                                                if ($previousPhase == 3) {
                                                    $cellClass .= ' black-right-border';
                                                }
                                                echo "<th colspan=\"$phaseColspan\" class=\"" . $cellClass . " " . ($previousPhase == 1 ? 'phase-1' : '') . "\"  style=\"white-space: nowrap; padding-top: 5px; padding-bottom: 5px; " . ($previousPhase == 1 ? 'width:150px; border-right-width: 3px;' : '') . "\"><span class=\"fs-6\">{$phases[$previousPhase]}</span></th>";
                                            }
                                            ?>
                                        </tr>
                                        <?php if (!empty(array_filter($frames))) : ?>
                                            <tr>
                                                <?php
                                                $previousFrame = '';
                                                $frameColspan = 0;
                                                foreach ($frames as $index => $f) {
                                                    if ($previousFrame !== $f) {
                                                        if ($frameColspan > 0) {
                                                            // Initialize the class
                                                            $cellClass = '';
                                                            // Check if phase is 3 for black border
                                                            if ($index - $frameColspan >= 0 && $phasesArray[$index - $frameColspan] == 3) {
                                                                $cellClass .= ' black-right-border';
                                                            }
                                                            // Check if the index is valid before accessing the array
                                                            $borderStyle = ($index - $frameColspan >= 0 && $phasesArray[$index - $frameColspan] == 1) ? 'width:150px; border-right-width: 3px;' : '';
                                                            echo "<th colspan=\"$frameColspan\"  class=\""  . $cellClass . " " . (($index - $frameColspan >= 0 && $phasesArray[$index - $frameColspan] == 1) ? 'phase-1' : '') . "\" style=\"padding-top: 5px; padding-bottom: 5px; $borderStyle\"><span class=\"fs-6\">{$frameArray[$previousFrame]}</span></th>";
                                                        }
                                                        $previousFrame = $f;
                                                        $frameColspan = 1;
                                                    } else {
                                                        $frameColspan++;
                                                    }
                                                }
                                                // Output the last group
                                                if ($frameColspan > 0) {
                                                    // Initialize the class
                                                    $cellClass = '';
                                                    // Check if phase is 3 for black border
                                                    if (count($frames) - $frameColspan >= 0 && $phasesArray[count($frames) - $frameColspan]  == 3) {
                                                        $cellClass .= ' black-right-border';
                                                    }
                                                    // Check if the index is valid before accessing the array
                                                    $borderStyle = (count($frames) - $frameColspan >= 0 && $phasesArray[count($frames) - $frameColspan] == 1) ? 'width:150px; border-right-width: 3px;' : '';
                                                    echo "<th colspan=\"$frameColspan\"  class=\""  . $cellClass . " " . ((count($frames) - $frameColspan >= 0 && $phasesArray[count($frames) - $frameColspan] == 1) ? 'phase-1' : '') . "\" style=\"padding-top: 5px; padding-bottom: 5px; $borderStyle\"><span class=\"fs-6\">{$frameArray[$previousFrame]}</span></th>";
                                                }
                                                ?>
                                            </tr>
                                        <?php endif; ?>

                                        <tr>
                                            <?php
                                            $previousDate = '';
                                            $dateColspan = 0;

                                            foreach ($dates as $index => $d) {
                                                if ($previousDate !== $d) {
                                                    if ($dateColspan > 0) {
                                                        // Initialize the class
                                                        $cellClass = '';
                                                        // Check if phase is 3 for black border
                                                        if ($index - $dateColspan >= 0 && $phasesArray[$index - $dateColspan] == 3) {
                                                            $cellClass .= ' black-right-border';
                                                        }
                                                        // Check if the index is valid before accessing the array
                                                        $borderStyle = ($index - $dateColspan >= 0 && $phasesArray[$index - $dateColspan] == 1) ? 'width:150px; border-right-width: 3px;' : '';
                                                        $dateColor = ($prog_ch_made[$index - $dateColspan] != '') ? '#2074BA' : 'lightgrey';
                                                        $highlightStyle = ($pg[$index - $dateColspan] == 1) ? 'background-color:' . $dateColor . '; border-right-width: 3px; border-right-color:black' : '';
                                                        $onclickAction = ($pg[$index - $dateColspan] == 1) ? "setLastClickedCell(this); programChangeShow('{$prog_ch_alert[$index -$dateColspan]}', '{$prog_ch_made[$index -$dateColspan]}', '{$target['client_id']}', '{$target['target_id']}')" : '';
                                                        echo "<td colspan=\"$dateColspan\" class=\"" . $cellClass . " " . (($index - $dateColspan >= 0 && $phasesArray[$index - $dateColspan] == 1) ? 'phase-1' : '') . "\" style=\"$borderStyle padding-top: 5px; padding-bottom: 5px; white-space: nowrap; $highlightStyle\" onclick=\"$onclickAction\">" . app_date($previousDate) . "</td>";
                                                    }
                                                    $previousDate = $d;
                                                    $dateColspan = 1;
                                                } else {
                                                    $dateColspan++;
                                                }
                                            }
                                            // Output the last group
                                            if ($dateColspan > 0) {
                                                // Initialize the class
                                                $cellClass = '';
                                                // Check if phase is 3 for black border
                                                if (count($dates) - $dateColspan >= 0 && $phasesArray[count($dates) - $dateColspan] == 3) {
                                                    $cellClass .= ' black-right-border';
                                                }
                                                $borderStyle = (count($dates) - $dateColspan >= 0 && $phasesArray[count($dates) - $dateColspan] == 1) ? 'width:150px; border-right-width: 3px;' : '';
                                                $dateColor = ($prog_ch_made[count($dates) - $dateColspan] != '') ? '#2074BA' : 'lightgrey';
                                                $highlightStyle = ($pg[count($dates) - $dateColspan] == 1) ? 'background-color: ' . $dateColor . '; border-right-width: 3px; border-right-color:black' : '';
                                                $onclickAction = ($pg[count($dates) - $dateColspan] == 1) ? "setLastClickedCell(this); programChangeShow('{$prog_ch_alert[count($dates) -$dateColspan]}', '{$prog_ch_made[count($dates) -$dateColspan]}', '{$target['client_id']}', '{$target['target_id']}')" : '';
                                                echo "<td colspan=\"$dateColspan\" class=\""  . $cellClass . " " . ((count($dates) - $dateColspan >= 0 && $phasesArray[count($dates) - $dateColspan] == 1) ? 'phase-1' : '') . "\" style=\"$borderStyle padding-top: 5px; padding-bottom: 5px; white-space: nowrap; $highlightStyle\" onclick=\"$onclickAction\">" . app_date($previousDate) . "</td>";
                                            }
                                            ?>
                                        </tr>
                                        <tr>
                                            <?php
                                            $previousDate = '';
                                            $dateColspan = 0;
                                            $valuesByDate = [];

                                            foreach ($dates as $index => $d) {
                                                if (!isset($valuesByDate[$d])) {
                                                    $valuesByDate[$d] = [
                                                        'values' => [],
                                                        'phase' => $phasesArray[$index],
                                                        'pg' => $pg[$index],
                                                        'session_id' => $session_ids[$index]
                                                    ];
                                                }
                                                $valuesByDate[$d]['values'][] = $values[$index];
                                            }

                                            foreach ($valuesByDate as $date => $data) {
                                                $borderStyle = ($data['phase'] == 1) ? 'width:150px; border-right-width: 3px;' : '';
                                                $highlightStyle = ($data['pg'] == 1) ? 'border-right-width: 3px; border-right-color:black' : '';

                                                // Initialize the class
                                                $cellClass = '';
                                                // Check if phase is 3 for black border
                                                if ($data['phase'] == 3) {
                                                    $cellClass .= ' black-right-border';
                                                }

                                                // Calculate colspan based on the number of values for the same date
                                                $colspan = count($data['values']);

                                                echo "<td colspan=\"$colspan\" class=\"" . ($data['phase'] == 1 ? 'phase-1' : '') . " " . ($cellClass) . "\" style=\"$borderStyle padding-top: 5px; padding-bottom: 5px; white-space: nowrap; $highlightStyle\" >";
                                                echo '<div class="d-flex flex-nowrap gap-1">';
                                                echo implode('', $data['values']);
                                                echo '</div>';
                                                echo "</td>";
                                            }
                                            ?>
                                        </tr>

                                    </table>
                                </div>

                            </p>
                        </a>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endforeach; ?>


        </div>
    </div>
</div>
<?php
$existingCollectedData = json_decode($existingData->collected_data);
$existingInputs = $existingCollectedData->inputs;
$existingResult = $existingCollectedData->result;
$existingPhaseName = $existingCollectedData->phase->name;

$existingStatus = '';
if ($existingData->is_processed) {
    $existingStatus = '<span class="badge border border-success text-success">Processed</span>';
} elseif ($existingData->is_conflicted) {
    $existingStatus = '<span class="badge border border-danger text-danger">Conflict</span>';
} else {
    $existingStatus = '<span class="badge border border-info text-info">Pending</span>';
}
?>

<!-- 🟢 Conflict Resolution Section -->
<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0">Current Target Data & Resolve Conflict</h5>
    </div>
    <div class="card-body">
        <!-- ✅ Display Target Information in a More Compact Format -->
        <p>
            Conflict detected on target <strong class="text-primary"><?= $existingData->target_name; ?></strong>
            during session on <strong><?= app_date($existingData->session_date); ?></strong>. Target phase when data was collected: <strong><?= $existingPhaseName; ?></strong>. Collected values:
            <span class="d-inline-flex flex-nowrap gap-1">
                <?php foreach ($existingResult as $value) : ?>
                    <span class="rounded-circle d-flex justify-content-center align-items-center"
                        style="width: 30px; height: 30px; background-color: #e0e0e0; font-size: 12px;">
                        <?= $value !== null && $value !== '' ? htmlspecialchars($value) : '<i class="ri-close-line text-danger"></i>' ?>
                    </span>
                <?php endforeach; ?>
            </span>

        </p>

        <!-- ⚠️ Warning Message -->
        <p class="text-muted">
            <strong>Warning:</strong> Resolving this conflict will **delete all processed data, program changes, alerts, degrees of independence, and mastered statuses** from this target starting from the <strong><?= app_date($existingData->session_date); ?></strong>.
            <br>This action **cannot be undone**.
        </p>

        <!-- ✅ User Confirmation Checkbox -->
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="confirmResolution">
            <label class="form-check-label text-danger" for="confirmResolution">
                I understand that this action is irreversible and want to proceed.
            </label>
        </div>
    </div>

    <!-- 🟢 Action Buttons -->
    <div class="card-footer text-end">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="offcanvas">
            <i class="ri-close-line"></i> Cancel
        </button>
        <button type="button" class="btn btn-danger" id="resolveConflictBtn"
            data-id="<?= $existingData->id; ?>" disabled>
            <i class="ri-git-merge-line"></i> Resolve Conflict
        </button>
    </div>
</div>

<script>
    // ✅ Enable/Disable Resolve Button based on checkbox
    document.getElementById('confirmResolution').addEventListener('change', function() {
        document.getElementById('resolveConflictBtn').disabled = !this.checked;
    });
</script>
