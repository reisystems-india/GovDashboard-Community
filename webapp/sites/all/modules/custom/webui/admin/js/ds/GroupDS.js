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


var GroupDS = isc.defineClass("GroupDS", "DrupalDS").addClassProperties({
    _instance: null,
    getInstance: function() {
        if ( this._instance === null ) {
            this._instance = GroupDS.create({
                ID: "groupDS",
                resource: "group"
            });
        }
        return this._instance;
    }
});

GroupDS = GroupDS.addProperties({
    fields: [
        {name:"id", primaryKey:true, type: "integer", hidden:true},
        {name:"name", type: "string"},
        {name:"description", type: "string"}
    ],

    init: function() {
        this.Super("init", arguments);
        this.dataURL = '/api/' + this.resource + '.json';
    },

    transformRequest: function(dsRequest) {

        if ( dsRequest.operationType == 'fetch' && dsRequest.data['id'] != '' && dsRequest.data['id'] != null) {
             dsRequest.actionURL = '/api/' + this.resource + '/'+ dsRequest.data['id'] +'.json';
        }

        if ( dsRequest.operationType == 'update' || dsRequest.operationType == 'remove' || dsRequest.operationType == 'add' ) {
            dsRequest.httpHeaders = {"X-CSRF-Token": GovDashboard.token};
        }

        if ( dsRequest.operationType == 'update' || dsRequest.operationType == 'remove' ) {
            dsRequest.actionURL = '/api/' + this.resource + '/'+ dsRequest.data['id'] +'.json';

        }

        if ( dsRequest.operationType == 'add' || dsRequest.operationType == 'update' ) {
            dsRequest.contentType = 'application/json';
            return "{ \"" + this.resource + "\": " + isc.JSONEncoder.create().encode(dsRequest.data) + "}";
        }
    }
});
