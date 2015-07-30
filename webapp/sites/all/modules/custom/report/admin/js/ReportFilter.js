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
        throw new Error('ReportFilter requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportFilter requires GD');
    }

    var GD = global.GD;

    global.GD.ReportFilter = GD.Filter.extend({
        value: null,
        text: null,
        name: null,
        exposed: null,
        operator: null,
        type: null,
        column: null,

        init: function(object) {
            this.value = null;
            this.text = null;
            this.name = null;
            this.exposed = null;
            this.operator = null;
            this.value = null;
            this.column = null;

            if (object) {
                if (object['operator']) {
                    this.operator = GD.OperatorFactory.getOperator(object['operator'], object);
                }

                this.name = object['name'];
                this.exposed = object['exposed'];
                this.value = object['value'];
                this.column = object['column'];
                this.type = object['column'] ? object['column']['type'] : null;
                this.setText();
            }
        },

        setText: function() {
            this.text = this.name + (this.operator ? ' ' + this.operator.toString() : '');
        },

        getType: function() {
            return this.type;
        },

        getColumn: function() {
            return this.column;
        },

        setColumn: function(column) {
            this.column = column;
        },

        getExposed: function() {
            return this.exposed;
        },

        setExposed: function(exposed) {
            this.exposed = exposed;
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
            return this.column ? [this.column['datasetName']] : null;
        },

        getColumnName: function() {
            if(typeof(this.column['id']) == 'undefined') {
                return this.column.name;
            }
            
            return this.column ? this.column['id'] : '';
        },

        getColumnPublicName: function() {
            return this.column ? this.column['name'] : '';
        },

        getName: function() {
            return this.name;
        },

        getConfig: function() {
            if(this.getColumn()){
                return {
                    column: this.getColumn().name || this.column,
                    exposed: this.getExposed() ? 1 : 0,
                    name: this.getName() || this.name,
                    value: this.getValue() || this.value,
                    operator: this.getOperator() || this.operator
                };
            }
         }
    });
})(typeof window === 'undefined' ? this : window, jQuery);