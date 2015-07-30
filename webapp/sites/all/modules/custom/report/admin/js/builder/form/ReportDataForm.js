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
        throw new Error('ReportDataForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportDataForm requires GD');
    }

    var GD = global.GD;

    global.GD.ReportDataForm = GD.View.extend({

        init: function(object, container, options) {
            this._super(object, container, options);
        },

        getController: function () {
            return this.options.builder;
        },

        loadData: function () {
            var _this = this,
                controllerObj = this.getController(),
                reportObj = _this.getController().getReport();

            GD.DatasetFactory.getDatasetList(controllerObj.getDatasourceName(), function ( data ) {
                _this.getForm().empty();
                var options = [];
                options.push('<option value="">Select Dataset</option>');
                $.each(data,function(i,dataset){
                    options.push('<option value="'+dataset.name+'">'+dataset.publicName+'</option>');
                });
                _this.getForm().append(options.join("\n"));
                if (typeof(reportObj.datasets) != "undefined" && reportObj.getDatasets().length == 1) {
                    _this.getForm().find('option[value="'+reportObj.getDatasetName()+'"]').attr("selected",true);
                }

                //  IE9 Hack: Force redraw to fix first character issue
                _this.getForm().width(_this.getForm().width());

                _this.getForm().on("change",function(){
                    _this.validationMessagingView.clean();
                });
            }, function(jqXHR, textStatus, errorThrown) {
                _this.messaging.addErrors(jqXHR.responseText);
                _this.messaging.displayMessages();
            });
        },

        getFormContainer: function() {
            if ( !this.formContainer ) {
                this.formContainer = $('<div><div id="validationMessage"></div></div>');
                this.formContainer.append($('<div class="form-group"></div>').append(this.getLabel(),this.getForm()),this.getApplyButton());
                this.validationMessagingView = new GD.MessagingView(this.formContainer.find('#validationMessage'));
            }
            return this.formContainer;
        },

        getLabel: function () {
            if ( !this.label ) {
                this.label = $('<label class="form-label" for="datasetList">Dataset</label>');
            }
            return this.label;
        },

        getForm: function () {
		  
            if ( !this.form ) {
	            this.form = $('<select class="form-control input-sm" id="datasetList"></select>');
		    }
			return this.form;
        },

        getApplyButton: function () {
            if ( !this.applyButton ) {
                this.applyButton = $('<button data-loading-text="Loading..." class="btn btn-primary btn-sm pull-right" type="button">Apply</button>');
                var _this = this;
                this.applyButton.on('click',function(){
                    $(this).button('loading');

                    var datasetName = _this.getForm().val();
                    if ( !datasetName ) {
                        _this.validationMessagingView.addErrors('Select a dataset');
                        _this.validationMessagingView.displayMessages();
                        _this.getApplyButton().button('reset');
                        return false;
                    }

                    var originalDatasetName = _this.getController().getReport().getDataset().getName();

                    if ( originalDatasetName && originalDatasetName !== datasetName ) {

                        // reload the page with the new dataset as the param
                        var uri = new GD.Util.UriHandler();
                        uri.addParam('dataset',datasetName);

                        var reportName = _this.getController().getReport().getName();
                        if ( reportName ) {
                            uri.addParam('title', reportName);
                        }

                        uri.redirect();
                        return false;
                    }

                    if ( originalDatasetName !== datasetName ) {
                        GD.DatasetFactory.getDatasetUiMetadata(
                            datasetName,
                            function ( data ) {
                                _this.getController().getReport().setDataset(new GD.ReportDataset(data));
                                $(document).trigger({
                                    type: 'changed.report.dataset'
                                });
                            },
                            function(jqXHR, textStatus, errorThrown) {
                                global.GD.ReportBuilderMessagingView.addErrors(jqXHR.responseText);
                                global.GD.ReportBuilderMessagingView.displayMessages();
                            }, function(){
                                _this.getApplyButton().button('reset');
                            }
                        );
                    } else {
                        _this.getApplyButton().button('reset');
                    }
                });
            }

		    return this.applyButton;
        },

        render: function() {
            if ( this.container ) {
                this.container.append(this.getFormContainer());
            }
            return this.getFormContainer();
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);