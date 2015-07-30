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
        throw new Error('DashboardLinkListForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DashboardLinkListForm requires GD');
    }

    var GD = global.GD;

    global.GD.DashboardLinkListForm = GD.DashboardListForm.extend({
        getItems: function() {
            return this.object.getLinks();
        },

        setItem: function(items, key) {
            items[key]['val'] = items[key].getId();
            items[key]['text'] = items[key].getText();
            this.items.push(items[key]);
        },

        getIcons: function() {
            return ['trash'];
        },

        removeItemFromDashboard: function() {
            this.dashboard.removeLink(this.items[this.removingItem]);
        },

        getModalTitle: function() {
            return 'Delete Link';
        },

        getItemName: function() {
            return 'Link';
        },

        attachEventHandlers: function(addHandler, clickHandler) {
            this._super(addHandler);
            this.itemCallback = clickHandler;
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);