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
        throw new Error('SelectFilterForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('SelectFilterForm requires GD');
    }

    var GD = global.GD;

    global.GD.SelectFilterForm = GD.FilterForm.extend({
        selectOptions: null,

        init: function(object, container, options) {
            this._super(object, container, options);

            if (options) {
                this.selectOptions = options['options'];
            }
        },

        getFormInput: function() {
            if (!this.formInput) {
                this.formInput = $('<select tabindex="100" class="form-control"></select>');
                this.formInput.uniqueId();
            }

            return this.formInput;
        },

        getSecondFormInput: function() {
            if (!this.secondFormInput) {
                this.secondFormInput = $('<select tabindex="100" class="form-control"></select>');
                this.secondFormInput.uniqueId();
            }

            return this.secondFormInput;
        },

        getForm: function() {
            if (!this.form) {
                this.form = $('<div class="bldr-flt-frm-val"></div>');
                if (this.selectOptions) {
                    this.setOptions();
                } else {
                    this.getFormInput().prop('disabled', true);
                }

                var l = $('<label class="control-label">Value: </label>');
                l.attr('for', this.getFormInput().attr('id'));
                this.form.append(l, this.getFormInput());
            }

            return this.form;
        },

        getSecondForm: function() {
            if (!this.secondForm) {
                this.secondForm = $('<div class="bldr-flt-frm-val"></div>');
                if (!this.selectOptions) {
                    this.getSecondFormInput().prop('disabled', true);
                }

                var l = $('<label class="control-label">Value: </label>');
                l.attr('for', this.getSecondFormInput().attr('id'))
                this.secondForm.append(l, this.getSecondFormInput());
            }

            return this.secondForm;
        },

        setOptions: function(options) {
            if (options) {
                this.selectOptions = options;
            }

            this.getFormInput().removeAttr('disabled');
            this.getSecondFormInput().removeAttr('disabled');

            var _this = this;
            $.each(this.selectOptions, function(i, o) {
                _this.getFormInput().append('<option value="' + o + '">' + o + '</option>');
                _this.getSecondFormInput().append('<option value="' + o + '">' + o + '</option>');
            });

            if (this.object) {
                var val = this.object.getValue();
                if (val) {
                    if (val[0]) {
                        this.getFormInput().val(val[0]);
                    }

                    if (val[1]) {
                        this.getSecondFormInput().val(val[1]);
                    }
                }
            }
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);
