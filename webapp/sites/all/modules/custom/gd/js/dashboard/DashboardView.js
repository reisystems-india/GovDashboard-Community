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
        throw new Error('DashboardView requires GD');
    }

    var GD = global.GD;

    (function($,Highcharts) {
        if ( typeof $ === 'undefined' ) {
            throw new Error('DashboardView Requires jQuery');
        }

        var DashboardView = GD.View.extend({

            init: function ( object, container ) {
                this._super(object,container);
                return this;
            },

            render: function () {
                var _this = this;

                // sets page title
                $("h1").text(this.title);

                // render dashboard
                $(this.container).append('<div id="dash_viewer" class="dashboard-view-wrapper"></div>');
                $('#dash_viewer').width(this.object.width).height(this.object.height).css('position','relative');

                // render items
                $.each(this.object.items,function(index, item){
                    if ( item.type == 'report' ) {
                        $('#dash_viewer').append('<div id="'+item.container+'" class="dashboard-report-container" tabindex="'+item.tabIndex+'"></div>');
                        item.view.render();
                        if (_this.object.options.autodrawMenus) {
                            item.menu.view.render();
                        }

                    } else {
                        var it = $(item.html);
                        it.addClass('dashboard-report-container');
                        if (item.type == 'text') {
                            it.addClass('dashboard-text');
                        } else if (item.type == 'image') {
                            it.addClass('dashboard-image');
                        }
                        $('#dash_viewer').append(it);
                    }
                });
            }
        });

        global.GD.DashboardView = DashboardView;
    })(typeof global.GD_jQuery != 'undefined' ? global.GD_jQuery : jQuery, typeof global.GD_Highcharts != 'undefined' ? global.GD_Highcharts : (typeof Highcharts != 'undefined' ? Highcharts : undefined));
})(typeof window === 'undefined' ? this : window);
