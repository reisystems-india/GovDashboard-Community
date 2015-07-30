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

var GDHeader = isc.defineClass("GDHeader", "Layout");

GDHeader = GDHeader.addClassProperties({

    _instance: null,

    getInstance: function() {
        if ( this._instance === null ) {
            this._instance = GDHeader.create({});
        }
        return this._instance;
    }
});

GDHeader = GDHeader.addProperties({
    width: "*",
    height: "*",
    layoutMargin: 0,
    overflow: "visible",
	position: "relative",

    initWidget: function() {
        this.Super("initWidget", arguments);
    },

    getHeader : function () {
        var headerLogo = isc.Img.create({
            width: 255,
            height: 74,
            imageType: "normal",
            src: "[SKIN]/govdash_logo.png",
            margin: 10,
            click: function() {
                GDHeader.getInstance().clickNav(
                    function() {
                        window.location.href = "/cp";
                    }
                );
            },
            cursor: "pointer"
        });

        var headerUserLinks = isc.HLayout.create({
            align: "right",
            layoutTopMargin: 5,
            members: [
                isc.HTMLFlow.create({
                    styleName: "welcomeUser",
                    width: "*",
                    contents: "Welcome, " + GovDashboard.getUser().getName()
                }),
                isc.IButton.create({
                    title: "Profile",
                    baseStyle: "navLink",
                    autoFit: true,
                    click: function() {
                        GDHeader.getInstance().clickNav(
                            function() {
                                GDNavigation.gotoProfile();
                            }
                        );
                    }
                }),
                isc.IButton.create({
                    title: "Logout",
                    baseStyle: "navLink",
                    autoFit: true,
                    click: function() {
                        GDHeader.getInstance().clickNav(
                            function() {
                                GDNavigation.gotoLogout();
                            }
                        );
                    }
                })
            ]
        });

        var headerLinks = isc.VLayout.create({
            membersMargin: 10,
            members: [
                headerUserLinks,
                this.getMainNavigation()
            ]
        });

        return isc.HLayout.create({
            ID: 'GDAppHeader',
            width: "auto",
            height: "60",
            styleName: "headerRegion",
            layoutTopMargin: 0,
            members: [
            	headerLogo,
                isc.LayoutSpacer.create(),
                headerLinks
            ]
        });
    },

    clickNav: function(callback) {
        if (Account.getAppliedChanges().length) {
            AccountActions.confirmExit(
                callback
            );
        }
        else {
            callback();
        }
    },
	
	getMainNavigation: function () {
		if ( typeof this.mainNavigation == "undefined" ) {
			this.mainNavigation = isc.HLayout.create({
				membersMargin: 10,
				layoutRightMargin: 10,
				align: "right",
				position: "relative",
				members: [
					isc.IButton.create({
						ID: "navButtonDatasets",
						title: "Datasets",
						baseStyle: "navButtonSelected",
						autoFit: true,
						position: "relative",
						click: function() {
							GDHeader.getInstance().clickNav(
								function() {
                                    GDNavigation.gotoDatasets();
								}
							);
						}
					}),
					isc.IButton.create({
						ID: "navButtonReports",
						title: "Reports",
						baseStyle: "navButton",
						autoFit: true,
						position: "relative",
						click: function() {
							GDHeader.getInstance().clickNav(
								function() {
									GDNavigation.gotoReports();
								}
							);
						}
					}),
					isc.IButton.create({
						ID: "navButtonDashboards",
						title: "Dashboards",
						baseStyle: "navButton",
						autoFit: true,
						position: "relative",
						click: function() {
							GDHeader.getInstance().clickNav(
								function() {
									GDNavigation.gotoDashboards();
								}
							);
						}
					}),

                    isc.IButton.create({
                        ID: "navButtonAccount",
                        title: "Account",
                        baseStyle: "navButton",
                        autoFit: true,
                        position: "relative",
                        click: function() {
                            GDHeader.getInstance().clickNav(
                                function() {
                                    GDNavigation.gotoAccount();
                                }
                            );
                        }
                    })
				]
			});
		}
		
		return this.mainNavigation;
	}
	
});