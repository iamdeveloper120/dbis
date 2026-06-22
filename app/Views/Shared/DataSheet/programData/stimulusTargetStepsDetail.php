  <?php if (!empty($chainLabel)): ?>
    <div class="mb-2">
        <?= $chainLabel ?>
    </div>
<?php endif; ?>
  <table class="table table-bordered">
      <thead class="table-light text-center align-middle">
          <tr>
              <th>Step #</th>
              <th class="text-start">SD / C</th>
              <th class="text-start">Response</th>
              <?php foreach ($sessionDates as $date): ?>
                  <th><?= app_date($date) ?></th>
              <?php endforeach; ?>
          </tr>
      </thead>
      <tbody>
          <?php foreach ($steps as $step): ?>
              <tr>
                  <td class="text-center"><?= esc($step->step_number) ?></td>
                  <td>
                      <b>S:</b> <?= esc($step->sd_text) ?><br>
                      <b>C:</b> <?= esc($step->c_text) ?>
                  </td>
                  <td><?= esc($step->response_text) ?></td>

                  <?php foreach ($sessionDates as $date): ?>
                      <td class="text-center">
                          <?php
                            //$values = $matrix[$step->id][$date] ?? []; 
                            //echo $values ? implode(', ', $values) : '-';
                            $values = $matrix[$step->id][$date] ?? [];

                            $filtered = array_filter($values, fn($v) => trim((string)$v) !== '');

                            if (empty($filtered)) {
                                echo '<i class="ri-forbid-line" style="color: #f1c40f;" title="No valid data"></i>';
                            } else {
                                echo implode(', ', $filtered);
                            }
                            ?>
                      </td>
                  <?php endforeach; ?>
              </tr>
          <?php endforeach; ?>
      </tbody>
  </table>