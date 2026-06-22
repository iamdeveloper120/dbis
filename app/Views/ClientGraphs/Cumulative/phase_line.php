<?= $this->extend("layout/master") ?>
<?= $this->section("page_content") ?>
<div class="row">
    <div class="col-lg-12">
        <div class="tab-content text-muted">
            <div class="tab-pane active show" role="tabpanel">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header border-bottom-dashed pb-0 mb-0">
                                <?= view('ClientGraphs/Cumulative/_tabs', ['tab' => 'phase-line']) ?>
                            </div>
                            <div class="card-body border-bottom-dashed border-bottom">
                                <div class="row justify-content-end">
                                    <div class="col-lg-10 col-md-12 col-sm-12">
                                        <select class="form-control " id="client_dropdown_list">
                                            <option value="">SELECT CLIENT ID</option>
                                            <?php foreach ($clients as $client) {  ?>
                                                 <option value="<?= $client->id ?>"><?= esc($client->internal_mrn . ' - ' . $client->name()) ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="col-lg-2 col-md-12 col-sm-12 align-self-end">
                                        <div class="gap-2 float-end">
                                            <div class="btn-group mt-4 mt-md-0" role="group" aria-label="Basic example">
                                                <button type="button" class="btn btn-warning bg-gradient waves-effect waves-light" id="back" title="Previous client"><i class="ri-arrow-left-line"></i></button>&nbsp;
                                                <button type="button" class="btn btn-warning bg-gradient waves-effect waves-light" id="next" title="Next client"><i class="ri-arrow-right-line"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body border-bottom-dashed border-bottom">
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
    </div>
    <!--end col-->
</div>

<?= $this->endSection() ?>
<?= $this->section("page_modal") ?>
<div class="modal fade" id="add_modal" tabindex="-1" aria-hidden="true">>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="add_modal_title">Add Weekly Graph Phaseline</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> </button>
            </div>
            <form action="#" method="post" autocomplete="off">
                <div class="modal-body">
                    <div class="row">
                        <input type="text" name="client_id" id="client_id" hidden="hidden" value="">
                        <div class="mb-3">
                            <label class="form-label" for="a_week_date">Date *</label>
                            <input type="text" class="form-control " name="a_week_date" id="a_week_date">
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="phaseline_key">Phaseline Key</label>
                            <input type="text" step=".50" class="form-control " name="phaseline_key" id="phaseline_key">
                        </div>


                    </div>



                </div>
                <div class="modal-footer">
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="ri-close-line align-bottom me-1"></i>Close</button>
                        <button type="button" class="btn btn-primary" id="btn_add"><i class="ri-save-line align-bottom me-1"></i>Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="update_modal" tabindex="-1" aria-hidden="true">>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="update_modal_title"> </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                </button>
            </div>
            <div class="modal-body">

                <div class="row">
                    <input type="text" class="form-control " hidden="hidden" name="id" id="id">
                    <input type="text" name="client_id" id="client_id" hidden="hidden" value="">

                    <div class="mb-3">
                        <label class="form-label" for="week_date">Date *</label>
                        <input type="text" class="form-control " name="u_week_date" id="u_week_date">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="hours">Phaseline Key *</label>
                        <input type="text" step="1" class="form-control " name="phaseline_key" id="phaseline_key">
                    </div>

                </div>


            </div>
            <div class="modal-footer">
                <div class="hstack gap-2 justify-content-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="ri-close-line align-bottom me-1"></i>Close</button>
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
        $("#a_week_date").flatpickr({
            dateFormat: dateFormat,
            maxDate: "today",
            weekNumbers: true,
        });
        $("#u_week_date").flatpickr({
            dateFormat: dateFormat,
            maxDate: "today",
            weekNumbers: true,
        });


        /***************************************************************************************** */
        var csrfToken = "<?= csrf_hash() ?>";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        });
        /***************************************************************************************** */
        $('#client_dropdown_list').select2();
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
                            text: '<i class="ri-add-line align-bottom me-1"></i>Add Phase Line Key',
                            className: 'btn btn-soft-info waves-effect waves-light material-shadow-none',
                            attr: {
                                id: 'add_phase_line_key'
                            },
                            action: function(e, dt, node, config) {
                                // Add your action for the button here
                                show_add_modal();
                            }
                        }, {
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
                    targets: [0, 1, 2],
                    className: 'dt-nowrap'
                },
                {
                    targets: [2], // Action column
                    render: function(data, type, row) {
                        return '<div class="btn-group" role="group" aria-label="Small button">' +
                            '<button id="' + row.id + '" client_id="' + row.client_id + '" type="button" class="btn btn-outline-warning  btn-icon waves-effect waves-light update btn-sm"><i class="ri-edit-line"></i></button>&nbsp;' +
                            '<button id="' + row.id + '" client_id="' + row.client_id + '" type="button" class="btn btn-outline-danger btn-icon waves-effect waves-light delete btn-sm"><i class="ri-delete-bin-line"></i></button>' +
                            '</div>'
                    }
                },
            ],
            columns: [{
                    data: 'p_date',
                    title: 'Date'
                }, // Date
                {
                    data: 'p_key',
                    title: 'Phase Line Key'
                },
                {
                    data: null,
                    title: 'Action'
                } // Action column (Edit/Delete buttons")
            ]


        });

        /****************************************************************************************  */
 

        $('#next').on('click', function() {
            var dropdown = $('#client_dropdown_list');
            var selectedOption = dropdown.val();
            var optionsCount = dropdown.find('option').length;
            if (optionsCount > 0) {
                var currentIndex = dropdown.find('option').index(dropdown.find('option:selected'));
                var nextIndex = currentIndex + 1;

                // Wrap around to the first option if the last option is selected
                if (nextIndex >= optionsCount) {
                    nextIndex = 1;
                }

                // Set the next option as selected
                dropdown.prop('selectedIndex', nextIndex).trigger('change');
                $('#search').click();
            } else {
                showAlert('', 'Client not exist', 'info');
            }

        });
        $('#back').on('click', function() {
            var dropdown = $('#client_dropdown_list');
            var selectedOption = dropdown.val();
            var optionsCount = dropdown.find('option').length;
            if (optionsCount > 0) {
                var currentIndex = dropdown.find('option').index(dropdown.find('option:selected'));
                var nextIndex = currentIndex - 1;

                // Wrap around to the first option if the last option is selected
                if (nextIndex <= 0) {
                    nextIndex = optionsCount - 1;
                }

                // Set the next option as selected
                dropdown.prop('selectedIndex', nextIndex).trigger('change');
                $('#search').click();
            } else {
                showAlert('', 'Client not exist', 'info');
            }

        });
       
        $("#client_dropdown_list").on('change', function(e) {
            e.preventDefault;
            search = $(this);
            let client_id = $("#client_dropdown_list").val();

            var ajaxRequest = $.ajax({
                url: '/graphs/cumulative/phase-line/list',
                type: 'post',
                data: {
                    "client_id": client_id,
                    "graph_type": 'Cumulative'
                },
                beforeSend: function(xhr) {
                    $('#client_dropdown_list').prop("disabled", true);
                    search.prop("disabled", true);
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
            ajaxRequest.always(function() {
                $('#client_dropdown_list').prop("disabled", false);
                search.prop("disabled", false);
            });

        }); //On change function ends


        /***************************************************************************************** */

        function show_add_modal() {
            let client_id = $("#client_dropdown_list").val();

            if (client_id == '') {
                showAlert('', 'Select Client', 'error');

            } else {
                let client_detail = $("#client_dropdown_list :selected").html();
                $("#add_modal_title").html('Add Cumulative Graph Phase Line  [' + client_detail + ']');
                $("#add_modal #client_id").val(client_id);
                $('#add_modal').modal('show');
            }

        };

        /***************************************************************************************** */
        $('#btn_add').on('click', function() {
            var btn = $(this);

            var data = {
                'p_date': $('#add_modal #a_week_date').val(),
                'client_id': $('#add_modal #client_id').val(),
                'graph_type': 'Cumulative',
                'p_key': $('#add_modal #phaseline_key').val()

            };

            var ajaxRequest = $.ajax({
                url: '<?php echo base_url() ?>graphs/cumulative/phase-line/new',
                type: 'post',
                data: data,
                beforeSend: function(xhr) {
                    btn.prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {

                    // Get current table data
                    var currentData = table.data().toArray();

                    // Add the new row data to the beginning of the array
                    currentData.unshift(response.data);

                    // Clear the table and re-add the data with the new row at the top
                    table.clear().rows.add(currentData).draw(false);

                    // Always switch back to the first page to show the new row
                    table.page('first').draw(false);

                    // Get the newly added row (which is now the first row)
                    var newRow = table.row(0).node();

                    // Apply CSS to the new row to highlight it in green
                    $(newRow).css({
                        'background-color': '#d4f8d4', // Green color for new row
                        'color': '#000'
                    });


                    $('#add_modal').modal('hide');
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
        $('#add_modal').on('hidden.bs.modal', function(e) {
            $('#add_modal #add_modal_title').val('');
            $('#add_modal #a_week_date').val('');
            $('#add_modal #client_id').val('');
            $('#add_modal #phaseline_key').val('');

        });


        /**************************************************************************************** */
        $("#phase_line_table").on('click', '.update', function(e) {
            var btn = $(this);
            var id = $(this).attr('id');

            current_row = $(this).parents('tr');
            selectedRowIndex = current_row.index();

            if (current_row.hasClass('child')) {
                current_row = current_row.prev();
                selectedRowIndex = current_row.index();
            }

            var ajaxRequest = $.ajax({
                url: '<?php echo base_url() ?>graphs/cumulative/phase-line/get-selected',
                type: 'post',
                data: {
                    "id": id,
                    'graph_type': 'Cumulative'
                },
                beforeSend: function(xhr) {
                    btn.prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    row_data = response.data;

                    $('#update_modal #update_modal_title').html('Update Cumulative Graph Phase Line');
                    $("#u_week_date").flatpickr({
                        defaultDate: row_data.p_date,
                        dateFormat: dateFormat,
                        maxDate: "today",
                        weekNumbers: true,
                    });
                    $('#update_modal #id').val(row_data.id);
                    $('#update_modal #client_id').val(row_data.client_id);
                    $('#update_modal #phaseline_key').val(row_data.p_key);

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
        $('#btn_update').on('click', function() {
            var btn = $(this);
            var data = {
                'id': $('#update_modal #id').val(),
                'client_id': $('#update_modal #client_id').val(),
                'p_date': $('#update_modal #u_week_date').val(),
                'graph_type': 'Cumulative',
                'p_key': $('#update_modal #phaseline_key').val()
            };
            var ajaxRequest = $.ajax({
                url: '<?php echo base_url() ?>graphs/cumulative/phase-line/update',
                type: 'post',
                data: data,
                beforeSend: function(xhr) {
                    btn.prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {

                    var updatedRow = table.row(current_row).data(response.data).draw(false).node();

                    // Highlight the updated row in blue
                    //$(updatedRow).addClass('table-info');
                    $(updatedRow).css({
                        'background-color': '#d4ebf8',
                        'color': '#000'
                    });

                    $('#update_modal').modal('hide');
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
        $('#update_modal').on('hidden.bs.modal', function(e) {
            $('#update_modal #update_modal_title').val('');
            $('#update_modal #u_week_date').val('');
            $('#update_modal #client_id').val('');
            $('#update_modal #phaseline_key').val('');
            $('#update_modal #id').val('');

        });

        /*************************************************************************************** */
        $("#phase_line_table").on('click', '.delete', function(e) {

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
                confirmButtonClass: 'btn btn-primary w-xs me-2 mt-2',
                cancelButtonClass: 'btn btn-danger w-xs me-2 mt-2',
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var ajaxRequest = $.ajax({
                        url: '<?php echo base_url() ?>graphs/cumulative/phase-line/delete',
                        type: 'post',
                        data: {
                            "id": id,
                            'graph_type': 'Cumulative'
                        },
                        beforeSend: function(xhr) {}
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
                    current_row = '';
                }
            });
        });

        /***************************************************************************************** */

    });
</script>
<?= $this->endSection() ?>