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
        throw new Error('Dashboard requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('Dashboard requires GD');
    }

    var GD = global.GD;

    var Dashboard = GD.Class.extend({
        options: null,
        id: null,
        name: null,
        datasource: null,
        filters: null,
        links: null,
        widgets: null,
        width: 1080,
        height: 600,
        customView: null,
        items: null,
        reports: null,
        tags: null,
        author: null,

        init: function(object, options) {
            this.object = object;
            this.options = options;

            this.id = -1;
            this.datasource = null;
            this.public = false;
            this.width = 1080;
            this.height = 600;
            this.customView = null;
            this.filters = [];
            this.links = [];
            this.widgets = [];
            this.items = [];
            this.reports = {};
            this.tags = [];
            this.author = null;
            this.exportable = false;
            this.printable = false;
            if (object) {
                if (object['id']) {
                    this.id = object['id'];
                }

                if (object['name']) {
                    this.name = object['name'];
                }

                if (object['datasource']) {
                    this.datasource = object['datasource'];
                }

                if (object['customView']) {
                    this.customView = object['customView'];
                }

                this.public = object['public'] == "1";

                var key;
                for(key in object['reports']) {
                    this.reports[object['reports'][key]['id']] = (object['reports'][key]);
                }

                for(key in object['tags']) {
                    this.tags.push(object['tags'][key]);
                }

                if (object['config'] && (options && !options['lazy'])) {
                    this.width = object['config']['width'];
                    this.height = object['config']['height'];

                    for (key in object['config'].filters) {
                        this.filters.push(new GD.Filter(object['config'].filters[key]));
                    }

                    for (key in object['config'].drilldowns) {
                        this.links.push(new GD.Link(object['config'].drilldowns[key]));
                    }

                    for (key in object['config'].items) {
                        var widget = GD.DashboardWidgetFactory.getWidget(object['config'].items[key].type,object['config'].items[key]);
                        this.widgets.push(widget);
                    }

                    this.exportable = object['config']['exportable'];
                    this.printable = object['config']['printable'];
                }

                if (object['author']) {
                    this.author = object['author'];
                }
            }

            if (options && options['singleton']) {
                GD.Dashboard.singleton = this;
            }

            $(document).on(GD.Dashboard.getEventList(), function(e) {
                global.GovdashDashboardBuilder['dashboard'] = e['dashboard'];
            });
        },

        isNew: function() {
            return this.getId() == -1;
        },

        getId: function() {
            return this.id;
        },

        setId: function ( id ) {
            this.id = id;
        },

        validate: function() {
            var valid = true;
            var filters = this.getFilters();
            $.each(filters, function(i, filter) {
                if (!filter['column'][0]) {
                    global.GD.DashboardBuilderMessagingView.addWarnings('Unsupported filter: ' + filter['name']);
                }
            });

            var links = this.getLinks();
            $.each(links, function(i, link) {
                if (link['column']['invalid']) {
                    global.GD.DashboardBuilderMessagingView.addWarnings('Unsupported column for link: ' + link['column']['name']);
                }
            });

            global.GD.DashboardBuilderMessagingView.displayMessages();
            return valid;
        },

        getName: function () {
            return this.name;
        },

        setName: function ( name ) {
            this.name = name;
        },

        getDescription: function () {
            return this.description;
        },

        setDescription: function ( desc ) {
            this.description = desc;
        },

        getAuthor: function() {
            return this.author;
        },

        getAutoPosition: function() {
            var position = {
                left: 0,
                top: 0
            };
            var collision;
            do {
                collision = false;
                for (var i = 0; i < this.widgets.length; i++) {
                    var p = this.widgets[i]['position'] ? this.widgets[i]['position'] : {'left': this.widgets[i]['left'], top: this.widgets[i]['top']};
                    if (p['left'] == position['left']) {
                        if (p['top'] == position['top']) {
                            position['left'] += 100;
                            position['top'] += 100;
                            collision = true;
                        }
                    }
                }
            } while (collision);

            return position;
        },

        addWidget: function ( widget ) {
            this.widgets.push(widget);

            $(document).trigger({
                type: 'add.dashboard.widget',
                widget: widget,
                dashboard: this
            });
        },

        removeWidget: function ( id ) {
            var index = -1;
            for ( var key in this.widgets ) {
                if ( this.widgets[key].getId() == id ) {
                    index = key;
                    break;
                }
            }

            if (index > -1) {
                this.widgets[index].clean();
                this.widgets.splice(index,1);
            }

            $(document).trigger({
                type: 'remove.dashboard.widget',
                removed: id,
                dashboard: this
            });
        },

        getWidgets: function() {
            return this.widgets;
        },

        getWidget: function ( id ) {
            var index = -1;
            for ( var key in this.widgets ) {
                if ( this.widgets[key].getId() == id ) {
                    index = key;
                    break;
                }
            }
            if (index > -1) {
                return this.widgets[index];
            } else {
                throw Error('Widget not found');
            }
        },

        addReport: function(report) {
            this.reports[report['id']] = report;

            $(document).trigger({
                type: 'add.dashboard.report',
                added: report['id'],
                dashboard: this
            })
        },

        removeReport: function(id) {
            delete this.reports[id];

            $(document).trigger({
                type: 'remove.dashboard.report',
                removed: id,
                dashboard: this
            });
        },

        getPublic: function() {
            return !(this.public == null || !this.public);
        },

        setPublic: function(val) {
            this.public = val;
        },

        getExportable: function() {
            return !(this.exportable == null || !this.exportable);
        },
        getPrintable: function() {
            return !(this.printable == null || !this.printable);
        },
        setExportable: function(val) {
            this.exportable = val;
        },
        setPrintable: function(val) {
            this.printable = val;
        },
        getReports: function() {
            return this.reports;
        },

        getReportIds: function() {
            return this.reports ? Object.keys(this.reports) : [];
        },

        getDatasourcePublicName: function() {
            var name = null;

            if (this.datasource) {
                name = this.datasource['publicName'];
            } else if (typeof GovdashDashboardBuilder != 'undefined') {
                name = GovdashDashboardBuilder.admin.getActiveDatasource()['publicName'];
            }

            return name;
        },

        getDatasourceName: function() {
            var name = null;

            if (this.datasource) {
                name = this.datasource['name'];
            } else if (typeof GovdashDashboardBuilder != 'undefined') {
                name = GovdashDashboardBuilder.admin.getActiveDatasourceName();
            }

            return name;
        },

        getDatasource: function() {
            return this.datasource;
        },

        getLinks: function() {
            return this.links;
        },

        addLink: function(d) {
            d.setId(this.links.length);
            this.getLinks().push(d);

            $(document).trigger({
                type: 'add.dashboard.link',
                added: d,
                dashboard: this
            });
        },

        removeLink: function(d) {
            var links = this.links;
            var i = null;
            for (var key in links) {
                if (GD.Link.compareLinks(links[key], d)) {
                    i = key;
                    break;
                }
            }
            if (i) {
                this.links.splice(i, 1);
            }

            $(document).trigger({
                type: 'remove.dashboard.link',
                added: d,
                dashboard: this
            });
        },

        editLink: function(d) {
            var e = function(g) {
                g.column = d['column'];
                g.dashboard = d['dashboard'];
                g.filters = d['filters'];
                g.report = d['report'];
            };

            for (var key in this.links) {
                if (GD.Link.compareLinks(this.links[key], d)) {
                    e(this.links[key]);
                    break;
                }
            }

            $(document).trigger({
                type: 'edit.dashboard.link',
                editted: d,
                dashboard: this
            });
        },

        getFilters: function() {
            return this.filters;
        },

        addFilter: function(f) {
            this.filters.push(f);

            $(document).trigger({
                type: 'add.dashboard.filter',
                added: f,
                dashboard: this
            });
        },

        removeFilter: function(f) {
            var i = null;
            for (var key in this.filters) {
                if (GD.Filter.compareFilters(this.filters[key], f)) {
                    i = key;
                    break;
                }
            }
            if (i) {
                this.filters.splice(i, 1);
            }


            $(document).trigger({
                type: 'remove.dashboard.filter',
                removed: f,
                dashboard: this
            });
        },

        editFilter: function (f) {
            var e = function(g) {
                g.exposed = f['exposed'];
                g.exposedType = f['exposedType'];
                g.operator = f['operator'];
                g.value = f['value'];
                g.view['options'] = f['view']['options'];
            };

            for (var key in this.filters) {
                if (GD.Filter.compareFilters(this.filters[key], f)) {
                    e(this.filters[key]);
                    break;
                }
            }

            $(document).trigger({
                type: 'edit.dashboard.filter',
                editted: f,
                dashboard: this
            });
        },

        getFiltersConfig: function() {
            var f = [];

            for(var key in this.filters) {
                f.push(this.filters[key].getConfig());
            }

            return f;
        },

        getLinksConfig: function() {
            var d = [];

            var links = this.links;
            for(var key in links) {
                d.push(links[key].getConfig());
            }

            return d;
        },

        getTags: function() {
            return this.tags;
        },

        setTags: function(tags) {
            this.tags = tags;
        },

        setHeight: function ( height ) {
            this.height = height;
        },

        getHeight: function() {
            return this.height;
        },

        setWidth: function(width) {
            this.width = width;
        },

        getWidth: function() {
            return this.width;
        },

        setCustomView: function(val) {
            if (this.customView != val) {
                this.customView = val;

                $(document).trigger({
                    type: 'edit.dashboard.custom',
                    dashboard: this
                });
            }
        },

        getCustomView: function() {
            return this.customView;
        },

        getWidgetsConfig: function() {
            var w = [];
            for(var key in this.widgets) {
                w.push(this.widgets[key].getConfig());
            }
            return w;
        },

        getConfig: function() {
            return {
                id: this.getId(),
                name: this.getName(),
                description: this.getDescription(),
                datasource: this.getDatasourceName(),
                reports: this.getReports(),
                public: this.getPublic(),
                config: {
                    items: this.getWidgetsConfig(),
                    filters: this.getFiltersConfig(),
                    drilldowns: this.getLinksConfig(),
                    width: this.getWidth(),
                    height: this.getHeight(),
                    exportable : this.getExportable(),
                    printable : this.getPrintable()
                },
                customView: this.getCustomView(),
                tags: this.getTags()
            };
        }
    });

    GD.Dashboard = Dashboard;

    GD.Dashboard.getEventList = function() {
        return 'add.dashboard.link remove.dashboard.link edit.dashboard.link add.dashboard.filter remove.dashboard.filter edit.dashboard.filter edit.dashboard.custom';
    };

    GD.Dashboard.compareDashboards = function(a, b) {
        var same = true;

        //same &= (a.getCustomView() == b.getCustomView());
        same &= (a.getName() == b.getName());
        same &= (a.getDescription() == b.getDescription());
        same &= (a.getPublic() == b.getPublic());
        same &= ($('div.bldr-ntf-vw-cntr.changed').length == 0);
        same &= (a.getHeight() == b.getHeight());

        //  Same number of items
        same &= (a.getWidgets().length == b.getWidgets().length);
        same &= (a.getReportIds().length == b.getReportIds().length);
        same &= (a.getFilters().length == b.getFilters().length);
        same &= (a.getLinks().length == b.getLinks().length);

        return same;
    };

})(typeof window === 'undefined' ? this : window, jQuery);