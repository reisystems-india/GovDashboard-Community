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
        throw new Error('DateFormat requires GD');
    }

    if ( typeof global.GD.Util === 'undefined' ) {
        global.GD.Util = {};
    }

    (function($,Highcharts) {

        if ( typeof $ === 'undefined' ) {
            throw new Error('DateFormat requires jQuery');
        }

        (function (Date, undefined) {
            var origParse = Date.parse, numericKeys = [ 1, 4, 5, 6, 7, 10, 11 ];
            Date.parse = function (date) {
                var timestamp, struct, minutesOffset = 0;

                // ES5 §15.9.4.2 states that the string should attempt to be parsed as a Date Time String Format string
                // before falling back to any implementation-specific date parsing, so that’s what we do, even if native
                // implementations could be faster
                //              1 YYYY                2 MM       3 DD           4 HH    5 mm       6 ss        7 msec        8 Z 9 ±    10 tzHH    11 tzmm
                if ((struct = /^(\d{4}|[+\-]\d{6})(?:-(\d{2})(?:-(\d{2}))?)?(?:T(\d{2}):(\d{2})(?::(\d{2})(?:\.(\d{3}))?)?(?:(Z)|([+\-])(\d{2})(?::(\d{2}))?)?)?/.exec(date))) {
                    // avoid NaN timestamps caused by “undefined” values being passed to Date.UTC
                    for (var i = 0, k; (k = numericKeys[i]); ++i) {
                        struct[k] = +struct[k] || 0;
                    }

                    // allow undefined days and months
                    struct[2] = (+struct[2] || 1) - 1;
                    struct[3] = +struct[3] || 1;

                    if (struct[8] !== 'Z' && struct[9] !== undefined) {
                        minutesOffset = struct[10] * 60 + struct[11];

                        if (struct[9] === '+') {
                            minutesOffset = 0 - minutesOffset;
                        }
                    }

                    timestamp = Date.UTC(struct[1], struct[2], struct[3], struct[4], struct[5] + minutesOffset, struct[6], struct[7]);
                }
                else {
                    timestamp = origParse ? origParse(date) : NaN;
                }

                return timestamp;
            };
        }(Date));

        var DateFormat = {
            numberOfQuarters: function ( a, b ) {
                var quarters = ( b.getFullYear() - a.getFullYear() ) * 4;
                quarters -= Math.floor(a.getMonth() / 3);
                quarters += Math.floor(b.getMonth() / 3);
                return quarters;
            },

            numberOfMonths: function ( a, b ) {
                var months = Math.floor( b.getFullYear() - a.getFullYear() ) * 12;
                months -= Math.floor(a.getMonth());
                months += Math.floor(b.getMonth());
                return months;
            },

            isLeapYear:function ( year ) {
                return ( (year % 4 == 0) && (year % 100 != 0) ) || (year % 400 == 0);
            },

            //  Get instance of date object from incoming date string
            //  If incoming date string is invalid, return the current date
            getDateInstance: function ( date ) {
                if (this.validateDate(date))
                    return new Date(date);

                return new Date();
            },

            //  We have to validate the dates ourselves to ensure incoming date will work in Chrome, I.E., and FF
            //  Expecting mm/dd/yyyy
            validateDate: function ( dateString ) {
                var dateRegex = /^\d{1,2}\/\d{1,2}\/\d{4}$/;
                if (dateString.match(dateRegex)) {
                    var pieces = dateString.split('/');
                    if (pieces[0] < 13) {
                        if (pieces[0] % 2 === 1) {
                            if (pieces[1] < 32)
                                return true;
                        }
                        else if (pieces[0] != '2' && pieces[0] != '02') {
                            if (pieces[1] < 31)
                                return true;
                        }
                        else {
                            //  Calculate leap year
                            var leap = this.isLeapYear(pieces[2]);
                            if ((leap && pieces[1] <= 29) || (!leap && pieces[1] <= 28))
                                return true;
                        }
                    }
                }

                return false;
            },

            getUSFormat: function ( date ) {
                if ( typeof date != "undefined" ) {
                    var dateDate = date;
                    if ( typeof dateDate == "string" ) {
                        dateDate = this.getDateInstance(dateDate.replace(/-/g, '/').replace('T', ' '));
                    }
                    var dateValue = ('0' + (dateDate.getMonth() + 1)).slice(-2) + '/'
                                + ('0' + dateDate.getDate()).slice(-2) + '/'
                                + dateDate.getFullYear();
                    return dateValue;
                } else {
                    return date;
                }
            },

            getYear: function ( date ) {
                if ( date ) {
                    var dateMatch = date.match(/\d{4}/);
                    if ( dateMatch ) {
                        return dateMatch[0];
                    } else {
                        return date;
                    }
                } else {
                    return date;
                }
            },

            getMonth: function ( date ) {
                if ( date ) {
                    var dateDate = date;
                    if ( typeof dateDate == "string" ) {
                        dateDate = new Date(dateDate.replace(/-/g, '/').replace('T', ' '));
                    }
                    return dateDate.getMonth();
                } else {
                    return null;
                }
            },

            getMonthCode: function ( date ) {
                if ( date ) {
                    var m = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                    var dateDate = date;
                    if ( typeof dateDate == "string" ) {
                        dateDate = new Date(dateDate.replace(/-/g, '/').replace('T', ' '));
                    }
                    return m[dateDate.getMonth()];
                } else {
                    return null;
                }
            },

            getMonthValue: function ( month ) {
                var _value = null;
                var m = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                $.each(m,function(index,value){
                    if (value === month) {
                        _value = index + 1;
                        return false;
                    }
                });

                return _value;
            },

            getQuarterCode: function ( date ) {

                if ( date ) {
                    var dateDate = date;
                    if ( typeof dateDate == "string" ) {
                        dateDate = new Date(dateDate.replace(/-/g, '/').replace('T', ' '));
                    }
                    var month = dateDate.getMonth()+1;
                    var quarter = null;
                    $.each({'Q1':1,'Q2':4,'Q3':7,'Q4':10},function(index,value){
                        if ( month >= value && month <= (value+2) ) {
                            quarter = index;
                        }
                    });
                    return quarter;
                } else {
                    return null;
                }
            },

            convertQuarterToDate: function ( quarter, year ) {
                var date = null;

                $.each({'Q1':'01-01','Q2':'04-01','Q3':'07-01','Q4':'10-01'},function(index,value){
                    if ( index == quarter ) {
                        date = DateFormat.getUSFormat(value+'-'+year);
                    }
                });
                return date;
            },

            parseISO8601: function(str) {
                return new Date(Date.parse(str));
            },

            getUSShortDateTime: function ( date ) {

                var curr_date = date.getDate();

                var curr_month = date.getMonth()+1;

                var curr_year = date.getFullYear();

                var a_p = "";
                var curr_hour = date.getHours();
                if (curr_hour < 12) {
                    a_p = "AM";
                } else {
                    a_p = "PM";
                }
                if (curr_hour == 0) {
                    curr_hour = 12;
                }
                if (curr_hour > 12) {
                    curr_hour = curr_hour - 12;
                }

                var curr_min = date.getMinutes();

                curr_min = curr_min + "";

                if (curr_min.length == 1) {
                    curr_min = "0" + curr_min;
                }

                return curr_month + '/' + curr_date + '/' + curr_year + ' ' + curr_hour + ':' + curr_min + ' ' +a_p;
            },

            getUSDateTime: function ( date ) {

                var curr_date = date.getDate();
                curr_date = curr_date.toString();
                if (curr_date.length == 1) {
                    curr_date = "0" + curr_date;
                }

                var curr_month = date.getMonth()+1;
                curr_month = curr_month.toString();
                if (curr_month.length == 1) {
                    curr_month = "0" + curr_month;
                }

                var curr_year = date.getFullYear();

                var a_p = "";
                var curr_hour = date.getHours();
                if (curr_hour < 12) {
                    a_p = "AM";
                } else {
                    a_p = "PM";
                }
                if (curr_hour == 0) {
                    curr_hour = 12;
                }
                if (curr_hour > 12) {
                    curr_hour = curr_hour - 12;
                }

                curr_hour = curr_hour.toString();
                if (curr_hour.length == 1) {
                    curr_hour = "0" + curr_hour;
                }

                var curr_min = date.getMinutes();
                curr_min = curr_min.toString();
                if (curr_min.length == 1) {
                    curr_min = "0" + curr_min;
                }

                var curr_sec = date.getSeconds();
                curr_sec = curr_sec.toString();
                if (curr_sec.length == 1) {
                    curr_sec = "0" + curr_sec;
                }

                return curr_month + '/' + curr_date + '/' + curr_year + ' ' + curr_hour + ':' + curr_min + ':' + curr_sec + ' ' +a_p;
            },

            getQuarters: function() {
                return ["Q1", "Q2", "Q3", "Q4"];
            },

            getMonthsNames: function() {
                return ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            },

            getMonths: function() {
                return ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            }
        };

        global.GD.Util.DateFormat = DateFormat;
    })(typeof global.GD_jQuery != 'undefined' ? global.GD_jQuery : jQuery, typeof global.GD_Highcharts != 'undefined' ? global.GD_Highcharts : (typeof Highcharts != 'undefined' ? Highcharts : undefined));
})(typeof window === 'undefined' ? this : window);
