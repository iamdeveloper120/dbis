<?= $this->extend("layout/master") ?>
<?= $this->section("page_content") ?>
<style>
    .custom-row {
        background-color: #f9f9f9;
    }
</style>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h5 class="mb-sm-0">Client Management</h5>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active">List</li>
        </ol>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-bottom-dashed">
                <div class="row g-4 align-items-center">
                    <div class="col-sm">
                        <div>
                            <h5 class="card-title mb-0">Add, Edit, Active or De-activate Client</h5>
                        </div>
                    </div>
                    <div class="col-sm-auto">
                        <div class="d-flex flex-wrap align-items-start gap-2">
                            <?php if (auth()->user()->can('clients.create')) : ?>
                                <button type="button" class="btn btn-primary" id="add_new_client"><i class="ri-add-line align-bottom me-1"></i> Add New Client</button>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body border-bottom-dashed border-bottom">
                <div class="table-responsive">
                    <table id="client_datatable" class="table table-bordered align-middle" style="width:100%">
                        <thead>
                            <tr>
                                <!--<th>id</th>-->
                                <th>Client No.</th>
                                <th>Name</th>
                                <th>List of Programs</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th style="width:170px">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
        <!--end col-->
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section("page_modal") ?>
<div class="modal fade" id="addNewClientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="addNewClientModalTitle">Add new Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
            </div>
            <form action="#" method="post" autocomplete="off">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="internal_mrn" class="form-label">Client No.</label>
                        <input type="text" class="form-control" name="internal_mrn" id="internal_mrn" placeholder="">
                    </div>
                    <div class="mb-3">
                        <label for="fname" class="form-label">First Name</label>
                        <input type="text" class="form-control" name="fname" id="fname" placeholder="">
                    </div>
                    <div class="mb-3">
                        <label for="lname" class="form-label">Last Name</label>
                        <input type="text" class="form-control" name="lname" id="lname" placeholder="">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">List of Programs</label>
                        <input type="text" class="form-control" name="description" id="description" placeholder="">
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="ri-close-line align-bottom me-1"></i>Close</button>
                        <button type="button" class="btn btn-primary" id="btn_add_new_client"><i class="ri-save-line align-bottom me-1"></i>Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="updateClientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="updateClientModalTitle">Update Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
            </div>
            <form action="#" method="post" autocomplete="off">
                <div class="modal-body">
                    <input type="text" class="form-control" hidden="hidden" name="update_id" id="update_id">
                    <div class="mb-3">
                        <label for="internal_mrn" class="form-label">Client No.</label>
                        <input type="text" class="form-control" name="update_internal_mrn" id="update_internal_mrn" placeholder="">
                    </div>
                    <div class="mb-3">
                        <label for="update_fname" class="form-label">First Name</label>
                        <input type="text" class="form-control" name="update_fname" id="update_fname" placeholder="">
                    </div>
                    <div class="mb-3">
                        <label for="update_lname" class="form-label">Last Name</label>
                        <input type="text" class="form-control" name="update_lname" id="update_lname" placeholder="">
                    </div>
                    <div class="mb-3">
                        <label for="update_description" class="form-label">List of Programs</label>
                        <input type="text" class="form-control" name="update_description" id="update_description" placeholder="">
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="ri-close-line align-bottom me-1"></i>Close</button>
                        <button type="button" class="btn btn-primary" id="btn_update_client"><i class="ri-save-line align-bottom me-1"></i>Update</button>
                    </div>
                </div>
            </form>

        </div>
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

        table = $('#client_datatable').DataTable({
            data: dataSet,
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


            order: [
                [1, 'asc'],
                [0, 'asc']
            ],
            columnDefs: [{
                    targets: [0, 1, 3, 4, 5],
                    className: 'dt-nowrap'
                } // Apply nowrap to specified columns
            ]
        });
        /***************************************************************************************** */
        $('#add_new_client').on('click', function(e) {
            e.preventDefault;
            $('#addNewClientModal').modal('show');
        });
        /***************************************************************************************** */
        $('#addNewClientModal').on('hidden.bs.modal', function(e) {
            $('#fname').val('');
            $('#lname').val('');
            $('#internal_mrn').val('');
            $('#description').val('');
        });
        /***************************************************************************************** */

        $('#btn_add_new_client').on('click', function() {
            var data = {
                'fname': $('#fname').val(),
                'lname': $('#lname').val(),
                'internal_mrn': $('#internal_mrn').val(),
                'description': $('#description').val()
            };
            var ajaxRequest = $.ajax({
                url: '<?php echo base_url() ?>/clients/new',
                type: 'post',
                data: data,
                beforeSend: function(xhr) {
                    $('#btn_add_new_client').prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {

                    addTableRow(response.data);

                    $('#addNewClientModal').modal('hide');
                    showAlert(response.statusText, response.message, response.status);

                } else {
                    showAlert(response.statusText, response.message, response.status);

                }
            });
            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
            ajaxRequest.always(function() {
                $('#btn_add_new_client').prop("disabled", false);
            });
        });
        /***************************************************************************************** */
        $("#client_datatable").on('click', '.status-change', function(e) {

            var client_id = $(this).attr('id');
            var current_status = $(this).attr('status');
            var mrn = $(this).attr('mrn');
            var url = '/';
            current_row = $(this).parents('tr');
            if (current_row.hasClass('child')) {
                current_row = current_row.prev();
            }
            var text = '';
            var title = '';
            var status = 0;
            if (current_status == 0) {
                text = 'Activating Client ' + mrn;
                title = "Are you sure?";
                status = 1;
                url = '/clients/activate';
            } else {
                text = 'Warning: system will deactivate Client ' + mrn + '.  All users assigned to the client will be de-linked. The client can be activated in the future, but users will need to be reassigned to this client. Daily and weekly data will be retained.';
                title = "Are you sure?";
                status = 0;
                url = '/clients/deactivate';
            }
            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Save',
                customClass: {
                    confirmButton: 'btn btn-primary w-xs me-2 mt-2',
                    cancelButton: 'btn btn-danger w-xs me-2 mt-2',
                },

                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var ajaxRequest = $.ajax({
                        url: url,
                        type: 'post',
                        data: {
                            "id": client_id,
                            'status': status
                        },
                        beforeSend: function(xhr) {

                        }
                    });
                    ajaxRequest.done(function(response) {
                        if (response.status == 'success') {
                            updateTableRow(response.data);
                            showAlert(response.statusText, response.message, response.status);
                        } else {
                            showAlert(response.statusText, response.message, response.status);
                        }
                    });
                    ajaxRequest.fail(function(jqXHR, textStatus, error) {
                        showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
                    });
                    ajaxRequest.always(function() {

                    });
                }
            });

        });
        /***************************************************************************************** */
        $("#client_datatable").on('click', '.delete', function(e) {
            var btn = $(this);
            var client_id = $(this).attr('id');

            current_row = $(this).parents('tr');
            if (current_row.hasClass('child')) {
                current_row = current_row.prev();
            }

            Swal.fire({
                title: "Are you sure?",
                text: "Once deleted, you will not be able to recover this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                confirmButtonClass: 'btn btn-primary w-xs me-2 mt-2',
                cancelButtonClass: 'btn btn-danger w-xs me-2 mt-2',
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var ajaxRequest = $.ajax({
                        url: '<?php echo base_url() ?>/clients/delete',
                        type: 'post',
                        data: {
                            "id": client_id
                        },
                        beforeSend: function(xhr) {
                            btn.prop("disabled", true);
                        }
                    });
                    ajaxRequest.done(function(response) {
                        if (response.status == 'success') {
                            table.row(current_row).remove().draw(false);
                            showAlert(response.statusText, response.message, response.status);
                        } else {
                            showAlert(response.statusText, response.message, response.status);
                        }
                    });
                    ajaxRequest.fail(function(jqXHR, textStatus, error) {
                        showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
                    });
                    ajaxRequest.always(function() {
                        btn.prop("disabled", false);
                    });

                } else {
                    current_row = '';
                }
            });

        });
        /***************************************************************************************** */
        // Redirect to full client information page instead of opening modal
        $("#client_datatable").on('click', '.update-client', function(e) {
            e.preventDefault();

            var btn = $(this);
            var id = btn.attr('id');

            // Optional: show loading spinner or disable temporarily
            btn.prop("disabled", true);

            // Directly redirect to detail view page
            window.location.href = '<?= base_url() ?>clients/single/' + id;
        });
        /***************************************************************************************** */
 
        /***************************************************************************************** */
        $('#updateClientModal').on('hidden.bs.modal', function(e) {
            $('#updateClientModalTitle').html('Update Client');
            $('#update_id').val('');
            $('#update_internal_mrn').val('');
            $('#update_fname').val('');
            $('#update_lname').val('');
            $('#update_description').val('');
        });
        /***************************************************************************************** */
        $('#btn_update_client').on('click', function() {
            var btn = $(this);
            var data = {
                'id': $('#update_id').val(),
                'fname': $('#update_fname').val(),
                'lname': $('#update_lname').val(),
                'internal_mrn': $('#update_internal_mrn').val(),
                'description': $('#update_description').val()
            };
            var ajaxRequest = $.ajax({
                url: '<?php echo base_url() ?>/clients/update',
                type: 'post',
                data: data,
                beforeSend: function(xhr) {
                    btn.prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    updateTableRow(response.data);
                    $('#updateClientModal').modal('hide');
                    showAlert(response.statusText, response.message, response.status);
                } else {
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
        /***************************************************************************************** */

    }); // End of document ready
</script>
<?= $this->endSection() ?>
