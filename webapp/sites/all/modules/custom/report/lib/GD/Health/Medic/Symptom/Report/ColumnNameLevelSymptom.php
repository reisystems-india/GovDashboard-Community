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

class ColumnNameLevelSymptom extends DefaultSymptom {

    private $affected;

    public static function getName() {
        return 'Report columns with level definition.';
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
                    \LogHelper::log_info(t('Inspecting report model column @old', array('@old' => $old)));
                    $result = $this->detectColumnsWithLevelDefinition($old);
                    if (isset($result)) {
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
                     \LogHelper::log_info(t('Inspecting report column config @old', array('@old' => $old)));
                    $result = $this->detectColumnsWithLevelDefinition($old);
                    if (isset($result)) {
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
                         \LogHelper::log_info(t('Inspecting report column sequence config @old', array('@old' => $old)));
                        $result = $this->detectColumnsWithLevelDefinition($old);
                        if (isset($result)) {
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
                     \LogHelper::log_info(t('Inspecting report data sorting column @old', array('@old' => $old)));
                    $result = $this->detectColumnsWithLevelDefinition($old);
                    if (isset($result)) {
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
                     \LogHelper::log_info(t('Inspecting report visual series column @old', array('@old' => $old)));
                    $result = $this->detectColumnsWithLevelDefinition($old);
                    if (isset($result)) {
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
                 \LogHelper::log_info(t('Inspecting report visual traffic column @old', array('@old' => $old)));
                $result = $this->detectColumnsWithLevelDefinition($old);
                if (isset($result)) {
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
                    $result = $this->detectColumnsWithLevelDefinition($old);
                    if (isset($result)) {
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
                    $result = $this->detectColumnsWithLevelDefinition($old);
                    if (isset($result)) {
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
                $result = $this->detectColumnsWithLevelDefinition($old);
                if (isset($result)) {
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

    private function detectColumnsWithLevelDefinition ( $uiColumnName ) {

        list($namespace, $columnName) = \AbstractDatasetUIMetaDataGenerator::splitElementUIMetaDataName($uiColumnName);
        if (!isset($namespace)) {
            throw new \IllegalArgumentException(t(
                'Name space has not been defined for the column name: @columnName',
                array('@columnName' => $uiColumnName)));
        }

        $result = null;

        /**
        if ( strpos(trim($uiColumnName),'.date.') !== false ) {
            return null;
        }

        if ( strpos(trim($uiColumnName),'.year.') !== false ) {
            return null;
        }

        if ( strpos(trim($uiColumnName),'.month.') !== false ) {
            return null;
        }

        if ( strpos(trim($uiColumnName),'.quarter.') !== false ) {
            return null;
        }
        **/

        if ($namespace == \AbstractAttributeUIMetaData::NAME_SPACE) {
            $elements = explode(\ParameterNameHelper::DELIMITER__DEFAULT, $columnName);

            list($dimensionReference, $dimensionName) = \ReferencePathHelper::splitReference($elements[0]);
            $levelName = isset($elements[1]) ? $elements[1] : NULL;
            $leafName = isset($elements[2]) ? $elements[2] : NULL;

            if (isset($levelName)) {
                if (isset($leafName) && ($dimensionName != $levelName)) {
                    $result = 'Unsupported level definition in the column name for attribute.';
                }
            }
        } else if ($namespace == \AbstractMeasureUIMetaData::NAME_SPACE) {
            // changing name for distinct count measure
            list($measureReference, $measureName) = \ReferencePathHelper::splitReference($columnName);
            $parts = explode(\StarSchemaNamingConvention::MEASURE_NAME_DELIMITER, $measureName);
            if (isset($parts[2]) && ($parts[2] == \StarSchemaNamingConvention::$MEASURE_NAME_SUFFIX__DISTINCT_COUNT)) {
                if ($parts[0] != $parts[1]) {
                    if ($parts[1] == 'date') {
                        // it is distinct count measure for date dimension
                    }
                    else {
                        $result = 'Unsupported level definition in the column name for attribute\'s distinct count measure';
                    }
                }
            }
        }

        return $result;
    }
    
    private function attachTreatment ( &$diagnosis ) {
        $diagnosis['treatments'] = array();
        $diagnosis['treatments'][] = 'ReportConfigRemoveColumnLevel';

        $reportNode = node_load($diagnosis['info']['reportNodeId']);
        // lookup datasource
        $datasourceQuery = new \EntityFieldQuery();
        $datasourceQuery->entityCondition('entity_type', 'node');
        $datasourceQuery->propertyCondition('type', NODE_TYPE_DATAMART);
        $datasourceQuery->addTag('DANGEROUS_ACCESS_CHECK_OPT_OUT');
        $datasourceQuery->fieldCondition('field_datamart_sysname', 'value', get_node_field_value($reportNode, 'field_report_datasource', 0, 'value', FALSE));
        $datasourceEntities = $datasourceQuery->execute();
        $datasource_nids = isset($datasourceEntities['node']) ? array_keys($datasourceEntities['node']) : NULL;
        if (count($datasource_nids) != 1) {
            $diagnosis['treatments'][] = 'ReportDelete';
            $diagnosis['notes'] .= ' Datasource could not be found.';
        } else {
            $datamartNode = node_load($datasource_nids[0]);
            if ( $datamartNode->status == NODE_PUBLISHED ) {
                $diagnosis['notes'] .= ' Datasource is published.';
            } else {
                $diagnosis['treatments'][] = 'ReportDelete';
                $diagnosis['notes'] .= ' Datasource is not published.';
            }

        }


    }


}
