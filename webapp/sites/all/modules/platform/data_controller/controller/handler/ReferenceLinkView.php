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


class ReferenceLinkView extends AbstractObject {

    protected $primaryDatasetName = NULL;

    public function __construct($primaryDatasetName) {
        parent::__construct();

        $this->primaryDatasetName = $this->adjustDatasetName($primaryDatasetName);
    }

    protected function adjustDatasetName($datasetName) {
        return $datasetName;
    }

    public function generate(array $possiblyLinkableDatasetNames = NULL) {
        $adjustedPossiblyLinkableDatasetNames = NULL;
        if (isset($possiblyLinkableDatasetNames)) {
            foreach ($possiblyLinkableDatasetNames as $possiblyLinkableDatasetName) {
                $adjustedPossiblyLinkableDatasetName = $this->adjustDatasetName($possiblyLinkableDatasetName);
                // we should not try to link with itself
                if ($adjustedPossiblyLinkableDatasetName == $this->primaryDatasetName) {
                    continue;
                }

                ArrayHelper::addUniqueValue($adjustedPossiblyLinkableDatasetNames, $adjustedPossiblyLinkableDatasetName);
            }
        }

        // preparing reference paths for all datasets which we need to test for possible linkage
        $referencePaths = NULL;
        if (isset($adjustedPossiblyLinkableDatasetNames)) {
            foreach ($adjustedPossiblyLinkableDatasetNames as $adjustedPossiblyLinkableDatasetName) {
                // column name should not be provided because we just try to link with the dataset
                $referencePath = ReferencePathHelper::assembleReference($adjustedPossiblyLinkableDatasetName, NULL);

                $referencePaths[$referencePath] = FALSE; // FALSE - if we cannot find link it is ok. Not everything could be connected
            }
        }

        $linkBuilder = new ReferenceLinkBuilder();
        $link = $linkBuilder->prepareReferenceBranches($this->primaryDatasetName, $referencePaths);

        return $link;
    }
}
