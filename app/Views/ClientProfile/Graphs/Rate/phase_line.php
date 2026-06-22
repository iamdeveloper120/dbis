<?= $this->extend("layout/master-profile") ?>
<?= $this->section("head_tag") ?>
<?= $this->endSection() ?>
<?= $this->section("page_content") ?>
<div class="tab-content text-muted">
    <div class="tab-pane active show" role="tabpanel">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header border-bottom-dashed pb-0 mb-0">
                        <?= view('ClientProfile/Graphs/Rate/_tabs', ['tab' => 'phase-line']) ?>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="phase_line_table" class="table table-bordered  align-middle" style="width:100%"> </table>
                        </div>
                    </div>

                </div>
                <!--end col-->
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section("page_js") ?>
<script>
    $(document).ready(function() {
        /***************************************************************************************** */
        let client_id = "<?= $client->id ?>"; // passed from controller
        var csrfToken = "<?= csrf_hash() ?>";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        });
        // Page-level loader binding (ONLY for this page)
        $(document).ajaxStart(function() {
            showPageLoader();
        });

        $(document).ajaxStop(function() {
            hidePageLoader();
        });
        /***************************************************************************************** */

        var dataSet = [];

        table = $('#phase_line_table').DataTable({
            response: false,
            data: dataSet,
            lengthChange: false,
            ordering: false,
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
                        },
                        {
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
                        placeholder: 'Search'
                    }
                }
            },

            columnDefs: [{
                    targets: [0],
                    render: function(data, type, row) {
                        if (type === 'sort' || type === 'type') {
                            // Return the original date format as a sortable value
                            return moment(data, 'YYYY-MM-DD').format('YYYYMMDD');
                        }
                        // Return the formatted date for display
                        return moment(data, 'YYYY-MM-DD').format(momentDateFormat);
                    }
                },

                {
                    targets: [0, 1],
                    className: 'dt-nowrap'
                },
            ],
            columns: [{
                    data: 'p_date',
                    title: 'Date'
                }, // Date
                {
                    data: 'p_key',
                    title: 'Phase Line Key'
                }
            ]


        });

        /****************************************************************************************  */

        function loadPhaseline() {

            var ajaxRequest = $.ajax({
                url: '/graphs/rate/phase-line/list',
                type: 'post',
                data: {
                    "client_id": client_id,
                    "graph_type": 'Target_Rate'
                },
                beforeSend: function(xhr) {

                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    table.clear();
                    table.rows.add(response.data);
                    table.draw();
                } else if (response.status == 'validation_error') {
                    let errors = Object.values(response.message);
                    displayValidationErrors(errors);
                } else {
                    showAlert(response.statusText, response.message, response.status);
                }
            });

            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });

        }
        loadPhaseline();
        /***************************************************************************************** */

    });
</script>
<?= $this->endSection() ?>