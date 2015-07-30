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
        throw new Error('ReportColorColumnTree requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportColorColumnTree requires GD');
    }

    var GD = global.GD;

    GD.ReportColorColumnTree = GD.ReportColumnTree.extend({
        columnChanged: function() {},

        getColumnData: function() {
            var datasetObj = this.getController().getReport().getDataset();
            if ( !this.columnData ) {
                if ( this.object ) {
                    this.columnData = [{
                        'type': 'none',
                        'text': '< No Color >',
                        'id': null
                    }];
                    this.columnLookup = {};
                    var _this = this;
                    var parseColumn = function(column) {
                        var exclude = false;
                        if (column['type'] != 'string') {
                            exclude = true;
                        }

                        var l = [];
                        if (column['children']) {
                            for (var k in column['children']) {
                                var child = parseColumn(column['children'][k]);
                                if (child) {
                                    l.push(child);
                                }
                            }
                        }

                        var c = $.extend({}, column);
                        if (exclude && !l.length) {
                            return null;
                        } else {
                            if (exclude) {
                                c['type'] = 'disabled';
                            }
                            c['children'] = l;
                            _this.columnLookup[c['id']] = c;
                            return c;
                        }
                    };
                    var columnData = datasetObj.getColumns();
                    for (var i in columnData) {
                        var column = parseColumn(columnData[i]);
                        if (column) {
                            this.columnData.push(column);
                        }
                    }
                }
            }

            return this.columnData;
        }
    });
})(window ? window : window, jQuery);
