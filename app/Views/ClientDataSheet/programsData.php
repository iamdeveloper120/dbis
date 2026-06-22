<?= $this->extend("layout/master") ?>
<?= $this->section("head_tag") ?>
<style>
    .offcanvas,
    .offcanvas-lg,
    .offcanvas-md,
    .offcanvas-sm,
    .offcanvas-xl,
    .offcanvas-xxl {

        --vz-offcanvas-width: 100%;

    }


    table th,
    table td {
        white-space: nowrap;
    }

    /* Global Red Border for the Right */
    .table-red-right-border>tbody>tr>td:last-child,
    .table-red-right-border>tbody>tr>th:last-child {
        border-right-width: 3px;
        border-right-color: red;
    }

    .table-black-right-border>tbody>tr>td:last-child,
    .table-black-right-border>tbody>tr>th:last-child {
        border-right-width: 3px;
        border-right-color: black;
    }

    /* Black Border for Phase 3 (Right Inner Border) */
    .black-right-border::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        /* Offset from the red border */
        height: 100%;
        width: 2px;
        background-color: black;
    }

    /* Positioning for the cells */
    .table td,
    .table th {
        position: relative;
        /* Required for pseudo-elements to work */
    }


    .phase-1 {
        width: 150px;
        min-width: 150px;
        max-width: 150px;
    }

    .px200 {
        width: 150px;
        min-width: 150px;
        max-width: 150px;
        word-wrap: break-word;
    }

    .no-hover {
        pointer-events: none;
        /* This disables all pointer interactions */
    }

    .no-hover:hover {
        background-color: inherit;
        /* Keeps the original background color on hover */
        color: inherit;
        /* Keeps the original text color on hover */
        cursor: default !important;
        /* Force the default cursor */
        /* Sets the cursor to the default arrow */
    }

    .list-group-item {
        cursor: default !important;
    }

    .list-group-item:hover {
        background-color: #2074ba1a;
        /* Replace with your preferred hover color */
        /* You can add additional hover styles here if needed */
    }
</style>
<?= $this->endSection() ?>
<?= $this->section("page_content") ?>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h5 class="mb-sm-0">Data Sheets</h5>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active">List</li>
        </ol>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="tab-content text-muted">
            <div class="tab-pane active show" role="tabpanel">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <a href="<?= base_url() ?>dataSheet" type="button" class="btn btn-sm btn-light btn-label  float-end"><i class="ri-arrow-left-line label-icon align-middle fs-16 me-2"></i>Back to Client List</a>
                                <h6 class="card-title mb-0"><?= $client->internal_mrn ?> - <?= $client->name() ?></h6>
                            </div>
                            <div class="card-header pb-0 mb-0">
                                <?= view('ClientDataSheet/_tabs', ['tab' => 'yesNoTab']) ?>
                            </div>
                            <div class="card-body border-bottom-dashed border-bottom">
                                <div class="border-bottom-dashed border-bottom" style="padding-top: 0px; padding-bottom:10px; margin-bottom:20px">
                                    <form>
                                        <div class="row g-3">
                                            <div class="col-xxl-5 col-sm-12">
                                                <div>
                                                    <select class="form-control" name="choices-single-default" id="sDomain">
                                                        <option value="" selected>All Domains</option>
                                                        <?php
                                                        foreach ($domains as $domain) {  ?>
                                                            <option value="<?php echo $domain->id; ?>">
                                                                <?php echo $domain->name . ' ( ' . $domain->domain_code . ' )  '; ?></option>
                                                        <?php } ?>

                                                    </select>
                                                </div>
                                            </div>
                                            <!--end col-->
                                            <div class="col-xxl-5 col-sm-12">
                                                <div>
                                                    <select class="form-control" name="choices-single-default" id="sGoal">
                                                        <option value="" selected>All Goals</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <!--<div class="col-xxl-2 col-sm-12">
                                                <div>
                                                    <select class="form-control" name="choices-single-default" id="sProbeSet">
                                                        <option value="" selected>All Probe Sets</option>
                                                        <option value="yes_no">Yes/No</option>
                                                        <option value="traffic_light">Traffic Light</option>
                                                        <option value="prompt_level">Prompt Level</option>
                                                        <option value="duration">Duration</option>
                                                        <option value="count">Count</option>
                                                    </select>
                                                </div>
                                            </div>-->

                                            <!--end col-->
                                            <div class="col-xxl-2 col-sm-12">
                                                <div>
                                                    <button id="filter_data" type="button" class="btn btn-outline-primary w-100"> <i class="ri-equalizer-line me-1 align-bottom"></i>Apply Filter</button>
                                                </div>
                                            </div>
                                            <!--end col-->
                                        </div>
                                        <!--end row-->
                                    </form>
                                </div>
                                <div id="dataSheetTableArea">
                                    <?= view('ClientDataSheet/programsDataTable') ?>
                                </div>
                            </div>

                        </div>
                        <!--end col-->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end col-->
</div>
<!-- right offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">

    <div class="offcanvas-body " id='prog_ch_area' style="background-color: lightgrey;">
        ...
    </div>
    <div class="offcanvas-footer border p-3 text-center">
        <a href="javascript:void(0);" class="link-primary" data-bs-dismiss="offcanvas">Back to target data sheet <i class="ri-arrow-right-s-line align-middle ms-1"></i></a>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="rulesCanvas" aria-labelledby="offcanvasRightLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="rulesCanvasTitle"> </h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body " id='rulesCanvasDetail'> </div>
    <div class="offcanvas-footer border p-3 text-center">
        <a href="javascript:void(0);" class="btn btn-primary" data-bs-dismiss="offcanvas"><i class="ri-arrow-left-s-line align-middle ms-1"></i> Back to Data Sheet</a>
    </div>
</div>
<div class="offcanvas offcanvas-end" tabindex="-1" id="transitionEntriesCanvas" aria-labelledby="offcanvasRightLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="transitionEntriesCanvasTitle"> </h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body " id='transitionEntriesCanvasDetail'> </div>
    <div class="offcanvas-footer border p-3 text-center">
        <a href="javascript:void(0);" class="btn btn-primary" data-bs-dismiss="offcanvas"><i class="ri-arrow-left-s-line align-middle ms-1"></i> Back to Data Sheet</a>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="targetStepsCanvas" aria-labelledby="offcanvasRightLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="targetStepsCanvasTitle"> </h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body " id='targetStepsCanvasDetail'> </div>
    <div class="offcanvas-footer border p-3 text-center">
        <a href="javascript:void(0);" class="btn btn-primary" data-bs-dismiss="offcanvas"><i class="ri-arrow-left-s-line align-middle ms-1"></i> Back to Data Sheet</a>
    </div>
</div>
<?= $this->endSection() ?>


<?= $this->section("page_js") ?>
<script>
    /*function bindSearchAndSortLogic() {
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.onkeyup = function() {
                filterList();
                updateTargetCount();
            };
        }

        sortTargetsByLatestDate();
        updateTargetCount();
    }

    function sortTargetsByLatestDate() {
        const listContainer = document.getElementById('listContainer');
        if (!listContainer) return;

        const items = Array.from(listContainer.querySelectorAll('.list-group-item'));

        items.sort((a, b) => {
            // Level 1: latest session date DESC
            const dateA = new Date(a.dataset.latestDate || '1900-01-01');
            const dateB = new Date(b.dataset.latestDate || '1900-01-01');
            const dateDiff = dateB - dateA;
            if (dateDiff !== 0) return dateDiff;

            // Level 2: domain code ASC
            const domainA = a.dataset.domainCode || '';
            const domainB = b.dataset.domainCode || '';
            if (domainA !== domainB) return domainA.localeCompare(domainB);

            // Level 3: goal code ASC
            const goalA = a.dataset.goalCode || '';
            const goalB = b.dataset.goalCode || '';
            if (goalA !== goalB) return goalA.localeCompare(goalB);

            // Level 4: target name ASC
            const nameA = a.dataset.targetName?.toLowerCase() || '';
            const nameB = b.dataset.targetName?.toLowerCase() || '';
            return nameA.localeCompare(nameB);
        });

        items.forEach(item => listContainer.appendChild(item));
    }


    function filterList() {
        const input = document.getElementById('searchInput');
        const filter = input.value.toLowerCase();
        const listContainer = document.getElementById('listContainer');
        const items = listContainer.getElementsByClassName('list-group-item');

        for (let i = 0; i < items.length; i++) {
            const itemText = items[i].innerText || items[i].textContent;
            items[i].style.display = itemText.toLowerCase().indexOf(filter) > -1 ? "" : "none";
        }
    }

    function updateTargetCount() {
        const items = document.querySelectorAll('#listContainer .list-group-item');
        let visibleCount = 0;
        items.forEach(item => {
            if (item.style.display !== "none") visibleCount++;
        });

        const countEl = document.getElementById('targetCount');
        if (countEl) {
            countEl.textContent = "Total Targets: " + visibleCount;
        }
    }*/
    // Global filter state
    let currentStatusFilter = 'All';

    function bindSearchAndSortLogic() {
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.onkeyup = function() {
                applyCombinedFilters();
            };
        }

        // Bind status filter buttons
        document.querySelectorAll('.status-filter').forEach(btn => {
            btn.addEventListener('click', function() {
                // Toggle active class
                document.querySelectorAll('.status-filter').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                // Set global filter and reapply
                currentStatusFilter = this.dataset.filter;
                document.getElementById('searchInput').value = ''; // clear search input
                applyCombinedFilters();
            });
        });

        sortTargetsByPriority();
        applyCombinedFilters(); // Filter and count together
    }

    function applyCombinedFilters() {
        const searchText = document.getElementById('searchInput').value.toLowerCase();
        const items = document.querySelectorAll('#listContainer .list-group-item');

        items.forEach(item => {
            const textMatch = (item.innerText || item.textContent).toLowerCase().includes(searchText);
            const statusMatch = currentStatusFilter === 'All' || (item.dataset.status === currentStatusFilter);

            item.style.display = (textMatch && statusMatch) ? "" : "none";
        });

        updateTargetCount();
    }

    function sortTargetsByPriority() {
        const listContainer = document.getElementById('listContainer');
        if (!listContainer) return;

        const items = Array.from(listContainer.querySelectorAll('.list-group-item'));

        items.sort((a, b) => {
            const dateA = new Date(a.dataset.latestDate || '1900-01-01');
            const dateB = new Date(b.dataset.latestDate || '1900-01-01');
            const dateDiff = dateB - dateA;
            if (dateDiff !== 0) return dateDiff;

            const domainA = a.dataset.domainCode || '';
            const domainB = b.dataset.domainCode || '';
            if (domainA !== domainB) return domainA.localeCompare(domainB);

            const goalA = a.dataset.goalCode || '';
            const goalB = b.dataset.goalCode || '';
            if (goalA !== goalB) return goalA.localeCompare(goalB);

            const nameA = a.dataset.targetName?.toLowerCase() || '';
            const nameB = b.dataset.targetName?.toLowerCase() || '';
            return nameA.localeCompare(nameB);
        });

        items.forEach(item => listContainer.appendChild(item));
    }

    function updateTargetCount() {
        const items = document.querySelectorAll('#listContainer .list-group-item');
        let visibleCount = 0;
        items.forEach(item => {
            if (item.style.display !== "none") visibleCount++;
        });

        const countEl = document.getElementById('targetCount');
        if (countEl) {
            countEl.textContent = "Total Targets: " + visibleCount;
        }
    }
</script>

<script>
    $(document).ready(function() {

        /***************************************************************************************** */
        var csrfToken = "<?= csrf_hash() ?>";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        });
        $('select').select2();
        /***************************************************************************************** */



        // Listen to the domain selection change
        $('#sDomain').on('change', function() {
            var domain_id = $(this).val();
            var client_id = '<?= $client->id ?>';

            // Clear the existing options in the Goals dropdown and add "All Goals" option
            $('#sGoal').empty().append('<option value="">All Goals</option>');

            if (domain_id !== '') {
                // Send an AJAX request to fetch goals for the selected domain
                $.ajax({
                    url: '<?= base_url('dataSheet/getGoalsByDomain') ?>',
                    type: 'POST',
                    data: {
                        client_id: client_id,
                        domain_id: domain_id,
                        csrf_test_name: csrfToken // Include the CSRF token
                    },
                    success: function(response) {
                        // Log the response for debugging
                        // Populate the Goals dropdown with the fetched goals from the object
                        if (response && response.length > 0) {
                            $.each(response, function(index, goal) {
                                $('#sGoal').append(
                                    $('<option></option>').attr('value', goal.id).text(goal.name + ' (' + goal.goal_code + ')')
                                );
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('Error fetching goals:', error);
                    }
                });
            }
        });

        // When the "Apply Filter" button is clicked
        $('#filter_data').on('click', function() {
            var domain_id = $('#sDomain').val();
            var goal_id = $('#sGoal').val();
            var probeSet = null; //$('#sProbeSet').val();
            var client_id = '<?= $client->id ?>';


            // Send an AJAX request to filter the data
            $.ajax({
                url: '<?= base_url('dataSheet/filterProgramData') ?>',
                type: 'POST',
                data: {
                    client_id: client_id,
                    domain_id: domain_id,
                    goal_id: goal_id,
                    probeSet: probeSet,
                },
                success: function(response) {
                    $('#dataSheetTableArea').html(response);
                    // 🔁 Rebind sorting, counting, and filtering
                    bindSearchAndSortLogic();
                },
                error: function(xhr, status, error) {
                    console.log('Error fetching filtered data:', error);
                }
            });
        });

        $('#dataSheetTableArea').on('click', '.active-probe-detail', function() {

            var goal_id = $(this).attr('goal-id');
            var client_id = $(this).attr('client-id');
            openActiveProbeSetRules(client_id, goal_id);
        });
        $('#dataSheetTableArea').on('click', '.percentage-yes-no', function() {

            var collection_id = $(this).attr('data-collection-id');
            openTransitionEntries(collection_id);
        });
        $('#dataSheetTableArea').on('click', '.stimulus-program', function() {

            var collection_id = $(this).attr('data-collection-id');
            var target_id = $(this).attr('data-target-id');
            openTargetSteps(collection_id, target_id);
        });
        // Initial sort and count on first render
        bindSearchAndSortLogic();
    });

    /************************************************************************************************* */
    var offcanvasRight = document.getElementById('offcanvasRight')
    var bsc = new bootstrap.Offcanvas(offcanvasRight)
    var prog_ch_btn = null;

    var rulesCanvasID = document.getElementById('rulesCanvas');
    var rulesCanvas = new bootstrap.Offcanvas(rulesCanvasID);


    $('#offcanvasRight').on('hidden.bs.offcanvas', function() {
        // Trigger a custom event when the offcanvas is hidden
        $('#prog_ch_area').html('');
    });



    function programChangeShow(pg_alert_id, pg_change_id, client_id, target_id) {

        console.log(pg_alert_id, pg_change_id, client_id, target_id);
        var ajaxRequest = $.ajax({
            type: 'POST',
            url: '<?= base_url('sessions/programChange/getForm') ?>',
            data: {
                pg_alert_id: pg_alert_id,
                pg_change_id: pg_change_id,
                client_id: client_id,
                target_id: target_id,
            },
            dataType: 'html',
            beforeSend: function(xhr) {

            }
        });
        ajaxRequest.done(function(response) {
            // Update program list content
            if (response == '') {
                showAlert('', "No program change has been made", 'info');
            } else {
                $('#prog_ch_area').html(response);
                bsc.show()
            }

        });

        ajaxRequest.fail(function(jqXHR, textStatus, error) {
            showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
        });
        ajaxRequest.always(function() {

        });
    }

    $('#rulesCanvas').on('hidden.bs.offcanvas', function() {
        // Trigger a custom event when the offcanvas is hidden
        $('#rulesCanvasDetail').html('');
    });

    function openActiveProbeSetRules(client_id, goal_id) {

        $.ajax({
            url: '<?php echo base_url() ?>client-program/goal/load-client-active-probe-set-rules', // Endpoint to load the probe sets list
            type: 'post',
            data: {
                "goal_id": goal_id,
                "client_id": client_id
            },
            success: function(response) {
                if (response.status === 'success') {
                    $('#rulesCanvasTitle').html('Active Probe Set and Rules for selected client and goal');
                    $('#rulesCanvasDetail').html(response.html);
                    $('#rulesCanvasDetail input').prop('disabled', true);
                    rulesCanvas.show()
                } else {
                    showAlert(response.statusText, response.message, response.status);
                }
            },
            error: function(jqXHR, textStatus, error) {
                showAlert('Error', "Request failed: " + textStatus + '<br>' + error, 'error');
            }
        });
    }

    var lastClickedCell = null;

    function setLastClickedCell(cell) {
        lastClickedCell = cell;
    }

    var transitionEntriesCanvasID = document.getElementById('transitionEntriesCanvas');
    var transitionEntriesCanvas = new bootstrap.Offcanvas(transitionEntriesCanvasID);

    $('#transitionEntriesCanvas').on('hidden.bs.offcanvas', function() {
        // Trigger a custom event when the offcanvas is hidden
        $('#transitionEntriesCanvasDetail').html('');
    });

    function openTransitionEntries(collection_id) {

        $.ajax({
            url: '<?php echo base_url() ?>dataSheet/programData/percentage-probe-yes-no/transition-list', // Endpoint to load the probe sets list
            type: 'post',
            data: {
                "collection_id": collection_id,
            },
            success: function(response) {
                $('#transitionEntriesCanvasTitle').html('Trial data');
                $('#transitionEntriesCanvasDetail').html(response.html);
                transitionEntriesCanvas.show()
            },
            error: function(jqXHR, textStatus, error) {
                showAlert('Error', "Request failed: " + textStatus + '<br>' + error, 'error');
            }
        });
    }

    var targetStepsCanvasID = document.getElementById('targetStepsCanvas');
    var targetStepsCanvasCanvas = new bootstrap.Offcanvas(targetStepsCanvasID);

    $('#targetStepsCanvas').on('hidden.bs.offcanvas', function() {
        // Trigger a custom event when the offcanvas is hidden
        $('#targetStepsCanvasDetail').html('');
    });

    function openTargetSteps(collection_id, target_id) {

        $.ajax({
            url: '<?php echo base_url() ?>dataSheet/programData/stimulust-program/steps-data-sheet', // Endpoint to load the probe sets list
            type: 'post',
            data: {
                "collection_id": collection_id,
                "target_id": target_id,
            },
            success: function(response) {
                $('#targetStepsCanvasTitle').html('Stimulus Response Chain');
                $('#targetStepsCanvasDetail').html(response.html);
                targetStepsCanvasCanvas.show()
            },
            error: function(jqXHR, textStatus, error) {
                showAlert('Error', "Request failed: " + textStatus + '<br>' + error, 'error');
            }
        });
    }
</script>
<?= $this->endSection() ?>