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


(function(global,undefined) {

    if (global.GD == null) {
        throw new Error('Ext requires GD');
    }

    var GD = global.GD;

    (function($,Highcharts) {
        if ($ == null) {
            throw new Error('Ext requires jQuery');
        }

        var Ext = GD.App.extend({

            container: null,
            autodraw: true,
            autodrawMenus: true,
            dashboard: null,
            host: GD.options.host,
            dashboards: [],
            filters: [],
            breadcrumbs: [],
            datasource: null,

            init: function (options) {
                this._super(options);

                if (options.container != null) {
                    this.container = options.container;
                    $(options.container).addClass('gd-container'); // needed for gd-namespaced jquery ui css
                }

                if (options.autodraw != null) {
                    this.autodraw = options.autodraw;
                }

                if (options.autodrawMenus != null) {
                    this.autodrawMenus = options.autodrawMenus;
                } else {
                    this.options.autodrawMenus = this.autodrawMenus; // needs to be injected
                }

                if (options.datasource != null) {
                    this.datasource = options.datasource;
                }

                var uriHandler = new GD.Util.UriHandler();
                var uriParams = uriHandler.parseURI(location.href);
                var uriDashboardId = uriParams.queryObject.id;

                if (options.dashboard != null && uriDashboardId == null) {
                    this.dashboardId = options.dashboard;
                } else if (uriDashboardId != null) {
                    this.dashboardId = uriDashboardId;
                    this.options.dashboardId = uriDashboardId;
                }

                if (options.host != null) {
                    this.host = options.host;
                } else {
                    this.options.host = this.host; // needs to be injected
                }

                if (options.requestParams != null) {
                    this.options.requestParams = options.requestParams;
                }

                var css = this.host + '/gd/ext/css';
                if (options.theme != null && GD.options.themePath != null) {
                    css += '?theme=true';
                }

                GD.Util.addCSS(css);

                // sets the external url that embedded this app
                this.uri = new GD.Util.UriHandler();

                var data = {};
                if (this.options.requestParams)  {
                    $.each(this.options.requestParams,function(key, value){
                        data[key] = value;
                    });
                }
                $.ajaxSetup({
                    'data': data
                });
            },

            run: function () {
                var _this = this;

                if (_this.dashboardId) {
                    _this.getDashboard(_this.dashboardId);
                } else {

                    var uri = new GD.Util.UriHandler(_this.host+'/gd/ext');
                    uri.addParam('origin', _this.uri.getOrigin());
                    if (this.datasource) {
                        uri.addParam('datasource', _this.datasource);
                    }

                    $.ajax({
                        url: uri.getURI(),
                        dataType: 'jsonp',
                        success: function (response) {
                            $.each(response.data, function(index, value){
                                _this.dashboards.push({
                                    id: value.id,
                                    title: value.title
                                });
                            });
                            if ( _this.dashboards.length ) {
                                _this.dashboardId = _this.dashboards[0].id;
                                _this.options.dashboardId = _this.dashboards[0].id;
                                _this.getDashboard(_this.dashboards[0].id);
                            }
                        }
                    });
                }
            },

            getDashboard: function (id) {
                var _this = this;

                if (!id) {
                    id = _this.dashboardId;
                }

                var uri = new GD.Util.UriHandler(_this.host+'/gd/ext/dashboard/'+id);
                uri.addParam('origin', _this.uri.getOrigin()).mergeQueryString(_this.uri.getQueryString());

                if (this.options.requestParams)  {
                    $.each(this.options.requestParams,function(key, value){
                        uri.addParam(key, value);
                    });
                }

                $.ajax({
                    url: uri.getURI(),
                    dataType: 'jsonp'
                }).done(function (data, textStatus, jqXHR) {
                    _this.handleGetDashboardResponse(data);
                }).fail(function (jqXHR, textStatus, errorThrown) {
                    _this.handleGetDashboardError(jqXHR, textStatus, errorThrown);
                });
            },

            setDashboard: function (dashboard) {
                this.dashboard = dashboard;
            },

            handleGetDashboardResponse: function (response) {
                if (response.status.code == "200") {
                    this.setDashboard(new GD.Dashboard(response.data, this.options));
                    this.dashboardId = this.dashboard.id;
                    this.setFilters(response.data.filters);
                    this.setBreadcrumbs(response.data.breadcrumbs);

                    if (this.autodraw) {
                        this.render();
                    }

                    if (this.options.callback) {
                        this.options.callback(this, response);
                    }
                }
            },

            handleGetDashboardError: function (jqXHR, textStatus, errorThrown) {
                //  TODO Handle error in generic lib
            },

            setFilters: function (filters){
                var _this = this;
                if ( !filters ) {
                    return;
                }
                $.each(filters,function(index, object){
                    if (object.exposed) {
                        var info = _this.uri.getFilterInfo(object.name, _this.options.dashboard);
                        if (info) {
                            if (info.ddf != 1) {
                                object.operator = info.operator;
                                object.value = info.value;
                                _this.filters.push(new GD.Filter(object, _this.options));
                            }
                        } else {
                            _this.filters.push(new GD.Filter(object, _this.options));
                        }
                    }
                });
            },

            setBreadcrumbs: function (breadcrumbs) {
                var _this = this;

                $.each(breadcrumbs,function(index,object){
                    _this.breadcrumbs[index] = new GD.Breadcrumb(object, this.options);
                });
            },

            render: function () {
                this.renderHeader();
                this.renderBreadcrumb();
                this.renderFilter();
                this.renderDashboard();
            },

            renderHeader: function() {
                var container = $('<div class="row"></div>');
                var btnContainer = $('<div class="pull-right"></div>');
                if (this.dashboard['export']) {
                    var exportButton = $('<button role="button" type="button" id="exportButton" tabindex="100" class="btn btn-default btn-gd-dashboard-export">Export</button>').attr('data-dashboard', this.dashboard['id']);
                    exportButton.click(function(){
                        var id = $(this).data('dashboard');
                        var currentUri = new GD.Util.UriHandler();
                        currentUri.removeParam('id');
                        var downloadUri = new GD.Util.UriHandler('/dashboard/'+id+'/export'+currentUri.getQueryString());
                        downloadUri.redirect();
                    });
                    btnContainer.append(exportButton);
                }

                if (this.dashboard['print']) {
                    var prnt = $('<button role="button" type="button" style="margin-left:3px;" id="printButton" tabindex="100" class="btn btn-default btn-gd-dashboard-print" href="javascript:void(0)">Print</button>');
                    prnt.click(function() {
                        window.GD_PrintFeatures.beforePrintProcessing.call(window.GD_PrintFeatures);
                    });
                    btnContainer.append(prnt);
                }
                container.append($('<div class="col-md-12"></div>').append(btnContainer));
                $(this.options.container).append(container);
            },

            renderBreadcrumb: function () {
                GD.BreadcrumbFactory.renderBreadcrumbs(this.breadcrumbs, this.options.container, this.options);
            },

            renderFilter: function () {
                GD.FilterViewFactory.renderFilters(this.filters, this.options.container, {dashboard: this.dashboardId});
            },

            renderDashboard: function () {
                new GD.DashboardView(this.dashboard, this.options.container).render();
            },

            refresh: function () {
                this.getDashboard();
            },

            lookupFilterValues: function (request, callback) {
                var _this = this;
                var data = {
                    filter: request.name,
                    query: request.term,
                    limit: 10
                };
                var uri = new GD.Util.UriHandler(_this.options.host+'/gd/ext/dashboard/'+_this.options.dashboard+'/filter/data');

                if (this.options.requestParams)  {
                    $.each(this.options.requestParams, function(key, value){
                        uri.addParam(key, value);
                    });
                }

                if (request['filters']) {
                    data['appliedFilters'] = [];
                    $.each(request['filters'], function(i, f) {
                        if (f['operator']) {
                            data['appliedFilters'].push({value: f['value'], name: f['name'], operator: f['operator']});
                        }
                    });
                }

                $.ajax({
                    url: uri.getURI(),
                    dataType: "jsonp",
                    data: data,
                    success: function(ajaxResponse) {
                        if ( typeof(callback) == "function" ) {
                            callback(ajaxResponse.data, function (item) {
                                return {
                                    label: item,
                                    value: item
                                };
                            });
                        }
                    }
                });
            },

            applyFilter: function (filter, options) {

                if (!options.operator || (!options.value && options.operator.type != 'data')) {
                    return;
                }

                filter.operator = options.operator.getId();
                filter.setValue(options.value);

                var uri = new GD.Util.UriHandler();
                uri.addFilter(filter, this.options.dashboard);
                uri.redirect();
            },

            applyMultipleFilters: function (filterOptions) {
                var uri = new GD.Util.UriHandler();
                var _this = this;
                $.each(filterOptions, function (i, filterOption) {
                    var filter = filterOption.filter;
                    var options = filterOption.options;
                    filter.operator = options.operator.getId();
                    filter.setValue(options.value);
                    uri.addFilter(filter, _this.options.dashboard);
                });
                uri.redirect();
            },

            clearFilter: function (name) {
                var uri = new GD.Util.UriHandler();
                if ($.isArray(name)) {
                    var _this = this;
                    $.each(name, function (i, v) {
                        uri.removeFilter(v, _this.options.dashboard);
                    });
                } else {
                    uri.removeFilter(name, this.options.dashboard);
                }
                uri.redirect();
            }
        });

        // add to global space
        global.GD.Ext = Ext;
    })(typeof global.GD_jQuery != 'undefined' ? global.GD_jQuery : jQuery, typeof global.GD_Highcharts != 'undefined' ? global.GD_Highcharts : (typeof Highcharts != 'undefined' ? Highcharts : undefined));
})(typeof window === 'undefined' ? this : window);
