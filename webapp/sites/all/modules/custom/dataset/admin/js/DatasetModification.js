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
        throw new Error('DatasetModification requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DatasetModification requires GD');
    }

    var GD = global.GD;

    var DatasetModificationContext = GD.Class.extend({
        init: function () {
            this.isUpdated = false;
            this.isNameUpdated = false;
            this.isNameDescription = false;
            this.newColumns = [];
            this.changedColumns = {};
        },

        hasColumnChanged: function ( c ) {
            return typeof this.changedColumns[c.getName()] != 'undefined' && this.changedColumns[c.getName()] != null;
        }
    });

    var ColumnModificationContext = GD.Class.extend({
        init: function () {
            this.isUpdated = false;
            this.isNameUpdated = false;
            this.isTypeUpdated = false;
        }
    });

    var DatasetModification = GD.Class.extend({
        original: null,
        current: null,
        response: null,

        context: null,

        init: function ( original, current, response ) {
            this.original = original;
            this.current = current;
            this.response = response;
        },

        execute: function () {
            this.gatherChanges();
            var _this = this;

            $.event.trigger({
                'type': 'datasetModificationAnalyzed',
                'context': _this.context
            });

            return this.context;
        },

        gatherChanges: function () {
            this.context = new DatasetModificationContext();

            // name changed
            if ( this.original.publicName != this.current.publicName ) {
                this.context.isUpdated = true;
                this.context.isNameUpdated = true;
            }

            // description changed
            if ( this.original.description != this.current.description ) {
                this.context.isUpdated = true;
                this.context.isDescriptionUpdated = true;
            }

            // current column mods
            for ( var i = 0, ocount = this.original.columns.length; i<ocount; i++) {
                for ( var j = 0, ccount = this.current.columns.length; j<ccount; j++) {
                    if ( this.original.columns[i].name == this.current.columns[j].name ) {
                        if ( !this.context.changedColumns[this.original.columns[i].name] ) {
                            this.context.changedColumns[this.original.columns[i].name] = new ColumnModificationContext();
                        }

                        // name
                        this.context.changedColumns[this.original.columns[i].name].isNameUpdated = (this.original.columns[i].publicName != this.current.columns[j].publicName);

                        // type
                        this.context.changedColumns[this.original.columns[i].name].isTypeUpdated = (this.original.columns[i].type.applicationType != this.current.columns[j].type.applicationType);

                    }
                }
            }

            // new columns
            if ( this.original.columns.length != this.current.columns.length ) {
                for ( i = 0; i < this.current.columns.length; i++ ) {
                    if ( this.current.columns[i]['name'] == null ) {
                        this.context.newColumns.push(this.current.columns[i]);
                    }
                }
            }
        }

    });

    // add to global space
    global.GD.DatasetModification = DatasetModification;

})(typeof window === 'undefined' ? this : window, jQuery);