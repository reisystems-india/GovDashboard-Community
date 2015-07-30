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
        throw new Error('ReportNumericSpanForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportNumericSpanForm requires GD');
    }

    var GD = global.GD;

    global.GD.ReportNumericSpanForm = GD.View.extend({

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
                                '<div class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#visualizePanelGroup" href="#reportNumericSpanPanel">',
								'Numeric Span',
								'</div>',
                            '</div>',
                        '</div>',
                        '<div id="reportNumericSpanPanel" class="panel-collapse collapse">',
                            '<div class="panel-body">',
                                '<form class="form-horizontal">',
                                    '<div class="form-group">',
                                        '<label class="col-sm-12">Minimum</label>',
                                        '<div class="col-sm-6">',
                                            '<label class="radio-inline">',
                                                '<input type="radio" name="reportDisplayNumericSpanOption" value="Auto" checked> Auto',
                                            '</label>',
                                        '</div>',
                                        '<div class="col-sm-6">',
                                            '<label class="radio-inline">',
                                                '<input type="radio" name="reportDisplayNumericSpanOption" value="Fixed"> Fixed',
                                            '</label>',
                                        '</div>',
                                    '</div>',
                                        
                                        '<div class="reportNumericSpan">',
                                            '<input type="text" class="form-control reportDisplayNumericSpanFixedVal">',
                                            '<div class="help-block"></div>',
                                        '</div>',
                                        
                                    
                                '</form>',
                            '</div>',
                        '</div>',
                    '</div>'
                ].join("\n"));
            }
            return this.formContainer;
        },
        
        getNumericSpanFixedValue: function () {
            return this.formContainer.find(".reportDisplayNumericSpanFixedVal").val();
        },

        setNumericSpanFixedValue: function ( value ) {
            this.formContainer.find(".reportDisplayNumericSpanFixedVal").val( value );
        },
        
        getNumericSpanSetting: function () {
            return this.formContainer.find('input[name="reportDisplayNumericSpanOption"]:checked').val();
        },

        setNumericSpanSetting: function ( value ) {
            this.formContainer.find('input[name="reportDisplayNumericSpanOption"][value="'+value+'"]').prop('checked',true);
        },
        
        saveOptions: function () {
            var _this = this,
                reportObj = _this.getController().getReport();
            reportObj.setVisualizationOption('minNumericSpan', _this.getNumericSpanSetting());
            if(_this.getNumericSpanSetting() == "Fixed") {
                reportObj.setVisualizationOption('minNumericSpanValue', _this.getNumericSpanFixedValue());
            }
        },
        
        showNumericSpaninput: function() {
            var _this = this,
                container = _this.formContainer;
            container.find(".reportDisplayNumericSpanFixedVal").hide();
            
            if(this.getController().getReport().getVisual().minNumericSpan == "Fixed") {
                container.find(".reportDisplayNumericSpanFixedVal").show();
            } else {
                container.find(".reportDisplayNumericSpanFixedVal").hide();
            }
                        
            container.find('input[name="reportDisplayNumericSpanOption"]').on('click',function(){
                if(container.find('input[name="reportDisplayNumericSpanOption"]:checked').val() == "Fixed") {
                    container.find(".reportDisplayNumericSpanFixedVal").show();
                } else {
                    container.find(".reportDisplayNumericSpanFixedVal").hide();
                }
            });
        },

        render: function() {
            if ( this.container ) {
                var reportObj = this.getController().getReport();
                this.container.append(this.getFormContainer());
                this.showNumericSpaninput();
                if(reportObj.getVisual().minNumericSpan) {
                    this.setNumericSpanSetting(reportObj.getVisual().minNumericSpan);
                }
                if(reportObj.getVisual().minNumericSpanValue) {
                    this.setNumericSpanFixedValue(reportObj.getVisual().minNumericSpanValue);
                }
            }

            return this.getFormContainer();
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);