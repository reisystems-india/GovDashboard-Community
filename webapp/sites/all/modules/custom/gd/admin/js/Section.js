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
        throw new Error('Section requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('Section requires GD');
    }

    var GD = global.GD;

    var Section = GD.Class.extend({

        name: null,
        title: null,

        weight: 0,

        routes: [],

        widgets: [],
        sections: [],

        parent: null,

        layout: null,

        active: false,

        init: function ( options ) {
            var _this = this;

            this.uri = new GD.Util.UriHandler();

            if ( typeof options.weight != 'undefined' ) {
                this.weight = options.weight;
            }

            if ( typeof options.widgets != 'undefined' ) {
                this.widgets = options.widgets;
                $.each(this.widgets,function(i,widget){
                    $.each(widget.routes,function(j,route){
                        _this.routes.push(route);
                    });
                });
            }

            if ( typeof options.sections != 'undefined' ) {
                this.sections = options.sections;
                $.each(this.sections,function(i,section){
                    $.each(section.routes,function(j,route){
                        _this.routes.push(route);
                    });
                });
            }
        },

        getName: function () {
            return this.name;
        },

        getTitle: function () {
            return this.title;
        },

        getWeight: function () {
            return this.weight;
        },

        getDefaultRoute: function () {
            return null;
        },

        initLayout: function () {
            if ( this.layout == null ) {
                this.layoutHeader = $('<div class="row gd-section-header"><div class="col-md-6 gd-section-header-left"></div><div class="col-md-6 gd-section-header-right"></div></div>');

                this.layoutBody = $('<div class="col-md-12 gd-section-body">');
                var body_wrap = $('<div class="row"></div>').append(this.layoutBody);

                this.layoutFooter = $('<div class="col-md-12 gd-section-footer">');
                var footer_wrap = $('<div class="row"></div>').append(this.layoutFooter);

                this.layout = $('<div class="gd-section"></div>').append(this.layoutHeader,body_wrap,footer_wrap);

                if ( this.parent == null ) {
                    $('#gd-admin-body').append(this.layout);
                } else {
                    this.parent.layoutBody.append(this.layout);
                }
            }
        },

        setActive: function () {
          this.active = true;
        },

        isActive: function () {
            return this.active;
        },

        getSectionMenu: function () {
            var nav = '';
            var active_found = false;
            for ( var i = 0, count = this.sections.length; i < count; i++ ) {
                var active = '';
                if ( this.sections[i].isActive() ) {
                    active = ' class="active"';
                    active_found = true;
                }
                nav += '<li'+active+'><a href="'+this.sections[i].getDefaultRoute()+'">'+this.sections[i].getTitle()+'</a></li>';
            }
            if ( nav != '' ) {
                nav = '<li'+((active_found)?'':' class="active"')+'><a href="'+this.getDefaultRoute()+'"><i class="icon-home"></i></a></li>'+nav;
                return '<ul class="nav nav-tabs">'+nav+'</ul>';
            } else {
                return null;
            }
        },

        dispatch: function ( request ) {
            this.initLayout();
            this.dispatched = false;
            var _this = this;

            $.each(this.widgets,function(i,widget){
                if ( _this.dispatched ) {
                    return false;
                }
                $.each(widget.routes,function(j,route){
                    var routeMatcher = new RegExp(route.replace(/:[^\s/]+/g, '([\\w-]+)'));
                    if ( request.match(routeMatcher) ) {
                        _this.dispatched = true;
                        _this.setActive();
                        widget.Section = _this;
                        widget.dispatch(request);
                        return false;
                    }
                });
            });

            if ( !this.dispatched ) {
                $.each(this.sections,function(i,section){
                    if ( _this.dispatched ) {
                        return false;
                    }
                    $.each(section.routes,function(j,route){
                        var routeMatcher = new RegExp(route.replace(/:[^\s/]+/g, '([\\w-]+)'));
                        if ( request.match(routeMatcher) ) {
                            _this.dispatched = true;
                            _this.setActive();
                            section.parent = _this;
                            section.dispatch(request);
                            return false;
                        }
                    });
                });
            }
        }
    });

    // add to global space
    global.GD.Section = Section;

})(typeof window === 'undefined' ? this : window, jQuery);
