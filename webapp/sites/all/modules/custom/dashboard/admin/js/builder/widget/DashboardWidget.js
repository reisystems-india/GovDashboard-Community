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
        throw new Error('DashboardWidget requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DashboardWidget requires GD');
    }

    var GD = global.GD;

    var DashboardWidget = GD.Class.extend({

        id: null,
        width: null,
        height: null,
        top: null,
        left: null,
        content: null,
        type: null,

        canvasItem: null,
        contextMenu: null,

        container: null,

        init: function(object, options) {

            if ( object ) {

                if ( object.position ) {
                    this.left = object.position.left;
                    this.top = object.position.top;
                }

                if ( object.size ) {
                    this.width = object.size.width;
                    this.height = object.size.height;
                }

                if ( object.content ) {
                    this.content = object.content;
                }

                if ( object.id ) {
                    this.id = object.id;
                }
            }

            if ( options ) {
                this.options = options;
            }

        },

        getId: function() {
            return this.id;
        },

        setId: function(id) {
            this.id = id;
            return this;
        },

        getType: function () {
            return this.type;
        },

        getContent: function() {
            return this.content;
        },

        setContent: function(content) {
            this.content = content;
        },

        getWidth: function () {
            return this.width;
        },

        getHeight: function () {
            return this.height;
        },

        getLeft: function () {
            return this.left;
        },

        getTop: function () {
            return this.top;
        },

        getView: function () {
            return this.getCanvasItem();
        },

        loadView: function ( callback ) {
            this.getCanvasItem().find('.inner-content').html(this.getContent());
            if ( callback ) {
                callback();
            }
        },

        setContainer: function ( container ) {
            this.container = container;
            return this;
        },

        getContainer: function () {
            return this.container;
        },

        getCanvasItem: function () {
            if ( !this.canvasItem ) {
                this.canvasItem = $('<div><div class="inner-content" style="width:100%;height:100%;overflow:hidden;"></div></div>');
                this.canvasItem.css({
                    position: 'absolute',
                    top: this.top,
                    left: this.left,
                    width: this.width,
                    height: this.height,
                    border: '1px dashed #666'
                });

                var _this = this;

                this.canvasItem.resizable({
                    grid: 2,
                    handles: "all",
                    containment: ".govdb-grd",
                    start: function( event, ui ) {
                        _this.canvasItem.find('.inner-content').hide();
                    },
                    stop: function( event, ui ) {
                        _this.reloadView();
                    }
                });

                this.canvasItem.draggable({
                    grid: [2,2],
                    cursor: "move",
                    containment: ".govdb-grd"
                });

                this.getContextMenu();

                this.canvasItem.contextMenu({
                    menuSelector: '#widget-context-menu'+this.getId(),
                    menuSelected: function (invokedOn, selectedMenu) {
                        var action = selectedMenu.data("action");
                        _this.handleContextMenuAction(action);
                    }
                });
            }

            return this.canvasItem;
        },

        focus: function() {
            this.getCanvasItem().focus();
        },

        getContextMenu: function () {
            if ( !this.contextMenu ) {
                var contextMenuMarkup = [
                  '<ul id="widget-context-menu'+this.getId()+'" class="dropdown-menu dsb-widget-context" role="menu" style="display:none;">',
                    '<li role="presentation" class="dropdown-header">Actions</li>',
                    '<li role="presentation"><a role="menuitem" tabindex="-1" href="#" data-action="delete"><span class="glyphicon glyphicon-remove"></span> Remove</a></li>',
                  '</ul>'
                ];

                this.contextMenu = $(contextMenuMarkup.join("\n"));
                $('body').append(this.contextMenu);
            }

            return this.contextMenu;
        },

        addContextMenuItem: function (item) {
            this.getContextMenu().append(item);
        },

        handleContextMenuAction: function ( action ) {
            if ( action == 'delete' ) {
                this.getContainer().removeWidget(this.getId());
            }
        },

        clean: function () {
            if ( this.canvasItem ) {
                this.canvasItem.remove();
            }
            if ( this.contextMenu ) {
                this.contextMenu.remove();
            }
        },

        getConfig: function() {
            return {
                type: this.getType(),
                position: {
                    left: this.getCanvasItem().position().left,
                    top: this.getCanvasItem().position().top
                },
                size: {
                    // adding 2px to compensate for border
                    width: this.getCanvasItem().width()+2,
                    height: this.getCanvasItem().height()+2
                },
                content: this.getContent()
            };
        },

        attachCanvasItemEvent: function ( event, callback ) {
            this.getCanvasItem().on(event,callback);
        },

        reloadView: function() {
            this.getCanvasItem().find('.inner-content').hide();

            var _this = this;
            this.loadView(function(){
                _this.canvasItem.find('.inner-content').show();
            });
        }
    });

    GD.DashboardWidget = DashboardWidget;

})(typeof window === 'undefined' ? this : window, jQuery);