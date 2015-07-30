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


class GD_WidgetTable extends GD_Widget {

    public function getView ( array $options = array() ) {

        $scrollHeight = 400;
        $heightAdj = 0;
        if ( $this->ReportConfig->showTitle() || $this->ReportConfig->showMenu() ) {
            $heightAdj = (GD_REPORT_TITLE_HEIGHT);
        }

        $reportSize = $this->ReportConfig->getDisplaySize();
        if ( !empty($reportSize) ) {
            $scrollHeight = $reportSize->height;
        }

        if ( isset($_REQUEST['h']) && isset($_REQUEST['w']) ) {
            $scrollHeight = $_REQUEST['h'];
        }

        $scrollHeight -= $heightAdj;
        $viewStart = '<div class="report" id="report-'.intval($this->ReportConfig->getId()).'" style="height:' . $scrollHeight . 'px;position:relative;">';
        $viewEnd = '</div>';
        $grabber = '<div id="report-'.intval($this->ReportConfig->getId()).'grabber" class="dashboardBuilderCanvasMover"></div>';

        $attributes = array();
        $attributes['class']['data'] = 'GD_datatable';
        $attributes['id']['data'] = 'dataTable_' . intval($this->ReportConfig->getId());

        $tableData = array('data' => null, 'fields' => gd_report_get_table_fields($this->ReportConfig));
        $tableView = new GD_TableView($tableData, $attributes);

        $admin = !empty($options['admin']) ? $options['admin'] : FALSE;
        $external = !empty($options['external']) ? $options['external'] : FALSE;
        $public = !empty($options['public']) ? $options['public'] : FALSE;

        $scrollHeight -= 33;

        return
            $viewStart
            .$grabber
            .$tableView->getHtml()
            .$viewEnd
            .$this->getDataTableScript(
                'dataTable_' . intval($this->ReportConfig->getId()),
                $scrollHeight,
                $this->getSummary(),
                $admin,
                $public,
                $external,
                $this->getInitialSorting($tableData['fields'])
            );
    }
    protected function getSummary() {
        return $this->getGenericSummary();
    }

    /**
     * TODO: look how many urls we have.  this is no fun...
     *
     * @param $admin
     * @param $public
     * @param $external
     * @return null|string
     */
    protected function getCallbackUrl( $admin, $public, $external ) {
        $url = null;

        if ( $external ) {
            $url = GOVDASH_HOST .'/gd/ext/dashboard/' . $this->ReportConfig->dashboard . '/data';
        } else if ( $public ) {
            $url = '/public/dashboard/'.$this->ReportConfig->dashboard.'/data';
        } else {

            if ( !isset($this->ReportConfig->dashboard) ) {
                $url = '/api/report/data.json';
            } else {
                if ( $admin ) {
                    $url = '/api/dashboard/data.json';
                } else {
                    $url = '/api/dashboard/'.$this->ReportConfig->dashboard.'/data.json';
                }
            }
        }

        return $url;
    }

    protected function getInitialSorting($tableFields) {
        if ( count($this->ReportConfig->orderBy) <= 0 ) {
            return null;
        }

        $columnIndex = array();
        $i = 0;
        foreach ($tableFields as $field) {
            $columnIndex[$field['name']] = $i;
            ++$i;
        }

        $sort = array();
        foreach ($this->ReportConfig->orderBy as $orderBy) {
            if (array_key_exists($orderBy['column'], $columnIndex)) {
                $s = '[';
                $s .= $columnIndex[$orderBy['column']];
                $s .= ', ';
                $s .= "'" . $orderBy['order'] . "'";
                $s .= ']';
                $sort[] = $s;
            }
        }
        return '[' . (count($sort) > 0 ? implode($sort, ',') : '') . ']';
    }

    protected function getDataTableScript( $tableId, $scrollHeight, $tableSummary = '', $admin = TRUE, $public = FALSE, $external = FALSE, $sorting = null ) {
        $datasourceName = $this->ReportConfig->getNode() != null ? get_node_field_value($this->ReportConfig->getNode(), 'field_report_datasource') : null;
        if (!isset($datasourceName)) {
            $datasourceName = gd_datasource_get_active();
        }
        //  We can't use repeating background images because IE 9 does not reliably print them
        $overlay = gd_datasource_is_property($datasourceName, 'draft') ?
            '$("#' . $tableId . '").dataTable().fnSettings().aoDrawCallback.push({
                "fn": function () {
                    if ($("#' . $tableId . '").width() > $("#' . $tableId . '_wrapper > .table-content").width()) {
                        $("#report-'.intval($this->ReportConfig->getId()).'-overlay").height($("#' . $tableId . '_wrapper > .table-content").height() - 14);
                    }
                    if ($("#' . $tableId . '").height() > $("#' . $tableId . '_wrapper > .table-content").height()) {
                        $("#report-'.intval($this->ReportConfig->getId()).'-overlay").width($("#' . $tableId . '_wrapper > .table-content").width() - 14);
                    }

                    //  Table is taller than the actual image forcing us to manually repeat the image
                    if ($("#' . $tableId . '_wrapper > .table-content").height() > 1008) {
                        var diff = $("#' . $tableId . '").height() - 1008;
                        var num = Math.ceil(diff/1008);
                        for (var i = 1; i <= num; i++) {
                            $("#' . $tableId . '_wrapper").append($(\'<img alt="Draft Overlay" title="Draft Overlay" aria-label="Draft Overlay" class="report-draft-overlay" src="/sites/all/modules/custom/report/includes/images/draft.png"/>\').css("top", (1008 * i) + "px"));
                        }
                    } else if ($("#' . $tableId . '_wrapper > .table-content").width() > 1080) {
                        var diff = $("#' . $tableId . '").width() - 1008;
                        var num = Math.ceil(diff/1008);
                        for (var i = 1; i <= num; i++) {
                            $("#' . $tableId . '_wrapper").append($(\'<img alt="Draft Overlay" title="Draft Overlay" aria-label="Draft Overlay" class="report-draft-overlay" src="/sites/all/modules/custom/report/includes/images/draft.png"/>\').css("left", (1008 * i) + "px"));
                        }
                    }
                }
           });
           $("#' . $tableId . '_wrapper").append(\'<img alt="Draft Overlay" title="Draft Overlay" aria-label="Draft Overlay" id="report-'.intval($this->ReportConfig->getId()).'-overlay" class="report-draft-overlay" src="/sites/all/modules/custom/report/includes/images/draft.png"/>\');
           $("#report-'.intval($this->ReportConfig->getId()).'-overlay").bind("mouseenter mouseleave click dblclick focusout hover mousedown mousemove mouseout mouseover mouseup", function (e) {
                if (navigator.appName == "Microsoft Internet Explorer") {
                    jQuery("#report-'.intval($this->ReportConfig->getId()).'-overlay").hide();
                    var target = document.elementFromPoint(e.clientX, e.clientY);
                    if (jQuery(target).is("a") && jQuery(target).attr("href") != "#" && e.type == "click") {
                        window.location = jQuery(target).attr("href");
                    }
                    jQuery(target).trigger(e.type);
                    jQuery("#report-'.intval($this->ReportConfig->getId()).'-overlay").show();
                }
           });' : '';

        /*
         * TODO Move to actual JS file
         * For right now this has to kept in PHP due to how report render is done.
         * The JS below needs report configuration data (tableID, scrollHeight, etc.) for the table;
         *  which means that to move this to a separate JS file, we need to send back
         *  the raw table HTML + the configuration information that the JS needs, which
         *  isn't done right now.
         */
        $dataTableScript =
            '<script type="text/javascript">
                (function (global) {
                !function($,undefined) {
                    var func = function() {
                        $("#report-' . intval($this->ReportConfig->getId()) . '").on("ready.report.render", function() {
                            if ($.fn.DataTable.isDataTable("#' . $tableId . '"))' . (!$admin ? ('return;') : '$("#' . $tableId . '").dataTable().fnDestroy();') . '
                            var ROWS_PER_PAGE = ' . (GD_TABLE_ROWS_PER_PAGE) . ';

                            var dashboardId = $("#dash_viewer").attr("dashboard_id");
                            //$.fn.dataTableExt.sErrMode = "throw";
                            var sortable = [];
                            var sortableColumns = [];

                            $("#' . $tableId . '").find("th").each(function () {
                                if ( $(this).attr("column-name").indexOf("measure") != -1 ) {
                                    sortableColumns[$(this).attr("column-index")] = true;
                                } else if ( $(this).attr("column-name").indexOf("column") != -1 ) {
                                    sortableColumns[$(this).attr("column-index")] = false;
                                } else {
                                    sortableColumns[$(this).attr("column-index")] = null;
                                }
                            });

                            var disableMeasures = $.inArray(true, sortableColumns) != -1 && $.inArray(false, sortableColumns) != -1;
                            if ( disableMeasures ) {
                                $.each(sortableColumns, function (i, v) {
                                    if ( v === true ) {
                                        sortable.push( {"bSortable": false} );
                                    } else {
                                        sortable.push(null);
                                    }
                                });
                            } else {
                                $.each(sortableColumns, function (i, v) {
                                    sortable.push(null);
                                });
                            }
                            $("#' . $tableId . '").dataTable({
                                "sDom": \'<"clear-table clearfix"i><"table-content"rt><"table-paging clearfix"p>\',
                                "bServerSide": true,
                                "bProcessing": true,
                                "aoColumns": sortable,
                                ' . (isset($sorting) ? '"aaSorting": ' . $sorting . ',' : '') . '
                                "iDisplayLength": ROWS_PER_PAGE,
                                "oLanguage": {
                                    "sInfo": "Showing _START_ to _END_ of _TOTAL_ records",
                                    "sProcessing": "<div class=\"ldng datatableProcessing\"></div>"
                                },
                                "fnServerData": function  ( sSource, aoData, fnCallback, oSettings ) {
                                    var page = Math.ceil(oSettings._iDisplayStart / oSettings._iDisplayLength);
                                    var orderBys = [];
                                    $.each(oSettings.aaSorting, function(i, sorting) {
                                        var sortedColumn = sorting[0];
                                        var columnName = $("#' . $tableId . ' > thead > tr > th[column-index=" + sortedColumn + "]").attr("sort-column");
                                        orderBys.push({ "column": columnName, "order": sorting[1] });
                                    });
                                    oSettings.jqXHR = $.ajax({
                                        "dataType": ' . ($external ? '"jsonp"' : '"json"') . ',
                                        "type": "' . ($admin ? "POST" : "GET") . '",
                                        "url": "' . ($this->getCallbackUrl($admin, $public, $external)) . '",
                                        "data": {
                                            ' . $this->getServerArgs($admin) . ',
                                            "orderBy": orderBys,
                                            "offset": page * ROWS_PER_PAGE,
                                            "limit": ROWS_PER_PAGE
                                        },
                                        "beforeSend": function(xhr, settings) {
                                            xhr.setRequestHeader("X-CSRF-Token",((typeof GovDashboard !== "undefined")? GovDashboard.token : GD.options.csrf));
                                        },
                                        "success": function (data) {
                                            if ($("#' . $tableId . '").length) {
                                                var json = {};
                                                if (data["code"] == null || data["code"] == 200) {
                                                    $.each(aoData, function (i, o) {
                                                        if ( o["name"] == "sEcho" ) {
                                                            json.sEcho = o["value"];
                                                            return false;
                                                        }
                                                    });
                                                    json.iTotalDisplayRecords = data["response"]["totalRows"];
                                                    json.iTotalRecords = data["response"]["totalRows"];
                                                    json.aaData = [];

                                                    var columns = [];
                                                    for ( var i = 0; i < oSettings.aoColumns.length; i++ ) {
                                                        columns.push($("#' . $tableId . ' > thead > tr > th[column-index=\'" + i + "\']").attr("column-name"));
                                                    }

                                                    $.each(data["response"]["data"], function (i, r) {
                                                        var row = r["record"];
                                                        var obj = [];
                                                        $.each(columns, function (i, c) {
                                                            obj.push(row[c]);
                                                        });
                                                        json.aaData.push(obj);
                                                    });

                                                    $("#' . $tableId . '_wrapper").find("th").attr("title", function () {
                                                        return "Click to sort column";
                                                    });

                                                    if (data["response"]["totalRows"] > json.aaData.length) {
                                                        var height = $("#' . $tableId . '_wrapper > div.table-content").css("height");
                                                        $("#' . $tableId . '_wrapper > div.table-content").css("height", ' . $scrollHeight . ' - 45 - $("#gd-report-footer-' . intval($this->ReportConfig->getId()) . '").height());
                                                        $("#' . $tableId . '_wrapper").find("div.dataTables_paginate").css("line-height", 0);
                                                        $("#' . $tableId . '_wrapper > div.table-paging").find("a").attr("tabindex", "3000");
                                                    } else {
                                                        $("#' . $tableId . '_wrapper > div.table-content").css("height", ' . $scrollHeight . ' - $("#gd-report-footer-' . intval($this->ReportConfig->getId()) . '").height());
                                                        $("#' . $tableId . '_wrapper > div.table-paging").hide();
                                                    }
                                                    $("#' . $tableId . '_wrapper > div.table-content").find("thead").addClass("gd-table-header");
                                                    $("#' . $tableId . '_wrapper > div.table-content").find("th").attr("scope", "col");
                                                    $("#' . $tableId . '_wrapper > div.table-content").find("th").attr("tabindex", "3000");
                                                    $("#' . $tableId . '_wrapper > div.table-content").find("tbody").removeAttr("role");
                                                    $("#' . $tableId . '_wrapper > div.table-content").find("tbody").removeAttr("aria-live");
                                                    $("#' . $tableId . '_wrapper > div.table-content").find("tbody").removeAttr("aria-relevant");
                                                    ' . ((!isset($this->ReportConfig->options['visual']['advTableDisplayHeader'])) || $this->ReportConfig->options['visual']['advTableDisplayHeader'] === TRUE ? '' : '$("#' . $tableId . '_wrapper > div.table-content").find("thead").hide();') . '
                                                    fnCallback(json);
                                                    $("#' . $tableId . '_wrapper > div.table-content").find("th").attr("title", function() { return $(this).attr("column-title") + ": activate to sort column " + ($(this).hasClass("sorting_desc") ? "descending" : "ascending"); });
                                                    $("#' . $tableId . '_wrapper > div.table-paging").find("li.paginate_button, a").attr("tabindex", "3000");

                                                    //$("div.dataTables_info").show();
                                                } else if(data["code"] == 500) {
                                                    $("#' . $tableId . '").dataTable().fnSettings().oLanguage.sEmptyTable = "There was an error retrieving table data. Please contact the site administrator.";
                                                    $("#' . $tableId . '").dataTable().fnDraw();
                                                }
                                            }
                                        }
                                    }).fail(function (jqXHR, textStatus, errorThrown) {
                                        ' . ($admin ? $this->getAdminError() : '') . '
                                    });
                                }
                            });

                            $("#' . $tableId . '_wrapper > .table-content").css("width", "100%");
                            $("#' . $tableId . '_wrapper > .table-content").attr("tabindex", "3000");
                            $("#' . $tableId . '_wrapper > .table-content").css("overflow", "auto");
                            $("#' . $tableId . '_wrapper > div.table-content > table").attr("summary", "' . $tableSummary . '");
                            ' . $overlay . '
                        });
                    };
                    ' . (!$external ? ('func();') : ('GD.ExecuteTable' . intval($this->ReportConfig->getId()) . ' = func;')) . '
                    }(global.GD_jQuery ? global.GD_jQuery : jQuery);
                })(!window ? this : window);
            </script>'
        ;

        return $dataTableScript;
    }

    protected function getAdminError() {
        $viewName = 'ReportBuilderMessagingView';
        if ($this->ReportConfig->dashboard) {
            $viewName = 'DashboardBuilderMessagingView';
        }
        return 'if ( errorThrown ) {
                    GD.' . $viewName . '.addErrors(errorThrown);
                    GD.' . $viewName . '.displayMessages();
                } else if ( jqXHR.responseText ) {
                    var messages = $.parseJSON(jqXHR.responseText);
                    GD.' . $viewName . '.addErrors(messages);
                    GD.' . $viewName . '.displayMessages();
                }'
        ;
    }

    //  TODO Replicate in JavaScript when DataTable is a widget
    protected function getServerArgs( $admin ) {
        $args = array();
        $args[] = '"ds": "' . gd_datasource_get_active() . '"';

        if ( $admin ) {
            if ( $this->ReportConfig->dashboard ) {
                $args[] = '"dashboard": JSON.stringify(GovdashDashboardBuilder.getDashboard().getConfig())';
                $args[] = '"reportId":' . $this->ReportConfig->getId();
            } else {
                $args[] = '"report": GovdashReportBuilder.getReport().getConfig()';
            }
        } else {
            $args[] = '"reportId":' . $this->ReportConfig->getId();
            $args[] = '"origin": location.pathname';
            $filters = array();
            $filter_args = '"filter": [';
            foreach ( $this->ReportConfig->getFilters() as $f ) {
                if ( !empty($f->name) && !empty($_REQUEST['t']) ) {
                    foreach ( $_REQUEST['t'] as $dashboard => $filter ) {
                        foreach ($filter as $name => $t) {
                            if ($dashboard == $this->ReportConfig->dashboard || isset($t['ddf'])) {
                                if ( $f->name == $name ) {
                                    if (is_array($t)) {
                                        $filters[] =
                                            '{"dashboard":' . $dashboard . ',
                                            "name": "' . $name . '",
                                            "operator": "' . $t['o'] . '"
                                            '. (isset($t['v']) ? ',"value": ' . (is_array($t['v']) ? '["' . implode('","', $t['v']) . '"]' : '"' . htmlspecialchars($t['v']) . '"') : '') . '
                                            '.(isset($t['ddf']) ? ',"ddf": true' : '').'}';
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if ( count($filters) > 0 ) {
                $filter_args .= count($filters) > 1 ? implode(',', $filters) : $filters[0];
            }
            $filter_args .= ']';
            $args[] = $filter_args;

            if (isset($_REQUEST['bc'])) {
                $args[] = '"bc":"' .  $_REQUEST['bc'] . '"';
            }
        }
        return implode(',', $args);
    }

    public function getGenericSummary() {
        return 'This table displays ' . $this->ReportConfig->title . ' information';
    }
}
