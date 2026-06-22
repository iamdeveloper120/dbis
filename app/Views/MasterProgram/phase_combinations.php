<div class="table-container mt-4">
    <h5>Target Phase Combinations</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Initial Phase</th>
                <th>Final Phase</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($phaseCombinations as $combination) : ?>
                <tr>
                     <td><?= $combination['name'] ?></td>
                    <td><?= $combination['description'] ?></td>
                    <td><?= $combination['initial_phase_name'] ?></td>
                    <td><?= $combination['final_phase_name'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
