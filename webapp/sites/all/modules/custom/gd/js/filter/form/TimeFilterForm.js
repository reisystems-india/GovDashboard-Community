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
        throw new Error('TimeFilterForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('TimeFilterForm requires GD');
    }

    var GD = global.GD;

    global.GD.TimeFilterForm = GD.FilterForm.extend({
        validateValues: function(messages) {
            this._super(messages);
            if (this.getValue() && !GD.Utility.isValidTime(this.getValue())) {
                messages.push('Please enter a valid value.');
                this.toggleFormError();
            } else {
                this.toggleFormError(false);
            }

            if (GD.OperatorFactory.isRangeOperator(this.getOperator()) && this.getSecondValue()) {
                if (!GD.Utility.isValidTime(this.getSecondValue())) {
                    messages.push('Please enter a valid value.');
                    this.toggleSecondFormError();
                } else if (this.getValue()) {
                    if (GD.Utility.isAfter(this.getValue(), this.getSecondValue())) {
                        messages.push('Please enter a valid time range.');
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
                return m.format('HH:mm:ss');
            }

            return null;
        },

        getSecondValue: function() {
            var m = this.getSecondFormInput().data("DateTimePicker").getDate();
            if (m) {
                return m.format('HH:mm:ss');
            }

            return null;
        },

        setValue: function(v) {
            if (v) {
                v = GD.Utility.formatTime(v);
            }
            this.getFormInput().data("DateTimePicker").setDate(v);
        },

        setSecondValue: function(v) {
            if (v) {
                v = GD.Utility.formatTime(v);
            }
            this.getSecondFormInput().data("DateTimePicker").setDate(v);
        },

        getFormInput: function() {
            if (!this.formInput) {
                this.formInput = $('<div class="input-group date"></div>');
                this.formInput.append('<input tabindex="100" type="text" class="form-control" />');
                this.formInput.append('<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>');
                this.formInput.datetimepicker({
                    defaultDate: null,
                    pickDate: false,
                    useSeconds: true
                });
            }

            return this.formInput;
        },

        getSecondFormInput: function() {
            if (!this.secondFormInput) {
                this.secondFormInput = $('<div class="input-group date"></div>');
                this.secondFormInput.append('<input tabindex="100" type="text" class="form-control" />');
                this.secondFormInput.append('<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>');
                this.secondFormInput.datetimepicker({
                    defaultDate: null,
                    pickDate: false,
                    useSeconds: true
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
                pickDate: false,
                useSeconds: true
            });

            this.getSecondFormInput().datetimepicker({
                defaultDate: null,
                pickDate: false,
                useSeconds: true
            });
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);
