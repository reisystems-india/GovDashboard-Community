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
        throw new Error('DatafileFactory requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DatafileFactory requires GD');
    }

    var GD = global.GD;

    var DatafileFactory = {

        getDatafile: function( id, done, fail, always ) {
            var settings = {
                dataType: 'json'
            };
            GD.AjaxFactory.GET('/api/datafile/' + id + '.json', settings, done, fail, always);
        },

        createDatafile: function ( datafile, done, fail, always ) {
            var settings = {
                dataType: 'json',
                data: datafile
            };
            GD.AjaxFactory.POST('/api/datafile.json', settings, done, fail, always);
        },

        updateDatafile: function ( id, datafile, done, fail, always ) {
            var settings = {
                dataType: 'json',
                data: datafile
            };
            GD.AjaxFactory.PUT('/api/datafile/' + id + '.json', settings, done, fail, always);
        },

        deleteDatafile: function ( id, done, fail, always ) {
            var settings = {
                dataType: 'json'
            };
            GD.AjaxFactory.DELETE('/api/datafile/' + id + '.json', settings, done, fail, always);
        },

        getStructure: function( id, done, fail, always ) {
            var settings = {
                dataType: 'json',
                data: {
                    'ds': GovdashAdmin.getActiveDatasourceName()
                }
            };
            GD.AjaxFactory.GET('/api/datafile/' + id + '/structure.json', settings, done, fail, always);
        }
    };

    // add to global space
    global.GD.DatafileFactory = DatafileFactory;

})(typeof window === 'undefined' ? this : window, jQuery);