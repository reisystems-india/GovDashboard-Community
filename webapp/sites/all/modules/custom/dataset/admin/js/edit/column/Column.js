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
        throw new Error('Column requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('Column requires GD');
    }

    var GD = global.GD;

    var Column = GD.Class.extend({
        options: null,

        nid: null,
        type: null,
        name: null,
        used: null,
        key: null,
        publicName: null,
        columnIndex: null,
        persistence: null,
        source: null,
        description: null,

        init: function(object, options) {
            $.extend(this,object);
            this.options = options;

            if ( this.type == null ) {
                this.type = {
                    applicationType: null,
                    scale: null
                };
            }

            if ( this.used == null ) {
                this.used = true;
            }

            if ( this.visible == null ) {
                this.visible = true;
            }
        },

        getDescription: function() {
            return this.description;
        },

        getColumnIndex: function () {
            return this.columnIndex;
        },

        setColumnIndex: function ( index ) {
            this.columnIndex = index;
        },

        getType: function() {
            return this.type.applicationType;
        },

        setType: function ( type ) {
            this.type.applicationType = type;
        },

        getScale: function () {
            return this.type.scale;
        },

        setScale: function ( scale ) {
            this.type.scale = scale;
        },

        getPublicName: function () {
            //  TODO Define null check in utils
            return (typeof this.publicName != 'undefined' && this.publicName != '' && this.publicName != null) ? this.publicName : '';
        },

        setPublicName: function ( name ) {
            this.publicName = name;
        },

        getName: function () {
            return this.name;
        },

        setName: function ( name ) {
            this.name = name;
        },

        isEnabled: function () {
            return this.used;
        },

        setEnabled: function ( enable ) {
            this.used = enable;
        },

        isPrimaryKey: function () {
            return this.key;
        },

        setPrimaryKey: function ( key ) {
            this.key = key;
        },

        isVisible: function () {
            return this.isVisible;
        },

        setVisible: function ( visible ) {
            this.isVisible = visible;
        },

        getSource: function () {
            return this.source;
        },

        setSource: function ( source ) {
            this.source = source;
        },

        getPersistence: function () {
            return this.persistence;
        },

        setPersistence: function ( persistence ) {
            this.persistence = persistence;
        },

        isNumericType: function () {
            var type = this.getType();
            return type == 'number' || type == 'integer' || type == 'percent' || type == 'currency';
        },

        isUsableInCalculation: function () {
            return this.isNumericType();
        },

        equals: function ( c ) {
            if ( typeof c != 'undefined' && c != null ) {
                if ( (c.name == this.name) || (c.name == null && c.publicName == this.publicName) ) {
                    return true;
                }
            }

            return false;
        },

        getRawColumn: function () {
            var r = {};
            for (var p in this) {
                if ( typeof this[p] != 'function' ) {
                    r[p] = this[p]
                }
            }
            return r;
        }
    });

    Column.isMeasure = function(name) {
        var isMeasure = false;
        if (name) {
            var parts = name.split(':');
            isMeasure = parts[0] === 'measure';
        }

        return isMeasure;
    };

    Column.canBeUsedInCalculation = function (c) {
        switch(c['type']['applicationType']) {
            case 'number':
            case 'integer':
            case 'percent':
            case 'currency':
                return true;
            default:
                return false;
        }
    };

    Column.getFullColumnName = function(column, lookup) {
        var name = column['name'];

        var names = [];
        if (column['parents'] && name.indexOf('/') === -1) {
            $.each(column['parents'], function(i, parent) {
                if (lookup[parent]) {
                    var n = lookup[parent]['name'];
                    if (n !== names[names.length - 1] && n.indexOf('/') === -1) {
                        names.push(n);
                    }
                }
            });
        }

        names.push(name);
        return names.join('/');
    };

    // interesting way to create constants under this column class but not get exported
    Column.PERSISTENCE__NO_STORAGE = function(){return 0;};
    Column.PERSISTENCE__STORAGE_CREATED = function(){return 1;};
    Column.PERSISTENCE__CALCULATED = function(){return 1000;};

    // add to global space
    global.GD.Column = Column;

})(typeof window === 'undefined' ? this : window, jQuery);