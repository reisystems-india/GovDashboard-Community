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
        throw new Error('Dataset requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('Dataset requires GD');
    }

    var GD = global.GD;

    var Dataset = GD.Class.extend({
        options: null,

        nid: null,
        publicName: null,
        name: null,
        description: null,
        type: null,

        init: function(object, options) {
            $.extend(this,object);
            this.options = options;
        },

        isNew: function () {
            return (this.name === null);
        },

        getPublicName: function () {
            return this.publicName;
        },

        getName: function () {
            return this.name;
        },

        getDescription: function () {
            return this.description;
        },

        setPublicName: function ( name ) {
            this.publicName = name;
        },

        setDescription: function ( desc ) {
            this.description = desc;
        },

        isNew: function () {
            return this.nid == null;
        },

        isReadOnly: function () {
            return !this.nid;
        },

        getRawDataset: function () {
            var r = {};

            for (var p in this) {
                if ( typeof this[p] != 'function' ) {
                    r[p] = this[p];
                }
            }

            return r;
        }
    });

    // add to global space
    global.GD.Dataset = Dataset;

})(typeof window === 'undefined' ? this : window, jQuery);