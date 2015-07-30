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


(function(global,undefined){


    if ( typeof global.GD === 'undefined' ) {
        throw new Error('GD_Chatter requires GD');
    }

    var GD = global.GD;

    (function($,Highcharts) {
        if ( typeof $ === 'undefined' ) {
            throw new Error('GD_Chatter requires jQuery');
        }

        var Chatter = GD.View.extend({
            chatterWindow: null,
            chatterHeader: null,
            chatterBody: null,
            chatterCommentBox: null,
            user: null,

            clearBody: function() {
                this.getChatterBody().empty();
            },

            getChatterHeader: function() {
                if (this.chatterHeader == null) {
                    this.chatterHeader = $('<div id="chatter-header" class="chatter-header"></div>');
                    this.back = $('<div class="chatter-header-back"></div>');
                    var title = $('<div class="chatter-header-title">GovDashboard</div>');
                    var exit = $('<div class="chatter-header-close"></div>');

                    var _this = this;
                    exit.click(function() {
                        _this.getChatterWindow().hide();
                    });

                    this.chatterHeader.append(this.back, title, exit);
                }

                return this.chatterHeader;
            },

            getChatterBody: function() {
                if (this.chatterBody == null) {
                    this.chatterBody = $('<div id="chatter-body" class="chatter-body"></div>');
                }

                return this.chatterBody;
            },

            getChatterCommentBox: function() {
                if (this.chatterCommentBox == null) {
                    this.chatterCommentBox = $('<div class="chatter-comment"></div>');
                    this.textBox = $('<input class="chatter-text-box" disabled="disabled" type="textbox"/>');
                    this.chatterCommentBox.append(this.textBox);
                }

                return this.chatterCommentBox;
            },

            getChatterWindow: function() {
                if (this.chatterWindow == null) {
                    this.chatterWindow = $('<div class="chatter-container" style="position: absolute;"></div>');
                    this.chatterWindow.append(this.getChatterHeader());
                    this.chatterWindow.append(this.getChatterBody());
                    this.chatterWindow.append(this.getChatterCommentBox());
                    this.chatterWindow.draggable({
                        scroll: true,
                        handle: '#chatter-header'
                    });
                }

                return this.chatterWindow;
            },

            startPolling: function() {
                if (this.user == null) {
                    var _this = this;
                    var interval = setInterval(function() {
                        $.ajax({
                            'url': 'chatter/user',
                            'success': function(data, textStatus, jqXHR) {
                                data = $.parseJSON(data);
                                if ((!$.isArray(data) || data[0]['errorCode'] == null) && data['errorCode'] != 1) {
                                    _this.user = new GD.ChatterUser(data);
                                    clearInterval(interval);
                                    _this.toggleGroupList();
                                }
                            }
                        });
                    }, 3000);
                }
            },

            toggleLoading: function() {
                this.clearBody();
                this.getChatterBody().append('<div class="chatter-loading"></div>');
                this.textBox.attr('disabled', 'disabled');
            },

            toggleGroupList: function() {
                this.toggleLoading();

                this.textBox.attr('disabled', 'disabled');
                var _this = this;
                $.ajax({
                    'url': 'chatter/groups',
                    'success': function(data, textStatus, jqXHR) {
                        _this.clearBody();
                        data = $.parseJSON(data);
                        if (!$.isArray(data) || data[0]['errorCode'] == null) {
                            var groups = data['response']['groups'];
                            $.each(groups, function (i, g) {
                                var item = $('<div class="chatter-group clear"></div>');
                                item.attr('group-id', g['id']);
                                var image = $('<img class="chatter-group-image" src="' + g['photo']['standardEmailPhotoUrl'] + '"/>');
                                var text = $('<span class="chatter-group-text">' + g['name'] + '</span>');
                                item.append(image, text);
                                _this.getChatterBody().append(item);
                            });

                            var item = $('<div class="chatter-group clear"></div>');
                            var image = $('<img class="chatter-group-image" src="http://www.veryicon.com/icon/64/Movie%20%26%20TV/The%20Simpsons%20Collection%20vol%202/Public.png"/>');
                            var text = $('<span class="chatter-group-text">Public Feed</span>');
                            item.append(image, text);
                            _this.getChatterBody().append(item);

                            $('.chatter-group').click(function() {
                                _this.toggleFeedItems($(this).attr('group-id'));
                            });

                            _this.back.unbind('click');
                            _this.back.click(function () {
                                _this.getChatterWindow().hide();
                            });
                        } else {
                            _this.toggleError();
                        }
                    }
                });
            },

            toggleFeedItems: function(group) {
                this.toggleLoading();

                var url = 'chatter/feeds?dashboard=' + $('#dash_viewer').attr('dashboard_id');
                if (group != null) {
                    url += '&group=' + group;
                }
                var _this = this;
                $.ajax({
                    'url': url,
                    'success': function(data, textStatus, jqXHR) {
                        _this.clearBody();
                        data = $.parseJSON(data);
                        if (!$.isArray(data) || data[0]['errorCode'] == null) {
                            if (data['response'] != null && data['response'].length != 0) {
                                $.each(data['response'], function(i, fi) {
                                    var item = $('<div class="chatter-feed-item clear clearfix"></div>');
                                    item.attr('feed-id', fi['id']);
                                    var image = $('<img class="chatter-feed-item-image" src="' + fi['actor']['photo']['standardEmailPhotoUrl'] + '"/>');
                                    var container = $('<div class="chatter-feed-item-details"></div>');
                                    var name = $('<span class="chatter-feed-item-name">' + fi['actor']['name'] + '</span>');
                                    var text = $('<span class="chatter-feed-item-text">' + fi['body']['text'] + '</span>');
                                    container.append(name, text);
                                    item.append(image, container);
                                    _this.getChatterBody().append(item);

                                    item.click(function() {
                                        _this.toggleComments($(this).attr('feed-id'), fi, group);
                                    });
                                });
                            } else {
                                var item = $('<div class="chatter-message">There are currently no discussions for the current dashboard. Add one using the text box below.</div>');
                                _this.getChatterBody().append(item);
                            }

                            _this.textBox.removeAttr('disabled');
                            _this.textBox.unbind('keyup');
                            _this.textBox.on('keyup', function(e) {
                                var code = e.keyCode || e.which;
                                if (code == 13) {
                                    _this.addFeed(group, $(this).val());
                                    $(this).val('');
                                    e.stopPropagation();
                                    e.preventDefault();
                                }
                            });

                            _this.back.unbind('click');
                            _this.back.click(function () {
                                _this.toggleGroupList();
                            });
                        } else {
                            _this.toggleError();
                        }
                    }
                });
            },

            toggleComments: function(feedId, feedItem, group) {
                this.toggleLoading();

                var url = 'chatter/comments?feed=' + feedId;
                var _this = this;
                $.ajax({
                    'url': url,
                    'success': function(data, textStatus, jqXHR) {
                        _this.clearBody();
                        data = $.parseJSON(data);
                        if (!$.isArray(data) || data[0]['errorCode'] == null) {
                            var item = $('<div class="chatter-feed-item clear clearfix"></div>');
                            var image = $('<img class="chatter-feed-item-image" src="' + feedItem['actor']['photo']['standardEmailPhotoUrl'] + '"/>');
                            var container = $('<div class="chatter-feed-item-details"></div>');
                            var name = $('<span class="chatter-feed-item-name">' + feedItem['actor']['name'] + '</span>');
                            var text = $('<span class="chatter-feed-item-text">' + feedItem['body']['text'] + '</span>');
                            container.append(name, text);
                            item.append(image, container);
                            _this.getChatterBody().append(item);

                            if (data['response'] != null && data['response'].length != 0) {
                                $.each(data['response']['comments'], function(i, c) {
                                    var item = $('<div class="chatter-comment-item clear clearfix"></div>');
                                    item.attr('comment-id', c['id']);
                                    var image = $('<img class="chatter-comment-item-image" src="' + c['user']['photo']['standardEmailPhotoUrl'] + '"/>');
                                    var container = $('<div class="chatter-comment-item-details"></div>');
                                    var name = $('<span class="chatter-comment-item-name">' + c['user']['name'] + '</span>');
                                    var text = $('<span class="chatter-comment-item-text">' + c['body']['text'] + '</span>');
                                    container.append(name, text);
                                    item.append(image, container);
                                    _this.getChatterBody().append(item);
                                });
                            }

                            _this.textBox.removeAttr('disabled');
                            _this.textBox.unbind('keyup');
                            _this.textBox.on('keyup', function (e) {
                                var code = e.keyCode || e.which;
                                if (code == 13) {
                                    _this.addComment(feedId, feedItem, $(this).val());
                                    $(this).val('');
                                    e.stopPropagation();
                                    e.preventDefault();
                                }
                            });

                            _this.back.unbind('click');
                            _this.back.click(function() {
                                _this.toggleFeedItems(group);
                            });
                        } else {
                            _this.toggleError();
                        }
                    }
                });
            },

            toggleError: function() {
                var item = $('<div class="chatter-message">There was an error retrieving data from the server. Please refresh the page and try again.</div>');
                _this.getChatterBody().append(item);
            },

            addFeed: function(groupId, value) {
                this.toggleLoading();
                var url = 'chatter/new/feed?comment=' + encodeURI(value) + '&dashboard=' + $('#dash_viewer').attr('dashboard_id');
                if (groupId != null) {
                    url += '&group=' + groupId;
                }
                var _this = this;
                $.ajax({
                    'url': url,
                    'success': function(data, textStatus, jqXHR) {
                        _this.toggleFeedItems(groupId);
                    }
                });
            },

            addComment: function(feedId, feedItem, value) {
                this.toggleLoading();
                var url = 'chatter/new/comment?feed=' + feedId + '&comment=' + encodeURI(value);
                var _this = this;
                $.ajax({
                    'url': url,
                    'success': function(data, textStatus, jqXHR) {
                        _this.toggleComments(feedId, feedItem);
                    }
                });
            },

            render: function() {
                $('body').append(this.getChatterWindow());
            },

            run: function() {
                this.toggleLoading();
                var _this = this;
                $.ajax({
                    'url': 'chatter/user',
                    'success': function(data, textStatus, jqXHR) {
                        data = $.parseJSON(data);
                        if ((!$.isArray(data) || data[0]['errorCode'] == null) && data['errorCode'] != 1) {
                            _this.user = new GD.ChatterUser(data);
                            _this.toggleGroupList();
                        } else {
                            window.open('chatter/access', '_blank');
                            _this.startPolling();
                        }
                    }
                });
            }
        });

        global.GD.Chatter = Chatter;

        $(document).on('gd-social-register', function(e) {
            var list = e['list'];
            var icon = $('<div class="chatter-icon"></div>');
            list.append(icon);
            icon.click(function() {
                if (global.GD.chatterWindow == null) {
                    global.GD.chatterWindow = new GD.Chatter();
                    global.GD.chatterWindow.render();
                    global.GD.chatterWindow.run();
                } else {
                    global.GD.chatterWindow.getChatterWindow().show();
                }
            });
        });
    })(typeof global.GD_jQuery != 'undefined' ? global.GD_jQuery : jQuery, typeof global.GD_Highcharts != 'undefined' ? global.GD_Highcharts : (typeof Highcharts != 'undefined' ? Highcharts : undefined));
})(typeof window === 'undefined' ? this : window);