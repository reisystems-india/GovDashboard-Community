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
        throw new Error('DatasourceFactory requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DatasourceFactory requires GD');
    }

    var GD = global.GD;

    //  TODO Migrate from success to done, fail and always
    var DatasourceFactory = {

        getExport: function( datasource, done, fail, always ) {
            var settings = {
                contentType: "application/json; charset=utf-8",
                dataType: 'json'
            };

            return GD.AjaxFactory.GET('/api/datasource/' + datasource + '/export.json', settings, done, fail, always );
        },

        sync: function(datasource, data, done, fail, always) {
            var settings = {
                contentType: "application/json; charset=utf-8",
                dataType: 'json',
                data: data
            };

            return GD.AjaxFactory.POST('/api/datasource/' + datasource + '/sync.json', settings, done, fail, always );
        },

        sendImport: function( data, done, fail, always ) {
            var settings = {
                contentType: "application/json; charset=utf-8",
                dataType: 'json',
                data: data
            };

            return GD.AjaxFactory.POST('/api/datasource/import.json', settings, done, fail, always );
        },

        getDatasourceIndex: function ( options, done, fail, always ) {
            var settings = {
                contentType: "application/json; charset=utf-8",
                dataType: 'json',
                data: {}
            };

            if ( options && options.sort ) {
                settings.data.sort = options.sort;
            }

            return GD.AjaxFactory.GET('/api/datasource.json', settings, done, fail, always );
        },

        getDatasourceList: function ( callback ) {
            var settings = {
                contentType: "application/json; charset=utf-8",
                dataType: 'json',
                success: callback
            };
            GD.AjaxFactory.GET('/api/datasource.json', settings);
        },

        getDatasource: function( id, callback ) {
            var settings = {
                contentType: "application/json; charset=utf-8",
                dataType: 'json',
                success: callback
            };
            GD.AjaxFactory.GET('/api/datasource/' + id + '.json', settings);
        },

        createDatasource: function ( datasource, callback ) {
            var settings = {
                contentType: "application/json; charset=utf-8",
                dataType: 'json',
                success: callback,
                data: JSON.stringify({'datasource':datasource})
            };
            GD.AjaxFactory.POST('/api/datasource.json', settings);
        },

        updateDatasource: function ( id, datasource, callback ) {
            var settings = {
                contentType: "application/json; charset=utf-8",
                dataType: 'json',
                success: callback,
                data: JSON.stringify({'datasource':datasource})
            };
            GD.AjaxFactory.PUT('/api/datasource/' + id + '.json', settings);
        },

        deleteDatasource: function ( id, callback ) {
            var settings = {
                contentType: "application/json; charset=utf-8",
                dataType: 'json',
                success: callback
            };
            GD.AjaxFactory.DELETE('/api/datasource/' + id + '.json', settings);
        },

        setActiveDatasource: function ( id, callback ) {
            var settings = {
                contentType: "application/json; charset=utf-8",
                dataType: 'json',
                success: callback
            };
            GD.AjaxFactory.POST('/api/datasource/' + id + '/setactive.json', settings);
        }
    };

    // add to global space
    global.GD.DatasourceFactory = DatasourceFactory;

})(typeof window === 'undefined' ? this : window, jQuery);