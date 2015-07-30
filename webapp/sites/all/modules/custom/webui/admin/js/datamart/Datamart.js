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

var Datamart = isc.defineClass("Datamart");

Datamart = Datamart.addClassProperties({
    _instance: null,

    getInstance: function() {
        if ( Datamart._instance === null ) {
            Datamart._instance = Datamart.create({});
        }

        return Datamart._instance;
    }
});

Datamart = Datamart.addProperties({

    record: null,

    load: function ( id, callback ) {
        if ( id ) {
            DatamartDS.getInstance().fetchRecord(id, function(dsResponse, data, dsRequest) {
                Datamart.record = data[0];
                if ( callback ) {
                    callback();
                }
            });
        }
    },

    reset: function () {
        this.record = null;
    },

    setRecord: function ( record ) {
        this.record = record;
    },

    getRecord: function () {
        return this.record;
    },

    getId: function () {
        return this.record.id;
    }

});

