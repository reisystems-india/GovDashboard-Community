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
        throw new Error('TreeView requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('TreeView requires GD');
    }

    var GD = global.GD;

    var TreeView = GD.View.extend({
        parent: null,
        listItemContainer: null,
        itemContainer: null,
        header: null,
        icon: null,
        label: null,

        init: function ( object, container, options ) {
            this._super(object, container, options);

            if ( typeof options.parent != "undefined" ) {
                this.parent = options.parent;
            }
        },

        labelClicked: function () {},

        iconClicked: function () {},

        getIcon: function () {
            if ( this.icon == null ) {
                this.icon = $('<i class="tree-icon"></i>');

                var _this = this;
                this.icon.on('click', function () {
                    _this.iconClicked();
                });
            }

            return this.icon;
        },

        getLabel: function () {
            if ( this.label == null ) {
                this.label = $('<div class="tree-item-label">' + this.object['name'] + '</div>');

                var _this = this;
                this.label.on('click', function () {
                    _this.labelClicked();
                });
            }

            return this.label;
        },

        getHeader: function () {
            if ( this.header == null ) {
                this.header = $('<div class="tree-item-header" tabindex="0"></div>');
                this.header.append(this.getIcon());
                this.header.append(this.getLabel());
            }

            return this.header;
        },

        getItemContainer: function () {
            if ( this.itemContainer == null ) {
                this.itemContainer = $('<div class="tree-item"></div>');
                this.itemContainer.append(this.getHeader());
            }

            return this.itemContainer;
        },

        setType: function ( type ) {},

        getListItemContainer: function () {
            if ( this.listItemContainer == null ) {
                this.listItemContainer = $('<li class="tree-list-item"></li>');
                this.listItemContainer.append(this.getItemContainer());
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
    global.GD.TreeView = TreeView;

})(typeof window === 'undefined' ? this : window, jQuery);
