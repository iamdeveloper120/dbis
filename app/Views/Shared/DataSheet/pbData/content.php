<div class="table-responsive">
    <table class="table table-bordered" style="width: 100%;" id="pb_dataTable">
        <thead>
            <tr>
                <th class="dt-nowrap">Session Date</th>
                <th class="dt-nowrap">Start Time</th>
                <th class="dt-nowrap">End Time</th>
                <th class="dt-nowrap">Duration</th>
                <th>Antecedent (A)</th>
                <th>Behavior (B)</th>
                <th>Consequence (C)</th>
                <th>Comments</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($pbDailyData) && !empty($pbDailyData)) : ?>
                <?php foreach ($pbDailyData as $data) : ?>
                    <tr>
                        <td class="dt-nowrap"><?= app_date(esc($data['session_date'])) ?></td>
                        <td class="dt-nowrap"><?= esc($data['start_time']) ?></td>
                        <td class="dt-nowrap"><?= esc($data['end_time']) ?></td>
                        <td class="dt-nowrap"><?= get_time_difference($data['start_time'], $data['end_time'], 'human'); ?></td>

                        <!-- Check if antecedent is 'Other' and display antecedent_other if it is -->
                        <td class="">
                            <?= esc($data['antecedent']); ?>
                        </td>
                        <!-- Check if behavior is 'Other' and display behavior_other if it is -->
                        <td class="">
                            <?php
                            $existing_behaviors = json_decode($data['behavior'], true); // Decode the JSON string
                            $behavior_display = [];
                            if ($existing_behaviors) {
                                foreach ($existing_behaviors as $behavior) {
                                    //$behavior_display[] = esc($behavior['behavior']) . " (Intensity: " . esc($behavior['intensity']) . ")";
                                    $behavior_display[] = esc($behavior['behavior']);
                                }
                            }
                            echo implode(', ', $behavior_display); // Display behaviors with intensities
                            ?>
                        </td>


                        <!-- Check if consequence is 'Other' and display consequence_other if it is -->
                        <td class="">
                            <?= esc($data['consequence']); ?>
                        </td>
                        <td style="word-wrap: break-word">
                            <?= esc($data['abc_comments']); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>