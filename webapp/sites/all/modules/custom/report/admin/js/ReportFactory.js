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
        throw new Error('ReportFactory requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportFactory requires GD');
    }

    var GD = global.GD;

    var ReportFactory = {
        getReport: function( id, options, done, fail, always ) {

            var settings = {
                contentType: "application/json; charset=utf-8",
                dataType: 'json'
            };

            if ( options && options.async ) {
                settings.async = options.async;
            }

            GD.AjaxFactory.GET('/api/report/' + id + '.json', settings, done, fail, always);
        },

        getReportList: function ( datasource, done, fail, always ) {
            var settings = {
                contentType: "application/json; charset=utf-8",
                dataType: 'json',
                data: {
                    filter: {
                        datasource: typeof datasource == 'undefined' || datasource == null ? GovdashAdmin.getActiveDatasourceName() : datasource
                    }
                }
            };

            GD.AjaxFactory.GET('/api/report.json', settings, done, fail, always );
        },

        createReport: function ( report, done, fail, always ) {
            var settings = {
                contentType: "application/json; charset=utf-8",
                dataType: 'json',
                data: JSON.stringify({'report':report})
            };
            GD.AjaxFactory.POST('/api/report.json', settings, done, fail, always);
        },

        updateReport: function ( id, report, done, fail, always ) {
            var settings = {
                contentType: "application/json; charset=utf-8",
                dataType: 'json',
                data: JSON.stringify({'report':report})
            };
            GD.AjaxFactory.PUT('/api/report/' + id + '.json', settings, done, fail, always);
        },

        deleteReport: function ( id, done, fail, always ) {
            var settings = {
                contentType: "application/json; charset=utf-8",
                dataType: 'json'
            };
            GD.AjaxFactory.DELETE('/api/report/' + id + '.json', settings, done, fail, always);
        },
        getReportReference: function(id, done, fail, always){
            var settings = {
                contentType: "application/json; charset=utf-8",
                dataType: 'json'
            };
            return GD.AjaxFactory.GET('/api/report/' + id + '/referenced.json', settings, done, fail, always);
        },
        getData: function ( report, done, fail, always ) {
            var settings = {
                contentType: "application/json; charset=utf-8",
                dataType: 'json',
                data: JSON.stringify({'report':report})
            };
            GD.AjaxFactory.POST('/api/report/data.json', settings, done, fail, always);
        }
        
    };

    // add to global space
    global.GD.ReportFactory = ReportFactory;

})(typeof window === 'undefined' ? this : window, jQuery);