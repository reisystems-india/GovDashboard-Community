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


class Environment extends AbstractEnvironment {

    public static $LOCALE__DEFAULT = NULL;

    private $locale = NULL;

    protected function __construct() {
        parent::__construct();
        $this->locale = $this->prepareLocale();
    }

    /**
     * @static
     * @return Environment
     */
    public static function getInstance() {
        $instance = &drupal_static(__CLASS__ . '::' . __FUNCTION__);
        if (!isset($instance)) {
            $instance = new Environment();
        }

        return $instance;
    }

    public function getRootSectionName() {
        return 'Dashboard Platform';
    }

    protected function prepareLocale() {
        global $conf;
        global $language;

        $country = isset($conf['site_default_country']) ? trim($conf['site_default_country']) : '';
        if ($country === '') {
            $country = 'us';
            LogHelper::log_warn(t("Default country was not defined. Go to 'Configuration' | 'Regional and language' | 'Regional settings' to set default country"));
        }

        $localeSubTags = array('language' => $language->language, 'region' => $country);

        return Locale::composeLocale($localeSubTags);
    }

    public function getLocale() {
        return isset(self::$LOCALE__DEFAULT) ? self::$LOCALE__DEFAULT : $this->locale;
    }
}
