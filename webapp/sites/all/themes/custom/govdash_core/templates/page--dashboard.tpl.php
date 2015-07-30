<?php // TODO Clean up IDs. Use standard Camel Case instead of dashes. ?>

<div id="page">
    <header id="header" role="banner">
        <div id="header-inside" class="container">
            <div id="header-inside-left">
                <?php if ($logo): ?>
                    <a tabindex="5" href="javascript:void(0)" title="<?php print $site_name; ?>">
                        <img class="gd-logo" src="<?php print $logo; ?>" alt="<?php print $site_name; ?>"/>
                    </a>
                <?php endif; ?>
            </div>
            <div id="header-inside-right">
                <div id="search-area">
                    <?php if ($user->uid) : ?>
                        <ul class="secondary-menu links clearfix">
                            <li
                                class="first"><?php print l($user->firstname . ' ' . $user->lastname, 'user/profile', array('attributes' => array('tabindex' => '5'))); ?></li>
                            <?php
                            if (gd_account_user_is_admin() || gd_account_user_is_any_datasource_admin()) {
                                print '<li>' . l("Control Panel", "cp", array('query'=>array('ds'=>gd_datasource_find_active()),'attributes' => array('tabindex' => '5'))) . '</li>';
                            }
                            ?>
                            <li class="last"><?php print l("Logout", "user/logout", array('attributes' => array('tabindex' => '5'),'query' => array('destination' => $_SERVER['REQUEST_URI']))); ?></li>
                        </ul>
                    <?php else : ?>
                        <ul class="secondary-menu links clearfix">
                            <li class="last">
                                <?php
                                if (!gd_security_is_single_sign_on()) {
                                    print l("Login", "user", array('attributes' => array('tabindex' => '5')));
                                }
                                ?>
                            </li>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    <?php
    $datasources = $page['variables']['datasources'];
    $dashboard = $page['variables']['dashboard'];
    $dashboards = $page['variables']['dashboards'];

    $active = NULL;
    foreach ($datasources as $d) {
        if ( isset($d->active) ) {
            $active = $d;
            break;
        }
    }
    ?>
    <div>
        <div id="gd-navbar" class="navbar navbar-default" role="navigation">
            <div class="nav">
                <div class="container">
                    <ul title="Dashboard List" id="dashboardList" class="nav navbar-nav" tabindex="5">
                        <?php
                        foreach ($dashboards as $dash) {
                            print '<li dash-id="' . $dash->nid . '" dash-name="' . $dash->title . '"' . ($dash->nid == $dashboard->nid ? ' class="active"' : '') . '>
                                    <a' . ($dash->nid != $dashboard->nid ? ' title="' . $dash->title . '" href="/dashboards?id=' . $dash->nid . '"' : '') . ' tabindex="5">' . $dash->title . '</a>
                                </li>';
                        }
                        ?>
                    </ul>
                    <ul title="Datasource List" id="datasourceList" class="nav navbar-nav navbar-right" tabindex="5">
                        <li class="divider-vertical"></li>
                        <li class="dropdown">
                            <a id="currentDatasource" title="<?php print $active->publicName ?>" tabindex="5" href="#" role="button" class="dropdown-toggle" data-toggle="dropdown">
                                <strong><?php print $active->publicName ?></strong>
                                <b class="caret"></b>
                            </a>
                            <ul class="dropdown-menu" role="menu" aria-labelledby="currentDatasource">
                                <?php
                                foreach ($datasources as $ds) {
                                    $anchor = '<a role="menuitem" title="' . $ds->publicName . '" tabindex="-1" href="#"' . (isset($ds->active) ? '' : ' ds-name="' . $ds->name . '"') . ' class="datasource-link">'
                                        . (isset($ds->active) ? '<span class="icon-hidden">Current Datasource</span><span class="glyphicon glyphicon-ok"></span> ' : '') . $ds->publicName .
                                        '</a>';
                                    $lineItem = '<li role="presentation">' . $anchor . '</li>';
                                    print $lineItem;
                                }
                                ?>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <script type="text/javascript">

            jQuery(document).ready(function() {
                jQuery('#datasourceList').focus(function () {
                    jQuery('#dashboardList li.dropdown').toggleClass('open', false);
                });
                jQuery('#currentDashboard').focus(function () {
                    jQuery('#datasourceList li.dropdown').toggleClass('open', false);
                });
                jQuery('#content').focus(function () {
                    jQuery('#dashboardList li.dropdown').toggleClass('open', false);
                    jQuery('#datasourceList li.dropdown').toggleClass('open', false);
                });
            });
        </script>
        <script type="text/javascript">
            (function () {
                jQuery('a.datasource-link').click(function (e) {
                    e.preventDefault();
                    if (jQuery(this).attr('ds-name')) {
                        location.href = '/dashboards?ds=' + jQuery(this).attr('ds-name');
                    }
                });
            })();
        </script>
        <script type="text/javascript">
            (function () {
                var dsWidth = jQuery('#datasourceList').width();
                var dbWidth = jQuery('#dashboardList').width();

                if (dsWidth + dbWidth > 1000) {
                    var children = jQuery('#dashboardList').children();
                    var overflow = jQuery('<li class="dropdown"></li>');
                    var active = jQuery('<a id="currentDashboard" class="dropdown-toggle" role="button" data-toggle="dropdown" href="#" tabindex="5"></a>');
                    active.append('<span class="glyphicon glyphicon-th-list"></span> ');
                    var strong = jQuery('<strong></strong>');
                    strong.text('<?php print isset($dashboard) ? addslashes($dashboard->title) : ''; ?>');
                    active.append(strong);
                    overflow.append(active);

                    var dropdown = jQuery('<ul class="dropdown-menu" role="menu" aria-labelledby="currentDashboard" style="max-height:260px;overflow-y:scroll;"></ul>');
                    overflow.append(dropdown);
                    jQuery.each(children, function (i, c) {
                        c = jQuery(c);
                        var lineItem = jQuery('<li role="presentation"></li>');
                        var anchor = jQuery('<a role="menuitem" tabindex="-1" tabindex="5"></a>');
                        anchor.attr('title', c.attr('dash-name'));
                        lineItem.append(anchor);

                        if (c.hasClass('active')) {
                            anchor.append('<span class="icon-hidden">Current Dashboard</span>');
                            anchor.append('<span class="glyphicon glyphicon-ok"></span> ');
                            anchor.attr('href', '#');
                        } else {
                            anchor.attr('href', '/dashboards?id=' + c.attr('dash-id'));
                        }
                        anchor.append(c.attr('dash-name'));

                        dropdown.append(lineItem);
                    });

                    jQuery('#dashboardList').empty();
                    jQuery('#dashboardList').append(overflow);

                    dsWidth = jQuery('#datasourceList').width();
                    dbWidth = jQuery('#dashboardList').width();
                    if (dsWidth + dbWidth > 1000) {
                        jQuery('#currentDashboard').text(jQuery('#currentDashboard').text().substr(0, 100) + '...');
                    }
                }
            })();
        </script>

        <div id="content" class="container" >
            <div class="element-invisible"><a id="main-content"></a></div>
            <div id="content-inside" class="inside">
                <div id="console" class="clearfix">
                    <?php
                    $messages = drupal_get_messages();
                    if(isset($messages['warning'][0])){?>
                        <div class="alert alert-block alert-danger">
                            <a class="close" href="#" data-dismiss="alert">&times;</a>
                            <?php print  $messages['warning'][0];?>
                        </div>
                    <?php }
                    ?>
                </div>
                <?php print render($page['content']); ?>
            </div>
        </div>
    </div>
    <!-- TODO : Footer tag issue on printing, investigate later  -->
    <div id="appendix" class="print-element">
        <h3>Appendix</h3>
    </div>
    <div id="footer" class="<?php print $classes; ?>">
        <div class="container">
            <div id="footer-bottom-left">
                <a tabindex="3000" class="footer-link" href="http://www.govdashboard.com/support" target="_blank">Support</a>
                <span class="footer-separator">|</span>
                <a tabindex="3000" class="footer-link" href="/accessibility/players" target="_blank">Viewers &
                    Players</a>
                <span class="footer-separator">|</span>
                <a tabindex="3000" class="footer-link" href="/accessibility/info" target="_blank">Accessibility</a>
            </div>
            <div id="footer-bottom-right">
                <a tabindex="3000" style="display: inline-block;" width="100%" height="100%"
                   href="http://www.govdashboard.com"
                   target="_blank"><img
                        src="<?php print base_path() . path_to_theme() . '/images/footer/govdashboard-power.png'; ?>"
                        border="0"
                        align="right" alt="Powered by GovDashboard"/></a>
            </div>
        </div>
    </div>
</div>

<?php print render($page['bottom']); ?>
