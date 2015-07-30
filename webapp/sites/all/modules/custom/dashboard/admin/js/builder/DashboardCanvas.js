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
        throw new Error('DashboardCanvas requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DashboardCanvas requires GD');
    }

    var GD = global.GD;

    var DashboardCanvas = GD.Class.extend({

        dashboard: null,
        options: null,
        canvas: null,

        init: function(object, options) {
            if (object) {
                this.dashboard = object;
            } else {
                this.dashboard = GD.Dashboard.singleton;
            }

            this.options = options;

            this.canvas = $('.govdb-grd').height(this.dashboard.getHeight());

            var _this = this;

            this.canvas.droppable({
                accept: ".dropItem",
                tolerance:'pointer',
                drop: function (event, ui) {
                    var type = ui.draggable.data('widget');
                    var c_offset = $(this).offset();
                    var position = {
                        left: event.pageX - event.offsetX - c_offset.left,
                        top: event.pageY - event.offsetY - c_offset.top
                    };

                    var widget = GD.DashboardWidgetFactory.getWidget(type,{position: position});
                    _this.addWidget(widget);
                    _this.renderWidget(widget);
                }
            });

            this.canvas.resizable({
                handles: "s",
                minHeight: 600,
                stop: function( event, ui ) {
                    _this.getDashboard().setHeight(_this.getCanvas().height());
                }
            });

            var widgets = this.dashboard.getWidgets();
            for ( var key in widgets ) {
                widgets[key].setContainer(this);
            }

            this.dashboard.setWidth(_this.getCanvas().width());

            $(document).on('add.dashboard.report',function(e){
                var widget = GD.DashboardWidgetFactory.getWidget('report',{
                    type: 'report',
                    content: e.added
                });
                var p = _this.getDashboard().getAutoPosition();
                widget['left'] = p['left'];
                widget['top'] = p['top'];
                _this.addWidget(widget);
                _this.renderWidget(widget);
            });

            $(document).on(GD.Dashboard.getEventList(), function(e) {
                var widgets = _this.getDashboard().getWidgets();
                for ( var key in widgets ) {
                    if ( widgets[key].getType() == 'report' ) {
                        widgets[key].reloadView();
                    }
                }
            });

            $(document).on('remove.dashboard.report', function(e) {
                var widgets = _this.getDashboard().getWidgets();
                var index = -1;
                for ( var key in widgets ) {
                    if ( widgets[key].getType() == 'report' && widgets[key].getContent() == e.removed ) {
                        index = key;
                        break;
                    }
                }

                if ( index ) {
                    _this.removeWidget(widgets[index].getId());
                }
            });

        },

        getCanvas: function () {
            return this.canvas;
        },

        getDashboard: function () {
            return this.dashboard;
        },

        renderWidgets: function () {
            var widgets = this.getDashboard().getWidgets();
            for ( var key in widgets ) {
                this.renderWidget(widgets[key]);
            }
        },

        renderWidget: function ( widget ) {
            this.getCanvas().append(widget.getView());
            widget.loadView();
            return this;
        },

        addWidget: function ( widget ) {
            widget.setContainer(this);
            this.getDashboard().addWidget(widget);
            return this;
        },

        removeWidget: function ( id ) {
            this.getDashboard().removeWidget(id);
        }
    });

    // add to global space
    GD.DashboardCanvas = DashboardCanvas;

})(typeof window === 'undefined' ? this : window, jQuery);