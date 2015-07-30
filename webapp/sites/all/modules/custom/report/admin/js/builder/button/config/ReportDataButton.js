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
        throw new Error('ReportDataButton requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportDataButton requires GD');
    }

    var GD = global.GD;

    global.GD.ReportDataButton = GD.ReportConfigButton.extend({

        init: function(object, options) {
            this._super({'button': '#reportDataButton','form': '#reportDataForm'}, options);
            this.form.css('top', '31px');
            this.form.css('left', '15px');

            this.initForm();
        },

        initForm: function() {
            var reportObj = this.getController().getReport();
            if ( !reportObj.isNew() ) {
                this.dataList = new GD.ReportDataList(null,this.form,this.options);
            } else {
                this.dataForm = new GD.ReportDataForm(null,this.form,this.options);
            }
        },

        openForm: function() {
            this._super();
            var reportObj = this.getController().getReport();
            if ( !reportObj.isNew() ) {
                this.dataList.render();
            } else {
                this.dataForm.loadData();
                this.dataForm.render();
            }
        },

        attachEventHandlers: function() {
            this._super();

            var _this = this;
            $(document).on('changed.report.dataset', function() {
                _this.closeForm();
            });

            $(document).on('changed.report.type', function() {
                 _this.closeForm();
            });
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);