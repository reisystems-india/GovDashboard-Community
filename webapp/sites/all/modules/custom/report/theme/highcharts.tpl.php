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


$enableMouseTracking = 'true';
if ( isset($_REQUEST['export-view']) ) {
    $enableMouseTracking = 'false';
}

$isForDashboardBuilder = false;
// If dashboard builder -- not report builder, not dashboard viewer
if ( $_GET['q'] == 'dashboard/report/preview' || isset($_REQUEST['dashboardBuilder']) ) { /* TODO: how else can we check for this??? */
    $isForDashboardBuilder = true;
}

$showTitle = false;
if ( !isset($ReportConfig->options['visual']['displayChartTitle']) || is_null($ReportConfig->options['visual']['displayChartTitle']) || $ReportConfig->options['visual']['displayChartTitle']==true ) {
    $showTitle = true;
}

$showMenu = false;
// extra checks for backwards compatibility
if ( !isset($ReportConfig->options['visual']['displayReportMenu']) || is_null($ReportConfig->options['visual']['displayReportMenu']) || $ReportConfig->options['visual']['displayReportMenu']==true ) {
    $showMenu = true;
}

$editable = false;
if ( gd_account_user_is_admin() || gd_account_user_is_datasource_admin(null,gd_datasource_get_active()) ) {
    $editable = true;
}

$chartHeight = 0;
$chartTop = 0;

if ( $showTitle || $showMenu ) {
    $chartTop = GD_REPORT_TITLE_HEIGHT;
}

if ( !empty($ReportConfig->dashboard) ) {
    $reportSize = $ReportConfig->getDisplaySize();
}

// dashboard viewer
if ( isset($ReportConfig->dashboard) ) {
    if ( !empty($reportSize) ) {
        $chartHeight = $reportSize->height - $chartTop - GD_BUILDER_BORDER_ADJUSTMENT;
    }
// report & dashboard builder
} else if ( isset($_GET['h']) ) {
    $chartHeight = $_GET['h'] - $chartTop;
    // if dashboard builder
    if ( $_GET['q'] == 'dashboard/report/preview' ) {
        $chartHeight -= GD_BUILDER_BORDER_ADJUSTMENT;
    }
}

$chartBottom = $chartHeight + $chartTop;

$chartWidth = 0;
// dashboard viewer
if ( isset($ReportConfig->dashboard) ) {
    if ( !empty($reportSize) ) {
        $chartWidth = $reportSize->width - GD_BUILDER_BORDER_ADJUSTMENT;
    }
// report & dashboard builder
} else if ( isset($_GET['w']) ) {
    $chartWidth = $_GET['w'];
    // if dashboard builder
    if ( $_GET['q'] == 'dashboard/report/preview' ) {
        $chartWidth -= GD_BUILDER_BORDER_ADJUSTMENT;
    }
}

$chartStyle = '';
if ( $chartHeight ) {
    $chartStyle .= 'height:'.$chartHeight.'px;';
}

if ( $chartWidth ) {
    $chartStyle .= 'width:'.$chartWidth.'px;';
}

?>
<div id="<?php echo 'report-' . intval($ReportConfig->getId()); ?>" style="position:relative;clear:both;<?php echo $chartStyle ?>">
    <div class="ldng"></div>
</div>
<script type="text/javascript">
(function (global,undefined) {
    (function ($,Highcharts) {
        $('#<?php echo 'report-' . intval($ReportConfig->getId()); ?>').on('ready.report.render', function() {
            // mapping between SVG attributes and the corresponding options
            Highcharts.seriesTypes.column.prototype.pointAttrToOptions.dashstyle = 'dashStyle';

            Highcharts.setOptions({
                colors: [
                    '#4572A7', '#AA4643', '#89A54E', '#80699B', '#3D96AE', '#DB843D', '#92A8CD', '#A47D7C', '#B5CA92',
                    '#66FF66', '#FF66FF', '#FFCC66', '#FF9966', '#33CCFF', '#3399FF', '#003300', '#003399', '#993300'
                ]
            });

            var formatters = [];
            <?php if (!empty($visual['datalabels'])) {
                foreach($visual['datalabels'] as $dl) {
                    echo 'formatters.push({"name": "' . $dl->series . '", "display": ' . $dl->display . ', "formatter": { "getFormattedValue": function() { ' . $dl->formatter . ' } }});';
                }
            } ?>

            var GD_Report<?php echo intval($ReportConfig->getId()); ?>_Common = {

                GetLink: function (value, displayValue) {
                    var links = <?php echo json_encode($columnLinks); ?>;

                    if (value == null) {
                        value = this.value;
                    }

                    var prefix = "", suffix = "", v = "";
                    if (links[value] != null) {
                        prefix = '<a href="' + links[value] + '">';
                        suffix = '</a>';
                    }

                    if (displayValue != null) {
                        v = displayValue;
                    } else {
                        v = value;
                    }

                    v = v.replace(/&/g, "&amp;")
                        .replace(/</g, "&lt;")
                        .replace(/>/g, "&gt;")
                        .replace(/"/g, "&quot;")
                        .replace(/'/g, "&#039;");

                    return prefix + v + suffix;
                },

                ClickHandler: function () {
                    $('.gd-report-menu').hide();
                    if (this.options.url != null) {
                        location.href = this.options.url;
                    }
                },

                GetQuarter: function (month) {
                    var quarter = {
                        "01": "Q1",
                        "02": "Q1",
                        "03": "Q1",
                        "04": "Q2",
                        "05": "Q2",
                        "06": "Q2",
                        "07": "Q3",
                        "08": "Q3",
                        "09": "Q3",
                        "10": "Q4",
                        "11": "Q4",
                        "12": "Q4"
                    };

                    return quarter[month];
                }
            };

            <?php if ( $isForDashboardBuilder ) { ?>
            $("#<?php echo 'report-' . intval($ReportConfig->getId()); ?>").mouseover(function () {
                $("#<?php echo 'report-' . intval($ReportConfig->getId()) . 'grabber'; ?>").show();
            });
            <?php } ?>

            var chart_<?php echo intval($ReportConfig->getId());?> = new Highcharts.Chart({
                chart: {
                    chartId: <?php echo intval($ReportConfig->getId()); ?>,
                    chartTitle: "<?php echo str_replace('"','\\"',$ReportConfig->title); ?>",
                    <?php
                    if ( $editable ) {
                        ?>
                    chartEditable: true,
                    <?php
                } else {
                    ?>
                    chartEditable: false,
                    <?php
                }
                ?>
                    renderTo: '<?php echo 'report-' . intval($ReportConfig->getId()); ?>',
                    <?php if ($config['xaxis']['type'] == 'datetime') { ?>
                    zoomType: 'x',
                    <?php } ?>
                    defaultSeriesType: '<?php echo $config['chartType']; ?>',
                    margins: [0, 0, 0, 0],
                    spacingTop: 10,
                    spacingBottom: 15,
                    spacingLeft: 10,
                    <?php if (isset($visual['spacingRight']) ) { ?>
                    spacingRight: <?php echo $visual['spacingRight']; ?>,
                    <?php } else {?>
                    spacingRight: 25,
                    <?php } ?>
                    borderWidth: <?php echo $config['borderWidth']?$config['borderWidth']:0; ?>,
                    borderColor: "#eeeeee",
                    style: {
                        fontFamily: '"Lucida Grande", "Lucida Sans Unicode", Tahoma, Arial, sans-serif'
                    }
                    , width: <?php echo $chartWidth; ?>
                    , height: $("#<?php echo 'report-' . intval($ReportConfig->getId()); ?>").height()
                    , events: {
                        click: function () {
                            $('.gd-report-menu').hide();
                        },
                        load: function() {
                            <?php echo 'var draft = ' . ($draft ? 'true' : 'false') . ';'; ?>
                            if (draft) {
                                var width = this.chartWidth;
                                var height = this.chartHeight;
                                var ratio = 1008 / 316;

                                var imgWidth = .25 * width;
                                var imgHeight = ratio * imgWidth;
                                var href="data:image/png;base64,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";

                                this.renderer.image(href, 0, 0, imgWidth, imgHeight).attr({opacity:".25"}).add();
                                this.renderer.image(href, imgWidth, 0, imgWidth, imgHeight).attr({opacity:".25"}).add();
                                this.renderer.image(href, imgWidth * 2, 0, imgWidth, imgHeight).attr({opacity:".25"}).add();
                                this.renderer.image(href, imgWidth * 3, 0, imgWidth, imgHeight).attr({opacity:".25"}).add();

                                var h = imgHeight;
                                while (height > h) {
                                    this.renderer.image(href, 0, h, imgWidth, imgHeight).attr({opacity:".25"}).add();
                                    this.renderer.image(href, imgWidth, h, imgWidth, imgHeight).attr({opacity:".25"}).add();
                                    this.renderer.image(href, imgWidth * 2, h, imgWidth, imgHeight).attr({opacity:".25"}).add();
                                    this.renderer.image(href, imgWidth * 3, h, imgWidth, imgHeight).attr({opacity:".25"}).add();
                                    h = h + imgHeight;
                                }
                            }
                            $(GD).trigger("chartLoaded",[this]);
                        },
                        redraw:function(){
                            $(GD).trigger("chartRedrawn",[this]);
                        }
                    }
                },
                exporting: {
                    enabled: false
                },
                tooltip: {
                    enabled: <?php echo $visual['showTooltip']; ?>,
                    <?php if ($visual['displayPercentStack']) { ?>
                        "pointFormat": "<span style=\"color:{series.color}\">{series.name}</span>: <b>{point.y}</b> ({point.percentage:.0f}%)<br/>",
                        "shared": true
                    <?php } else if ( isset($config['tooltip']) ) { ?>
                    formatter: function () {
                        <?php for($i=0; $i<sizeof($config['yaxis']['name']); $i++){ ?>
                            if (this.series.name == "<?php echo str_replace('"','\\"',$config['yaxis']['seriesTitle'][$config['yaxis']['name'][$i]]);?>") {
                                <?php echo($config['tooltip'][$config['yaxis']['name'][$i]]);?>
                            }
                        <?php }?>
                        return this.x + ": " + this.y;
                    }
                <?php } ?>
                },
                credits: {
                    enabled: false
                },
                title: {
                    /*text: "<?php echo $config['chartTitle']; ?>",*/
                    text: " ",
                    style: {
                        fontSize: '<?php echo $config['titleFontSize']; ?>'
                    }
                },
                <?php
                if ( isset($visual['targetLine']) && $visual['targetLine'] == 1 && isset($config['yaxis']['targetLineFormattedValue'][0])) {
                    if (!empty($config['yaxis']['title'][0])) { ?>
                subtitle: {
                    text: '<?php echo "* Target value for ".$config['yaxis']['title'][0]." is ".$config['yaxis']['targetLineFormattedValue'][0];?>',
                    align: 'right',
                    y: 10,
                    style: {
                        color: '<?php echo isset($visual['targetLineColor']) ? $visual['targetLineColor'] : 'black'; ?>'
                    }
                },
                <?php } else {?>
                subtitle: {
                    text: '<?php echo "* Target value ".$config['yaxis']['title'][0]." is ".$config['yaxis']['targetLineFormattedValue'][0];?>',
                    align: 'right',
                    y: 10,
                    style: {
                        color: '<?php echo isset($visual['targetLineColor']) ? $visual['targetLineColor'] : 'black' ?>'
                    }
                },
                <?php }} ?>
                plotOptions: {
                    cursor: 'pointer',

                    area: {
                        point: {
                            events: {
                                click: GD_Report<?php echo intval($ReportConfig->getId()); ?>_Common.ClickHandler
                            }
                        },
                        cursor: 'pointer',
                        shadow: false,
                        borderWidth: 0,
                        dataLabels: {
                            color: "black",
                            enabled: true,
                            crop: false,
                            formatter: function () {
                                var value = '';
                                var _this = this;
                                $.each(formatters, function (i, f) {
                                    if (f['name'] == _this.point.series.name) {
                                        if (f['display']) {
                                            f['formatter']['value'] = _this.point.y;
                                            value = f['formatter']['getFormattedValue']();
                                        } else {
                                            value = _this.point.y;
                                        }
                                        return false;
                                    }
                                });
                                return value;
                            }
                        }
                    },
                    areaspline: {
                        point: {
                            events: {
                                click: GD_Report<?php echo intval($ReportConfig->getId()); ?>_Common.ClickHandler
                            }
                        },
                        cursor: 'pointer',
                        shadow: false,
                        borderWidth: 0,
                        dataLabels: {
                            color: "black",
                            enabled: true,
                            crop: false,
                            formatter: function () {
                                var value = '';
                                var _this = this;
                                $.each(formatters, function (i, f) {
                                    if (f['name'] == _this.point.series.name) {
                                        if (f['display']) {
                                            f['formatter']['value'] = _this.point.y;
                                            value = f['formatter']['getFormattedValue']();
                                        } else {
                                            value = _this.point.y;
                                        }
                                        return false;
                                    }
                                });
                                return value;
                            }
                        }
                    },
                    bar: {
                        <?php if ($visual['displayPercentStack']) { ?>
                            stacking: 'percent',
                        <?php } ?>
                        point: {
                            events: {
                                click: GD_Report<?php echo intval($ReportConfig->getId()); ?>_Common.ClickHandler
                            }
                        },
                        cursor: 'pointer',
                        shadow: false,
                        borderWidth: 0,
                        marginRight: 130,
                        dataLabels: {
                            color: "black",
                            enabled: true,
                            crop: false,
                            formatter: function () {
                                var value = '';
                                var _this = this;
                                $.each(formatters, function (i, f) {
                                    if (f['name'] == _this.point.series.name) {
                                        if (f['display']) {
                                            f['formatter']['value'] = _this.point.y;
                                            value = f['formatter']['getFormattedValue']();
                                        } else {
                                            value = _this.point.y;
                                        }
                                        return false;
                                    }
                                });
                                return value;
                            }
                        }
                    },
                    column: {
                        <?php if ($visual['displayPercentStack']) { ?>
                            stacking: 'percent',
                        <?php } ?>
                        point: {
                            events: {
                                click: GD_Report<?php echo intval($ReportConfig->getId()); ?>_Common.ClickHandler
                            }
                        },
                        cursor: 'pointer',
                        shadow: false,
                        borderWidth: 0,
                        dataLabels: {
                            color: "black",
                            enabled: true,
                            crop: false,
                            formatter: function () {
                                var value = '';
                                var _this = this;
                                $.each(formatters, function (i, f) {
                                    if (f['name'] == _this.point.series.name) {
                                        if (f['display']) {
                                            f['formatter']['value'] = _this.point.y;
                                            value = f['formatter']['getFormattedValue']();
                                        } else {
                                            value = _this.point.y;
                                        }
                                        return false;
                                    }
                                });
                                return value;
                            }
                        }
                    },
                    line: {
                        point: {
                            events: {
                                click: GD_Report<?php echo intval($ReportConfig->getId()); ?>_Common.ClickHandler
                            }
                        },
                        cursor: 'pointer',
                        shadow: false,
                        borderWidth: 0,
                        dataLabels: {
                            color: "black",
                            enabled: true,
                            crop: false,
                            formatter: function () {
                                var value = '';
                                var _this = this;
                                $.each(formatters, function (i, f) {
                                    if (f['name'] == _this.point.series.name) {
                                        if (f['display']) {
                                            f['formatter']['value'] = _this.point.y;
                                            value = f['formatter']['getFormattedValue']();
                                        } else {
                                            value = _this.point.y;
                                        }
                                        return false;
                                    }
                                });
                                return value;
                            }
                        }
                    },
                    pie: {
                        <?php if ( isset($config['pie']['borderWidth']) ) { ?>
                        borderWidth: <?php echo $config['pie']['borderWidth']; ?>,
                        <?php } ?>
                        shadow: <?php echo $config['pie']['shadow']; ?>,
                        point: {
                            events: {
                                click: GD_Report<?php echo intval($ReportConfig->getId()); ?>_Common.ClickHandler,
                                legendItemClick: function () {
                                    $('.gd-report-menu').hide();
                                }
                            }
                        },
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            crop: false,
                            enabled: <?php echo $config['pie']['enableDataLabels']; ?>,
                            formatter: function () {
                                if (!this.point.name) {
                                    this.point.color = 'null'; // hide connector
                                    return '';
                                } else {
                                    <?php
                                    if ( isset($config['pie']['dataLabelsOptions']) ) {
                                        if ( $config['pie']['dataLabelsOptions'] == 1 ) {
                                            echo "return Math.round(this.percentage*100)/100 + ' %';\n";
                                        } else {if ( $config['pie']['dataLabelsOptions'] == 2 ) {
                                            echo "return this.point.yFormatted+' ('+Math.round(this.percentage*100)/100 + ' %)';\n";
                                        } else {
                                            echo "return this.point.yFormatted;\n";
                                        }}
                                    } else {
                                        echo "return this.point.yFormatted;\n";
                                    }
                                    ?>
                                }
                            },

                            style: {
                                fontSize: '<?php echo $config['pie']['dataLabelsFontSize']; ?>'
                            }
                        },
                        showInLegend: <?php echo $visual['displayLegend']; ?>
                    },
                    funnel: {
                        <?php if ( isset($config['funnel']['borderWidth']) ) { ?>
                        borderWidth: <?php echo $config['funnel']['borderWidth']; ?>,
                        <?php } ?>
                        <?php if ( isset($config['funnel']['shadow']) ) { ?>
                        shadow: <?php echo $config['funnel']['shadow']; ?>,
                        <?php } ?>
                        point: {
                            events: {
                                click: GD_Report<?php echo intval($ReportConfig->getId()); ?>_Common.ClickHandler,
                                legendItemClick: function () {
                                    $('.gd-report-menu').hide();
                                }
                            }
                        },
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            <?php if ( isset($config['funnel']['enableDataLabels']) ) { ?>
                            crop: false,
                            enabled: <?php echo $config['funnel']['enableDataLabels']; ?>,
                            <?php } ?>
                            formatter: function () {
                                if (!this.point.name) {
                                    this.point.color = 'null'; // hide connector
                                    return '';
                                } else {
                                    //GOVDB-2072
                                    return this.point.yFormatted;
                                }
                            },

                            style: {
                                <?php if ( isset($config['funnel']['dataLabelsFontSize']) ) { ?>
                                fontSize: '<?php echo $config['funnel']['dataLabelsFontSize']; ?>'
                                <?php } ?>
                            }
                        },
                        showInLegend: <?php echo $visual['displayLegend']; ?>
                    },
                    scatter: {
                        shadow: false
                    },
                    spline: {
                        shadow: false,
                        dataLabels: {
                            color: "black",
                            enabled: true,
                            crop: false,
                            formatter: function () {
                                var value = '';
                                var _this = this;
                                $.each(formatters, function (i, f) {
                                    if (f['name'] == _this.point.series.name) {
                                        if (f['display']) {
                                            f['formatter']['value'] = _this.point.y;
                                            value = f['formatter']['getFormattedValue']();
                                        } else {
                                            value = _this.point.y;
                                        }
                                        return false;
                                    }
                                });
                                return value;
                            }
                        }
                    },
                    series: {
                        <?php if ( isset($visual['stack']) && $visual['stack'] == TRUE) { ?>
                        stacking: '<?php echo $visual['displayPercentStack'] ? 'percent' : 'normal'; ?>',
                        <?php } ?>
                        events: {
                            legendItemClick: function () {
                                $('.gd-report-menu').hide();
                            }
                        },
                        //causing pie rendering issues.
                        /*shadow: false,
                         animation: false,*/
                        enableMouseTracking: <?php echo $enableMouseTracking; ?>
                    }
                },
                xAxis: {
                    <?php if ($config['xaxis']['type'] == 'linear') { ?>
                    categories: <?php echo $categories; ?>,
                    <?php } ?>
                    title: {
                        text: "<?php echo str_replace('"','\\"',$config['xaxis']['title']); ?>",
                        style: {
                            fontSize: '<?php echo $config['xaxis']['titleFontSize']; ?>'
                        }
                    },
                    type: '<?php echo $config['xaxis']['type']; ?>',
                    labels: {
                        <?php if (isset($config['xaxis']['formatter'])) { ?>
                        formatter: function () { <?php echo $config['xaxis']['formatter']; ?>
                        },
                        <?php } ?>
                        enabled: <?php echo $visual['displayXAxisLabel']; ?>,
                        rotation: <?php echo $visual['labelRotation']; ?>,
                        align: '<?php echo !empty($visual['xaxisAlign']) ? $visual['xaxisAlign'] : ''; ?>',
                        <?php if ($ReportConfig->options['config']['chartType'] == "bar") { ?>
                        y: 5, // added to keep rotated values with null data from overlapping
                        <?php } ?>
                        style: {
                            fontSize: '<?php echo $config['xaxis']['labelsFontSize']; ?>'
                        }
                    }
                },
                <?php if( $visual['displaySeries'] && $config['chartType'] != 'pie' ){ ?>
                yAxis: [
                    <?php
                    $sizeOfDefaultColorArray = 9;
                    $defaultColorIndex = 0;
                    ?>
                    //iterate over all series
                    <?php for($i=0; $i<sizeof($config['yaxis']['title']); $i++){ ?>

                    <?php
                    $defaultColorIndex = ($defaultColorIndex % $sizeOfDefaultColorArray);
                    ?>
                    //for first series apply all the visual settings. TODO: need to redo the input form to accept config for multiple series.
                    //Consider this as primary series.
                    <?php if($i==0) {?>
                    {
                        //show target-line for primary column only
                        <?php
                        if ( isset($visual['targetLine']) ) {
                            if ($visual['targetLine'] == 1) { ?>
                        plotLines: [{
                            color: '<?php echo $visual['targetLineColor'] ?>',
                            value: <?php echo $visual['targetLineValue']?$visual['targetLineValue']:0 ?>,
                            width: 3,
                            zIndex: 1
                        }],
                        <?php }
                } ?>

                        title: {
                            text: "<?php echo str_replace('"','\\"',$config['yaxis']['title'][$i]); ?>",
                            style: {
                                <?php if ( isset($visual['series'][$config['yaxis']['name'][$i]]['color']) && $visual['series'][$config['yaxis']['name'][$i]]['color'] != NULL ) { ?>
                                color: '<?php echo $visual['series'][$config['yaxis']['name'][$i]]['color'];?>',
                                <?php } else {?>
                                color: Highcharts.getOptions().colors[<?php echo $defaultColorIndex;?>],
                                <?php } ?>
                                fontSize: '<?php echo $config['yaxis']['titleFontSize']; ?>'
                            }
                        },
                        labels: {
                            <?php if (isset($visual['yaxisRotation'])) { ?>
                            rotation: <?php echo $visual['yaxisRotation']; ?>,
                            align: 'right',
                            <?php } ?>
                            <?php if (isset($config['yaxis']['formatter'])) { ?>
                            formatter: function () { <?php echo $config['yaxis']['formatter'][$i]; ?>
                            },
                            <?php } ?>
                            style: {
                                <?php if ( isset($visual['series'][$config['yaxis']['name'][$i]]['color']) && $visual['series'][$config['yaxis']['name'][$i]]['color'] != NULL ) { ?>
                                color: '<?php echo $visual['series'][$config['yaxis']['name'][$i]]['color'];?>',
                                <?php } else {?>
                                color: Highcharts.getOptions().colors[<?php echo $defaultColorIndex;?>],
                                <?php $defaultColorIndex++; } ?>
                                fontSize: '<?php echo $config['yaxis']['labelsFontSize']; ?>'
                            }
                        },
                        gridLineWidth: <?php echo $visual['gridLineWidth']?$visual['gridLineWidth']:'false'; ?>,
                        min: <?php echo $config['yaxis']['min']?$config['yaxis']['min']:0; ?>,
                        max: <?php echo $config['yaxis']['max']?$config['yaxis']['max']:0; ?>,
                        <?php if ( ($config['yaxis']['min']!="null") || ($config['yaxis']['max']!="null") ) { ?>
                        startOnTick: true,
                        <?php } ?>
                        tickInterval: <?php echo $config['yaxis']['tickInterval']?$config['yaxis']['tickInterval']:'auto'; ?>
                    }
                    <?php } else {?>
                    //For all other series apply generic settings
                    {
                        opposite: true,
                        title: {
                            text: "<?php echo str_replace('"','\\"',$config['yaxis']['title'][$i]); ?>",
                            style: {
                                <?php if ( isset($visual['series'][$config['yaxis']['name'][$i]]['color']) && $visual['series'][$config['yaxis']['name'][$i]]['color'] != NULL ) { ?>
                                color: '<?php echo $visual['series'][$config['yaxis']['name'][$i]]['color'];?>',
                                <?php } else {?>
                                color: Highcharts.getOptions().colors[<?php echo $defaultColorIndex;?>],
                                <?php } ?>
                                fontSize: '<?php echo $config['yaxis']['titleFontSize']; ?>'
                            }
                        },
                        gridLineWidth: <?php echo $visual['gridLineWidth']?$visual['gridLineWidth']:0; ?>,
                        labels: {
                            <?php if (isset($visual['yaxisRotation'])) { ?>
                            rotation: <?php echo $visual['yaxisRotation']; ?>,
                            align: 'left',
                            <?php } ?>
                            <?php if (isset($config['yaxis']['formatter'])) { ?>
                            formatter: function () { <?php echo $config['yaxis']['formatter'][$i]; ?>
                            },
                            <?php } ?>
                            style: {
                                <?php if ( isset($visual['series'][$config['yaxis']['name'][$i]]['color']) && $visual['series'][$config['yaxis']['name'][$i]]['color'] != NULL ) { ?>
                                color: '<?php echo $visual['series'][$config['yaxis']['name'][$i]]['color'];?>',
                                <?php } else {?>
                                color: Highcharts.getOptions().colors[<?php echo $defaultColorIndex;?>],
                                <?php $defaultColorIndex++; } ?>
                                fontSize: '<?php echo $config['yaxis']['labelsFontSize']; ?>'
                            }
                        }
                    }
                    <?php }?>
                    //adding comma after each element.
                    <?php if($i+1!=sizeof($config['yaxis']['title'])){?>, <?php }?>
                    <?php } ?>
                ],
                <?php } else { ?>
                yAxis: {
                    title: {
                        text: "<?php echo str_replace('"','\\"',$config['yaxis']['title'][0]); ?>",
                        style: {
                            fontSize: '<?php echo $config['yaxis']['titleFontSize']; ?>'
                        }
                    },
                    <?php
                    //Target line implementation
                    if ( isset($visual['targetLine']) ) {
                        if ($visual['targetLine'] == 1) { ?>
                    plotLines: [{
                        color: '<?php echo isset($visual['targetLineColor']) ? $visual['targetLineColor'] : 'black' ?>',
                        value: <?php echo $visual['targetLineValue']? $visual['targetLineValue']:0?>,
                        width: 3,
                        zIndex: 1
                    }],
                    <?php }
            } ?>
                    labels: {
                        <?php if (isset($visual['yaxisRotation'])) { ?>
                        rotation: <?php echo $visual['yaxisRotation']; ?>,
                        align: 'right',
                        <?php } ?>
                        <?php if (isset($config['yaxis']['formatter'])) { ?>
                        //  Percent stacking will not using formatter for labels
                        formatter: function () { <?php echo ($visual['displayPercentStack'] ? 'return this.value + "%";' :  $config['yaxis']['formatter'][0]); ?>
                        },
                        <?php } ?>
                        style: {
                            fontSize: '<?php echo $config['yaxis']['labelsFontSize']; ?>'
                        }
                    },
                    gridLineWidth: <?php echo $visual['gridLineWidth']?$visual['gridLineWidth']:'false'; ?>,
                    min: <?php echo $config['yaxis']['min']?$config['yaxis']['min']:0; ?>,
                    max: <?php echo $config['yaxis']['max']?$config['yaxis']['max']:0; ?>,
                    <?php if ( ($config['yaxis']['min']!="null") || ($config['yaxis']['max']!="null") ) { ?>
                    startOnTick: true,
                    <?php } ?>
                    tickInterval: <?php echo $config['yaxis']['tickInterval']?$config['yaxis']['tickInterval']:'"auto"'; ?>
                },
                <?php } ?>
                legend: {
                    enabled: <?php echo $visual['displayLegend']; ?>,
                    itemStyle: {
                        fontSize: '<?php echo $config['legend']['fontSize']; ?>'
                    }
                },
                series: <?php echo $data; ?>
            });
            global.chart_<?php echo intval($ReportConfig->getId());?> = chart_<?php echo intval($ReportConfig->getId());?>;
            $('#<?php echo 'report-' . intval($ReportConfig->getId()); ?>').trigger('ready.report.highcharts', chart_<?php echo intval($ReportConfig->getId());?>);
        });
    })(global.GD_jQuery != null ? global.GD_jQuery : jQuery, global.GD_Highcharts != null ? global.GD_Highcharts : Highcharts);
})(window);
</script>
