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
        throw new Error('ReportCanvas requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportCanvas requires GD');
    }

    var GD = global.GD;

    var ReportCanvas = GD.Class.extend({

        controller: null,
        options: null,
        canvas: null,

        init: function(object, options) {
            this.controller = object;
            this.options = options;
        },

        getCanvas: function () {
            if ( !this.canvas ) {
                this.attachEventHandlers();
                this.canvas = $('.govdb-grd');
            }
            return this.canvas;
        },

        getController: function () {
            return this.controller;
        },

        getReport: function () {
            return this.controller.getReport();
        },

        loadPreview: function () {
            var canvasObj = this.getCanvas();
            var chartType = this.getController().getReport().getChartType();
            if ( this.getController().getReportTypeToolbar().validatePreview(chartType) ) {
                canvasObj.empty();
                canvasObj.append('<div class="ldng"></div>');

                var width = this.getCanvas().width();
                var height = this.getCanvas().height();

                var data = {
                    config: JSON.stringify(this.getReport().getConfig())
                };

                $.ajax({
                    type: 'POST',
                    url: '/report/preview?'+'w='+width+'&h='+height,
                    data: data,
                    success: function ( data ){
                        canvasObj.html(data);
                        $(document).trigger({
                            type: 'reloaded.report.preview'
                        });
                    }
                });
            }
       },

        attachEventHandlers: function() {
            var _this = this;

            $(document).on('changed.report.type', function() {
                _this.loadPreview();
            });

            $(document).on('changed.report.filters', function() {
                _this.loadPreview();
            });
            
            $(document).on('changed.report.customview', function() {
                _this.loadPreview();
            });

            $(document).on('saved.report.visualize', function() {
                _this.loadPreview();
            });

            $(document).on('report.update.post', function() {
                _this.loadPreview();
            });
        }

    });

    // add to global space
    GD.ReportCanvas = ReportCanvas;

})(typeof window === 'undefined' ? this : window, jQuery);