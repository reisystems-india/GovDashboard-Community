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
        throw new Error('CalculatedColumnView requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('CalculatedColumnView requires GD');
    }

    var GD = global.GD;

    var CalculatedColumnView = GD.View.extend({
        columnContainer: null,
        columnHeader: null,
        columnBody: null,

        nameContainer: null,
        nameInput: null,

        typeContainer: null,
        typeInput: null,

        typeTree: null,
        treeMapping: null,

        expandContainer: null,
        expandInput: null,

        scriptContainer: null,
        scriptInput: null,

        enableInput: null,
        keyInput: null,

        deleteButton: null,
        deleted: false,

        editor: null,

        validate: function () {
            var messages = [];

            if (  this.getEnabledInput().prop('checked') && !this.getDeleted() ) {
                if ( this.getPublicNameInput().attr('column-name') == '' ) {
                    messages.push('Column name is required.');
                    //  TODO Highlight / indicate input
                }
                if ( this.getTypeInput().attr('data-type') == '' ) {
                    messages.push('Column data type is required.');
                    //  TODO Highlight / indicate input
                }

                if (this.editor.getValue() == '') {
                    messages.push('Formula is required for calculated column.');
                }
            }

            return messages;
        },

        toggleReadOnlyView: function () {
            this.editor.setReadonly(true);
        },

        loadedDataTypes: function ( dataTypes, treeMapping ) {
            this.treeMapping = treeMapping;
            this.getTypeTree().loadedDataTypes(dataTypes);
            var type = this.object.getType();
            this.getTypeTree().setType(type);
            if (!type) {
                this.typeTree.expandAll();
            }
            this.getTypeInput().text(type == null ? 'Select Data Type' : this.treeMapping[type]);
            this.getTypeInput().attr('data-type', type == null ? '' : type);
        },

        datasetChanged: function ( context, type ) {
            this.getPublicNameInput().removeClass('changed-info changed-error');
            //  If the column is in the context or it is a completely new column
            if ( this.object.getName() == null ) {
                this.getPublicNameInput().addClass(type == 'error' ? 'changed-error' : 'changed-info');
            } else if ( typeof context.changedColumns[this.object.getName()] != 'undefined' ) {
                if ( context.changedColumns[this.object.getName()].isNameUpdated ) {
                    this.getPublicNameInput().addClass(type == 'error' ? 'changed-error' : 'changed-info');
                }
            }
            //  TODO Add script to check
        },

        getColumn: function () {
            var c = new GD.Column(this.object);
            c.setPublicName(this.getPublicNameInput().attr('column-name'));
            c.setType(this.getTypeInput().attr('data-type'));
            c.setEnabled(this.getEnabledInput().prop('checked'));
            c.setSource(this.editor.getValue());

            this.object = c;
            return c;
        },

        setColumn: function ( column ) {
            this.object = column;
        },

        setUsableColumns: function ( columns ) {
            this.options.usableColumns = columns;
            this.updateEditorColumnSelector();
        },

        getColumnContainer: function () {
            if ( this.columnContainer == null ) {
                this.columnContainer = $('<div class="well well-sm gd-column-view-container"></div>');
                this.columnContainer.append(this.getColumnHeader());
                this.getTypeTree().render();
                this.columnContainer.append(this.getColumnBody());

                var _this = this;
                $(document).on('changesMade', function ( e ) {
                    _this.datasetChanged(e['context'], e['changeType']);
                });

                $(document).on('readOnlyView', function () {
                    _this.readOnlyView();
                });
            }

            return this.columnContainer;
        },

        getColumnHeader: function () {
            if ( this.columnHeader == null ) {
                this.columnHeader = $('<div class="row gd-column-view-header"></div>');
                this.columnHeader.append(this.getPublicNameContainer());
                this.columnHeader.append(this.getTypeContainer());
                this.columnHeader.append($('<div class="col-md-5" />').append(this.getColumnOptionsContainer()));
                this.columnHeader.append($('<div class="col-md-1" />').append(this.getDeleteButton()));
            }

            return this.columnHeader;
        },

        getColumnOptionsContainer: function () {
            if ( this.columnOptionsContainer == null ) {
                this.columnOptionsContainer = $('<div class="row gd-column-view-options-container" />');
                this.columnOptionsContainer.append(
                    $('<div class="col-sm-4 checkbox" />').append($('<label></label>').append(this.getEnabledInput(),' Enabled')),
                    $('<div class="col-sm-4">&nbsp;</div>'),
                    $('<div class="col-sm-4">&nbsp;</div>')
                );
            }
            return this.columnOptionsContainer;
        },

        getExpandIcon: function () {
            if ( this.expandInput == null ) {
                this.expandInput = $('<span class="glyphicon glyphicon-plus gd-column-view-icon gd-column-view-expand-icon"></span>');

                var _this = this;
                this.expandInput.on('click', function () {
                    if ( _this.getColumnBody().css('display') == 'none' ) {
                        _this.getExpandIcon().removeClass('glyphicon-plus');
                        _this.getExpandIcon().addClass('glyphicon-minus');
                        _this.getColumnBody().show();

                        _this.editor.resize();
                        _this.editor.renderer.updateFull();

                    } else {
                        _this.getExpandIcon().removeClass('glyphicon-minus');
                        _this.getExpandIcon().addClass('glyphicon-plus');
                        _this.getColumnBody().hide();
                    }
                });
            }

            return this.expandInput;
        },

        getPublicNameContainer: function () {
            if ( this.nameContainer == null ) {
                this.nameContainer = $('<div class="col-md-4 gd-column-view-name-container"></div>');
                this.nameContainer.append(this.getExpandIcon());
                this.nameContainer.append(this.getPublicNameInput());
            }

            return this.nameContainer;
        },

        getPublicNameInput: function () {
            if ( this.nameInput == null ) {
                this.nameInput = $('<input column-name="" placeholder="Column Name" class="form-control gd-column-view-text gd-column-view-name-value" type="text"/>');
                this.nameInput.val(this.object.getPublicName());
                this.nameInput.attr('column-name', this.object.getPublicName());

                this.nameInput.on('keyup change', function () {
                    $(this).attr('column-name', $(this).val());
                    $.event.trigger({
                        'type':'datasetModificationDetected',
                        'element': this
                    });
                });
            }

            return this.nameInput;
        },

        getTypeContainer: function () {
            if ( this.typeContainer == null ) {
                this.typeContainer = $('<div class="col-md-2 gd-column-view-type-container"></div>');
                this.typeContainer.append(this.getTypeInput());
            }

            return this.typeContainer;
        },

        getTypeInput: function () {
            if ( this.typeInput == null ) {
                this.typeInput = $('<a class="gd-column-view-type-value" data-type="" tabindex="0"></a>');

                var _this = this;
                this.typeInput.on('click', function () {
                    var position = _this.typeInput.position();
                    _this.getTypeTree().getTypeTreeContainer().css({left:position.left + 390}).modal();
                });
            }

            return this.typeInput;
        },

        getColumnBody: function () {
            if ( this.columnBody == null ) {
                this.columnBody = $('<div class="row gd-column-view-body" style="display:none;"></div>');

                var column_wrapper = $('<div class="col-md-12"></div>').append(this.getEditorColumnSelector());
                var editor_wrapper = $('<div class="col-md-12"></div>').append(this.getScriptInput());

                var wrapper = $('<div class="column-editor-wrapper"></div>').append(
                    $('<div class="row"></div>').append(column_wrapper),
                    $('<div class="row"></div>').append(editor_wrapper)
                );

                this.columnBody.append(wrapper);
            }

            return this.columnBody;
        },

        getEditorColumnSelector: function () {
            if ( this.editorColumnSelector == null ) {
                this.editorColumnSelector = $(
                    '<div class="btn-group">'+
                        '<button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">Insert Column <span class="caret"></span></button>'+
                        '<ul class="dropdown-menu column-list"></ul>'+
                    '</div>'
                );

                var items = [];
                var _this = this;
                if ( this.options.usableColumns != null ) {
                    $.each(this.options.usableColumns,function(k,v){
                        if ( _this.object.getName() != v.name ) {
                            items.push('<li><a rel="'+ v.name+'">'+ v.publicName +'</a></li>');
                        }
                    });
                }
                this.editorColumnSelector.find('.column-list').append(items.join("\n"));

                this.editorColumnSelector.find('.column-list li a').on('click',function(){
                    _this.editor.insert('$COLUMN{'+$(this).attr('rel')+'}');
                });
            }

            return this.editorColumnSelector;
        },

        updateEditorColumnSelector: function () {
            this.editorColumnSelector.find('.column-list').empty();
            var items = [];
            var _this = this;
            $.each(this.options.usableColumns,function(k,v){
                if ( _this.object.getName() != v.name ) {
                    items.push('<li><a rel="'+ v.name+'">'+ v.publicName +'</a></li>');
                }
            });
            this.editorColumnSelector.find('.column-list').append(items.join("\n"));
            this.editorColumnSelector.find('.column-list li a').on('click',function(){
                _this.editor.insert('$COLUMN{'+$(this).attr('rel')+'}');
            });
        },

        getScriptInput: function () {
            if ( this.scriptInput == null ) {
                this.scriptInput = $('<div class="gd-column-editor"></div>');
                this.editor = ace.edit(this.scriptInput.get(0));
                this.editor.setShowPrintMargin(false);
                this.editor.setHighlightActiveLine(false);
                this.editor.renderer.setShowGutter(false);
                this.editor.getSession().setMode("ace/mode/sql");
                this.editor.getSession().getDocument().setValue(this.object.getSource());
            }

            return this.scriptInput;
        },

        getEnabledInput: function () {
            if ( this.enableInput == null ) {
                this.enableInput = $('<input type="checkbox" />');
                this.enableInput.prop('checked', this.object.isEnabled());
            }

            return this.enableInput;
        },

        getDeleted: function () {
            return this.deleted;
        },

        changedDeleted: function () {
            this.deleted = !this.deleted;

            //  TODO change view of column view
            if ( this.deleted ) {
                this.getColumnContainer().addClass('disabled-column');
            } else {
                this.getColumnContainer().removeClass('disabled-column');
            }
        },

        getTypeTree: function () {
            if ( this.typeTree == null ) {
                this.typeTree = new GD.TypeTreeView(this.object, this.getColumnContainer(), {'types': this.types, 'parent':this});
                $(this.typeTree.getTypeTreeContainer()).on('shown', function () {
                    $('.modal-backdrop').css('opacity', 0);
                }).resizable();
            }

            return this.typeTree;
        },

        changeType: function ( type, publicName ) {
            this.typeInput.attr('data-type', type);
            this.typeInput.text(publicName);
        },

        getDeleteButton: function () {
            if ( this.deleteButton == null ) {
                this.deleteButton = $('<button class="btn btn-default"><span class="glyphicon glyphicon-trash"></span></button>');

                var _this = this;
                this.deleteButton.on('click', function () {
                    _this.changedDeleted();
                    _this.deleteButton.empty();
                    if ( _this.getDeleted() ) {
                        _this.deleteButton.text('Restore');
                        _this.deleteButton.addClass('btn-success');
                        _this.deleteButton.removeClass('btn-default');
                    } else {
                        _this.deleteButton.append('<span class="glyphicon glyphicon-trash"></span>');
                        _this.deleteButton.removeClass('btn-success');
                        _this.deleteButton.addClass('btn-default');
                    }

                    $.event.trigger({
                        'type': 'deleteColumn',
                        'view': _this,
                        'column': _this.object
                    });
                });

            }

            return this.deleteButton;
        },

        render: function () {
            if ( this.container != null ) {
                $(this.container).append(this.getColumnContainer());
            } else {
                return this.getColumnContainer();
            }
        }
    });

    // add to global space
    global.GD.CalculatedColumnView = CalculatedColumnView;

})(typeof window === 'undefined' ? this : window, jQuery);