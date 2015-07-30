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
        throw new Error('TypeTreeView requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('TypeTreeView requires GD');
    }

    var GD = global.GD;

    var TypeTreeView = GD.View.extend({
        treeData: null,
        typeTree: null,
        typeTreeContainer: null,
        treeItems: null,
        parent: null,

        init: function (object, container, options) {
            this._super(object, container, options);
            if ( typeof options.types != "undefined" && options.types != null ) {
                this.treeData = this.parseTree(options.types);
            }

            if ( typeof options.parent != "undefined" ) {
                this.parent = options.parent;
            }
        },

        loadedDataTypes: function ( dataTypes ) {
            this.treeData = dataTypes;
            this.loadTreeView();
        },

        loadTreeView: function () {
            if ( this.treeData != null ) {
                this.getTypeTree().empty();
                this.treeItems = [];
                var list = $('<ul class="tree-list"></ul>');
                var _this = this;
                $.each(this.treeData, function (i, td) {
                    _this.buildItem(td, list);
                });

                this.getTypeTree().append(list);
            }
        },

        buildItem: function ( info, parent ) {
            var view = null;
            if ( typeof info['children'] != 'undefined' && info['children'] != null ) {
                view = new GD.TreeFolderView(info, parent, { 'parent': this });
            } else {
                view = new GD.TreeItemView(info, parent, { 'parent': this });
            }
            view.render();
            this.treeItems.push(view);
        },

        expandAll: function () {
            $.each(this.treeItems, function (k, ti) {
                if (ti.expandAll) {
                    ti.expandAll();
                }
            });
        },

        setType: function ( sysName ) {
            $.each(this.treeItems, function (i, ti) {
                if ( ti.setType(sysName) ) {
                    return false;
                }
            });
        },

        selectedType: function ( type, publicName, view ) {
            this.getTypeTree().find('.tree-selected').removeClass('tree-selected');
            view.addClass('tree-selected');
            this.getTypeTreeContainer().modal('hide');
            if ( this.parent != null ) {
                this.parent.changeType(type, publicName);
            }
        },

        getTypeTree: function () {
            if ( this.typeTree == null ) {
                this.typeTree = $('<div class="type-tree"></div>');
            }

            return this.typeTree;
        },

        getTypeTreeContainer: function () {
            if ( this.typeTreeContainer == null ) {
                this.typeTreeContainer = $('<div class="tree-container" style="display:none;"></div>');
                this.typeTreeContainer.append(this.getTypeTree());
            }

            return this.typeTreeContainer;
        },

        render: function () {
            if ( this.container != null ) {
                $(this.container).append(this.getTypeTreeContainer());
            }

            return this.getTypeTreeContainer();
        }
    });

    // add to global space
    global.GD.TypeTreeView = TypeTreeView;

})(typeof window === 'undefined' ? this : window, jQuery);