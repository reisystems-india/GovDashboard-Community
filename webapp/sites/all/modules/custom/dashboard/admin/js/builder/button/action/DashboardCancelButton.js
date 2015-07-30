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
        throw new Error('DashboardCancelButton requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DashboardCancelButton requires GD');
    }

    var GD = global.GD;

    global.GD.DashboardCancelButton = GD.BuilderButton.extend({
        redirectLink: '/',

        init: function(object, options) {
            this.buttonTop = $('#dashboardCancelTop');
            this.buttonBottom = $('#dashboardCancelBottom');
            this.redirectLink = '/cp/dashboard';

            if (options) {
                if (options['builder']) {
                    this.redirectLink += '?ds=' + options['builder'].getDatasourceName();
                }
            }

            this._super(null, options);
        },

        clickedButton: function(e) {
            this.redirect();
        },

        redirect: function() {
            location.href = this.redirectLink;
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);