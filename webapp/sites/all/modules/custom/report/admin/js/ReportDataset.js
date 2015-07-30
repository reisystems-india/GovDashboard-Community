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
        throw new Error('ReportDataset requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportDataset requires GD');
    }

    var GD = global.GD;

    var ReportDataset = GD.Class.extend({
        name: null,
        options: null,

        columnData: null,
        columnLookup: null,

        facts: null,
        attributes: null,
        measures: null,

        init: function ( object, options ) {
            $.extend(this,object);
            this.options = options;
        },

        getName: function () {
            return this.name;
        },

        getDisplayName: function () {
            return this.displayName;
        },

        parseColumns: function ( element, parent, parentId, datasetSysname, parents ) {
            if ( $.isArray(element) ) {
                for ( var i = 0; i < element.length; i++ ) {
                    var attr = {
                        name: element[i].publicName,
                        id: element[i].name,
                        description: element[i].description,
                        available: element[i].available,
                        datasetId: datasetSysname,
                        text: element[i].publicName,
                        val: element[i].name
                    };

                    if ( !element[i].isVisible ) {
                        continue;
                    }

                    if ( parentId ) {
                        attr.parentId = parentId;
                    }

                    if ( element[i].datasetName ) {
                        attr.dataset = element[i].datasetName;
                        attr.datasetName = element[i].datasetName;
                    }

                    if ( parents ) {
                        attr.parents = parents;
                    }

                    //MARKER1: Why this code
                    if ( element[i].type && element[i].isSelectable ) {
                        if ( element[i].type.applicationType ) { // if elements[i].type is empty object
                            if ( element[i].type.applicationType.indexOf('@') == -1 ) {
                                attr.type = element[i].type.applicationType;//.replace(":", "_");
                            } else {
                                attr.type = 'string';
                            }
                        } else {
                            attr.type = 'string';
                        }
                        attr.scale = element[i].type.scale;
                    } else {
                        attr.type = 'disabled';
                    }

                    if (GD.Column.isMeasure(attr['id'])) {
                        this.getMeasureIDs().push(attr['id']);
                    }

                    this.columnLookup[attr['id']] = attr;

                    if ( element[i]['elements'] && element[i]['elements'].length ) {
                        attr['children'] = [];
                        var p = [];
                        if (parents) {
                            p = parents.slice(0);
                        }
                        p.push(attr['id']);
                        this.parseColumns(element[i]['elements'], attr['children'], attr['id'], datasetSysname, p);
                    }

                    if (parent) {
                        parent.push(attr);
                    }
                }
            }
        },

        findColumn: function ( name ) {
            if ( !this.columnLookup ) {
                this.getColumns();
            }

            if ( this.columnLookup[name] ) {
                var column = this.columnLookup[name];
                if(column.hasOwnProperty("parents") && column.parents instanceof Array){
                    column.name = this.columnLookup[column.parentId].name +"/"+ column.text ;
                }
                return column;
            }

            return null;
        },

        getColumn: function ( name ) {
            return this.findColumn(name);
        },

        getColumns: function () {
            if ( !this.columnData ) {
                this.columnData = [];
                this.columnLookup = {};

                this.parseColumns(this.attributes, this.columnData, this.name, this.name);
                this.parseColumns(this.facts, this.columnData, this.name, this.name);
                this.parseColumns(this.measures, this.columnData, this.name, this.name);
            }
            return this.columnData.slice(0);
        },

        getMeasureIDs: function() {
            if (!this.measureIDs) {
                this.measureIDs = [];
            }

            return this.measureIDs;
        },

        getAvailableMeasures: function() {
            if (!this.availableMeasures) {
                this.availableMeasures = [];
                var measures = this.getMeasureIDs();
                var _this = this;
                $.each(measures, function(i, id) {
                    _this.availableMeasures.push(_this.findColumn(id));
                });
            }

            return this.availableMeasures.slice(0);
        },

        getColumnLookup: function () {
            return this.columnLookup;
        }
    });

    // add to namespace
    GD.ReportDataset = ReportDataset;


})(typeof window === 'undefined' ? this : window, jQuery);