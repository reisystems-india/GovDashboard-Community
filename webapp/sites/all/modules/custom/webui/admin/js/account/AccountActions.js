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

var AccountActions = isc.defineClass("AccountActions");

AccountActions = AccountActions.addClassProperties({

    exit: function(){
        AccountSectionActions.clickAccountNavButton('dashboards');
    },

    confirmExit: function(callback) {
        isc.confirm(
            "Changes have been made to the following:<br/><br/>" + Account.getAppliedChanges().join (", ") + ". <br/><br/>These changes will not be saved. Are you sure you want to Exit?",
            function(answer){
                if (answer) {
                    Account.clearChangeLog();
                    callback();
                }
            },
            {toolbarButtons:[
                isc.Dialog.YES,
                isc.Dialog.NO
            ],
            title: "Changes Have Been Made",
            canDragReposition: false,
            showModalMask: true}
        );
    },

    applySearchCriteria: function() {
        var criteria = AccountDashboards.getDashboardDatasourceFilter().getValuesAsCriteria();
        AccountDashboards.getDashboardsListGrid().filterData(criteria);
    },

    gotoDashboardLanding: function(){
        AccountDashboards.getDashboardDatasourceFilter().getField('datamartId').setValue("");
        AccountDashboards.getDashboardsListGrid().invalidateCache();
        //AccountDashboards.getDashboardsListGrid().dataArrived = function (startRow, endRow) {
            DatasourceDS.getInstance().fetchData(null, function(dsResponse, data) {
                var dashboards = [];
                var all = {"id":"", "name":"Show All"};
                dashboards.push(all);

                if(data != null){
                    for(var i=0; i<data.length; i++){
                        var dm = {"id":data[i].id, "name": data[i].name};
                        dashboards.push(dm);
                    }
                }

                dashboards = dashboards.getValueMap("id","name");
                AccountDashboards.getDashboardDatasourceFilter().getField('datamartId').setValueMap(dashboards);

            });
        //}
    },

    showPane: function(pane){
        AccountDashboards.getDashboardsListGrid().hide();
        UserDetailsLayout.getInstance().getContainer().hide();
        UserListLayout.getInstance().getUserTileGrid().hide();
        UserAddNewLayout.getInstance().getContainer().hide();
        GroupDetailsLayout.getInstance().getContainer().hide();
        GroupListLayout.getInstance().getGroupTileGrid().hide();
        GroupAddNewLayout.getInstance().getContainer().hide();
        AccountDashboards.getDashboardsListGrid().hide();
        AccountSection.getStatsPage().hide();

        pane.show();
    }

});