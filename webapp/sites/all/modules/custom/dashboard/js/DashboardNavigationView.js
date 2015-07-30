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
        throw new Error('DashboardNavigationView requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DashboardNavigationView requires GD');
    }

    var GD = global.GD;

    var DashboardNavigationView = GD.View.extend({

        init: function ( object, container, options ) {
            this._super(object, container, options);
            this.initLayout();
        },

        initLayout: function () {
            if (this.layout) {
                this.layout = $([
                    '<ul class="nav pull-right">',
                    '<li class="divider-vertical"></li>',
                    '<li class="dropdown">',
                    '<a class="dropdown-toggle" data-toggle="dropdown" href="#"><strong id="gd-admin-datamart-name">Default</strong> <b class="caret"></b></a>',
                    '<ul class="dropdown-menu" style="z-index:1000000;max-height:260px;overflow-y:scroll;"></ul>',
                    '</li>',
                    '</ul>'
                ].join("\n"));

                var li = [];
                for ( var i = 0, datamart_count=this.object.length; i < datamart_count; i++ ) {
                    if ( !this.object[i].isActive() ) {
                        li.push('<li><a href="#" rel="'+this.object[i].getId()+'">'+this.object[i].getName()+'</a></li>');
                    } else {
                        $('.dropdown-toggle strong',this.layout).text(this.object[i].getName());
                        li.push('<li><a href="#"><i class="icon-ok"></i> '+this.object[i].getName()+'</a></li>');
                    }
                }

                this.list = $(li.join("\n"));

                $('.dropdown-menu',this.layout).append(this.list);
            }
        },


        render: function () {
            this.container.append(this.layout);
        }
    });

    // add to global space
    global.GD.DashboardNavigationView = DashboardNavigationView;

})(typeof window === 'undefined' ? this : window, jQuery);