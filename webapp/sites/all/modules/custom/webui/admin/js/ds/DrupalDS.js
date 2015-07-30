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


var DrupalDS = isc.defineClass("DrupalDS", "DataSource").addProperties({
    resource:null,
    dataFormat: "json",
    dataURL:null,
    operationBindings: [
        {operationType: "fetch", dataProtocol:"getParams"},
        {operationType: "add", dataProtocol:"postMessage"},
        {operationType: "update", dataProtocol:"postMessage", requestProperties: {httpMethod: "PUT"}},
        {operationType: "remove", dataProtocol:"getParams", requestProperties: {httpMethod: "DELETE"}}
    ],

    init: function() {
        this.Super("init", arguments);
        this.dataURL = '/api/' + this.resource + '.json';
    },

    transformRequest: function ( dsRequest ) {
        dsRequest.useSimpleHttp = true;

        dsRequest.httpHeaders = {"X-CSRF-Token" : GovDashboard.token };

        // if item specific
        if ( dsRequest.data && dsRequest.data[this.getPrimaryKeyField().name] ) {

            var id = dsRequest.data[this.getPrimaryKeyField().name];

            if ( dsRequest.operationType == 'fetch' || dsRequest.operationType == 'update' || dsRequest.operationType == 'remove' ) {
                dsRequest.actionURL = '/api/' + this.resource + '/'+ id +'.json';

                if ( typeof dsRequest.originalData.fields != "undefined" ) {
                    dsRequest.actionURL += '?fields='+dsRequest.originalData.fields;
                }
            }

            if ( dsRequest.operationType == 'add' && dsRequest.data['saveas'] ) {
                dsRequest.actionURL = '/api/' + this.resource + '/'+ id +'/save_as.json';
            }

            if ( dsRequest.operationType == 'fetch' && typeof dsRequest.relationship != "undefined" ) {

                if ( typeof dsRequest.view != "undefined" ) {
                    dsRequest.actionURL = '/api/' + this.resource + '/'+ id +'/'+dsRequest.relationship+'/'+dsRequest.view+'.json';
                } else {
                    dsRequest.actionURL = '/api/' + this.resource + '/'+ id +'/'+dsRequest.relationship+'.json';
                }
            }

        } else {

            // only for index requests
            if ( dsRequest.operationType == 'fetch' && dsRequest.originalData ) {
                dsRequest.actionURL = '/api/' + this.resource + '.json?';

                if ( typeof dsRequest.originalData.datasource != "undefined" ) {
                    dsRequest.actionURL += '&filter[datasource]=' + dsRequest.originalData.datasource;
                }

                if ( typeof dsRequest.originalData.dataset != "undefined" ) {
                    dsRequest.actionURL += '&filter[dataset]=' + dsRequest.originalData.dataset;
                }

                if ( typeof dsRequest.originalData.secondaryDataset != "undefined" ) {
                    dsRequest.actionURL += '&filter[secondaryDataset]=' + dsRequest.originalData.secondaryDataset;
                }

                if ( typeof dsRequest.originalData.datatype != "undefined" ) {
                    dsRequest.actionURL += '&filter[datatype]=' + dsRequest.originalData.datatype;
                }

                if ( typeof dsRequest.originalData.fields != "undefined" ) {
                    dsRequest.actionURL += '&fields='+dsRequest.originalData.fields;
                }

                if ( typeof dsRequest.originalData.filter != "undefined" ) {
                    for ( var f in dsRequest.originalData.filter ) {
                        if ( isc.isA.Array(dsRequest.originalData.filter[f]) ) {
                            for ( var i = 0; i < dsRequest.originalData.filter[f].length; i++ ) {
                                dsRequest.actionURL += '&filter['+f+']='+dsRequest.originalData.filter[f];
                            }
                        } else {
                            dsRequest.actionURL += '&filter['+f+']='+dsRequest.originalData.filter[f];
                        }

                    }
                }

                if ( typeof dsRequest.originalData.groups != "undefined" ) {

                    for ( var i = 0; i < dsRequest.originalData.groups.getLength(); i++ ) {
                        dsRequest.actionURL += '&filter[groups][]='+dsRequest.originalData.groups[i];
                    }
                }
            }
        }

        // build the object to send for post and put
        if ( dsRequest.operationType == 'add' || dsRequest.operationType == 'update' ) {
            dsRequest.contentType = 'application/json';
            var payload = isc.JSONEncoder.create().encode(dsRequest.data);
            return "{ \"" + this.resource + "\": " + payload + "}";
        }
    },

    transformResponse: function(dsResponse, dsRequest, data) {
        // print warnings
        if ( typeof dsResponse.httpHeaders != 'undefined' && dsResponse.httpHeaders && typeof dsResponse.httpHeaders['gd_warnings'] != 'undefined' ) {
            var warnings = dsResponse.httpHeaders['gd_warnings'];
            MessageSection.showMessage(warnings,'warning');
        }
    }
});

/**
 * Service error handler.
 */
var LastRpcErrorResponse = null;
var RPCManager = isc.RPCManager.addClassProperties({
    handleTransportError : function (transactionNum, status, httpResponseCode, httpResponseText) {
        LastRpcErrorResponse = jQuery.parseJSON(httpResponseText);
    },

    handleError : function (response, request) {
        MessageSection.clearMessages();
        response.errors = [];

        if ( response.httpResponseCode == 403 ) {
            GDNavigation.gotoDatasets();
            MessageSection.showMessage(['Access Denied'], 'error');
        } else if ( response.httpResponseCode == 401 ) {
            var path  = document.URL.split('/');
            if ( path[3] ) {
                window.location = 'user?destination='+encodeURIComponent(path[3]);
            } else {
                window.location = 'user';
            }
        } else if ( response.httpResponseCode == 404 ) {
            MessageSection.showMessage([response.data.data], 'error');
        } else if ( response.httpResponseCode == 502 ) {
            MessageSection.showMessage([response.data.data], 'error');
        } else if ( response.httpResponseCode == 406 ) {
            MessageSection.showMessage(LastRpcErrorResponse, 'error');
            LastRpcErrorResponse = null;
        } else {
            MessageSection.showMessage("An unexpected Error has occurred. Please contact your Site Administrator.", "error");
        }
    }
});