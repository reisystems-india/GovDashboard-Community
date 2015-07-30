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
        throw new Error('WorkflowSection requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('WorkflowSection requires GD');
    }

    var GD = global.GD;

    var WorkflowSection = GD.Section.extend({

        routes: ['/cp/workflow'],

        name: 'workflow',
        title: 'Workflow',

        init: function ( options ) {
            this._super(options);
            this.model = new GD.WorkflowModel(this);
        },

        getDefaultRoute: function () {
            return this.routes[0];
        },

        dispatch: function ( request ) {
            this._super(request);
            var _this = this;
            if ( !this.dispatched ) {
                $.each(this.routes,function(i,route){
                    var routeMatcher = new RegExp(route.replace(/:[^\s/]+/g, '([\\w_:]+)'));
                    var match = request.match(routeMatcher);
                    if ( match ) {
                        _this.dispatched = true;
                        _this.setActive();
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
            this.layoutHeader.find('.gd-section-header-left').append('<h1>Workflow Management</h1>');

            this.view = new GD.WorkflowIndexView(this.model, this.layoutBody, {'section':this});
            this.view.render();

            var _this = this;
            $.when(this.model.getDatasources()).then(function() {
                _this.view.loadDatasources(_this.model.datasources);
            });
        }
    });

    // add to global space
    global.GD.WorkflowSection = WorkflowSection;

})(typeof window === 'undefined' ? this : window, jQuery);