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

(function ($, win) {

    // setup the dialog
    var settings = {
        title: 'Warning!',
        message: 'Your session is about to expire!',
        countdown_message1: 'Due to inactivity, you will be signed out in ',
        countdown_message2: 'All unsaved data will be lost',
        question: 'Do you want to continue your session?'
    };


    var dialog = [
        '<div id="timeout-dialog" style="display:none;" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">',
        '  <div class="modal-dialog">',
        '    <div class="modal-content">',
        '      <div class="modal-header"><h3><span class="glyphicon glyphicon-warning-sign"></span> '+settings.title+'</h3></div>',
        '      <div class="modal-body">',
        '        <p>'+settings.message+'</p>',
        '        <p>'+settings.countdown_message1+'</p>',
        '        <span id="timeout-countdown-value" style="font-weight:bold"></span>',
        '        <p>'+settings.countdown_message2+'</p>',
        '        <p>'+settings.question+'</p>',
        '      </div>',
        '      <div class="modal-footer">',
        '        <button class="btn timeout-keep-alive-btn" data-dismiss="modal" aria-hidden="true">Yes, Keep Working</button>',
        '        <button class="btn timeout-sign-out-btn" data-dismiss="modal" aria-hidden="true">No, Logoff</button>',
        '      </div>',
        '    </div>',
        '  </div>',
        '</div>'
    ].join("\n");

    dialog = $(dialog);

    $('body').append(dialog);

    dialog.modal({
        'backdrop': 'static',
        'keyboard': true,
        'show': false
    });

    var GD_IdleTimer = {
        countdownDialog: null,
        sessionCountdown: null,
        countdownTimer: 60,
        idleTime: 400,
        countdownTime: 200,
        pollUrl: null,
        lastActive: null,
        timerValue: null,

        init: function ( settings ) {
            this.countdownDialog = settings.dialog;
            this.refresh = settings.resume;
            this.terminate = settings.cancel;
            this.pollUrl = settings.pollUrl;
            this.timerValue = $(settings.timerValue);

            // get variables from php and convert to milliseconds
            var config = Drupal.settings.timeout || { session_limit: 1800, countdown_timer: 60, session_last_active: null };
            this.sessionCountdown = config.session_limit * 1000;
            this.countdownTimer = config.countdown_timer;
            this.idleTime = config.session_limit * 1000;
            this.lastActive = config.session_last_active;

            var _this = this;
            $(document).bind("idle.idleTimer", function(){
                _this.idle();
            });

            if (settings.autoStart) {
                this.start();
            }
        },

        start: function() {
            $.idleTimer(this.idleTime);
        },

        stop: function () {
            var _this = this;
            $.idleTimer('destroy');
            $(document).bind("idle.idleTimer", function(){
                _this.idle();
            });
        },

        restart: function() {
            this.stop();
            this.start();
        },

        idle: function() {
            var _this = this;
            _this.stop();

            $.ajax({
                url: this.pollUrl,
                success: function(data, textStatus, jqXHR) {
                    if (data === _this.lastActive) {
                        _this.startCountdown();
                    } else if (!data) {
                        _this.timeout();
                    } else if (isNaN(data)) {
                        //  Server sent back something that wasn't a number, reload in that case
                        location.reload();
                    } else {
                        _this.lastActive = data;
                        var current = new Date().getTime();

                        //  Reset idle timer
                        _this.idleTime = _this.sessionCountdown - (current - _this.lastActive);
                        if (_this.idleTime < 0) {
                            _this.startCountdown();
                        } else {
                            _this.start();
                        }
                    }
                }
            });
        },

        startCountdown: function() {
            var counter = this.countdownTimer;
            var _this = this;

            this.countdownDialog.modal('show');

            $(this.refresh).click(function() {
                _this.stopCountdown();
                _this.keepAlive();
            });

            $(this.terminate).click(function() {
                _this.stopCountdown();
                _this.timeout();
            });

            $(_this.timerValue).html(counter).append(' sec');
            this.countdown = win.setInterval(function(){
                if(--counter === 0){
                    _this.stopCountdown();
                    _this.timeout();
                } else {
                    $(_this.timerValue).html(counter).append(' sec');
                }
            }, 1000);
        },

        stopCountdown: function() {
            win.clearInterval(this.countdown);
            this.countdownDialog.modal('hide');
        },

        timeout: function() {
            var destination = window.location.pathname + window.location.search;
            destination = destination.slice(1); // strips leading '/'

            //  regex sanitation
            destination = destination.replace(/user\?destination=/g, '');
            window.location.href = "/timeout?destination="+destination;
        },

        keepAlive: function() {
            var _this = this;

            $.ajax({
                url: '/timeout/keepAlive',
                success: function(data, textStatus, jqXHR) {
                    _this.lastActive = data;
                    var current = new Date().getTime();
                    _this.idleTime = _this.sessionCountdown - (current - _this.lastActive);
                    if (_this.idleTime < 0) {
                        _this.idleTime = _this.sessionCountdown;
                    }
                    _this.start();
                }
            });
        }
    };

    GD_IdleTimer.init({
        dialog: dialog,
        resume: 'button.timeout-keep-alive-btn',
        cancel: 'button.timeout-sign-out-btn',
        autoStart: true,
        keepAliveUrl: '/timeout/keepAlive',
        pollUrl: '/timeout/lastActive',
        timerValue: '#timeout-countdown-value'
    });

})(jQuery, window);