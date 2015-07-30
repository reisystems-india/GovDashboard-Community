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

/**
 * This DS is not used in application yet. This is sample implementation of REST data-source.
 * created by: ykhadilkar
 */

var RestDS = isc.defineClass("RestDS", "RestDataSource").addProperties({
    _instance:null,
   dataFormat: "json",
    fields: [
        {name:"id", primaryKey:true, type: "integer", hidden:true},
        {name:"name", type: "string"},
        {name:"description", type: "string", canFilter: false},
        {name:"lastModified", type: "date", canFilter: false},
        {name:"lastModifiedBy", type: "string"}
    ],
    dataURL:null,
    operationBindings: [
        {operationType: "fetch", dataProtocol:"getParams"},
        {operationType: "add", dataProtocol:"postMessage"},
        {operationType: "update", dataProtocol:"postMessage", requestProperties: {httpMethod: "PUT"}},
        {operationType: "remove", dataProtocol:"getParams", requestProperties: {httpMethod: "DELETE"}}
    ]
});

RestDS = RestDS.addClassMethods({
    getInstance: function() {
        if (!this._instance) {
            this._instance = RestDS.create({
                ID:"datsetRestDS",
                resource:"dataset"
            });
        }
        return this._instance;
    }
});

RestDS = RestDS.addMethods({
    init: function() {
        this.Super("init", arguments);
        this.dataURL = '/api/' + this.resource + '.json';
    },
    transformRequest: function(dsRequest) {
        if ( dsRequest.operationType == 'fetch' && dsRequest.data && dsRequest.data['id'])
        {
             dsRequest.actionURL = '/api/' + this.resource + '/'+ dsRequest.data['id'] +'.json';
        }

        if ( dsRequest.operationType == 'update' || dsRequest.operationType == 'remove' )
        {
            dsRequest.httpHeaders = {"X-CSRF-Token" : GovDashboard.token };
            dsRequest.actionURL = '/api/' + this.resource + '/'+ dsRequest.data['id'] +'.json';
        }

        if ( dsRequest.operationType == 'add' || dsRequest.operationType == 'update' )
        {
            dsRequest.httpHeaders = {"X-CSRF-Token" : GovDashboard.token };
            dsRequest.contentType = 'application/json';
            var payload = isc.JSONEncoder.create({
                serializeInstances:"long",
                showDebugOutput:true,
                skipInternalProperties:true
            }).encode(dsRequest.data);
            return "{ \"" + this.resource + "\": " + payload + "}";
        }
        return this.Super("transformRequest", arguments);
    },
    transformResponse: function(dsResponse, dsRequest, data){
        var dsResponse = this.Super("transformResponse", arguments);
        // ... do something to dsResponse ...
        return dsResponse;
    }
});
