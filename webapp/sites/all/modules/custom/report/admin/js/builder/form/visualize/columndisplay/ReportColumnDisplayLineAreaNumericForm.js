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
        throw new Error('ReportColumnDisplayLineAreaNumericForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportColumnDisplayLineAreaNumericForm requires GD');
    }

    var GD = global.GD;

    var ReportColumnDisplayLineAreaNumericForm = GD.View.extend({

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

/*        isNew: function() {
            return ( this.object.new );
        },*/

        getFormContainer: function() {
            if ( !this.formContainer ) {
                this.formContainer = $([
                    '<form role="form" class="form-horizontal">',
                    '<div class="form-group">',
                    '<label class="col-sm-4 control-label" for="formGroupInputLarge"> Show Title:</label>',
                    '<div class="col-sm-8 radiogrp">',
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

                    '<div class="form-group" id="othertitle">',
                    '<label class="col-sm-4 control-label" >Other Title :</label>',
                    '<div class="col-sm-7">',
                    '<input type="text" id="" name="textOtherTitle" class="form-control  textOtherTitle">',
                    '</div>',
                    '</div>',

                    '<div class="form-group">',
                    '<div class="col-sm-12">',
                    '<label>',
                    '<input type="checkbox" name="checkDataLabels" value="1" checked> Show Data Labels ',
                    '</label>',
                    '</div>',
                    '<div class="col-sm-12">',
                    '<label>',
                    '<input type="checkbox"  name="checkMarker" value="1" checked> Show Markers ',
                    '</label>',
                    '</div>',
                    '<div class="col-sm-12">',
                    '<label>',
                    '<input type="checkbox" name="checkLine" value="1"> Step Line ',
                    '</label>',
                    '</div>',
                    '<div class="col-sm-12">',
                    '<label class="radio-inline">',
                    '<input type="radio" name="colorAllNumericOption" value="standard" checked> Color All ',
                    '</label>',
                    '</div>',
                    '</div>',

                    // Start By Colorall
                    '<div class="form-group" id="byColorall">',
                    '<label class="col-sm-4 control-label " > Color : </label>',
                    '<div class="col-sm-8">',
                    '<input  type="text" style="width:100%" name="textColorCode" class="columnDisplayColor textColorCode form-control">',
                    '</div>',
                    '</div>',

// end By Colorall

                    '</form>'
                ].join("\n"));
            }
            this.formContainer.find(".columnDisplayColor").spectrum({
                chooseText: "Apply",
                cancelText: "Cancel",
                preferredFormat: "hex",
                showInput: true,
                allowEmpty: true
            });
            return this.formContainer;
        },


        getOptionTitleSetting: function () {
            return this.formContainer.find('input[name="NumericOption"]:checked').val();
        },

        setOptionNumericSetting: function ( value ) {
            this.formContainer.find('input[name="NumericOption"][value="'+value+'"]').prop('checked',true);
        },

        setOptionColorCodeSetting: function ( value ) {
            this.formContainer.find('.textColorCode').spectrum('set', value );
        },

        setOptionTitleSetting: function ( value ) {
            this.formContainer.find('.textOtherTitle').val( value );
        },

        setCheckDataLabelsSetting: function ( value ) {
            this.formContainer.find('input[name="checkDataLabels"]').prop('checked',value);
        },
        setCheckMarkerSetting: function ( value ) {
            this.formContainer.find('input[name="checkMarker"]').prop('checked',value);
        },
        setCheckLineSetting: function ( value ) {
            this.formContainer.find('input[name="checkLine"]').prop('checked',value);
        },

        setEditOptions: function(){
            this.setOptionNumericSetting(this.object.options.displayTitle);
            this.setOptionColorCodeSetting(this.object.color);
            this.setOptionTitleSetting(this.object.options.title);
            this.setCheckDataLabelsSetting(this.object.options.displayDataLabels?true:false);
            this.setCheckMarkerSetting(this.object.options.displayMarkers?true:false);
            this.setCheckLineSetting(this.object.options.step?true:false);

            if(this.object.options.displayTitle == 'other')
            {
                $("#othertitle").show();
            }
        },

        showHideOptionTitleValueinput: function() {
            var _this = this;
            var container = _this.formContainer;
            /*    if(this.getController().getReport().getVisual().tickInterval == "other") {
             _this.formContainer.find(".tickIntervalCustomValue").show();
             } else {
             _this.formContainer.find(".tickIntervalCustomValue").hide();
             }  */

            $("#othertitle").hide();

            container.find('input[name="NumericOption"]').on('click',function(){
                if(container.find('input[name="NumericOption"]:checked').val() == "other") {
                    $("#othertitle").show();
                } else {
                    $("#othertitle").hide();
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

        saveOptions: function() {
            var color = this.formContainer.find('input[name="textColorCode"]').val();
            this.object.options = {
                displayDataLabels: this.formContainer.find('input[name="checkDataLabels"]:checked').length ? 1 : 0,
                displayTitle: this.getOptionTitleSetting(),
                displayMarkers: this.formContainer.find('input[name="checkMarker"]:checked').length ? 1 : 0,
                step: this.formContainer.find('input[name="checkLine"]:checked').length ? 1 : 0,
                title: this.formContainer.find('input[name="textOtherTitle"]').val()
            };
            this.object.colorScheme = 'standard';
            if(color){
                this.object.color = this.formContainer.find('input[name="textColorCode"]').val();
            }
            this.getController().getReport().setColumnDisplayOption(this.object);
        },


        render: function(isNew) {
            if ( this.container ) {
                this.container.append(this.getFormContainer(isNew));
                this.showHideOptionTitleValueinput();
                if(this.object.columnId){
                    this.setEditOptions();
                }
            }
            return this.formContainer;
        }

    });

    GD.ReportColumnDisplayLineAreaNumericForm = ReportColumnDisplayLineAreaNumericForm;

})(typeof window === 'undefined' ? this : window, jQuery);