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
<html lang="en">
<head>
    <?php
    global $theme;
    $path = drupal_get_path('theme', 'govdash_core');
    ?>
    <link rel="stylesheet" href="/sites/all/libraries/bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" href=<?php print $path."/css/style-cp.css"?> />

    <script src="/sites/all/libraries/jquery/jquery.min.js"></script>
    <script src="/sites/all/libraries/bootstrap/js/bootstrap.min.js"></script>
</head>
<body>
<?php

if ( !gd_account_user_is_admin() ) {
    echo "<h3>Access Denied</h3>";
    drupal_exit();
}

$errors = array();
if ( isset($_POST['createdatamart']) && ($_POST['createdatamart'] == "yes") ) {

    // validate
    if ( trim($_POST['publicName']) == '' ) {
        $errors[] = "Name can not be blank";
    }

    if ( !gd_datasource_name_is_unique(trim($_POST['publicName'])) ) {
        $errors[] = "Topic Name already exists. Please rename the topic";
    }

    // process
    if ( empty($errors) ) {
        global $user;

        $new = array();
        $new['publicName'] = $_POST['publicName'];
        $new['description'] = $_POST['description'];

        try {
            $datasourceName = gd_datasource_create($new);
            gd_datasource_set_active($datasourceName);

            drupal_set_message("Topic successfully created","status");
            drupal_goto("account_datamart_statistics_charts");

        } catch ( Exception $e ) {
            LogHelper::log_error($e);
            $errors[] = $e->getMessage();
        }
    }
}

?>
<div id="content-inside">
    <div style="float: left; width: 85%"><h2>Create Topic</h2></div>
    <div style="float: right; width: 15%">
        <div><a href='/account_datamart_statistics_charts' class="fancyCpButton">Back to Statistics</a></div>
    </div>
    <br clear="all">
<?php
if (variable_get("account_settings_maxdms") > count(gd_datasource_get_all())) {
    ?>
    <form id="dmcreate" action="/account_datamart_create_datamart" method="post">
        <table border="0" width="93%">
            <?php if ( !empty($errors) ) { ?>
            <tr>
                <td colspan="2">
                    <div class="alert alert-danger">
                    <h4>Error!</h4>
                    <?php
                    foreach ($errors as $message) {
                        echo $message.'<br/>';
                    }
                    ?>
                    </div>
                </td>
            </tr>
            <?php } ?>
            <tr>
                <td width="15%" valign="middle" align="right"><label>Name :</label></td>
                <td align="left" class="formCell">
                    <input type="text" maxlength="32" class="required" name="publicName" size="32" value="<?php if (isset($_POST['publicName'])) { echo $_POST['publicName']; } ?>" />
                </td>
            </tr>
            <tr>
                <td valign="top" align="right"><label>Description :</label></td>
                <td align="left" class="formCell">
                    <textarea cols="32" rows="5" name="description"><?php if (isset($_POST['description'])) { echo $_POST['description']; } ?></textarea>
                </td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
                <td align="right">&nbsp;</td>
                <td align="left" class="formButtons">
                    <input type="button" name="cancel" onclick="window.location='/account_datamart_statistics_charts';" value="Cancel">
                    <input type="hidden" name="createdatamart" value="yes">
                    <input type="submit" name="submit" value="Create">
                </td>
            </tr>
        </table>
    </form>

</div>
<?php } else {?>
<h3>Cannot add topic.</h3>
<h4>You have reached the maximum limit. Please contact GovDashboard support to upgrade your account package.</h4>
<?php }?>

</body>
</html>