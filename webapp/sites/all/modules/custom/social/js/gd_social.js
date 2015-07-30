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


(function(global,undefined){


    if ( typeof global.GD === 'undefined' ) {
        throw new Error('GD_Social requires GD');
    }

    var GD = global.GD;

    (function($,Highcharts) {
        if ( typeof $ === 'undefined' ) {
            throw new Error('GD_Social requires jQuery');
        }

        $(document).ready(function() {
            var shareSection = $('<div class="share-section"></div>');
            var shareButton = $('<div class="share-button"></div>');
            var shareList = $('<div class="share-list" style="display:none;"></div>');
            shareSection.append(shareButton, shareList);
            shareSection.insertBefore($('#dashboard-viewer-edit-button'));

            $.event.trigger({
                'type': 'gd-social-register',
                'list': shareList,
                'button': shareButton,
                'section': shareSection
            });

            $(shareButton).click(function() {
                shareList.slideToggle('down');
            });

            $(shareList).click(function() {
                shareList.slideToggle('down');
            });
        });
    })(typeof global.GD_jQuery != 'undefined' ? global.GD_jQuery : jQuery, typeof global.GD_Highcharts != 'undefined' ? global.GD_Highcharts : (typeof Highcharts != 'undefined' ? Highcharts : undefined));
})(typeof window === 'undefined' ? this : window);
