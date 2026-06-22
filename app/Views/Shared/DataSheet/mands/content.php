  <div class="card border">
       <div id="client_mands_area">
              <table class="table table-bordered nowrap fixed-columns-table" style="width: 100%;" id="mands_dataTable">
                  <thead>
                      <tr>
                          <th class="dt-nowrap">Date</th>
                          <th>Total Mands</th>
                          <th>Total Peer Mands</th>
                          <th>Total Eye Contact Mands</th>
                          <th>Duration</th>
                          <th>Frequency/M</th>
                          <th>Variety</th>
                          <th>Total FPP</th>
                          <th>Total PPP</th>
                          <th>Total GP</th>
                          <th>Total V</th>
                          <th>Total IV</th>
                          <th>Total Item</th>
                          <th>Total Mo</th>
                          <th>Total TMO</th>
                          <th>Total Mand Errors</th>
                          <th>Total S</th>
                          <th>Total R</th>
                          <th>Total IA</th>
                          <th>% S</th>
                          <th>% R</th>
                          <th>% IA</th>
                          <th>Total Initial attempts</th>
                          <th>% SS</th>
                          <th>% WA</th>
                          <th>% IW</th>
                          <th>% AF</th>
                          <th>Total Prompt Delay Trials</th>
                          <th>% No Change</th>
                          <th>% Improved</th>
                          <th>% Deteriorated</th>
                          <th>Total Echoic Trials</th>
                          <th>% No Change</th>
                          <th>% Improved</th>
                          <th>% Deteriorated</th>
                          <th>Mands Daily Data</th>


                      </tr>
                  </thead>
                  <tbody>
                      <?php foreach ($mandsSummaryData as $row) : ?>
                          <tr>
                              <td class="dt-nowrap"><?= app_date($row->session_date); ?> </td>
                              <td><?= $row->total_mands; ?> </td>
                              <td><?= $row->total_peer_mands; ?> </td>
                              <td><?= $row->total_eye_contact_mands; ?> </td>
                              <td><?= $row->total_duration != '0.00' ? convertDecimalToTime($row->total_duration) : ''; ?> </td>
                              <td><?= $row->frequency_of_mands_per_minute ?> </td>
                              <td><?= $row->variety_of_mands; ?> </td>
                              <td><?= $row->total_FPP_mands; ?> </td>
                              <td><?= $row->total_PPP_mands; ?> </td>
                              <td><?= $row->total_GP_mands; ?> </td>
                              <td><?= $row->total_V_mands; ?> </td>
                              <td><?= $row->total_IV_mands; ?> </td>
                              <td><?= $row->total_Item_mands; ?> </td>
                              <td><?= $row->total_MO_mands; ?> </td>
                              <td><?= $row->total_TMO_mands; ?> </td>
                              <td><?= $row->total_mands_with_errors; ?> </td>
                              <td><?= $row->total_mands_errors_s; ?> </td>
                              <td><?= $row->total_mands_errors_r; ?> </td>
                              <td><?= $row->total_mands_errors_ia; ?> </td>
                              <td><?= $row->percentage_of_scrolled_mands; ?> </td>
                              <td><?= $row->percentage_of_repetitive_mands; ?> </td>
                              <td><?= $row->percentage_of_inappropriate_autoclitics; ?> </td>
                              <td><?= $row->total_mands_with_initial_attempts; ?> </td>
                              <td><?= $row->percentage_of_SS_attempts; ?> </td>
                              <td><?= $row->percentage_of_WA_attempts; ?> </td>
                              <td><?= $row->percentage_of_IW_attempts; ?> </td>
                              <td><?= $row->percentage_of_AF_attempts; ?> </td>
                              <td><?= $row->total_trials_with_prompt_delay; ?> </td>
                              <td><?= $row->percentage_of_remained_with_prompt_delay; ?> </td>
                              <td><?= $row->percentage_of_improved_with_prompt_delay; ?> </td>
                              <td><?= $row->percentage_of_worsened_with_prompt_delay; ?> </td>
                              <td><?= $row->total_trials_with_echoic_trials; ?> </td>
                              <td><?= $row->percentage_of_remained_with_echoic_trials; ?> </td>
                              <td><?= $row->percentage_of_improved_with_echoic_trials; ?> </td>
                              <td><?= $row->percentage_of_worsened_with_echoic_trials; ?> </td>
                              <td>
                                  <button type="button" class="btn btn-sm btn-outline-primary btn-icon waves-effect waves-light material-shadow-none view_mands_session_data" data-client-id="<?= $row->client_id; ?> " data-session-date="<?= $row->session_date; ?> "><i class="ri-eye-fill"></i></button>
                              </td>
                          </tr>
                      <?php endforeach; ?>
                  </tbody>
              </table>
          </div>
  </div>
