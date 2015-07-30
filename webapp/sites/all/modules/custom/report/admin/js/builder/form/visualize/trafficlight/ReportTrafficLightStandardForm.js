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
        throw new Error('ReportTrafficLightStandardForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportTrafficLightStandardForm requires GD');
    }

    var GD = global.GD;

    global.GD.ReportTrafficLightStandardForm = GD.View.extend({

        init: function(object, container, options) {
            this._super(object, container, options);
            var _this = this;
            $(document).off('save.report.visualize').on('save.report.visualize',function(){
                _this.saveOptions();
            });
            this.integerType = ['integer', 'number', 'percent', 'currency'];
            this.stringType = ['string'];
        },

        getController: function () {
            return this.options.builder;
        },


        getFormContainer: function() {
            if ( !this.formContainer ) {
                this.formContainer = $([
                    '<form class="form-horizontal">',
                    '<div class="col-sm-12">',

                    '<div class="form-group">',
                    '<label>Traffic Light For Column '+this.object.displayName+'</label>',
                    '<div class="TrafficLightColimnList"></div>',
                    '</div>',

                    '<div class="reportTrafficLightStringValue">',
                    '<label for="reportTrafficLighName col-sm-12">Value Criteria:</label>',
                    '<div class="form-group">',
                    '<div class="col-sm-2">',
                    '<img src="/sites/all/modules/custom/webui/admin/images/traffic_light_red.png" style="padding: 8px 0;">',
                    '</div>',
                    '<div class="col-sm-10">',
                    '<input type="text" name="trafficRedValue" class="form-control input-sm reportDisplayTrafficLightFixedVal">',
                    '</div>',
                    '</div>',
                    '<div class="form-group">',
                    '<div class="col-sm-2">',
                    '<img src="/sites/all/modules/custom/webui/admin/images/traffic_light_yellow.png" style="padding: 8px 0;">',
                    '</div>',
                    '<div class="col-sm-10">',
                    '<input type="text" name="trafficYellowValue" class="form-control input-sm reportDisplayTrafficLightFixedVal">',
                    '</div>',

                    '</div>',
                    '<div class="form-group">',
                    '<div class="col-sm-2">',
                    '<img src="/sites/all/modules/custom/webui/admin/images/traffic_light_green.png" style="padding: 8px 0;">',
                    '</div>',
                    '<div class="col-sm-10">',
                    '<input type="text" name="trafficGreenValue" class="form-control input-sm reportDisplayTrafficLightFixedVal">',
                    '</div>',
                    '</div>',
                    '</div>',




                    '<div class="reportTrafficLightIntegerValue form-group">',
                    '<div class="form-group">',
                    '<div class="col-sm-1">',
                    '<img src="/sites/all/modules/custom/webui/admin/images/traffic_light_red.png" style="padding: 8px 0;">',
                    '</div>',
                    '<div class="col-sm-4">',
                    '<input type="text" name="trafficRedFrom" class="form-control input-sm tarfficLightFrom0">',
                    '</div>',
                    '<label class="col-sm-2 control-label"> to </label>',
                    '<div class="col-sm-4">',
                    '<input type="text" name="trafficRedTo" class="form-control input-sm tarfficLightTo0">',
                    '</div>',
                    '</div>',

                    '<div class="form-group">',
                    '<div class="col-sm-1">',
                    '<img src="/sites/all/modules/custom/webui/admin/images/traffic_light_yellow.png" style="padding: 8px 0;">',
                    '</div>',
                    '<div class="col-sm-4">',
                    '<input type="text" name="trafficYellowFrom" class="form-control input-sm tarfficLightFrom1">',
                    '</div>',
                    '<label class="col-sm-2 control-label"> to </label>',
                    '<div class="col-sm-4">',
                    '<input type="text" name="trafficYellowTo"  class="form-control input-sm tarfficLightTo1">',
                    '</div>',
                    '</div>',

                    '<div class="form-group">',
                    '<div class="col-sm-1">',
                    '<img src="/sites/all/modules/custom/webui/admin/images/traffic_light_green.png" style="padding: 8px 0;">',
                    '</div>',
                    '<div class="col-sm-4">',
                    '<input type="text" name="trafficGreenFrom" class="form-control input-sm tarfficLightFrom2">',
                    '</div>',
                    '<label class="col-sm-2 control-label"> to </label>',
                    '<div class="col-sm-4">',
                    '<input type="text" name="trafficGreenTo"  class="form-control input-sm tarfficLightTo2">',
                    '</div>',
                    '</div>',
                    '</div>',
                    '<div class="form-group">',
                    '<div class="checkbox" style="padding-left: 0;">',
                    '<label>',
                    '<input type="checkbox" class="form-control input-sm"  name="reportDisplayTrafficLightOption" checked>',
                    ' Display Traffic Light Image</label>',
                    '</div>',
                    '</div>',
                    '<div class="reportDisplayTrafficLightImageOption">',
                    '<label class="imgposition">Image Position:</label>',
                    '<label class="radio">',
                    '<input type="radio" name="reportTrafficlightImageOption" value="replace" checked> Replace Value in Column',
                    '</label>',
                    '<label class="radio">',
                    '<input type="radio" name="reportTrafficlightImageOption" value="left"> Separate Column, Left of Value',
                    '</label>',
                    '<label class="radio">',
                    '<input type="radio" name="reportTrafficlightImageOption" value="right"> Separate Column, Right of Value',
                    '</label>',
                    '</div>',

                    '<div class="reportTrafficImageColumn form-group">',
                    '<label>Image Column Title: </label>',
                    '<input type="text" class="form-control input-sm reportDisplayTrafficImageColumnTitle" value="Status">',
                    '</div>',
                    '</div>',

                    '</form>'
                ].join("\n"));
            }

            return this.formContainer;
        },

        getTrafficLightColumnSelected: function() {
            return this.formContainer.find('#columnlistcontainer').val();
        },
        setTrafficLightColumnSelected: function( value ) {
            this.formContainer.find('option[value="'+value+'"]').attr("selected","selected");
        },

        getTrafficLightSeletecdColumnType: function() {
            var trafficLightColumnSelected = this.getTrafficLightColumnSelected();
            if(trafficLightColumnSelected) {
                var selectedColumn = this.getController().getReport().getColumn(trafficLightColumnSelected);
                return this.trafficLightSeletecdColumnType = selectedColumn.type;
            }
        },

        getTrafficLightValue: function() {
            var trafficLightValue = {};
            var container = this.formContainer;
            trafficLightValue['trafficRedValue'] = container.find('input[name="trafficRedValue"]').val();
            trafficLightValue['trafficYellowValue'] = container.find('input[name="trafficYellowValue"]').val();
            trafficLightValue['trafficGreenValue'] = container.find('input[name="trafficGreenValue"]').val();
            return trafficLightValue;
        },

        getTrafficLightFromToValue: function() {
            var trafficLightValue = {};
            var container = this.formContainer;
            trafficLightValue['trafficRedValue'] = container.find('input[name="trafficRedValue"]').val();
            trafficLightValue['trafficYellowValue'] = container.find('input[name="trafficYellowValue"]').val();
            trafficLightValue['trafficGreenValue'] = container.find('input[name="trafficGreenValue"]').val();
            return trafficLightValue;
        },


        getTrafficLightValueFrom: function (counter) {
            return this.formContainer.find('.tarfficLightFrom'+counter).val();
        },
        setTrafficLightValueFrom: function (counter,value) {
            this.formContainer.find('.tarfficLightFrom'+counter).val(value);

        },

        //Set Traffic Light Text Value For String Column Type
        setdefaultTrafficRedValue: function(value) {
            this.formContainer.find('input[name="trafficRedValue"]').val( value );
        },

        setdefaultTrafficYellowValue: function(value) {
            this.formContainer.find('input[name="trafficYellowValue"]').val( value );
        },

        setdefaultTrafficGreenValue: function(value) {
            this.formContainer.find('input[name="trafficGreenValue"]').val( value );
        },

        //Set Traffic Light From Value For Integer Column Type
        setdefaultTrafficRedFromValue: function(value) {
            this.formContainer.find('input[name="trafficRedFrom"]').val( value );
        },

        setdefaultTrafficYellowFromValue: function(value) {
            this.formContainer.find('input[name="trafficYellowFrom"]').val( value );
        },

        setdefaultTrafficGreenFromValue: function(value) {
            this.formContainer.find('input[name="trafficGreenFrom"]').val( value );
        },

        //Set Traffic Light To Value For Integer Column Type
        setdefaultTrafficRedToValue: function(value) {
            this.formContainer.find('input[name="trafficRedTo"]').val( value );
        },

        setdefaultTrafficYellowToValue: function(value) {
            this.formContainer.find('input[name="trafficYellowTo"]').val( value );
        },

        setdefaultTrafficGreenToValue: function(value) {
            this.formContainer.find('input[name="trafficGreenTo"]').val( value );
        },

        getTrafficLightValueTo: function (counter) {
            return this.formContainer.find('.tarfficLightTo'+counter).val();
        },

        setTrafficLightValueTo: function (counter,value) {
            this.formContainer.find('.tarfficLightTo'+counter).val( value );
        },

        getTrafficLightImageSetting: function () {
            if(this.formContainer.find('input[name="reportDisplayTrafficLightOption"]').is(':checked') == true) {
                return this.getTrafficLightImageVal = 1;
            } else {
                return this.getTrafficLightImageVal = 0;
            }
        },

        setTrafficLightImageSetting: function ( value ) {
            if(value == 1) {
                this.formContainer.find('input[name="reportDisplayTrafficLightOption"]').prop('checked',true);
            }else{
                this.formContainer.find('input[name="reportDisplayTrafficLightOption"]').prop('checked',false);
            }
        },

        getTrafficLightImagePositionSetting: function () {
            return this.formContainer.find('input[name="reportTrafficlightImageOption"]:checked').val();
        },

        setTrafficLightImagePositionSetting: function ( value ) {
            this.formContainer.find('input[name="reportTrafficlightImageOption"][value="'+value+'"]').prop('checked',true);
        },

        getTrafficLightImageColumntitle: function() {
            return this.formContainer.find('.reportDisplayTrafficImageColumnTitle').val();
        },

        setTrafficLightImageColumntitle: function( value ) {
            this.formContainer.find('.reportDisplayTrafficImageColumnTitle').val(value);
        },

        showReportDisplayTrafficLightOption: function(){
            var formContainer = this.formContainer;
            if(formContainer.find('input[name="reportDisplayTrafficLightOption"]').is(':checked') == true && this.getController().getReport().getChartType() !== 'pivot_table') {
                formContainer.find(".reportDisplayTrafficLightImageOption").show();
                if(formContainer.find('input[name="reportTrafficlightImageOption"]:checked').val() != 'replace'){
                    formContainer.find(".reportTrafficImageColumn").show();
                }
            }  else {
                formContainer.find(".reportDisplayTrafficLightImageOption").hide();
                if(formContainer.find('input[name="reportTrafficlightImageOption"]:checked').val() != 'replace'){
                    formContainer.find(".reportTrafficImageColumn").hide();
                }
             }
        },

        showTrafficLightImageOption: function() {
            var reportObj = this.getController().getReport(),
                _this = this;
            var container = _this.formContainer;
            container.find(".reportDisplayTrafficLightImageOption").hide();
            container.find(".reportTrafficImageColumn").hide();
            if(reportObj.getVisual().trafficDisplayImage == 1) {
                container.find(".reportDisplayTrafficLightImageOption").show();
            } else {
                container.find(".reportDisplayTrafficLightImageOption").hide();
            }

            if(reportObj.getVisual().traffic[this.object.columnId] &&
                reportObj.getVisual().traffic[this.object.columnId].trafficDisplayImagePosition &&
                reportObj.getVisual().traffic[this.object.columnId].trafficDisplayImagePosition!= "replace" &&
                this.getController().getReport().getChartType() !== 'pivot_table'
            ) {
                container.find(".reportTrafficImageColumn").show();
            } else {
                container.find(".reportTrafficImageColumn").hide();
            }

            this.showReportDisplayTrafficLightOption();

            container.find('input[name="reportDisplayTrafficLightOption"]').on('click',function(){
                _this.showReportDisplayTrafficLightOption.call(_this);
            });

            container.find('input[name="reportTrafficlightImageOption"]').on('click',function(){
                if(container.find('input[name="reportTrafficlightImageOption"]:checked').val() != 'replace') {
                    if(!_this.getTrafficLightImageColumntitle()){
                        _this.setTrafficLightImageColumntitle("Status");
                    }
                    container.find(".reportTrafficImageColumn").show();
                } else {
                    container.find(".reportTrafficImageColumn").hide();
                }
            });
        },

        saveOptions: function () {
            if(this.formContainer.is(":visible")){
                var trafficData ={},
                    reportObj = this.getController().getReport();
                if(reportObj.getVisual().traffic) {
                    trafficData = reportObj.getVisual().traffic;
                }

                //var columnId = this.object.columnId;

                var columnId = {};
                columnId['trafficColumn'] = this.object.columnId;
                columnId['trafficDisplayImage'] = this.getTrafficLightImageSetting();
                if(this.getTrafficLightImageSetting() == 1) {
                    columnId['trafficDisplayImagePosition'] = this.getTrafficLightImagePositionSetting()
                }

                if(this.getTrafficLightImagePositionSetting() != "replace") {
                    columnId['trafficDisplayImageTitle'] = this.getTrafficLightImageColumntitle()
                }

                if ($.inArray(this.object.columnType,this.stringType)  > -1 ) {
                    columnId['trafficRedValue'] = this.getTrafficLightValue().trafficRedValue;
                    columnId['trafficYellowValue'] = this.getTrafficLightValue().trafficYellowValue;
                    columnId['trafficGreenValue'] = this.getTrafficLightValue().trafficGreenValue;
                }

                if ($.inArray(this.object.columnType,this.integerType)  > -1 ) {
                    var trafficLightFrom = ['trafficRedFrom', 'trafficYellowFrom', 'trafficGreenFrom'];
                    var trafficLightTo = ['trafficRedTo', 'trafficYellowTo', 'trafficGreenTo'];

                    for(var i=0; i<=2; i++) {
                        columnId[trafficLightFrom[i]] = this.getTrafficLightValueFrom(i);
                        columnId[trafficLightTo[i]] = this.getTrafficLightValueTo(i);
                    }
                }

                trafficData[this.object.columnId] = columnId;

                this.getController().getReport().setVisualizationOption('traffic', trafficData);
            }

        },

        render: function() {
            if ( this.container ) {
                this.container.append(this.getFormContainer());

                this.formContainer.find('.reportTrafficLightIntegerValue').hide();
                this.formContainer.find('.reportTrafficLightStringValue').hide();


                if ($.inArray(this.object.columnType,this.integerType)  > -1 ) {
                    this.formContainer.find('.reportTrafficLightIntegerValue').show();
                }

                if ($.inArray(this.object.columnType,this.stringType)  > -1 ) {
                    this.formContainer.find('.reportTrafficLightStringValue').show();
                }

                var reportObj = this.getController().getReport();
                //Set default From value for Traffic Light
                if(reportObj.getVisual().traffic[this.object.columnId]) {

                    if(reportObj.getVisual().traffic[this.object.columnId].trafficRedFrom) {
                        this.setdefaultTrafficRedFromValue(reportObj.getVisual().traffic[this.object.columnId].trafficRedFrom);
                    }

                    if(reportObj.getVisual().traffic[this.object.columnId].trafficYellowFrom) {
                        this.setdefaultTrafficYellowFromValue(reportObj.getVisual().traffic[this.object.columnId].trafficYellowFrom);
                    }

                    if(reportObj.getVisual().traffic[this.object.columnId].trafficGreenFrom) {
                        this.setdefaultTrafficGreenFromValue(reportObj.getVisual().traffic[this.object.columnId].trafficGreenFrom);
                    }

                    //Set default To value for Traffic Light
                    if(reportObj.getVisual().traffic[this.object.columnId].trafficRedTo) {
                        this.setdefaultTrafficRedToValue(reportObj.getVisual().traffic[this.object.columnId].trafficRedTo);
                    }

                    if(reportObj.getVisual().traffic[this.object.columnId].trafficYellowTo) {
                        this.setdefaultTrafficYellowToValue(reportObj.getVisual().traffic[this.object.columnId].trafficYellowTo);
                    }

                    if(reportObj.getVisual().traffic[this.object.columnId].trafficGreenTo) {
                        this.setdefaultTrafficGreenToValue(reportObj.getVisual().traffic[this.object.columnId].trafficGreenTo);
                    }

                    //Set default Value for String Value
                    if(reportObj.getVisual().traffic[this.object.columnId].trafficRedValue) {
                        this.setdefaultTrafficRedValue(reportObj.getVisual().traffic[this.object.columnId].trafficRedValue);
                    }

                    if(reportObj.getVisual().traffic[this.object.columnId].trafficYellowValue) {
                        this.setdefaultTrafficYellowValue(reportObj.getVisual().traffic[this.object.columnId].trafficYellowValue);
                    }

                    if(reportObj.getVisual().traffic[this.object.columnId].trafficGreenValue) {
                        this.setdefaultTrafficGreenValue(reportObj.getVisual().traffic[this.object.columnId].trafficGreenValue);
                    }

                    this.setTrafficLightColumnSelected(reportObj.getVisual().traffic[this.object.columnId].trafficColumn);

                    this.setTrafficLightImageSetting(reportObj.getVisual().traffic[this.object.columnId].trafficDisplayImage);

                    if(reportObj.getVisual().traffic[this.object.columnId].trafficDisplayImage == 1) {
                        this.setTrafficLightImagePositionSetting(reportObj.getVisual().traffic[this.object.columnId].trafficDisplayImagePosition);
                        $('.reportDisplayTrafficLightImageOption').show();
                        this.setTrafficLightImageColumntitle(reportObj.getVisual().traffic[this.object.columnId].trafficDisplayImageTitle);
                    }
                }
                this.showTrafficLightImageOption();
                if (reportObj.getChartType() !== 'table') {
                    $('.reportDisplayTrafficLightImageOption').hide();
                }
            }
            return this.formContainer;
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);