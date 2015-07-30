<?php
    $reportId = $ReportConfig->getId();
    $reportId = isset($reportId) ? $reportId : 0;
    $disableDrag = (!$pvtTableDisableDrag || $pvtTableDisableDrag === "false")?0:1;
?>
<div id="report-<?php echo $reportId; ?>" class="gd-report-pivot-table report" style="height:<?php echo $height; ?>px;width:<?php echo $width; ?>px;overflow:hidden; position: relative;">
</div>

<script type="text/javascript">
    !function(global, $, undefined) {
        $("#report-<?php echo $reportId; ?>").on('ready.report.render', function() {
            <?php if ((gd_datasource_is_property(gd_datasource_get_active(), 'draft'))) { ?>
                var overlay = null;
                if (navigator.appVersion.indexOf("MSIE 9.") !== -1) {
                    overlay = $('<svg class="report-draft-overlay" xmlns="http://www.w3.org/2000/svg" xmlns:xlink= "http://www.w3.org/1999/xlink"><image alt="Draft Overlay" title="Draft Overlay" aria-label="Draft Overlay"  xlink:href="/sites/all/modules/custom/report/includes/images/draft.png" x="0" y="0" height="<?php echo $height > $width ? ('100%') : ($width . 'px'); ?>" width="<?php echo $width > $height ? ('100%') : ($height . 'px'); ?>"/></svg>');
                } else {
                    overlay = $('<img alt="Draft Overlay" title="Draft Overlay" aria-label="Draft Overlay" class="report-draft-overlay" src="/sites/all/modules/custom/report/includes/images/draft.png" style="<?php echo $height > $width ? ( 'height') : ('width'); ?>: 100%;">');
                }
                $('#report-<?php echo $reportId; ?>').append(overlay);
            <?php } ?>
            var urlOptions = {
                url: "<?php echo $url; ?>"
            };
            global.GD.PivotTableFactory.createTable(<?php echo $reportId; ?>, "#report-<?php echo $reportId;?>",
                {
                    measures: <?php echo json_encode($measures); ?>,
                    draft: <?php echo (gd_datasource_is_property(gd_datasource_get_active(), 'draft') ? "true" : "false"); ?>,
                    rows: <?php echo isset($rows) ? json_encode($rows): '[]'; ?>,
                    cols: <?php echo isset($cols) ? json_encode($cols): '[]'; ?>,
                    columns: <?php echo json_encode($columns); ?>,
                    url: urlOptions,
                    allowDragDrop: <?php echo $disableDrag; ?>,
                    callType: "<?php echo ( isset($admin) ? "POST" : "GET" ) ?>",
                    getDataObject: function(columns, measure) {
                        if (!columns) {
                            columns = [];
                        }
                        if (measure) {
                            columns.push(measure);
                        }
                        <?php if (isset($admin) && !isset($dashboard)) {
                            echo 'var config = GovdashReportBuilder.getReport().getConfig();';
                            echo "config['config']['model']['columns'] = columns;";
                        } ?>
                        return {
                            "ds": "<?php echo gd_datasource_get_active(); ?>",
                            <?php if (isset($admin)) {
                                if (isset($dashboard)) {
                                    echo '"dashboard": JSON.stringify(GovdashDashboardBuilder.getDashboard().getConfig()),';
                                    echo '"reportId":' . $reportId . ',';
                                    echo '"columns": columns';
                                } else {
                                    echo '"report": JSON.stringify(config)';
                                }
                            } else {
                                echo '"columns": columns,';
                                echo '"reportId":' . $reportId . ',';
                                echo '"origin": location.pathname,';
                                $filters = array();
                                foreach ( $ReportConfig->getFilters() as $f ) {
                                    if ( !empty($f->name) && !empty($_REQUEST['t']) ) {
                                        foreach ( $_REQUEST['t'] as $dashboard => $filter ) {
                                            foreach ($filter as $name => $t) {
                                                if ($dashboard == $ReportConfig->dashboard || isset($t['ddf'])) {
                                                    if ( $f->name == $name ) {
                                                        if (is_array($t)) {
                                                            $obj = new stdClass();
                                                            $obj->dashboard = $dashboard;
                                                            $obj->name = $name;
                                                            $obj->operator = $t['o'];
                                                            if (isset($t['v'])) {
                                                                if (is_array($t['v'])) {
                                                                    $obj->value = array(implode('","', $t['v']));
                                                                } else {
                                                                    $obj->value = htmlspecialchars($t['v']);
                                                                }
                                                            }
                                                            if (isset($t['ddf'])) {
                                                                $obj->ddf = true;
                                                            }
                                                            $filters[] = $obj;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                echo '"filter": ' . json_encode($filters);

                                if (isset($_REQUEST['bc'])) {
                                    echo ',"bc":"' .  $_REQUEST['bc'] . '"';
                                }
                            } ?>
                        };
                    }
                });
        });
    }(typeof window === 'undefined' ? this : window, jQuery);
</script>