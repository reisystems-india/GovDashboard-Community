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

namespace GD\Security\Authentication\Factory;

use GD\Security\Authentication\Authentication;
use GD\Security\Authentication\Adapter\OAuthAdapter;

class OAuthAuthenticationFactory extends AbstractAuthenticationFactory {
    protected static $authentication;
    protected static $instance;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new OAuthAuthenticationFactory();
        }

        return self::$instance;
    }

    public function getAuthentication() {
        if (!isset(self::$authentication)) {
            self::$authentication = new Authentication(new OAuthAdapter());
        }

        return self::$authentication;
    }

}