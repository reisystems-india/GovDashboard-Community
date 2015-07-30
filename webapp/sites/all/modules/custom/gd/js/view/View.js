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
        throw new Error('View requires GD');
    }

    var GD = global.GD;

    (function($,Highcharts) {

        if ( typeof $ === 'undefined' ) {
            throw new Error('View Requires jQuery');
        }

        var View = GD.Class.extend({

            object: null,
            container: null,
            options: [],
            output: '',

            init: function ( object, container, options ) {
                this.object = object;
                this.container = container;
                this.options = options;
                return this;
            },

            getViewCss: function () {
                return "gd-view";
            },

            getContainerCss: function () {
                return 'gd-container';
            },

            getLabelCss: function () {
                return 'gd-label';
            },

            getSpanCss: function () {
                return 'gd-spn';
            },

            render: function () {
                $(this.container).append(this.output);
            }

        });

        global.GD.View = View;
    })(typeof global.GD_jQuery != 'undefined' ? global.GD_jQuery : jQuery, typeof global.GD_Highcharts != 'undefined' ? global.GD_Highcharts : (typeof Highcharts != 'undefined' ? Highcharts : undefined));
})(typeof window === 'undefined' ? this : window);