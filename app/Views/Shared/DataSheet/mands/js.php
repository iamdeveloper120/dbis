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
        var MandsTable = $('#mands_dataTable').DataTable({

            fixedColumns: {
                start: 1,
                end: 1
            },
            scrollCollapse: true,
            scrollX: true,
            response: false,
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
                        },
                        {
                            extend: 'copy',
                            className: 'btn btn-light bg-gradient waves-effect waves-light'
                        }, {
                            extend: 'excel',
                            className: 'btn btn-light bg-gradient waves-effect waves-light'
                        },
                        {
                            extend: 'colvis',
                            className: 'btn btn-light bg-gradient waves-effect waves-light'
                        }
                    ]
                },
                topEnd: {
                    search: {
                        placeholder: 'Search',

                    },

                }
            },


        });
        /***************************************************************************** */
        var mandsOffcanvasRight = document.getElementById('mandsOffcanvasRight')
        var mbsc = new bootstrap.Offcanvas(mandsOffcanvasRight)

        $('#mandsOffcanvasRight').on('hidden.bs.offcanvas', function() {
            // Trigger a custom event when the offcanvas is hidden
            $('#mands_daily_data').html('');
        });
        $('#client_mands_area').on('click', '.view_mands_session_data', function(e) {
            console.log('view_mands_session_data');
            var view_mands_btn = $(this);
            var client_id = $(this).data('client-id');
            var session_date = $(this).data('session-date');


            var ajaxRequest = $.ajax({
                type: 'POST',
                url: '<?= site_url('/shared-datasheet/mandsDailyData') ?>',
                data: {
                    client_id: client_id,
                    session_date: session_date
                },
                dataType: 'html',
                beforeSend: function(xhr) {
                    view_mands_btn.prop("disabled", true);
                }
            });
            ajaxRequest.done(function(response) {
                // Update program list content
                $('#mands_daily_data').html(response);
                mbsc.show()
            });

            ajaxRequest.fail(function(jqXHR, textStatus, error) {
                swalAert(jqXHR.status, "Request failed: " + textStatus + '<br>' + error, 'error');
            });
            ajaxRequest.always(function() {
                view_mands_btn.prop("disabled", false);
            });


        });

    });
</script>