<style>
    .offcanvas,
    .offcanvas-lg,
    .offcanvas-md,
    .offcanvas-sm,
    .offcanvas-xl,
    .offcanvas-xxl {

        --vz-offcanvas-width: 100%;

    }

    .btn-soft-custom-danger {
        --vz-btn-color: #f06548;
        --vz-btn-bg: rgba(240, 101, 72, 0.1);
        --vz-btn-border-color: transparent;
        --vz-btn-hover-color: #f06548;
        --vz-btn-hover-bg: rgba(240, 101, 72, 0.1);
        --vz-btn-hover-border-color: transparent;
        --vz-btn-focus-shadow-rgb: 240, 101, 72;
        --vz-btn-active-color: #f06548;
        --vz-btn-active-bg: rgba(240, 101, 72, 0.1);
        --vz-btn-active-border-color: transparent;
    }

    .btn-soft-custom-success {
        --vz-btn-color: #0ab39c;
        --vz-btn-bg: rgba(10, 179, 156, 0.1);
        --vz-btn-border-color: transparent;
        --vz-btn-hover-color: #0ab39c;
        --vz-btn-hover-bg: rgba(10, 179, 156, 0.1);
        --vz-btn-hover-border-color: transparent;
        --vz-btn-focus-shadow-rgb: 10, 179, 156;
        --vz-btn-active-color: var(--vz-btn-hover-color);
        --vz-btn-active-bg: rgba(10, 179, 156, 0.1);
        --vz-btn-active-border-color: transparent;
    }

    .btn-soft-custom-primary {
        --vz-btn-color: #2074BA;
        --vz-btn-bg: rgba(64, 81, 137, 0.1);
        --vz-btn-border-color: transparent;
        --vz-btn-hover-color: #2074BA;
        --vz-btn-hover-bg: rgba(64, 81, 137, 0.1);
        --vz-btn-hover-border-color: transparent;
        --vz-btn-focus-shadow-rgb: 64, 81, 137;
        --vz-btn-active-color: var(--vz-btn-hover-color);
        --vz-btn-active-bg: rgba(64, 81, 137, 0.1);
        --vz-btn-active-border-color: transparent;
    }

    table-red-right-border {
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-red-right-border>tbody>tr>td:last-child,
    .table-red-right-border>tbody>tr>th:last-child,
    .table-red-right-border>tfoot>tr>td:last-child,
    .table-red-right-border>tfoot>tr>th:last-child,
    .table-red-right-border>thead>tr>td:last-child,
    .table-red-right-border>thead>tr>th:last-child {
        border-right: 3px solid red !important;
    }

    table.dataTable thead tr th {
        word-wrap: break-word;
        /* word-break: break-all; */
    }

    table.dataTable tbody tr td {
        word-wrap: break-word;
        /* word-break: break-all;/ */
    }


    th,
    td {
        white-space: nowrap;
    }

    div.dataTables_wrapper {
        width: 800px;
        margin: 0 auto;
    }

    .phase-1 {
        width: 100px;
        min-width: 100px;
        max-width: 100px;
    }

    .px200 {
        width: 150px;
        min-width: 150px;
        max-width: 150px;
        word-wrap: break-word;
    }

    .DTFC_LeftBodyLiner {
        overflow-y: unset !important
    }

    .DTFC_RightBodyLiner {
        overflow-y: unset !important
    }
</style>