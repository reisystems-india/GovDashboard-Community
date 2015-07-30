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
        throw new Error('ReportColumnButton requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportColumnButton requires GD');
    }

    var GD = global.GD;

    global.GD.ReportColumnButton = GD.ReportConfigButton.extend({

        notification: null,
        notificationView: null,
        initialized: false,

        init: function(object, options) {
            this._super({'button': '#reportColumnButton','form': '#reportColumnForm'}, options);
            this.form.css('top', '31px');
            this.form.css('left', '99px');
            var reportObj = this.getController().getReport();
            this.notification = new GD.Notification(reportObj.getColumns().length);
            this.notificationView = new GD.NotificationView(this.notification, $('#reportColumnButton'), {'right': '0', 'top': '-10px'});
            this.notificationView.render();
            if (reportObj.isNew()) {
                this.disable();
            }
        },

        initForm: function() {
            this.distroyResizable();
            this.columnForm = new GD.ReportColumnForm(null,this.form,this.options);
        },

        openForm: function(isNewDataSet) {
            this._super();
            //  TODO Review, no need to call render more than once. Show/Hide works fine after initial render
            if (!this.initialized) {
                this.initialized = true;
                this.columnForm.render(isNewDataSet);
            }
            var reportFrm = $('#reportColumnForm');
            if(isNewDataSet || !reportFrm.data("uiResizable")){
                reportFrm.resizable({
                    minWidth:"750",
                    handles: 'e',
                    resize: function( event, ui ) {
                        $(document).trigger({
                            type: 'resize.column.form',
                            width: ui.size.width
                        });
                    }
                });
            }
        },

        distroyResizable: function(){
            var reportFrm = $('#reportColumnForm');
            if(reportFrm.data("uiResizable")){
                reportFrm.resizable("destroy");
            }
        },
        attachEventHandlers: function() {
            this._super();
            var _this = this;

            $(document).on('builder.report.loaded builder.report.column.add builder.report.column.remove', function() {
                _this.updateNotification();
            });

            $(document).on('changed.report.dataset', function() {
                _this.distroyResizable();
                _this.form.empty();
                _this.initForm();
                _this.enable();
                _this.openForm(true);
                _this.updateNotification();
            });
            
            $(document).on('changed.report.columns', function() {
                _this.closeForm();
                _this.updateNotification();
                var controllerObj = _this.getController(),
                    disabledColumns = controllerObj.reportTypeToolbar.updateToolbar();

                controllerObj.getCanvas().loadPreview();
            });

            $(document).on('changed.report.type', function() {
                 _this.closeForm();
            });
        },

        updateNotification: function(updated) {
            var columnsLength = this.getController().report.getColumns().length;
            if(!updated && this.notification.original !== columnsLength){
                this.notification.resetOriginal();
            }
            this.notification.setValue(columnsLength, updated);
            this.notificationView.update();
        }

    });

})(typeof window === 'undefined' ? this : window, jQuery);