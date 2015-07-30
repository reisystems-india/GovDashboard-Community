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
        throw new Error('BuilderConfigButton requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('BuilderConfigButton requires GD');
    }

    var GD = global.GD;

    global.GD.BuilderConfigButton = GD.BuilderButton.extend({
        options: null,
        form: null,

        init: function(object, options) {
            if (object) {
                this.setForm(object['form']);
            }

            this._super(object, options);
        },

        setForm: function(id) {
            this.form = id ? $(id) : null;
        },

        clickedButton: function(e) {
            if (this.form.hasClass('open')) {
                this.closeForm();
            } else {
                this.openForm();
            }
        },

        openForm: function() {
            this.button.addClass('active');
            this.form.addClass('open');
            this.form.show();

            //  Trigger event when config form is opened
            $('div.dsb-frm, div.rpt-frm').trigger({
                type: 'configFormOpen',
                form: this.form
            });
        },

        closeForm: function() {
            this.button.removeClass('active');
            this.form.removeClass('open');
            this.form.hide();
        },

        isChild: function(target) {
            return ( $(target).parents('div.dsb-frm, button.dsb-cnf-btn').length > 0 ||
                     $(target).parents('div.rpt-frm, button.rpt-cnf-btn').length > 0 );
        },

        isThisWidget: function(target) {
            //  Proper usage of identity operator. Don't alter to equality!
            return target === this.form.get(0) || target === this.button.get(0) || this.isChild(target);
        },

        attachEventHandlers: function() {
            this._super();

            var _this = this;

            $("body").click(function(e) {
                if (_this.button.hasClass("active") && $.contains(document, e.target)) {
                    if (!_this.form.parent().find(e.target).length) {
                        _this.closeForm();
                    }
                }
            });

            $(".report-config").focusin(function() {
                if (_this.button.hasClass("active")) {
                    if (!_this.form.parent().find(document.activeElement).length && !_this.form.parent().is(document.activeElement)) {
                        _this.closeForm();
                    }
                }
            });

            this.form.off('configFormOpen').on('configFormOpen', function(e) {
                if (e.form !== _this.form) {
                    _this.closeForm();
                }
            });
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);