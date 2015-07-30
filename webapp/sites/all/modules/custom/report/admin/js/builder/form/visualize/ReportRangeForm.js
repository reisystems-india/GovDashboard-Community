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
        throw new Error('ReportRangeForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportRangeForm requires GD');
    }

    var GD = global.GD;

    global.GD.ReportRangeForm = GD.View.extend({

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
                                '<div class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#visualizePanelGroup" href="#reportRangePanel">',
								'Range',
								'</div>',
                            '</div>',
                        '</div>',
                        '<div id="reportRangePanel" class="panel-collapse collapse">',
                            '<div class="panel-body">',
                                '<form class="form-horizontal">',
                                    
                                    '<div id="reportMinRangePanel">',
                                        '<div class="form-group">',
                                           '<label class="col-sm-12">Minimum</label>', 
                                           '<div class="col-sm-6">',
                                                '<label class="radio-inline">',
                                                    '<input type="radio" name="reportDisplayMinRangeOption" value="Auto" checked> Auto',
                                                '</label>',
                                            '</div>',
                                            '<div class="col-sm-6">',
                                                '<label class="radio-inline">',
                                                    '<input type="radio" name="reportDisplayMinRangeOption" value="Fixed"> Fixed',
                                                '</label>',
                                            '</div>', 
                                        '</div>', 

                                        '<div class="reportMinRange">',
                                            '<input type="text" class="form-control input-sm reportDisplayMinRangeFixedVal">',
                                            '<div class="help-block"></div>',
                                        '</div>',
                                        
                                    '</div>',
                                    
                                    
                                    '<div id="reportMaxRangePanel">',
                                        '<div class="form-group">',
                                           '<label class="col-sm-12">Maximum</label>',
                                           '<div class="col-sm-6">',
                                                '<label class="radio-inline">',
                                                    '<input type="radio" name="reportDisplayMaxRangeOption" value="Auto" checked> Auto',
                                                '</label>',
                                            '</div>',
                                            '<div class="col-sm-6">',
                                                '<label class="radio-inline">',
                                                    '<input type="radio" name="reportDisplayMaxRangeOption" value="Fixed"> Fixed',
                                                '</label>',
                                            '</div>',
                                        '</div>',
                                        '<div class="reportMaxRange">',
                                            '<input type="text" class="form-control input-sm reportDisplayMaxRangeFixedVal">',
                                            '<div class="help-block"></div>',
                                        '</div>',                                        
                                    '</div>',

                                '</form>',

                            '</div>',
                        '</div>',
                    '</div>'
                ].join("\n"));
            }
            return this.formContainer;
        },
        
        getMaxRangeFixedValue: function () {
            return this.formContainer.find(".reportDisplayMaxRangeFixedVal").val();
        },

        setMaxRangeFixedValue: function ( value ) {
            this.formContainer.find(".reportDisplayMaxRangeFixedVal").val( value );
        },
        
        getMinRangeFixedValue: function () {
            return this.formContainer.find(".reportDisplayMinRangeFixedVal").val();
        },

        setMinRangeFixedValue: function ( value ) {
            this.formContainer.find(".reportDisplayMinRangeFixedVal").val( value );
        }, 
        
        getMaxRangeSetting: function () {
            return this.formContainer.find('input[name="reportDisplayMaxRangeOption"]:checked').val();
        },

        setMaxRangeSetting: function ( value ) {
            this.formContainer.find('input[name="reportDisplayMaxRangeOption"][value="'+value+'"]').prop('checked',true);
        },
        
        getMinRangeSetting: function () {
            return this.formContainer.find('input[name="reportDisplayMinRangeOption"]:checked').val();
        },

        setMinRangeSetting: function ( value ) {
            this.formContainer.find('input[name="reportDisplayMinRangeOption"][value="'+value+'"]').prop('checked',true);
        },
        
        saveOptions: function () {
            var reportObj = this.getController().getReport();
            reportObj.setVisualizationOption('rangeAutoMinimum', this.getMinRangeSetting());
            reportObj.setVisualizationOption('rangeAutoMaximum', this.getMaxRangeSetting());
            if(this.getMinRangeSetting() == "Fixed") {
                reportObj.setVisualizationOption('rangeMinimum', this.getMinRangeFixedValue());
            }else{
                this.setMinRangeFixedValue("");
                reportObj.removeVisualizationOption('rangeMinimum');
            }
            
            if(this.getMaxRangeSetting() == "Fixed") {
                reportObj.setVisualizationOption('rangeMaximum', this.getMaxRangeFixedValue());
            }else{
                this.setMaxRangeFixedValue("");
                reportObj.removeVisualizationOption('rangeMaximum');
            }
        },
        
        showMaxRangeFixedValueinput: function() {
            var reportObj = this.getController().getReport(),
                _this = this,
                container = _this.formContainer;
                
            container.find(".reportDisplayMaxRangeFixedVal").hide();
            container.find(".reportDisplayMinRangeFixedVal").hide();
            
            if(reportObj.getVisual().rangeAutoMinimum == "Fixed") {
                container.find(".reportDisplayMinRangeFixedVal").show();
            } else {
                container.find(".reportDisplayMinRangeFixedVal").hide();
            }
            
            if(reportObj.getVisual().rangeAutoMaximum == "Fixed") {
                container.find(".reportDisplayMaxRangeFixedVal").show();
            } else {
                container.find(".reportDisplayMaxRangeFixedVal").hide();
            }
            
            container.find('input[name="reportDisplayMinRangeOption"]').on('click',function(){
                if(container.find('input[name="reportDisplayMinRangeOption"]:checked').val() == "Fixed") {
                    container.find(".reportDisplayMinRangeFixedVal").show();
                } else {
                    container.find(".reportDisplayMinRangeFixedVal").hide();
                }
            });
            
            container.find('input[name="reportDisplayMaxRangeOption"]').on('click',function(){
                if(container.find('input[name="reportDisplayMaxRangeOption"]:checked').val() == "Fixed") {
                    container.find(".reportDisplayMaxRangeFixedVal").show();
                } else {
                    container.find(".reportDisplayMaxRangeFixedVal").hide();
                }
            });
        },
        
        render: function() {
            if ( this.container ) {
                var reportObj = this.getController().getReport();
                this.container.append(this.getFormContainer());
                
                this.showMaxRangeFixedValueinput();
                if(reportObj.getVisual().rangeAutoMinimum) {
                    this.setMinRangeSetting(reportObj.getVisual().rangeAutoMinimum);
                }
                if(reportObj.getVisual().rangeAutoMaximum) {
                    this.setMaxRangeSetting(reportObj.getVisual().rangeAutoMaximum);
                }
                if(reportObj.getVisual().rangeMinimum) {
                    this.setMinRangeFixedValue(reportObj.getVisual().rangeMinimum);
                }
                if(this.getController().getReport().getVisual().rangeMaximum) {
                    this.setMaxRangeFixedValue(reportObj.getVisual().rangeMaximum);
                }
            }

            return this.getFormContainer();
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);