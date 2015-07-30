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
        throw new Error('ViewPrimitiveFactory requires GD');
    }

    var GD = global.GD;

    (function($,Highcharts) {

        if ( typeof $ === 'undefined' ) {
            throw new Error('ViewPrimitiveFactory requires jQuery');
        }

        var ViewFactory = {
            getRangedView: function ( object, container, options ) {
                return new GD.ViewCustomRanged(object, container, options);
            },

            getView: function ( object, container, options ) {
                if ( typeof object.type == "undefined" ){
                    return null;
                }

                switch (object.type) {
                    case "select":
                        return new GD.ViewSelect(object.options, container, options);
                        break;
                    case "radio":
                        return new GD.ViewRadioGroup(object.options, container, options);
                        break;
                    case "slider":
                        return new GD.ViewSlider(object.options, container, options);
                        break;
                    case "slider:date":
                        return new GD.ViewDateSlider(object.options, container, options);
                        break;
                    case "slider:month":
                        return new GD.ViewMonthSlider(object.options, container, options);
                        break;
                    case "slider:quarter":
                        return new GD.ViewQuarterSlider(object.options, container, options);
                        break;
                    case "multiselect":
                    case "checkbox":
                        return new GD.ViewCheckboxGroup(object.options, container, options);
                        break;
                    case "text":
                        return new GD.ViewText(object.options, container, options);
                        break;
                    default:
                        return null;
                        break;
                }
            }
        };

        global.GD.ViewFactory = ViewFactory;
    })(typeof global.GD_jQuery != 'undefined' ? global.GD_jQuery : jQuery, typeof global.GD_Highcharts != 'undefined' ? global.GD_Highcharts : (typeof Highcharts != 'undefined' ? Highcharts : undefined));
})(typeof window === 'undefined' ? this : window);
