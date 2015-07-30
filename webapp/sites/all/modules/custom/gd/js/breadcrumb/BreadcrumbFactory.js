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
        throw new Error('BreadcrumbFactory requires GD');
    }

    var GD = global.GD;

    (function($,Highcharts) {
        if ( typeof $ === 'undefined' ) {
            throw new Error('BreadcrumbFactory requires jQuery');
        }

        var BreadcrumbFactory = {

            renderBreadcrumbs: function ( breadcrumbs, container, options ) {
                if (typeof breadcrumbs == 'undefined' || breadcrumbs == null || breadcrumbs.length <= 1)
                    return;

                var breadcrumbContainer = $('<div id="gd-breadcrumb-container"></div>');
                for ( var i = 0 ; i < breadcrumbs.length - 1 ; i ++ ) {
                    var view = new GD.BreadcrumbView(breadcrumbs[i], breadcrumbContainer, options);
                    view.render();
                    var separator = new GD.BreadcrumbSeparator(null, breadcrumbContainer, options);
                    separator.render();
                }

                new GD.BreadcrumbView(breadcrumbs[breadcrumbs.length - 1], breadcrumbContainer, options).render();

                if ( typeof container == "undefined" )
                    return breadcrumbContainer;
                else
                    $(container).append(breadcrumbContainer);
            }
        };

        global.GD.BreadcrumbFactory = BreadcrumbFactory;
    })(typeof global.GD_jQuery != 'undefined' ? global.GD_jQuery : jQuery, typeof global.GD_Highcharts != 'undefined' ? global.GD_Highcharts : (typeof Highcharts != 'undefined' ? Highcharts : undefined));
})(typeof window === 'undefined' ? this : window);