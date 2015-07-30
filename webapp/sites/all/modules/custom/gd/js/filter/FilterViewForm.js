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
        throw new Error('FilterViewForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('FilterViewForm requires GD');
    }

    var GD = global.GD;

    global.GD.FilterViewForm = GD.View.extend({
        formButton: null,
        formContainer: null,
        actionButtons: null,
        cancelButton: null,
        applyButton: null,
        clearButton: null,
        filterForm: null,

        init: function(object, container, options) {
            this._super(object, container, options);
            this.initVariables();
        },

        initVariables: function() {
            this.formButton = null;
            this.formContainer = null;
            this.actionButtons = null;
            this.cancelButton = null;
            this.applyButton = null;
            this.clearButton = null;
            this.filterForm = null;

            if (this.options) {
                this.setFilterForm(this.options['filterForm']);
            }
        },

        setFilterForm: function(form) {
            this.filterForm = form;
        },

        getFormContainer: function() {
            if (!this.formContainer) {
                this.formContainer = $('<div class="flt-vw-frm clearfix"></div>');
                this.formContainer.append(this.getFilterFormContainer());
                this.formContainer.append(this.getActionButtons());
            }

            return this.formContainer;
        },

        getFilterFormContainer: function() {
            if (!this.filterFormContainer) {
                this.filterFormContainer = $('<div class="flt-vw-frm-cntnr"></div>');

                if (this.filterForm) {
                    this.filterFormContainer.append(this.filterForm.render());
                }
            }

            return this.filterFormContainer;
        },

        getClearButton: function() {
            if (!this.clearButton) {
                this.clearButton = $('<button tabindex="100" type="button" class="vw-act-btn flt-vw-act-btn btn btn-default">Clear</button>');
                this.clearButton.css('margin-right', '10px');            
            }

            return this.clearButton;
        },

        getCancelButton: function() {
            if (!this.cancelButton) {
                this.cancelButton = $('<button tabindex="100" type="button" class="vw-act-btn flt-vw-act-btn btn btn-default">Cancel</button>');
            }

            return this.cancelButton;
        },

        getApplyButton: function() {
            if (!this.applyButton) {
                this.applyButton = $('<button tabindex="100" type="button" class="vw-act-btn flt-vw-act-btn btn btn-primary">Apply</button>');
            }

            return this.applyButton;
        },

        getActionButtons: function() {
            if (!this.actionButtons) {
                this.actionButtons = $('<div class="pull-right vw-act-btn-cntr flt-vw-act-cntr"></div>');
                this.actionButtons.append(this.getApplyButton());
                this.actionButtons.append(this.getClearButton());
                this.actionButtons.append(this.getCancelButton());
            }

            return this.actionButtons;
        },

        addForm: function() {
            if (this.container) {
                $(this.container).find('div.popover-content').append(this.getFormContainer());
            } else {
                $('#' + this.getFilterButton().attr('aria-describedby')).find('div.popover-content').append(this.getFormContainer());
            }
            this.filterForm.reinitialize();
            this.attachEventHandlers();
        },

        getFilterButton: function() {
            if (!this.filterButton) {
                this.filterButton = $('<button tabindex="100" class="btn btn-default flt-btn">'+this.object.getName()+'</button>');

                var _this = this;
                var po = this.filterButton.popover({
                    placement: 'bottom',
                    html: true,
                    content: '<div class="flt-vw-cntr" style="width:275px;"></div>'
                });

                this.filterButton.on('shown.bs.popover', function() {
                    _this.addForm();
                });

                this.filterButton.on('show.bs.popover', function() {
                    $(document).trigger({
                        type: 'shown.filter.view',
                        filter: _this.object
                    });
                });

                this.filterButton.on('hide.bs.popover', function() {
                    $(document).trigger({
                        type: 'hidden.filter.view',
                        filter: _this.object
                    });
                });
            }

            return this.filterButton;
        },

        render: function(refresh) {
            if (refresh) {
                this.initVariables();
            }

            if (this.container) {
                this.container.append(this.getFilterButton());
            }

            return this.getFilterButton();
        },

        attachEventHandlers: function() {
            var _this = this;

            this.getApplyButton().off('click').click(function() {
                _this.applyButtonClicked();
            });

            this.getCancelButton().off('click').click(function() {
                _this.cancelButtonClicked();
            });

            this.getClearButton().off('click').click(function() {
                _this.clearButtonClicked();
            })
        },

        clearButtonClicked: function() {
            var uri = new GD.Util.UriHandler();
            uri.removeFilter(this.object['name'], this.options['dashboard']);
            uri.redirect();
        },

        cancelButtonClicked: function() {
            this.hide();
            var uri = new GD.Util.UriHandler();
            var f = uri.getFilterInfo(this.object['name'], this.options['dashboard']);
            if (f) {
                this.setOperator(f['operator']);
                this.setValue(f['value']);
            }
            this.getFilterButton().focus();
        },

        toggleFormError:function(show, form, errormsg, operator){
            if(show){
                form.append($('<div class="help-block">'+errormsg+'</div>'));
            }else{
                form.find('.help-block').each(function(){
                    $(this).remove();
                })
            }
        },

        applyButtonClicked: function() {
            var errorMessages = {
                operator:[],
                value:[]
            };
            this.filterForm.validateOperator(errorMessages.operator);
            var operatorError = errorMessages.operator.length;
            if( operatorError === 0){
                this.filterForm.validateValues(errorMessages.value);
            }
            if(!operatorError && errorMessages.value.length === 0){
                this.toggleFormError(false,this.filterFormContainer);
                var uri = new GD.Util.UriHandler();
                uri.addFilter({name: this.object['name'], operator: {name: this.getOperator()}, value: this.getValue()}, this.options['dashboard']);
                uri.redirect();
            }else{
                this.toggleFormError(false,this.filterFormContainer);
                if(operatorError){
                    this.toggleFormError(true,this.filterFormContainer.find('.form-group').eq(0), errorMessages.operator[0]);
                }else{
                    for(var i in errorMessages.value){
                        if(this.filterFormContainer.find('.bldr-flt-frm-val.has-error').eq(i)){
                            this.toggleFormError(true,this.filterFormContainer.find('.bldr-flt-frm-val.has-error').eq(i), errorMessages.value[i]);
                        }else{
                            this.toggleFormError(true,this.filterFormContainer.find('.bldr-flt-frm-val.has-error'), errorMessages.value[i]);
                        }

                    }
                }
            }
          },

        setOperator: function(o) {
            if (this.filterForm) {
                this.filterForm.setOperator(o);
            }
        },

        setValue: function(v) {
            if (GD.OperatorFactory.isParameterOperator(this.getOperator())) {
                if (GD.OperatorFactory.isRangeOperator(this.getOperator())) {
                    this.filterForm.setValue(v[0]);
                    this.filterForm.setSecondValue(v[1]);
                } else {
                    this.filterForm.setValue(v);
                }
            } else {
                this.filterForm.setValue(null);
                this.filterForm.setSecondValue(null);
            }
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

        show: function() {
            this.getFilterButton().popover('show');
        },

        hide: function() {
            this.getFilterButton().popover('hide');
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);
