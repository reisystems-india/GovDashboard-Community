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
        throw new Error('ReportFilterForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportFilterForm requires GD');
    }

    var GD = global.GD;

    global.GD.ReportFilterForm = GD.View.extend({
        formContainer: null,
        actionButtons: null,
        cancelButton: null,
        applyButton: null,
        deleteButton: null,
        exposedContainer: null,
        filterForm: null,
        modal: null,
        isNew: false,

        init: function(object, container, options) {
            this._super(object, container, options);
            this.initVariables();

            if (options) {
                this.isNew = options['create'];
            } else {
                this.isNew = false;
            }
        },

        initVariables: function() {
            this.formContainer = null;
            this.actionButtons = null;
            this.cancelButton = null;
            this.applyButton = null;
            this.deleteButton = null;
            this.filterForm = null;
            this.exposedContainer = null;
            this.modal = null;

            if (this.options) {
                this.setFilterForm(this.options['filterForm']);
            }
        },

        setFilterForm: function(form) {
            this.filterForm = form;
        },

        getFormContainer: function() {
            if (!this.formContainer) {
                this.formContainer = $('<div class="rpt-flt-frm"></div>');
                var header = $('<h5 class="rpt-frm-hdr rpt-flt-frm-hdr"></h5>');
                if (!this.isNew) {
                    header.text('Edit filter for Column "' + this.object.name + '":');
                } else {
                    header.text('Create Filter for Column "' + this.object.getColumnPublicName() + '":');
                }

                this.formContainer.append(header);
                this.formContainer.append(this.getNameContainer());
                this.formContainer.append(this.getExposedContainer());
                this.formContainer.append(this.getFilterFormContainer());
                this.formContainer.append(this.getActionButtons());
            }

            return this.formContainer;
        },

        getNameInput: function() {
            if (!this.nameInput) {
                this.nameInput = $('<input type="text" class="form-control input-sm">');
                if (this.object.getName()) {
                    this.nameInput.val(this.object.getName());
                } else {
                    this.nameInput.val(this.object.getColumnPublicName());
                }


            }

            return this.nameInput;
        },

        getNameContainer: function() {
            if (!this.nameContainer) {
                this.nameContainer = $('<div class="form-group"></div>');
                this.nameContainer.append('<label for="">Filter Name: </label>');
                this.nameContainer.append(this.getNameInput());
            }

            return this.nameContainer;
        },

        getFilterFormContainer: function() {
            if (!this.filterFormContainer) {
                this.filterFormContainer = $('<div class="rpt-flt-frm-cntnr reportFilterCriteria"></div>');

                if (this.filterForm) {
                    this.filterFormContainer.append(this.filterForm.render());
                }
            }

            return this.filterFormContainer;
        },

        getExposedOption: function() {
            if (!this.exposedOption){
                this.exposedOption = $('<input type="checkbox" value="exposed" class="form-control input-sm">');
            }

            return this.exposedOption;
        },

        getExposedContainer: function() {
            if (!this.exposedContainer) {
                this.exposedContainer = $('<div class="rpt-flt-frm-xps-cnt"></div>');
                var c = $('<div class="checkbox"></div>');
                c.append($('<label></label>').append(this.getExposedOption()).append('Pre-Apply filter to report'));

                if (this.object) {
                    if(this.object.getExposed() !== undefined){
                        this.getExposedOption().prop('checked', !this.object.getExposed());
                        this.exposedChanged(!this.object.getExposed());
                    }else{
                        this.getExposedOption().prop('checked', false);
                        this.exposedChanged(false);
                    }
                }

                this.exposedContainer.append(c);
            }

            return this.exposedContainer;
        },

        getDeleteButton: function() {
            if (!this.deleteButton) {
                this.deleteButton = $('<button type="button" class="rpt-act-btn rpt-flt-act-btn btn btn-default btn-sm pull-right">Delete</button>');
            }

            return this.deleteButton;
        },

        getCancelButton: function() {
            if (!this.cancelButton) {
                this.cancelButton = $('<button type="button" class="rpt-act-btn rpt-flt-act-btn btn btn-default btn-sm pull-right">Cancel</button>');
            }

            return this.cancelButton;
        },

        getApplyButton: function() {
            if (!this.applyButton) {
                this.applyButton = $('<button type="button" class="rpt-act-btn rpt-flt-act-btn btn btn-primary btn-sm pull-right">Apply</button>');
            }

            return this.applyButton;
        },

        getActionButtons: function() {
            if (!this.actionButtons) {
                this.actionButtons = $('<div class="clearfix rpt-act-btn-cntr rpt-flt-act-cntr"></div>');
                this.actionButtons.append(this.getCancelButton());
                if (!this.isNew) {
                    this.actionButtons.append(this.getDeleteButton());
                }
                this.actionButtons.append(this.getApplyButton());
           }

            return this.actionButtons;
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

        exposedChanged: function(exposed) {
            if (exposed) {
                this.getFilterFormContainer().show();
                if (this.filterForm) {
                    this.filterForm.show();
                }
            } else {
                this.getFilterFormContainer().hide();
                if (this.filterForm) {
                    this.filterForm.hide();
                }
            }
        },

        getModal: function(callback) {
            if (!this.modal) {
                this.modal = $('<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true"></div>');
                var content = $('<div class="modal-content"></div>');
                var header = $('<div class="modal-header"></div>');
                header.append('<h4>Delete Filter</h4>');
                var body = $('<div class="modal-body row"><span style="font-size:25px;" class="glyphicon glyphicon-info-sign col-md-1"></span><div style="height:25px; line-height: 25px;" class="col-md-11">Are you sure you want to delete this filter?</div></div>');
                var footer = $('<div class="modal-footer"></div>');
                var deleteButton = $('<button type="button" class="btn btn-danger" data-dismiss="modal">Delete</button>');

                var _this = this;
                deleteButton.on('click', function() {
                    _this.deleteFilter(callback);
                });

                footer.append(deleteButton);
                footer.append('<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>');
                content.append(header, body, footer);
                var dialog = $('<div class="modal-dialog"></div>');
                dialog.append(content);
                this.modal.append(dialog);
                $('body').append(this.modal);
            }

            return this.modal;
        },

        deleteButtonClicked: function(callback) {
            this.getModal(callback).modal();
        },

        deleteFilter: function(callback) {
            if (callback) {
                callback(this.getFilter());
            }
        },

        validate: function() {
            this.messages = [];
             if (!this.getExposed()) {
                if(this.options.filterForm){
                    this.filterForm.validateValues(this.messages);
                }
            }
            return (this.messages.length === 0);
        },

        addFieldError: function(message,form){
            var msgField = ($('.input-group', form).length > 0)?$('.input-group', form): $('.form-control', form);
            msgField.after('<div class="help-block">'+message+'</div>');
        },

        removeFieldError: function(form){
            $('.help-block', form).remove();
        },

        applyButtonClicked: function(callback) {
            var form = this.options.filterForm.getForm() || $('.bldr-flt-frm-val'),
                secondForm = this.options.filterForm.getSecondForm() || null;
            this.removeFieldError(form);
            if(secondForm){
                this.removeFieldError(secondForm);
            }
            if (!this.validate()) {
                // To-Do: display error message logic: implemented in weird way should be handled in form.
                if (this.messages.length === 1){
                    if (form.hasClass("has-error")){
                        this.addFieldError(this.messages[0],form);
                    } else {
                        this.addFieldError(this.messages[0],secondForm);
                    }
                } else {
                    this.addFieldError(this.messages[0],form);
                    this.addFieldError(this.messages[1],secondForm);
                }
            } else {
                this.removeFieldError();
                var column = {};
                column.datasetName = this.object.getDatasetNames();
                if (this.isNew == true) {
                    column.name = this.object.getColumnName();
                } else {
                    column.name = this.object.getColumnPublicName();
                }
                column.type = this.object.column.type;
                this.object.name = this.getNameInput().val();
                this.object.setColumn(column);
                this.object.setOperator(this.getOperator());
                this.getFilter();
                callback();
            }
        },

        attachEventHandlers: function(cancel, apply, del) {
            var _this = this;

            this.getExposedOption().change(function() {
                _this.exposedChanged(this.checked);
            });

            if (cancel && this.getCancelButton()) {
                this.getCancelButton().on('click',function() {
                    $(document).trigger({
                        type: 'cancel.filter.edit'
                    });
                    cancel();
                });
            }

            if (apply && this.getApplyButton()) {
                this.getApplyButton().on('click',function() {
                    _this.applyButtonClicked(apply);
                });
            }

            if (!this.isNew) {
                if (del && this.getDeleteButton()) {
                    this.getDeleteButton().on('click',function() {
                        _this.deleteButtonClicked(del);
                    });
                }
            }
        },

        getFilterName: function() {
            return this.getNameInput().val();
        },

        getOperator: function() {
            return this.filterForm ? this.filterForm.getOperator() : null;
        },

        getValue: function() {
            if (GD.OperatorFactory.isParameterOperator(this.getOperator())) {
                if (GD.OperatorFactory.isRangeOperator(this.getOperator())) {
                    return [this.filterForm.getValue(), this.filterForm.getSecondValue()];
                } else {
                    return this.filterForm.getValue();
                }
            } else {
                return null;
            }
        },

        getExposed: function() {
            return !this.getExposedOption().prop('checked');
        },
        getFilter: function() {
            var f = this.object;
            var o = this.filterForm.getOperator();

            f.setExposed(this.getExposed());
            if (!this.getExposed()) {
                if (GD.OperatorFactory.isParameterOperator(o)) {
                    if (GD.OperatorFactory.isRangeOperator(o)) {
                        f.setValue(this.filterForm.getValue(), this.filterForm.getSecondValue());
                    } else {
                        f.setValue(this.filterForm.getValue());
                    }
                } else {
                    f.setValue();
                }
                f.setOperator(o);
            } else {
                f.setValue();
                f.setOperator(null);
            }

            return f;
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);