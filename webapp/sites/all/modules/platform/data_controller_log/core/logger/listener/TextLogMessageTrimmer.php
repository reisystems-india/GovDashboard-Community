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


class TextLogMessageTrimmer extends AbstractLogMessageListener {

    public static $LOGGED_TEXT_LENGTH__MAXIMUM = 1024; // NULL - logging string

    public function log($level, &$message) {
        if (is_string($message) && isset(self::$LOGGED_TEXT_LENGTH__MAXIMUM)) {
            $length = strlen($message);
            if ($length > self::$LOGGED_TEXT_LENGTH__MAXIMUM) {
                $message = substr($message, 0, self::$LOGGED_TEXT_LENGTH__MAXIMUM)
                    . t(' ... @trimmedCharacterLength more character(s)', array('@trimmedCharacterLength' => ($length - self::$LOGGED_TEXT_LENGTH__MAXIMUM)));
            }
        }
    }
}
