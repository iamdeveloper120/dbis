<!-- JAVASCRIPT -->
<script src="/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/assets/libs/simplebar/simplebar.min.js"></script>
<script src="/assets/libs/node-waves/waves.min.js"></script>
<script src="/assets/libs/feather-icons/feather.min.js"></script>
<script src="/assets/js/pages/plugins/lord-icon-2.1.0.js"></script>
<script type='text/javascript' src='/assets/libs/choices.js/public/assets/scripts/choices.min.js'></script>
<script type='text/javascript' src='/assets/libs/flatpickr/flatpickr.min.js'></script>

<script src="/assets/libs/sweetalert2/sweetalert2.min.js"></script>

<script type='text/javascript' src='/assets/libs/flatpickr/plugins/monthSelect/index.js'></script>
<link href="/assets/libs/flatpickr/plugins/monthSelect/style.css" rel="stylesheet" />
<script src="/assets/libs/@tarekraafat/autocomplete.js/autoComplete.min.js"></script>


<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.1.5/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.datatables.net/autofill/2.7.0/js/dataTables.autoFill.js"></script>
<script src="https://cdn.datatables.net/autofill/2.7.0/js/autoFill.bootstrap5.js"></script>
<script src="https://cdn.datatables.net/buttons/3.1.2/js/dataTables.buttons.js"></script>
<script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.bootstrap5.js"></script>
<script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.colVis.js"></script>
<script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.html5.js"></script>
<script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.print.js"></script>
<script src="https://cdn.datatables.net/colreorder/2.0.4/js/dataTables.colReorder.js"></script>
<script src="https://cdn.datatables.net/datetime/1.5.3/js/dataTables.dateTime.js"></script>
<script src="https://cdn.datatables.net/fixedcolumns/5.0.1/js/dataTables.fixedColumns.js"></script>
<script src="https://cdn.datatables.net/fixedheader/4.0.1/js/dataTables.fixedHeader.js"></script>
<script src="https://cdn.datatables.net/keytable/2.12.1/js/dataTables.keyTable.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.3/js/dataTables.responsive.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.3/js/responsive.bootstrap5.js"></script>
<script src="https://cdn.datatables.net/rowgroup/1.5.0/js/dataTables.rowGroup.js"></script>
<script src="https://cdn.datatables.net/rowreorder/1.5.0/js/dataTables.rowReorder.js"></script>
<script src="https://cdn.datatables.net/scroller/2.4.3/js/dataTables.scroller.js"></script>
<script src="https://cdn.datatables.net/searchbuilder/1.8.0/js/dataTables.searchBuilder.js"></script>
<script src="https://cdn.datatables.net/searchbuilder/1.8.0/js/searchBuilder.bootstrap5.js"></script>
<script src="https://cdn.datatables.net/searchpanes/2.3.2/js/dataTables.searchPanes.js"></script>
<script src="https://cdn.datatables.net/searchpanes/2.3.2/js/searchPanes.bootstrap5.js"></script>
<script src="https://cdn.datatables.net/select/2.0.5/js/dataTables.select.js"></script>
<script src="https://cdn.datatables.net/staterestore/1.4.1/js/dataTables.stateRestore.js"></script>
<script src="https://cdn.datatables.net/staterestore/1.4.1/js/stateRestore.bootstrap5.js"></script>

<script>
    /************************************************** */
    /*! © SpryMedia Ltd - datatables.net/license */

    (function(factory) {
        if (typeof define === 'function' && define.amd) {
            // AMD
            define(['jquery', 'datatables.net'], function($) {
                return factory($, window, document);
            });
        } else if (typeof exports === 'object') {
            // CommonJS
            var jq = require('jquery');
            var cjsRequires = function(root, $) {
                if (!$.fn.dataTable) {
                    require('datatables.net')(root, $);
                }
            };

            if (typeof window === 'undefined') {
                module.exports = function(root, $) {
                    if (!root) {
                        // CommonJS environments without a window global must pass a
                        // root. This will give an error otherwise
                        root = window;
                    }

                    if (!$) {
                        $ = jq(root);
                    }

                    cjsRequires(root, $);
                    return factory($, root, root.document);
                };
            } else {
                cjsRequires(window, jq);
                module.exports = factory(jq, window, window.document);
            }
        } else {
            // Browser
            factory(jQuery, window, document);
        }
    }(function($, window, document, undefined) {
        'use strict';
        var DataTable = $.fn.dataTable;


        /**
         * It can be quite useful to jump straight to a page which contains a certain
         * piece of data (a user name for example). This plug-in provides exactly that
         * ability, searching for a given data parameter from a given column and
         * immediately shifting the paging of the table to jump to that point.
         *
         * If multiple data points match the requested data, the paging will be shifted
         * to show the first instance. If there are no matches, the paging will not
         * change.
         *
         * Note that unlike the core DataTables API methods, this plug-in will
         * automatically call `dt-api draw()` to redraw the table with the current page
         * shown.
         *
         * @name page.JumpToData()
         * @summary Jump to a page by searching for data from a column
         * @author [Allan Jardine](http://datatables.net)
         * @requires DataTables 1.10+
         *
         * @param {*} data Data to search for
         * @param {integer} column Column index
         * @returns {Api} DataTables API instance
         *
         * @example
         *    var table = $('#example').DataTable();
         *    table.page.jumpToData( "Allan Jardine", 0 );
         */


        DataTable.Api.register('page.jumpToData()', function(data, column) {
            var pos = this.column(column, {
                order: 'current'
            }).data().indexOf(data);
            if (pos >= 0) {
                var page = Math.floor(pos / this.page.info().length);
                this.page(page).draw(false);
            }
            return this;
        });

        /**
         * This plugin jumps to the right page of the DataTable to show the required row
         *
         * @version 1.0
         * @name row().show()
         * @summary See the row in datable by display the right pagination page
         * @author [Edouard Labre](http://www.edouardlabre.com)
         *
         * @param {void} a row must be selected
         * @returns {DataTables.Api.Rows} DataTables Rows API instance
         *
         * @example
         *    // Add an element to a huge table and go to the right pagination page
         *    var table = $('#example').DataTable();
         *    var new_row = {
         *      DT_RowId: 'row_example',
         *      name: 'example',
         *      value: 'an example row'
         *    };
         *
         *    table.row.add( new_row ).draw().show().draw(false);
         */
        DataTable.Api.register('row().show()', function() {
            var page_info = this.table().page.info();
            // Get row index
            var new_row_index = this.index();
            // Row position
            var row_position = this.table()
                .rows({
                    search: 'applied'
                })[0]
                .indexOf(new_row_index);
            // Already on right page ?
            if ((row_position >= page_info.start && row_position < page_info.end) ||
                row_position < 0) {
                // Return row object
                return this;
            }
            // Find page number
            var page_to_display = Math.floor(row_position / this.table().page.len());
            // Go to that page
            this.table().page(page_to_display);
            // Return row object
            return this;
        });
        return DataTable;
    }));
</script>



