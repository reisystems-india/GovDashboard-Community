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
        throw new Error('FilterForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('FilterForm requires GD');
    }

    var GD = global.GD;

    global.GD.FilterForm = GD.View.extend({
        operators: null,
        operatorSelect: null,
        formContainer: null,
        operatorView: null,
        operatorLabel: null,
        operatorText: null,
        form: null,
        formInput: null,
        secondForm: null,
        secondFormInput: null,

        init: function(object, container, options) {
            this._super(object, container, options);
            this.operatorSelect = null;
            this.formContainer = null;
            this.operatorView = null;
            this.operatorLabel = null;
            this.form = null;
            this.formInput = null;
            this.secondForm = null;
            this.secondFormInput = null;

            if (options) {
                this.operators = options['operators'];
                this.operatorText = options['operatorText'];
            } else {
                this.operators = null;
                this.operatorText = 'Operators:'
            }
        },

        validateOperator: function(messages) {
            if (!this.getOperator()) {
                messages.push('Please select a filter operator.');
                this.toggleOperatorError();
            } else {
                this.toggleOperatorError(false);
            }
        },

        validateValues: function(messages) {
            if (GD.OperatorFactory.isParameterOperator(this.getOperator()) && !this.getValue()) {
                messages.push('Please enter a filter value.');
                this.toggleFormError();
            } else {
                this.toggleFormError(false);
            }

            if (GD.OperatorFactory.isRangeOperator(this.getOperator()) && !this.getSecondValue()) {
                messages.push('Please enter a range filter value.');
                this.toggleSecondFormError();
            } else {
                this.toggleSecondFormError(false);
            }
        },

        toggleOperatorError: function(i) {
            this.getOperatorView().toggleClass('has-error', typeof i != 'undefined' ? i : true);
        },

        toggleFormError: function(i) {
            this.getForm().toggleClass('has-error', typeof i != 'undefined' ? i : true);
        },

        toggleSecondFormError: function(i) {
            this.getSecondForm().toggleClass('has-error', typeof i != 'undefined' ? i : true);
        },

        validate: function(messagingView) {
            var messages = [];
            this.validateOperator(messages);
            this.validateValues(messages);

            if (messages.length != 0) {
                if (messagingView) {
                    messagingView.addErrors(messages);
                    messagingView.displayMessages();
                }
                return false;
            } else {
                return true;
            }
        },

        setValue: function(v) {
            this.getFormInput().val(v);
        },

        getValue: function() {
            return this.getFormInput().val();
        },

        setSecondValue: function(v) {
            this.getSecondFormInput().val(v);
        },

        getSecondValue: function() {
            return this.getSecondFormInput().val();
        },

        setOperator: function(o) {
            this.getOperatorSelect().val(o);
        },

        getOperator: function() {
            return this.getOperatorSelect().val();
        },

        getOperatorSelect: function() {
            if (!this.operatorSelect) {
                this.operatorSelect = $('<select tabindex="100" class="form-control"></select>');
                this.operatorSelect.uniqueId();
            }

            return this.operatorSelect;
        },

        getOperatorView: function() {
            if (!this.operatorView) {
                this.operatorView = $('<div class="form-group"></div>');
                if (this.operators) {
                    var _this = this;
                    $.each(this.operators, function(i, o) {
                         _this.getOperatorSelect().append('<option value="' + i + '" class="bldr-slct-opt">' + o + '</option>');
                    });
                }

                if (this.object && this.object.operator) {
                    this.getOperatorSelect().val(this.object.getOperator());
                }

                this.operatorView.append(this.getOperatorLabel(), this.getOperatorSelect());
            }

            return this.operatorView;
        },

        operatorChanged: function(operator) {
            if (!GD.OperatorFactory.isParameterOperator(operator) || !operator) {
                this.hideForm();
                this.hideSecondForm();
            } else {
                this.showForm();
                if (GD.OperatorFactory.isRangeOperator(operator)) {
                    this.showSecondForm();
                } else {
                    this.hideSecondForm();
                }
            }
        },

        getOperatorLabel: function() {
            if (!this.operatorLabel) {
                this.operatorLabel = $('<label class="control-label"></label>');
                this.operatorLabel.attr('for', this.getOperatorSelect().attr('id'));
                this.operatorLabel.text(this.operatorText);
            }

            return this.operatorLabel;
        },

        getFormContainer: function() {
            if (!this.formContainer) {
                this.formContainer = $('<div class="bldr-flt-frm"></div>');
                this.formContainer.append(this.getOperatorView(), this.getForm(), this.getSecondForm());

                if (this.object) {
                    this.initForms();
                }
            }

            return this.formContainer;
        },

        initForms: function() {
            if (this.object.getOperator()) {
                if (!GD.OperatorFactory.isParameterOperator(this.object.getOperator())) {
                    this.hideForm();
                    this.hideSecondForm();
                } else {
                    this.showForm();
                    if (GD.OperatorFactory.isRangeOperator(this.object.getOperator())) {
                        this.showSecondForm();
                    } else {
                        this.hideSecondForm();
                    }
                }
            } else {
                this.hideForm();
                this.hideSecondForm();
            }
        },

        getFormInput: function() {
            if (!this.formInput) {
                this.formInput = $('<input tabindex="100" type="text" class="form-control"/>');
                this.formInput.uniqueId();

                if (this.object) {
                    var val = this.object.getValue();
                    if (val) {
                        this.formInput.val(val[0]);
                    }
                }
            }

            return this.formInput;
        },

        getForm: function() {
            if (!this.form) {
                this.form = $('<div class="bldr-flt-frm-val form-group"></div>');
                var l = $('<label class="control-label">Value: </label>');
                l.attr('for', this.getFormInput().attr('id'));
                this.form.append(l, this.getFormInput());
            }

            return this.form;
        },

        getSecondFormInput: function() {
            if (!this.secondFormInput) {
                this.secondFormInput = $('<input tabindex="100" type="text" class="form-control"/>');
                this.secondFormInput.uniqueId();

                if (this.object) {
                    var val = this.object.getValue();
                    if (val) {
                        this.secondFormInput.val(val[1]);
                    }
                }
            }

            return this.secondFormInput;
        },

        getSecondForm: function() {
            if (!this.secondForm) {
                this.secondForm = $('<div class="bldr-flt-frm-val form-group"></div>');
                var l = $('<label class="control-label">Value: </label>');
                l.attr('for', this.getSecondFormInput().attr('id'));
                this.secondForm.append(l, this.getSecondFormInput());
            }

            return this.secondForm;
        },

        hideForm: function() {
            this.getForm().hide();
        },

        showForm: function() {
            this.getForm().show();
        },

        hideSecondForm: function() {
            this.getSecondForm().hide();
        },

        showSecondForm: function() {
            this.getSecondForm().show();
        },

        render: function() {
            if (this.container) {
                this.container.append(this.getFormContainer());
            }

            this.attachEventHandlers();

            return this.getFormContainer();
        },

        show: function() {
            this.getFormContainer().show();
            if (this.getOperator()) {
                if (GD.OperatorFactory.isParameterOperator(this.getOperator())) {
                    if (GD.OperatorFactory.isRangeOperator(this.getOperator())) {
                        this.showSecondForm();
                    } else {
                        this.showForm();
                    }
                }
            }
        },

        hide: function() {
            this.getFormContainer().hide();
            this.hideForm();
            this.hideSecondForm();
        },

        attachEventHandlers: function() {
            var _this = this;
            this.getOperatorSelect().change(function() {
                _this.operatorChanged($(this).val());
            });
        },

        reinitialize: function() {
            this.attachEventHandlers();
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);
