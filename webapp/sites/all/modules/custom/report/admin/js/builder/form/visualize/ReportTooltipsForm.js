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
        throw new Error('ReportTooltipsForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportTooltipsForm requires GD');
    }

    var GD = global.GD;

    global.GD.ReportTooltipsForm = GD.View.extend({

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
                                '<div class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#visualizePanelGroup" href="#reportTooltipsPanel">',
                                'Tooltips',
                                '</div>',
                            '</div>',
                        '</div>',
                        '<div id="reportTooltipsPanel" class="panel-collapse collapse">',
                            '<div class="panel-body">',
                                '<form>',
                                    '<div class="form-group">',
                                        '<label>Show</label>',
                                        '<div>',
                                            '<label class="radio-inline">',
                                                '<input type="radio" name="reportDisplayTooltipOption" value="none"> None',
                                            '</label>',
                                            '<label class="radio-inline">',
                                                '<input type="radio" name="reportDisplayTooltipOption" value="value" checked> Value',
                                            '</label>',
                                            '<label class="radio-inline">',
                                                '<input type="radio" name="reportDisplayTooltipOption" value="details"> Details',
                                            '</label>',
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
        
        getTooltipSetting: function () {
            return this.formContainer.find('input[name="reportDisplayTooltipOption"]:checked').val();
        },

        setTooltipSetting: function ( value ) {
            this.formContainer.find('input[name="reportDisplayTooltipOption"][value="'+value+'"]').prop('checked',true);
        },
        
        saveOptions: function () {
            var _this = this;
            _this.getController().getReport().setVisualizationOption('displayTooltip', _this.getTooltipSetting());
            
        },

        render: function() {
            if ( this.container ) {
                this.getFormContainer();
                var reportObj = this.getController().getReport();
                if(reportObj.getVisual().displayTooltip) {
                    this.setTooltipSetting(reportObj.getVisual().displayTooltip);
                }
                this.container.append(this.formContainer);
            }

            return this;
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);