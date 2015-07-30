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
        throw new Error('BuilderListView requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('BuilderListView requires GD');
    }

    var GD = global.GD;

    global.GD.BuilderListView = GD.ListView.extend({
        header: null,
        flags: null,

        init: function(object, container, options) {
            if (options) {
                this.header = options['header'];
                this.flags = options['icons'];
            } else {
                this.header = null;
                this.flags = null;
            }

            this._super(object, container, options);
        },

        renderOptions: function(options) {
            this.listView.empty();
            this.listItems = {};
            if (options) {
                var index = 1;
                for(var key in options) {
                    this.renderOption(options, key, index);
                    index++;
                }
            }
        },

        renderOption: function(options, key, index) {
            var v = options[key];
            var unsupported = v.unsupported || false;
            var container = $('<div class="row lst-itm lst-itm-' + (key % 2 == 0 ? 'even' : 'odd') + '"></div>');
            var header = $('<span class="lst-itm-hdr">' + (this.header ? this.header : '') + ' <span class="bldr-lst-itm-ndx">' + index + '</span>: </span>');
            var label = $('<span class="lst-itm-txt"></span>');
            label.text(v['text']);
            container.append($('<div data-unsupported="'+ unsupported +'" class="col-md-8 lst-itm-val" value="' + GD.Utility.sanitizeValue(v['val']) + '"></div>').append(header, label), this.getIcons(v['val']));
            this.listItems[v['val']] = container;
            this.listView.append(container);
        },

        getIcons: function(value) {
            var container = $('<div class="bldr-lst-itm-icn-cntr pull-right"></div>');

            if (this.flags) {
                for(var key in this.flags) {
                    var icon = $('<span class="glyphicon bldr-lst-itm-icn"></span>');
                    if (value != null && value != undefined) {
                        icon.attr('value', value);
                    }

                    if (this.flags[key] == 'public' && this.options['publicCondition']) {
                        if (this.options['publicCondition'](value)) {
                            icon.addClass(this.getClasses(this.flags[key]));
                        }
                    } else {
                        icon.addClass(this.getClasses(this.flags[key]));
                    }

                    container.append(icon);
                }
                container.addClass('col-md-' + (this.flags.length + 2));
            }

            return container;
        },

        getClasses: function(flag) {
            if (!this.classes) {
                this.classes = {
                    'trash' : 'glyphicon-trash bldr-lst-itm-icn-trsh',
                    'public': 'glyphicon-globe bldr-lst-itm-icn-pblc'
                }
            }

            return this.classes[flag];
        },

        getChildren: function(selector) {
            return this.listView ? this.listView.find(selector) : null;
        },

        removeByValue: function(v) {
            this.listItems[v].nextAll().each(function() {
                var elem = $(this).find('span.bldr-lst-itm-ndx').get(0);
                var text = parseInt($(elem).text());
                $(elem).text(text - 1);
                $(this).toggleClass('lst-itm-odd lst-itm-even');
            });
            this.listItems[v].remove();
            delete this.listItems[v];
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);
