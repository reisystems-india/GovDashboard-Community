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


abstract class AbstractMetaModelLoader extends AbstractObject implements MetaModelLoader {

    private $name = NULL;

    protected function prepareName() {
        return get_class($this);
    }

    final public function getName() {
        if (!isset($this->name)) {
            $this->name = $this->prepareName();
        }

        return $this->name;
    }

    public function prepare(AbstractMetaModel $metamodel) {}

    public function load(AbstractMetaModel $metamodel, array $filters = NULL) {}

    public function finalize(AbstractMetaModel $metamodel) {}

    protected function isMetaDataAcceptable(AbstractMetaData $metadata, array $filters = NULL) {
        $classname = get_class($metadata);
        if (isset($filters[$classname])) {
            foreach ($filters[$classname] as $propertyName => $filterValues) {
                if (isset($metadata->$propertyName)) {
                    $propertyValue = $metadata->$propertyName;
                    if (!in_array($propertyValue, $filterValues)) {
                        return FALSE;
                    }
                }
            }
        }

        return TRUE;
    }
}
