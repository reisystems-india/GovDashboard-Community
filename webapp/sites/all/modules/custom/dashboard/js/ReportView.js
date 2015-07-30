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

/* Needed for dashboard view (/dashboards).
    TODO: replace this functionality with gd library
* */
(function(global,$,undefined) {
    var ReportView = {
        getChartView: function(reportId) {
            this.getReport(reportId);
        },

        getTableView: function(reportId, page) {
            if ( typeof page == 'undefined' ) {
                page = 1;
            }
            this.getReport(reportId, page, true);
        },

        getReport: function(reportId, page, tableView) {
            var reportSelector = "#report-"+reportId;
            var reportFooterSelector = "#gd-report-footer-"+reportId;
            if ( typeof page == 'undefined' ) {
                page = 1;
            }

            if ( typeof tableView == 'undefined' ) {
                tableView = false;
            }

            var dashboardId = $('#dash_viewer').attr('dashboard_id');

            var queryString = 'page='+page;
            queryString += '&'+this.getDashboardFilterQueryString();
            if (tableView) {
                queryString += '&type=table';
                $('#gd-report-top-menu-root-'+reportId).attr('charttype','table'); // required for inactiveLink method to work properly.
            }
            // need to rebind these links, then take action on inactive
            ReportMenu.getChartLink(reportId);
            ReportMenu.getTableLink(reportId);
            ReportMenu.setActiveLink(reportId,tableView);

            var ajaxUrl = '/dashboard/'+dashboardId+'/report/'+reportId+'/?'+queryString;
            var dataType = 'jsonp';
            if ( /\/public\//i.test(document.URL) ) {
                ajaxUrl = '/public' + ajaxUrl;
                dataType = 'json';
            }

            $.ajax({
                url: ajaxUrl,
                data: null,
                dataType: dataType,
                success: function ( response ){
                    // if session has expired, prevent login screen from appearing within the div. redirect to top window
                    if (response.data.body.indexOf('<a href="/user/password">') >= 0) {
                        location.href = "/";
                    }
                    else {
                        // Re-including the highcharts library causes other highcharts in the dashboard to re-render,
                        // and they will only partially render, so strip it out
                        var regex = new RegExp('<script src="http://.*/sites/all/libraries/highcharts/js/highcharts.js"></script>', 'g');
                        jQuery(reportSelector).replaceWith(response.data.body.replace(regex, ""));
                        jQuery(reportFooterSelector).replaceWith(response.data.footer.replace(regex, ""));
                        if (tableView) {
                            jQuery(reportSelector).removeClass('report-view');
                            jQuery(reportSelector).addClass('table-view');
                        } else {
                            jQuery(reportSelector).addClass('report-view');
                            jQuery(reportSelector).removeClass('table-view');
                        }
                    }
                },
                complete: function(jqXHR, textStatus) {
                    if (textStatus == "parsererror") {
                        location.href = "/";
                    }
                }
            });
        },

        getDashboardFilterQueryString: function() {
            var urlStr = window.location.href;
            var reMatch = urlStr.match(/[?|&]t\[.*/); // todo: this grabs everything after first t param, need better regex pattern
            if (reMatch && reMatch.length) {
                return reMatch[0].replace('?', '');
            }
            else return '';
        }
    };

    global.ReportView = ReportView;

})(typeof window === 'undefined' ? this : window, jQuery);