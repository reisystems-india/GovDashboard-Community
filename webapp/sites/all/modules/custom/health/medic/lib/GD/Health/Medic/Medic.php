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


namespace GD\Health\Medic;

use GD\Common\Pattern\AbstractObject;

class Medic extends AbstractObject {

    private $symptomConfigs = null;
    private $symptoms = null;

    private $treatmentConfigs = null;

    public function __construct () {
        $this->registerSymptoms();
        $this->registerTreatments();
    }

    public function getAffected ( $symptomId ) {
        if ( !$this->understandsSymptom($symptomId) ) {
            throw new \Exception('Unknown requested symptom: '.$symptomId);
        }
        $symptom = $this->getSymptom($symptomId);
        return $symptom->getAffected();
    }

    public function applyTreatment ( $patient, $treatmentName ) {
        if ( !$this->knowsTreatment($treatmentName) ) {
            throw new \Exception('Unknown requested treatment: '.$treatmentName);
        }
        $treatment = $this->getTreatment($treatmentName);
        $treatment->apply($patient);
    }

    protected function registerSymptoms() {
        $handlers = module_invoke_all('gd_health_medic_symptoms');
        if (empty($handlers)) {
            return;
        }
        $this->symptomConfigs = array();
        foreach ($handlers as $h) {
            $this->symptomConfigs[] = $h;
        }
    }

    public function getSymptom ( $id ) {
        $config = $this->symptomConfigs[$id];
        return new $config['className']();
    }

    public function getSymptoms() {
        if ( !$this->symptoms && $this->symptomConfigs ) {
            $this->symptoms = array();
            foreach ( $this->symptomConfigs as $config ) {
                $this->symptoms[] = new $config['className']();
            }
        }
        return $this->symptoms;
    }

    protected function registerTreatments() {
        $handlers = module_invoke_all('gd_health_medic_treatments');
        if (empty($handlers)) {
            return;
        }
        $this->treatmentConfigs = array();
        foreach ($handlers as $key => $h) {
            $this->treatmentConfigs[$key] = $h;
        }

        // sort by operation weight
        uasort($this->treatmentConfigs, function ($a, $b) {
            if ($a['weight'] == $b['weight']) {
                return 0;
            }
            return ($a['weight'] < $b['weight']) ? -1 : 1;
        });
    }

    public function getTreatment ( $name ) {
        $config = $this->treatmentConfigs[$name];
        return new $config['className']();
    }

    public function understandsSymptom ( $id ) {
        return isset($this->symptomConfigs[$id]);
    }

    public function knowsTreatment ( $name ) {
        return isset($this->treatmentConfigs[$name]);
    }

}