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

var GroupAddNewLayout = isc.defineClass('GroupAddNewLayout');

GroupAddNewLayout = GroupAddNewLayout.addClassProperties({
	_instance: null,

    getInstance: function() {
    	if ( !this._instance ) {
    		this._instance = GroupAddNewLayout.create();
    	}
    	return this._instance;
	}
});

GroupAddNewLayout = GroupAddNewLayout.addProperties({
    getContainer: function() {
        if ( typeof this.container == 'undefined') {
            this.container = isc.VLayout.create({
                ID: 'GroupAddNewLayoutContainer',
                width: '100%',
                height: '100%',
                overflow: 'visible',
                layoutMargin: 0,
                backgroundColor: "white",
                /*membersMargin: 6,*/
                members: [
                    this.getHeaderLayout(),
                    NewGroupForm.getAddNewGroupForm()
                ]
            });
        }

        return this.container;
    },

    getHeaderLayout: function () {
        if ( typeof this.headerLayout == 'undefined' )
        {
            this.headerLayout = isc.HLayout.create({
                width: '100%',
                height: 10,
                membersMargin: 0,
                defaultLayoutAlign: 'right',
                styleName: 'reportHeader',
                layoutMargin: 0,
                layoutRightMargin: 12,
                layoutBottomMargin: 10,
                layoutTopMargin: 5,
                members: [
                    isc.Label.create({
                        contents: '<h2>Add New Group</h2>',
                        width: '100%',
                        height: 10,
                        autoDraw: false,
                        styleName: 'accountHeader'
                    })
                ]
            });
        }
        return this.headerLayout;
    }
});