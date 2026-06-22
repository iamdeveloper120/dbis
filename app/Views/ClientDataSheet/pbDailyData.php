<?= $this->extend("layout/master") ?>
<?= $this->section("page_content") ?>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h5 class="mb-sm-0">Data Sheets</h5>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active">Problem Behavior</li>
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
                                <?= view('ClientDataSheet/_tabs', ['tab' => 'pbTab']) ?>
                            </div>
                            <div class="card-body border-bottom-dashed border-bottom">
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
                                                          <td class="">
                                                            <?= esc($data['abc_comments']); ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
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
<?= $this->section("page_modal") ?>


<?= $this->endSection() ?>

<?= $this->section("page_js") ?>
<script>
    $(document).ready(function() {

        /***************************************************************************************** */
        var csrfToken = "<?= csrf_hash() ?>";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        });
        /***************************************************************************************** */
        var pbTable = $('#pb_dataTable').DataTable({
            response: false,
            ordering: false,
            lengthChange: false,
            lengthMenu: [
                [10, 25, 50, -1],
                ['10 rows', '25 rows', '50 rows', 'Show all']
            ],
            layout: {
                topStart: {
                    buttons: [{
                            extend: 'pageLength',
                            className: 'btn btn-light bg-gradient waves-effect waves-light'
                        },
                        {
                            extend: 'copy',
                            className: 'btn btn-light bg-gradient waves-effect waves-light'
                        }, {
                            extend: 'excel',
                            className: 'btn btn-light bg-gradient waves-effect waves-light'
                        },
                        {
                            extend: 'colvis',
                            className: 'btn btn-light bg-gradient waves-effect waves-light'
                        }
                    ]
                },
                topEnd: {
                    search: {
                        placeholder: 'Search',

                    },

                }
            },


        });


    });
</script>
<?= $this->endSection() ?>