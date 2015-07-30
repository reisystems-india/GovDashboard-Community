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

//  TODO Write this completely.
(function(global,undefined) {

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportMenuView requires GD');
    }

    var GD = global.GD;

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
        footerClone.find('span').wrap('<p></p>');
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


    (function($,Highcharts) {

        if ( typeof $ === 'undefined' ) {
            throw new Error('ReportMenuView Requires jQuery');
        }

        var ReportMenuView = GD.View.extend({

            init: function ( object, container ) {

                this._super(object, container);
                this.id = object.id;
                this.menu = $('<ul></ul>').attr('class', 'gd-report-menu');

                return this;
            },

            getMenu: function() {
                return this.menu;
            },

            setMenu: function(menu) {
                this.menu = menu;
            },

            getViewSection:function (reportId, reportProperties, tabIndex) {
                var list = $('<li id="gd-report-view-link-' + reportId + '" class="gd-report-menu-section gd-report-menu-list-item"></li>');
                this.viewLink = $('<a title="Report ' + reportId +  '- View" class="gd-report-menu-anchor"><span style="width:100%;height:100%;" tabindex="' + tabIndex + '" class="gd-report-menu-link gd-report-view-link">View</span></a>');
                var subList = $('<ul id="gd-report-view-menu-' + reportId + '" class="gd-report-view-menu gd-report-menu-' + reportId + '"></ul>');
                list.append(this.viewLink);
                if (reportProperties['chartType'] != 'table') {
                    var chartOption = $('<li></li>');
                    this.chartLink = $('<a title="Report ' + reportId +  '- View Chart" tabindex="' + tabIndex + '" class="gd-report-menu-link gd-chart-link" id="gd-chart-link-' + reportId + '" report_id="' + reportId + '">Chart</a>');
                    chartOption.append(this.chartLink);
                    subList.append(chartOption);
                }

                var tableOption = $('<li></li>');
                this.tableLink = $('<a title="Report ' + reportId +  '- View Table" tabindex="' + tabIndex + '" class="gd-report-menu-link gd-table-link" id="gd-table-link-' + reportId + '" report_id="' + reportId + '">Table</a>');
                tableOption.append(this.tableLink);
                subList.append(tableOption);
                list.append($('<div></div>').append(subList));
                return list;
            },

            getActionsSection:function (reportId, reportProperties, tabIndex) {
                var _this = this;
                var list = $('<li id="gd-report-action-link-' + reportId + '" class="gd-report-menu-section gd-report-menu-list-item"></li>');
                this.actionLink = $('<a title="Report ' + reportId +  '- Actions" class="gd-report-menu-anchor"><span tabindex="' + tabIndex + '" class="gd-report-menu-link gd-report-action-link">Actions</span></a>');
                var subList = $('<ul id="gd-report-action-menu-' + reportId + '" class="gd-report-action-menu gd-report-menu-' + reportId + '"></ul>');
                list.append(this.actionLink, $('<div></div>').append(subList));

                var topMenu = $('#gd-report-top-menu-' + reportId);

                var toggleCSV = topMenu.attr('csv') == 1;
                var toggleCSVRaw = topMenu.attr('csv_raw') == 1;
                var toggleExcel = topMenu.attr('excel') == 1;
                var toggleExcelRaw = topMenu.attr('excel_raw') == 1;
                var togglePdf = topMenu.attr('pdf') == 1;

                //  Only toggle export if it is enabled and have at least one option enabled as well
                var toggleExport = topMenu.attr('export') == 1 && (toggleCSV || toggleCSVRaw || toggleExcel || toggleExcelRaw || togglePdf);
                var togglePrint = topMenu.attr('print') == 1;

                if (toggleExport) {
                    var exportOption = $('<li id="gd-report-export-link-' + reportId + '" class="gd-report-menu-list-item gd-report-export-list"></li>');
                    this.exportLink = $('<a title="Report ' + reportId +  '- Export" class="gd-report-menu-anchor"><span tabindex="' + tabIndex + '" class="gd-report-menu-link gd-export-link">Export</span></a>');
                    var exportSubList = $('<ul id="gd-report-export-menu-' + reportId + '" class="gd-report-export-menu gd-report-menu-' + reportId + '"></ul>');

                    if ( toggleCSV ) {
                        var csvOption = $('<li></li>');
                        this.csvLink = $('<a title="Report ' + reportId + '- Export CSV" tabindex="' + tabIndex + '" class="gd-report-menu-link gd-export-csv" id="gd-export-csv-' + reportId + '" report_id="' + reportId + '" href="' + this.object.parent.getExportUrl({
                            type: 'csv',
                            raw: false
                        }) + '">Export CSV</a>');
                        csvOption.append(this.csvLink);
                        exportSubList.append(csvOption);
                    }

                    if ( toggleCSVRaw ) {
                        var csvRawOption = $('<li></li>');
                        this.csvRawLink = $('<a title="Report ' + reportId + '- Export CSV Raw" tabindex="' + tabIndex + '" class="gd-report-menu-link gd-export-csv-raw" id="gd-export-csv-raw-' + reportId + '" report_id="' + reportId + '" href="' + this.object.parent.getExportUrl({
                            type: 'csv',
                            raw: true
                        }) + '">Export CSV Raw</a>');
                        csvRawOption.append(this.csvRawLink);
                        exportSubList.append(csvRawOption);
                    }

                    if ( toggleExcel ) {
                        var excelOption = $('<li></li>');
                        this.excelLink = $('<a title="Report ' + reportId + '- Export Excel" tabindex="' + tabIndex + '" class="gd-report-menu-link gd-export-excel" id="gd-export-excel-' + reportId + '" report_id="' + reportId + '" href="' + this.object.parent.getExportUrl({
                            type: 'excel',
                            raw: false
                        }) + '">Export Excel</a>');
                        excelOption.append(this.excelLink);
                        exportSubList.append(excelOption);
                    }

                    if ( toggleExcelRaw ) {
                        var excelRawOption = $('<li></li>');
                        this.excelRawLink = $('<a title="Report ' + reportId + '- Export Excel Raw" tabindex="' + tabIndex + '" class="gd-report-menu-link gd-export-excel-raw" id="gd-export-excel-raw-' + reportId + '" report_id="' + reportId + '" href="' + this.object.parent.getExportUrl({
                            type: 'excel',
                            raw: true
                        }) + '">Export Excel Raw</a>');
                        excelRawOption.append(this.excelRawLink);
                        exportSubList.append(excelRawOption);
                    }

                    if ( togglePdf && this.isHighcharts(reportProperties['chartType'])) {
                        var pdfOption = $('<li></li>');
                        this.pdfLink = $('<a title="Report ' + reportId +  '- Export PDF" tabindex="' + tabIndex + '" class="gd-report-menu-link gd-export-pdf" id="gd-export-pdf-' + reportId + '" report_id="' + reportId + '" report_type="pdf">Export PDF</a>');
                        pdfOption.append(this.pdfLink);
                        exportSubList.append(pdfOption);

                        // ----------------------------------------------
                        // bind click event only once
                        if (typeof this.pdfLink.data('events') == 'undefined' || typeof this.pdfLink.data('events').click == 'undefined') {
                            this.pdfLink.click(function () {
                                _this.object.parent.Export({
                                    type: $(this).attr('report_type'),
                                    csrf: global.GD.options.csrf
                                });
                            });
                        }
                        // 508/Keyboard Accessibility
                        if (typeof this.pdfLink.data('events') == 'undefined' || typeof this.pdfLink.data('events').keypress == 'undefined') {
                            this.pdfLink.keypress(function (e) {
                                var code = (e.keyCode ? e.keyCode : e.which);
                                if (code == 13) { // Enter key
                                    _this.object.parent.Export({
                                        type: $(this).attr('report_type'),
                                        csrf: global.GD.options.csrf
                                    });
                                }
                            });
                        }
                    }

                    exportOption.append(this.exportLink, $('<div></div>').append(exportSubList));
                    subList.append(exportOption);
                }

                if (togglePrint) {
                    // Print
                    if (reportProperties['chartType'] != 'map') {
                        var printOption = $('<li></li>');
                        this.printLink = $('<a tabindex="' + tabIndex + '" class="gd-report-menu-link gd-print-link" id="gd-print-link-' + reportId + '">Print</a>');
                        printOption.append(this.printLink);
                        subList.append(printOption);
                    }
                }

                return list;
            },

            isHighcharts:function (chartType) {
                return $.inArray(chartType, ['table', 'dynamic_text', 'map', 'sparkline', 'gauge', 'customview']) == -1;
            },

            hookEvents: function() {
                var menu = this.getMenu();
                // menu toggle event
                $(this.container).unbind('click');
                $(this.container).click(function(){
                    $(menu).toggle();
                });
                $(this.container).keyup(function (e) {
                    var code = (e.keyCode ? e.keyCode : e.which);
                    if (code == 13) { // Enter key
                        $(menu).toggle();
                    }
                });

                var reportId = this.object.id;
                $(this.container).click(function (e) {
                    $('#gd-report-menu-' + reportId).toggle();
                    $('.gd-report-menu-' + reportId).each(function () {
                        if ($(this).is(':visible')) {
                            $(this).hide();
                        }
                    });
                    e.stopPropagation();
                });

                $(this.container).keyup(function (e) {
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

                this.viewLink.click(function (e) {
                    $('#gd-report-view-menu-' + reportId).toggle();
                    $('.gd-report-menu-' + reportId).each(function () {
                        if (!$(this).hasClass('gd-report-view-menu') && $(this).is(':visible')) {
                            $(this).hide();
                        }
                    });
                    e.stopPropagation();
                });

                this.viewLink.keyup(function (e) {
                    var code = (e.keyCode ? e.keyCode : e.which);
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

                this.actionLink.click(function (e) {
                    $('#gd-report-action-menu-' + reportId).toggle();
                    $('.gd-report-menu-' + reportId).each(function () {
                        if (!$(this).hasClass('gd-report-action-menu') && $(this).is(':visible')) {
                            $(this).hide();
                        }
                    });
                    e.stopPropagation();
                });

                this.actionLink.keyup(function (e) {
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

                var toggleExport = $('#gd-report-top-menu-' + reportId).attr('export') == 1;
                if (toggleExport) {
                    this.exportLink.click(function (e) {
                        $('#gd-report-export-menu-' + reportId).toggle();
                        $('.gd-report-menu-' + reportId).each(function () {
                            if (!$(this).hasClass('gd-report-export-menu') && !$(this).hasClass('gd-report-action-menu') && $(this).is(':visible')) {
                                $(this).hide();
                            }
                        });
                        e.stopPropagation();
                    });

                    this.exportLink.keyup(function (e) {
                        var code = (e.keyCode ? e.keyCode : e.which);
                        if (code == 13) { // Enter key
                            $('#gd-report-export-menu-' + reportId).toggle();
                            $('.gd-report-menu-' + reportId).each(function () {
                                if (!$(this).hasClass('gd-report-export-menu') && !$(this).hasClass('gd-report-action-menu') && $(this).is(':visible')) {
                                    $(this).hide();
                                }
                            });
                        }
                        e.stopPropagation();
                    });
                }

                var _this = this;
                if (this.object.display == 'table') {
                    this.tableLink.on('click', function(){
                        _this.object.parent.view.showDefault();
                    });

                } else {
                    this.chartLink.on('click', function(){
                        _this.object.parent.view.showDefault();
                    });

                    this.chartLink.keyup(function (e) {
                        var code = (e.keyCode ? e.keyCode : e.which);
                        if (code == 13) { // Enter key
                            _this.object.parent.view.showDefault();
                        }
                    });

                    this.tableLink.on('click', function(){
                        _this.object.parent.view.showTable();
                        GD['ExecuteTable' + _this.object.id]();
                        $("#report-" + _this.object.id).trigger("ready.report.render");
                    });

                    this.tableLink.keyup(function (e) {
                        var code = (e.keyCode ? e.keyCode : e.which);
                        if (code == 13) { // Enter key
                            _this.object.parent.view.showTable();
                        }
                    });
                }

                var togglePrint = $('#gd-report-top-menu-' + reportId).attr('print') == 1;
                if (togglePrint) {
                    if (this.printLink) {
                        this.printLink.on('click', function(){
                            _this.object.parent.printReport();
                        });
                    }
                }

                // click on anything but report menu root -> close menu

                $('html').click(function(e){
                    if ( !$(e.target).parents('.gd-report-export-list').length &&
                        (!e.target.className ||
                            $.inArray(e.target.className,
                                [
                                    'gd-report-menu-container',
                                    'gd-report-top-menu',
                                    'gd-report-top-menu-root',
                                    'gd-report-menu-root-icon',
                                    'gd-report-menu-root-arrow'
                                ]
                            )<0)
                        ) {
                        $(menu).hide();
                    }
                });
            },

            render: function() {
                var menu = this.getMenu();
                $(this.container).append(menu);
                menu.append(this.getViewSection(this.object.id, {'chartType': this.object.display}, this.object.tabIndex), this.getActionsSection(this.object.id, {'chartType': this.object.display}, this.object.tabIndex));
                this.hookEvents();
            }
        });

        // add to global space
        global.GD.ReportMenuView = ReportMenuView;
    })(typeof global.GD_jQuery != 'undefined' ? global.GD_jQuery : jQuery, typeof global.GD_Highcharts != 'undefined' ? global.GD_Highcharts : (typeof Highcharts != 'undefined' ? Highcharts : undefined));
})(typeof window === 'undefined' ? this : window);
