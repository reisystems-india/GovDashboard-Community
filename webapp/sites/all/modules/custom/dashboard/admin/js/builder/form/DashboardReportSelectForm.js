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
        throw new Error('DashboardReportsButton requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DashboardReportsButton requires GD');
    }

    var GD = global.GD;

    global.GD.DashboardReportSelectForm = GD.View.extend({
        selectViewHeader: null,
        selectReportListView: null,
        selectedReportsView: null,
        selectActionView: null,
        listView: null,
        reportList: null,
        dashboard: null,

        init: function(object, container, options) {
            this._super(object, container, options);
            this.initVariables();
        },

        initVariables: function() {
            this.selectViewHeader = null;
            this.selectReportListView = null;
            this.selectedReportsView = null;
            this.selectedReports = null;
            this.selectActionView = null;
            this.listView = null;
            this.reportList = null;
            this.applyButton = null;
            this.cancelButton = null;
            this.formContainer = null;

            if (this.object) {
                this.dashboard = this.object;
            } else {
                this.dashboard = GD.Dashboard.singleton;
            }
        },

        getListView: function() {
            if (!this.listView) {
                var _this = this;
                var listOptions = {'search': true, 'select': true, 'callback': function(elem) { if($(elem).is(':checked')) _this.addReport(elem); else _this.removeReport(elem); }};
                this.listView = new GD.ListView(null, this.getSelectReportListView(), listOptions);
            }

            return this.listView;
        },

        getSelectViewHeader: function() {
            if (!this.selectViewHeader) {
                this.selectViewHeader = $('<div class=""><strong>Select Reports:</strong></div>');
            }

            return this.selectViewHeader;
        },

        getSelectReportListView: function() {
            if (!this.selectReportListView) {
                this.selectReportListView = $('<div class=""></div>');
                this.getListView().setHeight(200);
                this.setReportListOptions();
                this.getListView().render();
            }

            return this.selectReportListView;
        },

        setReportListOptions: function() {
            if (!this.reportList) {
                var _this = this;
                this.getReportList(function() {
                    _this.setReportListOptions();
                });
            } else {
                this.getListView().renderOptions(this.reportList);
                this.getListView().setOptions(this.dashboard.getReportIds());
            }
        },

        getReportList: function(callback) {
            if (!this.reportList) {
                var _this = this;
                $.ajax({
                    url: '/api/report.json',
                    data: {
                        filter: {
                            datasource: this.getDatasource()
                        }
                    },
                    success: function(data) {
                        _this.parseReportList(data);
                        callback();
                    }
                });
            } else {
                callback();
            }
        },

        parseReportList: function(data) {
            this.reportList = {};
            for (var d in data) {
                this.parseReport(data[d]);
            }
        },

        parseReport: function(d) {
            this.reportList[d['id']] = {'val': d['id'], 'text': d['title']};
        },

        getDatasource: function() {
            return (this.options ? this.options['datasource'] : null);
        },

        removeReport: function(report) {
            var s = '#reportListItem' + $(report).val();
            if ($(s).get(0) === $('#dashboardReportList :last-child').get(0)) {
                var prev = $('#reportListItem' + $(report).val()).prev();
                prev.text(prev.text().substring(0, prev.text().length - 2));
            }
            $(s).remove();
        },

        addReport: function(report) {
            if ($('#dashboardReportList').children().length) {
                $('#dashboardReportList :last-child').append(', ');
            }
            this.selectedReports.append('<div class="dsb-rpt-lst-item" id="reportListItem' + $(report).val() + '">' + $(report).attr('name') + '</div>');
        },

        getSelectedReportView: function() {
            if (!this.selectedReportsView) {
                this.selectedReportsView = $('<div></div>');
                this.selectedReportsView.append('<strong>Reports Selected:</strong>');
                this.selectedReports = $('<div id="dashboardReportList"></div>');

                if (this.dashboard) {
                    this.setSelectedText();
                }

                this.selectedReportsView.append(this.selectedReports);
            }

            return this.selectedReportsView;
        },

        setSelectedText: function() {
            this.selectedReports.empty();
            var reports = this.dashboard.getReports();
            for(var k in reports) {
                this.selectedReports.append('<div class="dsb-rpt-lst-item" id="reportListItem' + reports[k]['id'] + '">' + reports[k]['title'] + ', </div>');
            }
            var last = this.selectedReports.children().last();
            last.text(last.text().substring(0, last.text().length - 2));
        },

        getSelectActionView: function() {
            if (!this.selectActionView) {
                this.selectActionView = $('<div id="dashboardReportsActions" class="rpt-flt-act-cntr pull-right"></div>');
                this.selectActionView.append(this.getApplyButton());
                this.selectActionView.append(this.getCancelButton());
                
            }

            return this.selectActionView;
        },

        getApplyButton: function() {
            if (!this.applyButton) {
                this.applyButton = $('<button class="rpt-flt-act-btn btn btn-primary btn-sm">Apply</button>');
            }

            return this.applyButton;
        },

        getCancelButton: function() {
            if (!this.cancelButton) {
                this.cancelButton = $('<button class="rpt-flt-act-btn btn btn-default btn-sm">Cancel</button>');
            }

            return this.cancelButton;
        },

        getFormContainer: function() {
            if (!this.formContainer) {
                this.formContainer = $('<div></div>');
                this.formContainer.append(this.getSelectViewHeader());
                this.formContainer.append(this.getSelectReportListView());
                this.formContainer.append(this.getSelectedReportView());
                this.formContainer.append(this.getSelectActionView());
            }

            return this.formContainer;
        },

        resetList: function() {
            if (this.listView) {
                this.listView.setOptions(this.dashboard.getReportIds());
                this.setSelectedText();
            }
        },

        render: function(refresh) {
            if (refresh) {
                this.initVariables();
            }

            if (this.container) {
                this.container.append(this.getFormContainer());
            }

            return this.getFormContainer();
        },

        getReports: function() {
            return this.getListView().getSelected();
        },

        attachEventHandlers: function(cancel, apply) {
            this.getApplyButton().click(function() {
                if (apply) {
                    apply();
                }
            });

            this.getCancelButton().click(function() {
                if (cancel) {
                    cancel();
                }
            })
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);