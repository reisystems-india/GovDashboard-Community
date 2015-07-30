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


class GD_ReportConfigFactory extends \AbstractFactory {
    private static $instance = NULL;
    private static $configList = NULL;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new GD_ReportConfigFactory();
        }

        return self::$instance;
    }

    public function getConfigById($reportId) {
        if (isset(self::$configList[$reportId])) {
            return self::$configList[$reportId];
        } else {
            return $this->getConfig(node_load($reportId));
        }
    }

    public function getConfig($report) {
        $id = null;
        if ( !empty($report->nid) ) {
            $id = $report->nid;
        } else if ( !empty($report->id) ) {
            $id = $report->id;
        }

        if (isset($id) && isset(self::$configList[$id])) {
            return self::$configList[$id];
        } else {
            $conf = null;
            if ( !empty($report->nid) && !is_null($report->nid) ) {
                $config = json_decode(trim(get_node_field_value($report,'field_report_conf')), true);
                $conf = new GD_ReportConfig($config);
                $conf->setNode($report);
                $conf->setDescription(get_node_field_value($report,'field_report_desc'));
                $conf->setTitle($report->title);
            } else {
                if (is_array($report->config)) {
                    $config = $report->config;
                    $conf = new GD_ReportConfig($config);
                    if (!empty($report->style)) {
                        $conf->setStyle($report->style);
                    } else {
                        $conf->setStyle('');
                    }
                    if (isset($report->title)) {
                        $conf->setTitle($report->title);
                    }
                } else {
                    $config = json_decode(trim($report->config), true);
                    $conf = new GD_ReportConfig($config['config']);
                    if ( !empty($report->style) ) {
                        $conf->setStyle($report->style);
                    } else {
                        $conf->setStyle('');
                    }
                }
            }

            if (isset($id)) {
                $conf->setId($id);
                self::$configList[$id] = $conf;
            }
            return $conf;
        }
    }

    protected function __construct() {
        self::$configList = array();
    }
}