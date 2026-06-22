<?= $this->extend("layout/master") ?>
<?= $this->section("page_content") ?>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h5 class="mb-sm-0">View/Configure client program settings</h5>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active">Clients</li>
        </ol>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">

        <div class="card">

            <div class="card-body border-bottom-dashed border-bottom">
                <div class="table-responsive">
                    <table id="client_datatable" class="table table-bordered nowrap align-middle" style="width:100%">
                        <thead>
                            <tr>
                                <th>MRN</th>
                                <th>Name</th>
                                <th style="width:100px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clients as $client) : ?>
                                <tr>
                                    <td><?= $client->internal_mrn; ?></td>
                                    <td><?= $client->first_name . ' ' . $client->last_name; ?></td>
                                    <td style="width:100px">                                         
                                        <a href="<?= base_url() . 'client-program/' . encodeValue($client->id) . '/domains'; ?>" type="button" class="btn btn-sm btn-info btn-label waves-effect waves-light"><i class="ri-settings-4-line label-icon align-middle fs-16 me-2"></i>Program Settings</a>
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
                        placeholder: 'Search',

                    },

                }
            },
            columnDefs: [{
                    width: '10%',
                    targets: 0
                }, // Domain Code
                {
                    width: '80%',
                    targets: 1
                }, // Domain Name
                {
                    width: '10%',
                    targets: 2,
                    className: 'dt-nowrap'
                },
            ]

        });
        /**************************************************************************************** */



    }); // End of document ready
</script>
<?= $this->endSection() ?>