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
        throw new Error('ReportSparklineForm requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportSparklineForm requires GD');
    }

    var GD = global.GD;

    global.GD.ReportSparklineForm = GD.View.extend({

        init: function(object, container, options) {
            this._super(object, container, options);
        },

        getController: function () {
            return this.options.builder;
        },

        getFormContainer: function() {
            if ( !this.formContainer ) {
                this.formContainer = $([
                    '<div>',
                        '<p></p>',
                        '<div class="form-group">',
                        '<div id="sparkline-type">',
                             '<label class="radio-inline">',
                                '<input type="radio" name="sparklineTypeOption" value="line" checked> Line',
                            '</label>',
                            '<label class="radio-inline">',
                                '<input type="radio" name="sparklineTypeOption" value="bar"> Bar',
                            '</label>',
                             '<label class="radio-inline">',
                                '<input type="radio" name="sparklineTypeOption" value="pie"> Pie',
                            '</label>',
                        '</div>',
                    '</div>'
                ].join("\n"));
            }
            return this.formContainer;
        },
        
        

        render: function() {
            if ( this.container ) {
                this.container.append(this.getFormContainer());
            }

            return this.getFormContainer();
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);