  <div class="border-bottom-dashed border-bottom" style="padding-top: 0px; padding-bottom:10px; margin-bottom:20px">
      <form>
          <div class="row g-3">
              <div class="col-xxl-5 col-sm-12">
                  <div>
                      <select class="form-control" name="choices-single-default" id="sDomain">
                          <option value="" selected>All Domains</option>
                          <?php
                            foreach ($domains as $domain) {  ?>
                              <option value="<?php echo $domain->id; ?>">
                                  <?php echo $domain->name . ' ( ' . $domain->domain_code . ' )  '; ?></option>
                          <?php } ?>

                      </select>
                  </div>
              </div>
              <!--end col-->
              <div class="col-xxl-5 col-sm-12">
                  <div>
                      <select class="form-control" name="choices-single-default" id="sGoal">
                          <option value="" selected>All Goals</option>
                      </select>
                  </div>
              </div>

              <div class="col-xxl-2 col-sm-12">
                  <div>
                      <button id="filter_data" type="button" class="btn btn-outline-primary w-100"> <i class="ri-equalizer-line me-1 align-bottom"></i>Apply Filter</button>
                  </div>
              </div>
              <!--end col-->
          </div>
          <!--end row-->
      </form>
  </div>
  <div id="dataSheetTableArea">
      <?= view('Shared/DataSheet/programData/programsDataTable') ?>
  </div>