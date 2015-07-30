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
        throw new Error('WorkflowDatasourcesForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('WorkflowDatasourcesForm requires GD');
    }

    var GD = global.GD;

    global.GD.WorkflowDatasourcesForm = GD.View.extend({
        formContainer: null,
        datasourceList: null,

        init: function(object, container, options) {
            this._super(object, container, options);
            this.initVariables();
        },

        initVariables: function() {
            this.formContainer = null;
            this.datasourceList = null;
            this.items = null;
            this.destinations = [];
        },

        loadDatasources: function (datasources) {
            this.items = {};
            var items = [];
            for(var i = 0; i < datasources.length; i++) {
                this.items[datasources[i]['name']] = datasources[i]['publicName'];
                items.push({val: datasources[i]['name'], text: datasources[i]['publicName']});
            }
            this.getDatasourcesList().setItems(items);
            this.getDatasourcesList().initTree();
            this.getFormContainer().empty();
            this.getFormContainer().append(this.getDatasourcesList().render());

            var _this = this;
            this.getDatasourcesList().attachEventHandlers(function(items) {
                _this.itemsChanged(items);
            });
        },

        itemsChanged: function(items) {
            var a = [], r = [];

            for (var i = 0; i < items.length; i++) {
                if ($.inArray(items[i], this.destinations) === -1) {
                    this.destinations.push(items[i]);
                    a.push({ id: items[i], value: this.items[items[i]] });
                }
            }

            for (i = 0; i < this.destinations.length; i++) {
                if ($.inArray(this.destinations[i], items) === -1) {
                    r.push(this.destinations[i]);
                    this.destinations.splice(i, 1);
                }
            }

            $(document).trigger({
                type: 'changed.workflow.to',
                added: a,
                removed: r
            });
        },

        getDatasourcesList: function() {
            if (!this.datasourceList) {
                this.datasourceList = new GD.TreeView(null, null, {'search': true, 'checkbox': true, 'icons': false});
            }

            return this.datasourceList;
        },

        getFormContainer: function() {
            if (!this.formContainer) {
                this.formContainer = $('<div style="width: 300px;"></div>');
                var loader = $('<div class="loader" data-initialize="loader" style="margin-left:auto;margin-right:auto;"></div>');
                this.formContainer.append(loader);
                loader.loader();
            }

            return this.formContainer;
        },

        getDestinations: function() {
            return this.destinations;
        },

        render: function() {
            if (this.container) {
                this.container.append(this.getFormContainer());
            }

            this.attachEventHandlers();

            return this.getFormContainer();
        },

        attachEventHandlers: function() {
            var _this = this;
            $(document).on('remove.workflow.to', function(e) {
                var i = $.inArray(e['id'], _this.destinations);
                if (i !== -1) {
                    _this.destinations.splice(i, 1);
                    _this.getDatasourcesList().deselectNode(e['id']);
                }
            });
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);