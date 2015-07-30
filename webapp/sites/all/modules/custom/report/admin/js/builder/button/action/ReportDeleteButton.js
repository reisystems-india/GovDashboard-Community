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
        throw new Error('ReportDeleteButton requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportDeleteButton requires GD');
    }

    var GD = global.GD;

    global.GD.ReportDeleteButton = GD.BuilderButton.extend({
        modal: null,
        warnModal: null,
        reportDashboards: null,

        init: function(object, options) {

            this.buttonTop = $('#reportDeleteTop');
            this.buttonBottom = $('#reportDeleteBottom');
            this.buttonTop.on('report.delete.post', function(){location.href='/cp/report?ds='+GovdashReportBuilder.admin.getActiveDatasourceName();});
            this.buttonBottom.on('report.delete.post', function(){location.href='/cp/report?ds='+GovdashReportBuilder.admin.getActiveDatasourceName();});

            this._super(null, options);
        },

        changeButton: function(changeParam){
            this.getButtonTop().button(changeParam);
            this.getButtonBottom().button(changeParam);
        },

        getModal: function() {
            if (!this.modal) {
                this.modal = $('<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true"></div>');
                var content = $('<div class="modal-content"></div>');
                var header = $('<div class="modal-header"></div>');
                header.append('<h4>Delete Report</h4>');
                var body = $('<div class="modal-body row"><span style="font-size:25px;" class="glyphicon glyphicon-info-sign col-md-1"></span><div style="height:25px; line-height: 25px;" class="col-md-11">Are you sure you want to delete this report? This action cannot be undone.</div></div>');
                var footer = $('<div class="modal-footer"></div>');
                var deleteButton = $('<button type="button" class="btn btn-danger" data-dismiss="modal">Delete</button>');

                var _this = this;
                deleteButton.on('click',function() {
                    _this.changeButton('loading');
                    _this.getController().deleteReport();
                });

                footer.append(deleteButton);
                footer.append('<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>');
                content.append(header, body, footer);
                var dialog = $('<div class="modal-dialog"></div>');
                dialog.append(content);
                this.modal.append(dialog);
                $('body').append(this.modal);
            }

            return this.modal;
        },

        getReportUsedWarningModal: function(){
            if (!this.warnModal) {
                this.warnModal = $('<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true"></div>');
                var content = $('<div class="modal-content"></div>');
                var header = $('<div class="modal-header"></div>');
                header.append('<h4>Cannot delete Report</h4>');
                var body = $('<div class="modal-body row"><span style="font-size:25px;" ' +
                'class="glyphicon glyphicon-warning-sign col-md-1"></span><div style="height:25px; line-height: 25px;" ' +
                'class="col-md-11">This report cannot be deleted because it is used in following dashboard(s):</div><div class="usedInDashboardList"></div></div>');
                var footer = $('<div class="modal-footer"></div>');
                footer.append('<button type="button" class="btn btn-default" data-dismiss="modal">Ok</button>');
                content.append(header, body, footer);
                var dialog = $('<div class="modal-dialog"></div>');
                dialog.append(content);
                this.warnModal.append(dialog);
                $('body').append(this.warnModal);
            }

            var links = [];
            $.each(this.reportDashboards,function(key,dashboard){
                links.push('<a href="/cp/dashboard/'+dashboard.id+'" target="_blank" title="Visit Dashboard">'+dashboard.name+'</a>');
            });

            this.warnModal.find('.usedInDashboardList').html(links.join(', '));

            return this.warnModal;
        },

        clickedButton: function(e) {
            var _this= this;
            var ref = this.getController().getReportReference();
            ref.then(function(data){
                if (data.dashboards && data.dashboards.length > 0){
                    var dashboards = data.dashboards.map(function(val){
                        return val;
                    });
                    _this.reportDashboards = dashboards;
                    _this.getReportUsedWarningModal().modal();
                } else {
                    _this.getModal().modal();
                }
            });
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);