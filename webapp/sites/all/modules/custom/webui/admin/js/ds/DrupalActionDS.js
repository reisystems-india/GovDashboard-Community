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

// $Id$

var DrupalActionDS = isc.defineClass("DrupalActionDS", "DataSource").addProperties({
    resource: null,
    dataFormat: "json",
    dataURL: null,
    operationBindings: [
        {operationType:"fetch", dataProtocol:"postMessage"},
        {operationType:"add", dataProtocol:"postMessage"},
        {operationType:"update", dataProtocol:"postMessage", requestProperties:{httpMethod: "PUT"}},
        {operationType:"remove", dataProtocol:"getParams", requestProperties:{httpMethod: "DELETE"}}
    ],

    init: function() {
        this.Super('init', arguments);
        this.dataURL = '/api/' + this.resource + '.json';
    }
});