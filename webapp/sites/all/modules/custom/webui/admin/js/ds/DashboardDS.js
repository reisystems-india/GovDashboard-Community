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


var DashboardDS = isc.defineClass("DashboardDS", "DrupalDS").addClassProperties({
    _instance: null,
    getInstance: function() {
        if (!this._instance) {
            this._instance = DashboardDS.create({
                ID:"dashboardDS",
                resource: "dashboard"
            });
        }
        return this._instance;
    }
});

DashboardDS = DashboardDS.addProperties({
    fields: [
        {name:"id", primaryKey: true, type: "integer", hidden: true},
        {name:"name", title:"Dashboard", required: true},
        {name:"description", title:"Description"},
        {name:"author", title:"Author", valueXPath:"author/name"},
        {name:"changed", title:"Last Modified", type: "datetime"},
        {name:"created", title:"Created", type: "datetime"},
        {
            name:"reports",
            title:"Reports",
            fields:[
                {name:"id", primaryKey: true, type: "integer", hidden: true},
                {name:"name", title:"Name"}
            ],
            multiple:"true"
        },
        {name: "reportnames", title: "Reports", valueXPath:"reports/name"},

        {
            name:"datasets",
            title:"Data",
            fields:[
                {name:"id", primaryKey: true, type: "integer", hidden: true},
                {name:"name", title:"Name"}
            ],
            multiple:"true"
        },
        {name: "datasetNames", title: "Data", valueXPath:"datasets/publicName"},
        {name:"config", hidden: true},
        {
            name:"datasource",
            title:"Topic",
            fields:[
                {name:"name", primaryKey: true, type: "string", hidden: true},
                {name:"publicName", title:"Name"}
            ]
        },
        {name: "datasourceName", title: "Datasource Name", valueXPath:"datasource/name"},
        {name: "datasourcePublicName", title: "Datasource Public Name", valueXPath:"datasource/publicName"},
        {name: "groupNames", title: "Groups", valueXPath:"groups/name"},
        {name: "userNames", title: "Users", valueXPath:"users/fullname"},
        {name: "public", title: "Public", valueXPath:"public"}
    ]
});



/**
 * For Dashboard Filters
 */
var DashboardColumnsDS = isc.defineClass("DashboardColumnsDS", "DrupalActionDS");

DashboardColumnsDS = DashboardColumnsDS.addClassProperties({
    _instance: null,
    getInstance: function() {
        if (!this._instance) {
            this._instance = DashboardColumnsDS.create({
                ID:"dashboardColumnsDS",
                resource:"dashboard"
            });
        }
        return this._instance;
    }
});

DashboardColumnsDS = DashboardColumnsDS.addProperties({
    fields: [
        {name:"id", primaryKey: true, type: "string", hidden: true},
        {name:"name", title:"Name", required: true},
        {name:"description", title:"Description"},
        {
            name:"reports",
            title:"Reports",
            fields:[
                {name:"id", primaryKey: true, type: "integer", hidden: true},
                {name:"name", title:"Name"}
            ],
            multiple:"true"
        },
        {name: "reportnames", title: "Reports", valueXPath:"reports/name"},
        {name:"type", title: "Type"},
        {name:"dataset", title: "Dataset"}
    ]
});

DashboardColumnsDS = DashboardColumnsDS.addProperties({
    init: function() {
        this.Super("init", arguments);
        this.dataURL = '/api/' + this.resource + '.json';
    },
    transformRequest: function(dsRequest) {
        if ( dsRequest.operationType == 'fetch' ) {
            dsRequest.actionURL = '/api/' + this.resource + '/'+ dsRequest.data['id'] +'/columns.json';
            dsRequest.contentType = 'application/json';

            var data = dsRequest.data;

            if ( typeof data.name != "undefined" ) {
                data.query = data.name + '*';
            }

            var payload = isc.JSONEncoder.create({
                serializeInstances:"long",
                showDebugOutput:true,
                skipInternalProperties:true
            }).encode(data);
            return '{ "params": ' + payload + '}';
        }
    }
});