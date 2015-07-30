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
        throw new Error('ReportTrafficLightListForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportTrafficLightListForm requires GD');
    }

    var GD = global.GD;

    global.GD.ReportTrafficLightListForm = GD.ReportListForm.extend({
        getItems: function() {
            var items = [];

            var _this = this;
            $.each(this.object.getTrafficLightOptions(),function(k,v){
                var item = v,
                    column = _this.object.getColumn(k);

                if(column){
                    v.displayName = column.name;
                    v.columnId = k;
                    v.columnType = column.type;
                }
                items.push(v);
            });

            return items;
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

            if (this.items) {
                for(i in this.items){
                    var column = this.items[i].columnId;
                    if($.inArray(column,selectedColumns) === -1){
                        this.tmpRemovedItems.push(this.items[i]);
                        this.removingItem = i;
                        this.removeItemFromReport();
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
                    var itemLength = this.items.length,
                        i;
                    for(i=itemLength-1; i>=0; i--){
                        var column = this.items[i].trafficColumn;
                        if(!columnLookup.hasOwnProperty(column)){
                            var formula = this.options.builder.getReport().getFormula(column);
                            if (formula) {
                                //this.items[i].unsupported = false;
                            } else {
                                /*this.items.splice(i,1);
                                this.items[i]["text"] = this.items[i]["name"] = "Unsupported Column:" + column;
                                this.items[i].unsupported = true;*/
                            }
                        }else{
                            //this.items[i].unsupported = false;
                        }
                    }
                    this.itemList.renderOptions(this.items);
                }
            }

            return this.itemList;
        },

        removeItemFromReport: function() {
            this.object.removeTrafficLightOption(this.items[this.removingItem]);
        },

        getModalTitle: function() {
            return 'Delete Traffic Light';
        },

        getItemName: function() {
            return 'Traffic Light';
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);