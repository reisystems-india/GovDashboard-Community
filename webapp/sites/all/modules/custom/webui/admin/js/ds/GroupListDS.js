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


var GroupListDS = isc.defineClass("GroupListDS", "DataSource");

GroupListDS = GroupListDS.addClassProperties({

    _instance: null,

    getInstance: function() {
        if ( this._instance == null ) {
            this._instance = GroupListDS.create({
                ID:"groupListDS",
                resource:"group"
            });
        }
        return this._instance;
    }
});

GroupListDS = GroupListDS.addProperties({
    resource:null,
    dataFormat: "json",
    dataURL:null,

    init: function() {
        this.Super("init", arguments);
        this.dataURL = '/api/' + this.resource + '.json';
    },

    transformRequest: function(dsRequest) {
        if ( dsRequest.operationType == 'fetch' && dsRequest.data['id'] != '' && dsRequest.data['id'] != null) {
            dsRequest.actionURL = '/api/' + this.resource + '/'+ dsRequest.data['id'] +'/user_groups.json';
        }
    }

});