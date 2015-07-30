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

(function(global,$) {

    var DatasetWidgetUrl = {

        name: 'url',

        title: 'URL',

        routes: ['/cp/dataset/new/url'],

        dispatch: function ( request ) {

            $('#gd-admin-header').append('<div class="page-header"><h1>Dataset Management <small>File Upload</small></h1></div>');


            var markup = 'SCRIPT FORM';

            $('#gd-admin-body').append(markup);


        }

    };

    global.GD.DatasetWidgetUrl = DatasetWidgetUrl;

})(typeof window === 'undefined' ? this : window, jQuery);