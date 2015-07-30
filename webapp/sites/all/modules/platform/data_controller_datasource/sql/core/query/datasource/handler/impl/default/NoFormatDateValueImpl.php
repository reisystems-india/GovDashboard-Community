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


class NoFormatDateValueImpl extends AbstractFormatDateValueImpl {

    protected function adjustFormat($format, $acceptTZ) {
        $isFormatValid = TRUE;

        // validating provided mask
        $year = $month = $day = $hour = $hour24 = $minute = $second = $period = $timezone = FALSE;
        for ($i = 0, $len = strlen($format); $i < $len; $i++) {
            $specifier = $format[$i];

            switch ($specifier) {
                case 'Y':
                    if ($month || $day || $hour || $hour24 || $minute || $second || $period || $timezone) {
                        $isFormatValid = FALSE;
                    }
                    else {
                        $year = TRUE;
                    }
                    break;
                case 'm':
                    if (!$year || $day || $hour || $hour24 || $minute || $second || $period || $timezone) {
                        $isFormatValid = FALSE;
                    }
                    else {
                        $month = TRUE;
                    }
                    break;
                case 'd':
                    if (!$year || !$month || $hour || $hour24 || $minute || $second || $period || $timezone) {
                        $isFormatValid = FALSE;
                    }
                    else {
                        $day = TRUE;
                    }
                    break;
                case 'h':
                    if (!$year || !$month || !$day || $hour24 || $minute || $second || $period || $timezone) {
                        $isFormatValid = FALSE;
                    }
                    else {
                        $hour = TRUE;
                    }
                    break;
                case 'H':
                    if (!$year || !$month || !$day || $hour || $minute || $second || $period || $timezone) {
                        $isFormatValid = FALSE;
                    }
                    else {
                        $hour24 = TRUE;
                    }
                    break;
                case 'i':
                    if (!$year || !$month || !$day || (!$hour && !$hour24) || $second || $period || $timezone) {
                        $isFormatValid = FALSE;
                    }
                    else {
                        $minute = TRUE;
                    }
                    break;
                case 's':
                    if (!$year || !$month || !$day || (!$hour && !$hour24) || !$minute || $period || $timezone) {
                        $isFormatValid = FALSE;
                    }
                    else {
                        $second = TRUE;
                    }
                    break;
                case 'a':
                    if (!$year || !$month || !$day || (!$hour && !$hour24) || !$minute || !$second || $timezone) {
                        $isFormatValid = FALSE;
                    }
                    else {
                        $period = TRUE;
                    }
                    break;
                case '0':
                    if (!$year || !$month || !$day || (!$hour && !$hour24) || !$minute || !$second || !$period) {
                        $isFormatValid = FALSE;
                    }
                    else {
                        $timezone = TRUE;
                    }
                    break;
            }

            if (!$isFormatValid) {
                throw new UnsupportedOperationException(t(
                    "Unsupported format for value of DATETIME / DATE / TIME data type: %format. Only 'year-month-day[ hour:minute:second[ period[timezone]]]' is supported",
                    array('%format' => $format)));
            }
        }

        return $format;
    }

    public function formatStringToDateImpl(DataSourceHandler $handler, $formattedValue, $adjustedFormat, $datatype) {
        return $formattedValue;
    }
}
