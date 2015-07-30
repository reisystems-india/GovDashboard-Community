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
        throw new Error('ReportTickIntervalForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportTickIntervalForm requires GD');
    }

    var GD = global.GD;

    global.GD.ReportTickIntervalForm = GD.View.extend({

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
                                '<div class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#visualizePanelGroup" href="#reportTickIntervalPanel">',
								'Tick Interval in Numeric Dimension',
								'</div>',
                            '</div>',
                        '</div>',
                        '<div id="reportTickIntervalPanel" class="panel-collapse collapse">',
                            '<div class="panel-body">',
                                '<form class="form-horizontal">',
                                    '<div class="form-group">',
                                        '<div class="col-sm-6">',
                                            '<label class="radio-inline">',
                                                '<input type="radio" name="tickIntervalOption" value="auto" checked> Auto',
                                            '</label>',
                                        '</div>',
                                        '<div class="col-sm-6">',
                                            '<label class="radio-inline">',
                                                '<input type="radio" name="tickIntervalOption" value="Custom"> Custom',
                                            '</label>',  
                                        '</div>', 
                                    '</div>', 
                                                                      
                                    '<div class="has-feedback"">',                                                                      
                                        '<div class="vsl-trg-frm-cntnr">',
                                            '<input type="text" class="form-control tickIntervalCustomValue" id="tickIntervalCustomValue">',
                                            '<span class="glyphicon  form-control-feedback"></span>',
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

        getTickIntervalSetting: function () {
            return this.formContainer.find('input[name="tickIntervalOption"]:checked').val();
        },

        setTickIntervalSetting: function ( value ) {
            this.formContainer.find('input[name="tickIntervalOption"][value="'+value+'"]').prop('checked',true);
        },
        getTickIntervalCustomValue: function () {
            return this.formContainer.find(".tickIntervalCustomValue").val();            
        }, 
        setTickIntervalCustomValue: function ( value ) {
            this.formContainer.find(".tickIntervalCustomValue").val( value );
        }, 
        
        saveOptions: function () {
            var _this = this,
                reportObj = _this.getController().getReport();
            reportObj.setVisualizationOption('tickInterval', _this.getTickIntervalSetting());
            reportObj.setVisualizationOption('tickIntervalValue', _this.getTickIntervalCustomValue());
            
        },

        showTickIntervalCustomValueinput: function() {
            var _this = this;     
            var container = _this.formContainer;
            
            if(this.getController().getReport().getVisual().tickInterval == "Custom") {
                container.find(".tickIntervalCustomValue").show();
            } else {
                container.find(".tickIntervalCustomValue").hide();
            }            
          
            
            container.find('input[name="tickIntervalOption"]').on('click',function(){
                if(container.find('input[name="tickIntervalOption"]:checked').val() == "Custom") {
                    container.find(".tickIntervalCustomValue").show();
                } else {
                    container.find(".tickIntervalCustomValue").hide();
                }
            });
          
        },
        

        render: function() {
            
            if ( this.container ) {
                this.container.append(this.getFormContainer());
                var reportObj = this.getController().getReport();
                this.showTickIntervalCustomValueinput();

                if(reportObj.getVisual().tickInterval) {
                    this.setTickIntervalSetting(reportObj.getVisual().tickInterval);
                }
               
                if(reportObj.getVisual().tickIntervalValue) {
                    this.setTickIntervalCustomValue(reportObj.getVisual().tickIntervalValue);
                }
            }

            return this.getFormContainer();
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);