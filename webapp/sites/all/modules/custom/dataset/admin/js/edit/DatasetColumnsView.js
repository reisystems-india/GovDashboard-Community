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
        throw new Error('DatasetColumnsView requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DatasetColumnsView requires GD');
    }

    var GD = global.GD;

    var DatasetColumnsView = GD.View.extend({
        mainContainer: null,

        columnsHeader: null,
        columnsContainer: null,

        addColumnButton: null,
        columns: null,
        columnsView: null,
        columnViewList: null,
        dataTypes: null,
        createdView: false,

        init: function ( object, container, options ) {
            this._super(object, container, options);
            if ( this.object != null ) {
                this.loadColumns();
            }
        },

        toggleReadOnlyView: function () {
            this.getColumnsBody().sortable('disable');
            this.getAddColumnButton().hide();
            this.readOnlyView = true;
        },

        toggleCreateView: function () {
            this.getColumnsBody().sortable('disable');
            this.getAddColumnButton().hide();
            this.createdView = true;
        },

        validate: function () {
            var messages = [];
            var names = [];
            $.each(this.columnViewList, function (i, v) {
                messages = messages.concat(v.validate());
                if ( !v.getDeleted() && v.getEnabledInput().prop('checked') ) {
                    if ( names.indexOf(v.getPublicNameInput().attr('column-name').toLowerCase()) != -1 ) {
                        messages.push('Column names must be unique');
                    } else {
                        names.push(v.getPublicNameInput().attr('column-name').toLowerCase());
                    }
                }
            });

            return messages;
        },

        getColumns: function () {
            var columnList = [];

            //  Re-index column views
            var deleted = 0;
            $('div.gd-column-view-container').each(function(i, v) {
                if ($(this).hasClass('disabled-column')) {
                    deleted += 1;
                } else {
                    $(this).attr('column-index', i - deleted);
                }
            });

            $.each(this.columnViewList, function (i, v) {
                if ( !v.getDeleted() ) {
                    var c = v.getColumn();
                    if (c.getColumnIndex()) {
                        columnList.push(c);
                    }
                }
            });

            columnList.sort(function (a, b) {
                return a.getColumnIndex() - b.getColumnIndex();
            });

            return $.merge(columnList, this.getInvisibleColumns(columnList.slice(-1)[0].getColumnIndex()));
        },

        getInvisibleColumns: function (index) {
            var columns = [];

            $.each(this.object.columns, function (i, c) {
                if ( c['visible'] === false ) {
                    if ( index ) {
                        c['columnIndex'] = ++index;
                    }

                    columns.push(new GD.Column(c));
                }
            });

            return columns;
        },

        loadColumns: function (reloadView) {
            this.columns = [];
            var _this = this;

            if ( this.object.columns != null ) {
                this.object.columns.sort(function (a, b) {
                    return a['columnIndex'] - b['columnIndex'];
                });

                var index = 0;
                $.each(this.object.columns, function (i, c) {
                    if ( c['persistence'] === GD.Column.PERSISTENCE__NO_STORAGE() || c['persistence'] === GD.Column.PERSISTENCE__STORAGE_CREATED() ) {
                        if ( c['visible'] !== false ) {
                            var column = new GD.Column(c);
                            column.setColumnIndex(index++);
                            _this.columns.push(column);
                        }
                    }
                });
            }

            if ( reloadView || typeof this.columnViewList == 'undefined' || this.columnViewList == null ) {
                this.loadColumnViews();
            } else {
                $.each(this.columns, function (i, c) {
                    $.each(_this.columnViewList, function (i, v) {
                        if ( c.equals(v.object) ) {
                            return false;
                        }
                    });
                });
            }
        },

        loadColumnViews: function () {
            var container = this.getColumnsBody();
            container.empty();
            this.columnViewList = [];
            var _this = this;
            $.each(this.columns, function (i, c) {
                var view = new GD.ColumnView(c, container, {});
                view.render();
                if ( _this.dataTypes != null && _this.treeMapping != null ) {
                    view.loadedDataTypes(_this.dataTypes, _this.treeMapping, _this.keyCompatibleColumns);
                }
                _this.columnViewList.push(view);

                if ( _this.createdView ) {
                    view.toggleCreateView();
                }

                if ( _this.readOnlyView ) {
                    view.toggleReadOnlyView();
                }
            });
        },

        loadedDataset: function ( dataset, reloadView ) {
            this.object = dataset;
            this.loadColumns(reloadView);
            if ( reloadView ) {
                this.getMainContainer().show();
            }
        },

        datasetChanged: function ( context, changeType ) {
            if ( changeType == 'error' ) {
                for (var i = 0; i < this.columnViewList.length; i++) {
                    if ( this.columnViewList[i].getDeleted() && !context.hasColumnChanged(this.columnViewList[i].object) ) {
                        this.columnViewList[i].getColumnContainer().remove();
                        this.columnViewList.splice(i, 1);
                        --i;
                    }
                }
            }
        },

        loadedDataTypes: function ( dataTypes ) {
            this.parseTree(dataTypes);
            if ( this.columnViewList != null ) {
                var _this = this;
                $.each(this.columnViewList, function (i, v) {
                    v.loadedDataTypes(_this.dataTypes, _this.treeMapping, _this.keyCompatibleColumns);
                });
            }
        },

        deleteColumn: function ( view, column ) {
            if ( column.getName() == null ) {
                for (var i = 0; i < this.columnViewList.length; i++) {
                    if ( this.columnViewList[i] == view ) {
                        this.columnViewList.splice(i, 1);
                        view.getColumnContainer().remove();
                        break;
                    }
                }
            }
        },

        parseTree: function ( data ) {
            if (data) {
                var treeData = [];
                var treeMapping = {};
                var keyCompatibleColumns = [];
                var _this = this;
                var traverse = function ( item, parent, parentName ) {
                    if ( item.name != _this.object.getName() || _this.object.getName() == null ) {
                        var treeItem = {
                            name: item.publicName,
                            type: item.subtypes ? 'folder' : 'item',
                            additionalParameters: {
                                sysname: item.name,
                                displayName: item['isParentShownOnSelect'] ? parentName + ' > ' + item.publicName : item.publicName,
                                isSelectable: item.isSelectable,
                                isAutomaticallyExpanded: item.isAutomaticallyExpanded
                            }
                        };

                        if (item['isKeyCompatible']) {
                            keyCompatibleColumns.push(item.name);
                        }

                        if (item.subtypes) {
                            treeItem.children = [];
                            for ( var i=0, arrayLength=item.subtypes.length; i<arrayLength; i++ ) {
                                traverse(item.subtypes[i], treeItem.children, (item.isVisible ? treeItem.additionalParameters.displayName : null));
                            }
                        }

                        if ( item.isVisible ) {
                            //  If a tree has children, then it's length must be greater than 0 or is selectable
                            if (treeItem.children) {
                                if ( treeItem.children.length != 0 || item.isSelectable ) {
                                    parent.push(treeItem);
                                    if ( item.isSelectable ) {
                                        treeMapping[item.name] = treeItem.additionalParameters.displayName;
                                    }
                                }
                            } else {
                                parent.push(treeItem);
                                if ( item.isSelectable ) {
                                    treeMapping[item.name] = treeItem.additionalParameters.displayName;
                                }
                            }
                        } else {
                            //  TODO Need to redo structure for data types on server. This is temp solution for dates.
                            if (treeItem.children) {
                                $.each(treeItem.children, function (i, c) {
                                    parent.push(c);
                                    if ( c.isSelectable ) {
                                        treeMapping[c.name] = c.additionalParameters.displayName;
                                    }
                                });
                            }
                        }
                    }
                };

                for ( var i=0, arrayLength=data.subtypes.length; i<arrayLength; i++ ) {
                    traverse(data.subtypes[i],treeData, null);
                }

                this.dataTypes = treeData;
                this.treeMapping = treeMapping;
                this.keyCompatibleColumns = keyCompatibleColumns;
            }
        },

        addColumn: function () {
            var v = new GD.ColumnView(new GD.Column({persistence:GD.Column.PERSISTENCE__NO_STORAGE()}), this.getColumnsBody(), this.options);
            this.columnViewList.push(v);
            v.render();
            v.loadedDataTypes(this.dataTypes, this.treeMapping, this.keyCompatibleColumns);
        },

        getAddColumnButton: function () {
            if ( this.addColumnButton == null ) {
                this.addColumnButton = $('<button class="btn btn-success">Add Column</button>');

                var _this = this;
                this.addColumnButton.on('click', function () {
                    _this.addColumn();
                });
            }

            return this.addColumnButton;
        },

        getColumnsHeader: function () {
            if ( this.columnsHeader == null ) {
                this.columnsHeader = $('<div class="row"></div>');
                this.columnsHeader.append($('<div class="col-md-12 columns-header"></div>').append('<h3>Columns</h3>'));
            }

            return this.columnsHeader;
        },

        getColumnsBody: function () {
            if ( this.columnsContainer == null ) {
                this.columnsContainer = $('<div class="col-md-12 columns-body"></div>');
                this.columnsContainer.sortable({
                    'handle': '.gd-column-view-move-icon',
                    'placeholder': "ui-state-highlight",
                    'stop': function ( event, ui ) {
                        var list = $(this).children();
                        $.each(list, function (i, l) {
                            $(l).attr('column-index', i);
                        });
                    }
                });

                var _this = this;
                this.columnsContainer.on('createView', function () {
                    _this.toggleCreateView();
                });
            }

            return this.columnsContainer;
        },

        getColumnsFooter: function () {
            if ( this.columnsFooter == null ) {
                this.columnsFooter = $('<div class="row"></div>');
                this.columnsFooter.append($('<div class="col-md-12 columns-footer"></div>').append(this.getAddColumnButton()));
            }

            return this.columnsFooter;
        },

        getMainContainer: function () {
            if ( this.mainContainer == null ) {
                this.mainContainer = $('<div class="gd-dataset-columns-container"></div>');
                this.mainContainer.append(
                    this.getColumnsHeader(),
                    $('<div class="row gd-dataset-columns-body"></div>').append(this.getColumnsBody()),
                    this.getColumnsFooter()
                );

                var _this = this;

                $(document).on('createView', function () {
                    _this.toggleCreateView();
                });

                $(document).on('readOnlyView', function () {
                    _this.toggleReadOnlyView();
                });

                $(document).on('loadedDataset', function (e) {
                    _this.loadedDataset(e['dataset'], e['reloadView']);
                });

                $(document).on('loadedDataTypes', function (e) {
                    _this.loadedDataTypes(e['dataTypes']);
                });

                $(document).on('changesMade', function (e) {
                    _this.datasetChanged(e['context'], e['changeType']);
                });

                $(document).on('deleteColumn', function (e) {
                    _this.deleteColumn(e['view'], e['column']);
                });
            }

            return this.mainContainer;
        },

        render: function () {
            if ( this.container != null ) {
                $(this.container).append(this.getMainContainer());
            } else {
                return this.getMainContainer();
            }
        }
    });

    // add to global space
    global.GD.DatasetColumnsView = DatasetColumnsView;

})(typeof window === 'undefined' ? this : window, jQuery);