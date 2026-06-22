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
</style>
<?= $this->endSection() ?>
<?= $this->section("page_content") ?>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h5 class="mb-sm-0"><span class="text-info"><?php echo $client->internal_mrn; ?></span> - Client Program </h5>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="<?= base_url() ?>client-program">Clients</a></li>
            <li class="breadcrumb-item active">Targets</li>
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

                                <?= view('ClientProgram/_tabs', ['tab' => 'targets', 'client_id' => $encodedClientId]) ?>
                            </div>
                            <div class="card-body border-bottom-dashed border-bottom pb-0">
                                <div class="row pb-2">
                                    <div class="col-md-6">
                                        <select class="form-control " id="domains_dropdown_list">
                                            <option value="">SELECT Domain</option>
                                            <?php $object = json_decode(json_encode($domains), FALSE);
                                            foreach ($object as $domain) {  ?>
                                                <option value="<?php echo $domain->id; ?>">
                                                    <?php echo $domain->name . ' ( ' . $domain->domain_code . ' )  '; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <select class="form-control " id="goals_dropdown_list">
                                            <option value="">SELECT Goal</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div id="probe_set_configuration"></div>
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
                                                <th>Target Name</th>
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
<!-- right offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="offcanvasTitle"> </h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body " id='probe_set_and_rules_area'> </div>
    <div class="offcanvas-footer border p-3 text-center">
        <a href="javascript:void(0);" class="btn btn-primary" data-bs-dismiss="offcanvas"><i class="ri-arrow-left-s-line align-middle ms-1"></i> Back to target list</a>
    </div>
</div>


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
                        <input type="text" class="form-control " hidden="hidden" name="goal_id" id="goal_id">
                        <input type="text" class="form-control " hidden="hidden" name="client_id" id="client_id" value="<?= $client->id ?>">
                    </div>
                    <div class="row">

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
                    <input type="text" class="form-control " hidden="hidden" name="goal_id" id="goal_id">
                    <input type="text" class="form-control " hidden="hidden" name="client_id" id="client_id" value="<?= $client->id ?>">
                </div>
                <div class="row">
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
        var offcanvasRight = document.getElementById('offcanvasRight')
        var offcanvas = new bootstrap.Offcanvas(offcanvasRight)

        /***************************************************************************************** */
        var csrfToken = "<?= csrf_hash() ?>";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        });

        $('select').select2();
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
                            text: '<i class="ri-add-line align-bottom me-1"></i>New Targets',
                            className: 'btn btn-soft-info waves-effect waves-light material-shadow-none',
                            attr: {
                                id: 'add_targets'
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
                    "client_id": client_id
                },
                beforeSend: function(xhr) {

                    $('#domains_dropdown_list').prop("disabled", true);
                    $("#goals_dropdown_list").prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {

                    // Clear the current options in the goals dropdown
                    $('#goals_dropdown_list').empty();

                    // Add a default option
                    $('#goals_dropdown_list').append('<option value="">SELECT Goal</option>');

                    // Iterate through the retrieved data and append options to the goals dropdown
                    for (var i = 0; i < response.data.length; i++) {
                        var goal = response.data[i];
                        $('#goals_dropdown_list').append('<option value="' + goal.id + '">' + goal.goal_code + ' - ' + goal.name + '</option>');
                    }

                    // Trigger the change event to update Select2
                    $('#goals_dropdown_list').trigger('change');
                } else {
                    showAlert(response.statusText, response.message, response.status);
                }
            });
            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
            ajaxRequest.always(function() {
                $('#domains_dropdown_list').prop("disabled", false);
                $("#goals_dropdown_list").prop("disabled", false);
            });

        }); //On change function ends
        /****************************************************************************************  */
        $("#goals_dropdown_list").on('change', function(e) {
            e.preventDefault;
            let goal_id = $("#goals_dropdown_list").val();
            let client_id = <?= $client->id ?>;
            var ajaxRequest = $.ajax({
                url: '<?php echo base_url() ?>client-program/targets/list',
                type: 'post',
                data: {
                    "goal_id": goal_id,
                    "client_id": client_id,
                },
                beforeSend: function(xhr) {
                    $('#domains_dropdown_list').prop("disabled", true);
                    $("#goals_dropdown_list").prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
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
                $("#goals_dropdown_list").prop("disabled", false);
            });

            // New AJAX request to check probe set configuration
            if (goal_id != '') {
                $.ajax({
                    url: '<?php echo base_url() ?>client-program/goal/check-goal-probe-sets',
                    type: 'post',
                    data: {
                        "goal_id": goal_id,
                        "client_id": client_id
                    },
                    success: function(response) {
                        showLinkedProbes(response);
                    },
                    error: function(jqXHR, textStatus, error) {
                        showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
                    }
                });
            } else {
                $('#probe_set_configuration').html('');
            }



        }); //On change function ends

        function showLinkedProbes(response) {
            let messageIcon, borderColorClass, buttonHtml;

            // Set the icon, border color, and buttons based on the response status
            if (response.status === 'success') {
                messageIcon = '<i class="ri-user-smile-line me-3 align-middle fs-16 text-primary"></i>';
                borderColorClass = 'border border-primary';
                buttonHtml = `
                                <button type="button" class="btn btn-outline-warning" id="manage_rules_btn">
                                    <i class="ri-edit-line align-bottom me-1"></i> Manage Existing Rules
                                </button>
                                <button type="button" class="btn btn-outline-primary" id="add_new_probe_set_btn">
                                    <i class="ri-add-line align-bottom me-1"></i> Attach New Probe Set and Rules
                                </button>`;
            } else if (response.status === 'no_config') {
                messageIcon = '<i class="ri-error-warning-line me-3 align-middle fs-16 text-warning"></i>';
                borderColorClass = 'border border-warning';
                buttonHtml = `
                                <button type="button" class="btn btn-outline-primary" id="add_new_probe_set_btn">
                                    <i class="ri-add-line align-bottom me-1"></i> Attach New Probe Set and Rules
                                </button>`;
            }

            // Render the HTML
            $('#probe_set_configuration').html(`
                    <div class="${borderColorClass} p-3 mb-3 rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                ${messageIcon} ${response.message}
                            </div>
                            <div class="d-flex gap-1">
                                ${buttonHtml}
                            </div>
                        </div>
                    </div>
                `);
        }


        /*function showLinkedProbes(response) {
            if (response.status == 'success') {
                $('#probe_set_configuration').html(`
                           <div class="card-header border-0" style="padding: 10px 0 0 0; margin-bottom: 0px;">
                                            <div class="row align-items-center gy-3">
                                                <div class="col-sm">
                                                 
                                                    <div class="alert alert-primary alert-top-border" role="alert">
                                                        <i class="ri-user-smile-line me-3 align-middle fs-16 text-primary"></i> ${response.message} 
                                                    </div>
     
                                                </div>
                                                <div class="col-sm-auto">
                                                    <div class="d-flex gap-1 flex-wrap">
                                                        <button type="button" class="btn  btn-outline-warning" id="manage_rules_btn"><i class="ri-edit-line align-bottom me-1"></i> Manage Existing Rules</button>
                                                        <button type="button" class="btn  btn-outline-primary" id="add_new_probe_set_btn"><i class="ri-add-line align-bottom me-1"></i> Attach New Probe Set and Rules</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                        `);
            } else if (response.status == 'no_config') {
                $('#probe_set_configuration').html(`
                                <div class="card-header border-0" style="padding: 10px 0 0 0; margin-bottom: 0px;">
                                    <div class="row align-items-center gy-3">
                                        <div class="col-sm">
                                                <div class="alert alert-warning alert-top-border" role="alert">
                                                        <i class="ri-error-warning-line me-3 align-middle fs-16 text-warning"></i> ${response.message} 
                                                    </div>
     
                                        </div>
                                        <div class="col-sm-auto">
                                            <div class="d-flex gap-1 flex-wrap">
                                                <button type="button" class="btn  btn-outline-primary" id="add_new_probe_set_btn"><i class="ri-add-line align-bottom me-1"></i> Attach New Probe Set and Rules</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `);
            }
        }*/

        $('#offcanvasRight').on('hidden.bs.offcanvas', function() {
            // Trigger a custom event when the offcanvas is hidden
            $('#probe_set_and_rules_area').html('');
            $('#offcanvasTitle').html('');

            let goal_id = $("#goals_dropdown_list").val();
            let client_id = <?= $client->id ?>;
            if (goal_id != '') {
                $.ajax({
                    url: '<?php echo base_url() ?>client-program/goal/check-goal-probe-sets',
                    type: 'post',
                    data: {
                        "goal_id": goal_id,
                        "client_id": client_id
                    },
                    success: function(response) {
                        showLinkedProbes(response);
                    },
                    error: function(jqXHR, textStatus, error) {
                        showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
                    }
                });
            } else {
                $('#probe_set_configuration').html('');
            }

        });
        /***************************************************************************************** */

        function showAddModal() {
            let domain_id = $("#domains_dropdown_list").val();
            let goal_id = $("#goals_dropdown_list").val();

            if (domain_id == '' || goal_id == '') {
                showAlert('', 'Select Domain and Goal', 'error');
            } else {

                let domain_detail = $("#domains_dropdown_list :selected").html();
                let goal_detail = $("#goals_dropdown_list :selected").html();
                $("#add_modal_title").html('Add Target for <br> Domain: ' + domain_detail + '<br> Goal: ' + goal_detail + '');

                $("#add_modal #goal_id").val(goal_id);

                $('#add_modal').modal('show');
            }
        };


        /***************************************************************************************** */
        $('#add_modal').on('hidden.bs.modal', function(e) {
            $('#add_modal #add_modal_title').val('');
            $('#add_modal #goal_id').val('');
            $('#add_modal #name').val('');
            $('#add_modal #description').val('');
        });

        /***************************************************************************************** */
        $('#btn_add').on('click', function() {
            var btn = $(this);
            var data = {
                'goal_id': $('#add_modal #goal_id').val(),
                'client_id': $('#add_modal #client_id').val(),
                'name': $('#add_modal #name').val(),
                'description': $('#add_modal #description').val(),
            };
            var ajaxRequest = $.ajax({
                url: '<?php echo base_url() ?>client-program/targets/create',
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
            e.preventDefault;
            var btn = $(this);

            var btn = $(this);
            current_row = $(this).parents('tr');
            if (current_row.hasClass('child')) {
                current_row = current_row.prev();
            }
            let domain_id = $("#domains_dropdown_list").val();
            let goal_id = $("#goals_dropdown_list").val();
            let id = $(this).attr('id');

            if (domain_id == '' || goal_id == '') {
                showAlert('', 'Select Domain and Goal', 'error');
            } else {
                let domain_detail = $("#domains_dropdown_list :selected").html();
                let goal_detail = $("#goals_dropdown_list :selected").html();
                var ajaxRequest = $.ajax({
                    url: '<?php echo base_url() ?>client-program/targets/single',
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

                        $("#update_modal_title").html('Update Target for <br> Domain: ' + domain_detail + '<br> Goal: ' + goal_detail + '');

                        $("#update_modal #goal_id").val(goal_id);
                        $('#update_modal #id').val(row_data.id);

                        $('#update_modal #name').val(row_data.name);
                        $('#update_modal #description').val(row_data.description);


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
            }


        });
        /***************************************************************************************** */
        $('#update_modal').on('hidden.bs.modal', function(e) {
            $('#update_modal #update_modal_title').val('');
            $('#update_modal #goal_id').val('');
            $('#update_modal #name').val('');
            $('#update_modal #description').val('');
            $('#update_modal #id').val('');

        });
        /***************************************************************************************** */
        $('#btn_update').on('click', function() {
            var btn = $(this);
            var data = {
                'id': $('#update_modal #id').val(),
                'goal_id': $('#update_modal #goal_id').val(),
                'client_id': $('#update_modal #client_id').val(),
                'name': $('#update_modal #name').val(),
                'description': $('#update_modal #description').val(),

            };
            //console.log(data);
            var ajaxRequest = $.ajax({
                url: '<?php echo base_url() ?>client-program/targets/update',
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
                        url: '<?php echo base_url() ?>client-program/targets/delete',
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

        /********************************************************************** */
        // Event listener for the "Add New Probe Set and Rules" button
        $(document).on('click', '#add_new_probe_set_btn', function() {
            let goal_id = $("#goals_dropdown_list").val();
            let client_id = <?= $client->id ?>;

            // Load the modal for adding a new probe set
            $.ajax({
                url: '<?php echo base_url() ?>client-program/goal/create-probe-set',
                type: 'post',
                data: {
                    "goal_id": goal_id,
                    "client_id": client_id
                },
                success: function(response) {
                    if (response.status === 'success' || response.status === 'no_config') {
                        $('#offcanvasTitle').html('Attach Probe Set and Rules for selected client and goal');
                        $('#probe_set_and_rules_area').html(response.html);
                        offcanvas.show()
                    } else {
                        showAlert(response.statusText, response.message, response.status);
                    }
                },
                error: function(jqXHR, textStatus, error) {
                    showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
                }
            });
        });

        // Event listener for the "Manage Existing Rules" button
        $(document).on('click', '#manage_rules_btn', function() {
            let goal_id = $("#goals_dropdown_list").val();
            let client_id = <?= $client->id ?>;

            // Ensure goal_id is selected before proceeding
            if (!goal_id) {
                showAlert('Error', 'Please select a goal first.', 'error');
                return;
            }

            // Load the list of existing probe sets
            $.ajax({
                url: '<?php echo base_url() ?>client-program/goal/load-client-existing-probe-sets-list', // Endpoint to load the probe sets list
                type: 'post',
                data: {
                    "goal_id": goal_id,
                    "client_id": client_id
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#offcanvasTitle').html('Update Probe Set and Rules for selected client and goal');
                        $('#probe_set_and_rules_area').html(response.html); // Load the HTML content into the offcanvas area
                        offcanvas.show(); // Show the offcanvas panel
                    } else {
                        showAlert(response.statusText, response.message, response.status);
                    }
                },
                error: function(jqXHR, textStatus, error) {
                    showAlert('Error', "Request failed: " + textStatus + '<br>' + error, 'error');
                }
            });
        });

        /********************************************************************** */
    });
</script>
<?= $this->endSection() ?>