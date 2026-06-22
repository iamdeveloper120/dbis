  <div id="view-identifier" data-view="percentage_probe_yes_no"></div>
  <div class="row mb-4">
      <div class="col-sm order-3 order-sm-2 mt-3 mt-sm-0">
          <h5 class="fw-semibold mb-0">Program Target List for "<span class="text-primary fw-medium fst-italic">[<?= $domain->domain_code; ?>][<?= $goal->goal_code; ?>]</span> "</h5>
      </div>
      <div class="col-auto order-2 order-sm-3 ms-auto">
          <div class="hstack gap-2">
              <div class="btn-group" role="group" aria-label="Basic example">
                  <a class="btn btn-icon fw-semibold btn-soft-info  " href="#targetSlider" role="button" data-bs-slide="prev"><i class="ri-arrow-left-line"></i></a>&nbsp;
                  <a class="btn btn-icon fw-semibold btn-soft-info  " href="#targetSlider" role="button" data-bs-slide="next"><i class="ri-arrow-right-line"></i></a>
              </div>
          </div>
      </div>
  </div>
  <div class="row mb-4">
      <div class="col-md-12">
          <!-- Target list will be rendered here -->
          <div class="">
              <div id="targetSlider" class="carousel slide" data-bs-interval="false">
                  <div class="carousel-inner" role="listbox">
                      <?php
                        if (empty($targets))
                            echo "There are no targets scheduled for this goal today.";
                        ?>
                      <?php $first = 0;
                        foreach ($targets as $target) : ?>
                          <?php
                            $activation_days = $target['additional_data']['current_rule']['activation_days'] ?? null;
                            $last_session_date = $target['last_session_date'] ?? null;

                            // Only proceed with the date comparison if both variables are set (not null)
                            if ($activation_days !== null && $last_session_date !== null) {
                                $days_since_last_session = getDaysDifference($last_session_date);

                                // Skip if the number of days since the last session is less than the activation days
                                if ($days_since_last_session < $activation_days) {
                                    continue;
                                }
                            }
                            $first++;
                            ?>

                          <div class="card border carousel-item <?= $first == 1 ? 'active' : '' ?> ">
                              <div class="card-header">
                                  <h6 class="card-title mb-0"><?= $target['target_name']; ?>
                                      <span class="badge bg-primary-subtle text-primary float-end"><?= $target['phase_name'] ?></span>
                                  </h6>
                              </div>
                              <div class="card-body">
                                  <p class="card-text">
                                  <div class="row">
                                      <?= $target['input_html'] ?> <!-- Rendered Inputs from Controller -->

                                  </div>
                                  </p>
                              </div>
                              <div class="card-footer">
                                  <div class="row">
                                      <div class="col-sm-12 col-md-10 col-lg-10 text-start">
                                      </div>
                                      <div class="col-sm-12 col-md-2 col-lg-2 text-end">
                                          <button
                                              data-client-id="<?= $client_id; ?>"
                                              data-session-id="<?= $session_id; ?>"
                                              data-domain-id="<?= $domain->id; ?>"
                                              data-goal-id="<?= $goal->id; ?>"
                                              data-target-id="<?= $target['target_id']; ?>"
                                              data-probe-set-id="<?= $target['client_probe_set_id']; ?>"
                                              data-current-phase-id="<?= $target['current_phase_id']; ?>"
                                              class="btn btn-primary save-button-percentage-yes-no"><i class="ri-save-2-line"></i> Save</button>
                                      </div>
                                  </div>
                              </div>
                              <div class="card-footer">
                                  <div class="row">
                                      <div class="col-sm-12 col-md-12 col-lg-12">
                                          <!-- Accordions Fill Colored -->
                                          <div class="accordion custom-accordionwithicon" id="accordionFill">
                                              <div class="accordion-item">
                                                  <h2 class="accordion-header" id="accordionFillExample1">
                                                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accor_fill1" aria-expanded="false" aria-controls="accor_fill1">
                                                          Collected Trial Data
                                                      </button>
                                                  </h2>
                                                  <div id="accor_fill1" class="accordion-collapse collapse" aria-labelledby="accordionFillExample1" data-bs-parent="#accordionFill">
                                                      <div class="accordion-body">
                                                          <ul class="list-group">
                                                              <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                  <h5 class="">Trial</h5>
                                                                  <h5 class="">Response</h5>
                                                              </li>
                                                          </ul>
                                                          <ul class="list-group  transition-list" id="transition_list_<?= $target['target_id'] ?>">

                                                              <?php if (isset($target['existingEntry'])): ?>
                                                                  <?php
                                                                    $collectedData = json_decode($target['existingEntry']->collected_data, true);
                                                                    $transitions = $collectedData['transitions'] ?? [];
                                                                    ?>
                                                                  <?php foreach ($transitions as $item): ?>
                                                                      <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                          <span><?= esc($item['transition']) ?></span>
                                                                          <span class="text-muted small"><?= esc($item['answer']) ?></span>
                                                                      </li>
                                                                  <?php endforeach; ?>
                                                              <?php endif; ?>
                                                          </ul>
                                                      </div>
                                                  </div>
                                              </div>

                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      <?php endforeach; ?>

                  </div>

              </div>

          </div>
      </div>
  </div>
  <!-- Spinner container -->
  <div id="spinner-container" class="position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center d-none" style="z-index: 9999; background-color: rgba(0, 0, 0, 0.5); display: none;">
      <div class="spinner-border text-primary" role="status">
          <span class="sr-only">Loading...</span>
      </div>
  </div>