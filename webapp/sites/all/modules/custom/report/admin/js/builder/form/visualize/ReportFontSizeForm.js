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
        throw new Error('ReportFontSizeForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportFontSizeForm requires GD');
    }

    var GD = global.GD;

    global.GD.ReportFontSizeForm = GD.View.extend({

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
                                '<div class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#visualizePanelGroup" href="#reportFontSizePanel">',
								'Font size',
								'</div>',
                            '</div>',
                        '</div>',
                        '<div id="reportFontSizePanel" class="panel-collapse collapse">',
                            '<div class="panel-body">',
                                '<form>',
                                    '<div class="form-group">',
                                            '<div>',
                                            '<label class="radio-inline">',
                                                '<input type="radio" name="reportFontSizeFormOption" value="Small" > Small',
                                            '</label>',
                                            '<label class="radio-inline">',
                                                '<input type="radio" name="reportFontSizeFormOption" value="Medium"  checked> Medium',
                                            '</label>',
                                            '<label class="radio-inline">',
                                                '<input type="radio" name="reportFontSizeFormOption" value="Large"> Large',
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
        
        saveOptions: function () {
            var _this = this;
            _this.getController().getReport().setVisualizationOption('fontSize', _this.getFontSizeSetting());
            
        },
        
        getFontSizeSetting: function () {
            return this.formContainer.find('input[name="reportFontSizeFormOption"]:checked').val();
	    },

        setFontSizeSetting: function ( value ) {
            this.formContainer.find('input[name="reportFontSizeFormOption"][value="'+value+'"]').prop('checked',true);
        },

        render: function() {
            if ( this.container ) {
                this.getFormContainer();
                var reportObj = this.getController().getReport();
                if(reportObj.getVisual().fontSize) {
                    this.setFontSizeSetting(reportObj.getVisual().fontSize);
                }
                this.container.append(this.formContainer);
            }

            return this;
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);