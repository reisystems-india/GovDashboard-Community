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

    var DatasetSection = GD.Section.extend({

        routes: ['/cp/dataset/new','/cp/dataset/:id','/cp/dataset'],

        widgets: [],

        name: 'dataset',
        title: 'Datasets',

        init: function ( options ) {
            this._super(options);
        },

        getDefaultRoute: function () {
            return this.routes[2];
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
                        if ( request == _this.routes[2] ) {
                            _this.renderIndex();
                        } else if ( request == _this.routes[0] ) {
                            _this.renderNew();
                        } else {
                            _this.renderEdit(match[1]);
                        }
                        return false;
                    }
                });
            }
        },

        renderIndex: function () {
            this.messaging = new GD.MessagingView('#gd-admin-messages');
            this.layoutHeader.find('.gd-section-header-left').append('<h1>Dataset Management</h1>');

            var View = new GD.DatasetListView(null, this.layoutBody, {'section':this});
            View.render();

            var _this = this;
            GD.DatasetFactory.getDatasetList(GovdashAdmin.getActiveDatasourceName(), function ( data ) {
                View.loadData(data);
            }, function(jqXHR, textStatus, errorThrown) {
                _this.messaging.addErrors(jqXHR.responseText);
                _this.messaging.displayMessages();
            });
        },

        renderNew: function () {
            this.layoutHeader.find('.gd-section-header-left').append('<h1>Dataset Management <small>Create dataset from?</small></h1>');
            var View = new GD.DatasetWidgetView(this.widgets, this.layoutBody, {'section':this});
            View.render();
            $.event.trigger({
                'type': 'createView'
            });
        },

        renderEdit: function ( datasetName ) {
            var widget = null;
            var _this = this;
            if ( datasetName != null ) {
                GD.DatasetFactory.getDataset(datasetName, function (response) {
                    var dataset = new GD.Dataset(response);
                    $.each(_this.widgets,function(i,w) {
                        //  TODO Pull from dataset
                        if ( w.name == 'file' ) {
                            widget = w;
                            widget.Section = _this;
                        }
                    });
                    if ( widget != null ) {
                        widget.loadEditViews(dataset, response);
                    }
                },
                function(jqXHR, textStatus, errorThrown) {
                    _this.messaging = new GD.MessagingView('#gd-admin-messages');
                    _this.messaging.addErrors(jqXHR.responseText);
                    _this.messaging.displayMessages();
                    $("html, body").animate({ scrollTop: 0 }, "slow");
                });
            }
        }
    });

    // add to global space
    global.GD.DatasetSection = DatasetSection;

})(typeof window === 'undefined' ? this : window, jQuery);