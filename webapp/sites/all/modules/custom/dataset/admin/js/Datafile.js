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
        throw new Error('Datafile requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('Datafile requires GD');
    }

    var GD = global.GD;

    var Datafile = GD.Class.extend({
        record: null,
        options: null,

        structure: null,
        preview: null,

        init: function(object, options) {
            $.extend(this,object);
            this.record = object;
            this.options = options;
        },

        reset: function () {
            this.record = null;
            this.structure = null;
            this.preview = null;
        },

        getRecord: function () {
            return this.record;
        },

        getId: function () {
            return this.record.id;
        },

        getHasHeader: function () {
            if ( this.record === null ) {
                return null;
            } else {
                return this.record.hasheader;
            }
        },

        setHasHeader: function ( value ) {
            this.record.hasheader = value;
        }
    });

    // add to global space
    global.GD.Datafile = Datafile;

})(typeof window === 'undefined' ? this : window, jQuery);