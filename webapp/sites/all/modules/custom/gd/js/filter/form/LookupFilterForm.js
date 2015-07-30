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
        throw new Error('LookupFilterForm requires jQuery');
    }

    if (typeof global.GD === 'undefined') {
        throw new Error('LookupFilterForm requires GD');
    }

    var GD = global.GD;

    global.GD.LookupFilterForm = GD.FilterForm.extend({
        listForm:null,
        secondFormInput:null,
        listOptions:null,
        selected: null,

        init:function (object, container, options) {
            this._super(object, container, options);
            this.listForm = null;
            this.secondFormInput = null;

            this.listOptions = null;
            if (options) {
                this.listOptions = options['options'];
            }

            if (object) {
                var val = object.getValue();
                if (val) {
                    if ($.isArray(val)) {
                        this.selected = val;
                    } else {
                        this.selected = [val];
                    }
                } else {
                    this.selected = [];
                }
            }
        },

        validateValues:function (messages) {
            if (GD.OperatorFactory.isParameterOperator(this.getOperator())) {
                if (!GD.OperatorFactory.isWildcardOperator(this.getOperator())) {
                    var vals = this.getValue();
                    if (vals.length == 0) {
                        messages.push('Please select at least one string value.');
                        this.getForm().toggleClass('has-error', true);
                    }
                } else {
                    if (this.getValue() == '') {
                        messages.push('Please enter a filter value.');
                        this.getSecondForm().toggleClass('has-error', true);
                    }
                }
            }
        },

        getValue:function () {
            return GD.OperatorFactory.isWildcardOperator(this.getOperator()) ? this.getSecondFormInput().val() : this.getSelectedValues();
        },

        lookupValues:function (q) {
            if (this.object && this.object.getDatasetNames()) {
                this.listOptions = {};
                var _this = this;
                var names = this.object.getDatasetNames();
                var length = names.length;
                var index = 0;
                for (var i = 0; i < length; i++) {
                    var c = function(data) {
                        _this.parseData(data, _this.listOptions);
                        if (++index >= length) {
                            _this.getListForm().renderOptions(_this.listOptions);
                            if (_this.getOperator()) {
                                if (!GD.OperatorFactory.isWildcardOperator(_this.getOperator())) {
                                    _this.getListForm().setOptions(_this.getSelectedValues());
                                }
                            }
                        }
                    };

                    this.lookup(c, names[i], this.object.getColumnName(i), q);
                }
            }
        },

        lookup: function(callback, name, column, q, offset, limit) {
            var d = {
                c:column,
                g:false,
                s:column,
                query: q ? ('*' + q + '*') : '*'
            };
            if (offset) {
                d['o'] = offset;
            }

            if (limit) {
                d['l'] = limit;
            }
            $.ajax({
                url:'/api/dataset/' + name + '/lookup.json',
                data:d,
                success:function (data) {
                    if (callback) {
                        callback(data);
                    }
                }
            });
        },

        parseData:function (raw, list) {
            if (!list) {
                list = {};
            }
            for (var i = 0; i < raw.length; i++) {
                if (!list[raw[i]['name']]) {
                    list[raw[i]['name']] = {val:raw[i]['name'], text:raw[i]['name']};
                }
            }

            return list;
        },

        addSelectedValue: function(val) {
            this.selected.push(val);
        },

        removeSelectedValue: function(index) {
            this.selected.splice(index, 1);
        },

        getSelectedValues: function() {
            return this.selected;
        },

        getListForm:function () {
            if (!this.listForm) {
                var _this = this;
                this.end = false;
                var listOptions = {
                    'callback': function(chkbox) {
                        var val = $(chkbox).attr('value');
                        var i = $.inArray(val, _this.getSelectedValues());
                        if (i === -1) {
                            _this.addSelectedValue(val);
                        } else {
                            _this.removeSelectedValue(i);
                        }
                    },
                    'search':true,
                    'select':true,
                    'ajax': {
                        'callback': function(val) {
                            _this.lookupValues(val);
                            _this.end = false;
                        }
                    },
                    'infinite': {
                        'callback': function(pkg) {
                            _this.infiniteScrollingCallback(pkg);
                        }
                    }
                };
                this.listForm = new GD.ListView(this.listOptions, null, listOptions);
                this.listForm.setHeight(this.getListHeight(), this.getListViewHeight());
            }

            return this.listForm;
        },

        getListHeight: function() {
            return 200;
        },

        getListViewHeight: function() {
            return 137;
        },

        infiniteScrollingCallback: function(pkg) {
            var _this = this;
            var names = this.object.getDatasetNames();
            var length = names.length;
            var index = 0;
            for (var i = 0; i < length; i++) {
                var newOptions = {};
                var c = function(data) {
                    _this.parseData(data, newOptions);
                    if (++index >= length && !end) {
                        if (pkg['callback']) {
                            pkg['callback'](newOptions);
                        }
                        end = data.length < 100;
                    }

                    if (!GD.OperatorFactory.isWildcardOperator(_this.getOperator())) {
                        _this.getListForm().setOptions(_this.getSelectedValues());
                    }
                };

                _this.lookup(c, names[i], _this.object.getColumnName(i), pkg['query'], pkg['counter'] * 100);
            }
        },

        getForm:function () {
            if (!this.form) {
                this.form = $('<div class="bldr-flt-frm-val form-group"></div>');
                var l = $('<label class="control-label">Value: </label>');
                l.attr('for', this.getListForm().searchText.attr('id'));
                this.form.append(l, this.getListForm().render());
                this.getListForm().attachEventHandlers();
            }

            return this.form;
        },

        operatorChanged:function (operator) {
            if (GD.OperatorFactory.isWildcardOperator(operator)) {
                this.hideForm();
                this.showSecondForm();
            } else if (GD.OperatorFactory.isParameterOperator(operator)) {
                this.showForm();
                this.hideSecondForm();
            } else {
                this.hideForm();
                this.hideSecondForm();
            }
        },

        getSecondFormInput: function() {
            if (!this.secondFormInput) {
                this.secondFormInput = $('<input tabindex="100" type="text" class="form-control" placeholder="Enter search criteria..."/>');
                this.secondFormInput.uniqueId();

                if (this.object.getOperator()) {
                    if (GD.OperatorFactory.isParameterOperator(this.object.getOperator())) {
                        if (GD.OperatorFactory.isWildcardOperator(this.object.getOperator())) {
                            this.secondFormInput.val(this.object.getValue());
                        }
                    }
                }
            }

            return this.secondFormInput;
        },

        //  String filters do not have a range value so we use second form as wildcard form instead
        getSecondForm:function () {
            if (!this.secondForm) {
                this.secondForm = $('<div class="bldr-flt-frm-val form-group"></div>');
                var l  = $('<label class="control-label">Value: </label>');
                l.attr('for', this.getSecondFormInput().attr('id'));
                this.secondForm.append(l, this.getSecondFormInput(),
                    '<span>Use ? to represent single character. Use * to represent a series of characters.</span>');
            }

            return this.secondForm;
        },

        initForms: function() {
            this.hideForm();
            this.hideSecondForm();
            if (this.object.getOperator()) {
                if (GD.OperatorFactory.isParameterOperator(this.object.getOperator())) {
                    if (GD.OperatorFactory.isWildcardOperator(this.object.getOperator())) {
                        this.showSecondForm();
                    } else {
                        this.showForm();
                    }
                }
            }
        },

        showForm: function() {
            this.getForm().show();
            if (!this.listOptions) {
                this.lookupValues();
            }
        },

        show: function() {
            this.getFormContainer().show();
            if (this.getOperator()) {
                if (GD.OperatorFactory.isParameterOperator(this.getOperator())) {
                    if (GD.OperatorFactory.isWildcardOperator(this.getOperator())) {
                        this.showSecondForm();
                    } else {
                        this.showForm();
                    }
                }
            }
        },

        reinitialize: function() {
            this.getListForm().attachEventHandlers();
            this._super();
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);
