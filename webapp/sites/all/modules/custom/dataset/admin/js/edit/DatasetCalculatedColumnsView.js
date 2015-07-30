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
        throw new Error('DatasetCalculatedColumnsView requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DatasetCalculatedColumnsView requires GD');
    }

    var GD = global.GD;

    var DatasetCalculatedColumnsView = GD.View.extend({
        mainContainer: null,

        columnsHeader: null,
        columnsContainer: null,

        addColumnButton: null,
        columns: null,
        columnsView: null,
        columnViewList: null,
        dataTypes: null,
        createdView: false,
        usableColumns: null,

        init: function ( object, container, options ) {
            this._super(object, container, options);
            if ( this.object != null ) {
                this.loadColumns();
            }
        },

        toggleReadOnlyView: function () {
            this.getAddColumnButton().hide();
        },

        toggleCreateView: function () {
            this.getMainContainer().hide();
        },

        validate: function () {
            var messages = [];
            var names = [];
            $.each(this.columnViewList, function (i, v) {
                messages = messages.concat(v.validate());
                if ( !v.getDeleted() && v.getEnabledInput().prop('checked') ) {
                    if ( names.indexOf(v.getPublicNameInput().val().toLowerCase()) != -1 ) {
                        messages.push('Column names must be unique');
                    } else {
                        names.push(v.getPublicNameInput().val().toLowerCase());
                    }
                }
            });
            return messages;
        },

        getColumns: function () {
            var columnList = [];
            $.each(this.columnViewList, function (i, v) {
                if ( !v.getDeleted() ) {
                    var c = v.getColumn();
                    columnList.push(c);
                }
            });
            return columnList;
        },

        loadColumns: function (reloadView) {
            this.columns = [];
            var _this = this;

            if ( this.object.columns != null ) {
                $.each(this.object.columns, function (i, c) {
                    if ( c['persistence'] === GD.Column.PERSISTENCE__CALCULATED() ) {
                        var column = new GD.Column(c);
                        _this.columns.push(column);
                    }
                });
            }

            if ( reloadView || typeof this.columnViewList == 'undefined' || this.columnViewList == null ) {
                this.loadColumnViews();
            } else {
                $.each(this.columnViewList, function (i, v) {
                    $.each(_this.columns, function (i, c) {
                        if ( c.equals(v.object) ) {
                            v.setColumn(c);
                            return false;
                        }
                    });
                    v.setUsableColumns(_this.getUsableColumns());
                });
            }
        },

        loadColumnViews: function () {
            var container = this.getColumnsBody();
            container.empty();
            var _this = this;
            _this.columnViewList = [];
            $.each(this.columns, function (i, c) {
                var view = new GD.CalculatedColumnView(c, container, {parent:_this, usableColumns:_this.getUsableColumns()});
                view.render();
                if ( _this.dataTypes != null && _this.treeMapping != null ) {
                    view.loadedDataTypes(_this.dataTypes, _this.treeMapping);
                }
                _this.columnViewList.push(view);
            });
            if ( !_this.columnViewList.length ) {
                container.append('<p>There are no calculated columns.</p>');
            }
        },

        loadedDataset: function ( dataset, reloadView ) {
            this.object = dataset;
            this.usableColumns = null;
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
                    v.loadedDataTypes(_this.dataTypes, _this.treeMapping);
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
            if ( typeof data != "undefined" && data != null ) {
                var treeData = [];
                var treeMapping = {};
                var _this = this;
                var traverse = function ( item, parent, parentName ) {
                    if ( item.name != _this.object.getName() || _this.object.getName() == null ) {
                        var treeItem = {
                            name: item.publicName,
                            type: item.subtypes ? 'folder' : 'item',
                            additionalParameters: {
                                sysname: item.name,
                                displayName: item['isParentShownOnSelect'] ? parentName + ' > ' + item.publicName : item.publicName,
                                isSelectable: item.isSelectable
                            }
                        };

                        if ( typeof item.subtypes != 'undefined' && item.subtypes ) {
                            treeItem.children = [];
                            for ( var i=0, arrayLength=item.subtypes.length; i<arrayLength; i++ ) {
                                traverse(item.subtypes[i], treeItem.children, (item.isVisible ? treeItem.additionalParameters.displayName : null));
                            }
                        }

                        if ( item['isFormulaExpressionCompatible'] && item['isVisible'] ) {
                            //  If a tree has children, then it's length must be greater than 0 or is selectable
                            if ( typeof treeItem.children != 'undefined' ) {
                                if ( treeItem.children.length != 0 || item.isSelectable ) {
                                    parent.push(treeItem);
                                    treeMapping[item.name] = treeItem.additionalParameters.displayName;
                                }
                            } else {
                                parent.push(treeItem);
                                treeMapping[item.name] = treeItem.additionalParameters.displayName;
                            }
                        } else {
                            if ( typeof treeItem.children != 'undefined' ) {
                                $.each(treeItem.children, function (i, c) {
                                    parent.push(c);
                                    treeMapping[c.name] = c.additionalParameters.displayName;
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
            }
        },

        addColumn: function () {
            if ( this.columnViewList.length == 0 ) {
                this.getColumnsBody().empty();
            }
            var v = new GD.CalculatedColumnView(new GD.Column({persistence:GD.Column.PERSISTENCE__CALCULATED()}), this.getColumnsBody(), {parent:this, usableColumns:this.getUsableColumns()});
            this.columnViewList.push(v);
            v.render();
            v.loadedDataTypes(this.dataTypes, this.treeMapping);
        },

        getUsableColumns: function () {
            if ( this.usableColumns == null ) {
                this.usableColumns = [];
                var _this = this;
                $.each(this.object.columns, function (i, c) {
                    //  Use the static function to avoid generating new objects for every column
                    if ( GD.Column.canBeUsedInCalculation(c) ) {
                        _this.usableColumns.push(c);
                    }
                });
            }

            return this.usableColumns;
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
                this.columnsHeader.append($('<div class="col-md-12 columns-header"></div>').append('<h3>Calculated Columns</h3>'));
            }

            return this.columnsHeader;
        },

        getColumnsFooter: function () {
            if ( this.columnsFooter == null ) {
                this.columnsFooter = $('<div class="row"></div>');
                this.columnsFooter.append($('<div class="col-md-12 columns-footer"></div>').append(this.getAddColumnButton()));
            }

            return this.columnsFooter;
        },

        getColumnsBody: function () {
            if ( this.columnsContainer == null ) {
                this.columnsContainer = $('<div class="col-md-12 columns-body"></div>');
            }

            return this.columnsContainer;
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
    global.GD.DatasetCalculatedColumnsView = DatasetCalculatedColumnsView;

})(typeof window === 'undefined' ? this : window, jQuery);