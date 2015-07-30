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
        throw new Error('ViewSlider requires GD');
    }

    var GD = global.GD;

    (function($,Highcharts) {

        if ( typeof $ === 'undefined' ) {
            throw new Error('ViewSlider requires jQuery');
        }

        var ViewSlider = GD.ViewPrimitive.extend({

            min: 0,
            max: 0,
            step: 0,
            range: null,
            valueLabel: null,

            init: function ( object, container, options ) {
                this._super(object,container,options);

                if ( typeof object != "undefined" ) {
                    this.setOptions(object);
                }

                this.formitem = $('<div tabindex="3000" class="'+this.getViewCss()+'"></div>');
                this.formitem.attr('id', this.getId());
                this.valueLabel = $('<div class="'+this.getLabelCss()+'"></div>');

                var _this = this;
                this.formitem.slider({
                    min: _this.min,
                    max: _this.max,
                    step: _this.step,
                    slide: function( event, ui ) {
                        _this.sliderEvent(event,ui);
                    },
                    change: function ( event, ui ) {
                        _this.sliderEvent(event,ui);
                    }
                });

                this.formitem.keydown(function ( e ) {
                    var keyCode = e.keyCode || e.which;
                    var value = _this.formitem.slider('value');
                    if ( keyCode == 37 ) {
                        if ( --value >= _this.min ) {
                            _this.formitem.slider('value', value);
                        }
                    } else if ( keyCode == 39 ) {
                        if ( ++value <= _this.max ) {
                            _this.formitem.slider('value', value);
                        }
                    }
                });

                return this;
            },

            getId: function () {
                return GD.Util.IdGenerator.getId({name: typeof this.options['FilterName'] != 'undefined' ? this.options['FilterName'] : 'gd-view-slider'});
            },

            setOptions: function ( options ) {
                if ( typeof options['min'] != "undefined" )
                    this.min = parseInt(options['min']);

                if ( typeof options['max'] != "undefined" )
                    this.max = parseInt(options['max']);

                if ( typeof options['step'] != "undefined" )
                    this.step = parseInt(options['step']);

                if ( typeof options['range'] != "undefined" )
                    this.range = options['range'];
            },

            setValue: function ( value ) {
                if (value) {
                    this.formitem.slider("value", value);
                } else {
                    this.formitem.slider("value", this.min);
                }
                this.valueLabel.text(this.formitem.slider("value"));
            },

            getValue: function () {
                return this.formitem.slider("value");
            },

            setMin: function ( value ) {
                this.formitem.slider("option", "min", value);
            },

            setMax: function ( value ) {
                this.formitem.slider("option", "max", value);
            },

            setStep: function ( value ) {
                this.formitem.slider("option", "step", value);
            },

            setRange: function ( value ) {
                this.formitem.slider("option", "range", value);
            },

            getViewCss: function () {
                return this._super() + ' gd-view-slider';
            },

            getLabelCss: function () {
                return this._super() + ' gd-label-slider';
            },

            getContainerCss: function () {
                return this._super() + ' gd-container-slider';
            },

            sliderEvent: function (event, ui) {
                this.valueLabel.text( ui.value );
            },

            render: function () {
                var output = $('<div class="'+this.getContainerCss()+'"></div>').append(this.formitem, '<br/>', this.valueLabel);
                output.uniqueId();

                if ( this.container == null ) {
                    return output;
                } else {
                    $(this.container).append(output);
                }
            }
        });

        global.GD.ViewSlider = ViewSlider;
    })(typeof global.GD_jQuery != 'undefined' ? global.GD_jQuery : jQuery, typeof global.GD_Highcharts != 'undefined' ? global.GD_Highcharts : (typeof Highcharts != 'undefined' ? Highcharts : undefined));
})(typeof window === 'undefined' ? this : window);
