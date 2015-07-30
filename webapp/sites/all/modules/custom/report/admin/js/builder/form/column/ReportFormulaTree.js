(function(global,$,undefined) {

    if ( typeof $ === 'undefined' ) {
        throw new Error('ReportFormulaTree requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportFormulaTree requires GD');
    }

    var GD = global.GD;

    //  TODO Abstract common functionality to new GD.ReportTree
    var ReportFormulaTree = GD.View.extend({
        formContainer: null,
        treeView: null,
        treeOptions: null,
        formulaData: null,
        formulaLookup: null,
        formulaQueue: null,
        selected: null,

        init: function (object, container, options ) {
            this.object = object;
            this.container = container;
            this.options = options;

            this.initVariables();

            var _this = this;
            $(document).on('added.report.formulas', function(e) {
                _this.formulaAdded(e['added']);
            });
            $(document).on('removed.report.formulas', function(e) {
                _this.formulaRemoved(e['removed']);
            });
            $(document).on('updated.report.formulas', function(e) {
                _this.formulaUpdated(e['updated']);
            });
        },

        initVariables: function() {
            this.formContainer = null;
            this.treeView = null;
            this.treeOptions = null;
            this.formulaData = null;
            this.formulaLookup = null;
            this.formulaQueue = null;
            this.selected = null;
        },

        getController: function () {
            return this.options.controller;
        },

        getTreeOptions: function() {
            if (!this.treeOptions) {
                this.treeOptions = {
                    'search': false,
                    'types' : {
                        "default" : {
                            "icon" : "/sites/all/modules/custom/webui/admin/images/empty_icon.png"
                        },
                        "number" : {
                            "icon" : "/sites/all/modules/custom/webui/admin/images/number_icon.png"
                        },
                        "integer" : {
                            "icon" : "/sites/all/modules/custom/webui/admin/images/integer_icon.png"
                        },
                        "percent" : {
                            "icon" : "/sites/all/modules/custom/webui/admin/images/percent_icon.png"
                        },
                        "currency" : {
                            "icon" : "/sites/all/modules/custom/webui/admin/images/currency_icon.png"
                        },
                        "string" : {
                            "icon" : "/sites/all/modules/custom/webui/admin/images/string_icon.png"
                        },
                        "date2": {
                            "icon" : "/sites/all/modules/custom/webui/admin/images/date_icon.png"
                        }
                    }
                };

                if (this.options && this.options['checkbox']) {
                    this.treeOptions['checkbox'] = true;
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

        getFormulaData: function() {
            if (!this.formulaData) {
                var reportObj = this.getController().getReport(),
                raw = reportObj.getFormulas();
                this.formulaData = [];
                for (var i in raw) {
                    this.formulaData.push({
                        type: raw[i].getType(),
                        val: raw[i].getID(),
                        text: raw[i].getName()
                    });
                }
            }
            return this.formulaData;
        },

        getFormulaLookup: function() {
            if (!this.formulaLookup) {
                var reportObj = this.getController().getReport();
                this.formulaLookup = reportObj.getFormulaLookup();
            }

            return this.formulaLookup;
        },

        formulaAdded: function(formula) {
            this.getTreeView().addNode({
                type: formula.getType(),
                val: formula.getID(),
                text: formula.getName()
            });
        },

        formulaRemoved: function(formula) {
            this.getTreeView().removeNode(formula.getID());
        },

        formulaUpdated: function(formula) {
            this.getTreeView().editNode({val: formula.getID(), text: formula.getName(), type: formula.getType()});
        },

        loadTreeView: function () {
            var treeviewObj = this.getTreeView();
            treeviewObj.setItems(this.getFormulaData());

            var _this = this;
            treeviewObj.initTree(function(selected) {
                _this.attachTypeTooltip();
                _this.attachEditIcon();
                _this.attachDeleteIcon();
            });

            if (this.formulaQueue) {
                this.setSelected(this.formulaQueue);
            }

            // set callback for whenever tree is updated
            this.getTreeView().attachEventHandlers(function(selected) {
                _this.formulaChanged(selected);
            });
        },

        getColumnData: function() {
            var reportObj = this.getController().getReport();
            return reportObj.getFormulas();
        },

        getSelected: function() {
            return this.getTreeView().getSelected();
        },

        setSelected: function(formulas) {
            this.selected = [];
            var _this = this,
                o = [],
                valid = [],
                lookup = this.getFormulaLookup();
            
            for (var i = 0; i < formulas.length; i++) {
                if (GD.Formula.isFormula(formulas[i])) {
                    valid.push(formulas[i]);
                    if (lookup[formulas[i]]) {
                        o.push(lookup[formulas[i]]);
                    } else {
                        o.push({'text': 'Unsupported Column: ' + formulas[i], 'val': formulas[i], 'invalid': true});
                    }
                }
            }
            //this.selected = valid;
            this.getTreeView().setSelected(o, function() {
                _this.formulaChanged(valid);
            });
        },

        editFormula: function(formula) {
            var lookup = this.getFormulaLookup();
            $(document).trigger({
                type: 'edit.report.formula',
                formula: lookup[formula]
            });
        },

        attachEditIcon: function() {
            var items = this.getFormContainer().find('li.jstree-node'),
                _this = this;
            items.each(function() {
                if ($(this).find('i.report-formula-edit-icon').length == 0) {
                    var icon = $('<i class="report-tree-icon report-formula-edit-icon glyphicon glyphicon-pencil"></i>');
                    icon.attr('title', 'Edit Formula');
                    $(this).find('i.jstree-themeicon-custom').after(icon);
                } else {
                    icon = $(this).find('i.report-formula-edit-icon');
                }

                icon.tooltip({
                    container: 'body',
                    trigger: 'hover'
                });

                var $this = $(this);
                icon.off('click').click(function(e) {
                    if (_this.findById($this.attr('val'))) {
                        _this.editFormula($this.attr('val'));
                    }
                    e.stopPropagation();
                });
            });
        },

        deleteFormula: function(formula) {
            var lookup = this.getFormulaLookup();
            var f = lookup[formula];
            if (!f) {
                f = new GD.Formula({name: formula});
            }
            $(document).trigger({
                type: 'delete.report.formula',
                formula: f
            });
        },

        attachDeleteIcon: function() {
            var items = this.getFormContainer().find('li.jstree-node'),
                _this = this;
            items.each(function() {
                if ($(this).find('i.report-formula-remove-icon').length == 0) {
                    var icon = $('<i class="report-tree-icon report-formula-remove-icon glyphicon glyphicon-remove"></i>');
                    icon.attr('title', 'Delete Formula');
                    $(this).find('i.jstree-themeicon-custom').after(icon);
                } else {
                    icon = $(this).find('i.report-formula-remove-icon');
                }

                icon.tooltip({
                    container: 'body',
                    trigger: 'hover'
                });

                var $this = $(this);
                icon.off('click').click(function(e) {
                    _this.deleteFormula($this.attr('val'));
                    e.stopPropagation();
                });
            });
        },

        attachTypeTooltip: function() {
            var customIcons = this.getFormContainer().find('i.jstree-themeicon-custom:not(.report-formula-edit-icon):not(.report-formula-remove-icon)');
            customIcons.attr('title', function() {
                if ($(this).attr('style').indexOf("number") != -1) {
                    return 'number';
                } else if ($(this).attr('style').indexOf("integer") != -1) {
                    return 'integer';
                } else if ($(this).attr('style').indexOf("percent") != -1) {
                    return 'percent';
                } else if ($(this).attr('style').indexOf("currency") != -1) {
                    return 'currency';
                } else if ($(this).attr('style').indexOf("string") != -1) {
                    return 'string';
                } else if ($(this).attr('style').indexOf("date") != -1) {
                    return 'date';
                }
            });

            customIcons.tooltip({
                container: 'body',
                delay: { show: 250, hide: 0 }
            });
            customIcons.off('click').click(function(e) {
                e.stopPropagation();
            });
        },

        formulaChanged: function ( selected ) {

            var a = [],
                r = [],
                lookup = this.getFormulaLookup();
  
            for (var i in selected) {
                if ($.inArray(selected[i], this.selected) === -1) {
                    var formula = lookup[selected[i]];
                    if (formula) {
                        a.push(formula);
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
                type: 'changed.formula.selection',
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
            return this.getFormulaLookup()[id];
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
                this.getTreeView().addNode({'text': 'Unsupported Formula: ' + id, 'val': id, 'invalid': true});
                this.getTreeView().selectNode(id);
            } else {
                this.getTreeView().selectNode(id);
            }
        },

        deselectNode: function(id) {
            this.getTreeView().deselectNode(id);
        }
    });

    GD.ReportFormulaTree = ReportFormulaTree;

})(window ? window : window, jQuery);
