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

    if ( !gd_account_user_is_admin() && !gd_account_user_is_any_datasource_admin() ) {
    echo "<h3>Access Denied</h3>";
    drupal_exit();
}
?>

<div id="datamart-usage-chart" class="stats"></div>
<script type='text/javascript'>

    var datamartLinks = <?php echo $datamarts_links_js; ?>;

    $(document).ready(function() {
        var chart = new Highcharts.Chart({
            chart: {
                renderTo: 'datamart-usage-chart',
                defaultSeriesType: 'bar',
                width: 450,
                height: 570,
                margins: [0,0,0,0],
                spacingTop: 4,
                spacingBottom: 15,
                spacingLeft: 35,
                spacingRight: 25,
                borderWidth: 0,
                borderColor: "#fff",
                className: 'hchart-stats'
            },
            title: {
                text: 'Account Usage by Topic'
            },
            subtitle: {
                text: '(as of <?php echo date("m-d-Y"); ?>)',
                align: 'center'
            },
            xAxis: {
                categories: <?php echo $datamarts_list_js; ?>,
                labels: {
                    formatter: function() {
                        <?php if( gd_account_user_is_admin($user) ){ ?>
                            for (var i=0,count=datamartLinks.length;i<count;i+=1) {
                                if ( this.value == datamartLinks[i].title ) {
                                    return '<a href="<?php echo GOVDASH_HOST; ?>' + datamartLinks[i].url +'">'+this.value+'</a>';
                                }
                            }
                        <?php } else { ?>
                            for (var i=0,count=datamartLinks.length;i<count;i+=1) {
                                if ( this.value == datamartLinks[i].title ) {
                                    return this.value;
                                }
                            }
                        <?php } ?>
                        return this.value;
                    },
                    align: 'right',
                    style: {
                        color: '#1C3F95',
                        fontWeight: 'bold',
                        lineHeight: '11px',
                        width: '200px',
                        textDecoration: 'underline'
                    }
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Data Records',
                    align: 'middle'
                }
            },

            tooltip: {
                formatter: function() {
                    return 'Total Records: ' + this.y;
                }
            },
            plotOptions: {
                bar: {
                    dataLabels: {
                        enabled: false
                    }
                }
            },
            credits: {
                enabled: false
            },
            series: [
                {
                    name: 'Total Data Records',
                    data: <?php echo $datamarts_record_counts_js; ?>
                }
            ]
        });
    });
</script>