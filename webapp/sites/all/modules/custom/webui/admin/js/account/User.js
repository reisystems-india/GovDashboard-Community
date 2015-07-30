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

var User = isc.defineClass("User");

//class properties
User = User.addClassProperties({
    _instance: null,
    allGroups: null,

    getInstance: function() {
        //load new user object here.
        if ( !this._instance ) {
            this._instance = User.create();
        }
        return this._instance;
    },

    //returns all available groups.
    getAllGroups: function () {
        //return all available groups
        return this.allGroups;
    }
});


//Instance properties
User = User.addProperties({
    id: null,
    firstname: null,
    lastname: null,
    fullname: null,
    email: null,
    groups: null,
    dashboards: null,
    datasources: null,
    status: null,

    getSimpleUserObject: function(){
        return {
            id: this.getId(),
            firstname: this.getFirstname(),
            lastname: this.getLastname(),
            email: this.getEmail(),
            groups: this.getGroups(),
            status: this.getStatus()
        };
    },

    load: function(data) {
        this.setId(data.id);
        this.setFirstname(data.firstname);
        this.setLastname(data.lastname);
        this.setFullname(data.fullname);
        this.setEmail(data.email);
        this.setGroups(data.groups);
        this.setDashboards(data.dashboards);
        this.setDatasources(data.datasources);
        this.setStatus(data.status);
    },

    getId: function() {
        return this.id;
    },

    setId: function ( id ) {
        this.id = id;
    },

    getFirstname: function() {
        return this.firstname;
    },

    setFirstname: function ( firstname ) {
        this.firstname = firstname;
    },

    getLastname: function() {
        return this.lastname;
    },

    setLastname: function ( lastname ) {
        this.lastname = lastname;
    },

    getFullname: function() {
        return this.fullname;
    },

    setFullname: function( fullname ) {
        this.fullname = fullname;
    },

    getEmail: function() {
        return this.email;
    },

    setEmail: function ( email ) {
        this.email = email;
    },

    //returns user groups
    getGroups: function () {
        return this.groups;
    },

    //assigns user to list of groups
    setGroups: function ( groups ) {
        this.groups = groups;
    },

    //returns user dashboards
    getDashboards: function(){
        return this.dashboards;
    },

    setDashboards: function ( dashboards ) {
        this.dashboards = dashboards;
    },

    //return user data-marts
    getDatasources: function() {
        return this.datasources;
    },

    setDatasources: function(datasources) {
        this.datasources = datasources;
    },

    //return user status
    getStatus: function() {
        return this.status;
    },

    setStatus: function(status) {
        this.status = status;
    }
});