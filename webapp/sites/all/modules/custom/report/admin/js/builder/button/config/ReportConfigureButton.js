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
        throw new Error('ReportConfigureButton requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportConfigureButton requires GD');
    }

    var GD = global.GD;

    global.GD.ReportConfigureButton = GD.ReportConfigButton.extend({
        notification: null,
        notificationView: null,
        
        init: function(object, options) {
            this._super({'button': '#reportConfigureButton','form': '#reportConfigureForm'}, options);
            this.form.css('top', '31px');
            this.form.css('left', '304px');
        },

        initForm: function() {
            this.form.append(this.getPanelGroup());
            this.notification = new GD.Notification(this.getConfigCount());
            this.notificationView = new GD.NotificationView(this.notification, $('#reportConfigureButton'), {'right': '0', 'top': '-10px'});
            this.notificationView.render();
        },

        getConfigCount: function () {
            var reportObj = this.getController().getReport(),
                columnLookup = reportObj.getDataset()?(reportObj.getDataset().getColumnLookup() ? Object.keys(reportObj.getDataset().getColumnLookup()) : null): null,
                columnConfig = reportObj.getColumnConfigs(),
                formatCount = 0;

            if (columnConfig && columnLookup) {
                for(i in columnConfig){
                    if($.inArray(columnConfig[i].columnId, columnLookup) !== -1){
                        formatCount++;
                    }
                }
            }else if(columnConfig){
                formatCount = columnConfig.length;
            }

            var sortCount = 0;
            if (reportObj.getOrderBys() != null) {
                sortCount = reportObj.getOrderBys().length;
            }

            var limitCount = 0;
            if (reportObj.getLimit() != null) {
                limitCount = 1;
            }

            var offsetCount = 0;
            if (reportObj.getOffset() != 0) {
                offsetCount = 1;
            }

            return (formatCount + sortCount + (limitCount || offsetCount));
        },
        
        openForm: function() {
            this._super();
            var panelGroup = this.getPanelGroup();
            if (!this.sortForm || !this.limitForm || !this.formatForm || !this.orderForm) {
                $('.panel-body', panelGroup).empty();

                this.sortForm = new GD.ReportSortForm(null,panelGroup.find('#reportSortForm .panel-body'),this.options);
                this.sortForm.render();
                this.limitForm = new GD.ReportLimitForm(null,panelGroup.find('#reportLimitForm .panel-body'),this.options);
                this.limitForm.render();
                this.formatForm = new GD.ReportFormatForm(null,panelGroup.find('#reportFormatForm .panel-body'),this.options);
                this.formatForm.render();
                this.orderForm = new GD.ReportColumnOrderForm(null,panelGroup.find('#reportColumnOrderForm .panel-body'),this.options);
                this.orderForm.render();
            } else {
                this.sortForm.update();
                this.formatForm.update();
                this.orderForm.update();
            }

            var reportObj = this.getController().getReport();
            if (reportObj.getChartType() === 'pivot_table') {
                $('#reportSortPanel').hide();
                $('#reportLimitPanel').hide();
                $('#reportColumnOrderPanel').hide();
            }else{
                $('#reportSortPanel').show();
                $('#reportLimitPanel').show();
                $('#reportColumnOrderPanel').show();
            }
            this.updateNotification();

            if (this.form.data("uiResizable")){
                this.form.resizable("destroy");
            }
            this.form.resizable ({
                minWidth: 300,
                maxWidth: 596,
                handles: 'e'
            });
        },
        
        getPanelGroup: function() {
            if ( !this.panelGroup ) {
                this.panelGroup = $([
                    '<div class="panel-group" id="configurePanelGroup">',
                        '<div id="reportSortPanel" class="panel panel-default">',
                         '<div class="panel-heading">',
                                '<div class="panel-title">',
									'<div class="accordion-toggle" data-toggle="collapse" data-parent="#configurePanelGroup" href="#reportSortForm">',
									'Sort',
									'</div>',
                                '</div>',
                            '</div>',
                            '<div id="reportSortForm" class="panel-collapse collapse in">',
                                '<div class="panel-body">',
                                '</div>',
                            '</div>',
                        '</div>',
                        '<div id="reportLimitPanel" class="panel panel-default">',
                           '<div class="panel-heading">',
                                '<div class="panel-title">',
									'<div class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#configurePanelGroup" href="#reportLimitForm">',
									'Limit',
									'</div>',
                                '</div>',
                            '</div>',
                            '<div id="reportLimitForm" class="panel-collapse collapse">',
                                '<div class="panel-body">',
                                '</div>',
                            '</div>',
                        '</div>',
                        '<div id="reportFormatPanel" class="panel panel-default">',
                            '<div class="panel-heading">',
                                '<div class="panel-title">',
									'<div class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#configurePanelGroup" href="#reportFormatForm">',
									'Format',
                                    '</div>',
                                '</div>',
                            '</div>',
                            '<div id="reportFormatForm" class="panel-collapse collapse">',
                                '<div class="panel-body">',
                                '</div>',
                            '</div>',
                        '</div>',
                        '<div id="reportColumnOrderPanel" class="panel panel-default">',
                            '<div class="panel-heading">',
                                '<div class="panel-title">',
									'<div class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#configurePanelGroup" href="#reportColumnOrderForm">',
									'Column Order',
                                    '</div>',
                                '</div>',
                            '</div>',
                            '<div id="reportColumnOrderForm" class="panel-collapse collapse">',
                                '<div class="panel-body">',
                                '</div>',
                            '</div>',
                        '</div>',
                    '</div>'
                ].join("\n"));
            }
            return this.panelGroup;
        },
        
        attachEventHandlers: function() {
            this._super();
            var _this = this,
                controllerObj = _this.getController();
            
            
            $(document).on('changed.query.sort', function(e) {
                _this.closeForm();
                _this.updateNotification(false);
                controllerObj.getCanvas().loadPreview();
            });

            $(document).on('changed.query.limit', function(e) {
                 _this.closeForm();
                _this.updateNotification(false);
                controllerObj.getCanvas().loadPreview();
            });

            $(document).on('changed.column.order', function(e) {
                _this.closeForm();
                controllerObj.getCanvas().loadPreview();
            });

            $(document).on('changed.column.format', function(e) {
                 _this.closeForm();
                _this.updateNotification(false);
                controllerObj.getCanvas().loadPreview();
            });
            
            $(document).on('remove.column.format', function(e) {
                 _this.closeForm();
                _this.updateNotification(false);
                controllerObj.getCanvas().loadPreview();
            });
            
            $(document).on('changed.report.columns', function(e) {
                if(!_this.formatForm){
                    _this.openForm();
                }
                _this.formatForm.listForm.updateAddedConf(e.columns);


                _this.enable();
                _this.updateNotification(false);
            });
            $(document).on('preprocess.report.save', function(){
                if(_this.formatForm){
                    _this.formatForm.listForm.removeTempConfig();
                }
            });

            $(document).on('changed.report.type', function() {
                 _this.closeForm();
            });

            $(document).on('report.update.post', function() {
                _this.updateNotification(true);
            });
        },

        updateNotification: function(updateOriginal) {
            var configureCount = this.getConfigCount();

            if ( updateOriginal ){
                this.notification.setValue(configureCount,true);
                this.notification.setChanged(false);
            } else {
                this.notification.setValue(configureCount);
            }
            this.notificationView.update();
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);