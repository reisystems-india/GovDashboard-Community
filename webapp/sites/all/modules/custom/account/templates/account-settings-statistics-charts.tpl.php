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
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    global $theme;
    $path = drupal_get_path('theme', 'govdash_core');
    ?>
    <link rel="stylesheet" href="/sites/all/libraries/bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" href=<?php print $path."/css/style-cp.css"?> />

    <script src="/sites/all/libraries/jquery/jquery.min.js"></script>
    <script src="/sites/all/libraries/highcharts/js/highcharts.js"></script>
    <script src="/sites/all/libraries/bootstrap/js/bootstrap.min.js"></script>

</head>

<body>

<?php
if (isset($_GET['datamartcreate']) && $_GET['datamartcreate'] == "success") {
    print '<script type="text/javascript">alert("Topic Created Successfully"); parent.location.reload();</script>';
} else if (isset($_GET['datamartedit']) && $_GET['datamartedit'] == "success") {
    print '<script type="text/javascript">alert("Topic Edited Successfully"); parent.location.reload();</script>';
}

if (isset($_POST['fiscalmonth'])) {
    $fiscalStartMonth = $_POST['fiscalmonth'];
    variable_set('curFiscalStartMonth', $fiscalStartMonth);
    module_invoke_all('gd_fiscal_start_month_updated', $fiscalStartMonth);
}

$months = array(
    1 => 'January',
    2 => 'February',
    3 => 'March',
    4 => 'April',
    5 => 'May',
    6 => 'June',
    7 => 'July',
    8 => 'August',
    9 => 'September',
    10 => 'October',
    11 => 'November',
    12 => 'December'
);
?>
<div id="content-inside">
    <div style="float: left; width: 89%"><h2>Statistics &amp; Settings</h2></div>

    <?php if ( gd_account_user_is_admin($user) ) { ?>
    <div style="float: right; width: 11%">
        <div><a href="/account_datamart_create_datamart" class="fancyCpButton">Add Topic</a></div>
    </div>
    <?php } ?>
    <br clear="all">
        <table class="statistics" cellpadding="0" cellspacing="0">
        <?php
        global $user;
        //if user is user no 1 or part of instance admin then he can create datamart and set fiscal year
        if ( gd_account_user_is_admin($user) ) {
            ?>
            <tr>
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
                <td align="right" width="50%">&nbsp;</td>
                <td align="right" width="50%">
                    <div class="ddown-right">
                        <div class="styled-select">
                        <form name="fiscalyr" action="/account_datamart_statistics_charts" method="POST">
                            <select name="fiscalmonth" id="month" onchange="this.form.submit();" size="1">
                                <?php
                                foreach ($months as $key => $val) {
                                    if ($key == variable_get("curFiscalStartMonth")) {
                                        print "<option value='$key' selected>$val</option>";
                                    }
                                    else {
                                        print "<option value='$key'>$val</option>";
                                    }
                                }
                                ?>
                            </select>
                        </form>
                        </div>
                    </div>
                    <div class="ddown-left">Fiscal Year Starts in:</div>
                </td>
            </tr>
            <?php
        }
        ?>
        <tr>
            <td colspan="2">
                <div id="main">
                    <div id="console" class="clearfix">
                        <?php
                        $messages = drupal_get_messages();

                        if ( !empty($messages['warning']) ) {
                            echo '<div class="alert alert-block">';
                            echo '<button type="button" class="close" data-dismiss="alert">&times;</button>';
                            echo '<h4>Warning!</h4>';
                            foreach ($messages['warning'] as $message) {
                                echo $message.'<br/>';
                            }
                            echo '</div>';
                        }

                        if ( !empty($messages['status']) ) {
                            echo '<div class="alert alert-success">';
                            echo '<button type="button" class="close" data-dismiss="alert">&times;</button>';
                            echo '<h4>Success!</h4>';
                            foreach ($messages['status'] as $message) {
                                echo $message.'<br/>';
                            }
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </td>
        </tr>
    </table>
    <table class="statistics" cellpadding="0" cellspacing="0">
        <tr>
            <td colspan="2">
                <div class="separator"></div>
            </td>
        </tr>
        <tr>
            <td width=450>
                <div><?php echo theme('account_settings_barchart'); ?></div>
            </td>
            <td>
                <table width="100%" align="center">
                    <tr>
                        <td>
                            <div style="display:block"><?php echo theme('account_settings_dialchart_user'); ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div><?php echo theme('account_settings_dialchart_datamart'); ?></div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
</body>
</html>