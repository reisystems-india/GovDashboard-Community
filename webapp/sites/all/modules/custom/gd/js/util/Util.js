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

(function(global,undefined){

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('Util requires GD');
    }

    (function($,Highcharts) {

        if ( typeof $ === 'undefined' ) {
            throw new Error('Util Requires jQuery');
        }

        var Util = {

            addCSS: function ( path, domain ) {
                var url = path;
                if ( domain ) {
                    url = domain + path;
                }

                if (document.createStyleSheet){ // for IE
                    document.createStyleSheet(url);
                } else {
                    $("head").append('<link rel="stylesheet" type="text/css" href="'+url+'" media="all" />');
                }
            },

            getFilterQueryString: function() {
                var urlStr = window.location.href;
                var reMatch = urlStr.match(/[&?]t\[.*/); // todo: this grabs everything from the first t param, need better regex pattern
                if (reMatch && reMatch.length) {
                    return reMatch[0].replace('?', '');
                }
                else return '';
            },

            Google: {

                loadAPIPromise: null,

                loadAPIShared: function loadAPI(callback) {
                    if (!global.GD.Util.Google.loadAPIPromise) {
                        var deferred = $.Deferred();
                        $.ajax({
                            url: "https://www.google.com/jsapi/",
                            dataType: "script",
                            success: function() {
                                google.load("maps", "3", {
                                    callback: function() {
                                        deferred.resolve();
                                    },
                                    other_params: "sensor=false"
                                });
                            }
                        });
                        global.GD.Util.Google.loadAPIPromise = deferred.promise();
                    }
                    global.GD.Util.Google.loadAPIPromise.done(callback);
                }
            }

        };

        global.GD.Util = Util;
    })(typeof global.GD_jQuery != 'undefined' ? global.GD_jQuery : jQuery, typeof global.GD_Highcharts != 'undefined' ? global.GD_Highcharts : (typeof Highcharts != 'undefined' ? Highcharts : undefined));
})(typeof window === 'undefined' ? this : window);