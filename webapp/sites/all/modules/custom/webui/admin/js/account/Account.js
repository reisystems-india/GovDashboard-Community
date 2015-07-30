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

var Account = isc.defineClass("Account");

//Class methods
Account = Account.addClassProperties({
    logChanges: function(whatChanged) {
        if (!this.appliedChanges) {
            this.getAppliedChanges();
        }

        if (jQuery.inArray(whatChanged, this.appliedChanges) < 0) {
            this.appliedChanges.push(whatChanged);
        }
    },

    clearChangeLog: function() {
        this.appliedChanges = [];
    },

    getAppliedChanges: function() {
        if(typeof this.appliedChanges === "undefined") {
            this.appliedChanges = [];
        }

        return this.appliedChanges;
    }
});