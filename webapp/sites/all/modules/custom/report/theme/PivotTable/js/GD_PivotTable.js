!function(global) {
    !function($,undefined) {
        if (global.GD == null) {
            throw new Error('Pivot Table requires GD');
        }

        var GD = global.GD;

        global.GD_PIVOT_COLUMN_LIMIT = 100;
        global.GD_PIVOT_ROW_LIMIT = 100;

        GD.PivotTable = GD.View.extend({
            init: function (object, container, options) {
                this._super(null, container, options);
                this.reportId = object;
                this.width = $(this.container).width();
                this.height = $(this.container).height();
                this.allowDragAndDrop = options ? options['allowDragDrop'] : true;
                var _this = this;
                $(document).off('update.gd.pivot').on('update.gd.pivot', function (event, options) {
                    _this.getData(options['rows'], options['columns'], options['measure'], function (data) {
                        if (options['callback']) {
                            options['callback'](data);
                        }
                    });
                });
                $(document).off('preprocess.report.save').on('preprocess.report.save', function (event, options) {
                    var cols = [], rows = [];
                    $('.pvtCols').find('span').each(function () {
                        return cols.push($(this).attr("id"));
                    });
                    $('.pvtRows').find('span').each(function () {
                        return rows.push($(this).attr("id"));
                    });
                    options['config']['visual']['cols'] = cols;
                    options['config']['visual']['rows'] = rows;
                });

                this.allTotal = options['allTotal'];
                this.initTable(null);
            },


            getColumns: function () {
                return this.options ? (this.options['columns'] ? this.options['columns'] : []) : [];
            },

            getPivotOptions: function () {
                return {
                    cols: this.getCols(),
                    rows: this.getRows(),
                    columns: this.getColumns(),
                    aggregators: this.getAggregators(),
                    renderers: this.getRenderers(),
                    hiddenAttributes: this.getHiddenAttributes(),
                    lookup: this.lookup
                };
            },

            getCols: function () {
                return this.options ? (this.options['cols'] ? this.options['cols'] : []) : [];
            },

            getRows: function () {
                return this.options ? (this.options['rows'] ? this.options['rows'] : []) : [];
            },

            getHiddenAttributes: function () {
                var hidden = [];

                if (this.options && this.options['measures']) {
                    for (var id in this.options['measures']) {
                        hidden.push(this.options['measures'][id]['name']);
                    }
                }

                return hidden;
            },

            getRenderers: function () {
                var _this = this;
                return {
                    Table: function (pvtData, opts) {
                        opts['width'] = _this.width - _this.getWidgetContainer().find('.pvtUnused').width() - 14;
                        opts['height'] = _this.height - _this.getWidgetContainer().find('.pvtUnused').height() - 51;
                        opts['lookupCell'] = function (column, row) {
                            return _this.lookupCellData(column, row);
                        };
                        opts['lookupColumn'] = function (column) {
                            return _this.lookupColumnTotals(column);
                        };
                        opts['lookupRow'] = function (row) {
                            return _this.lookupRowTotals(row);
                        };
                        opts['lookupTotal'] = function () {
                            return _this.lookupAllTotal();
                        };
                        opts['draft'] = _this.isDraft();
                        return $.pivotUtilities.renderers['Table'](pvtData, opts);
                    }
                };
            },

            isDraft: function () {
                return (this.options ? (this.options['draft'] ? true : false) : false);
            },

            getAggregators: function () {
                var aggregators = {};
                if (this.options && this.options['measures']) {
                    var _this = this;
                    for (var id in this.options['measures']) {
                        aggregators[id] = {
                            name: this.options['measures'][id]['name'],
                            aggregate: function () {
                                return _this.options['measures'][id]['name'];
                            }
                        };
                    }
                }
                return aggregators;
            },

            getUrlOptions: function () {
                return this.options ? (this.options['url'] ? this.options['url'] : null) : null;
            },

            getCallbackData: function (columns, measure) {
                return this.options ? (this.options['getDataObject'] ? this.options['getDataObject'](columns, measure) : null) : null;
            },

            getCallbackType: function () {
                return this.options ? (this.options['callType'] ? this.options['callType'] : "GET") : "GET";
            },

            getData: function (rows, columns, measure, callback) {
                if (!this.inProgress) {
                    this.inProgress = true;
                    $('.pvtAggregator, .row-paging select, .col-paging select').attr("disabled", "");
                    $('.ui-sortable li').addClass('ui-state-disabled');
                    $('.ui-sortable').sortable({
                        cancel: ".ui-state-disabled"
                    });
                    this.getLoadingOverlay().show();
                } else {
                    return;
                }

                var options = this.getUrlOptions();
                var deferred = [];

                //  All rows and columns
                deferred.push($.ajax({
                    url: options['url'],
                    data: this.getCallbackData($.merge($.merge([], rows), columns), measure),
                    type: this.getCallbackType()
                }));

                //  Total for columns
                deferred.push($.ajax({
                    url: options['url'],
                    data: this.getCallbackData($.merge([], columns), measure),
                    type: this.getCallbackType()
                }));

                //  Total for rows
                deferred.push($.ajax({
                    url: options['url'],
                    data: this.getCallbackData($.merge([], rows), measure),
                    type: this.getCallbackType()
                }));

                //  All total
                deferred.push($.ajax({
                    url: options['url'],
                    data: this.getCallbackData([measure]),
                    type: this.getCallbackType()
                }));

                var _this = this;
                $.when.apply($, deferred).done(function (allData, columnTotals, rowTotals, allTotal) {
                    var data = _this.parseCellData(columns, rows, measure, allData[0]['response']);
                    _this.parseTotalData(columns, rows, measure, columnTotals[0]['response'], rowTotals[0]['response'], allTotal[0]['response']);
                    if (callback) {
                        callback(data);
                    }
                }).fail(function (jqXHR, textStatus, errorThrown) {

                }).always(function () {
                    _this.inProgress = false;
                    $('.pvtAggregator, .row-paging select, .col-paging select').removeAttr("disabled");
                    _this.getLoadingOverlay().hide();
                    if (_this.allowDragAndDrop) {
                        $('.ui-sortable li').removeClass('ui-state-disabled');
                    }
                });
            },

            lookupCellData: function (column, row) {
                var data = ' ';

                if (this.cellLookupMap) {
                    //  Can't use truthy/falsey because 0 is a valid data point
                    if (this.cellLookupMap[column] !== null && typeof this.cellLookupMap[column] !== 'undefined') {
                        if (this.cellLookupMap[column][row] !== null && typeof this.cellLookupMap[column][row] !== 'undefined') {
                            data = this.cellLookupMap[column][row];
                        }
                    }
                }

                return data;
            },

            lookupRowTotals: function (row) {
                return (!this.rowTotalLookupMap || this.rowTotalLookupMap[row] === null || typeof this.rowTotalLookupMap[row] === 'undefined') ? ' ' : this.rowTotalLookupMap[row];
            },

            lookupColumnTotals: function (column) {
                return (!this.columnTotalLookupMap || this.columnTotalLookupMap[column] === null || typeof this.columnTotalLookupMap[column] === 'undefined') ? ' ' : this.columnTotalLookupMap[column];
            },

            lookupAllTotal: function () {
                return (this.allTotal === null || typeof this.allTotal === 'undefined') ? ' ' : this.allTotal;
            },

            parseCellData: function (columns, rows, measure, allData) {
                var d = [];
                var columnMap = {};
                this.cellLookupMap = {};
                $.each(allData['fields'], function (i, f) {
                    columnMap[f['name']] = f['title'];
                });
                var _this = this;
                $.each(allData['data'], function (i, record) {
                    var r = {};
                    for (var id in record['record']) {
                        r[columnMap[id]] = record['record'][id];
                    }

                    if (columns && columns.length != 0 && rows && rows.length != 0 && measure) {
                        var cKey = [];
                        $.each(columns, function (i, c) {
                            if (record['record'][c]) {
                                cKey.push(record['record'][c]);
                            } else {
                                cKey.push("null");
                            }
                        });
                        cKey = cKey.join(" ");
                        if (!_this.cellLookupMap[cKey]) {
                            _this.cellLookupMap[cKey] = {};
                        }
                        var rKey = [];
                        $.each(rows, function (i, r) {
                            if (record['record'][r]) {
                                rKey.push(record['record'][r]);
                            } else {
                                rKey.push("null");
                            }
                        });
                        rKey = rKey.join(" ");
                        _this.cellLookupMap[cKey][rKey] = record['record'][measure];
                    }

                    d.push(r);
                });
                return d;
            },

            parseTotalData: function (columns, rows, measure, columnTotals, rowTotals, allTotal) {
                var _this = this;
                this.columnTotalLookupMap = {};
                if (columns && columns.length != 0) {
                    $.each(columnTotals['data'], function (i, record) {
                        var key = [];
                        $.each(columns, function (i, c) {
                            if (record['record'][c]) {
                                key.push(record['record'][c]);
                            } else {
                                key.push("null");
                            }
                        });
                        _this.columnTotalLookupMap[key.join(" ")] = record['record'][measure];
                    });
                }
                this.rowTotalLookupMap = {};
                if (rows && rows.length != 0) {
                    $.each(rowTotals['data'], function (i, record) {
                        var key = [];
                        $.each(rows, function (i, r) {
                            if (record['record'][r]) {
                                key.push(record['record'][r]);
                            } else {
                                key.push("null");
                            }
                        });
                        _this.rowTotalLookupMap[key.join(" ")] = record['record'][measure];
                    });
                }
                if (allTotal['data'] && allTotal['data'][0]) {
                    this.allTotal = allTotal['data'][0]['record'][measure];
                }
            },

            getWidgetContainer: function () {
                if (!this.widgetContainer) {
                    this.widgetContainer = $('<div class="gd-widget-container gd-widget-pivot-table"></div>');
                }

                return this.widgetContainer;
            },

            getLoadingOverlay: function () {
                if (!this.loadingOverlay) {
                    this.loadingOverlay = $('<div class="ldng" style="position:absolute;z-index:1;display:none;"></div>');
                }

                return this.loadingOverlay;
            },

            initTable: function (data) {
                if (this.container) {
                    $(this.container).append(this.getLoadingOverlay(), this.getWidgetContainer());
                    this.getWidgetContainer().pivotUI(data, this.getPivotOptions());
                }
                if (!this.allowDragAndDrop) {
                    $('.ui-sortable li').addClass('ui-state-disabled');
                    $('.ui-sortable').sortable({
                        cancel: ".ui-state-disabled"
                    });
                }
            }
        });

        GD.PivotTableFactory = {
            createTable: function (id, container, options) {
                if (!$(container).find('div.gd-widget-pivot-table').length) {
                    return new GD.PivotTable(id, container, options);
                } else {
                    return null;
                }
            }
        };
    }(global.GD_jQuery ? global.GD_jQuery : jQuery);
}(window ? window : window);