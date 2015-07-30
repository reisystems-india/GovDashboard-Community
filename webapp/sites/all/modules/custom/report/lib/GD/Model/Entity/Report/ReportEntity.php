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


namespace GD\Model\Entity\Report;

use GD\Model\Entity\AbstractEntity;
use GD\Model\Entity\Report;
use GD\Model\Entity\User\UserEntity;

class ReportEntity extends AbstractEntity {

    protected $id;
    protected $name;
    protected $author;

    protected $config;

    public function __construct() {
        $this->$config = new ReportConfig();
        $this->$author = new UserEntity();
    }

    public function getId () {
        return $this->id;
    }

    public function getName () {
        return $this->name;
    }

    public function getAuthor () {
        return $this->author->uid;
    }

    public function getUuid () {
        return $this->uuid;
    }

    public function getDescription () {
        return $this->description;
    }

    public function getDatasource () {
        return $this->datasource;
    }

    public function getDatasets () {
        return $this->datasets;
    }

    public function getConfig () {
        return $this->config;
    }

    public function getCustomCode () {
        return $this->customCode;
    }

    public function getTags () {
        return $this->tags;
    }

}