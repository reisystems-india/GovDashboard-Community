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

var WorkflowSection = isc.defineClass("WorkflowSection");

WorkflowSection = WorkflowSection.addClassProperties({

    weight: 10,

    getId: function () {
        return 'WorkflowSection';
    },

    getName: function () {
        return 'workflow';
    },

    getTitle: function () {
        return 'Workflow';
    },

    getDefaultRoute: function () {
        return '/cp/workflow?ds=' + Datasource.getInstance().getName();
    },

	getContainer: function () {
		if ( typeof this.container == 'undefined' ) 
		{
			this.container = isc.VLayout.create({
				ID: "workflowPane",
				width: 900,
				height: "100%",
                layoutMargin: 0,
                autoDraw:false,
				members: []
		    });
	    }
		    
		return this.container;
	}
});

