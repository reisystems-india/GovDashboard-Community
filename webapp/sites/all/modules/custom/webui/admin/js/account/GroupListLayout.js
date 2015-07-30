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


var GroupListLayout = isc.defineClass('GroupListLayout');

GroupListLayout = GroupListLayout.addClassProperties({
	_instance: null,

    getInstance: function() {
    	if ( !this._instance ) {
    		this._instance = GroupListLayout.create();
    	}
    	return this._instance;
	}
});

GroupListLayout = GroupListLayout.addProperties({
    getPaneHeader: function() {
        if ( typeof this.paneHeader == 'undefined') {
            this.paneHeader = isc.VLayout.create({
                width: '100%',
                height: '100%',
                overflow: 'visible',
                layoutTopMargin: 0,
                members: [
                    this.getHeaderLayout(),
                    this.getSearchSortFormLayout()
                ]
            });
        }

        return this.paneHeader;
    },

    getHeaderLayout: function () {
        if ( typeof this.headerLayout == 'undefined' ){
            this.headerLayout = isc.HLayout.create({
                ID: 'AccountGroupListHeaderLayout',
                width: '100%',
                height: 60,
                membersMargin: 10,
                defaultLayoutAlign: 'right',
                styleName: 'reportHeader',
                layoutMargin: 0,
                layoutRightMargin: 12,
                layoutBottomMargin: 10,
                layoutTopMargin: 5,
                members: [
                    isc.Label.create({
                        contents: '<h2>Group Management</h2>',
                        width: '*',
                        height: 10,
                        autoDraw: false,
                        styleName: 'accountHeader'
                    }),
                    isc.Layout.create({
                        ID: 'AccountAddNewGroupButton',
                        styleName: 'fancyCpButton',
                        contents: '<span>Add New Group</span>',
                        width: 140,
                        showIf:"GovDashboard.user.type == 1",
                        click: function() {
                            GroupListActions.clickAddNewGroup();
                        }
                    })
                ]
            });
        }
        return this.headerLayout;
    },

    getSearchSortFormLayout: function() {
        if (typeof this.searchSortLayout == 'undefined') {
            this.searchSortLayout = isc.HLayout.create({
                layoutTopMargin: 8,
                members: [
                    this.getGroupSearchForm(),
                    isc.LayoutSpacer.create(),
                    this.getGroupSortForm()
                ]
            });
        }

        return this.searchSortLayout;
    },

    getGroupSearchForm: function() {
        if (typeof this.userSearchForm == 'undefined') {
            this.userSearchForm = isc.DynamicForm.create({
                ID: 'AccountGroupSearchForm',
                dataSource : GroupDS.getInstance(),
                fields: [
                    {
                        name: 'name',
                        showTitle: false,
                        operator: 'iContains',
                        hint: 'Search Group Names',
                        width: 200,
                        showHintInField: true,
                        cellClassName: 'userSearchField'
                    }
                ],
                itemChanged: function() {
                    var criteria = this.getValuesAsCriteria();
                    GroupListLayout.getInstance().getGroupTileGrid().fetchData(criteria);
                }
            });
        }

        return this.userSearchForm;
    },

    resetSearchForm: function() {
        this.getGroupSearchForm().reset();
    },

    getGroupSortForm: function() {
        if (typeof this.userSortForm == 'undefined') {
            this.userSortForm = isc.DynamicForm.create({
                ID: 'AccountGroupSortForm',
                wrapItemTitles: false,
                fields: [
                    {
                        name: 'sortBy',
                        title: 'Sort by',
                        type: 'select',
                        defaultToFirstOption: true,
                        valueMap: {
                            'name': 'Group Name',
                            'changed': 'Last Modified'
                        }
                    }
                ],
                itemChanged: function(item, newValue, oldValue) {
                    var sortVal = this.getValue('sortBy');
                    if ( sortVal ) {
                        switch ( sortVal ) {
                            case 'name':
                                GroupListLayout.getInstance().getGroupTileGrid().data.sortByProperty(sortVal, true);
                                break;
                            case 'changed':
                                GroupListLayout.getInstance().getGroupTileGrid().data.sortByProperty(sortVal, false);
                                break;
                            default:
                                GroupListLayout.getInstance().getGroupTileGrid().data.sortByProperty(sortVal, true);
                        }
                    }
                }
            });
        }

        return this.userSortForm;
    },

    resetSortForm: function() {
        this.getGroupSortForm().reset();
    },

    //update selected users
    updateUserSelection: function(data){
        //invalidate user list
        GroupDetailsLayout.getInstance().getUserListGrid().invalidateCache();

        // select current users
        GroupDetailsLayout.getInstance().getUserListGrid().dataArrived = function (startRow, endRow) {

            for(var index=0; index < data[0].users.length; index++){
                var record = null;
                for (var i = startRow; i <= endRow; i++) {
                    record = GroupDetailsLayout.getInstance().getUserListGrid().getRecord(i);
                    if (record) {
                        var user_id = data[0].users[index].id;
                        if (user_id == record.id) {
                            GroupDetailsLayout.getInstance().getUserListGrid().selectRecord(record);
                            break;
                        }
                    }
                }
            }

            //Clear change log for current selection
            Account.clearChangeLog();

            return true;
        };
    },

    //update selected dashboards
    updateDashboardSelection: function (data) {
        // invalidate dashboards list
        GroupDetailsLayout.getInstance().getDashboardListGrid().invalidateCache();

        // select current dashboards
        GroupDetailsLayout.getInstance().getDashboardListGrid().fetchData();
        GroupDetailsLayout.getInstance().getDashboardListGrid().dataArrived = function (startRow, endRow) {

            for(var index=0; index < data[0].dashboards.length; index++){
                var record = null;
                for (var i = startRow; i <= endRow; i++) {
                    record = GroupDetailsLayout.getInstance().getDashboardListGrid().getRecord(i);
                    if ( record ) {
                        var dashboard = data[0].dashboards[index];
                        if (dashboard.id == record.id) {
                            if ( Group.getInstance().findDatasource(dashboard.datasource.name) ) {
                                record.editable = 1;
                                record.canSelect = false;
                            } else {
                                GroupDetailsLayout.getInstance().getDashboardListGrid().selectRecord(record);
                            }
                            break;
                        }
                    }
                }
            }

            // Clear change log for current selection
            GroupDetailsLayout.getInstance().getDashboardListGrid().markForRedraw();
            Account.clearChangeLog();

            return true;
        };
    },

    //update selected dashboards AdminDatasource user
    updateDatasourceAdminDashboardSelection: function (data,criteria) {

        // invalidate dashboards list
        GroupDetailsLayout.getInstance().getDatasourceAdminDashboardListGrid().invalidateCache();

        // select current dashboards
        GroupDetailsLayout.getInstance().getDatasourceAdminDashboardListGrid().fetchData(criteria);
        GroupDetailsLayout.getInstance().getDatasourceAdminDashboardListGrid().dataArrived = function (startRow, endRow) {
            for(var index=0; index < data[0].dashboards.length; index++){
                var record = null;
                for (var i = startRow; i <= endRow; i++) {
                    record = GroupDetailsLayout.getInstance().getDatasourceAdminDashboardListGrid().getRecord(i);
                    if (record) {
                        var dashboard = data[0].dashboards[index];
                        if ( dashboard.id == record.id ) {
                            if ( Group.getInstance().findDatasource(dashboard.datasource.name) ) {
                                record.editable = 1;
                                record.canSelect = false;
                            } else if ( Group.getInstance().isViewOnlyDashboard(dashboard.id) ) {
                                record.canSelect = false;
                            } else {
                                GroupDetailsLayout.getInstance().getDatasourceAdminDashboardListGrid().selectRecord(record);
                            }
                            break;
                        }
                    }
                }
            }
            //Clear change log for current selection
            Account.clearChangeLog();
            GroupDetailsLayout.getInstance().getDatasourceAdminDashboardListGrid().markForRedraw();

            return true;
        };
    },

    //update selected datasources
    updateDatasourceSelection: function(data){
        //invalidate datasource list
        GroupDetailsLayout.getInstance().getDatasourceListGrid().invalidateCache();

        // select current datasources
        GroupDetailsLayout.getInstance().getDatasourceListGrid().fetchData();
        GroupDetailsLayout.getInstance().getDatasourceListGrid().dataArrived = function (startRow, endRow) {

            for (var index = 0; index < data[0].datasources.length; index++ ){
                var record = null;
                for (var i = startRow; i <= endRow; i++) {
                    record = GroupDetailsLayout.getInstance().getDatasourceListGrid().getRecord(i);
                    if (record) {
                        var datasourceName = data[0].datasources[index].name;
                        if ( datasourceName == record.name ) {
                            GroupDetailsLayout.getInstance().getDatasourceListGrid().selectRecord(record);
                            break;
                        }
                    }
                }
            }

            //Clear change log for current selection
            Account.clearChangeLog();

            return true;
        };
    },

    getGroupTileGrid: function() {
        if (typeof this.userTileGrid == 'undefined') {
            this.userTileGrid = isc.TileGrid.create({
                ID: 'AccountGroupTileGrid',
                autoDraw: false,
                tileHeight: 60,
                tileWidth: 225,
                height: '100%',
                width: '100%',
                tileValueAlign: 'left',
                styleName: 'groupTiles',
                autoFetchData: false,
                showAllRecords: true,
                animateTileChange: false,
                dataSource: GroupListDS.getInstance(),
                recordClick: function (viewer, tile, record) {
                    GroupDS.getInstance().fetchData({id: record.id}, function(dsResponse, data) {
                        GroupListActions.clickGroupTile(data);
                    });
                },
                fields: [
                    {name: 'name'},
                    {name: 'description'}
                ],

                getTileHTML : function (record) {

                    var html = [
                        '<div class="groupTile" style="background-position:left center;padding:0; margin:0; width:225px; height:50px; cursor:pointer;">',
                                '<span style="margin-left:50px;font-weight:bold;">'+record.name+'</span><br/>',
                                '<p style="margin-left:55px;padding-right:5px;font-size:80%;line-height:80%;">'+record.description+'</p>',
                        '</div>'
                    ];

                    return html.join("");
                },

                dataArrived: function() {
                    this.data.sortByProperty('name', true);
                }
            })
        }
        return this.userTileGrid;
    }
});
