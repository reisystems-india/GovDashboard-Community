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
        throw new Error('DashboardWidgetReport requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DashboardWidgetReport requires GD');
    }

    var GD = global.GD;

    var DashboardWidgetReport = GD.DashboardWidget.extend({

        init: function(object, options) {
            this._super(object, options);

            this.type = 'report';

            if ( !this.width || !this.height ) {
                this.width = 400;
                this.height = 300;
            }

            if ( !this.content ) {
                this.content = '';
            }

            this.addContextMenuItem($('<li role="presentation"><a role="menuitem" tabindex="-1" href="#" data-action="edit"><span class="glyphicon glyphicon-pencil"></span> Edit Report</a></li>'));
        },

        loadView: function ( callback ) {

            this.getCanvasItem().addClass('ldng');

            var data = {
                dashboard: JSON.stringify(this.getContainer().getDashboard().getConfig()),
                report: JSON.stringify({id: this.getContent()}),
                ds: this.getContainer().options.builder.admin.getActiveDatasourceName()
            };

            var _this = this;

            $.ajax({
                type: 'POST',
                url: '/dashboard/report/preview?w='+(this.getCanvasItem().width())+'&h='+(this.getCanvasItem().height()),
                data: data,
                success: function ( data, textStatus, jqXHR ){
                    _this.getCanvasItem().removeClass('ldng');
                    _this.getCanvasItem().find('.inner-content').html(data);
                    if ( callback ) {
                        callback();
                    }
                }
            });
        },

        handleContextMenuAction: function ( action ) {
            if ( action == 'edit' ) {
                location.href = '/cp/report/'+this.getContent();
            } else if ( action == 'delete' ) {
                this.getContainer().removeWidget(this.getId());
                this.getContainer().getDashboard().removeReport(this.getContent());
            }
        }
    });

    // add to global space
    GD.DashboardWidgetReport = DashboardWidgetReport;

})(typeof window === 'undefined' ? this : window, jQuery);