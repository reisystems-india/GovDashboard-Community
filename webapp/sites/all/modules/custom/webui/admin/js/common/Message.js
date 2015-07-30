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

var Message = isc.defineClass("Message", "HTMLFlow");

Message = Message.addProperties({

    initWidget: function ( args ) {

        this.overflow = "visible";

        if ( typeof args.displayTime != "undefined" ) {
            this.activateTimer(args.displayTime);
        }
    },

    activateTimer: function ( ms ) {
        var message = this;
        setTimeout(function(){
            message.animateHide({
                effect: "fade",
                callback: function() { message.markForDestroy(); },
                duration: 1200
            });
        }, ms);
    }
});