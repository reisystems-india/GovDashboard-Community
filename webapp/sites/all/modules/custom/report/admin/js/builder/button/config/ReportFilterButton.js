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
        throw new Error('ReportFilterButton requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportFilterButton requires GD');
    }

    var GD = global.GD;

    global.GD.ReportFilterButton = GD.ReportConfigButton.extend({
        monthsCache: null,
        quartersCache: null,
        editForm: null,
        createListForm: null,
        createForm: null,
        notification: null,
        notificationView: null,

        init: function(object, options) {
            this._super({'button': '#reportFilterButton','form': '#reportFilterForm'}, options);
            this.monthsCache = null;
            this.quartersCache = null;
            this.form.css('top', '31px');
            this.form.css('left', '212px');
        },

        initForm: function(object) {
            var reportObj = this.getController().getReport();
            this.listForm = new GD.ReportFilterListForm(this, this.form, {'builder': this.getController()});
            this.editForm = null;
            this.createListForm = new GD.ReportFilterCreateForm(reportObj, this.form, {'builder': this.getController()});
            this.createForm = null;
            this.notification = new GD.Notification(reportObj.getFilters().length);
            this.notificationView = new GD.NotificationView(this.notification, $('#reportFilterButton'), {'right': '0', 'top': '-10px'});
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
            }, function(f) {
                _this.deleteFilter(f);
            });

            if (this.form.data("uiResizable")){
                this.form.resizable("destroy");
            }
            this.form.resizable ({
                minWidth: 265,
                maxWidth: 596,
                handles: 'e'
            });
        },

        editFilterView: function(filter) {
            this.form.empty();

            var options = {
                'operatorText': 'Default Operator:',
                'filters': this.getController().getReport().getFilters()
            };

            var _this = this;
            var form = GD.FilterFormFactory.getForm(filter, null, options);
            this.editForm = new GD.ReportFilterForm(filter, this.form, {
                filterForm: form
            });
            this.editForm.render(true);

            this.editForm.attachEventHandlers(function() {
                // cancel
            },function() {
                // apply
                _this.editFilter();
                _this.closeForm();
            }, function(f) {
                _this.deleteFilter(f);
            });

            if (this.form.data("uiResizable")){
                this.form.resizable("destroy");
            }
            this.form.resizable ({
                minWidth: 265,
                maxWidth: 596,
                handles: 'e'
            });
        },

        filterCreateListView: function() {
            this.form.empty();
            this.createListForm.render(true);

            var _this = this;
            this.createListForm.attachEventHandlers(function() {
                _this.closeForm();
            }, function(f) {
                _this.filterCreateView(f);
            });

            if (this.form.data("uiResizable")){
                this.form.resizable("destroy");
            }
            this.form.resizable ({
                minWidth: 265,
                maxWidth: 596,
                handles: 'e'
            });
        },

        filterCreateView: function(filter) {
            this.form.empty();

            var f = new GD.ReportFilter(filter);
            var options = { operatorText: 'Operators' };

            var _this = this;
            var form = GD.FilterFormFactory.getForm(f, null, options);
            this.createForm = new GD.ReportFilterForm(f, this.form, {
                filterForm: form,
                create: true
            });
            this.createForm.render(true);

            this.createForm.attachEventHandlers(function() {
                // on cancel
            }, function() {
                // on apply
                _this.createFilter();
                _this.closeForm();
            });

            if (this.form.data("uiResizable")){
                this.form.resizable("destroy");
            }
            this.form.resizable ({
                minWidth: 265,
                maxWidth: 596,
                handles: 'e'
            });
        },

        createFilter: function() {
            this.getController().getReport().addFilter(this.createForm.getFilter());
            $(document).trigger({
                type: 'changed.report.filters'
            });
        },

        deleteFilter: function(f) {
            this.getController().getReport().removeFilter(f);
            this.filterListView();
            $(document).trigger({
                type: 'changed.report.filters'
            });
        },

        editFilter: function() {
            this.getController().getReport().editFilter(this.editForm.getFilter());
            $(document).trigger({
                type: 'changed.report.filters'
            });
        },

        openForm: function() {
            this.filterListView();
            this._super();
        },

        updateNotification: function(updateOriginal) {
            var filterCount = 0;
            var reportFilters = this.getController().getReport().getFilters();
            if ($.isArray(reportFilters)) {
                filterCount = reportFilters.length;
            }
            if ( this.notification ) {
                if (!updateOriginal && this.notification.original !== filterCount) {
                    this.notification.resetOriginal();
                }
                this.notification.setValue(filterCount, updateOriginal);
                this.notificationView.update();
            }
        },

        attachEventHandlers: function() {
            this._super();
            var _this = this;
            
            $(document).on('changed.report.columns', function() {
                 _this.enable();
            });

            $(document).on('changed.report.type', function() {
                 _this.closeForm();
            });
            
            $(document).on('cancel.filter.edit', function() {
                _this.closeForm();
            });

            $(document).on('changed.report.filters', function() {
                _this.updateNotification(false);
            });

            //$(document).on('notification.report.filter remove.report.filter', function() {
            //    _this.closeForm();
            //});
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);