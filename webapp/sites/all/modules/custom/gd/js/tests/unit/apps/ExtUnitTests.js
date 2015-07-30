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
    $(document).ready(function () {
        var GD = global.GD;

        var app = null;
        var options = null;

        function getSetupAndTearDown() {
            return {
                'setup': function () {
                    options = {
                        'container': 'Test Container',
                        'autodraw': false,
                        'autodrawMenus': false,
                        'dashboard': 'Test dashboard',
                        'host': 'Test host',
                        'datamart': '1',
                        'css': [
                            //  TODO Test external CSS page
                        ]
                    };
                    app = new GD.Ext(options);
                },
                'teardown': function () {
                    app = null;
                    options = null;
                }
            };
        }

        function getValidOptions() {
            return {
                'host': 'Test Host 2',
                'dashboard': 'Test Dashboard 2',
                'datamart': 'Test Datamart 2',
                'extraOption': 'Test Extra Option 2'
            };
        }

        module('GD.Ext', getSetupAndTearDown());
    });
})(typeof window === 'undefined' ? this : window, jQuery);