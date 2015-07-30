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
        throw new Error('Utility requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('Utility requires GD');
    }

    var GD = global.GD;

    $.fn.extend({
        uniqueId: (function() {
            var uuid = 0;

            return function() {
                return this.each(function() {
                    if ( !this.id ) {
                        this.id = "ui-id-" + ( ++uuid );
                    }
                });
            };
        })()
    });

    global.GD.Utility = {

        getMonthCodes: function() {
            return ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        },

        getQuarterCodes: function() {
            return ["Q1", "Q2", "Q3", "Q4"];
        },

        sanitizeValue: function(v) {
            return String(v).replace(/[ &<>"'\/]/g, '');
        },

        isValidDate: function(date) {
            return moment(date,'YYYY-MM-DD',true).isValid();
        },

        isValidDateTime: function(datetime) {
            return moment(datetime,'YYYY-MM-DDTHH:mm:ss.SSSZZ',true).isValid();
        },

        isValidTime: function(time) {
            return moment(time,'HH:mm:ss',true).isValid();
        },

        formatDateTime: function(d) {
            return moment(d, 'YYYY-MM-DDTHH:mm:ss.SSSZZ').format('MM/DD/YYYY hh:mm:ss A');
        },

        formatDate: function(d) {
            return moment(d, 'YYYY-MM-DD').format('MM/DD/YYYY');
        },

        formatTime: function(t) {
            return moment(t, 'HH:mm:ss').format('hh:mm:ss A');
        },

        isAfter: function(a, b) {
            var v1 = moment(a);
            var v2 = moment(b);
            return v1.isAfter(v2);
        },

        isBefore: function(a, b) {
            var v1 = moment(a);
            var v2 = moment(b);
            return v1.isBefore(v2);
        },

        parseISO8601: function(str) {
            return Date.UTC(str.slice(0,4), str.slice(5,7) - 1, str.slice(8,10), str.slice(11,13), str.slice(14,16), str.slice(17, 19));
        },

        getUSFormat: function(date) {
            if (date) {
                var dateDate = date;

                if( typeof dateDate == "string" ) {
                    dateDate = new Date(dateDate.replace(/-/g, '/').replace('T', ' '));
                }

                return ('0' + (dateDate.getMonth() + 1)).slice(-2) + '/'
                    + ('0' + dateDate.getDate()).slice(-2) + '/'
                    + dateDate.getFullYear();
            } else {
                return date;
            }
        },

        getMonthNumber: function (code) {
            var m = ['January','February','March','April','May','June','July',
                'August','September','October','November','December'];
            return m.indexOf(code) + 1;
        },

        getYear: function(date) {
            if (date) {
                var dateMatch = date.match(/\d{4}/);
                if (dateMatch) {
                    return dateMatch[0];
                }
                else {
                    return date;
                }
            } else {
                return date;
            }
        },

        getMonth: function ( date ) {
            if (date) {
                var dateDate = date;

                if( typeof dateDate == "string" ) {
                    dateDate = new Date(dateDate.replace(/-/g, '/').replace('T', ' '));
                }

                return dateDate.getMonth();
            } else {
                return null;
            }
        },

        getMonthName: function ( date ) {
            var m = ['January','February','March','April','May','June','July',
                'August','September','October','November','December'];

            if (date) {
                var dateDate = date;

                if( typeof dateDate == "string" ) {
                    dateDate = new Date(dateDate.replace(/-/g, '/').replace('T', ' '));
                }

                return m[dateDate.getMonth()];
            } else {
                return null;
            }
        },

        getQuarterCode: function ( date ) {
            if (date) {
                var dateDate = date;

                if( typeof dateDate == "string" ) {
                    dateDate = new Date(dateDate.replace(/-/g, '/').replace('T', ' '));
                }

                var month = dateDate.getMonth()+1;

                var quarter = null;
                $.each({'Q1':1,'Q2':4,'Q3':7,'Q4':10}, function(index,value){
                    if ( month >= value && month <= (value+2)   ) {
                        quarter = index;
                        return false;
                    }
                });
                return quarter;
            }else {
                return null;
            }
        },

        convertQuarterToDate: function ( quarter, year ) {
            var date = null;
            $.each({'Q1':'01-01','Q2':'04-01','Q3':'07-01','Q4':'10-01'}, function(index,value){
                if ( index == quarter ) {
                    date = GD.Utility.getUSFormat(year+'-'+value);
                    return false;
                }
            });
            return date;
        },

        escapeSelector: function(id) {
            return "#" + id.replace( /(:|\.|\[|\])/g, "\\$1" );
        },

        generateUUID: function() {
            var d = new Date().getTime();
            var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                var r = (d + Math.random()*16)%16 | 0;
                d = Math.floor(d/16);
                return (c=='x' ? r : (r&0x3|0x8)).toString(16);
            });
            return uuid;
        },

        // allows precision to be undefined
        numberFormat: function (number, decimals, dec_point, thousands_sep) {
            number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
            var n = !isFinite(+number) ? 0 : +number;
            var prec = (decimals == null) ? null : Math.abs(decimals);
            var sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep;
            var dec = (typeof dec_point === 'undefined') ? '.' : dec_point;
            var toFixedFix = function(n, prec) {
                var k = Math.pow(10, prec);
                return '' + (Math.round(n * k) / k).toFixed(prec);
            };
            // Fix for IE parseFloat(0.55).toFixed(0) = 0;
            var s = '';
            if ( prec === null ) {
                s = ('' + n).split('.');
            } else {
                s = toFixedFix(n, prec).split('.');
            }
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
        }
    };

})(typeof window === 'undefined' ? this : window, jQuery);