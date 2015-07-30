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


class DataTypeUIMapping extends AbstractObject {

    public $datatype = NULL;
    public $isVisible = TRUE;

    public $parentDataType = NULL;
    public $isParentShownOnSelect = FALSE;
    public $isAutomaticallyExpanded = FALSE;

    public $isKeyCompatible = FALSE;
    public $isFormulaExpressionCompatible = FALSE;

    public function __construct(
            $datatype, $isVisible = TRUE, $parentDataType = NULL,
            $isParentShownOnSelect = FALSE, $isAutomaticallyExpanded = FALSE,
            $isKeyCompatible = FALSE, $isFormulaExpressionCompatible = FALSE) {
        parent::__construct();

        $this->datatype = $datatype;
        $this->isVisible = $isVisible;

        $this->parentDataType = $parentDataType;
        $this->isParentShownOnSelect = $isParentShownOnSelect;
        $this->isAutomaticallyExpanded = $isAutomaticallyExpanded;

        $this->isKeyCompatible = $isKeyCompatible;
        $this->isFormulaExpressionCompatible = $isFormulaExpressionCompatible;
    }
}


class DataTypeUIMetaData extends AbstractElementUIMetaData {

    public $isParentShownOnSelect = FALSE;
    public $isAutomaticallyExpanded = FALSE;
    public $isKeyCompatible = FALSE;
    public $isFormulaExpressionCompatible = FALSE;

    /**
     * @var DataTypeUIMetaData[] | null
     */
    public $subtypes = NULL;

    public function __construct() {
        parent::__construct();
        $this->isSelectable = FALSE;
    }

    public function findElement($elementName) {
        if ($this->name == $elementName) {
            return $this;
        }

        if (isset($this->subtypes)) {
            foreach ($this->subtypes as $subtype) {
                $element = $subtype->findElement($elementName);
                if (isset($element)) {
                    return $element;
                }
            }
        }

        return NULL;
    }

    public function registerElement(DataTypeUIMetaData $element) {
        $this->subtypes[] = $element;
    }
}