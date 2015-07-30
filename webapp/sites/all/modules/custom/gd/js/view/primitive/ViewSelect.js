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
        throw new Error('ViewSelect requires GD');
    }

    var GD = global.GD;

    (function($,Highcharts) {

        if ( typeof $ === 'undefined' ) {
            throw new Error('ViewSelect requires jQuery');
        }

        var ViewSelect = GD.ViewPrimitive.extend({

            init: function ( object, container, options ) {
                this._super(object,container,options);

                this.formitem = $('<select class="'+this.getViewCss()+'" tabindex="3000"></select>');
                this.formitem.uniqueId();

                if (typeof this.object['values'] != "undefined") {
                    this.setOptions(this.object.values);
                }
                return this;
            },

            getId: function () {
                return GD.Util.IdGenerator.getId({name: typeof this.options['FilterName'] != 'undefined' ? this.options['FilterName'] : 'gd-view-select'});
            },

            getViewCss: function () {
                return this._super() + ' gd-view-select form-control';
            },

            getOptionCss: function () {
                return 'gd-option-select';
            },

            getContainerCss: function () {
                return this._super() + ' gd-container-select';
            },

            setOptions: function ( options ) {
                var _this = this;
                $.each(options, function (index, option) {
                    var mask = option;
                    if ( typeof _this.options['ValueMask'] != "undefined" )
                        mask = _this.options.ValueMask(option);

                    _this.formitem.append('<option class="'+_this.getOptionCss()+'" value="'+option+'">'+mask+'</option>');
                });
            },

            setChangedEvent: function ( func ) {
                this.formitem.on("change", func);
            },

            render: function () {
                var output = $('<div class="'+this.getContainerCss()+'"></div>').append(this.formitem);

                if ( this.container == null ) {
                    return output;
                } else {
                    $(this.container).append(output);
                    return output;
                }
            }
        });

        global.GD.ViewSelect = ViewSelect;
    })(typeof global.GD_jQuery != 'undefined' ? global.GD_jQuery : jQuery, typeof global.GD_Highcharts != 'undefined' ? global.GD_Highcharts : (typeof Highcharts != 'undefined' ? Highcharts : undefined));
})(typeof window === 'undefined' ? this : window);
