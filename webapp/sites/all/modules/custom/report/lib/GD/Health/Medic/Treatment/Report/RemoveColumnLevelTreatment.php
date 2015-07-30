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

class RemoveColumnLevelTreatment extends DefaultTreatment {

    public static function getName() {
        return 'Remove column level definition';
    }

    public static function getDescription() {
        return 'Will split up column name, remove the level definition, and put it back together.';
    }

    public function apply ( $patients ) {
        if ( !is_array($patients) ) {
            $patients = array($patients);
        }

        foreach ( $patients as $patient ) {
            \LogHelper::log_info('Applying ReportConfigRemoveColumnLevel treatment to: ' . $patient->reportNodeId);
        }
    }

}
