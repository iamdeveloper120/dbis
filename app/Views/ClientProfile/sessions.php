<?= $this->extend("layout/master-profile") ?>
<?= $this->section("head_tag") ?>
<style>
    table.dataTable tr.table-danger>* {
        background-color: #f8d7da !important;
    }

    table.dataTable tr.table-success>* {
        background-color: #d1e7dd !important;
    }

    .file-manager-content-scroll {
        padding-bottom: 150px !important;
        min-height: calc(100vh - 150px);
        box-sizing: border-box;
    }
</style>
<?= $this->endSection() ?>
<?= $this->section("page_content") ?>
<div class="mx-n3 pt-2 px-2 file-manager-content-scroll">
    <div class="table-responsive">
        <table id="dashboard_session_table" class="table table-bordered table-hover" style="width: 100%;">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Instructor</th>
                    <th>QR</th>
                    <th>PB Duration</th>
                    <th>Mands</th>
                    <th>Wow Moment</th>
                    <th>Comment</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sessions as $s): ?>
                    <?php
                    $wow = esc($s['wow_moments']);
                    $com = esc($s['instructor_comments']);
                    $wow_short = strlen($wow) > 60 ? substr($wow, 0, 60) . '…' : $wow;
                    $com_short = strlen($com) > 60 ? substr($com, 0, 60) . '…' : $com;
                    ?>
                    <tr class="<?= $s['qr'] == 1 ? '' : ($s['qr'] == 3 ? '' : '') ?>">
                        <td class="dt-nowrap" data-order="<?= strtotime($s['session_date_raw'] ?? $s['session_date']) ?>">
                            <?= esc($s['session_date']); ?>
                        </td>
                        <td class="dt-nowrap"><?= esc($s['start_time']); ?><br><?= esc($s['end_time']); ?></td>
                        <td class="dt-nowrap"><?= esc($s['instructor_name']); ?></td>
                        <td class="dt-nowrap">
                            <?php if ($s['qr'] == 1): ?>
                                <span class="badge bg-danger">Poor</span>
                            <?php elseif ($s['qr'] == 2): ?>
                                <span class="badge bg-warning text-dark">Good</span>
                            <?php else: ?>
                                <span class="badge bg-success">Excellent</span>
                            <?php endif; ?>
                            <span class="d-none">(<?= esc($s['qr']); ?>)</span>
                        </td>
                        <td class="dt-nowrap <?= $s['pb_duration_sec'] > 600 ? 'text-danger fw-bold' : '' ?>">
                            <?= esc($s['pb_duration_formatted']); ?>
                        </td>
                        <td class="dt-nowrap">
                            <?= esc($s['mands_total']); ?> (<?= esc($s['mands_variety']); ?> var., <?= esc($s['mands_freq']); ?>/min)
                        </td>
                        <td data-full-text="<?= esc($wow); ?>">
                            <?= $wow_short ?>
                            <?php if (strlen($wow) > 60): ?>
                                <br>
                                <a href="#" class="readMore text-success">Read more</a>
                            <?php endif; ?>
                        </td>
                        <td data-full-text="<?= esc($com); ?>">
                            <?= $com_short ?>
                            <?php if (strlen($com) > 60): ?>
                                <br>
                                <a href="#" class="readMore text-success">Read more</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>

        </table>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section("page_modal") ?>
<div class="modal fade" id="full_comment_modal" tabindex="-1" aria-hidden="true">>
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="nosession_wd_modal_title">Comment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                </button>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <div class="hstack gap-2 justify-content-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="ri-close-line align-bottom me-1"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>


<?= $this->section("page_js") ?>
<script>
    $(document).ready(function() {
        function exportFormatter(data, row, column, node) {

            // 1. Wow Moment / Comment
            const full = $(node).data('full-text');
            if (full) {
                return full;
            }

            // 2. Time column (index 1)
            if (column === 1) {
                return $(node).html()
                    .replace(/<br\s*\/?>/gi, ' ')
                    .trim();
            }

            // 3. QR column (index 3)
            if (column === 3) {
                return $(node).find('.badge').text().trim();
            }

            // 4. Default: strip HTML
            return $('<div>').html(data).text().trim();
        }

        /***************************************************************************************** */
        var csrfToken = "<?= csrf_hash() ?>";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        });
        /***************************************************************************************** */
        $('#dashboard_session_table').DataTable({

            lengthChange: false,
            ordering: true,
            lengthMenu: [
                [5,10, 25, 50, -1],
                ['5 rows', '10 rows', '25 rows', '50 rows', 'Show all']
            ],
            layout: {
                topStart: {
                    buttons: [{
                            extend: 'pageLength',
                            className: 'btn btn-light bg-gradient waves-effect waves-light'
                        },
                        {
                            extend: 'copy',
                            className: 'btn btn-light bg-gradient waves-effect waves-light',
                            exportOptions: {
                                format: {
                                    body: exportFormatter
                                }
                            }
                        },
                        {
                            extend: 'excel',
                            className: 'btn btn-light bg-gradient waves-effect waves-light',
                            exportOptions: {
                                format: {
                                    body: exportFormatter
                                }
                            }
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
                [0, 'desc']
            ],

        });

        $('#dashboard_session_table').on('click', '.readMore', function(e) {
            e.preventDefault();
            const $cell = $(this).closest('td');
            const fullText = $cell.data('full-text');
            $('#full_comment_modal .modal-body').html(fullText);
            $('#full_comment_modal').modal('show');
        });



    });
</script>

<?= $this->endSection() ?>