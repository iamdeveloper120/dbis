<!-- View (Mands data sheet) -->

<div class="table-responsive">
    <div id="client_mands_session_data">
        <table class="table table-bordered dt-nowrap nowrap" style="width: 100%;" id="client_mands_session_data_dataTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Reinforcer</th>
                    <th>Prompt Level</th>
                    <th>Utterance</th>
                    <th>Mand Error</th>
                    <th>Peer Manding</th>
                    <th>Eye Contact</th>
                    <th>Initial Attempt</th>
                    <th>Probe</th>
                    <th>Prompt Delay</th>
                    <th>Probe</th>
                    <th>Echoic 1</th>
                    <th>Probe</th>
                    <th>Echoic 2</th>
                    <th>Probe</th>
                    <th>Echoic 3</th>
                    <th>Probe</th>
                    <th>Comparison Prompt Delay</th>
                    <th>Comparison Echoic Trial</th>

                </tr>
            </thead>
            <tbody>
                <?php foreach ($mandsData as $mandId => $mand) : ?>
                    <tr>
                        <td><?= $mandId + 1; ?> </td>
                        <td><?= $mand->reinforcer_input; ?> </td>
                        <td><span data-bs-toggle="tooltip" data-bs-placement="top" title="<?= esc($mand->get_prompt_level_tooltip()); ?>"><?= esc($mand->get_prompt_level_label()); ?></span></td>
                        <td><?= $mand->utterance_input ?? ''; ?> </td>
                        <td>
                            <?php if ($mand->mands_error != 1): ?>
                                <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?= esc($mand->get_mand_error_tooltip()); ?>"><?= esc($mand->get_mand_error_label()); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= $mand->get_peer_manding_label(); ?> </td>
                        <td><?= $mand->get_eye_contact_label(); ?> </td>
                        <td><?= $mand->initial_attempt_input; ?> </td>
                        <td>
                            <?php if ($mand->initial_attempt != 1): ?>
                                <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?= esc($mand->get_initial_input_response_tooltip()); ?>"><?= esc($mand->get_initial_input_response_label()); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= $mand->prompt_delay_input; ?> </td>
                        <td>
                            <?php if ($mand->prompt_delay != 1): ?>
                                <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?= esc($mand->get_prompt_delay_response_tooltip()); ?>"><?= esc($mand->get_prompt_delay_response_label()); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= $mand->echoic_1_input; ?> </td>
                        <td>
                            <?php if ($mand->echoic_1 != 1): ?>
                                <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?= esc($mand->get_echoic_1_response_tooltip()); ?>"><?= esc($mand->get_echoic_1_response_label()); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= $mand->echoic_2_input; ?> </td>
                        <td>
                            <?php if ($mand->echoic_2 != 1): ?>
                                <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?= esc($mand->get_echoic_2_response_tooltip()); ?>"><?= esc($mand->get_echoic_2_response_label()); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= $mand->echoic_3_input; ?> </td>
                        <td>
                            <?php if ($mand->echoic_3 != 1): ?>
                                <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?= esc($mand->get_echoic_3_response_tooltip()); ?>"><?= esc($mand->get_echoic_3_response_label()); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= $mand->get_prompt_delay_comparison_label(); ?> </td>
                        <td><?= $mand->get_echoic_comparison_label(); ?> </td>
                    </tr>

                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>



<script>
    $(document).ready(function() {
        $('select').select2();
        /***************************************************************************************** */
        var csrfToken = "<?= csrf_hash() ?>";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        });

        function initMandsTooltips() {
            if (typeof bootstrap === 'undefined' || typeof bootstrap.Tooltip === 'undefined') {
                return;
            }
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
                if (!bootstrap.Tooltip.getInstance(el)) {
                    new bootstrap.Tooltip(el, {
                        trigger: 'hover focus'
                    });
                }
            });
        }
        initMandsTooltips();
        /***************************************************************************************** */
        var mandsDailyDataTable = $('#client_mands_session_data_dataTable').DataTable({
            response: false,
            ordering: false,
            lengthChange: false,
            lengthMenu: [
                [10, 25, 50, -1],
                ['10 rows', '25 rows', '50 rows', 'Show all']
            ],
            layout: {
                topStart: {
                    buttons: [{
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

        });

        /***************************************************************************** */

    });



    /***************************************************************************** */
</script>
