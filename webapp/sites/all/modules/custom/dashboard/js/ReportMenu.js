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

/* Needed for dashboard view (/dashboards).
 TODO: replace this functionality with gd library
 * */
(function (global, $, undefined) {

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

        var footerClone = $('#gd-report-footer-' + options.reportId).clone();
        footerClone.find('style').remove();
        footerClone.find('span, div').wrap('<p></p>');
        footerClone.find('span').css('font-size', 'xx-small').css('text-align', '');

        var footer = document.createElement("input");
        footer.type = "hidden";
        footer.name = "footer";
        footer.value = footerClone.get(0).outerHTML;
        form.appendChild(footer);

        // submit
        form.submit();

        // clean up
        Highcharts.discardElement(form);
    };

    var ReportMenu = {
        initMenus:function () {
            // render all report menus
            $('.gd-report-top-menu-root').each(function () {
                var reportProperties = {};
                reportProperties['canEdit'] = Boolean($(this).attr('edit'));
                reportProperties['chartType'] = $(this).attr('chartType');
                ReportMenu.initMenu($(this).attr('report_id'), reportProperties);
            });
        },

        initMenu:function (reportId, reportProperties) {
            var topMenu = $('#gd-report-top-menu-' + reportId);
            var toggleCSV = topMenu.attr('csv') == 1;
            var toggleCSVRaw = topMenu.attr('csv_raw') == 1;
            var toggleExcel = topMenu.attr('excel') == 1;
            var toggleExcelRaw = topMenu.attr('excel_raw') == 1;
            var togglePdf = topMenu.attr('pdf') == 1;

            //  Only toggle export if it is enabled and have at least one option enabled as well
            var toggleExport = topMenu.attr('export') == 1 && (toggleCSV || toggleCSVRaw || toggleExcel || toggleExcelRaw || togglePdf);
            var togglePrint = topMenu.attr('print') == 1;
            // Inject menu lists:
            // ----------------------------------------------
            var topMenuRoot = $('#gd-report-top-menu-root-' + reportId);
            topMenuRoot.append(ReportMenu.getMenuList(reportId, reportProperties));

            $('#gd-report-top-menu-link-' + reportId).click(function (e) {
                $('#gd-report-menu-' + reportId).toggle();
                $('.gd-report-menu-' + reportId).each(function () {
                    if ($(this).is(':visible')) {
                        $(this).hide();
                    }
                });
                e.stopPropagation();
            }).keyup(function (e) {
                    var code = (e.keyCode ? e.keyCode : e.which);
                    if (code == 13) { // Enter key
                        $('#gd-report-menu-' + reportId).toggle();
                        $('.gd-report-menu-' + reportId).each(function () {
                            if ($(this).is(':visible')) {
                                $(this).hide();
                            }
                        });
                    }
                    e.stopPropagation();
                });

            $('#gd-report-view-link-' + reportId).click(function (e) {
                $('#gd-report-view-menu-' + reportId).toggle();
                $('.gd-report-menu-' + reportId).each(function () {
                    if (!$(this).hasClass('gd-report-view-menu') && $(this).is(':visible')) {
                        $(this).hide();
                    }
                });
                e.stopPropagation();
            }).keyup(function (e) {
                    var code = e.keyCode | e.which;
                    if (code == 13) { // Enter key
                        $('#gd-report-view-menu-' + reportId).toggle();
                        $('.gd-report-menu-' + reportId).each(function () {
                            if (!$(this).hasClass('gd-report-view-menu') && $(this).is(':visible')) {
                                $(this).hide();
                            }
                        });
                    }
                    e.stopPropagation();
                });

            $('#gd-report-action-link-' + reportId).click(function (e) {
                $('#gd-report-action-menu-' + reportId).toggle();
                $('.gd-report-menu-' + reportId).each(function () {
                    if (!$(this).hasClass('gd-report-action-menu') && $(this).is(':visible')) {
                        $(this).hide();
                    }
                });
                e.stopPropagation();
            }).keyup(function (e) {
                    var code = (e.keyCode ? e.keyCode : e.which);
                    if (code == 13) { // Enter key
                        $('#gd-report-action-menu-' + reportId).toggle();
                        $('.gd-report-menu-' + reportId).each(function () {
                            if (!$(this).hasClass('gd-report-action-menu') && $(this).is(':visible')) {
                                $(this).hide();
                            }
                        });
                    }
                    e.stopPropagation();
                });

            if (toggleExport) {
                $('#gd-report-export-link-' + reportId).click(function (e) {
                    $('#gd-report-export-menu-' + reportId).toggle();
                    $('.gd-report-menu-' + reportId).each(function () {
                        if (!$(this).hasClass('gd-report-export-menu') && !$(this).hasClass('gd-report-action-menu') && $(this).is(':visible')) {
                            $(this).hide();
                        }
                    });

                    e.stopPropagation();
                }).keyup(function (e) {
                        var code = e.keyCode | e.which;
                        if (code == 13) { // Enter key
                            $('#gd-report-export-menu-' + reportId).toggle();
                            $('.gd-report-menu-' + reportId).each(function () {
                                if ($(this).hasClass('gd-report-export-menu') && $(this).hasClass('gd-report-action-menu') && $(this).is(':visible')) {
                                    $(this).hide();
                                }
                            });
                        }
                        e.stopPropagation();
                    });


                if (togglePdf) {
                    // Export to PDF
                    // ----------------------------------------------
                    var pdfLink = $('#gd-export-pdf-' + reportId);
                    // bind click event only once
                    if (typeof pdfLink.data('events') == 'undefined' || typeof pdfLink.data('events').click == 'undefined') {
                        pdfLink.click(function () {
                            ReportMenu.Export($(this).attr('report_id'), {type:$(this).attr('report_type'), csrf: global.GD.options.csrf});
                        });
                    }
                    // 508/Keyboard Accessibility
                    if (typeof pdfLink.data('events') == 'undefined' || typeof pdfLink.data('events').keypress == 'undefined') {
                        pdfLink.keypress(function (e) {
                            var code = (e.keyCode ? e.keyCode : e.which);
                            if (code == 13) { // Enter key
                                ReportMenu.Export($(this).attr('report_id'), {type:$(this).attr('report_type'), csrf: global.GD.options.csrf});
                            }
                        });
                    }
                }
            }

            if (togglePrint) {
                // Print
                // ----------------------------------------------
                var printLink = $('#gd-print-link-' + reportId);
                // bind click event only once
                if (typeof printLink.data('events') == 'undefined' || typeof printLink.data('events').click == 'undefined') {
                    printLink.click(function () {
                        ReportMenu.printReport(reportId, reportProperties);
                    });
                }
                // 508/Keyboard Accessibility
                if (typeof printLink.data('events') == 'undefined' || typeof printLink.data('events').keypress == 'undefined') {
                    printLink.keypress(function (e) {
                        var code = (e.keyCode ? e.keyCode : e.which);
                        if (code == 13) { // Enter key
                            ReportMenu.printReport(reportId, reportProperties);
                        }
                    });
                }
            }

            // Bind click events for menu items:
            // ----------------------------------------------
            ReportMenu.getChartLink(reportId);
            ReportMenu.getTableLink(reportId);

            var chartType = topMenuRoot.attr('charttype');
            var tableView = (chartType == 'table');
            ReportMenu.setActiveLink(reportId, tableView);
        },

        getChartLink:function (reportId) {
            // Chart View
            // ----------------------------------------------
            var chartLink = $('#gd-chart-link-' + reportId);
            // bind click event only once
            if (typeof chartLink.data('events') == 'undefined' || typeof chartLink.data('events').click == 'undefined') {
                chartLink.click(function () {
                    ReportView.getChartView(reportId);
                });
            }
            // 508/Keyboard Accessibility
            if (typeof chartLink.data('events') == 'undefined' || typeof chartLink.data('events').keypress == 'undefined') {
                chartLink.keypress(function (e) {
                    var code = (e.keyCode ? e.keyCode : e.which);
                    if (code == 13) { // Enter key
                        ReportView.getChartView(reportId);
                    }
                });
            }

        },

        getTableLink:function (reportId) {
            // Table View
            // ----------------------------------------------
            var tableLink = $('#gd-table-link-' + reportId);
            // bind click event only once
            if (typeof tableLink.data('events') == 'undefined' || typeof tableLink.data('events').click == 'undefined') {
                tableLink.click(function () {
                    ReportView.getTableView(reportId);
                });
            }
            // 508/Keyboard Accessibility
            if (typeof tableLink.data('events') == 'undefined' || typeof tableLink.data('events').keypress == 'undefined') {
                tableLink.keypress(function (e) {
                    var code = (e.keyCode ? e.keyCode : e.which);
                    if (code == 13) { // Enter key
                        ReportView.getTableView(reportId);
                    }
                });
            }
        },

        setActiveLink:function (reportId, tableView) {
            if (typeof tableView == 'undefined') {
                tableView = false;
            }

            var chartLink = $('#gd-chart-link-' + reportId);
            var tableLink = $('#gd-table-link-' + reportId);

            // Toggle Chart/Table Menu Link
            if (tableView) {
                tableLink.addClass('gd-inactive-link');
                chartLink.removeClass('gd-inactive-link');
                $(tableLink).unbind('click');
                $(tableLink).unbind('keypress');
            } else {
                tableLink.removeClass('gd-inactive-link');
                chartLink.addClass('gd-inactive-link');
                $(chartLink).unbind('click');
                $(chartLink).unbind('keypress');
            }
        },

        printReport:function (reportId, reportProperties) {
            var reportSelector = '#report-' + reportId;
            var tableSelector = '#report-' + reportId + ' > div.dataTables_wrapper';
            var headerSelector = '#gd-report-header-' + reportId + ' > h3';

            var clone = null;
            var header = null,
                footerOptions = document.getElementById('gd-report-footer-' + reportId);
            if ($(headerSelector).length != 0) {
                header = $(headerSelector).clone().get(0);
            }

            if (this.isHighcharts(reportProperties['chartType']) && !$(reportSelector).hasClass('table-view')) {
                var chart = window['chart_' + reportId];
                var origHeight = chart.chartHeight;
                var origWidth = chart.chartWidth;
                chart.setSize(700, 850, false);
                clone = document.getElementById('report-' + reportId);

                ReportMenu.executePrint(clone.innerHTML, header, null, null, footerOptions);
                setTimeout(function () {
                    chart.setSize(origWidth, origHeight, false);
                    // pie charts won't render propertly when resized, so just pull the chart again
                    if (reportProperties['chartType'] == 'pie' || reportProperties['chartType'] == 'gauge') {
                        ReportView.getChartView(reportId);
                    }
                }, 1500);
            } else if (reportProperties['chartType'] == 'table' || $(reportSelector).hasClass('table-view')) {
                clone = $(tableSelector).clone();
                clone.find('div.table-content').css('height', '100%');
                clone.find('div.table-paging').css('display', 'none');
                ReportMenu.executePrint(clone.get(0).outerHTML, header, null, null, footerOptions);
            } else if (reportProperties['chartType'] != 'map') {
                clone = document.getElementById('report-' + reportId);
                var reportContainer = "#dashboard-report-container-" + reportId;
                var height = $(reportContainer).css('height') || $(reportContainer).attr('height');
                ReportMenu.executePrint(clone.innerHTML, header, null, {'height': height}, footerOptions);
            }

            ReportMenu.resetMaps();
        },

        // setTimeout hack necessary for Chrome: google maps not re-rendering completely
        // due to setTimeout in the print method & chrome is too fast
        resetMaps: function() {
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

        executePrint: function(markup, header, scripts, styles, footerOptions) {
            var params = [
                'width='+screen.width,
                'height='+screen.height
            ].join(',');

            var printWindow = window.open('/dashboard/report/print', 'Print', params);
            var func = function() {
                printWindow.document.body.innerHTML = '<style>a:link:after,a:visited:after{content:"";font-size:90%}</style>';
                if (header != null) {
                    printWindow.document.body.innerHTML += header.outerHTML;
                }
                if (styles != null) {
                    if (styles.height != null) {
                        printWindow.document.body.style.height = styles.height;
                    }
                }
                markup = markup.replace('&quot;', '\'');
                printWindow.document.body.innerHTML += markup;
                if (scripts != null) {
                    scripts.each(function () {
                        var script = printWindow.document.createElement('script');
                        script.type = 'text/javascript';
                        if ( $(this).attr('src') != null ) {
                            script.src = $(this).attr('src');
                        }
                        script.text = this.innerHTML;
                        printWindow.document.body.appendChild(script);
                    });
                }

                if (footerOptions != null) {
                    printWindow.document.body.innerHTML += footerOptions.outerHTML;
                }

                var isIE = /*@cc_on!@*/false || !!document.documentMode;
                 if(isIE){
                     printWindow.document.close();
                     printWindow.focus();
                     printWindow.print();
                     printWindow.close();
                 }else{
                     printWindow.matchMedia('print').addListener(function(mql) {
                         if (mql.matches) {
                             } else {
                                 printWindow.close();
                             }
                     });
                     printWindow.onafterprint = function(){
                         printWindow.close();
                     };
                     printWindow.document.close();
                     printWindow.focus();
                     printWindow.print();
                 };
            };

            printWindow.addEventListener ?
                printWindow.addEventListener("load",func,false) :
                printWindow.attachEvent && printWindow.attachEvent("onload",func);
        },

        getViewSection:function (reportId, reportProperties, tabIndex) {
            var list = $('<li id="gd-report-view-link-' + reportId + '" class="gd-report-menu-section gd-report-menu-list-item"></li>');
            var viewLink = $('<a class="gd-report-menu-anchor"><span style="width:100%;height:100%;" tabindex="' + tabIndex + '" class="gd-report-menu-link gd-report-view-link">View</span></a>');
            var subList = $('<ul id="gd-report-view-menu-' + reportId + '" class="gd-report-view-menu gd-report-menu-' + reportId + '"></ul>');
            list.append(viewLink);
            if (reportProperties['chartType'] != 'table') {
                var chartOption = $('<li></li>');
                var chartLink = $('<a tabindex="' + tabIndex + '" class="gd-report-menu-link gd-chart-link" id="gd-chart-link-' + reportId + '" report_id="' + reportId + '">Chart</a>');
                chartOption.append(chartLink);
                subList.append(chartOption);
            }

            var tableOption = $('<li></li>');
            var tableLink = $('<a tabindex="' + tabIndex + '" class="gd-report-menu-link gd-table-link" id="gd-table-link-' + reportId + '" report_id="' + reportId + '">Table</a>');
            tableOption.append(tableLink);
            subList.append(tableOption);
            list.append($('<div></div>').append(subList));
            return list;
        },

        getActionsSection:function (reportId, reportProperties, tabIndex) {
            var list = $('<li id="gd-report-action-link-' + reportId + '" class="gd-report-menu-section gd-report-menu-list-item"></li>');
            var actionLink = $('<a class="gd-report-menu-anchor"><span tabindex="' + tabIndex + '" class="gd-report-menu-link gd-report-action-link">Actions</span></a>');
            var subList = $('<ul id="gd-report-action-menu-' + reportId + '" class="gd-report-action-menu gd-report-menu-' + reportId + '"></ul>');
            list.append(actionLink, $('<div></div>').append(subList));
            var topMenu = $('#gd-report-top-menu-' + reportId);
            var toggleCSV = topMenu.attr('csv') == 1;
            var toggleCSVRaw = topMenu.attr('csv_raw') == 1;
            var toggleExcel = topMenu.attr('excel') == 1;
            var toggleExcelRaw = topMenu.attr('excel_raw') == 1;
            var togglePdf = topMenu.attr('pdf') == 1;

            //  Only toggle export if it is enabled and have at least one option enabled as well
            var toggleExport = topMenu.attr('export') == 1 && (toggleCSV || toggleCSVRaw || toggleExcel || toggleExcelRaw || togglePdf);

            var csvOption = '';
            var csvLink = '';
            var csvRawOption = '';
            var excelOption = '';
            var excelRawOption = '';
            var pdfOption = '';
            var exportOption = '';
            var exportLink = '';
            var exportSubList = '';

            if (toggleExport) {
                exportOption = $('<li id="gd-report-export-link-' + reportId + '" class="gd-report-menu-list-item gd-report-export-list"></li>');
                exportLink = $('<a title="Report ' + reportId +  '- Export Options" class="gd-report-menu-anchor"><span tabindex="' + tabIndex + '" class="gd-report-menu-link gd-export-link">Export</span></a>');
                exportSubList = $('<ul id="gd-report-export-menu-' + reportId + '" class="gd-report-export-menu gd-report-menu-' + reportId + '"></ul>');

                if (toggleCSV) {
                    csvOption = $(document.createElement('li'));
                    csvLink = $('<a title="Report ' + reportId +  '- Export CSV" tabindex="' + tabIndex + '" class="gd-report-menu-link gd-export-csv" id="gd-export-csv-' + reportId + '" report_id="' + reportId + '" href="' + this.getExportUrl(reportId, {type:'csv'}) + '">Export CSV</a>');
                    csvOption.append(csvLink);
                }
            }

            if ( !this.isPublicUrl() && reportProperties['canEdit'] ) {
                var editOption = $('<li></li>');
                var editLink = $('<a title="Report ' + reportId +  '- Edit Report" tabindex="' + tabIndex + '" class="gd-report-menu-link gd-edit-link" href="/cp/report/' + reportId + '">Edit</a>');
                editOption.append(editLink);
                subList.append(editOption);
            }

            if (toggleExport) {
                if (toggleCSVRaw) {
                    csvRawOption = $(document.createElement('li'));
                    var csvRawLink = $('<a title="Report ' + reportId +  '- Export CSV Raw" tabindex="' + tabIndex + '" class="gd-report-menu-link gd-export-csv-raw" id="gd-export-csv-raw-' + reportId + '" report_id="' + reportId + '" href="' + this.getExportUrl(reportId, {type:'csv', raw:true}) + '">Export CSV Raw</a>');
                    csvRawOption.append(csvRawLink);
                }

                if (toggleExcel) {
                    excelOption = $(document.createElement('li'));
                    var excelLink = $('<a title="Report ' + reportId +  '- Export Excel" tabindex="' + tabIndex + '" class="gd-report-menu-link gd-export-excel" id="gd-export-excel-' + reportId + '" report_id="' + reportId + '" href="' + this.getExportUrl(reportId, {type:'xls'}) + '">Export Excel</a>');
                    excelOption.append(excelLink);
                }

                if (toggleExcelRaw) {
                    excelRawOption = $(document.createElement('li'));
                    var excelRawLink = $('<a title="Report ' + reportId +  '- Export Excel Raw" tabindex="' + tabIndex + '" class="gd-report-menu-link gd-export-excel-raw" id="gd-export-excel-raw-' + reportId + '" report_id="' + reportId + '" href="' + this.getExportUrl(reportId, {type:'xls', raw:true}) + '">Export Excel Raw</a>');
                    excelRawOption.append(excelRawLink);
                }

                if (this.isHighcharts(reportProperties['chartType'])) {
                    if (togglePdf) {
                        pdfOption = $(document.createElement('li'));
                        var pdfLink = $('<a title="Report ' + reportId +  '- Export PDF" tabindex="' + tabIndex + '" class="gd-report-menu-link gd-export-pdf" id="gd-export-pdf-' + reportId + '" report_id="' + reportId + '" report_type="pdf">Export PDF</a>');
                        pdfOption.append(pdfLink);
                        exportSubList.append(pdfOption);
                    }
                }

                exportSubList.append(csvOption, csvRawOption, excelOption, excelRawOption);
                exportOption.append(exportLink, $(document.createElement('div')).append(exportSubList));
                subList.append(exportOption);
            }

            var togglePrint = topMenu.attr('print') == 1;
            if (togglePrint) {
                // Print
                if (reportProperties['chartType'] != 'map') {
                    var printOption = $(document.createElement('li'));
                    var printLink = $('<a tabindex="' + tabIndex + '" class="gd-report-menu-link gd-print-link" id="gd-print-link-' + reportId + '">Print</a>');
                    printOption.append(printLink);
                    subList.append(printOption);
                }
            }

            return list;
        },

        getMenuList:function (reportId, reportProperties) {
            var tabIndex = 3000;
            var list = $('<ul class="gd-report-menu" id="gd-report-menu-' + reportId + '"></ul>');
            var view = this.getViewSection(reportId, reportProperties, tabIndex);
            var actions = this.getActionsSection(reportId, reportProperties, tabIndex);
            list.append(view, actions);
            return list;
        },

        isHighcharts:function (chartType) {
            return $.inArray(chartType, ['table', 'dynamic_text', 'map', 'sparkline', 'customview', 'pivot_table']) == -1;
        },

        isPublicUrl:function () {
            return /\/public\//i.test(document.URL);
        },

        getExportUrl:function (report, options) {
            var params;
            var type = 'csv';
            var queries = [];
            var dashboard = /id=(\d*)/g.exec(String(location.search));
            var url = '';
            var filters = location.search.match(/(t.[^&]+)/g);

            if (options.type) {
                type = options.type;
            }

            //  add dashboard id
            if (dashboard) {
                queries.push('dashboard=' + dashboard[1]);
                dashboard = dashboard[1];
            } else {
                var f = /t\[(\d*)]/g.exec(String(location.search));

                if ( !f ) {
                    f = /dashboard\/(\d*)/g.exec(String(location.pathname));
                }

                if (f) {
                    queries.push('dashboard=' + f[1]);
                    dashboard = f[1];
                }
            }

            // cache?
            if ( ReportMenu.isPublicUrl() ) {
                queries.push('cache=1');
                queries.push('type='+type);
                queries.push('report='+report);
                url = '/public/dashboard/' + dashboard + '/export';
            } else {
                url = '/api/report/' + report + '/export.' + type;
            }

            //  add filters to query
            if (filters) {
                $.each(filters, function (i, filter) {
                    queries.push(filter);
                });
            }
            if (options.raw) {
                queries.push('raw=true');
            }

            params = '?' + queries.join('&');

            return url + params;
        },

        Export:function (report, options) {

            if (typeof window['chart_' + report] != 'undefined') {
                var chartOptions = {};

                var exportOptions = {
                    url:this.getExportUrl(report, options),
                    type:options.type,
                    filename:null,
                    width:null,
                    reportId: report,
                    csrf: options.csrf
                };

                window['chart_' + report].exportChart(exportOptions, chartOptions);
            }
        }
    };

    $(document).ready(function () {
        ReportMenu.initMenus();
    });

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

    global.ReportMenu = ReportMenu;

})(typeof window === 'undefined' ? this : window, jQuery);
