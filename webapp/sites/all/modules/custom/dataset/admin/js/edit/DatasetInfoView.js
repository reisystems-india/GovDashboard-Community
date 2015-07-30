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
        throw new Error('DatasetInfoView requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DatasetInfoView requires GD');
    }

    var GD = global.GD;

    var DatasetInfoView = GD.View.extend({
        inputContainer: null,
        nameInput: null,
        descriptionInput: null,
        infoContainer: null,
        statsView: null,

        validate: function () {
            var messages = [];
            if ( this.getNameInput().attr('dataset-name') == '' ) {
                messages.push('Dataset must have a name.');
            }

            return messages;
        },

        toggleReadOnlyView: function () {
            this.getNameInput().attr('disabled', 'disabled');
            this.getDescriptionInput().attr('disabled', 'disabled');
        },

        datasetChanged: function ( context, type ) {
            this.getNameInput().removeClass('changed-info changed-error');
            if ( context.isNameUpdated ) {
                this.getNameInput().addClass(type == 'error' ? 'changed-error' : 'changed-info');
            }

            this.getDescriptionInput().removeClass('changed-info changed-error');
            if ( context.isDescriptionUpdated ) {
                this.getDescriptionInput().addClass(type == 'error' ? 'changed-error' : 'changed-info');
            }
        },

        getDataset: function () {
            return {
                publicName: this.getNameInput().attr('dataset-name') != '' ? this.getNameInput().attr('dataset-name') : null,
                description: this.getDescriptionInput().val() != '' ? this.getDescriptionInput().val() : null
            };
        },

        loadedDataset: function ( dataset, reloadView ) {
            this.object = dataset;
            if ( reloadView ) {
                if ( dataset.getPublicName() != '' && dataset.getPublicName() != null ) {
                    this.getNameInput().val(dataset.getPublicName());
                    this.getNameInput().attr('dataset-name', dataset.getPublicName());
                }

                if ( dataset.getDescription() != '' && dataset.getDescription() != null ) {
                    this.getDescriptionInput().val(dataset.getDescription());
                }
            }
        },

        getInputContainer: function () {
            if ( this.inputContainer == null ) {
                this.inputContainer = $('<div class="col-md-5"></div>');
                var form = $('<form></form>');
                form.append(this.getNameInput(),this.getDescriptionInput());
                this.inputContainer.append(form);
            }

            return this.inputContainer;
        },

        getNameInput: function () {
            if ( this.nameInput == null ) {
                this.nameInput = $('<input type="text" dataset-name="" placeholder="Dataset Name" class="form-control gd-dataset-name-value" />');

                this.nameInput.on('keyup change', function () {
                    $(this).attr('dataset-name', $(this).val());
                    $.event.trigger({
                        'type':'datasetModificationDetected',
                        'element': this
                    });
                });
            }

            return this.nameInput;
        },

        getDescriptionInput: function () {
            if ( this.descriptionInput == null ) {
                this.descriptionInput = $('<textarea rows="5" class="form-control"></textarea>');

                this.descriptionInput.on('keyup change', function () {
                    $.event.trigger({
                        'type':'datasetModificationDetected',
                        'element': this
                    });
                });
            }

            return this.descriptionInput;
        },

        getStatsView: function () {
            if ( this.statsView == null ) {
                this.statsView = new GD.DatasetStatsView(this.object, this.getInfoContainer(), this.options);
            }

            return this.statsView;
        },

        getInfoContainer: function () {
            if ( this.infoContainer == null ) {
                this.infoContainer = $('<div class="row"></div>');
                this.infoContainer.append(this.getInputContainer());
                this.getStatsView().render();

                var _this = this;
                $(document).on('loadedDataset', function ( e ) {
                    _this.loadedDataset(e['dataset'], e['reloadView']);
                });

                $(document).on('changesMade', function ( e ) {
                    _this.datasetChanged(e['context'], e['changeType']);
                });

                $(document).on('readOnlyView', function () {
                    _this.toggleReadOnlyView();
                });
            }

            return this.infoContainer;
        },

        render: function () {
            if ( this.container != null ) {
                $(this.container).append(this.getInfoContainer());
            } else {
                return this.getInfoContainer();
            }
        }
    });

    // add to global space
    global.GD.DatasetInfoView = DatasetInfoView;

})(typeof window === 'undefined' ? this : window, jQuery);