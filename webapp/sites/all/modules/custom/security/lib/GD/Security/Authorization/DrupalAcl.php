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


namespace GD\Security\Authorization;

use GD\Common\Collections\ArrayCollection;
use GD\Security\Authorization\Entity\Entity;

class DrupalAcl extends AbstractAcl {

    public function isAllowed ( ArrayCollection $roles, Entity $entity, $operation ) {
        $sql  = "SELECT 1 FROM {gd_role_permission} ";
        $sql .= "WHERE entityId = :entityId AND entityType = :entityType AND roleId IN (:roleIds) AND operation = :operation";

        $bindings = array(
            ':entityId' => $entity->getEntityId(),
            ':entityType' => $entity->getEntityType(),
            ':roleIds' => $roles->toArray(),
            ':operation' => $operation
        );

        $result = db_query($sql,$bindings);

        if ( $result->fetchField() ) {
            return true;
        } else {
            return false;
        }
    }

    public function allow ( ArrayCollection $roles, Entity $entity, $operation ) {
        $sql  = "INSERT INTO {gd_role_permission} (roleId,entityId,entityType,operation) ";
        $sql .= "VALUES ( :roleId, :entityId, :entityType, :operation )";

        foreach ( $roles as $role ) {
            $bindings = array(
                ':roleId' => $role->getRoleId(),
                ':entityId' => $entity->getEntityId(),
                ':entityType' => $entity->getEntityType(),
                ':operation' => $operation
            );
            db_query($sql,$bindings);
        }
    }

    public function deny ( ArrayCollection $roles, Entity $entity, $operation ) {
        $sql  = "DELETE FROM {gd_role_permission} ";
        $sql .= "WHERE roleId = :roleId AND entityId = :entityId AND operation = :operation";

        foreach ( $roles as $role ) {
            $bindings = array(
                ':roleId' => $role->getRoleId(),
                ':entityId' => $entity->getEntityId(),
                ':operation' => $operation
            );
            db_query($sql,$bindings);
        }
    }
} 