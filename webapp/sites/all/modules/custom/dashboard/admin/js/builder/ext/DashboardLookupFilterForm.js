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

(function (global, $, undefined) {

    if (typeof $ === 'undefined') {
        throw new Error('DashboardLookupFilterForm requires jQuery');
    }

    if (typeof global.GD === 'undefined') {
        throw new Error('DashboardLookupFilterForm requires GD');
    }

    var GD = global.GD;

    global.GD.DashboardLookupFilterForm = GD.LookupFilterForm.extend({

        getListHeight: function() {
            return 200;
        },

        getListViewHeight: function() {
            return 175;
        },

        parseData:function (raw, list) {
            if (!list) {
                list = {};
            }
            for (var i = 0; i < raw.length; i++) {
                if (!list[raw[i]]) {
                    list[raw[i]] = {val:raw[i], text:raw[i]};
                }
            }

            return list;
        },

        lookupValues:function (q) {
            if (this.object) {
                this.listOptions = {};
                var _this = this;
                var c = function(data) {
                    _this.parseData(data, _this.listOptions);
                    _this.getListForm().renderOptions(_this.listOptions);
                    if (_this.getOperator()) {
                        if (!GD.OperatorFactory.isWildcardOperator(_this.getOperator())) {
                            _this.getListForm().setOptions(_this.getSelectedValues());
                        }
                    }
                };

                this.lookup(c, this.object.getName(), q);
            }
        },

        lookup: function(callback, filter, q, offset, limit) {
            var d = {
                dashboard: JSON.stringify(GovdashDashboardBuilder.getDashboard().getConfig()),
                filter:filter,
                query: q ? ('*' + q + '*') : '*'
            };
            if (offset) {
                d['offset'] = offset;
            }

            if (limit) {
                d['limit'] = limit;
            } else {
                d['limit'] = 100;
            }

            if (this.options && this.options['filters']) {
                d['appliedFilters'] = [];
                $.each(this.options['filters'], function(i, f) {
                    if (f['operator']) {
                        d['appliedFilters'].push({value: f['value'], name: f['name'], operator: f['operator'].getName()});
                    }
                });
            }

            $.ajax({
                url: '/api/dashboard/lookup.json',
                type: 'POST',
                data: d,
                success: function (raw) {
                    if (callback) {
                        callback(raw['data']);
                    }
                }
            });
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);
