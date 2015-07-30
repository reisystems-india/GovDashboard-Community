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
        throw new Error('ReportListView requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('ReportListView requires GD');
    }

    var GD = global.GD;

    var ReportListView = GD.View.extend({

        section: null,

        init: function ( object, container, options ) {
            this._super(object, container, options);

            if (options && options.section) {
                this.section = options.section;
            }
        },

        initLayout: function () {
            if ( this.layout == null ) {
                this.layoutHeader = $('<div id="gd-view-report-list-header"></div>');

                this.layoutBody = $('<div class="col-md-12" id="gd-view-report-list-body"></div>');
                var body_wrap = $('<div class="row"></div>').append(this.layoutBody);

                this.layoutFooter = $('<div class="col-md-12" id="gd-view-report-list-footer"></div>');
                var datasource = GovdashAdmin.getActiveDatasource();
                if (datasource['draft']) {
                    this.layoutFooter.append('<span style="font-style: italic; font-size: 11px;">These visualizations have NOT been approved for use and are in draft form only.</span>');
                }

                var footer_wrap = $('<div class="row"></div>').append(this.layoutFooter);

                this.layout = $('<div id="gd-view-report-list"></div>').append(this.layoutHeader,body_wrap,footer_wrap);

                this.container.append(this.layout);
            }
        },

        loadData: function ( data ) {
            this.object = data;

            var parseDatasets = function ( datasets ) {
                var o = [];
                for ( var i = 0, count = datasets.length; i < count; i++ ) {
                    o.push('<a tabindex="100" href="/cp/dataset/'+datasets[i].name+'">'+datasets[i].publicName+'</a>');
                }
                return o.join(', ');
            };

            var parseDashboards = function ( dashboards ) {
                var o = [];
                for ( var i = 0, count = dashboards.length; i < count; i++ ) {
                    o.push('<a tabindex="100" href="/cp/dashboard/'+dashboards[i].id+'">'+dashboards[i].name+'</a>');
                }
                return o.join(', ');
            };

            var tableData = [];
            $.each(this.object, function (i, r) {
                var row = [];

                var name = '';
                var email = '';
                if (r['author']) {
                    if (r['author']['name']) {
                        name = r['author']['name'];
                    }

                    if (r['author']['email']) {
                        email = r['author']['email'];
                    } else {
                        email = "Removed User";
                    }
                }

                if ( !r['title'] ) {
                    r['title'] = 'missing name';
                }

                row.push('<a tabindex="100" href="/cp/report/'+r['id']+'">'+r['title']+'</a>', name + ' (<small>'+email+'</small>)', GD.Util.DateFormat.getUSShortDateTime(GD.Util.DateFormat.parseISO8601(r['changed'])),parseDatasets(r['datasets']),parseDashboards(r['dashboards']));
                tableData.push(row);
            });

            if ( tableData.length == 0 ) {
                this.table.dataTable().fnSettings().oLanguage.sEmptyTable = 'No reports available';
                this.table.dataTable().fnDraw();
            } else {
                this.table.dataTable().fnClearTable();
                this.table.dataTable().fnAddData(tableData);
                this.table.dataTable().fnSort([[ 2, "desc" ]]);
                $('#reportList_info').show();
            }

            this.table.find('th').attr('tabindex', 100);
            this.layoutBody.find('a').attr('tabindex', 100);
            this.layoutBody.find('li.paginate_button').attr('tabindex', 100);
            this.layoutBody.find('select').attr('tabindex', 100);
            this.layoutBody.find('input').attr('tabindex', 100);

            $('#reportList_filter input').attr('id', 'reportList_search');
            $('#reportList_filter label').attr('for', 'reportList_search');
        },

        render: function () {

            this.initLayout();
            var datasource = GovdashAdmin.getActiveDatasource();
            var actions = $('<div class="row gd-report-actions"><div class="col-md-12 text-right"><a tabindex="100" class="btn bldr-btn-success" href="/cp/report/create?ds='+GovdashAdmin.getActiveDatasource().getName()+'">New Report</a></div></div>');
            this.section.layoutHeader.find('.gd-section-header-right').append(actions);

            var table = $('<table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered" id="reportList"></table>');
            table.append('<thead><tr><th>Name</th><th>Author</th><th>Last Modified</th><th>Data</th><th>Used in Dashboards</th></tr></thead>');
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
                    { "sWidth": "30%" },
                    { "sWidth": "15%", "sClass": "left" },
                    { "sWidth": "15%", "sClass": "right"},
                    { "sWidth": "20%", "sClass": "left" },
                    { "sWidth": "20%", "sClass": "left"}
                ],
                "aaSorting": [[ 2, "desc" ]]
            });

            this.table = table;
        }
    });

    // add to global space
    global.GD.ReportListView = ReportListView;

})(typeof window === 'undefined' ? this : window, jQuery);