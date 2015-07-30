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


class ArrayLogMessageSlicer extends AbstractLogMessageListener {

    public static $LOGGED_ELEMENTS__MAXIMUM = 50; // NULL - logging whole array

    public function log($level, &$message) {
        if (is_array($message) && isset(self::$LOGGED_ELEMENTS__MAXIMUM)) {
            $count = count($message);
            if ($count > self::$LOGGED_ELEMENTS__MAXIMUM) {
                $slice = array_slice($message, 0, self::$LOGGED_ELEMENTS__MAXIMUM);
                $slice[] = t(
                    '@deletedElementCount ELEMENTS WERE OMITTED TO SAVE LOG SPACE',
                    array('@deletedElementCount' => ($count - self::$LOGGED_ELEMENTS__MAXIMUM)));

                $message = $slice;
            }
        }
    }
}
