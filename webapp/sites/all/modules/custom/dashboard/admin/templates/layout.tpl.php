<?php
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
?>
    <div class="row" id="gd-admin-header"></div>
    <div class="row">
        <div class="col-md-12" id="gd-admin-body">
            <div class="dashboard-header">
                <h2>Dashboard <?php print ($update ? 'Update' : 'Create'); ?></h2>
            </div>
            <div class="row" id="gd-admin-messages"></div>
            <div class="row">
                <div class="col-md-6">
                    <input id="dashboardName" class="form-control pull-left" type="text" name="name" placeholder="Enter a dashboard title" />
                    <?php print ($update ? '<a id="viewDashboardLink" href="/dashboards?id='.$dashboard->id.'" target="_blank">View</a>' : ''); ?>
                </div>
                <div class="col-md-6">
                    <button id="dashboardCancelTop" type="button" class="dsb-act-btn btn btn-default pull-right">Cancel</button>
                    <?php
                    print ($update ? '<button id="dashboardDeleteTop" type="button" class="dsb-act-btn btn btn-default pull-right">Delete</button><button id="dashboardSaveAsTop" type="button" class="dsb-act-btn btn btn-success pull-right">Save As</button>' : '');
                    ?>
                    <button id="dashboardSaveTop" type="button" data-loading-text="Saving..." class="dsb-act-btn btn btn-success pull-right">Save</button>
                </div>                
            </div>
            <div id="dashboardButtons" class="row">
                <div class="col-md-6">
                    <div id="dashboardConfigButtons">
                    <button id="dashboardReports" type="button" class="dsb-cnf-btn btn btn-default"><span class="fa fa-edit"></span> Reports <span class="caret"></span></button>
                    <button id="dashboardFilters" type="button" class="dsb-cnf-btn btn btn-default"><span class="fa fa-filter"></span> Filter <span class="caret"></span></button>
                    <button id="dashboardLink" type="button" class="dsb-cnf-btn btn btn-default"><span class="fa fa-link"></span> Link <span class="caret"></span></button>
                    <button id="dashboardDisplay" type="button" class="dsb-cnf-btn btn btn-default"><span class="fa fa-film"></span> Display <span class="caret"></span></button>
                        <div id="dashboardForms">
                            <div id="dashboardReportsForm" class="dsb-frm bldr-absolute"></div>
                            <div id="dashboardFiltersForm" class="dsb-frm bldr-absolute"></div>
                            <div id="dashboardLinkForm" class="dsb-frm bldr-absolute"></div>
                            <div id="dashboardDisplayForm" class="dsb-frm bldr-absolute"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">

                </div>
            </div>
            <div class="row">
                <div id="dashboardEditorContainer" class="col-md-10 col-md-offset-1">
                </div>
            </div>
            <div>
                <div class="govdb-cnvs-cntr">
                    <div class="govdb-cnvs">
                        <div class="govdb-grd"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row" id="gd-admin-footer"></div>
    <div class="bottomButtons">
        <button id="dashboardCancelBottom" type="button" class="dsb-act-btn btn btn-default pull-right">Cancel</button>
        <?php
        print ($update ? '<button id="dashboardDeleteBottom" type="button" class="dsb-act-btn btn btn-default pull-right">Delete</button><button id="dashboardSaveAsBottom" type="button" class="dsb-act-btn btn btn-success pull-right">Save As</button>' : '');
        ?>
        <button id="dashboardSaveBottom" type="button" data-loading-text="Saving..." class="dsb-act-btn btn btn-success pull-right">Save</button>
    </div>
</div>
<script type="text/javascript">
    <!--//--><![CDATA[//><!--
    (function(global) {
        (function($,Highcharts) {
            GD.DashboardBuilder.config = <?php echo json_encode($config); ?>;
            global.GovdashDashboardBuilder = new GD.DashboardBuilder(
                <?php echo json_encode($dashboard); ?>,
                {<?php echo gd_admin_get_init(); ?>
            });
            global.GovdashDashboardBuilder.run();
        })(typeof global.GD_jQuery != "undefined" ? global.GD_jQuery : jQuery, typeof global.GD_Highcharts != "undefined" ? global.GD_Highcharts : (typeof Highcharts != "undefined" ? Highcharts : undefined));
    })(typeof window === "undefined" ? this : window);
    //--><!]]>
</script>