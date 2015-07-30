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
        throw new Error('DashboardDeleteButton requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DashboardDeleteButton requires GD');
    }

    var GD = global.GD;

    global.GD.DashboardDeleteButton = GD.BuilderButton.extend({
        modal: null,

        init: function(object, options) {
            if (object) {
                this.dashboard = object;
            } else {
                this.dashboard = GD.Dashboard.singleton;
            }

            this.buttonTop = $('#dashboardDeleteTop');
            this.buttonBottom = $('#dashboardDeleteBottom');

            this.buttonTop.on('dashboard.delete.post', function(){location.href='/cp/dashboard?ds='+GovdashDashboardBuilder.admin.getActiveDatasourceName();});
            this.buttonBottom.on('dashboard.delete.post', function(){location.href='/cp/dashboard?ds='+GovdashDashboardBuilder.admin.getActiveDatasourceName();});
            
            this._super(null, options);
        },

        changeButton:function(changeParam){
            this.getButtonTop().button(changeParam);
            this.getButtonBottom().button(changeParam);
        },

        getModal: function() {
            if (!this.modal) {
                this.modal = $('<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true"></div>');
                var content = $('<div class="modal-content"></div>');
                var header = $('<div class="modal-header"></div>');
                header.append('<h4>Delete Dashboard</h4>');
                var body = $('<div class="modal-body row"><span style="font-size:25px;" class="glyphicon glyphicon-info-sign col-md-1"></span><div style="height:25px; line-height: 25px;" class="col-md-11">Are you sure you want to delete this dashboard? This action cannot be undone.</div></div>');
                var footer = $('<div class="modal-footer"></div>');
             
                var deleteButton = $('<button type="button" class="btn btn-danger" data-dismiss="modal">Delete</button>');

                var _this = this;
                deleteButton.on('click',function() {
                    _this.getController().deleteDashboard();
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

        clickedButton: function(e) {
            this.getModal().modal();
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);