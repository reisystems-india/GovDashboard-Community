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

var AccountDashboards = isc.defineClass('AccountDashboards');

AccountDashboards = AccountDashboards.addClassProperties({
    getDashboardHeaderLayout: function () {
        if ( typeof this.dashboardHeaderLayout == 'undefined' )
        {
            this.dashboardHeaderLayout = isc.HLayout.create({
                width: '100%',
                height: 10,
                membersMargin: 0,
                styleName: 'reportHeader',
                layoutMargin: 0,
                layoutTopMargin: 17,
                layoutBottomMargin: 10,
                backgroundColor: '#FFF',
                members: [
                    isc.Label.create({
                        contents: '<h2>Dashboards</h2>',
                        width: '100%',
                        height: 10,
                        autoDraw: false,
                        styleName: 'accountHeader'
                    }),
                    isc.AccountDashboards.getDashboardDatasourceFilter()
                ]
            });
        }
        return this.dashboardHeaderLayout;
    },

    getDashboardDatasourceFilter: function() {
        if (typeof this.dashboardDatasourceFilter == 'undefined') {
            this.dashboardDatasourceFilter = isc.DynamicForm.create({
                ID: 'AccountDashboardDatasourceFilter',
                fields: [
                    {
                        name: 'datamartId',
                        showTitle: true,
                        title: "Filter By Topic",
                        type: 'select',
                        operator:'equals',
                        defaultValue:""
                    }
                ],
                itemChanged: function() {
                    AccountActions.applySearchCriteria();
                }
            });
        }

        return this.dashboardDatasourceFilter;
    },

    getDashboardsListGrid: function() {
        if ( typeof this.dashboardsListGrid == 'undefined') {
            this.dashboardsListGrid = isc.ListGrid.create({
                ID: "AccountDashboardListGrid",
                styleName: "tableGrid",
                headerAutoFitEvent: "none",
                leaveScrollbarGap: false,
                wrapCells: true,
                fixedRecordHeights: false,
                autoFetchData : true,
                width: "100%",
                /*autoFitData:"both",*/
                sortField: "changed",
                sortDirection: "descending",
                dataSource: DashboardDS.getInstance(),
                initialCriteria:{datasource:Datasource.getInstance().getName()},
                fields: [
                    {name:"name", title:"Dashboard Name"},
                    {name:"datasourcePublicName", title:"Topic", width:"10%"},
                    {name:"author", title:"Author", width:"10%"},
                    {name:"changed", title:"Last Modified", width:"10%"},
                    {name:"groupNames", title:"Groups"},
                    {name:"userNames", title:"Users"}
                ]
            })
        }

        return this.dashboardsListGrid;
    }
});