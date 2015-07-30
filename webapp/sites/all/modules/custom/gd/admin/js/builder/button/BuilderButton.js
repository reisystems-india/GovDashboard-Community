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
        throw new Error('BuilderButton requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('BuilderButton requires GD');
    }

    var GD = global.GD;

    global.GD.BuilderButton = GD.Class.extend({
        controller: null,
        options: null,
        button: null,

        init: function(object, options) {
            this.options = options;

            if (object) {
                this.setButton(object['button']);
            }

            if (options && options['builder']) {
                this.controller = options['builder'];
            } else {
                throw new Error('Button requires access to controller object.');
            }

            this.attachEventHandlers();
        },

        setButton: function(id) {
            this.button = id ? $(id) : null;
        },

        getButton: function() {
            return this.button;
        },

        getButtonTop: function() {
            return this.buttonTop;
        },

        getButtonBottom: function() {
            return this.buttonBottom;
        },

        clickedButton: function(e) {},

        closeConfigForms: function(){
            var configButtons = this.controller.configButtons;
            $.each(configButtons,function(k,button){
                if (button.hasOwnProperty('closeForm')) {
                    button.closeForm();
                }
            });
        },

        attachEventHandlers: function() {
            var _this = this;

            if ( this.hasOwnProperty('buttonTop') && this.hasOwnProperty('buttonBottom')) {
                this.buttonTop.off('click').on('click',function (e) {
                    if (!$(this).hasClass('disabled')) {
                        _this.clickedButton(e);
                    }
                });
                this.buttonBottom.off('click').on('click',function (e) {
                    if (!$(this).hasClass('disabled')) {
                        _this.clickedButton(e);
                    }
                });
            } else if (this.button) {
                this.button.off('click').on('click',function (e) {
                    if (!$(this).hasClass('disabled')) {
                        _this.clickedButton(e);
                    }
                });
            }
        },

        getController: function() {
            return this.controller;
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);