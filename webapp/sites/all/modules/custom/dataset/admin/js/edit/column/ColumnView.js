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
        throw new Error('ColumnView requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ColumnView requires GD');
    }

    var GD = global.GD;

    var ColumnView = GD.View.extend({
        columnContainer: null,
        columnHeader: null,
        columnBody: null,

        nameContainer: null,
        nameInput: null,

        typeContainer: null,
        typeInput: null,

        commentIcon: null,
        moveInput: null,

        typeTree: null,
        keyCompatibleColumns: null,

        enableInput: null,
        keyInput: null,

        deleteButton: null,
        deleted: false,

        init: function ( object, container, options ) {
            this._super(object,container,options);

            this.setEnabledInput(this.object.isEnabled());
            this.setKeyInput(this.object.isPrimaryKey());
        },

        validate: function () {
            var messages = [];

            if ( this.getEnabledInput().prop('checked') && !this.getDeleted() ) {
                if ( this.getPublicNameInput().attr('column-name') == '' ) {
                    messages.push('Column name is required.');
                    //  TODO Highlight / indicate input
                }

                if ( this.getTypeInput().attr('data-type') == '' ) {
                    messages.push('Column data type is required.');
                    //  TODO Highlight / indicate input
                }
            }

            return messages;
        },

        loadedDataTypes: function ( dataTypes, treeMapping, keyCompatibleColumns ) {
            this.keyCompatibleColumns = keyCompatibleColumns;
            this.getTypeTree().loadedDataTypes(dataTypes);
            var type = this.object.getType();
            //  Does the column type exist in the list of data types sent from the server?
            if (treeMapping[type]) {
                this.getTypeTree().setType(type);
            }

            var _this = this;
            if ($.inArray(type, keyCompatibleColumns) == -1) {
                _this.getKeyInput().attr('disabled', 'disabled');
            }

            this.getTypeInput().text(treeMapping[type] ? treeMapping[type] : 'Select Data Type');
            this.getTypeInput().attr('data-type', treeMapping[type] ? type : '');
        },

        datasetChanged: function ( context, type ) {
            this.getPublicNameInput().removeClass('changed-info changed-error');
            this.getTypeContainer().removeClass('changed-info changed-error');

            if ( this.object.getName() == null ) {
                this.getPublicNameInput().addClass(type == 'error' ? 'changed-error' : 'changed-info');
                this.getTypeContainer().addClass(type == 'error' ? 'changed-error' : 'changed-info');
            } else if ( typeof context.changedColumns[this.object.getName()] != 'undefined' ) {
                if ( context.changedColumns[this.object.getName()].isNameUpdated ) {
                    this.getPublicNameInput().addClass(type == 'error' ? 'changed-error' : 'changed-info');
                }

                if ( context.changedColumns[this.object.getName()].isTypeUpdated ) {
                    this.getTypeContainer().addClass(type == 'error' ? 'changed-error' : 'changed-info');
                }
            }
        },

        toggleReadOnlyView: function () {
            this.getMoveIcon().hide();
            this.getDeleteButton().hide();
            this.getPublicNameInput().attr('disabled', 'disabled');
            this.getEnabledInput().attr('disabled', 'disabled');
            this.getKeyInput().attr('disabled', 'disabled');
            this.getTypeInput().addClass('disabled');
            this.getTypeInput().unbind();
        },

        toggleCreateView: function () {
            this.getMoveIcon().hide();
            this.getDeleteButton().hide();
        },

        getColumn: function () {
            var c = new GD.Column(this.object);
            c.setPublicName(this.getPublicNameInput().attr('column-name'));
            c.setPrimaryKey(this.getKeyInput().prop('checked'));
            c.setEnabled(this.getEnabledInput().prop('checked'));
            c.setType(this.getTypeInput().attr('data-type'));
            c.setColumnIndex(this.getColumnContainer().attr('column-index'));

            this.object = c;
            return c;
        },

        setColumn: function ( column ) {
            this.object = column;
        },

        getColumnContainer: function () {
            if ( this.columnContainer == null ) {
                this.columnContainer = $('<div class="well well-sm gd-column-view-container"></div>');
                this.columnContainer.attr('column-index', this.object.getColumnIndex());
                this.columnContainer.append(this.getColumnHeader());
                this.getTypeTree().render();

                var _this = this;
                $(document).on('changesMade', function ( e ) {
                    _this.datasetChanged(e['context'], e['changeType']);
                });

                $(document).on('readOnlyView', function () {
                    _this.toggleReadOnlyView();
                });
            }

            return this.columnContainer;
        },

        getColumnHeader: function () {
            if ( this.columnHeader == null ) {
                this.columnHeader = $('<div class="row gd-column-view-header"></div>');
                this.columnHeader.append(this.getPublicNameContainer());
                this.columnHeader.append(this.getTypeContainer());
                this.columnHeader.append($('<div class="col-md-3" />').append(this.getColumnOptionsContainer()));
                this.columnHeader.append($('<div class="col-md-1" />').append(this.getDeleteButton()));
            }

            return this.columnHeader;
        },

        getMoveIcon: function () {
            if ( this.moveInput == null ) {
                this.moveInput = $('<span class="glyphicon glyphicon-move gd-column-view-icon gd-column-view-move-icon pull-left"></span>');
            }

            return this.moveInput;
        },

        getCommentIcon: function() {
            if (!this.commentIcon) {
                this.commentIcon = $('<span class="glyphicon glyphicon-info-sign pull-right" style="font-size:18px;margin-top:8px;"></span>');
                this.commentIcon.attr('title', this.object.getDescription());
                this.commentIcon.tooltip({
                    container: 'body',
                    delay: { show: 250, hide: 0 }
                });
            }

            return this.commentIcon;
        },

        getPublicNameContainer: function () {
            if ( this.nameContainer == null ) {
                this.nameContainer = $('<div class="col-md-4 gd-column-view-name-container"></div>');
                this.nameContainer.append(this.getMoveIcon());
                this.nameContainer.append(this.getPublicNameInput());

                if (this.object.getDescription()) {
                    this.nameContainer.append(this.getCommentIcon());
                }
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
                this.typeContainer = $('<div class="col-md-4 gd-column-view-type-container"></div>');
                this.typeContainer.append(this.getTypeInput());
            }

            return this.typeContainer;
        },

        getTypeInput: function () {
            if ( this.typeInput == null ) {
                this.typeInput = $('<a class="gd-column-view-type-value" data-type="" tabindex="0"></a>');

                var _this = this;
                this.typeInput.click(function () {
                    var position = _this.typeInput.position();
                    _this.getTypeTree().getTypeTreeContainer().css({left:position.left + 390}).modal();
                });
            }

            return this.typeInput;
        },

        getColumnOptionsContainer: function () {
            if ( this.columnOptionsContainer == null ) {
                this.columnOptionsContainer = $([
                    '<div class="row gd-column-view-options-container pull-right">',
                        '<div class="col-sm-5">',
                            '<div class="checkbox">',
                                '<label><input type="checkbox" name="enabled" /> Enabled</label>',
                            '</div>',
                        '</div>',
                        '<div class="col-sm-7">',
                            '<div class="checkbox">',
                                '<label><input type="checkbox" name="key" /> Primary Key</label>',
                            '</div>',
                        '</div>',
                    '</div>'
                ].join("\n"));
            }
            return this.columnOptionsContainer;
        },

        setEnabledInput: function ( enabled ) {
            this.getEnabledInput().prop('checked',enabled);
        },

        getEnabledInput: function () {
            if ( this.enabledInput == null ) {
                this.enabledInput = this.getColumnOptionsContainer().find('input[name=enabled]');
            }
            return this.enabledInput;
        },

        setKeyInput: function ( key ) {
            this.getKeyInput().prop('checked',key);
        },

        getKeyInput: function () {
            if ( this.keyInput == null ) {
                this.keyInput = this.getColumnOptionsContainer().find('input[name=key]');
            }
            return this.keyInput;
        },

        getDeleteButton: function () {
            if ( this.deleteButton == null ) {
                this.deleteButton = $('<button class="btn btn-default"></button>');
                this.deleteButton.append('<span class="glyphicon glyphicon-trash"></span>');

                var _this = this;
                this.deleteButton.on('click', function () {
                    _this.changedDeleted();
                    _this.deleteButton.empty();
                    if ( _this.getDeleted() ) {
                        _this.deleteButton.text('Restore');
                        _this.deleteButton.removeClass('btn-default');
                        _this.deleteButton.addClass('btn-success');
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

        getTypeTree: function () {
            if ( this.typeTree == null ) {
                this.typeTree = new GD.TypeTreeView(this.object, this.getColumnContainer(), {'types': this.types, 'parent':this});
                $(this.typeTree.getTypeTreeContainer()).on('shown', function () {
                    $('.modal-backdrop').css('opacity', 0);
                }).resizable();
            }

            return this.typeTree;
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

        getDeleted: function () {
            return this.deleted;
        },

        changeType: function ( type, publicName ) {
            this.typeInput.attr('data-type', type);
            this.typeInput.text(publicName);

            var _this = this;
            if ($.inArray(type, this.keyCompatibleColumns) == -1) {
                _this.getKeyInput().attr('disabled', 'disabled');
            } else {
                _this.getKeyInput().removeAttr('disabled');
            }
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
    global.GD.ColumnView = ColumnView;

})(!window ? this : window, jQuery);