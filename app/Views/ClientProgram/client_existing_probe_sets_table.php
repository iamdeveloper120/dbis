<table class="table table-bordered">
    <thead>
        <tr>
            <th>Client Name</th>
            <th>Domain Name</th>
            <th>Goal Name</th>
            <th>Probe Set Name</th>
            <th>Combination Name</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($probeSets as $probeSet) : ?>
            <tr>
                <td><?= $probeSet['client_name'] ?></td>
                <td><?= $probeSet['domain_name'] ?></td>
                <td><?= $probeSet['goal_name'] ?></td>
                <td><?= $probeSet['probe_set_name'] ?></td>
                <td><?= $probeSet['combination_name'] ?></td>
                <td><?= $probeSet['is_active'] ? 'Active' : 'Inactive' ?></td>
                <td>
                    <?php if ($probeSet['is_active']) : ?>
                        <button class="btn btn-outline-warning edit-probe-set" data-id="<?= $probeSet['id'] ?>" data-client-id="<?= $probeSet['client_id'] ?>" data-goal-id="<?= $probeSet['goal_id'] ?>" data-client-probe-set-id="<?= $probeSet['id'] ?>" data-probe-set-id="<?= $probeSet['probe_set_id'] ?>" data-combination-id="<?= $probeSet['combination_id'] ?>"><i class="ri-edit-line"></i>Edit</button>
                    <?php else : ?>
                        <button class="btn btn-outline-warning edit-probe-set" data-id="<?= $probeSet['id'] ?>" data-client-id="<?= $probeSet['client_id'] ?>" data-goal-id="<?= $probeSet['goal_id'] ?>" data-client-probe-set-id="<?= $probeSet['id'] ?>" data-probe-set-id="<?= $probeSet['probe_set_id'] ?>" data-combination-id="<?= $probeSet['combination_id'] ?>"><i class="ri-edit-line"></i>Edit</button>
                        <button class="btn btn-outline-success activate-probe-set" data-id="<?= $probeSet['id'] ?>" data-client-id="<?= $probeSet['client_id'] ?>" data-goal-id="<?= $probeSet['goal_id'] ?>"><i class="ri-link"></i>Activate</button>
                    <?php endif; ?>
                    <button class="btn btn-outline-danger delete-probe-set" data-id="<?= $probeSet['id'] ?>" data-client-id="<?= $probeSet['client_id'] ?>" data-goal-id="<?= $probeSet['goal_id'] ?>"><i class="ri-delete-bin-line"></i>Delete</button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>