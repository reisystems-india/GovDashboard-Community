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
        throw new Error('DashboardFactory requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DashboardFactory requires GD');
    }

    var GD = global.GD;

    var DashboardFactory = {
        getDashboard: function( id, done, fail, always ) {
            var settings = {
                contentType: "application/json; charset=utf-8",
                dataType: 'json'
            };
            GD.AjaxFactory.GET('/api/dashboard/' + id + '.json', settings, done, fail, always);
        },

        getDashboardList: function ( datasource, done, fail, always ) {
            var settings = {
                contentType: "application/json; charset=utf-8",
                dataType: 'json',
                data: {
                    filter: {
                        'datasource': typeof datasource == 'undefined' || datasource == null ? GovdashAdmin.getActiveDatasourceName() : datasource
                    },
                    fields: 'reports'
                }
            };
            GD.AjaxFactory.GET('/api/dashboard.json', settings, done, fail, always );
        },

        createDashboard: function ( dashboard, done, fail, always ) {
            var settings = {
                contentType: "application/json; charset=utf-8",
                dataType: 'json',
                data: JSON.stringify({'dashboard':dashboard})
            };
            GD.AjaxFactory.POST('/api/dashboard.json', settings, done, fail, always);
        },

        updateDashboard: function ( id, dashboard, done, fail, always ) {
            var settings = {
                contentType: "application/json; charset=utf-8",
                dataType: 'json',
                data: JSON.stringify({'dashboard':dashboard})
            };
            GD.AjaxFactory.PUT('/api/dashboard/' + id + '.json', settings, done, fail, always);
        },

        deleteDashboard: function ( id, done, fail, always ) {
            var settings = {
                contentType: "application/json; charset=utf-8",
                dataType: 'json'
            };
            GD.AjaxFactory.DELETE('/api/dashboard/' + id + '.json', settings, done, fail, always);
        }
    };

    // add to global space
    global.GD.DashboardFactory = DashboardFactory;

})(typeof window === 'undefined' ? this : window, jQuery);