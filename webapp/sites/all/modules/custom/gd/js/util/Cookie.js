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

(function(global,undefined) {

    (function($,Highcharts) {

        if ( typeof $ === 'undefined' ) {
            throw new Error('Cookie requires jQuery');
        }

        (function (factory) {
            if (typeof define === 'function' && define.amd) {
                // AMD. Register as anonymous module.
                define(['jquery'], factory);
            } else {
                // Browser globals.
                factory(jQuery);
            }
        }(function ($) {

            var pluses = /\+/g;

            function raw(s) {
                return s;
            }

            function decoded(s) {
                return decodeURIComponent(s.replace(pluses, ' '));
            }

            function converted(s) {
                if (s.indexOf('"') === 0) {
                    // This is a quoted cookie as according to RFC2068, unescape
                    s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
                }
                try {
                    return config.json ? JSON.parse(s) : s;
                } catch(er) {}
            }

            var config = $.cookie = function (key, value, options) {

                // write
                if (value !== undefined) {
                    options = $.extend({}, config.defaults, options);

                    if (typeof options.expires === 'number') {
                        var days = options.expires, t = options.expires = new Date();
                        t.setDate(t.getDate() + days);
                    }

                    value = config.json ? JSON.stringify(value) : String(value);

                    return (document.cookie = [
                        config.raw ? key : encodeURIComponent(key),
                        '=',
                        config.raw ? value : encodeURIComponent(value),
                        options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
                        options.path    ? '; path=' + options.path : '',
                        options.domain  ? '; domain=' + options.domain : '',
                        options.secure  ? '; secure' : ''
                    ].join(''));
                }

                // read
                var decode = config.raw ? raw : decoded;
                var cookies = document.cookie.split('; ');
                var result = key ? undefined : {};
                for (var i = 0, l = cookies.length; i < l; i++) {
                    var parts = cookies[i].split('=');
                    var name = decode(parts.shift());
                    var cookie = decode(parts.join('='));

                    if (key && key === name) {
                        result = converted(cookie);
                        break;
                    }

                    if (!key) {
                        result[name] = converted(cookie);
                    }
                }

                return result;
            };

            config.defaults = {};

            $.removeCookie = function (key, options) {
                if ($.cookie(key) !== undefined) {
                    // Must not alter options, thus extending a fresh object...
                    $.cookie(key, '', $.extend({}, options, { expires: -1 }));
                    return true;
                }
                return false;
            };

        }));
    })(typeof global.GD_jQuery != 'undefined' ? global.GD_jQuery : jQuery, typeof global.GD_Highcharts != 'undefined' ? global.GD_Highcharts : (typeof Highcharts != 'undefined' ? Highcharts : undefined));
})(typeof window === 'undefined' ? this : window);