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

(function(global,$, undefined) {

    if ( typeof $ === 'undefined' ) {
        throw new Error('MessagingView requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('MessagingView requires GD');
    }

    var GD = global.GD;

    var MessagingView = GD.Class.extend({
        options: null,

        messageContainer: null,
        messages: null,

        noticesContainer: null,
        warningsContainer: null,
        errorsContainer: null,

        init: function ( container, options ) {
            this.options = options;

            this.messageContainer = $(container);
            this.noticesContainer = $('<div class="gd-notices-messages gd-message-container"></div>');
            this.warningsContainer = $('<div class="gd-warnings-messages gd-message-container"></div>');
            this.errorsContainer = $('<div class="gd-errors-messages gd-message-container"></div>');
            this.messageContainer.append(this.noticesContainer, this.warningsContainer, this.errorsContainer);
            this.clearMessages();
        },

        clearMessages: function () {
            this.messages = {
                'notices': [],
                'warnings': [],
                'errors': []
            };
        },

        clearDisplay: function() {
            this.noticesContainer.empty();
            this.warningsContainer.empty();
            this.errorsContainer.empty();
        },

        clearNotices: function () {
            this.messages['notices'] = [];
        },

        clearWarnings: function () {
            this.messages['warnings'] = [];
        },

        clearErrors: function () {
            this.messages['errors'] = [];
        },

        addNotices: function ( notice, clearMessages ) {
            var messages = [];

            if ( typeof clearMessages != 'undefined' ) {
                this.clearNotices();
            }

            if ( $.isArray(notice) ) {
                $.each(notice, function (i, n) {
                    messages.push(n);
                })
            } else {
                messages.push(notice);
            }

            this.messages['notices'] = this.messages['notices'].concat(messages);
        },

        addWarnings: function ( warning, clearMessages ) {
            var messages = [];

            if ( typeof clearMessages != 'undefined' ) {
                this.clearWarnings();
            }

            if ( $.isArray(warning) ) {
                $.each(warning, function (i, w) {
                    messages.push(w);
                })
            } else {
                messages.push(warning);
            }

            this.messages['warnings'] = this.messages['warnings'].concat(messages);
        },

        addErrors: function ( error, clearMessages ) {
            var messages = [];

            if ( typeof clearMessages != 'undefined' ) {
                this.clearErrors();
            }

            var _this = this;
            if ( $.isArray(error) ) {
                $.each(error, function (i, e) {
                    messages.push(_this.sanitize(e));
                })
            } else {
                messages.push(error);
            }

            this.messages['errors'] = _.union(this.messages['errors'],messages);
        },

        displayMessages: function ( keepOldMessages ) {
            if ( typeof keepOldMessages == 'undefined' ) {
                this.noticesContainer.empty();
                this.warningsContainer.empty();
                this.errorsContainer.empty();
            }

            if (this.messages['notices'].length) {
                this.noticesContainer.append(this.getMessage('notice', this.messages['notices']));
            }

            if (this.messages['warnings'].length) {
                this.warningsContainer.append(this.getMessage('warn', this.messages['warnings']));
            }

            if (this.messages['errors'].length) {
                this.errorsContainer.append(this.getMessage('error', this.messages['errors']));
            }

            this.clearMessages();
            $("html, body").animate({ scrollTop: 0 }, "slow");
        },

        getMessage: function(type, n) {
            return type == 'notice' ? this.getNoticeMessage(n) : (type == 'warn' ? this.getWarningMessage(n) : this.getErrorMessage(n));
        },

        showMessage: function(message, type, keepOldMessages) {
            if (!keepOldMessages) {
                this.noticesContainer.empty();
                this.warningsContainer.empty();
                this.errorsContainer.empty();
            }

            var m = this.getMessage(type, message);

            var container = this.errorsContainer;
            if (type == 'notice') {
                container = this.noticesContainer;
            } else if (type == 'warning') {
                container = this.warningsContainer;
            }

            container.append(m);
            $("html, body").animate({ scrollTop: 0 }, "slow");
        },

        getNoticeMessage: function ( n ) {
            var el = $('<div class="alert alert-success"></div>');
            el.append('<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>');
            if ($.isArray(n) && n.length > 1) {
                var list = $('<ul style="max-height:100px; overflow-y:auto;"></ul>');
                $.each(n, function(i, m) {
                    var item = $('<li></li>');
                    item.append(m);
                    list.append(item);
                });
                el.append(list);
            } else if ($.isArray(n)) {
                el.append(n[0]);
            } else {
                el.append(n);
            }
            return el;
        },

        getWarningMessage: function ( w ) {
            var el = $('<div class="alert alert-warning"></div>');
            el.append('<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>');
            if ($.isArray(w) && w.length > 1) {
                el.append('<strong>The following warnings were found: </strong>');
                var list = $('<ul style="max-height:100px; overflow-y:auto;"></ul>');
                $.each(w, function(i, m) {
                    var item = $('<li></li>');
                    item.append(m);
                    list.append(item);
                });
                el.append(list);
            } else if ($.isArray(w)) {
                el.append(w[0]);
            } else {
                el.append(w);
            }
            return el;
        },

        getErrorMessage: function ( e ) {
            var el = $('<div class="alert alert-danger"></div>');
            el.append('<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>');
            if ($.isArray(e) && e.length > 1) {
                el.append('<strong>The following errors were found: </strong>');
                var list = $('<ul style="max-height:100px; overflow-y:auto;"></ul>');
                $.each(e, function(i, m) {
                    var item = $('<li></li>');
                    item.append(m);
                    list.append(item);
                });
                el.append(list);
            } else if ($.isArray(e)) {
                el.append(e[0]);
            } else {
                el.append(e);
            }
            return el;
        },

        sanitize: function (message) {
            return $('<div/>').html(message).text();
        },

        clean: function() {
            this.clearMessages();
            this.clearDisplay();
        }
    });

    global.GD.MessagingView = MessagingView;

})(typeof window === 'undefined' ? this : window, jQuery);