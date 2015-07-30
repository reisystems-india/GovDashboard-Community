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
        throw new Error('FilterOperator requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('FilterOperator requires GD');
    }

    var GD = global.GD;

    global.GD.FilterOperator = GD.Class.extend({
        options: null,
        name: null,
        value: null,
        modifier: null,

        init: function(object, options) {
            this.name = object['operator'];
            this.options = options;

            if (object) {
                this.value = this.formatValue(object['value']);
            }

            if (options) {
                this.modifier = options['mod'];
            }
        },

        getName: function() {
            return this.name;
        },

        format: function(v) {
            if (GD.Utility.isValidDateTime(v)) {
                return GD.Utility.formatDateTime(v);
            } else if (GD.Utility.isValidDate(v)) {
                return GD.Utility.formatDate(v);
            } else if (GD.Utility.isValidTime(v)) {
                return GD.Utility.formatTime(v);
            }

            return v;
        },

        formatValue: function(value) {
            if ($.isArray(value)) {
                var v = [];
                for (var i = 0; i < value.length; i++) {
                    v.push(this.format(value[i]));
                }
                return v;
            }

            return this.format(value);
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);