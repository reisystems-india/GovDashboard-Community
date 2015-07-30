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
        throw new Error('ReportColumnDisplayPieNumericForm requires jQuery');
    }

    if (typeof global.GD === 'undefined') {
        throw new Error('ReportColumnDisplayPieNumericForm requires GD');
    }

    var GD = global.GD;

    var ReportColumnDisplayPieNumericForm = GD.View.extend({

        formContainer: null,
        form: null,

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

        /*isNew: function() {
         return ( this.object.new );
         },*/

        getFormContainer: function () {
            if (!this.formContainer) {
                this.formContainer = $([
                    '<form role="form" class="form-horizontal">',
                    '<div class="form-group">',
                    '<div class="col-sm-4">',
                    '<label class="radio-inline">',
                    '<input type="radio" name="NumericOption" class="byRangePercent" value="rangePercent" checked> By % Range ',
                    '</label>',
                    '</div>',
                    '<div class="col-sm-4">',
                    '<label class="radio-inline">',
                    '<input type="radio" name="NumericOption" class="byRange" value="rangeNumeric"> By # Range ',
                    '</label>',
                    '</div>',
                    '<div class="col-sm-4">',
                    '<label class="radio-inline">',
                    '<input type="radio" name="NumericOption" class="byValue" value="value"> By Value ',
                    '</label>',
                    '</div>',
                    '</div>',


                    // Start By % Range
                    '<div id="byRangePercent" >',

                    '<div class="form-group">',
                    '<label class="col-sm-4 control-label">Intervals</label>',
                    '<div class="col-sm-8">',
                    '<select class="form-control input-sm colorSchemeInterval">',
                    '<option value="0" checked>0</option>',
                    '<option value="1">1</option>',
                    '<option value="2">2</option>',
                    '<option value="3">3</option>',
                    '<option value="4">4</option>',
                    '<option value="5">5</option>',
                    '</select>',
                    '</div>',
                    '</div>',

                    '<div class="groupColorScheme0">',
                    '<label class="col-sm-9">Range</label>',
                    '<label class="col-sm-3">Pick Color</label>',
                    '</div>',

                    '<div class="groupColorScheme1">',
                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" style="width:100%"  name="rangeIntervalFrom1">',
                    '</div>',

                    '<div class="col-sm-2" style="padding: 2px 0;text-align: center;">',
                    '<label>% to</label>',
                    '</div>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" style="width:100%"  name="rangeIntervalTo1" >',
                    '</div>',

                    '<div class="col-sm-1" style="padding: 2px 0;text-align: center;">',
                    '<label>%</label>',
                    '</div>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input  type="text" style="width:100%"  class="rangeIntervalColor"   name="rangeIntervalColor1">',
                    '</div>',
                    '</div>',

                    '<div class="groupColorScheme2">',
                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" style="width:100%"  name="rangeIntervalFrom2"   >',
                    '</div>',

                    '<div class="col-sm-2" style="padding: 2px 0;text-align: center;">',
                    '<label>% to</label>',
                    '</div>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" style="width:100%"  name="rangeIntervalTo2" >',
                    '</div>',

                    '<div class="col-sm-1" style="padding: 2px 0;text-align: center;">',
                    '<label>%</label>',
                    '</div>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input  type="text" style="width:100%" class="rangeIntervalColor"    name="rangeIntervalColor2">',
                    '</div>',
                    '</div>',

                    '<div class="groupColorScheme3">',
                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" style="width:100%"  name="rangeIntervalFrom3"   >',
                    '</div>',

                    '<div class="col-sm-2" style="padding: 2px 0;text-align: center;">',
                    '<label>% to</label>',
                    '</div>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" style="width:100%"  name="rangeIntervalTo3" >',
                    '</div>',

                    '<div class="col-sm-1" style="padding: 2px 0;text-align: center;">',
                    '<label>%</label>',
                    '</div>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input  type="text" style="width:100%" class="rangeIntervalColor"    name="rangeIntervalColor3">',
                    '</div>',
                    '</div>',

                    '<div class="groupColorScheme4">',
                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" style="width:100%"  name="rangeIntervalFrom4"   >',
                    '</div>',

                    '<div class="col-sm-2" style="padding: 2px 0;text-align: center;">',
                    '<label>% to</label>',
                    '</div>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" style="width:100%"  name="rangeIntervalTo4" >',
                    '</div>',

                    '<div class="col-sm-1" style="padding: 2px 0;text-align: center;">',
                    '<label>%</label>',
                    '</div>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input  type="text" style="width:100%" class="rangeIntervalColor"    name="rangeIntervalColor4">',
                    '</div>',
                    '</div>',

                    '<div class="groupColorScheme5">',
                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" style="width:100%"  name="rangeIntervalFrom5"   >',
                    '</div>',

                    '<div class="col-sm-2" style="padding: 2px 0;text-align: center;">',
                    '<label>% to</label>',
                    '</div>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" style="width:100%"  name="rangeIntervalTo5" >',
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
                    '<div id="byRange" >',
                    '<div class="form-group">',
                    '<label class="col-sm-4 control-label">Intervals</label>',
                    '<div class="col-sm-8">',
                    '<select class="form-control input-sm colorSchemeInterval2">',
                    '<option value="0" checked>0</option>',
                    '<option value="1">1</option>',
                    '<option value="2">2</option>',
                    '<option value="3">3</option>',
                    '<option value="4">4</option>',
                    '<option value="5">5</option>',
                    '</select>',
                    '</div>',
                    '</div>',

                    '<div class="groupColorSchemeValue0">',
                    '<div class="col-sm-7">',
                    '<label> Range </label>',
                    '</div>',
                    '<div class="col-sm-5">',
                    '<label>Pick Color</label>',
                    '</div>',
                    '</div>',


                    '<div class="groupColorSchemeValue1">',
                    '<div class="groupColumnDisplayRangeNueric1">',
                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" name="rangeIntervalFromNumeric1" style="width:100%">',
                    '</div>',

                    '<label class="col-sm-1" style="text-align: center; padding: 2px 0;">to</label>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input  type="text" name="rangeIntervalToNumeric1" style="width:100%">',
                    '</div>',

                    '<div class="col-sm-5">',
                    '<input  type="text" style="width:100%" class="rangeIntervalColorNumeric" name="rangeIntervalColorNumeric1">',
                    '</div>',
                    '</div>',
                    '</div>',


                    '<div class="groupColorSchemeValue2">',
                    '<div class="groupColumnDisplayRangeNueric2">',
                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" name="rangeIntervalFromNumeric2" style="width:100%">',
                    '</div>',

                    '<label class="col-sm-1" style="text-align: center; padding: 2px 0;">to</label>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input  type="text" name="rangeIntervalToNumeric2" style="width:100%">',
                    '</div>',

                    '<div class="col-sm-5">',
                    '<input  type="text" style="width:100%" class="rangeIntervalColorNumeric" name="rangeIntervalColorNumeric2">',
                    '</div>',
                    '</div>',
                    '</div>',

                    '<div class="groupColorSchemeValue3">',
                    '<div class="groupColumnDisplayRangeNueric3">',
                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" name="rangeIntervalFromNumeric3" style="width:100%">',
                    '</div>',

                    '<label class="col-sm-1" style="text-align: center; padding: 2px 0;">to</label>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input  type="text" name="rangeIntervalToNumeric3" style="width:100%">',
                    '</div>',

                    '<div class="col-sm-5">',
                    '<input  type="text" style="width:100%" class="rangeIntervalColorNumeric" name="rangeIntervalColorNumeric3">',
                    '</div>',
                    '</div>',
                    '</div>',

                    '<div class="groupColorSchemeValue4">',
                    '<div class="groupColumnDisplayRangeNueric4">',
                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" name="rangeIntervalFromNumeric4" style="width:100%">',
                    '</div>',

                    '<label class="col-sm-1" style="text-align: center; padding: 2px 0;">to</label>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input  type="text" name="rangeIntervalToNumeric4" style="width:100%">',
                    '</div>',

                    '<div class="col-sm-5">',
                    '<input  type="text" style="width:100%" class="rangeIntervalColorNumeric" name="rangeIntervalColorNumeric4">',
                    '</div>',
                    '</div>',
                    '</div>',

                    '<div class="groupColorSchemeValue5">',
                    '<div class="groupColumnDisplayRangeNueric5">',
                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" name="rangeIntervalFromNumeric1" style="width:100%">',
                    '</div>',

                    '<label class="col-sm-1" style="text-align: center; padding: 2px 0;">to</label>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input  type="text" name="rangeIntervalToNumeric5" style="width:100%">',
                    '</div>',

                    '<div class="col-sm-5">',
                    '<input  type="text" style="width:100%" class="rangeIntervalColorNumeric" name="rangeIntervalColorNumeric5">',
                    '</div>',
                    '</div>',
                    '</div>',


                    '</div>',
                    // end By Range


                    // Start By Value
                    '<div id="byValue" >',
                    '<div class="form-group">',
                    '<label class="col-sm-5 control-label" style="padding-left: 13px !important;"> Show Criteria </label>',
                    '<div class="col-sm-7">',
                    '<select class="form-control input-sm colorSchemeCriteria">',
                    '<option value="0" checked>0</option>',
                    '<option value="1">1</option>',
                    '<option value="2">2</option>',
                    '<option value="3">3</option>',
                    '<option value="4">4</option>',
                    '<option value="5">5</option>',
                    '</select>',
                    '</div>',
                    '</div>',

                    '<div class="groupColorSchemeCriteria0" >',
                    '<div class="col-md-7" style="padding: 0;">',
                    '<label> Value </label>',
                    '</div>',
                    '<div class="col-md-5">',
                    '<label> Pick Color</label>',
                    '</div>',
                    '</div>',

                    '<div class="form-group groupColorSchemeCriteria1">',
                    '<div class="col-md-7">',
                    '<input type="text"  style="width:100%" class="rangeValue1">',
                    '</div>',
                    '<div class="col-md-5">',
                    '<input type="text"  style="width:100%" class="rangeColor rangeColor1">',
                    '</div>',
                    '</div>',

                    '<div class="form-group groupColorSchemeCriteria2">',

                    '<div class="col-md-7">',
                    '<input type="text"  style="width:100%" class="rangeValue2">',
                    '</div>',
                    '<div class="col-md-5">',
                    '<input type="text" style="width:100%" class="rangeColor rangeColor2">',
                    '</div>',

                    '</div>',

                    '<div class="form-group groupColorSchemeCriteria3">',

                    '<div class="col-md-7">',
                    '<input type="text"  style="width:100%" class="rangeValue3">',
                    '</div>',
                    '<div class="col-md-5">',
                    '<input type="text"  style="width:100%" class="rangeColor rangeColor3">',
                    '</div>',

                    '</div>',

                    '<div class="form-group groupColorSchemeCriteria4">',

                    '<div class="col-md-7">',
                    '<input type="text"  style="width:100%" class="rangeValue4">',
                    '</div>',
                    '<div class="col-md-5">',
                    '<input type="text"  style="width:100%" class="rangeColor rangeColor4">',
                    '</div>',

                    '</div>',

                    '<div class="form-group groupColorSchemeCriteria5">',

                    '<div class="col-md-7">',
                    '<input type="text"  style="width:100%" class="rangeValue5">',
                    '</div>',
                    '<div class="col-md-5">',
                    '<input type="text" style="width:100%" class="rangeColor rangeColor5">',
                    '</div>',

                    '</div>',
                    '</div>',
                    //by value
                    '</form>'].join("\n"));
            }
            this.formContainer.find(".rangeIntervalColor, .rangeIntervalColorNumeric, .rangeColor")
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

        showByOptionValueInput: function () {
            var _this = this;

            this.formContainer.find("#byValue").hide();
            this.formContainer.find("#byRange").hide();
            this.formContainer.find("#byRangePercent").show();

            for (var i = 0; i <= 5; i++) {
                this.formContainer.find('.groupColorScheme' + i).hide();
            }

            this.formContainer.find('input[name="NumericOption"]').on('click', function () {
                var rangeVal = _this.formContainer.find('input[name="NumericOption"]:checked').val();
                if (rangeVal == "value") {
                    _this.formContainer.find("#byValue").show();
                    _this.formContainer.find("#byRange").hide();
                    _this.formContainer.find("#byRangePercent").hide();

                } else if (rangeVal == "rangeNumeric") {
                    _this.formContainer.find("#byValue").hide();
                    _this.formContainer.find("#byRange").show();
                    _this.formContainer.find("#byRangePercent").hide();

                } else if (rangeVal == "rangePercent") {
                    _this.formContainer.find("#byValue").hide();
                    _this.formContainer.find("#byRange").hide();
                    _this.formContainer.find("#byRangePercent").show();
                }
            });
        },

        getByOptionSetting: function () {
            return this.formContainer.find('input[name="NumericOption"]:checked').val();
        },

        setByOptionSetting: function (value) {
            this.formContainer.find('input[name="NumericOption"][value="' + value + '"]').prop('checked', true);
        },

        getReportColorScheme: function () {
            return this.formContainer.find('.colorSchemeInterval option:selected').val();
        },

        setReportColorScheme: function (selectedValue) {
            this.formContainer.find('.colorSchemeInterval option[value="' + selectedValue + '"]').prop('selected', true);
        },

        getReportColorScheme2: function () {
            return this.formContainer.find('.colorSchemeInterval2 option:selected').val();
        },

        setReportColorScheme2: function (selectedValue) {
            this.formContainer.find('.colorSchemeInterval2 option[value="' + selectedValue + '"]').prop('selected', true);
        },

        getReportColorScheme3: function () {
            return this.formContainer.find('.colorSchemeCriteria option:selected').val();
        },

        setReportColorScheme3: function (selectedValue) {
            this.formContainer.find('.colorSchemeCriteria option[value="' + selectedValue + '"]').prop('selected', true);
        },

        //For Range percent
        setRangeIntervalFrom: function (counter, value) {
            this.formContainer.find('input[name="rangeIntervalFrom' + counter + '"]').val(value);
        },

        getRangeIntervalFrom: function (counter) {
            return this.formContainer.find('input[name="rangeIntervalFrom' + counter + '"]').val();
        },

        setRangeIntervalTo: function (counter, value) {
            this.formContainer.find('input[name="rangeIntervalTo' + counter + '"]').val(value);
        },

        getRangeIntervalTo: function (counter) {
            return this.formContainer.find('input[name="rangeIntervalTo' + counter + '"]').val();
        },

        setRangeIntervalColor: function (counter, value) {
            this.formContainer.find('input[name="rangeIntervalColor' + counter + '"]').spectrum('set', value);
        },

        getRangeIntervalColor: function (counter) {
            return this.formContainer.find('input[name="rangeIntervalColor' + counter + '"]').val();
        },

        //For Range Percent
        getRangeIntervalFromNumeric: function (counter) {
            return this.formContainer.find('input[name="rangeIntervalFromNumeric' + counter + '"]').val();
        },

        setRangeIntervalFromNumeric: function (counter, value) {
            this.formContainer.find('input[name="rangeIntervalFromNumeric' + counter + '"]').val(value);

        },
        getRangeIntervalToNumeric: function (counter) {
            return this.formContainer.find('input[name="rangeIntervalToNumeric' + counter + '"]').val();
        },

        setRangeIntervalToNumeric: function (counter, value) {
            this.formContainer.find('input[name="rangeIntervalToNumeric' + counter + '"]').val(value);

        },

        getRangeIntervalColorNumeric: function (counter) {
            return this.formContainer.find('input[name="rangeIntervalColorNumeric' + counter + '"]').val();
        },

        setRangeIntervalColorNumeric: function (counter, value) {
            this.formContainer.find('input[name="rangeIntervalColorNumeric' + counter + '"]').val(value);

        },

        //For rangeNumeric
        setRangeValue: function (counter, value) {
            this.formContainer.find('.rangeValue' + counter).val(value);
        },

        getRangeValue: function (counter) {
            return this.formContainer.find('.rangeValue' + counter).val();
        },

        setRangeColor: function (counter, value) {
            this.formContainer.find('.rangeColor' + counter).spectrum('set', value);
        },

        getRangeColor: function (counter) {
            return this.formContainer.find('.rangeColor' + counter).val();
        },


        setEditOptions: function () {
            var sel_count = 0;
            var i = 0;
            var formContainer = this.getFormContainer();
            if (this.object.colorScheme == 'rangePercent') {
                this.setByOptionSetting(this.object.colorScheme);
                this.setReportColorScheme(this.object.intervals);
                sel_count = this.object.intervals;
                if (sel_count > 0) {
                    formContainer.find('.groupColorScheme0').show();
                }
                for (i = 1; i < sel_count + 1; i++) {
                    formContainer.find('.groupColorScheme' + i).show();
                    this.setRangeIntervalFrom(i, this.object['rangeIntervalFrom' + i]);
                    this.setRangeIntervalTo(i, this.object['rangeIntervalTo' + i]);
                    this.setRangeIntervalColor(i, this.object['rangeIntervalColor' + i]);
                }
            }

            if (this.object.colorScheme == 'rangeNumeric') {
                this.setByOptionSetting(this.object.colorScheme);
                this.setReportColorScheme2(this.object.intervals);
                sel_count = this.object.intervals;
                if (sel_count > 0) {
                    formContainer.find('.groupColorSchemeValue0').show();
                }
                for (i = 1; i < sel_count + 1; i++) {
                    formContainer.find('.groupColorSchemeValue' + i).show();
                    this.setRangeIntervalFromNumeric(i, this.object['rangeIntervalFromNumeric' + i]);
                    this.setRangeIntervalToNumeric(i, this.object['rangeIntervalToNumeric' + i]);
                    this.setRangeIntervalColorNumeric(i, this.object['rangeIntervalColor' + i]);
                }
            }

            if (this.object.colorScheme == 'value') {

                this.setByOptionSetting(this.object.colorScheme);
                this.setReportColorScheme3(this.object.criteria);
                sel_count = this.object.criteria;
                if (sel_count > 0) {
                    formContainer.find('.groupColorSchemeCriteria0').show();
                }
                for (i = 1; i < sel_count + 1; i++) {
                    formContainer.find('.groupColorSchemeCriteria' + i).show();
                    this.setRangeValue(i, this.object['value' + i]);
                    this.setRangeColor(i, this.object['color' + i]);
                }
            }
            var rangeVal = formContainer.find('input[name="NumericOption"]:checked').val();
            if (rangeVal == "value") {
                formContainer.find("#byValue").show();
                formContainer.find("#byRange").hide();
                formContainer.find("#byRangePercent").hide();
            } else if (rangeVal == "rangeNumeric") {
                formContainer.find("#byValue").hide();
                formContainer.find("#byRange").show();
                formContainer.find("#byRangePercent").hide();

            } else if (rangeVal == "rangePercent") {
                formContainer.find("#byValue").hide();
                formContainer.find("#byRange").hide();
                formContainer.find("#byRangePercent").show();
            }
        },

        showHideColorSchemeInput: function () {
            var _this = this;
            var i = 0;
            if (_this.getReportColorScheme() == 0) {
                for (i = 0; i <= 5; i++) {
                    this.formContainer.find('.groupColorScheme' + i).hide();
                }
            }

            if (_this.getReportColorScheme2() == 0) {
                for (i = 0; i <= 5; i++) {
                    this.formContainer.find('.groupColorSchemeValue' + i).hide();
                }
            }

            if (_this.getReportColorScheme3() == 0) {
                for (i = 0; i <= 5; i++) {
                    this.formContainer.find('.groupColorSchemeCriteria' + i).hide();
                }
            }


            this.formContainer.find('.colorSchemeInterval').change(function () {
                var count = _this.getReportColorScheme();
                for (i = 0; i <= 5; i++) {
                    _this.formContainer.find('.groupColorScheme' + i).hide();
                }
                for (i = 0; i <= count; i++) {
                    _this.formContainer.find('.groupColorScheme' + i).show();
                }
            });

            this.formContainer.find('.colorSchemeInterval2').change(function () {
                var count2 = _this.getReportColorScheme2();
                for (i = 0; i <= 5; i++) {
                    _this.formContainer.find('.groupColorSchemeValue' + i).hide();
                }
                for (i = 0; i <= count2; i++) {
                    _this.formContainer.find('.groupColorSchemeValue' + i).show();
                }
            });

            this.formContainer.find('.colorSchemeCriteria').change(function () {
                var count3 = _this.getReportColorScheme3();
                for (i = 0; i <= 5; i++) {
                    _this.formContainer.find('.groupColorSchemeCriteria' + i).hide();
                }
                for (i = 0; i <= count3; i++) {
                    _this.formContainer.find('.groupColorSchemeCriteria' + i).show();
                }
            });

        },

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

        saveOptions: function () {
            var _this = this;
            var i = 0;
            if (_this.getByOptionSetting() == 'rangePercent') {
                this.object.colorScheme = 'rangePercent';
                this.object.intervals = this.formContainer.find('.colorSchemeInterval option:selected').val();

                for (i = 1; i <= _this.getReportColorScheme(); i++) {
                    this.object['rangeIntervalFromNumeric' + i] = '';
                    this.object['rangeIntervalToNumeric' + i] = '';
                    this.object['rangeIntervalFrom' + i] = this.getRangeIntervalFrom(i);
                    this.object['rangeIntervalTo' + i] = this.getRangeIntervalTo(i);
                    this.object['rangeIntervalColor' + i] = this.getRangeIntervalColor(i);
                }
            }

            if (_this.getByOptionSetting() == 'rangeNumeric') {

                this.object.colorScheme = 'rangeNumeric';
                this.object.intervals = this.formContainer.find('.colorSchemeInterval2 option:selected').val();
                for (i = 1; i <= _this.getReportColorScheme2(); i++) {
                    this.object['rangeIntervalFrom' + i] = '';
                    this.object['rangeIntervalTo' + i] = '';
                    this.object['rangeIntervalFromNumeric' + i] = this.getRangeIntervalFromNumeric(i);
                    this.object['rangeIntervalToNumeric' + i] = this.getRangeIntervalToNumeric(i);
                    this.object['rangeIntervalColor' + i] = this.getRangeIntervalColorNumeric(i)
                }
            }

            if (_this.getByOptionSetting() == 'value') {
                this.object.colorScheme = 'value';
                this.object.criteria = this.formContainer.find('.colorSchemeCriteria option:selected').val();

                for (i = 1; i <= _this.getReportColorScheme3(); i++) {

                    this.object['value' + i] = this.getRangeValue(i);
                    this.object['color' + i] = this.getRangeColor(i);
                }
            }

            this.getController().getReport().setColumnDisplayOption(this.object);
        },

        render: function () {
            if (this.container) {
                this.container.append(this.getFormContainer());
                this.showByOptionValueInput();
                this.showHideColorSchemeInput();
                if (this.object.columnId) {
                    this.setEditOptions();
                }
            }
            return this.getFormContainer();
        }

    });

    GD.ReportColumnDisplayPieNumericForm = ReportColumnDisplayPieNumericForm;

})(typeof window === 'undefined' ? this : window, jQuery);