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
        throw new Error('ReportFormatFormFactory requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportFormatFormFactory requires GD');
    }

    var GD = global.GD;

    global.GD.ReportFormatFormFactory = {

        getForm: function ( format, container, options ) {
            var form = null;
            switch ( format.columnType ) {
                case 'date' :
                    form = new GD.ReportColumnFormatDateForm(format,container,options);
                    break;
                case 'integer' :
                case 'number' :
                case 'percent' :
                case 'currency' :
                    form = new GD.ReportColumnFormatNumericForm(format,container,options);
                    break;
                case 'URI' :
                    form = new GD.ReportColumnFormatURIForm(format,container,options);
                    break;
                default:
                    form = new GD.ReportColumnFormatForm(format,container,options);
            }
            return form;
        }

    };

})(typeof window === 'undefined' ? this : window, jQuery);