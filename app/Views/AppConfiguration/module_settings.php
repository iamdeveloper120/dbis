<?= $this->extend("layout/master") ?>
<?= $this->section("page_content") ?>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h5 class="mb-sm-0">MIS Configuration</h5>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active">Module Settings</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body border-bottom-dashed border-bottom">
                <blockquote class="blockquote custom-blockquote blockquote-outline blockquote-primary rounded mb-0">
                    <h5 class="text-dark mb-2">Current Mand List - Reinforcer Media</h5>
                    <p class="text-muted mb-3">These rules control media upload and limits in Client Profile Current Mand List.</p>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label d-block mb-2">Allowed Image Types</label>
                            <div class="d-flex flex-wrap gap-3">
                                <?php foreach ($imageTypeOptions as $type): ?>
                                    <?php $checked = in_array($type, $settings['image_types'] ?? [], true); ?>
                                    <div class="form-check form-check-inline me-0">
                                        <input class="form-check-input" type="checkbox" name="image_types[]" id="image_type_<?= esc($type, 'attr') ?>" value="<?= esc($type, 'attr') ?>" <?= $checked ? 'checked' : '' ?>>
                                        <label class="form-check-label text-uppercase" for="image_type_<?= esc($type, 'attr') ?>"><?= esc($type) ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label d-block mb-2">Allowed Video Types</label>
                            <div class="d-flex flex-wrap gap-3">
                                <?php foreach ($videoTypeOptions as $type): ?>
                                    <?php $checked = in_array($type, $settings['video_types'] ?? [], true); ?>
                                    <div class="form-check form-check-inline me-0">
                                        <input class="form-check-input" type="checkbox" name="video_types[]" id="video_type_<?= esc($type, 'attr') ?>" value="<?= esc($type, 'attr') ?>" <?= $checked ? 'checked' : '' ?>>
                                        <label class="form-check-label text-uppercase" for="video_type_<?= esc($type, 'attr') ?>"><?= esc($type) ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="form-label" for="image_max_size_mb">Max Image Size (MB)</label>
                            <input type="number" min="1" max="<?= (int) ($settings['image_max_size_limit_mb'] ?? 20) ?>" step="1" id="image_max_size_mb" class="form-control" value="<?= esc((string) ($settings['image_max_size_mb'] ?? 5), 'attr') ?>">
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="form-label" for="video_max_size_mb">Max Video Size (MB)</label>
                            <input type="number" min="1" max="<?= (int) ($settings['video_max_size_limit_mb'] ?? 500) ?>" step="1" id="video_max_size_mb" class="form-control" value="<?= esc((string) ($settings['video_max_size_mb'] ?? 25), 'attr') ?>">
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="form-label" for="image_max_count">Max Image Files Per Reinforcer</label>
                            <input type="number" min="1" max="<?= (int) ($settings['image_max_count_limit'] ?? 100) ?>" step="1" id="image_max_count" class="form-control" value="<?= esc((string) ($settings['image_max_count'] ?? 5), 'attr') ?>">
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="form-label" for="video_max_count">Max Video Files Per Reinforcer</label>
                            <input type="number" min="1" max="<?= (int) ($settings['video_max_count_limit'] ?? 100) ?>" step="1" id="video_max_count" class="form-control" value="<?= esc((string) ($settings['video_max_count'] ?? 5), 'attr') ?>">
                        </div>
                    </div>
                </blockquote>

                <hr>
                <div class="row">
                    <div class="col-12">
                        <button type="button" id="btn_save_module_settings" class="btn btn-primary float-end">
                            <i class="ri-save-line"></i> Save Settings
                        </button>
                    </div>
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

        function setSaveLoading(btn) {
            if (!btn.data('default-html')) {
                btn.data('default-html', btn.html());
            }
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm align-middle me-1" role="status" aria-hidden="true"></span>Saving...');
        }

        function resetSaveLoading(btn) {
            var html = btn.data('default-html');
            if (html) {
                btn.html(html);
            }
            btn.prop('disabled', false);
        }

        $('#btn_save_module_settings').on('click', function() {
            var btn = $(this);
            var formData = new FormData();

            $('input[name="image_types[]"]:checked').each(function() {
                formData.append('image_types[]', $(this).val());
            });
            $('input[name="video_types[]"]:checked').each(function() {
                formData.append('video_types[]', $(this).val());
            });

            formData.append('image_max_size_mb', $('#image_max_size_mb').val());
            formData.append('video_max_size_mb', $('#video_max_size_mb').val());
            formData.append('image_max_count', $('#image_max_count').val());
            formData.append('video_max_count', $('#video_max_count').val());

            $.ajax({
                url: '<?= base_url() ?>app-configuration/module-settings/save',
                type: 'post',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    setSaveLoading(btn);
                }
            }).done(function(response) {
                if (response.status === 'error' && response.statusText === 'Validation_Error' && response.validationErrors) {
                    var errors = Object.values(response.validationErrors || {});
                    showAlert(response.statusText, errors.join('<br>'), response.status);
                    return;
                }
                showAlert(response.statusText, response.message, response.status);
            }).fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            }).always(function() {
                resetSaveLoading(btn);
            });
        });
    });
</script>
<?= $this->endSection() ?>
