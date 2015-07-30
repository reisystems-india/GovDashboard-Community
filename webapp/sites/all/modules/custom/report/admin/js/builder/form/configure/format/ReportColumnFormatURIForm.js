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
        throw new Error('ReportColumnFormatURIForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportColumnFormatURIForm requires GD');
    }

    var GD = global.GD;

    var ReportColumnFormatURIForm = GD.ReportColumnFormatForm.extend({

        init: function(object, container, options) {
            this._super(object, container, options);

            this.getForm().append($([
                '<div class="form-group">',
                    '<label>Display As</label>',
                    '<div>',
                        '<label class="">',
                            '<input type="radio" name="reportColumnURIFormat" value="link"> This is an external link',
                        '</label>',
                        '<label class="">',
                            '<input type="radio" name="reportColumnURIFormat" value="image"> This is an image',
                        '</label>',
                     '</div>',
                '</div>'
                
            ].join("\n")));

            // apply object values
            this.setFormat(this.object.formatter.format);
           
        },

        
        getFormat: function() {
            var format = this.getForm().find('input:radio[name=reportColumnURIFormat]:checked').val();
            if ( !format ) {
                return null;
            } else {
                return format;
            }
        },

        setFormat: function ( format ) {
            this.getForm().find('input:radio[name=reportColumnURIFormat]').filter('[value='+format+']').prop('checked', true);
        },

        applyButtonClicked: function ( callback ) {
            if ( this.validate() ) {
                var controllerObj = this.getController();
                // set format properties
                this.object.displayName = this.getDisplayName();
                this.object.formatter.format = this.getFormat();
                this.object.formatter.scale = null;
                this.object.formatter.chartType = null;

                // save to report
                controllerObj.getReport().setColumnConfig(this.object.columnId,this.object);

                // reload report preview
                controllerObj.getCanvas().loadPreview();

                callback();
            }
        }

    });

    GD.ReportColumnFormatURIForm = ReportColumnFormatURIForm;

})(typeof window === 'undefined' ? this : window, jQuery);