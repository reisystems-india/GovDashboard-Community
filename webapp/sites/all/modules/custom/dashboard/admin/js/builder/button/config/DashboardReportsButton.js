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

    global.GD.DashboardReportsButton = GD.DashboardConfigButton.extend({
        listForm: null,
        selectForm: null,
        notification: null,
        notificationView: null,

        init: function(object, options) {
            this.button = $('#dashboardReports');
            this.form = $('#dashboardReportsForm');
            this._super(object, options);
        },

        initForm: function() {
            this.listForm = new GD.DashboardReportListForm(this.dashboard, this.form, {'datasource': this.getDatasource()});
            this.selectForm = new GD.DashboardReportSelectForm(this.dashboard, this.form, {'datasource': this.getDatasource()});
            this.notification = new GD.Notification(this.dashboard.getReportIds().length);
            this.notificationView = new GD.NotificationView(this.notification, $('#dashboardReports'), {'right': '0', 'top': '-10px'});
            this.notificationView.render();
            this.enable();
        },

        selectReportView: function() {
            this.form.empty();
            this.selectForm.render(true);
            this.selectForm.resetList();

            var _this = this;
            this.selectForm.attachEventHandlers(function() {
                _this.closeForm();
            }, function() {
                _this.applyReports();
                _this.closeForm();
            });
        },

        reportListView: function() {
            this.form.empty();
            this.listForm.render(true);

            var _this = this;
            this.listForm.attachEventHandlers(function() {
                _this.selectReportView();
            });
        },

        applyReports: function() {
            var selected = this.selectForm.getReports();
            var ids = this.dashboard.getReportIds();
            var _this = this;
            jQuery.grep(selected, function(i) {
                if (jQuery.inArray(i, ids) == -1) {
                    _this.dashboard.addReport({
                        id: parseInt(_this.selectForm.reportList[i]['val']),
                        title: _this.selectForm.reportList[i]['text']
                    });
                }
            });

            jQuery.grep(ids, function(i) {
                if (jQuery.inArray(i, selected) == -1) _this.dashboard.removeReport(i);
            });
        },

        openForm: function() {
            this._super();
            this.reportListView();
        },

        updateNotification: function(updated) {
            this.notification.setValue(this.dashboard.getReportIds().length, updated);
            this.notificationView.update();
        },

        attachEventHandlers: function() {
            this._super();
            var _this = this;
            $(document).on('add.dashboard.report remove.dashboard.report', function() {
                _this.updateNotification();
            });
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);