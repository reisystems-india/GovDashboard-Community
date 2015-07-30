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
        throw new Error('ViewRadioGroup requires GD');
    }

    var GD = global.GD;

    (function($,Highcharts) {

        if ( typeof $ === 'undefined' ) {
            throw new Error('ViewRadioGroup requires jQuery');
        }

        //  TODO define view composite abstraction
        var ViewRadioGroup = GD.ViewPrimitive.extend({
            forms: null,
            groupName: null,

            init: function ( object, container, options ) {
                this._super(object,container,options);

                if (typeof this.options['FilterName'] != "undefined") {
                    if (typeof this.options['group'] != "undefined") {
                        this.setName(this.options.FilterName + '-' + this.options.group);
                    } else {
                        this.setName(this.options.FilterName);
                    }
                }

                this.forms = [];

                this.formitem = $('<fieldset class="' + this.getViewCss() + '"></fieldset>');
                this.formitem.uniqueId();
                this.formitem.append('<legend>Options</legend>');

                if (typeof this.object['values'] != "undefined") {
                    this.setOptions(this.object.values);
                }

                return this;
            },

            getValue: function () {
                var values = [];
                $.each(this.forms, function (index, form) {
                    if (form.isChecked())
                        values.push(form.getValue());
                });

                if ( values.length == 1)
                    return values[0];

                return values;
            },

            setValue: function ( value ) {
                $.each(this.forms, function (index, form) {
                    if ($.isArray(value)) {
                        if ($.inArray(form.getValue(), value) != -1) {
                            form.setChecked(true)
                        } else {
                            form.setChecked(false);
                        }
                    } else {
                        if (form.getValue() == value) {
                            form.setChecked(true);
                        } else {
                            form.setChecked(false);
                        }
                    }
                });
            },

            setName: function ( name ) {
                this.groupName = name;
            },

            getViewCss: function () {
                return this._super() + ' gd-view-radio-group';
            },

            setOptions: function ( options ) {
                var _this = this;
                $.each(options, function (index, option) {
                    var radio = new GD.ViewRadio(option, _this.formitem, _this.options);
                    radio.setGroupName(_this.groupName);
                    _this.forms.push(radio);
                });
            },

            render: function () {
                $.each(this.forms, function (index, form) {
                    form.render();
                });

                if ( this.container == null ) {
                    return this.formitem;
                } else {
                    $(this.container).append(this.formitem);
                }

            }
        });

        global.GD.ViewRadioGroup = ViewRadioGroup;
    })(typeof global.GD_jQuery != 'undefined' ? global.GD_jQuery : jQuery, typeof global.GD_Highcharts != 'undefined' ? global.GD_Highcharts : (typeof Highcharts != 'undefined' ? Highcharts : undefined));
})(typeof window === 'undefined' ? this : window);
