<?= $this->extend("layout/master") ?>

<?= $this->section("page_content") ?>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h5 class="mb-sm-0">Mands Reinforcers</h5>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active">Reinforcers</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs nav-justified nav-border-top nav-border-top-primary mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" data-bs-toggle="tab" href="#reinforcer-general-tab" role="tab" aria-selected="true">
                            <i class="ri-list-check-2 align-middle me-1"></i>General
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#reinforcer-client-defaults-tab" role="tab" aria-selected="false">
                            <i class="ri-user-settings-line align-middle me-1"></i>Client Defaults
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane active show" id="reinforcer-general-tab" role="tabpanel">
                        <div class="table-responsive">
                            <table id="dataTable" class="table table-bordered align-middle" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th style="width:200px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane" id="reinforcer-client-defaults-tab" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label" for="client_dropdown_list_defaults">Client *</label>
                                <select class="form-control" id="client_dropdown_list_defaults">
                                    <option value="">SELECT CLIENT</option>
                                    <?php foreach (($clients ?? []) as $client) : ?>
                                        <option value="<?= $client->id ?>"><?= esc($client->internal_mrn . ' - ' . $client->name()) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-7 d-flex align-items-end justify-content-end">
                                <button type="button" class="btn btn-primary" id="save_client_defaults">
                                    <i class="ri-save-line align-bottom me-1"></i>Save Client Defaults
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive mt-3">
                            <table class="table table-bordered align-middle" id="client_defaults_table">
                                <thead>
                                    <tr>
                                        <th style="width:100px;">Order</th>
                                        <th>Reinforcer</th>
                                        <th style="width:130px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php for ($i = 1; $i <= 10; $i++) : ?>
                                        <tr data-row="<?= $i ?>" draggable="true">
                                            <td class="text-center fw-semibold">
                                                <span class="order-badge"><?= $i ?></span>
                                                <i class="ri-draggable ms-2 text-muted drag-handle" title="Drag to reorder"></i>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control client-default-name" placeholder="Reinforcer <?= $i ?>">
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-secondary move-up" title="Move up">
                                                    <i class="ri-arrow-up-line"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary move-down" title="Move down">
                                                    <i class="ri-arrow-down-line"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endfor; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section("page_modal") ?>
<div class="modal fade" id="add_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="add_modal_title">Add Reinforcer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label class="form-label" for="add_name">Name *</label>
                <input type="text" class="form-control" name="name" id="add_name">
            </div>
            <div class="modal-footer">
                <div class="hstack gap-2 justify-content-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="ri-close-line align-bottom me-1"></i>Close</button>
                    <button type="button" class="btn btn-primary" id="btn_add"><i class="ri-save-line align-bottom me-1"></i>Save</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="update_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="update_modal_title">Update Reinforcer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="update_id">
                <label class="form-label" for="update_name">Name *</label>
                <input type="text" class="form-control" name="name" id="update_name">
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
        var csrfToken = "<?= csrf_hash() ?>";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        });

        const appBaseUrl = '<?= base_url() ?>';
        let table = null;
        let currentRow = null;

        function setActionButtonLoading(button, label) {
            if (!button || !button.length) {
                return;
            }

            if (!button.data('original-html')) {
                button.data('original-html', button.html());
            }

            const spinnerHtml = '<span class="spinner-border spinner-border-sm align-middle me-1" role="status" aria-hidden="true"></span>';
            button
                .prop('disabled', true)
                .attr('aria-busy', 'true')
                .html(spinnerHtml + label);
        }

        function resetActionButtonLoading(button) {
            if (!button || !button.length) {
                return;
            }

            const originalHtml = button.data('original-html');
            if (originalHtml) {
                button.html(originalHtml);
            }

            button
                .prop('disabled', false)
                .removeAttr('aria-busy');
        }

        function normalizeReinforcer(item) {
            return {
                id: item.id || '',
                name: item.name || '',
            };
        }

        function resetAddModal() {
            $('#add_modal #add_name').val('');
        }

        function resetUpdateModal() {
            $('#update_modal #update_id').val('');
            $('#update_modal #update_name').val('');
        }

        function populateDataTable(data) {
            table.clear().rows.add(data).draw();
        }

        function loadReinforcers() {
            $.ajax({
                url: appBaseUrl + 'mands/reinforcer/list',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    const data = (response.data || []).map(function(item) {
                        return normalizeReinforcer(item);
                    });
                    populateDataTable(data);
                },
                error: function(error) {
                    console.error('Error fetching data:', error);
                }
            });
        }

        const clientDefaultsSelect = $('#client_dropdown_list_defaults');
        clientDefaultsSelect.select2();

        function reindexDefaultRows() {
            $('#client_defaults_table tbody tr').each(function(index) {
                const currentOrder = index + 1;
                $(this).find('.order-badge').text(currentOrder);
            });
        }

        function swapRows(currentRow, targetRow) {
            if (!targetRow || targetRow.length === 0) {
                return;
            }
            const currentInput = currentRow.find('.client-default-name');
            const targetInput = targetRow.find('.client-default-name');
            const currentValue = currentInput.val();
            currentInput.val(targetInput.val());
            targetInput.val(currentValue);
            reindexDefaultRows();
        }

        function clearClientDefaultsForm() {
            $('#client_defaults_table .client-default-name').val('');
            reindexDefaultRows();
        }

        function loadClientDefaults(clientId) {
            clearClientDefaultsForm();
            if (!clientId) {
                return;
            }

            $.ajax({
                url: appBaseUrl + 'mands/reinforcer/client-defaults/list',
                type: 'post',
                dataType: 'json',
                data: {
                    client_id: clientId
                },
                success: function(response) {
                    if (response.status !== 'success') {
                        return;
                    }
                    const rows = (response.data || []);
                    $('#client_defaults_table tbody tr').each(function(index) {
                        const row = rows[index] || null;
                        $(this).find('.client-default-name').val(row ? row.name : '');
                    });
                    reindexDefaultRows();
                }
            });
        }

        clientDefaultsSelect.on('change', function() {
            loadClientDefaults($(this).val());
        });

        $('#client_defaults_table').on('click', '.move-up', function() {
            const row = $(this).closest('tr');
            swapRows(row, row.prev('tr'));
        });

        $('#client_defaults_table').on('click', '.move-down', function() {
            const row = $(this).closest('tr');
            swapRows(row, row.next('tr'));
        });

        let draggingRow = null;
        const defaultsTbody = document.querySelector('#client_defaults_table tbody');
        if (defaultsTbody) {
            defaultsTbody.addEventListener('dragstart', function(e) {
                const interactiveTarget = e.target.closest('input,button');
                if (interactiveTarget) {
                    e.preventDefault();
                    return;
                }
                const row = e.target.closest('tr');
                if (!row) return;
                draggingRow = row;
                row.classList.add('table-active');
                if (e.dataTransfer) {
                    e.dataTransfer.effectAllowed = 'move';
                }
            });

            defaultsTbody.addEventListener('dragend', function(e) {
                const row = e.target.closest('tr');
                if (row) {
                    row.classList.remove('table-active');
                }
                draggingRow = null;
            });

            defaultsTbody.addEventListener('dragover', function(e) {
                if (!draggingRow) return;
                e.preventDefault();
                const targetRow = e.target.closest('tr');
                if (!targetRow || targetRow === draggingRow) return;

                const rect = targetRow.getBoundingClientRect();
                const shouldInsertAfter = (e.clientY - rect.top) > (rect.height / 2);
                if (shouldInsertAfter) {
                    targetRow.after(draggingRow);
                } else {
                    targetRow.before(draggingRow);
                }
                reindexDefaultRows();
            });
        }

        $('#save_client_defaults').on('click', function() {
            const btn = $(this);
            const clientId = clientDefaultsSelect.val();

            if (!clientId) {
                showAlert('Validation', 'Please select a client', 'error');
                return;
            }

            const defaults = [];
            $('#client_defaults_table tbody tr').each(function(index) {
                defaults.push({
                    order: index + 1,
                    name: $(this).find('.client-default-name').val()
                });
            });

            const ajaxRequest = $.ajax({
                url: appBaseUrl + 'mands/reinforcer/client-defaults/save',
                type: 'post',
                dataType: 'json',
                data: {
                    client_id: clientId,
                    defaults: JSON.stringify(defaults)
                },
                beforeSend: function() {
                    setActionButtonLoading(btn, 'Saving...');
                }
            });

            ajaxRequest.done(function(response) {
                if (response.status === 'success') {
                    showAlert(response.statusText, response.message, response.status);
                    loadClientDefaults(clientId);
                } else if (response.status === 'error' && response.statusText === 'Validation_Error') {
                    let errors = Object.values(response.validationErrors || {});
                    displayValidationErrors(errors);
                } else {
                    showAlert(response.statusText, response.message, response.status);
                }
            });

            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });

            ajaxRequest.always(function() {
                resetActionButtonLoading(btn);
            });
        });

        table = $('#dataTable').DataTable({
            lengthChange: false,
            lengthMenu: [
                [10, 25, 50, -1],
                ['10 rows', '25 rows', '50 rows', 'Show all']
            ],
            layout: {
                topStart: {
                    buttons: [{
                            text: '<i class="ri-add-line align-bottom me-1"></i>New Reinforcer',
                            className: 'btn btn-soft-info waves-effect waves-light material-shadow-none',
                            action: function() {
                                resetAddModal();
                                $('#add_modal').modal('show');
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
                    data: 'name',
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        return '<button id="' + row.id + '" type="button" class="btn btn-sm btn-outline-warning update"><i class="ri-edit-line align-bottom me-1"></i>Edit</button>&nbsp;' +
                            '<button id="' + row.id + '" type="button" class="btn btn-sm btn-outline-danger delete"><i class="ri-delete-bin-line align-bottom me-1"></i>Delete</button>';
                    },
                    className: 'dt-nowrap'
                }
            ]
        });

        loadReinforcers();

        $('#add_modal').on('hidden.bs.modal', function() {
            resetAddModal();
        });

        $('#btn_add').on('click', function() {
            var btn = $(this);

            var ajaxRequest = $.ajax({
                url: appBaseUrl + 'mands/reinforcer/create',
                type: 'post',
                dataType: 'json',
                data: {
                    name: $('#add_modal #add_name').val(),
                },
                beforeSend: function() {
                    setActionButtonLoading(btn, 'Saving...');
                }
            });

            ajaxRequest.done(function(response) {
                if (response.status === 'success') {
                    $('#add_modal').modal('hide');
                    loadReinforcers();
                    showAlert(response.statusText, response.message, response.status);
                } else if (response.status === 'error' && response.statusText === 'Validation_Error') {
                    let errors = Object.values(response.validationErrors || {});
                    displayValidationErrors(errors);
                } else {
                    showAlert(response.statusText, response.message, response.status);
                }
            });

            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });

            ajaxRequest.always(function() {
                resetActionButtonLoading(btn);
            });
        });

        $("#dataTable").on('click', '.update', function() {
            var btn = $(this);
            var id = $(this).attr('id');
            currentRow = $(this).parents('tr');
            if (currentRow.hasClass('child')) {
                currentRow = currentRow.prev();
            }

            var ajaxRequest = $.ajax({
                url: appBaseUrl + 'mands/reinforcer/single',
                type: 'post',
                dataType: 'json',
                data: {
                    id: id,
                },
                beforeSend: function() {
                    btn.prop("disabled", true);
                }
            });

            ajaxRequest.done(function(response) {
                if (response.status === 'success') {
                    const rowData = normalizeReinforcer(response.data || {});
                    $('#update_modal #update_id').val(rowData.id);
                    $('#update_modal #update_name').val(rowData.name);
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

        $('#update_modal').on('hidden.bs.modal', function() {
            resetUpdateModal();
        });

        $('#btn_update').on('click', function() {
            var btn = $(this);

            var ajaxRequest = $.ajax({
                url: appBaseUrl + 'mands/reinforcer/update',
                type: 'post',
                dataType: 'json',
                data: {
                    id: $('#update_modal #update_id').val(),
                    name: $('#update_modal #update_name').val(),
                },
                beforeSend: function() {
                    setActionButtonLoading(btn, 'Updating...');
                }
            });

            ajaxRequest.done(function(response) {
                if (response.status === 'success') {
                    $('#update_modal').modal('hide');
                    loadReinforcers();
                    showAlert(response.statusText, response.message, response.status);
                } else if (response.status === 'error' && response.statusText === 'Validation_Error') {
                    let errors = Object.values(response.validationErrors || {});
                    displayValidationErrors(errors);
                } else {
                    showAlert(response.statusText, response.message, response.status);
                }
            });

            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });

            ajaxRequest.always(function() {
                resetActionButtonLoading(btn);
            });
        });

        $("#dataTable").on('click', '.delete', function() {
            var id = $(this).attr('id');
            currentRow = $(this).parents('tr');
            if (currentRow.hasClass('child')) {
                currentRow = currentRow.prev();
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
                if (!result.isConfirmed) {
                    currentRow = null;
                    return;
                }

                var ajaxRequest = $.ajax({
                    url: appBaseUrl + 'mands/reinforcer/delete',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        id: id,
                    }
                });

                ajaxRequest.done(function(response) {
                    if (response.status === 'success') {
                        table.row(currentRow).remove().draw(false);
                        showAlert(response.statusText, response.message, response.status);
                    } else {
                        showAlert(response.statusText, response.message, response.status);
                    }
                });

                ajaxRequest.fail(function(jqXHR, textStatus, error) {
                    showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
                });
            });
        });
    });
</script>
<?= $this->endSection() ?>
