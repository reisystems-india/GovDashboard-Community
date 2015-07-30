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

var CurrentUser = isc.defineClass("CurrentUser");

CurrentUser = CurrentUser.addProperties({

    getId: function () {
        return this.id;
    },

    getName: function () {
        return this.getFullname();
    },

    getUsername: function () {
        return this.name;
    },

    getEmail: function () {
        return this.email;
    },

    getFullname: function () {
        return this.fullname;
    },

    getFirstname: function () {
        return this.firstname;
    },

    getLastname: function () {
        return this.lastname;
    },

    getType: function () {
        return this.type;
    },

    getGroups: function () {
        return this.groups;
    }
});