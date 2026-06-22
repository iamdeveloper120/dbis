<?= $this->extend("layout/master") ?>
<?= $this->section("page_content") ?>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h5 class="mb-sm-0">Select a client to proceed with the session</h5>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active">Session List</li>
        </ol>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <?php if (session('instructor') !== null) : ?>
            <!-- Danger Alert -->
            <div class="alert alert-danger alert-top-border alert-dismissible fade show" role="alert">
                <i class="ri-error-warning-line me-3 align-middle fs-16 text-danger"></i><?= session('instructor') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif ?>
        <?php if (session('supervisor') !== null) : ?>
            <!-- Danger Alert -->
            <div class="alert alert-danger alert-top-border alert-dismissible fade show" role="alert">
                <i class="ri-error-warning-line me-3 align-middle fs-16 text-danger"></i><?= session('supervisor') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif ?>
        <?php if (session('error') !== null) : ?>
            <!-- Danger Alert -->
            <div class="alert alert-danger alert-top-border alert-dismissible fade show" role="alert">
                <i class="ri-error-warning-line me-3 align-middle fs-16 text-danger"></i><?= session('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif ?>
        <div class="card">
            <div class="card-body border-bottom-dashed border-bottom">
                <div class="table-responsive">
                    <table id="client_datatable" class="table table-bordered nowrap align-middle" style="width:100%">
                        <thead>
                            <tr>
                                <th>Client No.</th>
                                <th>Name</th>
                                <th style="width:110px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clients as $client) : ?>
                                <tr>
                                    <td><?= $client->internal_mrn; ?></td>
                                    <td><?= $client->first_name . ' ' . $client->last_name; ?></td>
                                    <td style="width:110px">
                                        <a href="<?= base_url() . 'sessions/live/client/' . encodeValue($client->id); ?>" type="button" class="btn btn-sm btn-light btn-label waves-effect waves-light"><i class="ri-user-smile-line label-icon align-middle fs-16 me-2"></i>Take Session</a>

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


        table = $('#client_datatable').DataTable({
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
                        placeholder: 'Search'
                    }
                }
            },
            columnDefs: [{
                    targets: 0,
                    width: '15%'
                }, // MRN
                {
                    targets: 1,
                    visible: false
                }, // Name column is hidden
                {
                    targets: 2,
                    width: '110px',
                    orderable: false
                } // Action column fixed width
            ]
        });

        /***************************************************************************************** */


    }); // End of document ready
</script>
<?= $this->endSection() ?>