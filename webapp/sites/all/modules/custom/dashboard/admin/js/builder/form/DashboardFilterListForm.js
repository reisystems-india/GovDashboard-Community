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
        throw new Error('DashboardFilterListForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DashboardFilterListForm requires GD');
    }

    var GD = global.GD;

    global.GD.DashboardFilterListForm = GD.DashboardListForm.extend({
        getItems: function() {
            return this.object.getFilters();
        },

        setItem: function(items, key) {
            items[key]['val'] = this.items.length;
            if (items[key]['invalid']) {
                items[key]['text'] = "Unsupported filter: " + items[key]['name'];
            }
            this.items.push(items[key]);
        },

        getIcons: function() {
            return ['public', 'trash'];
        },

        getItemList: function() {
            if (!this.itemList) {
                var _this = this;
                this.itemList = new GD.BuilderListView(null, this.getFormContainer(), { 'header': this.getItemName(), 'icons': this.getIcons(),
                'publicCondition': function(v) {
                    return _this.isExposed(v);
                }});

                if (this.items) {
                    this.itemList.renderOptions(this.items);
                }
            }

            return this.itemList;
        },

        isExposed: function(name) {
            var exposed = false;
            $.each(this.items, function(k, i) {
                if (i['val'] == name) {
                    exposed = i['exposed'] == 1;
                    return false;
                }
            });
            return exposed;
        },

        removeItemFromDashboard: function() {
            this.dashboard.removeFilter(this.items[this.removingItem]);
        },

        itemClicked: function(v) {
            if (!this.items[v]['invalid'] && this.itemCallback) {
                this.itemCallback(this.items[v]);
            }
        },

        getModalTitle: function() {
            return 'Delete Filter';
        },

        getItemName: function() {
            return 'Filter';
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);