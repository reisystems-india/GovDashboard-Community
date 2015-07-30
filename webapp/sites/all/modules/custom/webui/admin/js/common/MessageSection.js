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

var MessageSection = isc.defineClass("MessageSection");

MessageSection = MessageSection.addClassProperties({

    messageItems: {
        ok: [],
        warning: [],
        error: []
    },

    clearDisplay: function() {
        this.clearQueue();
        this.clearMessages();
    },

	getContainer: function () {
		if ( typeof this.container == 'undefined' )
		{
			this.container = isc.VLayout.create({
				ID: "messagesContainer",
				width: "1000",
				height: 1,
                membersMargin: 5,
                layoutMargin: 0
		    });
	    }

		return this.container;
	},

    addErrors: function ( message ) {
        this.addMessage(message, 'error');
    },

    addMessage: function ( message, messageType ) {
        if ( typeof messageType === 'undefined' ) {
            messageType = 'ok';
        }
        switch ( messageType ) {
            case 'warning' : this.messageItems.warning.push(message); break;
            case 'error' : this.messageItems.error.push(message); break;
            default: this.messageItems.ok.push(message); break;
        }
    },

    displayMessages: function() {
        this.showMessages();
    },

    showMessages: function () {
        var _this = this;
        jQuery.each(this.messageItems,function ( type, messages ) {
            if ( messages.length ) {
                _this.getContainer().addMember(_this.createMessage(messages,type));
            }
        });
        this.clearQueue();
        window.top.scroll(0, 0);
    },

    clearQueue: function () {
        this.messageItems.warning = [];
        this.messageItems.error = [];
        this.messageItems.ok = [];
    },

    showMessage: function ( message, messageType ) { // messages param should be an array or html
        if ( typeof messageType === 'undefined' ) {
            messageType = 'ok';
        }
        this.getContainer().addMember(this.createMessage(message,messageType));
        window.top.scroll(0, 0);
    },

    clearMessages: function() {
        this.getContainer().setMembers([]);
        this.clearQueue();
    },

    createMessage: function ( message, messageType ) {
        var container = jQuery('<div></div>');
        var alert = jQuery('<div class="alert"></div>');
        container.append(alert);
        alert.append('<button type="button" class="close" data-dismiss="alert">&times;</button>');
        if ( messageType == 'info' ) {
            alert.addClass('alert-info');
        } else if ( messageType == 'ok' ) {
            alert.addClass('alert-success');
        } else if ( messageType == 'error' ) {
            alert.addClass('alert-danger');
        } else if ( messageType == 'warning' ) {
            alert.addClass('alert-warning');
        }

        if ( jQuery.isArray(message) ) {
            alert.addClass('alert-block');
            if ( typeof messageType !== 'undefined' ) {
                switch ( messageType ) {
                    case 'error' :
                        alert.append('<h4>The following Error(s) were found:</h4>');
                        break;
                    case 'warning' :
                        alert.append('<h4>The following Warning(s) were found:</h4>');
                        break;
                }
            }

            var msgCount = message.length;
            if ( msgCount > 1 ) {
                var list = jQuery('<ul></ul>');
                for ( var i = 0; i < msgCount; i++ ) {
                    list.append('<li>' + message[i] + '</li>');
                }
                alert.append(list);
            } else {
                alert.append(message[0]);
            }
        } else {
            alert.append(message);
        }

        return Message.create({
            contents: container.html()
        });
    }
});
