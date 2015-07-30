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

(function(global,$,undefined) {

    if ( typeof $ === 'undefined' ) {
        throw new Error('WorkflowModel requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('WorkflowModel requires GD');
    }

    var GD = global.GD;

    var WorkflowModel = GD.Class.extend({

        init: function ( context ) {
            this.context = context;
            return this;
        },

        getDatasources: function () {
            var _this = this;
            return GD.DatasourceFactory.getDatasourceIndex(null,
                function(data){
                    _this.datasources = [];
                    for (var i = 0, count=data.length; i<count; i++) {
                        _this.datasources.push(new GD.Datasource(data[i]));
                    }
                },
                function(jqXHR, textStatus, errorThrown) {
                    _this.context.messaging.addErrors(jqXHR.responseText);
                    _this.context.messaging.displayMessages();
                }
            );
        },

        getDatasource: function ( name ) {
            var datasource = null;
            $.each(this.datasources,function(k,v){
                if (v.getName() == name) {
                    datasource = v;
                    return false;
                }
            });
            return datasource;
        },

        getImport: function(items, options) {
            var reports = [], dashboards = [];
            for (var key in items) {
                for (var i = 0; i < items[key].length; i++) {
                    if (items[key][i]['type'] == 'report') {
                        reports.push(items[key][i]['object']);
                    } else {
                        dashboards.push(items[key][i]['object']);
                    }
                }
            }

            var lookup = [];
            for (var l = 0; l < reports.length; l++) {
                lookup.push(reports[l]['uuid']);
            }

            if (options['reports']) {
                var reportList = options['reports'];
                for(var j = 0; j < dashboards.length; j++) {
                    var r = dashboards[j]['reports'];
                    for (var k = 0; k < r.length; k++) {
                        var uuid = r[k];
                        if (!lookup[uuid]) {
                            for (var n = 0; n < reportList.length; n++) {
                                if (uuid == reportList[n]['uuid']) {
                                    reports.push(reportList[n]);
                                }
                            }
                        }
                    }
                }
            }

            return {"export": { "reports": reports, "dashboards": dashboards}};
        },

        processItems: function(items, destinations, options) {
            this.messages = {
                'notices': [],
                'errors': []
            };

            var _this = this;
            var calls = [];
            for(var i = 0; i < destinations.length; i++) {
                calls.push(GD.DatasourceFactory.sync(destinations[i], JSON.stringify(this.getImport(items, options)), function(data){
                        var datasource = _this.getDatasource(data['name']);
                        _this.messages['notices'].push('Items copied to Datasource ['+datasource.getPublicName()+'] successfully.');
                    },
                    function(jqXHR, textStatus, errorThrown) {
                        _this.messages['errors'].push(jqXHR.responseText);
                    }));
            }

            $.when.apply($, calls).always(function() {
                _this.context.view.messaging.clean();

                if (_this.messages['notices'].length > 0) {
                    _this.context.view.messaging.showMessage(_this.messages['notices'], 'notice');
                }

                if (_this.messages['errors'].length > 0) {
                    _this.context.view.messaging.showMessage(_this.messages['errors'], 'error');
                }
                if (options['callback']) {
                    options['callback']();
                }
            });
        }
    });

    // add to global space
    global.GD.WorkflowModel = WorkflowModel;

})(typeof window === 'undefined' ? this : window, jQuery);