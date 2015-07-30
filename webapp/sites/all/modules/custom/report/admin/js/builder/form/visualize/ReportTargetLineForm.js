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
        throw new Error('ReportTargetLineForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportTargetLineForm requires GD');
    }

    var GD = global.GD;

    global.GD.ReportTargetLineForm = GD.View.extend({

        init: function(object, container, options) {
            this._super(object, container, options);
            var _this = this;
            $(document).on('save.report.visualize',function(){
                _this.saveOptions();
            });
        },

        getController: function () {
            return this.options.builder;
        },

        getFormContainer: function() {
            if ( !this.formContainer ) {
                this.formContainer = $([
                    '<div class="panel panel-default">',
                        '<div class="panel-heading">',
                            '<div class="panel-title">',
                                '<div class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#visualizePanelGroup" href="#reportTargetLinePanel">',
								'Target Line',
								'</div>',
                            '</div>',
                        '</div>',
                        '<div id="reportTargetLinePanel" class="panel-collapse collapse">',
                            '<div class="panel-body">',
                                '<form class="form-horizontal">',
                                    '<div class="form-group">',
                                            '<div class="checkbox" style="padding-left:10px;">',
                                                '<label>',
                                                '<input type="checkbox" class="form-control input-sm" name="reportDisplayShowTargetLineOption">',
                                                ' Show Target Line</label>',
                                            '</div>',
                                    '</div>',
                                        '<div class="form-group">',
                                            '<div class="reportTargetLineValue">',
                                                '<label class="control-label col-sm-3" >Value: </label>',
                                                '<div class="col-sm-9">',
                                                    '<input type="text" class="form-control reportDisplayShowTargetLineValue ">',
                                                    '<span class="glyphicon form-control-feedback"></span>',
                                                    '<div class="help-block"></div>',
                                                '</div>',
                                            '</div>',
                                        '</div>',
                                        '<div class="form-group">',
                                            '<div class="reportTargetLineColor">',
                                                '<label class="control-label col-sm-3">Color: </label>',
                                                '<div class="col-sm-9">',
                                                    '<input type="text" class="form-control reportDisplayShowTargetLineColor">',
                                                    '<span class="glyphicon form-control-feedback"></span>',
                                                    '<div class="help-block"></div>',
                                                '</div>',
                                            '</div>',
                                        '</div>',

                                    
                                '</form>',
                            '</div>',
                        '</div>',
                    '</div>'
                ].join("\n"));
            }
            this.formContainer.find('.reportDisplayShowTargetLineColor').spectrum({
                chooseText: "Apply",
                cancelText: "Cancel",
                preferredFormat: "hex",
                showInput: true,
                allowEmpty: true
            });
            return this.formContainer;
        },
        
        getTargetLineValue: function () {
            return this.formContainer.find(".reportDisplayShowTargetLineValue").val();
        },

        setTargetLineValue: function ( value ) {
            this.formContainer.find(".reportDisplayShowTargetLineValue").val( value );
        },
        
        getTargetLineColor: function () {
            return this.formContainer.find(".reportDisplayShowTargetLineColor").val();
        },

        setTargetLineColor: function ( value ) {
            this.formContainer.find(".reportDisplayShowTargetLineColor").spectrum('set', value );
        },
        
        getTargetLineSetting: function () {
            if(this.formContainer.find('input[name="reportDisplayShowTargetLineOption"]').is(':checked') == true) {
                return this.TargetLineSetting = 1;
            } else {
                return this.TargetLineSetting = 0;
            }
        },
        showHideTargetLineColor: function(){
            var reportObj = this.getController().getReport();
            if(reportObj && reportObj.config && reportObj.config.config.chartType === "table"){
                this.formContainer.find(".reportTargetLineColor").hide();
            }else{
                this.formContainer.find(".reportTargetLineColor").show();
            }
        },
        setTargetLineSetting: function ( value ) {
            if(value == 1) {
                this.formContainer.find('input[name="reportDisplayShowTargetLineOption"]').prop('checked',true);
            }
        },
        
        saveOptions: function () {
            var reportObj = this.getController().getReport();
            reportObj.setVisualizationOption('targetLine', this.getTargetLineSetting());
            if(this.getTargetLineSetting() == true) {
                reportObj.setVisualizationOption('targetLineValue', this.getTargetLineValue());
                reportObj.setVisualizationOption('targetLineColor', this.getTargetLineColor());
            } else {
                reportObj.setVisualizationOption('targetLineValue', null);
                reportObj.setVisualizationOption('targetLineColor', null);
            }
        },
        
        showNumericSpaninput: function() {
            var _this = this,
                container = this.formContainer;
            if(this.getController().getReport().getVisual().targetLine) {
                container.find(".reportTargetLineValue").show();
                this.showHideTargetLineColor();
            } else {
                container.find(".reportTargetLineValue").hide();
                container.find(".reportTargetLineColor").hide();
            }
            var _this = this;
            container.find('input[name="reportDisplayShowTargetLineOption"]').on('click',function(){
                if(container.find('input[name="reportDisplayShowTargetLineOption"]').is(':checked') == true) {
                    container.find(".reportTargetLineValue").show();
                    _this.showHideTargetLineColor();
                }  else {
                    container.find(".reportTargetLineValue").hide();
                    container.find(".reportTargetLineColor").hide();
                }
            });
        },

        render: function() {
            if ( this.container ) {
                var reportObj = this.getController().getReport();
                this.container.append(this.getFormContainer());
                this.showNumericSpaninput();
                if(reportObj.getVisual().targetLine) {
                    this.setTargetLineSetting(reportObj.getVisual().targetLine);
                }
                
                if(reportObj.getVisual().targetLineValue) {
                    this.setTargetLineValue(reportObj.getVisual().targetLineValue);
                }
                
                if(reportObj.getVisual().targetLineColor) {
                    this.setTargetLineColor(reportObj.getVisual().targetLineColor);
                }
            }

            return this.getFormContainer();
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);