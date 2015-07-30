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
        throw new Error('ReportColorSchemeForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportColorSchemeForm requires GD');
    }

    var GD = global.GD;

    global.GD.ReportColorSchemeForm = GD.View.extend({

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
                    '<div class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#visualizePanelGroup" href="#reportColorSchemePanel">',
                    'Categories and Color Scheme',
                    '</div>',
                    '</div>',
                    '</div>',

                    '<div id="reportColorSchemePanel" class="panel-collapse collapse">',
                    '<div class="panel-body">',
                    '<form class="form-horizontal">',
                    '<div id="byRangePercent">',
                    '<div class="form-group">',
                    '<label class="col-sm-4 control-label">Intervals</label>',
                    '<div class="col-sm-8">',
                    '<select id="intervalColorScheme" class="form-control input-sm">',
                    '<option value="0">0</option>',
                    '<option value="1">1</option>',
                    '<option value="2">2</option>',
                    '<option value="3">3</option>',
                    '<option value="4">4</option>',
                    '<option value="5">5</option>',
                    '</select>',
                    '</div>',
                    '</div>',

                    '<div class="groupColorScheme0">',
                    '<label class="col-sm-9" style="padding: 0 5px;"> Range </label>',
                    '<label class="col-sm-3">Pick Color</label>',
                    '</div>',

                    '<div class="groupColorScheme1">',
                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" style="width:100%"  name="rangeIntervalFromText1"   >',
                    '</div>',

                    '<div class="col-sm-2" style="padding: 2px 0;text-align: center;">',
                    '<label>% to</label>',
                    '</div>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" style="width:100%"  name="rangeIntervalToText1" >',
                    '</div>',

                    '<div class="col-sm-1" style="padding: 2px 0;text-align: center;">',
                    '<label>%</label style="padding: 0 5px;">',
                    '</div>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input  type="text" style="width:100%" class="rangeIntervalColorText" name="rangeIntervalColorText1">',
                    '</div>',
                    '</div>',


                    '<div class="groupColorScheme2">',
                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" style="width:100%"  name="rangeIntervalFromText2"   >',
                    '</div>',

                    '<div class="col-sm-2" style="padding: 2px 0;text-align: center;">',
                    '<label>% to</label>',
                    '</div>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" style="width:100%"  name="rangeIntervalToText2" >',
                    '</div>',

                    '<div class="col-sm-1" style="padding: 2px 0;text-align: center;">',
                    '<label>%</label style="padding: 0 5px;">',
                    '</div>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input  type="text" style="width:100%" class="rangeIntervalColorText" name="rangeIntervalColorText2">',
                    '</div>',
                    '</div>',

                    '<div class="groupColorScheme3">',
                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" style="width:100%"  name="rangeIntervalFromText3"   >',
                    '</div>',

                    '<div class="col-sm-2" style="padding: 2px 0;text-align: center;">',
                    '<label>% to</label>',
                    '</div>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" style="width:100%"  name="rangeIntervalToText3" >',
                    '</div>',

                    '<div class="col-sm-1" style="padding: 2px 0;text-align: center;">',
                    '<label>%</label style="padding: 0 5px;">',
                    '</div>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input  type="text" style="width:100%" class="rangeIntervalColorText" name="rangeIntervalColorText3">',
                    '</div>',
                    '</div>',

                    '<div class="groupColorScheme4">',
                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" style="width:100%"  name="rangeIntervalFromText4"   >',
                    '</div>',

                    '<div class="col-sm-2" style="padding: 2px 0;text-align: center;">',
                    '<label>% to</label>',
                    '</div>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" style="width:100%"  name="rangeIntervalToText4" >',
                    '</div>',

                    '<div class="col-sm-1" style="padding: 2px 0;text-align: center;">',
                    '<label>%</label style="padding: 0 5px;">',
                    '</div>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input  type="text" style="width:100%" class="rangeIntervalColorText" name="rangeIntervalColorText4">',
                    '</div>',
                    '</div>',

                    '<div class="groupColorScheme5">',
                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" style="width:100%"  name="rangeIntervalFromText5"   >',
                    '</div>',

                    '<div class="col-sm-2" style="padding: 2px 0;text-align: center;">',
                    '<label>% to</label>',
                    '</div>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input type="text" style="width:100%"  name="rangeIntervalToText5" >',
                    '</div>',

                    '<div class="col-sm-1" style="padding: 2px 0;text-align: center;">',
                    '<label>%</label style="padding: 0 5px;">',
                    '</div>',

                    '<div class="col-sm-3" style="padding: 0 5px;">',
                    '<input  type="text" style="width:100%" class="rangeIntervalColorText"  name="rangeIntervalColorText5">',
                    '</div>',
                    '</div>',
                    '</form>',

                    '</div>',
                    '</div>',

                    '</div>'
                ].join("\n"));
            }
            this.formContainer.find(".rangeIntervalColorText").each(function(){
                $(this).spectrum({
                    chooseText: "Apply",
                    cancelText: "Cancel",
                    preferredFormat: "hex",
                    showInput: true,
                    allowEmpty: true
                });
            });
            return this.formContainer;
        },


        getReportColorScheme: function () {
            return this.formContainer.find('#intervalColorScheme option:selected').text();
        },

        setReportColorScheme: function (selectedValue) {
            $('#intervalColorScheme').find('option[value="' + selectedValue + '"]').prop('selected', true);
        },

        getRangeIntervalFrom: function (counter) {
            return this.formContainer.find('[name="rangeIntervalFromText'+ counter + '"]').val();
        },

        setRangeIntervalFrom: function (counter,value) {
            this.formContainer.find('[name="rangeIntervalFromText'+ counter + '"]').val(value);

        },

        getRangeIntervalTo: function (counter) {
            return this.formContainer.find('[name="rangeIntervalToText'+ counter + '"]').val();
        },

        setRangeIntervalTo: function (counter,value) {
            this.formContainer.find('[name="rangeIntervalToText'+ counter + '"]').val( value );
        },

        getRangeIntervalColor: function (counter) {
            return this.formContainer.find('[name="rangeIntervalColorText'+ counter + '"]').val();
        },

        setRangeIntervalColor: function (counter,value) {
            this.formContainer.find('[name="rangeIntervalColorText'+ counter + '"]').spectrum('set', value );
        },

        saveOptions: function () {
            var reportObj = this.getController().getReport(),
                rangeInterval = this.getReportColorScheme();
            for(var i=1; i<= 5; i++) {
                if(i <= rangeInterval){
                    var rangeIntervalColor = this.getRangeIntervalColor(i);
                    reportObj.setVisualizationOption('rangeIntervalFrom' + i, this.getRangeIntervalFrom(i));
                    reportObj.setVisualizationOption('rangeIntervalTo' + i, this.getRangeIntervalTo(i));
                    if(rangeIntervalColor){
                        reportObj.setVisualizationOption('rangeIntervalColor' + i, rangeIntervalColor);
                    }
                }else{
                    reportObj.setVisualizationOption('rangeIntervalFrom' + i, "");
                    reportObj.setVisualizationOption('rangeIntervalTo' + i, "");
                    reportObj.setVisualizationOption('rangeIntervalColor' + i, "");
                }

            }

            reportObj.setVisualizationOption('intervals', rangeInterval);
        },

        showColorSchemeInput: function(){
            var _this = this,
                formContainer =  _this.getFormContainer();
            for(var i=0; i<6; i++) {
                formContainer.find('.groupColorScheme'+i).hide();
            }

            var count = _this.getReportColorScheme();
            if(parseInt(count)){
                for(var i=0; i<=count; i++) {
                    formContainer.find('.groupColorScheme'+i).show();
                }
            }

        },

        showHideColorSchemeInput: function(){
            var _this = this;
            var container = this.getFormContainer();
            container.find('#intervalColorScheme').change(function(){

                var count = _this.getReportColorScheme();

                for(var i=0; i<=5; i++) {
                    container.find('.groupColorScheme'+i).hide();
                }
                if(parseInt(count)){
                    for(var i=0; i<=count; i++) {
                        container.find('.groupColorScheme'+i).show();
                    }
                }

            });
        },

        setColorSchemeValues: function(){

            for(var i=0; i<6; i++) {

                var visual_data = this.getController().getReport().getVisual();

                if(visual_data['rangeIntervalFrom'+i] ) {
                    this.setRangeIntervalFrom(i,visual_data['rangeIntervalFrom'+i]);

                }

                if(visual_data['rangeIntervalTo'+i]) {
                    this.setRangeIntervalTo(i,visual_data['rangeIntervalTo'+i]);
                }

                if(visual_data['rangeIntervalColor'+i]) {
                    this.setRangeIntervalColor(i,visual_data['rangeIntervalColor'+i]);
                }

            }


            this.setReportColorScheme(visual_data['intervals']);
        },

        render: function() {
            if ( this.container ) {
                this.container.append(this.getFormContainer());
                this.setColorSchemeValues();
                this.showColorSchemeInput();
                this.showHideColorSchemeInput();
            }

            return this.getFormContainer();
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);