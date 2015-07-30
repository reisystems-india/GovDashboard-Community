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
        throw new Error('ReportColumnDisplayStringForm requires jQuery');
    }

    if (typeof global.GD === 'undefined') {
        throw new Error('ReportColumnDisplayStringForm requires GD');
    }

    var GD = global.GD;

    var ReportColumnDisplayStringForm = GD.View.extend({

        formContainer: null,
        form: null,
        modal: null,
        deleteButton: null,
        cancelButton: null,
        applyButton: null,

        init: function (object, container, options) {
            this._super(object, container, options);
            var _this = this;
            $(document).on('save.report.visualize', function () {
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
                    '<h5>Column Display for Column "' + this.object.displayName + '"</h5>',
                    '<div class="form-group">',
                    '<label class="radio-inline">',
                    '<input type="radio" name="reportColumnDisplayNumericOption" value="1" checked> By Value',
                    '</label>',
                    '</div>',
                    '<div class="form-group">',
                    '<div class="row">',
                    '<div class="col-md-5">',
                    '<label>Show Criteria :</label>',
                    '</div>',
                    '<div class="col-md-7">',
                    '<select id="criteriaColumnDisplay" class="form-control input-sm">',
                    '<option value="0" selected="selected">0</option>',
                    '<option value="1">1</option>',
                    '<option value="2">2</option>',
                    '<option value="3">3</option>',
                    '<option value="4">4</option>',
                    '<option value="5">5</option>',
                    '</select>',
                    '</div>',
                    '</div>',
                    '</div>',
                    '<div class="form-group groupColumnDisplayString">',
                    '<div class="row">',
                    '<div class="col-md-7">',
                    '<label>Value</label>',
                    '</div>',
                    '<div class="col-md-5">',
                    '<label>Pick Color</label>',
                    '</div>',
                    '</div>',
                    '</div>',

                    '<div class="form-group groupColumnDisplay1">',
                    '<div class="row">',
                    '<div class="col-md-7">',
                    '<input type="text" class="criteriaValue1" style="width:100%">',
                    '</div>',
                    '<div class="col-md-5">',
                    '<input type="text" class="criteriaColor criteriaColor1" style="width:100%">',
                    '</div>',
                    '</div>',
                    '</div>',

                    '<div class="form-group groupColumnDisplay2">',
                    '<div class="row">',
                    '<div class="col-md-7">',
                    '<input type="text" class="criteriaValue2" style="width:100%">',
                    '</div>',
                    '<div class="col-md-5">',
                    '<input type="text" class="criteriaColor criteriaColor2" style="width:100%">',
                    '</div>',
                    '</div>',
                    '</div>',

                    '<div class="form-group groupColumnDisplay3">',
                    '<div class="row">',
                    '<div class="col-md-7">',
                    '<input type="text" class="criteriaValue3" style="width:100%">',
                    '</div>',
                    '<div class="col-md-5">',
                    '<input type="text" class="criteriaColor criteriaColor3" style="width:100%">',
                    '</div>',
                    '</div>',
                    '</div>',

                    '<div class="form-group groupColumnDisplay4">',
                    '<div class="row">',
                    '<div class="col-md-7">',
                    '<input type="text" class="criteriaValue4" style="width:100%">',
                    '</div>',
                    '<div class="col-md-5">',
                    '<input type="text" class="criteriaColor criteriaColor4" style="width:100%">',
                    '</div>',
                    '</div>',
                    '</div>',

                    '<div class="form-group groupColumnDisplay5">',
                    '<div class="row">',
                    '<div class="col-md-7">',
                    '<input type="text" class="criteriaValue5" style="width:100%">',
                    '</div>',
                    '<div class="col-md-5">',
                    '<input type="text" class="criteriaColor criteriaColor5" style="width:100%">',
                    '</div>',
                    '</div>',
                    '</div>'
                ].join("\n"));
            }
            this.formContainer.find($(".criteriaColor"))
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

        getReportColumnDisplay: function () {
            var count = 0;
            count = this.getFormContainer().find('#criteriaColumnDisplay option:selected').val();
            return count;
        },

        setReportColumnDisplay: function (selectedValue) {
            $('#criteriaColumnDisplay option[value="' + selectedValue + '"]').prop('selected', true);
        },

        getCriteriaValue: function (counter) {
            return this.getFormContainer().find('.criteriaValue' + counter).val();
        },

        setCriteriaValue: function (counter, value) {
            this.getFormContainer().find('.criteriaValue' + counter).val(value);

        },

        setdefaultCriteriaColorValue: function () {
            var reportObj = this.getController().getReport(),
                sel_count = parseInt(reportObj.getVisual().series[this.object.columnId].criteria),
                criteriaValue = reportObj.getVisual().series[this.object.columnId];
            for (var i = 1; i < sel_count + 1; i++) {
                this.setCriteriaValue(i, criteriaValue['value' + i]);
                this.setCriteriaColor(i, criteriaValue['color' + i]);
            }
        },

        getCriteriaColor: function (counter) {
            return this.getFormContainer().find('.criteriaColor' + counter).val();
        },

        setCriteriaColor: function (counter, value) {
            this.getFormContainer().find('.criteriaColor' + counter).spectrum('set', value);
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

        showColumnDisplayInput: function () {
            var _this = this;
            for (var i = 1; i < 6; i++) {
                $('.groupColumnDisplay' + i).hide();
            }

            var count = _this.getReportColumnDisplay();
            if (count > 0) {
                $('.groupColumnDisplayString').show();
            } else {
                $('.groupColumnDisplayString').hide();
            }
            for (var i = 1; i <= count; i++) {
                $('.groupColumnDisplay' + i).show();
            }
        },

        showHideColumnDisplayInput: function () {
            var _this = this;
            this.getFormContainer().find('#criteriaColumnDisplay').change(function () {
                var count = _this.getReportColumnDisplay();
                if (count > 0) {
                    $('.groupColumnDisplayString').show();
                } else {
                    $('.groupColumnDisplayString').hide();
                }
                for (var i = 1; i <= 5; i++) {
                    $('.groupColumnDisplay' + i).hide();
                }
                for (var i = 1; i <= count; i++) {
                    $('.groupColumnDisplay' + i).show();
                }
            });
        },

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

        showColumnDisplayStringEditView: function () {
            this.setReportColumnDisplay(this.object.criteria);
            var sel_count = parseInt(this.object.criteria);
            if (sel_count > 0) {
                $('.groupColumnDisplayString').show();
            } else {
                $('.groupColumnDisplayString').hide();
            }
            for (var i = 1; i < sel_count + 1; i++) {
                $('.groupColumnDisplay' + i).show();
                this.setCriteriaValue(i, this.object['value' + i]);
                this.setCriteriaColor(i, this.object['color' + i]);
            }
        },

        saveOptions: function () {

            this.object.options = {
                displayDataLabels: 1,
                displayTitle: 'yes'
            };

            this.object.colorScheme = 'value';
            this.object.criteria = this.getReportColumnDisplay();


            var sel_count = parseInt(this.getReportColumnDisplay());
            for (var i = 1; i < sel_count + 1; i++) {
                this.object['value' + i] = this.getCriteriaValue(i);
                this.object['color' + i] = this.getCriteriaColor(i);
            }

            this.getController().getReport().setColumnDisplayOption(this.object);


        },

        render: function () {
            if (this.container) {
                this.container.append(this.getFormContainer());

                this.showColumnDisplayInput();
                this.showHideColumnDisplayInput();
                if (this.object.columnId) {
                    this.showColumnDisplayStringEditView();
                }
            }
            return this.getFormContainer();
        }

    });

    GD.ReportColumnDisplayStringForm = ReportColumnDisplayStringForm;

})(typeof window === 'undefined' ? this : window, jQuery);