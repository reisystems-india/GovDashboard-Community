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


var UserDetailsLayout = isc.defineClass('UserDetailsLayout');

UserDetailsLayout = isc.UserDetailsLayout.addClassProperties({
	_instance: null,
    getInstance: function() {
    	if ( !this._instance ) {
    		this._instance = UserDetailsLayout.create();
    	}
    	return this._instance;
	}
});

UserDetailsLayout = UserDetailsLayout.addProperties({
    getContainer: function() {
        if ( typeof this.container == 'undefined') {
            this.container = isc.VLayout.create({
                ID: 'UserDetailsLayoutContainer',
                width: '100%',
                height: '100%',
                overflow: 'visible',
                layoutTopMargin: 0,
                backgroundColor: '#FFF',
                members: [
                    this.getHeaderLayout(),
                    isc.HLayout.create({
                        layoutTopMargin: 8,
                        members: [
                            this.getUserNavButtonLayout(),
                            this.getUserDetailsPane()
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
                ID: 'AccountUserDetailsHeader',
                width: '100%',
                height: 50,
                membersMargin: 10,
                defaultLayoutAlign: 'right',
                styleName: 'reportHeader',
                layoutMargin: 0,
                layoutBottomMargin: 10,
                members: [
                    isc.Label.create({
                        ID: 'AccountUserDetailsHeaderLabel',
                        contents: '',
                        width: '100%',
                        height: 10,
                        autoDraw: false,
                        styleName: 'accountHeader'
                    }),
                    this.getUserDetailsForm(),
                    isc.Button.create({
                        ID: 'AccountUserDeactivateButton',
                        title: 'DeActivate User',
                        width: 130,
                        showIf:"GovDashboard.user.type == 1",
                        click: function() {
                            UserDetailsActions.deactivateUser();
                        }
                    }),
                    isc.Button.create({
                        ID: 'AccountUserActivateButton',
                        title: 'Activate User',
                        width: 120,
                        visibility:"hidden",
                        showIf:"GovDashboard.user.type == 1",
                        click: function() {
                            UserDetailsActions.activateUser();
                        }
                    }),
                    isc.Button.create({
                        ID: 'AccountUserCancelButton',
                        title: 'Cancel',
                        width: 100,
                        click: function() {
                            AccountSection.clickNav(
                                function() {
                                    UserDetailsActions.cancelUser();
                                }
                            );
                        }
                    }),
                    isc.Button.create({
                        ID: 'AccountUserSaveButton',
                        title: 'Save',
                        width: 100,
                        click: function() {
                            UserDetailsActions.saveUser();
                        }
                    })
                ]
            });
        }
        return this.headerLayout;
    },

    getUserNavButtonLayout: function() {
        if ( typeof this.userNavButtonLayout == 'undefined' )
        {
            this.userNavButtonLayout = isc.VLayout.create({
                ID: 'UserDetailsNavButtonLayout',
                width: 120,
                height: '*',
                layoutMargin: 0,
                membersMargin: 10,
                members: [
                    isc.IButton.create({
                        ID: 'UserGroupsButton',
                        baseStyle: "vertTabActive",
                        title: 'Groups',
                        width: 120,
                        height: 40,
                        click: function() {
                            UserDetailsActions.clickUserDetailsNavButton('groups');
                        }
                    }),
                    isc.IButton.create({
                        ID: 'UserDashboardsButton',
                        baseStyle: "vertTab",
                        title: 'Dashboards',
                        width: 120,
                        height: 40,
                        click: function() {
                            UserDetailsActions.clickUserDetailsNavButton('dashboards');
                        }
                    }),
                    isc.IButton.create({
                        ID: 'UserDatasourcesButton',
                        baseStyle: "vertTab",
                        title: 'Topics',
                        width: 120,
                        height: 40,
                        click: function() {
                            UserDetailsActions.clickUserDetailsNavButton('datasources');
                        }
                    })
                ]
            });
        }

        return this.userNavButtonLayout;
    },

    getUserDetailsPane: function() {
        if (typeof this.userDetailsPane == 'undefined') {
            this.userDetailsPane = isc.VLayout.create({
                ID: 'AccountUserDetailsPane',
                width: '*',
                height: '100%',
                overflow: 'visible',
                layoutMargin: 4,
                backgroundColor: '#808793',
                members: [
                    this.getGroupPane(),
                    this.getDashboardPane(),
                    this.getDatasourcePane()
                ]
            });
        }

        return this.userDetailsPane;
    },

    getGroupPane: function() {
        if (typeof this.groupPane == 'undefined') {
            this.groupPane = isc.VLayout.create({
                ID: 'AccountUserGroupPane',
                width: '100%',
                height: '100%',
                overflow: 'visible',
                visibility:"hidden",
                layoutMargin: 0,
                membersMargin: 4,
                members: [
                    this.getGroupsListGrid(),
                    this.getDatasourceAdminGroupsListGrid()
                ]
            });
        }

        return this.groupPane;
    },

    getDashboardPane: function() {
        if (typeof this.dashboardPane == 'undefined') {
            this.dashboardPane = isc.VLayout.create({
                ID: 'AccountUserDashboardPane',
                width: '100%',
                height: '100%',
                overflow: 'visible',
                visibility:"hidden",
                layoutMargin: 0,
                membersMargin: 4,
                members: [
                    this.getDashboardFilterLayout(),
                    this.getDashboardsListGrid()
                ]
            });
        }

        return this.dashboardPane;
    },

    getDatasourcePane: function() {
        if (typeof this.datasourcePane == 'undefined') {
            this.datasourcePane = isc.VLayout.create({
                ID: 'AccountUserDatasourcePane',
                width: '100%',
                height: '100%',
                overflow: 'visible',
                visibility:"hidden",
                layoutMargin: 0,
                membersMargin: 4,
                members: [
                    this.getDatasourceListGrid()
                ]
            });
        }

        return this.datasourcePane;
    },

    getUserDetailsForm: function() {
        if ( typeof this.userDetailsForm == 'undefined' ) {
            this.userDetailsForm = isc.DynamicForm.create({
				width: "*",
                showInlineErrors: true,
                numCols: 4,
                wrapCells: false,
                wrapTitle: false,
                colWidths: ["1%", "*", "1%", "*"],
                fields: [
			        {
			    		name: "firstname",
                        cellClassName: "reportNameField",
			    		title: "First Name",
						showTitle: false,
         				type: "text",
                        hint: "First Name",
                        showHintInField: true,
                        required: true,
                        value: User.getInstance().getFirstname(),
                        width: 250,
                        length: 50
			    	},
                    {
                        name: "lastname",
                        cellClassName: "reportNameField",
                        title: "Last Name",
                        showTitle: false,
                        type: "text",
                        hint: "Last Name",
                        showHintInField: true,
                        required: true,
                        value: User.getInstance().getLastname(),
                        width: 250,
                        length: 50,
                        startRow: true
                    },
                    {
			    		name: "email",
                        title: "Email",
                        showTitle: false,
                        type: "staticText",
                        value: User.getInstance().getEmail(),
                        startRow: true
			    	}
			    ]
			});
		}

		return this.userDetailsForm;
    },

    getGroupsListGrid: function() {
        if (typeof this.groupsListGrid == 'undefined') {
            this.groupsListGrid = isc.ListGrid.create({
                ID: 'UserGroupsListGrid',
                styleName: "tableGrid",
                headerAutoFitEvent: 'none',
                leaveScrollbarGap: false,
                width: '100%',
                height: 400,
                dataSource: UserGroupDS.getInstance(),
                initialCriteria:{id: User.getInstance().getId()},
                autoFetchData: true,
                showEmptyMessage: true,
                sortField: 'name',
                sortDirection: 'descending',
                emptyMessage: '<br>This user does not belong to any Groups.',
                selectionAppearance: 'checkbox',
                selectionType: 'simple',
                selectionChanged: function() {
                    Account.logChanges("Groups");
                    User.getInstance().setGroups(this.getSelection());
                },
                fields: [
                    {
                        name:'name',
                        title:'Group Name',
                        type:'text'
                    },
                    {
                        name:'description',
                        title:'Description',
                        type:'text'
                    },
                    {
                        name:'changed',
                        title:'Last Modified',
                        formatCellValue: function (value) {
                            return GD.Util.DateFormat.getUSShortDateTime(GD.Util.DateFormat.parseISO8601(value));
                        }
                    }
                ]
            });
        }

        return this.groupsListGrid;
    },

    getDashboardFilterLayout: function() {
        if (typeof this.dashboardFilterLayout == 'undefined') {
            this.dashboardFilterLayout = isc.HLayout.create({
                layoutTopMargin: 8,
                members: [
                    isc.LayoutSpacer.create(),
                    this.getDashboardFilterForm()
                ]
            });
        }

        return this.dashboardFilterLayout;
    },

    getDashboardFilterForm: function() {
        if (typeof this.dashboardFilterForm == 'undefined') {
            this.dashboardFilterForm = isc.DynamicForm.create({
                ID: 'AccountUserDashboardFilterForm',
                fields: [
                    {
                        name: 'editable',
                        title: 'Filter by',
                        type: 'select',
                        defaultValue: 'all',
                        valueMap: {
                            'all': 'All',
                            '1': 'Editable',
                            '0': 'Read Only'
                        }
                    }
                ],
                itemChanged: function(item, newValue, oldValue) {
                    if ( newValue == 1 ) {
                        var dashboards = [];
                        jQuery.each(User.getInstance().getDashboards(),function(key,value){
                            if ( value.editable == 1 ) {
                                dashboards.push(value);
                            }
                        });
                        UserDetailsLayout.getInstance().getDashboardsListGrid().setData(dashboards);
                    } else if ( newValue == 0 ) {
                        var dashboards = [];
                        jQuery.each(User.getInstance().getDashboards(),function(key,value){
                            if ( value.editable == 0 ) {
                                dashboards.push(value);
                            }
                        });
                        UserDetailsLayout.getInstance().getDashboardsListGrid().setData(dashboards);
                    } else {
                        UserDetailsLayout.getInstance().getDashboardsListGrid().setData(User.getInstance().getDashboards());
                    }
                }
            });
        }

        return this.dashboardFilterForm;
    },

    resetDashboardFilterForm: function() {
        this.getDashboardFilterForm().reset();
    },

    getDashboardsListGrid: function() {
        if (typeof this.dashboardsListGrid == 'undefined') {
            this.dashboardsListGrid = isc.ListGrid.create({
                ID: 'UserDashboardsListGrid',
                styleName: "tableGrid",
                headerAutoFitEvent: 'none',
                leaveScrollbarGap: false,
                width: '100%',
                height: 400,
                sortField: 'name',
                sortDirection:'descending',
                showEmptyMessage: true,
                emptyMessage: '<br>This user does not have access to any Dashboards.',
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
                        name:'name', title:'Dashboard Name', width:'40%'
                    },
                    {
                        name:'datasourcePublicName',
                        title:'Topic',
                        width:'25%'
                    },
                    {
                        name:'changed',
                        title:'Last Modified',
                        width:'25%',
                        formatCellValue: function (value) {
                            return GD.Util.DateFormat.getUSShortDateTime(GD.Util.DateFormat.parseISO8601(value));
                        }
                    }
                ]
            });
        }

        return this.dashboardsListGrid;
    },

    getDatasourceListGrid: function() {
        if (typeof this.datasourceListGrid == 'undefined') {
            this.datasourceListGrid = isc.ListGrid.create({
                ID: 'UserDatasourcesListGrid',
                styleName: "tableGrid",
                headerAutoFitEvent: 'none',
                leaveScrollbarGap: false,
                width: '100%',
                height: 400,
                sortField: 'publicName',
                sortDirection: 'descending',
                datetimeFormatter: 'toUSShortDateTime',
                showEmptyMessage: true,
                emptyMessage: '<br>This user does not have access to any Topics.',
                fields: [
                    {name:'publicName', title:'Topic Name', width:'40%'},
                    {name:'description', title:'Description', width:'25%'},
                    {
                        name:'changed',
                        title:'Last Modified',
                        width:'25%',
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

    getDatasourceAdminGroupsListGrid: function() {
        if (typeof this.datasourceAdminGroupsListGrid == 'undefined') {
            this.datasourceAdminGroupsListGrid = isc.ListGrid.create({
                ID: 'datasourceAdminUserGroupsListGrid',
                styleName: "tableGrid",
                headerAutoFitEvent: 'none',
                leaveScrollbarGap: false,
                width: '100%',
                height: 400,
                dataSource: UserGroupDS.getInstance(),
                autoFetchData: false,
                showEmptyMessage: true,
                sortField: 'name',
                sortDirection: 'descending',
                emptyMessage: '<br>This user does not belong to any Groups.',
                selectionAppearance: 'none',
                selectionType: 'none',
                fields: [
                    {name:'name', title:'Group Name', type:'text'},
                    {name:'description', title:'Description', type:'text'},
                    {
                        name:'changed',
                        title:'Last Modified',
                        formatCellValue: function (value) {
                            return GD.Util.DateFormat.getUSShortDateTime(GD.Util.DateFormat.parseISO8601(value));
                        }
                    }
                ]
            });
        }

        return this.datasourceAdminGroupsListGrid;
    }

});
