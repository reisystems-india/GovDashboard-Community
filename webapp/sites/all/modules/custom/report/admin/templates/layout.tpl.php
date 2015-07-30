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
            <div class="report-header">
                <h2>Report <?php print ($update ? 'Update' : 'Create'); ?></h2>
            </div>
            <div class="row" id="gd-admin-messages"></div>
            <div class="row">
                <div class="col-md-6">
                    <input id="reportName" tabindex="3000" class="form-control" type="text" name="name" placeholder="Enter a report title"/>
                </div>
                <div class="col-md-6">
                    <div class="clearfix pull-right">
                        <button tabindex="3000" id="reportSaveTop" type="button" data-loading-text="Saving..." class="rpt-act-btn btn bldr-btn-success pull-left">Save</button>
                        <?php
                        print ($update ? '<button tabindex="3000" id="reportSaveAsTop" type="button" class="rpt-act-btn btn bldr-btn-success pull-left">Save As</button>
                        <button tabindex="3000" id="reportDeleteTop" type="button" class="rpt-act-btn btn bldr-btn pull-left">Delete</button>' : '');
                        ?>
                        <button tabindex="3000" id="reportCancelTop" type="button" class="rpt-act-btn btn bldr-btn pull-left">Cancel</button>
                    </div>
                </div>
            </div>
            <div id="reportButtons" class="row">
                <div class="col-md-6">
                    <div class="report-config">
                        <button id="reportDataButton" tabindex="3000" type="button" class="rpt-cnf-btn btn bldr-btn"><span class="fa fa-database"></span> Data <span class="caret"></span></button>
                        <div id="reportDataForm" class="rpt-frm bldr-absolute"></div>
                    </div>
                    <div class="report-config">
                        <button id="reportColumnButton" tabindex="3000" type="button" class="rpt-cnf-btn btn bldr-btn"><span class="fa fa-th-list"></span> Columns <span class="caret"></span></button>
                        <div id="reportColumnForm" class="rpt-frm bldr-absolute"></div>
                    </div>
                    <div class="report-config">
                        <button id="reportFilterButton" tabindex="3000" type="button" class="rpt-cnf-btn btn bldr-btn"><span class="fa fa-filter"></span> Filters <span class="caret"></span></button>
                        <div id="reportFilterForm" class="rpt-frm bldr-absolute"></div>
                    </div>
                    <div class="report-config">
                        <button id="reportConfigureButton" tabindex="3000" type="button" class="rpt-cnf-btn btn bldr-btn"><span class="fa fa-cog"></span> Configure <span class="caret"></span></button>
                        <div id="reportConfigureForm" class="rpt-frm bldr-absolute"></div>
                    </div>
                    <div class="report-config">
                        <button id="reportVisualizeButton" tabindex="3000" type="button" class="rpt-cnf-btn btn bldr-btn"><span class="fa fa-eye"></span> Visualize <span class="caret"></span></button>
                        <div id="reportVisualizeForm" class="rpt-frm bldr-absolute"></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div id="reportTypeToolbarContainer"></div>
                </div>
            </div>
            <div class="row">
				<div id="reportCustomConfig">
                </div>
                <div id="reportEditorContainer" class="col-md-10 col-md-offset-1">
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
        <div class="clearfix pull-right">
            <button tabindex="3000" id="reportSaveBottom" type="button" data-loading-text="Saving..." class="rpt-act-btn btn bldr-btn-success pull-left">Save</button>
            <?php
            print ($update ? '<button tabindex="3000" id="reportSaveAsBottom" type="button" class="rpt-act-btn btn bldr-btn-success pull-left">Save As</button>
                        <button tabindex="3000" id="reportDeleteBottom" type="button" class="rpt-act-btn btn bldr-btn pull-left">Delete</button>' : '');
            ?>
            <button tabindex="3000" id="reportCancelBottom" type="button" class="rpt-act-btn btn bldr-btn pull-left">Cancel</button>
        </div>
    </div>
<script type="text/javascript">
    (function(global) {
        (function($,Highcharts) {
            global.GovdashReportBuilder = new GD.ReportBuilder(
                <?php echo json_encode($report); ?>,
                {<?php echo gd_admin_get_init(); ?>
            });
            global.GovdashReportBuilder.getReport().setDataset(new GD.ReportDataset(<?php echo json_encode($reportDataset); ?>));
            global.GovdashReportBuilder.run();
        })(typeof global.GD_jQuery != "undefined" ? global.GD_jQuery : jQuery, typeof global.GD_Highcharts != "undefined" ? global.GD_Highcharts : (typeof Highcharts != "undefined" ? Highcharts : undefined));
    })(typeof window === "undefined" ? this : window);
</script>