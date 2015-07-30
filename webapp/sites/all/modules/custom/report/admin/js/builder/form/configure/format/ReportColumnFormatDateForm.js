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
        throw new Error('ReportColumnFormatDateForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportColumnFormatDateForm requires GD');
    }

    var GD = global.GD;

    var ReportColumnFormatDateForm = GD.ReportColumnFormatForm.extend({

        init: function(object, container, options) {
            this._super(object, container, options);

            this.getForm().append($([
                        '<div class="form-group">',
                            '<label for="reportColumnFormatDateType">Format</label>',
                            '<select class="form-control input-sm" id="reportColumnFormatDateType">',
                                '<option value="mdy">MM/DD/YYYY</option>',
                                '<option value="ymd">YYYY-MM-DD</option>',
                            '</select>',
                        '</div>'
            ].join("\n")));

            this.setFormat(this.object.formatter.format);
        },

        getFormat: function() {
            var getForm = this.getForm();
            var dateFormat = getForm.find('#reportColumnFormatDateType').val();
            if ( !dateFormat ) {
                return null;
            } else {
                return dateFormat;
            }
        },

        setFormat: function ( dateFormat ) {
            var getForm = this.getForm();
            getForm.find('#reportColumnFormatDateType').val(dateFormat);
        },

        applyButtonClicked: function ( callback ) {
            if ( this.validate() ) {

                // set format properties
                this.object.displayName = this.getDisplayName();
                this.object.formatter.format = this.getFormat();
                this.object.formatter.scale = null;
                this.object.formatter.chartType = null;
                var controllerObj = this.getController();
                // save to report
                controllerObj.getReport().setColumnConfig(this.object.columnId,this.object);

                // reload report preview
                controllerObj.getCanvas().loadPreview();

                callback();
            }
        }

    });

    GD.ReportColumnFormatDateForm = ReportColumnFormatDateForm;

})(typeof window === 'undefined' ? this : window, jQuery);