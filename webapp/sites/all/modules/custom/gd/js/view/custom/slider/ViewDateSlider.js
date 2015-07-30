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
        throw new Error('ViewDateSlider requires GD');
    }

    var GD = global.GD;

    (function($,Highcharts) {

        if ( typeof $ === 'undefined' ) {
            throw new Error('ViewDateSlider requires jQuery');
        }

        var ViewDateSlider = GD.ViewSlider.extend({

            setOptions: function ( options ) {
                if ( typeof options['min'] != "undefined" )
                    this.min = new Date(options['min']).getTime();

                if ( typeof options['max'] != "undefined" )
                    this.max = new Date(options['max']).getTime();

                this.step = 86400000;
            },

            setValue: function ( value ) {
                if (value != null ) {
                    this.formitem.slider("value", new Date(value).getTime());
                }

                this.valueLabel.text( GD.Util.DateFormat.getUSFormat(new Date(this.formitem.slider("value"))) );
            },

            sliderEvent: function ( event, ui ) {
                this.valueLabel.text( GD.Util.DateFormat.getUSFormat(new Date(ui.value)) );
            },

            getValue: function () {
                return GD.Util.DateFormat.getUSFormat(new Date(this.formitem.slider("value")));
            },

            getViewCss: function () {
                return this._super() + ' gd-view-date-slider';
            },

            getLabelCss: function () {
                return this._super() + ' gd-label-date-slider';
            },

            getContainerCss: function () {
                return this._super() + ' gd-container-date-slider';
            }
        });

        global.GD.ViewDateSlider = ViewDateSlider;
    })(typeof global.GD_jQuery != 'undefined' ? global.GD_jQuery : jQuery, typeof global.GD_Highcharts != 'undefined' ? global.GD_Highcharts : (typeof Highcharts != 'undefined' ? Highcharts : undefined));
})(typeof window === 'undefined' ? this : window);
