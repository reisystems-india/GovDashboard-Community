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
        throw new Error('DashboardLinkButton requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DashboardLinkButton requires GD');
    }

    var GD = global.GD;

    global.GD.DashboardLinkButton = GD.DashboardConfigButton.extend({
        listForm: null,
        editForm: null,
        createForm: null,

        init: function(object, options) {
            this.button = $('#dashboardLink');
            this.form = $('#dashboardLinkForm');
            this.form.css('left', '206px');
            this._super(object, options);
        },

        initForm: function() {
            this.listForm = new GD.DashboardLinkListForm(this.dashboard, this.form, {'datasource': this.getDatasource()});
            this.editForm = null;
            this.createForm = null;
            this.notification = new GD.Notification(this.dashboard.getLinks().length);
            this.notificationView = new GD.NotificationView(this.notification, $('#dashboardLink'), {'right': '0', 'top': '-10px'});
            this.notificationView.render();
        },

        createLinkView: function() {
            this.form.empty();
            this.createForm = new GD.DashboardLinkForm(new GD.Link(), this.form, {'datasource': this.getDatasource(), 'dashboard': this.dashboard});
            this.createForm.render(true);

            var _this = this;
            this.createForm.attachEventHandlers(function() {
                _this.closeForm();
            }, function() {
                _this.addLink();
                _this.closeForm();
            });
        },

        editLinkView: function(d) {
            this.form.empty();
            this.editForm = new GD.DashboardLinkForm(d, this.form, {'datasource': this.getDatasource(), 'dashboard': this.dashboard});
            this.editForm.render(true);

            var _this = this;
            this.editForm.attachEventHandlers(function() {
                _this.closeForm();
            }, function() {
                _this.editLink();
                _this.closeForm();
            });
        },

        linkListView: function() {
            this.form.empty();
            this.listForm.render(true);

            var _this = this;
            this.listForm.attachEventHandlers(function() {
                _this.createLinkView();
            }, function(d) {
                _this.editLinkView(d);
            });
        },

        addLink: function() {
            var link = this.createForm.getLink();
            this.dashboard.addLink(link);
        },

        editLink: function() {
            var link = this.editForm.getLink();
            this.dashboard.editLink(link);
        },

        openForm: function() {
            this._super();
            this.linkListView();
        },

        updateNotification: function(updated) {
            this.notification.setValue(this.dashboard.getLinks().length, updated);
            this.notificationView.update();
        },

        attachEventHandlers: function() {
            this._super();
            var _this = this;
            $(document).on('edit.dashboard.link', function() {
                _this.notification.setChanged(true);
            });
            $(document).on('add.dashboard.link remove.dashboard.link edit.dashboard.link', function() {
                _this.updateNotification();
            });
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);