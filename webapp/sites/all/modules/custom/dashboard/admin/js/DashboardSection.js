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
        throw new Error('Ext requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('Ext requires GD');
    }

    var GD = global.GD;

    var DashboardSection = GD.Section.extend({

        routes: ['/cp/dashboard'],

        name: 'dashboard',
        title: 'Dashboards',

        init: function ( options ) {
            this._super(options);
        },

        getDefaultRoute: function () {
            return this.routes[0];
        },

        dispatch: function ( request ) {
            this._super(request);
            var _this = this;
            if ( !this.dispatched ) {
                $.each(this.routes,function(i,route){
                    var routeMatcher = new RegExp(route.replace(/:[^\s/]+/g, '([\\w-]+)'));
                    var match = request.match(routeMatcher);
                    if ( match ) {
                        _this.setActive();
                        _this.dispatched = true;
                        if ( request == _this.routes[0] ) {
                            _this.renderIndex();
                        }
                        return false;
                    }
                });
            }
        },

        renderIndex: function () {
            this.messaging = new GD.MessagingView('#gd-admin-messages');
            this.layoutHeader.find('.gd-section-header-left').append('<h1>Dashboard Management</h1>');

            var View = new GD.DashboardListView(null, this.layoutBody, { 'section':this });
            View.render();

            var _this = this;
            GD.DashboardFactory.getDashboardList(GovdashAdmin.getActiveDatasourceName(), function ( data ) {
                View.loadData(data);
            }, function(jqXHR, textStatus, errorThrown) {
                _this.messaging.addErrors(jqXHR.responseText);
                _this.messaging.displayMessages();
                $("html, body").animate({ scrollTop: 0 }, "slow");
            });
        }
    });

    // add to global space
    global.GD.DashboardSection = DashboardSection;

})(typeof window === 'undefined' ? this : window, jQuery);