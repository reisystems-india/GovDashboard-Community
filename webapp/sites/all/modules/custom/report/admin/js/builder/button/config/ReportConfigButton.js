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
        throw new Error('ReportConfigButton requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportConfigButton requires GD');
    }

    var GD = global.GD;

    global.GD.ReportConfigButton = GD.BuilderConfigButton.extend({

        //  Take care of storing the model
        init: function(object, options) {

            this._super(object, options);
            if (this.getController().getReport().isNew()) {
                this.disable();
            }

           //var original = parseFloat(this.form.css('top').substr(0, this.form.css('top').length - 2));
            var original = 161;
            var _this = this;
            $(document).on('bldr.mssgng.display', function(e, i) {
                _this.form.css('top', original + i);
            });
            this.initForm();
        },

        initForm: function() {},

        disable: function() {
            this.button.addClass('disabled');
        },

        enable: function() {
            this.button.removeClass('disabled');
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);