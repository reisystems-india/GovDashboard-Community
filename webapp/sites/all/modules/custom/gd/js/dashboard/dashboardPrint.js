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


(function ($, window, document, undefined) {

    var printFeatures = function(msg){
        this.loadedCharts = [];
        this.deferred = {};
        this.chartSize = {};
        this.originalHeights = {};
        this.originalWidths = {};
        this.init(msg);
    };

    printFeatures.prototype = {
        init: function(arg){
            this.attachEvents();
        },

        attachEvents: function(){
            var oThis = this,
                deferred = oThis.deferred;

            jQuery(GD).on("chartRedrawn", function(e, chart){
                if($(chart.renderTo).attr("id") in deferred){
                    deferred[$(chart.renderTo).attr("id")].resolve();
                }
            });
            if (window.matchMedia) {
                var mediaQueryList = window.matchMedia('print');
                mediaQueryList.addListener(function(mql) {
                    if (mql.matches) {
                    } else {
                        oThis.afterPrint.call(oThis);
                    }
                });
            }
            window.onafterprint = function(){oThis.afterPrint.call(oThis)};
            jQuery(document).ready(function() {
                jQuery("#printButton").on("click", function(){
                    oThis.beforePrintProcessing.call(oThis);
                });
            });
        },
        reorderDOM: function() {
            var order = [];
            var reportContainerID = 'div.dashboard-report-container';
            $(reportContainerID).each(function() {
                var top = parseInt($(this).css("top").slice(0,-2));
                var left = parseInt($(this).css("left").slice(0,-2));
                order.push({ t: top, l: left, item : $(this) });
            });

            //  Sort items logically first
            order.sort(function(a, b) {
                if (a['t'] == b['t']) return a['l'] - b['l'];
                return a['t'] - b['t'];
            });

            var index = 0;
            //  Try to group dashboard text with reports
            $.each(order, function(i, o) {
                if (typeof o["index"] != 'undefined') return true;

                o['index'] = index++;

                if (!o['item'].find('div.report-map').length) {
                    o['item'].css('page-break-before', 'always');
                }
                if (o['item'].hasClass('dashboard-text')) {
                    //  Look for logically ordered chart
                    for (var l = i + 1; l < order.length; l++) {
                        if (order[l]["item"].hasClass('dashboard-text') || order[l]["item"].hasClass('dashboard-image') || typeof order[l]['index'] != 'undefined') continue;
                        order[l]["index"] = index++;
                        break;
                    }
                }
            });

            //   Resort based on index calculated
            order.sort(function(a, b) {
                return a['index'] - b['index'];
            });

            $.each(order, function(i, o) {
                o['item'].detach();
                if (order[i - 1]) {
                    o['item'].insertAfter(order[i - 1]['item']);
                } else {
                    $('#dash_viewer').prepend(o['item']);
                }
            });


        },
        beforePrintProcessing: function(){
            var deferred = this.deferred;

            this.originalHeights["#dash_viewer"] = jQuery("#dash_viewer").height();
            $('.dynamic-report').parent("div[id*='dashboard-report-container-']").css("height","auto");
            $(".dashboard-report-container:not('.dashboard-image')").addClass("dashboard-report-container-print");
            $(".dashboard-image").addClass("dashboard-image-print");
            jQuery("#printButton").hide();

            this.reorderDOM();
            this.pivotTablePrintAdjustment();
            this.tablePrintAdjustments();
            //this.highChartsPrintAdjustment();

            var deferredArr = $.map(deferred, function(value){
                return value;
            });
            $.when.apply(this,deferredArr).then(function(){
                setTimeout(function(){
                    window.print();
                },2000)
            });
        },
        pivotTablePrintAdjustment : function(){
            $(".gd-report-pivot-table, .gd-widget-pivot-table").width($("#dashboard-view").width());
            $(".gd-widget-pivot-table table").css("margin", "0 auto");
        },
        highChartsPrintAdjustment: function(){
            var deferred = this.deferred,
                chartHeightRestriction = null,
                _this = this;

            Highcharts.charts.forEach(function(el,index){
                if (!el) return;
                deferred[$(el.renderTo).attr("id")] = new $.Deferred();
                _this.chartSize[$(el.renderTo).attr("id")] = {width:el.chartWidth, height:el.chartHeight};
                var width = $("#dashboard-view").width();
                if(el.chartHeight > 1100){
                    chartHeightRestriction = 1100;
                }
                if(el.options.chart.type === "gauge" || el.options.chart.defaultSeriesType === "pie"){
                    if(width - el.chartWidth > 50){ // hack to avoid resizing full width charts, resetting full width chart has problem
                        el.options.legend.maxHeight = 100;
                        var elContainer = $(el.renderTo);
                        _this.originalHeights["#" + elContainer.parent().attr("id")] = elContainer.parent().height();
                        elContainer.parent().height(width+20);
                        _this.originalHeights["#" + elContainer.attr("id")] = elContainer.height();
                        elContainer.height(width);
                        _this.originalWidths["#" + elContainer.attr("id")] = elContainer.width();
                        elContainer.width(width);
                        elContainer.css("margin", "0 auto");
                        el.setSize(width, width);
                        el.redraw();
                    }else{
                        el.redraw();
                    }
                }else{
                    if(chartHeightRestriction){
                        var containerId = "#" + $(el.container).parents(".dashboard-report-container").attr("id");
                        _this.originalHeights[containerId] = jQuery(containerId).height();
                        $(containerId).height(chartHeightRestriction + 100);
                    }
                    el.setSize(width, chartHeightRestriction);
                }
            });
        },
        tablePrintAdjustments: function(){},
        afterPrint: function(){
            var chartSize = this.chartSize;
            $("#printButton").show();
            $("#dash_viewer").removeClass('print-view');
            $(".dashboard-report-container").removeClass("dashboard-report-container-printTable dashboard-report-container-print");
            $(".gd-report-pivot-table, .gd-widget-pivot-table").css("width","auto");
            $(".gd-widget-pivot-table table").css("margin", "0");
            $(".dashboard-image").removeClass("dashboard-image-print");
            /*Highcharts.charts.forEach(function(el,index){
                if (!el) return;
                if(el.options.chart.type === "gauge" || el.options.chart.defaultSeriesType === "pie") {
                    delete el.options.legend.maxHeight;
                    setTimeout(function() {
                        el.setSize(chartSize[jQuery(el.renderTo).attr("id")].width, chartSize[jQuery(el.renderTo).attr("id")].height, false);
                    }, 1);
                }else{
                    el.setSize(chartSize[jQuery(el.renderTo).attr("id")].width, chartSize[jQuery(el.renderTo).attr("id")].height, false);
                }
            });*/

            $.each(this.originalHeights, function(containerID, height) {
                 $(containerID).height(height);
            });

            $.each(this.originalWidths, function(containerID, width) {
                $(containerID).width(width);
            });
        }
    };

    window.GD_PrintFeatures = new printFeatures();
})(jQuery, this, this.document);