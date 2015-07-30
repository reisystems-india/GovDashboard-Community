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

var NewGroupForm = isc.defineClass('NewGroupForm');

NewGroupForm = NewGroupForm.addClassProperties({
    getAddNewGroupForm: function() {
        if ( typeof this.newGroupForm == 'undefined' ) {
            this.newGroupForm = isc.DynamicForm.create({
                ID: 'newGroupForm',
                compoundEditor: this,
                autoDraw: false,
                dataSource: GroupDS.getInstance(),
                width: '100%',
                numCols: 3,
                colWidths:[150,110,'*'],
                fields: [
                    {
                        name: 'name',
                        title: 'Group Name',
                        type: 'text',
                        height:30,
                        required: true,
                        cellHeight: 30,
                        width: 250,
                        colSpan: 3
                    },
                    {
                        name: 'description',
                        title: 'Group Description',
                        type: 'textarea',
                        canDragResize:false,
                        required: true,
                        cellHeight: 60,
                        height: 50,
                        width: 250,
                        colSpan:3
                    },
                    {
                        name: 'users',
                        displayField: 'fullname',
                        valueField: 'id',
                        sortField: 'fullname',
                        title: 'Users',
                        type: 'select',
                        multiple: true,
                        optionDataSource: isc.UserDS.getInstance(),
                        hint: '<nobr>select users for the group</nobr>',
                        cellHeight: 30,
                        width: 250,
                        colSpan:3
                    },
                    {
                        name: 'datasources',
                        displayField: 'publicName',
                        valueField: 'name',
                        sortField: 'publicName',
                        title: 'Topics',
                        type: 'select',
                        multiple: true,
                        optionDataSource: DatasourceDS.getInstance(),
                        hint: '<nobr>select data for the group</nobr>',
                        cellHeight: 30,
                        width: 250,
                        colSpan: 3
                    },
                    {
                        name: 'dashboards',
                        displayField: 'name',
                        valueField: 'id',
                        sortField: 'name',
                        title: 'Dashboards',
                        type: 'select',
                        multiple: true,
                        optionDataSource: DashboardDS.getInstance(),
                        initialCriteria:{datasource:Datasource.getInstance().getName()},
                        hint: '<nobr>select Dashboards for the group</nobr>',
                        cellHeight:30,
                        width:250,
                        colSpan:3
                    },
                    {
                        name: 'staticItem',
                        type: 'BlurbItem',
                        value: '',
                        colSpan:3,
                        startRow: true,
                        endRow: true
                    },
                    {
                        name: 'staticItem',
                        type:'BlurbItem',
                        value: '',
                        colSpan: 1,
                        startRow: true,
                        endRow: false
                    },
                    {
                        name: 'cancel',
                        title: 'Cancel',
                        type: 'button',
                        width: 100,
                        colSpan: 1,
                        startRow: false,
                        endRow: false,
                        click: function() {
                            NewGroupForm.clearAddNewGroupForm();
                            GroupListActions.gotoLanding();
                        }
                    },
                    {
                        name: 'create',
                        title: 'Create Group',
                        type: 'button',
                        width: 120,
                        colSpan: 1,
                        startRow: false,
                        endRow: true,
                        click: function() {
                            // replace apostrophe in groupname with ascii
                            var groupname = NewGroupForm.getAddNewGroupForm().getValue('name');
                            var groupname = groupname.replace(/'/g, "&#039;");
                            NewGroupForm.getAddNewGroupForm().setValue('name',groupname);

                            NewGroupForm.getAddNewGroupForm().saveData(
                                function(dsResponse, data, dsRequest) {
                                    if ( !dsResponse.errors ) {
                                        GroupListActions.gotoLanding();
                                        NewGroupForm.clearAddNewGroupForm();
                                        GroupListLayout.getInstance().getGroupTileGrid().data.sortByProperty('name', true);
                                        MessageSection.showMessage(["Group Successfully Created"]);
                                    }
                                }
                            );

                        }
                    }
                ]
            });
        }

        return this.newGroupForm;
    },

    clearAddNewGroupForm:function(){
        if ( typeof this.newGroupForm != 'undefined' ){
            this.newGroupForm.clearValues();
        }
    }
});