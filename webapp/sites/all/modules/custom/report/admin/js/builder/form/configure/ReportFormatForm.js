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
        throw new Error('ReportFormatForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportFormatForm requires GD');
    }

    var GD = global.GD;

    var ReportFormatForm = GD.View.extend({

        init: function(object, container, options) {
            this._super(object, container, options);
            this.initForm();
        },

        getFormContainer: function() {
            if ( !this.formContainer ) {
                this.formContainer = $('<div></div>');
            }
            return this.formContainer;
        },

        update: function() {
            if (this.listForm) this.showListView();
            if (this.createForm) this.createForm.update();
        },

        initForm: function() {
            this.listForm = new GD.ReportFormatListForm(this.getController().getReport(), this.getFormContainer(), this.options);
            this.editForm = null;
            this.createForm = null;
        },

        showListView: function() {
            this.getFormContainer().empty();
            this.listForm.enableDisableAddbutton();
            this.listForm.render(true);

            var _this = this;
            this.listForm.attachEventHandlers(
                // add handler
                function() {
                    _this.showCreateView();
                },
                // click handler
                function(f) {
                    _this.showEditView(f, false);
                }
            );
        },

        showCreateView: function () {
            this.getFormContainer().empty();
            this.createForm = new GD.ReportFormatCreateForm(null,this.getFormContainer(),this.options);
            this.createForm.render();

            var _this = this;
            this.createForm.attachEventHandlers(
                // cancel handler
                function() {
                    _this.showListView();
                },
                // next handler
                function(f) {
                    _this.showEditView(f, true);
                }
            );
        },

        showEditView: function ( format, isNew ) {
            this.getFormContainer().empty();
            this.options["isNew"] = isNew;
            this.editForm = GD.ReportFormatFormFactory.getForm(format,this.getFormContainer(), this.options);
            this.editForm.render();

            var _this = this;
            this.editForm.attachEventHandlers(
                // cancel handler
                function() {
                    _this.showListView();
                },
                // apply handler
                function(f) {
                    _this.showListView();
                },
                // delete handler
                function(f) {
                    _this.showListView();
                }
            );

        },

        getController: function () {
            return this.options.builder;
        },

        render: function () {
            if ( this.container ) {
                this.container.append(this.getFormContainer());
            }

            this.showListView();

            return this.getFormContainer();
        }


    });

    GD.ReportFormatForm = ReportFormatForm;

})(typeof window === 'undefined' ? this : window, jQuery);