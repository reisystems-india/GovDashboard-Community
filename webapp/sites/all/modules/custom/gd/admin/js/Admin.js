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


(function (global, $, undefined) {

    if (typeof $ === 'undefined') {
        throw new Error('Admin requires jQuery');
    }

    if (typeof global.GD === 'undefined') {
        throw new Error('Admin requires GD');
    }

    var GD = global.GD;

    var Admin = GD.App.extend({

        container: null,
        host: GD.options.host,

        sections: [],

        init: function (options) {
            this._super(options);

            if (typeof options.host != 'undefined') {
                this.host = options.host;
            } else {
                this.options.host = this.host; // needs to be injected
            }

            if (typeof options.sections != 'undefined') {
                this.sections = options.sections;
            }

            if (typeof options.datasources != 'undefined') {
                this.datasources = [];
                for (var i = 0, datasource_count = options.datasources.length; i < datasource_count; i++) {
                    this.datasources.push(new GD.Datasource(options.datasources[i]));
                }
            }

            if (options.activeDS) {
                this.activeDS = options.activeDS;
            }

            if (options.token) {
                GD.options.csrf = options.token;
            }

            $.ajaxSetup({
                beforeSend: function (xhr, settings) {
                    xhr.setRequestHeader("X-CSRF-Token", GD.options.csrf);
                }
            });

            // sets the external url that embedded this app
            this.uri = new GD.Util.UriHandler();
        },

        run: function () {
            var _this = this;
            $(function () {
                if (_this.sections.length) {
                    if (_this.uri.path == '/cp' || _this.uri.path == '/cp/') {
                        _this.dispatch(_this.sections[0].getDefaultRoute());
                    } else {
                        _this.dispatch(_this.uri.path);
                    }

                    _this.renderSectionsNav();
                    _this.renderDatamartSelector();
                }
            });
        },

        renderSectionsNav: function () {
            var nav_items = '';
            for (var i = 0, count = this.sections.length; i < count; i++) {
                var active = '';
                if (this.sections[i].isActive()) {
                    active = ' class="active"';
                }
                nav_items += '<li' + active + '><a tabindex="10" class="gd-admin-main-nav-item" href="' + this.sections[i].getDefaultRoute() + '?ds=' + this.getActiveDatasourceName() + '">' + this.sections[i].getTitle() + '</a></li>';
            }

            var nav = $('<ul id="gd-admin-main-nav" class="nav navbar-nav">' + nav_items + '</ul>');

            $('div.container', $('#gd-navbar')).append(nav);
        },

        renderDatamartSelector: function () {
            var selectView = new GD.DatasourceSelectView(this.datasources, $('div.container', $('#gd-navbar')));
            selectView.render();
        },

        dispatch: function (request) {
            var dispatched = false;
            $.each(this.sections, function (i, section) {
                if (dispatched) {
                    return false;
                }
                $.each(section.routes, function (j, route) {
                    var routeMatcher = new RegExp(route.replace(/:[^\s/]+/g, '([\\w-]+)'));
                    if (request.match(routeMatcher)) {
                        dispatched = true;
                        section.dispatch(request);
                        return false;
                    }
                });
            });
        },

        getDatasources: function () {
            return this.datasources;
        },

        findDatasource: function (name) {
            for (var i = 0, count = this.datasources.length; i < count; i++) {
                if (name === this.datasources[i].getName()) {
                    return this.datasources[i];
                }
            }
            return null;
        },

        getActiveDatasource: function () {
            for (var i = 0, datasource_count = this.datasources.length; i < datasource_count; i++) {
                if (this.datasources[i].isActive()) {
                    return this.datasources[i];
                } else if (this.datasources[i].isActive() === null && this.activeDS && this.datasources[i].getName() === this.activeDS) {
                    return this.datasources[i];
                }
            }
            return null;
        },

        getActiveDatasourceName: function () {
            var datasource = this.getActiveDatasource();
            if (datasource === null) {
                return null;
            } else {
                return datasource.getName();
            }
        }
    });

    // add to global space
    global.GD.Admin = Admin;


    /* MODAL CLASS DEFINITION
     * ====================== */

    var Modal = function (element, options) {
        this.options = options;
        this.$element = $(element)
            .delegate('[data-dismiss="modal"]', 'click.dismiss.modal', $.proxy(this.hide, this));
        this.options.remote && this.$element.find('.modal-body').load(this.options.remote)
    };

    Modal.prototype = {

        constructor: Modal,

        toggle: function () {
            return this[!this.isShown ? 'show' : 'hide']();
        },

        show: function () {
            var _this = this;
            var e = $.Event('show');

            this.$element.trigger(e);

            if (this.isShown || e.isDefaultPrevented()) return;

            this.isShown = true;

            this.escape();

            this.backdrop(function () {
                var transition = $.support.transition && _this.$element.hasClass('fade');

                if (!_this.$element.parent().length) {
                    _this.$element.appendTo(document.body); //don't move modals dom position
                }

                _this.$element.show();

                if (transition) {
                    // force reflow
                    _this.$element[0].offsetWidth;
                }

                _this.$element
                    .addClass('in')
                    .attr('aria-hidden', false);

                _this.enforceFocus();

                transition ?
                    _this.$element.one($.support.transition.end, function () {
                        _this.$element.focus().trigger('shown')
                    }) :
                    _this.$element.focus().trigger('shown');

            });
        },

        hide: function (e) {
            e && e.preventDefault();

            e = $.Event('hide');

            this.$element.trigger(e);

            if (!this.isShown || e.isDefaultPrevented()) return;

            this.isShown = false;

            this.escape();

            $(document).off('focusin.modal');

            this.$element
                .removeClass('in')
                .attr('aria-hidden', true);

            $.support.transition && this.$element.hasClass('fade') ?
                this.hideWithTransition() :
                this.hideModal();
        },

        enforceFocus: function () {
            var _this = this;
            $(document).on('focusin.modal', function (e) {
                if (_this.$element[0] !== e.target && !_this.$element.has(e.target).length) {
                    _this.$element.focus();
                }
            })
        }

        , escape: function () {
            var _this = this;
            if (this.isShown && this.options.keyboard) {
                this.$element.on('keyup.dismiss.modal', function (e) {
                    e.which == 27 && _this.hide();
                })
            } else if (!this.isShown) {
                this.$element.off('keyup.dismiss.modal');
            }
        }

        , hideWithTransition: function () {
            var _this = this
                , timeout = setTimeout(function () {
                    _this.$element.off($.support.transition.end);
                    _this.hideModal();
                }, 500);

            this.$element.one($.support.transition.end, function () {
                clearTimeout(timeout);
                _this.hideModal();
            });
        }

        , hideModal: function () {
            var _this = this;
            this.$element.hide();
            this.backdrop(function () {
                _this.removeBackdrop();
                _this.$element.trigger('hidden');
            });
        }

        , removeBackdrop: function () {
            this.$backdrop && this.$backdrop.remove();
            this.$backdrop = null;
        }

        , backdrop: function (callback) {
            var _this = this
                , animate = this.$element.hasClass('fade') ? 'fade' : '';

            if (this.isShown && this.options.backdrop) {
                var doAnimate = $.support.transition && animate;

                this.$backdrop = $('<div class="modal-backdrop ' + animate + '" />')
                    .appendTo(document.body);

                this.$backdrop.click(
                    this.options.backdrop == 'static' ?
                        $.proxy(this.$element[0].focus, this.$element[0])
                        : $.proxy(this.hide, this)
                );

                if (doAnimate) this.$backdrop[0].offsetWidth;// force reflow

                this.$backdrop.addClass('in');

                if (!callback) return;

                doAnimate ?
                    this.$backdrop.one($.support.transition.end, callback) :
                    callback();

            } else if (!this.isShown && this.$backdrop) {
                this.$backdrop.removeClass('in');

                $.support.transition && this.$element.hasClass('fade') ?
                    this.$backdrop.one($.support.transition.end, callback) :
                    callback();

            } else if (callback) {
                callback();
            }
        }
    };


    /* MODAL PLUGIN DEFINITION
     * ======================= */

    var old = $.fn.modal;

    $.fn.modal = function (option) {
        return this.each(function () {
            var $this = $(this)
                , data = $this.data('modal')
                , options = $.extend({}, $.fn.modal.defaults, $this.data(), typeof option == 'object' && option);
            if (!data) $this.data('modal', (data = new Modal(this, options)));
            if (typeof option == 'string') data[option]();
            else if (options.show) data.show();
        })
    };

    $.fn.modal.defaults = {
        backdrop: true
        , keyboard: true
        , show: true
    };

    $.fn.modal.Constructor = Modal;


    /* MODAL NO CONFLICT
     * ================= */

    $.fn.modal.noConflict = function () {
        $.fn.modal = old;
        return this;
    };


    /* MODAL DATA-API
     * ============== */

    $(document).on('click.modal.data-api', '[data-toggle="modal"]', function (e) {
        var $this = $(this)
            , href = $this.attr('href')
            , $target = $($this.attr('data-target') || (href && href.replace(/.*(?=#[^\s]+$)/, ''))) //strip for ie7
            , option = $target.data('modal') ? 'toggle' : $.extend({remote: !/#/.test(href) && href}, $target.data(), $this.data());

        e.preventDefault();

        $target
            .modal(option)
            .one('hide', function () {
                $this.focus();
            });
    });

    /* TOOLTIP PUBLIC CLASS DEFINITION
     * =============================== */

    var Tooltip = function (element, options) {
        this.init('tooltip', element, options);
    };

    Tooltip.prototype = {

        constructor: Tooltip

        , init: function (type, element, options) {
            var eventIn
                , eventOut
                , triggers
                , trigger
                , i;

            this.type = type;
            this.$element = $(element);
            this.options = this.getOptions(options);
            this.enabled = true;

            triggers = this.options.trigger.split(' ');

            for (i = triggers.length; i--;) {
                trigger = triggers[i];
                if (trigger == 'click') {
                    this.$element.on('click.' + this.type, this.options.selector, $.proxy(this.toggle, this));
                } else if (trigger != 'manual') {
                    eventIn = trigger == 'hover' ? 'mouseenter' : 'focus';
                    eventOut = trigger == 'hover' ? 'mouseleave' : 'blur';
                    this.$element.on(eventIn + '.' + this.type, this.options.selector, $.proxy(this.enter, this));
                    this.$element.on(eventOut + '.' + this.type, this.options.selector, $.proxy(this.leave, this));
                }
            }

            this.options.selector ?
                (this._options = $.extend({}, this.options, {trigger: 'manual', selector: ''})) :
                this.fixTitle();
        }

        , getOptions: function (options) {
            options = $.extend({}, $.fn[this.type].defaults, this.$element.data(), options);

            if (options.delay && typeof options.delay == 'number') {
                options.delay = {
                    show: options.delay
                    , hide: options.delay
                };
            }

            return options;
        }

        , enter: function (e) {
            var defaults = $.fn[this.type].defaults
                , options = {}
                , self;

            this._options && $.each(this._options, function (key, value) {
                if (defaults[key] != value) options[key] = value;
            }, this);

            self = $(e.currentTarget)[this.type](options).data(this.type);

            if (!self.options.delay || !self.options.delay.show) return self.show();

            clearTimeout(this.timeout);
            self.hoverState = 'in';
            this.timeout = setTimeout(function () {
                if (self.hoverState == 'in') self.show();
            }, self.options.delay.show);
        }

        , leave: function (e) {
            var self = $(e.currentTarget)[this.type](this._options).data(this.type);

            if (this.timeout) clearTimeout(this.timeout);
            if (!self.options.delay || !self.options.delay.hide) return self.hide();

            self.hoverState = 'out';
            this.timeout = setTimeout(function () {
                if (self.hoverState == 'out') self.hide();
            }, self.options.delay.hide);
        }

        , show: function () {
            var $tip
                , pos
                , actualWidth
                , actualHeight
                , placement
                , tp
                , e = $.Event('show');

            if (this.hasContent() && this.enabled) {
                this.$element.trigger(e);
                if (e.isDefaultPrevented()) return;
                $tip = this.tip();
                this.setContent();

                if (this.options.animation) {
                    $tip.addClass('fade');
                }

                placement = typeof this.options.placement == 'function' ?
                    this.options.placement.call(this, $tip[0], this.$element[0]) :
                    this.options.placement;

                $tip
                    .detach()
                    .css({top: 0, left: 0, display: 'block'});

                this.options.container ? $tip.appendTo(this.options.container) : $tip.insertAfter(this.$element);

                pos = this.getPosition();

                actualWidth = $tip[0].offsetWidth;
                actualHeight = $tip[0].offsetHeight;

                switch (placement) {
                    case 'bottom':
                        tp = {top: pos.top + pos.height, left: pos.left + pos.width / 2 - actualWidth / 2};
                        break;
                    case 'top':
                        tp = {top: pos.top - actualHeight, left: pos.left + pos.width / 2 - actualWidth / 2};
                        break;
                    case 'left':
                        tp = {top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left - actualWidth};
                        break;
                    case 'right':
                        tp = {top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left + pos.width};
                        break;
                }

                this.applyPlacement(tp, placement);
                this.$element.trigger('shown');
            }
        }

        , applyPlacement: function (offset, placement) {
            var $tip = this.tip()
                , width = $tip[0].offsetWidth
                , height = $tip[0].offsetHeight
                , actualWidth
                , actualHeight
                , delta
                , replace;

            $tip
                .offset(offset)
                .addClass(placement)
                .addClass('in');

            actualWidth = $tip[0].offsetWidth;
            actualHeight = $tip[0].offsetHeight;

            if (placement == 'top' && actualHeight != height) {
                offset.top = offset.top + height - actualHeight;
                replace = true;
            }

            if (placement == 'bottom' || placement == 'top') {
                delta = 0;

                if (offset.left < 0) {
                    delta = offset.left * -2;
                    offset.left = 0;
                    $tip.offset(offset);
                    actualWidth = $tip[0].offsetWidth;
                    actualHeight = $tip[0].offsetHeight;
                }

                this.replaceArrow(delta - width + actualWidth, actualWidth, 'left');
            } else {
                this.replaceArrow(actualHeight - height, actualHeight, 'top');
            }

            if (replace) $tip.offset(offset);
        }

        , replaceArrow: function (delta, dimension, position) {
            this
                .arrow()
                .css(position, delta ? (50 * (1 - delta / dimension) + "%") : '');
        }

        , setContent: function () {
            var $tip = this.tip()
                , title = this.getTitle();

            $tip.find('.tooltip-inner')[this.options.html ? 'html' : 'text'](title);
            $tip.removeClass('fade in top bottom left right');
        },

        hide: function () {
            var $tip = this.tip();
            var e = $.Event('hide');

            this.$element.trigger(e);
            if (e.isDefaultPrevented()) return;

            $tip.removeClass('in');

            function removeWithAnimation() {
                var timeout = setTimeout(function () {
                    $tip.off($.support.transition.end).detach()
                }, 500);

                $tip.one($.support.transition.end, function () {
                    clearTimeout(timeout);
                    $tip.detach();
                });
            }

            $.support.transition && this.$tip.hasClass('fade') ?
                removeWithAnimation() :
                $tip.detach();

            this.$element.trigger('hidden');

            return this;
        }

        , fixTitle: function () {
            var $e = this.$element;
            if ($e.attr('title') || typeof($e.attr('data-original-title')) != 'string') {
                $e.attr('data-original-title', $e.attr('title') || '').attr('title', '');
            }
        }

        , hasContent: function () {
            return this.getTitle();
        }

        , getPosition: function () {
            var el = this.$element[0];
            return $.extend({}, (typeof el.getBoundingClientRect == 'function') ? el.getBoundingClientRect() : {
                width: el.offsetWidth
                , height: el.offsetHeight
            }, this.$element.offset());
        }

        , getTitle: function () {
            var title
                , $e = this.$element
                , o = this.options;

            title = $e.attr('data-original-title')
            || (typeof o.title == 'function' ? o.title.call($e[0]) : o.title);

            return title;
        }

        , tip: function () {
            return this.$tip = this.$tip || $(this.options.template);
        }

        , arrow: function () {
            return this.$arrow = this.$arrow || this.tip().find(".tooltip-arrow");
        }

        , validate: function () {
            if (!this.$element[0].parentNode) {
                this.hide();
                this.$element = null;
                this.options = null;
            }
        }

        , enable: function () {
            this.enabled = true;
        }

        , disable: function () {
            this.enabled = false;
        }

        , toggleEnabled: function () {
            this.enabled = !this.enabled;
        }

        , toggle: function (e) {
            var self = e ? $(e.currentTarget)[this.type](this._options).data(this.type) : this;
            self.tip().hasClass('in') ? self.hide() : self.show();
        }

        , destroy: function () {
            this.hide().$element.off('.' + this.type).removeData(this.type);
        }

    };


    /* TOOLTIP PLUGIN DEFINITION
     * ========================= */

    var tooltipOld = $.fn.tooltip;

    $.fn.tooltip = function (option) {
        return this.each(function () {
            var $this = $(this)
                , data = $this.data('tooltip')
                , options = typeof option == 'object' && option;
            if (!data) $this.data('tooltip', (data = new Tooltip(this, options)));
            if (typeof option == 'string') data[option]();
        });
    };

    $.fn.tooltip.Constructor = Tooltip;

    $.fn.tooltip.defaults = {
        animation: true
        , placement: 'top'
        , selector: false
        , template: '<div class="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
        , trigger: 'hover focus'
        , title: ''
        , delay: 0
        , html: false
        , container: false
    };


    /* TOOLTIP NO CONFLICT
     * =================== */

    $.fn.tooltip.noConflict = function () {
        $.fn.tooltip = tooltipOld;
        return this;
    };

    /* POPOVER PUBLIC CLASS DEFINITION
     * =============================== */

    var Popover = function (element, options) {
        this.init('popover', element, options);
    };


    /* NOTE: POPOVER EXTENDS BOOTSTRAP-TOOLTIP.js
     ========================================== */

    Popover.prototype = $.extend({}, $.fn.tooltip.Constructor.prototype, {

        constructor: Popover,

        setContent: function () {
            var $tip = this.tip()
                , title = this.getTitle()
                , content = this.getContent();

            $tip.find('.popover-title')[this.options.html ? 'html' : 'text'](title);
            $tip.find('.popover-content')[this.options.html ? 'html' : 'text'](content);
            $tip.removeClass('fade top bottom left right in');
        },

        hasContent: function () {
            return this.getTitle() || this.getContent();
        },

        getContent: function () {
            var $e = this.$element,
                o = this.options;

            return (typeof o.content == 'function' ? o.content.call($e[0]) : o.content) || $e.attr('data-content');
        },

        tip: function () {
            if (!this.$tip) {
                this.$tip = $(this.options.template);
            }
            return this.$tip;
        },

        destroy: function () {
            this.hide().$element.off('.' + this.type).removeData(this.type);
        }

    });


    /* POPOVER PLUGIN DEFINITION
     * ======================= */

    var popoverOld = $.fn.popover;

    $.fn.popover = function (option) {
        return this.each(function () {
            var $this = $(this)
                , data = $this.data('popover')
                , options = typeof option == 'object' && option;
            if (!data) $this.data('popover', (data = new Popover(this, options)));
            if (typeof option == 'string') data[option]();
        })
    };

    $.fn.popover.Constructor = Popover;

    $.fn.popover.defaults = $.extend({}, $.fn.tooltip.defaults, {
        placement: 'right'
        ,
        trigger: 'click'
        ,
        content: ''
        ,
        template: '<div class="popover"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
    });


    /* POPOVER NO CONFLICT
     * =================== */

    $.fn.popover.noConflict = function () {
        $.fn.popover = popoverOld;
        return this;
    };


})(typeof window === 'undefined' ? this : window, jQuery);
