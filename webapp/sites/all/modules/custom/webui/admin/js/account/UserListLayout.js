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


var UserListLayout = isc.defineClass('UserListLayout');

UserListLayout = isc.UserListLayout.addClassProperties({
	_instance: null,

    getInstance: function() {
    	if ( !this._instance ) {
    		this._instance = UserListLayout.create();
    	}
    	return this._instance;
	}
});

UserListLayout = isc.UserListLayout.addProperties({
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

    getHeaderLayout: function() {
        if ( typeof this.headerLayout == 'undefined' ) {
            this.headerLayout = isc.HLayout.create({
                ID: 'AccountUserListHeader',
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
                        contents: '<h2>User Management</h2>',
                        width: '*',
                        height: 10,
                        autoDraw: false,
                        styleName: 'accountHeader'
                    }),
                    isc.Layout.create({
                        ID: 'newUserButton',
                        styleName: 'fancyCpButton',
                        contents: '<span>Add New User</span>',
                        showIf:"GovDashboard.user.type == 1",
                        width: 130,
                        click: function() {
                            UserListActions.clickAddNewUser();
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
                    this.getUserSearchForm(),
                    isc.LayoutSpacer.create(),
                    this.getUserSortForm()
                ]
            });
        }

        return this.searchSortLayout;
    },

    getUserSearchForm: function() {
        if (typeof this.userSearchForm == 'undefined') {
            this.userSearchForm = isc.DynamicForm.create({
                ID: 'AccountUserSearchForm',
                fields: [
                    {
                        name: 'fullname',
                        showTitle: false,
                        operator: 'iContains',
                        hint: 'Search User Names',
                        width: 200,
                        showHintInField: true,
                        cellClassName: 'userSearchField'
                    },
                    {
                        name: 'status',
                        showTitle: false,
                        type: 'select',
                        operator: 'inSet',
                        defaultValue: 'all',
                        valueMap:{
                            'all': 'All',
                            '1': 'Active',
                            '0': 'Blocked'
                        }
                    }
                ],
                itemChanged: function() {
                    UserListActions.applySearchCriteria();
                }
            });
        }

        return this.userSearchForm;
    },

    resetSearchForm: function() {
        this.getUserSearchForm().reset();
    },

    getUserSortForm: function() {
        if (typeof this.userSortForm == 'undefined') {
            this.userSortForm = isc.DynamicForm.create({
                ID: 'AccountUserSortForm',
                wrapItemTitles: false,
                fields: [
                    {
                        name: 'sortBy',
                        title: 'Sort by',
                        type: 'select',
                        defaultToFirstOption: true,
                        valueMap: {
                            'firstname': 'First Name',
                            'lastname': 'Last Name',
                            'email': 'Email',
                            'status': 'Status'
                        }
                    }
                ],
                itemChanged: function(item, newValue, oldValue) {
                    var sortVal = this.getValue('sortBy');
                    if (sortVal) {
                        AccountUserTileGrid.data.sortByProperty(sortVal, true);
                    }
                }
            });
        }
        
        return this.userSortForm;
    },

    resetSortForm: function() {
        this.getUserSortForm().reset();
    },

    //update selected groups
    updateGroupSelection: function(data){
        //invalidate group list
        UserDetailsLayout.getInstance().getGroupsListGrid().invalidateCache();

        //set user id as criteria to get groups from that user only.
        UserDetailsLayout.getInstance().getGroupsListGrid().setCriteria({id: User.getInstance().getId()});

        // select current groups
        UserDetailsLayout.getInstance().getGroupsListGrid().dataArrived = function (startRow, endRow) {

            for(var group_index=0; group_index < data[0].groups.length; group_index++){
                var r = null;
                for (var i = startRow; i <= endRow; i++) {
                    r = UserDetailsLayout.getInstance().getGroupsListGrid().getRecord(i);
                    if (r) {
                        var group_id = data[0].groups[group_index].id;
                        if (group_id == r.id) {
                            UserDetailsLayout.getInstance().getGroupsListGrid().selectRecord(r);
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

    getUserTileGrid: function() {
        if (typeof this.userTileGrid == 'undefined') {
            this.userTileGrid = isc.TileGrid.create({
                ID: 'AccountUserTileGrid',
                autoDraw: false,
                tileHeight: 60,
                tileWidth: 225,
                height: '100%',
                width: '100%',
                overflow: 'visible',
                tileValueAlign: 'left',
                layoutMargin: 0,
                autoFetchData: false,
                showAllRecords: true,
                animateTileChange: false,
                dataSource: isc.UserDS.getInstance(),
                recordClick: function (viewer, tile, record) {
                    isc.UserDS.getInstance().fetchData({id: record.id}, function(dsResponse, data) {
                        UserListActions.clickUserTile(data);
                    });
                },
                fields: [
                    {name:'fullname'},
                    {name:'email'},
                    {name:'status', valueMap: {'0':'Blocked', '1':'Active'}
                    }
                ],

               getTileHTML : function (record) {
                    // override getTileHTML() and add an "Avatar" .. active vs blocked
                    if ( record.status == 0 ) {
                        var status = 'Blocked';
                        var myClass = 'userTileBlocked';
                    } else {
                        var status = 'Active';
                        var myClass  = 'userTile';
                    }

                   var html = [
                       '<div class="'+myClass+'" style="background-position:left center;padding:0; margin:0; width:225px; height:50px; cursor:pointer;">',
                       '<span style="margin-left:50px;font-weight:bold;">'+record.fullname+'</span><br/>',
                       '<p style="margin-left:55px;padding-right:5px;font-size:80%;line-height:80%;">'+record.email+'</p>',
                       '<p style="margin-left:55px;padding-right:5px;font-size:80%;line-height:80%;">'+status+'</p>',
                       '</div>'
                   ];

                   return html.join("");

                },

                dataArrived: function() {
                    this.data.sortByProperty('fullname', true);
                }
            })
        }
        return this.userTileGrid;
    }
});
