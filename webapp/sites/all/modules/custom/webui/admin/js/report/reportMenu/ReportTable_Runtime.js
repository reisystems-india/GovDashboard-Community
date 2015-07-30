/*
 * Copyright 2014 REI Systems, Inc.
 * 
 * This file is part of GovDashboard.
 * 
 * GovDashboard is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * GovDashboard is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with GovDashboard.  If not, see <http://www.gnu.org/licenses/>.
 */

/* needed for advanced table pagination and column sorting in the Report & Dashboard Builders */
(function(global,$,undefined) {
    var ReportTable_Runtime = {
        tableSorts: {},

        initTable: function(reportId) { // Bind advanced table events for a report table
            // Fixed & Resizeable header
            // ----------------------------------------------
            var table = $('#dataTable_'+reportId);
            var tableContainer = $('#reportScroll_'+reportId);

            if (table.length && tableContainer.length) {
                // headerHeight = space to account for fixed header height
                // todo: add notes to css???
                var headerHeight = 32;

                // Make a copy of the table for printing
                var printTable = $(table).clone();
                printTable.attr('id', 'print_'+printTable.attr('id'));
                printTable.css('display', 'none');
                $('html').append(printTable);

                // *********************************************************************************************************
                // START colRatio calculation
                // This is required for resizeable columns to work
                // *********************************************************************************************************
                var colCount = $('#dataTable_'+reportId+' th').length;

                // scrollbarWidth = space to account for scrollbar presence.
                var scrollbarWidth = 17;
                // check if tableContainer is not overflowing = no vertical scrollbar, then remove space for scrollbar
                if (tableContainer[0].offsetHeight >= table[0].offsetHeight) {
                    scrollbarWidth = 0;
                }

                // TODO - IE8: vertical scrollbar is always present, so account for that

                // calculate equal widths for each column
                var colRatioVal = Math.floor((tableContainer.width()-scrollbarWidth)/colCount);
                // to get full table width coverage, find any extra pixels and add those to the first column
                var extraPixels = tableContainer.width() - (colRatioVal*colCount) - scrollbarWidth;

                var colRatio = [];
                for (var i=0; i<colCount; i++) {
                    if (i==0) {
                        colRatio.push(colRatioVal + extraPixels);
                    } else {
                        colRatio.push(colRatioVal);
                    }
                }
                // END colRatio calculation
                // *********************************************************************************************************

                table.fixheadertable({
                    colratio    : colRatio,
                    height      : tableContainer.height(),//-headerHeight,
                    width       : tableContainer.width(),
                    zebra       : true,
                    resizeCol   : true,
                    minColWidth : 50,
                    whiteSpace	: 'normal'
                });
            }

            // Sorting: table header click
            // ----------------------------------------------
            var headers = $('#report-'+reportId+' th');
            // bind click event only once
            if (typeof headers.data('events') == 'undefined' || typeof headers.data('events').click == 'undefined') {
                headers.click(function(){
                    ReportTable_Runtime.ColumnClick(reportId, $(this).attr('column'));
                });
            }

            // Pagination: page drop-down onchange
            // --------------------------------------------------------
            var pageSelect = $('#reportPaginationBar_'+reportId+' .advTablePageSelect');
            // bind event only once - prevents duplicate events
            if (typeof pageSelect.data('events') == 'undefined' || typeof pageSelect.data('events').change == 'undefined') {
                pageSelect.change(function() {
                    ReportTable_Runtime.PageClick(reportId, $(this).val());
                });
            }

            // Pagination: arrow button click (first, prev, next, last)
            // --------------------------------------------------------
            var pageButtons = $('#reportPaginationBar_'+reportId+' .gd-pagination-button');
            pageButtons.each(function() {
                // bind event only once - prevents duplicate events
                if (typeof $(this).data('events') == 'undefined' || typeof $(this).data('events').click == 'undefined') {
                    $(this).click(function() {
                        ReportTable_Runtime.PageClick($(this).attr('reportId'), $(this).attr('page'));
                    });
                }
            });
        },

        PageClick: function(reportId, page) {
            // Report Builder
            if ( typeof ReportConfig != "undefined" && ReportSection.getContainer().isVisible() ) {
                ReportView_Runtime.getTableView(reportId, page);
            }
            // Dashboard Builder
            else {
                var params = ReportTable_Runtime.getDashReportSize(reportId);
                ReportView_Runtime.getTableView(reportId, page, params);
            }
        },

        ColumnClick: function(reportId, column) {
            ReportTable_Runtime.getTableSort(reportId).push(column);
            // Report Builder
            if ( typeof ReportConfig != "undefined" && ReportSection.getContainer().isVisible() ) {
                ReportView_Runtime.getTableView(reportId);
            }
            // Dashboard Builder
            else {
                var params = ReportTable_Runtime.getDashReportSize(reportId);
                ReportView_Runtime.getTableView(reportId, 1, params);
            }
        },

        getDashReportSize: function(reportId) {
            var items = DashboardCanvas.getInstance().children;
            var pos = {};
            for (var i=0, iCount=items.length; i<iCount; i++) {
                if (items[i].report == reportId) {
                    pos = items[i].getPosition();
                    break;
                }
            }
            var borderAdj = 4; // border adjustment: dashed border width
            var size = {};
            if (pos.hasOwnProperty('width') && pos.hasOwnProperty('height')) {
                size = {w:pos.width-borderAdj, h:pos.height-borderAdj};
            }

            return size;
        },

        getDrilldowns: function(reportId) {
            var drilldowns = {};

            if (typeof $('#dataTable_'+reportId).attr('drilldowns') != 'undefined') {
                drilldowns = $('#dataTable_'+reportId).attr('drilldowns').replace(/'/g, '"');
            }

            return drilldowns;
        },

        getFilters: function(reportId) {
            var filters = {};

            if (typeof $('#dataTable_'+reportId).attr('filters') != 'undefined') {
                filters = $('#dataTable_'+reportId).attr('filters').replace(/'/g, '"');
            }

            return filters;
        },

        showSortArrow: function(reportId) {
            if (ReportTable_Runtime.getTableSortSimple(reportId).length) {
                var sortCol = ReportTable_Runtime.getTableSortSimple(reportId)[0];
                var colName = sortCol.column;
                var colOrder = sortCol.order;
                $("#report-"+reportId+" th").each(function(){
                    if ( $(this).attr("column") == colName && colOrder == "asc" ) {
                        $(this).removeClass("advTableSortDesc");
                        $(this).addClass("advTableSortAsc");
                    }
                    else if ( $(this).attr("column") == colName && colOrder == "desc" ) {
                        $(this).removeClass("advTableSortAsc");
                        $(this).addClass("advTableSortDesc");
                    }
                });
            }
        },

        getTableSorts: function() {
            return this.tableSorts;
        },

        getTableSort: function(reportId) {
            if (typeof this.tableSorts[reportId] == "undefined") {
                this.tableSorts[reportId] = [];
            }

            return this.tableSorts[reportId];
        },

        setTableSorts: function(tableSorts) {
            this.tableSorts = tableSorts;
        },

        setTableSort: function(reportId, tableSort) {
            this.tableSorts[reportId] = tableSort;
        },

        getTableSortSimple: function(reportId) {
            var s = this.getTableSort(reportId);

            var colCounts = {};
            var colSortOrder = [];
            for ( var i=0, sCount=s.length; i<sCount; i++ ) {
                if ( typeof colCounts[s[i]] == "undefined" ) {
                    colCounts[s[i]] = 1;
                } else {
                    colCounts[s[i]]++;
                }

                for ( var j=0, oCount=colSortOrder.length; j<oCount; j++ ) {
                    if ( colSortOrder[j] == s[i] ) {
                        colSortOrder.splice(j, 1);
                        break;
                    }
                }
                colSortOrder.push(s[i]);
            }

            var colOrder = {};
            for (key in colCounts) {
                var column = key;
                var order = "asc";
                if (colCounts[key]%2 == 0) {
                    order = "desc";
                }
                colOrder[column] = ({"column":column, "order":order});
            }

            var tableSort = [];
            for ( var i=0, soCount=colSortOrder.length; i<soCount; i++ ) {
                tableSort.push(colOrder[colSortOrder[i]]);
            }

            return tableSort.reverse();
        },

        getTableSortSerialized: function(reportId) {
            var sort = this.getTableSortSimple(reportId);
            var sortArr = [];
            for (var i=0, sortCount=sort.length; i<sortCount; i++) {
                if (sort[i].order == 'desc') {
                    sortArr.push('-'+sort[i].column);
                } else {
                    sortArr.push(sort[i].column);
                }
            }

            return sortArr.toString();
        }
    };

    global.ReportTable_Runtime = ReportTable_Runtime;

})(typeof window === 'undefined' ? this : window, jQuery);