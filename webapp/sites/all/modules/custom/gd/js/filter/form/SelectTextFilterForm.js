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
        throw new Error('SelectTextFilterForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('SelectTextFilterForm requires GD');
    }

    var GD = global.GD;

    global.GD.SelectTextFilterForm = GD.FilterForm.extend({
        selectInput: null,
        selectOptions: null,
        secondSelectInput: null,

        init: function(object, container, options) {
            this._super(object, container, options);
            this.selectInput = null;
            this.secondSelectInput = null;

            if (options) {
                this.selectOptions = options['options'];
            }
        },

        getPlaceholder: function() {
            return this.options ? this.options['placeholder'] : '';
        },

        getSecondValue: function() {
            if (!this.getSecondSelectValue() || !this.getSecondTextValue()) {
                return null;
            }

            return [this.getSecondSelectValue(), this.getSecondTextValue()];
        },

        getSecondSelectValue: function() {
            return this.getSecondSelectInput().val();
        },

        getSecondTextValue: function() {
            return this.getSecondFormInput().val();
        },

        getValue: function() {
            if (!this.getSelectValue() || !this.getTextValue()) {
                return null;
            }

            return [this.getSelectValue(), this.getTextValue()];
        },

        getSelectValue: function() {
            return this.getSelectInput().val();
        },

        getTextValue: function() {
            return this.getFormInput().val();
        },

        getFormInput: function() {
            if (!this.formInput) {
                this.formInput = $('<input tabindex="100" type="text" class="form-control bldr-flt-frm-slt-txt"/>');
                this.formInput.uniqueId();
            }

            return this.formInput;
        },

        getSelectInput: function() {
            if (!this.selectInput) {
                this.selectInput = $('<select tabindex="100" class="form-control"></select>');
                this.selectInput.uniqueId();
            }

            return this.selectInput;
        },

        getSecondFormInput: function() {
            if (!this.secondFormInput) {
                this.secondFormInput = $('<input tabindex="100" type="text" class="form-control bldr-flt-frm-slt-txt"/>');
                this.secondFormInput.uniqueId();
                this.secondFormInput.attr('placeholder', this.getPlaceholder());
            }

            return this.secondFormInput;
        },

        getSecondSelectInput: function() {
            if (!this.secondSelectInput) {
                this.secondSelectInput = $('<select tabindex="100" class="form-control"></select>');
                this.secondSelectInput.uniqueId();
            }

            return this.secondSelectInput;
        },

        getForm: function() {
            if (!this.form) {
                this.form = $('<div class="bldr-flt-frm-val form-group"></div>');
                if (this.selectOptions) {
                    this.setOptions();
                } else {
                    this.getSelectInput().prop('disabled', true);
                    this.getFormInput().prop('disabled', true);
                }

                var l = $('<label class="control-label">Month: </label>');
                l.attr('for', this.getSelectInput().attr('id'));
                var l2 = $('<label class="control-label">Year: </label>');
                l2.attr('for', this.getFormInput().attr('id'));
                this.form.append(l, this.getSelectInput(), l2, this.getFormInput());
            }

            return this.form;
        },

        getSecondForm: function() {
            if (!this.secondForm) {
                this.secondForm = $('<div class="bldr-flt-frm-val"></div>');
                if (!this.selectOptions) {
                    this.getSecondSelectInput().prop('disabled', true);
                    this.getSecondFormInput().prop('disabled', true);
                }

                var l = $('<label class="control-label">Month: </label>');
                l.attr('for', this.getSecondSelectInput().attr('id'));
                var l2 = $('<label class="control-label">Year: </label>');
                l2.attr('for', this.getSecondFormInput().attr('id'));
                this.secondForm.append(l, this.getSecondSelectInput(), l2, this.getSecondFormInput());
            }

            return this.secondForm;
        },

        setOptions: function(options) {
            if (options) {
                this.selectOptions = options;
            }

            this.getFormInput().removeAttr('disabled');
            this.getSelectInput().removeAttr('disabled');
            this.getSecondFormInput().removeAttr('disabled');
            this.getSecondSelectInput().removeAttr('disabled');

            var _this = this;
            $.each(this.selectOptions, function(i, o) {
                _this.getSelectInput().append('<option value="' + o + '">' + o + '</option>');
                _this.getSecondSelectInput().append('<option value="' + o + '">' + o + '</option>');
            });
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);
