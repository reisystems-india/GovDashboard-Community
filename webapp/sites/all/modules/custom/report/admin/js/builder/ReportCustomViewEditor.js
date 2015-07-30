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
        throw new Error('ReportCustomViewEditor requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportCustomViewEditor requires GD');
    }

    var GD = global.GD;

    var ReportCustomViewEditor = GD.View.extend({

        editor: null,
        editorContainer: null,
        editorDataView: null,
        editorInitialized: false,

        init: function(object, container, options) {
            this._super(object, container, options);

            this.initEditorContainer();
            this.container.append(this.getEditorContainer());

            if ( this.getController().getReport().getChartType() == 'customview' ) {
                this.initEditor();
                this.container.show();
            } else {
                this.container.hide();
            }
        },

        getController: function() {
            return this.object;
        },

        initEditor: function() {
            this.editor = ace.edit("reportBuilderEditorView");
            this.editor.getSession().setMode("ace/mode/html");
            this.editorInitialized = true;
            var reportObj = this.getController().getReport();
            if ( reportObj ) {
                this.editor.setValue(reportObj.getCustomView());
            }
        },

        getEditor: function() {
            if (!this.editor) {
                this.initEditor();
            }

            return this.editor;
        },

        initEditorContainer: function() {
            this.editorContainer = $('<div></div>');

            var applyButton = $('<button class="pull-right btn btn-default">Apply</button>');
            var _this = this;
            applyButton.on('click',function() {
                _this.getController().getReport().setCustomView(_this.getCustomViewValue());
                $(document).trigger({
                    type: 'changed.report.customview'
                });
            });

            this.editorContainer.append('<h5 style="padding: 0;">Data</h5>',this.getEditorDataView(),'<h5 style="padding: 0;">Code</h5>','<div class="well"><div id="reportBuilderEditorView"></div></div>', applyButton);
        },

        getEditorContainer: function() {
            if (!this.editorContainer) {
                this.initEditorContainer();
            }

            return this.editorContainer;
        },

        getEditorDataView: function () {
            if (!this.editorDataView) {
                this.editorDataView = $('<div id="reportBuilderEditorDataView" class="reportCustomData" style="margin: 10px 0;text-align: justify;word-break: break-word;padding: 10px;overflow-y: auto;height: 150px;border: 1px solid #e3e3e3;"></div>');
            }

            return this.editorDataView;
        },

        getCustomViewValue: function() {
            return this.editorInitialized ? this.getEditor().getValue() : null;
        },

        getReportData: function () {
            var _this = this,
                reportObj = _this.getController().getReport();
            if ( reportObj.getDataset() && reportObj.getColumns() ) {
                GD.ReportFactory.getData(reportObj.getConfig(), function (data) {
                    _this.getEditorDataView().html('<pre>'+JSON.stringify(data.response.data,null,2)+'</pre>');
                }, function (jqXHR, textStatus, errorThrown) {
                    global.GD.ReportBuilderMessagingView.addErrors(jqXHR.responseText);
                    global.GD.ReportBuilderMessagingView.displayMessages();
                });
            }
        },

        attachEventHandlers: function() {
            var _this = this,
                reportObj = _this.getController().getReport();

            $(document).on('changed.report.type', function() {
                if ( reportObj.getChartType() == 'customview' ) {
                    if ( !_this.editorInitialized ) {
                        _this.initEditor();
                    }
                    _this.container.show();
                } else {
                    _this.container.hide();
                }
            });

            $(document).on('reloaded.report.preview', function() {
                if ( reportObj.getChartType() == 'customview' ) {
                    _this.getReportData();
                }
            });
        }

    });

    GD.ReportCustomViewEditor = ReportCustomViewEditor;

})(typeof window === 'undefined' ? this : window, jQuery);