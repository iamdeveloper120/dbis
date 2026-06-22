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
        var pg_dataTable = $('#pg_dataTable').DataTable({
            fixedColumns: {
                start: 1,
                end: 1
            },
            scrollCollapse: true,
            scrollX: true,
            ordering: false,
            lengthChange: false,
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
            },
            ajax: {
                url: '<?= base_url('shared-datasheet/filterProgramChange') ?>',
                type: 'POST',
                data: function(d) {
                    d.client_id = '<?= $client->id ?>';
                    d.domain_id = $('#sDomain').val();
                    d.goal_id = $('#sGoal').val();
                    d.probe_set_id = $('#sProbe').val();
                },
                dataSrc: function(json) {
                    return json;
                }
            },
            columns: [{
                    data: 'session_date',
                    title: 'Date',
                    width: '5%',
                    className: "dt-nowrap",
                    render: function(data, type, row) {
                        // Format the date using Moment.js and the momentDateFormat
                        return moment(data).format(momentDateFormat);
                    }
                },
                {
                    data: 'probe_set_name',
                    title: 'Probe Set',
                    width: '10%',
                    className: "dt-nowrap"
                },
                {
                    data: 'domain_code',
                    title: 'Domain',
                    width: '5%',
                    className: "dt-nowrap"
                },
                {
                    data: 'goal_code',
                    title: 'Goal',
                    width: '5%',
                    className: "dt-nowrap"
                },
                {
                    data: 'target_name',
                    title: 'Target',
                    width: '10%',
                    className: "table-wrap-target" // Allow wrapping for target_name column
                },
                {
                    data: 'is_change_made',
                    title: 'Program Changed?',
                    width: '5%',
                    className: "table-wrap-description",
                    render: function(data, type, row) {
                        // Format the date using Moment.js and the momentDateFormat
                        if (data == '0')
                            return 'No';
                        if (data == '1')
                            return 'Yes';
                        return '';
                    }
                },
                {
                    data: 'consecutive_criteria',
                    title: 'Trial Override',
                    width: '5%',
                    className: "table-wrap-description"
                },
                {
                    data: 'incorrect_response',
                    title: 'Incorrect Response',
                    width: '20%',
                    className: "table-wrap-incorrect-response", // Allow wrapping for incorrect_response column
                    render: function(data, type, row) {
                        if (data !== null) {
                            if (type === 'display' && data.length > 50) { // Change 50 to your desired limit
                                var escapedString = data.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                                return data.substr(0, 50) + '... <a href="#" class="readMore_r" data-full-comment="' + escapedString + '"><span class="badge bg-info-subtle text-info">Read more</span></a>';
                            }
                        }

                        return data;
                    }
                },
                {
                    data: 'behavioral_variables',
                    title: 'Behavioral Variables',
                    width: '20%',
                    className: "table-wrap-behavioral-variables", // Allow wrapping for behavioral_variables column
                    render: function(data, type, row) {
                        if (data !== null) {
                            if (type === 'display' && data.length > 50) { // Change 50 to your desired limit
                                var escapedString = data.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                                return data.substr(0, 50) + '... <a href="#" class="readMore_v" data-full-comment="' + escapedString + '"><span class="badge bg-info-subtle text-info">Read more</span></a>';
                            }
                        }

                        return data;
                    }
                },
                {
                    data: 'description',
                    title: 'Description',
                    width: '20%',
                    className: "table-wrap-description", // Allow wrapping for description column
                    render: function(data, type, row) {
                        if (data !== null) {
                            if (type === 'display' && data.length > 50) { // Change 50 to your desired limit
                                var escapedString = data.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                                return data.substr(0, 50) + '... <a href="#" class="readMore_d" data-full-comment="' + escapedString + '"><span class="badge bg-info-subtle text-info">Read more</span></a>';
                            }
                        }

                        return data;
                    }
                },
                {
                    data: null,
                    title: 'Detail',
                    width: '5%',
                    render: function(data, type, row) {
                        if (row.change_id) { // Check if change_id exists (not null, not empty, not zero)
                            return '<button class="btn btn-sm btn-outline-primary view_pg_data" ' +
                                'data-alert-id="' + row.alert_id + '" ' +
                                'data-change-id="' + row.change_id + '" ' +
                                'data-client-id="<?= $client->id ?>" ' +
                                'data-target-id="' + row.target_id + '">' +
                                '<i class="ri-eye-line"></i></button>';
                        } else {
                            return ''; // Return empty string (no button)
                        }
                    }

                }
            ]
        });

        $('#pg_dataTable').on('click', '.readMore_r', function(e) {
            e.preventDefault();
            const fullComment = $(this).data('full-comment'); // get the full comment from the data attribute                
            $('#full_comment_modal .modal-title').html('Incorrect Response');
            $('#full_comment_modal .modal-body').html(fullComment);
            $('#full_comment_modal').modal('show');
        });

        $('#pg_dataTable').on('click', '.readMore_v', function(e) {
            e.preventDefault();
            const fullComment = $(this).data('full-comment'); // get the full comment from the data attribute                
            $('#full_comment_modal .modal-title').html('Behavioral Variables');
            $('#full_comment_modal .modal-body').html(fullComment);
            $('#full_comment_modal').modal('show');
        });


        $('#pg_dataTable').on('click', '.readMore_d', function(e) {
            e.preventDefault();
            const fullComment = $(this).data('full-comment'); // get the full comment from the data attribute                
            $('#full_comment_modal .modal-title').html('Description');
            $('#full_comment_modal .modal-body').html(fullComment);
            $('#full_comment_modal').modal('show');
        });



        $('#filter_data').on('click', function() {
            pg_dataTable.ajax.reload(); // This will reload the table based on the filters
        });
        /***************************************************************************** */
        var pgOffcanvasRight = document.getElementById('pgOffcanvasRight')
        var mbsc = new bootstrap.Offcanvas(pgOffcanvasRight)

        $('#pgOffcanvasRight').on('hidden.bs.offcanvas', function() {
            // Trigger a custom event when the offcanvas is hidden
            $('#program_change_data').html('');
        });
        /******************************************************************************************* */
        $('#client_pg_area').on('click', '.view_pg_data', function(e) {
            var pg_alert_id = $(this).attr('data-alert-id');
            var pg_change_id = $(this).attr('data-change-id');
            var client_id = $(this).attr('data-client-id');
            var target_id = $(this).attr('data-target-id');

            programChangeShow(pg_alert_id, pg_change_id, client_id, target_id);
        });
        /******************************************************************************************* */
        function programChangeShow(pg_alert_id, pg_change_id, client_id, target_id) {
            console.log(pg_alert_id, pg_change_id, client_id, target_id);
            var ajaxRequest = $.ajax({
                type: 'POST',
                url: '<?= base_url('sessions/programChange/getForm') ?>',
                data: {
                    pg_alert_id: pg_alert_id,
                    pg_change_id: pg_change_id,
                    client_id: client_id,
                    target_id: target_id,
                },
                dataType: 'html',
                beforeSend: function(xhr) {

                }
            });
            ajaxRequest.done(function(response) {
                // Update program list content
                if (response == '') {
                    showAlert('', "No program change has been made", 'info');
                } else {
                    $('#program_change_data').html(response);
                    mbsc.show()
                }

            });

            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                showAlert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
            ajaxRequest.always(function() {

            });
        }
        /***************************************************************************************** */
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

    });
</script>