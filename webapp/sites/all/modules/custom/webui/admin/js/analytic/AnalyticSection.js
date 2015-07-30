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

var AnalyticSection = isc.defineClass("AnalyticSection");

AnalyticSection = AnalyticSection.addClassProperties({

    weight: 5,

    getId: function () {
        return 'AnalyticSection';
    },

    getName: function () {
        return 'model';
    },

    getTitle: function () {
        return 'Analytics';
    },

    getDefaultRoute: function () {
        return '/cp/analytics';
    },

    getContainer: function () {
        if ( typeof this.container == 'undefined' )
        {
            this.container = isc.VLayout.create({
                ID: "analyticPane",
                width: 900,
                height: "100%",
                layoutMargin: 0,
                defaultLayoutAlign: "center",
                members: []
            });
        }

        return this.container;
    }
});
