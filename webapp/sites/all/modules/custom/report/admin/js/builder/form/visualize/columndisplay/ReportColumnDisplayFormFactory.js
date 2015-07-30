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
        throw new Error('ReportColumnDisplayFormFactory requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportColumnDisplayFormFactory requires GD');
    }

    var GD = global.GD;

    global.GD.ReportColumnDisplayFormFactory = {

        getForm: function ( format, container, options ) {

            var form = null;
            
            switch ( format.columnType ) {               
                case 'integer' :
                case 'number' :
                case 'percent' :
                case 'currency' :
                    if ( options.builder.getReport().getChartType() == 'line' || options.builder.getReport().getChartType() == 'area' ){
                        form = new GD.ReportColumnDisplayLineAreaNumericForm(format,container,options);
                    }
                    else if(options.builder.getReport().getChartType()=='bar' || options.builder.getReport().getChartType()=='column') {
                        form = new GD.ReportColumnDisplayNumericForm(format,container,options);
                    }
                    else if(options.builder.getReport().getChartType()=='pie') {
                        form = new GD.ReportColumnDisplayPieNumericForm(format,container,options);
                    }
                    break;               
                default:
                    form = new GD.ReportColumnDisplayStringForm(format,container,options);
            }

            if(options.builder.getReport().getChartType() =='dynamic_text'){
                form = new GD.ReportColumnDisplayDynamicNumericForm(format,container,options);
            }

            return form;
        }

    };

})(typeof window === 'undefined' ? this : window, jQuery);