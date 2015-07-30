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
        throw new Error('ReportFilterListForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportFilterListForm requires GD');
    }

    var GD = global.GD;

    global.GD.ReportFilterListForm = GD.ReportListForm.extend({

        getItems: function() {
            return this.object.getController().getReport().getFilters();
        },

        setItem: function(items, key) {
            items[key]['val'] = this.items.length;
            this.items.push(items[key]);
        },

        getIcons: function() {
            return ['public', 'trash'];
        },

        getItemList: function() {
            if (!this.itemList) {
                var _this = this,
                    columnLookup = this.options.builder.getReport().getDataset().getColumnLookup(),
                    report = this.options.builder.getReport();
                this.itemList = new GD.BuilderListView(null, this.getFormContainer(), { 'header': this.getItemName(), 'icons': this.getIcons(),
                'publicCondition': function(v) {
                    return _this.isExposed(v);
                }});

                if ( this.items ) {
                    for(var i in this.items){
                        var filterColumnName =this.items[i].column?(this.items[i].column["name"] || this.items[i].column)
                            :"";
                        if(!columnLookup.hasOwnProperty(filterColumnName)){
                            var formulaLookup = report.getFormula(filterColumnName);
                            if (!formulaLookup) {
                                if(this.items[i].name && this.items[i].name.indexOf("Unsupported") === -1){
                                    this.items[i]["text"] = "Unsupported filter:" + this.items[i].name;
                                }else if(!this.items[i].name){
                                    this.items[i]["text"] = this.items[i]["name"] = "Unsupported filter:NA";
                                }
                               this.items[i].unsupported = true;
                            } else {
                                this.items[i].unsupported = false;
                            }
                        } else {
                            this.items[i].unsupported = false;
                        }
                    }
                    this.itemList.renderOptions(this.items);
                }
            }
            return this.itemList;
        },

        isExposed: function(name) {
            var exposed = false;
            $.each(this.items, function(k, v) {
                if (v['val'] == name) {
                    exposed = (v['exposed'] == 1 || v['exposed'] == true);
                    return false;
                }
            });
            return exposed;
        },

        removeItemFromReport: function() {
            this.object.deleteFilter(this.items[this.removingItem]);
        },

        getModalTitle: function() {
            return 'Delete Filter';
        },

        getItemName: function() {
            return 'Filter';
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);