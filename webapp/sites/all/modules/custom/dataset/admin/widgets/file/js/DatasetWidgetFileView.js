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
        throw new Error('DatasetWidgetFileView requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DatasetWidgetFileView requires GD');
    }

    var GD = global.GD;

    var DatasetWidgetFileView = GD.DatasetEditView.extend({
        loadedEvents: [],
        requiredEvents: ['dataset', 'datatypes'],

        init: function ( object, container, options ) {
            this._super(object, container, options);

            var _this = this;
            $(document).on('loadedDataset', function (e) {
                _this.loadedDataset(e['dataset'], e['response'], e['reloadView']);
            });

            $(document).on('truncatedDataset', function (e) {
                _this.truncatedDataset(e['dataset'], e['response']);
            });

            $(document).on('addedDatasetData', function (e) {
                _this.addedDatasetData(e['dataset']);
            });
        },

        getDataset: function () {
            var d = {};
            $.extend(d, this.dataset.getRawDataset());
            $.extend(d, this._super());

            d.columns = [];
            var columns = this.columnView.getColumns();
            $.each(columns,function(k,v){
                d.columns.push(v.getRawColumn());
            });
            var index = columns.length;
            var calculated_columns = this.calculatedColumnsView.getColumns();
            $.each(calculated_columns,function(k,v){
                v.setColumnIndex(index);
                d.columns.push(v.getRawColumn());
                index++;
            });

            return new GD.Dataset(d);
        },

        validate: function () {
            var messages = this._super();
            messages = messages.concat(this.dataView.validate());
            messages = messages.concat(this.columnView.validate());
            messages = messages.concat(this.calculatedColumnsView.validate());
            return messages;
        },

        initLayout: function() {
            this._super();
            this.dataView = new GD.DatasetDataView(this.dataset, this.layoutBody, this.options);
            this.columnView = new GD.DatasetColumnsView(this.dataset, this.layoutBody, this.options);
            this.calculatedColumnsView = new GD.DatasetCalculatedColumnsView(this.dataset, this.layoutBody, this.options);
        },

        initUploadWidget: function ( container ) {
            var input = $('<input id="fileupload" type="file" name="files[]" multiple />');

            var p = [
                '<div id="fileUploadProgressBar" class="progress progress-striped active">',
                    '<div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0;">0%</div>',
                '</div>'
            ];

            var progressBar = $(p.join("\n"));

            container.append(input, progressBar);

            progressBar.hide();

            var _this = this;
            input.fileupload({
                url: '/datafile/upload.json?ds='+GovdashAdmin.getActiveDatasourceName(),
                dataType: 'json',
                paramName: 'files[datafile]',
                acceptFileTypes: '/(\.|\/)(csv|zip)$/i',
                add: function (e, data) {
                    progressBar.show();
                    var goUpload = true;
                    var uploadFile = data.files[0];
                    if (!(/\.(csv|zip)$/i).test(uploadFile.name)) {
                        alert('Choose a CSV or ZIP');
                        goUpload = false;
                    }
                    if (goUpload == true) {
                        data.submit();
                    }
                },
                done: function (e, data) {
                    GD.DatasetWidgetFile.handleFileUpload(e, data.result);
                },
                fail: function (e, data) {
                    //  TODO Display error message
                },
                always: function (e, data) {
                    progressBar.hide();
                    progressBar.find('.progress-bar').attr('aria-valuenow',0).css('width',0).html('0%');
                },
                progressall: function (e, data) {
                    var val = parseInt(data.loaded / data.total * 100, 10);
                    progressBar.find('.progress-bar').attr('aria-valuenow',val).css('width',val+'%').html(val+'%');
                }
            }).bind('fileuploadsubmit', function (e, data) {
                if ( _this.dataset != null && !_this.dataset.isNew() ) {
                    data.formData = {
                        'appendTypeRadio': 'append',
                        'datasetName': _this.dataset.getName()
                    };
                }
            });
        },

        loadedEvent: function ( name ) {
            this.loadedEvents.push(name);
            var loadCompleted = true;

            var _this = this;
            $.each(this.requiredEvents, function (i, e) {
                if ($.inArray(e, _this.loadedEvents) === -1) {
                    loadCompleted = false;
                    return false;
                }
            });

            if (loadCompleted) {
                $.event.trigger({
                    'type': 'loadComplete'
                });
            }
        },

        addedDatasetData: function ( dataset ) {
            GD.DatasetFactory.getPreviewData(dataset.name, function ( data ) {
                $.event.trigger({
                    'type': 'loadedPreview',
                    'previewData': data
                });
            });

            $.event.trigger({
                'type': 'loadedStats',
                'stats': dataset.stats
            });

            GD.DatasetFactory.getDatasetHistory(dataset.name, function ( data ) {
                var historyColumns = [
                    {'title':'File', 'getValue': function (r) { return r.name; } },
                    {'title':'Author', 'getValue': function (r) { return r.author.name; } },
                    {'title':'Rows', 'getValue': function (r) { return r.rows; } },
                    {'title':'Update Type', 'getValue': function (r) { return r.action; } },
                    {'title':'Status', 'getValue': function (r) { return r.status; } },
                    {'title':'Changed', 'type':'date', 'getValue': function (r) { return GD.Util.DateFormat.getUSShortDateTime(GD.Util.DateFormat.parseISO8601(r.changed)) }},
                    {'title':'', 'type':'url', 'getUrlText': function (r) { return 'Download'; }, 'getValue': function (r) { return r.uri; } }
                ];

                $.event.trigger({
                    'type': 'loadedChangelog',
                    'columns': historyColumns,
                    'changelog': data
                })
            });
        },

        truncatedDataset: function ( dataset, response ) {
            GD.DatasetFactory.getPreviewData(dataset.name, function ( data ) {
                $.event.trigger({
                    'type': 'loadedPreview',
                    'previewData': data
                })
            });

            GD.DatasetFactory.getDatasetHistory(dataset.name, function ( data ) {
                var historyColumns = [
                    {'title':'File', 'getValue': function (r) { return r.name; } },
                    {'title':'Author', 'getValue': function (r) { return r.author.name; } },
                    {'title':'Rows', 'getValue': function (r) { return r.rows; } },
                    {'title':'Update Type', 'getValue': function (r) { return r.action; } },
                    {'title':'Status', 'getValue': function (r) { return r.status; } },
                    {'title':'Changed', 'type':'date', 'getValue': function (r) { return GD.Util.DateFormat.getUSShortDateTime(GD.Util.DateFormat.parseISO8601(r.changed)) }},
                    {'title':'', 'type':'url', 'getUrlText': function (r) { return 'Download'; }, 'getValue': function (r) { return r.uri; } }
                ];

                $.event.trigger({
                    'type': 'loadedChangelog',
                    'columns': historyColumns,
                    'changelog': data
                })
            });
        },

        loadedDataset: function ( dataset, response, reloadView ) {
            this.loadedEvent('dataset');
            this.dataset = dataset;

            this.loadedEvent('stats');
            $.event.trigger({
                'type': 'loadedStats',
                'stats': dataset.stats
            });

            var _this = this;
            if ( typeof reloadView != 'undefined' ) {
                if (dataset.name != null) {

                    GD.DatasetFactory.getPreviewData(dataset.name, function ( data ) {
                        _this.loadedEvent('preview');
                        $.event.trigger({
                            'type': 'loadedPreview',
                            'previewData': data
                        })
                    });

                    if ( dataset.nid ) {
                        GD.DatasetFactory.getDatasetHistory(dataset.name, function ( data ) {
                            _this.loadedEvent('history');
                            var historyColumns = [
                                {'title':'File', 'getValue': function (r) { return r.name; } },
                                {'title':'Author', 'getValue': function (r) { return r.author.name; } },
                                {'title':'Rows', 'getValue': function (r) { return r.rows; } },
                                {'title':'Update Type', 'getValue': function (r) { return r.action; } },
                                {'title':'Status', 'getValue': function (r) { return r.status; } },
                                {'title':'Changed', 'type':'date', 'getValue': function (r) { return GD.Util.DateFormat.getUSShortDateTime(GD.Util.DateFormat.parseISO8601(r.changed)) }},
                                {'title':'', 'type':'url', 'getUrlText': function (r) { return 'Download'; }, 'getValue': function (r) { return r.uri; } }
                            ];

                            $.event.trigger({
                                'type': 'loadedChangelog',
                                'columns': historyColumns,
                                'changelog': data
                            })
                        });
                    }
                }

                GD.DatasetFactory.getDataTypes(GovdashAdmin.getActiveDatasourceName(), function(data) {
                    _this.loadedEvent('datatypes');
                    $.event.trigger({
                        'type': 'loadedDataTypes',
                        'dataTypes': data
                    })
                });
            }
        },

        render: function () {
            this._super();
            this.dataView.render();
            this.columnView.render();
            this.calculatedColumnsView.render();
            var uploadContainer = this.dataView.getUploadInput();
            this.initUploadWidget(uploadContainer);
        }
    });

    // add to global space
    global.GD.DatasetWidgetFileView = DatasetWidgetFileView;

})(typeof window === 'undefined' ? this : window, jQuery);