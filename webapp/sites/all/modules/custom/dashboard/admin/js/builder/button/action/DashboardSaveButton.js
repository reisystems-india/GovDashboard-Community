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
        throw new Error('DashboardSaveButton requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DashboardSaveButton requires GD');
    }

    var GD = global.GD;

    var DashboardSaveButton = GD.BuilderButton.extend({
        init: function(object, options) {
            this._super(null, options);

            this.buttonTop = $('#dashboardSaveTop');
            this.buttonBottom = $('#dashboardSaveBottom');

            this.attachEventHandlers();
        },

        changeButton: function(changeParam){
            this.getButtonTop().button(changeParam);
            this.getButtonBottom().button(changeParam);
        },

        clickedButton: function( e ) {
            this.closeConfigForms();
            var dashboardName = this.getController().getDashboard().getName();
            if ( !dashboardName || dashboardName.replace(/^\s+|\s+$/g,'') === '' ) {
                this.changeButton('reset');
                global.GD.DashboardBuilderMessagingView.addErrors('You must provide a dashboard title.');
                global.GD.DashboardBuilderMessagingView.displayMessages();
            } else if ( this.getController().getDashboard().getReportIds().length < 1 ) {
                this.changeButton('reset');
                global.GD.DashboardBuilderMessagingView.addErrors('You must select at least one report.');
                global.GD.DashboardBuilderMessagingView.displayMessages();
            } else {
                // look up name
                var _this = this;
                GD.DashboardFactory.getDashboardList(_this.getController().getDatasourceName(), function ( data ) {
                    var unique = true;
                    if ( data && $.isArray(data) ) {
                        for (var i = 0, dataLength=data.length; i < dataLength; i++) {
                            if ( data[i].name == dashboardName && (_this.getController().getDashboard().isNew() || data[i].id != _this.getController().getDashboard().getId()) ) {
                                unique = false;
                                break;
                            }
                        }
                    }
                    if ( unique ) {
                        _this.getController().saveDashboard();
                    } else {
                        _this.changeButton('reset');
                        global.GD.DashboardBuilderMessagingView.addErrors('Dashboard title is not unique.');
                        global.GD.DashboardBuilderMessagingView.displayMessages();
                    }

                }, function(jqXHR, textStatus, errorThrown) {
                    _this.changeButton('reset');
                    global.GD.DashboardBuilderMessagingView.addErrors(jqXHR.responseText);
                    global.GD.DashboardBuilderMessagingView.displayMessages();
                });
            }
        }
    });

    GD.DashboardSaveButton = DashboardSaveButton;

})(typeof window === 'undefined' ? this : window, jQuery);