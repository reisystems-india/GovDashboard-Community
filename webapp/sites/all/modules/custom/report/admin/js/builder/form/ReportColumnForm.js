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
        throw new Error('ReportColumnForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportColumnForm requires GD');
    }

    var GD = global.GD;

    var ReportColumnForm = GD.View.extend({
        formContainer: null,
        selectedForm: null,
        selectionForm: null,
        divider: null,

        init: function(object, container, options) {
            this._super(object, container, options);
        },

        getController: function () {
            return this.options.builder;
        },

        getFormContainer: function() {
            if (!this.formContainer) {
                this.formContainer = $('<div class="report-column-form"></div>');
            }

            return this.formContainer;
        },

        getColumns: function() {
            return this.getController().getReport().getColumns();
        },

        getSelectedColumnForm: function() {
            if (!this.selectedForm) {
                this.selectedForm = new GD.ReportSelectedColumnForm(null, this.getFormContainer(), { controller: this.getController() });
            }

            return this.selectedForm;
        },

        getColumnSelectionForm: function() {
            if (!this.selectionForm) {
                this.selectionForm = new GD.ReportColumnSelectionForm(null, this.getFormContainer(), { controller: this.getController() });
            }

            return this.selectionForm;
        },

        getFormDivider: function() {
            if (!this.divider) {
                this.divider = $('<div class="report-column-divider pull-left"></div>');
            }

            return this.divider;
        },

        getFooter: function() {
            if (!this.footer) {
                this.footer = $('<div class="report-form-footer clearfix"></div>');
                this.footer.append(this.getApplyButton());
            }

            return this.footer;
        },

        validate: function() {
            GD.ReportBuilderMessagingView.clean();

            var pass = true,
                columns = this.getSelectedColumnForm().getColumnList();
            if (!columns.length) {
                pass = false;
                GD.ReportBuilderMessagingView.addErrors('Select at least one Column');
                GD.ReportBuilderMessagingView.displayMessages();
            }

            return pass;
        },

        getApplyButton: function() {
            if (!this.applyButton) {
                this.applyButton = $('<button tabindex="3000" type="button" class="btn-save btn btn-primary">Apply</button>');

                var _this = this;
                this.applyButton.click(function() {
                    if (_this.validate()) {
                        _this.getController().getReport().setColumns(_this.getSelectedColumnForm().getColumnList());
                    }
                });
            }

            return this.applyButton;
        },

        render: function() {
            if ( this.container ) {
                this.container.append(this.getFormContainer());
                this.getSelectedColumnForm().render();
                var formcontainerObj = this.getFormContainer();
                formcontainerObj.append(this.getFormDivider());
                this.getColumnSelectionForm().render();
                formcontainerObj.append('<div class="clearfix"></div>');
                formcontainerObj.append(this.getFooter());
            }

            return this.getFormContainer();
        }
    });

    GD.ReportColumnForm = ReportColumnForm;

})(typeof window === 'undefined' ? this : window, jQuery);