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
        throw new Error('ReportColumnOrderForm requires jQuery');
    }

    if (typeof global.GD === 'undefined') {
        throw new Error('ReportColumnOrderForm requires GD');
    }

    var GD = global.GD;

    global.GD.ReportColumnOrderForm = GD.View.extend({

        init: function (object, container, options) {
            this._super(object, container, options);
        },

        getController: function () {
            return this.options.builder;
        },

        update: function() {
            this.getColumnList().empty();
            this.loadColumns();
        },

        getFormContainer: function () {
            if (!this.formContainer) {
                this.formContainer = $([
                    '<div>',
                    '<p>Drag and drop columns to change display order.</p>',
                    '<div class="list-group" id="reportConfigColumnOrderList"></div>',
                    '<div class="form-group">',
                    '<button class="btn btn-primary btn-sm pull-right" type="button">Apply</button>',
                    '</div>',
                    '</div>'
                ].join("\n"));

                this.getSaveButton();
            }
            return this.formContainer;
        },

        getColumnList: function () {
            return this.getFormContainer().find('.list-group');
        },

        loadColumns: function () {
            var reportObj = this.getController().getReport(),
                order = reportObj.getColumnOrder(),
                columns = reportObj.getColumns();

            // ADDING COLUMNS TO COLUMN ORDER WHEN COLUMNS ARE CHANGED (ADDED AND REMOVED)
            if (order.length < 1) {
                order = reportObj.getColumns();
            } else {
                var loopArray = (order.length > columns.length) ? order : columns;
                for (i in loopArray) {
                    var present = columns.indexOf(order[i]);
                    if (present === -1) {
                        order.splice(i, 1);
                    }
                    if (columns[i] && order.indexOf(columns[i]) === -1) {
                        order.push(columns[i]);
                    }
                }
                this.getController().getReport().setColumnOrder(order);
            }

            for (var i = 0, count = order.length; i < count; i++) {
                var column = reportObj.getColumn(order[i]);
                if (column) {
                    this.getColumnList().append('<a href="javascript:void(0)" class="list-group-item" data-id="' + column.id + '"><span class="glyphicon glyphicon-move"></span>' + column.name + '</a>');
                }else{
                    this.getColumnList().append('<a href="javascript:void(0)" class="list-group-item" data-id="' + order[i] + '"><span class="glyphicon glyphicon-move"></span>' + "Unsupported column:" + order[i] + '</a>');
                }

            }
        },

        getSaveButton: function () {
            if (!this.saveButton) {
                this.saveButton = this.getFormContainer().find('button');
                var _this = this;
                this.saveButton.on('click', function () {

                    // get columnOrder
                    var columnOrder = [];
                    $.each(_this.getColumnList().children(), function () {
                        columnOrder.push($(this).data('id'));
                    });

                    // save to report
                    _this.getController().getReport().setColumnOrder(columnOrder);

                    // the canvas should be listening to this event, and refresh
                    $(document).trigger({
                        type: 'changed.column.order'
                    });
                });
            }
            return this.saveButton;
        },

        render: function () {
            if (this.container) {
                this.container.append(this.getFormContainer());

                this.loadColumns();

                this.getColumnList().sortable({containment: "parent"});
            }


            return this.getFormContainer();
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);