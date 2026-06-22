<?php
// Function to convert session rating numbers to text
function getSessionRatingText($rating)
{
    $ratings = [
        '1' => 'Poor',
        '2' => 'Good',
        '3' => 'Excellent'
    ];
    return isset($ratings[$rating]) ? $ratings[$rating] : 'Not Rated';
}
?>
<div class="card border">
    <div class="card-body">
        <table class="table table-bordered table-info mt-2">
            <thead>
                <tr>
                    <th>Processed At</th>
                    <th>Processed By</th>
                    <th>Total Targets</th>
                    <th>Success</th>
                    <th>Conflicts</th>
                    <th>Deleted</th>
                    <th>Process Count</th>
                    <th>Session Status</th>
                    <th>Session Rating</th>
                    <th>Instructor Comments</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= app_date($log['processed_at'], true); ?></td>
                    <td><?= htmlspecialchars($log['first_name'] . ' ' . $log['last_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= $log['total_targets']; ?></td>
                    <td><?= $log['processed_success']; ?></td>
                    <td><?= $log['conflicted_targets']; ?></td>
                    <td><?= $log['deleted_targets']; ?></td>
                    <td><?= $log['process_count']; ?></td>
                    <td><?= $log['session_status']; ?></td>
                    <td><?= $sessionDetails ? getSessionRatingText($sessionDetails['session_rating']) : ''; ?></td>
                    <td><?= $sessionDetails ? $sessionDetails['instructor_comments'] : ''; ?></td>
                </tr>
            </tbody>
        </table>

        <!-- Conflicted Targets Table -->
        <?php if (!empty($conflictedTargets)): ?>
            <h5 class="mt-3 text-warning">Conflicted Targets</h5>
            <table class="table table-bordered mt-2">
                <thead>
                    <tr>
                        <th>Domain</th>
                        <th>Goal</th>
                        <th>Target</th>
                        <th>Phase</th>
                        <th>Result</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($conflictedTargets as $target): ?>
                        <tr>
                            <td><?= htmlspecialchars($target['data']['domain_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($target['data']['goal_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($target['data']['target_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($target['data']['phase_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($target['data']['result'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($target['message'], ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Deleted Targets Table -->
        <?php if (!empty($deletedTargets)): ?>
            <h5 class="mt-3 text-danger">Deleted Targets</h5>
            <table class="table table-bordered mt-2">
                <thead>
                    <tr>
                        <th>Domain</th>
                        <th>Goal</th>
                        <th>Target</th>
                        <th>Phase</th>
                        <th>Result</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($deletedTargets as $target): ?>
                        <tr>
                            <td><?= htmlspecialchars($target['data']['domain_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($target['data']['goal_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($target['data']['target_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($target['data']['phase_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($target['data']['result'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($target['message'], ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
          <!-- Deleted Targets Table -->
          <?php if (!empty($processedTargets)): ?>
            <h5 class="mt-3 text-danger">Processed Targets</h5>
            <table class="table table-bordered mt-2">
                <thead>
                    <tr>
                        <th>Domain</th>
                        <th>Goal</th>
                        <th>Target</th>
                        <th>Phase</th>
                        <th>Result</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($processedTargets as $target): ?>
                        <tr>
                            <td><?= htmlspecialchars($target['data']['domain_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($target['data']['goal_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($target['data']['target_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($target['data']['phase_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($target['data']['result'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($target['message'], ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>