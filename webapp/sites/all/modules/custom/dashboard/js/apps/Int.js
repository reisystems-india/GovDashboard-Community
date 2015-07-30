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
        throw new Error('Int requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('Int requires GD');
    }

    var GD = global.GD;

    var Int = GD.App.extend({
        filters: null,
        breadcrumbs: null,
        dashboard: null,
        filterContainer: null,
        breadcrumbContainer: null,
        host: null,

        init: function ( options ) {
            this._super(options);

            if (options.csrf) {
                GD.options.csrf = options.csrf;
            }

            if (options.public) {
                this.public = options.public;
            }

            if ( typeof options.filters != "undefined" ) {
                this.filters = options.filters;
            }

            if ( typeof options.breadcrumbs != "undefined" ) {
                this.breadcrumbs = options.breadcrumbs;
            }

            if ( typeof options.dashboard != "undefined" ) {
                this.dashboard = options.dashboard;
            }

            if ( typeof options.filterContainer != "undefined" ) {
                this.filterContainer = options.filterContainer;
            }

            if ( typeof options.breadcrumbContainer != "undefined" ) {
                this.breadcrumbContainer = options.breadcrumbContainer;
            }

            if ( typeof options.host != "undefined" ) {
                this.host = options.host;
            }

            var css = [
                this.host+"/sites/all/libraries/jquery-ui/css/ui-lightness/jquery-ui.css",
                this.host+"/sites/all/libraries/jquery-ui/css/ui-lightness/jquery.ui.selectmenu.css",
                this.host+"/sites/all/modules/custom/webui/external/css/global.css"
            ];

            if ( typeof options.css != 'undefined' ) {
                $.each(options.css,function(index,value){
                    css.push(value);
                });
            }

            for (var i=0, cssCount=css.length; i<cssCount; i++) {
                GD.Util.addCSS(css[i]);
            }
        },

        run: function () {
            this.renderBreadcrumbs();
            this.renderFilters();

            jQuery(document).ready(function() {
                $('div.dashboard-report-container').on('focusin', function(){
                    GD.FilterViewFactory.closeViews();
                });
            });
        },

        renderBreadcrumbs: function () {
            var breadcrumbs = [];
            $.each(this.breadcrumbs, function ( index, breadcrumb ) {
                breadcrumbs.push(new GD.Breadcrumb(breadcrumb));
            });

            this.breadcrumbs = breadcrumbs;
            GD.BreadcrumbFactory.renderBreadcrumbs(this.breadcrumbs, this.breadcrumbContainer, this.options);
        },

        renderFilters: function () {
            var filters = [];
            var _this = this;
            $.each(this.filters, function (index, filter) {
                var f = new GD.Filter(filter, _this.options);
                if (filter['ddf']) {
                    f.setVisible(false);
                }
                filters.push(f);
            });

            this.filters = filters;
            GD.FilterViewFactory.renderFilters(this.filters, this.filterContainer, {dashboard: this.dashboard, public: this.public});
        }
    });

    global.GD.Int = Int;

})(typeof window === 'undefined' ? this : window, jQuery);

