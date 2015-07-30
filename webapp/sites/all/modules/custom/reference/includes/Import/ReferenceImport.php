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


use \GD\Sync\Import;

class ReferenceImport extends Import\AbstractEntityImport {

    protected $datasets;

    protected function create(Import\ImportStream $stream, Import\ImportContext $context) {
        $references = $stream->get('references');
        if (empty($references)) {
            return;
        }

        $this->datasets = $stream->get('datasets');
        if (empty($this->datasets)) {
            throw new Exception('Missing datasets for references.');
        }

        foreach ( $references as $i => $reference ) {

            // rename reference point datasets
            foreach ( $reference->referencePoints as $referencePoint ) {
                foreach ( $this->datasets as $dataset ) {
                    if ( isset($dataset->uuid) && $dataset->uuid == $referencePoint->dataset ) {
                        $referencePoint->dataset = $dataset->name;
                    }
                }
            }

            $node = $this->createReference($reference);

            if ( !empty($node->nid ) ) {
                $references[$i] = $node;
            } else {
                throw new Exception('Reference node creation failed');
            }
        }

        $stream->set('references',$references);
    }

    protected function update(Import\ImportStream $stream, Import\ImportContext $context) {
        $references = $stream->get('references');
        if (empty($references)) {
            return;
        }

        $metamodel = data_controller_get_metamodel();
        $this->datasets = $metamodel->datasets;

        foreach ( $references as $referenceKey => $reference ) {

            // rename reference point datasets
            foreach ( $reference->referencePoints as $referencePoint ) {
                foreach ( $this->datasets as $dataset ) {
                    if ( isset($dataset->uuid) && $dataset->uuid == $referencePoint->dataset ) {
                        $referencePoint->dataset = $dataset->name;
                    }
                }
            }

            $existingReferenceNode = $this->findExistingReference($reference);
            if ( !$existingReferenceNode ) {
                // create
                $node = $this->createReference($reference);
                if ( !empty($node->nid ) ) {
                    $references[$referenceKey] = $node;
                } else {
                    throw new Exception('Reference node creation failed');
                }
            } else {

                $existingReferenceNode->title = $reference->title;

                // really, just update columns
                $existingReferencePointNids = get_node_field_node_ref($existingReferenceNode,'field_reference_point',null);
                foreach ( $existingReferencePointNids as $existingReferencePointNid ) {
                    $existingReferencePointNode = node_load($existingReferencePointNid);
                    foreach ( $reference->referencePoints as $referencePoint ) {
                        if ( $referencePoint->dataset == get_node_field_value($existingReferencePointNode,'field_ref_point_dataset_sysname') ) {
                            $existingReferencePointNode->field_ref_point_column_sysname[$existingReferencePointNode->language] = array();
                            foreach ( $referencePoint->columns as $column ) {
                                $existingReferencePointNode->field_ref_point_column_sysname[$existingReferencePointNode->language][] = array('value' => $column);
                            }
                        }
                    }
                    node_save($existingReferencePointNode);
                }

                node_save($existingReferenceNode);

                $references[$referenceKey] = $existingReferenceNode;
            }
        }

        $stream->set('references',$references);
    }

    protected function createReference ( $reference ) {
        $node = new stdClass();
        $node->type = NODE_TYPE_REFERENCE;
        $node->language = LANGUAGE_NONE;
        $node->status = NODE_PUBLISHED;
        node_object_prepare($node);

        $node->title = $reference->title;
        $node->field_reference_sysname[$node->language][0]['value'] = GD_NamingConvention::generateReferenceName();

        $node->field_reference_point[$node->language] = array();
        foreach ( $reference->referencePoints as $referencePoint ) {
            $referencePointNode = $this->createReferencePoint($referencePoint);
            if ( empty($referencePointNode->nid ) ) {
                throw new Exception('Reference Point node creation failed');
            }
            $node->field_reference_point[$node->language][] = array('nid'=>$referencePointNode->nid);
        }

        node_save($node);
        return $node;
    }

    protected function createReferencePoint ( $referencePoint ) {
        $node = new stdClass();
        $node->type = NODE_TYPE_REFERENCE_POINT;
        $node->language = LANGUAGE_NONE;
        $node->status = NODE_PUBLISHED;
        node_object_prepare($node);

        $node->title = $referencePoint->title;
        $node->originalNid = $referencePoint->id;

        $node->field_ref_point_dataset_sysname[$node->language][0]['value'] = $referencePoint->dataset;

        // update column sysname to reflect new dataset name
        foreach ( $referencePoint->columns as $ref_point_column ) {
            $node->field_ref_point_column_sysname[$node->language][] = array('value' => $ref_point_column);
        }

        // create it
        node_save($node);
        return $node;
    }

    /**
     * Find existing reference via datasets
     *
     * @param $reference
     * @return null
     * @throws Exception
     */
    protected function findExistingReference ( $reference ) {

        $referenceCandidateNodes = (array) gd_reference_get_references(LOAD_ENTITY);

        // find references with exact same reference points
        $matchedReferences = array();
        foreach ( $referenceCandidateNodes as $referenceCandidateNode ) {
            $match = false;
            // don't go any further if reference point counts don't match
            if ( count((array) get_node_field_node_ref($referenceCandidateNode,'field_reference_point',null)) == count($reference->referencePoints) ) {
                continue;
            }
            // must match all reference points
            foreach ( (array) get_node_field_node_ref($referenceCandidateNode,'field_reference_point',null) as $referencePointNid ) {
                $referencePointNode = node_load($referencePointNid);
                foreach ( $reference->referencePoints as $referencePoint ) {
                    if ( $referencePoint->dataset == get_node_field_value($referencePointNode,'field_ref_point_dataset_sysname') ) {
                        $match = true;
                    } else {
                        $match = false;
                    }
                }
            }

            if ( $match ) {
                $matchedReferences[] = $referenceCandidateNode;
            }
        }

        if ( count($matchedReferences) == 1 ) {
            return $matchedReferences[0];
        } else if ( count($matchedReferences) > 1 ) {
            throw new Exception('More than one identical reference found.');
        } else {
            return null;
        }

    }
}