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
        throw new Error('TreeItemView requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('TreeItemView requires GD');
    }

    var GD = global.GD;

    var TreeItemView = GD.TreeView.extend({

        clicked: function () {
            if ( this.object['additionalParameters']['isSelectable'] ) {
                this.parent.selectedType(this.object['additionalParameters']['sysname'], this.object['additionalParameters']['displayName'], this.getHeader());
            }
        },

        labelClicked: function () {
            this.clicked();
        },

        iconClicked: function () {
            this.clicked();
        },

        setType: function ( type ) {
            if ( this.object['additionalParameters']['sysname'] == type ) {
                this.getHeader().addClass('tree-selected');
            }
        },

        getIcon: function () {
            if ( this.icon == null ) {
                this.icon = this._super();
                this.icon.addClass('tree-dot');
            }

            return this.icon;
        }
    });

    // add to global space
    global.GD.TreeItemView = TreeItemView;

})(typeof window === 'undefined' ? this : window, jQuery);
