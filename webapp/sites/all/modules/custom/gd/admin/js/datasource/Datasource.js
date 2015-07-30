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
        throw new Error('Datasource requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('Datasource requires GD');
    }

    var GD = global.GD;

    var Datasource = GD.Class.extend({

        name: null,
        publicName: null,
        description: null,
        active: null,

        init: function(object) {
            $.extend(this,object);
        },

        getName: function () {
            return this.name;
        },

        getPublicName: function () {
            return this.publicName;
        },

        getDescription: function () {
            return this.description;
        },

        isActive: function () {
            if ( this.active ) {
                return true;
            } else {
                return false;
            }
        },

        isReadOnly: function () {
            if ( this.readonly ) {
                return true;
            } else {
                return false;
            }
        }
    });

    // add to global space
    global.GD.Datasource = Datasource;

})(typeof window === 'undefined' ? this : window, jQuery);