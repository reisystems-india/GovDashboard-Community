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
        throw new Error('ReportFooterListForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportFooterListForm requires GD');
    }

    var GD = global.GD;

    global.GD.ReportFooterListForm = GD.ReportListForm.extend({
        getItems: function() {
            var items = [];

            $.each(this.object.getFooters(),function(i, v){
                items.push($.extend({}, v));
            });

            return items;
        },

        setItem: function(items, key) {
            items[key]['val'] = this.items.length;
            this.items.push(items[key]);
        },

        getIcons: function() {
            return ['trash'];
        },

        getItemList: function() {
            if (!this.itemList) {
                this.itemList = new GD.BuilderListView(null, this.getFormContainer(), { 'header': this.getItemName(), 'icons': this.getIcons()});
                if (this.items) {
                    this.itemList.renderOptions(this.items);
                }
            }

            return this.itemList;
        },

        removeItemFromReport: function() {
            this.object.removeFooter(this.items[this.removingItem]);
        },

        getModalTitle: function() {
            return 'Delete Footer';
        },

        getItemName: function() {
            return 'Footer';
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);