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
        throw new Error('DatasourceSelectView requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DatasourceSelectView requires GD');
    }

    var GD = global.GD;

    var DatasourceSelectView = GD.View.extend({

        init: function ( object, container, options ) {
            this._super(object, container, options);
            this.initLayout();
        },

        initLayout: function () {
            if ( typeof this.layout == 'undefined' ) {
                this.layout = $([
                    '<ul class="nav navbar-nav navbar-right">',
                        '<li class="divider-vertical"></li>',
                        '<li class="dropdown">',
                            '<a tabindex="10" class="dropdown-toggle" data-toggle="dropdown" href="#"><strong id="gd-admin-datasource-name">Default</strong> <b class="caret"></b></a>',
                            '<ul class="dropdown-menu" style="z-index:1000000;max-height:260px;overflow-y:scroll;"></ul>',
                        '</li>',
                    '</ul>'
                ].join("\n"));

                var li = [];
                for ( var i = 0, datasource_count=this.object.length; i < datasource_count; i++ ) {
                    if ( !this.object[i].isActive() ) {
                        li.push('<li><a tabindex="10" href="#" rel="'+this.object[i].getName()+'">'+this.object[i].getPublicName()+'</a></li>');
                    } else {
                        $('.dropdown-toggle strong',this.layout).text(this.object[i].getPublicName());
                        li.push('<li><a href="#"><span class="glyphicon glyphicon-ok"></span> '+this.object[i].getPublicName()+'</a></li>');
                    }
                }

                this.list = $(li.join("\n"));

                $('.dropdown-menu',this.layout).append(this.list);

                $('.dropdown-menu a[rel]',this.layout).on('click',function(event){
                    var path = window.location.pathname.replace(/^\/|\/$/g, '');
                    var s = path.split('/');
                    window.location.href = '/' + s[0] + (typeof s[1] != 'undefined' ? ('/' + s[1]) : '') + '?ds='+$(this).attr('rel');
                });
            }
        },


        render: function () {
            this.container.append(this.layout);
        }
    });

    // add to global space
    global.GD.DatasourceSelectView = DatasourceSelectView;

})(typeof window === 'undefined' ? this : window, jQuery);