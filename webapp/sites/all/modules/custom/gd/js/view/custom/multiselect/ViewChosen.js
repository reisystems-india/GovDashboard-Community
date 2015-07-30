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

    if (!global.GD) {
        throw new Error('ViewChosen requires GD');
    }

    var GD = global.GD;

    (function($,Highcharts) {

        if (!$) {
            throw new Error('ViewChosen requires jQuery');
        }

        if (!$.fn.chosen) {
            throw new Error('ViewChosen requires Chosen');
        }

        var ViewChosen = GD.ViewPrimitive.extend({
            label: null,
            title: null,
            choices: null,

            init: function ( object, container, options ) {
                this._super(object,container,options);
                this.formitem = $('<select class="gd-view-chosen" multiple></select>');

                if (object) {
                    this.setOptions(object);
                }

                if (!options) {
                    return;
                }

                if (options.id) {
                    this.formitem.attr('id', options.id);
                }

                if (options.title) {
                    this.setTitle(options.title);
                } else {
                    this.setTitle('Options');
                }

                if (options.limit) {
                    this.setLimit(options.limit);
                }
            },

            setValue: function(value) {
                this.formitem.val(value);
                this.formitem.trigger('liszt:updated');
            },

            getTitle: function() {
                return this.title;
            },

            setTitle: function(title) {
                this.title = title;
            },

            getLimit: function() {
                return this.limit;
            },

            setLimit: function(limit) {
                this.limit = limit;
            },

            getOptions: function() {
                return this.object;
            },

            setOptions: function(options, groups) {
                var _this = this;
                if (options && $.isArray(options)) {
                    $.each(options, function(i, o) {
                        if (groups) {
                            var group = $('<optgroup></optgroup>');
                            group.attr('label', o['name']);
                            $.each(o['children'], function(i, c) {
                                group.append('<option class="gd-view-chosen-option" value="'+c['value']+'">'+c['text']+'</option>');
                            });
                            _this.formitem.append(group);
                        } else {
                            _this.formitem.append('<option class="gd-view-chosen-option" value="'+o['value']+'">'+o['text']+'</option>');
                        }
                    });
                }
            },

            getValues: function() {
                return this.formitem.val();
            },

            setValues: function (values) {
                this.formitem.val(values);
            },

            getLabel: function() {
                if (!this.label) {
                    this.label = $('<span class="gd-view-chosen-label">' + this.title + '</span>');
                }
            },

            render: function () {
                var _this = this;

                var output = $('<div class="gd-view-chosen-container" style="margin-top: 20px;"></div>');                
                

                output.append($('<div class="row"></div>').append(this.formitem));
                this.formitem.chosen({
                    width: '91%',
                    max_selected_options: _this.limit ? _this.limit : 100,
                    no_results_text: 'No results found'
                });

                output.prepend('<div class="col-md-1 noMP"><label class="pull-left" style="margin-top: 5px;" for="' + this.formitem.attr('id') + '" class="gd-view-chosen-label">' + this.title + ':</label></div>');

                if ( this.container == null ) {
                    return output;
                } else {
                    $(this.container).append(output);
                    $('#' + this.formitem.attr('id') + '_chzn').css('overflow', 'hidden').find("input").attr("tabindex", "3000");
                    return output;
                }
            }
        });

        global.GD.ViewChosen = ViewChosen;
    })(global.GD_jQuery ? global.GD_jQuery : jQuery, global.GD_Highcharts ? global.GD_Highcharts : (Highcharts ? Highcharts : undefined));
})(window ? window : this, jQuery);
