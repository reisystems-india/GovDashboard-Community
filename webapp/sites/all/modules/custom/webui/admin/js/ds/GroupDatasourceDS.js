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
 * User (datamart admin) Datasource ListGrid Client-Only DataSource
 */

var GroupDatasourceDS = isc.defineClass("GroupDatasourceDS", "DataSource").addClassProperties({
    _instance: null,
    getInstance: function() {
        if(!this._instance) {
            this._instance = GroupDatasourceDS.create({
                ID: "AccountGroupDatasource",
                fields:[
                    {name:"publicName", title:"Topic Name"},
                    {name:"changed", title:"Last Modified"},
                    {name:"description", title:"Description"}
                ],
                clientOnly: true,
                testData: GroupDatasourceData.getListGridData()
            });
        }
        return this._instance;
    }
});
