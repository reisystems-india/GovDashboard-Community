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
        throw new Error('DashboardWidgetFactory requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DashboardWidgetFactory requires GD');
    }

    var GD = global.GD;

    var DashboardWidgetFactory = {
        sequence: 0,

        getWidget: function(type,object,options) {

            var widget = null;
            object.id = this.sequence++;

            switch ( type ) {

                case 'text' :
                    widget = new GD.DashboardWidgetText(object,options);
                    break;

                case 'image' :
                    widget = new GD.DashboardWidgetImage(object,options);
                    break;

                case 'report' :
                    widget = new GD.DashboardWidgetReport(object,options);
                    break;

                default:
                    throw new Error('Missing or unknown widget type');
                    break;
            }

            return widget;
        }
    };

    // add to global space
    global.GD.DashboardWidgetFactory = DashboardWidgetFactory;

})(typeof window === 'undefined' ? this : window, jQuery);