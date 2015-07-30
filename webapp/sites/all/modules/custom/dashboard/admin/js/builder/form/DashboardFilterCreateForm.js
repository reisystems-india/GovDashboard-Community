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
        throw new Error('DashboardFilterCreateForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DashboardFilterCreateForm requires GD');
    }

    var GD = global.GD;

    global.GD.DashboardFilterCreateForm = GD.View.extend({
        formContainer: null,
        reportListSelect: null,
        reportListLookup: null,
        datasource: null,
        actionContainer: null,
        nextButton: null,
        cancelButton: null,
        modal: null,
        cancelCallback: null,

        init: function(object, container, options) {
            this._super(object, container, options);

            this.formContainer = null;
            this.reportListSelect = null;
            this.reportListLookup = null;
            this.actionContainer = null;
            this.nextButton = null;
            this.cancelButton = null;
            this.modal = null;
            this.cancelCallback = null;

            if (options) {
                this.datasource = options['datasource'];
            }
        },

        getReportListSelect: function() {
            if (!this.reportListSelect) {
                this.reportListSelect = $('<select class="form-control"></select>');

                if (!this.reportListLookup) {
                    this.getFilterList();
                    this.reportListSelect.prop('disabled', true);
                }
            }

            return this.reportListSelect;
        },

        getFilterList: function() {
            this.getReportListSelect().empty();

            var _this = this;
            $.ajax({
                url: '/api/report.json',
                data: {
                    filter: {
                        datasource: this.datasource,
                        id: this.object.getReportIds().join(',')
                    },
                    fields: 'filters'
                },
                success: function(data) {
                    _this.parseData(data);
                    _this.renderList();
                }
            });
        },

        parseData: function(raw) {
            this.reportListLookup = {};

            if (raw) {
                for (var i = 0; i < raw.length; i++) {
                    for(var key in raw[i].filters) {
                        if (this.reportListLookup[raw[i].filters[key]['name']]) {
                            this.mergeFilters(raw[i].filters[key]);
                        } else {
                            if (raw[i].filters[key]['exposed']) {
                                this.reportListLookup[raw[i].filters[key]['name']] = raw[i].filters[key];
                            }
                        }
                    }
                }
            }
        },

        mergeFilters: function(filter) {
            if ($.isArray(this.reportListLookup[filter['name']]['column'])) {
                var exists = false;
                var columns = this.reportListLookup[filter['name']]['column'];
                for (var i = 0; i < columns.length; i++) {
                    if (filter['column']['datasetName'] == columns[i]['datasetName']) {
                        if (filter['column']['name'] == columns[i]['name']) {
                            exists = true;
                            break;
                        }
                    }
                }

                if (!exists) {
                    this.reportListLookup[filter['name']]['column'].push(filter['column']);
                }
            } else {
                var c = this.reportListLookup[filter['name']]['column'];
                if (filter['column']['datasetName'] == c['datasetName']) {
                    if (filter['column']['name'] == c['name']) {
                        this.reportListLookup[filter['name']]['column'] = [c, filter['column']];
                    }
                }
            }
        },

        renderList: function() {
            this.getReportListSelect().empty();

            var list = [];
            var f = [];
            var k = this.object.getFilters();
            for (var j in k) {
                f.push(k[j]['name']);
            }

            $.grep(Object.keys(this.reportListLookup), function(el) {
                if ($.inArray(el, f) == -1) {
                    list.push(el);
                }
            });

            if (list.length == 0) {
                this.getModal().modal();
            } else {
                for(var key in list) {
                    this.getReportListSelect().append('<option value="' + list[key] + '">' + list[key] + '</option>');
                }
            }
            this.getReportListSelect().prop('disabled', false);
            this.getNextButton().prop('disabled', false);
        },

        getModal: function() {
            if (!this.modal) {
                this.modal = $('<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true"></div>');
                var content = $('<div class="modal-content"></div>');
                var header = $('<div class="modal-header"></div>');
                header.append('<h4>Note</h4>');
                var body = $('<div class="modal-body row"></div>');
                body.append('<span class="bldr-mdl-icn glyphicon glyphicon-info-sign col-md-1"></span>');
                body.append('<div class="bldr-mdl-cntnt col-md-11">There are no Report Filters available.</div>');
                var footer = $('<div class="modal-footer"></div>');
                var okButton = $('<button type="button" class="btn btn-default" data-dismiss="modal">OK</button>');

                var _this = this;
                okButton.click(function() {
                    _this.redirectBackList();
                });

                footer.append(okButton);
                content.append(header, body, footer);
                var dialog = $('<div class="modal-dialog"></div>');
                dialog.append(content);
                this.modal.append(dialog);
                $('body').append(this.modal);
            }

            return this.modal;
        },

        getNextButton: function() {
            if (!this.nextButton) {
                this.nextButton = $('<button type="button" class="dsb-flt-act-btn btn btn-primary">Next</button>');

                if (!this.reportListLookup) {
                    this.nextButton.prop('disabled', true);
                }
            }

            return this.nextButton;
        },

        getCancelButton: function() {
            if (!this.cancelButton) {
                this.cancelButton = $('<button type="button" class="dsb-flt-act-btn btn btn-default">Cancel</button>');
            }

            return this.cancelButton;
        },

        getActionButtons: function() {
            if (!this.actionContainer) {
                this.actionContainer = $('<div class="dsb-flt-act-cntr clearfix pull-right"></div>');
                this.actionContainer.append(this.getNextButton(), this.getCancelButton());
            }

            return this.actionContainer;
        },

        getFormContainer: function() {
            if (!this.formContainer) {
                this.formContainer = $('<div></div>');
                this.formContainer.append('<h5>Select Report Filter:</h5>', this.getReportListSelect(), this.getActionButtons());
            }

            return this.formContainer;
        },

        render: function() {
            if (this.container) {
                this.container.append(this.getFormContainer());

                if (this.reportListLookup) {
                    this.renderList();
                }
            }

            return this.getFormContainer();
        },

        attachEventHandlers: function(cancel, next) {
            this.cancelCallback = cancel;

            var _this = this;
            this.getModal().on('hidden.bs.modal', function (e) {
                _this.redirectBackList();
            });

            this.getCancelButton().click(function() {
                if (cancel) {
                    cancel();
                }
            });

            this.getNextButton().click(function() {
                if (next) {
                    next(_this.reportListLookup[_this.getReportListSelect().val()]);
                }
            });
        },

        redirectBackList: function() {
            if (this.cancelCallback) {
                this.cancelCallback();
            }
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);