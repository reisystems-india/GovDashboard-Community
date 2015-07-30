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


class LogHelper extends AbstractFactory {

    const LEVEL_DEBUG = 'Debug';
    const LEVEL_INFO = 'Info';
    const LEVEL_NOTICE = 'Notice';
    const LEVEL_WARNING = 'Warning';
    const LEVEL_ERROR = 'Error';
    const LEVEL_CRITICAL = 'Critical';
    const LEVEL_ALERT = 'Alert';
    const LEVEL_EMERGENCY = 'Emergency';

    protected $messageListeners = NULL;

    protected function __construct() {
        parent::__construct();

        $this->initializeMessageListeners();
    }

    /**
     * @static
     * @return LogHelper
     */
    protected static function getInstance() {
        $instance = &drupal_static(__CLASS__ . '::' . __FUNCTION__);
        if (!isset($instance)) {
            $instance = new LogHelper();
        }

        return $instance;
    }

    protected function initializeMessageListeners() {
        $this->messageListeners = array();

        $listenerConfigurations = module_invoke_all('dp_log_message_listener');
        foreach ($listenerConfigurations as $listenerConfiguration) {
            $classname = $listenerConfiguration['classname'];

            // recommended priority value:
            //     -10000 ..  -501: collect info
            //       -500 ..    -1: message modification
            //          0 .. 10000: logging into storage
            $priority = isset($listenerConfiguration['priority']) ? $listenerConfiguration['priority'] : 0;

            $this->messageListeners[$priority][] = new $classname();
        }

        ksort($this->messageListeners);
    }

    public static function formatExecutionTime($timeStart) {
        $SCALE_INTERVAL = 10;

        $timeEnd = 1000 * (microtime(TRUE) - $timeStart);
        $output = "$timeEnd ms";

        if ($timeEnd > $SCALE_INTERVAL) {
            $timeScale = $SCALE_INTERVAL;
            while ($timeEnd > ($newTimeScale = ($timeScale * $SCALE_INTERVAL))) {
                $timeScale = $newTimeScale;
            }

            $output .= " (>$timeScale ms)";
        }

        return $output;
    }

    public static function log_emergency($message) {
        LogHelper::getInstance()->log(self::LEVEL_EMERGENCY, $message);
    }

    public static function log_alert($message) {
        LogHelper::getInstance()->log(self::LEVEL_ALERT, $message);
    }

    public static function log_critical($message) {
        LogHelper::getInstance()->log(self::LEVEL_CRITICAL, $message);
    }

    public static function log_error($message) {
        LogHelper::getInstance()->log(self::LEVEL_ERROR, $message);
    }

    public static function log_warn($message) {
        LogHelper::getInstance()->log(self::LEVEL_WARNING, $message);
    }

    public static function log_notice($message) {
        LogHelper::getInstance()->log(self::LEVEL_NOTICE, $message);
    }

    public static function log_info($message) {
        LogHelper::getInstance()->log(self::LEVEL_INFO, $message);
    }

    public static function log_debug($message) {
        LogHelper::getInstance()->log(self::LEVEL_DEBUG, $message);
    }

    protected function log($level, $message) {
        $isOriginalMessagePresent = isset($message);

        foreach ($this->messageListeners as $listeners) {
            foreach ($listeners as $listener) {
                $listener->log($level, $message);

                // checking if the message was eliminated
                if ($isOriginalMessagePresent && !isset($message)) {
                    return;
                }
            }
        }
    }
}
