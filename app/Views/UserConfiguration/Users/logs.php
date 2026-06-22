<?= $this->extend("layout/master") ?>
<?= $this->section("page_content") ?>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h5 class="mb-sm-0">Staff Members Sign In Logs</h5>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active">Logs</li>
        </ol>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="card">

            <div class="card-body border-bottom-dashed border-bottom">
                <div class="table-responsive">
                    <table id="logs" class="table table-bordered nowrap align-middle" style="width:100%">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Date</th>
                                <th>IP Address</th>
                                <th>User Agent</th>
                                <th>Success?</th>
                            </tr>
                        </thead>
                        <?php if (isset($allLogins) && count($allLogins)) : ?>
                            <tbody>
                                <?php foreach ($allLogins as $login) : ?>
                                    <tr>
                                        <td><?= $login->identifier ?? '' ?></td>
                                        <td><?= app_date($login->date, true, false) ?></td>
                                        <td><?= $login->ip_address ?? '' ?></td>
                                        <td><?= $login->user_agent ?? '' ?></td>
                                        <td>
                                            <?php if ($login->success) : ?>
                                                <span class="badge rounded-pill bg-success">Success</span>
                                            <?php else : ?>
                                                <span class="badge rounded-pill bg-secondary">Failed</span>
                                            <?php endif ?>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        <?php else : ?>
                            <div class="alert alert-secondary">No recent login attempts.</div>
                        <?php endif ?>
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
        var dateandtimeformat = momentDateFormat + ' HH:mm:ss';
        table = $('#logs').DataTable({
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
            order: [
                [0, 'asc'],
                [1, 'desc'],
            ],
            columnDefs: [{
                targets: [1],
                render: function(data, type, row) {
                    if (type === 'sort' || type === 'type') {
                        // Return the original date format as a sortable value
                        return moment(data).format('YYYYMMDDHHmmss');
                    }
                    // Return the formatted date for display
                    return data;
                }
            }]
        });

    }); // End of document ready
</script>
<?= $this->endSection() ?>