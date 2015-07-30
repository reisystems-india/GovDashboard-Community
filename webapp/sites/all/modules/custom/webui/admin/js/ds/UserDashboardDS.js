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

// User Dashboard ListGrid Client-Only DataSource

var UserDashboardDS = isc.defineClass("UserDashboardDS", "DataSource");

UserDashboardDS = UserDashboardDS.addClassProperties({
    getInstance: function() {
        if (!this._instance) {
            this._instance = UserDashboardDS.create({
                ID:"UserDashboardDS",
                clientOnly:true,
                fields: [
                    {
                        name:"dashboardid",
                        title:"Dashboard Id"
                    },
                    {name:"dbeditaccess", title:"Dashboard Edit"},
                    {name:"dashboardname",title:"Dashboard Name"},
                    {name:"dashboardesc", title:"Dashboard Desc"},
                    {name:"dashboardtime",title:"Dashboard Time"}
                ]
            });
        }
        return this._instance;
    }
});
