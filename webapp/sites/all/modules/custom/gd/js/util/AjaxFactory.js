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

(function(global,undefined) {

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('AjaxFactory requires GD');
    }

    var GD = global.GD;

    (function($,Highcharts) {

        if ( typeof $ === 'undefined' ) {
            throw new Error('AjaxFactory requires jQuery');
        }

        var AjaxFactory = {
            call: function (type, url, settings, doneCallback, failCallback, alwaysCallback) {

                if ( typeof settings.async == 'undefined' ) {
                    settings.async = true;
                }

                return $.ajax({
                    type: type,
                    url: url,
                    data: settings.data,
                    success: settings.success,
                    dataType: settings.dataType,
                    contentType: settings.contentType,
                    async: settings.async
                }).done(doneCallback).fail(failCallback).always(alwaysCallback);
            },

            GET: function (url, settings, done, fail, always) {
                return this.call('GET', url, settings, done, fail, always);
            },

            POST: function (url, settings, done, fail, always) {
                return this.call('POST', url, settings, done, fail, always);
            },

            PUT: function (url, settings, done, fail, always) {
                return this.call('PUT', url, settings, done, fail, always);
            },

            DELETE: function (url, settings, done, fail, always) {
                return this.call('DELETE', url, settings, done, fail, always);
            }
        };

        // add to global space
        global.GD.AjaxFactory = AjaxFactory;
    })(typeof global.GD_jQuery != 'undefined' ? global.GD_jQuery : jQuery, typeof global.GD_Highcharts != 'undefined' ? global.GD_Highcharts : (typeof Highcharts != 'undefined' ? Highcharts : undefined));
})(typeof window === 'undefined' ? this : window);