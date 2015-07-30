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
        throw new Error('DashboardFilterButton requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DashboardFilterButton requires GD');
    }

    var GD = global.GD;

    global.GD.DashboardFilterButton = GD.DashboardConfigButton.extend({
        editForm: null,
        createListForm: null,
        createForm: null,
        notification: null,
        notificationView: null,

        init: function(object, options) {
            this.button = $('#dashboardFilters');
            this.form = $('#dashboardFiltersForm');
            this.form.css('left', '120px');
            this._super(object, options);
        },

        initForm: function() {
            this.listForm = new GD.DashboardFilterListForm(this.dashboard, this.form, {'datasource': this.getDatasource()});
            this.editForm = null;
            this.createListForm = null;
            this.createForm = null;
            this.notification = new GD.Notification(this.dashboard.getFilters().length);
            this.notificationView = new GD.NotificationView(this.notification, $('#dashboardFilters'), {'right': '0', 'top': '-10px'});
            this.notificationView.render();
        },

        filterListView: function() {
            this.form.empty();
            this.listForm.render(true);

            var _this = this;
            this.listForm.attachEventHandlers(function() {
                _this.filterCreateListView();
            }, function(f) {
                _this.editFilterView(f);
            });
        },

        editFilterView: function(filter) {
            this.form.empty();

            var options = {
                'operatorText': 'Default Operator:',
                'operators': {'not.required': '-None-'},
                'filters': this.dashboard.getFilters()
            };

            var form = GD.FilterFormFactory.getForm(filter, null, options);
            this.editForm = new GD.DashboardFilterForm(filter, this.form, {
                filterForm: form
            });
            this.editForm.render(true);

            var _this = this;
            this.editForm.attachEventHandlers(function() {
                _this.closeForm();
            }, function() {
                _this.editFilter();
                _this.closeForm();
            }, function() {
                _this.deleteFilter();
                _this.closeForm();
            });
        },

        filterCreateListView: function() {
            this.form.empty();
            this.createListForm = new GD.DashboardFilterCreateForm(this.dashboard, this.form, {'datasource': this.getDatasource()});
            this.createListForm.render(true);

            var _this = this;
            this.createListForm.attachEventHandlers(function() {
                _this.closeForm();
            }, function(f) {
                _this.filterCreateView(f);
            });
        },

        filterCreateView: function(filter) {
            this.form.empty();

            var f = new GD.Filter(filter);
            f.setExposed(1);
            var options = {
                'operatorText': 'Default Operator:',
                'operators': {'not.required': '-None-'}
            };
            var form = GD.FilterFormFactory.getForm(f, null, options);
            this.createForm = new GD.DashboardFilterForm(f, this.form, {
                filterForm: form,
                create: true
            });
            this.createForm.render(true);

            var _this = this;
            this.createForm.attachEventHandlers(function() {
                _this.closeForm();
            }, function() {
                _this.createFilter();
                _this.closeForm();
            });
        },

        createFilter: function() {
            var f = this.createForm.getFilter();
            this.dashboard.addFilter(f);
        },

        deleteFilter: function() {
            var f = this.editForm.getFilter();
            this.dashboard.removeFilter(f);
        },

        editFilter: function() {
            var f = this.editForm.getFilter();
            this.dashboard.editFilter(f);
        },

        openForm: function() {
            this.filterListView();
            this._super();
        },

        updateNotification: function(updated) {
            this.notification.setValue(this.dashboard.getFilters().length, updated);
            this.notificationView.update();
        },

        attachEventHandlers: function() {
            this._super();
            var _this = this;
            $(document).on('add.dashboard.filter remove.dashboard.filter edit.dashboard.filter', function() {
                _this.updateNotification();
            });

            $(document).on('add.dashboard.report', function() {
                _this.enable();
            });
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);