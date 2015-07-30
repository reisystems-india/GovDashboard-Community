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
        throw new Error('ReportColumnDisplayDynamicNumericForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportColumnDisplayDynamicNumericForm requires GD');
    }

    var GD = global.GD;

    var ReportColumnDisplayDynamicNumericForm = GD.View.extend({

        formContainer: null,
        form: null,
     
        init: function(object, container, options) {
            this._super(object, container, options);
            var _this = this;
            $(document).off('save.report.visualize').on('save.report.visualize',function(){
                _this.saveOptions();
            });
        },
        
        getController: function () {
            return this.options.builder;
        },

        /*isNew: function() {
            return ( this.object.new );
        },*/

        getFormContainer: function() {
            if ( !this.formContainer ) {
                this.formContainer = $([  
    '<form role="form" class="form-horizontal">', 
        '<div class="row">',
             '<label class="col-sm-12">Column Display for Column "'+this.object.displayName+'"</label>',  
        '</div>',
        '<div class="form-group reportDynamicShowTitleOption">', 
            '<label class="col-sm-4 control-label" for="formGroupInputLarge"> Show Title:</label>', 
             '<div class="col-sm-8">', 
                  '<label class="radio-inline">', 
                   '<input type="radio" name="NumericOption"  value="yes" checked>  Yes ',
                  '</label>', 
                  '<label class="radio-inline">', 
                   '<input type="radio" name="NumericOption"  value="no">  No',
                  '</label>', 
                  '<label class="radio-inline">', 
                   '<input type="radio" name="NumericOption"  value="other"> Other ', 
                  '</label>', 
             '</div>', 
        '</div>', 

        '<div class="row" id="Dynamicothertitle">',
            '<label class="col-sm-3">Title :</label>',
            '<div class="col-sm-9">',
              '<input type="text" class="form-control input-sm" id="reportDynamicDynamicothertitle">',
            '</div>',
        '</div>',

        '<div class="checkbox reportDynamicCheck">',          
            '<label>', 
                '<input type="checkbox" class="form-control input-sm" id="reportDynamicShowTitleLine"> Show Title Inline ',
            '</label>',                   
        '</div>',
        
      
      

        // Start By Colorall
    '<div id="byColorall">',
        '<div class="form-group">', 
            '<label class="col-sm-5 control-label"> Text Size : </label>', 
            '<div class="col-sm-7">', 
                  '<input type="text" class="form-control input-sm" style="width:100%" id="reportDynamicTextSize">', 
            '</div>', 
        '</div>', 

         '<div class="form-group">', 
            '<label class="col-sm-5 control-label"> Font : </label>', 
            '<div class="col-sm-7">', 
                '<select class="form-control input-sm" id="reportDynamicFontType">', 
                     '<option value="arial,helvetica,sans-serif">Arial</option>',
                      '<option value="courier new,courier,monospace">Courier New </option>',
                      '<option value="georgia,times new roman,times,serif">Georgia </option>',
                      '<option value="tahoma,arial,helvetica,sans-serif">Tahoma</option>',
                      '<option value="times new roman,times,serif">Times New Roman</option>',
                      '<option value="verdana,arial,helvetica">Verdana</option>',
                      '<option value="impact">Impact</option>',
                '</select>', 
            '</div>', 
        '</div>',

         '<div class="form-group">', 
            '<label class="col-sm-5 control-label"> Color : </label>', 
            '<div class="col-sm-7">', 
                  '<input  type="text" class="form-control input-sm" style="width:100%" id="reportDynamicColor">', 
            '</div>', 
        '</div>', 

    '</div>',   

    '</form>'].join("\n"));
            }
            this.formContainer.find($("#reportDynamicColor")).spectrum({
                chooseText: "Apply",
                cancelText: "Cancel",
                preferredFormat: "hex",
                showInput: true,
                allowEmpty: true
            });
            return this.formContainer;
        },

     
        getOptionTitleSetting: function () {
            return $('input[name="NumericOption"]:checked', this.getFormContainer()).val();
        },

        setOptionTitleSetting: function ( value ) {
            $('input[name="NumericOption"][value="'+value+'"]', this.getFormContainer()).prop('checked',true);
        },
        
        getColumnDisplayShowTitleLineSetting: function () {
            if($('#reportDynamicShowTitleLine', this.getFormContainer()).is(':checked') == true) {
                return this.TargetLineSetting = 1;
            } else {
                return this.TargetLineSetting = 0;
            }
        },

        setColumnDisplayShowTitleLineSetting: function ( value ) {
            if(value === 1 || value === "1") {
                $("#reportDynamicShowTitleLine", this.getFormContainer()).prop('checked',true);
            } else {
                $("#reportDynamicShowTitleLine", this.getFormContainer()).prop('checked',false);
            }
        },
        
        setColumnDisplayDynamicDynamicothertitle: function ( value ) {
            $("#reportDynamicDynamicothertitle", this.getFormContainer()).val(value);
        },
        
        getColumnDisplayDynamicDynamicothertitle: function () {
            return $("#reportDynamicDynamicothertitle", this.getFormContainer()).val();
        },
        

        setColumnDisplayTextSize: function ( value ) {
            $("#reportDynamicTextSize", this.getFormContainer()).val( value );
        },
        
        getColumnDisplayTextSize: function () {
            return $("#reportDynamicTextSize", this.getFormContainer()).val();
        },

        setColumnDisplayColor: function ( value ) {
            $("#reportDynamicColor", this.getFormContainer()).spectrum('set', value );
        },
        
        getColumnDisplayColor: function () {
            return $("#reportDynamicColor", this.getFormContainer()).val();
        },
        
        getReportColumnDisplayFontType: function () {
            return $('#reportDynamicFontType option:selected', this.getFormContainer()).val();
        },
        
        setReportColumnDisplayFontType: function (selectedValue) {
            $('#reportDynamicFontType option[value="' + selectedValue + '"]').prop('selected', true);
        },

        showHideOptionTitleValueinput: function() {
            var _this = this;     
            
            if(this.object.columnType == "string") {
                //_this.getFormContainer().find('.reportDynamicShowTitleOption').hide();
                $('#reportDynamicShowTitleLine', _this.getFormContainer()).prop('checked', false);
            } else {
                $('.reportDynamicShowTitleOption', _this.getFormContainer()).show();
            }
            
            $("#Dynamicothertitle").hide();
            
            this.getFormContainer().find('input[name="NumericOption"]').on('click',function(){
                if($('input[name="NumericOption"]:checked', _this.getFormContainer()).val() == "other") {
                    $("#Dynamicothertitle").show();
                } else {
                    $("#Dynamicothertitle").hide();
                }
                
                if(_this.getFormContainer().find('input[name="NumericOption"]:checked').val() == "no") {
                    $(".reportDynamicCheck", _this.getFormContainer()).hide();
                } else {
                    $(".reportDynamicCheck", _this.getFormContainer()).show();
                }
            });
          
        },


        getForm: function () {
            if ( !this.form ) {
                this.form = $([
                    '<form role="form">',
                        '<h5>Formatting for Column "'+this.object.displayName+'"</h5>',
                        '<div class="form-group">',
                            '<label for="reportColumnFormatName">Display Name</label>',
                            '<input type="text" class="form-control input-sm" id="reportColumnDisplayStringName" value="'+this.object.displayName+'">',
                        '</div>',
                    '</form>'
                ].join("\n"));
            }

            return this.form;
        },
        
        showColumnDisplayDynamicEditView : function() {
            this.setColumnDisplayTextSize(this.object['dynamicTextSize']);
            this.setColumnDisplayColor(this.object['dynamicTextColor']);
            this.setReportColumnDisplayFontType(this.object['dynamicTextFont']);
            this.setOptionTitleSetting(this.object['dynamicTextShowTitle']);
            this.setColumnDisplayShowTitleLineSetting(this.object['dynamicTextShowTitleInline']);
            this.setColumnDisplayDynamicDynamicothertitle(this.object['dynamicTextTitle']);
            if(this.object['dynamicTextShowTitle'] == 'other') {
                $("#Dynamicothertitle").show();
            }
        },
        
        saveOptions: function() {
            this.object.options = {
                displayDataLabels: 1,
                displayTitle: 'yes'
            };
            this.object['dynamicTextFont'] = this.getReportColumnDisplayFontType();
            this.object['dynamicTextSize'] = this.getColumnDisplayTextSize();
            this.object['dynamicTextColor'] = this.getColumnDisplayColor();
            this.object['dynamicTextShowTitleInline'] = this.getColumnDisplayShowTitleLineSetting();
            this.object['dynamicTextTitle'] = this.getColumnDisplayDynamicDynamicothertitle();
            this.object['dynamicTextShowTitle'] = this.getOptionTitleSetting();
            
            this.getController().getReport().setColumnDisplayOption(this.object);
        },
        
        
        render: function() {
            if ( this.container ) {
                this.container.append(this.getFormContainer());
                this.showHideOptionTitleValueinput();
                if(this.object.columnId){
                    this.showColumnDisplayDynamicEditView();
                }
            }
            return this.getFormContainer();
        }

    });

    GD.ReportColumnDisplayDynamicNumericForm = ReportColumnDisplayDynamicNumericForm;

})(typeof window === 'undefined' ? this : window, jQuery);