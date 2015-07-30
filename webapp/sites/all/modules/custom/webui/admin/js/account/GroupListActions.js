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

var GroupListActions = isc.defineClass('GroupListActions');

GroupListActions = GroupListActions.addClassProperties({
    clickGroupTile: function(data) {

        //load group object
        Group.getInstance().load(data[0]);

        // permissions
        // filter out unselected rows for data-mart admin roles.
        if ( GovDashboard.user.type != 1 ) {
            // Update user list
            GroupDetailsLayout.getInstance().getDatasourceAdminUserListGrid().invalidateCache();
            GroupUserData.setListGridData(data[0].users);
            GroupUserDS.getInstance().setCacheData(GroupUserData.getListGridData());

            // Update dashboard list
            GroupDetailsLayout.getInstance().getDatasourceAdminDashboardListGrid().invalidateCache();

            // Datasource Admin can only view the dashboards assigned to the Topics assigned to his group
            // but Datasource Admin can add his OWN dashboards to other groups.
            var groups = [];
            // current group viewed
            var group_current = Group.getInstance().getId();
            groups.push(group_current);
            // user's groups
            jQuery.each(GovDashboard.user.getGroups(),function(key,value){
                // don't include authenticated user group
                if ( key > 4 ) {
                    var group_user = parseInt(key);
                    groups.push(group_user);
                }
            });

            var criteria = {
                groups: groups.getUniqueItems(),
                datasource: Datasource.getInstance().getName()
            };

            GroupDetailsLayout.getInstance().getDatasourceAdminDashboardListGrid().fetchData(criteria);
            GroupListLayout.getInstance().updateDatasourceAdminDashboardSelection(data,criteria);

            //Update datasource list
            GroupDetailsLayout.getInstance().getDatasourceAdminDatasourceListGrid().invalidateCache();
            GroupDatasourceData.setListGridData(data[0].datasources);
            GroupDatasourceDS.getInstance().setCacheData(GroupDatasourceData.getListGridData());

            // Hide Admin Panes
            GroupDetailsLayout.getInstance().getUserListGrid().hide();
            GroupDetailsLayout.getInstance().getDashboardListGrid().hide();
            GroupDetailsLayout.getInstance().getDatasourceListGrid().hide();

        } else {

            // update user list selection
            GroupListLayout.getInstance().updateUserSelection(data);
            // update dashboard list selection
            GroupListLayout.getInstance().updateDashboardSelection(data);
            // update datasource list selection
            GroupListLayout.getInstance().updateDatasourceSelection(data);

            // Hide Datasource Admin Panes
            GroupDetailsLayout.getInstance().getDatasourceAdminUserListGrid().hide();
            GroupDetailsLayout.getInstance().getDatasourceAdminDashboardListGrid().hide();
            GroupDetailsLayout.getInstance().getDatasourceAdminDatasourceListGrid().hide();
        }


        GroupDetailsLayout.getInstance().getHeaderLayout().getMember('AccountGroupDetailsHeaderLabel').setContents(
            '<h2>Group Management : ' + Group.getInstance().getName() + '</h2>'
        );

        GroupDetailsLayout.getInstance().getGroupDetailsForm().setValue("groupname",Group.getInstance().getName());
        GroupDetailsLayout.getInstance().getGroupDetailsForm().setValue("description",Group.getInstance().getDescription());

        // if user is site admin then let them edit group name
        if ( GovDashboard.user.type == 1 ) {
            GroupDetailsLayout.getInstance().getGroupDetailsForm().show();
            GroupDetailsLayout.getInstance().getHeaderLayout().getMember('AccountGroupDetailsHeaderLabel').hide();
        }else{
            GroupDetailsLayout.getInstance().getGroupDetailsForm().hide();
            GroupDetailsLayout.getInstance().getHeaderLayout().getMember('AccountGroupDetailsHeaderLabel').show();
        }


        var groupDetailsLayout = GroupDetailsLayout.getInstance();

        MessageSection.clearMessages();
        AccountSectionActions.showListLayoutHeader('none');
        //AccountSection.getNavPane().setMembers([groupDetailsLayout.getContainer()]);
        AccountActions.showPane(groupDetailsLayout.getContainer());
    },

    gotoLanding: function() {
        GroupListLayout.getInstance().getGroupTileGrid().invalidateCache();
        var groupListLayout = GroupListLayout.getInstance();
        var groupTileGrid = GroupListLayout.getInstance().getGroupTileGrid();

        MessageSection.clearMessages();
        groupListLayout.resetSearchForm();
        groupListLayout.resetSortForm();
        groupTileGrid.invalidateCache();
        groupTileGrid.fetchData();
        groupTileGrid.data.sortByProperty(); // sort by group name. this is working, not sure how
        GroupDetailsActions.resetNav();
        AccountSectionActions.showListLayoutHeader('groups');
        //AccountSection.getNavPane().setMembers([GroupListLayout.getInstance().getGroupTileGrid()]);
        AccountActions.showPane(GroupListLayout.getInstance().getGroupTileGrid());
    },

    clickAddNewGroup: function(){
        var groupAddNewLayout = GroupAddNewLayout.getInstance();

        MessageSection.clearMessages();
        NewGroupForm.getAddNewGroupForm().clearValues();
        AccountSectionActions.showListLayoutHeader('none');
        //AccountSection.getNavPane().setMembers([groupAddNewLayout.getContainer()]);
        AccountActions.showPane(groupAddNewLayout.getContainer());
    }
});