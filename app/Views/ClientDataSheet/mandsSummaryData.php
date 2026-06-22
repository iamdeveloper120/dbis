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

    .btn-soft-custom-danger {
        --vz-btn-color: #f06548;
        --vz-btn-bg: rgba(240, 101, 72, 0.1);
        --vz-btn-border-color: transparent;
        --vz-btn-hover-color: #f06548;
        --vz-btn-hover-bg: rgba(240, 101, 72, 0.1);
        --vz-btn-hover-border-color: transparent;
        --vz-btn-focus-shadow-rgb: 240, 101, 72;
        --vz-btn-active-color: #f06548;
        --vz-btn-active-bg: rgba(240, 101, 72, 0.1);
        --vz-btn-active-border-color: transparent;
    }

    .btn-soft-custom-success {
        --vz-btn-color: #0ab39c;
        --vz-btn-bg: rgba(10, 179, 156, 0.1);
        --vz-btn-border-color: transparent;
        --vz-btn-hover-color: #0ab39c;
        --vz-btn-hover-bg: rgba(10, 179, 156, 0.1);
        --vz-btn-hover-border-color: transparent;
        --vz-btn-focus-shadow-rgb: 10, 179, 156;
        --vz-btn-active-color: var(--vz-btn-hover-color);
        --vz-btn-active-bg: rgba(10, 179, 156, 0.1);
        --vz-btn-active-border-color: transparent;
    }

    .btn-soft-custom-primary {
        --vz-btn-color: #2074BA;
        --vz-btn-bg: rgba(64, 81, 137, 0.1);
        --vz-btn-border-color: transparent;
        --vz-btn-hover-color: #2074BA;
        --vz-btn-hover-bg: rgba(64, 81, 137, 0.1);
        --vz-btn-hover-border-color: transparent;
        --vz-btn-focus-shadow-rgb: 64, 81, 137;
        --vz-btn-active-color: var(--vz-btn-hover-color);
        --vz-btn-active-bg: rgba(64, 81, 137, 0.1);
        --vz-btn-active-border-color: transparent;
    }

    table-red-right-border {
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-red-right-border>tbody>tr>td:last-child,
    .table-red-right-border>tbody>tr>th:last-child,
    .table-red-right-border>tfoot>tr>td:last-child,
    .table-red-right-border>tfoot>tr>th:last-child,
    .table-red-right-border>thead>tr>td:last-child,
    .table-red-right-border>thead>tr>th:last-child {
        border-right: 3px solid red !important;
    }

    table.dataTable thead tr th {
        word-wrap: break-word;
        /* word-break: break-all; */
    }

    table.dataTable tbody tr td {
        word-wrap: break-word;
        /* word-break: break-all;/ */
    }


    th,
    td {
        white-space: nowrap;
    }

    div.dataTables_wrapper {
        width: 800px;
        margin: 0 auto;
    }

    .phase-1 {
        width: 100px;
        min-width: 100px;
        max-width: 100px;
    }

    .px200 {
        width: 150px;
        min-width: 150px;
        max-width: 150px;
        word-wrap: break-word;
    }

    .DTFC_LeftBodyLiner {
        overflow-y: unset !important
    }

    .DTFC_RightBodyLiner {
        overflow-y: unset !important
    }
</style>
<?= $this->endSection() ?>
<?= $this->section("page_content") ?>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h5 class="mb-sm-0">Data Sheets</h5>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active">Mands</li>
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
                                <?= view('ClientDataSheet/_tabs', ['tab' => 'mandsTab']) ?>
                            </div>
                            <div class="card-body border-bottom-dashed border-bottom">
                                <div id="client_mands_area">
                                    <table class="table table-bordered nowrap fixed-columns-table" style="width: 100%;" id="mands_dataTable">
                                        <thead>
                                            <tr>
                                                <th class="dt-nowrap">Date</th>
                                                <th>Total Mands</th>
                                                <th>Total Peer Mands</th>
                                                <th>Total Eye Contact Mands</th>
                                                <th>Duration</th>
                                                <th>Frequency/M</th>
                                                <th>Variety</th>
                                                <th>Total FPP</th>
                                                <th>Total PPP</th>
                                                <th>Total GP</th>
                                                <th>Total V</th>
                                                <th>Total IV</th>
                                                <th>Total Item</th>
                                                <th>Total Mo</th>
                                                <th>Total TMO</th>
                                                <th>Total Mand Errors</th>
                                                <th>Total S</th>
                                                <th>Total R</th>
                                                <th>Total IA</th>
                                                <th>% S</th>
                                                <th>% R</th>
                                                <th>% IA</th>
                                                <th>Total Initial attempts</th>
                                                <th>% SS</th>
                                                <th>% WA</th>
                                                <th>% IW</th>
                                                <th>% AF</th>
                                                <th>Total Prompt Delay Trials</th>
                                                <th>% No Change</th>
                                                <th>% Improved</th>
                                                <th>% Deteriorated</th>
                                                <th>Total Echoic Trials</th>
                                                <th>% No Change</th>
                                                <th>% Improved</th>
                                                <th>% Deteriorated</th>
                                                <th>Mands Daily Data</th>


                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($mandsSummaryData as $row) : ?>
                                                <tr>
                                                    <td class="dt-nowrap"><?= app_date($row->session_date); ?> </td>
                                                    <td><?= $row->total_mands; ?> </td>
                                                    <td><?= $row->total_peer_mands; ?> </td>
                                                    <td><?= $row->total_eye_contact_mands; ?> </td>
                                                    <td><?= $row->total_duration !='0.00' ? convertDecimalToTime($row->total_duration) : '' ; ?> </td>
                                                    <td><?= $row->frequency_of_mands_per_minute ?> </td>
                                                    <td><?= $row->variety_of_mands; ?> </td>
                                                    <td><?= $row->total_FPP_mands; ?> </td>
                                                    <td><?= $row->total_PPP_mands; ?> </td>
                                                    <td><?= $row->total_GP_mands; ?> </td>
                                                    <td><?= $row->total_V_mands; ?> </td>
                                                    <td><?= $row->total_IV_mands; ?> </td>
                                                    <td><?= $row->total_Item_mands; ?> </td>
                                                    <td><?= $row->total_MO_mands; ?> </td>
                                                    <td><?= $row->total_TMO_mands; ?> </td>
                                                    <td><?= $row->total_mands_with_errors; ?> </td>
                                                    <td><?= $row->total_mands_errors_s; ?> </td>
                                                    <td><?= $row->total_mands_errors_r; ?> </td>
                                                    <td><?= $row->total_mands_errors_ia; ?> </td>
                                                    <td><?= $row->percentage_of_scrolled_mands; ?> </td>
                                                    <td><?= $row->percentage_of_repetitive_mands; ?> </td>
                                                    <td><?= $row->percentage_of_inappropriate_autoclitics; ?> </td>
                                                    <td><?= $row->total_mands_with_initial_attempts; ?> </td>
                                                    <td><?= $row->percentage_of_SS_attempts; ?> </td>
                                                    <td><?= $row->percentage_of_WA_attempts; ?> </td>
                                                    <td><?= $row->percentage_of_IW_attempts; ?> </td>
                                                    <td><?= $row->percentage_of_AF_attempts; ?> </td>
                                                    <td><?= $row->total_trials_with_prompt_delay; ?> </td>
                                                    <td><?= $row->percentage_of_remained_with_prompt_delay; ?> </td>
                                                    <td><?= $row->percentage_of_improved_with_prompt_delay; ?> </td>
                                                    <td><?= $row->percentage_of_worsened_with_prompt_delay; ?> </td>
                                                    <td><?= $row->total_trials_with_echoic_trials; ?> </td>
                                                    <td><?= $row->percentage_of_remained_with_echoic_trials; ?> </td>
                                                    <td><?= $row->percentage_of_improved_with_echoic_trials; ?> </td>
                                                    <td><?= $row->percentage_of_worsened_with_echoic_trials; ?> </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-outline-primary btn-icon waves-effect waves-light material-shadow-none view_mands_session_data" data-client-id="<?= $row->client_id; ?> " data-session-date="<?= $row->session_date; ?> "><i class="ri-eye-fill"></i></button>
                                                    </td>
                                                </tr>

                                            <?php endforeach; ?>
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

<div class="offcanvas offcanvas-end" data-bs-backdrop="false" tabindex="-1" id="mandsOffcanvasRight" aria-labelledby="mandsOffcanvasRight">
    <div class="offcanvas-header border-bottom  bg-info-subtle">
        <h5 class="offcanvas-title" id="offcanvasScrollingLabel">Mands Daily Data</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body " id='mands_daily_data'>
        ...
    </div>
    <div class="offcanvas-footer border p-3 text-center bg-dark-subtle">
        <a href="javascript:void(0);" class="link-primary" data-bs-dismiss="offcanvas">Back to Mands data sheet <i class="ri-arrow-right-s-line align-middle ms-1"></i></a>
    </div>
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
        var MandsTable = $('#mands_dataTable').DataTable({

            fixedColumns: {
                start: 1,
                end: 1
            },
            scrollCollapse: true,
            scrollX: true,
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
        /***************************************************************************** */
        var mandsOffcanvasRight = document.getElementById('mandsOffcanvasRight')
        var mbsc = new bootstrap.Offcanvas(mandsOffcanvasRight)

        $('#mandsOffcanvasRight').on('hidden.bs.offcanvas', function() {
            // Trigger a custom event when the offcanvas is hidden
            $('#mands_daily_data').html('');
        });
        $('#client_mands_area').on('click', '.view_mands_session_data', function(e) {
            console.log('view_mands_session_data');
            var view_mands_btn = $(this);
            var client_id = $(this).data('client-id');
            var session_date = $(this).data('session-date');


            var ajaxRequest = $.ajax({
                type: 'POST',
                url: '<?= site_url('/dataSheet/mandsDailyData') ?>',
                data: {
                    client_id: client_id,
                    session_date: session_date
                },
                dataType: 'html',
                beforeSend: function(xhr) {
                    view_mands_btn.prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                // Update program list content
                $('#mands_daily_data').html(response);
                mbsc.show()
            });

            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                swalAert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
            ajaxRequest.always(function() {
                view_mands_btn.prop("disabled", false);
            });


        });

    });
</script>
<?= $this->endSection() ?>
