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
        throw new Error('DatasetFactory requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DatasetFactory requires GD');
    }

    var GD = global.GD;

    var DatasetFactory = {
        getDataset: function( id, done, fail, always  ) {
            var settings = {
                dataType: 'json'
            };
            GD.AjaxFactory.GET('/api/dataset/' + id + '.json', settings, done, fail, always );
        },

        getDataTypes: function ( datasource, done, fail, always ) {
            var settings = {
                dataType: 'json',
                data: {
                    'ds': typeof datasource == 'undefined' || datasource == null ? GovdashAdmin.getActiveDatasourceName() : datasource
                }
            };
            GD.AjaxFactory.GET('/api/datatype.json', settings, done, fail, always );
        },

        getDatasetHistory: function ( id, done, fail, always ) {
            var settings = {
                dataType: 'json'
            };
            GD.AjaxFactory.GET('/api/dataset/' + id + '/data/changelog.json', settings, done, fail, always );
        },

        getPreviewData: function ( id, done, fail, always ) {
            var settings = {
                dataType: 'json'
            };
            GD.AjaxFactory.GET('/api/dataset/' + id + '/data.json', settings, done, fail, always );
        },

        getReferenced: function ( id, done, fail, always ) {
            var settings = {
                dataType: 'json'
            };
            GD.AjaxFactory.GET('/api/dataset/' + id + '/referenced.json', settings, done, fail, always );
        },

        getDatasetUiMetadata: function( id, done, fail, always  ) {
            var settings = {
                dataType: 'json'
            };
            GD.AjaxFactory.GET('/api/dataset/' + id + '/ui.json', settings, done, fail, always );
        },

        getDatasetList: function ( datasource, done, fail, always ) {
            var settings = {
                contentType: "application/json; charset=utf-8",
                dataType: 'json',
                data: {
                    filter: {
                        'datasource': typeof datasource == 'undefined' || datasource == null ? GovdashAdmin.getActiveDatasourceName() : datasource
                    }
                }
            };
            GD.AjaxFactory.GET('/api/dataset.json', settings, done, fail, always );
        },

        createDataset: function ( dataset, done, fail, always ) {
            var settings = {
                contentType: "application/json; charset=utf-8",
                dataType: 'json',
                data: JSON.stringify({'dataset':dataset})
            };
            GD.AjaxFactory.POST('/api/dataset.json', settings, done, fail, always );
        },

        updateDataset: function ( id, dataset, done, fail, always ) {
            var settings = {
                contentType: "application/json; charset=utf-8",
                dataType: 'json',
                data: JSON.stringify({'dataset':dataset})
            };
            GD.AjaxFactory.PUT('/api/dataset/' + id + '.json', settings, done, fail, always);
        },

        deleteDataset: function ( id, done, fail, always ) {
            var settings = {
                contentType: "application/json; charset=utf-8",
                dataType: 'json'
            };
            GD.AjaxFactory.DELETE('/api/dataset/' + id + '.json', settings, done, fail, always);
        },

        truncateDataset: function ( id, done, fail, always ) {
            var settings = {
                contentType: "application/json; charset=utf-8",
                dataType: 'json'
            };
            GD.AjaxFactory.POST('/api/dataset/' + id + '/truncate.json', settings, done, fail, always );
        }
    };

    // add to global space
    global.GD.DatasetFactory = DatasetFactory;

})(typeof window === 'undefined' ? this : window, jQuery);