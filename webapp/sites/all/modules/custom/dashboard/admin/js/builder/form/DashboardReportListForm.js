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
        throw new Error('DashboardReportsButton requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DashboardReportsButton requires GD');
    }

    var GD = global.GD;

    global.GD.DashboardReportListForm = GD.DashboardListForm.extend({
        getItems: function() {
            return this.object.getReports();
        },

        setItem: function(items, key) {
            this.items.push({'val': items[key]['id'], 'text': items[key]['title']});
        },

        removeItemFromDashboard: function() {
            this.dashboard.removeReport(this.removingItem);
        },

        getModalTitle: function() {
            return 'Delete Report';
        },

        getItemName: function() {
            return 'Report';
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);