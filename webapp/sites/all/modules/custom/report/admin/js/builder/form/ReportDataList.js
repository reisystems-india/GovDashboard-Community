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
        throw new Error('ReportDataList requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportDataList requires GD');
    }

    var GD = global.GD;

    global.GD.ReportDataList = GD.View.extend({

        init: function(object, container, options) {
            this._super(object, container, options);
        },

        getController: function () {
            return this.options.builder;
        },

        getView: function() {
            var reportObj = this.getController().getReport();
            if ( !this.view ) {
                var view = [
                    '<div>',
                    '  <strong>Dataset: </strong>',
                    '  <a tabindex="3000" href="/cp/dataset/'+reportObj.getDatasetName()+'" title="Go to Dataset">'+reportObj.getDatasetPublicName()+'</a>',
                    '<div>'
                ];
                this.view = $(view.join("\n"));
            }
            return this.view;
        },

        render: function() {
            if ( this.container ) {
                this.container.append(this.getView());
            }
            return this.getView();
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);