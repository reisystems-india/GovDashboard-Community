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
        throw new Error('ReportColumnDisplayForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportColumnDisplayForm requires GD');
    }

    var GD = global.GD;

    var ReportColumnDisplayForm = GD.View.extend({

        init: function(object, container, options) {
           
            this._super(object, container, options);
            this.initForm();

        },

       
        getFormContainer: function() {
            if ( !this.formContainer ) {
                this.formContainer = $([                   
                     '<div class="panel panel-default">',
                        '<div class="panel-heading">',
                            '<div class="panel-title">',
                                '<div class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#visualizePanelGroup" href="#reportColumnDisplayPanel">',
								'Column Display Options',
								'</div>',
                            '</div>',
                        '</div>',
                        '<div id="reportColumnDisplayPanel" class="panel-collapse collapse">',
                            '<div class="panel-body">',
                               
                            '</div>',
                        '</div>',
                    '</div>'
                ].join("\n"));
            }
            return this.formContainer;
        },


        initForm: function() {

            //console.log(this.getFormContainer().find('#reportColumnDisplayPanel .panel-body'));
           this.listForm = new GD.ReportColumnDisplayListForm(this.getController().getReport(), $('#reportColumnDisplayPanel .panel-body', this.getFormContainer()), this.options);
            this.editForm = null;
            this.createForm = null;
            this.unsupportedColumnTypeArr = ["URI", "date", "date2","date:month", "date_month", "date:quarter", "date_quarter", "date:year", "date2:year","date_year","datetime"];
        },

        showListView: function() {
            this.formContainer.find('#reportColumnDisplayPanel .panel-body').empty();
            this.listForm.enableDisableAddbutton(this.unsupportedColumnTypeArr);
            this.listForm.render(true);
            var _this = this;
            this.listForm.attachEventHandlers(
                // add handler
                function() {
                    _this.showCreateView();
                },
                // click handler
                function(f) {
                    _this.showEditView(f, false);
                }
            );
        },

        showCreateView: function () {
            this.formContainer.find('#reportColumnDisplayPanel .panel-body').empty();
            this.createForm = new GD.ReportColumnDisplayCreateForm(this.unsupportedColumnTypeArr ,this.getFormContainer().find('#reportColumnDisplayPanel .panel-body'),this.options);
            this.createForm.render();

            var _this = this;
            this.createForm.attachEventHandlers(
                // cancel handler
                function() {
                    _this.showListView();
                },
                // next handler
                function(f) {
                    _this.showEditView(f, true);
                }
            );
        },

        showEditView: function ( format, newVal) {
            var container = this.formContainer.find('#reportColumnDisplayPanel .panel-body'),
                _this = this;
            container.empty();
            this.editForm = GD.ReportColumnDisplayFormFactory.getForm(format,container,this.options);
            this.editForm.render(newVal);
            this.getCancelButton().off("click").on("click", function(){
                _this.showListView();
            })
            this.getAddButton().off("click").on("click", function(){
                _this.editForm.saveOptions();
                _this.showListView();
            })
            container.append(this.getActionButtons());

            /*this.editForm.attachEventHandlers(
                // cancel handler
                function() {
                    _this.showListView();
                },
                // apply handler
                function(f) {
                    _this.showListView();
                },
                // delete handler
                function(f) {
                    _this.showListView();
                }
            );*/

        },

        getController: function () {
            return this.options.builder;
        },

        render: function () {
            if ( this.container ) {
                this.container.append(this.getFormContainer());
            }

            this.showListView();

            return this.getFormContainer();
        },
        getAddButton: function(){
            if (!this.addButton) {
                this.addButton = $('<button type="button" class="rpt-flt-act-btn btn btn-primary btn-sm">Add</button>');
            }

            return this.addButton;
        },
        getCancelButton: function(){
            if (!this.cancelButton) {
                this.cancelButton = $('<button type="button" class="rpt-flt-act-btn btn btn-default btn-sm">Cancel</button>');
            }

            return this.cancelButton;
        },

        getActionButtons: function() {
            if (!this.actionContainer) {
                this.actionContainer = $('<div class="rpt-flt-act-cntr columnDisplayActionButtons"></div>');
                this.actionContainer.append(this.getAddButton(),' ',this.getCancelButton());
            }

            return this.actionContainer;
        }

    });

    GD.ReportColumnDisplayForm = ReportColumnDisplayForm;

})(typeof window === 'undefined' ? this : window, jQuery);