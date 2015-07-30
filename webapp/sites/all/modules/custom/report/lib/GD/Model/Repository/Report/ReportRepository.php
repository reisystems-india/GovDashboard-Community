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


namespace GD\Model\Repository\Report;

use GD\Model\Repository\DrupalRepository;
use GD\Model\Entity\Entity;
use GD\Model\Entity\Report\ReportEntity;
use GD\Model\Entity\Report\Factory\ReportEntityFactory;
use GD\Utility\Uuid;


class ReportRepository extends DrupalRepository {

    const NODE_TYPE = 'report';

    public function get ( $id ) {
        $node = node_load($id);
        if ( !$node ) {
            throw new \Exception('Report Node Not Found');
        }
        if ( $node->type !== self::NODE_TYPE ) {
            throw new \Exception('Node Not Of Type Report');
        }
        return ReportEntityFactory::createFromNode($node);
    }

    public function save ( Entity $entity ) {

        if ( !($entity instanceof ReportEntity) ) {
            throw new \Exception('Expected ReportEntity');
        }

        if ( !$entity->getId() ) {
            $this->create($entity);
        } else {
            $this->update($entity);
        }
    }

    public function delete ( $id ) {
        $node = node_load($id);
        if ( !$node ) {
            throw new \Exception('Report Node Not Found');
        }
        if ( $node->type !== self::NODE_TYPE ) {
            throw new \Exception('Node Not Of Type Report');
        }
        $node->status = NODE_NOT_PUBLISHED;
        node_save($node);
    }

    private function create ( ReportEntity $entity ) {
        $node = new \stdClass();
        $node->type = self::NODE_TYPE;
        $node->language = LANGUAGE_NONE;
        node_object_prepare($node);

        $node->title = $entity->getName();
        $node->uid = $entity->getAuthor()->getId();

        $node->field_report_uuid[$node->language][0]['value'] = $entity->getUuid(); //Uuid::generate();

        $node->field_report_desc[$node->language][0]['value'] = $entity->getDescription();

        $node->field_report_datasource[$node->language][0]['value'] = $entity->getDatasource();

        $node->field_report_conf[$node->language][0]['value'] = $entity->getConfig()->toJson();

        $node->field_report_dataset_sysnames[$node->language] = array();
        foreach ( $entity->getDatasets() as $dataset ) {
            $node->field_report_dataset_sysnames[$node->language][] = array('value' => $dataset->name);
        }

        $node->field_report_custom_view[$node->language][0]['value'] = $entity->getCustomCode();

        $node->field_report_tags[$node->language] = array();
        foreach ( $entity->getTags() as $tag ) {
            $node->field_report_tags[$node->language][] = array('tid' => $tag->tid);
        }

        node_save($node);

        // update the entity object

        // created, updated, uuid, etc.

    }

    private function update ( ReportEntity $entity ) {

    }

} 