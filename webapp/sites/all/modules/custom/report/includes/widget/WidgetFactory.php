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


class GD_WidgetFactory {

    public static function getView ( GD_ReportConfig $ReportConfig, array $options = array() ) {
        $widget = 'GD_Widget'.ucwords($ReportConfig->getDisplayType());
        if ( class_exists($widget) ) {
            $w = new $widget($ReportConfig);
        } else {
            throw new Exception($widget.' does not exist.');
        }

        return $w->getView($options);
    }
}

 
