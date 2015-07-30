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
        throw new Error('ReportFooterEditForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportFooterEditForm requires GD');
    }

    var GD = global.GD;

    GD.ReportFooterEditForm = GD.View.extend({
        formContainer: null,
        form: null,
        modal: null,
        deleteButton: null,
        cancelButton: null,
        applyButton: null,

        getController: function () {
            return this.options.builder;
        },

        isNew: function() {
            return (this.object['new']);
        },

        getFormContainer: function() {
            if ( !this.formContainer ) {
                var report = this.getController().getReport();
                var column = report.getColumn(this.object['measure']);
                var displayName = "";
                if (column) {
                    displayName = column['name'];
                } else {
                    displayName = "Unsupported Column: " + this.object['measure'];
                }
                this.formContainer = $([
                    '<h5>Footer for "'+displayName+'"</h5>',
                    '<form class="form-horizontal">',
                        '<div class="form-group">',
                            '<label class="col-sm-2 control-label" for="reportFooterTextValue">Text:</label>',
                            '<div class="col-sm-10">',
                                '<input type="text" class="form-control" id="reportFooterTextValue">',
                            '</div>',
                        '</div>',
                        '<div class="form-group">',
                            '<label class="col-sm-12">Alignment:</label>',
                            '<div class="col-sm-4">',
                                '<label class="radio-inline">',
                                    '<input type="radio" name="reportFooterAlignmentOption" value="left"> Left',
                                '</label>',
                            '</div>',
                            '<div class="col-sm-4">',
                                '<label class="radio-inline">',
                                    '<input type="radio" name="reportFooterAlignmentOption" value="center"> Center',
                                '</label>',
                            '</div>',
                            '<div class="col-sm-4">',
                                '<label class="radio-inline">',
                                    '<input type="radio" name="reportFooterAlignmentOption" value="right" checked> Right',
                                '</label>',
                            '</div>',
                        '</div>',
                        '<div class="form-group">',
                            '<label class="col-sm-5 control-label" for="reportFooterSize"> Text Size : </label>',
                            '<div class="col-sm-7">',
                                '<input type="text" class="form-control input-sm" style="width:100%" id="reportFooterSize">',
                            '</div>',
                        '</div>',

                        '<div class="form-group">',
                            '<label class="col-sm-5 control-label" for="reportFooterFont"> Font : </label>',
                            '<div class="col-sm-7">',
                                '<select class="form-control input-sm" id="reportFooterFont">',
                                    '<option value="arial,helvetica,sans-serif">Arial</option>',
                                    '<option value="courier new,courier,monospace">Courier New </option>',
                                    '<option value="georgia,times new roman,times,serif">Georgia </option>',
                                    '<option value="tahoma,arial,helvetica,sans-serif">Tahoma</option>',
                                    '<option value="times new roman,times,serif">Times New Roman</option>',
                                    '<option value="verdana,arial,helvetica">Verdana</option>',
                                    '<option value="impact">Impact</option>',
                                '</select>',
                            '</div>',
                        '</div>',

                        '<div class="form-group">',
                            '<label class="col-sm-5 control-label" for="reportFooterColor"> Color : </label>',
                            '<div class="col-sm-7">',
                                '<input type="text" class="form-control input-sm" style="width:100%" id="reportFooterColor">',
                            '</div>',
                        '</div>',

                        '<div class="form-group">',
                            '<label class="col-sm-12">Display As: </label>',
                            '<div class="col-sm-4">',
                                '<label class="radio-inline">',
                                    '<input type="radio" name="reportFooterFormat" value="number" checked> Number',
                                '</label>',
                            '</div>',
                            '<div class="col-sm-4">',
                                '<label class="radio-inline">',
                                    '<input type="radio" name="reportFooterFormat" value="percent"> Percent',
                                '</label>',
                            '</div>',
                            '<div class="col-sm-4">',
                                '<label class="radio-inline">',
                                    '<input type="radio" name="reportFooterFormat" value="currency"> Currency',
                                '</label>',
                            '</div>',
                        '</div>',
                        '<div class="form-group">',
                            '<label class="col-sm-12" for="reportFooterScale">Decimal Places:</label>',
                            '<div class="col-sm-12">',
                                '<input type="text" class="form-control" id="reportFooterScale">',
                            '</div>',
                        '</div>',
                    '</form>'
                ].join("\n"));
                this.formContainer.find('#reportFooterTextValue').val(this.object['text']);
                this.formContainer.find('input[name="reportFooterAlignmentOption"][value="' + this.object['alignment'] + '"]').prop('checked', true);
                this.formContainer.find('#reportFooterSize').val(this.object['size'] ? this.object['size'] : 14);
                this.formContainer.find('#reportFooterFont').val(this.object['font'] ? this.object['font'] : "arial,helvetica,sans-serif");
                this.formContainer.find("#reportFooterColor").spectrum({
                    chooseText: "Apply",
                    cancelText: "Cancel",
                    preferredFormat: "hex",
                    showInput: true,
                    allowEmpty: true
                });
                this.formContainer.find("#reportFooterColor").spectrum('set', this.object['color'] ? this.object['color'] : '#000080');
                if (this.object['format']) {
                    this.formContainer.find('input[name="reportFooterFormat"][value="' + this.object['format'] + '"]').prop('checked', true);
                } else {
                    if (column) {
                        this.formContainer.find('input[name="reportFooterFormat"][value="' + column['type'] + '"]').prop('checked', true);
                    }
                }
                this.formContainer.find('#reportFooterScale').val(this.object['scale'] ? this.object['scale'] : 0);
            }

            return this.formContainer;
        },

        saveOptions: function() {
            this.object['text'] = this.getFormContainer().find('#reportFooterTextValue').val();
            this.object['alignment'] = this.getFormContainer().find('input[name="reportFooterAlignmentOption"]:checked').val();
            this.object['size'] = this.getFormContainer().find('#reportFooterSize').val();
            this.object['color'] = this.getFormContainer().find("#reportFooterColor").val();
            this.object['font'] = this.getFormContainer().find('#reportFooterFont').val();
            this.object['format'] = this.getFormContainer().find('input[name="reportFooterFormat"]:checked').val();
            this.object['scale'] = this.getFormContainer().find('#reportFooterScale').val();
            if (this.isNew()) {
                this.getController().getReport().addFooter(this.object);
            } else {
                this.getController().getReport().editFooter(this.object);
            }
        },

        render: function() {
            if ( this.container ) {
                this.container.append(this.getFormContainer());
            }
            return this.getFormContainer();
        }
    });
})(typeof window === 'undefined' ? this : window, jQuery);