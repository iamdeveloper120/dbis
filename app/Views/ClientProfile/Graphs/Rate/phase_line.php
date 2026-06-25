<?= $this->extend("layout/master-profile") ?>
<?= $this->section("head_tag") ?>
<?= $this->endSection() ?>
<?= $this->section("page_content") ?>
<div class="tab-content text-muted">
    <div class="tab-pane active show" role="tabpanel">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header border-bottom-dashed pb-0 mb-0">
                        <?= view('ClientProfile/Graphs/Rate/_tabs', ['tab' => 'phase-line']) ?>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="phase_line_table" class="table table-bordered align-middle" style="width:100%"> </table>
                        </div>
                    </div>
                </div>
                <!--end col-->
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
                <h5 class="modal-title">Add Rate Graph Phase Line</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="#" method="post" autocomplete="off">
                <div class="modal-body">
                    <div class="row">
                        <div class="mb-3">
                            <label class="form-label" for="a_week_date">Date *</label>
                            <input type="text" class="form-control" id="a_week_date">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="a_phaseline_key">Phaseline Key *</label>
                            <input type="text" class="form-control" id="a_phaseline_key">
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

<div class="modal fade" id="update_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title">Update Rate Graph Phase Line</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <input type="hidden" id="u_id">
                    <div class="mb-3">
                        <label class="form-label" for="u_week_date">Date *</label>
                        <input type="text" class="form-control" id="u_week_date">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="u_phaseline_key">Phaseline Key *</label>
                        <input type="text" class="form-control" id="u_phaseline_key">
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

        var csrfToken = "<?= csrf_hash() ?>";
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrfToken } });
        $(document).ajaxStart(function() { showPageLoader(); });
        $(document).ajaxStop(function() { hidePageLoader(); });

        var baseUrl = '/client-profile/graphs/rate/<?= encodeValue($client->id) ?>/phase-line';
        var current_row;
        var dataSet = [];

        table = $('#phase_line_table').DataTable({
            response: false,
            data: dataSet,
            lengthChange: false,
            ordering: false,
            lengthMenu: [[10, 25, 50, -1], ['10 rows', '25 rows', '50 rows', 'Show all']],
            layout: {
                topStart: {
                    buttons: [
                        {
                            text: '<i class="ri-add-line align-bottom me-1"></i>Add Phase Line Key',
                            className: 'btn btn-soft-info waves-effect waves-light material-shadow-none',
                            action: function() { show_add_modal(); }
                        },
                        { extend: 'pageLength', className: 'btn btn-light bg-gradient waves-effect waves-light' },
                        { extend: 'copy',       className: 'btn btn-light bg-gradient waves-effect waves-light' },
                        { extend: 'excel',      className: 'btn btn-light bg-gradient waves-effect waves-light' },
                        { extend: 'colvis',     className: 'btn btn-light bg-gradient waves-effect waves-light' }
                    ]
                },
                topEnd: { search: { placeholder: 'Search' } }
            },
            columnDefs: [
                {
                    targets: [0],
                    render: function(data, type, row) {
                        if (type === 'sort' || type === 'type') {
                            return moment(data, 'YYYY-MM-DD').format('YYYYMMDD');
                        }
                        return moment(data, 'YYYY-MM-DD').format(momentDateFormat);
                    }
                },
                { targets: [0, 1, 2], className: 'dt-nowrap' },
                {
                    targets: [2],
                    render: function(data, type, row) {
                        return '<div class="btn-group" role="group">' +
                            '<button id="' + row.id + '" type="button" class="btn btn-outline-warning btn-icon waves-effect waves-light update btn-sm"><i class="ri-edit-line"></i></button>&nbsp;' +
                            '<button id="' + row.id + '" type="button" class="btn btn-outline-danger btn-icon waves-effect waves-light delete btn-sm"><i class="ri-delete-bin-line"></i></button>' +
                            '</div>';
                    }
                }
            ],
            columns: [
                { data: 'p_date', title: 'Date', width: '20%', className: 'text-start' },
                { data: 'p_key',  title: 'Phase Line Key', width: '70%', className: 'text-start' },
                { data: null,     title: 'Action', width: '10%' }
            ]
        });

        function loadPhaseLines() {
            var ajaxRequest = $.ajax({
                url: baseUrl + '/list',
                type: 'post',
                data: { "graph_type": 'Target_Rate' }
            });
            ajaxRequest.done(function(response) {
                if (response.status === 'success') {
                    table.clear();
                    table.rows.add(response.data);
                    table.draw();
                } else {
                    showAlert(response.statusText, response.message, response.status);
                }
            });
            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
        }
        loadPhaseLines();

        function show_add_modal() {
            $('#a_week_date').val('');
            $('#a_phaseline_key').val('');
            $('#add_modal').modal('show');
        }

        $('#a_week_date').flatpickr({ dateFormat: dateFormat, maxDate: 'today', weekNumbers: true });

        $('#btn_add').on('click', function() {
            var btn = $(this);
            var ajaxRequest = $.ajax({
                url: baseUrl + '/new',
                type: 'post',
                data: { 'p_date': $('#a_week_date').val(), 'p_key': $('#a_phaseline_key').val() },
                beforeSend: function() { btn.prop('disabled', true); }
            });
            ajaxRequest.done(function(response) {
                if (response.status === 'success') {
                    var currentData = table.data().toArray();
                    currentData.unshift(response.data);
                    table.clear().rows.add(currentData).draw(false);
                    table.page('first').draw(false);
                    $(table.row(0).node()).css({ 'background-color': '#d4f8d4', 'color': '#000' });
                    $('#add_modal').modal('hide');
                    showAlert(response.statusText, response.message, response.status);
                } else {
                    showAlert(response.statusText, response.message, response.status);
                }
            });
            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
            ajaxRequest.always(function() { btn.prop('disabled', false); });
        });

        $('#add_modal').on('hidden.bs.modal', function() {
            $('#a_week_date').val('');
            $('#a_phaseline_key').val('');
        });

        $("#phase_line_table").on('click', '.update', function() {
            var btn = $(this);
            var id  = $(this).attr('id');
            current_row = $(this).parents('tr');
            if (current_row.hasClass('child')) { current_row = current_row.prev(); }

            var ajaxRequest = $.ajax({
                url: baseUrl + '/get-selected',
                type: 'post',
                data: { "id": id, 'graph_type': 'Target_Rate' },
                beforeSend: function() { btn.prop('disabled', true); }
            });
            ajaxRequest.done(function(response) {
                if (response.status === 'success') {
                    var row_data = response.data;
                    $('#u_id').val(row_data.id);
                    $('#u_phaseline_key').val(row_data.p_key);
                    $('#u_week_date').flatpickr({
                        defaultDate: row_data.p_date,
                        dateFormat: dateFormat,
                        maxDate: 'today',
                        weekNumbers: true
                    });
                    $('#update_modal').modal('show');
                } else {
                    showAlert(response.statusText, response.message, response.status);
                }
            });
            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
            ajaxRequest.always(function() { btn.prop('disabled', false); });
        });

        $('#btn_update').on('click', function() {
            var btn = $(this);
            var ajaxRequest = $.ajax({
                url: baseUrl + '/update',
                type: 'post',
                data: { 'id': $('#u_id').val(), 'p_date': $('#u_week_date').val(), 'p_key': $('#u_phaseline_key').val() },
                beforeSend: function() { btn.prop('disabled', true); }
            });
            ajaxRequest.done(function(response) {
                if (response.status === 'success') {
                    $(table.row(current_row).data(response.data).draw(false).node()).css({ 'background-color': '#d4ebf8', 'color': '#000' });
                    $('#update_modal').modal('hide');
                    showAlert(response.statusText, response.message, response.status);
                } else {
                    showAlert(response.statusText, response.message, response.status);
                }
            });
            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
            ajaxRequest.always(function() { btn.prop('disabled', false); });
        });

        $('#update_modal').on('hidden.bs.modal', function() {
            $('#u_id').val('');
            $('#u_week_date').val('');
            $('#u_phaseline_key').val('');
        });

        $("#phase_line_table").on('click', '.delete', function() {
            var id = $(this).attr('id');
            current_row = $(this).parents('tr');
            if (current_row.hasClass('child')) { current_row = current_row.prev(); }

            Swal.fire({
                title: 'Are you sure?',
                text: 'Once deleted, you will not be able to recover this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                confirmButtonClass: 'btn btn-primary w-xs me-2 mt-2',
                cancelButtonClass: 'btn btn-danger w-xs me-2 mt-2',
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var ajaxRequest = $.ajax({
                        url: baseUrl + '/delete',
                        type: 'post',
                        data: { 'id': id, 'graph_type': 'Target_Rate' }
                    });
                    ajaxRequest.done(function(response) {
                        if (response.status === 'success') {
                            table.row(current_row).remove().draw(false);
                            showAlert(response.statusText, response.message, response.status);
                        } else {
                            showAlert(response.statusText, response.message, response.status);
                        }
                    });
                    ajaxRequest.fail(function(jqXHR, textStatus, error) {
                        showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
                    });
                }
            });
        });

    });
</script>
<?= $this->endSection() ?>
