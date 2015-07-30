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
        throw new Error('ReportFooterCreateForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportFooterCreateForm requires GD');
    }

    var GD = global.GD;

    var ReportFooterCreateForm = GD.View.extend({
        formContainer: null,
        actionContainer: null,
        nextButton: null,
        cancelButton: null,
        cancelCallback: null,

        init: function(object, container, options) {
            this._super(object, container, options);

            this.formContainer = null;
            this.actionContainer = null;
            this.nextButton = null;
            this.cancelButton = null;
            this.cancelCallback = null;
            this.nextCallback = null;
        },

        getController: function () {
            return this.options.builder;
        },

        getMeasureSelector: function () {
            if ( !this.columnSelector ) {
                this.columnSelector = $('<div class="list-group"></div>');
                this.loadMeasures();
            }

            return this.columnSelector;
        },

        loadMeasures: function () {
            var reportObj = this.getController().getReport(),
                measures = reportObj.getAvailableMeasures(true),
                usedMeasures = $.map(reportObj.getFooters(), function(f, i) { return f['measure']; });

            var _this = this;
            $.each(measures, function(i, measure) {
                if ($.inArray(measure['id'], usedMeasures) === -1) {
                    _this.getMeasureSelector().append('<a href="#" class="list-group-item" data-id="'+measure['id']+'">'+measure['name']+'</a>');
                }
            });

            this.getMeasureSelector().find('a').on('click',function(e){
                e.preventDefault();
                var measure = _this.getController().getReport().getColumn($(this).data('id'));
                if ( _this.nextCallback ) {
                    _this.nextCallback(measure['id'], measure['name']);
                }
            });
        },

        getCancelButton: function() {
            if (!this.cancelButton) {
                this.cancelButton = $('<button type="button" class="rpt-flt-act-btn btn btn-default btn-sm">Cancel</button>');
            }

            return this.cancelButton;
        },

        getActionButtons: function() {
            if (!this.actionContainer) {
                this.actionContainer = $('<div class="rpt-flt-act-cntr pull-right"></div>');
                this.actionContainer.append(this.getCancelButton());
            }

            return this.actionContainer;
        },

        getFormContainer: function() {
            if ( !this.formContainer ) {
                this.formContainer = $('<div></div>').append('<h5>Select a Measure</h5>', this.getMeasureSelector(), this.getActionButtons());
            }
            return this.formContainer;
        },

        render: function() {
            if ( this.container ) {
                this.container.append(this.getFormContainer());
            }

            return this.getFormContainer();
        },

        attachEventHandlers: function(cancel, next) {
            this.cancelCallback = cancel;
            this.getCancelButton().click(function() {
                if ( cancel ) {
                    cancel();
                }
            });

            this.nextCallback = next;
        }
    });

    GD.ReportFooterCreateForm = ReportFooterCreateForm;

})(typeof window === 'undefined' ? this : window, jQuery);