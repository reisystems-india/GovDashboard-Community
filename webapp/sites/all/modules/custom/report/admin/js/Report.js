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
        throw new Error('Report requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('Report requires GD');
    }

    var GD = global.GD;

    var Report = GD.Class.extend({
        options: null,

        id: 0,
        title: null,
        description: null,
        type: null,
        author: null,
        config: {
            config: {
                chartType: "table"
            },
            columnConfigs: [],
            model: {
                columns: [],
                datasets: [],
                filters: [],
                orderBy: [],
                limit: null,
                offset: 0,
                columnOrder: []
            },
            visual: {}
        },
        customView: null,
        datasource: null,
        filters: [],
        datasets: [],

        init: function(object, options) {
            if ( object ) {
                $.extend(this, object);
            }

            this.options = options;
            if ( object ) {
                for (var k in object.filters) {
                    this.filters[k] = new GD.ReportFilter(object.filters[k]);
                }

                if ("config" in object && object.config.model.formulas) {
                    for (var l in object.config.model.formulas) {
                        this.config.model.formulas[l] = new GD.Formula(object.config.model.formulas[l]);
                    }
                }
            }
        },

        validate: function() {
            //  Validate columns and filters after ReportDataset has been loaded
            var valid = true;
            var c = this.getColumns();
            for (var i in c) {
                if (!this.getDataset().findColumn(c[i]) && !this.getFormulaLookup()[c[i]]) {
                    global.GD.ReportBuilderMessagingView.addWarnings('Unsupported column selected: ' + c[i]);
                    valid = false;
                }
            }

            var f = this.getFilters(),
                j;

            for (j in f) {
                if(f[j]){
                    var columnName = f[j]['column']?f[j]['column']["name"] || f[j]['column']:"";
                    if (!this.getDataset().findColumn(columnName) && !this.getFormula(columnName)) {
                        global.GD.ReportBuilderMessagingView.addWarnings('Unsupported filter selected: ' + f[j]['name']);
                        valid = false;
                    }
                }

            }

            var color = this.getVisualizationOption('useColumnDataForColor');
            if (color) {
                if (!this.getDataset().findColumn(color) && !this.getFormulaLookup()[color]) {
                    global.GD.ReportBuilderMessagingView.addWarnings('Unsupported column for color selected: ' + color);
                }
            }

            global.GD.ReportBuilderMessagingView.displayMessages();

            //update notification in case of unsupported column
            GovdashReportBuilder.configButtons.configure.updateNotification();
            //flag to keep messages not to clean in preview load.
            this.reportValid = valid;
            return valid;
        },

        isNew: function() {
            return !this.getId();
        },

        getId: function () {
            return this.id;
        },

        setId: function ( id ) {
            this.id = id;
        },

        getName: function () {
            return this.title;
        },

        setName: function ( title ) {
            this.title = title;
        },

        getDescription: function () {
            return this.description;
        },

        setDescription: function ( desc ) {
            this.description = desc;
        },

        getAuthor: function () {
            return this.author;
        },

        getFilters: function() {
            return this.filters;
        },

        addFilter: function(f) {
            this.filters.push(f);
        },

        removeFilter: function(f) {
            var i = null;
            for (var key in this.filters) {
                if (GD.Filter.compareFilters(this.filters[key], f)) {
                    i = key;
                    break;
                }
            }
            if (i) {
                this.filters.splice(i, 1);
            }
        },

        editFilter: function (f) {
            var e = function(g) {
                g.column = f['column'];
                g.exposed = f['exposed'];
                g.exposedType = f['exposedType'];
                g.operator = f['operator'];
                g.value = f['value'];
            };

            for (var key in this.filters) {
                if (GD.Filter.compareFilters(this.filters[key], f)) {
                    e(this.filters[key]);
                    break;
                }
            }
        },

        getFiltersConfig: function() {
            var f = [];

            for(var key in this.filters) {
                f.push(this.filters[key].getConfig());
            }

            return f;
        },
        
        getColumnConfigs: function() {
            return this.config.columnConfigs;
        },
        
        setColumnConfigs: function(columnConfigs) {
            this.config.columnConfigs = columnConfigs;
        },

        setColumnConfig: function ( columnId, format  ) {

            var configs = this.getColumnConfigs();
            var found = false;
            for ( var i = 0, configs_length = configs.length; i < configs_length; i++ ) {
                if ( columnId == configs[i].columnId ) {
                    configs[i] = format;
                    found = true;
                    break;
                }
            }

            if ( !found ) {
                configs.push(format);
            }
        },

        findColumnConfig: function ( columnId ) {
            var config = null;
            var configs = this.getColumnConfigs();
            for ( var i = 0, configs_length = configs.length; i < configs_length; i++ ) {
                if ( columnId == configs[i].columnId ) {
                    config = configs[i];
                    break;
                }
            }
            return config;
        },
        
        getChartType: function() {
            return this.config.config.chartType;
        },

        setChartType: function ( type ) {
            this.config.config.chartType = type;
        },

        getFormula: function(name) {
            return this.getFormulaLookup()[name];
        },

        getFormulaLookup: function() {
            if (!this.formulaLookup) {
                this.formulaLookup = {};
                var raw = this.getFormulas();
                for (var i in raw) {
                    this.formulaLookup[raw[i].getID()] = raw[i];
                }
            }

            return this.formulaLookup;
        },

        getFormulas: function() {
            return this.config.model.formulas;
        },

        removeFormula: function(formula) {
            var i = null;
            for (var key in this.config.model.formulas) {
                if (GD.Formula.compareFormulas(this.config.model.formulas[key], formula)) {
                    i = key;
                    break;
                }
            }
            if (i) {
                this.config.model.formulas.splice(i, 1);
                delete this.getFormulaLookup()[formula.getID()];
            }

            //  Run validation code after removing formulas to trigger warnings
            this.validate();

            $(document).trigger({
                type: 'removed.report.formulas',
                removed: formula,
                report: this
            });
        },

        addFormula: function(formula) {
            if (!this.config.model.formulas) {
                this.config.model.formulas = [];
            }
            formula.generateID();
            this.getFormulaLookup()[formula.getID()] = formula;
            this.config.model.formulas.push(formula);
            $(document).trigger({
                type: 'added.report.formulas',
                added: formula,
                report: this
            });
        },

        editFormula: function(formula) {
            var formulas = this.getFormulas();
            var index = -1;
            for (var i in formulas) {
                if (formulas[i].getID() === formula.getID()) {
                    index = i;
                }
            }

            if (index) {
                formulas[index].setName(formula.getName());
                formulas[index].setExpression(formula.getExpression());
                formulas[index].setType(formula.getType());
                formulas[index].setExpressionLanguage(formula.getExpressionLanguage());
                formulas[index].setVersion(formula.getVersion());
            }

            $(document).trigger({
                type: 'updated.report.formulas',
                updated: formulas[index],
                report: this
            });
        },

        setFormulas: function(columns) {
            this.config.model.formulas = columns;
            $(document).trigger({
                type: 'changed.report.formulas'
            });
        },
        
        getColumns: function() {
            return this.config.model.columns;
        },
        
        setColumns: function(columns) {
            this.config.model.columns = columns;
            $(document).trigger({
                type: 'changed.report.columns'
            });
        },

        getAvailableMeasures: function(refresh) {
            if (!this.availableMeasures || refresh) {
                var datasetMeasures = this.getDataset().getAvailableMeasures();
                var formulaMeasures = [];
                var formulas = this.getFormulas();
                if (formulas) {
                    $.each(formulas, function(i, formula) {
                        if (GD.Formula.isMeasure(formula)) {
                            formulaMeasures.push(formula);
                        }
                    });
                }
                this.availableMeasures = $.merge(formulaMeasures, datasetMeasures);
            }

            return this.availableMeasures;
        },

        getUsableFormulaColumns: function (force) {
            if (!this.usableColumns || force) {
                this.usableColumns = [];
                var _this = this;
                $.each(this.getDataset().getColumns(), function (i, c) {
                    if (GD.Formula.canBeUsedInCalculation(c['id'])) {
                        _this.usableColumns.push(c);
                    }
                });

                if (this.getFormulas()) {
                    $.each(this.getFormulas(), function (i, f) {
                        _this.usableColumns.push(f);
                    });
                }
            }

            return this.usableColumns;
        },

        getColumn: function ( name ) {
            var column = this.datasets[0].getColumn(name);
            if (!column) {
                column = this.getFormula(name);
            }
            return column;
        },

        getColumnDisplayOptions: function () {
            if ( !this.config.visual.series || $.isArray(this.config.visual.series)) {
                this.config.visual.series = {};
            }
            return this.config.visual.series;
        },
        
        getTrafficLightOptions: function () {
            if ( !this.config.visual.traffic  || $.isArray(this.config.visual.traffic)) {
                this.config.visual.traffic = {};
            }
            return this.config.visual.traffic;
        },

        removeColumnDisplayOption: function ( option ) {
            if ( this.getColumnDisplayOptions()[option.columnId] ) {
                delete this.getColumnDisplayOptions()[option.columnId];
            }
        },
        
        removeTrafficLightOption: function ( option ) {
            if ( this.getTrafficLightOptions()[option.columnId] ) {
                delete this.getTrafficLightOptions()[option.columnId];
            }
        },

        setColumnDisplayOption: function ( option ) {
            this.getColumnDisplayOptions()[option.columnId] = option;
        },

        setDataset: function ( ReportDataset ) {
            this.datasets[0] = ReportDataset;
            this.validate();
        },

        getDataset: function() {
            if ( this.datasets[0] ) {
                return this.datasets[0];
            } else {
                return null;
            }
        },

        getDatasets: function () {
            return this.datasets;
        },

        getDatasetName: function() {
            if ( this.datasets[0] ) {
                return this.datasets[0].name;
            } else {
                return null;
            }
        },

        getDatasetPublicName: function() {
            return this.datasets[0].publicName;
        },

        getDatasetConfig: function () {
            var config = [];
            for ( var key in this.datasets ) {
                config[key] = this.datasets[key].name;
            }
            return config;
        },

        getOrderBys: function() {
            return this.config.model.orderBy;
        },

        setOrderBys: function ( sorts ) {
            this.config.model.orderBy = sorts;
        },

        editOrderBy: function(sort) {
            $.each(this.config.model.orderBy, function(i, s) {
                if (s['id'] == sort['id'] || s['column'] == sort['column']) {
                    s['order'] = sort['order'];
                    return false;
                }
            });
        },

        addOrderBy: function(sort) {
            if (!this.config.model.orderBy)  {
                this.config.model.orderBy = [];
            }
            var id = null;
            if (!$.grep(this.config.model.orderBy, function(e) { return e['column'] === sort['column']; }).length) {
                sort['id'] = this.config.model.orderBy.length;
                this.config.model.orderBy.push(sort);
                id = sort['id'];
            }

            return id;
        },

        removeOrderBy: function(sort) {
            var index = 0;
            $.each(this.config.model.orderBy, function(i, s) {
                if (s['id'] == sort['id'] || s['column'] == sort['column']) {
                    index = i;
                    return false;
                }
            });

            this.config.model.orderBy.splice(index, 1);
        },
        
        setLimit: function( limit ) {
            this.config.model.limit = limit;
        },
        
        getLimit: function() {
            return this.config.model.limit;
        },

        setOffset: function( offset ) {
            this.config.model.offset = offset;
        },
        
        getOffset: function() {
            return this.config.model.offset;
        },
        
        getColumnOrder: function() {
            return this.config.model.columnOrder;
        },

        setColumnOrder: function ( columnOrder ) {
            return this.config.model.columnOrder = columnOrder;
        },

        getVisualizationOption: function ( name ) {
            return this.config.visual[name];
        },

        setVisualizationOption: function ( name, value) {
            this.config.visual[name] = value;
        },

        removeVisualizationOption:function(name){
           delete this.config.visual[name];
        },

        getFooters: function() {
            if (!this.config.visual['footer']) {
                this.config.visual['footer'] = [];
            }

            return this.config.visual['footer'];
        },

        addFooter: function(footer) {
            delete footer['new'];
            this.getFooters().push(footer);
        },

        editFooter: function(footer) {
            var footers = this.getFooters();
            var _this = this;
            $.each(footers, function(i, f) {
                if (f['measure'] == footer['measure']) {
                    _this.getFooters()[i]['text'] = footer['text'];
                    _this.getFooters()[i]['alignment'] = footer['alignment'];
                    _this.getFooters()[i]['measure'] = footer['measure'];
                    _this.getFooters()[i]['size'] = footer['size'];
                    _this.getFooters()[i]['font'] = footer['font'];
                    _this.getFooters()[i]['color'] = footer['color'];
                    _this.getFooters()[i]['format'] = footer['format'];
                    _this.getFooters()[i]['scale'] = footer['scale'];
                    return false;
                }
            });
        },

        removeFooter: function(footer) {
            var footers = this.getFooters();
            var _this = this;
            $.each(footers, function(i, f) {
                if (f['measure'] == footer['measure']) {
                    _this.getFooters().splice(i, 1);
                    return false;
                }
            });
        },
        
        getVisual: function() {
            return this.config.visual;
        },
        
        getCustomView: function() {
            return this.customView;
        },
        
        setCustomView: function(markup) {
            this.customView = markup;
        },
        
        getTags: function() {
            return this.tags;
        },

        setTags: function(tags) {
            this.tags = tags;
        },

        setDatasource: function ( datasource ) {
            this.datasource = datasource;
        },

        getDatasourcePublicName: function() {
            var name = null;

            if (this.datasource) {
                name = this.datasource['publicName'];
            } else if (typeof GovdashReportBuilder != 'undefined') {
                name = GovdashReportBuilder.admin.getActiveDatasource()['publicName'];
            }

            return name;
        },

        getDatasourceName: function() {
            var name = null;

            if (this.datasource) {
                name = this.datasource['name'];
            } else if (typeof GovdashReportBuilder != 'undefined') {
                name = GovdashReportBuilder.admin.getActiveDatasourceName();
            }

            return name;
        },

        getDatasource: function() {
            return this.datasource;
        },

        getFormulasRaw: function() {
            var f = [];
            var formulas = this.getFormulas();
            for (var i in formulas) {
                f.push(formulas[i].getRaw());
            }

            return f;
        },

        getSelectedColumnsTypeCount: function(){
            var numericColumnCount = 0,
                nonNumericColumnCount = 0,
                measureCount = 0,
                columns = this.getColumns();

            for ( var i = 0; i < columns.length; i++ ) {
                var columnDetails = this.getColumn(columns[i]);
                if (columnDetails) {
                    if (columnDetails.type == 'integer' || columnDetails.type == 'number' || columnDetails.type == 'currency' || columnDetails.type == 'percent') {
                        numericColumnCount++;
                    } else {
                        nonNumericColumnCount++;
                    }

                    if (GD.Column.isMeasure(columns[i]) || GD.Formula.isMeasure(columnDetails)) {
                        measureCount++;
                    }
                }
            }

            return {
                numericColumnCount:numericColumnCount,
                nonNumericColumnCount: nonNumericColumnCount,
                measureCount: measureCount
            };
        },


        getConfig: function() {
            return {
                id: this.getId(),
                title: this.getName(),
                description: this.getDescription(),
                datasource: this.getDatasourceName(),
                config: {
                    columnConfigs: this.getColumnConfigs(),
                    config: {
                        chartType: this.getChartType()
                    },
                    model: {
                        formulas: this.getFormulasRaw(),
                        columns: this.getColumns(),
                        datasets: this.getDatasetConfig(),
                        filters: this.getFiltersConfig(),
                        orderBy: this.getOrderBys(),
                        limit: this.getLimit(),
                        offset: this.getOffset(),
                        columnOrder: this.getColumnOrder()
                    },
                    visual: this.getVisual()
                },
                customView: this.getCustomView(),
                tags: this.getTags()
            };
        }
    });

    // add to namespace
    GD.Report = Report;
})(typeof window === 'undefined' ? this : window, jQuery);