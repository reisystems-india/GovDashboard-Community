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

(function(global,$, undefined) {

    if ( typeof $ === 'undefined' ) {
        throw new Error('DatasetWidgetFile requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DatasetWidgetFile requires GD');
    }

    var GD = global.GD;

    var DatasetWidgetFile = {

        name: 'file',
        title: 'File',
        routes: ['/cp/dataset/new/file'],

        Section: null,
        SectionView: null,

        dataset: null,
        dataView: null,
        columnView: null,

        messaging: null,

        dispatch: function ( request ) {
            this.loadCreateViews();
        },

        getHeroText: function () {
            return '<h4>.csv <span>Upload a csv file up to 20MB in size.  It may also be compressed as a zip file.</span></h4>';
        },

        loadCreateViews: function () {
            this.messaging = new GD.MessagingView('#gd-admin-messages');
            this.Section.layoutHeader.find('gd-section-header-left').append('<h1>Dataset Creation</h1>');

            this.view = new GD.DatasetWidgetFileView(this, this.Section.layoutBody);
            this.view.render();

            var _this = this;
            $(document).on("saveDataset", function () {
                _this.saveDataset();
            });
            $(document).on("cancelEdit", function () {
                _this.cancelEdit();
            });

            $.event.trigger({
                'type':'createView'
            });
        },

        loadEditViews: function ( dataset, response ) {
            this.messaging = new GD.MessagingView('#gd-admin-messages');
            this.dataset = dataset;

            this.Section.layoutHeader.find('.gd-section-header-left').append('<h1>' + (dataset.isReadOnly() ? 'Dataset View' : 'Dataset Update') + '</h1>');

            this.view = new GD.DatasetWidgetFileView(this, this.Section.layoutBody);
            this.view.render();

            var _this = this;
            $(document).on("saveDataset", function () {
                _this.saveDataset();
            });
            $(document).on("cancelEdit", function () {
                _this.cancelEdit();
            });
            $(document).on("deleteDataset", function () {
                _this.deleteDataset();
            });

            $(document).on("truncateDataset", function () {
                _this.truncateDataset();
            });

            $(document).on("datasetModificationDetected", function (event,item) {
                _this.handleDatasetModification();
            });

            $(document).ajaxError(function(event, jqXHR, ajaxSettings, thrownError) {

                if ( thrownError ) {
                    _this.messaging.addErrors(thrownError.substring(1)); // because services module adds a colon
                }else if ( jqXHR.responseText != '' ) {
                    var messages = $.parseJSON(jqXHR.responseText);
                    _this.messaging.addErrors(messages);
                }

                _this.messaging.displayMessages();
            });

            $.event.trigger({
                'type': 'loadedDataset',
                'dataset': dataset,
                'response': response,
                'reloadView': true
            });

            if (dataset.isReadOnly()) {
                $.event.trigger({
                    'type': 'readOnlyView'
                });
            }
        },

        validate: function () {
            var messages = this.view.validate();
            this.messaging.addErrors(messages);
            this.messaging.displayMessages();
            return messages.length == 0;
        },

        handleDatasetModification: function () {
            var analyzer = new GD.DatasetModification(this.dataset,this.view.getDataset(),null);
            var datasetContext = analyzer.execute();

            $.event.trigger({
                'type':'changesMade',
                'context': datasetContext,
                'changeType': 'info'
            });
        },

        cancelEdit: function () {
            //  TODO Check logs and prompt if necessary
            location.href = '/cp/dataset?ds='+GovdashAdmin.getActiveDatasourceName();
        },

        //  TODO Define base widget class and move save
        saveDataset: function () {
            if ( this.validate() ) {
                var d = this.view.getDataset();

                //  New Dataset
                if ( d.nid == null ) {
                    d.datasourceName = GovdashAdmin.getActiveDatasourceName();
                    this.showProcessingModal('Create');
                    var _this = this;
                    GD.DatasetFactory.createDataset(d, function ( data, textStatus, jqXHR ) {
                        //  TODO Send success message to edit page
                        location.href = '/cp/dataset?ds='+GovdashAdmin.getActiveDatasourceName();
                    }, function ( jqXHR, textStatus, errorThrown ) {
                        if ( jqXHR.responseText ) {
                            var messages = $.parseJSON(jqXHR.responseText);
                            if ( messages['error'] ) {
                                messages = messages['error'];
                            }
                            _this.messaging.addErrors(messages);
                        } else if ( errorThrown ) {
                            _this.messaging.addErrors(errorThrown);
                        } else {
                            _this.messaging.addErrors("Unknown error while attempting to create save dataset.");
                        }
                        _this.messaging.displayMessages();
                    }, function ( data, textStatus, jqXHR ) {
                        _this.hideProcessingModal();
                    });
                } else {
                    this.promptUserUpdate(d);
                }
            }
        },

        //  TODO Move all modal stuff to it's own system
        showProcessingModal: function ( operation ) {

            var template = [
                '<div class="modal-header">',
                    '<h3>Dataset ' + operation + '</h3>',
                '</div>',
                '<div class="modal-body">',
                    'Processing<br/>',
                    '<div id="datasetProcessingProgressBar" class="progress progress-striped active">',
                        '<div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 0;"></div>',
                    '</div>',
                '</div>'
            ].join("\n");

            this.view.getModalContainer().find('.modal-content').empty().append(template);

            this.view.getModalContainer().modal('show');

            $('#datasetProcessingProgressBar').find('.progress-bar').prop('aria-valuenow',0).css('width',0);

            var interval = setInterval(function() {
                var val = $('#datasetProcessingProgressBar').find('.progress-bar').prop('aria-valuenow') || 0;
                val = val + 1;
                $('#datasetProcessingProgressBar').find('.progress-bar').prop('aria-valuenow',val).css('width',val+'%');

                if ( val > 99 ) {
                    clearInterval(interval);
                }
            }, 10);
        },

        hideProcessingModal: function () {
            this.view.getModalContainer().modal('hide');
        },

        deleteDataset: function () {
            var d = this.view.getDataset();
            this.promptUserDelete(d);
        },

        truncateDataset: function () {
            this.showProcessingModal('Truncate');
            var _this = this;
            var dataset = this.view.getDataset();
            GD.DatasetFactory.truncateDataset(dataset.name, function ( data ) {
                if ( typeof data['errors'] != 'undefined' && data['errors'].length != 0 ) {
                    _this.messaging.addWarnings(data['warnings']);
                    _this.messaging.addErrors(data['messages']['errors']);
                    _this.messaging.displayMessages();
                } else {
                    if ( typeof data['warnings'] != 'undefined' && data['warnings'].length != 0 ) {
                        _this.messaging.addWarnings(data['warnings']);
                    }
                    _this.messaging.addNotices('Dataset truncated successfully.');
                    _this.messaging.displayMessages();
                    _this.dataset.stats.records = 0;
                    $.event.trigger({
                        'type': 'truncatedDataset',
                        'dataset': _this.dataset
                    });
                }
            }, function ( jqXHR, textStatus, errorThrown ) {
                _this.messaging.addErrors('Truncating dataset failed');
                if ( errorThrown ) {
                    _this.messaging.addErrors(errorThrown);
                }

                if ( jqXHR.responseText ) {
                    var messages = $.parseJSON(jqXHR.responseText);
                    _this.messaging.addErrors(messages);
                }
                _this.messaging.displayMessages();
            }, function ( data ) {
                _this.hideProcessingModal();
                $.event.trigger({
                    'type': 'loadedStats',
                    'stats': _this.dataset.stats
                });
            });
        },

        fireUpdateDataset: function ( dataset ) {
            this.showProcessingModal('Update');
            var _this = this;
            GD.DatasetFactory.updateDataset(dataset.name, dataset.getRawDataset(), function ( data ) {
                if ( typeof data['errors'] != 'undefined' && data['errors'].length != 0 ) {
                    _this.messaging.addWarnings(data['warnings']);
                    _this.messaging.addErrors(data['messages']['errors']);
                    _this.messaging.displayMessages();
                } else {
                    if ( typeof data['warnings'] != 'undefined' && data['warnings'].length != 0 ) {
                        _this.messaging.addWarnings(data['warnings']);
                    }
                    _this.messaging.addNotices('Dataset saved successfully.');
                    _this.messaging.displayMessages();
                }
            }, function ( jqXHR, textStatus, errorThrown ) {
                _this.messaging.addErrors('Dataset saving failed.');
                if ( errorThrown ) {
                    _this.messaging.addErrors(errorThrown);
                }

                if ( jqXHR.responseText ) {
                    var messages = $.parseJSON(jqXHR.responseText);
                    _this.messaging.addErrors(messages);
                }
                _this.messaging.displayMessages();
            }, function ( data ) {
                _this.hideProcessingModal();
                GD.DatasetFactory.getDataset(dataset.name, function ( data ) {
                    _this.dataset = new GD.Dataset(data);

                    var analyzer = new GD.DatasetModification(_this.dataset,dataset,null);
                    var datasetContext = analyzer.execute();

                    $.event.trigger({
                        'type':'changesMade',
                        'context': datasetContext,
                        'changeType': 'error'
                    });

                    $.event.trigger({
                        'type': 'loadedDataset',
                        'dataset': _this.dataset
                    });

                    //  Manually reload the preview
                    GD.DatasetFactory.getPreviewData(dataset.name, function ( data ) {
                        $.event.trigger({
                            'type': 'loadedPreview',
                            'previewData': data
                        })
                    });
                });
            });
        },

        fireDeleteDataset: function ( dataset ) {
            this.showProcessingModal('Delete');
            var _this = this;
            GD.DatasetFactory.deleteDataset(dataset.name, function ( data ) {
                //  TODO Send success message to list page
                location.href = '/cp/dataset?ds='+GovdashAdmin.getActiveDatasourceName();
            }, function ( data ) {
                _this.messaging.addErrors('Deleting dataset failed',true);
                _this.messaging.displayMessages();
            }, function ( data ) {
                _this.hideProcessingModal();
            });
        },

        //  TODO Move all modal stuff to it's own system
        promptUserDelete: function ( dataset ) {

            var template = [
                '<div class="modal-header"><div class="modal-title"><h4>Dataset Delete</h4></div></div>',
                '<div class="modal-body">Are you sure you want to delete this dataset?</div>',
                '<div class="modal-footer">',
                '  <button class="btn btn-primary yes" data-dismiss="modal" aria-hidden="true">Yes</button>',
                '  <button class="btn" data-dismiss="modal" aria-hidden="true">No</button>',
                '</div>'
            ].join("\n");

            this.view.getModalContainer().find('.modal-content').empty().append(template);

            var _this = this;
            $('.yes', this.view.getModalContainer()).on('click', function () {
                _this.fireDeleteDataset(dataset);
            });

            this.view.getModalContainer().modal('show');
        },

        //  TODO Move all modal stuff to it's own system
        promptUserUpdate: function ( dataset ) {

            var template = [
                '<div class="modal-header"><div class="modal-title"><h4>Dataset Update</h4></div></div>',
                '<div class="modal-body">Are you sure you want to make changes to this dataset?</div>',
                '<div class="modal-footer">',
                '  <button class="btn btn-primary yes" data-dismiss="modal" aria-hidden="true">Yes</button>',
                '  <button class="btn" data-dismiss="modal" aria-hidden="true">No</button>',
                '</div>'
            ].join("\n");

            this.view.getModalContainer().find('.modal-content').empty().append(template);

            var _this = this;
            $('.yes', this.view.getModalContainer()).on('click', function () {
                _this.fireUpdateDataset(dataset);
            });

            this.view.getModalContainer().modal('show');
        },

        handleFileUpload: function (event, result ) {
            var _this = this;

            if ( typeof result['errors'] != 'undefined' && result['errors'].length != 0 ) {
                _this.messaging.addWarnings(result['warnings']);
                _this.messaging.addErrors(result['errors']);
                _this.messaging.displayMessages();
            } else {
                _this.messaging.addWarnings(result['messages']['warnings']);
                var message = 'Dataset data uploaded successfully.';

                if ( result.dataset ) {

                    if ( result['uploadStats'] ) {
                        message += ' '+ result['uploadStats']['lineCount'] + ' rows processed';

                        var uploadStats = [];
                        if ( result['uploadStats']['insertedRecordCount'] ) {
                            uploadStats.push(result['uploadStats']['insertedRecordCount'] + ' new record'+((result['uploadStats']['insertedRecordCount']>1)?'s':'')+' inserted');
                        }
                        if ( result['uploadStats']['updatedRecordCount'] ) {
                            uploadStats.push(result['uploadStats']['updatedRecordCount'] + ' existing record'+((result['uploadStats']['updatedRecordCount']>1)?'s':'')+' updated');
                        }
                        if ( result['uploadStats']['deletedRecordCount'] ) {
                            uploadStats.push(result['uploadStats']['deletedRecordCount'] + ' existing record'+((result['uploadStats']['deletedRecordCount']>1)?'s':'')+' deleted');
                        }

                        if ( uploadStats.length ) {
                            message += ' (' + uploadStats.join(', ') + ').';
                        } else {
                            message += '.';
                        }
                    }
                } else if ( result['rows'] != null ) {
                    message += ' '+ result['rows'] + ' rows processed.';
                }

                _this.messaging.addNotices(message);
                _this.messaging.displayMessages();

                if ( result.dataset ) {

                    GD.DatasetFactory.getDataset(result.dataset, function(data){
                        if ( typeof data['messages'] != 'undefined' && data['messages'].status == 'validation' && data['messages']['errors'].length != 0 ) {
                            _this.messaging.addErrors(data['messages']['errors']);
                            _this.messaging.displayMessages();
                        } else {
                            if ( typeof data['messages'] != 'undefined' && data['messages']['warnings'].length != 0 ) {
                                _this.messaging.addWarnings(data['messages']['warnings']);
                                _this.messaging.displayMessages();
                            }

                            _this.dataset.stats.records = data.stats.records;
                            $.event.trigger({
                                'type': 'addedDatasetData',
                                'dataset': _this.dataset
                            });
                        }
                    }
                    ,function (jqXHR, textStatus, errorThrown) {
                            if ( errorThrown ) {
                                _this.messaging.addErrors(errorThrown);
                            }

                            if ( jqXHR.responseText ) {
                                var messages = $.parseJSON(jqXHR.responseText);
                                _this.messaging.addErrors(messages);
                            }
                            _this.messaging.displayMessages();
                    });

                } else {
                    GD.DatafileFactory.getStructure(result.id, function( data ){
                        if ( typeof data['messages'] != 'undefined' && data['messages'].status == 'validation' && data['messages']['errors'].length != 0 ) {
                            _this.messaging.addErrors(data['messages']['errors']);
                            _this.messaging.displayMessages();
                        } else {
                            if ( typeof data['messages'] != 'undefined' && data['messages']['warnings'].length != 0 ) {
                                _this.messaging.addWarnings(data['messages']['warnings']);
                                _this.messaging.displayMessages();
                            }

                            _this.dataset = new GD.Dataset(data['structure']);
                            _this.dataset.datafile = result.id;

                            $.event.trigger({
                                'type': 'loadedDataset',
                                'dataset': _this.dataset,
                                'response': data,
                                'reloadView': true
                            });

                            $.event.trigger({
                                'type': 'loadedPreview',
                                'previewData': data['preview']
                            });

                            $.event.trigger({
                                'type': 'loadComplete'
                            });
                        }
                    }, function (jqXHR, textStatus, errorThrown) {
                        if ( errorThrown ) {
                            _this.messaging.addErrors(errorThrown);
                        }

                        if ( jqXHR.responseText ) {
                            var messages = $.parseJSON(jqXHR.responseText);
                            _this.messaging.addErrors(messages);
                        }
                        _this.messaging.displayMessages();
                    });
                }
            }
        }
    };

    global.GD.DatasetWidgetFile = DatasetWidgetFile;

})(typeof window === 'undefined' ? this : window, jQuery);