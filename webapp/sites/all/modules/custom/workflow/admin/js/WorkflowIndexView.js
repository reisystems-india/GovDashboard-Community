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
        throw new Error('WorkflowIndexView requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('WorkflowIndexView requires GD');
    }

    var GD = global.GD;

    var WorkflowIndexView = GD.View.extend({
        toForm: null,
        toHelp: null,
        toContainer: null,
        fromForm: null,
        fromHelp: null,
        fromContainer: null,
        formContainer: null,
        modal: null,

        init: function ( object, container, options ) {
            this._super(object, container, options);
            this.initVariables();
        },

        initVariables: function() {
            this.toForm = null;
            this.toHelp = null;
            this.toContainer = null;
            this.fromForm = null;
            this.fromHelp = null;
            this.fromContainer = null;
            this.syncForm = null;
            this.syncHelp = null;
            this.syncContainer = null;
            this.formContainer = null;
            this.modal = null;
        },

        initLayout: function () {
            if ( this.layout == null ) {
                this.layoutHeader = $('<div id="gd-view-workflow-list-header"><div id="gd-view-workflow-list-messages"></div></div>');

                this.layoutBody = $('<div class="col-md-12" id="gd-view-workflow-list-body"></div>');
                this.layoutMessages = $('<div class="col-md-12" id="gd-view-workflow-list-body-messages"></div>');
                var body_wrap = $('<div class="row"></div>').append(this.layoutMessages,this.layoutBody);

                this.layoutFooter = $('<div class="col-md-12" id="gd-view-workflow-list-footer"></div>');
                var footer_wrap = $('<div class="row"></div>').append(this.layoutFooter);

                this.layout = $('<div id="gd-view-workflow-list"></div>').append(this.layoutHeader,body_wrap,footer_wrap);

                this.container.append(this.layout);

                this.messaging = new GD.MessagingView('#gd-view-workflow-list-body-messages');
            }
        },

        loadDatasources: function(datasources) {
            this.getFromForm().loadDatasources(datasources);
            this.getToForm().loadDatasources(datasources);
            this.getSyncForm().loadDatasources(datasources);
        },

        getSyncForm: function() {
            if (!this.syncForm) {
                this.syncForm = new GD.WorkflowOptionsForm();
            }

            return this.syncForm;
        },

        getSyncHelp: function() {
            if (!this.syncHelp) {
                this.syncHelp = $('<h3></h3>');
                this.syncHelp.text('3. Sync');
            }

            return this.syncHelp;
        },

        getSyncContainer: function() {
            if (!this.syncContainer) {
                this.syncContainer = $('<div class="workflow-form-container pull-left"></div>');
                this.syncContainer.append(this.getSyncHelp());
                this.syncContainer.append(this.getSyncForm().render());
            }

            return this.syncContainer;
        },

        getToForm: function() {
            if (!this.toForm) {
                this.toForm = new GD.WorkflowDatasourcesForm();
            }

            return this.toForm;
        },

        getToHelp: function() {
            if (!this.toHelp) {
                this.toHelp = $('<h3></h3>');
                this.toHelp.text('2. Select Destination(s)');
            }

            return this.toHelp;
        },

        getToContainer: function() {
            if (!this.toContainer) {
                this.toContainer = $('<div class="workflow-form-container pull-left"></div>');
                this.toContainer.append(this.getToHelp());
                this.toContainer.append(this.getToForm().render());
            }

            return this.toContainer;
        },

        getFromForm: function() {
            if (!this.fromForm) {
                this.fromForm = new GD.WorkflowItemsForm(null, null, { messaging: this.getWorkflowMessaging() });
            }

            return this.fromForm;
        },

        getFromHelp: function() {
            if (!this.fromHelp) {
                this.fromHelp = $('<h3></h3>');
                this.fromHelp.text('1. Select Item(s) to Sync');
            }

            return this.fromHelp;
        },

        getFromContainer: function() {
            if (!this.fromContainer) {
                this.fromContainer = $('<div class="workflow-form-container pull-left"></div>');
                this.fromContainer.append(this.getFromHelp());
                this.fromContainer.append(this.getFromForm().render());
            }

            return this.fromContainer;
        },

        getFormContainer: function() {
            if (!this.formContainer) {
                this.formContainer = $('<div class="fuelux"></div>');
                this.formContainer.append(this.getFromContainer());
                this.formContainer.append(this.getToContainer());
                this.formContainer.append(this.getSyncContainer());
            }

            return this.formContainer;
        },

        getWorkflowMessaging: function() {
            if (!this.messaging) {
                this.messaging = new GD.MessagingView('#gd-admin-messages');
            }

            return this.messaging;
        },

        render: function () {
            this.initLayout();
            this.layoutBody.append(this.getFormContainer());
            this.attachEventHandlers();
        },

        validate: function() {
            var valid = true;

            this.getWorkflowMessaging().clearMessages();
            this.getWorkflowMessaging().clearDisplay();

            var items = this.getFromForm().getItems();
            if (!Object.keys(items).length) {
                valid = false;
                this.getWorkflowMessaging().addErrors('Please select at least one item to sync.');
            }

            var dest = this.getToForm().getDestinations();
            if (!dest.length) {
                valid = false;
                this.getWorkflowMessaging().addErrors('Please select at least one destination to sync.');
            }

            this.getWorkflowMessaging().displayMessages();

            return valid;
        },

        runSync: function() {
            if (this.validate()) {
                if (this.object) {
                    this.getModal().modal({
                        keyboard: false
                    });

                    var _this = this;
                    var c = function() {
                        _this.getModal().modal('hide');
                    };
                    try {
                        this.object.processItems(this.getFromForm().getItemObjects(), this.getToForm().getDestinations(),
                            { callback: c, reports: this.getFromForm().getReportList(), dashboards: this.getFromForm().getDashboardList() });
                    } catch(e) {
                        _this.context.view.messaging.showMessage(e.getMessage(), 'notice');
                        c();
                    }
                }
            }
        },

        getModal: function() {
            if (!this.modal) {
                this.modal = $('<div class="modal fade fuelux" tabindex="-1" role="dialog" aria-hidden="true"></div>');
                var content = $('<div class="modal-content"></div>');
                var header = $('<div class="modal-header"></div>');
                header.append('<h4>Workflow</h4>');
                var body = $('<div class="modal-body row"></div>');
                var loader = $('<div class="loader" data-initialize="loader" style="margin-left:auto;margin-right:auto;"></div>');
                body.append(loader);
                var footer = $('<div class="modal-footer"></div>');
                content.append(header, body, footer);
                var dialog = $('<div class="modal-dialog"></div>');
                dialog.append(content);
                this.modal.append(dialog);
                $('body').append(this.modal);
                loader.loader();
            }

            return this.modal;
        },

        attachEventHandlers: function() {
            var _this = this;
            $(document).on('execute.workflow.sync', function() {
                _this.runSync();
            });
        }
    });

    // add to global space
    global.GD.WorkflowIndexView = WorkflowIndexView;

})(typeof window === 'undefined' ? this : window, jQuery);