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

    (function(global,$,undefined) {

        if ( typeof $ === 'undefined' ) {
            throw new Error('Filter requires jQuery');
        }

        if ( typeof global.GD === 'undefined' ) {
            throw new Error('Filter requires GD');
        }

        var GD = global.GD;

        //  TODO Refactor
        global.GD.Filter = GD.Class.extend({
            value: null,
            text: null,
            name: null,
            exposed: null,
            exposedType: null,
            operator: null,
            type: null,
            column: null,
            visible: true,

            init: function(object) {
                this.value = null;
                this.text = null;
                this.name = null;
                this.exposed = null;
                this.exposedType = null;
                this.operator = null;
                this.value = null;
                this.column = null;
                this.visible = true;

                if (object) {
                    if (object['operator']) {
                        this.operator = GD.OperatorFactory.getOperator(object['operator'], object);
                    }

                    this.name = object['name'];
                    this.exposed = object['exposed'];
                    this.exposedType = object['exposedType'];
                    this.view = object['view'] ? object['view'] : {};
                    this.invalid = object['invalid'] ? true : false;
                    this.value = object['value'];
                    this.column = $.isArray(object['column']) ? object['column'] :  [object['column']];
                    this.type = object['type'];
                    this.setText();
                }
            },

            setVisible: function(visible) {
                this.visible = visible;
            },

            isVisible: function() {
                return this.visible && this.exposed;
            },

            setText: function() {
                this.text = this.name + (this.operator ? (' ' + this.operator.toString()) : '');
            },

            getType: function() {
                return this.type;
            },

            getExposed: function() {
                return this.exposed;
            },

            setExposed: function(exposed) {
                this.exposed = exposed;
            },

            getExposedType: function() {
                return this.exposedType;
            },

            setExposedType: function(type) {
                this.exposedType = type;
            },

            getOptions: function() {
                return this.view['options'] ? this.view['options'] : null;
            },

            setOptions: function(options) {
                if (this.view) {
                    this.view['options'] = options;
                }
            },

            getPresentationOperator: function() {
                return this.view['operator'] ? this.view['operator'] : null;
            },

            setPresentationOperator: function(operator) {
                if (this.view) {
                    this.view['operator'] = operator;
                }
            },

            getPresentationType: function() {
                return this.view['type'] ? this.view['type'] : null;
            },

            setPresentationType: function(type) {
                if (this.view) {
                    this.view['type'] = type;
                }
            },

            getView: function() {
                return this.view;
            },

            setView: function(view) {
                this.view = view;
            },

            getOperator: function() {
                return this.operator ? this.operator.getName() : null;
            },

            setOperator: function(operator) {
                if (typeof operator == 'string') {
                    operator = GD.OperatorFactory.getOperator(operator, { 'operator': operator, 'value': this.getValue() });

                }

                this.operator = operator;
                this.setText();
            },

            getValue: function() {
                return this.value;
            },

            setValue: function() {
                if (GD.Filter.isString(this.getType())) {
                    this.value = arguments[0];
                } else {
                    this.value = [];

                    for(var key in arguments) {
                        this.value.push(arguments[key]);
                    }
                }
            },

            getDatasetNames: function() {
                if (this.column) {
                    var names = [];
                    for (var i = 0; i < this.column.length; i++) {
                        if(this.column[i]){
                            names.push(this.column[i]['datasetName']);
                        }
                    }
                    return names;
                } else {
                    return [];
                }
            },

            getColumnName: function(i) {
                return this.column ? this.column[i]['name'] : '';
            },

            getName: function() {
                return this.name;
            },

            getConfig: function() {
                return {
                    exposed: this.getExposed() ? 1 : 0,
                    exposedType: this.getExposedType(),
                    name: this.getName(),
                    value: this.getValue(),
                    operator: this.getOperator(),
                    view: this.getView()
                };
            }
        });

        global.GD.Filter.isString = function(type) {
            return type == 'string' || (type['applicationType'] ? type['applicationType'] == 'string' : false);
        };

        global.GD.Filter.compareFilters = function(a, b) {
            return a['name'] == b['name'];
        };

        global.GD.Filter.isNumeric = function(type) {
            return $.inArray(type, ['number', 'currency', 'percent', 'percentage']) !== -1;
        };

        global.GD.Filter.isSliderPresentation = function(type) {
            return type && type.search(/slider/) != -1;
        };

        global.GD.Filter.getOperatorText = function (operator, type) {
            if (type && GD.OperatorFactory.operators[type]) {
                return GD.OperatorFactory.operators[type][operator];
            }

            return GD.OperatorFactory.operators['generic'][operator];
        };

        global.GD.Filter.isRangeOperator = function (operator) {
            return operator.search(/range/) !== -1;
        };

        global.GD.Filter.getFilterText = function ( filter ) {
            var text = '<div style="word-break: break-all;">';
            text += '<span style="font-weight: bold;">' + filter['name'] + '</span> ';
            text += '<span>' + GD.Filter.getOperatorText(filter['operator'], filter['type']) + '</span> ';
            text += '<span>' + GD.Filter.getValueText(filter) + '</span> ';
            text += '</div>';
            return text;
        };

        global.GD.Filter.getValueText = function ( filter ) {
            var text = null;
            if ( filter['value'] ) {
                if ( !$.isArray(filter['value']) ) {
                    filter['value'] = [filter['value']];
                }else{
                    if($.isArray(filter['value'][0])){
                        if($.isArray(filter['value'][0][0])){
                            filter['value'] = filter['value'][0][0];
                        }else{
                            filter['value'] = filter['value'][0];
                        }
                    }
                }
                var formatted = [];

                for ( var i=0, valueCount=filter['value'].length; i < valueCount; i++ ) {
                    formatted[i] = GD.Filter.getValueFormatted(filter['value'][i], filter['type']);

                }
                if ( GD.Filter.isRangeOperator(filter['operator']) ) {
                    text = formatted.join(' and ');
                } else {
                    text = formatted.join(', ');
                }
            }else{
                text='';
            }
            return text;
        };

        global.GD.Filter.getValueFormatted = function ( value, type ) {
            switch ( type ) {

                case 'date' :
                case 'datetime' :
                    return global.GD.Util.DateFormat.getUSDateTime(new Date(Date.parse(value)));// format fix for IE

                default:
                    return value;
            }
        }
        global.GD.Filter.renderOverlay = function(container, filters, title) {
            var parts = typeof container == 'string' ? container.split('-') : null;
            var containerID = null;
            if (parts) {
                containerID = parts[1];
            } else {
                containerID = $(container).attr('id');
            }

            var list = ['<ul style="list-style:none;margin-bottom: 0;">'];
            if (filters.length) {
                $.each(filters, function(i, f) {
                    if (f['operator']) {
                        list.push("<li>" + global.GD.Filter.getFilterText(f) + "</li>");
                    }
                });
            }

            if (list.length === 1) {
                list.push('<li><strong>No filters applied</strong></li>');
            }

            list.push('</ul>');
            $(container).find('[data-toggle="popover"]').popover({
                html: true,
                placement: 'left',
                content: list.join(''),
                template: '<div class="popover filter-overlay-popover" role="tooltip"><div class="arrow"></div><div class="popover-content"></div></div>'
            });

            $(document).ready(function() {
                if (containerID) {
                    var id = 'appendix-' + containerID;
                    //if (!$('#' + id).length) {
                        $('#appendix').append($('<div id="' + id + '" class="print-element clearfix"></div>').append($('<span class="print-element" style="font-weight: bold;"></span>').text(title), list.join('')));
                    //}
                }

            });
        };

        /*
            if (window) {
                window.onbeforeprint = function() {
                    $('.print-element').show();
                };

                window.onafterprint = function() {
                    $('.print-element').hide();
                };
            }*/

    })(typeof window === 'undefined' ? this : window, jQuery);
