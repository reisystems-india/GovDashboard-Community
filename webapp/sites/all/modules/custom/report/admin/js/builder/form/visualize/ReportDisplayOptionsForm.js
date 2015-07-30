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
        throw new Error('ReportDisplayOptionsForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportDisplayOptionsForm requires GD');
    }

    var GD = global.GD;

    global.GD.ReportDisplayOptionsForm = GD.View.extend({

        init: function(object, container, options) {
            this._super(object, container, options);


            this.allDisplayOptions = {

                displayChartTitle: {
                    name: 'displayChartTitle',
                    label: 'Show Title',
                    defaultValue: true
                },

                displayFilterOverlay: {
                    name: 'displayFilterOverlay',
                    label: 'Show Filter Overlay',
                    defaultValue: false
                },

                displayReportMenu: {
                    name: 'displayReportMenu',
                    label: 'Show Menu',
                    defaultValue: true
                },

                displayBorder: {
                    name: 'displayBorder',
                    label: 'Show Border',
                    defaultValue: false
                },

                displayLegend: {
                    name: 'displayLegend',
                    label: 'Show Legend',
                    defaultValue: true
                },

                displaySeries: {
                    name: 'displaySeries',
                    label: 'Show Multiple Axes',
                    defaultValue: false
                },

                displayGridLines: {
                    name: 'displayGridLines',
                    label: 'Show Grid Lines',
                    defaultValue: true
                },

                displayPieSliceLabels: {
                    name: 'displayPieSliceLabels',
                    label: 'Show Slice Labels',
                    defaultValue: true
                },

                displayPieSliceLabelsOptions: {
                    name: 'displayPieSliceLabelsOptions',
                    label: 'Label Options',
                    defaultValue: 0,
                    type: 'select',
                    valueMap: {
                        0: 'Value',
                        1: 'As Percent',
                        2: 'Value with Percent'
                    }
                },

                displayPieSliceBorders: {
                    name: 'displayPieSliceBorders',
                    label: 'Show Slice Borders',
                    defaultValue: true
                },

                displayPieShadow: {
                    name: 'displayPieShadow',
                    label: 'Show Pie Shadow',
                    defaultValue: true
                },

                advTableDisplayHeader: {
                    name: 'advTableDisplayHeader',
                    label: 'Display Table Header Row',
                    defaultValue: true
                },
                pvtTableDisableDrag: {
                    name: 'pvtTableDisableDrag',
                    label: 'Enable Drag and Drop',
                    defaultValue: true
                },

                stack: {
                    name: 'stack',
                    label: 'Stack Multiple Series',
                    defaultValue: false
                },

                displayPercentStack: {
                    name: 'displayPercentStack',
                    label: 'Stack as Percentages',
                    defaultValue: false
                },

                styleBackgroundColor: {
                    name: 'styleBackgroundColor',
                    label: 'Background Color',
                    defaultValue: null,
                    type: 'color'
                }
            };

            switch ( this.getController().getReport().getChartType() ) {
                case 'line' :
                case 'area' :
                    this.displayOptions = [
                        this.allDisplayOptions.displayChartTitle,
                        this.allDisplayOptions.displayFilterOverlay,
                        this.allDisplayOptions.displayReportMenu,
                        this.allDisplayOptions.displayBorder,
                        this.allDisplayOptions.displayLegend,
                        this.allDisplayOptions.displaySeries,
                        this.allDisplayOptions.displayGridLines,
                        this.allDisplayOptions.stack
                    ];
                    break;
                case 'bar' :
                case 'column' :
                    this.displayOptions = [
                        this.allDisplayOptions.displayChartTitle,
                        this.allDisplayOptions.displayFilterOverlay,
                        this.allDisplayOptions.displayReportMenu,
                        this.allDisplayOptions.displayBorder,
                        this.allDisplayOptions.displayLegend,
                        this.allDisplayOptions.displaySeries,
                        this.allDisplayOptions.displayGridLines,
                        this.allDisplayOptions.stack,
                        this.allDisplayOptions.displayPercentStack
                    ];
                    break;
                case 'gauge' :
                case 'sparkline' :
                case 'customview' :
                case 'map' :
                    this.displayOptions = [
                        this.allDisplayOptions.displayChartTitle,
                        this.allDisplayOptions.displayFilterOverlay,
                        this.allDisplayOptions.displayReportMenu
                    ];
                    break;

                case 'dynamic_text' :
                    this.displayOptions = [
                        this.allDisplayOptions.displayChartTitle,
                        this.allDisplayOptions.displayFilterOverlay,
                        this.allDisplayOptions.displayReportMenu,
                        this.allDisplayOptions.styleBackgroundColor
                    ];
                    break;

                case 'pie' :
                    this.displayOptions = [
                        this.allDisplayOptions.displayChartTitle,
                        this.allDisplayOptions.displayFilterOverlay,
                        this.allDisplayOptions.displayReportMenu,
                        this.allDisplayOptions.displayBorder,
                        this.allDisplayOptions.displayLegend,
                        this.allDisplayOptions.displayPieSliceLabels,
                        this.allDisplayOptions.displayPieSliceLabelsOptions,
                        this.allDisplayOptions.displayPieShadow
                    ];
                    break;

                case 'table' :
                    this.displayOptions = [
                        this.allDisplayOptions.displayChartTitle,
                        this.allDisplayOptions.displayFilterOverlay,
                        this.allDisplayOptions.displayReportMenu,
                        this.allDisplayOptions.advTableDisplayHeader
                    ];
                    break;
                case 'pivot_table':
                    this.displayOptions = [
                        this.allDisplayOptions.displayChartTitle,
                        this.allDisplayOptions.displayFilterOverlay,
                        this.allDisplayOptions.displayReportMenu,
                        this.allDisplayOptions.pvtTableDisableDrag
                    ];
                    break;
                default:
                    this.displayOptions = [
                        this.allDisplayOptions.displayChartTitle,
                        this.allDisplayOptions.displayFilterOverlay,
                        this.allDisplayOptions.displayReportMenu
                    ];
                    break;
            }

            var _this = this;
            $(document).off('save.report.visualize').on('save.report.visualize',function(){
                _this.saveOptions();
            });

        },

        getController: function () {
            return this.options.builder;
        },

        getDisplayOptionFormItem: function ( option ) {
            if ( option.type == 'select' ) {
                var getValueMapMarkup = function (option) {
                    var markup = '';
                    $.each(option.valueMap, function (k, v) {
                        markup = markup + '<option value="' + k + '"' + ((option.defaultValue == k) ? ' selected' : '') + '>' + v + '</option>';
                    });
                    return markup;
                };
                return [
                    '<div class="form-group">',
                    '<label>' + option.label + '</label>',
                    '<select id="' + option.name + '" name="' + option.name + '" class="form-control">',
                    getValueMapMarkup(option),
                    '</select>',
                    '</div>'
                ].join("\n");
            } else if ( option.type == 'color' ) {
                return [
                    '<div class="form-group">',
                    '<label>'+option.label+'</label>',
                    '<input type="text" id="'+option.name+'" name="'+option.name+'" class="form-control option-type-color" />',
                    '</div>'
                ].join("\n");
            } else {
                var checked = option.defaultValue?'checked="checked"':"";
                return [
                    '<div class="checkbox">',
                        '<label><input type="checkbox" name="'+option.name+'"'+checked+'/> '+option.label+'</label>',
                    '</div>'
                ].join("\n");
            }
        },

        getDisplayOptionFormItemValue: function ( option ) {
            if ( option.type == 'select' ){
                return $('select[name="'+option.name+'"]', this.getFormContainer()).val();
            } else if ( option.type == 'color' ){
                return $('input[name="'+option.name+'"]', this.getFormContainer()).val();
            } else {
                return $('input[name="'+option.name+'"]', this.getFormContainer()).prop('checked');
            }
        },

        setDisplayOptionFormItemValue: function ( option, value ) {
            if ( option.type == 'select' ) {
                $('select[name="'+option.name+'"]', this.getFormContainer()).val(value);
            } else if ( option.type == 'color' ) {
                $('input[name="'+option.name+'"]', this.getFormContainer()).spectrum('set', value );
            } else {
                $('input[name="'+option.name+'"]', this.getFormContainer()).prop('checked',(value));
            }
        },

        getFormContainer: function() {
            if ( !this.formContainer ) {
                var options = '',
                    _this = this;
                $.each(this.displayOptions,function(k,v){
                    options = options + _this.getDisplayOptionFormItem(v);
                });
                
                this.formContainer = $([
                    '<div class="panel panel-default">',
                        '<div class="panel-heading">',
                            '<div class="panel-title">',
                                '<div class="accordion-toggle" data-toggle="collapse" data-parent="#visualizePanelGroup" href="#reportDisplayOptionsPanel">',
								'Display Options',
								'</div>',
                            '</div>',
                        '</div>',
                        '<div id="reportDisplayOptionsPanel" class="panel-collapse collapse in">',
                            '<div class="panel-body"><form>'+options+'</form></div>',
                        '</div>',
                    '</div>'
                ].join("\n"));

                this.formContainer.find(".option-type-color").each(function(){
                    $(this).spectrum({
                        chooseText: "Ok",
                        cancelText: "Cancel",
                        preferredFormat: "hex",
                        showInput: true,
                        allowEmpty: true
                    });
                });
            }
            return this.formContainer;
        },

        saveOptions: function () {
            var _this = this;
            $.each(this.displayOptions,function(k,option){
                _this.getController().getReport().setVisualizationOption(option.name,_this.getDisplayOptionFormItemValue(option));
            });
        },

        render: function() {
            if ( this.container ) {
                this.container.append(this.getFormContainer());
                
                var reportObj = this.getController().getReport();
                var _this = this;

                $.each(this.displayOptions,function(k,option){
                    var value = reportObj.getVisualizationOption(option.name);
                    if ( value !== null && value !== '' ) {
                        _this.setDisplayOptionFormItemValue(option,value);
                    }
                });
            }

            return this;
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);