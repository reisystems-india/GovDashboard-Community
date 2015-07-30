(function(global,$,undefined) {

    if ( typeof $ === 'undefined' ) {
        throw new Error('ReportSelectedColumnForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportSelectedColumnForm requires GD');
    }

    var GD = global.GD;

    var ReportSelectedColumnForm = GD.View.extend({
        formContainer: null,
        formHeader: null,
        columns: null,

        init: function(object, container, options) {
            this._super(object, container, options);
            this.columns = [];

            //redundant functionality: columnList is already getting populated when tree is rendered and columns are selected.
            /* if (object) {
             this.setColumnList(object);
             }*/

            var _this = this;
            $(document).on('changed.column.selection changed.formula.selection', function(e) {
                _this.addColumns(e['added']);
                _this.removeColumns(e['removed']);
            });
        },

        getController: function() {
            if (this.options) {
                return this.options.controller;
            }

            return null;
        },

        getColumnLookup: function() {
            if (!this.columnlookup) {
                this.columnlookup = $.extend(true, {}, this.getController().getReport().getDataset().getColumnLookup());
            }

            return this.columnlookup;
        },

        getFormulaLookup: function() {
            if (!this.formulaLookup) {
                this.formulaLookup = this.getController().getReport().getFormulaLookup();
            }

            return this.formulaLookup;
        },

        addView: function(c) {
            var v = $('<div class="clearfix report-column-item" style="padding-bottom: 3px; padding-left: 10px; padding-right: 10px;"></div>');
            var name = $('<span class="pull-left report-column-item-text"></span>');
            v.attr('column', c['id']);
            name.text(GD.Column.getFullColumnName(c, this.getColumnLookup()));
            var close = $('<span tabindex="3000" style="cursor: pointer;margin-top:2px;" class="glyphicon glyphicon-remove pull-right"></span>');
            v.append(name, close);
            this.getItemContainer().append(v);

            var _this = this;
            close.click(function() {
                _this.removeColumn(c['id']);
                $(document).trigger({
                    type: "removed.column.selected",
                    columns: _this.columns,
                    colId:c['id']
                });
            });
        },

        removeView: function(column) {
            $('div.report-column-item[column="' + column + '"]').remove();
        },

        addColumns: function(columns) {
            for (var i in columns) {
                this.addColumn(columns[i]);
            }
        },

        addColumn: function(column) {
            if (column) {
                this.addView(column);
                this.columns.push(column['id']);
            }
        },

        removeColumns: function(columns) {
            for (var i in columns) {
                this.removeColumn(columns[i]);
            }
        },

        removeColumn: function(column) {
            var index = $.inArray(column, this.columns);
            if (index != -1) {
                this.columns.splice(index, 1);
                this.removeView(column);
            }
        },

        setColumnList: function(list) {
            var columns = this.getColumnLookup(),
                formulas = this.getFormulaLookup();
            for(var i in list) {
                //  Only add columns isn't already added
                if ($.inArray(list[i], this.columns) === -1) {
                    if (columns[list[i]]) {
                        this.addColumn(columns[list[i]]);
                    } else if (formulas[list[i]]) {
                        this.addColumn(formulas[list[i]]);
                    }
                }
            }

            if (this.columns.length) {
                for (var j in this.columns) {
                    //  Remove columns not in the incoming list
                    if ($.inArray(this.columns[j], list) === -1) {
                        this.removeColumn(this.columns[j]);
                    }
                }
            }
        },

        getColumnList: function() {
            return this.columns;
        },

        getItemContainer: function() {
            if (!this.itemContainer) {
                this.itemContainer = $('<div class="report-column-selected-container"></div>');
            }

            return this.itemContainer;
        },

        getFormContainer: function() {
            if (!this.formContainer) {
                this.formContainer = $('<div class="report-column-selected pull-left"></div>');
                this.formContainer.append(this.getFormHeader());
                this.formContainer.append(this.getItemContainer());
            }

            return this.formContainer;
        },

        getFormHeader: function() {
            if (!this.formHeader) {
                this.formHeader = $('<h5>Columns Selected:</h5>');
            }

            return this.formHeader;
        },

        render: function() {
            if (this.container) {
                this.container.append(this.getFormContainer());
            }

            return this.getFormContainer();
        }
    });

    GD.ReportSelectedColumnForm = ReportSelectedColumnForm;

})(window ? window : window, jQuery);
