<?= $this->extend("layout/master") ?>
<?= $this->section("page_content") ?>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h5 class="mb-sm-0">MIS Configuration</h5>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active">Report Settings</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body border-bottom-dashed border-bottom">
                <blockquote class="blockquote custom-blockquote blockquote-outline blockquote-primary rounded mb-0">
                    <h5 class="text-dark mb-2">Header (Left)</h5>
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Line 1</label>
                            <input type="text" id="header_line_1" class="form-control" value="<?= esc($settings['header_line_1'], 'attr') ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Line 2</label>
                            <input type="text" id="header_line_2" class="form-control" value="<?= esc($settings['header_line_2'], 'attr') ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Line 3</label>
                            <input type="text" id="header_line_3" class="form-control" value="<?= esc($settings['header_line_3'], 'attr') ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Line 4</label>
                            <input type="text" id="header_line_4" class="form-control" value="<?= esc($settings['header_line_4'], 'attr') ?>">
                        </div>
                    </div>
                </blockquote>
                <br>

                <blockquote class="blockquote custom-blockquote blockquote-outline blockquote-primary rounded mb-0">
                    <h5 class="text-dark mb-2">Header (Center/Right)</h5>
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Center Caption</label>
                            <input type="text" id="header_center_caption" class="form-control" value="<?= esc($settings['header_center_caption'], 'attr') ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" id="phone" class="form-control" value="<?= esc($settings['phone'], 'attr') ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Website</label>
                            <input type="text" id="website" class="form-control" value="<?= esc($settings['website'], 'attr') ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Location Line</label>
                            <input type="text" id="location_line" class="form-control" value="<?= esc($settings['location_line'], 'attr') ?>">
                        </div>
                    </div>
                </blockquote>
                <br>

                <blockquote class="blockquote custom-blockquote blockquote-outline blockquote-primary rounded mb-0">
                    <h5 class="text-dark mb-2">Branding</h5>
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Upload Logo (Replace Existing)</label>
                            <input type="file" id="logo_file" class="form-control" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp">
                            <small class="text-muted">Allowed: PNG/JPG/JPEG/WEBP, max 2MB.</small>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Current Logo</label>
                            <?php if (!empty($settings['logo_path'])) : ?>
                                <div class="border rounded p-2 bg-light">
                                    <img src="<?= base_url('app-configuration/report-settings/logo') ?>" alt="Current report logo" style="max-height:80px;max-width:100%;">
                                </div>
                            <?php else : ?>
                                <div class="text-muted">No logo uploaded yet.</div>
                            <?php endif ?>
                        </div>
                    </div>
                </blockquote>
                <br>

                <blockquote class="blockquote custom-blockquote blockquote-outline blockquote-primary rounded mb-0">
                    <h5 class="text-dark mb-2">Footer</h5>
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Company</label>
                            <input type="text" id="footer_company" class="form-control" value="<?= esc($settings['footer_company'], 'attr') ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Address Line 1</label>
                            <input type="text" id="footer_address_line_1" class="form-control" value="<?= esc($settings['footer_address_line_1'], 'attr') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address Line 2</label>
                            <input type="text" id="footer_address_line_2" class="form-control" value="<?= esc($settings['footer_address_line_2'], 'attr') ?>">
                        </div>
                    </div>
                </blockquote>
                <br>

                <blockquote class="blockquote custom-blockquote blockquote-outline blockquote-primary rounded mb-0">
                    <h5 class="text-dark mb-2">Progress Report Images</h5>
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Max Image Size (MB)</label>
                            <input
                                type="number"
                                min="1"
                                max="10"
                                step="1"
                                id="progress_image_max_size_mb"
                                class="form-control"
                                value="<?= esc($settings['progress_image_max_size_mb'] ?? '', 'attr') ?>"
                            >
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Max Images Per Draft</label>
                            <input
                                type="number"
                                min="1"
                                max="20"
                                step="1"
                                id="progress_image_max_count"
                                class="form-control"
                                value="<?= esc($settings['progress_image_max_count'] ?? '', 'attr') ?>"
                            >
                        </div>
                    </div>
                    <small class="text-muted">These limits apply to Instructional Programs image upload in Progress Report draft.</small>
                </blockquote>

                <hr>
                <div class="row">
                    <div class="col-12">
                        <button type="button" id="btn_save_report_settings" class="btn btn-primary float-end">
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

        $('#btn_save_report_settings').on('click', function() {
            var btn = $(this);
            var formData = new FormData();
            formData.append('header_line_1', $('#header_line_1').val());
            formData.append('header_line_2', $('#header_line_2').val());
            formData.append('header_line_3', $('#header_line_3').val());
            formData.append('header_line_4', $('#header_line_4').val());
            formData.append('header_center_caption', $('#header_center_caption').val());
            formData.append('phone', $('#phone').val());
            formData.append('website', $('#website').val());
            formData.append('location_line', $('#location_line').val());
            formData.append('footer_company', $('#footer_company').val());
            formData.append('footer_address_line_1', $('#footer_address_line_1').val());
            formData.append('footer_address_line_2', $('#footer_address_line_2').val());
            formData.append('progress_image_max_size_mb', $('#progress_image_max_size_mb').val());
            formData.append('progress_image_max_count', $('#progress_image_max_count').val());

            var logoInput = $('#logo_file')[0];
            if (logoInput && logoInput.files && logoInput.files.length > 0) {
                formData.append('logo_file', logoInput.files[0]);
            }

            $.ajax({
                url: '<?= base_url() ?>app-configuration/report-settings/save',
                type: 'post',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    btn.prop("disabled", true);
                }
            }).done(function(response) {
                showAlert(response.statusText, response.message, response.status);
                if (response.status === 'success' && logoInput && logoInput.files && logoInput.files.length > 0) {
                    logoInput.value = '';
                }
            }).fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            }).always(function() {
                btn.prop("disabled", false);
            });
        });
    });
</script>
<?= $this->endSection() ?>
