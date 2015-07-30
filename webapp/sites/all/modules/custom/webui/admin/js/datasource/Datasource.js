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

var Datasource = isc.defineClass("Datasource");

Datasource = Datasource.addClassProperties({
    _instance: null,

    getInstance: function() {
        if ( Datasource._instance === null ) {
            Datasource._instance = Datasource.create({});
        }

        return Datasource._instance;
    }
});

Datasource = Datasource.addProperties({

    record: null,

    fetch: function ( id, callback ) {
        if ( id ) {
            DatasourceDS.getInstance().fetchData({name:id}, function(dsResponse, data, dsRequest) {
                if ( !dsResponse.errors ) {
                    Datasource.load(data[0],callback);
                }
            });
        }
    },

    load: function ( record, callback ) {

        this.reset();
        this.record = record;

        if ( callback ) {
            callback();
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

    getName: function () {
        return this.record.name;
    },

    getPublicName: function () {
        return this.record.publicName;
    },

    isActive: function () {
        return this.record.active;
    }

});

