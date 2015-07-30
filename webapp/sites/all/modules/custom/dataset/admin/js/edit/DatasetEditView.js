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
        throw new Error('DatasetEditView requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DatasetEditView requires GD');
    }

    var GD = global.GD;

    var DatasetEditView = GD.View.extend({
        dataset: null,

        topActionView: null,
        infoView: null,
        statsView: null,
        bottomActionView: null,
        modal: null,

        init: function ( object, container, options ) {
            this._super(object, container, options);

            if ( typeof options != 'undefined') {
                if ( typeof options['widget'] != 'undefined' ) {
                    this.widget = options['widget'];
                }
            }

            this.initLayout();
        },

        initLayout: function () {
            if ( this.layout == null ) {
                this.layoutHeader = $('<div class="col-md-12" id="gd-view-dataset-edit-header">');
                var header_wrap = $('<div class="row">').append(this.layoutHeader);

                this.layoutBody = $('<div class="col-md-12" id="gd-view-dataset-edit-body">');
                var body_wrap = $('<div class="row">').append(this.layoutBody);

                this.layoutFooter = $('<div class="col-md-12" id="gd-view-dataset-edit-footer">');
                var footer_wrap = $('<div class="row">').append(this.layoutFooter);

                this.layout = $('<div id="gd-view-dataset-edit">').append(header_wrap,body_wrap,footer_wrap, this.getModalContainer());

                this.container.append(this.layout);
            }
        },

        validate: function () {
            var messages = this.getInfoView().validate();
            return messages;
        },

        getDataset: function () {
            var dataset = this.getInfoView().getDataset();
            return dataset;
        },

        getTopActionView: function () {
            if ( this.topActionView == null ) {
                this.topActionView = new GD.DatasetActionView(this.dataset, this.layoutHeader, this.options);
            }
            return this.topActionView;
        },

        getInfoView: function () {
            if ( this.infoView == null ) {
                this.infoView = new GD.DatasetInfoView(this.dataset, this.layoutBody, this.options);
            }
            return this.infoView;
        },

        getBottomActionView: function () {
            if ( this.bottomActionView == null ) {
                this.bottomActionView = new GD.DatasetActionView(this.dataset, this.layoutFooter, this.options);
            }

            return this.bottomActionView;
        },

        getModalContainer: function () {
            if ( this.modal == null ) {

                var m = [
                    '<div class="modal fade" tabindex="-1">',
                        '<div class="modal-dialog">',
                            '<div class="modal-content">',
                            '</div>',
                        '</div>',
                    '</div>'
                ];
                this.modal = $(m.join("\n"));

                this.modal.modal({
                    'backdrop': 'static',
                    'keyboard': true,
                    'show': false
                });
            }

            return this.modal;
        },

        render: function () {
            this.getTopActionView().render();
            this.getInfoView().render();
            this.getBottomActionView().render();
        }
    });

    // add to global space
    global.GD.DatasetEditView = DatasetEditView;

})(typeof window === 'undefined' ? this : window, jQuery);