  <div class="border-bottom-dashed border-bottom" style="padding-bottom:10px; margin-bottom:20px">
      <form>
          <div class="row g-3">
              <div class="col-xxl-4 col-sm-12">
                  <select class="form-control" name="choices-single-default" id="sDomain">
                      <option value="" selected>All Domains</option>
                      <?php
                        foreach ($domains as $domain) {  ?>
                          <option value="<?php echo $domain->id; ?>">
                              <?php echo $domain->name . ' ( ' . $domain->domain_code . ' )  '; ?></option>
                      <?php } ?>

                  </select>
              </div>
              <!--end col-->
              <div class="col-xxl-4 col-sm-12">
                  <select class="form-control" name="choices-single-default" id="sGoal">
                      <option value="" selected>All Goals</option>
                  </select>
              </div>
              <div class="col-xxl-2 col-sm-12">
                  <select class="form-control" name="choices-single-default" id="sProbe">
                      <option value="" selected>All Probe Set</option>
                      <?php
                        foreach ($probeSets as $probe) {  ?>
                          <option value="<?php echo $probe['id']; ?>">
                              <?php echo $probe['name']; ?></option>
                      <?php } ?>

                  </select>
              </div>

              <!--end col-->
              <div class="col-xxl-2 col-sm-12">
                  <button id="filter_data" type="button" class="btn btn-outline-primary w-100"> <i class="ri-equalizer-line me-1 align-bottom"></i>Apply Filter</button>

              </div>
              <!--end col-->
          </div>
          <!--end row-->
      </form>
  </div>
  <div id="client_pg_area">
      <table class="table table-bordered nowrap fixed-columns-table" style="width: 100%;" id="pg_dataTable"></table>
  </div>