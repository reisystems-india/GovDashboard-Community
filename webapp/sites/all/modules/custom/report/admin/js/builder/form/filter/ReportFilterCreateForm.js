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
        throw new Error('ReportFilterCreateForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportFilterCreateForm requires GD');
    }

    var GD = global.GD;

    global.GD.ReportFilterCreateForm = GD.View.extend({
        formContainer: null,
        datasource: null,
        actionContainer: null,
        nextButton: null,
        cancelButton: null,
        modal: null,
        cancelCallback: null,

        init: function(object, container, options) {
            this._super(object, container, options);

            this.formContainer = null;
            this.actionContainer = null;
            this.nextButton = null;
            this.cancelButton = null;
            this.modal = null;
            this.cancelCallback = null;
            if (options) {
                this.datasource = options['datasource'];
            }
        },

        getController: function () {
            return this.options.builder;
        },
        
        getFilterTree: function () {
            if ( !this.filterTree ) {
                this.filterTree = new GD.ReportFilterColumnTree(
                    {
                        columns: [],
                        dataset: this.object.getDatasetName()
                    },
                    this.getFormContainer(),
                    {
                        controller: this.getController()
                    }
                );
            }
            return this.filterTree;
        },

        getNextButton: function() {
            if (!this.nextButton) {
                this.nextButton = $('<button type="button" class="rpt-flt-act-btn btn btn-primary btn-sm">Next</button>');

                if (!this.reportListLookup) {
                    //this.nextButton.prop('disabled', true);
                }
            }

            return this.nextButton;
        },

        getCancelButton: function() {
            if (!this.cancelButton) {
                this.cancelButton = $('<button type="button" class="rpt-flt-act-btn btn btn-default btn-sm">Cancel</button>');
            }

            return this.cancelButton;
        },

        getActionButtons: function() {
            if (!this.actionContainer) {
                this.actionContainer = $('<div class="rpt-flt-act-cntr pull-right"></div>');
                this.actionContainer.append(this.getNextButton(),' ',this.getCancelButton());
            }

            return this.actionContainer;
        },
        
        getFormContainer: function(isReset) {
            if (!this.formContainer || isReset) {
                this.formContainer = $('<div><div id="validationMessage"></div></div>');
                this.formContainer.append('Choose a column to filter:', this.getFilterTree().render(), this.getActionButtons());
                this.validationMessagingView = new GD.MessagingView(this.formContainer.find('#validationMessage'));
            }

            return this.formContainer;
        },

        render: function(isReset) {
            if (this.container) {
                if(this.container.data("uiResizable")){
                    this.container.resizable("destroy");
                }
                this.container.append(this.getFormContainer(isReset));
            }
    
            return this.getFormContainer();
        },

        attachEventHandlers: function(cancel, next) {
            var _this = this;
            _this.validationMessagingView.clean();
            this.cancelCallback = cancel;
            this.getCancelButton().click(function() {
                if (cancel) {
                    cancel();
                }
            });

            this.nextCallback = next;
            this.getNextButton().click(function() {
                if(_this.container.data("uiResizable")){
                    _this.container.resizable("destroy");
                }
                var selected = _this.getFilterTree().getSelected();
                if(selected.length > 0){
                    var column = _this.getFilterTree().findById(selected[0]);
                    if(column.hasOwnProperty("parents") && column.parents instanceof Array){
                        column.name =  _this.getFilterTree().findById(column.parentId).name +"/"+ column.text ;
                    }
                    // need to figure out what to pass next.
                    var filter = {
                        column: column,
                        type: column.type,
                        name: column.name,
                        exposed: column.exposed,
                        exposedType: column.exposedType,
                        view: column.view,
                        value: column.value
                    };

                    if ( next ) {
                        next(filter);
                    }
                }else{
                    _this.validationMessagingView.addErrors('Select a filter');
                    _this.validationMessagingView.displayMessages()
                }

            });
        },

        redirectBackList: function() {
            if (this.cancelCallback) {
                this.cancelCallback();
            }
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);