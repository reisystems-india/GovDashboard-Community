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
        throw new Error('DatasetActionView requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DatasetActionView requires GD');
    }

    var GD = global.GD;

    var DatasetActionView = GD.View.extend({
        saveButton: null,
        cancelButton: null,
        deleteButton: null,
        buttonContainer: null,

        init: function (object, container, options) {
            this._super(object, container, options);
        },

        loadComplete: function () {
            this.getDeleteButton().removeClass('disabled');
            this.getSaveButton().removeClass('disabled');
        },

        toggleCreateView: function () {
            this.getDeleteButton().hide();
        },

        toggleReadOnlyView: function () {
            this.getDeleteButton().hide();
            this.getSaveButton().addClass('disabled');
        },

        getButtonContainer: function () {
            if ( this.buttonContainer == null ) {
                this.buttonContainer = $('<div class="col-md-12 text-right dataset-actions"></div>');

                this.buttonContainer.append(this.getSaveButton(),this.getCancelButton(),this.getDeleteButton());
            }

            return this.buttonContainer;
        },

        getSaveButton: function () {
            if ( this.saveButton == null ) {
                this.saveButton = $('<button class="btn btn-success disabled">Save</button>');

                var _this = this;
                this.saveButton.on('click', function () {
                    _this.clickedSave();
                });
            }

            return this.saveButton;
        },

        clickedSave: function () {
            if ( this.getSaveButton().hasClass('disabled') ) {
                return;
            }

            $.event.trigger({
                'type': 'saveDataset'
            });
        },

        getCancelButton: function () {
            if ( this.cancelButton == null ) {
                this.cancelButton = $('<button type="button" class="btn btn-default">Cancel</button>');

                var _this = this;
                this.cancelButton.on('click', function () {
                    _this.clickedCancel();
                });
            }

            return this.cancelButton;
        },

        clickedCancel: function () {
            if ( this.getCancelButton().hasClass('disabled') ) {
                return;
            }

            $.event.trigger({
                'type': 'cancelEdit'
            });
        },

        getDeleteButton: function () {
            if ( this.deleteButton == null ) {
                this.deleteButton = $('<button type="button" class="btn btn-default disabled" data-toggle="tooltip" title="Delete Dataset"><span class="glyphicon glyphicon-trash"></span></button>');

                var _this = this;
                this.deleteButton.on('click', function () {
                    _this.clickedDelete();
                });
            }

            return this.deleteButton;
        },

        clickedDelete: function () {
            if ( this.getDeleteButton().hasClass('disabled') ) {
                return;
            }

            $.event.trigger({
                'type': 'deleteDataset'
            })
        },

        render: function () {
            var container = $('<div class="row gd-section-dataset-action-view"></div>');
            container.append(this.getButtonContainer());

            //  TODO Move to init
            var _this = this;
            $(document).on('loadComplete', function () {
                _this.loadComplete();
            });

            $(document).on('createView', function () {
                _this.toggleCreateView();
            });

            $(document).on('readOnlyView', function () {
                _this.toggleReadOnlyView();
                container.unbind('loadComplete');
            });

            if ( this.container != null ) {
                $(this.container).append(container);
            } else {
                return container;
            }
        }
    });

    // add to global space
    global.GD.DatasetActionView = DatasetActionView;

})(typeof window === 'undefined' ? this : window, jQuery);