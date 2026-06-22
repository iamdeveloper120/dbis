<?= $this->extend("layout/master") ?>
<?= $this->section("page_content") ?>
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-bottom-dashed">

                <div class="row g-4 align-items-center">
                    <div class="col-sm">
                        <div>
                            <h5 class="card-title mb-0">Edit Staff (<?= $user->name() ?>)</h5>
                        </div>
                    </div>
                    <div class="col-sm-auto">
                        <div class="d-flex flex-wrap align-items-start gap-2">
                            <a href="/users/list" type="button" class="btn btn-primary"><i class="ri-arrow-go-back-fill align-bottom me-1"></i> Staff Member List</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body border-bottom-dashed border-bottom">


                <?= view('admin/users/_tabs', ['tab' => 'clients_permissions', 'user' => $user]) ?>
                <div class="tab-content text-muted">
                    <div class="tab-pane active show" role="tabpanel">
                        <form action="#" method="post">
                            <?= csrf_field() ?>

                            <fieldset>
                                <legend>Client Permissions</legend>

                                <p>These permissions are applied to staff members</p>



                                <div class="table-responsive">
                                    <table class="table table-bordered" id="client_permission_datatable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>id</th>
                                                <th>MRN</th>
                                                <th>Client#</th>
                                                <th>Name</th>
                                                <th>Status</th>
                                                <th style="width:20px">Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </fieldset>


                        </form>
                    </div>
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
        var dataSet = <?php echo json_encode($clients) ?>;
        var current_row = '';

        var table = $('#client_permission_datatable').DataTable({
            response: true,
            data: dataSet,
            lengthChange: true,
            lengthMenu: [
                [10, 25, 50, -1],
                ['10 rows', '25 rows', '50 rows', 'Show all']
            ],

        });

        $("#client_permission_datatable").on('click', '.client-permission', function(e) {
            var btn = $(this);
            var client_id = $(this).attr('client_id');
            var u_id = $(this).attr('u_id');
            var permission = 0;
            var current_row = $(this).parents('tr');
            if ($('#customCheck-' + client_id).is(":checked")) {
                permission = 1;
                 
            } else {
                console.log("unchecked");
            }


            var ajaxRequest = $.ajax({
                url: '/clients/permission',
                type: 'post',
                data: {
                    "user_id": u_id,
                    'client_id': client_id,
                    "permission": permission
                },
                beforeSend: function(xhr) {
                    btn.prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    showAlert(response.statusText, response.message, response.status);
                } else {
                    if (permission === 1) {
                        $('#customCheck-' + client_id).prop("checked", false);
                    } else {
                        $('#customCheck-' + client_id).prop("checked", true);
                    }
                    showAlert(response.statusText, response.message, response.status);
                }
            });
            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
            ajaxRequest.always(function() {
                btn.prop("disabled", false);
            });

        });
    });
    /*let inputs = document.getElementsByClassName('in-group');
    Array.prototype.forEach.call(inputs, function(el, i) {
        el.indeterminate = true;
    });*/
</script>
<?= $this->endSection() ?>