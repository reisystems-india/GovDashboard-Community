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


(function(global,undefined){

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ViewQuarterSlider requires GD');
    }

    var GD = global.GD;

    (function($,Highcharts) {

        if ( typeof $ === 'undefined' ) {
            throw new Error('ViewQuarterSlider requires jQuery');
        }

        var ViewQuarterSlider = GD.ViewSlider.extend({

            minDate: null,
            maxDate: null,
            dateValue: null,

            setOptions: function ( options ) {
                if ( typeof options['min'] != "undefined" )
                    this.minDate = new Date(options['min']);

                if ( typeof options['max'] != "undefined" )
                    this.maxDate = new Date(options['max']);

                this.min = 0;
                this.max = GD.Util.DateFormat.numberOfQuarters(this.minDate, this.maxDate);
                this.step = 1;
                this.dateValue = this.minDate;
            },

            setValue: function ( value ) {
                if (value != null ) {
                    this.dateValue = new Date(value);
                    this.formitem.slider("value", GD.Util.DateFormat.numberOfQuarters(this.minDate, this.dateValue));
                }

                this.displayText();
            },

            displayText: function ( ) {
                var text = GD.Util.DateFormat.getQuarterCode(this.dateValue) + ' - ' + this.dateValue.getFullYear();
                this.valueLabel.text( text );
            },

            sliderEvent: function ( event, ui ) {
                var year = this.minDate.getFullYear() + Math.floor( ui.value / 4 );
                var months = this.minDate.getMonth() + ( Math.floor( ui.value % 4 ) * 3 );

                if (months > 11) {
                    year++;
                    months = months - 12;
                }

                this.dateValue = new Date(year, months, this.minDate.getDate());
                this.displayText();
            },

            getValue: function () {
                return GD.Util.DateFormat.getUSFormat(this.dateValue);
            },

            getViewCss: function () {
                return this._super() + ' gd-view-quarter-slider';
            },

            getLabelCss: function () {
                return this._super() + ' gd-label-quarter-slider';
            },

            getContainerCss: function () {
                return this._super() + ' gd-container-quarter-slider';
            }
        });

        global.GD.ViewQuarterSlider = ViewQuarterSlider;
    })(typeof global.GD_jQuery != 'undefined' ? global.GD_jQuery : jQuery, typeof global.GD_Highcharts != 'undefined' ? global.GD_Highcharts : (typeof Highcharts != 'undefined' ? Highcharts : undefined));
})(typeof window === 'undefined' ? this : window);
