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

class InvalidHiddenFilterSymptom extends DefaultSymptom {

    private $affected;

    public static function getName() {
        return 'Pre-applied report filters that are invalid.';
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

            // loop through filters
            if (!empty($reportConfig->model->filters)) {
                foreach ($reportConfig->model->filters as $key => $value) {
                    $result = $this->detectInvalidFilter($value);
                    if ($result) {
                        $patient = array(
                            'info' => array(
                                'reportNodeId' => $node->nid,
                                'reportTitle' => $node->title,
                                'filter' => $value,
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
        }
    }

    private function detectInvalidFilter ( $filter ) {
        if ( !$filter->exposed && ( !$filter->operator || !$filter->value ) ) {

            if ( $filter->operator == 'empty.not' ) {
                return null;
            }

            return 'Flagged as pre-applied but missing operator and/or value.';

        } else {
            return null;
        }
    }
    
    private function attachTreatment ( &$diagnosis ) {
        $diagnosis['treatments'] = array();
        $diagnosis['treatments'][] = 'ReportConfigRemoveFilter';
    }


}
