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
        throw new Error('BuilderCustomViewEditor requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('BuilderCustomViewEditor requires GD');
    }

    var GD = global.GD;

    global.GD.BuilderCustomViewEditor = GD.View.extend({
        editor: null,
        editorContainer: null,
        toggled: false,

        init: function(object, container, options) {
            this._super(object, container, options);
            if (object) {
                this.object = object;
            } else {
                this.object = GD.Dashboard.singleton;
            }

            this.initVariables();
        },

        initVariables: function() {
            this.toggled = false;
            this.initEditorContainer();
            this.initEditor();
        },

        initEditor: function() {
            this.editor = ace.edit("customEditor");
            this.editor.getSession().setMode("ace/mode/html");

            if ( this.object ) {
                if ( this.object.getCustomView() ) {
                    this.toggled = true;
                    this.editor.setValue(this.object.getCustomView());
                }
            }
        },

        getController: function () {
            return this.options.builder;
        },

        getEditor: function() {
            if (!this.editor) {
                this.initEditor();
            }

            return this.editor;
        },

        applyCustomView: function() {
            this.object.setCustomView(this.getCustomViewValue());
        },

        initEditorContainer: function() {
            this.editorContainer = $(this.container);
            this.editorContainer.hide();
            if (!$('#customEditor').length) {
                var well = $('<div class="well"></div>');
                var editor = $('<div id="customEditor"></div>');

                var apply = $('<button class="pull-right btn btn-default">Apply</button>');
                var _this = this;
                apply.click(function() {
                    _this.applyCustomView();
                });

                this.editorContainer.append(well.append(editor), apply);
            }

            if ( this.object ) {
                if ( this.object.getCustomView() ) {
                    this.editorContainer.show();
                }
            }
        },

        getEditorContainer: function() {
            if (!this.editorContainer) {
                this.initEditorContainer();
            }

            return this.editorContainer;
        },

        getCustomViewValue: function() {
            return this.toggled ? this.getEditor().getValue() : null;
        },

        attachEventHandlers: function() {
            var _this = this;
            $(document).on('toggle.builder.custom', function(e) {
                _this.toggled = e['toggle'];
                if (e['toggle']) {
                    _this.getEditorContainer().show();
                } else {
                    _this.getEditorContainer().hide();
                }
            });
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);