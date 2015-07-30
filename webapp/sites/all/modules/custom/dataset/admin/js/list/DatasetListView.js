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
        throw new Error('DatasetListView requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('DatasetListView requires GD');
    }

    var GD = global.GD;

    var DatasetListView = GD.View.extend({
        section: null,

        init: function ( object, container, options ) {
            this._super(object, container, options);

            if (options.section) {
                this.section = options.section;
            }
        },

        initLayout: function () {
            if ( this.layout == null ) {
                this.layoutHeader = $('<div id="gd-view-dataset-list-header">');

                this.layoutBody = $('<div class="col-md-12" id="gd-view-dataset-list-body">');
                var body_wrap = $('<div class="row">').append(this.layoutBody);

                this.layoutFooter = $('<div class="col-md-12" id="gd-view-dataset-list-footer">');
                var footer_wrap = $('<div class="row">').append(this.layoutFooter);

                this.layout = $('<div id="gd-view-dataset-list">').append(this.layoutHeader,body_wrap,footer_wrap);

                this.container.append(this.layout);
            }
        },

        loadData: function ( data ) {
            this.object = data;

            var tableData = [];
            var datesModified = [];
            var authors = [];
            $.each(this.object, function (i, d) {
                var row = [];
                var link = null;
                if ( d['datasourceName'] == 'script:python' ) {
                    link = '<a>'+d['publicName']+'</a>';
                } else {
                    link = '<a href="/cp/dataset/'+d['name']+'">'+d['publicName']+'</a>';
                }

                if (d['description']) {
                    row.push(link + '<br/><small>' + d['description'] + '</small>');
                } else {
                    row.push(link);
                }

                if (d['author']) {
                    var name = '';
                    if (d['author']['name']) {
                        name = d['author']['name'];
                    }

                    var email = '';
                    if (d['author']['email']) {
                        email = d['author']['email'];
                    } else {
                        email = "Removed User";
                    }
                    row.push(name + ' (<small>'+email+'</small>)');
                    authors.push(d['author']);
                } else {
                    row.push('&nbsp;');
                }

                if (d['changed']) {
                    row.push(GD.Util.DateFormat.getUSShortDateTime(GD.Util.DateFormat.parseISO8601(d['changed'])));
                    datesModified.push(d['changed']);
                } else {
                    row.push('&nbsp;');
                }

                tableData.push(row);
            });

            if ( tableData.length == 0 ) {
                this.table.dataTable().fnSettings().oLanguage.sEmptyTable = 'No datasets available';
                this.table.dataTable().fnDraw();
            } else {
                this.table.dataTable().fnClearTable();
                this.table.dataTable().fnAddData(tableData);
                this.table.dataTable().fnSort([[2, "desc" ]]);
                $('#datasetList_info').show();
            }

            this.table.find('th').attr('tabindex', 100);
            this.layoutBody.find('a').attr('tabindex', 100);
            this.layoutBody.find('li.paginate_button').attr('tabindex', 100);
            this.layoutBody.find('select').attr('tabindex', 100);
            this.layoutBody.find('input').attr('tabindex', 100);

            $('#datasetList_filter input').attr('id', 'datasetList_search');
            $('#datasetList_filter label').attr('for', 'datasetList_search');

            if ( datesModified.length != 0) {
                this.table.dataTable().fnSetColumnVis(2, true);
            }

            if ( authors.length != 0 ) {
                this.table.dataTable().fnSetColumnVis(1, true);
            }
        },

        render: function () {

            this.initLayout();

            // add new dataset button
            var activeDatasource = GovdashAdmin.getActiveDatasource();
            if (activeDatasource) {
                if ( !activeDatasource.isReadOnly() ) {
                    var actions = $('<div class="row gd-dataset-actions"><div class="col-md-12"><a class="btn btn-success pull-right" href="/cp/dataset/new/file?ds='+activeDatasource.getName()+'">New Dataset</a></div></div>');
                    //this.layoutBody.append(actions);
                    this.section.layoutHeader.find('.gd-section-header-right').append(actions);
                }
            } else {
                var messaging = this.section.messaging;
                messaging.addErrors('No active datasource set.');
                messaging.displayMessages();
            }

            var table = $('<table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered" id="datasetList"></table>');
            table.append('<thead><tr><th>Name</th><th>Author</th><th>Last Modified</th></tr></thead>');
            table.append('<tbody></tbody>');

            this.layoutBody.append(table);

            table.dataTable({
                "bPaginate": true,
                "bLengthChange": true,
                "bFilter": true,
                "bSort": true,
                "bInfo": true,
                "bProcessing": true,
                "bAutoWidth": false,
                "language": {
                    "emptyTable": "Loading..."
                },
                "aoColumns": [
                    { "sWidth": "50%" },
                    { "sWidth": "25%", "sClass": "left" },
                    { "sWidth": "25%", "sClass": "right"}
                ],
                "aaSorting": [[ 2, "desc" ]]
            });




            this.table = table;
        }
    });

    // add to global space
    global.GD.DatasetListView = DatasetListView;

})(typeof window === 'undefined' ? this : window, jQuery);