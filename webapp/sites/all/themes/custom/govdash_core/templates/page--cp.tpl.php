<script type="text/javascript">
    !function() {
        $('#skip-link').find('a').attr('href', '#gd-page').click(function() { $("#gd-page").focus(); });
    }();
</script>
<div id="gd-page">
    <div id="gd-nav">

        <div id="gd-navmain" class="container">
            <div class="row">
                <div class="col-md-4">
                    <?php if ($logo): ?>
                    <a tabindex="2" href="<?php print check_url($front_page); ?>" title="<?php print $site_name; ?>"><img class="gd-logo" id="gd-logo" src="<?php print $logo; ?>" alt="<?php print $site_name; ?>" /></a>
                    <?php endif; ?>
                </div>
                <div class="col-md-8">
                    <?php if ($logged_in) : ?>
                    <ul class="secondary-menu menulist pull-right">
                        <li class="first"><?php print l($user->firstname . ' ' . $user->lastname,'user/profile', array('attributes' => array('tabindex' => '3'))); ?></li>
                        <?php
                        if ( gd_account_user_is_admin() || gd_account_user_is_any_datasource_admin() ) {
                            if ( arg(0) == 'cp' ) {
                                print '<li>'.l("Dashboard Viewer","dashboards", array('query'=>array('ds'=>gd_datasource_find_active()),'attributes' => array('tabindex' => '4'))).'</li>';
                            } else {
                                print '<li>'.l("Control Panel","cp", array('attributes' => array('tabindex' => '4'))).'</li>';
                            }
                        } else {
                            print '<li>'.l("Dashboard Viewer","dashboards", array('attributes' => array('tabindex' => '4'))).'</li>';
                        }
                        ?>
                        <li class="last"><?php print l("Logout","user/logout",array('attributes' => array('tabindex' => '5'))); ?></li>
                    </ul>
                    <?php else : ?>
                    <ul class="menu pull-right">
                        <li class="last">
                            <?php
                            if ( !gd_security_is_single_sign_on() ) {
                                print l("Login","user", array('attributes' => array('tabindex' => '3')));
                            }
                            ?>
                        </li>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div id="gd-navbar" class="navbar navbar-default" role="navigation">
            <div class="nav">
                <div class="container">
                </div>
            </div>
        </div>

    </div>

    <div id="gd-header" class="container"></div>

    <div id="gd-body" class="container">
        <div class="element-invisible"><a name="main-content"></a></div>
        <?php print render($page['content']); ?>
    </div>

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

</div>
<div id="appendix" class="print-element">
    <h3>Appendix</h3>
</div>
