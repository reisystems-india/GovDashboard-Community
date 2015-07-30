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
        throw new Error('ReportXaxisDisplayForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportXaxisDisplayForm requires GD');
    }

    var GD = global.GD;

    global.GD.ReportXaxisDisplayForm = GD.View.extend({

        init: function(object, container, options) {
            this._super(object, container, options);


            var _this = this;
            $(document).on('save.report.visualize',function(){
                _this.saveOptions();
            });
        },

        getController: function () {
            return this.options.builder;
        },            
               
        getFormContainer: function() {
            if ( !this.formContainer ) {
                this.formContainer = $([
                    '<div class="panel panel-default">',
                        '<div class="panel-heading">',
                            '<div class="panel-title">',
                                '<div class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#visualizePanelGroup" href="#reportXAxisDisplayOptionsPanel">',
								'X-Axis Display Options</div>',
                            '</div>',
                        '</div>',

                        '<div id="reportXAxisDisplayOptionsPanel" class="panel-collapse collapse">',
                            '<div class="panel-body">',
                                '<form class="form-horizontal">',
                                    '<div class="form-group">', 
                                        '<label class="col-sm-12">Show Title:</label>',                                 
                                        '<div class="col-sm-4">',
                                            '<label class="radio-inline">',
                                                '<input type="radio" name="x-axisShowTitleOption" value="yes" checked> Yes',
                                            '</label>',
                                        '</div>', 
                                        '<div class="col-sm-4">', 
                                            '<label class="radio-inline">',
                                                '<input type="radio" name="x-axisShowTitleOption" value="no"> No',
                                            '</label>', 
                                        '</div>',  
                                        '<div class="col-sm-4">', 
                                            '<label class="radio-inline">',
                                                '<input type="radio" name="x-axisShowTitleOption" value="other"> Other',
                                            '</label>',                                         
                                        '</div>',                                       
                                    '</div>',

                                     '<div class="form-group titleTextXAxis">',                                        
                                        '<label class="col-sm-2 control-label ">Title:</label>',
                                        '<div class="col-sm-10">',
                                           '<input type="text" class="form-control reportDisplayXAxisTitleValue" >',
                                       '</div>',
                                     '</div>',

                                     '<div class="form-group">',
                                       '<div class="checkbox" style="padding-left: 10px;">',
                                            '<label>',
                                                '<input type="checkbox" value="1" name="displayXAxisLabel" class="form-control input-sm"> Show Labels:',
                                            '</label>',
                                        '</div>',                          
                                     '</div>',

                                    '<div class="form-group">', 
                                        '<label class="col-sm-12">Rotate Labels:</label>', 
                                            '<div class="col-sm-12">',
                                                '<label class="radio-inline">',
                                                    '<input type="radio" name="rotateLabelsOption" value="-90"> -90',
                                                '</label>',
                                                '<label class="radio-inline" style="padding-left: 15px;">',
                                                    '<input type="radio" name="rotateLabelsOption" value="-45" checked> -45',
                                                '</label>',  
                                                '<label class="radio-inline" style="padding-left: 15px;">',
                                                    '<input type="radio" name="rotateLabelsOption" value="0"> 0',
                                                '</label>',  
                                                '<label class="radio-inline" style="padding-left: 15px;">',
                                                    '<input type="radio" name="rotateLabelsOption" value="45"> 45',
                                                '</label>',  
                                                '<label class="radio-inline" style="padding-left: 15px;">',
                                                    '<input type="radio" name="rotateLabelsOption" value="90"> 90',
                                                '</label>',    
                                            '</div>',                 
                                    '</div>',

                                '</form>',
                            '</div>',
                        '</div>',

                    '</div>'
                ].join("\n"));
            }
            return this.formContainer;
        },

        getXAxisShowTitleOption: function () {
            return this.formContainer.find('input[name="x-axisShowTitleOption"]:checked').val();
        },

        setXAxisShowTitleOption: function ( value ) {
            this.formContainer.find('input[name="x-axisShowTitleOption"][value="'+value+'"]').prop('checked',true);
        },
        
        getLabelRotation: function () {
            return this.formContainer.find('input[name="rotateLabelsOption"]:checked').val();
        },

        setLabelRotation: function ( value ) {
            this.formContainer.find('input[name="rotateLabelsOption"][value="'+value+'"]').prop('checked',true);
        },

        getDisplayXAxisLabel: function () {
            return this.formContainer.find('input[name="displayXAxisLabel"]:checked').val();
        },

        setDisplayXAxisLabel: function ( value ) {
            this.formContainer.find('input[name="displayXAxisLabel"][value="'+value+'"]').prop('checked',true);
        },

        getDisplayXAxisTitleValue: function () {
            return this.formContainer.find(".reportDisplayXAxisTitleValue").val();
        },

        setDisplayXAxisTitleValue: function ( value ) {
            this.formContainer.find(".reportDisplayXAxisTitleValue").val( value );
        },
        
        saveOptions: function () {  
             var _this = this,
                reportObj = _this.getController().getReport();
            reportObj.setVisualizationOption('displayXAxisTitle', _this.getXAxisShowTitleOption());
            reportObj.setVisualizationOption('labelRotation', _this.getLabelRotation());
            reportObj.setVisualizationOption('displayXAxisTitleValue', _this.getDisplayXAxisTitleValue());
            reportObj.setVisualizationOption('displayXAxisLabel', _this.getDisplayXAxisLabel());
        },


        showXAxisInput: function() {
            var _this = this;

            if(this.getController().getReport().getVisual().displayXAxisTitle == "other") {
                    _this.formContainer.find(".titleTextXAxis").show();
            } else {
                    _this.formContainer.find(".titleTextXAxis").hide();
            }

            _this.formContainer.find('input[name="x-axisShowTitleOption"]').on('click',function(){
                if(_this.formContainer.find('input[name="x-axisShowTitleOption"]:checked').val() == "other") {
                        _this.formContainer.find(".titleTextXAxis").show();
                } else {
                        _this.formContainer.find(".titleTextXAxis").hide();
                    }
            });

        },

        render: function() {
            if ( this.container ) {

                this.container.append(this.getFormContainer());
                var reportObj = this.getController().getReport();
                this.showXAxisInput();
                if(reportObj.getVisual().displayXAxisTitle) {
                    this.setXAxisShowTitleOption(reportObj.getVisual().displayXAxisTitle);
                }
                if(reportObj.getVisual().labelRotation) {
                    this.setLabelRotation(reportObj.getVisual().labelRotation);
                }
                if(reportObj.getVisual().displayXAxisTitleValue) {
                    this.setDisplayXAxisTitleValue(reportObj.getVisual().displayXAxisTitleValue);
                }
                if(reportObj.getVisual().displayXAxisLabel) {
                    this.setDisplayXAxisLabel(reportObj.getVisual().displayXAxisLabel);
                }  
            }

            return this.getFormContainer();
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);