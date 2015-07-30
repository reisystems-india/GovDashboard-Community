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
        throw new Error('DateFilterForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DateFilterForm requires GD');
    }

    var GD = global.GD;

    global.GD.DateFilterForm = GD.FilterForm.extend({
        textInput: null,
        secondTextInput: null,

        init: function(object, container, options) {
            this._super(object, container, options);
            this.textInput = null;
            this.secondTextInput = null;
        },

        validateValues: function(messages) {
            this._super(messages);
            if(this.getValue() ){
                if (!GD.Utility.isValidDate(this.getValue())) {
                    messages.push('Please enter a valid value.');
                    this.toggleFormError();
                } else {
                    this.toggleFormError(false);
                }

            }

            if (GD.OperatorFactory.isRangeOperator(this.getOperator()) && this.getSecondValue()) {
                if (!GD.Utility.isValidDate(this.getSecondValue())) {
                    messages.push('Please enter a valid value.');
                    this.toggleSecondFormError();
                } else if (this.getValue()) {
                    if (GD.Utility.isAfter(this.getValue(), this.getSecondValue())) {
                        messages.push('Please enter a valid date range.');
                        this.toggleFormError();
                        this.toggleSecondFormError();
                    } else {
                        this.toggleFormError(false);
                        this.toggleSecondFormError(false);
                    }
                }
            }
        },

        getValue: function() {
            var m = this.getFormInput().data("DateTimePicker").getDate();
            if (m) {
                return m.format('YYYY-MM-DD');
            }

            return null;
        },

        getSecondValue: function() {
            var m = this.getSecondFormInput().data("DateTimePicker").getDate();
            if (m) {
                return m.format('YYYY-MM-DD');
            }

            return null;
        },

        setValue: function(v) {
            if (v) {
                v = GD.Utility.formatDate(v);
            }
            this.getFormInput().data("DateTimePicker").setDate(v);
        },

        setSecondValue: function(v) {
            if (v) {
                v = GD.Utility.formatDate(v);
            }
            this.getSecondFormInput().data("DateTimePicker").setDate(v);
        },

        getForm: function() {
            if (!this.form) {
                this.form = $('<div class="bldr-flt-frm-val form-group"></div>');
                var l = $('<label class="control-label">Value: </label>');
                l.attr('for', this.getTextInput().attr('id'));
                this.form.append(l, this.getFormInput());
            }

            return this.form;
        },

        getTextInput: function() {
            if (!this.textInput) {
                this.textInput = $('<input tabindex="100" type="text" class="form-control" />');
                this.textInput.uniqueId();
            }

            return this.textInput;
        },

        getFormInput: function() {
            if (!this.formInput) {
                this.formInput = $('<div class="input-group date"></div>');
                this.formInput.append(this.getTextInput(), '<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>');
                this.formInput.datetimepicker({
                    defaultDate: null,
                    pickTime: false
                });
            }

            return this.formInput;
        },

        getSecondForm: function() {
            if (!this.secondForm) {
                this.secondForm = $('<div class="bldr-flt-frm-val form-group"></div>');
                var l = $('<label class="control-label">Value: </label>');
                l.attr('for', this.getSecondTextInput().attr('id'));
                this.secondForm.append(l, this.getSecondFormInput());
            }

            return this.secondForm;
        },

        getSecondTextInput: function() {
            if (!this.secondTextInput) {
                this.secondTextInput = $('<input tabindex="100" type="text" class="form-control" />');
                this.secondTextInput.uniqueId();
            }

            return this.secondTextInput;
        },

        getSecondFormInput: function() {
            if (!this.secondFormInput) {
                this.secondFormInput = $('<div class="input-group date"></div>');
                this.secondFormInput.append(this.getSecondTextInput, '<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>');
                this.secondFormInput.datetimepicker({
                    defaultDate: null,
                    pickTime: false
                });
            }

            return this.secondFormInput;
        },

        render: function() {
            var r = this._super();

            if (this.object) {
                var val = this.object.getValue();
                if (val) {
                    if (val[0]) {
                        this.setValue(val[0]);
                    }

                    if (val[1]) {
                        this.setSecondValue(val[1]);
                    }
                }
            }

            return r;
        },

        reinitialize: function() {
            this._super();

            this.getFormInput().datetimepicker({
                defaultDate: null,
                pickTime: false
            });

            this.getSecondFormInput().datetimepicker({
                defaultDate: null,
                pickTime: false
            });
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);
