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

(function (global, $, undefined) {

    if (typeof $ === 'undefined') {
        throw new Error('ReportVisualizeButton requires jQuery');
    }

    if (typeof global.GD === 'undefined') {
        throw new Error('ReportVisualizeButton requires GD');
    }

    var GD = global.GD;

    global.GD.ReportVisualizeButton = GD.ReportConfigButton.extend({
        notification: null,
        notificationView: null,

        init: function (object, options) {
            this._super({'button': '#reportVisualizeButton', 'form': '#reportVisualizeForm'}, options);
            this.form.css('top', '31px');
            this.form.css('left', '422px');
        },

        initForm: function () {
            this.form.append(this.getPanelGroup(), $('<div class="pull-right"></div>').append(this.getApplyButton(), ' ', this.getCancelButton()));

            var targetlineCount = 0,
                tooltipsCount = 0,
                fontsizeCount = 0,
                tickIntervalCount = 0,
                numericSpanCount = 0,
                minimumCount = 0,
                maximumCount = 0,
                rangeIntervalCount = 0,
                reportObj = this.getController().getReport(),
                seriesData = reportObj.getColumnDisplayOptions(),
                seriesDataCount = Object.keys(seriesData).length;

            if (reportObj.getVisual().targetLine == 1) {
                targetlineCount = reportObj.getVisual().targetLine;
            }

            if (reportObj.getVisual().displayTooltip === 'details' || reportObj.getVisual().displayTooltip === 'value') {
                tooltipsCount = 1;
            }

            if (reportObj.getVisual().fontSize === 'Large' || reportObj.getVisual().fontSize === 'Small') {
                fontsizeCount = 1;
            }

            if (reportObj.getVisual().tickInterval === 'custom') {
                tickIntervalCount = 1;
            }

            if (reportObj.getVisual().minNumericSpan === 'Fixed') {
                numericSpanCount = 1;
            }

            if (reportObj.getVisual().rangeAutoMinimum === 'Fixed') {
                minimumCount = 1;
            }

            if (reportObj.getVisual().rangeAutoMaximum === 'Fixed') {
                maximumCount = 1;
            }

            if (reportObj.getVisual().intervals > 0) {
                rangeIntervalCount = 1;
            }


            var trafficData = reportObj.getTrafficLightOptions();
            var trafficDataCount = Object.keys(trafficData).length;

            var visualCount = seriesDataCount + trafficDataCount + targetlineCount + tooltipsCount + fontsizeCount + tickIntervalCount + numericSpanCount + minimumCount + maximumCount + rangeIntervalCount;

            if (visualCount != 0) {
                this.notification = new GD.Notification(visualCount);
            }
        },

        getApplyButton: function () {
            // Alternate to bootstrap accordion collapse function as collapse function not working. (probably due to multiple jquery files)
            var expand = function (el) {
                if (!el.hasClass("in")) {
                    el.siblings(".panel-heading").find(".accordion-toggle").trigger("click");
                }
            };
            if (!this.applyButton) {
                var _this = this,
                    reportObj = _this.getController().getReport();
                this.applyButton = $('<button tabindex="3000" type="button" class="btn bldr-btn-primary btn-sm">Apply</button>');
                this.applyButton.on('click',function(){
                    //TODO: validation before putting object values in form.
                    $.when($(document).trigger('save.report.visualize')).done(function () {
                        var visualData = reportObj.getVisual();
                        var reportType = reportObj.getChartType();
                        var errorCount = 0;
                        if ($.inArray(reportType, ['line', 'area', 'bar', 'column']) > -1) {
                            if (visualData.tickInterval == "Custom") {
                                var panel = $('#reportTickIntervalPanel');
                                if (visualData.tickIntervalValue == '') {
                                    panel.addClass('has-error');
                                    panel.find('.help-block').html('Please enter the tick interval.');
                                    expand(panel);
                                    errorCount++;
                                } else {
                                    if (isNaN(visualData.tickIntervalValue) == true) {
                                        panel.addClass('has-error');
                                        panel.find('.help-block').html('Please enter a numeric value.');
                                        expand(panel);
                                        errorCount++;
                                    } else {
                                        panel.find('.help-block').html('');
                                        panel.removeClass('has-error').addClass('has-success');
                                    }
                                }
                            }
                        }

                        if ($.inArray(reportType, ['line', 'area', 'bar', 'column']) > -1) {
                            if (visualData.minNumericSpan === "Fixed") {
                                var span = $('.reportNumericSpan');
                                if (visualData.minNumericSpanValue == '') {
                                    span.addClass('has-error');
                                    span.find('.help-block').html('Please enter a numeric span value.');
                                    expand($('#reportNumericSpanPanel'));
                                    errorCount++;
                                } else {
                                    if (isNaN(visualData.minNumericSpanValue) == true) {
                                        span.addClass('has-error');
                                        span.find('.help-block').html('Please enter a numeric value.');
                                        expand($('#reportNumericSpanPanel'));
                                        errorCount++;
                                    } else {
                                        span.find('.help-block').html('');
                                        span.removeClass('has-error').addClass('has-success');
                                    }
                                }
                            }
                        }

                        if ($.inArray(reportType, ['gauge']) > -1) {
                            var minRange = $('.reportMinRange');
                            var maxRange = $('.reportMaxRange');
                            if (visualData.rangeAutoMinimum == "Fixed") {
                                if (visualData.rangeMinimum == '') {
                                    minRange.addClass('has-error');
                                    minRange.find('.help-block').html('Please select a MINIMUM value.');
                                    expand($('#reportRangePanel'));
                                    errorCount++;
                                } else {
                                    if (isNaN(visualData.rangeMinimum) == true) {
                                        minRange.addClass('has-error');
                                        minRange.find('.help-block').html('Please enter a numeric value.');
                                        expand($('#reportRangePanel'));
                                        errorCount++;
                                    } else {
                                        minRange.find('.help-block').html('');
                                        minRange.removeClass('has-error').addClass('has-success');
                                    }
                                }
                            }

                            if (visualData.rangeAutoMaximum == "Fixed") {
                                if (visualData.rangeMaximum == '') {
                                    maxRange.addClass('has-error');
                                    maxRange.find('.help-block').html('Please select a MAXIMUM value.');
                                    expand($('#reportRangePanel'));
                                    errorCount++
                                } else {
                                    if (isNaN(visualData.rangeMaximum) == true) {
                                        maxRange.addClass('has-error');
                                        maxRange.find('.help-block').html('Please enter a numeric value.');
                                        expand($('#reportRangePanel'));
                                        errorCount++;
                                    } else {
                                        maxRange.find('.help-block').html('');
                                        maxRange.removeClass('has-error').addClass('has-success');
                                    }
                                }
                            }
                        }


                        if ($.inArray(reportType, ['line', 'area', 'bar', 'column', 'table']) > -1) {
                            if (visualData.targetLine == 1) {
                                var lineValue = $('.reportTargetLineValue');
                                var lineColor = $('.reportTargetLineColor');
                                if (visualData.targetLineValue == '') {
                                    lineValue.addClass('has-error');
                                    lineValue.find('.help-block').html('Please enter the target line value.');
                                    expand($('#reportTargetLinePanel'));
                                    errorCount++;
                                } else {
                                    if (isNaN(visualData.targetLineValue) == true) {
                                        lineValue.find('.help-block').html('Please enter a numeric value.');
                                        lineValue.addClass('has-error');
                                        expand($('#reportTargetLinePanel'));
                                        errorCount++;
                                    } else {
                                        lineValue.find('.help-block').html('');
                                        lineValue.removeClass('has-error').addClass('has-success');
                                    }
                                }

                                if (!visualData.targetLineColor && reportObj.getChartType() !== "table") {
                                    lineColor.addClass('has-error');
                                    lineColor.find('.help-block').html('Please enter the target line color.')
                                    expand($('#reportTargetLinePanel'));
                                    errorCount++
                                } else {
                                    lineColor.find('.help-block').html('');
                                    lineColor.removeClass('has-error').addClass('has-success');
                                }
                            }
                        }

                        if (errorCount == 0) {
                            _this.closeForm();
                            $(document).trigger('saved.report.visualize')
                        }
                    });

                });
            }
            return this.applyButton;
        },

        getCancelButton: function () {
            if (!this.cancelButton) {
                var _this = this;
                this.cancelButton = $('<button tabindex="3000" type="button" class="btn bldr-btn btn-sm">Cancel</button>');
                this.cancelButton.on('click',function(){
                    _this.closeForm();
                });
            }
            return this.cancelButton;
        },


        openForm: function () {
            this._super();
            this.getPanelGroup().empty();

            var reportType = this.getController().getReport().getChartType();

            new GD.ReportDisplayOptionsForm(null, this.getPanelGroup(), this.options).render();
            new GD.ReportFooterOptionsForm(null, this.getPanelGroup(), this.options).render();

            if ($.inArray(reportType, ['line', 'area', 'bar', 'column', 'pie']) > -1) {
                new GD.ReportTooltipsForm(null, this.getPanelGroup(), this.options).render();
            }

            if ($.inArray(reportType, ['line', 'area', 'bar', 'column']) > -1) {
                new GD.ReportXaxisDisplayForm(null, this.getPanelGroup(), this.options).render();
            }

            if ($.inArray(reportType, ['line', 'area', 'bar', 'column', 'pie']) > -1) {
                new GD.ReportFontSizeForm(null, this.getPanelGroup(), this.options).render();
            }

            if ($.inArray(reportType, ['table', 'line', 'area', 'bar', 'column']) > -1) {
                new GD.ReportTargetLineForm(null, this.getPanelGroup(), this.options).render();
            }

            if ($.inArray(reportType, ['line', 'area', 'bar', 'column']) > -1) {
                new GD.ReportNumericSpanForm(null, this.getPanelGroup(), this.options).render();
            }

            if ($.inArray(reportType, ['line', 'area', 'bar', 'column']) > -1) {
                new GD.ReportTickIntervalForm(null, this.getPanelGroup(), this.options).render();
            }

            if ($.inArray(reportType, ['line', 'area', 'bar', 'column', 'pie', 'dynamic_text']) > -1) {
                this.columnDisplayForm = new GD.ReportColumnDisplayForm(null, this.getPanelGroup(), this.options);
                this.columnDisplayForm.render()
            }

            if ($.inArray(reportType, ['gauge']) > -1) {
                new GD.ReportRangeForm(null, this.getPanelGroup(), this.options).render();
            }

            if ($.inArray(reportType, ['gauge']) > -1) {
                new GD.ReportColorSchemeForm(null, this.getPanelGroup(), this.options).render();
            }

            if ($.inArray(reportType, ['table', 'pivot_table']) > -1) {
                this.trafficLightForm = new GD.ReportTrafficLightForm(null, this.getPanelGroup(), this.options);
                this.trafficLightForm.render();
            }

            if ($.inArray(reportType, ['bar', 'column', 'pie']) > -1) {
                this.colorDisplayForm = new GD.ReportColorOptionsForm(null, this.getPanelGroup(), this.options);
                this.colorDisplayForm.render();
            }

            if (this.form.data("uiResizable")) {
                this.form.resizable("destroy");
            }
            this.form.resizable({
                minWidth: 300,
                maxWidth: 596,
                handles: 'e',
                resize: function (event, ui) {
                    $(document).trigger({
                        type: 'resize.visualize.form',
                        width: ui.size.width
                    });
                }
            });
            this.button.focus();
        },

        getPanelGroup: function () {
            if (!this.panelGroup) {
                this.panelGroup = $('<div class="panel-group" id="visualizePanelGroup"></div>');
            }
            return this.panelGroup;
        },

        attachEventHandlers: function () {
            this._super();
            var _this = this;

            $(document).on('changed.report.columns', function (e) {
                _this.enable();
                _this.openForm();
                if (_this.columnDisplayForm) {
                    _this.columnDisplayForm.listForm.updateAddedConf(e.columns);
                } else if (_this.trafficLightForm) {
                    _this.trafficLightForm.listForm.updateAddedConf(e.columns);
                }

                $.when($(document).trigger('save.report.visualize')).done(function () {
                    _this.closeForm();
                });
            });
            $(document).on('preprocess.report.save', function () {
                if (_this.columnDisplayForm) {
                    _this.columnDisplayForm.listForm.removeTempConfig();
                } else if (_this.trafficLightForm) {
                    _this.trafficLightForm.listForm.removeTempConfig();
                }
            });

            $(document).on('changed.report.type remove.report.filter', function () {
                _this.openForm();
                $.when($(document).trigger('save.report.visualize')).done(function () {
                    _this.closeForm();
                });
            });
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);