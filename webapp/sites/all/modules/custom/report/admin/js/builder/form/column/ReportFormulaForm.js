(function(global,$,undefined) {

    if ( typeof $ === 'undefined' ) {
        throw new Error('ReportFormulaForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportFormulaForm requires GD');
    }

    var GD = global.GD;

    var ReportFormulaForm = GD.View.extend({
        formContainer: null,
        isNew: true,

        init: function(object, container, options) {
            this._super(object, container, options);

            if (object) {
                this.setFormula(object);
            } else {
                this.setFormula(new GD.Formula());
            }

            var _this = this;
            $(document).on('added.report.formulas removed.report.formulas', function() {
                _this.repopulateColumnList();
            });
        },

        getController: function() {
            if (this.options) {
                return this.options.controller;
            }

            return null;
        },

        validate: function() {
            GD.ReportBuilderMessagingView.clean();
            var pass = true,
                formulaNameArr = $.map(this.options.controller.report.getFormulaLookup(), function(el, i){
                    return el.name;
                });

            if (!this.getNameInput().val()) {
                GD.ReportBuilderMessagingView.addErrors('Formula name cannot be empty.');
                pass = false;
            } else if(this.isNew && $.inArray(this.getNameInput().val(), formulaNameArr) !== -1){
                GD.ReportBuilderMessagingView.addErrors('Formula exists with this name.');
                pass = false;
            }

            if (!this.getEditor().getValue()) {
                GD.ReportBuilderMessagingView.addErrors('Formula expression cannot be empty.');
                pass = false;
            }

            GD.ReportBuilderMessagingView.displayMessages();

            return pass;
        },

        setFormula: function(formula) {
            this.isNew = !(formula.getID());

            this.formula = formula;
            this.getNameInput().val(this.formula.getName());
            this.getEditor().getSession().getDocument().setValue(this.formula.getExpression());
            this.setLanguage(this.formula.getExpressionLanguage());
            this.setType(this.formula.getType());
        },

        getFormula: function() {
            var f = GD.Formula.clone(this.formula);
            f.setID(this.formula.getID());
            f.setName(this.getNameInput().val());
            f.setExpression(this.getEditor().getValue());
            f.setExpressionLanguage(this.getLanguage());
            f.setType(this.getTypeInput().val());

            return f;
        },

        getTypes: function() {
            if (!this.types) {
                this.types = [{
                    "name": "Number",
                    "value": "number"
                },{
                    "name": "Integer",
                    "value": "integer"
                },{
                    "name": "Currency",
                    "value": "currency"
                },{
                    "name": "Percent",
                    "value": "percent"
                },{
                    "name": "String",
                    "value": "string"
                },{
                    "name": "Date",
                    "value": "date2"
                }];
            }

            return this.types;
        },

        setType: function(type) {
            if (!type) {
                type = this.getTypes()[0];
                this.getTypeInput().val(type["value"]);
            } else {
                this.getTypeInput().val(type);
            }
        },

        getType: function() {
            var type = this.getTypeInput().val();
            if (!type) {
                type = this.getTypes()[0];
            }

            return type;
        },

        getTypeInput: function() {
            if (!this.typeInput) {
                this.typeInput = $('<select tabindex="3000" class="report-formula-form-type form-control"></select>');
                var types = this.getTypes(),
                    _this = this;
                $.each(types, function(i, t) {
                    var o = $('<option></option>');
                    if (i === 0) {
                        o.attr('selected', true);
                    }
                    o.attr('value', t['value']);
                    o.text(t['name']);
                    _this.typeInput.append(o);
                });
            }

            return this.typeInput;
        },

        repopulateColumnList: function(id) {
            this.getColumnsInput().empty();
            this.getColumnsInput().append('<button tabindex="3000" type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Insert Column <span class="caret"></span></button>');
            var list = $('<ul class="dropdown-menu column-list"></ul>'),
                _this = this;
            var populateList = function() {
                var c = _this.getController();
                if (c) {
                    var columns = c.getReport().getUsableFormulaColumns(true);
                    for (var i in columns) {
                        if (columns[i]['id'] == id) continue;

                        var o = $('<li><a rel="'+columns[i]['id']+'">'+ columns[i]['name'] +'</a></li>');
                        o.click(function() {
                            _this.getEditor().insert('$COLUMN{'+$(this).find('a').attr('rel')+'}');
                        });
                        list.append(o);
                    }
                }
            };
            populateList();
            this.getColumnsInput().append(list);
        },

        getColumnsInput: function() {
            if (!this.columnsInput) {
                this.columnsInput = $('<div class="report-formula-form-column btn-group"></div>');
            }

            return this.columnsInput;
        },

        getFunctions: function() {
            if (!this.functions) {
                this.functions = {
                    'SQL': [
                        {
                            "name": "Record Count",
                            "value": "COUNT(*)",
                            "favorite": true},
                        {
                            "name": "Distinct Count",
                            "value": "COUNT(DISTINCT /* column */)",
                            "favorite": true},
                        {
                            "name": "SUM",
                            "value": "SUM(/* column */)",
                            "favorite": true},
                        {
                            "name": "MAX",
                            "value": "MAX(/* column */)",
                            "favorite": true},
                        {
                            "name": "MIN",
                            "value": "MIN(/* column */)",
                            "favorite": true},
                        {
                            "name": "AVG",
                            "value": "AVG(/* column */)",
                            "favorite": true},
                        {
                            "name": "TOTAL",
                            "value": "TOTAL(/* column */)",
                            "favorite": true},
                        {
                            "name": "Current Date",
                            "value": "TRUNC(CURRENT_DATE, 'DDD')",
                            "category": "date",
                            "type": ["oracle"]},
                        {
                            "name": "Current Month",
                            "value": "EXTRACT(MONTH FROM CURRENT_DATE)",
                            "category": "date",
                            "type": ["oracle"]},
                        {
                            "name": "Current Day",
                            "value": "EXTRACT(DAY FROM CURRENT_DATE)",
                            "category": "date",
                            "type": ["oracle"]},
                        {
                            "name": "Current Date",
                            "value": "DATE(CURRENT_DATE)",
                            "category": "date",
                            "type": ["mysql"]},
                        {
                            "name": "Current Month",
                            "value": "MONTH(CURRENT_DATE)",
                            "category": "date",
                            "type": ["mysql"]},
                        {
                            "name": "Current Day",
                            "value": "DAY(CURRENT_DATE)",
                            "category": "date",
                            "type": ["mysql"]},
                        {
                            "name": "Length",
                            "value": "LENGTH(/* column */)",
                            "category": "string"},
                        {
                            "name": "SubString",
                            "value": "SUBSTR(/* column */, /* position */, /* length */)",
                            "category": "string"},
                        {
                            "name": "Concatenate",
                            "value": "CONCAT(/* column */, /* column */)",
                            "category": "string"},
                        {
                            "name": "Lower Case",
                            "value": "LOWER(/* column */)",
                            "category": "string"},
                        {
                            "name": "Upper Case",
                            "value": "UPPER(/* column */)",
                            "category": "string"}
                    ]
                };
            }

            return this.filterByDatabase(this.functions[this.getLanguage()]);
        },

        filterByDatabase: function(objs) {
            var database = this.getController().getActiveDatasource()['type'],
                filtered = [];
            if (objs) {
                for (var i = 0; i < objs.length; i++) {
                    //  If object type is null or database type is in type array of object
                    if (!objs[i]['type'] || $.inArray(database, objs[i]['type']) !== -1) {
                        filtered.push(objs[i]);
                    }
                }
            }

            return filtered;
        },

        getFunctionsInput: function() {
            if (!this.functionsInput) {
                this.functionsInput = $('<div class="report-formula-form-function btn-group"></div>');
                this.functionsInput.append('<button tabindex="3000" type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Insert Function <span class="caret"></span></button>');
                var list = $('<ul class="dropdown-menu column-list"></ul>');
                var functions = this.getFunctions();
                var _this = this;
                $.each(functions, function(i, f) {
                    var o = $('<li><a>'+ f['name'] +'</a></li>');
                    o.click(function() {
                        _this.getEditor().insert(f['value']);
                    });
                    list.append(o);
                });

                this.functionsInput.append(list);
            }

            return this.functionsInput;
        },

        getTemplates: function() {
            if (!this.templates) {
                this.templates = {
                    'SQL': [
                        {
                            "name": "IF - THEN - ELSE",
                            "value": "IF /* condition */\n\tTHEN /* return expression */\n\tELSE /* return expression */\nEND IF"},
                        {
                            "name": "IF - THEN - ELSEIF - ELSE",
                            "value": "IF /* condition */\n\tTHEN /* return expression */\n\t/* you may repeat ELSEIF and corresponding THEN multiple times */\n\tELSEIF /* condition */ THEN /* return expression */\n\tELSE /* return expression */\nEND IF"}
                    ]
                };
            }

            return this.templates[this.getLanguage()];
        },

        getTemplatesInput: function() {
            if (!this.templateInput) {
                this.templateInput = $('<div class="report-formula-form-template btn-group"></div>');
                this.templateInput.append('<button tabindex="3000" type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Insert Template <span class="caret"></span></button>');
                
                var list = $('<ul class="dropdown-menu column-list"></ul>'),
                    templates = this.getTemplates(),
                    _this = this;
                if (templates) {
                    $.each(templates, function(i, t) {
                        var o = $('<li><a>'+ t['name'] +'</a></li>');
                        o.click(function() {
                            _this.getEditor().insert(t['value']);
                        });
                        list.append(o);
                    });
                }

                this.templateInput.append(list);
            }

            return this.templateInput;
        },

        setLanguage: function(language) {
            if (!language) {
                language = this.getLanguages()[0];
            }
            this.getLanguageInput().val(language);
        },

        getLanguage: function() {
            var language = this.getLanguageInput().val();
            if (!language) {
                language = this.getLanguages()[0];
            }
            return language;
        },

        getLanguages: function() {
            if (!this.languages) {
                this.languages = ['SQL'];
            }

            return this.languages;
        },

        getLanguageInput: function() {
            if (!this.languageInput) {
                this.languageInput = $('<select tabindex="3000" class="report-formula-form-language form-control"></select>');
                
                var languages = this.getLanguages(),
                    _this = this;
                
                $.each(languages, function(i, l) {
                    var o = $('<option '+( i===0?'selected': '') +'></option>');
                    o.attr('value', l);
                    o.text(l);
                    _this.languageInput.append(o);
                });
                this.languageInput.val(languages[0]);
                this.languageInput.change(function() {
                    //console.log('Changed language ' + $(this).val());
                });
            }

            return this.languageInput;
        },

        getNameInput: function() {
            if (!this.nameInput) {
                this.nameInput = $('<input class="report-formula-form-name form-control" tabindex="3000" type="text" placeholder="Formula Name"/>');
            }

            return this.nameInput;
        },

        getEditor: function() {
            if (!this.editor) {
                this.editor = ace.edit(this.getExpressionEditor().get(0));
                this.editor.setShowPrintMargin(false);
                this.editor.setHighlightActiveLine(false);
                this.editor.renderer.setShowGutter(false);
                this.editor.getSession().setMode("ace/mode/sql");
            }

            return this.editor;
        },

        getExpressionEditor: function() {
            if (!this.expressionEditor) {
                this.expressionEditor = $('<div class="report-formula-form-editor"></div>');
                this.getEditor();
            }

            return this.expressionEditor;
        },

        getSaveButton: function() {
            if (!this.saveButton) {
                this.saveButton = $('<button tabindex="3000" class="btn btn-primary report-formula-form-button report-formula-form-save">Save</button>');
            }

            return this.saveButton;
        },

        getCancelButton: function() {
            if (!this.cancelButton) {
                this.cancelButton = $('<button tabindex="3000" class="btn btn-default report-formula-form-button report-formula-form-cancel">Cancel</button>');
            }

            return this.cancelButton;
        },

        getFormContainer: function() {
            if (!this.formContainer) {
                this.formContainer = $('<div class="report-formula-form-container form-group" style="display:none;"></div>');
                this.formContainer.append(this.getNameInput(), this.getTypeInput());
                this.formContainer.append(this.getTemplatesInput(), this.getColumnsInput(), this.getFunctionsInput());
                this.formContainer.append('<div class="clearfix"></div>');
                this.formContainer.append($('<div class="report-formula-form-editor-container"></div>').append(this.getExpressionEditor()));
                this.formContainer.append(this.getLanguageInput(), $("<div class='pull-right'></div>").append(this.getCancelButton(), this.getDeleteButton(), this.getSaveButton()));
            }

            return this.formContainer;
        },

        getDeleteButton: function() {
            if (!this.deleteButton) {
                this.deleteButton = $('<button tabindex="3000" class="btn btn-default report-formula-form-button report-formula-form-delete">Delete</button>');
            }

            return this.deleteButton;
        },

        show: function(formula) {
            var deletebuttonObj = this.getDeleteButton();
            if (!formula) {
                formula = new GD.Formula();
            }
            if (!formula.getID()) {
                deletebuttonObj.hide();
            } else {
                deletebuttonObj.show();
            }
            this.repopulateColumnList(formula.getID());
            this.setFormula(formula);
            this.getFormContainer().show();
            this.getNameInput().focus();
        },

        hide: function() {
            this.getFormContainer().hide();
        },

        render: function() {
            if (this.container) {
                this.container.append(this.getFormContainer());
            }

            return this.getFormContainer();
        },

        attachEventHandlers: function(cancel, del, save) {
            this.getCancelButton().click(function() {
                if (cancel) {
                    cancel();
                }
            });

            var _this = this;
            this.getDeleteButton().click(function() {
                if (del) {
                    del(_this.getFormula());
                }
            });

            this.getSaveButton().click(function() {
                if (_this.validate()) {
                    if (save) {
                        save(_this.getFormula());
                    }
                }
            });
        }
    });

    GD.ReportFormulaForm = ReportFormulaForm;

})(window ? window : window, jQuery);
