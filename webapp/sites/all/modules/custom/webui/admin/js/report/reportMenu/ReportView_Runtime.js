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

/* needed for advanced table pagination and column sorting in the Report & Dashboard Builders */
(function(global,$,undefined) {
    var ReportView_Runtime = {
        getChartView: function(reportId) {
            // Report Builder
            if ( typeof ReportConfig != 'undefined' && ReportSection.getContainer().isVisible() ) {
                MessageSection.clearMessages();
                ReportPreview.getPreview(ReportForm.getDetailsForm().getValues().name, ReportConfig.getSimpleConfig());
            }
            // Dashboard Builder
            else {
                this.getReport(reportId);
            }
        },

        getTableView: function(reportId, page, params) {
            if ( typeof page == 'undefined' ) {
                page = 1;
            }

            // Report Builder
            if ( ReportSection.getContainer().isVisible() ) {
                MessageSection.clearMessages();
                var tableConfig = ReportConfig.getSimpleConfig();
                tableConfig.config.chartType = 'table';
                ReportPreview.refreshPreview(ReportForm.getDetailsForm().getValues().name, tableConfig, page);
            }
            // Dashboard Builder
            else {
                this.getReport(reportId, page, 'table', params);
            }
        },

        getReport: function(reportId, page, type, params) {

            var tableView = (type == 'table');

            var dashboardId = Dashboard.getId();

            var queryString = 'dashboardBuilder=true';
            if ( typeof type != 'undefined' ) {
                queryString += '&type='+type;
            }
            if ( typeof params != 'undefined' && params ) {
                queryString += '&'+$.param(params);
            }
            // need to rebind these links, then take action on inactive
            ReportMenu_Runtime.getChartLink(reportId);
            ReportMenu_Runtime.getTableLink(reportId);
            ReportMenu_Runtime.setActiveLink(reportId,tableView);

            jQuery.ajax({
                url: '/dashboard/'+dashboardId+'/report/'+reportId+'/?'+queryString,
                data: {},
                dataType: 'jsonp',
                success: function ( response ){
                    // if session has expired, prevent login screen from appearing within the div. redirect to top window
                    if (response.data.body.indexOf('<a href="/user/password">') >= 0) {
                        location.href = "/";
                    }
                    else {
                        jQuery("#report-"+reportId).replaceWith(response.data.body);

                        //if we replace complete header "wheel menu" stops working. So extract heading text and replace only that.
                        var regex_header = new RegExp("\<h3 class=\"gd_report_title\"\>(.*?)<\/h3>");
                        var header_match_array = regex_header.exec(response.data.header);
                        jQuery("#gd-report-header-"+reportId+" .gd_report_title").replaceWith('<h3 class="gd_report_title">'+header_match_array[1]+'</h3>');
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
            var reMatch = urlStr.match(/&t\[.*/); // todo: this grabs everything after first t param, need better regex pattern
            if (reMatch && reMatch.length) {
                return reMatch[0];
            }
            else return '';
        }
    };

    global.ReportView_Runtime = ReportView_Runtime;

})(typeof window === 'undefined' ? this : window, $);