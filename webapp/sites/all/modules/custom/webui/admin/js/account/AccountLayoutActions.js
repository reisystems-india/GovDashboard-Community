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

var AccountSectionActions = isc.defineClass('AccountSectionActions');

AccountSectionActions = AccountSectionActions.addClassProperties({

    buttonIds: ["AccountDashboardsButton", "AccountUserMgmtButton", "AccountGroupMgmtButton", "AccountStatsButton"],

    clickAccountNavButton: function(option) {

        MessageSection.clearMessages();

        switch ( option ) {

            case 'dashboards':
                this.setActiveNavButton('AccountDashboardsButton');
                GDNavigation.addHistory('AccountDashboard');
                this.showListLayoutHeader(option);
                AccountActions.showPane(AccountDashboards.getDashboardsListGrid());
                AccountActions.gotoDashboardLanding();
                break;

            case 'users':
                this.setActiveNavButton('AccountUserMgmtButton');
                GDNavigation.addHistory('UserManagement');
                UserListActions.gotoLanding();
                break;

            case 'groups':
                this.setActiveNavButton('AccountGroupMgmtButton');
                GDNavigation.addHistory('GroupManagement');
                GroupListActions.gotoLanding();
                break;

            case 'stats':
                this.setActiveNavButton('AccountStatsButton');
                GDNavigation.addHistory('AccountSettings');
                this.showListLayoutHeader(option);
                AccountActions.showPane(AccountSection.getStatsPage());
                break;
        }
    },

    setActiveNavButton: function ( id ) {
        jQuery.each(AccountSection.navButtons,function(k,v){
            if ( k === id ) {
                v.setBaseStyle("buttonTabActive");
            } else {
                v.setBaseStyle("buttonTab");
                v.redraw();
            }
        });
    },

    showListLayoutHeader: function(option) {

        AccountDashboards.getDashboardHeaderLayout().hide();
        UserListLayout.getInstance().getPaneHeader().hide();
        GroupListLayout.getInstance().getPaneHeader().hide();
        AccountSection.getStatsHeaderLayout().hide();

        switch (option) {
            case 'dashboards':
                AccountDashboards.getDashboardHeaderLayout().show();
                break;
            case 'users':
                UserListLayout.getInstance().getPaneHeader().show();
                break;
            case 'groups':
                GroupListLayout.getInstance().getPaneHeader().show();
                break;
            case 'stats':
                AccountSection.getStatsHeaderLayout().show();
                break;
        }
    }
});
