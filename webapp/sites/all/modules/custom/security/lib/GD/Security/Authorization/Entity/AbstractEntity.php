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

namespace GD\Security\Authorization\Entity;

class AbstractEntity implements Entity
{
    /**
     * Unique id of Entity
     *
     * @var string
     */
    protected $entityId;

    /**
     * Sets the Entity identifier
     *
     * @param  string $entityId
     */
    public function __construct($entityId)
    {
        $this->entityId = (string) $entityId;
    }

    /**
     * Defined by Entity; returns the Entity identifier
     *
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * Defined by EntityInterface; returns the Entity identifier
     * Proxies to getEntityId()
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getEntityId();
    }
}