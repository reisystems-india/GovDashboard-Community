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
        throw new Error('ReportColumnFormatNumericForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportColumnFormatNumericForm requires GD');
    }

    var GD = global.GD;

    var ReportColumnFormatNumericForm = GD.ReportColumnFormatForm.extend({

        init: function(object, container, options) {
            this._super(object, container, options);

            this.getForm().append($([
                '<div class="form-group">',
                    '<label>Display As</label>',
                    '<div>',
                        '<label class="radio-inline">',
                            '<input type="radio" name="reportColumnFormatFormat" value="number" checked> Number',
                        '</label>',
                        '<label class="radio-inline">',
                            '<input type="radio" name="reportColumnFormatFormat" value="percent"> Percent',
                        '</label>',
                        '<label class="radio-inline">',
                            '<input type="radio" name="reportColumnFormatFormat" value="currency"> Currency',
                        '</label>',
                    '</div>',
                '</div>',
                '<div class="form-group">',
                    '<label for="reportColumnFormatScale">Decimal Places</label>',
                    '<input type="text" class="form-control input-sm" id="reportColumnFormatScale" value="'+this.object.formatter.scale+'">',
                '</div>',
                '<div id="reportColumnFormatSeriesForm" class="form-group">',
                    '<label for="reportColumnFormatSeries">Series Type</label>',
                    '<select class="form-control input-sm" id="reportColumnFormatSeries">',
                        '<option value="">Select Chart Type</option>',
                        '<option value="line">Line</option>',
                        '<option value="area">Area</option>',
                        '<option value="column">Column</option>',
                        '<option value="spline">Spline</option>',
                    '</select>',
                '</div>'
            ].join("\n")));

            // apply format values
            this.setFormat(this.object.formatter.format || this.object.columnType);
            this.setScale(this.object.formatter.scale);
            this.setSeries(this.object.formatter.chartType);

            var _this = this;
            this.getForm().find('input:radio[name=reportColumnFormatFormat]').on('change',function(){
                _this.setScale(null);
            });

            if ($.inArray(this.getController().getReport().getChartType(), ['line', 'area', 'bar', 'column']) === -1) {
                this.getForm().find('#reportColumnFormatSeriesForm').hide();
            }
        },

        getScale: function() {
            var scale = this.getForm().find('#reportColumnFormatScale').val();
            if ( !scale ) {
                return null;
            } else {
                return scale;
            }
        },

        setScale: function ( scale ) {
            if (scale === 'null') {scale = null;} // backwards compatibility check
            if ( scale === null ) {
                if ( this.getFormat() == 'currency' ) {
                    scale = 2;
                } else if ( this.getFormat() == 'percent' || this.getFormat() == 'percentage' ) {
                    scale = 0;
                }
            }
            this.getForm().find('#reportColumnFormatScale').val(scale);
        },

        getFormat: function() {
            var format = this.getForm().find('input:radio[name=reportColumnFormatFormat]:checked').val();
            if ( !format ) {
                return null;
            } else {
                return format;
            }
        },

        setFormat: function ( format ) {
            if(format === "integer"){
                format = "number";
            }
            this.getForm().find('input:radio[name=reportColumnFormatFormat]').filter('[value='+format+']').prop('checked', true);
        },

        getSeries: function() {
            var series = this.getForm().find('#reportColumnFormatSeries').val();
            if ( !series ) {
                return null;
            } else {
                return series;
            }
        },

        setSeries: function ( series ) {
            this.getForm().find('#reportColumnFormatSeries').val(series);
        },

        applyButtonClicked: function ( callback ) {
            if ( this.validate() ) {
                var formatterObj = this.object.formatter,
                    controllerObj = this.getController();
                // set format properties
                this.object.displayName = this.getDisplayName();
                
                    formatterObj.format = this.getFormat();
                    formatterObj.scale = this.getScale();
                    formatterObj.chartType = this.getSeries();

                // save to report
                controllerObj.getReport().setColumnConfig(this.object.columnId,this.object);

                // reload report preview
                controllerObj.getCanvas().loadPreview();

                callback();
            }
        }

    });

    GD.ReportColumnFormatNumericForm = ReportColumnFormatNumericForm;

})(typeof window === 'undefined' ? this : window, jQuery);