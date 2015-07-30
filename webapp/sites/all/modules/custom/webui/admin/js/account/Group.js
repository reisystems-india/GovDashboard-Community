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

var Group = isc.defineClass("Group");

//class properties
Group = Group.addClassProperties({
    _instance: null,
    allUsers: null,
    allDashboards: null,
    allDatasources: null,

    getInstance: function() {
        //load new group object here.
        if ( this._instance === null ) {
            this._instance = Group.create();
        }
        return this._instance;
    }
});

//Instance properties
Group = Group.addProperties({
    id: null,
    name: null,
    description: null,
    users: null,
    dashboards: null,
    datasources: null,

    load: function(data) {
        this.setId(data.id);
        this.setName(data.name);
        this.setDescription(data.description);
        this.setUsers(data.users);
        this.setDashboards(data.dashboards);
        this.setDatasources(data.datasources);
    },

    getSimpleGroupObject: function(){
        var simple = {};
        simple.id = this.getId();
        simple.name = this.getName();
        simple.description = this.getDescription();

        simple.users = [];
        jQuery.each(this.users,function(key,value){
            simple.users.push({
                id: value.id
            });
        });

        simple.dashboards = [];
        jQuery.each(this.dashboards,function(key,value){
            simple.dashboards.push({
                id: value.id
            });
        });

        simple.datasources = [];
        jQuery.each(this.datasources,function(key,value){
            simple.datasources.push({
                name: value.name
            });
        });

        return simple;
    },

    getId: function(){
        return this.id;
    },

    setId: function(id){
        this.id = id;
    },

    getName: function(){
        return this.name;
    },

    setName: function(name){
        this.name = name;
    },

    getDescription: function(){
        return this.description;
    },

    setDescription: function(description){
        this.description = description;
    },

    //returns users
    getUsers: function () {
        return this.users;
    },

    //assigns users to group
    setUsers: function(users){
        this.users = users;
    },

    //returns dashboards
    getDashboards: function () {
        return this.dashboards;
    },

    //assigns dashboards to group
    setDashboards: function(dashboards){
        this.dashboards = dashboards;
    },

    //returns datasources
    getDatasources: function () {
        return this.datasources;
    },

    //assigns datasources to group
    setDatasources: function(datasources){
        this.datasources = datasources;
    },

    findDatasource: function ( name ) {
        var datasource = null;
        jQuery.each(this.datasources, function(key,value){
            if ( value.name == name ) {
                datasource = value;
            }
        });
        return datasource;
    },

    findDashboard: function ( id ) {
        var dashboard = null;
        jQuery.each(this.dashboards, function(key,value){
            if ( value.id == id ) {
                dashboard = value;
            }
        });
        return dashboard;
    },

    getViewOnlyDashboards: function () {
        var dashboards = [];
        var _this = this;
        jQuery.each(this.dashboards, function(key,dashboard){
            if ( !_this.findDatasource(dashboard.datasource.name) ) {
                dashboards.push(dashboard);
            }
        });
        return dashboards;
    },

    isViewOnlyDashboard: function ( id ) {
        var dashboards = this.getViewOnlyDashboards();
        var found = false;
        jQuery.each(dashboards, function(key,dashboard){
            if ( id == dashboard.id ) {
                found = true;
            }
        });
        return found;
    }
});