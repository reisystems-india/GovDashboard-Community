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
        throw new Error('ReportFilterColumnTree requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportFilterColumnTree requires GD');
    }

    var GD = global.GD;

    global.GD.ReportFilterColumnTree = GD.ReportColumnTree.extend({
        formContainer: null,
        treeView: null,
        treeOptions: null,
        columnData: null,
        columnLookup: null,
        columnsQueue: null,

        init: function(object, container, options) {
            this._super(object, container, options);
        },

        getFormulaLookup: function() {
            if (!this.formulaLookup) {
                this.formulaLookup = this.getController().getReport().getFormulaLookup();
            }

            return this.formulaLookup;
        },

        loadedTreeCallback: function() {
            this.attachCommentIcon();
            this.attachTypeTooltip();
        },

        loadTreeView: function () {
            //  Add formulas to filter tree
            var formulas = this.getController().getReport().getFormulas(),
                columnNameArr = this.columnData.map(function(val,i){
                    return val.name;
                });
            if(formulas){
                for (var i = 0; i < formulas.length; i++) {
                    if($.inArray(formulas[i].id,columnNameArr) === -1){
                        var r = formulas[i].getRaw();
                        r['text'] = r['publicName'];
                        r['val'] = r['name'];
                        this.columnData.push(r);
                    }

                }
            }

            this._super();
        },

        findById: function(id) {
            var column = null;

            if (this.columnLookup) {
                if (this.columnLookup[id]) {
                    column = this.columnLookup[id];
                }
            }

            var lookup = this.getFormulaLookup();
            if (lookup) {
                if (lookup[id]) {
                    column = lookup[id];
                }
            }

            return column;
        },

        columnChanged: function(selected) {
            // do nothing
            // or go to next form instead of having to click next.
        },

        applyColumnSelection: function() {
			// do nothing
        }
    });

})(window ? window : window, jQuery);
