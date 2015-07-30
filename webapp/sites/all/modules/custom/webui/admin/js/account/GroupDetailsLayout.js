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

var GroupDetailsLayout = isc.defineClass('GroupDetailsLayout');

GroupDetailsLayout = GroupDetailsLayout.addClassProperties({

    _instance: null,

    getInstance: function() {
    	if ( !this._instance ) {
    		this._instance = GroupDetailsLayout.create();
    	}
    	return this._instance;
	}
});

GroupDetailsLayout = GroupDetailsLayout.addProperties({
    getContainer: function() {
        if ( typeof this.container == 'undefined') {
            this.container = isc.VLayout.create({
                ID: 'GroupDetailsLayoutContainer',
                width: '100%',
                height: '100%',
                overflow: 'visible',
                layoutMargin: 0,
                layoutTopMargin: 10,
                backgroundColor: '#FFF',
                members: [
                    this.getHeaderLayout(),
                    isc.HLayout.create({
                        layoutTopMargin: 8,
                        members: [
                            this.getGroupNavButtonLayout(),
                            this.getGroupDetailsPane()
                        ]
                    })
                ]
            });
        }

        return this.container;
    },

    getHeaderLayout: function () {
        if ( typeof this.headerLayout == 'undefined' )
        {
            this.headerLayout = isc.HLayout.create({
                ID: 'AccountGroupDetailsHeader',
                width: '100%',
                height: 50,
                membersMargin: 10,
                defaultLayoutAlign: 'right',
                styleName: 'reportHeader',
                layoutMargin: 0,
                layoutBottomMargin: 10,
                members: [
                    isc.Label.create({
                        ID: 'AccountGroupDetailsHeaderLabel',
                        contents: '',
                        width: '100%',
                        height: 10,
                        autoDraw: false,
                        styleName: 'accountHeader'
                    }),
                    this.getGroupDetailsForm(),
                    isc.Button.create({
                        ID: 'AccountGroupDeleteButton',
                        title: 'Delete Group',
                        width: 130,
                        showIf:"GovDashboard.user.type == 1",
                        click: function() {
                            GroupDetailsActions.clickDeleteGroup();
                        }
                    }),
                    isc.Button.create({
                        ID: 'AccountGroupCancelButton',
                        title: 'Cancel',
                        width: 100,
                        click: function() {
                            AccountSection.clickNav(
                                function() {
                                    GroupDetailsActions.cancelGroup();
                                }
                            );
                        }
                    }),
                    isc.Button.create({
                        ID: 'AccountGroupSaveButton',
                        title: 'Save',
                        width: 100,
                        click: function() {
                            GroupDetailsActions.saveGroup();
                        }
                    })
                ]
            });
        }
        return this.headerLayout;
    },

    getGroupNavButtonLayout: function() {
        if ( typeof this.groupNavButtonLayout == 'undefined' )
        {
            this.groupNavButtonLayout = isc.VLayout.create({
                ID: 'GroupDetailsNavButtonLayout',
                width: 120,
                height: '*',
                layoutMargin: 0,
                membersMargin: 10,
                members: [
                    isc.IButton.create({
                        ID: 'GroupUsersButton',
                        baseStyle: "vertTabActive",
                        title: 'Users',
                        width: 120,
                        height: 40,
                        click: function() {
                            isc.GroupDetailsActions.clickGroupDetailsNavButton('users');
                        }
                    }),
                    isc.IButton.create({
                        ID: 'GroupDashboardsButton',
                        baseStyle: "vertTab",
                        title: 'Dashboards',
                        width: 120,
                        height: 40,
                        click: function() {
                            isc.GroupDetailsActions.clickGroupDetailsNavButton('dashboards');
                        }
                    }),
                    isc.IButton.create({
                        ID: 'GroupDatasourcesButton',
                        baseStyle: "vertTab",
                        title: 'Topics',
                        width: 120,
                        height: 40,
                        click: function() {
                            isc.GroupDetailsActions.clickGroupDetailsNavButton('datasources');
                        }
                    })
                ]
            });
        }

        return this.groupNavButtonLayout;
    },

    getGroupDetailsPane: function() {
        if (typeof this.groupDetailsPane == 'undefined') {
            this.groupDetailsPane = isc.VLayout.create({
                ID: 'AccountGroupDetailsPane',
                width: '*',
                height: '100%',
                overflow: 'visible',
                layoutMargin: 4,
                backgroundColor: '#808793',
                members: [
                    this.getUserPane()
                ]
            });
        }

        return this.groupDetailsPane;
    },

    getUserPane: function() {
        if (typeof this.userPane == 'undefined') {
            this.userPane = isc.VLayout.create({
                ID: 'AccountGroupUserPane',
                width: '100%',
                height: '100%',
                overflow: 'visible',
                layoutMargin: 0,
                membersMargin: 4,
                members: [
                    this.getUserListGrid(),
                    this.getDatasourceAdminUserListGrid()
                ]
            });
        }

        return this.userPane;
    },

    getDashboardPane: function() {
        if (typeof this.dashboardPane == 'undefined') {
            this.dashboardPane = isc.VLayout.create({
                ID: 'AccountGroupDashboardPane',
                width: '100%',
                height: '100%',
                overflow: 'visible',
                layoutMargin: 0,
                membersMargin: 4,
                members: [
                    this.getDashboardListGrid(),
                    this.getDatasourceAdminDashboardListGrid()
                ]
            });
        }

        return this.dashboardPane;
    },

    getDatasourcePane: function() {
        if (typeof this.datasourcePane == 'undefined') {
            this.datasourcePane = isc.VLayout.create({
                ID: 'AccountGroupDatasourcePane',
                width: '100%',
                height: '100%',
                overflow: 'visible',
                layoutMargin: 0,
                membersMargin: 4,
                members: [
                    this.getDatasourceListGrid(),
                    this.getDatasourceAdminDatasourceListGrid()
                ]
            });
        }

        return this.datasourcePane;
    },

    getGroupDetailsForm: function() {

        if ( typeof this.groupDetailsForm == 'undefined' ) {
            this.groupDetailsForm = isc.DynamicForm.create({
                width: "*",
                showInlineErrors: true,
                numCols: 4,
                wrapCells: false,
                wrapTitle: false,
                colWidths: ["1%", "*", "1%", "*"],
                fields: [
                    {
                        name: "groupname",
                        title: "Group Name",
                        showTitle: false,
                        type: "text",
                        required: true,
                        value: Group.getInstance().getName(),
                        hint: "Group Name",
                        showHintInField: true,
                        width: 250,
                        cellHeight: 30
                    },
                    {
                        name: 'description',
                        title: 'Group Description',
                        showTitle: false,
                        type: 'textarea',
                        required: true,
                        value: Group.getInstance().getDescription(),
                        hint: "Group Description",
                        showHintInField: true,
                        width: 250,
                        cellHeight: 60,
                        height: 50,
                        startRow: true
                    }
                ]
            });
        }

        return this.groupDetailsForm;
    },

    //for site-admin
    getUserListGrid: function() {
        if (typeof this.userListGrid == 'undefined') {
            this.userListGrid = isc.ListGrid.create({
                ID: 'GroupUserListGrid',
                styleName: "tableGrid",
                headerAutoFitEvent: 'none',
                leaveScrollbarGap: false,
                height: 400,
                width: '100%',
                showEmptyMessage: true,
                sortField: 'fname',
                sortDirection: 'descending',
                emptyMessage: '<br>This group does not have any Users.',
                selectionAppearance: 'checkbox',
                dataSource: UserDS.getInstance(),
                autoFetchData: true,
                selectionChanged: function() {
                    Account.logChanges("Users");
                    Group.getInstance().setUsers(this.getSelection());
                },
                fields: [
                    {name:'firstname',
                        title:'First Name', type:'text',width: '30%'},
                    {name:'lastname',
                        title:'Last Name',  type:'text',width: '30%'},
                    {name:'email',
                        title:'Email',      type:'text',width: '30%'}
                ]
            });
        }

        return this.userListGrid;
    },

    // for Datasource admin role
    getDatasourceAdminUserListGrid: function() {
        if (typeof this.datasourceAdminUserListGrid == 'undefined') {
            this.datasourceAdminUserListGrid = isc.ListGrid.create({
                ID: 'datasourceAdminUserListGrid',
                styleName: "tableGrid",
                dataSource: GroupUserDS.getInstance(),
                headerAutoFitEvent: 'none',
                leaveScrollbarGap: false,
                width: '100%',
                height: 400,
                sortField: 'name',
                sortDirection:'descending',
                showEmptyMessage: true,
                autoFetchData: true,
                emptyMessage: '<br>This group does not have any Users.',
                fields: [
                    {name:'firstname',
                        title:'First Name', type:'text'},
                    {name:'lastname',
                        title:'Last Name',  type:'text'},
                    {name:'email',
                        title:'Email',      type:'text'}
                ]
            });
        }
        return this.datasourceAdminUserListGrid;
    },

    getDashboardListGrid: function() {
        if (typeof this.dashboardListGrid == 'undefined') {
            this.dashboardListGrid = isc.ListGrid.create({
                ID: 'GroupDashboardListGrid',
                styleName: "tableGrid",
                headerAutoFitEvent: 'none',
                leaveScrollbarGap: false,
                height: 400,
                width: '100%',
                showEmptyMessage: true,
                emptyMessage: '<br>This group has not been assigned any Dashboards.',
                selectionAppearance: 'checkbox',
                selectionType: 'simple',
                dataSource: DashboardDS.getInstance(),
                initialCriteria:{datasource:Datasource.getInstance().getName()},
                autoFetchData: true,
                selectionChanged: function() {
                    Account.logChanges("Dashboards");
                    Group.getInstance().setDashboards(this.getSelection());
                },
                fields: [
                    {
                        name:'editable',
                        title:'&nbsp;',
                        width:'26',
                        align:'right',
                        canFilter: true,
                        formatCellValue:function (value,record) {
                            if ( value == 1 ) {
                                return isc.Canvas.imgHTML('/sites/all/modules/custom/webui/admin/images/edit.png', '24', '24');
                            }
                        }
                    },
                    {
                        name:'name',
                        title:'Dashboard Name',
                        type:'text',
                        width: '35%'
                    },
                    {
                        name:'datasourcePublicName',
                        title:'Topic Name',
                        type:'text',
                        width: '35%'
                    },
                    {
                        name:'changed',
                        title:'Last Modified',
                        formatCellValue: function (value) {
                            return GD.Util.DateFormat.getUSShortDateTime(GD.Util.DateFormat.parseISO8601(value));
                        },
                        width: '15%'
                    }
                ]
            });
        }
        return this.dashboardListGrid;
    },

    //for Datasource admin role
    getDatasourceAdminDashboardListGrid: function() {
        if (typeof this.datasourceAdminDashboardListGrid == 'undefined') {
            this.datasourceAdminDashboardListGrid = isc.ListGrid.create({
                ID: 'GroupDashboardAdminDatasourceListGrid',
                styleName: "tableGrid",
                headerAutoFitEvent: 'none',
                leaveScrollbarGap: false,
                height: 400,
                width: '100%',
                sortField: 'name',
                sortDirection:'descending',
                showEmptyMessage: true,
                emptyMessage: '<br>This group has not been assigned any Dashboards.',
                selectionAppearance: 'checkbox',
                selectionType: 'simple',
                dataSource: DashboardDS.getInstance(),
                initialCriteria:{datasource:Datasource.getInstance().getName()},
                autoFetchData: false,
                selectionChanged: function() {
                    Account.logChanges("Dashboards");
                    var selectedDashboards = this.getSelection();
                    //var existingDashboards = Group.getInstance().getDashboards();
                    var existingDashboards = this.data.localData;
                    var dashboards = [];

                    for ( var i = 0; i <existingDashboards.length; i++) {
                        // only work with those dashboards the User can edit (Datasource Admin)
                        if ( existingDashboards[i] && existingDashboards[i].editable == '1' ) {

                            for ( var j = 0; j < selectedDashboards.length; j++ ) {
                                // was the dashboard selected, if not we will remove from existingDashboards
                                if ( existingDashboards[i].id == selectedDashboards[j].id ) {
                                    dashboards.push(existingDashboards[i]);
                                }
                            }
                        // add back the Group's own dashboards by default
                        } else {
                            dashboards.push(existingDashboards[i]);
                        }
                    }
                    Group.getInstance().setDashboards(dashboards);
                },
                fields: [
                    {name:'editable', title:'&nbsp;', width:'26', align:'right',
                        canFilter: true,
                        formatCellValue:function (value) {
                            if (value == '1') {
                                return isc.Canvas.imgHTML('/sites/all/modules/custom/webui/admin/images/edit.png', '24', '24');
                            }
                        }
                    },
                    {
                        name:'name',
                        title:'Dashboard Name',
                        type:'text',
                        width: '35%'
                    },
                    {
                        name:'datasourcePublicName',
                        title:'Topic Name',
                        type:'text',
                        width: '35%'
                    },
                    {
                        name:'changed',
                        title:'Last Modified',
                        formatCellValue: function (value) {
                            return GD.Util.DateFormat.getUSShortDateTime(GD.Util.DateFormat.parseISO8601(value));
                        },
                        width: '15%'
                    }
                ]
            });
        }
        return this.datasourceAdminDashboardListGrid;
    },

    //for admins
    getDatasourceListGrid: function() {
        if (typeof this.datasourceListGrid == 'undefined') {
            this.datasourceListGrid = isc.ListGrid.create({
                ID: 'GroupDatasourceListGrid',
                styleName: "tableGrid",
                headerAutoFitEvent: 'none',
                leaveScrollbarGap: false,
                height: 400,
                width: '100%',
                sortField: 'publicName',
                sortDirection:' descending',
                showEmptyMessage: true,
                emptyMessage: '<br>This group has not been assigned any Topics.',
                selectionAppearance: 'checkbox',
                selectionType: 'simple',
                dataSource: DatasourceDS.getInstance(),
                autoFetchData: true,
                selectionChanged: function() {
                    Account.logChanges("Topics");
                    Group.getInstance().setDatasources(this.getSelection());
                },
                fields: [
                    {name:'publicName', title:'Topic Name', width:'25%', type:'text'},
                    {name:'description', title:'Description', type:'text',width:'40%'},
                    {
                        name:'changed',
                        title:'Last Modified',
                        width:'15%',
                        formatCellValue: function (value) {
                            if ( value ) {
                                return GD.Util.DateFormat.getUSShortDateTime(GD.Util.DateFormat.parseISO8601(value));
                            }
                        }
                    }
                ]
            });
        }

        return this.datasourceListGrid;
    },

    //for Datasource admin role
    getDatasourceAdminDatasourceListGrid: function() {
        if (typeof this.datasourceAdminDatasourceListGrid == 'undefined') {
            this.datasourceAdminDatasourceListGrid = isc.ListGrid.create({
                ID: 'datasourceAdminDatasourceListGrid',
                dataSource: GroupDatasourceDS.getInstance(),
                headerAutoFitEvent: 'none',
                leaveScrollbarGap: false,
                width: '100%',
                height: 400,
                sortField: 'publicName',
                sortDirection:'descending',
                showEmptyMessage: true,
                autoFetchData: true,
                emptyMessage: '<br>This group does not have access to any Topics.',
                fields: [
                    {name:'publicName', title:'Topic Name', width:'35%', type:'text'},
                    {name:'description', title:'Description', width:'35%', type:'text'},
                    {
                        name:'changed',
                        title:'Last Modified',
                        width:'20%',
                        formatCellValue: function (value) {
                            if ( value ) {
                                return GD.Util.DateFormat.getUSShortDateTime(GD.Util.DateFormat.parseISO8601(value));
                            }
                        }
                    }
                ]
            });
        }
        return this.datasourceAdminDatasourceListGrid;
    }
});
