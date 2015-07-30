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
        throw new Error('OperatorFactory requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('OperatorFactory requires GD');
    }

    var GD = global.GD;

    global.GD.OperatorFactory = {
        operatorMap: {
            'equal': GD.EqualOperator,
            'equal.not': GD.EqualOperator,
            'greater.than': GD.GreaterThanOperator,
            'greater.or.equal': GD.GreaterThanOperator,
            'less.than': GD.LessThanOperator,
            'less.or.equal': GD.LessThanOperator,
            'range': GD.RangeOperator,
            'range.not': GD.RangeOperator,
            'empty': GD.EmptyOperator,
            'latest': GD.LatestOperator,
            'oldest': GD.OldestOperator,
            'empty.not': GD.EmptyOperator,
            'date:latest': GD.LatestOperator,
            'date:oldest': GD.OldestOperator,
            'date:previous': GD.PreviousOperator,
            'date:current': GD.CurrentOperator,
            'date:year:latest': GD.LatestOperator,
            'date:year:oldest': GD.OldestOperator,
            'date:year:previous': GD.PreviousOperator,
            'date:year:current': GD.CurrentOperator,
            'date:year.fiscal:current': GD.CurrentOperator,
            'date:month:latest': GD.LatestOperator,
            'date:month:oldest': GD.OldestOperator,
            'date:month:previous': GD.PreviousOperator,
            'date:month:current': GD.CurrentOperator,
            'date:quarter:latest': GD.LatestOperator,
            'date:quarter:oldest': GD.OldestOperator,
            'date:quarter:previous': GD.PreviousOperator,
            'date:quarter:current': GD.CurrentOperator,
            'date:quarter.fiscal:current': GD.CurrentOperator,
            'wildcard': GD.WildcardOperator,
            'wildcard.not': GD.WildcardOperator
        },

        operatorModifiers: {
            'equal.not': 'not',
            'greater.or.equal': 'or equal',
            'less.or.equal': 'or equal',
            'range.not': 'not',
            'empty.not': 'not',
            'latest': 'highest value',
            'oldest': 'lowest value',
            'date:year:latest': 'latest year',
            'date:year:oldest': 'oldest year',
            'date:year:previous': 'year',
            'date:year:current': 'year',
            'date:year.fiscal:current': 'fiscal year',
            'date:month:latest': 'latest month',
            'date:month:oldest': 'oldest month',
            'date:month:previous': 'month',
            'date:month:current': 'month',
            'date:quarter:latest': 'latest quarter',
            'date:quarter:oldest': 'oldest quarter',
            'date:quarter:previous': 'quarter',
            'date:quarter:current': 'quarter',
            'date:quarter.fiscal:current': 'fiscal quarter',
            'wildcard.not': 'not'
        },

        operators: {
            'generic': {
                "equal": "equal to",
                "equal.not": "not equal to",
                "greater.or.equal": "greater than or equal to",
                "greater.than": "greater than",
                "less.or.equal": "less than or equal to",
                "less.than": "less than",
                "range": "between",
                "range.dynamic": "between (dynamic)",
                "range.not": "not between",
                "empty": "is empty",
                "empty.not": "is not empty",
                "date:latest": "is latest date",
                "date:oldest": "is oldest date",
                "date:previous": "is previous date",
                "date:current": "is current date",
                "date:year:latest": "is latest year",
                "date:year:oldest": "is oldest year",
                "date:year:previous": "is previous year",
                "date:year:current": "is current year",
                "date:year.fiscal:current": "is current year",
                "date:month:latest": "is latest month",
                "date:month:oldest": "is oldest month",
                "date:month:previous": "is previous month",
                "date:month:current": "is current month",
                "date:quarter:latest": "is latest quarter",
                "date:quarter:oldest": "is oldest quarter",
                "date:quarter:previous": "is previous quarter",
                "date:quarter:current":"is current quarter",
                "date:quarter:current.fiscal":"is current quarter",
                "wildcard":"like",
                "wildcard.not":"not like",
                "regexp":"regexp",
                "regexp.not":"not regexp"
            },
            'date2': {
                "equal":"equal to",
                "equal.not": "not equal to",
                "empty":"empty",
                "empty.not":"not empty",
                "less.than":"before",
                "less.or.equal":"before or on",
                "greater.than":"after",
                "greater.or.equal":"after or on",
                "range":"between",
                "range.not":"not between",
                "date:latest":"is latest date",
                "date:oldest":"is oldest date",
                "date:previous":"is previous date",
                "date:current":"is current date"
            },
            'datetime': {
                "equal":"equal to",
                "equal.not": "not equal to",
                "empty":"empty",
                "empty.not":"not empty",
                "less.than":"before",
                "less.or.equal":"before or on",
                "greater.than":"after",
                "greater.or.equal":"after or on",
                "range":"between",
                "range.not":"not between",
                "date:latest":"is latest date and time",
                "date:oldest":"is oldest date and time",
                "date:previous":"is previous date and time",
                "date:current":"is current date"
            },
            'date:month': {
                "equal":"equal to",
                "equal.not":"not equal to",
                "empty":"empty",
                "empty.not":"not empty",
                "less.than":"before",
                "less.or.equal":"before or on",
                "greater.than":"after",
                "greater.or.equal":"after or on",
                "range":"between",
                "range.not":"not between",
                "date:month:latest":"is latest month",
                "date:month:oldest":"is oldest month",
                "date:month:previous":"is previous month",
                "date:month:current":"is current month"
            },
            'date:quarter': {
                "equal":"equal to",
                "equal.not":"not equal to",
                "empty":"empty",
                "empty.not":"not empty",
                "less.than":"before",
                "less.or.equal":"before or on",
                "greater.than":"after",
                "greater.or.equal":"after or on",
                "range":"between",
                "range.not":"not between",
                "date:quarter:latest":"is latest quarter",
                "date:quarter:oldest":"is oldest quarter",
                "date:quarter:previous":"is previous quarter",
                "date:quarter:current":"is current quarter"
            },
            'date:quarter.fiscal': {
                "equal":"equal to",
                "equal.not":"not equal to",
                "empty":"empty",
                "empty.not":"not empty",
                "less.than":"before",
                "less.or.equal":"before or on",
                "greater.than":"after",
                "greater.or.equal":"after or on",
                "range":"between",
                "range.not":"not between",
                "date:quarter:latest":"is latest quarter",
                "date:quarter:oldest":"is oldest quarter",
                "date:quarter:previous":"is previous quarter",
                'date:quarter.fiscal:current':"is current quarter"
            },
            'date2:year': {
                "equal":"equal to",
                "equal.not":"not equal to",
                "empty":"empty",
                "empty.not":"not empty",
                "less.than":"before",
                "less.or.equal":"before or in",
                "greater.than":"after",
                "greater.or.equal":"after or in",
                "range":"between",
                "range.not":"not between",
                "date:year:latest":"is latest year",
                "date:year:oldest":"is oldest year",
                "date:year:previous":"is previous year",
                "date:year:current":"is current year"
            },
            'date:year.fiscal': {
                "equal":"equal to",
                "equal.not":"not equal to",
                "empty":"empty",
                "empty.not":"not empty",
                "less.than":"before",
                "less.or.equal":"before or in",
                "greater.than":"after",
                "greater.or.equal":"after or in",
                "range":"between",
                "range.not":"not between",
                "date:year:latest":"is latest year",
                "date:year:oldest":"is oldest year",
                "date:year:previous":"is previous year",
                'date:year.fiscal:current':"is current year"
            },
            'time': {
                'equal': "equal to",
                'equal.not': "not equal to",
                'less.than': "before",
                'less.or.equal': "before or equal to",
                'greater.than': "after",
                'greater.or.equal': "after or equal to",
                'range': "between",
                'range.not': "not between",
                'empty': "is empty",
                'empty.not': "is not empty",
                'latest': "is latest time",
                'oldest': "is earliest time"
            },
            'numeric': {
                "equal":"equal to",
                "equal.not":"not equal to",
                "empty":"empty",
                "empty.not":"not empty",
                "less.than":"less than",
                "less.or.equal":"less than or equal to",
                "greater.than":"greater than",
                "greater.or.equal":"greater than or equal to",
                "range":"between",
                "range.not":"not between"
            },
            'string': {
                "equal":"equal to",
                "equal.not":"not equal to",
                "empty":"empty",
                "empty.not":"not empty",
                'wildcard': 'like',
                'wildcard.not': 'not like'
            },
            'URI' :{
                "equal":"equal to",
                "equal.not":"not equal to",
                "empty":"empty",
                "empty.not":"not empty",
                'wildcard': 'like',
                'wildcard.not': 'not like'
            },
            'integer': {
                "equal":"equal to",
                "equal.not":"not equal to",
                "empty":"empty",
                "empty.not":"not empty",
                "less.than":"less than",
                "less.or.equal":"less than or equal to",
                "greater.than":"greater than",
                "greater.or.equal":"greater than or equal to",
                "range":"between",
                "range.not":"not between",
                "latest": "highest value",
                "oldest": "lowest value"
            }
        },

        isOperator: function(o) {
            return $.inArray(o, Object.keys(GD.OperatorFactory.operatorMap)) != -1;
        },

        getOperator: function(operator, obj) {
            var p = GD.OperatorFactory.operatorMap[operator];
            return p ? new p(obj, { mod: GD.OperatorFactory.operatorModifiers[operator] }) : null;
        },

        getOperators: function(type) {
            return GD.Filter.isNumeric(type) ? GD.OperatorFactory.operators['numeric'] : GD.OperatorFactory.operators[type];
        },

        isParameterOperator: function(operator) {
            return $.inArray(operator, Object.keys(GD.OperatorFactory.operatorMap)) != -1 && operator.search(/latest|empty|oldest|previous|current/) == -1;
        },

        isRangeOperator: function(operator) {
            return $.inArray(operator, Object.keys(GD.OperatorFactory.operatorMap)) != -1 && operator.search(/range/) !== -1;
        },

        isWildcardOperator: function(operator) {
            return operator && operator.indexOf('wildcard') != -1;
        }
    };

})(typeof window === 'undefined' ? this : window, jQuery);