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


abstract class AbstractEnvironment extends AbstractSingleton {

    abstract public function getRootSectionName();

    public function getConfigurationSection($sectionName) {
        global $conf;

        $rootSectionName = $this->getRootSectionName();

        return isset($conf[$rootSectionName][$sectionName]) ? $conf[$rootSectionName][$sectionName] : NULL;
    }

    protected function getRegisteredFlags() {
        return NULL;
    }

    public function getConfigurationFlag($flagName) {
        $flags = $this->getConfigurationSection('Flag');

        // returning configuration value
        if (isset($flags[$flagName])) {
            return $flags[$flagName];
        }

        // returning default value
        $registeredFlags = $this->getRegisteredFlags();
        if (isset($registeredFlags[$flagName])) {
            return $registeredFlags[$flagName];
        }

        throw new IllegalStateException(t(
            'A flag has not been defined in settings.php ($conf[%rootSectionName][%sectionName][%propertyName])',
            array(
                '%rootSectionName' => $this->getRootSectionName(),
                '%sectionName' => 'Flag',
                '%propertyName' => $flagName)));

    }
}
