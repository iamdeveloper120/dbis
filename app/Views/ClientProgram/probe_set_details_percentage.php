<div class="card border card-border-primary">
    <div class="card-header">
        <h6 class="card-title mb-0"><?= esc($probeSet['name']) ?></h6>
    </div>
    <div class="card-body">
        <p class="card-text"><?= esc($probeSet['description']) ?></p>

        <div class="row mb-3">
            <div class="col-lg-6">
                <label for="c_key" class="form-label">
                    <strong>Consecutive Criteria Check Key (%)</strong>
                </label>
                <input
                    type="number"
                    id="c_key"
                    name="c_key"
                    class="form-control"
                    value="<?= esc($inputs['key']) ?>"
                    placeholder="Enter percentage">
            </div>
            <div class='col-lg-6'>
                <label for="additional_info" class="form-label">
                    <strong>Trial data</strong>
                </label>
                <input
                    id='additional_info'
                    name='additional_info'
                    placeholder='Instructor need to enter trial data when collecting data'
                    class='form-control mb-2'>
                </input>
            </div>
        </div>

        <p><strong>Yes or No selection for trial data:</strong></p>
        <?= $inputsHtml ?>
        <div id="inputContainer"></div>
    </div>
</div>