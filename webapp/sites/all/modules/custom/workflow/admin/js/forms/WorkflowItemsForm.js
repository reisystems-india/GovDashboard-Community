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
        throw new Error('WorkflowItemsForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('WorkflowItemsForm requires GD');
    }

    var GD = global.GD;

    global.GD.WorkflowItemsForm = GD.View.extend({
        init: function(object, container, options) {
            this._super(object, container, options);
            this.initVariables();
        },

        initVariables: function() {
            this.fromItemContainer = null;
            this.fromDatasources = null;
            this.fromItemList = null;
            this.items = null;
            this.fromContainer = null;
            this.itemsSelected = {};
        },

        getFromItemContainer: function() {
            if (!this.fromItemContainer) {
                this.fromItemContainer = $('<div></div>');
                this.fromItemContainer.css('margin-top', '10px');
            }

            return this.fromItemContainer;
        },

        getFromDatasources: function() {
            if (!this.fromDatasources) {
                this.fromDatasources = $('<select class="form-control"></select>');
            }

            return this.fromDatasources;
        },

        getFromItemList: function() {
            if (!this.fromItemList) {
                this.fromItemList = new GD.TreeView(null, null, {'search': true, 'checkbox': true, 'icons': false});
            }

            return this.fromItemList;
        },

        loadedItemList: function(reports, dashboards) {
            this.reports = reports;
            this.dashboards = dashboards;
            this.items = {};
            var r = [];
            var s = [];
            var ds = this.getFromDatasources().val();
            for(var i = 0; i < reports.length; i++) {
                this.items[reports[i]['id']] = {type: "report", object: reports[i]};
                r.push({text: reports[i]['title'], val: reports[i]['id']});
                if (this.itemsSelected[ds] && $.inArray(reports[i]['id'] + "", this.itemsSelected[ds]) !== -1) {
                    s.push({parents: ["Reports"], id: reports[i]['id']});
                }
            }

            var d = [];
            for (i = 0; i < dashboards.length; i++) {
                this.items[dashboards[i]['id']] = {type: "dashboard", object: dashboards[i]};
                d.push({text: dashboards[i]['title'], val: dashboards[i]['id']});
                if (this.itemsSelected[ds] && $.inArray(dashboards[i]['id'] + "", this.itemsSelected[ds]) !== -1) {
                    s.push({parents: ["Dashboards"], id: dashboards[i]['id']});
                }
            }

            var items = [{text: "Reports", children: r, "type": "disabled", val: "Reports"}, {text: "Dashboards", children: d, "type": "disabled", val: "Dashboards"}];
            this.getFromItemList().setItems(items);
            this.getFromItemList().initTree();
            this.getFromItemContainer().empty();
            this.getFromItemContainer().append(this.getFromItemList().render());
            this.getFromItemList().setSelected(s);
            var _this = this;
            this.getFromItemList().attachEventHandlers(function(items) {
                _this.itemsChanged(items);
            });
        },

        getItemObjects: function() {
            var r = {};

            for (var key in this.itemsSelected) {
                r[key] = [];
                for (var i = 0; i < this.itemsSelected[key].length; i++) {
                    r[key].push(this.items[this.itemsSelected[key][i]]);
                }
            }

            return r;
        },

        getItems: function() {
            return this.itemsSelected;
        },

        itemsChanged: function(items) {
            var ds = this.getFromDatasources().val();
            if (!this.itemsSelected[ds]) {
                this.itemsSelected[ds] = [];
            }

            var a = [];
            for (var j = 0; j < items.length; j++) {
                if ($.inArray(items[j], this.itemsSelected[ds]) === -1) {
                    this.itemsSelected[ds].push(items[j]);
                    a.push(this.items[items[j]]);
                }
            }

            var r = [];
            for (j = 0; j < this.itemsSelected[ds].length; j++) {
                if ($.inArray(this.itemsSelected[ds][j], items) === -1) {
                    r.push(this.items[this.itemsSelected[ds][j]]);
                    this.itemsSelected[ds].splice(j, 1);
                    if (!this.itemsSelected[ds].length) {
                        delete this.itemsSelected[ds];
                    }
                }
            }

            $(document).trigger({
                type: 'changed.workflow.from',
                added: a,
                removed: r,
                datasource: { name: ds, publicName: this.datasources[ds] }
            });
        },

        getReportList: function() {
            return this.reports;
        },

        getDashboardList: function() {
            return this.dashboards;
        },

        fromDatasourceChanged: function(datasource) {
            if (this.options) {
                var msg = this.options['messaging'];
                if (msg) {
                    msg.clean();
                }
            }
            this.getFromItemContainer().empty();
            var loader = $('<div class="loader" data-initialize="loader" style="margin-left:auto;margin-right:auto;"></div>');
            this.getFromItemContainer().append(loader);
            loader.loader();

            var reports = null;
            var dashboards = null;

            var _this = this;
            GD.DatasourceFactory.getExport(datasource,
                function(data) {
                    reports = data['reports'];
                    dashboards = data['dashboards'];
                    _this.loadedItemList(reports, dashboards);
                },
                function(obj, type, error) {
                    if (_this.options) {
                        var msg = _this.options['messaging'];
                        if (msg) {
                            msg.addErrors(error);
                            msg.displayMessages();
                        }
                    }
                    _this.getFromItemList().setItems([]);
                    _this.getFromItemList().initTree();
                    _this.getFromItemContainer().empty();
                    _this.getFromItemContainer().append(_this.getFromItemList().render());
                }
            );
        },

        getFromContainer: function() {
            if (!this.fromContainer) {
                this.fromContainer = $('<div></div>');
                var loader = $('<div class="loader" data-initialize="loader" style="margin-left:auto;margin-right:auto;"></div>');
                this.fromContainer.append(loader);
                loader.loader();
            }

            return this.fromContainer;
        },

        loadDatasources: function (datasources) {
            this.datasources = {};
            for(var i = 0; i < datasources.length; i++) {
                var o = $('<option></option>');
                o.attr('value', datasources[i]['name']);
                o.text(datasources[i]['publicName']);
                this.getFromDatasources().append(o);
                this.datasources[datasources[i]['name']] = datasources[i]['publicName'];
            }

            this.getFromContainer().empty();
            this.getFromContainer().append(this.getFromDatasources(), this.getFromItemContainer());

            var _this = this;
            this.getFromDatasources().val(null);
            this.getFromDatasources().change(function() {
                _this.fromDatasourceChanged($(this).val());
            });
        },

        render: function() {
            if (this.container) {
                this.container.append(this.getFromContainer());
            }

            this.attachEventHandler();

            return this.getFromContainer();
        },

        attachEventHandler: function() {
            var _this = this;
            $(document).on('remove.workflow.from', function(e) {
                var ds = e['datasource'];
                var i = $.inArray(e['id']+"", _this.itemsSelected[ds['name']]);
                if (i !== -1) {
                    _this.itemsSelected[ds['name']].splice(i, 1);
                    if (ds['name'] == _this.getFromDatasources().val()) {
                        _this.getFromItemList().deselectNode(e['id']);
                    }
                }
            });
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);