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
        throw new Error('DashboardLinkForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DashboardLinkForm requires GD');
    }

    var GD = global.GD;

    global.GD.DashboardLinkForm = GD.View.extend({
        formContainer: null,
        stepForms: null,
        wizardForm: null,
        header: null,
        stepActionsContainer: null,
        prevButton: null,
        cancelButton: null,
        nextButton: null,
        reportListForm: null,
        reportList: null,
        columnListForm: null,
        filterListForm: null,
        filterList: null,
        dashboardListForm: null,
        datasource: null,
        currentStep: 1,
        nextStep: null,
        temp: {},
        applyLink: null,

        init: function(object, container, options) {
            this._super(object, container, options);
            this.initVariables();
        },

        initVariables: function() {
            this.formContainer = null;
            this.stepForms = null;
            this.wizardForm = null;
            this.header = null;
            this.stepActionsContainer = null;
            this.prevButton = null;
            this.cancelButton = null;
            this.nextButton = null;
            this.reportListForm = null;
            this.reportList = null;
            this.columnListForm = null;
            this.filterListForm = null;
            this.filterList = null;
            this.dashboardListForm = null;
            this.nextStep = null;
            this.temp = {};
            this.applyLink = null;

            if (!this.object) {
                this.object = new GD.Link();
            }

            if (this.options) {
                if (this.options['dashboard']) {
                    this.dashboard = this.options['dashboard'];
                } else {
                    this.dashboard = GD.Dashboard.singleton;
                }

                this.datasource = this.options['datasource'];
            }

            this.currentStep = this.object.isNew() ? 0 : 1;
        },

        getStepForms: function() {
            if (!this.stepForms) {
                this.stepForms = [];
            }

            return this.stepForms;
        },

        getReportList: function(force) {
            if (!this.reportList || force) {
                this.reportList = [];
                if (this.dashboard) {
                    var reports = this.dashboard.getReports();
                    var links = this.dashboard.getLinks();
                    var l = [];
                    var key;

                    for (key in links) {
                        l.push(parseInt(links[key].getReportId()));
                    }

                    for (key in reports) {
                        if ($.inArray(reports[key]['id'], l) == -1) {
                            this.reportList.push({'val': reports[key]['id'], 'text': reports[key]['title']});
                        }
                    }
                }
            }

            return this.reportList;
        },

        setReport: function(value) {
            if (!this.temp) {
                this.temp = {};
            }

            this.temp['report'] = value;
        },

        getReport: function() {
            return (this.temp && this.temp['report']) ? this.temp['report'] : this.object.getReport();
        },

        getReportListForm: function() {
            if (!this.reportListForm) {
                var _this = this;
                var listOptions = {
                    'search': true,
                    'callback': function(c) {
                        _this.setReport({'id': $(c).attr('value'), 'name': $(c).text()});
                        _this.changeStep();
                    }
                };
                this.reportListForm = new GD.ListView(this.getReportList(), this.getStep1Form(), listOptions);
                this.reportListForm.setHeight(200);
            }

            return this.reportListForm;
        },

        getStep1Form: function() {
            if (!this.getStepForms()[0]) {
                this.getStepForms()[0] = $('<div class="dsb-lnk-stp-frm"></div>');
                this.getStepForms()[0].append('<span>Select Report to link to another Dashboard.</span>');
                this.getReportListForm().render();
            }

            return this.getStepForms()[0];
        },

        getColumnList: function() {
            var _this = this;
            $.ajax({
                'url': '/api/report.json',
                'data': {
                    'filter': {
                        'id': _this.getReport()['id'],
                        'datasource': this.datasource
                    },
                    'fields': 'dataset,filters,metadata,config'
                },
                'success': function(data) {
                    var columns = _this.parseColumns(data[0]);
                    _this.getColumnListForm().renderOptions(columns);
                    var c = _this.getColumn();
                    if (c) {
                        _this.getColumnListForm().setOptions(c['id']);
                    }
                }
            });
        },

        parseColumns: function(raw) {
            var columnLookup;
            var traverse = function (element, parent) {
                if ($.isArray(element)) {
                    for (var i = 0; i < element.length; i++) {
                        var column = {
                            id:element[i].name,
                            name:element[i].publicName,
                            publicName: element[i].publicName
                        };
                        if (parent) {
                            if (parent.publicName != element[i].publicName) {
                                column.name = parent.name + "/" + element[i].publicName;
                            } else {
                                column.name = parent.name;
                            }
                        }
                        columnLookup[element[i].name] = column;

                        if ($.isArray(element[i].elements)) {
                            arguments.callee(element[i].elements, column);
                        }
                    }
                }
            };

            var getColumn = function (id, metadata, formulas) {
                if (!columnLookup) {
                    columnLookup = {};
                    traverse(metadata['attributes']);
                    traverse(metadata['measures']);
                    if (formulas) {
                        $.each(formulas, function(i, f) {
                            columnLookup[f['name']] = f;
                            columnLookup[f['name']]['id'] = f['name'];
                            columnLookup[f['name']]['name'] = f['publicName'];
                        });
                    }
                }

                if ( columnLookup[id] ) {
                    return columnLookup[id];
                } else {
                    return null;
                }
            };

            var c = [];
            var columns = raw['config']['model']['columns'];
            for (var key in columns) {
                var column = getColumn(columns[key], raw['metadata'], raw['config']['model']['formulas']);
                if (column) {
                    $.each(raw['config']['columnConfigs'], function (i, c) {
                        if (column.id == c.columnId && c.displayName) {
                            column.name = c.displayName;
                        }
                    });
                    c.push({'val': column.id, 'text': column.name});
                }
            }

            return c;
        },

        getColumn: function() {
            return (this.temp && this.temp['column']) ? this.temp['column'] : this.object.getColumn();
        },

        setColumn: function(value) {
            if (!this.temp) {
                this.temp = {};
            }

            this.temp['column'] = value;
        },

        getColumnListForm: function() {
            if (!this.columnListForm) {
                var _this = this;
                var listOptions = {
                    'search': true,
                    'callback': function(c) {
                        _this.setColumn({'id': $(c).attr('value'), 'name': $(c).text()});
                        _this.changeStep();
                    }
                };
                this.columnListForm = new GD.ListView(null, this.getStep2Form(), listOptions);
                this.columnListForm.setHeight(200);
            }

            return this.columnListForm;
        },

        getStep2Form: function() {
            if (!this.getStepForms()[1]) {
                this.getStepForms()[1] = $('<div class="dsb-lnk-stp-frm"></div>');
                this.getStepForms()[1].append('<span>Select Column in Report to link to another Dashboard.</span>');
                this.getColumnListForm().render();
            }

            return this.getStepForms()[1];
        },

        getFilters: function() {
            return (this.temp && this.temp['filters']) ? this.temp['filters'] : this.object.getFilters();
        },

        setFilters: function(f) {
            if (!this.temp) {
                this.temp = {};
            }

            this.temp['filters'] = f;
        },

        filterNext: function() {
            var f = this.getFilterListForm().getSelected();
            var filters = [];
            for (var k in f) {
                filters.push(this.filterList[f[k]]['text']);
            }

            this.setFilters(filters);
        },

        getFilterList: function() {
            var _this = this;
            $.ajax({
                'url': '/api/report/' + this.getReport()['id'] + '.json',
                'data': {
                    'fields': 'filters,metadata'
                },
                'success': function(data) {
                    var filters = _this.parseFilters(data);
                    _this.getFilterListForm().renderOptions(filters);
                    var f = _this.getFilters();
                    if (f) {
                        _this.getFilterListForm().setOptions(f);
                    }
                }
            });
        },

        parseFilters: function(raw) {
            var filters = raw['filters'];
            var columns = raw['columns'];
            this.filterList = {};
            for (var k in filters) {
                //  Skip over invalid filters in the report
                if (filters[k]['column']) {
                    for (var l in columns) {
                        if (columns[l] === filters[k]['column']['name'] && filters[k]['exposed']) {
                            this.filterList[filters[k].name] = {'val': filters[k].name, 'text': filters[k].name};
                        }
                    }
                }
            }

            return this.filterList;
        },

        getFilterListForm: function() {
            if (!this.filterListForm) {
                var listOptions = {
                    'search': true,
                    'select': true
                };
                this.filterListForm = new GD.ListView(null, this.getStep3Form(), listOptions);
                this.filterListForm.setHeight(200);
            }

            return this.filterListForm;
        },

        getStep3Form: function() {
            if (!this.getStepForms()[2]) {
                this.getStepForms()[2] = $('<div class="dsb-lnk-stp-frm"></div>');
                this.getStepForms()[2].append('<span>Select Report Filters to pass to another Dashboard.</span>');
                this.getFilterListForm().render();
            }

            return this.getStepForms()[2];
        },

        getDashboard: function() {
            return (this.temp && this.temp['dashboard']) ? this.temp['dashboard'] : this.object.getDashboard();
        },

        setDashboard: function(d) {
            if (!this.temp) {
                this.temp = {};
            }

            this.temp['dashboard'] = d;
        },

        getDashboardList: function() {
            var _this = this;
            $.ajax({
                'url': '/api/dashboard.json',
                'data': {
                    'filter' : {
                        'datasource': this.datasource
                    }
                },
                'success': function(data) {
                    var dashboards = _this.parseDashboards(data);
                    _this.getDashboardListForm().renderOptions(dashboards);
                    var d = _this.getDashboard();
                    if (d) {
                        _this.getDashboardListForm().setOptions(d['id']);
                    }
                }
            });
        },

        parseDashboards: function(raw) {
            var d = [];
            if (this.dashboard) {
                for (var i = 0; i < raw.length; i++) {
                    if (raw[i].config) {
                        if (raw[i]['id'] != this.dashboard.getId()) {
                            d.push({'val': raw[i]['id'], 'text': raw[i]['name']});
                        }
                    }
                }
            }

            return d;
        },

        getDashboardListForm: function() {
            if (!this.dashboardListForm) {
                var _this = this;
                var listOptions = {
                    'search': true,
                    'callback': function(c) {
                        _this.setDashboard({'id': $(c).attr('value'), 'name': $(c).text()});
                        _this.changeStep();
                    }
                };
                this.dashboardListForm = new GD.ListView(null, this.getStep4Form(), listOptions);
                this.dashboardListForm.setHeight(200);
            }

            return this.dashboardListForm;
        },

        getStep4Form: function() {
            if (!this.getStepForms()[3]) {
                this.getStepForms()[3] = $('<div class="dsb-lnk-stp-frm"></div>');
                this.getStepForms()[3].append('<span>Select Dashboard to link to.</span>');
                this.getDashboardListForm().render();
            }

            return this.getStepForms()[3];
        },

        getHeader: function() {
            if (!this.header) {
                this.header = $('<h5 class="dsb-frm-hdr dsb-lnk-frm-hdr"></h5>');
            }

            return this.header;
        },

        getPrevButton: function() {
            if (!this.prevButton) {
                this.prevButton = $('<button type="button" class="dsb-act-btn dsb-lnk-act-btn btn btn-primary">Prev</button>');
            }

            return this.prevButton;
        },

        getCancelButton: function() {
            if (!this.cancelButton) {
                this.cancelButton = $('<button type="button" class="dsb-act-btn dsb-lnk-act-btn btn btn-default">Cancel</button>');
            }

            return this.cancelButton;
        },

        getNextButton: function() {
            if (!this.nextButton) {
                this.nextButton = $('<button type="button" class="dsb-act-btn dsb-lnk-act-btn btn btn-primary">Next</button>');
            }

            return this.nextButton;
        },

        getStepActionsContainer: function() {
            if (!this.stepActionsContainer) {
                this.stepActionsContainer = $('<div class="clearfix dsb-act-btn-cntr dsb-lnk-act-cntr pull-right"></div>');
                this.stepActionsContainer.append(this.getPrevButton(), this.getNextButton(), this.getCancelButton());
            }

            return this.stepActionsContainer;
        },

        setStep: function() {
            var steps = this.getStepForms();

            if (this.currentStep > steps.length - 1) {
                if (this.applyLink) {
                    this.applyLink();
                }
                return;
            }

            this.getHeader().text('Step ' + (this.currentStep + 1) + ' of 4:');
            for (var key in steps) {
                if (key != this.currentStep) {
                    steps[key].hide();
                } else {
                    steps[key].show();
                }
            }

            if (this.currentStep == 1) {
                this.getColumnList();
            } else if (this.currentStep == 2) {
                this.getFilterList();
                this.nextStep = this.filterNext;
            } else if (this.currentStep == 3) {
                this.getDashboardList();
            }

            var prev = true, next = true;
            if (this.currentStep == 0) {
                prev = false;
                next = false;
            } else {
                if (this.currentStep == 1) {
                    if (!this.object.isNew()) {
                        prev = false;
                    } else {
                        next = false;
                    }
                }

                if (this.currentStep == 3) {
                    if (!this.object.isNew()) {
                        this.getNextButton().text('Apply');
                        next = true;
                    } else {
                        this.getNextButton().text('Next');
                        next = false;
                    }
                }
            }

            if (prev) {
                this.getPrevButton().show();
            } else {
                this.getPrevButton().hide();
            }

            if (next) {
                this.getNextButton().show();
            } else {
                this.getNextButton().hide();
            }
        },

        changeStep: function(prev) {
            if (prev) {
                --this.currentStep;
            } else {
                ++this.currentStep;
            }

            this.setStep();
        },

        getWizardForm: function() {
            if (!this.wizardForm) {
                this.wizardForm = $('<div class="dsb-lnk-wzrd-frm"></div>');
                this.wizardForm.append(this.getHeader(), this.getStep1Form(), this.getStep2Form(), this.getStep3Form(), this.getStep4Form(), this.getStepActionsContainer());

                this.setStep();
            }

            return this.wizardForm;
        },

        getFormContainer: function() {
            if (!this.formContainer) {
                this.formContainer = $('<div class="dsb-lnk-frm"></div>');
                this.formContainer.append(this.getWizardForm());
            }

            return this.formContainer;
        },

        render: function(refresh) {
            if (refresh) {
                this.initVariables();
            }

            if (this.container) {
                this.container.append(this.getFormContainer());
            }

            return this.getFormContainer();
        },

        attachEventHandlers: function(cancel, applyCallback) {
            this.applyLink = applyCallback;

            var _this = this;
            this.getPrevButton().click(function() {
                _this.changeStep(true);
            });

            this.getCancelButton().click(function() {
                if (cancel) {
                    cancel();
                }
            });

            this.getNextButton().click(function() {
                if (_this.nextStep) {
                    _this.nextStep();
                }
                _this.changeStep();
            });
        },

        getLink: function() {
            var d = this.object;

            d.setReport(this.getReport());
            d.setColumn(this.getColumn());
            d.setFilters(this.getFilters());
            d.setDashboard(this.getDashboard());

            return d;
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);