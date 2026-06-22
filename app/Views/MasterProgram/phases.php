<div class="table-container mt-4">
    <h5>Target Phases</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($targetPhases as $phase) : ?>
                <tr>
                    <td><?= $phase['name'] ?></td>
                    <td><?= $phase['description'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>