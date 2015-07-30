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

namespace GD\Health\Medic\Symptom\Report;

use GD\Health\Medic\Symptom\DefaultSymptom;

class MissingColumnNameSymptom extends DefaultSymptom {

    private $affected;

    public static function getName() {
        return 'Report column configs with missing column name.';
    }

    public function getAffected() {
        if ( !isset($this->affected) ) {
            $this->scanReportConfigs();
        }
        return $this->affected;
    }

    private function scanReportConfigs () {
        $this->affected = array();

        $query = new \EntityFieldQuery();
        $query->entityCondition('entity_type', 'node');
        $query->propertyCondition('type', NODE_TYPE_REPORT);
        $query->propertyCondition('status', NODE_PUBLISHED);
        $query->addTag('DANGEROUS_ACCESS_CHECK_OPT_OUT');
        $result = $query->execute();
        $reportNids = isset($result['node']) ? array_keys($result['node']) : NULL;
        $reportNodes = node_load_multiple($reportNids);
        
        foreach ( $reportNodes as $node ) {

            \LogHelper::log_info(t('Inspecting report @nid', array('@nid' => $node->nid)));

            $reportConfigText = get_node_field_value($node, 'field_report_conf', 0, 'value', FALSE);
            $reportConfig = isset($reportConfigText) ? json_decode($reportConfigText) : NULL;
            if (!isset($reportConfig)) {
                \LogHelper::log_info('Report configuration is EMPTY');
                continue;
            }

            // check columns
            if (!empty($reportConfig->model->columns)) {
                foreach ($reportConfig->model->columns as $old) {
                    if (!isset($old)) {
                        continue;
                    }
                    \LogHelper::log_debug(t('Inspecting report model column @old', array('@old' => $old)));
                    $result = $this->detectEmptyColumn($old);
                    if ($result) {
                        $patient = array(
                            'info' => array(
                                'reportNodeId' => $node->nid,
                                'reportTitle' => $node->title,
                                'columnName' => $old,
                                'published' => $node->status,
                                'configPath' => 'model/columns'
                            ),
                            'notes' => $result
                        );
                        $this->attachTreatment($patient);
                        $this->affected[] = $patient;
                    }
                }
            }

            // check column configs
            if (!empty($reportConfig->columnConfigs)) {
                foreach ($reportConfig->columnConfigs as $key => $value) {
                    $old = $value->columnId;
                     \LogHelper::log_debug(t('Inspecting report column config @old', array('@old' => $old)));
                    $result = $this->detectEmptyColumn($old);
                    if ($result) {
                        $patient = array(
                            'info' => array(
                                'reportNodeId' => $node->nid,
                                'reportTitle' => $node->title,
                                'columnName' => $old,
                                'published' => $node->status,
                                'configPath' => 'columnConfigs'
                            ),
                            'notes' => $result
                        );
                        $this->attachTreatment($patient);
                        $this->affected[] = $patient;
                    }
                }
            }

            // check column orders
            if (!empty($reportConfig->model->columnOrder)) {
                foreach ($reportConfig->model->columnOrder as $old) {
                    if (isset($old)) {
                         \LogHelper::log_debug(t('Inspecting report column sequence config @old', array('@old' => $old)));
                        $result = $this->detectEmptyColumn($old);
                        if ($result) {
                            $patient = array(
                                'info' => array(
                                    'reportNodeId' => $node->nid,
                                    'reportTitle' => $node->title,
                                    'columnName' => $old,
                                    'published' => $node->status,
                                    'configPath' => 'model/columnOrder'
                                ),
                                'notes' => $result
                            );
                            $this->attachTreatment($patient);
                            $this->affected[] = $patient;
                        }
                    }
                }
            }

            // check column sorts
            if (!empty($reportConfig->model->orderBy)) {
                foreach ($reportConfig->model->orderBy as $key => $value) {
                    $old = $value->column;
                     \LogHelper::log_debug(t('Inspecting report data sorting column @old', array('@old' => $old)));
                    $result = $this->detectEmptyColumn($old);
                    if ($result) {
                        $patient = array(
                            'info' => array(
                                'reportNodeId' => $node->nid,
                                'reportTitle' => $node->title,
                                'columnName' => $old,
                                'published' => $node->status,
                                'configPath' => 'model/orderBy'
                            ),
                            'notes' => $result
                        );
                        $this->attachTreatment($patient);
                        $this->affected[] = $patient;
                    }
                }
            }

            // check visual series
            if (!empty($reportConfig->visual->series)) {
                foreach ($reportConfig->visual->series as $old => $value) {
                     \LogHelper::log_debug(t('Inspecting report visual series column @old', array('@old' => $old)));
                    $result = $this->detectEmptyColumn($old);
                    if ($result) {
                        $patient = array(
                            'info' => array(
                                'reportNodeId' => $node->nid,
                                'reportTitle' => $node->title,
                                'columnName' => $old,
                                'published' => $node->status,
                                'configPath' => 'visual/series'
                            ),
                            'notes' => $result
                        );
                        $this->attachTreatment($patient);
                        $this->affected[] = $patient;
                    }
                }
            }

            // check traffic column
            if (!empty($reportConfig->visual->trafficColumn)) {
                $old = $reportConfig->visual->trafficColumn;
                 \LogHelper::log_debug(t('Inspecting report visual traffic column @old', array('@old' => $old)));
                $result = $this->detectEmptyColumn($old);
                if ($result) {
                    $patient = array(
                        'info' => array(
                            'reportNodeId' => $node->nid,
                            'reportTitle' => $node->title,
                            'columnName' => $old,
                            'published' => $node->status,
                            'configPath' => 'visual/trafficColumn'
                        ),
                        'notes' => $result
                    );
                    $this->attachTreatment($patient);
                    $this->affected[] = $patient;
                }
            }

            // check traffic columns (v2)
            if (!empty($reportConfig->visual->traffic)) {
                foreach ($reportConfig->visual->traffic as $key => $value) {
                    $old = $value->trafficColumn;
                    $result = $this->detectEmptyColumn($old);
                    if ($result) {
                        $patient = array(
                            'info' => array(
                                'reportNodeId' => $node->nid,
                                'reportTitle' => $node->title,
                                'columnName' => $old,
                                'published' => $node->status,
                                'configPath' => 'visual/traffic'
                            ),
                            'notes' => $result
                        );
                        $this->attachTreatment($patient);
                        $this->affected[] = $patient;
                    }
                }
            }

            // update filters
            if (!empty($reportConfig->model->filters)) {
                foreach ($reportConfig->model->filters as $key => $value) {
                    $old = $value->column;
                    $result = $this->detectEmptyColumn($old);
                    if ($result) {
                        $patient = array(
                            'info' => array(
                                'reportNodeId' => $node->nid,
                                'reportTitle' => $node->title,
                                'columnName' => $old,
                                'published' => $node->status,
                                'configPath' => 'model/filters'
                            ),
                            'notes' => $result
                        );
                        $this->attachTreatment($patient);
                        $this->affected[] = $patient;
                    }
                }
            }

            // update color column
            if (!empty($reportConfig->visual->useColumnDataForColor)) {
                $old = $reportConfig->visual->useColumnDataForColor;
                $result = $this->detectEmptyColumn($old);
                if ($result) {
                    $patient = array(
                        'info' => array(
                            'reportNodeId' => $node->nid,
                            'reportTitle' => $node->title,
                            'columnName' => $old,
                            'published' => $node->status,
                            'configPath' => 'visual/useColumnDataForColor'
                        ),
                        'notes' => $result
                    );
                    $this->attachTreatment($patient);
                    $this->affected[] = $patient;
                }
            }
        }
    }

    private function detectEmptyColumn ( $uiColumnName ) {
        if ( empty($uiColumnName) ) {
            return 'Empty column name.';
        } else {
            return null;
        }
    }
    
    private function attachTreatment ( &$diagnosis ) {
        $diagnosis['treatments'] = array();
        $diagnosis['treatments'][] = 'ReportConfigRemoveColumnConfig';
    }


}
