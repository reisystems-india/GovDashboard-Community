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
        throw new Error('FilterViewFactory requires GD');
    }

    (function($,Highcharts) {
        if ( typeof $ === 'undefined' ) {
            throw new Error('FilterViewFactory requires jQuery');
        }

        var GD = global.GD;

        var FilterViewFactory = {
            filters: [],
            filterViews: null,
            clearAllButton: null,
            options: null,

            clearViews: function() {

            },

            getClearAllButton: function() {
                if ( !this.clearAllButton ) {
                    this.clearAllButton = $('<button title="Clears All Filters" class="btn btn-default flt-clear-btn pull-right" style="margin-right: 30px;">Clear All</button>');

                    this.clearAllButton.on('click',function(){
                        $(this).prop("disabled",true);
                        GD.FilterViewFactory.clearAllFilters();
                    });

                }
                return this.clearAllButton;
            },

            clearAllFilters: function () {
                var uri = new GD.Util.UriHandler();
                for (var k in GD.FilterViewFactory.filters) {
                    if (GD.FilterViewFactory.filters[k].getExposed() && GD.FilterViewFactory.filters[k].isVisible()) {
                        uri.removeFilter(GD.FilterViewFactory.filters[k].getName(), GD.FilterViewFactory.options['dashboard']);
                    }
                }
                uri.redirect();
            },

            renderFilters: function ( filters, container, options ) {
                if (GD.FilterViewFactory.filterViews) {
                    GD.FilterViewFactory.clearViews();
                }

                GD.FilterFormFactory.forms['string'] = GD.ViewLookupFilterForm;
                GD.FilterFormFactory.forms['URI'] = GD.ViewLookupFilterForm;
                GD.FilterViewFactory.filters = filters;
                GD.FilterViewFactory.filterViews = {};
                var filterContainer = $('<div id="gd-filter-container" style="position: relative;"></div>');
                filterContainer.append('<div class="filter-overlay"><span data-container="body" data-toggle="popover" class="glyphicon glyphicon-filter"></span></div>');

                var d = null;
                var pub = false;
                if (options) {
                    GD.FilterViewFactory.options = options;
                    d = options['dashboard'];
                    pub = options['public'];
                }

                var overlayFilters = [];
                var uri = new GD.Util.UriHandler();
                for (var k in filters) {
                    if (filters[k].getExposed()) {
                        var a = uri.getFilterInfo(filters[k]['name'], d);
                        if (a) {
                            //  Exposed Filters
                            overlayFilters.push({name: filters[k]['name'], operator: a['operator'], value: a['value'], type: filters[k]['type']});
                            filters[k].setOperator(a['operator']);
                            if ($.isArray(a['value']) && !GD.Filter.isString(filters[k].getType())) {
                                filters[k].setValue(a['value'][0], a['value'][1]);
                            } else {
                                filters[k].setValue(a['value']);
                            }
                        } else if (filters[k]['operator']) {
                            //  Default Filters
                            overlayFilters.push({name: filters[k]['name'], operator: filters[k]['operator']['name'], value: filters[k]['value'], type: filters[k]['type']});
                        }

                        if (filters[k].isVisible()) {
                            var o = { operatorText: 'Operators', operators: {'': 'Select an Operator'}, dashboard: d, "public": pub, "filters": filters };
                            var f = GD.FilterFormFactory.getForm(filters[k], null, o);
                            filters[k]['viewId'] = k;
                            var v = new GD.FilterViewForm(filters[k], filterContainer, {filterForm: f, dashboard: d, "public": pub});
                            v.render();
                            GD.FilterViewFactory.filterViews[k] = v;
                        }
                    } else {
                        //  Hidden Filters
                        overlayFilters.push({name: filters[k]['name'], operator: filters[k]['operator']['name'], value: filters[k]['value'], type: filters[k]['type']});
                    }
                }

                if ( d && !$.isEmptyObject(GD.FilterViewFactory.filterViews) ) {
                    filterContainer.append(this.getClearAllButton());
                    GD.Filter.renderOverlay(filterContainer, overlayFilters, 'Dashboard Filters Applied: ');
                }

                if (container) {
                    $(container).append(filterContainer);
                }

                $('body').on('click', function (e) {
                    $.each(GD.FilterViewFactory.filterViews, function (i, v) {
                        //the 'is' for buttons that trigger popups
                        //the 'has' for icons within a button that triggers a popup
                        if (!v.getFilterButton().is(e.target) && $('div.popover-content').has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                            v.hide();
                        }
                    });
                });

                $('.flt-btn').on('focusin', function() {
                    GD.FilterViewFactory.closeViews();
                });

                $(document).on('shown.filter.view', function(e) {
                    GD.FilterViewFactory.closeViews(e['filter']);
                });

                return filterContainer;
            },

            closeViews: function(exception) {
                if (GD.FilterViewFactory.filterViews) {
                    for (var k in GD.FilterViewFactory.filterViews) {
                        if (exception && k == exception['viewId']) {
                            continue;
                        }

                        GD.FilterViewFactory.filterViews[k].hide();
                    }
                }
            }
        };

        global.GD.FilterViewFactory = FilterViewFactory;
    })(typeof global.GD_jQuery != 'undefined' ? global.GD_jQuery : jQuery, typeof global.GD_Highcharts != 'undefined' ? global.GD_Highcharts : (typeof Highcharts != 'undefined' ? Highcharts : undefined));
})(typeof window === 'undefined' ? this : window);
