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
        throw new Error('NotificationView requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('NotificationView requires GD');
    }

    var GD = global.GD;

    global.GD.NotificationView = GD.View.extend({
        viewContainer: null,
        viewCounter: null,
        left: 0,
        top: 0,

        init: function(object, container, options) {
            this._super(object, container, options);
            this.initVariables();
        },

        initVariables: function() {
            this.viewContainer = null;
            this.viewCounter = null;

            if (this.options) {
                this.right = this.options['right'];
                this.top = this.options['top'];
            }
        },

        setViewCounter: function(val) {
            this.getViewCounter().text(val);
        },

        getViewCounter: function() {
            if (!this.viewCounter) {
                this.viewCounter = $('<span class="bldr-ntf-vw-txt"></span>');

                if (this.object) {
                    this.setViewCounter(this.object.getValue());
                }
            }

            return this.viewCounter;
        },

        emptyViewContainer: function(){
            if(this.viewContainer){
                this.viewContainer.empty();
            }
        },

        getViewContainer: function() {
            if (!this.viewContainer) {
                this.viewContainer = $('<div class="bldr-ntf-vw-cntr"></div>');
                this.viewContainer.append(this.getViewCounter());
                if (this.right) {
                    this.viewContainer.css('right', this.right);
                }

                if (this.top) {
                    this.viewContainer.css('top', this.top);
                }
                this.viewContainer.css('position','absolute');
                if (!this.object || !this.object.getValue()) {
                    this.viewContainer.hide();
                }
            }

            return this.viewContainer;
        },

        update: function() {
            if (this.object) {
                this.setViewCounter(this.object.getValue());
                if (this.object.getValue()) {
                    this.getViewContainer().show();
                } else {
                    if (!this.object.isChanged()) {
                        this.getViewContainer().hide();
                    }
                }
                this.getViewContainer().toggleClass('changed', this.object.isChanged());
            }
        },

        render: function(force) {
            if (force) {
                this.initVariables();
            }

            if (this.container) {
                this.container.append(this.getViewContainer());
            }

            return this.getViewContainer();
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);