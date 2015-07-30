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


if ( !isset($_GET['r']) ) {
    ?>
<script src="/sites/all/libraries/sparkline/jquery.sparkline.min.js"></script>
<script src="/sites/all/libraries/gd_print/jquery.gd_print.js"></script>
<script src="/sites/all/libraries/gd_print/jquery.gd_print_table.js"></script>
<?php
}

$isForDashboardBuilder = false;

// If dashboard builder -- not report builder, not dashboard viewer
if ( $_GET['q'] == 'dashboard/report/preview' ) { /* TODO: how else can we check for this??? */
    $isForDashboardBuilder = true;
}

$i=0;
$spans = array();
foreach($data as $name=>$values) {
    $spans['report_'.$config['id'].'_'.$i] = $values;
    $i++;
}
?>

<?php if ( $isForDashboardBuilder ) { ?>
<div id="<?php echo 'report-' . intval($ReportConfig->getId()) . 'grabber'; ?>" class="dashboardBuilderCanvasMover"></div>
<?php } ?>
<div class="report-container" id="<?php echo 'report-' . intval($ReportConfig->getId()); ?>" class="report" style="<?php echo (!empty($ReportConfig->options['style']) ? $ReportConfig->options['style'] : ''); ?> padding: 2px;">

    <?php foreach($spans as $id => $values) { ?>
    <div id="<?php echo $id; ?>" ></div>
    <?php } ?>
</div>
<script type="text/javascript">
    (function (global) {
        !function($, undefined) {
            <?php if ( $isForDashboardBuilder ) { ?>
            $("#<?php echo 'report-' . intval($ReportConfig->getId()); ?>").mouseover(function () {
                $("#<?php echo 'report-' . intval($ReportConfig->getId()) . 'grabber'; ?>").show();
            });
            <?php }


            foreach ($spans as $id => $values) { ?>
                var seriesData = <?php echo json_encode($values); ?>;
                $("#<?php echo $id; ?>").sparkline(seriesData, <?php echo $sparklineOptions;?>);
            <?php } ?>
        }(global.GD_jQuery ? global.GD_jQuery : jQuery);
    })(!window ? this : window);
</script>

<?php if ($draft) {
    echo '<script type="text/javascript">
        jQuery("#report-'.intval($ReportConfig->getId()).'").on("ready.report.render", function() {
            jQuery("#report-'.intval($ReportConfig->getId()).'").prepend(\'<div id="report-'.intval($ReportConfig->getId()).'-overlay" class="report-draft-overlay" src="/sites/all/modules/custom/report/includes/images/draft.png"/>\');
            jQuery("#report-'.intval($ReportConfig->getId()).'-overlay").height(jQuery("#report-'.intval($ReportConfig->getId()).'").height());
            jQuery("#report-'.intval($ReportConfig->getId()).'-overlay").width(jQuery("#report-'.intval($ReportConfig->getId()).'").width());
            jQuery("#report-'.intval($ReportConfig->getId()).'-overlay").css("top", "auto");
            jQuery("#report-'.intval($ReportConfig->getId()).'-overlay").bind("mouseenter mouseleave click dblclick foucsout hover mousedown mousemove mouseout mouseover mouseup", function (e) {
                if (navigator.appName == "Microsoft Internet Explorer") {
                    jQuery("#report-'.intval($ReportConfig->getId()).'-overlay").hide();
                    var target = document.elementFromPoint(e.clientX, e.clientY);
                    jQuery(target).trigger(e.type);
                    jQuery("#report-'.intval($ReportConfig->getId()).'-overlay").show();
                }
            });
        });
      </script>';
} ?>