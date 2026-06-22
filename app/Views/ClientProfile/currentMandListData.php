<?= $this->extend("layout/master-profile") ?>

<?= $this->section("head_tag") ?>
<style>
    .mand-media-thumb {
        width: 56px;
        height: 56px;
        object-fit: cover;
        border-radius: .5rem;
        border: 1px solid #dee2e6;
        cursor: pointer;
    }

    .mand-media-placeholder {
        color: #6c757d;
        font-size: .85rem;
    }

    .media-manager-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: .75rem;
    }

    .image-gallery-main {
        width: 100%;
        max-height: 62vh;
        min-height: 260px;
        object-fit: contain;
        border: 1px solid #dee2e6;
        border-radius: .5rem;
        background: #f8f9fa;
    }

    .image-gallery-thumbs {
        display: flex;
        gap: .5rem;
        overflow-x: auto;
        padding: .25rem 0 .1rem;
    }

    .image-gallery-thumb-btn {
        border: 2px solid transparent;
        border-radius: .4rem;
        padding: 0;
        background: transparent;
        line-height: 0;
        flex: 0 0 auto;
    }

    .image-gallery-thumb-btn.is-active {
        border-color: #0d6efd;
    }

    .image-gallery-thumb {
        width: 84px;
        height: 64px;
        object-fit: cover;
        border-radius: .3rem;
    }

    .media-manager-card {
        border: 1px solid #dee2e6;
        border-radius: .5rem;
        padding: .5rem;
        background: #fff;
    }

    .media-manager-card .preview {
        width: 100%;
        height: 110px;
        object-fit: cover;
        border-radius: .4rem;
        cursor: pointer;
    }

    .media-manager-card .actions {
        margin-top: .5rem;
        display: flex;
        gap: .35rem;
        flex-wrap: wrap;
    }

    .media-manager-video-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
    }

    .media-manager-video-actions {
        display: inline-flex;
        gap: .35rem;
    }

    .current-mand-action-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem;
        justify-content: center;
    }

    .current-mand-action-wrap .btn {
        white-space: nowrap;
    }

    #current_mand_list_table {
        table-layout: fixed;
    }

    #current_mand_list_table th,
    #current_mand_list_table td {
        vertical-align: middle;
    }

    #current_mand_list_table td {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #current_mand_list_table td:nth-child(1),
    #current_mand_list_table td:nth-child(3),
    #current_mand_list_table td:nth-child(4) {
        white-space: normal;
        word-break: break-word;
    }

    #current_mand_list_table td:nth-child(5),
    #current_mand_list_table td:nth-child(6),
    #current_mand_list_table td:last-child {
        overflow: visible;
    }

</style>
<?= $this->endSection() ?>

<?= $this->section("page_content") ?>
<?php
$canCreate = auth()->user()->can('client-profile.mands.dictionary.create');
$canUpdate = auth()->user()->can('client-profile.mands.dictionary.update');
$canDelete = auth()->user()->can('client-profile.mands.dictionary.delete');
$canManageMedia = auth()->user()->can('client-profile.mands.dictionary.media.manage');
?>

<div class="table-responsive overflow-visible">
    <table id="current_mand_list_table" class="table table-bordered align-middle mb-0" style="width:100%">
        <thead>
            <tr>
                <th style="width: 20%;">Mand Target</th>
                <th style="width: 10%;">Date Introduced</th>
                <th style="width: 20%;">Vocal/Sign</th>
                <th style="width: 20%;">Description</th>
                <th style="width: 10%;">Image</th>
                <th style="width: 10%;">Video</th>
                <th style="width: 10%;">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
<?= $this->endSection() ?>

<?= $this->section("page_modal") ?>
<?php if ($canCreate): ?>
    <div class="modal fade" id="currentMandAddModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Mand Target</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="add_reinforcer_name">Mand Target *</label>
                            <input type="text" class="form-control" id="add_reinforcer_name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="add_introduced_at">Date Introduced *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ri-calendar-line"></i></span>
                                <input type="text" class="form-control" id="add_introduced_at" data-provider="flatpickr" data-date-format="d-M-Y">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="add_vocal_sign">Vocal/Sign</label>
                            <input type="text" class="form-control" id="add_vocal_sign">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="add_description">Description</label>
                            <textarea class="form-control" id="add_description" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btn_add_mand_save">
                        <i class="ri-save-line align-bottom me-1"></i>Save
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($canUpdate): ?>
    <div class="modal fade" id="currentMandEditModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Current Mand</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_reinforcer_id">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="edit_reinforcer_name">Mand Target *</label>
                            <input type="text" class="form-control" id="edit_reinforcer_name">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="edit_introduced_at">Date Introduced *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ri-calendar-line"></i></span>
                                <input type="text" class="form-control" id="edit_introduced_at" data-provider="flatpickr" data-date-format="d-M-Y">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label" for="edit_vocal_sign">Vocal/Sign</label>
                            <input type="text" class="form-control" id="edit_vocal_sign">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="edit_description">Description</label>
                            <textarea class="form-control" id="edit_description" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btn_edit_mand_save">
                        <i class="ri-save-line align-bottom me-1"></i>Update
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="modal fade" id="currentMandMediaManagerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Media: <span id="current_mand_media_title">-</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="file" id="media_upload_images_input" class="d-none" multiple>
                <input type="file" id="media_upload_videos_input" class="d-none" multiple>
                <input type="file" id="media_replace_image_input" class="d-none">
                <input type="file" id="media_replace_video_input" class="d-none">

                <div class="row g-3">
                    <div class="col-lg-7">
                        <div class="card border mb-0">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h6 class="mb-0">Images <span id="current_mand_images_count" class="badge bg-info-subtle text-info">0</span></h6>
                                <?php if ($canManageMedia): ?>
                                    <button type="button" class="btn btn-sm btn-soft-info waves-effect waves-light" id="btn_upload_images">
                                        <i class="ri-image-add-line align-bottom me-1"></i>Upload Images
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <div id="current_mand_images_rules" class="small text-muted mb-2"></div>
                                <div id="current_mand_images_gallery" class="media-manager-gallery"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="card border mb-0">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h6 class="mb-0">Videos <span id="current_mand_videos_count" class="badge bg-primary-subtle text-primary">0</span></h6>
                                <?php if ($canManageMedia): ?>
                                    <button type="button" class="btn btn-sm btn-soft-primary waves-effect waves-light" id="btn_upload_videos">
                                        <i class="ri-video-add-line align-bottom me-1"></i>Upload Videos
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <div id="current_mand_videos_rules" class="small text-muted mb-2"></div>
                                <video id="currentMandManagerVideoPlayer" class="w-100 rounded border" style="background:#000; min-height:220px;" controls controlsList="nodownload" playsinline>
                                    <source id="currentMandManagerVideoSource" src="">
                                    Your browser does not support the video tag.
                                </video>
                                <div id="current_mand_videos_list" class="list-group list-group-flush mt-3"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="currentMandImageGalleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Images: <span id="current_mand_image_gallery_title">-</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="current_mand_image_gallery_meta" class="small text-muted mb-2"></div>
                <img id="current_mand_image_gallery_main" class="image-gallery-main mb-3" src="" alt="Selected image">
                <div id="current_mand_image_gallery_thumbs" class="image-gallery-thumbs"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="currentMandVideoGalleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Videos: <span id="current_mand_video_gallery_title">-</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="current_mand_video_gallery_meta" class="small text-muted mb-2"></div>
                <video id="currentMandColumnVideoPlayer" class="w-100 rounded border" style="background:#000; min-height:260px;" controls controlsList="nodownload" playsinline>
                    <source id="currentMandColumnVideoSource" src="">
                    Your browser does not support the video tag.
                </video>
                <div id="current_mand_video_gallery_list" class="list-group list-group-flush mt-3"></div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section("page_js") ?>
<script>
    (function() {
        const canCreate = <?= $canCreate ? 'true' : 'false' ?>;
        const canUpdate = <?= $canUpdate ? 'true' : 'false' ?>;
        const canDelete = <?= $canDelete ? 'true' : 'false' ?>;
        const canManageMedia = <?= $canManageMedia ? 'true' : 'false' ?>;
        const encodedClientId = '<?= esc($encodedClientId) ?>';
        const appBaseUrl = '<?= base_url() ?>';

        const listUrl = appBaseUrl + 'client-profile/dataSheet/currentMandList/list/' + encodedClientId;
        const createUrl = appBaseUrl + 'client-profile/dataSheet/currentMandList/create/' + encodedClientId;
        const updateUrl = appBaseUrl + 'client-profile/dataSheet/currentMandList/update/' + encodedClientId;
        const deleteUrl = appBaseUrl + 'client-profile/dataSheet/currentMandList/delete/' + encodedClientId;
        const mediaUploadUrl = appBaseUrl + 'client-profile/dataSheet/currentMandList/media/upload/' + encodedClientId;
        const mediaDeleteUrl = appBaseUrl + 'client-profile/dataSheet/currentMandList/media/delete/' + encodedClientId;
        const mediaReplaceUrl = appBaseUrl + 'client-profile/dataSheet/currentMandList/media/replace/' + encodedClientId;
        const mediaSettingsUrl = appBaseUrl + 'client-profile/dataSheet/currentMandList/media/settings/' + encodedClientId;
        const mediaViewBaseUrl = appBaseUrl + 'client-profile/dataSheet/currentMandList/media/view/' + encodedClientId + '/';

        let table = null;
        let currentEditRecord = null;
        let currentMediaRecord = null;
        let mediaReplaceContext = null;
        let selectedVideoMediaId = 0;
        let currentImageGalleryRecord = null;
        let currentImageGallerySelectedId = 0;
        let currentVideoGalleryRecord = null;
        let currentVideoGallerySelectedId = 0;
        let mediaSettings = {
            image_types: ['jpg', 'jpeg', 'png', 'webp'],
            video_types: ['mp4', 'webm', 'mov'],
            image_max_size_mb: 5,
            video_max_size_mb: 25,
            image_max_count: 5,
            video_max_count: 5,
        };

        const csrfToken = "<?= csrf_hash() ?>";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        });

        function escapeHtml(value) {
            return $('<div>').text(value || '').html();
        }

        function toInt(value, fallback) {
            const parsed = parseInt(value, 10);
            return Number.isFinite(parsed) && parsed > 0 ? parsed : fallback;
        }

        function setActionButtonLoading(button, label) {
            if (!button || !button.length) return;
            if (!button.data('original-html')) {
                button.data('original-html', button.html());
            }
            const spinner = '<span class="spinner-border spinner-border-sm align-middle me-1" role="status" aria-hidden="true"></span>';
            button.prop('disabled', true).attr('aria-busy', 'true').html(spinner + label);
        }

        function resetActionButtonLoading(button) {
            if (!button || !button.length) return;
            const original = button.data('original-html');
            if (original) {
                button.html(original);
            }
            button.prop('disabled', false).removeAttr('aria-busy');
        }

        function mediaViewUrl(mediaId) {
            return mediaViewBaseUrl + mediaId;
        }

        function normalizeSettings(raw) {
            const normalized = Object.assign({}, mediaSettings);
            if (raw && typeof raw === 'object') {
                if (Array.isArray(raw.image_types) && raw.image_types.length > 0) {
                    normalized.image_types = raw.image_types.map((item) => String(item || '').toLowerCase().trim()).filter(Boolean);
                }
                if (Array.isArray(raw.video_types) && raw.video_types.length > 0) {
                    normalized.video_types = raw.video_types.map((item) => String(item || '').toLowerCase().trim()).filter(Boolean);
                }
                normalized.image_max_size_mb = toInt(raw.image_max_size_mb, normalized.image_max_size_mb);
                normalized.video_max_size_mb = toInt(raw.video_max_size_mb, normalized.video_max_size_mb);
                normalized.image_max_count = toInt(raw.image_max_count, normalized.image_max_count);
                normalized.video_max_count = toInt(raw.video_max_count, normalized.video_max_count);
            }
            return normalized;
        }

        function normalizeRecord(item) {
            const images = Array.isArray(item.images) ? item.images : [];
            const videos = Array.isArray(item.videos) ? item.videos : [];
            return {
                id: parseInt(item.id || 0, 10),
                reinforcer_name: item.reinforcer_name || '',
                introduced_at: item.introduced_at || '',
                introduced_at_display: item.introduced_at_display || '',
                vocal_sign: item.vocal_sign || '',
                description: item.description || '',
                images: images.map((m) => ({
                    id: parseInt(m.id || 0, 10),
                    media_type: m.media_type || 'image',
                    media_path: m.media_path || ''
                })),
                videos: videos.map((m) => ({
                    id: parseInt(m.id || 0, 10),
                    media_type: m.media_type || 'video',
                    media_path: m.media_path || ''
                }))
            };
        }

        function findRowById(id) {
            const rows = table.rows().data().toArray();
            for (let i = 0; i < rows.length; i++) {
                if (parseInt(rows[i].id || 0, 10) === parseInt(id || 0, 10)) {
                    return rows[i];
                }
            }
            return null;
        }

        function renderImageCell(row) {
            if (!row.images || row.images.length === 0) {
                return '<span class="mand-media-placeholder">Not Available</span>';
            }
            const first = row.images[0];
            const badge = row.images.length > 1 ? '<span class="badge bg-info-subtle text-info ms-1">+' + (row.images.length - 1) + '</span>' : '';
            return '<button type="button" class="btn btn-link p-0 border-0 d-flex align-items-center justify-content-center gap-1 js-open-image-gallery" data-id="' + row.id + '" title="View images"><img src="' + escapeHtml(mediaViewUrl(first.id)) + '" class="mand-media-thumb" alt="Mand image">' + badge + '</button>';
        }

        function renderVideoCell(row) {
            if (!row.videos || row.videos.length === 0) {
                return '<span class="mand-media-placeholder">Not Available</span>';
            }
            const badge = row.videos.length > 1 ? '<span class="badge bg-primary-subtle text-primary ms-1">+' + (row.videos.length - 1) + '</span>' : '';
            return '<div class="d-flex align-items-center justify-content-center gap-1"><button type="button" class="btn btn-outline-primary btn-sm js-open-video-gallery" data-id="' + row.id + '" title="View videos"><i class="ri-video-line"></i></button>' + badge + '</div>';
        }

        function renderActionCell(row) {
            const items = [];
            if (canManageMedia) {
                items.push('<li><button type="button" class="dropdown-item js-manage-media" data-id="' + row.id + '"><i class="ri-folder-video-line align-bottom me-2"></i>Manage Media</button></li>');
            }
            if (canUpdate) {
                items.push('<li><button type="button" class="dropdown-item js-edit-mand" data-id="' + row.id + '"><i class="ri-edit-line align-bottom me-2"></i>Edit</button></li>');
            }
            if (canDelete) {
                items.push('<li><button type="button" class="dropdown-item text-danger js-delete-mand" data-id="' + row.id + '"><i class="ri-delete-bin-line align-bottom me-2"></i>Delete</button></li>');
            }

            if (items.length === 0) {
                return '<div class="text-center"><button type="button" class="btn btn-sm btn-light border disabled" aria-disabled="true" title="No actions available"><i class="ri-settings-3-line"></i></button></div>';
            }

            return '' +
                '<div class="dropdown text-center">' +
                '<button type="button" class="btn btn-sm btn-light border" data-bs-toggle="dropdown" aria-expanded="false" title="Actions">' +
                '<i class="ri-settings-3-line"></i>' +
                '</button>' +
                '<ul class="dropdown-menu dropdown-menu-end">' + items.join('') + '</ul>' +
                '</div>';
        }

        function buildColumns() {
            return [
                { data: 'reinforcer_name', width: '20%', render: (data) => data ? escapeHtml(data) : '<span class="mand-media-placeholder">Not Available</span>' },
                { data: 'introduced_at_display', width: '10%', render: (data, type, row) => (data || row.introduced_at) ? escapeHtml(data || row.introduced_at) : '<span class="mand-media-placeholder">Not Available</span>' },
                { data: 'vocal_sign', width: '20%', render: (data) => data ? escapeHtml(data) : '<span class="mand-media-placeholder">Not Available</span>' },
                { data: 'description', width: '20%', render: (data) => data ? escapeHtml(data) : '<span class="mand-media-placeholder">Not Available</span>' },
                { data: null, width: '10%', className: 'text-center', orderable: false, render: (data, type, row) => renderImageCell(row) },
                { data: null, width: '10%', className: 'text-center', orderable: false, render: (data, type, row) => renderVideoCell(row) },
                { data: null, width: '10%', className: 'dt-nowrap', orderable: false, render: (data, type, row) => renderActionCell(row) },
            ];
        }

        function loadCurrentMandList() {
            return $.ajax({
                url: listUrl,
                type: 'post',
                dataType: 'json'
            }).done(function(response) {
                if (response.status !== 'success') {
                    showAlert(response.statusText || 'Error', response.message || 'Unable to load Current Mand List', 'error');
                    return;
                }
                const rows = (response.data || []).map((item) => normalizeRecord(item));
                table.clear().rows.add(rows).draw(false);
            }).fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
        }

        function loadMediaSettings() {
            return $.ajax({
                url: mediaSettingsUrl,
                type: 'post',
                dataType: 'json'
            }).done(function(response) {
                if (response.status === 'success') {
                    mediaSettings = normalizeSettings(response.data || {});
                }
            }).always(function() {
                applyMediaSettingToInputs();
                renderMediaRules();
            });
        }

        function applyMediaSettingToInputs() {
            const imageAccept = (mediaSettings.image_types || []).map((ext) => '.' + ext).join(',');
            const videoAccept = (mediaSettings.video_types || []).map((ext) => '.' + ext).join(',');
            $('#media_upload_images_input, #media_replace_image_input').attr('accept', imageAccept);
            $('#media_upload_videos_input, #media_replace_video_input').attr('accept', videoAccept);
        }

        function renderMediaRules() {
            $('#current_mand_images_rules').text('Allowed: ' + (mediaSettings.image_types || []).join(', ').toUpperCase() + ' | Max Size: ' + mediaSettings.image_max_size_mb + 'MB | Max Files per Reinforcer: ' + mediaSettings.image_max_count);
            $('#current_mand_videos_rules').text('Allowed: ' + (mediaSettings.video_types || []).join(', ').toUpperCase() + ' | Max Size: ' + mediaSettings.video_max_size_mb + 'MB | Max Files per Reinforcer: ' + mediaSettings.video_max_count);
        }

        function resetAddModal() {
            $('#add_reinforcer_name, #add_introduced_at, #add_vocal_sign, #add_description').val('');
        }

        function resetEditModal() {
            currentEditRecord = null;
            $('#edit_reinforcer_id, #edit_reinforcer_name, #edit_introduced_at, #edit_vocal_sign, #edit_description').val('');
        }

        function initDateInput(selector) {
            const element = document.querySelector(selector);
            if (!element) return null;
            return flatpickr(element, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: typeof dateFormat !== 'undefined' ? dateFormat : 'd-M-Y',
                allowInput: true
            });
        }

        function openEditModalById(id) {
            const record = findRowById(id);
            if (!record) {
                showAlert('NotFound', 'Unable to load selected mand record.', 'error');
                return;
            }
            currentEditRecord = normalizeRecord(record);
            $('#edit_reinforcer_id').val(currentEditRecord.id);
            $('#edit_reinforcer_name').val(currentEditRecord.reinforcer_name);
            if (editIntroducedPicker) {
                editIntroducedPicker.setDate(currentEditRecord.introduced_at || '', true, 'Y-m-d');
            } else {
                $('#edit_introduced_at').val(currentEditRecord.introduced_at);
            }
            $('#edit_vocal_sign').val(currentEditRecord.vocal_sign);
            $('#edit_description').val(currentEditRecord.description);
            $('#currentMandEditModal').modal('show');
        }

        function resetMediaManagerPlayer() {
            selectedVideoMediaId = 0;
            $('#currentMandManagerVideoSource').attr('src', '');
            const player = document.getElementById('currentMandManagerVideoPlayer');
            if (player) {
                player.pause();
                player.load();
            }
        }

        function resetMediaManagerModal() {
            currentMediaRecord = null;
            mediaReplaceContext = null;
            $('#current_mand_media_title').text('-');
            $('#current_mand_images_gallery, #current_mand_videos_list').empty();
            $('#current_mand_images_count, #current_mand_videos_count').text('0');
            $('#media_upload_images_input, #media_upload_videos_input, #media_replace_image_input, #media_replace_video_input').val('');
            resetMediaManagerPlayer();
            renderMediaRules();
        }

        function renderImagesGallery() {
            const container = $('#current_mand_images_gallery');
            container.empty();
            const images = currentMediaRecord && Array.isArray(currentMediaRecord.images) ? currentMediaRecord.images : [];
            $('#current_mand_images_count').text(images.length);

            if (images.length === 0) {
                container.html('<div class="mand-media-placeholder">No images uploaded.</div>');
                return;
            }

            images.forEach(function(image, index) {
                let actions = '';
                if (canManageMedia) {
                    actions += '<button type="button" class="btn btn-sm btn-outline-warning js-replace-media" data-media-id="' + image.id + '" data-media-type="image" title="Replace image"><i class="ri-refresh-line"></i></button>';
                }
                if (canManageMedia) {
                    actions += '<button type="button" class="btn btn-sm btn-outline-danger js-delete-media" data-media-id="' + image.id + '" data-media-type="image" title="Delete image"><i class="ri-delete-bin-line"></i></button>';
                }

                container.append(
                    '<div class="media-manager-card">' +
                    '<img src="' + escapeHtml(mediaViewUrl(image.id)) + '" class="preview" alt="Image ' + (index + 1) + '">' +
                    '<div class="small text-muted mt-1">Image ' + (index + 1) + '</div>' +
                    '<div class="actions">' + actions + '</div>' +
                    '</div>'
                );
            });
        }

        function renderVideosList() {
            const list = $('#current_mand_videos_list');
            list.empty();
            const videos = currentMediaRecord && Array.isArray(currentMediaRecord.videos) ? currentMediaRecord.videos : [];
            $('#current_mand_videos_count').text(videos.length);

            if (videos.length === 0) {
                list.html('<div class="mand-media-placeholder py-2">No videos uploaded.</div>');
                resetMediaManagerPlayer();
                return;
            }

            if (!selectedVideoMediaId || !videos.some((v) => parseInt(v.id || 0, 10) === selectedVideoMediaId)) {
                selectedVideoMediaId = parseInt(videos[0].id || 0, 10);
                $('#currentMandManagerVideoSource').attr('src', mediaViewUrl(selectedVideoMediaId));
                const player = document.getElementById('currentMandManagerVideoPlayer');
                if (player) {
                    player.load();
                }
            }

            videos.forEach(function(video, index) {
                const active = parseInt(video.id || 0, 10) === selectedVideoMediaId;
                let actions = '';
                if (canManageMedia) {
                    actions += '<button type="button" class="btn btn-sm btn-outline-warning js-replace-media" data-media-id="' + video.id + '" data-media-type="video" title="Replace video"><i class="ri-refresh-line"></i></button>';
                }
                if (canManageMedia) {
                    actions += '<button type="button" class="btn btn-sm btn-outline-danger js-delete-media" data-media-id="' + video.id + '" data-media-type="video" title="Delete video"><i class="ri-delete-bin-line"></i></button>';
                }

                list.append(
                    '<div class="list-group-item ' + (active ? 'active' : '') + '">' +
                    '<div class="media-manager-video-item">' +
                    '<div><button type="button" class="btn btn-sm ' + (active ? 'btn-light' : 'btn-outline-primary') + ' js-play-video" data-media-id="' + video.id + '"><i class="ri-play-circle-line align-bottom me-1"></i>Video ' + (index + 1) + '</button></div>' +
                    '<div class="media-manager-video-actions">' + actions + '</div>' +
                    '</div>' +
                    '</div>'
                );
            });
        }

        function resetImageGalleryModal() {
            currentImageGalleryRecord = null;
            currentImageGallerySelectedId = 0;
            $('#current_mand_image_gallery_title').text('-');
            $('#current_mand_image_gallery_meta').text('');
            $('#current_mand_image_gallery_main').attr('src', '');
            $('#current_mand_image_gallery_thumbs').empty();
        }

        function renderImageGalleryModal() {
            const thumbs = $('#current_mand_image_gallery_thumbs');
            thumbs.empty();

            const images = currentImageGalleryRecord && Array.isArray(currentImageGalleryRecord.images) ? currentImageGalleryRecord.images : [];
            if (images.length === 0) {
                $('#current_mand_image_gallery_main').attr('src', '');
                $('#current_mand_image_gallery_meta').text('No images available');
                thumbs.html('<div class="mand-media-placeholder">No images available.</div>');
                return;
            }

            if (!currentImageGallerySelectedId || !images.some((img) => parseInt(img.id || 0, 10) === currentImageGallerySelectedId)) {
                currentImageGallerySelectedId = parseInt(images[0].id || 0, 10);
            }

            $('#current_mand_image_gallery_main').attr('src', mediaViewUrl(currentImageGallerySelectedId));
            const selectedIndex = images.findIndex((img) => parseInt(img.id || 0, 10) === currentImageGallerySelectedId);
            $('#current_mand_image_gallery_meta').text('Image ' + (selectedIndex + 1) + ' of ' + images.length);

            images.forEach(function(image, index) {
                const isActive = parseInt(image.id || 0, 10) === currentImageGallerySelectedId;
                thumbs.append(
                    '<button type="button" class="image-gallery-thumb-btn ' + (isActive ? 'is-active' : '') + ' js-image-gallery-thumb" data-media-id="' + image.id + '" title="Image ' + (index + 1) + '">' +
                    '<img src="' + escapeHtml(mediaViewUrl(image.id)) + '" class="image-gallery-thumb" alt="Image ' + (index + 1) + '">' +
                    '</button>'
                );
            });
        }

        function openImageGalleryById(id) {
            const row = findRowById(id);
            if (!row) {
                showAlert('NotFound', 'Unable to load selected mand images.', 'error');
                return;
            }

            currentImageGalleryRecord = normalizeRecord(row);
            currentImageGallerySelectedId = 0;
            $('#current_mand_image_gallery_title').text(currentImageGalleryRecord.reinforcer_name || '-');
            renderImageGalleryModal();
            $('#currentMandImageGalleryModal').modal('show');
        }

        function resetVideoGalleryPlayer() {
            currentVideoGallerySelectedId = 0;
            $('#currentMandColumnVideoSource').attr('src', '');
            const player = document.getElementById('currentMandColumnVideoPlayer');
            if (player) {
                player.pause();
                player.load();
            }
        }

        function resetVideoGalleryModal() {
            currentVideoGalleryRecord = null;
            $('#current_mand_video_gallery_title').text('-');
            $('#current_mand_video_gallery_meta').text('');
            $('#current_mand_video_gallery_list').empty();
            resetVideoGalleryPlayer();
        }

        function renderColumnVideoGalleryList() {
            const list = $('#current_mand_video_gallery_list');
            list.empty();

            const videos = currentVideoGalleryRecord && Array.isArray(currentVideoGalleryRecord.videos) ? currentVideoGalleryRecord.videos : [];
            if (videos.length === 0) {
                list.html('<div class="mand-media-placeholder py-2">No videos available.</div>');
                $('#current_mand_video_gallery_meta').text('No videos available');
                resetVideoGalleryPlayer();
                return;
            }

            if (!currentVideoGallerySelectedId || !videos.some((v) => parseInt(v.id || 0, 10) === currentVideoGallerySelectedId)) {
                currentVideoGallerySelectedId = parseInt(videos[0].id || 0, 10);
                $('#currentMandColumnVideoSource').attr('src', mediaViewUrl(currentVideoGallerySelectedId));
                const player = document.getElementById('currentMandColumnVideoPlayer');
                if (player) {
                    player.load();
                }
            }
            const selectedIndex = videos.findIndex((v) => parseInt(v.id || 0, 10) === currentVideoGallerySelectedId);
            $('#current_mand_video_gallery_meta').text('Video ' + (selectedIndex + 1) + ' of ' + videos.length);

            videos.forEach(function(video, index) {
                const active = parseInt(video.id || 0, 10) === currentVideoGallerySelectedId;
                list.append(
                    '<div class="list-group-item ' + (active ? 'active' : '') + '">' +
                    '<button type="button" class="btn btn-sm ' + (active ? 'btn-light' : 'btn-outline-primary') + ' js-column-video-play" data-media-id="' + video.id + '">' +
                    '<i class="ri-play-circle-line align-bottom me-1"></i>Video ' + (index + 1) +
                    '</button>' +
                    '</div>'
                );
            });
        }

        function openVideoGalleryById(id) {
            const row = findRowById(id);
            if (!row) {
                showAlert('NotFound', 'Unable to load selected mand videos.', 'error');
                return;
            }

            currentVideoGalleryRecord = normalizeRecord(row);
            currentVideoGallerySelectedId = 0;
            $('#current_mand_video_gallery_title').text(currentVideoGalleryRecord.reinforcer_name || '-');
            renderColumnVideoGalleryList();
            $('#currentMandVideoGalleryModal').modal('show');
        }

        function renderMediaManager() {
            if (!currentMediaRecord) return;
            $('#current_mand_media_title').text(currentMediaRecord.reinforcer_name || '-');
            renderMediaRules();
            renderImagesGallery();
            renderVideosList();
        }

        function openMediaManagerById(id) {
            const row = findRowById(id);
            if (!row) {
                showAlert('NotFound', 'Unable to load selected mand record.', 'error');
                return;
            }
            currentMediaRecord = normalizeRecord(row);
            selectedVideoMediaId = 0;
            mediaReplaceContext = null;
            renderMediaManager();
            $('#currentMandMediaManagerModal').modal('show');
        }

        function reloadTableAndMediaManager() {
            return loadCurrentMandList().then(function() {
                if (!currentMediaRecord) return;
                const row = findRowById(currentMediaRecord.id);
                if (!row) {
                    $('#currentMandMediaManagerModal').modal('hide');
                    return;
                }
                currentMediaRecord = normalizeRecord(row);
                renderMediaManager();
            });
        }

        function uploadOneMedia(reinforcerId, mediaType, file) {
            return new Promise(function(resolve, reject) {
                const formData = new FormData();
                formData.append('client_reinforcer_id', String(reinforcerId));
                formData.append('media_type', mediaType);
                formData.append('file', file);

                $.ajax({
                    url: mediaUploadUrl,
                    type: 'post',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json'
                }).done(function(response) {
                    if (response.status === 'success') {
                        resolve(response.data || {});
                        return;
                    }
                    reject(response.message || 'Media upload failed.');
                }).fail(function(jqXHR, textStatus, error) {
                    reject('Request failed: ' + textStatus + ' ' + error);
                });
            });
        }

        function uploadMediaBatch(reinforcerId, mediaType, files) {
            const list = Array.from(files || []);
            if (list.length === 0) {
                return Promise.resolve();
            }
            let chain = Promise.resolve();
            list.forEach(function(file) {
                chain = chain.then(function() {
                    return uploadOneMedia(reinforcerId, mediaType, file);
                });
            });
            return chain;
        }

        function replaceMedia(mediaId, mediaType, file) {
            return new Promise(function(resolve, reject) {
                const formData = new FormData();
                formData.append('media_id', String(mediaId));
                formData.append('media_type', mediaType);
                formData.append('file', file);

                $.ajax({
                    url: mediaReplaceUrl,
                    type: 'post',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json'
                }).done(function(response) {
                    if (response.status === 'success') {
                        resolve(response.data || {});
                        return;
                    }
                    reject(response.message || 'Media replace failed.');
                }).fail(function(jqXHR, textStatus, error) {
                    reject('Request failed: ' + textStatus + ' ' + error);
                });
            });
        }

        function deleteMedia(mediaId) {
            return $.ajax({
                url: mediaDeleteUrl,
                type: 'post',
                dataType: 'json',
                data: {
                    media_id: mediaId
                }
            });
        }

        function validateUploadCounts(mediaType, selectedCount) {
            if (!currentMediaRecord) {
                return {
                    ok: false,
                    message: 'Please select a reinforcer first.'
                };
            }
            const imageCount = Array.isArray(currentMediaRecord.images) ? currentMediaRecord.images.length : 0;
            const videoCount = Array.isArray(currentMediaRecord.videos) ? currentMediaRecord.videos.length : 0;

            if (mediaType === 'image' && imageCount + selectedCount > mediaSettings.image_max_count) {
                return {
                    ok: false,
                    message: 'Image limit exceeded. Max ' + mediaSettings.image_max_count + ' images per reinforcer.'
                };
            }
            if (mediaType === 'video' && videoCount + selectedCount > mediaSettings.video_max_count) {
                return {
                    ok: false,
                    message: 'Video limit exceeded. Max ' + mediaSettings.video_max_count + ' videos per reinforcer.'
                };
            }
            return {
                ok: true,
                message: ''
            };
        }

        const addIntroducedPicker = initDateInput('#add_introduced_at');
        const editIntroducedPicker = initDateInput('#edit_introduced_at');

        table = $('#current_mand_list_table').DataTable({
            lengthChange: false,
            autoWidth: false,
            lengthMenu: [
                [10, 25, 50, -1],
                ['10 rows', '25 rows', '50 rows', 'Show all']
            ],
            order: [
                [0, 'asc']
            ],
            pageLength: 10,
            layout: {
                topStart: {
                    buttons: (function() {
                        const buttons = [];
                        if (canCreate) {
                            buttons.push({
                                text: '<i class="ri-add-line align-bottom me-1"></i>Add Mand Target',
                                className: 'btn btn-soft-info waves-effect waves-light material-shadow-none',
                                action: function() {
                                    resetAddModal();
                                    if (addIntroducedPicker) {
                                        addIntroducedPicker.clear();
                                    }
                                    $('#currentMandAddModal').modal('show');
                                }
                            });
                        }
                        buttons.push({
                            extend: 'pageLength',
                            className: 'btn btn-light bg-gradient waves-effect waves-light'
                        });
                        buttons.push({
                            extend: 'copy',
                            className: 'btn btn-light bg-gradient waves-effect waves-light'
                        });
                        buttons.push({
                            extend: 'excel',
                            className: 'btn btn-light bg-gradient waves-effect waves-light'
                        });
                        buttons.push({
                            extend: 'colvis',
                            className: 'btn btn-light bg-gradient waves-effect waves-light'
                        });
                        return buttons;
                    })()
                },
                topEnd: {
                    search: {
                        placeholder: 'Search'
                    }
                }
            },
            columns: buildColumns()
        });

        loadMediaSettings().always(function() {
            loadCurrentMandList();
        });

        $(document).on('click', '.js-open-image-gallery', function() {
            const id = parseInt($(this).data('id') || 0, 10);
            if (!id) return;
            openImageGalleryById(id);
        });

        $(document).on('click', '.js-image-gallery-thumb', function() {
            const mediaId = parseInt($(this).data('media-id') || 0, 10);
            if (!mediaId) return;
            currentImageGallerySelectedId = mediaId;
            renderImageGalleryModal();
        });

        $(document).on('click', '.js-open-video-gallery', function() {
            const id = parseInt($(this).data('id') || 0, 10);
            if (!id) return;
            openVideoGalleryById(id);
        });

        $(document).on('click', '.js-column-video-play', function() {
            currentVideoGallerySelectedId = parseInt($(this).data('media-id') || 0, 10);
            renderColumnVideoGalleryList();
            if (!currentVideoGallerySelectedId) return;
            $('#currentMandColumnVideoSource').attr('src', mediaViewUrl(currentVideoGallerySelectedId));
            const player = document.getElementById('currentMandColumnVideoPlayer');
            if (player) {
                player.load();
            }
        });

        $('#currentMandImageGalleryModal').on('hidden.bs.modal', function() {
            resetImageGalleryModal();
        });

        $('#currentMandVideoGalleryModal').on('hidden.bs.modal', function() {
            resetVideoGalleryModal();
        });

        if (canManageMedia) {
            $(document).on('click', '.js-manage-media', function() {
                openMediaManagerById($(this).data('id'));
            });
        }

        $('#currentMandMediaManagerModal').on('hidden.bs.modal', function() {
            resetMediaManagerModal();
        });

        $(document).on('click', '.js-play-video', function() {
            selectedVideoMediaId = parseInt($(this).data('media-id') || 0, 10);
            renderVideosList();
            if (selectedVideoMediaId) {
                $('#currentMandManagerVideoSource').attr('src', mediaViewUrl(selectedVideoMediaId));
                const player = document.getElementById('currentMandManagerVideoPlayer');
                if (player) {
                    player.load();
                }
            }
        });

        if (canManageMedia) {
            $('#btn_upload_images').on('click', function() {
                $('#media_upload_images_input').trigger('click');
            });

            $('#btn_upload_videos').on('click', function() {
                $('#media_upload_videos_input').trigger('click');
            });

            $('#media_upload_images_input').on('change', function() {
                const files = this.files || [];
                if (!currentMediaRecord || files.length === 0) return;
                const check = validateUploadCounts('image', files.length);
                if (!check.ok) {
                    showAlert('Validation_Error', check.message, 'error');
                    $(this).val('');
                    return;
                }
                const btn = $('#btn_upload_images');
                setActionButtonLoading(btn, 'Uploading...');
                uploadMediaBatch(currentMediaRecord.id, 'image', files)
                    .then(function() {
                        $('#media_upload_images_input').val('');
                        return reloadTableAndMediaManager();
                    })
                    .catch(function(message) {
                        showAlert('Validation_Error', message || 'Unable to upload images.', 'error');
                    })
                    .finally(function() {
                        resetActionButtonLoading(btn);
                    });
            });

            $('#media_upload_videos_input').on('change', function() {
                const files = this.files || [];
                if (!currentMediaRecord || files.length === 0) return;
                const check = validateUploadCounts('video', files.length);
                if (!check.ok) {
                    showAlert('Validation_Error', check.message, 'error');
                    $(this).val('');
                    return;
                }
                const btn = $('#btn_upload_videos');
                setActionButtonLoading(btn, 'Uploading...');
                uploadMediaBatch(currentMediaRecord.id, 'video', files)
                    .then(function() {
                        $('#media_upload_videos_input').val('');
                        return reloadTableAndMediaManager();
                    })
                    .catch(function(message) {
                        showAlert('Validation_Error', message || 'Unable to upload videos.', 'error');
                    })
                    .finally(function() {
                        resetActionButtonLoading(btn);
                    });
            });

            $(document).on('click', '.js-replace-media', function() {
                mediaReplaceContext = {
                    mediaId: parseInt($(this).data('media-id') || 0, 10),
                    mediaType: String($(this).data('media-type') || '').toLowerCase()
                };
                if (!mediaReplaceContext.mediaId || !mediaReplaceContext.mediaType) return;
                if (mediaReplaceContext.mediaType === 'image') {
                    $('#media_replace_image_input').trigger('click');
                } else {
                    $('#media_replace_video_input').trigger('click');
                }
            });

            $('#media_replace_image_input').on('change', function() {
                const file = this.files && this.files[0] ? this.files[0] : null;
                if (!file || !mediaReplaceContext || mediaReplaceContext.mediaType !== 'image') return;
                replaceMedia(mediaReplaceContext.mediaId, 'image', file)
                    .then(function() {
                        $('#media_replace_image_input').val('');
                        mediaReplaceContext = null;
                        return reloadTableAndMediaManager();
                    })
                    .catch(function(message) {
                        showAlert('Validation_Error', message || 'Unable to replace image.', 'error');
                    });
            });

            $('#media_replace_video_input').on('change', function() {
                const file = this.files && this.files[0] ? this.files[0] : null;
                if (!file || !mediaReplaceContext || mediaReplaceContext.mediaType !== 'video') return;
                replaceMedia(mediaReplaceContext.mediaId, 'video', file)
                    .then(function() {
                        $('#media_replace_video_input').val('');
                        mediaReplaceContext = null;
                        return reloadTableAndMediaManager();
                    })
                    .catch(function(message) {
                        showAlert('Validation_Error', message || 'Unable to replace video.', 'error');
                    });
            });
        }

        if (canManageMedia) {
            $(document).on('click', '.js-delete-media', function() {
                const mediaId = parseInt($(this).data('media-id') || 0, 10);
                if (mediaId <= 0) return;
                Swal.fire({
                    title: "Are you sure?",
                    text: "This media will be permanently deleted.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    confirmButtonClass: 'btn btn-primary w-xs me-2 mt-2',
                    cancelButtonClass: 'btn btn-danger w-xs me-2 mt-2',
                    buttonsStyling: false
                }).then((result) => {
                    if (!result.isConfirmed) return;
                    deleteMedia(mediaId).done(function(response) {
                        if (response.status !== 'success') {
                            showAlert(response.statusText || 'Error', response.message || 'Unable to delete media.', response.status || 'error');
                            return;
                        }
                        reloadTableAndMediaManager();
                    }).fail(function(jqXHR, textStatus, error) {
                        showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
                    });
                });
            });
        }

        if (canCreate) {
            $('#currentMandAddModal').on('hidden.bs.modal', function() {
                resetAddModal();
                if (addIntroducedPicker) addIntroducedPicker.clear();
            });

            $('#btn_add_mand_save').on('click', function() {
                const btn = $(this);
                const payload = {
                    reinforcer_name: $('#add_reinforcer_name').val(),
                    introduced_at: $('#add_introduced_at').val(),
                    vocal_sign: $('#add_vocal_sign').val(),
                    description: $('#add_description').val(),
                };
                setActionButtonLoading(btn, 'Saving...');
                $.ajax({
                    url: createUrl,
                    type: 'post',
                    dataType: 'json',
                    data: payload
                }).done(function(response) {
                    if (response.status !== 'success') {
                        if (response.status === 'error' && response.statusText === 'Validation_Error') {
                            displayValidationErrors(Object.values(response.validationErrors || {}));
                            return;
                        }
                        showAlert(response.statusText, response.message, response.status || 'error');
                        return;
                    }
                    $('#currentMandAddModal').modal('hide');
                    showAlert(response.statusText, response.message, response.status);
                    loadCurrentMandList();
                }).fail(function(jqXHR, textStatus, error) {
                    showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
                }).always(function() {
                    resetActionButtonLoading(btn);
                });
            });
        }

        if (canUpdate) {
            $(document).on('click', '.js-edit-mand', function() {
                openEditModalById($(this).data('id'));
            });

            $('#currentMandEditModal').on('hidden.bs.modal', function() {
                resetEditModal();
                if (editIntroducedPicker) editIntroducedPicker.clear();
            });

            $('#btn_edit_mand_save').on('click', function() {
                if (!currentEditRecord) {
                    showAlert('Error', 'No record selected.', 'error');
                    return;
                }
                const btn = $(this);
                const payload = {
                    id: $('#edit_reinforcer_id').val(),
                    reinforcer_name: $('#edit_reinforcer_name').val(),
                    introduced_at: $('#edit_introduced_at').val(),
                    vocal_sign: $('#edit_vocal_sign').val(),
                    description: $('#edit_description').val(),
                };
                setActionButtonLoading(btn, 'Updating...');
                $.ajax({
                    url: updateUrl,
                    type: 'post',
                    dataType: 'json',
                    data: payload
                }).done(function(response) {
                    if (response.status !== 'success') {
                        if (response.status === 'error' && response.statusText === 'Validation_Error') {
                            displayValidationErrors(Object.values(response.validationErrors || {}));
                            return;
                        }
                        showAlert(response.statusText, response.message, response.status || 'error');
                        return;
                    }
                    $('#currentMandEditModal').modal('hide');
                    showAlert(response.statusText, response.message, response.status);
                    loadCurrentMandList();
                }).fail(function(jqXHR, textStatus, error) {
                    showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
                }).always(function() {
                    resetActionButtonLoading(btn);
                });
            });
        }

        if (canDelete) {
            $(document).on('click', '.js-delete-mand', function() {
                const id = parseInt($(this).data('id') || 0, 10);
                if (id <= 0) return;
                Swal.fire({
                    title: "Are you sure?",
                    text: "This item and all linked media will be permanently deleted.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    confirmButtonClass: 'btn btn-primary w-xs me-2 mt-2',
                    cancelButtonClass: 'btn btn-danger w-xs me-2 mt-2',
                    buttonsStyling: false
                }).then((result) => {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: deleteUrl,
                        type: 'post',
                        dataType: 'json',
                        data: {
                            id: id
                        }
                    }).done(function(response) {
                        if (response.status !== 'success') {
                            showAlert(response.statusText, response.message, response.status || 'error');
                            return;
                        }
                        showAlert(response.statusText, response.message, response.status);
                        loadCurrentMandList();
                    }).fail(function(jqXHR, textStatus, error) {
                        showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
                    });
                });
            });
        }
    })();
</script>
<?= $this->endSection() ?>
