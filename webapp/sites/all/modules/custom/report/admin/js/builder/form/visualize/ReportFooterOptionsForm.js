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
        throw new Error('ReportFooterOptionsForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportFooterOptionsForm requires GD');
    }

    var GD = global.GD;

    var ReportFooterOptionsForm = GD.View.extend({
        init: function(object, container, options) {
            this._super(object, container, options);
            this.initForm();
        },

        getFormContainer: function() {
            if ( !this.formContainer ) {
                this.formContainer = $([
                    '<div class="panel panel-default">',
                        '<div class="panel-heading">',
                            '<div class="panel-title">',
                                '<div class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#visualizePanelGroup" href="#reportFooterPanel">',
                                    'Footer Options',
                                '</div>',
                            '</div>',
                        '</div>',
                        '<div id="reportFooterPanel" class="panel-collapse collapse">',
                            '<div class="panel-body">',
                            '</div>',
                        '</div>',
                    '</div>'
                ].join("\n"));
            }
            return this.formContainer;
        },

        initForm: function() {
            this.listForm = new GD.ReportFooterListForm(this.getController().getReport(), this.getFormContainer().find('#reportFooterPanel .panel-body'), this.options);
            this.editForm = null;
            this.createForm = null;
        },

        showListView: function() {
            this.formContainer.find('#reportFooterPanel .panel-body').empty();
            this.listForm.render(true);

            var _this = this;
            this.listForm.attachEventHandlers(
                function() {
                    _this.showCreateView();
                },
                function(footer) {
                    _this.showEditView(footer, false);
                }
            );
        },

        showCreateView: function () {
            this.getFormContainer().find('#reportFooterPanel .panel-body').empty();
            this.createForm = new GD.ReportFooterCreateForm(null, this.getFormContainer().find('#reportFooterPanel .panel-body'), this.options);
            this.createForm.render();

            var _this = this;
            this.createForm.attachEventHandlers(
                function() {
                    _this.showListView();
                },
                function(id, name) {
                    _this.showEditView({measure: id, text: name, alignment: "left", "new": true});
                }
            );
        },

        showEditView: function (footer) {
            var container = this.getFormContainer().find('#reportFooterPanel .panel-body');
            container.empty();
            this.editForm = new GD.ReportFooterEditForm(footer, this.getFormContainer().find('#reportFooterPanel .panel-body'), this.options);
            this.editForm.render();

            if (footer['new']) {
                this.getAddButton().text('Add');
            } else {
                this.getAddButton().text('Save');
            }

            var _this = this;
            this.getCancelButton().off("click").on("click", function(){
                _this.showListView();
            });
            this.getAddButton().off("click").on("click", function(){
                _this.editForm.saveOptions();
                _this.showListView();
            });
            container.append(this.getActionButtons());
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
        },

        getAddButton: function(){
            if (!this.addButton) {
                this.addButton = $('<button type="button" class="rpt-flt-act-btn btn btn-primary btn-sm">Add</button>');
            }

            return this.addButton;
        },

        getCancelButton: function(){
            if (!this.cancelButton) {
                this.cancelButton = $('<button type="button" class="rpt-flt-act-btn btn btn-default btn-sm">Cancel</button>');
            }

            return this.cancelButton;
        },

        getActionButtons: function() {
            if (!this.actionContainer) {
                this.actionContainer = $('<div class="rpt-flt-act-cntr"></div>');
                this.actionContainer.append(this.getAddButton(),' ',this.getCancelButton());
            }

            return this.actionContainer;
        }
    });

    GD.ReportFooterOptionsForm = ReportFooterOptionsForm;

})(typeof window === 'undefined' ? this : window, jQuery);