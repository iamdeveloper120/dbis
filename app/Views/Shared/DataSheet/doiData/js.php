<script>
    $(document).ready(function() {
        $('select').select2();

        var csrfToken = "<?= csrf_hash() ?>";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        });

        var doiTable = $('#doi_targets_data_table').DataTable({
            responsive: false,
            ordering: false,
            lengthChange: false,
            ajax: {
                url: '<?= base_url('shared-datasheet/filterDOITargets') ?>',
                type: 'POST',
                data: function(d) {
                    // Send the filter data with the AJAX request
                    d.client_id = '<?= $client->id ?>';
                    d.domain_id = $('#sDomain').val();
                    d.goal_id = $('#sGoal').val();
                    d.probe_set_id = $('#sProbe').val();
                },
                dataSrc: function(json) {
                    // If the server returns JSON, process it here
                    return json;
                }
            },
            columns: [{
                    data: 'session_date',
                    render: function(data, type, row) {
                        // Format the date using Moment.js and the momentDateFormat
                        return moment(data).format(momentDateFormat);
                    }
                },
                {
                    data: 'probe_set_name'
                },
                {
                    data: 'domain_code'
                },
                {
                    data: 'goal_code'
                },
                {
                    data: 'target_name'
                },
                {
                    data: 'doi_value'
                }
            ],
            lengthMenu: [
                [10, 25, 50, -1],
                ['10 rows', '25 rows', '50 rows', 'Show all']
            ],
            layout: {
                topStart: {
                    buttons: [{
                        extend: 'pageLength',
                        className: 'btn btn-light bg-gradient waves-effect waves-light'
                    }, {
                        extend: 'copy',
                        className: 'btn btn-light bg-gradient waves-effect waves-light'
                    }, {
                        extend: 'excel',
                        className: 'btn btn-light bg-gradient waves-effect waves-light'
                    }, {
                        extend: 'colvis',
                        className: 'btn btn-light bg-gradient waves-effect waves-light'
                    }]
                },
                topEnd: {
                    search: {
                        placeholder: 'Search',
                    }
                }
            }
        });

        // Listen to the domain selection change
        $('#sDomain').on('change', function() {
            var domain_id = $(this).val();
            var client_id = '<?= $client->id ?>'; // Get the client ID from PHP
            var probe_type = 'count'; // Hardcoded for this view, can be dynamic for other views

            // Clear the existing options in the Goals dropdown and add "All Goals" option
            $('#sGoal').empty().append('<option value="">All Goals</option>');

            if (domain_id !== '') {
                // Send an AJAX request to fetch goals for the selected domain
                $.ajax({
                    url: '<?= base_url('shared-datasheet/getGoalsByDomain') ?>',
                    type: 'POST',
                    data: {
                        client_id: client_id,
                        domain_id: domain_id,
                    },
                    success: function(response) {
                        // Log the response for debugging


                        // Populate the Goals dropdown with the fetched goals from the object
                        if (response && response.length > 0) {
                            $.each(response, function(index, goal) {
                                $('#sGoal').append(
                                    $('<option></option>').attr('value', goal.id).text(goal.name + ' (' + goal.goal_code + ')')
                                );
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('Error fetching goals:', error);
                    }
                });
            }
        });

        // Apply filter and reload DataTable
        $('#filter_data').on('click', function() {
            doiTable.ajax.reload(); // This will reload the table based on the filters
        });


    });
</script>