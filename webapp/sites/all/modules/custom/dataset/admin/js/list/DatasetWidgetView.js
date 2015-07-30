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
        throw new Error('DatasetWidgetView requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DatasetWidgetView requires GD');
    }

    var GD = global.GD;

    var DatasetWidgetView = GD.View.extend({

        init: function ( object, container, options ) {
            this._super(object, container, options);
        },

        initLayout: function () {
            if ( this.layout == null ) {
                this.layoutHeader = $('<div class="col-md-12" id="gd-view-dataset-widget-header">');
                var header_wrap = $('<div class="row">').append(this.layoutHeader);

                this.layoutBody = $('<div class="col-md-12" id="gd-view-dataset-widget-body">');
                var body_wrap = $('<div class="row">').append(this.layoutBody);

                this.layoutFooter = $('<div class="col-md-12" id="gd-view-dataset-widget-footer">');
                var footer_wrap = $('<div class="row">').append(this.layoutFooter);

                this.layout = $('<div id="gd-view-dataset-widget">').append(header_wrap,body_wrap,footer_wrap);

                this.container.append(this.layout);
            }
        },

        render: function () {

            this.initLayout();

            var markup = '';
            var widget_count = this.object.length;
            for ( var i = 0; i < widget_count; i++ ) {
                var widget = this.object[i];
                // start row
                if ( i % 3 == 0 ) {
                    markup += '<div class="row" style="margin-bottom: 20px;">';
                }

                markup += '  <div class="widget-hero col-md-4 well"><a href="'+widget.routes[0]+'" class="btn btn-large btn-block btn-primary">'+widget.getHeroText()+'</a></div>';

                // end row
                if ( i % 3 == 2 ) {
                    markup += '</div>';
                }

            }
            if ( i % 3 != 2 ) {
                markup += '</div>';
            }

            this.layoutBody.append(markup);
        }
    });

    // add to global space
    global.GD.DatasetWidgetView = DatasetWidgetView;

})(typeof window === 'undefined' ? this : window, jQuery);