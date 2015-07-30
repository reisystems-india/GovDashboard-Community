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
        throw new Error('FilterFormFactory requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('FilterFormFactory requires GD');
    }

    var GD = global.GD;

    global.GD.FilterFormFactory = {
        forms: {
            'numeric': GD.FilterForm,
            'integer': GD.FilterForm,
            //'integer': GD.LookupFilterForm,
            'date2': GD.DateFilterForm,
            'datetime': GD.DateTimeFilterForm,
            'date2:year': GD.FilterForm,
            'date:year.fiscal': GD.FilterForm,
            'time': GD.TimeFilterForm,
            'string': GD.LookupFilterForm,
            'date:month': GD.DateMonthFilterForm,
            'date:quarter': GD.DateQuarterFilterForm,
            'date:quarter.fiscal': GD.DateQuarterFilterForm,
            'URI': GD.LookupFilterForm
        },

        getForm: function(filter, container, options) {
            if(filter['type'] && typeof filter['type'] === "object"){
                var type = filter['type'].applicationType;
            }else{
                var type = filter['type'] || "numeric";
            }
            var operators = GD.OperatorFactory.getOperators(type);
            if (options) {
                if (options['operators']) {
                    for(var key in operators) {
                        options['operators'][key] = operators[key];
                    }
                } else {
                    options['operators'] = operators;
                }
            }

            if (filter['type'] == 'date:month' || filter['type'] == 'date:quarter') {
                if (!options) {
                    options = {};
                }
                options['placeholder'] = 'Year';
            }
            //temporary fix need to fix at source...(removing different types of filter[type]) for bug #GOVDB-3038
            var filterType;
            if(filter['type'] && typeof filter['type'] === "object"){
                filterType = filter['type']['applicationType'];
            }else{
                filterType = filter['type'] || "numeric";
            }
            return GD.Filter.isNumeric(filterType) ?
                new GD.FilterFormFactory.forms['numeric'](filter, container, options) :
                new GD.FilterFormFactory.forms[filterType](filter, container, options);
        }
    };

})(typeof window === 'undefined' ? this : window, jQuery);
