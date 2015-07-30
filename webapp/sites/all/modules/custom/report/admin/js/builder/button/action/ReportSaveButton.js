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
        throw new Error('ReportSaveButton requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportSaveButton requires GD');
    }

    var GD = global.GD;

    var ReportSaveButton = GD.BuilderButton.extend({
        init: function(object, options) {
            this.buttonTop = $('#reportSaveTop');
            this.buttonBottom = $('#reportSaveBottom');

            this._super(null, options);

            var _this = this;
            $(document).on('report.update.post',function(e){
                _this.changeButton('reset');
            });
        },
        changeButton: function(changeParam){
            this.getButtonTop().button(changeParam);
            this.getButtonBottom().button(changeParam);
        },
        removeUnsupportedItems: function(){
            var reportObj = this.getController().getReport(),
                columnConfig = reportObj.getColumnConfigs(),
                columnDisplayOptions = reportObj.getColumnDisplayOptions(),
                trafficLightOptions = reportObj.getTrafficLightOptions(),
                selectedColumns = reportObj.getColumns(),
                i;

            for(i=columnConfig.length -1;i>=0;i--){
                if($.inArray(columnConfig[i].columnId, selectedColumns) === -1){
                    columnConfig.splice(i,1);
                }
            }
            for(i in columnDisplayOptions){
                if($.inArray(i, selectedColumns) === -1){
                    delete columnDisplayOptions[i];
                }
            }
            for(i in trafficLightOptions){
                if($.inArray(i, selectedColumns) === -1){
                    delete trafficLightOptions[i];
                }
            }

        },
        //Nasty fix to overwrite/delete visualization object properties in multiple cases.(ex. report type change)
        removeUnwantedItemsfromSaveObject:function(){
            var reportObj = this.getController().getReport(),
                reportType = reportObj.getChartType(),
                visualData = reportObj.getVisual();

            if(reportType !== "gauge"){
                if("rangeMaximum" in visualData){
                    delete visualData.rangeMaximum;
                    visualData.rangeAutoMaximum = "Auto";
                }
                if("rangeMinimum" in visualData){
                    delete visualData.rangeMinimum;
                    visualData.rangeAutoMinimum = "Auto";
                }
            }
            if($.inArray(reportType,['line', 'area', 'bar', 'column']) === -1){
                //remove x-axis display options
                var prop;
                for(prop in visualData){
                    if($.inArray(prop, ["labelRotation","displayXAxisLabel", "displayXAxisTitle", "displayXAxisTitleValue",
                            "minNumericSpan", "minNumericSpanValue", "tickInterval", "tickIntervalValue"]) !== -1){
                        delete visualData[prop];
                    }
                }
            }else{
                if(visualData.displayXAxisTitle !== "other" && "displayXAxisTitleValue" in visualData){
                    delete visualData.displayXAxisTitleValue;
                }
                if(visualData.minNumericSpan !== "Fixed" && "minNumericSpanValue" in visualData){
                    delete visualData.minNumericSpanValue;
                }
                if(visualData.tickInterval !== "Custom" && "tickIntervalValue" in visualData){
                    delete visualData.tickIntervalValue;
                }
            }
        },

        clickedButton: function( e ) {
            this.changeButton('loading');
            
            var _this = this,
                controllerObj = _this.getController(),
                reportObj = controllerObj.getReport();
            //this.removeUnsupportedItems();
            this.removeUnwantedItemsfromSaveObject();
            this.closeConfigForms();
            var reportName = reportObj.getName();
            if ( !reportName || reportName.replace(/^\s+|\s+$/g,'') === '' ) {
                this.changeButton('reset');
                global.GD.ReportBuilderMessagingView.addErrors('You must provide a report title.');
                global.GD.ReportBuilderMessagingView.displayMessages();
            } else if ( !("name" in reportObj.getDatasets()[0] && reportObj.getDatasets()[0].name)) {
                this.changeButton('reset');
                global.GD.ReportBuilderMessagingView.addErrors('You must select a dataset.');
                global.GD.ReportBuilderMessagingView.displayMessages();
            } else if ( reportObj.getColumns().length < 1 ) {
                this.changeButton('reset');
                global.GD.ReportBuilderMessagingView.addErrors('You must select a column.');
                global.GD.ReportBuilderMessagingView.displayMessages();
            } else {
                // look up name
                GD.ReportFactory.getReportList(controllerObj.getDatasourceName(), function ( data ) {
                    var unique = true;
                    for ( var i in data ) {
                        if ( data[i].title == reportName && (reportObj.isNew() || data[i].id != reportObj.getId()) ) {
                            unique = false;
                            break;
                        }
                    }

                    if ( unique ) {
                        controllerObj.saveReport();
                        /*_this.getController().getCanvas().loadPreview();*/
                    } else {
                        _this.changeButton('reset');
                        global.GD.ReportBuilderMessagingView.addErrors('Report title is not unique.');
                        global.GD.ReportBuilderMessagingView.displayMessages();
                    }

                }, function(jqXHR, textStatus, errorThrown) {
                    _this.changeButton('reset');
                    global.GD.ReportBuilderMessagingView.addErrors(jqXHR.responseText);
                    global.GD.ReportBuilderMessagingView.displayMessages();
                });
            }
        }
    });

    GD.ReportSaveButton = ReportSaveButton;

})(typeof window === 'undefined' ? this : window, jQuery);