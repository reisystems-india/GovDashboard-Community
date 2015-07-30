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


var UserDetailsActions = isc.defineClass('UserDetailsActions');

UserDetailsActions = UserDetailsActions.addClassProperties({
    clickUserDetailsNavButton: function(option) {
        var userDetailsLayout = UserDetailsLayout.getInstance();
        var buttonIds = ["UserGroupsButton", "UserDashboardsButton", "UserDatasourcesButton"];
        MessageSection.clearMessages();
        switch (option) {
            case 'groups':
                AccountSection.resetAllNavigationButtons(buttonIds,"vertical");
                if(isc.Canvas.getById('UserGroupsButton')){
                    isc.Canvas.getById('UserGroupsButton').setBaseStyle("vertTabActive");
                }
                userDetailsLayout.getGroupPane().show();
                userDetailsLayout.getDashboardPane().hide();
                userDetailsLayout.getDatasourcePane().hide();
                break;

            case 'dashboards':
                userDetailsLayout.resetDashboardFilterForm();
                AccountSection.resetAllNavigationButtons(buttonIds,"vertical");
                if(isc.Canvas.getById('UserDashboardsButton')){
                    isc.Canvas.getById('UserDashboardsButton').setBaseStyle("vertTabActive");
                }
                userDetailsLayout.getGroupPane().hide();
                userDetailsLayout.getDashboardPane().show();
                userDetailsLayout.getDatasourcePane().hide();
                break;

            case 'datasources':
                AccountSection.resetAllNavigationButtons(buttonIds,"vertical");
                if(isc.Canvas.getById('UserDatasourcesButton')){
                    isc.Canvas.getById('UserDatasourcesButton').setBaseStyle("vertTabActive");
                }
                userDetailsLayout.getGroupPane().hide();
                userDetailsLayout.getDashboardPane().hide();
                userDetailsLayout.getDatasourcePane().show();
                break;
        }
    },

    resetNav: function() {
        this.clickUserDetailsNavButton('groups');
    },

    activateUser: function(){
        MessageSection.clearMessages();
        isc.confirm(
            "Are you sure you want to Activate the user \"" + User.getInstance().getFullname() + "\"?",
            function(answer){
                if(answer) {
                    User.getInstance().setStatus(1);
                    UserDS.getInstance().updateData(User.getInstance().getSimpleUserObject(),
                        function(dsResponse, data) {
                            isc.Canvas.getById('AccountUserActivateButton').setVisibility(false);
                            isc.Canvas.getById('AccountUserDeactivateButton').setVisibility(true);
                            MessageSection.showMessage(["User Successfully Activated"]);
                        }
                    );
                }
            },
            {toolbarButtons:[
                isc.Dialog.YES,
                isc.Dialog.CANCEL
            ],
            title: "Activate User",
            canDragReposition: false,
            showModalMask: true}
        );
    },

    deactivateUser: function(){
        MessageSection.clearMessages();
        isc.confirm(
            "Are you sure you want to DeActivate the user \"" + User.getInstance().getFullname() + "\"?",
            function(answer){
                if(answer) {
                    User.getInstance().setStatus(0);
                    UserDS.getInstance().updateData(User.getInstance().getSimpleUserObject(),
                        function(dsResponse, data) {
                            if ( !dsResponse.errors ) {
                                isc.Canvas.getById('AccountUserActivateButton').setVisibility(true);
                                isc.Canvas.getById('AccountUserDeactivateButton').setVisibility(false);
                                MessageSection.showMessage(["User Successfully De-Activated"]);
                            }
                        }
                    );
                }
            },
            {toolbarButtons:[
                isc.Dialog.YES,
                isc.Dialog.CANCEL
            ],
            title: "DeActivate User",
            canDragReposition: false,
            showModalMask: true}
        );
    },

    saveUser: function(){
        MessageSection.clearMessages();
        UserDetailsLayout.getInstance().getUserDetailsForm().validate();
        var firstname = UserDetailsLayout.getInstance().getUserDetailsForm().getValue('firstname');
        var lastname = UserDetailsLayout.getInstance().getUserDetailsForm().getValue('lastname');
        var email = UserDetailsLayout.getInstance().getUserDetailsForm().getValue('email');
        if ( firstname != null && lastname != null && email != null ) {
            User.getInstance().setFirstname(firstname);
            User.getInstance().setLastname(lastname);
            User.getInstance().setEmail(email);
            UserDS.getInstance().updateData(User.getInstance().getSimpleUserObject(),
                function(dsResponse, data) {
                    if ( !dsResponse.errors ) {
                        MessageSection.showMessage(["User Successfully Updated"]);

                        User.getInstance().setDashboards(data[0].dashboards);
                        User.getInstance().setDatasources(data[0].datasources);

                        // dashboards
                        var dashboardListGrid = UserDetailsLayout.getInstance().getDashboardsListGrid();
                        dashboardListGrid.setData(User.getInstance().getDashboards());

                        // datasources
                        var datasourceListGrid = UserDetailsLayout.getInstance().getDatasourceListGrid();
                        datasourceListGrid.setData(User.getInstance().getDatasources());

                        // Clear change log for current selection
                        Account.clearChangeLog();
                        window.top.scroll(0, 0);
                    }
                }
            );
        }
    },

    cancelUser: function(){
        AccountSectionActions.clickAccountNavButton('users');
    }
});
