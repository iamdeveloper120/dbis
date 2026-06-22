<!-- Sweet Alerts js -->
<script src="/assets/js/app.js"></script>

<script>
    /****************************** */
    $(document).ajaxComplete(function(event, xhr) {
        if (xhr.status == 401) {
            // Handle unauthorized (session expired)
            showAlert('', 'Your session has expired. Please log in again.', 'info');
            window.location.href = '/login';
        } else if (xhr.status == 402) {
            // Handle forbidden (permission denied)
            showAlert('', 'Your session has expired. Please log in again.', 'info');
            window.location.href = '/login';
        } else if (xhr.status == 403) {
            // Handle forbidden (permission denied)
            var response = JSON.parse(xhr.responseText);
            showAlert('', response.message, 'error'); // Display server's message
        }
    });
    /************************************************** */
    var momentFormatMapping = {
        "d M y": "DD MMM YYYY",
        "d-M-y": "DD-MMM-YYYY",
        "d/M/y": "DD/MMM/YYYY",
        "d F Y": "DD MMMM YYYY",
        "d-F-Y": "DD-MMMM-YYYY",
        "d/F/Y": "DD/MMMM/YYYY",
        "Y-m-d": " YYYY-MM-DD"
        // Add more mappings as needed
    };
    flatpickr.l10ns.default.firstDayOfWeek = <?= CC_WEEK_START_DAY ?>;
    const dateFormat = "<?= CC_DATE_FORMAT ?>";
    const momentDateFormat = (momentFormatMapping.hasOwnProperty("<?= CC_DATE_FORMAT ?>")) ? momentFormatMapping["<?= CC_DATE_FORMAT ?>"] : 'YYYY-MM-DD';
    const timeFormat = "<?= CC_TIME_FORMAT ?>";

    var dateConfig = {
        dateFormat: dateFormat,
        weekNumbers: true,
    };

    /************************************************** */
    var table = '';
    var current_row = '';

    const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        }
    });

    function addTableRow(rowData, newCss = null, customClass = null) {
        // Add custom class and apply CSS
        var newRow = table.row.add(rowData).draw().show().draw(false).node();
        if (newCss == null) {
            $(newRow).css({
                'background-color': '#cef0eb',
                'color': '#000'
            });
        } else {
            $(newRow).css(newCss);
        }

        if (customClass == null) {
            $(newRow).addClass('table-success');
        } else {
            $(customClass).addClass(customClass);
        }

        // Scroll to the new row
        $('html, body').animate({
            scrollTop: $(newRow).offset().top
        }, 1000);
    }

    function updateTableRow(rowData, newCss = null, customClass = null) {
        // Add custom class and apply CSS

        table.row(current_row).remove().draw(false);
        var newRow = table.row.add(rowData).draw().show().draw(false).node();
        if (newCss == null) {
            $(newRow).css({
                'background-color': '#d4ebf8',
                'color': '#000'
            });
        } else {
            $(newRow).css(newCss);
        }

        if (customClass == null) {
            $(newRow).addClass('table-info');
        } else {
            $(customClass).addClass(customClass);
        }

        // Scroll to the new row
        $('html, body').animate({
            scrollTop: $(newRow).offset().top
        }, 1000);
    }

    /************************************************** */

    function showAlert(title, html, type) {
        Swal.fire({
            title: title,
            html: html,
            icon: type,
            customClass: {
                confirmButton: 'btn btn-primary w-xs me-2 mt-2',
            },
            buttonsStyling: false
        });
    }
    /************************************************** */
    function displayValidationErrors(errors) {
        console.log(errors);
        if (errors.length > 0) {
            let errorHTML = '<div class="list-group">';
            errors.forEach(function(error) {
                errorHTML += '<span class="list-group-item">' + error + '</span>';
            });
            errorHTML += '</div>';

            Swal.fire({
                title: "Validation Errors",
                html: errorHTML,
                icon: 'error',
                customClass: {
                    confirmButton: 'btn btn-primary w-xs me-2 mt-2',
                },
                buttonsStyling: false
            });
        }
    }

    function addRowAndHighlight(givenTable, givenRowData, hClass) {
        console.log("Adding row:", givenRowData);

        // Add the new row
        let rowAdded = givenTable.row.add(givenRowData);

        // Get the index of the added row
        let rowIndex = givenTable.row(rowAdded.index()).index();

        // Calculate the page where the row exists
        let pageSize = givenTable.page.len();
        let page = Math.floor(rowIndex / pageSize);

        // Navigate to the correct page
        givenTable.page(page).draw(false);

         
    }

    function highlightRow(rowNode, hClass) {
        $(rowNode).addClass(hClass);
    }

    function navigateToRowPage(table, rowNode) {
        // Get the row index
        let rowIndex = table.row(rowNode).index(); // Get the row index
        let pageSize = table.page.len(); // Number of rows per page
        let page = Math.floor(rowIndex / pageSize); // Calculate the page number

        // Move to the appropriate page and redraw the table
        table.page(page).draw(false);
    }


    /************************************************** */
</script>