<?= $this->extend("layout/master") ?>
<?= $this->section("head_tag") ?>
<style>
    .input-list {
        list-style-type: none;
        padding: 0;
    }

    .input-list li {
        margin-bottom: 5px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section("page_content") ?>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h5 class="mb-sm-0">Target Phases, Combination, Probe Set and Rules</h5>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active">Rules</li>
        </ol>
    </div>
</div>
<div class="card">   
    <div class="card-body">
        <div class="step-arrow-nav mb-4">
            <ul class="nav nav-pills custom-nav nav-justified" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-phases-nav" data-bs-toggle="pill" data-bs-target="#tab-phases" type="button" role="tab" aria-controls="tab-phases-nav" aria-selected="true">Target Phases</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link " id="tab-combinations-nav" data-bs-toggle="pill" data-bs-target="#tab-combinations" type="button" role="tab" aria-controls="tab-combinations-nav" aria-selected="false">Target Phases Combinations</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-rules-nav" data-bs-toggle="pill" data-bs-target="#tab-rules" type="button" role="tab" aria-controls="tab-rules-nav" aria-selected="false">Probe Sets & Rules for Combinations</button>
                </li>
            </ul>
        </div>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab-phases" role="tabpanel" aria-labelledby="tab-phases">
                <?= view('MasterProgram/phases') ?>
            </div>
            <!-- end tab pane -->

            <div class="tab-pane fade" id="tab-combinations" role="tabpanel" aria-labelledby="tab-combinations">
                <?= view('MasterProgram/phase_combinations') ?>
            </div>
            <!-- end tab pane -->
            <div class="tab-pane fade" id="tab-rules" role="tabpanel" aria-labelledby="tab-rules">
                <div class="mt-4">
                    <!-- Probe Sets Dropdown -->
                    <div class="mb-3">
                        <select id="probeSetSelect" class="form-select" onchange="loadProbeSetDetails(this.value)">
                            <option value="">Select a probe set</option>
                            <?php foreach ($probeSets as $probeSet) : ?>
                                <option value="<?= $probeSet['id'] ?>"><?= esc($probeSet['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Placeholder for loading details -->
                    <div id="probeSetDetailsContainer"></div>
                </div>
            </div>
            <!-- end tab pane -->
        </div>
        <!-- end tab content -->
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section("page_js") ?>
<script>
    function loadProbeSetDetails(probeSetId) {
        if (probeSetId === "") {
            $('#probeSetDetailsContainer').empty();
            return;
        }

        $.ajax({
            url: `/master-program/probe-set-details/${probeSetId}`,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                // Display the HTML received from the server
                $('#probeSetDetailsContainer').html(response.probeSetHtml);
                $('#probeSetDetailsContainer').append(response.rulesHtml);
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                alert('An error occurred while loading the probe set details.');
            }
        });
    }

    $(document).ready(function() {
        var csrfToken = "<?= csrf_hash() ?>";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        });


    });
</script>


<?= $this->endSection() ?>