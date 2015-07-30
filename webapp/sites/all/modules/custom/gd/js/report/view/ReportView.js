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
        throw new Error('ReportView requires GD');
    }

    var GD = global.GD;

    (function($,Highcharts) {

        if ( typeof $ === 'undefined' ) {
            throw new Error('ReportView Requires jQuery');
        }

        var ReportView = GD.View.extend({

            init: function ( object, container ) {
                this._super(object, container);
                this.contentDefaultWrapper = $('<div></div>');
                this.contentTableWrapper = $('<div></div>');
            },

            render: function() {

                var GD_BUILDER_BORDER_ADJUSTMENT = 4; // todo: need to pull this constant from report obj

                $(this.container)
                    .css('width',this.object.size.width-GD_BUILDER_BORDER_ADJUSTMENT)
                    .css('height',this.object.size.height-GD_BUILDER_BORDER_ADJUSTMENT)
                    .css('top',this.object.position.top)
                    .css('left',this.object.position.left)
                    .css('position','absolute');


                $(this.container).append(this.object.header);
                $(this.container).append(this.contentDefaultWrapper);
                $(this.container).append(this.contentTableWrapper);
                $(this.container).append(this.object.footer);

                if ( this.object.display == 'table' ) {
                    // default view
                    new GD.ReportContentViewTable(this.object, this.contentDefaultWrapper).render();
                    GD['ExecuteTable' + this.object.id]();
                } else {
                    // default view
                    new GD.ReportContentViewDefault(this.object, this.contentDefaultWrapper).render();

                    // table view
                    new GD.ReportContentViewTable(this.object, this.contentTableWrapper).render();
                }

                this.showDefault();

            },

            showDefault: function() {
                this.contentDefaultWrapper.show();
                this.contentTableWrapper.hide();
            },

            showTable: function() {
                this.contentDefaultWrapper.hide();
                this.contentTableWrapper.show();
            }
        });

        global.GD.ReportView = ReportView;
    })(typeof global.GD_jQuery != 'undefined' ? global.GD_jQuery : jQuery, typeof global.GD_Highcharts != 'undefined' ? global.GD_Highcharts : (typeof Highcharts != 'undefined' ? Highcharts : undefined));
})(typeof window === 'undefined' ? this : window);
