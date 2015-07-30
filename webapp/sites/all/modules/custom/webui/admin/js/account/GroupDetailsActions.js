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

var GroupDetailsActions = isc.defineClass('GroupDetailsActions');

GroupDetailsActions = GroupDetailsActions.addClassProperties({
    clickGroupDetailsNavButton: function(option) {
        MessageSection.clearMessages();
        var buttonIds = ["GroupUsersButton", "GroupDashboardsButton", "GroupDatasourcesButton"];
        switch (option) {
            case 'users':
                AccountSection.resetAllNavigationButtons(buttonIds,"vertical");
                if(isc.Canvas.getById('GroupUsersButton')){
                    isc.Canvas.getById('GroupUsersButton').setBaseStyle("vertTabActive");
                }
                GroupDetailsLayout.getInstance().getGroupDetailsPane().setMembers([
                    GroupDetailsLayout.getInstance().getUserPane()
                ]);
                break;

            case 'dashboards':
                AccountSection.resetAllNavigationButtons(buttonIds,"vertical");
                if(isc.Canvas.getById('GroupDashboardsButton')){
                    isc.Canvas.getById('GroupDashboardsButton').setBaseStyle("vertTabActive");
                }
                GroupDetailsLayout.getInstance().getGroupDetailsPane().setMembers([
                    GroupDetailsLayout.getInstance().getDashboardPane()
                ]);
                break;

            case 'datasources':
                AccountSection.resetAllNavigationButtons(buttonIds,"vertical");
                if(isc.Canvas.getById('GroupDatasourcesButton')){
                    isc.Canvas.getById('GroupDatasourcesButton').setBaseStyle("vertTabActive");
                }
                GroupDetailsLayout.getInstance().getGroupDetailsPane().setMembers([
                    GroupDetailsLayout.getInstance().getDatasourcePane()
                ]);
                break;
        }
    },

    resetNav: function() {
        this.clickGroupDetailsNavButton('users');
    },

    saveGroup: function() {

        MessageSection.clearMessages();
        GroupDetailsLayout.getInstance().getGroupDetailsForm().validate();
        var groupname = GroupDetailsLayout.getInstance().getGroupDetailsForm().getValue('groupname');
        var description = GroupDetailsLayout.getInstance().getGroupDetailsForm().getValue('description');

        if ( groupname != null && description != null ) {
            Group.getInstance().setName(groupname);
            Group.getInstance().setDescription(description);
            var simpleData = Group.getInstance().getSimpleGroupObject();
            GroupDS.getInstance().updateData(simpleData, function(dsResponse, data) {
                if ( !dsResponse.errors ) {

                    Group.getInstance().load(data[0]);

                    //update dashboard list selection
                    GroupListLayout.getInstance().updateDashboardSelection(data);

                    //update datasource list selection
                    GroupListLayout.getInstance().updateDatasourceSelection(data);

                    MessageSection.showMessage(["Group Successfully Updated"]);

                    //Clear change log for current selection
                    Account.clearChangeLog();
                    window.top.scroll(0, 0);

                } else {
                    //console.log(dsResponse.errors);
                }

            });
        }
    },

    clickDeleteGroup: function () {
        MessageSection.clearMessages();
        isc.confirm(
            "Are you sure you want to Delete the group \"" + Group.getInstance().getName() + "\"? This action cannot be undone.",
            function(answer){
                if(answer) {
                    GroupDS.getInstance().removeData(Group.getInstance().getSimpleGroupObject(),
                        function(dsResponse, data) {
                            AccountSectionActions.clickAccountNavButton('groups');
                            MessageSection.showMessage(["Group Successfully Deleted"]);
                        }
                    );
                }
            },
            {toolbarButtons:[
                isc.Dialog.YES,
                isc.Dialog.CANCEL
            ],
            title: "Delete Group",
            canDragReposition: false,
            showModalMask: true}
        );
    },

    cancelGroup: function(){
        AccountSectionActions.clickAccountNavButton('groups');
    }
});