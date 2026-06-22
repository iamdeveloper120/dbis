 <style>
     .offcanvas,
     .offcanvas-lg,
     .offcanvas-md,
     .offcanvas-sm,
     .offcanvas-xl,
     .offcanvas-xxl {

         --vz-offcanvas-width: 100%;

     }


     table th,
     table td {
         white-space: nowrap;
     }

     /* Global Red Border for the Right */
     .table-red-right-border>tbody>tr>td:last-child,
     .table-red-right-border>tbody>tr>th:last-child {
         border-right-width: 3px;
         border-right-color: red;
     }

     .table-black-right-border>tbody>tr>td:last-child,
     .table-black-right-border>tbody>tr>th:last-child {
         border-right-width: 3px;
         border-right-color: black;
     }

     /* Black Border for Phase 3 (Right Inner Border) */
     .black-right-border::before {
         content: '';
         position: absolute;
         top: 0;
         left: 0;
         /* Offset from the red border */
         height: 100%;
         width: 2px;
         background-color: black;
     }

     /* Positioning for the cells */
     .table td,
     .table th {
         position: relative;
         /* Required for pseudo-elements to work */
     }


     .phase-1 {
         width: 150px;
         min-width: 150px;
         max-width: 150px;
     }

     .px200 {
         width: 150px;
         min-width: 150px;
         max-width: 150px;
         word-wrap: break-word;
     }

     .no-hover {
         pointer-events: none;
         /* This disables all pointer interactions */
     }

     .no-hover:hover {
         background-color: inherit;
         /* Keeps the original background color on hover */
         color: inherit;
         /* Keeps the original text color on hover */
         cursor: default !important;
         /* Force the default cursor */
         /* Sets the cursor to the default arrow */
     }

     .list-group-item {
         cursor: default !important;
     }

     .list-group-item:hover {
         background-color: #2074ba1a;
         /* Replace with your preferred hover color */
         /* You can add additional hover styles here if needed */
     }
 </style>