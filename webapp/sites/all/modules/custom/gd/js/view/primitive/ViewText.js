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
        throw new Error('ViewText requires GD');
    }

    var GD = global.GD;

    (function($,Highcharts) {

        if ( typeof $ === 'undefined' ) {
            throw new Error('Viewtext requires jQuery');
        }

        var ViewText = GD.ViewPrimitive.extend({
            id: null,

            init: function ( object, container, options ) {
                this._super(object,container,options);

                this.formitem = $('<input tabindex="3000" class="'+this.getViewCss()+'" type="text" style="width:150px;"/>');
                this.formitem.uniqueId();
                this.formitem.val(object);

                return this;
            },

            getId: function () {
                return this.formitem.attr('id');
            },

            getViewCss: function () {
                return this._super() + ' gd-view-text';
            }
        });

        global.GD.ViewText = ViewText;
    })(typeof global.GD_jQuery != 'undefined' ? global.GD_jQuery : jQuery, typeof global.GD_Highcharts != 'undefined' ? global.GD_Highcharts : (typeof Highcharts != 'undefined' ? Highcharts : undefined));
})(typeof window === 'undefined' ? this : window);
