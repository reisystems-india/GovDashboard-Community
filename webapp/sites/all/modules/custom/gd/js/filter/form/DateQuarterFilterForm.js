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
        throw new Error('DateQuarterFilterForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DateQuarterFilterForm requires GD');
    }

    var GD = global.GD;

    global.GD.DateQuarterFilterForm = GD.SelectTextFilterForm.extend({
        init: function(object, container, options) {
            this._super(object, container, options);
            this.selectOptions = GD.Utility.getQuarterCodes();
        },

        setValue: function(v) {
            this.getSelectInput().val(GD.Utility.getQuarterCode(v));
            this.getFormInput().val(GD.Utility.getYear(v));
        },

        setSecondValue: function(v) {
            this.getSecondSelectInput().val(GD.Utility.getQuarterCode(v));
            this.getSecondFormInput().val(GD.Utility.getYear(v));
        },

        getValue: function() {
            if (!this.getSelectValue() || !this.getTextValue()) {
                return null;
            }

            return GD.Utility.convertQuarterToDate(this.getSelectValue(),this.getTextValue());
        },

        getSecondValue: function() {
            if (!this.getSecondSelectValue() || !this.getSecondTextValue()) {
                return null;
            }

            return GD.Utility.convertQuarterToDate(this.getSecondSelectValue(),this.getSecondTextValue());
        },

        getFormInput: function() {
            if (!this.formInput) {
                this._super();

                if (this.object) {
                    var val = this.object.getValue();
                    if (val && val[0]) {
                        this.formInput.val(GD.Utility.getYear(val[0]));
                    }
                }
            }

            return this.formInput;
        },

        getSecondFormInput: function() {
            if (!this.secondFormInput) {
                this._super();

                if (this.object) {
                    var val = this.object.getValue();
                    if (val && val[1]) {
                        this.secondFormInput.val(GD.Utility.getYear(val[1]));
                    }
                }
            }

            return this.secondFormInput;
        },

        setOptions: function(options) {
            this._super(options);

            if (this.object) {
                var val = this.object.getValue();
                if (val) {
                    if (val[0]) {
                        this.getSelectInput().val(GD.Utility.getQuarterCode(val[0]));
                    }

                    if (val[1]) {
                        this.getSecondSelectInput().val(GD.Utility.getQuarterCode(val[1]));
                    }
                }
            }
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);
