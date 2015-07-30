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
        throw new Error('BreadcrumbView requires GD');
    }

    var GD = global.GD;

    (function($,Highcharts) {

        if ( typeof $ === 'undefined' ) {
            throw new Error('BreadcrumbView requires jQuery');
        }

        var BreadcrumbView = GD.View.extend({

            init: function ( object, container, options ) {
                this._super(object,container,options);

                if (this.object != null) {
                    this.formitem = $('<a tabindex="3000" title="" class="'+this.getViewCss()+'" ' + this.getHref() + '>'+object.text+'</a>');
                    if (this.object.ddf != null) {
                        var _this = this;
                        var c = '';
                        $.each(this.object.ddf, function ( index, info ) {
                            var labelText = info.name + ' = ';
                            if ( $.isArray(_this.object.ddf.value) ) {
                                $.each(_this.object.ddf.value, function ( index, val ) {
                                    _this.formitem.append('<span class="hidden-offscreen">'+labelText+val+'</span>');
                                    c += '<span class="gd-label-breadcrumb-tooltip">'+labelText+val+'</span>';
                                });
                            } else {
                                _this.formitem.append('<span class="hidden-offscreen">'+labelText+info.value+'</span>');
                                c += '<span class="gd-label-breadcrumb-tooltip">'+labelText+info.value+'</span>';
                            }
                        });
                        this.formitem.popover({
                            html: true,
                            trigger: 'hover focus',
                            placement: 'bottom',
                            content: c
                        });
                    }
                }

                return this;
            },

            getHref: function () {
                if ( this.object.getLink() ) {
                    return 'href="' + this.object.getLink() + '"';
                }

                return '';
            },

            getViewCss: function () {
                return this._super() + ' gd-view-breadcrumb';
            },

            render: function () {
                var output = this.formitem;

                if ( this.container == null ) {
                    return output;
                } else {
                    $(this.container).append(output);
                }
            }
        });

        global.GD.BreadcrumbView = BreadcrumbView;
    })(typeof global.GD_jQuery != 'undefined' ? global.GD_jQuery : jQuery, typeof global.GD_Highcharts != 'undefined' ? global.GD_Highcharts : (typeof Highcharts != 'undefined' ? Highcharts : undefined));
})(typeof window === 'undefined' ? this : window);
