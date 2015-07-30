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


class ExceptionLogMessageFormatter extends AbstractLogMessageListener {

    public static $FORMATTER_OPTION__COMPOSITE_ELEMENT_KEY_VALUE_DELIMITER = ' => ';

    public static $ARGUMENT_ARRAY_INDEXED__VISIBLE_ELEMENT_MAXIMUM = 25;

    protected function logElementValue($value) {
        if (!isset($value)) {
            return 'NULL';
        }

        $formattedValue = NULL;
        if (is_object($value)) {
            $formattedValue = get_class($value) . '{...}';
        }
        elseif (is_array($value)) {
            $formattedValue = '[' . count($value) . ' element(s)]';
        }
        else {
            $formattedValue = $value;
        }

        return $formattedValue;
    }

    public function log($level, &$message) {
        if ($message instanceof Exception) {
            $exception = $message;

            $backtrace = $exception->getTrace();
            // Add the line throwing the exception to the backtrace.
            array_unshift(
                $backtrace,
                array(
                    'message' => ExceptionHelper::getExceptionMessage($exception),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine()));

            // resolving an issue that exception printing could consume NN MB of log space
            foreach ($backtrace as &$trace) {
                if (!isset($trace['args'])) {
                    continue;
                }

                $updatedArgs = NULL;
                foreach ($trace['args'] as $argKey => $argValue) {
                    $isObject = is_object($argValue);
                    if (is_array($argValue) || $isObject) {
                        $value = $isObject ? get_object_vars($argValue) : $argValue;

                        $count = count($value);
                        $max = min(self::$ARGUMENT_ARRAY_INDEXED__VISIBLE_ELEMENT_MAXIMUM, $count);

                        $convertedValue = '';

                        $index = 0;
                        foreach ($value as $k => $v) {
                            if ($index >= $max) {
                                break;
                            }

                            if ($convertedValue != '') {
                                $convertedValue .= ', ';
                            }
                            if (is_int($k) && ($k == $index)) {
                                $convertedValue .= $this->logElementValue($v);
                            }
                            else {
                                $convertedValue .= $k . self::$FORMATTER_OPTION__COMPOSITE_ELEMENT_KEY_VALUE_DELIMITER . $this->logElementValue($v);
                            }

                            $index++;
                        }
                        // checking if we skip some of the elements
                        if ($count > $max) {
                            $convertedValue .= ', ... ' . ($count - $max) . ' more ' . ($isObject ? 'property(-ies)' : 'element(s)');
                        }

                        if ($isObject) {
                            $s = get_class($argValue) . '{';
                            if ($convertedValue == '') {
                                $s .= '...';
                            }
                            $s .= '}';
                            $argValue = $s;
                        }
                        else {
                            $argValue = '[' . $convertedValue . ']';
                        }
                    }

                    $updatedArgs[$argKey] = $argValue;
                }
                $trace['args'] = $updatedArgs;
            }
            unset($trace);

            $message = $backtrace;
        }
    }
}
