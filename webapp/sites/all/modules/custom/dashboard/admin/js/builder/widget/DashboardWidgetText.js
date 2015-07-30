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
        throw new Error('DashboardWidgetText requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DashboardWidgetText requires GD');
    }

    var GD = global.GD;

    var DashboardWidgetText = GD.DashboardWidget.extend({

        init: function(object, options) {
            this._super(object, options);

            this.type = 'text';

            if ( !this.width || !this.height ) {
                this.width = 145;
                this.height = 35;
            }

            if ( !this.content ) {
                this.content = 'Double Click to Modify';
            }

            var _this = this;
            this.attachCanvasItemEvent('dblclick',function(){
                _this.getEditorWindow().modal('show');
                _this.getEditor().setHTML(_this.content);
            });
        },

        setContent: function(content) {
            this.content = content;
            this.getCanvasItem().find('.inner-content').html(content);
        },

        getEditorWindow: function () {
            if ( !this.editorWindow ) {
                var modal = [
                    '<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">>',
                        '<div class="modal-dialog">',
                            '<div class="modal-content">',
                                '<div class="modal-body">',
                                    '<div id="widget-text-editor-toolbar' + this.getId() + '">',
                                        '<span class="ql-format-group">',
                                            '<select title="Font" class="ql-font">',
                                                '<option value="Arial, Helvetica, sans-serif">Arial</option>',
                                                '<option value="\'Courier New\', Courier, monospace">Courier New</option>',
                                                '<option value="Georgia, serif">Georgia</option>',
                                                '<option value="Tahoma, Geneva, sans-serif">Tahoma</option>',
                                                '<option value="\'Times New Roman\', Times, serif">Times</option>',
                                                '<option value="Verdana, Geneva, sans-serif">Verdana</option>',
                                                '<option value="Impact, Charcoal, sans-serif">Impact</option>',
                                            '</select>',
                                            '<select title="Size" class="ql-size">',
                                                '<option value="8pt">8pt</option>',
                                                '<option value="10pt">10pt</option>',
                                                '<option value="12pt" selected>12pt</option>',
                                                '<option value="14pt">14pt</option>',
                                                '<option value="18pt">18pt</option>',
                                                '<option value="24pt">24pt</option>',
                                                '<option value="36pt">36pt</option>',
                                            '</select>',
                                        '</span>',
                                        '<span class="ql-format-group">',
                                            '<span title="Bold" class="ql-format-button ql-bold"></span>',
                                            '<span class="ql-format-separator"></span>',
                                            '<span title="Italic" class="ql-format-button ql-italic"></span>',
                                            '<span class="ql-format-separator"></span>',
                                            '<span title="Underline" class="ql-format-button ql-underline"></span>',
                                            '<span class="ql-format-separator"></span>',
                                        '</span>',
                                        '<span class="ql-format-group">',
                                            '<select title="Text Color" class="ql-color">',
                                                '<option value="rgb(0, 0, 0)" label="rgb(0, 0, 0)" selected=""></option>',
                                                '<option value="rgb(230, 0, 0)" label="rgb(230, 0, 0)"></option>',
                                                '<option value="rgb(255, 153, 0)" label="rgb(255, 153, 0)"></option>',
                                                '<option value="rgb(255, 255, 0)" label="rgb(255, 255, 0)"></option>',
                                                '<option value="rgb(0, 138, 0)" label="rgb(0, 138, 0)"></option>',
                                                '<option value="rgb(0, 102, 204)" label="rgb(0, 102, 204)"></option>',
                                                '<option value="rgb(153, 51, 255)" label="rgb(153, 51, 255)"></option>',
                                                '<option value="rgb(255, 255, 255)" label="rgb(255, 255, 255)"></option>',
                                                '<option value="rgb(250, 204, 204)" label="rgb(250, 204, 204)"></option>',
                                                '<option value="rgb(255, 235, 204)" label="rgb(255, 235, 204)"></option>',
                                                '<option value="rgb(255, 255, 204)" label="rgb(255, 255, 204)"></option>',
                                                '<option value="rgb(204, 232, 204)" label="rgb(204, 232, 204)"></option>',
                                                '<option value="rgb(204, 224, 245)" label="rgb(204, 224, 245)"></option>',
                                                '<option value="rgb(235, 214, 255)" label="rgb(235, 214, 255)"></option>',
                                                '<option value="rgb(187, 187, 187)" label="rgb(187, 187, 187)"></option>',
                                                '<option value="rgb(240, 102, 102)" label="rgb(240, 102, 102)"></option>',
                                                '<option value="rgb(255, 194, 102)" label="rgb(255, 194, 102)"></option>',
                                                '<option value="rgb(255, 255, 102)" label="rgb(255, 255, 102)"></option>',
                                                '<option value="rgb(102, 185, 102)" label="rgb(102, 185, 102)"></option>',
                                                '<option value="rgb(102, 163, 224)" label="rgb(102, 163, 224)"></option>',
                                                '<option value="rgb(194, 133, 255)" label="rgb(194, 133, 255)"></option>',
                                                '<option value="rgb(136, 136, 136)" label="rgb(136, 136, 136)"></option>',
                                                '<option value="rgb(161, 0, 0)" label="rgb(161, 0, 0)"></option>',
                                                '<option value="rgb(178, 107, 0)" label="rgb(178, 107, 0)"></option>',
                                                '<option value="rgb(178, 178, 0)" label="rgb(178, 178, 0)"></option>',
                                                '<option value="rgb(0, 97, 0)" label="rgb(0, 97, 0)"></option>',
                                                '<option value="rgb(0, 71, 178)" label="rgb(0, 71, 178)"></option>',
                                                '<option value="rgb(107, 36, 178)" label="rgb(107, 36, 178)"></option>',
                                                '<option value="rgb(68, 68, 68)" label="rgb(68, 68, 68)"></option>',
                                                '<option value="rgb(92, 0, 0)" label="rgb(92, 0, 0)"></option>',
                                                '<option value="rgb(102, 61, 0)" label="rgb(102, 61, 0)"></option>',
                                                '<option value="rgb(102, 102, 0)" label="rgb(102, 102, 0)"></option>',
                                                '<option value="rgb(0, 55, 0)" label="rgb(0, 55, 0)"></option>',
                                                '<option value="rgb(0, 41, 102)" label="rgb(0, 41, 102)"></option>',
                                                '<option value="rgb(61, 20, 102)" label="rgb(61, 20, 102)"></option>',
                                            '</select>',
                                            '<span class="ql-format-separator"></span>',
                                            '<select title="Background Color" class="ql-background">',
                                                '<option value="rgb(0, 0, 0)" label="rgb(0, 0, 0)"></option>',
                                                '<option value="rgb(230, 0, 0)" label="rgb(230, 0, 0)"></option>',
                                                '<option value="rgb(255, 153, 0)" label="rgb(255, 153, 0)"></option>',
                                                '<option value="rgb(255, 255, 0)" label="rgb(255, 255, 0)"></option>',
                                                '<option value="rgb(0, 138, 0)" label="rgb(0, 138, 0)"></option>',
                                                '<option value="rgb(0, 102, 204)" label="rgb(0, 102, 204)"></option>',
                                                '<option value="rgb(153, 51, 255)" label="rgb(153, 51, 255)"></option>',
                                                '<option value="rgb(255, 255, 255)" label="rgb(255, 255, 255)" selected=""></option>',
                                                '<option value="rgb(250, 204, 204)" label="rgb(250, 204, 204)"></option>',
                                                '<option value="rgb(255, 235, 204)" label="rgb(255, 235, 204)"></option>',
                                                '<option value="rgb(255, 255, 204)" label="rgb(255, 255, 204)"></option>',
                                                '<option value="rgb(204, 232, 204)" label="rgb(204, 232, 204)"></option>',
                                                '<option value="rgb(204, 224, 245)" label="rgb(204, 224, 245)"></option>',
                                                '<option value="rgb(235, 214, 255)" label="rgb(235, 214, 255)"></option>',
                                                '<option value="rgb(187, 187, 187)" label="rgb(187, 187, 187)"></option>',
                                                '<option value="rgb(240, 102, 102)" label="rgb(240, 102, 102)"></option>',
                                                '<option value="rgb(255, 194, 102)" label="rgb(255, 194, 102)"></option>',
                                                '<option value="rgb(255, 255, 102)" label="rgb(255, 255, 102)"></option>',
                                                '<option value="rgb(102, 185, 102)" label="rgb(102, 185, 102)"></option>',
                                                '<option value="rgb(102, 163, 224)" label="rgb(102, 163, 224)"></option>',
                                                '<option value="rgb(194, 133, 255)" label="rgb(194, 133, 255)"></option>',
                                                '<option value="rgb(136, 136, 136)" label="rgb(136, 136, 136)"></option>',
                                                '<option value="rgb(161, 0, 0)" label="rgb(161, 0, 0)"></option>',
                                                '<option value="rgb(178, 107, 0)" label="rgb(178, 107, 0)"></option>',
                                                '<option value="rgb(178, 178, 0)" label="rgb(178, 178, 0)"></option>',
                                                '<option value="rgb(0, 97, 0)" label="rgb(0, 97, 0)"></option>',
                                                '<option value="rgb(0, 71, 178)" label="rgb(0, 71, 178)"></option>',
                                                '<option value="rgb(107, 36, 178)" label="rgb(107, 36, 178)"></option>',
                                                '<option value="rgb(68, 68, 68)" label="rgb(68, 68, 68)"></option>',
                                                '<option value="rgb(92, 0, 0)" label="rgb(92, 0, 0)"></option>',
                                                '<option value="rgb(102, 61, 0)" label="rgb(102, 61, 0)"></option>',
                                                '<option value="rgb(102, 102, 0)" label="rgb(102, 102, 0)"></option>',
                                                '<option value="rgb(0, 55, 0)" label="rgb(0, 55, 0)"></option>',
                                                '<option value="rgb(0, 41, 102)" label="rgb(0, 41, 102)"></option>',
                                                '<option value="rgb(61, 20, 102)" label="rgb(61, 20, 102)"></option>',
                                            '</select>',
                                        '</span>',
                                        '<span class="ql-format-group">',
                                            '<span title="List" class="ql-format-button ql-list"></span>',
                                            '<span class="ql-format-separator"></span>',
                                                '<span title="Bullet" class="ql-format-button ql-bullet"></span>',
                                                '<span class="ql-format-separator"></span>',
                                                '<select title="Text Alignment" class="ql-align">',
                                                    '<option value="left" label="Left" selected=""></option>',
                                                    '<option value="center" label="Center"></option>',
                                                    '<option value="right" label="Right"></option>',
                                                    '<option value="justify" label="Justify"></option>',
                                                '</select>',
                                            '</span>',
                                        '</span>',
                                        '<span class="ql-format-group">',
                                            '<span title="Link" class="ql-format-button ql-link"></span>',
                                        '</span>',
                                    '</div>',
                                    '<div id="widget-text-editor-' + this.getId() + '"></div>',
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

                this.editorWindow.find('.modal-footer').prepend(this.getSaveButton());
            }

            return this.editorWindow;
        },

        getEditor: function () {
            if ( !this.editor ) {
                this.editor = new Quill(
                    '#widget-text-editor-' + this.getId(),
                    {
                        modules:
                        {
                            'toolbar': { container: '#widget-text-editor-toolbar' + this.getId() },
                            'link-tooltip': false
                        },
                        theme: 'snow'
                    }
                );

                var LinkTooltip = this.editor.getModule('link-tooltip');
                // Overriding function to remove the forced protocol so local urls work.
                // Important when syncing dashboards across environments.
                LinkTooltip._normalizeURL = function(url) {
                    return url;
                };
            }

            return this.editor;
        },

        getSaveButton: function () {
            if ( !this.saveButton ) {
                this.saveButton = $('<button type="button" class="btn btn-primary">Save</button>');

                var _this = this;
                this.saveButton.on('click',function(){
                    _this.setContent(_this.getEditor().getHTML());
                    _this.getEditorWindow().modal('hide');
                });
            }

            return this.saveButton;
        }

    });

    // add to global space
    GD.DashboardWidgetText = DashboardWidgetText;

})(typeof window === 'undefined' ? this : window, jQuery);