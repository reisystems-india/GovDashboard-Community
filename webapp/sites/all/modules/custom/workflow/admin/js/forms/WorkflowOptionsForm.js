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
        throw new Error('WorkflowOptionsForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('WorkflowOptionsForm requires GD');
    }

    var GD = global.GD;

    global.GD.WorkflowOptionsForm = GD.View.extend({
        destView: null,
        itemsView: null,
        syncButton: null,
        formContainer: null,
        itemsToMove: {},
        destinations: {},

        init: function(object, container, options) {
            this._super(object, container, options);
            this.initVariables();
        },

        initVariables: function() {
            this.destView = null;
            this.itemsView = null;
            this.syncButton = null;
            this.formContainer = null;
            this.itemsToMove = {};
            this.destinations = {};
        },

        loadDatasources: function() {
            this.getFormContainer().empty();
            this.getFormContainer().append('<h4>Items to Copy: </h4>',this.getItemsView(), '<h4>Destinations: </h4>', this.getDestinationView(), this.getSyncButton());
        },

        getDestinationView: function() {
            if (!this.destView) {
                this.destView = $('<div style="height:225px; overflow:auto;"></div>');
            }

            return this.destView;
        },

        getItemsView: function() {
            if (!this.itemsView) {
                this.itemsView = $('<div style="height:225px; overflow:auto;"></div>');
            }

            return this.itemsView;
        },

        getSyncButton: function() {
            if (!this.syncButton) {
                this.syncButton = $('<button class="btn btn-lg btn-primary" style="width: 350px;position: absolute;bottom: 0;">Sync</button>');

                this.syncButton.click(function() {
                    $(document).trigger({
                        type: 'execute.workflow.sync'
                    });
                });
            }

            return this.syncButton;
        },

        getFormContainer: function() {
            if (!this.formContainer) {
                this.formContainer = $('<div></div>');
                var loader = $('<div class="loader" data-initialize="loader" style="margin-left:auto;margin-right:auto;"></div>');
                this.formContainer.append(loader);
                loader.loader();
            }

            return this.formContainer;
        },

        render: function() {
            if (this.container) {
                this.container.append(this.getFormContainer());
            }

            this.attachEventHandlers();

            return this.getFormContainer();
        },

        getItemView: function(item, ds) {
            var _this = this;
            var c = function() {
                _this.itemsToMove[ds['name']][item['object']['id']].remove();
                delete _this.itemsToMove[ds['name']][item['object']['id']];
                $(document).trigger({
                    type: 'remove.workflow.from',
                    datasource: ds,
                    id: item['object']['id']
                });
            };
            return this.getCloseView(item['object']['title'], c);
        },

        getDestItemView: function(item) {
            var _this = this;
            var c = function() {
                _this.destinations[item['id']].remove();
                $(document).trigger({
                    type: 'remove.workflow.to',
                    id: item['id']
                });
            };
            return this.getCloseView(item['value'], c);
        },

        getCloseView: function(value, callback) {
            var container = $('<div class="workflow-sync-item"></div>');
            container.text(value);
            var close = $('<button type="button" class="close"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>');
            container.prepend(close);
            close.click(function() {
                if (callback) {
                    callback();
                }
            });
            return container;
        },

        getItemContainer: function(ds) {
            var c = $('<div></div>');
            c.attr('id', 'workflow-item-' + ds['name']);
            c.append('<span style="font-weight:bold;">' + ds['publicName'] + '</span>');
            var rl = $('<div class="workflow-report-list"></div>');
            rl.append('<span>Reports</span>');
            c.append(rl);
            var dl = $('<div class="workflow-dashboard-list"></div>');
            dl.append('<span>Dashboards</span>');
            c.append(dl);
            return c;
        },

        itemsChanged: function(added, removed, ds) {
            if (!this.itemsToMove[ds['name']]) {
                this.itemsToMove[ds['name']] = {};
                this.getItemsView().append(this.getItemContainer(ds));
            }

            for (var i = 0; i < added.length; i++) {
                var view = this.getItemView(added[i], ds);
                this.itemsToMove[ds['name']][added[i]['object']['id']] = view;
                if (added[i]['type'] == 'report') {
                    $(GD.Utility.escapeSelector('workflow-item-' + ds['name'])).find('div.workflow-report-list').append(view);
                } else {
                    $(GD.Utility.escapeSelector('workflow-item-' + ds['name'])).find('div.workflow-dashboard-list').append(view);
                }
            }

            for (i = 0; i < removed.length; i++) {
                this.itemsToMove[ds['name']][removed[i]['object']['id']].remove();
                delete this.itemsToMove[ds['name']][removed[i]['object']['id']];
            }

            var reports = $(GD.Utility.escapeSelector('workflow-item-' + ds['name']) + ' div.workflow-report-list').find('div.workflow-sync-item');
            if (!reports.length) {
                $(GD.Utility.escapeSelector('workflow-item-' + ds['name']) + ' div.workflow-report-list').hide();
            } else {
                $(GD.Utility.escapeSelector('workflow-item-' + ds['name']) + ' div.workflow-report-list').show();
            }

            var dash = $(GD.Utility.escapeSelector('workflow-item-' + ds['name']) + ' div.workflow-dashboard-list').find('div.workflow-sync-item');
            if (!dash.length) {
                $(GD.Utility.escapeSelector('workflow-item-' + ds['name']) + ' div.workflow-dashboard-list').hide();
            } else {
                $(GD.Utility.escapeSelector('workflow-item-' + ds['name']) + ' div.workflow-dashboard-list').show();
            }

            if (!reports.length && !dash.length) {
                $(GD.Utility.escapeSelector('workflow-item-' + ds['name'])).hide();
            } else {
                $(GD.Utility.escapeSelector('workflow-item-' + ds['name'])).show();
            }
        },

        destChanged: function(added, removed) {
            for (var i = 0; i < added.length; i++) {
                var view = this.getDestItemView(added[i]);
                this.destinations[added[i]['id']] = view;
                this.getDestinationView().append(view);
            }

            for (i = 0; i < removed.length; i++) {
                this.destinations[removed[i]].remove();
                delete this.destinations[removed[i]];
            }
        },

        attachEventHandlers: function() {
            var _this = this;
            $(document).on('changed.workflow.from', function(e) {
                _this.itemsChanged(e['added'], e['removed'], e['datasource']);
            });

            $(document).on('changed.workflow.to', function(e) {
                _this.destChanged(e['added'], e['removed']);
            });
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);
