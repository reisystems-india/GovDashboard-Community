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


// Used in group account dashboard list section
var GroupDashboardDS = isc.defineClass("GroupDashboardDS", "DataSource");

GroupDashboardDS = GroupDashboardDS.addClassMethods({
    getInstance: function() {
        if (!this._instance) {
            this._instance = GroupDashboardDS.create({
                ID:"GroupDashboardDS",
                clientOnly:true,
                fields: [
                    {name:"gdashboardid",  title:"Dashboard Id"},
                    {name:"gdbeditaccess", title:"Dashboard Edit"},
                    {name:"gdashboardname",title:"Dashboard Name"},
                    {name:"gdashboardesc", title:"Dashboard Desc"},
                    {name:"gdashboardtime",title:"Dashboard Time"}
                ]
            });
        }
        return this._instance;
    }
});
