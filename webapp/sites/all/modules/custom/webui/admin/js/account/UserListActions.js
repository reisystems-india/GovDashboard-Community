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


var UserListActions = isc.defineClass('UserListActions');

UserListActions = UserListActions.addClassProperties({
    clickUserTile: function(data) {

        // load user object
        User.getInstance().load(data[0]);

        // permissions
        // filter out unselected rows for data-mart admin roles.
        if ( GovDashboard.user.type != 1 ) {
            // Update group list
            UserDetailsLayout.getInstance().getDatasourceAdminGroupsListGrid().invalidateCache();
            // filter out results based on datasourceadmin = 0 (case: 'dashboard viewer')
            UserDetailsLayout.getInstance().getDatasourceAdminGroupsListGrid().fetchData({id: User.getInstance().getId(),admin_only:1});
            // Hide Admin Panes
            UserDetailsLayout.getInstance().getGroupsListGrid().hide();

        } else {
            //update group list selection
            UserListLayout.getInstance().updateGroupSelection(data);
            // Hide DatasourceAdmin Panes
            UserDetailsLayout.getInstance().getDatasourceAdminGroupsListGrid().hide();
        }

        // dashboards
        var dashboardListGrid = UserDetailsLayout.getInstance().getDashboardsListGrid();
        dashboardListGrid.setData(User.getInstance().getDashboards());

        // datasources
        var datasourceListGrid = UserDetailsLayout.getInstance().getDatasourceListGrid();
        datasourceListGrid.setData(User.getInstance().getDatasources());

        UserDetailsLayout.getInstance().getHeaderLayout().getMember('AccountUserDetailsHeaderLabel').setContents(
            '<h2 style="padding:0;">'
                + '<div>' + User.getInstance().getFullname() + '</div>'
                + '</h2>'
                + '<h3 style="clear:both;">' + User.getInstance().getEmail() + '</h3>'
        );

        //i f user is site admin or if trying to edit self
        if ( GovDashboard.user.type == 1 || GovDashboard.user.id == User.getInstance().getId() ){
            UserDetailsLayout.getInstance().getUserDetailsForm().show();
            UserDetailsLayout.getInstance().getHeaderLayout().getMember('AccountUserDetailsHeaderLabel').hide();
        } else {
            UserDetailsLayout.getInstance().getHeaderLayout().getMember('AccountUserDetailsHeaderLabel').show();
            UserDetailsLayout.getInstance().getUserDetailsForm().hide();
        }

        UserDetailsLayout.getInstance().getUserDetailsForm().setValue("firstname",User.getInstance().getFirstname());
        UserDetailsLayout.getInstance().getUserDetailsForm().setValue("lastname",User.getInstance().getLastname());
        UserDetailsLayout.getInstance().getUserDetailsForm().setValue("email",'<h3 style="clear:both;">'+User.getInstance().getEmail()+'</h3>');

        if ( User.getInstance().getStatus() == 1 ) {
            isc.Canvas.getById('AccountUserActivateButton').hide();
            isc.Canvas.getById('AccountUserDeactivateButton').show();
        } else {
            isc.Canvas.getById('AccountUserActivateButton').show();
            isc.Canvas.getById('AccountUserDeactivateButton').hide();
        }

        var userDetailsLayout = UserDetailsLayout.getInstance();
        UserDetailsActions.clickUserDetailsNavButton('groups');
        MessageSection.clearMessages();
        AccountSectionActions.showListLayoutHeader('none');
        //AccountSection.getNavPane().setMembers([userDetailsLayout.getContainer()]);
        AccountActions.showPane(userDetailsLayout.getContainer());
    },

    gotoLanding: function() {
        var userListLayout = UserListLayout.getInstance();
        var userTileGrid = UserListLayout.getInstance().getUserTileGrid();

        MessageSection.clearMessages();
        userListLayout.resetSearchForm();
        userListLayout.resetSortForm();
        userTileGrid.fetchData();
        userTileGrid.data.sortByProperty('firstname', true);
        userTileGrid.deselectAllRecords();
        //UserDetailsActions.resetNav();
        AccountSectionActions.showListLayoutHeader('users');
        //AccountSection.getNavPane().setMembers([UserListLayout.getInstance().getUserTileGrid()]);
        AccountActions.showPane(userTileGrid);
    },

    showActiveUsers: function() {
        AccountSection.clickNav(
            function() {
                AccountSectionActions.clickAccountNavButton('users');
                UserListLayout.getInstance().getUserSearchForm().setValue('status', '1');

                setTimeout(function(){
                    UserListActions.applySearchCriteria();
                }, 100);
            }
        );
    },

    applySearchCriteria: function() {
        var criteria = UserListLayout.getInstance().getUserSearchForm().getValuesAsCriteria();

        if (criteria != null) {
            for (var i=0, criteriaCount=criteria.criteria.length; i<criteriaCount; i++) {
                if (criteria.criteria[i].operator == 'inSet') {
                    if (criteria.criteria[i].value == 'all') {
                        criteria.criteria[i].value = ['0', '1'];
                    }
                }
            }
        }
        else {
            criteria = null;
        }

        UserListLayout.getInstance().getUserTileGrid().fetchData(criteria);
    },

    clickAddNewUser: function() {
        var userAddNewLayout = UserAddNewLayout.getInstance();

        MessageSection.clearMessages();
        if (!GD.NewUserForm) {
            var interval = setInterval(function() {
                if (NewUserForm.getAddNewUserForm().isDrawn()) {
                    clearInterval(interval);
                    GD.NewUserForm = new GD.AccountUserForm(null, $('#newUserForm'), {});
                    GD.NewUserForm.render();
                }
            }, 500);

        } else {
            GD.NewUserForm.clear();
        }
//        NewUserForm.getAddNewUserForm().clearValues();
        AccountSectionActions.showListLayoutHeader('none');
        //AccountSection.getNavPane().setMembers([userAddNewLayout.getContainer()]);
        AccountActions.showPane(userAddNewLayout.getContainer());
    }
});