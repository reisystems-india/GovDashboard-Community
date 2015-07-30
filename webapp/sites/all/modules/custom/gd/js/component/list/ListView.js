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
        throw new Error('ListView requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ListView requires GD');
    }

    var GD = global.GD;

    global.GD.ListView = GD.View.extend({
        listContainer: null,
        listView: null,
        loadingScreen: null,
        searchText: null,
        selectCallback: null,
        listItems: null,
        emptyMessage: null,

        init: function(object, container, options) {
            this._super(object,container,options);
            this.listView = $('<div class="lst-vw"></div>');
            this.listContainer = $('<div class="lst-cntr"></div>');
            this.loadingScreen = null;
            this.searchText = null;
            this.selectCallback = null;
            this.listItems = [];
            this.emptyMessage = null;

            if (!this.options) {
                this.options = {};
            } else {
                this.selectCallback = this.options['callback'];
            }

            this.initListView();
        },

        initListView: function() {
            if (this.options['search']) {
                var container = $('<div></div>');
                container.append(this.getSearchText());
                this.listContainer.append(container);
            }

            if (this.object) {
                this.renderOptions(this.object);
            } else {
                if (this.getSearchText() && !this.getSearchText().val()) {
                    this.getSearchText().prop('disabled', true);
                }
                this.listView.append(this.getLoadingScreen());
            }
            this.listContainer.append(this.listView);
        },

        getSearchText: function() {
            if (!this.searchText) {
                this.searchText = $('<input tabindex="100" class="form-control" placeholder="Enter search term" name="search" type="text"/>');
                this.searchText.uniqueId();
            }

            return this.searchText;
        },

        getLoadingScreen: function() {
            if (!this.loadingScreen) {
                this.loadingScreen = $('<div class="ldng"></div>');
            }

            return this.loadingScreen;
        },

        showAllItems: function() {
            for(var key in this.listItems) {
                this.showItem(key);
            }
        },

        showItem: function(key) {
            this.listItems[key].show();
        },

        //  Optimization: Create binary search tree for faster filtering
        filterList: function(value) {
            for(var key in this.listItems) {
                this.compareItem(key, value);
            }
        },

        compareItem: function(key, value) {
            var item = this.listItems[key];
            if (item.text().toLowerCase().indexOf(value.toLowerCase()) === -1) {
                item.hide();
            } else {
                item.show();
            }
        },

        setHeight: function(height, listViewHeight) {
            this.listContainer.css('height', height);
            if (this.options['search']) {
                lvHeight = listViewHeight?listViewHeight:(height - 33);
                this.listView.css('height', lvHeight);
            } else {
                this.listView.css('height', height);
            }
        },

        getEmptyMessage: function() {
            if (!this.emptyMessage) {
                this.emptyMessage = $('<h4 style="text-align: center;">No items to show</h4>');
            }

            return this.emptyMessage;
        },

        addOptions: function(options) {
            var index = 0;
            if (this.options['select']) {
                this.checkItems = [];
            }
            for(var key in options) {
                this.renderOption(options, key, index++);
            }
        },

        renderOptions: function(options) {
            if (this.getSearchText()) {
                this.getSearchText().prop('disabled', false);
            }
            this.listView.reset = true;
            this.listView.empty();
            this.listOptions = options;
            if (!options || ($.isArray(options) && options.length == 0) || ($.isPlainObject(options) && Object.keys(options).length == 0)) {
                this.listView.append(this.getEmptyMessage());
            } else {
                this.addOptions(options);
            }
        },

        renderOption: function(options, key, index) {
            var v = options[key];
            var container = $('<div class="lst-itm lst-itm-' + (index % 2 == 0 ? 'even' : 'odd') + '"></div>');
            var label;
            var _this = this;
            if (this.options['select']) {
                label = $('<label></label>');
                container.addClass('checkbox');
                var check = $('<input tabindex="100" value="' + v['val'] + '" name="' + v['text'] + '" type="checkbox"/>');
                check.click(function() {
                    _this.optionSelected(this);
                });
                this.checkItems.push(check);
                label.append(check);
            } else {
                label = $('<span value="' + v['val'] + '"></span>');
                label.click(function() {
                    _this.optionSelected(this);
                });
            }

            label.append(v['text']);
            container.attr('value', v['val']);
            container.append(label);

            this.listItems.push(container);
            this.listView.append(container);
        },

        optionSelected: function(chkbox) {
            if (this.selectCallback) {
                this.selectCallback(chkbox);
            }
        },

        render: function() {
            if (this.container) {
                this.container.append(this.listContainer);
            }
            this.attachEventHandlers();

            return this.listContainer;
        },

        setOptions: function(options) {
            if (!options) return;

            if (this.checkItems) {
                //  Cast incoming options to string since inArray doesn't match different types
                for(var o in options) {
                    options[o] = options[o]+"";
                }

                for(var i = 0; i < this.checkItems.length; i++) {
                    this.setOption(this.checkItems[i], $.inArray(this.checkItems[i].val(), options) !== -1);
                }
            } else {
                this.listView.find('div.lst-itm[value="' + options + '"]').addClass('lst-itm-slc');
            }
        },

        setOption: function(elem, checked) {
            $(elem).prop('checked', checked);
        },

        getSelected: function() {
            return $.map(this.listView.find('input:checked'), function(elem) {
                return $(elem).val();
            });
        },

        attachEventHandlers: function() {
            var _this = this;
            this.getSearchText().off('keyup');
            if (this.options['ajax']) {
                var timer = this.options['searchTimer'] ? this.options['searchTimer'] : 2000;
                var callback = this.options['ajax']['callback'];
                var to = null;
                var loading = false;
                this.getSearchText().keyup(function() {
                    if (!loading) {
                        _this.listView.empty();
                        _this.listView.append(_this.getLoadingScreen());
                        loading = true;
                    }
                    var st = this;
                    if (to) {
                        clearTimeout(to);
                    }
                    to = setTimeout(function() {
                        if (callback) {
                            callback($(st).val());
                            loading = false;
                        }
                    }, timer);
                });
            } else {
                this.getSearchText().keyup(function() {
                    if ($(this).val().length > 0) {
                        _this.filterList($(this).val());
                    } else {
                        _this.showAllItems();
                    }
                });
            }

            if (this.options['select']) {
                this.listView.find('div.lst-itm input').off('click').click(function() {
                    _this.optionSelected(this);
                });
            } else {
                this.listView.find('div.lst-itm span').off('click').click(function() {
                    _this.optionSelected(this);
                });
            }

            this.listView.off('scroll');
            if (this.options['infinite'] && this.listOptions) {
                var iCallback = this.options['infinite']['callback'];
                var getMore = false;
                var counter = 1;
                if (Object.keys(this.listOptions).length >= 100) {
                    this.listView.scroll(function() {
                        if (_this.listView.reset) {
                            counter = 1;
                            _this.listView.reset = false;
                        }
                        //  Trigger if scroll is at 2/3
                        if($(this).scrollTop() && $(this).scrollTop() + $(this).innerHeight() >= this.scrollHeight - (this.scrollHeight / 3)) {
                            if (!getMore) {
                                getMore = true;
                                var $this = $(this);
                                var scroll = $(this).scrollTop();
                                var pkg = {
                                    'counter': counter,
                                    'callback' : function(options) {
                                        counter++;
                                        _this.addOptions(options);
                                        $this.scrollTop(scroll);
                                        getMore = false;
                                    }
                                };

                                if (_this.getSearchText()) {
                                    pkg['query'] = _this.getSearchText().val();
                                }
                                iCallback(pkg);
                            }
                        }

                    });
                }
            }

        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);
