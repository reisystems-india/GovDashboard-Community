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
        throw new Error('Builder requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('Builder requires GD');
    }

    var GD = global.GD;

    var Builder = GD.Class.extend({
        admin: null,

        init: function ( options ) {
            //  Wanted to pass options array into new Admin()
            var createAdmin = (function() {
                function Admin(args) {
                    return GD.Admin.apply(this, args);
                }
                Admin.prototype = GD.Admin.prototype;

                return function() {
                    return new Admin(arguments);
                }
            })();

            this.admin = createAdmin(options);
        },

        getActiveDatasource: function() {
            return this.admin.getActiveDatasource();
        },

        getDatasourceName: function() {
            return this.admin.getActiveDatasourceName();
        },

        run: function() {
            this.admin.run();
        }
    });

    // add to global space
    global.GD.Builder = Builder;

})(typeof window === 'undefined' ? this : window, jQuery);
