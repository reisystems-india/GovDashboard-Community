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
        throw new Error('DatasetDataView requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DatasetDataView requires GD');
    }

    var GD = global.GD;

    var DatasetDataView = GD.View.extend({
        previewStatsContainer: null,
        previewStats: null,

        previewTableContainer: null,
        previewTable: null,

        buttonsContainer: null,
        previewButton: null,
        uploadButton: null,
        historyButton: null,

        uploadContainer: null,
        uploadInput: null,

        historyContainer: null,
        historyTable: null,

        activeView: null,
        prompt: null,

        init: function ( object, container, options ) {
            this._super(object, container, options);
            this.activeView = this.getPreviewTableContainer();

            var _this = this;
            $(document).on('createView', function () {
                _this.toggleCreateView();
            });

            $(document).on('truncatedDataset', function () {
                _this.getTruncateButton().addClass('disabled');
            });

            $(document).on('addedDatasetData', function (e) {
                _this.datasetStats = e['dataset'].stats;
                _this.loadPreviewStats(e['dataset'].stats);
                if ( _this.datasetStats['records'] > 0 ) {
                    _this.getTruncateButton().removeClass('disabled');
                }
            });
        },

        validate: function () {
            var messages = [];
            return messages;
        },

        toggleReadOnlyView: function() {
            this.getUploadButton().addClass('disabled');
            this.getTruncateButton().addClass('disabled');
            this.getHistoryButton().addClass('disabled');
            this.getTemplateButton().addClass('disabled');
            this.getTemplateButton().click(function (e) {
                e.preventDefault();
            })
        },

        toggleCreateView: function () {
            this.activeView = this.getUploadContainer();
            this.getPreviewButton().addClass('disabled');
            this.getHistoryButton().addClass('disabled');
            this.getTruncateButton().addClass('disabled');
            this.getUploadContainer().show();
        },

        loadedDataset: function ( dataset, reloadView ) {
            this.object = dataset;
            if ( reloadView && typeof this.object.getName() != 'undefined' && this.object.getName() != null ) {
                this.getTemplateButton().removeClass('disabled');
                this.getTemplateButton().attr('href', '/dataset/' + this.object.getName() + '/template?f=csv');
            }
        },

        loadedPreview: function ( previewData ) {
            this.getPreviewButton().removeClass('disabled');
            this.loadPreviewTable(previewData);
            this.clickedPreview();
        },

        loadedStats: function ( datasetStats ) {
            this.datasetStats = datasetStats;
            this.loadPreviewStats(datasetStats);
        },

        loadedChangelog: function ( columns, changelog ) {
            this.getHistoryButton().removeClass('disabled');
            this.loadHistory(columns, changelog);
        },

        loadComplete: function () {
            if ( this.datasetStats && this.datasetStats['records'] > 0 ) {
                this.getTruncateButton().removeClass('disabled');
            } else {
                this.getTruncateButton().addClass('disabled');
            }
        },

        getPreviewStats: function () {
            if ( this.previewStats == null ) {
                this.previewStats = $('<small></small>');
            }

            return this.previewStats;
        },

        getPreviewStatsContainer: function () {
            if ( this.previewStatsContainer == null ) {
                this.previewStatsContainer = $('<div class="row"></div>');
                var row = $('<div class="col-md-12"></div>');
                var container = $('<div class="row"></div>');
                var header = $('<h3>Data </h3>');
                header.append(this.getPreviewStats());
                container.append($('<div class="col-md-5"></div>').append(header));
                container.append(this.getButtonsContainer());
                row.append(container);
                this.previewStatsContainer.append(row);

                var _this = this;
                $(document).on('loadedStats', function (e) {
                    _this.loadedStats(e['stats']);
                });
            }

            return this.previewStatsContainer;
        },

        getHistoryTable: function () {
            if ( this.historyTable == null ) {
                this.historyTable = $('<table class="table table-condensed table-bordered table-striped"></table>');
            }

            return this.historyTable;
        },

        getHistoryContainer: function () {
            if ( this.historyContainer == null ) {
                this.historyContainer = $('<div class="row" style="display:none;"></div>');
                var container = $('<div class="col-md-12"></div>');
                container.append(this.getHistoryTable());
                this.historyContainer.append(container);

                var _this = this;
                $(document).on('loadedChangelog', function (e) {
                    _this.loadedChangelog(e['columns'], e['changelog']);
                });
            }

            return this.historyContainer;
        },

        getUploadInput: function () {
            if ( this.uploadInput == null ) {
                this.uploadInput = $('<div></div>');
            }

            return this.uploadInput;
        },

        getUploadContainer: function () {
            if ( this.uploadContainer == null ) {
                this.uploadContainer = $('<div class="row" style="display:none;"></div>');

                var container = $('<div class="col-md-6 col-md-offset-3"><p>Begin by selecting your data file, and then you can configure the data columns.</p></div>');
                container.append(this.getUploadInput());

                this.uploadContainer.append(container);
            }

            return this.uploadContainer;
        },

        getPreviewTable: function () {
            if ( this.previewTable == null ) {
                this.previewTable = $('<table class="table table-condensed table-bordered table-striped"></table>');
            }

            return this.previewTable;
        },

        getPreviewTableContainer: function () {
            if ( this.previewTableContainer == null ) {
                this.previewTableContainer = $('<div class="row" style="display:none;"></div>');
                var container = $('<div class="col-md-12"></div>');
                container.append(this.getPreviewTable());

                //  Make table scrollable
                container.css('overflow', 'auto');

                this.previewTableContainer.append(container);

                var _this = this;
                $(document).on('loadedDataset', function (e) {
                    _this.loadedDataset(e['dataset'], e['reloadView']);
                });

                $(document).on('loadedPreview', function (e) {
                    _this.loadedPreview(e['previewData']);
                });
            }

            return this.previewTableContainer;
        },

        getButtonsContainer: function () {
            if ( this.buttonsContainer == null ) {
                this.buttonsContainer = $('<div class="col-md-7"></div>');
                this.buttonsContainer.append($('<div class="btn-group pull-right data-actions"></div>').append(this.getUploadButton(),this.getPreviewButton(),this.getHistoryButton(),this.getTemplateButton(),this.getTruncateButton()));
            }

            return this.buttonsContainer;
        },

        clickedUpload: function () {
            if ( !this.getUploadButton().hasClass('disabled') ) {
                $('a.btn').removeClass('active');
                this.getUploadButton().addClass('active');
                this.activeView.hide();
                this.activeView = this.getUploadContainer();
                this.activeView.fadeIn(500);
            }
        },

        getUploadButton: function () {
            if ( this.uploadButton == null ) {
                this.uploadButton = $('<a class="btn btn-default active"><span class="glyphicon glyphicon-upload"></span> Upload</a>');

                var _this = this;
                this.uploadButton.on('click', function () {
                    _this.clickedUpload();
                });
            }

            return this.uploadButton;
        },

        getTemplateButton: function () {
            if ( this.templateButton == null ) {
                this.templateButton = $('<a target="_blank" class="btn btn-default disabled"><span class="glyphicon glyphicon-download"></span> Template</a>');
            }

            return this.templateButton;
        },

        clickedPreview: function () {
            if ( !this.getPreviewButton().hasClass('disabled') ) {
                $('a.btn').removeClass('active');
                this.getPreviewButton().addClass('active');
                this.activeView.hide();
                this.activeView = this.getPreviewTableContainer();
                this.activeView.fadeIn(500);
            }
        },

        getPreviewButton: function () {
            if ( this.previewButton == null ) {
                this.previewButton = $('<a class="btn btn-default disabled"><span class="glyphicon glyphicon-eye-open"></span> Preview</a>');
                var _this = this;
                this.previewButton.on('click', function () {
                    _this.clickedPreview();
                });
            }

            return this.previewButton;
        },

        clickedTruncate: function () {
            if ( !this.getTruncateButton().hasClass('disabled') ) {
                this.getPromptContainer().empty();
                var template = [
                    '<div class="modal-dialog">',
                    '  <div class="modal-content">',
                    '    <div class="modal-header"><h3>Dataset Truncate</h3></div>',
                    '    <div class="modal-body">Are you sure you want to truncate this dataset?</div>',
                    '    <div class="modal-footer">',
                    '      <button class="btn btn-primary yes" data-dismiss="modal" aria-hidden="true">Yes</button>',
                    '      <button class="btn" data-dismiss="modal" aria-hidden="true">No</button>',
                    '    </div>',
                    '  </div>',
                    '</div>'
                ].join("\n");

                this.getPromptContainer().append(template);

                var _this = this;
                $('.yes', this.getPromptContainer()).on('click', function () {
                    _this.getPromptContainer().modal('hide');
                    $.event.trigger({
                        'type': 'truncateDataset'
                    });
                });

                this.getPromptContainer().modal({'backdrop':'static'});
            }
        },

        getTruncateButton: function () {
            if ( this.truncateButton == null ) {
                this.truncateButton = $('<a class="btn btn-default disabled"><span class="glyphicon glyphicon-trash"></span> Truncate</a>');

                var _this = this;
                this.truncateButton.on('click', function () {
                    _this.clickedTruncate();
                });
            }

            return this.truncateButton;
        },

        clickedHistory: function () {
            if ( !this.getHistoryButton().hasClass('disabled') ) {
                $('a.btn').removeClass('active');
                this.getHistoryButton().addClass('active');
                this.activeView.hide();
                this.activeView = this.getHistoryContainer();
                this.activeView.fadeIn(500);
            }
        },

        getHistoryButton: function () {
            if ( this.historyButton == null ) {
                this.historyButton = $('<a class="btn btn-default disabled"><span class="glyphicon glyphicon-time"></span> History</a>');

                var _this = this;
                this.historyButton.on('click', function () {
                    _this.clickedHistory();
                });
            }

            return this.historyButton;
        },

        //  TODO Move all modal stuff to it's own system
        getPromptContainer: function () {
            if ( this.prompt == null ) {
                this.prompt = $('<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>');
            }

            return this.prompt;
        },

        loadHistory: function ( columns, data ) {
            var historyTable = this.getHistoryTable();
            historyTable.empty();
            var header = $('<tr></tr>');

            $.each(columns, function (i, c) {
                header.append('<th>' + c['title'] + '</th>');
            });

            historyTable.append(header);

            $.each(data, function (i, r) {
                var row = $('<tr></tr>');
                $.each(columns, function(i, c) {
                    var value = c['getValue'](r);

                    if ( c['type'] == 'url' ) {
                        row.append('<td><a href="' + value + '">' + c['getUrlText'](r) + '</a></td>');
                    } else {
                        row.append('<td>' + value + '</td>');
                    }
                });
                historyTable.append(row);
            });
        },

        loadPreviewStats: function ( data ) {
            var stats = this.getPreviewStats();
            if ( data ) {
                stats.text(data['records'] + ' Rows, ' + data['columns']['visible'] + ' Columns');
            }
        },

        loadPreviewTable: function ( data ) {
            var table = this.getPreviewTable();
            table.empty();
            var header = $('<tr></tr>');
            var columns = [];
            $.each(this.object.columns, function (i, c) {
                if ( c['used'] != false && c['visible'] != false ) {
                    var cell = $('<th>' + c.publicName + '</th>');
                    if ( c.persistence == GD.Column.PERSISTENCE__CALCULATED() ) {
                        cell.css('font-style','italic');
                    }
                    header.append(cell);
                    columns.push(c);
                }
            });
            table.append(header);

            if ( data != null && data.length ) {
                $.each(data, function (i, r) {
                    var row = $('<tr></tr>');
                    $.each(columns, function(i, c) {
                        var value = String(r[c.name]);
                        var cell = $('<td></td>');
                        cell.css('min-width', '75px');
                        if ( c.persistence == GD.Column.PERSISTENCE__CALCULATED() ) {
                            cell.css('font-style','italic');
                        }
                        if ( value.length >= 30 ) {
                            var anchor = $('<a data-toggle="tooltip">...</a>');
                            anchor.popover({
                                'placement': 'top',
                                'trigger': 'hover',
                                'content': value
                            });
                            cell.append(value.substr(0, 30), anchor);
                        } else {
                            cell.append(value);
                        }
                        row.append(cell);
                    });
                    table.append(row);
                });
            } else {
                table.append('<tr><td colspan="'+columns.length+'" style="text-align:center">There are no records.</td></tr>');
            }
        },

        render: function () {
            var _this = this;
            var container = this.container;
            if (!container) {
                container = $('<div></div>');
            }

            container.append(this.getPreviewStatsContainer(), this.getPreviewTableContainer(), this.getUploadContainer(), this.getHistoryContainer(), this.getPromptContainer());

            var unbind = false;
            $(document).on('loadComplete', function() {
                if (!unbind) {
                    _this.loadComplete();
                }
            });

            $(document).on('readOnlyView', function() {
                _this.toggleReadOnlyView();
                unbind = true;
            });
            return container;
        }
    });

    // add to global space
    global.GD.DatasetDataView = DatasetDataView;

})(typeof window === 'undefined' ? this : window, jQuery);