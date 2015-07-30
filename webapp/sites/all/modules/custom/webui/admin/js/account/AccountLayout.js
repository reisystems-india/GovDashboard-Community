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

var AccountSection = isc.defineClass('AccountSection');

AccountSection = AccountSection.addClassProperties({

    weight: 10,

    getId:function () {
        return 'AccountSection';
    },

    getName: function () {
        return 'account';
    },

    getTitle: function () {
        return 'Account';
    },

    getDefaultRoute: function () {
        return '/cp/account?ds=' + Datasource.getInstance().getName();
    },

    getContainer:function () {
        if (typeof this.container == 'undefined') {
            this.container = isc.VLayout.create({
                ID:'AccountSectionContainer',
                styleName:'accountLayoutContainer',
                width:1000,
                height:600,
                overflow:'visible',
                membersMargin:0,
                layoutMargin:0,
                autoDraw:false,
                members:[]
            });
        }

        return this.container;
    },

    getHeaderLayout:function () {
        if (typeof this.headerLayout == 'undefined') {
            this.headerLayout = isc.HLayout.create({
                width:'100%',
                height:10,
                membersMargin:0,
                layoutMargin:0,
                layoutBottomMargin:10,
                autoDraw:false,
                members:[
                    isc.Label.create({
                        contents:'<h2>Account Management</h2>',
                        width:'100%',
                        height:10,
                        styleName:'sectionHeader'
                    })
                ]
            });
        }

        return this.headerLayout;
    },


    /**
     *
     * @param buttonIds array
     * @param type e.g. horizontal or vertical
     */
    resetAllNavigationButtons:function (buttonIds, type) {
        if ("horizontal" == type) {
            if (isc.Canvas.getById(buttonIds[0]) != null) { // hacky way to check
                for (var i = 0, count = buttonIds.length; i < count; i++) {
                    isc.Canvas.getById(buttonIds[i]).setBaseStyle("buttonTab");
                }
            }
        } else {
            if (isc.Canvas.getById(buttonIds[0]) != null) { // hacky way to check
                for (var i = 0, count = buttonIds.length; i < count; i++) {
                    isc.Canvas.getById(buttonIds[i]).setBaseStyle("vertTab");
                }
            }
        }
    },

    clickNav:function (callback) {
        if (Account.getAppliedChanges().length) {
            AccountActions.confirmExit(
                    callback
            );
        }
        else {
            callback();
        }
    },

    getNavButtonLayout:function () {
        if (typeof this.navButtonLayout == 'undefined') {

            this.navButtons = {};

            this.navButtons.AccountDashboardsButton = isc.Button.create({
                ID:'AccountDashboardsButton',
                title:'Dashboards',
                width:"*",
                height:40,
                baseStyle:'buttonTab',
                click:function () {
                    AccountSection.clickNav(
                            function () {
                                AccountSectionActions.clickAccountNavButton('dashboards');
                            }
                    );
                }
            });

            this.navButtons.AccountUserMgmtButton = isc.Button.create({
                ID:'AccountUserMgmtButton',
                title:'User Management',
                width:"*",
                height:40,
                baseStyle:'buttonTab',
                click:function () {
                    AccountSection.clickNav(
                            function () {
                                AccountSectionActions.clickAccountNavButton('users');
                            }
                    );
                }
            });

            this.navButtons.AccountGroupMgmtButton = isc.Button.create({
                ID:'AccountGroupMgmtButton',
                title:'Group Management',
                width:"*",
                height:40,
                baseStyle:'buttonTab',
                click:function () {
                    AccountSection.clickNav(
                            function () {
                                AccountSectionActions.clickAccountNavButton('groups');
                            }
                    );
                }
            });

            this.navButtons.AccountStatsButton = isc.Button.create({
                ID:'AccountStatsButton',
                title:'Statistics & Settings',
                width:"*",
                height:40,
                baseStyle:'buttonTab',
                click:function () {
                    AccountSection.clickNav(
                            function () {
                                AccountSectionActions.clickAccountNavButton('stats');
                            }
                    );
                }
            });


            this.navButtonLayout = isc.HLayout.create({
                ID:'AccountNavButtonLayout',
                styleName:'accountNavButtonLayout',
                membersMargin:5,
                height:25,
                memberOverlap:0,
                members:[
                    this.navButtons.AccountDashboardsButton,
                    this.navButtons.AccountUserMgmtButton,
                    this.navButtons.AccountGroupMgmtButton,
                    this.navButtons.AccountStatsButton
                ]
            });
        }

        return this.navButtonLayout;
    },

    getMainPane:function () {
        if (typeof this.mainPane == 'undefined') {
            this.mainPane = isc.VLayout.create({
                styleName:'accountDashPane',
                width:'100%',
                height:'100%',
                overflow:'visible',
                layoutMargin:5,
                members:[
                    this.getPaneHeaders(),
                    this.getNavPane()
                ]
            });
        }

        return this.mainPane;
    },

    getPaneHeaders:function () {
        if (typeof this.paneHeaders == 'undefined') {
            this.paneHeaders = isc.VLayout.create({
                ID:'AccountPaneHeadersLayout',
                styleName:'AccountPaneHeadersLayout',
                width:'100%',
                height:1,
                overflow:'visible',
                layoutTopMargin:0,
                layoutLeftMargin:10,
                layoutRightMargin:10,
                layoutBottomMargin:0,
                backgroundColor:'white',
                members:[
                    AccountDashboards.getDashboardHeaderLayout(),
                    UserListLayout.getInstance().getPaneHeader(),
                    GroupListLayout.getInstance().getPaneHeader()
                ]
            });
        }

        return this.paneHeaders;
    },

    getNavPane:function () {
        if (typeof this.navPane == 'undefined') {
            this.navPane = isc.VLayout.create({
                ID:'AccountSectionNavPane',
                styleName:'AccountSectionNavPane',
                width:'100%',
                height:'*',
                overflow:'visible',
                layoutMargin:0,
                layoutTopMargin:10,
                layoutLeftMargin:10,
                layoutRightMargin:10,
                backgroundColor:'white',
                align:"top",
                members:[
                    AccountDashboards.getDashboardsListGrid(),
                    UserDetailsLayout.getInstance().getContainer(),
                    UserListLayout.getInstance().getUserTileGrid(),
                    UserAddNewLayout.getInstance().getContainer(),
                    GroupDetailsLayout.getInstance().getContainer(),
                    GroupListLayout.getInstance().getGroupTileGrid(),
                    GroupAddNewLayout.getInstance().getContainer(),
                    AccountSection.getStatsPage()
                ]
            });
        }

        return this.navPane;
    },

    getStatsHeaderLayout:function () {
        if (typeof this.statsHeaderLayout == 'undefined') {
            this.statsHeaderLayout = isc.HLayout.create({
                width:'100%',
                height:10,
                layoutMargin:0,
                layoutTopMargin:17/*,
                 members: [
                 isc.Label.create({
                 contents: '<h2>Statistics & Settings</h2>',
                 width: '100%',
                 backgroundColor: "#FFF",
                 height: 35,
                 autoDraw: false,
                 styleName: 'accountHeader'
                 })
                 ]*/
            });
        }
        return this.statsHeaderLayout;
    },

    getStatsPage:function () {
        if (typeof this.statsPage == 'undefined') {
            this.statsPage = isc.HTMLPane.create({
                ID:'AccountStatsPage',
                width:"100%",
                height:760,
                overflow:'hidden',
                contentsURL:'/account_datamart_statistics_charts',
                contentsType:'page'
            });
        }

        return this.statsPage;
    }
});
