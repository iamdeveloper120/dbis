<?= $this->extend("layout/master") ?>
<?= $this->section("page_content") ?>

<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h5 class="mb-sm-0">Data Sheets</h5>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active">DOI Targets</li>
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
                                <a href="<?= base_url() ?>dataSheet" type="button" class="btn btn-sm btn-light btn-label float-end">
                                    <i class="ri-arrow-left-line label-icon align-middle fs-16 me-2"></i>Back to Client List
                                </a>
                                <h6 class="card-title mb-0"><?= esc($client->internal_mrn) ?> - <?= esc($client->name()) ?></h6>
                            </div>
                            <div class="card-header pb-0 mb-0">
                                <?= view('ClientDataSheet/_tabs', ['tab' => 'doiTab']) ?>
                            </div>
                            <div class="card-body border-bottom-dashed border-bottom">
                                <div class="border-bottom-dashed border-bottom" style="padding-top: 0px; padding-bottom:10px; margin-bottom:20px">
                                    <form>
                                        <div class="row g-3">
                                            <div class="col-xxl-4 col-sm-12">
                                                <select class="form-control" name="choices-single-default" id="sDomain">
                                                    <option value="" selected>All Domains</option>
                                                    <?php
                                                    foreach ($domains as $domain) {  ?>
                                                        <option value="<?php echo $domain->id; ?>">
                                                            <?php echo $domain->name . ' ( ' . $domain->domain_code . ' )  '; ?></option>
                                                    <?php } ?>

                                                </select>
                                            </div>
                                            <!--end col-->
                                            <div class="col-xxl-4 col-sm-12">
                                                <select class="form-control" name="choices-single-default" id="sGoal">
                                                    <option value="" selected>All Goals</option>
                                                </select>
                                            </div>
                                            <div class="col-xxl-2 col-sm-12">
                                                <select class="form-control" name="choices-single-default" id="sProbe">
                                                    <option value="" selected>All Probe Set</option>
                                                    <?php
                                                    foreach ($probeSets as $probe) {  ?>
                                                        <option value="<?php echo $probe['id']; ?>">
                                                            <?php echo $probe['name']; ?></option>
                                                    <?php } ?>

                                                </select>
                                            </div>

                                            <!--end col-->
                                            <div class="col-xxl-2 col-sm-12">
                                                <button id="filter_data" type="button" class="btn btn-outline-primary w-100"> <i class="ri-equalizer-line me-1 align-bottom"></i>Apply Filter</button>

                                            </div>
                                            <!--end col-->
                                        </div>
                                        <!--end row-->
                                    </form>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered" style="width: 100%;" id="doi_targets_data_table">
                                        <thead>
                                            <tr>
                                                <th class="dt-nowrap">Date</th>
                                                <th class="dt-nowrap">Probe Set</th>
                                                <th class="dt-nowrap">Domain</th>
                                                <th class="dt-nowrap">Goal</th>
                                                <th class="dt-nowrap">Target</th>
                                                <th class="dt-nowrap">DOI Value</th>
                                            </tr>
                                        </thead>

                                    </table>
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

<?= $this->endSection() ?>

<?= $this->section("page_js") ?>
<script>
    $(document).ready(function() {
        $('select').select2();

        var csrfToken = "<?= csrf_hash() ?>";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        });

        var doiTable = $('#doi_targets_data_table').DataTable({
            responsive: false,
            ordering: false,
            lengthChange: false,
            ajax: {
                url: '<?= base_url('dataSheet/filterDOI') ?>',
                type: 'POST',
                data: function(d) {
                    // Send the filter data with the AJAX request
                    d.client_id = '<?= $client->id ?>';
                    d.domain_id = $('#sDomain').val();
                    d.goal_id = $('#sGoal').val();
                    d.probe_set_id = $('#sProbe').val();
                },
                dataSrc: function(json) {
                    // If the server returns JSON, process it here
                    return json;
                }
            },
            columns: [{
                    data: 'session_date',
                    render: function(data, type, row) {
                        // Format the date using Moment.js and the momentDateFormat
                        return moment(data).format(momentDateFormat);
                    }
                },
                {
                    data: 'probe_set_name'
                },
                {
                    data: 'domain_code'
                },
                {
                    data: 'goal_code'
                },
                {
                    data: 'target_name'
                },
                {
                    data: 'doi_value'
                }
            ],
            lengthMenu: [
                [10, 25, 50, -1],
                ['10 rows', '25 rows', '50 rows', 'Show all']
            ],
            layout: {
                topStart: {
                    buttons: [{
                        extend: 'pageLength',
                        className: 'btn btn-light bg-gradient waves-effect waves-light'
                    }, {
                        extend: 'copy',
                        className: 'btn btn-light bg-gradient waves-effect waves-light'
                    }, {
                        extend: 'excel',
                        className: 'btn btn-light bg-gradient waves-effect waves-light'
                    }, {
                        extend: 'colvis',
                        className: 'btn btn-light bg-gradient waves-effect waves-light'
                    }]
                },
                topEnd: {
                    search: {
                        placeholder: 'Search',
                    }
                }
            }
        });

        // Listen to the domain selection change
        $('#sDomain').on('change', function() {
            var domain_id = $(this).val();
            var client_id = '<?= $client->id ?>'; // Get the client ID from PHP
            var probe_type = 'count'; // Hardcoded for this view, can be dynamic for other views

            // Clear the existing options in the Goals dropdown and add "All Goals" option
            $('#sGoal').empty().append('<option value="">All Goals</option>');

            if (domain_id !== '') {
                // Send an AJAX request to fetch goals for the selected domain
                $.ajax({
                    url: '<?= base_url('dataSheet/getClientGoalsForFilter') ?>',
                    type: 'POST',
                    data: {
                        client_id: client_id,
                        domain_id: domain_id,
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

        // Apply filter and reload DataTable
        $('#filter_data').on('click', function() {
            doiTable.ajax.reload(); // This will reload the table based on the filters
        });

        // When the "Apply Filter" button is clicked
        /*$('#filter_data').on('click', function() {
            var domain_id = $('#sDomain').val();
            var goal_id = $('#sGoal').val();
            var client_id = '<?= $client->id ?>';

            // Send an AJAX request to filter the data
            $.ajax({
                url: '<?= base_url('dataSheet/filterDOI') ?>',
                type: 'POST',
                data: {
                    client_id: client_id,
                    domain_id: domain_id,
                    goal_id: goal_id,
                },
                success: function(response) {
                    // Replace the table content with the filtered data


                },
                error: function(xhr, status, error) {
                    console.log('Error fetching filtered data:', error);
                }
            });
        });*/
    });
</script>
<?= $this->endSection() ?>