<div class="row pb-2">
    <div class="col-md-5">
        <select class="form-control " id="targets_domains_dropdown_list">
            <option value="">All</option>
            <?php $object = json_decode(json_encode($domains), FALSE);
            foreach ($object as $domain) {  ?>
                <option value="<?php echo $domain->id; ?>">
                    <?php echo $domain->name . ' ( ' . $domain->domain_code . ' )  '; ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="col-md-5">
        <select class="form-control " id="targets_goals_dropdown_list">
            <option value="">All</option>
        </select>
    </div>
    <div class="col-md-2 text-end">
        <button type="button" id="search-target" class="btn btn-info bg-gradient waves-effect waves-light btn-label right " title="Search">
            <i class="ri-search-line label-icon align-middle fs-16 ms-2"></i>Search</button>
    </div>
</div>
<hr>
<div id="target_table_area">
    <?= view('ClientProgram/Wizard/targets_table', ['targets' => $targets]) ?>
</div>

<script>
    $(document).ready(function() {
        $('select').select2();

        $("#targets_domains_dropdown_list").on('change', function(e) {
            e.preventDefault;
            let client_id = $("#client_dropdown_list").val();
            let domain_id = $("#targets_domains_dropdown_list").val();

            if (client_id == '') {
                showAlert('', 'Select Client', 'error');
                return;
            }
            var ajaxRequest = $.ajax({
                url: '<?php echo base_url() ?>client-program/wizard/targets/goals',
                type: 'post',
                data: {
                    "client_id": client_id,
                    "domain_id": domain_id
                },
                beforeSend: function(xhr) {

                    $("#targets_domains_dropdown_list").prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    // Retrieve the goals array from the response data
                    var goals = response.data;

                    // Get the dropdown list element by its ID
                    var dropdown = document.getElementById('targets_goals_dropdown_list');

                    // Clear any existing options, except for the "All" option
                    dropdown.innerHTML = '<option value="">All</option>';

                    // Iterate over the goals array and add each item to the dropdown
                    goals.forEach(function(goal) {
                        // Create a new option element
                        var option = document.createElement('option');

                        // Set the value and text content of the option
                        option.value = goal.id;
                        option.textContent = goal.name;

                        // Append the option to the dropdown
                        dropdown.appendChild(option);
                    });

                } else {
                    showAlert(response.statusText, response.message, response.status);
                }
            });
            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
            ajaxRequest.always(function() {
                $('#targets_domains_dropdown_list').prop("disabled", false);
            });

        }); //On change function ends


        $("#search-target").on('click', function(e) {

            let client_id = $("#client_dropdown_list").val();
            let domain_id = $("#targets_domains_dropdown_list").val();
            let goal_id = $("#targets_goals_dropdown_list").val();

            if (client_id == '') {
                showAlert('', 'Select Client', 'error');
                return;
            }
            var ajaxRequest = $.ajax({
                url: '<?php echo base_url() ?>client-program/wizard/targets/search',
                type: 'post',
                data: {
                    "client_id": client_id,
                    "domain_id": domain_id,
                    "goal_id": goal_id
                },
                beforeSend: function(xhr) {

                    $("#client_dropdown_list").prop("disabled", true);
                    $("#targets_domains_dropdown_list").prop("disabled", true);
                    $("#targets_goals_dropdown_list").prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                if (response.status == 'success') {
                    // Populate DataTable with the retrieved data
                    $('#target_table_area').html(response.data);
                } else {
                    showAlert(response.statusText, response.message, response.status);
                }
            });
            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
            ajaxRequest.always(function() {
                $('#client_dropdown_list').prop("disabled", false);
                $("#targets_domains_dropdown_list").prop("disabled", false);
                $("#targets_goals_dropdown_list").prop("disabled", false);
            });

        }); //On change function ends

        /***************************************************************************************** */

    });
</script>