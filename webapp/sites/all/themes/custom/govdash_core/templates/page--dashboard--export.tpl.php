<div id="page">

    <header id="header" role="banner">
        <div id="header-inside" class="container">
            <div id="header-inside-left">
                <?php if ($logo): ?>
                <a tabindex="2" href="<?php print check_url($front_page); ?>" title="<?php print $site_name; ?>"><img class="gd-logo" src="<?php print $logo; ?>" alt="<?php print $site_name; ?>" /></a>
                <?php endif; ?>
            </div>

            <div id="header-inside-right">
                <div id="search-area">
                    <?php print render($page['search_area']); ?>
                    <?php if ($user->uid) : ?>
                    <ul class="secondary-menu links clearfix">
                        <li class="first"><?php print l($user->firstname . ' ' . $user->lastname,'user/profile', array('attributes' => array('tabindex' => '3'))); ?></li>
                        <?php
                        if ( gd_account_user_is_admin() || gd_account_user_is_any_datasource_admin() ) {
                            if ( arg(0) == 'cp' ) {
                                print '<li>'.l("Dashboard Viewer","dashboards", array('attributes' => array('tabindex' => '4'))).'</li>';
                            } else {
                                print '<li>'.l("Control Panel","cp", array('attributes' => array('tabindex' => '4'))).'</li>';
                            }
                        } else {
                            print '<li>'.l("Dashboard Viewer","dashboards", array('attributes' => array('tabindex' => '4'))).'</li>';
                        }
                        ?>
                        <li class="last"><?php print l("Logout","user/logout",array('attributes' => array('tabindex' => '5'),'query' => array('destination' => $_SERVER['REQUEST_URI']))); ?></li>
                    </ul>
                    <?php else : ?>
                    <ul class="secondary-menu links clearfix">
                        <li class="last">
                        <?php
                        if ( !gd_security_is_single_sign_on() ) {
                            print l("Login","user", array('attributes' => array('tabindex' => '3')));
                        }
                        ?>
                        </li>
                    </ul>
                    <?php endif; ?>
                </div><!-- EOF: #search-area -->
                <div id="header-menu">
                    <div id="header-menu-inside" class="inside">
                        <?php print render($page['header_menu']); ?>
                    </div>
                    <div id="header-menu-smart" class="inside">
                        <?php print render($page['header_menu_smart']); ?>
                    </div>
                </div><!-- EOF: #header-menu -->
            </div>
        </div>
        <?php print render($page['header']); ?>

    </header>

    <div>

        <?php if ($is_front): ?>
        <div id="post-header">
            <div id="post-header-inside" class="inside">
                <div id="post-header-content" class="region-post-header">
                    <?php print render($page['post_header']); ?>
                </div>
            </div>
            <div id="post-header-toggle">
                <div id="toggle-inside">
                    <a href="#" class="toggle" tabindex="6"></a>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <div id="pre-content">
            <div id="pre-content-inside" class="inside">
                <?php print render($page['pre_content']); ?>
            </div>
        </div>
        <div id="content">
            <div class="element-invisible"><a name="main-content"></a></div>
            <div id="content-inside" class="inside">
                <?php if (theme_get_setting('breadcrumb_display')): print $breadcrumb; endif; ?>

                <?php if ($page['highlighted']): ?><div id="highlighted"><?php print render($page['highlighted']); ?></div><?php endif; ?>

                <?php if ($messages): ?>
                <div id="console" class="clearfix">
                    <?php print $messages; ?>
                </div>
                <?php endif; ?>


                <div id="console" class="clearfix">
                    <?php
                    $messages = drupal_get_messages();
                    if(isset($messages['warning'][0])){?>
                        <div class="messages warning">
                            <?php print  $messages['warning'][0];?>
                        </div>
                        <?php }
                    ?>
                </div>


                <?php if ($page['help']): ?>
                <div id="help">
                    <?php print render($page['help']); ?>
                </div>
                <?php endif; ?>

                <?php if ($action_links): ?>
                <ul class="action-links">
                    <?php print render($action_links); ?>
                </ul>
                <?php endif; ?>


                <!-- fix for the missing titles is Views -->
                <?php print render($title_prefix); ?>
                <?php if ( !$is_front && !empty($title) && ($_SERVER['REQUEST_URI']!='/cp') ): ?>
                <h1<?php print $title_attributes; ?>><?php print $title ?></h1>
                <?php endif; ?>
                <?php print render($title_suffix); ?>
                <!--end fix -->

                <?php if ($tabs): ?><?php print render($tabs); ?><?php endif; ?>

                <?php print render($page['content']); ?>

                <?php print $feed_icons; ?>

                <div id="sidebar">

                    <?php print render($page['sidebar_first']); ?>

                </div><!-- EOF: #sidebar -->
            </div>
            <!-- /#content-inside -->
        </div>
        <!-- /#content -->
    </div>
    <!-- /#main -->

    <?php //  TODO Move to region--footer.tpl.php ?>
    <footer id="footer" class="<?php print $classes; ?>">
        <div id="footer-bottom-inside">
            <div id="footer-bottom-left">
                <a tabindex="3000" class="footer-link" href="http://www.govdashboard.com/support" target="_blank">Support</a>
                <span class="footer-separator">|</span>
                <a tabindex="3000" class="footer-link" href="/accessibility/players" target="_blank">Viewers & Players</a>
                <span class="footer-separator">|</span>
                <a tabindex="3000" class="footer-link" href="/accessibility/info" target="_blank">Accessibility</a>
            </div>
            <div id="footer-bottom-right">
                <a tabindex="3000" style="display: inline-block;" width="100%" height="100%" href="http://www.govdashboard.com"
                   target="_blank"><img
                        src="<?php print base_path() . path_to_theme() . '/images/footer/govdashboard-power.png'; ?>" border="0"
                        align="right" alt="Powered by GovDashboard"/></a><br/>
            </div>
        </div>
    </footer>
</div><!-- /#page -->
<?php print render($page['bottom']); ?>
<div id="appendix" class="print-element">
    <h3>Appendix</h3>
</div>