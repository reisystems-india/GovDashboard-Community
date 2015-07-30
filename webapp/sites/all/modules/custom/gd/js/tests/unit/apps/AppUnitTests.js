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
                        'host': 'Test Host',
                        'dashboard': 'Test Dashboard',
                        'datamart': 'Test Datamart',
                        'extraOption': 'Test Extra Option'
                    };
                    app = new GD.App(options);
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

        module('GD.App', getSetupAndTearDown());
        test('init', function () {
            app.init(null);
            deepEqual(app.options, options, 'Init with null pass');
            app.init();
            deepEqual(app.options, options, 'Init with no parameters pass');
            options = getValidOptions();
            app.init(options);
            deepEqual(app.options, options, 'Init with valid parameters pass');
        });

        test('setOptions', function () {
            app.setOptions(null);
            deepEqual(app.options, options, 'setOptions with null pass');
            app.setOptions();
            equal(app.options, options, 'setOptions with no parameters pass');
            options = getValidOptions();
            app.setOptions(options);
            deepEqual(app.options, options, 'setOptions with valid parameters pass');
        });
    });
})(typeof window === 'undefined' ? this : window, jQuery);