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

    if ( typeof $ === 'undefined' ) {
        throw new Error('ReportTypeToolbar requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportTypeToolbar requires GD');
    }

    var GD = global.GD;

    var ReportTypeToolbar = GD.View.extend({

        init: function ( object, container, options ) {
            this._super(object, container, options);

            this.chartTypes = [
                {
                    name: 'table',
                    publicName: 'Advanced Table'
                },
                {
                    name: 'line',
                    publicName: 'Line'
                },
                {
                    name: 'area',
                    publicName: 'Area'
                },
                {
                    name: 'bar',
                    publicName: 'Bar'
                },                {
                    name: 'column',
                    publicName: 'Column'
                },                {
                    name: 'pie',
                    publicName: 'Pie'
                },

                {
                    name: 'gauge',
                    publicName: 'Gauge'
                },                {
                    name: 'dynamic_text',
                    publicName: 'Dynamic Text'
                },                {
                    name: 'map',
                    publicName: 'Map'
                },                {
                    name: 'customview',
                    publicName: 'Custom View'
                },
                {
                    name: 'pivot_table',
                    publicName: 'Pivot Table'
                }
            ];


            this.attachEventHandlers();
        },

        getController: function () {
            return this.options.builder;
        },

        getToolbar: function () {
            if ( !this.toolbar ) {
                var icons = [];
                var chartIconEnabled, chartIconDisabled;
                for ( var i = 0, iconCount=this.chartTypes.length; i < iconCount; i++ ) {  

                   switch (this.chartTypes[i].name) {
                    case "table":
                        chartIconEnabled = "Advanced Table";
                        chartIconDisabled ="Advanced Table";
                         break;
                    case "line":
                       chartIconEnabled = "Line";
                       chartIconDisabled ="Line - You must have at least one numeric and at least one non-numeric value for this report type to function.";
                       break;
                    case "area":
                       chartIconEnabled = "Area";
                       chartIconDisabled ="Area - You must have at least one numeric and at least one non-numeric value for this report type to function.";
                       break;
                    case "bar":
                       chartIconEnabled = "Bar";
                       chartIconDisabled ="Bar - You must have at least one numeric and at least one non-numeric value for this report type to function.";
                       break;
                    case "column":
                       chartIconEnabled = "Column";
                       chartIconDisabled ="Column - You must have at least one numeric and at least one non-numeric value for this report type to function.";
                       break;
                    case "pie":
                       chartIconEnabled = "Pie";
                       chartIconDisabled ="Pie - You must have exactly 1 numeric column and at least 1 non-numeric column for this report type to function.";
                       break;
                    case "gauge":
                       chartIconEnabled = "Gauge";
                       chartIconDisabled ="Gauge - You must have exactly one numeric column for this report type to function."; break;
                    case "dynamic_text":
                       chartIconEnabled = "Dynamic Text";
                       chartIconDisabled ="Dynamic Text";
                       break;
                    case "map":
                       chartIconEnabled = "Map";
                       chartIconDisabled ="Map - You must have at least one numeric and at least one non-numeric value for this report type to function.";
                       break;
                    case "customview":
                       chartIconEnabled = "Custom View";
                       chartIconDisabled ="Custom View";
                        break;
                    case "pivot_table":
                        chartIconEnabled = "Pivot Table";
                        chartIconDisabled = "Pivot Table - You must have at least one measure for this report type to function.";
                        break;
                    }

                    icons.push('<div tabindex="3000" class="chart-icon chart-icon-'+this.chartTypes[i].name+'" data-report-type="'+this.chartTypes[i].name+'" data-chart-Enabled="'+chartIconEnabled+'" data-chart-Disabled="'+chartIconDisabled+'" data-toggle="tooltip" data-placement="left" ></div>');
                }
                this.toolbar = $('<div></div>').append(icons.join("\n"));
            }
            return this.toolbar;
        },

        updateToolbar: function () {
            var disabledReports = [],
                columnTypeCount = this.getController().getReport().getSelectedColumnsTypeCount(),
                numericColumnCount = columnTypeCount.numericColumnCount,
                nonNumericColumnCount = columnTypeCount.nonNumericColumnCount,
                measureCount = columnTypeCount.measureCount;

            var toolbarObj = this.getToolbar();
            $("div.chart-icon", toolbarObj).addClass('enabled');
            $("div.chart-icon", toolbarObj).removeClass('disabled');

            if ( numericColumnCount != 1 || !nonNumericColumnCount ) {
                $("div[data-report-type='pie']", toolbarObj).removeClass('enabled');
                $("div[data-report-type='pie']", toolbarObj).addClass('disabled');
                disabledReports.push("pie");
            }

            if ( numericColumnCount != 1 || nonNumericColumnCount ) {
                $("div[data-report-type='gauge']", toolbarObj).removeClass('enabled');
                $("div[data-report-type='gauge']", toolbarObj).addClass('disabled');
                disabledReports.push("gauge");
            }

            if ( !numericColumnCount || !nonNumericColumnCount ) {

                this.getToolbar().find("div[data-report-type='line'], div[data-report-type='area'], " +
                "div[data-report-type='bar'], div[data-report-type='column'], div[data-report-type='sparkline'], " +
                "div[data-report-type='map']").each(function(){
                    if($(this).hasClass("disabled")){
                        $(this).removeClass('disabled');
                        $(this).addClass('enabled');
                    }else{
                        $(this).removeClass('enabled');
                        $(this).addClass('disabled');
                        disabledReports.push($(this).data("report-type"));
                    }
                });
            }

            if (measureCount) {
                this.getToolbar().find("div[data-report-type='pivot_table']").removeClass("disabled").addClass('enabled');
            } else {
                this.getToolbar().find("div[data-report-type='pivot_table']").addClass("disabled").removeClass('enabled');
                disabledReports.push('pivot_table');
            }
            return disabledReports;
        },

        attachEventHandlers: function() {
            var _this = this,
                reportobj = _this.getController().getReport(),
                toolbarObj = this.getToolbar();
    
            this.getToolbar().find('.chart-icon').on('click',function(){
                var icon = $(this);

                icon.parent().find('.chart-icon').removeClass('active');

                if($("div[data-report-type='"+icon.data('reportType')+"']", toolbarObj).hasClass('enabled') == true) {
                    icon.addClass('active');

                    reportobj.setChartType(icon.data('reportType'));

                    $(document).trigger({
                        type: 'changed.report.type'
                    });
                }

            });

            $(document).on('loaded.report',function() {
               $("div[data-report-type='"+reportobj.getChartType()+"']", toolbarObj).addClass('enabled').addClass('active');
                _this.updateToolbar();
            });

        },

        validatePreview: function(chartType) {
            var controller = this.getController();
                reportObj = controller.getReport();
            var validationError = "",
                columnTypeCount = controller.getReport().getSelectedColumnsTypeCount(),
                numericColumnCount = columnTypeCount.numericColumnCount,
                nonNumericColumnCount = columnTypeCount.nonNumericColumnCount,
                measureCount = columnTypeCount.measureCount;

            if ( chartType !== "table" ) {

                switch ( chartType ) {

                    case "pie" :
                        if ( (numericColumnCount != 1 && measureCount != 1) || !nonNumericColumnCount ) {
                            validationError = "You must have exactly 1 numeric column and at least 1 non-numeric column for this report type to function. Go to the Table view to see the data.";
                        }
                        break;

                    case "gauge" :
                        if ( (numericColumnCount != 1 && measureCount != 1) || nonNumericColumnCount ) {
                            validationError = "You must have exactly one numeric column for this report type to function. Go to the Table view to see the data.";
                        }
                        break;

                    case "pivot_table" :
                        if(!measureCount){
                            validationError = "You must have at least one measure for this report type to function."
                        }
                        break;

                    case "customview":
                        break;

                    case "dynamic_text" :
                        break;

                    default:
                        if ( (!numericColumnCount && !measureCount) ||  !nonNumericColumnCount) {
                            validationError = "You must have at least 1 numeric or at least 1 non-numeric value for this report type to function. Go to the Table view to see the data.";
                        }
                }

                if ( validationError && controller.getCanvas().canvas && (!reportObj.hasOwnProperty("reportValid") || reportObj.reportValid !== false)) {
                    controller.getCanvas().canvas.html('<div>'+validationError+'</div>');
                    return false;
                }
            }

            return true;
        },

        render: function () {
            if ( this.container ) {
                this.container.append(this.getToolbar());
            }
            return this;
        }

    });

    // add to global space
    GD.ReportTypeToolbar = ReportTypeToolbar;

})(typeof window === 'undefined' ? this : window, jQuery);