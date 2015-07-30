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
        throw new Error('TreeView requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('TreeView requires GD');
    }

    var GD = global.GD;

    global.GD.TreeView = GD.View.extend({
        formContainer: null,
        searchInput: null,
        itemList: null,
        treeContainer: null,
        treeList: null,
        plugins: null,
        initialized: false,
        selected: null,
        selectedCallback: null,
        valueMap: null,
        queue: null,
        searchThreshold: 3,

        init: function(object, container, options) {
            this._super(object, container, options);

            if (!options) {
                this.options = {};
            }

            this.initVariables();
        },

        initVariables: function() {
            this.formContainer = null;
            this.searchInput = null;
            this.treeContainer = null;
            this.itemList = null;
            this.treeList = null;
            this.plugins = null;
            this.initialized = false;
            this.selected = null;
            this.selectedCallback = null;
            this.valueMap = null;
            this.searchThreshold = this.options['searchLimit'] ? this.options['searchLimit'] : 3;
        },

        getSearchInput: function() {
            if (!this.searchInput) {
                this.searchInput = $('<input tabindex="3000" class="form-control" type="text" name="Tree Search"/>');
                this.searchInput.attr('placeholder',  'Type at least ' + this.searchThreshold + ' characters to search');
            }

            return this.searchInput;
        },

        getHeight: function() {
            return this.options ? (this.options['height'] ? this.options['height'] : 'auto') : 'auto';
        },

        getTreeContainer: function() {
            if (!this.treeContainer) {
                this.treeContainer = $('<div class="bldr-tree-vw-cntnr"></div>');
                this.treeContainer.css('height', this.getHeight());
                this.treeContainer.css('overflow', 'auto');
                this.treeContainer.append('<div class="bldr-ldng"></div>');
            }

            return this.treeContainer;
        },

        getTreePlugins: function() {
            if (!this.plugins) {
                this.plugins = [ "wholerow" ];

                if (this.options) {
                    if (this.options['search']) {
                        this.plugins.push("search");
                    }

                    if (this.options['types']) {
                        this.plugins.push("types");
                    }

                    if (this.options['checkbox']) {
                        this.plugins.push("checkbox");
                    }
                }
            }

            return this.plugins;
        },

        getItems:function() {
            return this.itemList;
        },

        setItems: function(items) {
            this.itemList = [];
            this.initialized = false;
            var _this = this;
            var parseList = function(it, list) {
                for(var key = 0; key < it.length; key++) {
                    var isDisabled = it[key]['type'] == 'disabled';
                    var item = {
                        id: GD.Utility.generateUUID(),
                        text: it[key]['text'],
                        state: {
                            disabled: isDisabled
                        },
                        li_attr: {
                            "class": (isDisabled ? 'bldr-tree-vw-disabled' : ''),
                            "val": it[key]['val'],
                            "data-jstree": '{"type":"' + it[key]['type'] + '"}'
                        },
                        type: it[key]['type']
                    };
                    _this.getIdLookup()[it[key]['val']] = item['id'];

                    var children = [];
                    if (it[key]['children']) {
                        parseList(it[key]['children'], children);
                    }
                    item['children'] = children;
                    list.push(item);
                }
            };
            parseList(items, this.itemList);
        },

        getFormContainer: function() {
            if (!this.formContainer) {
                this.formContainer = $('<div class="bldr-tree-vw"></div>');

                if (this.options && this.options['search']) {
                    this.formContainer.append(this.getSearchInput());
                }

                this.formContainer.append(this.getTreeContainer());
            }

            return this.formContainer;
        },

        initTree: function(loaded) {
            if (this.initialized) return;

            var _this = this;
            var options = {
                "core" : {
                    "check_callback": true,
                    "animation" : false,
                    "expand_selected_onload": false,
                    "themes": {},
                    "data": this.getItems()
                },
                "plugins" : this.getTreePlugins()
            };

            if (this.options) {
                if (typeof this.options['icons'] != 'undefined') {
                    options['core']['themes']['icons'] = this.options['icons'];
                }

                if (this.options['search']) {
                    options['search'] = {
                        "show_only_matches": true
                    };
                    var to = false;
                    this.getSearchInput().keyup(function () {
                        var v = _this.getSearchInput().val();
                        if (v.length && v.length < _this.searchThreshold) v = "";
                        if(to) { clearTimeout(to); }
                        to = setTimeout(function () {
                            _this.getTreeContainer().jstree(true).search(v);
                        }, 250);
                    });
                }

                if (this.options['checkbox']) {
                    options['checkbox'] = {
                        "keep_selected_style": false,
                        "three_state": false
                    };
                }

                if (this.options['types']) {
                    options['types'] = this.options['types'];
                }

                if (typeof this.options['multiple'] != 'undefined') {
                    options['core']['multiple'] = this.options['multiple'];
                }
            }

            this.getTreeContainer().on('ready.jstree', function() {
                _this.initialized = true;
                _this.clearQueue();
                if (loaded) {
                    loaded(_this.getSelected());
                }
            });
            this.getTreeContainer().on('loaded.jstree after_open.jstree', function() {
                _this.getTreeContainer().find('a').removeAttr('href');
                if (loaded) {
                    loaded(_this.getSelected());
                }
            });
            var t = this.getTreeContainer().jstree(options);

            _this.getTreeContainer().find('a').removeAttr('href');
            if (loaded) {
                loaded(_this.getSelected());
            }

            if (this.selected) {
                this.setSelected(this.selected);
                this.selected = null;
                this.selectedCallback = null;
            }
        },

        getItemID: function(val) {
            return this.getIdLookup()[val];
        },

        getIdLookup: function() {
            if (!this.idLookup) {
                this.idLookup = {};
            }

            return this.idLookup;
        },

        render: function() {
            if (this.container) {
                this.container.append(this.getFormContainer());
            }

            if (this.object) {
                this.initTree();
            }

            return this.getFormContainer();
        },

        attachEventHandlers: function(updated) {
            var _this = this;
            if (this.options['search']) {
                this.getTreeContainer().on('search.jstree', function(e, data) {
                    if (!data['nodes'].length) {
                        _this.getTreeContainer().hide();
                    } else {
                        _this.getTreeContainer().show();
                    }
                });

                this.getTreeContainer().on('clear_search.jstree', function() {
                    _this.getTreeContainer().show();
                });
            }

            this.getTreeContainer().on('select_node.jstree deselect_node.jstree', function (e, data) {
                //  Don't trigger for disabled types
                if (data['node']['state']['type'] == 'disabled') {
                    if ($.inArray(data['node']['id'], data['selected']) !== -1) {
                        _this.getTreeContainer().jstree(true).deselect_node(data['node']['id']);
                    }
                    return;
                }

                if (updated) {
                    var selected = data['selected'];
                    var s = [];
                    for (var i = 0; i < selected.length; i++) {
                        s.push(_this.getTreeContainer().jstree(true).get_node(selected[i])['li_attr']['val']);
                    }

                    updated(s);
                }
            });
        },

        getSelected: function() {
            if (this.initialized) {
                var checked_ids = [];
                var nodes = this.getTreeContainer().jstree(true).get_selected();
                for (var i = 0; i < nodes.length; i++) {
                    checked_ids.push($('#' + nodes[i]).attr('val'));
                }
                return checked_ids;
            } else {
                return null;
            }
        },

        //  TODO: Review and make more generic
        setSelected: function(selected, callback) {
            if (this.initialized) {
                var id;
                this.getTreeContainer().jstree(true).deselect_all();
                this.closeAll();

                for (var i = 0; i < selected.length; i++) {
                    var selector = [];
                    if (selected[i]['invalid']) {
                        var newNode = { state: "closed", data: selected[i]['val'], text: selected[i]['text'] };
                        id = this.getTreeContainer().jstree(true).create_node('#', newNode, 'first');
                        $('#'+id).attr('val', selected[i]['val']);
                        this.getTreeContainer().jstree(true).get_node(id)['li_attr']['val'] = selected[i]['val'];
                        this.getTreeContainer().jstree(true).select_node(id);
                    } else {
                        var pa = [];
                        if (selected[i]['parents']) {
                            for (var j = 0; j < selected[i]['parents'].length; j++) {
                                var p = this.getTreeContainer().find('li[val="' + selected[i]['parents'][j] + '"]').attr('id');
                                selector.push('li[val="' + selected[i]['parents'][j] + '"]');
                                this.getTreeContainer().jstree(true).open_node(p);
                                pa.push(p);
                            }
                        }
                        var pSelector = selector.join(' ');
                        id = $((pSelector ? (pSelector  + ' ') : '') + ('li[val="' + selected[i]['id'] + '"]')).attr('id');
                        if (!id) {
                            for (var k = 0; k < pa.length; k++) {
                                this.getTreeContainer().jstree(true).close_node(pa[k]);
                            }
                        } else {
                            this.getTreeContainer().jstree(true).select_node(id);
                        }
                    }
                }

                if (this.selectedCallback) {
                    this.selectedCallback();
                }

                if ( callback ) {
                    callback();
                }
                this.getTreeContainer().trigger('loaded.jstree');
            } else {
                this.selected = selected;
                this.selectedCallback = callback;
            }
        },

        clearQueue:function() {
            var queue = this.getQueue();
            var _this = this;
            $.each(queue, function(i, q) {
                var func = q['func'];
                var args = q['args'];
                func.apply(_this, args);
            });

            this.queue = null;
        },

        getQueue: function() {
            if (!this.queue) {
                this.queue = [];
            }

            return this.queue;
        },

        editNode: function(node) {
            if (this.initialized) {
                var id = this.getItemID(node['val']);
                if (id) {
                    this.getTreeContainer().jstree(true).set_type(id, node['type']);
                    this.getTreeContainer().jstree(true).rename_node(id, node['text']);
                    this.getTreeContainer().trigger('loaded.jstree');
                }
            } else {
                this.getQueue().push({'func': this.editNode, 'args': [node]});
            }
        },

        setType: function(val, type) {
            if (this.initialized) {
                var id = this.getItemID(val);
                if (id) {
                    this.getTreeContainer().jstree(true).set_type(id, type);
                    this.getTreeContainer().trigger('loaded.jstree');
                }
            } else {
                this.getQueue().push({'func': this.setType, 'args': [val, type]});
            }
        },

        renameNode: function(val, text) {
            if (this.initialized) {
                var id = this.getItemID(val);
                if (id) {
                    this.getTreeContainer().jstree(true).rename_node(id, text);
                    this.getTreeContainer().trigger('loaded.jstree');
                }
            } else {
                this.getQueue().push({'func': this.renameNode, 'args': [val, type]});
            }
        },

        removeNode: function(val) {
            if (this.initialized) {
                var id = this.getItemID(val);
                if (id) {
                    this.getTreeContainer().jstree(true).delete_node(id);
                    this.getTreeContainer().trigger('loaded.jstree');
                }
            } else {
                this.getQueue().push({'func': this.renameNode, 'args': [val]});
            }
        },

        addNode: function(node) {
            if (this.initialized) {
                var id = this.getTreeContainer().jstree(true).create_node(null, { "text" : node['text'], "type": node['type'] }, "inside", false, false);
                $('#'+id).attr('val', node['val']);
                this.getIdLookup()[node['val']] = id;
                this.getTreeContainer().jstree(true).get_node(id)['li_attr']['val'] = node['val'];
                this.getTreeContainer().trigger('loaded.jstree');
            } else {
                this.getQueue().push({'func': this.addNode, 'args': [node]});
            }
        },

        disableNode: function(val) {
            if (this.initialized) {
                var id = this.getItemID(val);
                this.getTreeContainer().jstree(true).disable_node(id);
                this.getTreeContainer().trigger('loaded.jstree');
            } else {
                this.getQueue().push({'func': this.disableNode, 'args': [val]});
            }
        },

        enableNode: function(val) {
            if (this.initialized) {
                var id = this.getItemID(val);
                this.getTreeContainer().jstree(true).enable_node(id);
                this.getTreeContainer().trigger('loaded.jstree');
            } else {
                this.getQueue().push({'func': this.enableNode, 'args': [val]});
            }
        },

        deselectNode: function(val) {
            if (this.initialized) {
                var id = this.getItemID(val);
                if (id) {
                    this.getTreeContainer().jstree(true).deselect_node(id);
                    this.getTreeContainer().trigger('loaded.jstree');
                }
            } else {
                this.getQueue().push({'func': this.deselectNode, 'args': [val]});
            }
        },

        selectNode: function(val) {
            if (this.initialized) {
                if (!$.isArray(val)) {
                    val = [val];
                }

                for (var i = 0; i < val.length; i++) {
                    var id = this.getItemID(val);
                    if (id) {
                        this.getTreeContainer().jstree(true).select_node(id);
                        this.getTreeContainer().trigger('loaded.jstree');
                    }
                }
            } else {
                this.getQueue().push({'func': this.selectNode, 'args': [val]});
            }
        },

        //  Very expensive operation for large datasets
        openAll: function(val) {
            if (this.initialized) {
                var id = this.getItemID(val);
                this.getTreeContainer().jstree(true).open_all(id);
            } else {
                this.getQueue().push({'func': this.openAll, 'args': [val]});
            }
        },

        //  Very expensive operation for large datasets
        closeAll: function(val) {
            if (this.initialized) {
                var id = this.getItemID(val);
                this.getTreeContainer().jstree(true).close_all(id);
            } else {
                this.getQueue().push({'func': this.closeAll, 'args': [val]});
            }
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);
