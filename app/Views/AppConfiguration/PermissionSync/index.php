<?= $this->extend("layout/master") ?>
<?= $this->section("page_content") ?>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h5 class="mb-sm-0">MIS Configuration</h5>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active">Permission Sync</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body border-bottom-dashed border-bottom">
                <blockquote class="blockquote custom-blockquote blockquote-outline blockquote-primary rounded mb-0">
                    <h5 class="text-dark mb-2">Permission Sync</h5>
                    <p class="text-muted">
                        Sync groups and permissions from <code>AuthGroups.php</code> into Settings.
                        Existing group matrix assignments are preserved, and only missing default matrix entries are merged.
                    </p>

                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-8">
                            <div><strong>Last Sync Status:</strong> <span id="permission_sync_last_status"><?= esc((string) (setting('PermissionSync.last_status') ?? 'never')) ?></span></div>
                            <div><strong>Last Sync At:</strong> <span id="permission_sync_last_synced_at"><?= esc((string) (setting('PermissionSync.last_synced_at') ?? 'N/A')) ?></span></div>
                            <div><strong>Last Sync By:</strong> <span id="permission_sync_last_synced_by"><?= esc((string) (setting('PermissionSync.last_synced_by_username') ?? 'N/A')) ?></span></div>
                            <div><strong>Last Message:</strong> <span id="permission_sync_last_message"><?= esc((string) (setting('PermissionSync.last_message') ?? '')) ?></span></div>
                        </div>
                        <div class="col-12 col-md-4 text-md-end">
                            <button type="button" id="btn_sync_permissions" class="btn btn-warning">
                                <i class="ri-refresh-line"></i> Sync Permissions
                            </button>
                        </div>
                    </div>
                </blockquote>
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

        $('#btn_sync_permissions').on('click', function() {
            var button = $(this);
            var ajaxRequest = $.ajax({
                url: '<?= base_url() ?>app-configuration/permissions/sync',
                type: 'post',
                data: {},
                beforeSend: function() {
                    button.prop("disabled", true);
                }
            });

            ajaxRequest.done(function(response) {
                showAlert(response.statusText, response.message, response.status);

                if (response.data && response.data.meta) {
                    $('#permission_sync_last_status').text(response.data.meta.last_status || 'n/a');
                    $('#permission_sync_last_synced_at').text(response.data.meta.last_synced_at || 'n/a');
                    $('#permission_sync_last_synced_by').text(response.data.meta.last_synced_by_username || 'n/a');
                    $('#permission_sync_last_message').text(response.data.meta.last_message || '');
                }
            });

            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });

            ajaxRequest.always(function() {
                button.prop("disabled", false);
            });
        });
    });
</script>
<?= $this->endSection() ?>
