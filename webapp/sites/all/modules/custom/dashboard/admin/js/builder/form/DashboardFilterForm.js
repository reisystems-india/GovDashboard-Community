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
        throw new Error('DashboardFilterForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DashboardFilterForm requires GD');
    }

    var GD = global.GD;

    global.GD.DashboardFilterForm = GD.View.extend({
        formContainer: null,
        actionButtons: null,
        cancelButton: null,
        applyButton: null,
        deleteButton: null,
        filterForm: null,
        exposedContainer: null,
        exposedTypeContainer: null,
//        customPresentationContainer: null,
//        presentationType: null,
//        presentationOperator: null,
//        customValuesContainer: null,
//        customValuesForm: null,
//        customSliderContainer: null,
//        customMinForm: null,
//        customMaxForm: null,
//        customStepForm: null,
//        customMinFormInput: null,
//        customMaxFormInput: null,
//        customStepFormInput: null,
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
            this.exposedTypeContainer = null;
//            this.customPresentationContainer = null;
//            this.presentationType = null;
//            this.presentationOperator = null;
//            this.customValuesContainer = null;
//            this.customValuesForm = null;
//            this.customValueList = null;
//            this.customSliderContainer = null;
//            this.customMinForm = null;
//            this.customMaxForm = null;
//            this.customStepForm = null;
//            this.customMinFormInput = null;
//            this.customMaxFormInput = null;
//            this.customStepFormInput = null;
            this.modal = null;

            if (this.options) {
                this.setFilterForm(this.options['filterForm']);
            }
        },

        validate: function() {
            var val = this.filterForm ? this.filterForm.validate(GD.DashboardBuilderMessagingView) : true;

            if (this.getExposed() != 0) {
                if (this.getExposedType() == 'custom') {
                    if (GD.Filter.isSliderPresentation(this.getPresentationType())) {
                        if (this.getMinValue() == '' || isNaN(this.getMinValue())) {
                            val = false;
                            GD.DashboardBuilderMessagingView.addErrors('Slider presentation mode requires a valid min value.');
                            this.getMinForm().toggleClass('has-error', true);
                        } else {
                            this.getMinForm().toggleClass('has-error', false);
                        }

                        if (this.getMaxValue() == '' || isNaN(this.getMaxValue())) {
                            val = false;
                            GD.DashboardBuilderMessagingView.addErrors('Slider presentation mode requires a valid max value.');
                            this.getMaxForm().toggleClass('has-error', true);
                        } else {
                            this.getMaxForm().toggleClass('has-error', false);
                        }

                        if (this.getStepValue() == '' || isNaN(this.getStepValue())) {
                            val = false;
                            GD.DashboardBuilderMessagingView.addErrors('Slider presentation mode requires a valid step value.');
                            this.getStepForm().toggleClass('has-error', true);
                        } else {
                            this.getStepForm().toggleClass('has-error', false);
                        }
                    } else {
                        var vals = this.getCustomValues();
                        if (vals.length == 0) {
                            val = false;
                            GD.DashboardBuilderMessagingView.addErrors('Custom presentation mode requires a list of values.');
                            this.getCustomValuesForm().toggleClass('has-error', true);
                        } else {
                            this.getCustomValuesForm().toggleClass('has-error', false);
                        }
                    }
                }
            } else {
                if (!GD.OperatorFactory.isOperator(this.filterForm.getOperator())) {
                    val = false;
                    GD.DashboardBuilderMessagingView.addErrors('Please select a filter operator.');
                    this.filterForm.toggleOperatorError();
                } else {
                    this.filterForm.toggleOperatorError(false);
                }
            }

            if (!val) {
                GD.DashboardBuilderMessagingView.displayMessages(true);
            }

            return val;
        },

        setFilterForm: function(form) {
            this.filterForm = form;
        },

        getActionButtons: function() {
            if (!this.actionButtons) {
                this.actionButtons = $('<div class="clearfix dsb-act-btn-cntr dsb-flt-act-cntr pull-right"></div>');
                this.applyButton = $('<button type="button" class="dsb-act-btn dsb-flt-act-btn btn btn-primary">Apply</button>');
                this.deleteButton = $('<button type="button" class="dsb-act-btn dsb-flt-act-btn btn btn-default">Delete</button>');
                this.cancelButton = $('<button type="button" class="dsb-act-btn dsb-flt-act-btn btn btn-default">Cancel</button>');
               
                this.actionButtons.append(this.applyButton);
                if (!this.isNew) {
                    this.actionButtons.append(this.deleteButton);
                }
                this.actionButtons.append(this.cancelButton);
            }

            return this.actionButtons;
        },

        getFormContainer: function() {
            if (!this.formContainer) {
                this.formContainer = $('<div class="dsb-flt-frm"></div>');
                var header = $('<h5 class="dsb-frm-hdr dsb-flt-frm-hdr"></h5>');
                if (!this.isNew) {
                    header.text('Edit filter: ' + this.object.name);
                } else {
                    header.text('Create Filter');
                }

                this.formContainer.append(header);

                if (this.filterForm) {
                    this.formContainer.append(this.filterForm.render());
                }

                this.formContainer.append(this.getExposedContainer());
//                this.formContainer.append(this.getExposedTypeContainer());
//                this.formContainer.append(this.getCustomPresentationContainer());
                this.formContainer.append(this.getActionButtons());
            }

            return this.formContainer;
        },

        getHiddenOption: function() {
            if (!this.hiddenOption) {
                this.hiddenOption = $('<input type="radio" name="exposedOptions" value="hidden">');
            }

            return this.hiddenOption;
        },

        getExposedOption: function() {
            if (!this.exposedOption){
                this.exposedOption = $('<input type="radio" name="exposedOptions" value="exposed">');
            }

            return this.exposedOption;
        },

        getExposedContainer: function() {
            if (!this.exposedContainer) {
                this.exposedContainer = $('<div class="dsb-flt-frm-xps-cnt"></div>');
                var c = $('<div class="radio"></div>');
                c.append($('<label></label>').append(this.getHiddenOption()).append('Hidden'));
                var d = $('<div class="radio"></div>');
                d.append($('<label></label>').append(this.getExposedOption()).append('Exposed'));

                if (this.object) {
                    if (this.object.getExposed()) {
                        this.getExposedOption().prop('checked', true);
                    } else {
                        this.getHiddenOption().prop('checked', true);
                    }
                }

                this.exposedContainer.append(c,d);
            }

            return this.exposedContainer;
        },

        getPossiblePresentationTypes: function() {
            if (!GD.DashboardFilterForm.possiblePresentation) {
                GD.DashboardFilterForm.possiblePresentation = {
                    'default': {
                        'select': 'select',
                        'radio': 'radio',
                        'checkbox': 'checkbox',
                        'slider': 'slider'
                    },
                    'string': {
                        'select': 'select',
                        'radio': 'radio',
                        'checkbox': 'checkbox'
                    },
                    'date:month': {
                        'select': 'select',
                        'radio': 'radio',
                        'slider:month': 'slider',
                        'checkbox': 'checkbox'
                    },
                    'date:quarter': {
                        'select': 'select',
                        'radio': 'radio',
                        'slider:quarter': 'slider',
                        'checkbox': 'checkbox'
                    }
                };
            }

            return GD.DashboardFilterForm.possiblePresentation[this.object.getType()] ?
                GD.DashboardFilterForm.possiblePresentation[this.object.getType()] :
                GD.DashboardFilterForm.possiblePresentation['default'];
        },

        getPresentationTypeForm: function() {
            if (!this.presentationType) {
                this.presentationType = $('<select class="form-control"></select>');
            }

            return this.presentationType;
        },

        getPresentationOperatorForm: function() {
            if (!this.presentationOperator) {
                this.presentationOperator = $('<select class="form-control"></select>');
            }

            return this.presentationOperator;
        },

        getCustomPresentationContainer: function() {
            if (!this.customPresentationContainer) {
                this.customPresentationContainer = $('<div class="form-horizontal" role="form"></div>');

                var types = this.getPossiblePresentationTypes();
                for (var key in types) {
                    this.getPresentationTypeForm().append('<option value="' + key + '">' + types[key] + '</option>');
                }
                var c = $('<div></div>');
                c.append('<label class="control-label">Presentation:</label>', this.getPresentationTypeForm());

                if (this.object.getPresentationType()) {
                    this.getPresentationTypeForm().val(this.object.getPresentationType());
                }

                var operators = GD.OperatorFactory.getOperators(this.object.getType());
                this.getPresentationOperatorForm().append('<option value="all">all</option>');
                for(var key in operators) {
                    this.getPresentationOperatorForm().append('<option value="' + key + '">' + operators[key] + '</option>');
                }

                if (this.object.getPresentationOperator()) {
                    this.getPresentationOperatorForm().val(this.object.getPresentationOperator());
                }

                var _this = this;
                this.getPresentationTypeForm().change(function() {
                    if (GD.Filter.isSliderPresentation($(this).val())) {
                        _this.togglePresentationType(true);
                    } else {
                        _this.togglePresentationType(false);
                    }
                });

                this.getPresentationOperatorForm().change(function() {
                    var o = $(this).val();
                    if (o != 'all') {
                        if (!GD.OperatorFactory.isParameterOperator(o)) {
                            _this.hideCustomPresentation();
                        } else {
                            _this.showCustomPresentation();
                        }
                    } else {
                        _this.showCustomPresentation();
                    }
                });

                var d = $('<div></div>');
                d.append('<label class="control-label">Operator:</label>', this.getPresentationOperatorForm());

                this.customPresentationContainer.append(c,d);
                this.customPresentationContainer.append(this.getCustomValuesContainer(), this.getCustomSliderContainer());
            }

            return this.customPresentationContainer;
        },

        getExposedTypeContainer: function() {
            if (!this.exposedTypeContainer) {
                this.exposedTypeContainer = $('<div></div>');
                var c = $('<div class="radio"></div>');
                this.defaultOption = $('<input type="radio" name="exposedTypeOptions" value="default" checked>');
                c.append($('<label></label>').append(this.defaultOption).append('Default'));
                var d = $('<div class="radio"></div>');
                this.customOption = $('<input type="radio" name="exposedTypeOptions" value="custom">');
                d.append($('<label></label>').append(this.customOption).append('Custom'));

                if (this.object) {
                    if (this.object.getExposedType() == 'custom') {
                        this.customOption.prop('checked', true);
                        this.showCustomPresentation();
                    } else {
                        this.defaultOption.prop('checked', true);
                        this.hideCustomPresentation();
                    }

                    if (!this.object.getExposed()) {
                        this.exposedTypeContainer.css('display', 'none');
                        this.hideCustomPresentation();
                    }
                }

                this.exposedTypeContainer.append(c,d);
            }

            return this.exposedTypeContainer;
        },

        getMinFormInput: function() {
            if (!this.customMinFormInput) {
                this.customMinFormInput = $('<input type="text" class="form-control"/>');
                var options = this.object.getOptions();
                this.customMinFormInput.val(options ? (options['min'] ? options['min'] : null): null);
            }

            return this.customMinFormInput;
        },

        getMinForm: function() {
            if (!this.customMinForm) {
                this.customMinForm = $('<div class="dsb-flt-cstm-vals-frm form-group"></div>');
                this.customMinForm.append('<label class="control-label">Min: </label>', this.getMinFormInput());
            }

            return this.customMinForm;
        },

        getMaxFormInput: function() {
            if (!this.customMaxFormInput) {
                this.customMaxFormInput = $('<input type="text" class="form-control"/>');
                var options = this.object.getOptions();
                this.customMaxFormInput.val(options ? (options['max'] ? options['max'] : null): null);
            }

            return this.customMaxFormInput;
        },

        getMaxForm: function() {
            if (!this.customMaxForm) {
                this.customMaxForm = $('<div class="dsb-flt-cstm-vals-frm form-group"></div>');
                this.customMaxForm.append('<label class="control-label">Max: </label>', this.getMaxFormInput());
            }

            return this.customMaxForm;
        },

        getStepFormInput: function() {
            if (!this.customStepFormInput) {
                this.customStepFormInput = $('<input type="text" class="form-control"/>');
                var options = this.object.getOptions();
                this.customStepFormInput.val(options ? (options['step'] ? options['step'] : null): null);
            }

            return this.customStepFormInput;
        },

        getStepForm: function() {
            if (!this.customStepForm) {
                this.customStepForm = $('<div class="dsb-flt-cstm-vals-frm form-group"></div>');
                this.customStepForm.append('<label class="control-label">Step: </label>', this.getStepFormInput());
            }

            return this.customStepForm;
        },

        getCustomSliderContainer: function() {
            if (!this.customSliderContainer) {
                this.customSliderContainer = $('<div class="dsb-flt-cstm-cntr"></div>');
                this.customSliderContainer.append(this.getMinForm(), this.getMaxForm(), this.getStepForm());
            }

            return this.customSliderContainer;
        },

        getCustomValuesContainer: function() {
            if (!this.customValuesContainer) {
                this.customValuesContainer = $('<div class="dsb-flt-cstm-cntr"></div>');
                this.customValuesContainer.append(this.getCustomValuesForm(), this.getCustomValueList());
            }

            return this.customValuesContainer;
        },

        getCustomValuesForm: function() {
            if (!this.customValuesForm) {
                this.customValuesForm = $('<div class="dsb-flt-cstm-vals-frm form-group"></div>');
                var group = $('<div class="input-group"></div>');
                var addButton = $('<button class="btn btn-default">Add</button>');
                var textForm = $('<input type="text" class="form-control">');
                group.append(textForm, $('<span class="input-group-btn"></span>').append(addButton));

                var _this = this;
                addButton.click(function() {
                    _this.addCustomValue(textForm.val());
                    textForm.val('');
                });

                var options = this.object.getOptions();
                var values = options ? (options['values'] ? options['values'] : []): [];
                for (var key in values) {
                    this.addCustomValue(values[key]);
                }

                this.customValuesForm.append('<span class="glyphicon glyphicon-question-sign"></span><label class="control-label"> List of values is required</label>', group);
            }

            return this.customValuesForm;
        },

        addCustomValue: function(value) {
            var container = $('<div class="dsb-flt-cstm-vals-itm"></div>');
            var remove = $('<span class="dsb-flt-cstm-vals-itm-rmv glyphicon glyphicon-minus"></span>');
            remove.click(function() {
                container.remove();
            });
            var val = $('<span class="dsb-flt-cstm-vals-itm-txt"></span>');
            val.text(value);
            container.append(remove, val);
            this.getCustomValueList().append(container);
        },

        getCustomValueList: function() {
            if (!this.customValueList) {
                this.customValueList = $('<div class="dsb-flt-cstm-vals-lst"></div>');
            }

            return this.customValueList;
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

        getCustomValues: function() {
            var container = this.getCustomValueList();
            var values = [];

            container.find('span.dsb-flt-cstm-vals-itm-txt').each(function() {
                values.push($(this).text());
            });

            return values;
        },

        exposedChanged: function(exposed) {
            if (exposed) {
//                this.getExposedTypeContainer().show();
//                if (this.getExposedType() == 'custom') {
//                    this.showCustomPresentation();
//                } else {
//                    this.hideCustomPresentation();
//                }
            } else {
//                this.getExposedTypeContainer().hide();
//                this.hideCustomPresentation();
            }
        },

        exposedTypeChanged: function(type) {
            if (type == 'default') {
                this.hideCustomPresentation();
            } else {
                this.showCustomPresentation();
            }
        },

        togglePresentationType: function(slider) {
            if (slider) {
                this.getCustomValuesContainer().hide();
                this.getCustomSliderContainer().show();
            } else {
                this.getCustomValuesContainer().show();
                this.getCustomSliderContainer().hide();
            }
        },

        showCustomPresentation: function() {
            this.getCustomPresentationContainer().show();
            if (GD.Filter.isSliderPresentation(this.getPresentationTypeForm().val())) {
                this.togglePresentationType(true);
            } else {
                this.togglePresentationType(false);
            }
        },

        hideCustomPresentation: function() {
            this.getCustomPresentationContainer().hide();
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
                deleteButton.click(function() {
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
                callback();
            }
        },

        applyButtonClicked: function(callback) {
            GD.DashboardBuilderMessagingView.clearDisplay();
            if (this.validate()) {
                callback();
            }
        },

        attachEventHandlers: function(cancel, apply, del) {
            var _this = this;
            this.hiddenOption.change(function() {
                _this.exposedChanged(false);
            });

            this.exposedOption.change(function() {
                _this.exposedChanged(true);
            });

//            this.defaultOption.change(function() {
//                _this.exposedTypeChanged('default');
//            });
//
//            this.customOption.change(function() {
//                _this.exposedTypeChanged('custom');
//            });

            if (cancel && this.cancelButton) {
                this.cancelButton.click(function() {
                    cancel();
                });
            }

            if (apply && this.applyButton) {
                this.applyButton.click(function() {
                    _this.applyButtonClicked(apply);
                });
            }

            if (del && this.deleteButton) {
                this.deleteButton.click(function() {
                    _this.deleteButtonClicked(del);
                });
            }
        },

        getExposed: function() {
            return $('input:radio[name=exposedOptions]:checked').val() == 'exposed';
        },

        getExposedType: function() {
//            return $('input:radio[name=exposedTypeOptions]:checked').val();
            return "default";
        },

        getPresentationType: function() {
            return this.getPresentationTypeForm().val();
        },

        getPresentationOperator: function() {
            return this.getPresentationOperatorForm().val();
        },

        getMinValue: function() {
            return this.getMinFormInput().val();
        },

        getMaxValue: function() {
            return this.getMaxFormInput().val();
        },

        getStepValue: function() {
            return this.getStepFormInput().val();
        },

        getFilter: function() {
            var f = this.object;
            var o = this.filterForm.getOperator();

            if (GD.OperatorFactory.isOperator(o)) {
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

            f.setExposed(this.getExposed());
            f.setExposedType("default");
//            f.setExposedType(this.getExposedType());
//            if (this.getExposed() && this.getExposedType() == 'custom') {
//                var view = {};
//                view['operator'] = this.getPresentationOperator();
//                view['type'] = this.getPresentationType();
//                var options = null;
//                if (GD.Filter.isSliderPresentation(this.getPresentationType())) {
//                    options = {
//                        min: this.getMinValue(),
//                        max: this.getMaxValue(),
//                        step: this.getStepValue()
//                    };
//                } else {
//                    options = {
//                        values: this.getCustomValues()
//                    }
//                }
//                view['options'] = options;
//                f.setView(view);
//            } else {
                f.setView({});
//            }

            return f;
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);