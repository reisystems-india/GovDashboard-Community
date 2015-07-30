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
        throw new Error('ReportFormatListForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportFormatListForm requires GD');
    }

    var GD = global.GD;

    global.GD.ReportFormatListForm = GD.ReportListForm.extend({
        getItems: function() {
            return this.object.getColumnConfigs();
        },

        setItem: function(items, key) {
            items[key]['val'] = this.items.length;
            items[key]['text'] = items[key].displayName;
            this.items.push(items[key]);
        },

        getIcons: function() {
            return ['trash'];
        },
        removeTempConfig: function(){
            this.tmpRemovedItems = [];
        },
        updateAddedConf: function(selectedColumns){
            selectedColumns = selectedColumns || this.options.builder.getReport().getColumns();
            var tmpItemsId = $.map(this.tmpRemovedItems, function(el, i){
                return el.columnId;
            }),
            tmpItemMap = {};
            for (i in this.tmpRemovedItems){
                tmpItemMap[this.tmpRemovedItems[i].columnId] = this.tmpRemovedItems[i];
            }
            if (this.items) {
                var itemsLength = this.items.length;
                for(i=itemsLength-1; i>=0; i--){
                    var formatColumnName = this.items[i].columnId;
                    if($.inArray(formatColumnName,selectedColumns) === -1){
                        this.tmpRemovedItems.push(this.items[i]);
                        this.items.splice(i, 1);
                        this.object.getColumnConfigs().splice(i, 1);
                    }
                }
                if(this.tmpRemovedItems.length > 0){
                    for(i in selectedColumns){
                        if($.inArray(selectedColumns[i], tmpItemsId) !== -1){
                            this.items.push(tmpItemMap[selectedColumns[i]]);
                            this.object.getColumnConfigs().push(tmpItemMap[selectedColumns[i]]);
                        }
                    }
                }
            }
        },
        getItemList: function() {
            if (!this.itemList) {
                this.itemList = new GD.BuilderListView(null, this.getFormContainer(), { 'header': this.getItemName(), 'icons': this.getIcons()});
                var columnLookup = this.options.builder.getReport().getDataset().getColumnLookup(),
                    selectedColumns = this.options.builder.getReport().getColumns();
                if (this.items) {
                    var itemsLength = this.items.length;
                    for(i=itemsLength-1; i>=0; i--){
                        var formatColumnName = this.items[i].columnId;
                        if(!columnLookup.hasOwnProperty(formatColumnName)){
                            var formula = this.options.builder.getReport().getFormula(formatColumnName);
                            if (formula) {
                                //this.items[i].unsupported = false;
                            } else {
                                this.items.splice(i,1);
                                /*this.items[i]["text"] = this.items[i]["name"] = "Unsupported Column:" + formatColumnName;
                                this.items[i].unsupported = true;*/
                            }
                        }
                        else{
                            //this.items[i].unsupported = false;
                        }
                    }
                    this.itemList.renderOptions(this.items);
                }
            }

            return this.itemList;
        },

        removeItemFromReport: function() {
            this.object.getColumnConfigs().splice(this.removingItem, 1);
            //this.object.getColumnConfigs().removeFilter(this.items[this.removingItem]);
            $(document).trigger({
                type: 'remove.column.format'
            });
        },

        getModalTitle: function() {
            return 'Delete Format';
        },

        getItemName: function() {
            return 'Format';
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);