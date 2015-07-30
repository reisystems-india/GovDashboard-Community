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
        throw new Error('DashboardSaveAsButton requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DashboardSaveAsButton requires GD');
    }

    var GD = global.GD;

    var DashboardSaveAsButton = GD.BuilderButton.extend({
        modal: null,

        init: function(object, options) {
            if (object) {
                this.dashboard = object;
            } else {
                this.dashboard = GD.Dashboard.singleton;
            }

            this.buttonTop = $('#dashboardSaveAsTop');
            this.buttonBottom = $('#dashboardSaveAsBottom');

            this._super(null, options);
        },
        changeButton:function(changeParam){
            this.getButtonTop().button(changeParam);
            this.getButtonBottom().button(changeParam);
        },

        clickedButton: function( e ) {

            this.getEditorWindow();
            this.closeConfigForms();
            this.getEditor().find('span').removeClass('glyphicon-remove');
            this.getEditor().removeClass('has-error');
            this.getEditor().find('div.help-block').html('The title cannot be empty and must be unique.');

            this.getEditorWindow().modal();
        },

        getEditorWindow: function () {
            if ( !this.editorWindow ) {

                var modal = [
                    '<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">',
                        '<div class="modal-dialog">',
                            '<div class="modal-content">',
                                '<div class="modal-header"><h4>Save Dashboard As...</h4></div>',
                                '<div class="modal-body">',
                                '</div>',
                                '<div class="modal-footer">',
                                   
                                '</div>',
                            '</div>',
                        '</div>',
                    '</div>'
                ];

                this.editorWindow = $(modal.join("\n"));


                this.editorWindow.find('.modal-body').append(this.getEditor());
                this.editorWindow.find('.modal-footer').append(this.getSaveButton());
                this.editorWindow.find('.modal-footer').append('<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>');

                $('body').append(this.editorWindow);
            }

            return this.editorWindow;
        },

        getEditor: function () {
            if ( !this.editor ) {

                var form = [
                    '<div class="form-group has-feedback">',
                        '<label class="control-label" for="dashboard-saveas-name">Enter a new dashboard title:</label>',
                        '<input type="text" class="form-control" id="dashboard-saveas-name" placeholder="" />',
                        '<span class="glyphicon form-control-feedback"></span>',
                        '<div class="help-block">The title cannot be empty and must be unique.</div>',
                    '</div>'
                ];

                this.editor = $(form.join("\n"));
            }

            return this.editor;
        },

        getSaveButton: function () {
            if ( !this.saveButton ) {
                this.saveButton = $('<button type="button" class="btn btn-primary" data-loading-text="Checking...">Save</button>');

                var _this = this;
                this.saveButton.on('click',function(){
                    _this.getSaveButton().button('loading');

                    var dashboardName = _this.getEditor().find('input').val();

                    if ( !dashboardName || dashboardName.replace(/^\s+|\s+$/g,'') === '' ) {
                        _this.getEditor().find('span').addClass('glyphicon-remove');
                        _this.getEditor().addClass('has-error');
                        _this.getEditor().find('div.help-block').html('Title is required.');
                        _this.getSaveButton().button('reset');
                    } else {
                        // look up name
                        GD.DashboardFactory.getDashboardList(_this.getController().getDatasourceName(), function ( data ) {
                            var unique = true;
                            if ( data && $.isArray(data) ) {
                                for (var i = 0, dataLength=data.length; i < dataLength; i++) {
                                    if ( data[i].name == dashboardName ) {
                                        unique = false;
                                        break;
                                    }
                                }
                            }

                            if ( unique ) {
                                _this.getEditor().find('span').removeClass('glyphicon-remove').addClass('glyphicon-ok');
                                _this.getEditor().removeClass('has-error').addClass('has-success');
                                _this.getEditor().find('div.help-block').html('Title is ok.');

                                _this.getEditorWindow().modal('hide');
                                _this.getSaveButton().button('reset');

                                _this.getController().copyDashboard(dashboardName);

                            } else {
                                _this.getSaveButton().button('reset');
                                _this.getEditor().find('span').addClass('glyphicon-remove');
                                _this.getEditor().addClass('has-error');
                                _this.getEditor().find('div.help-block').html('Title is not unique.');
                            }

                        }, function(jqXHR, textStatus, errorThrown) {
                            _this.getSaveButton().button('reset');
                            _this.getEditorWindow().modal('hide');
                            global.GD.DashboardBuilderMessagingView.addErrors(jqXHR.responseText);
                            global.GD.DashboardBuilderMessagingView.displayMessages();
                        });
                    }


                });
            }

            return this.saveButton;
        }
    });

    GD.DashboardSaveAsButton = DashboardSaveAsButton;

})(typeof window === 'undefined' ? this : window, jQuery);