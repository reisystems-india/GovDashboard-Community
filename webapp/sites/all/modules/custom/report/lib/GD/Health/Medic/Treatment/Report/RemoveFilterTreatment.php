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

namespace GD\Health\Medic\Treatment\Report;

use GD\Health\Medic\Treatment\DefaultTreatment;

class RemoveFilterTreatment extends DefaultTreatment {

    public static function getName() {
        return 'Remove Filter';
    }

    public static function getDescription() {
        return 'Will remove the filter.';
    }

    public function apply ( $patients ) {

        if ( !is_array($patients) ) {
            $patients = array($patients);
        }

        foreach ( $patients as $patient ) {
            \LogHelper::log_info('Applying ReportConfigRemoveFilter treatment to: ' . $patient->reportNodeId);

            $node = node_load($patient->reportNodeId);

            $reportConfigText = get_node_field_value($node, 'field_report_conf', 0, 'value', FALSE);
            $reportConfig = isset($reportConfigText) ? json_decode($reportConfigText) : NULL;
            if (!isset($reportConfig)) {
                \LogHelper::log_info('Report configuration is EMPTY');
                return;
            }

            // check column configs
            if (!empty($reportConfig->model->filters)) {
                $preservedFilters = array();
                foreach ($reportConfig->model->filters as $key => $filter) {
                    if ( $patient->filter != $filter ) {
                        $preservedFilters[] = $filter;
                    }
                }
                $reportConfig->model->filters = $preservedFilters;

                $node->field_report_conf[$node->language][0]['value'] = json_encode($reportConfig);
                node_save($node);
            }
        }
    }

}
