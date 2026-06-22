<div class="row">
    <div class="col-lg-12">
        <div class="card" id="">
            <div class="card-header border-bottom-dashed">
                <div class="row g-4 align-items-center">
                    <div class="col-sm">
                        <div>
                            <h5 class="card-title mb-0">Client's Rate Data by Month</h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body border-bottom-dashed border-bottom">
                <div class="row">
                    <div class="col-md-12">

                        <div class="table-responsive">
                            <table id="kpi_clients_target_and_rate_table" class="table table-striped-columns nowrap align-middle fixed-columns-table" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <?php foreach ($months as $month) :
                                            $formattedMonth = new \DateTime($month);
                                            $formattedMonth = $formattedMonth->format("M-Y");
                                        ?>
                                            <th style="text-align: center;"><?= $formattedMonth ?></th>
                                        <?php endforeach; ?>
                                        <th>Percentage</th> <!-- New column for percentage -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clients as $client => $clientData) : ?>
                                        <tr>
                                            <td><?= $client ?></td>

                                            <?php foreach ($months as $month) : ?>
                                                <?php
                                                $data = isset($clientData['data'][$month]) ? $clientData['data'][$month] : null;
                                                $skillRate = isset($data['skill_rate']) ? $data['skill_rate'] : '-';
                                                $doiRate = isset($data['doi_rate']) ? $data['doi_rate'] : '-';
                                                $targetStatus = isset($data['target_status']) ? $data['target_status'] : '';
                                                ?>
                                                <?php if ($targetStatus == '1') { ?>
                                                    <td class="text-success" style="text-align: center;">
                                                        <span class="d-none">1</span>
                                                        <div class="btn-group d-print-none">
                                                            <i class="ri-checkbox-circle-line fs-17" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> </i>
                                                            <div class="dropdown-menu dropdown-menu-md p-3">
                                                                <p class="mb-0 d-print-none">
                                                                    Skills Target:<?= $clientData['skills_target_rate'] ?><br>
                                                                    Monthly Skills Rate: <?= $skillRate ?><br>
                                                                    <hr>
                                                                    DOI Target:<?= $clientData['doi_target_rate'] ?><br>
                                                                    Monthly DOI Rate: <?= $doiRate ?>


                                                                </p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                <?php
                                                } elseif ($targetStatus == '0') { ?>
                                                    <td class="text-danger" style="text-align: center;">
                                                        <span class="d-none">0</span>
                                                        <div class="btn-group d-print-none">
                                                            <i class="ri-close-circle-line fs-17" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> </i>
                                                            <div class="dropdown-menu dropdown-menu-md p-3">
                                                                <p class="mb-0 d-print-none">
                                                                    Skills Target:<?= $clientData['skills_target_rate'] ?><br>
                                                                    Monthly Skills Rate: <?= $skillRate ?><br>
                                                                    <hr>
                                                                    DOI Target:<?= $clientData['doi_target_rate'] ?><br>
                                                                    Monthly DOI Rate: <?= $doiRate ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                <?php

                                                } else {
                                                    echo '<td></td>';
                                                }
                                                ?>

                                            <?php endforeach; ?>
                                            <?php
                                            $clientTargetMetCount = 0; // Counter for client target met
                                            $clientTargetNotMetCount = 0; // Counter for client target not met

                                            foreach ($months as $month) {
                                                $data = isset($clientData['data'][$month]) ? $clientData['data'][$month] : null;
                                                $targetStatus = isset($data['target_status']) ? $data['target_status'] : null;

                                                if ($targetStatus === '1') {
                                                    $clientTargetMetCount++;
                                                } elseif ($targetStatus == '0') {
                                                    $clientTargetNotMetCount++;
                                                }
                                            }

                                            $denominator = $clientTargetMetCount + $clientTargetNotMetCount;

                                            $percentage = ($denominator > 0) ? $clientTargetMetCount / $denominator * 100 : 0;
                                            ?>
                                            <td><?= round($percentage, 2) ?>%</td>
                                        </tr>
                                    <?php endforeach; ?>

                                </tbody>
                                <tfoot>
                                    <tr> <!-- Last row with percentage calculations -->
                                        <td>Percentage</td>
                                        <?php foreach ($months as $month) : ?>
                                            <?php
                                            $clientTargetMetCount = 0; // Counter for client target met
                                            $clientTargetNotMetCount = 0; // Counter for client target not met

                                            foreach ($clients as $clientData) {
                                                $data = isset($clientData['data'][$month]) ? $clientData['data'][$month] : null;
                                                $targetStatus = isset($data['target_status']) ? $data['target_status'] : null;

                                                if ($targetStatus == '1') {
                                                    $clientTargetMetCount++;
                                                } elseif ($targetStatus == '0') {
                                                    $clientTargetNotMetCount++;
                                                }
                                            }

                                            $denominator = $clientTargetMetCount + $clientTargetNotMetCount;
                                            $percentage = ($denominator > 0) ? ($clientTargetMetCount / $denominator) * 100 : 0;
                                            ?>
                                            <td><?= round($percentage, 2) ?>%</td>
                                        <?php endforeach; ?>
                                        <td></td> <!-- Empty cell for the last column in the last row -->
                                    </tr>
                                    <tr> <!-- Last row with percentage calculations -->
                                        <td>Months</td>
                                        <?php foreach ($months as $month) :
                                            $formattedMonth = new \DateTime($month);
                                            $formattedMonth = $formattedMonth->format("M-Y");
                                        ?>

                                            <td><?= $formattedMonth ?></td>
                                        <?php endforeach; ?>
                                        <td></td> <!-- Empty cell for the last column in the last row -->
                                    </tr>
                                </tfoot>
                            </table>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        var table = $('#kpi_clients_target_and_rate_table').DataTable({
            scrollY: '400px',
            scrollX: true,
            paging: false,
            response: false,
            ordering: false,
            lengthChange: false,
            fixedColumns: {
                start: 1,
            },
            layout: {
                topStart: {
                    buttons: [{
                        className: 'btn btn-light bg-gradient waves-effect waves-light',
                        extend: 'excelHtml5',
                        exportOptions: {
                            format: {
                                body: function(data, row, column, node) {
                                    if ($(node).find('span').length) {
                                        return $(node).find('span').text();
                                    } else {
                                        return data;
                                    }
                                }
                            }
                        },
                        footer: true
                    }]
                },
                topEnd: {
                    search: {
                        placeholder: 'Search',

                    },

                }
            },
        });

    });
</script>