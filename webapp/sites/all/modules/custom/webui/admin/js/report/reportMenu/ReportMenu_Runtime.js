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

(function(global,$,undefined) {

    Highcharts.Chart.prototype.exportChart = function(options, chartOptions) {
        var form,
            chart = this,
            svg = chart.getSVG(chartOptions);

        // merge the options
        options = Highcharts.merge(chart.options.exporting, options);

        // create the form
        form = Highcharts.createElement('form', {
            method: 'post',
            action: options.url
        }, {
            display: 'none'
        }, document.body);

        // add the values
        Highcharts.each(['filename', 'type', 'width', 'svg'], function(name) {
            Highcharts.createElement('input', {
                type: 'hidden',
                name: name,
                value: {
                    filename: options.filename || 'chart',
                    type: options.type,
                    width: options.width,
                    svg: svg
                }[name]
            }, null, form);
        });

        var csrf = document.createElement("input");
        csrf.type = "hidden";
        csrf.name = "HTTP-X-CSRF-TOKEN";
        csrf.value = options.csrf;
        form.appendChild(csrf);

        // submit
        form.submit();

        // clean up
        Highcharts.discardElement(form);
    };

    var ReportMenu_Runtime = {
        initMenus: function() {
            // render all report menus
            $('.gd-report-top-menu-root').one(function(){
                ReportMenu_Runtime.initMenu($(this).attr('report_id'));
            });
        },

        initMenu: function(reportId) {
            // Inject menu lists
            // ----------------------------------------------
            var reportProperties = {};
            var root = '#gd-report-top-menu-root-'+reportId;
            reportProperties['canEdit'] = $(root).attr('edit');
            reportProperties['chartType'] = $(root).attr('chartType');
            $(root).append(this.getMenuList(reportId, reportProperties));

            // GOVDB-1510: Enabling menu in the Report Builder:  To prevent export errors and exports that do not reflect
            // the current report data, Export options are disabled until it all report changes are saved.
            if ( typeof ReportConfig != 'undefined' && ReportSection.getContainer().isVisible() ) {
                if (ReportForm.getAppliedChanges().length) {
                    this.disableExportMenu();
                }
            }

            var rootLink = '#gd-report-top-menu-link-' + reportId;
            var reportMenu = '#gd-report-menu-' + reportId;
            var viewMenu = '#gd-report-view-menu-' + reportId;
            var actionMenu = '#gd-report-action-menu-' + reportId;
            var exportMenu = '#gd-report-export-menu-' + reportId;
            $(rootLink).click(function (e) {
                $(reportMenu).toggle();
                if ($(viewMenu).is(':visible')) {
                    $(viewMenu).hide();
                }
                if ($(actionMenu).is(':visible')) {
                    $(actionMenu).hide();
                    if ($(exportMenu).is(':visible')) {
                        $(exportMenu).hide();
                    }
                }
                e.stopPropagation();
            });

            $(rootLink).keyup(function (e) {
                var code = (e.keyCode ? e.keyCode : e.which);
                if (code == 13) { // Enter key
                    $(reportMenu).toggle();
                    if ($(viewMenu).is(':visible')) {
                        $(viewMenu).hide();
                    }
                    if ($(actionMenu).is(':visible')) {
                        $(actionMenu).hide();
                        if ($(exportMenu).is(':visible')) {
                            $(exportMenu).hide();
                        }
                    }
                }
                e.stopPropagation();
            });

            var viewLink = '#gd-report-view-link-' + reportId;
            $(viewLink).click(function (e) {
                $(viewMenu).toggle();
                if ($(actionMenu).is(':visible')) {
                    $(actionMenu).hide();
                    if ($(exportMenu).is(':visible')) {
                        $(exportMenu).hide();
                    }
                }
                e.stopPropagation();
            });

            $(viewLink).keyup(function (e) {
                var code = (e.keyCode ? e.keyCode : e.which);
                if (code == 13) { // Enter key
                    $(viewMenu).toggle();
                    if ($(actionMenu).is(':visible')) {
                        $(actionMenu).hide();
                        if ($(exportMenu).is(':visible')) {
                            $(exportMenu).hide();
                        }
                    }
                }
                e.stopPropagation();
            });

            var actionLink = '#gd-report-action-link-' + reportId;
            $(actionLink).click(function (e) {
                $(actionMenu).toggle();
                if ($(viewMenu).is(':visible')) {
                    $(viewMenu).hide();
                }
                if ($(exportMenu).is(':visible')) {
                    $(exportMenu).hide();
                }
                e.stopPropagation();
            });

            $(actionLink).keyup(function (e) {
                var code = (e.keyCode ? e.keyCode : e.which);
                if (code == 13) { // Enter key
                    $(actionMenu).toggle();
                    if ($(viewMenu).is(':visible')) {
                        $(viewMenu).hide();
                    }
                    if ($(exportMenu).is(':visible')) {
                        $(exportMenu).hide();
                    }
                }
                e.stopPropagation();
            });

            // Bind click events for menu items:
            ReportMenu_Runtime.getChartLink(reportId);
            ReportMenu_Runtime.getTableLink(reportId);

            var chartType = $('#gd-report-top-menu-root-'+reportId).attr('charttype');
            var tableView = (chartType == 'table');
            ReportMenu_Runtime.setActiveLink(reportId,tableView);

            var toggleExport = $('#gd-report-top-menu-' + reportId).attr('export') == 1;
            if (toggleExport) {
                var exportLink = '#gd-report-export-link-' + reportId;
                $(exportLink).click(function (e) {
                    $(exportMenu).toggle();
                    if ($(viewMenu).is(':visible')) {
                        $(viewMenu).hide();
                    }
                    e.stopPropagation();
                });

                $(exportLink).keyup(function (e) {
                    var code = (e.keyCode ? e.keyCode : e.which);
                    if (code == 13) { // Enter key
                        $(exportMenu).toggle();
                        if ($(viewMenu).is(':visible')) {
                            $(viewMenu).hide();
                        }
                    }
                    e.stopPropagation();
                });

                // Export to PDF
                // ----------------------------------------------
                var pdfLink = $('#gd-export-pdf-'+reportId);
                // bind click event only once
                if (typeof pdfLink.data('events') == 'undefined' || typeof pdfLink.data('events').click == 'undefined') {
                    pdfLink.click(function(){
                        ReportMenu_Runtime.Export($(this).attr('report_id'),{type:$(this).attr('report_type'), csrf: GovDashboard.token});
                    });
                }
            }

            var togglePrint = $('#gd-report-top-menu-' + reportId).attr('print') == 1;
            if (togglePrint) {
                // Print
                // ----------------------------------------------
                var printLink = $('#gd-print-link-' + reportId);
                // bind click event only once
                if (typeof printLink.data('events') == 'undefined' || typeof printLink.data('events').click == 'undefined') {
                    printLink.click(function () {
                        ReportMenu_Runtime.printReport(reportId, reportProperties);
                    });
                }
                // 508/Keyboard Accessibility
                if (typeof printLink.data('events') == 'undefined' || typeof printLink.data('events').keypress == 'undefined') {
                    printLink.keypress(function (e) {
                        var code = (e.keyCode ? e.keyCode : e.which);
                        if (code == 13) { // Enter key
                            ReportMenu_Runtime.printReport(reportId, reportProperties);
                        }
                    });
                }
            }
        },

        printReport:function (reportId, reportProperties) {
            var printWindow = null;
            var clone = null;
            var params = [
                'width='+screen.width,
                'height='+screen.height
            ].join(',');

            var header = $("#gd-report-header-" + reportId + " > h3").clone().get(0);
            // highchart
            // -----------------------------------------------------------------------------------------------------
            if (this.isHighcharts(reportProperties['chartType'])) {
                // note: $.extend() doesn't work in this case because of how the
                // highcharts objects are built and how the setSize(), print()
                // methods manipulate them.
                var chart = window['chart_' + reportId];
                var origHeight = chart.chartHeight;
                var origWidth = chart.chartWidth;
                chart.setSize(700, 850, false);

                printWindow = window.open('/dashboard/report/print', 'Print', params);
                if ($('#gd-chart-link-' + reportId).hasClass('gd-inactive-link')) {
                    clone = $("#report-" + reportId).clone();
                } else {
                    clone = $('#dataTable_' + reportId).clone();
                }
                printWindow[printWindow.addEventListener ? 'addEventListener' : 'attachEvent'](
                    (printWindow.attachEvent ? 'on' : '') + 'load', function () {
                        try {
                            printWindow.document.head.innerHTML += '<style>a:link:after,a:visited:after{content:"";font-size:90%}</style>';
                            printWindow.document.body.innerHTML = header.outerHTML;
                            printWindow.document.body.innerHTML += clone.get(0).outerHTML;
                            printWindow.document.close();
                            printWindow.focus();
                            printWindow.print();
                            printWindow.close();
                        } catch (e) {
                            window.console && console.log(e);
                        }
                    }, false
                );

                // setTimeout hack necessary for Chrome: google maps not re-rendering completely
                // due to setTimeout in the print method & chrome is too fast
                setTimeout(function () {
                    chart.setSize(origWidth, origHeight, false);
                    // pie charts won't render propertly when resized, so just pull the chart again
                    if (reportProperties['chartType'] == 'pie') {
                        ReportView.getChartView(reportId);
                    }
                }, 1500);

                // table
                // -----------------------------------------------------------------------------------------------------
            } else if (reportProperties['chartType'] == 'table') {
                printWindow = window.open('/dashboard/report/print', 'Print', params);
                clone = $('#dataTable_' + reportId).clone();
                printWindow[printWindow.addEventListener ? 'addEventListener' : 'attachEvent'](
                    (printWindow.attachEvent ? 'on' : '') + 'load', function () {
                        try {
                            printWindow.document.head.innerHTML += '<style>a:link:after,a:visited:after{content:"";font-size:90%}</style>';
                            printWindow.document.body.innerHTML = header.outerHTML;
                            printWindow.document.body.innerHTML += clone.get(0).outerHTML;
                            printWindow.document.close();
                            printWindow.focus();
                            printWindow.print();
                            printWindow.close();
                        } catch (e) {
                            window.console && console.log(e);
                        }
                    }, false
                );

                // everything else except for map
                // -----------------------------------------------------------------------------------------------------
            } else if (reportProperties['chartType'] != 'map') {
                printWindow = window.open('/dashboard/report/print', 'Print', params);
                clone = $("#report-" + reportId).clone();
                var scripts = $("#dashboard-report-container-" + reportId).children('script');
                var height = $("#dashboard-report-container-" + reportId).css('height') || $("#dashboard-report-container-" + reportId).attr('height');
                printWindow[printWindow.addEventListener ? 'addEventListener' : 'attachEvent'](
                    (printWindow.attachEvent ? 'on' : '') + 'load', function () {

                        printWindow.document.body.innerHTML = '<style> @media print { * { overflow:visible !important; } } a:link:after,a:visited:after{content:"";font-size:90%}</style>';
                        printWindow.document.body.innerHTML += header.outerHTML;
                        printWindow.document.body.innerHTML += clone.get(0).outerHTML;
                        printWindow.document.body.style.height = height;
                        scripts.each(function () {
                            var script = printWindow.document.createElement('script');
                            script.type = 'text/javascript';
                            if ( $(this).attr('src') != null ) {
                                script.src = $(this).attr('src');
                            }
                            script.text = this.innerHTML;
                            printWindow.document.body.appendChild(script);
                        });
                        printWindow.document.close();
                        printWindow.focus();
                        printWindow.print();
                        printWindow.close();
                    }, false
                );
            }

            // setTimeout hack necessary for Chrome: google maps not re-rendering completely
            // due to setTimeout in the print method & chrome is too fast
            // todo: check if chrome
            if (/chrome/.test(navigator.userAgent.toLowerCase())) {
                var containers = $('[id*=dashboard-report-container-]');
                $.each(containers, function (i, container) {
                    var reportId = container.id.replace('dashboard-report-container-', '');
                    if (typeof window['gd_mapReport' + reportId] != 'undefined') {
                        setTimeout(function () {
                            google.maps.event.trigger(window['gd_mapReport' + reportId], 'resize');
                        }, 2000);
                    }
                });
            }
        },

        getChartLink: function(reportId){
            // Chart View
            // ----------------------------------------------
            var chartLink = $('#gd-chart-link-'+reportId);
            // bind click event only once
            if (typeof chartLink.data('events') == 'undefined' || typeof chartLink.data('events').click == 'undefined') {
                chartLink.click(function(){
                    ReportView_Runtime.getChartView(reportId);
                });
            }
        },

        getTableLink: function(reportId) {
            // Table View
            // ----------------------------------------------
            var tableLink = $('#gd-table-link-'+reportId);
            // bind click event only once
            if (typeof tableLink.data('events') == 'undefined' || typeof tableLink.data('events').click == 'undefined') {
                tableLink.click(function(){
                    ReportView_Runtime.getTableView(reportId);
                });
            }
        },

        setActiveLink: function(reportId,tableView) {

            if ( typeof tableView == 'undefined' ) {
                tableView = false;
            }

            var chartLink = $('#gd-chart-link-'+reportId);
            var tableLink = $('#gd-table-link-'+reportId);

            // Toggle Chart/Table Menu Link
            if ( tableView ) {
                tableLink.toggleClass('gd-inactive-link');
                $(tableLink).unbind('click');
                $(tableLink).unbind('keypress');
                if ( chartLink.hasClass('gd-inactive-link') ) {
                    chartLink.toggleClass('gd-inactive-link');
                }
            } else {
                chartLink.toggleClass('gd-inactive-link');
                $(chartLink).unbind('click');
                $(chartLink).unbind('keypress');
                if ( tableLink.hasClass('gd-inactive-link') ) {
                    tableLink.toggleClass('gd-inactive-link');
                }
            }
        },

        getViewSection:function (reportId, tabIndex) {
            var list = $('<li id="gd-report-view-link-' + reportId + '" class="gd-report-menu-section gd-report-menu-list-item"></li>');
            var viewLink = $('<a class="gd-report-menu-anchor"><span style="width:100%;height:100%;" tabindex="' + tabIndex + '" class="gd-report-menu-link gd-report-view-link">View</span></a>');
            var subList = $('<ul id="gd-report-view-menu-' + reportId + '" class="gd-report-view-menu"></ul>');

            var chartType = $('#gd-report-top-menu-root-'+reportId).attr('charttype');
            if (chartType != 'table') {
                var chartOption = $('<li></li>');
                var chartLink = $('<a tabindex="' + tabIndex + '" class="gd-report-menu-link gd-chart-link" id="gd-chart-link-' + reportId + '" report_id="' + reportId + '">Chart</a>');
                chartOption.append(chartLink);
            }

            var tableOption = $('<li></li>');
            var tableLink = $('<a tabindex="' + tabIndex + '" class="gd-report-menu-link gd-table-link" id="gd-table-link-' + reportId + '" report_id="' + reportId + '">Table</a>');
            tableOption.append(tableLink);
            subList.append(chartOption, tableOption);
            if (subList.children().length != 0) {
                list.append(viewLink);
                list.append($('<div></div>').append(subList));
            }
            return list;
        },

        getActionsSection:function (reportId, reportProperties, tabIndex) {
            var list = $('<li id="gd-report-action-link-' + reportId + '" class="gd-report-menu-section gd-report-menu-list-item"></li>');
            var actionLink = $('<a class="gd-report-menu-anchor"><span tabindex="' + tabIndex + '" class="gd-report-menu-link gd-report-action-link">Actions</span></a>');
            var subList = $('<ul id="gd-report-action-menu-' + reportId + '" class="gd-report-action-menu"></ul>');
            if (!this.isPublicUrl()) {
                var toggleExport = $('#gd-report-top-menu-' + reportId).attr('export') == 1;
                if (toggleExport) {
                    var exportOption = $('<li id="gd-report-export-link-' + reportId + '" class="gd-report-menu-list-item gd-report-export-list"></li>');
                    var exportLink = $('<a class="gd-report-menu-anchor"><span tabindex="' + tabIndex + '" class="gd-report-menu-link gd-export-link">Export</span></a>');
                    var exportSubList = $('<ul id="gd-report-export-menu-' + reportId + '" class="gd-report-export-menu"></ul>');

                    var csvOption = $('<li></li>');
                    var csvLink = $('<a tabindex="' + tabIndex + '" class="gd-report-menu-link gd-export-csv" id="gd-export-csv-' + reportId + '" report_id="' + reportId + '" href="' + this.getExportUrl(reportId, {type:'csv'}) + '">Export CSV</a>');
                    csvOption.append(csvLink);

                    var csvRawOption = $('<li></li>');
                    var csvRawLink = $('<a tabindex="' + tabIndex + '" class="gd-report-menu-link gd-export-csv-raw" id="gd-export-csv-raw-' + reportId + '" report_id="' + reportId + '" href="' + this.getExportUrl(reportId, {type:'csv', raw:true}) + '">Export CSV Raw</a>');
                    csvRawOption.append(csvRawLink);

                    var excelOption = $('<li></li>');
                    var excelLink = $('<a tabindex="' + tabIndex + '" class="gd-report-menu-link gd-export-excel" id="gd-export-excel-' + reportId + '" report_id="' + reportId + '" href="' + this.getExportUrl(reportId, {type:'xls'}) + '">Export Excel</a>');
                    excelOption.append(excelLink);

                    var excelRawOption = $('<li></li>');
                    var excelRawLink = $('<a tabindex="' + tabIndex + '" class="gd-report-menu-link gd-export-excel-raw" id="gd-export-excel-raw-' + reportId + '" report_id="' + reportId + '" href="' + this.getExportUrl(reportId, {type:'xls', raw:true}) + '">Export Excel Raw</a>');
                    excelRawOption.append(excelRawLink);

                    if (this.isHighcharts(reportProperties['chartType'])) {
                        var pdfOption = $('<li></li>');
                        var pdfLink = $('<a tabindex="' + tabIndex + '" class="gd-report-menu-link gd-export-pdf" id="gd-export-pdf-' + reportId + '" report_id="' + reportId + '" report_type="pdf">Export PDF</a>');
                        pdfOption.append(pdfLink);
                        exportSubList.append(pdfOption);
                    }

                    exportSubList.append(csvOption, csvRawOption, excelOption, excelRawOption);
                    exportOption.append(exportLink, $('<div></div>').append(exportSubList));
                    subList.append(exportOption);
                }
            }

            var togglePrint = $('#gd-report-top-menu-' + reportId).attr('print') == 1;
            if (togglePrint) {
                // Print
                if (reportProperties['chartType'] != 'map') {
                    var printOption = $('<li></li>');
                    var printLink = $('<a tabindex="' + tabIndex + '" class="gd-report-menu-link gd-print-link" id="gd-print-link-' + reportId + '">Print</a>');
                    printOption.append(printLink);
                    subList.append(printOption);
                }
            }
            if (subList.children().length != 0) {
                list.append(actionLink, $('<div></div>').append(subList));
            }

            return list;
        },

        getMenuList: function(reportId, reportProperties) {
            var tabIndex = 3000;
            var list = $('<ul class="gd-report-menu" id="gd-report-menu-'+reportId+'"></ul>');
            var view = this.getViewSection(reportId, tabIndex);
            var actions = this.getActionsSection(reportId, reportProperties, tabIndex);
            list.append(view, actions);
            return list;
        },

        isHighcharts: function ( chartType ) {
            return $.inArray(chartType, ['table', 'dynamic_text', 'map', 'sparkline', 'gauge', 'customview']) == -1;
        },

        isPublicUrl: function () {
            return /\/public\//i.test(document.URL);
        },

        getExportUrl: function ( report, options ) {
            // todo: once we get GD up, need to use GD.options
            /*var host = '';
            if ( typeof GD != 'undefined' ) {
                host = 'http://'+GD.options.server;
            }*/

            var host = 'http://' + location.host;

            var type = 'csv';
            if ( typeof options.type != 'undefined' ) {
                type = options.type;
            }

            var params = '';
            if ( typeof options.raw != 'undefined' ) {
                params = '?raw=true';
            }

            // Report Builder
            if ( report == 0 && typeof Report != 'undefined') {
                return '/api/report/' + Report.getId() + '/export.' + type + params;
            // Dashboard Builder
            } else {
                return '/api/report/' + report + '/export.' + type + params;
            }
        },

        Export: function ( report, options ) {

            if ( typeof window['chart_'+report] == 'undefined' ) {
                return;
            }

            var chartOptions = {};

            var exportOptions = {
                url: this.getExportUrl(report,options),
                type: options.type,
                filename: null,
                width: null,
                reportId: report,
                csrf: options.csrf
            };

            window['chart_'+report].exportChart(exportOptions,chartOptions);
        },

        disableExportMenu: function() {
            $('.gd-export-menu').attr('title', 'Cannot Export: This Report has Unsaved Changes');
            $('.gd-export-menu').addClass('gd-export-menu-disabled');
            $('.gd-export-options').hide();
        },

        enableExportMenu: function() {
            $('.gd-export-menu').removeAttr('title');
            $('.gd-export-menu').removeClass('gd-export-menu-disabled');

            // do below instead of show() because it affects submenu mouseover show/hide behavior
            $('.gd-export-options').css('display', '');
        }
    };

    var isContextMenuItem = function (element) {
        var classes = [
            'gd-report-menu-container',
            'gd-report-top-menu',
            'gd-report-top-menu-root',
            'gd-report-menu-root-icon',
            'gd-report-menu-root-arrow',
            'gd-report-menu-link',
            'gd-report-menu-anchor',
            'gd-report-menu-list-item'
        ];

        var isItem = false;
        if (typeof element.className == 'string') {
            var classList = element.className.split(' ');
            $.each(classList, function (i, c) {
                if ($.inArray(c, classes) != -1) {
                    isItem = true;
                    return false;
                }
            });
        }

        return isItem;
    };

    $('html').click(function (e) {
        if (typeof e.focus != 'undefined') {
            e.focus();
        }
        if (!$(e.target).parents('.gd-report-export-list').length && !isContextMenuItem(e.target)) {
            $('.gd-report-menu').hide();
        }
    });

    // 508/Keyboard Accessibility - hide menu when moving onto other object
    $('*').keyup(function (e) {
        if (!$(e.target).hasClass('gd-report-top-menu-link') && !$(e.target).hasClass('gd-report-menu-link')) {
            $('.gd-report-menu').hide();
            $('.gd-report-action-menu').hide();
            $('.gd-report-view-menu').hide();
            $('.gd-report-export-menu').hide();
        }
    });

    global.ReportMenu_Runtime = ReportMenu_Runtime;

})(typeof window === 'undefined' ? this : window, jQuery);
