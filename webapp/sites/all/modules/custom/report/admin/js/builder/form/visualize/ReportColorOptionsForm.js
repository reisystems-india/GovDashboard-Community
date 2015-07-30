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
        throw new Error('ReportColorOptionsForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportColorOptionsForm requires GD');
    }

    var GD = global.GD;

    GD.ReportColorOptionsForm = GD.View.extend({
        colorTree: null,

        init: function(object, container, options) {
            this._super(object, container, options);
            var _this = this;
            $(document).on('save.report.visualize',function(){
                _this.saveOptions();
            });
        },

        getFormContainer: function() {
            if ( !this.formContainer ) {
                var reportObj = this.getController().getReport();
                var chartType = reportObj.getChartType();
                var type = (chartType == 'bar' ? 'Bar' : (chartType == 'column' ? 'Column' : 'Slice'));

                this.formContainer = $([
                    '<div class="panel panel-default">',
                    '<div class="panel-heading">',
                    '<div class="panel-title">',
                    '<div class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#visualizePanelGroup" href="#reportColorPanel">',
                    type + ' Color Options',
                    '</div>',
                    '</div>',
                    '</div>',
                    '<div id="reportColorPanel" class="panel-collapse collapse">',
                    '<div class="panel-body">',
                    '</div>',
                    '</div>',
                    '</div>'
                ].join("\n"));
            }
            return this.formContainer;
        },

        getColorTree: function() {
            if (!this.colorTree) {
                var controllerObj = this.getController();
                this.colorTree = new GD.ReportColorColumnTree(
                    {
                        dataset: controllerObj.getReport().getDatasetName()
                    },
                    this.getFormContainer().find('#reportColorPanel .panel-body'),
                    {
                        'controller': controllerObj,
                        'multiple': false
                    }
                );
            }

            return this.colorTree;
        },

        getColorColumn: function() {
            return this.getColorTree().getSelected();
        },

        saveOptions: function() {
            if (this.getColorColumn()) {
                this.getController().getReport().setVisualizationOption('useColumnDataForColor', this.getColorColumn()[0]);
            }
        },

        getController: function () {
            return this.options.builder;
        },

        render: function () {
            if ( this.container ) {
                this.container.append(this.getFormContainer());
                this.getColorTree().render();

                var reportObj = this.getController().getReport();
                var color = reportObj.getVisualizationOption('useColumnDataForColor');
                if (color) {
                    this.getColorTree().selectNode(color);
                }
            }

            return this.getFormContainer();
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);