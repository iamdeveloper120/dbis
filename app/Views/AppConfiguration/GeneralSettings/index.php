<?= $this->extend("layout/master") ?>
<?= $this->section("page_content") ?>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h5 class="mb-sm-0">MIS Configuration</h5>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item active">General Setting</li>
        </ol>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="card">

            <div class="card-body border-bottom-dashed border-bottom">

                <blockquote class="blockquote custom-blockquote blockquote-outline blockquote-primary rounded mb-0">
                    <h5 class="text-dark mb-2">Application Name</h5>
                    <div class="row">
                        <div class="col-6">
                            <input type="text" class="form-control" id="siteName" value="<?= esc(old('siteName', setting('App.siteName')), 'attr') ?>" />
                        </div>

                    </div>
                </blockquote>
                <br>
                <blockquote class="blockquote custom-blockquote blockquote-outline blockquote-primary rounded mb-0">
                    <h5 class="text-dark mb-2">Timezone</h5>

                    <div class="row">
                        <div class="col-6 form-group">
                            <select id="timezoneArea" class="form-control">
                                <option>Select timezone...</option>
                                <?php foreach ($timezones as $timezone) : ?>
                                    <option value="<?= $timezone ?>" <?php if ($currentTZArea === $timezone) : ?> selected <?php endif ?>>
                                        <?= $timezone ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>

                        <div class="col-6 form-group">
                            <select id="timezone" class="form-control">
                                <?php if (isset($timezoneOptions) && !empty($timezoneOptions)) : ?>
                                    <?= $timezoneOptions ?>
                                <?php else : ?>
                                    <option value="0">No timezones</option>
                                <?php endif ?>
                            </select>
                        </div>
                    </div>

                </blockquote>
                <br>
                <blockquote class="blockquote custom-blockquote blockquote-outline blockquote-primary rounded mb-0">
                    <h5 class="text-dark mb-2">Week Start Day</h5>

                    <div class="row">
                        <div class="col-6 form-group">
                            <select id="weekStartDay" class="form-control">
                                <option value="1" <?php if (setting('App.weekStartDay') === '1') : ?> selected <?php endif ?>>
                                    Monday
                                </option>
                                <option value="2" <?php if (setting('App.weekStartDay') === '2') : ?> selected <?php endif ?>>
                                    Tuesday
                                </option>
                                <option value="3" <?php if (setting('App.weekStartDay') === '3') : ?> selected <?php endif ?>>
                                    Wednesday
                                </option>
                                <option value="4" <?php if (setting('App.weekStartDay') === '4') : ?> selected <?php endif ?>>
                                    Thursday
                                </option>
                                <option value="5" <?php if (setting('App.weekStartDay') === '5') : ?> selected <?php endif ?>>
                                    Friday
                                </option>
                                <option value="6" <?php if (setting('App.weekStartDay') === '6') : ?> selected <?php endif ?>>
                                    Saturday
                                </option>
                                <option value="0" <?php if (setting('App.weekStartDay') === '0') : ?> selected <?php endif ?>>
                                    Sunday
                                </option>
                            </select>
                        </div>
                    </div>

                </blockquote>
                <br>

                <blockquote class="blockquote custom-blockquote blockquote-outline blockquote-primary rounded mb-0">
                    <h5 class="text-dark mb-2">Date Format</h5>
                    <div class="mb-3">
                        <input class="mb-3-input" type="radio" name="dateFormat" value="d M y" <?php if (old('dateFormat', $dateFormat) === 'd M y') : ?> checked <?php endif ?>>
                        <label class="mb-3-label" for="dateFormat">
                            <strong> dd mm yyyy </strong> [Day (2-digit) Month (abbreviated) Year (4-digit) e.g., 18 Jul 2023]
                        </label>
                    </div>
                    <div class="mb-3">
                        <input class="mb-3-input" type="radio" name="dateFormat" value="d-M-y" <?php if (old('dateFormat', $dateFormat) === 'd-M-y') : ?> checked <?php endif ?>>
                        <label class="mb-3-label" for="dateFormat">
                            <strong> dd-mm-yyyy </strong>[Day (2-digit) Month (abbreviated) Year (4-digit) e.g., 18-Jul-2023]
                        </label>
                    </div>
                    <div class="mb-3">
                        <input class="mb-3-input" type="radio" name="dateFormat" value="d/M/y" <?php if (old('dateFormat', $dateFormat) === 'd/M/y') : ?> checked <?php endif ?>>
                        <label class="mb-3-label" for="dateFormat">
                            <strong> dd/mm/yyyy </strong> [Day (2-digit) Month (abbreviated) Year (4-digit) e.g., 18/Jul/2023]
                        </label>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <input class="mb-3-input" type="radio" name="dateFormat" value="d F Y" <?php if (old('dateFormat', $dateFormat) === 'd F Y') : ?> checked <?php endif ?>>
                        <label class="mb-3-label" for="dateFormat">
                            <strong> dd mm yyyy </strong> [Day (2-digit) Month (full name) Year (4-digit) e.g., 18 July 2023]
                        </label>
                    </div>
                    <div class="mb-3">
                        <input class="mb-3-input" type="radio" name="dateFormat" value="d-F-Y" <?php if (old('dateFormat', $dateFormat) === 'd-F-Y') : ?> checked <?php endif ?>>
                        <label class="mb-3-label" for="dateFormat">
                            <strong> dd-mm-yyyy </strong> [Day (2-digit) Month (full name) Year (4-digit) e.g., 18-July-2023]
                        </label>
                    </div>
                    <div class="mb-3">
                        <input class="mb-3-input" type="radio" name="dateFormat" value="d/F/Y" <?php if (old('dateFormat', $dateFormat) === 'd/F/Y') : ?> checked <?php endif ?>>
                        <label class="mb-3-label" for="dateFormat">
                            <strong> dd/mm/yyyy </strong> [Day (2-digit) Month (full name) Year (4-digit) e.g., 18/July/2023]
                        </label>
                    </div>
                </blockquote>
                <br>
                <blockquote class="blockquote custom-blockquote blockquote-outline blockquote-primary rounded mb-0">
                    <h5 class="text-dark mb-2">Time Format</h5>

                    <div class="row">
                        <div class="col-12 col-sm-4">
                            <div class="mb-3">
                                <input class="mb-3-input" type="radio" name="timeFormat" value="g:i A" <?php if (old('timeFormat', $timeFormat) === 'g:i A') : ?> checked <?php endif ?>>
                                <label class="mb-3-label" for="timeFormat">
                                    <strong>12 hour with AM/PM </strong> [e.g., 2:30 PM]
                                </label>
                            </div>
                            <div class="mb-3">
                                <input class="mb-3-input" type="radio" name="timeFormat" value="H:i" <?php if (old('timeFormat', $timeFormat) === 'H:i') : ?> checked <?php endif ?>>
                                <label class="mb-3-label" for="timeFormat">
                                    <strong> 24 hour </strong> [e.g., 14:30]

                                </label>
                            </div>

                        </div>
                    </div>
                </blockquote>
                <br>
                <blockquote class="blockquote custom-blockquote blockquote-outline blockquote-primary rounded mb-0">
                    <h5 class="text-dark mb-2">Session Processing Resolution Days</h5>
                    <p class="text-muted">Note: Session Processing Resolution Days defines how many days back a user can add, edit, or delete sessions, programs, mands, problem behaviors, and durations data. If a user has the necessary extra permission, they can perform these actions at any time, and this limit will be ignored.</p>
                    <div class="row">
                        <div class="col-6">
                            <input type="text" class="form-control" id="processingResolutionDays" value="<?= esc(old('processingResolutionDays', setting('App.sessionProcessingResolutionDays')), 'attr') ?>" />
                        </div>

                    </div>
                </blockquote>
                <hr>
                <div class="row">
                    <div class="col-12 col-sm-12">
                        <button type="submit" id="btn_save_setting" class="btn btn-primary float-end"><i class="ri-save-line"></i> Save Settings</button>
                    </div>
                </div>


            </div>
            <!--end col-->
        </div>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section("page_js") ?>
<script>
    $(document).ready(function() {
        /***************************************************************************************** */
        var csrfToken = "<?= csrf_hash() ?>";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        });
        /***************************************************************************************** */
        $('#btn_save_setting').on('click', function() {
            var data = {
                'siteName': $('#siteName').val(),
                'timezone': $('#timezone').val(),
                'dateFormat': $('input[name="dateFormat"]:checked').val(),
                'timeFormat': $('input[name="timeFormat"]:checked').val(),
                'weekStartDay': $('#weekStartDay').val(),
                'processingResolutionDays': $('#processingResolutionDays').val(),

                
            };
            var ajaxRequest = $.ajax({
                url: '<?php echo base_url() ?>app-configuration/general-settings/save',
                type: 'post',
                data: data,
                beforeSend: function(xhr) {
                    $('#btn_save_setting').prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    showAlert(response.statusText, response.message, response.status);

                } else {
                    showAlert(response.statusText, response.message, response.status);

                }
            });
            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
            ajaxRequest.always(function() {
                $('#btn_save_setting').prop("disabled", false);
            });
        });
        /***************************************************************************************** */

        $('#timezoneArea').change(function() {
            var data = {
                'timezoneArea': $('#timezoneArea').val()
            };

            $.ajax("<?php echo base_url() ?>app-configuration/general-settings/get-time-zones", {
                type: 'post',
                cache: false,
                dataType: 'text', 
                data: data,
                success: function(data) {
                    $('#timezone').html(data);
                },
                error: function(xhr, status) {
                    console.log(status);
                    console.log(xhr.responseText);
                }
            });

        });


    }); // End of document ready
</script>
<?= $this->endSection() ?>
