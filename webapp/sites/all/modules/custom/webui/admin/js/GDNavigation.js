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

var GDNavigation = isc.defineClass("GDNavigation");

GDNavigation = GDNavigation.addClassProperties({

    datasourceDropdown: {},

    showSection: function ( section ) {
        jQuery.each(GovDashboard.getSections(),function(key,value){
            if ( value.getId() == section.getId() ) {
                value.getContainer().show();
            } else {
                value.getContainer().hide();
            }
        });
    },

    dispatch: function ( App, path ) {
        var parts = path.slice(1).split('/');
        if ( parts[1] == 'account' ) {
            App.getBody().addMember(AccountSection.getContainer());
            this.renderAccount();
        }
    },

    gotoDatasets: function() {
        window.location.href = '/cp/dataset';
    },

    gotoDataset: function ( name ) {
        window.location.href = '/cp/dataset/' + name;
    },

    gotoReports: function() {
        window.location.href = '/cp/report?ds=' + Datasource.getInstance().getName();
    },

    gotoReport: function ( name ) {
        window.location.href = '/cp/report/' + name;
    },

    gotoNewReport: function ( datasetName ) {
        if ( typeof datasetName == "undefined" ) {
            window.location.href = '/cp/report/create?ds=' + Datasource.getInstance().getName();
        } else {
            window.location.href = '/cp/report/create?datasetName='+datasetName;
        }
    },

    gotoDashboards: function() {
        window.location.href = '/cp/dashboard';
    },

    gotoDashboard: function ( name ) {
        window.location.href = '/cp/dashboard/' + name;
    },

    gotoNewDashboard: function() {
        window.location.href = '/cp/dashboard/create';
    },

    renderAccount: function () {
        this.clearChangeLogs();

        this.showSection(AccountSection);

        if ( AccountSection.getContainer().getMembers().getLength() === 0 ) {
            AccountSection.getContainer().addMember(AccountSection.getHeaderLayout(),0);
            AccountSection.getContainer().addMember(AccountSection.getNavButtonLayout(),1);
            AccountSection.getContainer().addMember(AccountSection.getMainPane(),2);
        }

        AccountActions.exit();
    },

    gotoAccountArea: function ( accountNavOption ) {

        this.clearChangeLogs();

        if ( AccountSection.getContainer().getMembers().getLength() === 0 ) {
            AccountSection.getContainer().addMember(AccountSection.getHeaderLayout(),0);
            AccountSection.getContainer().addMember(AccountSection.getNavButtonLayout(),1);
            AccountSection.getContainer().addMember(AccountSection.getMainPane(),2);
        }

        this.showSection(AccountSection);

        AccountSectionActions.clickAccountNavButton(accountNavOption);

    },

    gotoAccount: function() {
        window.location.href = '/cp/account';
    },

    gotoProfile: function() {
        this.clearChangeLogs();
        window.location.href = "/user/profile";
    },

    gotoLogout: function() {
        this.clearChangeLogs();
        window.location.href = "/user/logout";
    },

    clearChangeLogs: function() {
        MessageSection.clearMessages();
    },

    jumpTo: function ( id ) { // Handle browser refreshes/direct link

        this.clearChangeLogs();

        if ( id === null ) {
            return;
        }

        // regex pattern to go to a specific account tab
        var accountTabPattern = /^accountDashboard$|^userManagement$|^groupManagement$|^accountSettings$/i;

        // Account (Sub) Tab
        if (id.match(accountTabPattern)) {
            var accountNavOption = null;

            if (id.match(/^accountDashboard$/i)) {
                accountNavOption = 'dashboards';
            }
            else if (id.match(/^userManagement$/i)) {
                accountNavOption = 'users';
            }
            else if (id.match(/^groupManagement$/i)) {
                accountNavOption = 'groups';
            }
            else if (id.match(/^accountSettings$/i)) {
                accountNavOption = 'stats';
            }

            this.gotoAccountArea(accountNavOption);
        }
    },

    addHistory: function ( id ) {
        isc.History.addHistoryEntry(id);
    }

});

