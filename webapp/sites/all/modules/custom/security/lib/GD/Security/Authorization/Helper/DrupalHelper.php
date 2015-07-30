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


namespace GD\Security\Authorization\Helper;

class DrupalHelper extends AbstractHelper {

    protected static $instance;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new DrupalHelper();
        }

        return self::$instance;
    }

    public static function findEntities ( array $params = array() ) {
        $sql = 'SELECT entityId, entityType FROM {gd_role_permission} ';

        $binds = array();
        $where = array();
        if ( !empty($params) ) {
            foreach ( $params as $key => $value ) {
                $binds[':'.$key] = $value;
                $where[] = $key .' = :'.$key;
            }
        }

        if ( !empty($where) ) {
            $sql .= 'WHERE '.implode(' AND ',$where);
        }

        $result = db_query($sql,$binds);

        if ( !$result || !$result->rowCount() ) {
            return array();
        }

        $results = $result->fetchAll();

        $entities = array();
        if ( isset($params['entityType']) && $params['entityType'] === 'datasource' ) {
            $datasources = gd_datasource_get_all();
            foreach ( $datasources as $datasourceName => $DS ) {
                foreach ( $results as $record ) {
                    if ( $record->entityId === $datasourceName ) {
                        $entities[$datasourceName] = $DS;
                    }
                }
            }
        } else if ( isset($params['entityType']) && $params['entityType'] === 'dashboard' ) {
            $dashboard_nids = array();
            foreach ( $results as $record ) {
                $dashboard_nids[] = $record->entityId;
            }
            $entities = node_load_multiple($dashboard_nids);
        } else {
            throw new \Exception('Unsupported EntityType');
        }

        return $entities;
    }

    public static function findRoles ( array $params = array() ) {
        $sql = 'SELECT DISTINCT(roleId) FROM {gd_role_permission} ';

        $binds = array();
        $where = array();
        if ( !empty($params) ) {
            foreach ( $params as $key => $value ) {
                $binds[':'.$key] = $value;
                $where[] = $key .' = :'.$key;
            }
        }

        if ( !empty($where) ) {
            $sql .= 'WHERE '.implode(' AND ',$where);
        }

        $result = db_query($sql,$binds);

        if ( !$result || !$result->rowCount() ) {
            return array();
        }

        $results = $result->fetchAll();

        $roles = array();
        foreach ( gd_account_group_get_all() as $role ) {
            foreach ( $results as $record ) {
                if ( $record->roleId === $role->rid ) {
                    $roles[] = $role;
                }
            }
        }

        return $roles;
    }
}