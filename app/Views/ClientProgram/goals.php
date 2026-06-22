<?= $this->extend("layout/master") ?>
<?= $this->section("head_tag") ?>
<style>

</style>
<?= $this->endSection() ?>
<?= $this->section("page_content") ?>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
<h5 class="mb-sm-0"><span class="text-info"><?php echo $client->internal_mrn; ?></span> - Client Program </h5>
    <div class="page-title-right">
    <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="<?= base_url() ?>client-program">Clients</a></li>
            <li class="breadcrumb-item active">Goals</li>
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
                            <div class="card-header border-bottom-dashed pb-0 mb-0">

                                <?= view('ClientProgram/_tabs', ['tab' => 'goals', 'client_id' => $encodedClientId]) ?>
                            </div>
                            <div class="card-body border-bottom-dashed border-bottom">
                                <div class="row pb-2">
                                    <div class="col-md-12">
                                        <select class="form-control " id="domains_dropdown_list">
                                            <option value="">SELECT Domain</option>
                                            <?php $object = json_decode(json_encode($domains), FALSE);
                                            foreach ($object as $domain) {  ?>
                                                <option value="<?php echo $domain->id; ?>">
                                                    <?php echo $domain->name . ' ( ' . $domain->domain_code . ' )  '; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>

                                </div>
                            </div>
                            <div class="card-body border-bottom-dashed border-bottom">

                                <div class="table-responsive">
                                    <table id="dataTable" class="table table-bordered align-middle" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Domain Code</th>
                                                <th>Goal Code</th>
                                                <th>Goal Name</th>
                                                <th>Description</th>
                                                <th style="width:170px">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody> </tbody>
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
<div class="modal fade" id="add_modal" tabindex="-1" aria-hidden="true">>
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="add_modal_title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> </button>
            </div>
            <form action="#" method="post" autocomplete="off">
                <div class="modal-body">
                    <div class="row">
                        <input type="text" class="form-control " hidden="hidden" name="domain_id" id="domain_id">
                        <input type="text" class="form-control " hidden="hidden" name="client_id" id="client_id" value="<?= $client->id ?>">
                    </div>
                    <div class="row">
                        <div class="mb-3">
                            <label class="form-label" for="target_state">Code *</label>
                            <input type="text" class="form-control " name="goal_code" id="goal_code">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="target_state">Name *</label>
                            <input type="text" class="form-control " name="name" id="name">
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="description">Description</label>
                            <input type="text" class="form-control " name="description" id="description">
                        </div>

                    </div>


                </div>
                <div class="modal-footer">
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="ri-close-line align-bottom me-1"></i>Close</button>
                        <button type="button" class="btn btn-primary" id="btn_add"><i class="ri-save-line align-bottom me-1"></i>Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="update_modal" tabindex="-1" aria-hidden="true">>
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="update_modal_title"> </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <input type="text" class="form-control " hidden="hidden" name="id" id="id">
                    <input type="text" class="form-control " hidden="hidden" name="domain_id" id="domain_id">
                    <input type="text" class="form-control " hidden="hidden" name="client_id" id="client_id" value="<?= $client->id ?>">
                </div>
                <div class="row">
                    <div class="mb-3">
                        <label class="form-label" for="target_state">Code *</label>
                        <input type="text" class="form-control " name="goal_code" id="goal_code">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="target_state">Name *</label>
                        <input type="text" class="form-control " name="name" id="name">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="description">Description</label>
                        <input type="text" class="form-control " name="description" id="description">
                    </div>
                </div>



            </div>
            <div class="modal-footer">
                <div class="hstack gap-2 justify-content-end">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="ri-close-line align-bottom me-1"></i>Close</button>
                    <button type="button" class="btn btn-primary" id="btn_update"><i class="ri-save-line align-bottom me-1"></i>Save</button>
                </div>
            </div>
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

        $('#domains_dropdown_list').select2();
        /***************************************************************************************** */

        table = $('#dataTable').DataTable({
            lengthChange: false,
            lengthMenu: [
                [10, 25, 50, -1],
                ['10 rows', '25 rows', '50 rows', 'Show all']
            ],
            layout: {
                topStart: {
                    buttons: [{
                            text: '<i class="ri-add-line align-bottom me-1"></i>New Goal',
                            className: 'btn btn-soft-info waves-effect waves-light material-shadow-none',
                            attr: {
                                id: 'add_goals'
                            },
                            action: function(e, dt, node, config) {
                                // Add your action for the button here
                                showAddModal();
                            }
                        },
                        {
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
            columns: [{
                    data: 'domain_code',
                    width: '10%',
                }, {
                    data: 'goal_code',
                    width: '10%',
                },
                {
                    data: 'name',
                    width: '50%',
                },
                {
                    data: 'description',
                    width: '20%',
                },
                {
                    data: null,
                    width: '10%',
                    render: function(data, type, row) {
                        return '<button id="' + row.id + '"  type="button" class="btn btn-sm btn-outline-warning update"><i class="ri-edit-line align-bottom me-1"></i>Edit</button>&nbsp;' +
                            '<button id="' + row.id + '"  type="button" class="btn btn-sm btn-outline-danger delete"><i class="ri-delete-bin-line align-bottom me-1"></i>Delete</button>';


                    },
                    className: "dt-nowrap"
                }
            ]

        });

        // Function to populate DataTable with data
        function populateDataTable(data) {
            table.clear().rows.add(data).draw();
        }


        /****************************************************************************************  */
        $("#domains_dropdown_list").on('change', function(e) {
            e.preventDefault;
            let domain_id = $("#domains_dropdown_list").val();
            let client_id = <?= $client->id ?>;
             
            var ajaxRequest = $.ajax({
                url: '<?php echo base_url() ?>client-program/goals/list',
                type: 'post',
                data: {
                    "domain_id": domain_id,
                    "client_id":client_id
                },
                beforeSend: function(xhr) {

                    $("#domains_dropdown_list").prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    // Populate DataTable with the retrieved data
                    populateDataTable(response.data);
                } else {
                    showAlert(response.statusText, response.message, response.status);
                }
            });
            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
            ajaxRequest.always(function() {
                $('#domains_dropdown_list').prop("disabled", false);
            });

        }); //On change function ends


        /***************************************************************************************** */

        function showAddModal() {
            let domain_id = $("#domains_dropdown_list").val();

            if (domain_id == '') {
                showAlert('', 'Select Domain', 'error');
            } else {

                let domain_detail = $("#domains_dropdown_list :selected").html();
                $("#add_modal_title").html('Add Goal for Domain: ' + domain_detail);
                $("#add_modal #domain_id").val(domain_id);
                $('#add_modal').modal('show');
            }

        };

        /***************************************************************************************** */
        $('#add_modal').on('hidden.bs.modal', function(e) {
            $('#add_modal #add_modal_title').val('');
            $('#add_modal #goal_code').val('');
            $('#add_modal #name').val('');
            $('#add_modal #description').val('');
            $('#add_modal #domain_id').val('');
        });

        /***************************************************************************************** */
        $('#btn_add').on('click', function() {
            var btn = $(this);
            var data = {
                'goal_code': $('#add_modal #goal_code').val(),
                'name': $('#add_modal #name').val(),
                'description': $('#add_modal #description').val(),
                'domain_id': $('#add_modal #domain_id').val(),
                'client_id': $('#add_modal #client_id').val(),

            };
            var ajaxRequest = $.ajax({
                url: '<?php echo base_url() ?>client-program/goals/create',
                type: 'post',
                data: data,
                beforeSend: function(xhr) {
                    btn.prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {

                    addTableRow(response.data);
                    $('#add_modal').modal('hide');
                    showAlert(response.statusText, response.message, response.status);

                } else if (response.status == 'error' && response.statusText == 'Validation_Error') {
                    let errors = Object.values(response.validationErrors);
                    displayValidationErrors(errors);

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
        $("#dataTable").on('click', '.update', function(e) {
            var btn = $(this);
            var id = $(this).attr('id');
            let domain_detail = $("#domains_dropdown_list :selected").html();
            $("#update_modal_title").html('Update Goal for Domain: ' + domain_detail);
            current_row = $(this).parents('tr');
            if (current_row.hasClass('child')) {
                current_row = current_row.prev();
            }
            var ajaxRequest = $.ajax({
                url: '<?php echo base_url() ?>client-program/goals/single',
                type: 'post',
                data: {
                    "id": id,
                },
                beforeSend: function(xhr) {
                    btn.prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    row_data = response.data;

                    $('#update_modal #id').val(row_data.id);
                    $('#update_modal #goal_code').val(row_data.goal_code);
                    $('#update_modal #name').val(row_data.name);
                    $('#update_modal #description').val(row_data.description);
                    $('#update_modal #domain_id').val(row_data.domain_id);


                    $('#update_modal').modal('show');
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
        $('#update_modal').on('hidden.bs.modal', function(e) {
            $('#update_modal #update_modal_title').val('');
            $('#update_modal #domain_code').val('');
            $('#update_modal #name').val('');
            $('#update_modal #description').val('');
            $('#update_modal #domain_id').val('');
            $('#update_modal #id').val('');

        });
        /***************************************************************************************** */
        $('#btn_update').on('click', function() {
            var btn = $(this);
            var data = {
                'id': $('#update_modal #id').val(),
                'goal_code': $('#update_modal #goal_code').val(),
                'name': $('#update_modal #name').val(),
                'description': $('#update_modal #description').val(),
                'domain_id': $('#update_modal #domain_id').val(),
                'client_id': $('#update_modal #client_id').val(),

            };
            //console.log(data);
            var ajaxRequest = $.ajax({
                url: '<?php echo base_url() ?>client-program/goals/update',
                type: 'post',
                data: data,
                beforeSend: function(xhr) {
                    btn.prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    updateTableRow(response.data);
                    $('#update_modal').modal('hide');
                    showAlert(response.statusText, response.message, response.status);
                } else if (response.status == 'error' && response.statusText == 'Validation_Error') {
                    let errors = Object.values(response.validationErrors);
                    displayValidationErrors(errors);

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
        $("#dataTable").on('click', '.delete', function(e) {
            var id = $(this).attr('id');

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
                customClass: {
                    confirmButton: 'btn btn-primary w-xs me-2 mt-2',
                    cancelButton: 'btn btn-danger w-xs me-2 mt-2',
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var ajaxRequest = $.ajax({
                        url: '<?php echo base_url() ?>client-program/goals/delete',
                        type: 'post',
                        data: {
                            "id": id,
                        },
                        beforeSend: function(xhr) {

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

                    });


                } else {
                    current_row = ''
                }
            });

        });
    });
</script>
<?= $this->endSection() ?>