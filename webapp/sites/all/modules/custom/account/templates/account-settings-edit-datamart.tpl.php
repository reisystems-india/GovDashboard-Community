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


if ( isset($_GET['datasource']) ) {
    $datasourceName = $_REQUEST['datasource'];
}

if ( isset($_POST['datasourceName']) ) {
    $datasourceName = $_POST['datasourceName'];
}

gd_datasource_set_active($datasourceName);

if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(null,gd_datasource_get_active()) ) {
    echo "<h3>Access Denied</h3>";
    drupal_exit();
}

$datasource = gd_datasource_get($datasourceName);


$deleteWarningMessage = "Are you sure you want to delete \'".($datasource->publicName)."\'? ".
                        "This will delete all the associated Dashboards, Reports and Datasets from that topic. ".
                        "This action cannot be undone.";

if ( isset($_POST['editdatamart']) && $_POST['editdatamart'] == "yes" ) {

    $errors = array();

    // validate
    if ( trim($_POST['publicName']) == '' ) {
        $errors[] = "Name can not be blank";
    }

    if ( trim($_POST['publicName']) != $datasource->publicName && !gd_datasource_name_is_unique(trim($_POST['publicName'])) ) {
        $errors[] = "Topic Name already exists. Please rename the topic";
    }

    // process
    if ( empty($errors) ) {
        $action = (isset($_POST['save_datamart'])) ? $_POST['save_datamart'] : $_POST['delete_datamart'];

        if ( $action == 'Save' ) {

            try {
                gd_datasource_update($datasourceName,$_POST);

                drupal_set_message("Topic successfully updated","status");
                drupal_goto("account_datamart_statistics_charts");
            } catch ( Exception $e ) {
                LogHelper::log_error($e);
                $errors[] = $e->getMessage();
            }


        } else if ( $action == 'Delete' ) {

            try {

                gd_datasource_unpublish($datasourceName);

                drupal_set_message("Topic successfully deleted","status");
                drupal_goto("account_datamart_statistics_charts");

            } catch ( Exception $e ) {
                LogHelper::log_error($e);
                $errors[] = $e->getMessage();
            }

        }
    }
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
    <script src="/sites/all/libraries/bootstrap/js/bootstrap.min.js"></script>
</head>
<body>
<div id="content-inside">
    <div style="float: left; width: 85%"><h2>Edit Topic : <?php print $datasource->publicName; ?></h2></div>
    <div style="float: right; width: 15%">
        <div><a href='/account_datamart_statistics_charts' class="fancyCpButton">Back to Statistics</a></div>
    </div>
    <br clear="all">

    <form id="dmedit" action="/account_datamart_edit_datamart" method="post">
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
                <td width="15%" valign="top" align="right"><label>Name:&nbsp;</label></td>
                <td align="left" class="formCell">
                    <input type="text" class="required" name="publicName" size="46" value="<?php if (isset($_POST['publicName'])) { echo $_POST['publicName']; } else { echo $datasource->publicName; } ?>">
                </td>
            </tr>
            <tr>
                <td valign="top" align="right"><label>Description:&nbsp;</label></td>
                <td align="left" class="formCell">
                    <textarea cols="34" rows="5" name="description"><?php if (isset($_POST['description'])) { echo $_POST['description']; } else { echo $datasource->description; } ?></textarea>
                </td>
            </tr>
            <tr>
                <td align="right">&nbsp;</td>
                <td align="left" class="formButtons">
                    <input type="hidden" name="editdatamart" value="yes">
                    <input type="hidden" name="datasourceName" value="<?php print $datasource->name;?>">
                    <input type="button" name="cancel" onclick="window.location='/account_datamart_statistics_charts';" value="Cancel">
                    <input type="submit" name="delete_datamart" id="delete_datamart" value="Delete">
                    <input type="submit" name="save_datamart" id="save_datamart" value="Save" />
                </td>
            </tr>
        </table>
    </form>
</div>
<br/><br/>

<script type="text/javascript">

    $("form input[type=submit]").click(function() {
        $("input[type=submit]", $(this).parents("form")).removeAttr("clicked");
        $(this).attr("clicked", "true");
    });

    jQuery('#dmedit').submit(function(){
        var message = '<?php echo $deleteWarningMessage; ?>';
        parent.isc.DatamartDS.getInstance().fetchData();
        //NOT ABLE TO USE SMART CLIENT CONFIRMATION MESSAGE BECAUSE FORM SUBMITION WOULD NOT WAIT FOR ANSWER
        if($("input[type=submit][clicked=true]").val() == "Delete"){
            var answer = confirm(message);
            return answer;
        }else{
            return true;
        }
    });

 </script>

</body>
</html>