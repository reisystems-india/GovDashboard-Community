(function(global,$,undefined) {

    if ( typeof $ === 'undefined' ) {
        throw new Error('ReportColumnSelectionForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportColumnSelectionForm requires GD');
    }

    var GD = global.GD;

    var ReportColumnSelectionForm = GD.View.extend({
        formContainer: null,
        formHeader: null,
        columnTree: null,
        formulaTree: null,

        init: function(object, container, options) {
            this._super(object, container, options);

            var _this = this;
            $(document).on('removed.column.selected', function(e) {
                //need not to set all selected columns every time, just set the particular column.
                _this.getColumnTree().getTreeView().deselectNode(e['colId']);
                _this.getFormulaTree().getTreeView().deselectNode(e['colId']);
            });

            $(document).on('edit.report.formula', function(e) {
                _this.editFormulaClicked(e['formula']);
            });

            $(document).on('delete.report.formula', function(e) {
                _this.deleteFormulaClicked(e['formula']);
            });

            $(document).on('resize.column.form', function(e) {
                if (e.width) {
                    _this.resizePanel(e.width);
                }
            });
        },

        resizePanel: function(w) {
            this.getFormContainer().width(w-305);
        },

        getColumnList: function() {
            this.getColumnTree().getSelected();
        },

        setColumnList: function(list) {
            this.getColumnTree().setSelected(list);
        },

        getController: function() {
            if (this.options) {
                return this.options.controller;
            }

            return null;
        },

        getFormContainer: function() {
            if (!this.formContainer) {
                this.formContainer = $('<div class="report-selected-column pull-right"></div>');
                this.formContainer.append(this.getTabContainer());
            }

            return this.formContainer;
        },

        getTabContainer: function() {
            if (!this.tabContainer) {
                this.tabContainer = $('<div role="tabpanel"></div>');
                var nav = $('<ul class="nav nav-tabs" role="tablist"></ul>');
                var column = $('<li role="presentation" class="active"><a tabindex="3000" href="#columns" aria-controls="columns" role="tab" data-toggle="tab">Columns</a></li>');
                var formula = $('<li role="presentation"><a tabindex="3000" href="#formulas" aria-controls="formulas" role="tab" data-toggle="tab">Formulas</a></li>');
                nav.append(column, formula);
                var content = $('<div class="tab-content"></div>');
                content.append(this.getColumnsContainer(), this.getFormulasContainer());
                this.tabContainer.append(nav, content);
            }

            return this.tabContainer;
        },

        getColumnTree: function() {
            if (!this.columnTree) {
                var controllerObj = this.getController();
                this.columnTree = new GD.ReportColumnTree(
                    {
                        dataset: controllerObj.getReport().getDatasetName()
                    },
                    this.getColumnsContainer(),
                    {
                        'checkbox': true,
                        'controller': controllerObj
                    }
                );
            }

            return this.columnTree;
        },

        getColumnsContainer: function() {
            if (!this.columnsContainer) {
                this.columnsContainer = $('<div role="tabpanel" class="tab-pane active" id="columns" style="padding: 10px;"></div>');
            }

            return this.columnsContainer;
        },

        getFormulaTree: function() {
            if (!this.formulaTree) {
                this.formulaTree = new GD.ReportFormulaTree(
                    {
                        dataset: this.getController().getReport().getDatasetName()
                    },
                    this.getFormulaSelectionForm(),
                    {
                        'checkbox': true,
                        'controller': this.getController()
                    }
                );
            }

            return this.formulaTree;
        },

        getLoadingScreen: function() {
            if (!this.loadingScreen) {
                this.loadingScreen = $('<div class="ldng" style="display:none;width:425px;height:344px;"></div>');
            }

            return this.loadingScreen;
        },

        validateFormulaExpression: function(formula) {
            this.getFormulaForm().hide();
            this.getLoadingScreen().show();
            var _this = this,
                reportObj = _this.getController().getReport(),
                conf = reportObj.getConfig();
            conf['config']['model']['filters'] = [];
            var update = formula.getID();
            conf['config']['model']['formulas'] = reportObj.getFormulasRaw();
            if (update) {
                $.each(conf['config']['model']['formulas'], function(i, f) {
                    if (f.name === update) {
                        f.publicName = formula.getName();
                        f.expression = formula.getExpression();
                        f.expressionLanguage = formula.getExpressionLanguage();
                        f.type = formula.getType();
                        f.version = formula.getVersion();
                    }
                });
            } else {
                conf['config']['model']['formulas'].push(formula.getRaw());
            }
            conf['config']['model']['limit'] = 1;
            conf['config']['model']['columns'] = [formula.getID()];
            $.ajax({
                'type': "POST",
                'url': '/api/report/data.json',
                'data': {
                    'ds': reportObj.getDatasourceName(),
                    'report': conf
                }
            }).done(function() {
                _this.getLoadingScreen().hide();
                _this.getFormulaSelectionForm().show();
                if (!update) {
                    reportObj.addFormula(formula);
                    GD.ReportBuilderMessagingView.showMessage('Formula added successful.', 'notice');
                } else {
                    reportObj.editFormula(formula);
                    GD.ReportBuilderMessagingView.showMessage('Formula updated successful.', 'notice');
                }
            }).fail(function(e, xhr, errorThrown) {
                if (!update) {
                    delete formula['id'];
                }
                _this.getFormulaForm().show(formula);
                _this.getLoadingScreen().hide();
                GD.ReportBuilderMessagingView.showMessage('Formula expression failed' + errorThrown, 'error');
            });
        },

        getDeleteModal: function(formula) {
            if (!this.deleteModal) {
                this.deleteModal = $('<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true"></div>');
                var dialog = $('<div class="modal-dialog"></div>');
                var content = $('<div class="modal-content"></div>');
                var header = $('<div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><h4 class="modal-title">Remove Formula</h4></div>');
                var body = $('<div class="modal-body row"><span style="font-size:25px;" class="glyphicon glyphicon-info-sign col-md-1"></span><div style="height:25px; line-height: 25px;" class="col-md-11">Are you sure you want to delete this formula?</div></div>');
                var footer = $('<div class="modal-footer"></div>');
                var remove = $('<button type="button" id="deleteFormula" class="btn btn-danger">Delete</button>');
                var close = $('<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>');
                footer.append(remove, close);
                content.append(header, body, footer);
                dialog.append(content);
                this.deleteModal.append(dialog);
                $('body').append(this.deleteModal);
            }

            var del = $('#deleteFormula');
            del.off('click');
            var _this = this;
            del.click(function() {
                _this.getDeleteModal().modal('hide');
                _this.getController().getReport().removeFormula(formula);
                _this.getFormulaSelectionForm().show();
                _this.getFormulaForm().hide();
                $(document).trigger({
                    type: 'changed.formula.selection',
                    added: [],
                    removed: [formula.id]
                });
            });

            return this.deleteModal;
        },

        deleteFormulaClicked: function(formula) {
            this.getDeleteModal(formula).modal();
        },

        saveFormulaClicked: function(formula) {
            this.validateFormulaExpression(formula);
        },

        cancelFormulaClicked: function() {
            this.getFormulaSelectionForm().show();
            this.getFormulaForm().hide();
        },

        newFormulaClicked: function() {
            this.getFormulaSelectionForm().hide();
            this.getFormulaForm().show();
        },

        editFormulaClicked: function(formula) {
            this.getFormulaSelectionForm().hide();
            this.getFormulaForm().show(formula);
        },

        getFormulaSelectionForm: function() {
            if (!this.formulaSelectionForm) {
                this.formulaSelectionForm = $('<div></div>');
                var link = $('<span class="report-formula-link" tabindex="3000">Create new Formula</span>');
                this.formulaSelectionForm.append($('<div class="report-formula-link-container"></div>').append(link));

                var _this = this;
                link.click(function() {
                    _this.newFormulaClicked();
                });
                link.keydown(function(e) {
                    var code = e.keyCode || e.which;
                    if (code == 13) {
                        _this.newFormulaClicked();
                    }
                });
            }

            return this.formulaSelectionForm;
        },

        getFormulasContainer: function() {
            if (!this.formulasContainer) {
                this.formulasContainer = $('<div role="tabpanel" class="tab-pane" id="formulas" style="padding: 10px;"></div>');
                this.formulasContainer.append(this.getFormulaSelectionForm(), this.getLoadingScreen());
            }

            return this.formulasContainer;
        },

        getFormulaForm: function() {
            if (!this.formulaForm) {
                this.formulaForm = new GD.ReportFormulaForm(null, this.getFormulasContainer(), {controller: this.getController()});

                var _this = this;
                this.formulaForm.attachEventHandlers(function() {
                    _this.cancelFormulaClicked();
                    _this.getFormulaSelectionForm().find(".report-formula-link").focus();
                }, function(formula) {
                    _this.deleteFormulaClicked(formula);
                }, function(formula) {
                    _this.saveFormulaClicked(formula);
                });
            }

            return this.formulaForm;
        },

        render: function() {
            if (this.container) {
                var columnTreeviewObj = this.getColumnTree(),
                    formulaTreeObj = this.getFormulaTree(),
                    reportObj = this.getController().getReport();
                this.container.append(this.getFormContainer());
                columnTreeviewObj.render();
                formulaTreeObj.render();

                //  Need to maintain column selection order
                var selected = reportObj.getColumns();
                if (selected && selected.length) {
                    $.each(selected, function(i, c) {
                        if (GD.Formula.isFormula(c)) {
                            formulaTreeObj.selectNode(c);
                        } else {
                            columnTreeviewObj.selectNode(c);
                        }
                    });
                }
                this.getFormulaForm().render();
            }

            return this.getFormContainer();
        }
    });

    GD.ReportColumnSelectionForm = ReportColumnSelectionForm;

})(window ? window : window, jQuery);
