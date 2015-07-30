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
        throw new Error('Notification requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('Notification requires GD');
    }

    var GD = global.GD;

    global.GD.Notification = GD.Class.extend({
        options: null,
        value: null,
        original: null,
        changed: false,

        init: function(object, options) {
            this.options = options;
            this.value = null;
            this.changed = false;

            if (object) {
                this.value = object;
            }

            this.original = this.value ? this.value : 0;
        },

        setChanged: function(val) {
            this.changed = val ? true : false;
        },

        isChanged: function() {
            return this.changed || this.getValue() != this.original;
        },

        getValue: function() {
            return this.value;
        },

        resetOriginal: function(){
          this.original = null;
        },

        setValue: function(val, org) {
            this.value = val;

            if (org) {
                this.original =  val;
            }
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);