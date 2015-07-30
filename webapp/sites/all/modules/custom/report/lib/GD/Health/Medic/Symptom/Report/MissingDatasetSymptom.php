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

class MissingDatasetSymptom extends DefaultSymptom {

    private $affected;

    public static function getName() {
        return 'Report missing dataset';
    }

    public function getAffected() {
        if ( !isset($this->affected) ) {
            $this->scanReports();
        }
        return $this->affected;
    }

    private function scanReports () {
        $this->affected = array();

        $query = new \EntityFieldQuery();
        $query->entityCondition('entity_type', 'node');
        $query->propertyCondition('type', NODE_TYPE_REPORT);
        $query->addTag('DANGEROUS_ACCESS_CHECK_OPT_OUT');
        $result = $query->execute();
        $reportNids = isset($result['node']) ? array_keys($result['node']) : NULL;
        $reportNodes = node_load_multiple($reportNids);
        
        foreach ( $reportNodes as $node ) {
            $datasetName = get_node_field_value($node,'field_report_dataset_sysnames');
            if ( empty($datasetName) ) {
                $patient = array(
                    'info' => array(
                        'reportNodeId' => $node->nid,
                        'reportTitle' => $node->title,
                        'published' => $node->status,
                        'type' => $node->type,
                        'datasetName' => $datasetName
                    ),
                    'notes' => 'Dataset field is empty.'
                );

                $this->attachTreatment($patient);
                $this->affected[] = $patient;
                continue;
            }

            // lookup dataset
            $datasourceQuery = new \EntityFieldQuery();
            $datasourceQuery->entityCondition('entity_type', 'node');
            $datasourceQuery->propertyCondition('type', NODE_TYPE_DATASET);
            $datasourceQuery->addTag('DANGEROUS_ACCESS_CHECK_OPT_OUT');
            $datasourceQuery->fieldCondition('field_dataset_sysname', 'value', $datasetName);
            $datasourceQuery->fieldCondition('field_dataset_datasource', 'value', get_node_field_value($node,'field_report_datasource'));
            $datasourceEntities = $datasourceQuery->execute();
            $datasource_nids = isset($datasourceEntities['node']) ? array_keys($datasourceEntities['node']) : NULL;

            if (count($datasource_nids) != 1) {
                $patient = array(
                    'info' => array(
                        'reportNodeId' => $node->nid,
                        'reportTitle' => $node->title,
                        'published' => $node->status,
                        'type' => $node->type,
                        'datasetName' => $datasetName
                    ),
                    'notes' => 'Dataset does not exist.'
                );

                $this->attachTreatment($patient);
                $this->affected[] = $patient;
                continue;
            }
        }
    }
    
    private function attachTreatment ( &$diagnosis ) {
        $diagnosis['treatments'] = array();

        $diagnosis['treatments'][] = 'ReportDelete';
        $diagnosis['treatments'][] = 'ReportRepairDataset';
    }


}
