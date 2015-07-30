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
        throw new Error('DashboardWidgetImage requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DashboardWidgetImage requires GD');
    }

    var GD = global.GD;

    var DashboardWidgetImage = GD.DashboardWidget.extend({

        init: function(object, options) {
            this._super(object, options);

            this.type = 'image';

            if ( !this.width || !this.height ) {
                this.width = 150;
                this.height = 150;
            }

            if ( !this.content ) {
                this.content = {
                    src: '',
                    alt: ''
                };
            }

            var _this = this;
            this.attachCanvasItemEvent('dblclick',function(){
                _this.getEditorWindow().modal('show');

                if ( _this.content.src ) {
                    _this.getEditor().find('#dashboard-widget-image-' + _this.getId() + '-src').val(_this.content.src);
                }

                _this.getEditor().find('#dashboard-widget-image-'+_this.getId()+'-alt').val(_this.content.alt);

            });

        },

        setContent: function(content) {
            this.content = content;
            this.getCanvasItem().find('.inner-content img').attr('src',content.src).attr('alt',content.alt);
        },

        loadView: function ( callback ) {
            this.getCanvasItem().find('.inner-content').html('<img width="100%" height="100%" src="'+this.getContent().src+'" alt="'+this.getContent().alt+'" />');
            if ( callback ) {
                callback();
            }
        },

        getEditorWindow: function () {
            if ( !this.editorWindow ) {

                var modal = [
                    '<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">>',
                        '<div class="modal-dialog">',
                            '<div class="modal-content">',
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

                this.editorWindow.modal({
                    show: false,
                    backdrop: false
                });

                this.editorWindow.find('.modal-body').append(this.getEditor());
                this.editorWindow.find('.modal-footer').prepend(this.getSaveButton());
            }

            return this.editorWindow;
        },

        getEditor: function () {
            if ( !this.editor ) {

                var form = [
                    '<div class="form-group">',
                        '<label for="dashboard-widget-image-'+this.getId()+'-src">Image URL</label>',
                        '<input type="text" class="form-control" id="dashboard-widget-image-'+this.getId()+'-src" placeholder="http://">',
                    '</div>',
                    '<div class="form-group">',
                        '<label for="dashboard-widget-image-'+this.getId()+'-alt">Image Alt Text</label>',
                        '<input type="text" class="form-control" id="dashboard-widget-image-'+this.getId()+'-alt" placeholder="">',
                    '</div>'
                ];

                this.editor = $(form.join("\n"));
            }

            return this.editor;
        },

        getSaveButton: function () {
            if ( !this.saveButton ) {
                this.saveButton = $('<button type="button" class="btn btn-primary">Save</button>');

                var _this = this;
                this.saveButton.on('click',function(){
                    _this.setContent({
                        src: _this.getEditor().find('#dashboard-widget-image-'+_this.getId()+'-src').val(),
                        alt: _this.getEditor().find('#dashboard-widget-image-'+_this.getId()+'-alt').val()
                    });
                    _this.getEditorWindow().modal('hide');
                });
            }

            return this.saveButton;
        }

    });

    // add to global space
    global.GD.DashboardWidgetImage = DashboardWidgetImage;

})(typeof window === 'undefined' ? this : window, jQuery);