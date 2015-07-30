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


(function(global,undefined) {

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('Report requires GD');
    }

    var GD = global.GD;

    (function($,Highcharts) {

        if ( typeof $ === 'undefined' ) {
            throw new Error('Report Requires jQuery');
        }

        var Report = GD.Class.extend({
            init: function ( object, options ) {
                $.extend(this,object);

                var _this = this;

                this.options = options;
                this.container = 'report-wrapper-'+object.id;
                this.tabIndex = 3000;
                this.external_uri = new GD.Util.UriHandler();
                this.uri = new GD.Util.UriHandler(options.host+'/gd/ext/dashboard/'+options.dashboard+'/report/'+object.id);

                if ( this.options.requestParams )  {
                    $.each(this.options.requestParams,function(key,value){
                        _this.uri.addParam(key,value);
                    });
                }

                this.view = new GD.ReportView(this, '#'+this.container);
                this.menu = new GD.ReportMenu(this, options);

                $.each(this.filters, function(index, filter){
                    filter.applyCallback = _this.applyFilter;
                    filter.clearCallback = _this.clearFilter;
                    filter.parent = _this;
                    _this.filters[index] = new GD.Filter(filter, options);
                });
            },

            /*applyFilter: function ( filter, options ) {
                // Note: this = the Filter object, not the Report object as you might expect
                // (since it's being passed as a callback for a Filter function)

                if ( !options.value || !options.operator ) {
                    return;
                }

                filter.operator = options.operator;
                filter.value = options.value;

                var report = filter.parent;
                report.uri.addFilter(filter);
                report.view.getReport();
            },

            clearFilter: function ( name ) {
                // Note: this = the Filter object, not the Report object as you might expect
                // (since it's being passed as a callback for a Filter function)

                var report = this.parent;
                report.uri.removeFilter(name);

                // apply existing dashboard filters
                var dashboardFilters = report.parent.filters;
                for ( var i=0, count=dashboardFilters.length; i<count; i++ ) {
                    if (dashboardFilters[i].name==name && dashboardFilters[i].value && dashboardFilters[i].operator) {
                        report.uri.addFilter(dashboardFilters[i]);
                    }
                }

                report.view.getReport();
            },*/

            printReport: function() {
                var reportId = this.id;
                var _this = this;
                var chartType = $('#gd-report-top-menu-root-'+reportId).attr('chartType');

                // highcharts print
                // -------------------------------------------------------------------------------------------------
                if ( $.inArray(chartType, ['table', 'dynamic_text', 'map', 'sparkline', 'gauge']) < 0 ) {
                    // note: $.extend() doesn't work in this case because of how the
                    // highcharts objects are built and how the setSize(), print()
                    // methods manipulate them.
                    var chart = window['chart_'+reportId];
                    var origHeight = chart.chartHeight;
                    var origWidth = chart.chartWidth;

                    chart.setSize(700, 900, false);

                    var element = $('#report-'+reportId)[0];
                    $(element).gd_print();

                    // setTimeout hack necessary for Chrome: google maps not re-rendering completely
                    // due to setTimeout in the print method & chrome is too fast
                    setTimeout (function(){
                        chart.setSize(origWidth, origHeight, false);
                        // pie charts won't render properly when resized, so just pull the chart again
                        if ( chartType == 'pie' ) {
                            // default view
                            new GD.ReportContentViewDefault(_this.view.object, _this.view.contentDefaultWrapper).render();
                        }
                    }, 1500);

                // table
                // -------------------------------------------------------------------------------------------------
                } else if ( chartType == 'table' ) {
                    var element = $('#gd-print-table-'+reportId)[0];
                    $(element).gd_print_table();

                // everything else except for map
                // -------------------------------------------------------------------------------------------------
                } else if ( chartType != 'map' ) {
                    var element = $('#report-'+reportId)[0];
                    $(element).gd_print();
                }

                // setTimeout hack necessary for Chrome: google maps not re-rendering completely
                // due to setTimeout in the print method & chrome is too fast
                // todo: check if chrome
                var containers = $('[id*=dashboard-report-container-]');
                $.each(containers, function(i, container){
                    var reportId = container.id.replace('dashboard-report-container-', '');
                    if ( typeof window['gd_mapReport'+reportId] != 'undefined' ) {
                        setTimeout (function(){
                            google.maps.event.trigger(window['gd_mapReport'+reportId], 'resize');
                        }, 2000);
                    }
                });
            },

            getExportUrl: function ( options ) {

                var type = 'csv';
                if ( typeof options.type != 'undefined' ) {
                    type = options.type;
                }

                if ( type == 'excel' ) {
                    type = 'xls';
                }

                var uri = new GD.Util.UriHandler(this.options.host+'/api/report/' + this.id + '/export.' + type);

                if ( this.options != null ) {
                    if ( this.options.dashboard != null ) {
                        uri.addParam('dashboard', this.options.dashboard);
                    }
                }

                if (options.raw === true) {
                    uri.addParam('raw','true');
                }

                if ( this.options.datasource ) {
                    uri.addParam('datasource',this.options.datasource);
                }

                var ext_uri = new GD.Util.UriHandler();
                uri.addParam('origin',ext_uri.getOrigin()).mergeQueryString(ext_uri.getQueryString());

                if ( this.options.requestParams )  {
                    $.each(this.options.requestParams,function(key,value){
                        uri.addParam(key,value);
                    });
                }

                return uri.getURI();
            },

            Export: function ( options ) {

                if ( typeof window['chart_'+this.id] == 'undefined' ) {
                    return;
                }

                var chartOptions = {};

                var exportOptions = {
                    url: this.getExportUrl(options),
                    type: options.type,
                    filename: null,
                    width: null,
                    reportId: this.id,
                    csrf: options.csrf
                };

                window['chart_'+this.id].exportChart(exportOptions, chartOptions);
            }

        });

        // add to global space
        global.GD.Report = Report;
    })(typeof global.GD_jQuery != 'undefined' ? global.GD_jQuery : jQuery, typeof global.GD_Highcharts != 'undefined' ? global.GD_Highcharts : (typeof Highcharts != 'undefined' ? Highcharts : undefined));
})(typeof window === 'undefined' ? this : window);
