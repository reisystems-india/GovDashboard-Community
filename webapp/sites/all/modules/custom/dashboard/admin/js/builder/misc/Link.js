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
        throw new Error('Link requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('Link requires GD');
    }

    var GD = global.GD;

    global.GD.Link = GD.Class.extend({
        id: null,
        column: null,
        dashboard: null,
        report: null,
        filters: null,

        init: function(object) {
            this.id = -1;
            this.column = null;
            this.dashboard = null;
            this.report = null;
            this.filters = [];

            if (object) {
                this.id = object['id'];
                this.column = object['column'];
                this.dashboard = object['dashboard'];
                this.report = object['report'];
                this.filters = object['filters'];
            }
        },

        isNew: function() {
            return this.id == -1;
        },

        setId: function(id) {
            this.id = id;
        },

        getId: function() {
            return this.id;
        },

        setFilters: function(f) {
            this.filters = f;
        },

        getFilters: function() {
            return this.filters;
        },

        setDashboard: function(d) {
            this.dashboard = d;
        },

        getDashboard: function() {
            return this.dashboard;
        },

        getDashboardId: function() {
            return this.getDashboard() ? this.getDashboard()['id'] : null;
        },

        getDashboardName: function() {
            return this.getDashboard() ? this.getDashboard()['name'] : null;
        },

        setReport: function(report) {
            this.report = report;
        },

        getReport: function() {
            return this.report;
        },

        getReportId: function() {
            return this.getReport() ? this.getReport()['id'] : null;
        },

        getReportName: function() {
            return this.getReport() ? this.getReport()['name'] : null;
        },

        setColumn: function(column) {
            this.column = column;
        },

        getColumn: function() {
            return this.column;
        },

        getColumnId: function() {
            return this.getColumn() ? this.getColumn()['id'] : null;
        },

        getColumnName: function() {
            return this.getColumn() ? this.getColumn()['name'] : null;
        },

        getText: function() {
            return this.getColumnName() + ' of ' + this.getReportName() + ' to ' + this.getDashboardName();
        },

        getConfig: function() {
            return {
                id: this.getId(),
                column: this.getColumnId(),
                dashboard: this.getDashboardId(),
                filters: this.getFilters(),
                report: this.getReportId()
            };
        }
    });

    global.GD.Link.compareLinks = function(a, b) {
        return a.getId() == b.getId();
    };

})(typeof window === 'undefined' ? this : window, jQuery);