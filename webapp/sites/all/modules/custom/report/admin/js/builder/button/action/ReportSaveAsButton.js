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
        throw new Error('ReportSaveAsButton requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportSaveAsButton requires GD');
    }

    var GD = global.GD;

    var ReportSaveAsButton = GD.BuilderButton.extend({
        modal: null,

        init: function(object, options) {

            this.buttonTop = $('#reportSaveAsTop');
            this.buttonBottom = $('#reportSaveAsBottom');

            this._super(null, options);

            var _this = this;
            $(document).on('report.update.post',function(e){
                //_this.getButton().button('reset');
            });

            //this.getEditorWindow();
        },

        clickedButton: function( e ) {

            //this.getButton().button('loading');
            //this.getController().saveReport();
            this.closeConfigForms();
            this.getEditorWindow();
            var editorObj = this.getEditor();
            $('span', editorObj).removeClass('glyphicon-remove');
            editorObj.removeClass('has-error');
            $('div.help-block', editorObj).html('The title cannot be empty and must be unique.');

            this.getEditorWindow().modal();
        },

        getEditorWindow: function () {
            if ( !this.editorWindow ) {

                var modal = [
                    '<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">',
                        '<div class="modal-dialog">',
                            '<div class="modal-content">',
                                '<div class="modal-header"><h4>Save Report As...</h4></div>',
                                '<div class="modal-body">',
                                '</div>',
                                '<div class="modal-footer">',
                                    '<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>',
                                '</div>',
                            '</div>',
                        '</div>',
                    '</div>'
                ];

                this.editorWindow = $(modal.join("\n"));


                this.editorWindow.find('.modal-body').append(this.getEditor());
                this.editorWindow.find('.modal-footer').prepend(this.getSaveButton());

                $('body').append(this.editorWindow);
            }

            return this.editorWindow;
        },

        getEditor: function () {
            if ( !this.editor ) {

                var form = [
                    '<div class="form-group has-feedback">',
                        '<label class="control-label" for="report-saveas-name">Enter a new report title:</label>',
                        '<input type="text" class="form-control" id="report-saveas-name" placeholder="" />',
                        '<span class="glyphicon form-control-feedback"></span>',
                        '<div class="help-block">The title cannot be empty and must be unique.</div>',
                    '</div>'
                ];

                this.editor = $(form.join("\n"));
            }

            return this.editor;
        },
        changeButton: function(changeParam){
            this.getButtonTop().button(changeParam);
            this.getButtonBottom().button(changeParam);
        },
        getSaveButton: function () {
            if ( !this.saveButton ) {
                this.saveButton = $('<button type="button" class="btn btn-primary" data-loading-text="Checking...">Save</button>');
                
                var _this = this,
                    controllerObj = _this.getController(),
                    savebuttonObj =  _this.getSaveButton();
                    editorData = _this.getEditor();
                    
                this.saveButton.on('click',function(){
                    savebuttonObj.button('loading');

                    var reportName = _this.getEditor().find('input').val();

                    if ( !reportName || reportName.replace(/^\s+|\s+$/g,'') === '' ) {
                        $('span', editorData).addClass('glyphicon-remove');
                        editorData.addClass('has-error');
                        $('div.help-block', editorData).html('Title is required.');
                        savebuttonObj.button('reset');
                    } else {
                        // look up name
                        GD.ReportFactory.getReportList(controllerObj.getDatasourceName(), function ( data ) {
                            var unique = true;
                            for ( var i in data ) {
                                if ( data[i].title == reportName ) {
                                    unique = false;
                                    break;
                                }
                            }

                            if ( unique ) {
                                $('span', editorData).removeClass('glyphicon-remove').addClass('glyphicon-ok');
                                editorData.removeClass('has-error').addClass('has-success');
                                $('div.help-block', editorData).html('Title is ok.');

                                _this.getEditorWindow().modal('hide');
                                savebuttonObj.button('reset');

                                _this.changeButton('loading');
                                controllerObj.copyReport(reportName);

                            } else {
                               savebuttonObj.button('reset');
                                $('span', editorData).addClass('glyphicon-remove');
                                editorData.addClass('has-error');
                                editorData('div.help-block', editorData).html('Title is not unique.');
                            }

                        }, function(jqXHR, textStatus, errorThrown) {
                            savebuttonObj.button('reset');
                            _this.getEditorWindow().modal('hide');
                            global.GD.ReportBuilderMessagingView.addErrors(jqXHR.responseText);
                            global.GD.ReportBuilderMessagingView.displayMessages();
                        });
                    }


                });
            }

            return this.saveButton;
        }
    });

    GD.ReportSaveAsButton = ReportSaveAsButton;

})(typeof window === 'undefined' ? this : window, jQuery);