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

(function(global, $, undefined) {

    if (typeof $ === 'undefined') {
        throw new Error('ReportSortForm requires jQuery');
    }

    if (typeof global.GD === 'undefined') {
        throw new Error('ReportSortForm requires GD');
    }

    var GD = global.GD;

    global.GD.ReportSortForm = GD.View.extend({
        columnTree: null,
        tdLookup: {},

        getController: function() {
            return this.options.builder;
        },

        getLabel: function() {
            if (!this.label) {
                this.label = $('<label>Sortable Columns:</label>');
            }
            return this.label;
        },

        update: function() {
        },

        getOrderListForm: function() {
            if (!this.orderListForm) {
                this.orderListForm = $('<table id="columnlist" class="table table-condensed"><thead><tr><th>Column Name</th><th></th></tr></thead> </table>');
            }
            return this.orderListForm;
        },

        getApplyButton: function() {
            if (!this.applyButton) {
                this.applyButton = $('<button class="btn btn-primary btn-sm pull-right" type="button" style="margin-top: 10px;">Add Sort</button>');
                var _this = this;
                this.applyButton.on('click', function(e) {
                    _this.addSort();
                });
            }
            return this.applyButton;
        },

        addSort: function() {
            var selected = this.getColumnTree().getSelected();
            if (selected) {
                var columnID = selected['id'];
                this.removeValidationMessage();
                var row = $([
                    '<tr>',
                        '<td class="datacol">',
                            '<span class="arrownav glyphicon glyphicon-arrow-up" style="cursor:pointer; margin-right: 10px;"></span>',
                            selected['name'],
                        '</td>',
                        '<td>',
                            '<span class="glyphicon glyphicon-trash deleteicon" style="cursor:pointer"></span>',
                        '</td>',
                    '</tr>'
                ].join("\n"));
                row.find('td.datacol').uniqueId();
                this.tdLookup[columnID] = row.find('td.datacol').attr('id');

                var _this = this;
                row.find('.arrownav').click(function() {
                    if ($(this).hasClass('glyphicon-arrow-up')) {
                        $(this).removeClass('glyphicon-arrow-up').addClass('glyphicon-arrow-down');
                        $(this).parent().data('order', 'desc');
                        _this.getController().getReport().editOrderBy({'id': id, order: 'desc'});
                    } else {
                        $(this).removeClass('glyphicon-arrow-down').addClass('glyphicon-arrow-up');
                        $(this).parent().data('order', 'asc');
                        _this.getController().getReport().editOrderBy({'id': id, order: 'asc'});
                    }
                    $(document).trigger({
                        type: 'changed.query.sort'
                    });
                });

                row.find('.deleteicon').click(function() {
                    _this.removeSort(columnID);
                });

                $('#columnlist').append(row);

                var id = this.getController().getReport().addOrderBy({'column': selected['id'], 'order': 'asc'});
                this.getColumnTree().deselectNode(columnID);
                this.getColumnTree().disableNode(columnID);
                $(document).trigger({
                    type: 'changed.query.sort'
                });
            } else {
                this.showValidationMessage("Select a column");
            }
        },

        removeSort: function(column) {
            this.removingColumn = column;
            var _this = this;
            this.getModal(function(){
                $('#' + _this.tdLookup[_this.removingColumn]).parent().remove();
                _this.getController().getReport().removeOrderBy({'column': _this.removingColumn});
                _this.getColumnTree().enableNode(_this.removingColumn);
                delete _this.tdLookup[_this.removingColumn];
                _this.removingColumn = null;
                $(document).trigger({
                    type: 'changed.query.sort'
                });
            }).modal();
        },

        showValidationMessage: function(msg){
            $("#columnlistcontainer").after('<div class="help-block"></div>');
            $('.bldr-ctrl-frm-val').addClass('has-error').find('.help-block').html(msg);
        },

        removeValidationMessage: function(){
            $('.bldr-ctrl-frm-val').removeClass('has-error').find('.help-block').remove();
        },

        getColumnTree: function() {
            if (!this.columnTree) {
                this.columnTree = new GD.ReportSortColumnTree(null, this.getFormContainer(), { 'controller': this.getController(), 'multiple': false });
            }

            return this.columnTree;
        },

        getFormContainer: function() {
            if (!this.formContainer) {
                this.formContainer = $('<div></div>');
            }

            return this.formContainer;
        },

        getModal: function ( callback ) {
            if ( !this.modal ) {
                this.modal = $([
                    '<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">',
                        '<div class="modal-dialog">',
                            '<div class="modal-content">',
                                '<div class="modal-header">',
                                    '<h4>Delete sort column</h4>',
                                '</div>',
                                '<div class="modal-body">',
                                    '<div class="row">',
                                        '<div class="col-sm-1"><span style="font-size:25px;" class="glyphicon glyphicon-info-sign"></span></div>',
                                        '<div style="height:25px; line-height: 25px;" class="col-sm-11">Are you sure you want to delete this column\'s Sorting?</div>',
                                    '</div>',
                                '</div>',
                                '<div class="modal-footer">',
                                    '<button type="button" class="btn btn-danger" data-dismiss="modal">Delete</button>',
                                    '<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>',
                                '</div>',
                            '</div>',
                        '</div>',
                    '</div>'
                ].join("\n"));

                this.modal.find('.btn-danger').on('click',function() {
                    callback();
                });

                $('body').append(this.modal);
            }

            return this.modal;
        },

        getForm: function() {
            var _this = this,
                SortedColumnlist = this.getController().getReport().getOrderBys(),
                lookupColumnList = this.getController().getReport().getDataset().getColumnLookup();
            _this.getOrderListForm().empty();

            var mainContainer = $('<table id="columnlist" class="table table-condensed"></table>'),
                labelname = $('<tr><th>Column Name</th><th></th></tr>'),
                columniconlist = [],
                tdcolumnlist = [],

                arrownav,
                columnnamelist,
                deleteicon,
                counter = 0;

            function addItem(column, unsupported) {
                columniconlist[counter] = $('<tr></tr>');
                tdcolumnlist[counter] = $('<td class="datacol" data-column="' + column.id + '" data-order="' + SortedColumnlist[i].order + '" ></td>');
                tdcolumnlist[counter].uniqueId();
                _this.tdLookup[column.id] = tdcolumnlist[counter].attr('id');

                if (SortedColumnlist[i].order == 'asc') {
                    arrownav = $('<span class="glyphicon glyphicon-arrow-up" style="cursor:pointer; margin-right: 10px;"></span>');
                } else {
                    arrownav = $('<span class="glyphicon glyphicon-arrow-down" style="cursor:pointer; margin-right: 10px;"></span>');
                }

                columnnamelist = column.name;
                deleteicon = $('<td><span class="glyphicon glyphicon-trash" style="cursor:pointer"></span></td>');
                tdcolumnlist[counter].append(arrownav, columnnamelist);
                columniconlist[counter].append(tdcolumnlist[counter], deleteicon);
                if(!unsupported){
                    arrownav.click(function() {
                        if ($(this).hasClass('glyphicon-arrow-up')) {
                            $(this).removeClass('glyphicon-arrow-up').addClass('glyphicon-arrow-down');
                            $(this).parent().data('order', 'desc');
                            _this.getController().getReport().editOrderBy({'column': column.id, order: 'desc'});
                        } else {
                            $(this).removeClass('glyphicon-arrow-down').addClass('glyphicon-arrow-up');
                            $(this).parent().data('order', 'asc');
                            _this.getController().getReport().editOrderBy({'column': column.id, order: 'asc'});
                        }
                        $(document).trigger({
                            type: 'changed.query.sort'
                        });
                    });
                }
                deleteicon.click(function() {
                    _this.removeSort(column.id);
                });
                _this.getColumnTree().deselectNode(column.id);
                _this.getColumnTree().disableNode(column.id);
                counter++;
            }
                
            for (var i = 0; i < SortedColumnlist.length; i++) {
                var column = SortedColumnlist[i].column,
                    formula = this.getController().getReport().getFormula(SortedColumnlist[i].column);
                if (column in lookupColumnList) {
                    addItem(lookupColumnList[column]);
                } else if (formula) {
                    addItem(formula);
                } else{
                    addItem({
                        id:column,
                        name:"UnsupportedColumn:" + column
                    }, true);
                }
            }

            mainContainer.append(labelname, columniconlist);

            return mainContainer;
        },

        render: function() {
            if (this.container) {
                this.container.append(this.getForm(), this.getLabel(), this.getFormContainer(), this.getApplyButton());
                this.getColumnTree().render();
            }
        }
    });

})(typeof window === 'undefined' ? this : window, jQuery);