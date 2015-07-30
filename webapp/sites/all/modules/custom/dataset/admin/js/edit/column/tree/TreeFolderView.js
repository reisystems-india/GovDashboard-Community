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
        throw new Error('TreeFolderView requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('TreeFolderView requires GD');
    }

    var GD = global.GD;

    var TreeFolderView = GD.TreeView.extend({
        subList: null,
        viewList: null,
        typeList: null,

        init: function ( object, container, options ) {
            this._super(object, container, options);
            this.typeList = [object['additionalParameters']['sysname']];
        },

        closeFolder: function () {
            if (this.getIcon().hasClass('glyphicon-folder-open')) {
                this.getIcon().removeClass('glyphicon-folder-open');
                this.getIcon().addClass('glyphicon-folder-close');
                this.getSubList().slideToggle();
            }
        },

        openFolder: function () {
            if (this.getIcon().hasClass('glyphicon-folder-close')) {
                this.getIcon().removeClass('glyphicon-folder-close');
                this.getIcon().addClass('glyphicon-folder-open');
                this.getSubList().slideToggle();
            }
        },

        labelClicked: function () {
            if ( this.object['additionalParameters']['isSelectable'] ) {
                this.parent.selectedType(this.object['additionalParameters']['sysname'], this.object['additionalParameters']['displayName'], this.getHeader());
            } else {
                this.iconClicked();
            }
        },

        iconClicked: function () {
            if ( this.getIcon().hasClass('glyphicon-folder-close') ) {
                this.openFolder();
            } else {
                this.closeFolder();
            }
        },

        expandAll: function () {
            this.openFolder();
            $.each(this.viewList, function (i, vl) {
                if (vl.expandAll) {
                    vl.expandAll();
                }
            });
        },

        getSubList: function () {
            if ( this.subList == null ) {
                this.subList = $('<ul class="tree-sub-list"></ul>');
                this.viewList = [];
                var _this = this;
                $.each(this.object.children, function (i, c) {
                    var view = null;
                    if ( typeof c.children != 'undefined' && c.children != null ) {
                        view = new GD.TreeFolderView(c, _this.subList, _this.options);
                        _this.typeList = _this.typeList.concat(view.typeList);
                    } else {
                        view = new GD.TreeItemView(c, _this.subList, _this.options);
                        _this.typeList.push(c['additionalParameters']['sysname']);
                    }

                    view.render();
                    _this.viewList.push(view);
                });
                if (this.object['additionalParameters']['isAutomaticallyExpanded']) {
                    this.getIcon().addClass('glyphicon-folder-open');
                } else {
                    this.getIcon().addClass('glyphicon-folder-close');
                    this.subList.hide();
                }
            }

            return this.subList;
        },

        getIcon: function () {
            if ( this.icon == null ) {
                this.icon = this._super();
                this.icon.addClass('tree-folder glyphicon');

            }
            return this.icon;
        },

        setType: function ( type ) {
            if ( $.inArray(type, this.typeList) != -1 ) {
                if ( this.object['additionalParameters']['sysname'] == type ) {
                    this.getHeader().addClass('tree-selected');
                } else {
                    this.openFolder();
                    $.each(this.viewList, function (i, v) {
                        v.setType(type);
                    });
                }
            }
        },

        getListItemContainer: function () {
            if ( this.listItemContainer == null ) {
                this.listItemContainer = this._super();
                this.listItemContainer.append(this.getSubList());
            }

            return this.listItemContainer;
        },

        render: function () {
            if ( this.container != null ) {
                $(this.container).append(this.getListItemContainer());
            } else {
                return this.getTypeTreeContainer();
            }
        }
    });

    // add to global space
    global.GD.TreeFolderView = TreeFolderView;

})(typeof window === 'undefined' ? this : window, jQuery);
