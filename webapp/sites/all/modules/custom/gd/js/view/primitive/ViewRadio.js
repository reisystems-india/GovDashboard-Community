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
        throw new Error('ViewCheckbox requires GD');
    }

    var GD = global.GD;

    (function($,Highcharts) {

        if ( typeof $ === 'undefined' ) {
            throw new Error('ViewCheckbox requires jQuery');
        }

        var ViewRadio = GD.ViewPrimitive.extend({
            text: null,

            init: function ( object, container, options ) {
                this._super(object,container,options);

                this.formitem = $('<input tabindex="3000" class="'+this.getViewCss()+'" type="radio"/>');
                this.formitem.uniqueId();

                if ( typeof object != "undefined" )
                    this.setValue(object);

                return this;
            },

            getViewCss: function () {
                return this._super() + ' gd-view-radio';
            },

            getContainerCss: function () {
                return this._super() + ' gd-container-radio';
            },

            getLabelCss: function () {
                return this._super() + ' gd-label-radio';
            },

            isChecked: function () {
                return this.formitem.is(':checked');
            },

            setGroupName: function ( name ) {
                this.formitem.prop('name', name);
            },

            getId: function () {
                return GD.Util.IdGenerator.getId({name: typeof this.options['FilterName'] != 'undefined' ? this.options['FilterName'] : 'gd-view-radio'});
            },

            //  TODO Sanitize value
            setValue: function ( value ) {
                this.formitem.prop('value', value);

                if ( typeof this.options['ValueMask'] != "undefined" )
                    value = this.options.ValueMask(value);

                this.text = $('<label class="'+this.getLabelCss()+'" for="'+this.getId()+'">'+value+'</label>');
            },

            setChecked: function ( bool ) {
                this.formitem.prop('checked', bool);
            },

            render: function () {
                var output = $('<div class="'+this.getContainerCss()+'"></div>').append(this.formitem, this.text);

                if ( this.container == null ) {
                    return output;
                } else {
                    $(this.container).append(output);
                }

            }
        });

        global.GD.ViewRadio = ViewRadio;
    })(typeof global.GD_jQuery != 'undefined' ? global.GD_jQuery : jQuery, typeof global.GD_Highcharts != 'undefined' ? global.GD_Highcharts : (typeof Highcharts != 'undefined' ? Highcharts : undefined));
})(typeof window === 'undefined' ? this : window);
