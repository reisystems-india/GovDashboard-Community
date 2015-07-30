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
        throw new Error('ReportLookupFilterForm requires jQuery');
    }

    if (typeof global.GD === 'undefined') {
        throw new Error('ReportLookupFilterForm requires GD');
    }

    var GD = global.GD;

    global.GD.ReportLookupFilterForm = GD.LookupFilterForm.extend({
        init:function (object, container, options) {
            this._super(object, container, options);
            if (object) {
                var val = object.getValue();
                if (val) {
                    if ($.isArray(val) && val.length) {
                        if ($.isArray(val[0])) {
                            this.selected = val[0];
                        } else {
                            this.selected = val;
                        }
                    } else {
                        this.selected = val;
                    }
                } else {
                    this.selected = [];
                }
            }
        },

        parseData:function (raw, list) {
            if (!list) {
                list = {};
            }
            if (raw) {
                for (var i = 0; i < raw.length; i++) {
                    if (!list[raw[i]]) {
                        list[raw[i]] = {val:raw[i], text:raw[i]};
                    }
                }
            }

            return list;
        },

        infiniteScrollingCallback: function(pkg, end) {
            var _this = this;
            var newOptions = {};
            var c = function(data) {
                _this.parseData(data, newOptions);
                if (!_this.end) {
                    if (pkg['callback']) {
                        pkg['callback'](newOptions);
                    }
                    _this.end = data.length < 100;
                }

                if (!GD.OperatorFactory.isWildcardOperator(_this.getOperator())) {
                    _this.getListForm().setOptions(_this.getSelectedValues());
                }
            };

            _this.lookup(c, _this.object.getColumnName(), pkg['query'], pkg['counter'] * 100);
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

                this.lookup(c, this.object.getColumnName(), q);
            }
        },

        lookup: function(callback, column, q, offset, limit) {
            var d = {
                report: GovdashReportBuilder.getReport().getConfig(),
                column:column,
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
                url: '/api/report/lookup.json',
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
