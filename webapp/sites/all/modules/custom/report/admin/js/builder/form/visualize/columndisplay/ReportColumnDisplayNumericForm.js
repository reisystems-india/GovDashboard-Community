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
        throw new Error('ReportColumnDisplayNumericForm requires jQuery');
    }

    if (typeof global.GD === 'undefined') {
        throw new Error('ReportColumnDisplayNumericForm requires GD');
    }

    var GD = global.GD;

    var ReportColumnDisplayNumericForm = GD.View.extend({

        formContainer: null,
        form: null,
        modal: null,
        deleteButton: null,
        cancelButton: null,
        applyButton: null,

        init: function (object, container, options) {
            this._super(object, container, options);
            var _this = this;
            $(document).off('save.report.visualize').on('save.report.visualize', function () {
                _this.saveOptions();
            });
        },

        getController: function () {
            return this.options.builder;
        },

        getFormContainer: function () {
            if (!this.formContainer) {
                this.formContainer = $([

                    '<form role="form" class="form-horizontal">',
                    '<div class="form-group">',
                    '<label class="col-sm-12">Column Display for Column "' + this.object.displayName + '"</label>',
                    '<label class="col-sm-4 control-label" for="formGroupInputLarge"> Show Title:</label>',
                    '<div class="col-sm-8 radiogrp">',
                    '<label class="radio-inline">',
                    '<input type="radio" name="reportColumnDisplayShowTitle" id="inlineRadio1" value="yes" checked>  Yes ',
                    '</label>',
                    '<label class="radio-inline">',
                    '<input type="radio" name="reportColumnDisplayShowTitle" id="inlineRadio2" value="no">  No',
                    '</label>',
                    '<label class="radio-inline">',
                    '<input type="radio" name="reportColumnDisplayShowTitle" id="inlineRadio3" value="other"> Other ',
                    '</label>',
                    '</div>',
                    '</div>',

                    '<div class="form-group" id="othertitle">',
                    '<label class="col-sm-4 control-label">Other Title :</label>',
                    '<div class="col-sm-7">',
                    '<input type="text" class="form-control" id="reportColumnDisplayOtherTitle">',
                    '</div>',
                    '</div>',

                    '<div class="form-group datalabels">',
                    '<div class="col-sm-12">',
                    '<label>',
                    '<input type="checkbox" name="reportColumnDisplayShowDataLabel" checked> Show Data Labels ',
                    '</label>',
                    '</div>',
                    '</div>',

                    '<div class="col-sm-12" style="margin-left:7px">',
                    '<label class="radio">',
                    '<input type="radio" name="reportColumnDisplayColorScheme" id="inlineRadio1" value="standard" checked> Color All ',
                    '</label>',

                    '<label class="radio">',
                    '<input type="radio" name="reportColumnDisplayColorScheme" id="inlineRadio2" value="rangePercent"> By % Range ',
                    '</label>',

                    '<label class="radio">',
                    '<input type="radio" name="reportColumnDisplayColorScheme" id="inlineRadio3" value="rangeNumeric"> By # Range ',
                    '</label>',

                    '<label class="radio">',
                    '<input type="radio" name="reportColumnDisplayColorScheme" id="inlineRadio4" value="value"> By Value ',
                    '</label>',
                    '</div>',

                    // Start By Colorall
                    '<div class="form-group" id="byColorall">',
                    '<label class="col-sm-4 control-label">Color</label>',
                    '<div class="col-sm-8">',
                    '<input  type="text" style="width:100%" class="form-control" name="standardColor1" id="reportColumnDisplaystandardColor">',
                    '</div>',
                    '</div>',

                    // end By Colorall

                    // Start By % Range
                    '<div id="byRangePercent">',
                    '<div class="form-group">',
                    '<label class="col-sm-4 control-label">Intervals</label>',
                    '<div class="col-sm-8">',
                    '<select id="reportColumnDisplabyRangePercentSelectList" class="form-control input-sm">',
                    '<option value="0">0</option>',
                    '<option value="1">1</option>',
                    '<option value="2">2</option>',
                    '<option value="3">3</option>',
                    '<option value="4">4</option>',
                    '<option value="5">5</option>',
                    '</select>',
                    '</div>',
                    '</div>',


                    '<div class="groupColumnDisplayRangePercentLabel">',
                    '<label class="col-sm-9"  > Range </label>',
                    '<label class="col-sm-3"  >Pick Color</label>',
                    '</div>',

                    '<div class="groupColumnDisplayRangePercent1">',
                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" style="width:100%"  name="intervalPercentFromValue1"   >',
                    '</div>',

                    '<div class="col-sm-2" style="padding: 2px 0;text-align: center;">',
                    '<label>% to</label>',
                    '</div>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" style="width:100%"  name="intervalPercentToValue1" >',
                    '</div>',

                    '<div class="col-sm-1" style="padding: 2px 0;text-align: center;">',
                    '<label>%</label>',
                    '</div>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input  type="text" style="width:100%" class="rangeIntervalColor" name="rangeIntervalColor1">',
                    '</div>',
                    '</div>',

                    '<div class="groupColumnDisplayRangePercent2">',
                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" style="width:100%"  name="intervalPercentFromValue2"   >',
                    '</div>',

                    '<div class="col-sm-2" style="padding: 2px 0;text-align: center;">',
                    '<label>% to</label>',
                    '</div>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" style="width:100%"  name="intervalPercentToValue2" >',
                    '</div>',

                    '<div class="col-sm-1" style="padding: 2px 0;text-align: center;">',
                    '<label>%</label>',
                    '</div>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input  type="text" style="width:100%" class="rangeIntervalColor" name="rangeIntervalColor2">',
                    '</div>',
                    '</div>',

                    '<div class="groupColumnDisplayRangePercent3">',
                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" style="width:100%"  name="intervalPercentFromValue3"   >',
                    '</div>',

                    '<div class="col-sm-2" style="padding: 2px 0;text-align: center;">',
                    '<label>% to</label>',
                    '</div>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" style="width:100%"  name="intervalPercentToValue3" >',
                    '</div>',

                    '<div class="col-sm-1" style="padding: 2px 0;text-align: center;">',
                    '<label>%</label>',
                    '</div>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input  type="text" style="width:100%" class="rangeIntervalColor"    name="rangeIntervalColor3">',
                    '</div>',

                    '</div>',

                    '<div class="groupColumnDisplayRangePercent4">',
                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" style="width:100%"  name="intervalPercentFromValue4"   >',
                    '</div>',

                    '<div class="col-sm-2" style="padding: 2px 0;text-align: center;">',
                    '<label>% to</label>',
                    '</div>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" style="width:100%"  name="intervalPercentToValue4" >',
                    '</div>',

                    '<div class="col-sm-1" style="padding: 2px 0;text-align: center;">',
                    '<label>%</label>',
                    '</div>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input  type="text" style="width:100%" class="rangeIntervalColor"    name="rangeIntervalColor4">',
                    '</div>',

                    '</div>',

                    '<div class="groupColumnDisplayRangePercent5">',
                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" style="width:100%"  name="intervalPercentFromValue5"   >',
                    '</div>',

                    '<div class="col-sm-2" style="padding: 2px 0;text-align: center;">',
                    '<label>% to</label>',
                    '</div>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" style="width:100%"  name="intervalPercentToValue5" >',
                    '</div>',

                    '<div class="col-sm-1" style="padding: 2px 0;text-align: center;">',
                    '<label>%</label>',
                    '</div>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input  type="text" style="width:100%" class="rangeIntervalColor"    name="rangeIntervalColor5">',
                    '</div>',

                    '</div>',

                    '</div>',

                    // end By % Range


                    // Start By Range
                    '<div id="byRange">',
                    '<div class="form-group">',
                    '<label class="col-sm-4 control-label">Intervals</label>',
                    '<div class="col-sm-8">',
                    '<select id="reportColumnDisplabyRangeNumericSelectList" class="form-control input-sm">',
                    '<option value="0">0</option>',
                    '<option value="1">1</option>',
                    '<option value="2">2</option>',
                    '<option value="3">3</option>',
                    '<option value="4">4</option>',
                    '<option value="5">5</option>',
                    '</select>',
                    '</div>',
                    '</div>',

                    '<div class="groupColumnDisplayRangeNuericLabel">',
                    '<div class="col-sm-7">',
                    '<label> Range </label>',
                    '</div>',
                    '<div class="col-sm-5">',
                    '<label>Pick Color</label>',
                    '</div>',
                    '</div>',

                    '<div class="groupColumnDisplayRangeNueric1">',
                    '<div class="col-sm-3" style="padding:0 5px">',
                    '<input type="text" name="intervalNumericFromValue1" style="width:100%">',
                    '</div>',
                    '<label class="col-sm-1" style="text-align: center; padding: 2px 0;">to</label>',
                    '<div class="col-sm-3" style="padding:0 5px">',
                    '<input  type="text" name="intervalNumericToValue1" style="width:100%">',
                    '</div>',
                    '<div class="col-sm-5">',
                    '<input  type="text" style="width:100%" class="rangeNumericColor"  name="rangeNumericColor1">',
                    '</div>',
                    '</div>',

                    '<div class="groupColumnDisplayRangeNueric2">',
                    '<div class="col-sm-3" style="padding:0 5px">',
                    '<input type="text" name="intervalNumericFromValue2" style="width:100%">',
                    '</div>',
                    '<label class="col-sm-1" style="text-align: center; padding: 2px 0;">to</label>',
                    '<div class="col-sm-3" style="padding:0 5px">',
                    '<input  type="text" name="intervalNumericToValue2" style="width:100%">',
                    '</div>',
                    '<div class="col-sm-5">',
                    '<input  type="text" style="width:100%" class="rangeNumericColor" name="rangeNumericColor2">',
                    '</div>',
                    '</div>',

                    '<div class="groupColumnDisplayRangeNueric3">',
                    '<div class="col-sm-3" style="padding:0 5px">',
                    '<input type="text" name="intervalNumericFromValue3" style="width:100%">',
                    '</div>',
                    '<label class="col-sm-1" style="text-align: center; padding: 2px 0;">to</label>',
                    '<div class="col-sm-3" style="padding:0 5px">',
                    '<input  type="text" name="intervalNumericToValue3" style="width:100%">',
                    '</div>',
                    '<div class="col-sm-5">',
                    '<input  type="text" style="width:100%" class="rangeNumericColor"   name="rangeNumericColor3">',
                    '</div>',
                    '</div>',

                    '<div class="groupColumnDisplayRangeNueric4">',
                    '<div class="col-sm-3" style="padding:0 5px">',
                    '<input type="text" name="intervalNumericFromValue4" style="width:100%">',
                    '</div>',
                    '<label class="col-sm-1" style="text-align: center; padding: 2px 0;">to</label>',
                    '<div class="col-sm-3" style="padding:0 5px">',
                    '<input  type="text" name="intervalNumericToValue4" style="width:100%">',
                    '</div>',
                    '<div class="col-sm-5">',
                    '<input  type="text" style="width:100%" class="rangeNumericColor"   name="rangeNumericColor4">',
                    '</div>',
                    '</div>',

                    '<div class="groupColumnDisplayRangeNueric5">',
                    '<div class="col-sm-3" style="padding:0 5px">',
                    '<input type="text" name="intervalNumericFromValue5" style="width:100%">',
                    '</div>',
                    '<label class="col-sm-1" style="text-align: center; padding: 2px 0;">to</label>',
                    '<div class="col-sm-3" style="padding:0 5px">',
                    '<input  type="text" name="intervalNumericToValue5" style="width:100%">',
                    '</div>',
                    '<div class="col-sm-5">',
                    '<input  type="text" style="width:100%" class="rangeNumericColor"   name="rangeNumericColor5">',
                    '</div>',
                    '</div>',


                    '</div>',
                    // end By Range


                    // Start By Value
                    '<div id="byValue">',
                    '<div class="form-group">',
                    '<label class="col-sm-5 control-label" for="formGroupInputSmall"> Show Criteria </label>',
                    '<div class="col-sm-7">',
                    '<select id="reportColumnDisplabyValueSelectList" class="form-control input-sm">',
                    '<option value="0">0</option>',
                    '<option value="1">1</option>',
                    '<option value="2">2</option>',
                    '<option value="3">3</option>',
                    '<option value="4">4</option>',
                    '<option value="5">5</option>',
                    '</select>',
                    '</div>',
                    '</div>',

                    '<div class="groupColumnDisplayRangeValueLabel">',
                    '<div class="col-sm-7">',
                    '<label> Value </label>',
                    '</div>',
                    '<div class="col-sm-5">',
                    '<label> Pick Color</label>',
                    '</div>',
                    '</div>',

                    '<div class="groupColumnDisplayValue1">',
                    '<div class="col-sm-7">',
                    '<input type="text" class="criteriaValue1" style="width:100%">',
                    '</div>',
                    '<div class="col-sm-5">',
                    '<input type="text" class="criteriaColor criteriaColor1" style="width:100%">',
                    '</div>',
                    '</div>',

                    '<div class="groupColumnDisplayValue2">',
                    '<div class="col-sm-7">',
                    '<input type="text" class="criteriaValue2" style="width:100%">',
                    '</div>',
                    '<div class="col-sm-5">',
                    '<input type="text" class="criteriaColor criteriaColor2" style="width:100%">',
                    '</div>',
                    '</div>',

                    '<div class="groupColumnDisplayValue3">',
                    '<div class="col-sm-7">',
                    '<input type="text" class="criteriaValue3" style="width:100%">',
                    '</div>',
                    '<div class="col-sm-5">',
                    '<input type="text" class="criteriaColor criteriaColor3" style="width:100%">',
                    '</div>',
                    '</div>',

                    '<div class="groupColumnDisplayValue4">',
                    '<div class="col-sm-7">',
                    '<input type="text" class="criteriaValue4" style="width:100%">',
                    '</div>',
                    '<div class="col-sm-5">',
                    '<input type="text" class="criteriaColor criteriaColor4" style="width:100%">',
                    '</div>',
                    '</div>',

                    '<div class="groupColumnDisplayValue5">',
                    '<div class="col-sm-7">',
                    '<input type="text" class="criteriaValue5" style="width:100%">',
                    '</div>',
                    '<div class="col-sm-5">',
                    '<input type="text" class="criteriaColor criteriaColor5" style="width:100%">',
                    '</div>',
                    '</div>',
                    '</div>',
                    //by value
                    '</form>'].join("\n"));
            }
            this.formContainer.find("#reportColumnDisplaystandardColor, .rangeIntervalColor, .rangeNumericColor, .criteriaColor")
                .each(function () {
                    $(this).spectrum({
                        chooseText: "Apply",
                        cancelText: "Cancel",
                        preferredFormat: "hex",
                        showInput: true,
                        allowEmpty: true
                    });
                });
            return this.formContainer;
        },

        //Start Set Get Value For Range Percent Dropdown
        getReportColumnDisplayRangePercent: function () {
            return this.formContainer.find('#reportColumnDisplabyRangePercentSelectList option:selected').val();
        },

        setReportColumnDisplayRangePercent: function (selectedValue) {
            this.formContainer.find('#reportColumnDisplabyRangePercentSelectList option[value="' + selectedValue + '"]').prop('selected', true);
        },
        //End Set Get Value For Range Percent Dropdown

        //Start Set/Get Value For Range Numeric Dropdown
        getReportColumnDisplayRangeNumeric: function () {
            return this.formContainer.find('#reportColumnDisplabyRangeNumericSelectList option:selected').val();
        },

        setReportColumnDisplayRangeNumeric: function (selectedValue) {
            this.formContainer.find('#reportColumnDisplabyRangeNumericSelectList option[value="' + selectedValue + '"]').prop('selected', true);
        },
        //End Set/Get Value For Range Numeric Dropdown

        //Start Set/Get Value For Value Dropdown
        getReportColumnDisplayValue: function () {
            return this.formContainer.find('#reportColumnDisplabyValueSelectList option:selected').val();
        },

        setReportColumnDisplayValue: function (selectedValue) {
            this.formContainer.find('#reportColumnDisplabyValueSelectList option[value="' + selectedValue + '"]').prop('selected', true);
        },
        //End Set/Get Value For Value Dropdown

        getColumnDisplayStandardSetting: function () {
            return this.formContainer.find('input[name="reportColumnDisplayColorScheme"]:checked').val();
        },

        setColumnDisplayStandardSetting: function (value) {
            this.formContainer.find('input[name="reportColumnDisplayColorScheme"][value="' + value + '"]').prop('checked', true);
        },

        getColumnDisplayShowTitleSetting: function () {
            return this.formContainer.find('input[name="reportColumnDisplayShowTitle"]:checked').val();
        },

        setColumnDisplayShowTitleSetting: function (value) {
            this.formContainer.find('input[name="reportColumnDisplayShowTitle"][value="' + value + '"]').prop('checked', true);
        },

        getColumnDisplayOthertitle: function () {
            return this.formContainer.find("#reportColumnDisplayOtherTitle").val();
        },

        setColumnDisplayOthertitle: function (value) {
            this.formContainer.find("#reportColumnDisplayOtherTitle").val(value);
        },

        getColumnDisplayStandardColor: function () {
            return this.formContainer.find("#reportColumnDisplaystandardColor").val();
        },

        setColumnDisplayStandardColor: function (value) {
            this.formContainer.find("#reportColumnDisplaystandardColor").spectrum('set', value);
        },

        getColumnDisplayShowDataLabelSetting: function () {
            if (this.formContainer.find('input[name="reportColumnDisplayShowDataLabel"]').is(':checked') == true) {
                return this.TargetLineSetting = 1;
            } else {
                return this.TargetLineSetting = 0;
            }
        },

        setColumnDisplayShowDataLabelSetting: function (value) {
            if (value == 1) {
                this.formContainer.find('input[name="reportColumnDisplayShowDataLabel"]').prop('checked', true);
            } else {
                this.formContainer.find('input[name="reportColumnDisplayShowDataLabel"]').prop('checked', false);
            }
        },

        //For Value Option
        getColumnDisplayValue: function (counter) {
            return this.formContainer.find('.criteriaValue' + counter).val();
        },

        setColumnDisplayValue: function (counter, value) {
            this.formContainer.find('.criteriaValue' + counter).val(value);

        },

        getColumnDisplayColor: function (counter) {
            return this.formContainer.find('.criteriaColor' + counter).val();
        },

        setColumnDisplayColor: function (counter, value) {
            this.formContainer.find('.criteriaColor' + counter).spectrum('set', value);
        },
        //For Value Option   

        getForm: function () {
            if (!this.form) {
                this.form = $([
                    '<form role="form">',
                    '<h5>Formatting for Column "' + this.object.displayName + '"</h5>',
                    '<div class="form-group">',
                    '<label for="reportColumnFormatName">Display Name</label>',
                    '<input type="text" class="form-control input-sm" id="reportColumnDisplayStringName" value="' + this.object.displayName + '">',
                    '</div>',
                    '</form>'
                ].join("\n"));
            }

            return this.form;
        },


        //Start Display Hide Element For Range Percent Option
        showColumnDisplayRangePercentInput: function () {
            var _this = this;
            var i = 1;
            for (i = 1; i < 6; i++) {
                this.formContainer.find('.groupColumnDisplayRangePercent' + i).hide();
            }

            var count = _this.getReportColumnDisplayRangePercent();

            for (i = 1; i <= count; i++) {
                this.formContainer.find('.groupColumnDisplayRangePercent' + i).show();
            }
        },


        showHideColumnDisplayRangePercentInput: function () {
            var _this = this;
            this.formContainer.find('#reportColumnDisplabyRangePercentSelectList').change(function () {
                var count = _this.getReportColumnDisplayRangePercent();
                var i = 1;
                if (count > 0) {
                    _this.formContainer.find('.groupColumnDisplayRangePercentLabel').show();
                } else {
                    _this.formContainer.find('.groupColumnDisplayRangePercentLabel').hide();
                }
                for (i = 1; i <= 5; i++) {
                    _this.formContainer.find('.groupColumnDisplayRangePercent' + i).hide();
                }
                for (i = 1; i <= count; i++) {
                    _this.formContainer.find('.groupColumnDisplayRangePercent' + i).show();
                }

            });
        },
        //End Display Hide Element For Range Percent Option

        setColumnDisplayValues: function () {
            for (var i = 1; i < 6; i++) {
                var visual_data = this.getController().getReport().getVisual();
                if (visual_data['series'][this.object.columnId]['value' + i]) {
                    this.setRangeCriteriaValue(i, visual_data['series'][this.object.columnId]['value' + i]);
                }

                if (visual_data['series'][this.object.columnId]['color' + i]) {
                    this.setCriteriaColor(i, visual_data['series'][this.object.columnId]['color' + i]);
                }
            }
            this.setReportColumnDisplay(visual_data['series'][this.object.columnId]['criteria']);
        },


        //Start Display Hide Element For Range Numeric Option
        showColumnDisplayRangeNumericInput: function () {
            var _this = this;
            var i = 1;
            for (i = 1; i < 6; i++) {
                this.formContainer.find('.groupColumnDisplayRangeNueric' + i).hide();
            }

            var count = _this.getReportColumnDisplayRangeNumeric();
            if (count > 0) {
                this.formContainer.find('.groupColumnDisplayRangeNuericLabel').show();
            } else {
                this.formContainer.find('.groupColumnDisplayRangeNuericLabel').hide();
            }
            for (i = 1; i <= count; i++) {
                this.formContainer.find('.groupColumnDisplayRangeNueric' + i).show();
            }
        },


        showHideColumnDisplayRangeNumericInput: function () {
            var _this = this;
            this.formContainer.find('#reportColumnDisplabyRangeNumericSelectList').change(function () {
                var count = _this.getReportColumnDisplayRangeNumeric();
                if (count > 0) {
                    _this.formContainer.find('.groupColumnDisplayRangeNuericLabel').show();
                } else {
                    _this.formContainer.find('.groupColumnDisplayRangeNuericLabel').hide();
                }
                for (var i = 1; i <= 5; i++) {
                    _this.formContainer.find('.groupColumnDisplayRangeNueric' + i).hide();
                }
                for (var i = 1; i <= count; i++) {
                    _this.formContainer.find('.groupColumnDisplayRangeNueric' + i).show();
                }

            });
        },
        //End Display Hide Element For Range Numeric Option

        //Start Display Hide Element For Value Option
        showColumnDisplayValueInput: function () {
            var _this = this,
                formContainer = this.formContainer;
            var i = 1;
            for (i = 1; i < 6; i++) {
                formContainer.find('.groupColumnDisplayValue' + i).hide();
            }

            var count = _this.getReportColumnDisplayValue();
            if (count > 0) {
                formContainer.find('.groupColumnDisplayRangeValueLabel').show();
            } else {
                formContainer.find('.groupColumnDisplayRangeValueLabel').hide();
            }
            for (i = 1; i <= count; i++) {
                formContainer.find('.groupColumnDisplayValue' + i).show();
            }
        },


        showHideColumnDisplayValueInput: function () {
            var _this = this,
                formContainer = this.formContainer;
            formContainer.find('#reportColumnDisplabyValueSelectList').change(function () {
                var count = _this.getReportColumnDisplayValue();
                var i = 1;
                if (count > 0) {
                    formContainer.find('.groupColumnDisplayRangeValueLabel').show();
                } else {
                    formContainer.find('.groupColumnDisplayRangeValueLabel').hide();
                }

                for (i = 1; i <= 5; i++) {
                    formContainer.find('.groupColumnDisplayValue' + i).hide();
                }
                for (i = 1; i <= count; i++) {
                    formContainer.find('.groupColumnDisplayValue' + i).show();
                }

            });
        },
        //End Display Hide Element For Value Option

        getModal: function (callback) {
            if (!this.modal) {
                this.modal = $([
                    '<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">',
                    '<div class="modal-dialog">',
                    '<div class="modal-content">',
                    '<div class="modal-header">',
                    '<h4>Delete Column Format</h4>',
                    '</div>',
                    '<div class="modal-body">',
                    '<div class="row">',
                    '<div class="col-sm-1"><span style="font-size:25px;" class="glyphicon glyphicon-info-sign"></span></div>',
                    '<div style="height:25px; line-height: 25px;" class="col-sm-11">Are you sure you want to delete this column\'s formatting?</div>',
                    '</div>',
                    '</div>',
                    '<div class="modal-footer">',
                    '<button type="button" class="btn btn-danger" data-dismiss="modal">Delete</button>',
                    '<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>',
                    '</div>',
                    '</div>',
                    '</div>',
                    '</div>'

                ].join("\n"));

                var _this = this;
                this.modal.find('.btn-danger').on('click', function () {
                    _this.deleteFormat(callback);
                });

                $('body').append(this.modal);
            }

            return this.modal;
        },

        showColumnDisplaytOtherTitleinput: function () {
            var formContainer = this.getFormContainer();
            formContainer.find("#othertitle").hide();

            formContainer.find('input[name="reportColumnDisplayShowTitle"]').on('click', function () {
                if (formContainer.find('input[name="reportColumnDisplayShowTitle"]:checked').val() == "other") {
                    formContainer.find("#othertitle").show();
                } else {
                    formContainer.find("#othertitle").hide();
                }
            });
        },

        showColumnDisplaytPresentationType: function () {
            var formContainer = this.getFormContainer();

            $(".groupColumnDisplayRangePercentLabel, #byRangePercent, #byRange, #byValue", formContainer).hide();

            if (this.object.options) {
                if (this.object.options.title == "other") {
                    formContainer.find("#reportColumnDisplayOtherTitle").show();
                }
            }

            formContainer.find('input[name="reportColumnDisplayColorScheme"]').on('click', function () {

                if (formContainer.find('input[name="reportColumnDisplayColorScheme"]:checked').val() == "standard") {
                    formContainer.find("#byColorall").show();
                } else {
                    formContainer.find("#byColorall").hide();
                }

                if (formContainer.find('input[name="reportColumnDisplayColorScheme"]:checked').val() == "rangePercent") {
                    formContainer.find("#byRangePercent").show();
                } else {
                    formContainer.find("#byRangePercent").hide();
                }

                if (formContainer.find('input[name="reportColumnDisplayColorScheme"]:checked').val() == "rangeNumeric") {
                    formContainer.find("#byRange").show();
                } else {
                    formContainer.find("#byRange").hide();
                }

                if (formContainer.find('input[name="reportColumnDisplayColorScheme"]:checked').val() == "value") {
                    formContainer.find("#byValue").show();
                } else {
                    formContainer.find("#byValue").hide();
                }
            });
        },

        //For RangePercent
        getColumnDisplayRangePercentFromValue: function (counter) {
            return this.formContainer.find('input[name="intervalPercentFromValue' + counter + '"]').val();
        },

        setColumnDisplayRangePercentFromValue: function (counter, value) {
            this.formContainer.find('input[name="intervalPercentFromValue' + counter + '"]').val(value);

        },

        getColumnDisplayRangePercentToValue: function (counter) {
            return this.formContainer.find('input[name="intervalPercentToValue' + counter + '"]').val();
        },

        setColumnDisplayRangePercentToValue: function (counter, value) {
            this.formContainer.find('input[name="intervalPercentToValue' + counter + '"]').val(value);

        },

        getColumnDisplayRangePercentColor: function (counter) {
            return this.formContainer.find('input[name="rangeIntervalColor' + counter + '"]').val();
        },

        setColumnDisplayRangePercentColor: function (counter, value) {
            this.formContainer.find('input[name="rangeIntervalColor' + counter + '"]').spectrum('set', value);

        },
        //For RangePercent

        //For RangeNumeric
        getColumnDisplayRangeNumericFromValue: function (counter) {
            return this.formContainer.find('input[name="intervalNumericFromValue' + counter + '"]').val();
        },

        setColumnDisplayRangeNumericFromValue: function (counter, value) {
            this.formContainer.find('input[name="intervalNumericFromValue' + counter + '"]').val(value);

        },

        getColumnDisplayRangeNumericToValue: function (counter) {
            return this.formContainer.find('input[name="intervalNumericToValue' + counter + '"]').val();
        },

        setColumnDisplayRangeNumericToValue: function (counter, value) {
            this.formContainer.find('input[name="intervalNumericToValue' + counter + '"]').val(value);

        },

        getColumnDisplayRangeNumericColor: function (counter) {
            return this.formContainer.find('input[name="rangeNumericColor' + counter + '"]').val();
        },

        setColumnDisplayRangeNumericColor: function (counter, value) {
            this.formContainer.find('input[name="rangeNumericColor' + counter + '"]').spectrum('set', value);

        },
        //For RangeNumeric

        saveOptions: function () {
            if (this.object.colorScheme) {
                this.object = {
                    columnId: this.object.columnId,
                    columnType: this.object.columnType,
                    displayName: this.object.displayName,
                    new: false
                }
            }
            this.object.options = {
                displayDataLabels: this.getColumnDisplayShowDataLabelSetting(),
                displayTitle: this.getColumnDisplayShowTitleSetting()
            };
            if (this.getColumnDisplayShowTitleSetting() == 'other') {
                this.object.options.title = this.getColumnDisplayOthertitle();
            }

            if (this.getColumnDisplayStandardSetting() == 'rangeNumeric' || this.getColumnDisplayStandardSetting() == 'value') {
                this.object.options.displayMarkers = 1;
                this.object.options.step = null;
            }

            this.object.colorScheme = this.getColumnDisplayStandardSetting();

            if (this.getColumnDisplayStandardSetting() == 'standard') {
                var standardColor = this.getColumnDisplayStandardColor();
                if (standardColor) {
                    this.object.color = this.getColumnDisplayStandardColor();
                }
            } else if (this.getColumnDisplayStandardSetting() == 'rangePercent') {
                this.object.intervals = this.getReportColumnDisplayRangePercent();
                var sel_count = this.getReportColumnDisplayRangePercent();
                for (var i = 1; i < sel_count + 1; i++) {
                    var rangePercentColor = this.getColumnDisplayRangePercentColor(i);
                    this.object['rangeIntervalFrom' + i] = this.getColumnDisplayRangePercentFromValue(i);
                    this.object['rangeIntervalTo' + i] = this.getColumnDisplayRangePercentToValue(i);
                    if (rangePercentColor) {
                        this.object['rangeIntervalColor' + i] = rangePercentColor;
                    }
                }
            } else if (this.getColumnDisplayStandardSetting() == 'rangeNumeric') {
                this.object.intervals = this.getReportColumnDisplayRangeNumeric();
                var sel_count = this.getReportColumnDisplayRangeNumeric();
                for (var i = 1; i < sel_count + 1; i++) {
                    var rangeNumericColor = this.getColumnDisplayRangeNumericColor(i);
                    this.object['rangeIntervalFromNumeric' + i] = this.getColumnDisplayRangeNumericFromValue(i);
                    this.object['rangeIntervalToNumeric' + i] = this.getColumnDisplayRangeNumericToValue(i);
                    if (rangeNumericColor) {
                        this.object['rangeIntervalColor' + i] = rangeNumericColor;
                    }
                }
            } else if (this.getColumnDisplayStandardSetting() == 'value') {
                this.object.criteria = this.getReportColumnDisplayValue();
                var sel_count = this.getReportColumnDisplayValue();
                for (var i = 1; i < sel_count + 1; i++) {
                    var displayColor = this.getColumnDisplayColor(i);
                    this.object['value' + i] = this.getColumnDisplayValue(i);
                    if (displayColor) {
                        this.object['color' + i] = displayColor;
                    }
                }
            }

            this.getController().getReport().setColumnDisplayOption(this.object);
        },

        showColumnDisplayEditView: function () {
            var formContainer = this.getFormContainer();
            this.setColumnDisplayShowDataLabelSetting(this.object.options.displayDataLabels);
            this.setColumnDisplayShowTitleSetting(this.object.options.displayTitle);
            this.setColumnDisplayOthertitle(this.object.options.title);
            if (this.object.options.displayTitle == "other") {
                $("#othertitle").show();
            }
            this.setColumnDisplayStandardColor(this.object.color);
            this.setColumnDisplayStandardSetting(this.object.colorScheme);
            if (this.object.colorScheme == "standard") {
                formContainer.find("#byColorall").show();
            } else {
                formContainer.find("#byColorall").hide();
            }

            if (this.object.colorScheme == "rangePercent") {
                formContainer.find("#byRangePercent").show();
                this.setReportColumnDisplayRangePercent(this.object.intervals);
                var sel_count = this.object.intervals;
                if (sel_count > 0) {
                    formContainer.find('.groupColumnDisplayRangePercentLabel').show();
                }
                for (var i = 1; i < sel_count + 1; i++) {
                    formContainer.find('.groupColumnDisplayRangePercent' + i).show();
                    this.setColumnDisplayRangePercentFromValue(i, this.object['rangeIntervalFrom' + i]);
                    this.setColumnDisplayRangePercentToValue(i, this.object['rangeIntervalTo' + i]);
                    this.setColumnDisplayRangePercentColor(i, this.object['rangeIntervalColor' + i]);
                }
            } else {
                formContainer.find("#byRangePercent").hide();
            }

            if (this.object.colorScheme == "rangeNumeric") {

                formContainer.find("#byRange").show();
                this.setReportColumnDisplayRangeNumeric(this.object.intervals);
                var sel_count = this.object.intervals;
                if (sel_count > 0) {
                    formContainer.find('.groupColumnDisplayRangeNuericLabel').show();
                }
                for (var i = 1; i < sel_count + 1; i++) {
                    formContainer.find('.groupColumnDisplayRangeNueric' + i).show();
                    this.setColumnDisplayRangeNumericFromValue(i, this.object['rangeIntervalFromNumeric' + i]);
                    this.setColumnDisplayRangeNumericToValue(i, this.object['rangeIntervalToNumeric' + i]);
                    this.setColumnDisplayRangeNumericColor(i, this.object['rangeIntervalColor' + i]);

                    this.getColumnDisplayValue(i);
                    this.object['color' + i] = this.getColumnDisplayColor(i);
                }
            } else {
                formContainer.find("#byRange").hide();
            }

            if (this.object.colorScheme == "value") {
                formContainer.find("#byValue").show();
                this.setReportColumnDisplayValue(this.object.criteria);
                var sel_count = this.object.criteria;
                if (sel_count > 0) {
                    formContainer.find('.groupColumnDisplayRangeValueLabel').show();
                }
                for (var i = 1; i < sel_count + 1; i++) {
                    formContainer.find('.groupColumnDisplayValue' + i).show();
                    this.setColumnDisplayValue(i, this.object['value' + i]);
                    this.setColumnDisplayColor(i, this.object['color' + i]);
                }
            } else {
                formContainer.find("#byValue").hide();
            }
        },


        render: function () {
            if (this.container) {
                this.container.append(this.getFormContainer());
                this.showColumnDisplaytOtherTitleinput();

                this.showColumnDisplaytPresentationType();

                this.showColumnDisplayRangePercentInput();
                this.showHideColumnDisplayRangePercentInput();

                this.showColumnDisplayRangeNumericInput();
                this.showHideColumnDisplayRangeNumericInput();

                this.showColumnDisplayValueInput();
                this.showHideColumnDisplayValueInput();
                if (this.object.columnId) {
                    this.showColumnDisplayEditView();
                }

            }
            return this.getFormContainer();
        }

    });

    GD.ReportColumnDisplayNumericForm = ReportColumnDisplayNumericForm;

})(typeof window === 'undefined' ? this : window, jQuery);