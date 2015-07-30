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
        throw new Error('ReportColumnFormatForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportColumnFormatForm requires GD');
    }

    var GD = global.GD;

    var ReportColumnFormatForm = GD.View.extend({

        formContainer: null,
        form: null,
        modal: null,
        deleteButton: null,
        cancelButton: null,
        applyButton: null,

        init: function(object, container, options) {
            this._super(object, container, options);
        },
        
        getController: function () {
            return this.options.builder;
        },

        isNew: function() {
            return this.options["isNew"];
        },

        getFormContainer: function() {
            if ( !this.formContainer ) {
                this.formContainer = $('<div></div>').append(this.getForm(), this.getActionButtons());
            }
            return this.formContainer;
        },

        getForm: function () {
            if ( !this.form ) {
                this.form = $([
                    '<form role="form">',
                        '<h5>Formatting for Column "'+this.object.displayName+'"</h5>',
                        '<div class="form-group">',
                            '<label for="reportColumnFormatName">Display Name</label>',
                            '<input type="text" class="form-control input-sm" id="reportColumnFormatName" value="'+this.object.displayName+'">',
                        '</div>',
                    '</form>'
                ].join("\n"));
            }

            return this.form;
        },

        getDisplayName: function () {
            return this.getForm().find('#reportColumnFormatName').val();
        },

        setDisplayName: function ( name ) {
            this.getForm().find('#reportColumnFormatName').val(name);
        },

        getDeleteButton: function() {
            if ( !this.deleteButton ) {
                this.deleteButton = $('<button type="button" class="rpt-act-btn rpt-fmt-act-btn btn btn-default btn-sm">Delete</button>');
            }

            return this.deleteButton;
        },

        getCancelButton: function() {
            if ( !this.cancelButton ) {
                this.cancelButton = $('<button type="button" class="rpt-act-btn rpt-fmt-act-btn btn btn-default btn-smt">Cancel</button>');
            }

            return this.cancelButton;
        },

        getApplyButton: function() {
            if ( !this.applyButton ) {
                this.applyButton = $('<button type="button" class="rpt-act-btn rpt-fmt-act-btn btn btn-primary btn-sm">Apply</button>');
            }

            return this.applyButton;
        },

        getActionButtons: function() {
            if ( !this.actionButtons ) {

                this.actionButtons = $('<div class="rpt-act-btn-cntr rpt-fmt-act-cntr pull-right"></div>');

                this.actionButtons.append(this.getApplyButton());

                if ( !this.isNew() ) {
                    this.actionButtons.append(this.getDeleteButton());
                }

                this.actionButtons.append(this.getCancelButton());
            }

            return this.actionButtons;
        },

        getModal: function ( callback ) {
            if ( !this.modal ) {
                this.modal = $([
                    '<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">',
                        '<div class="modal-dialog">',
                            '<div class="modal-content">',
                                '<div class="modal-header">',
                                    '<h4>Delete Column Format</h4>',
                                '</div>',
                                '<div class="modal-body">',
                                    '<div class="row">',
                                        '<div class="col-sm-1"><span style="font-size:25px;" class="glyphicon glyphicon-info-sign"></span></div>',
                                        '<div style="height:25px; line-height: 25px;" class="col-sm-11">Are you sure you want to delete this column\'s formatting?</div>',
                                    '</div>',
                                '</div>',
                                '<div class="modal-footer">',
                                    '<button type="button" class="btn btn-danger" data-dismiss="modal">Delete</button>',
                                    '<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>',
                                '</div>',
                            '</div>',
                        '</div>',
                    '</div>'

                ].join("\n"));

                var _this = this;
                this.modal.find('.btn-danger').on('click',function() {
                    _this.deleteFormat(callback);
                });

                $('body').append(this.modal);
            }

            return this.modal;
        },

        deleteButtonClicked: function ( callback ) {
            this.getModal(callback).modal();
        },

        deleteFormat: function ( callback ) {
            var columnConfig = this.getController().getReport().getColumnConfigs(),
                length = columnConfig.length,
                i;
            for(i=length-1;i>=0;i--){
                if(columnConfig[i]['columnId'] === this.object.columnId){
                    columnConfig.splice(i,1)
                }
            }
            if ( callback ) {
                callback();
            }
            $(document).trigger({
                type: 'remove.column.format'
            });
        },

        validate: function() {
            var valid = true;

            // add validation

            return valid;
        },

        applyButtonClicked: function ( callback ) {
            if ( this.validate() ) {
                var controllerObj = this.getController();
                this.object.displayName = this.getDisplayName();
                controllerObj.getReport().setColumnConfig(this.object.columnId,this.object);
                controllerObj.getCanvas().loadPreview();
                callback();
            }
        },

        attachEventHandlers: function ( cancel, apply, del ) {
            var _this = this;

            if ( cancel && this.getCancelButton() ) {
                this.getCancelButton().click(function() {
                    cancel();
                });
            }

            if ( apply && this.getApplyButton() ) {
                this.getApplyButton().click(function() {
                    _this.applyButtonClicked(apply);
                    $(document).trigger({
                        type: 'changed.column.format'
                    });
                });
            }

            if ( !this.isNew() ) {
                if ( del && this.getDeleteButton() ) {
                    this.getDeleteButton().click(function() {
                        _this.deleteButtonClicked(del);
                    });
                }
            }
        },

        render: function() {
            if ( this.container ) {
                this.container.append(this.getFormContainer());
            }
            return this.getFormContainer();
        }

    });

    GD.ReportColumnFormatForm = ReportColumnFormatForm;

})(typeof window === 'undefined' ? this : window, jQuery);