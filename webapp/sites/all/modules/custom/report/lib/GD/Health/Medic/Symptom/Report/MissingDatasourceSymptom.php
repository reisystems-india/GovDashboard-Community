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

class MissingDatasourceSymptom extends DefaultSymptom {

    private $affected;

    public static function getName() {
        return 'Report missing datasource';
    }

    public function getAffected() {
        if ( !isset($this->affected) ) {
            $this->scanForOrphanedReports();
        }
        return $this->affected;
    }

    private function scanForOrphanedReports () {
        $this->affected = array();

        $query = new \EntityFieldQuery();
        $query->entityCondition('entity_type', 'node');
        $query->propertyCondition('type', NODE_TYPE_REPORT);
        $query->addTag('DANGEROUS_ACCESS_CHECK_OPT_OUT');
        $result = $query->execute();
        $reportNids = isset($result['node']) ? array_keys($result['node']) : NULL;
        $reportNodes = node_load_multiple($reportNids);
        
        foreach ( $reportNodes as $node ) {
            $datasourceName = trim(get_node_field_value($node,'field_report_datasource'));
            if ( empty($datasourceName) ) {
                $patient = array(
                    'info' => array(
                        'reportNodeId' => $node->nid,
                        'reportTitle' => $node->title,
                        'published' => $node->status,
                        'type' => $node->type,
                        'datasourceName' => $datasourceName
                    ),
                    'notes' => 'Datasource is empty.'
                );

                $this->attachTreatment($patient);
                $this->affected[] = $patient;
                continue;
            }

            // lookup datasource
            $datasourceQuery = new \EntityFieldQuery();
            $datasourceQuery->entityCondition('entity_type', 'node');
            $datasourceQuery->propertyCondition('type', NODE_TYPE_DATAMART);
            $datasourceQuery->addTag('DANGEROUS_ACCESS_CHECK_OPT_OUT');
            $datasourceQuery->fieldCondition('field_datamart_sysname', 'value', $datasourceName);
            $datasourceEntities = $datasourceQuery->execute();
            $datasource_nids = isset($datasourceEntities['node']) ? array_keys($datasourceEntities['node']) : NULL;

            if (count($datasource_nids) != 1) {
                $patient = array(
                    'info' => array(
                        'reportNodeId' => $node->nid,
                        'reportTitle' => $node->title,
                        'published' => $node->status,
                        'type' => $node->type,
                        'datasourceName' => $datasourceName
                    ),
                    'notes' => 'Datasource does not exist.'
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

    }


}
