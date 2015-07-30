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
        throw new Error('ReportColumnTree requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportColumnTree requires GD');
    }

    var GD = global.GD;

    //  TODO Abstract common functionality to new GD.ReportTree
    var ReportColumnTree = GD.View.extend({
        formContainer: null,
        treeView: null,
        treeOptions: null,
        columnData: null,
        columnLookup: null,
        columnsQueue: null,
        selected: null,

        init: function (object, container, options ) {
            this.object = object;
            this.container = container;
            this.options = options;
            this.initVariables();
        },

        initVariables: function() {
            this.formContainer = null;
            this.treeView = null;
            this.treeOptions = null;
            this.treeView = null;
            this.columnData = null;
            this.columnLookup = null;
            this.columnsQueue = null;
            this.selected = null;

            this.getColumnData();
            this.getColumnLookup();
        },

        getController: function () {
            return this.options.controller;
        },

        getTreeOptions: function() {
            if (!this.treeOptions) {
                this.treeOptions = {
                    'search': true,
                    'types' : {
                        "default" : {
                            "icon" : "/sites/all/modules/custom/webui/admin/images/empty_icon.png"
                        },
                        "number" : {
                            "icon" : "/sites/all/modules/custom/webui/admin/images/number_icon.png"
                        },
                        "integer" : {
                            "icon" : "/sites/all/modules/custom/webui/admin/images/number_icon.png"
                        },
                        "percent" : {
                            "icon" : "/sites/all/modules/custom/webui/admin/images/number_icon.png"
                        },
                        "currency" : {
                            "icon" : "/sites/all/modules/custom/webui/admin/images/number_icon.png"
                        },
                        "URI" : {
                            "icon" : "/sites/all/modules/custom/webui/admin/images/string_icon.png"
                        },
                        "string" : {
                            "icon" : "/sites/all/modules/custom/webui/admin/images/string_icon.png"
                        },
                        "date2": {
                            "icon" : "/sites/all/modules/custom/webui/admin/images/date_icon.png"
                        },
                        "date:month": {
                            "icon" : "/sites/all/modules/custom/webui/admin/images/date_month_icon.png"
                        },
                        "date_month": {
                            "icon" : "/sites/all/modules/custom/webui/admin/images/date_month_icon.png"
                        },
                        "date:quarter": {
                            "icon" : "/sites/all/modules/custom/webui/admin/images/date_quarter_icon.png"
                        },
                        "date_quarter": {
                            "icon" : "/sites/all/modules/custom/webui/admin/images/date_quarter_icon.png"
                        },
                        "date2:year": {
                            "icon" : "/sites/all/modules/custom/webui/admin/images/date_year_icon.png"
                        },
                        "date_year": {
                            "icon" : "/sites/all/modules/custom/webui/admin/images/date_year_icon.png"
                        }
                    }
                };

                if (this.options) {
                    if (this.options['checkbox']) {
                        this.treeOptions['checkbox'] = true;
                    }

                    if (typeof this.options['multiple'] != 'undefined' && !this.options['multiple']) {
                        this.treeOptions['multiple'] = false;
                    }
                }
            }

            return this.treeOptions;
        },

        getTreeView: function() {
            if (!this.treeView) {
				this.treeView = new GD.TreeView(null, this.getFormContainer(), this.getTreeOptions());
            }

            return this.treeView;
        },

        loadedTreeCallback: function(selected) {
            this.attachTabIndex();
            this.attachCommentIcon();
            this.attachTypeTooltip();
        },

        loadTreeView: function () {
            var treeviewObj = this.getTreeView();
            treeviewObj.setItems(this.columnData);

            var _this = this;
            treeviewObj.initTree(function(selected) {
                _this.loadedTreeCallback(selected);
            });

            if (this.columnsQueue) {
                this.setSelected(this.columnsQueue);
            }

            // set callback for whenever tree is updated
            treeviewObj.attachEventHandlers(function(selected) {
                _this.columnChanged(selected);
            });
        },

        getColumnData: function() {
            if ( !this.columnData ) {
                if ( this.object ) {
                    var datasetObj = this.getController().getReport().getDataset();
                    this.columnData = datasetObj.getColumns();
                }
            }

            return this.columnData;
        },

        getColumnLookup: function() {
            if (!this.columnLookup) {
                var datasetObj = this.getController().getReport().getDataset();
                this.columnLookup = $.extend(true, {}, datasetObj.getColumnLookup());
            }

            return this.columnLookup;
        },

        getSelected: function() {
            return this.getTreeView().getSelected();
        },

        setSelected: function(columns) {
            global.GD.ReportBuilderMessagingView.clean();
            if ( !this.columnLookup ) {
                this.columnsQueue = columns;
            } else {
                this.selected = [];
                var _this = this;
                var o = [], valid = [];
                for (var i = 0; i < columns.length; i++) {
                    if (!GD.Formula.isFormula(columns[i])) {
                        valid.push(columns[i]);
                        if (this.columnLookup[columns[i]]) {
                            o.push(this.columnLookup[columns[i]]);
                        } else if (!GD.Formula.isFormula(columns[i])) {
                            o.push({'text': 'Unsupported Column: ' + columns[i], 'val': columns[i], 'invalid': true});
                        }
                    }
                }
                // why we are setting selected array when we are overwriting it further.
                //this.selected = valid;
                this.getTreeView().setSelected(o, function() {
                    _this.columnChanged(valid);
                });
            }
        },

        attachTabIndex: function() {
            this.getFormContainer().find("i.jstree-checkbox:not([tabindex])").attr('tabindex', 3000);
        },

        attachCommentIcon: function() {
            var container = this.getFormContainer(),
                _this = this,
                items = container.find('li.jstree-node').filter(function() { return $(this).find('i.rpt-cmmnt-icon').length == 0 && _this.columnLookup[$(this).attr('val')] && _this.columnLookup[$(this).attr('val')]['description']; })
            items.each(function() {
                var icon = $('<i class="jstree-icon jstree-themeicon jstree-themeicon-custom rpt-cmmnt-icon glyphicon glyphicon-info-sign"></i>');
                icon.attr('title', _this.columnLookup[$(this).attr('val')]['description']);
                $(this).find('i.jstree-themeicon-custom').after(icon);

                icon.tooltip({
                    container: 'body',
                    placement: 'right',
                    trigger: 'hover'
                });

                icon.off('click');
                icon.click(function(e) {
                    e.stopPropagation();
                });
            });
        },

        attachTypeTooltip: function() {
            var customIcons = $('i.jstree-themeicon-custom:not(.rpt-cmmnt-icon):not([title])', this.getFormContainer());
            customIcons.attr('title', function() {
                if ($(this).attr('style').indexOf("number") != -1) {
                    return 'number';
                } else if ($(this).attr('style').indexOf("integer") != -1) {
                    return 'integer';
                } else if ($(this).attr('style').indexOf("percent") != -1) {
                    return 'percent';
                } else if ($(this).attr('style').indexOf("currency") != -1) {
                    return 'currency';
                } else if ($(this).attr('style').indexOf("URI") != -1) {
                    return 'URI';
                } else if ($(this).attr('style').indexOf("string") != -1) {
                    return 'string';
                } else if ($(this).attr('style').indexOf("date") != -1) {
                    return 'date';
                } else if ($(this).attr('style').indexOf("date:month") != -1) {
                    return 'date month';
                } else if ($(this).attr('style').indexOf("date_month") != -1) {
                    return 'date month';
                } else if ($(this).attr('style').indexOf("date:quarter") != -1) {
                    return 'date quarter';
                } else if ($(this).attr('style').indexOf("date_quarter") != -1) {
                    return 'date quarter';
                } else if ($(this).attr('style').indexOf("date:year") != -1) {
                    return 'date year';
                } else if ($(this).attr('style').indexOf("date_year") != -1) {
                    return 'date year';
                }
            });

            customIcons.tooltip({
                container: 'body',
                trigger: 'hover'
            });
            customIcons.off('click');
            customIcons.click(function(e) {
                e.stopPropagation();
            });
        },

        columnChanged: function ( selected ) {

            var a = [],
                r = [];

            for (var i in selected) {
                if ($.inArray(selected[i], this.selected) === -1) {
                    var column = this.columnLookup[selected[i]];
                    if (column) {
                        a.push(column);
                    } else {
                        a.push({'id': selected[i], 'name': 'Unsupported Column: ' + selected[i]});
                    }
                }
            }

            for (var j in this.selected) {
                if ($.inArray(this.selected[j], selected) === -1) {
                    r.push(this.selected[j]);
                }
            }

            this.selected = selected;

            $(document).trigger({
                type: 'changed.column.selection',
                added: a,
                removed: r
            });
        },

        getFormContainer: function() {
            if (!this.formContainer) {
                this.formContainer = $('<div class="bldr-clmn-tree"></div>');
            }

            return this.formContainer;
        },

        render: function() {
            if (this.container) {
                this.container.append(this.getFormContainer());
            }

            this.getTreeView().render();
            this.loadTreeView();

            return this.getFormContainer();
        },

        findById: function(id) {
            return this.columnLookup ? this.columnLookup[id] : null;
        },

        getSelectedColumnObjects: function() {
            var selectedCols = [],
                dsColumns = this.getColumnData(),
                selectedColIds = this.getSelected();
            
            for(var j=0; j<selectedColIds.length; j++){
                for ( var i=0, dsColumnCount=dsColumns.length; i<dsColumnCount; i+=1 ) {
                    if ( selectedColIds[j] == dsColumns[i].id)  {
                       selectedCols.push(dsColumns[i]);
                    }
                    
                    if(dsColumns[i].children){
                        for ( var k=0; k<dsColumns[i].children.length; k++ ) {
                            if ( selectedColIds[j] == dsColumns[i].children[k].id)
                            {
                                selectedCols.push(dsColumns[i].children[k]);
                            }

                        }
                    }
                }
            }

            return selectedCols;
        },

        selectNode: function(id) {
            if (!this.findById(id)) {
                this.getTreeView().addNode({'text': 'Unsupported Column: ' + id, 'val': id, 'invalid': true});
                this.getTreeView().selectNode(id);
            } else {
                this.getTreeView().selectNode(id);
            }
        },

        deselectNode: function(id) {
            this.getTreeView().deselectNode(id);
        },

        disableNode: function(id) {
            this.getTreeView().disableNode(id);
        },

        enableNode: function(id) {
            this.getTreeView().enableNode(id);
        }
    });

    GD.ReportColumnTree = ReportColumnTree;

})(window ? window : window, jQuery);
