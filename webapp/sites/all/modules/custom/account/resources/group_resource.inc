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


function _gd_account_group_resource_definition () {
    return array(
        'account_group' => array(
            'create' => array(
                'file' => array('file' => 'inc', 'module' => 'gd_account_group', 'name' => 'resources/group_resource'),
                'help' => 'Create an account group',
                'callback' => '_gd_account_group_resource_create',
                'access callback' => '_gd_account_group_resource_access',
                'access arguments' => array('create'),
                'access arguments append' => true,
                'args' => array(
                    array(
                        'name' => 'group',
                        'description' => 'The group to create',
                        'source' => array('data' => 'group'),
                        'optional' => false,
                    ),
                ),
            ),
            'retrieve' => array(
                'file' => array('file' => 'inc', 'module' => 'gd_account_group', 'name' => 'resources/group_resource'),
                'help' => 'Retrieves an account group',
                'callback' => '_gd_account_group_resource_retrieve',
                'access callback' => '_gd_account_group_resource_access',
                'access arguments' => array('view'),
                'access arguments append' => true,
                'args' => array(
                    array(
                        'name' => 'id',
                        'type' => 'int',
                        'description' => 'The id of the group to get',
                        'source' => array('path' => '0'),
                        'optional' => false,
                    ),
                ),
            ),
            'update' => array(
                'file' => array('file' => 'inc', 'module' => 'gd_account_group', 'name' => 'resources/group_resource'),
                'help' => 'Update an account group',
                'callback' => '_gd_account_group_resource_update',
                'access callback' => '_gd_account_group_resource_access',
                'access arguments' => array('update'),
                'access arguments append' => true,
                'args' => array(
                    array(
                        'name' => 'id',
                        'type' => 'int',
                        'description' => 'The id of the group to update',
                        'source' => array('path' => '0'),
                        'optional' => false,
                    ),
                    array(
                        'name' => 'group',
                        'description' => 'The group to update',
                        'source' => array('data' => 'group'),
                        'optional' => false,
                    ),
                ),
            ),
            'delete' => array(
                'file' => array('file' => 'inc', 'module' => 'gd_account_group', 'name' => 'resources/group_resource'),
                'help' => 'Deletes an account group',
                'callback' => '_gd_account_group_resource_delete',
                'access callback' => '_gd_account_group_resource_access',
                'access arguments' => array('delete'),
                'access arguments append' => true,
                'args' => array(
                    array(
                        'name' => 'id',
                        'type' => 'int',
                        'description' => 'The id of the group to delete',
                        'source' => array('path' => '0'),
                        'optional' => false,
                    ),
                ),
            ),
            'index' => array(
                'file' => array('file' => 'inc', 'module' => 'gd_account_group', 'name' => 'resources/group_resource'),
                'help' => 'Lists groups in an account',
                'callback' => '_gd_account_group_resource_index',
                'access callback' => '_gd_account_group_resource_access',
                'access arguments' => array('index'),
                'access arguments append' => true,
                'args' => array(),
            ),
            'relationships' => array(
                'user_groups' => array(
                    'file' => array('type' => 'inc', 'module' => 'gd_account_group', 'name' => 'resources/group_resource'),
                    'help' => 'Lists of user_groups under Group tab, Admin screening',
                    'callback' => '_gd_account_group_resource_user_groups',
                    'access callback' => '_gd_account_group_resource_access',
                    'access arguments append' => true,
                    'args' => array(
                        array(
                            'name' => 'id',
                            'type' => 'int',
                            'description' => 'User Id',
                            'source' => array('path' => '0'),
                            'optional' => true,
                        )
                    )
                ),
                'users' => array(
                    'file' => array('type' => 'inc', 'module' => 'gd_account_group', 'name' => 'resources/group_resource'),
                    'help' => 'Lists of users in an group, Admin screening',
                    'callback' => '_gd_account_group_resource_users',
                    'access callback' => '_gd_account_group_resource_access',
                    'access arguments append' => true,
                    'args' => array(
                        array(
                            'name' => 'id',
                            'type' => 'int',
                            'description' => 'User Id',
                            'source' => array('path' => '0'),
                            'optional' => true,
                        )
                    )
                )
            )
        )
    );

}

/**
 * Access control
 *
 * @param $op
 * @param $args
 * @return bool
 */
function _gd_account_group_resource_access () {
    return user_is_logged_in();
}

/**
 * @return array|services_error
 */
function _gd_account_group_resource_index () {
    try {
        if ( !gd_account_user_is_admin() && !gd_account_user_is_any_datasource_admin() ) {
            throw new ServicesException('Forbidden',403);
        }
        
        $result = array();
        foreach ( gd_account_group_get_all() as $group ) {
            $result[] = _gd_account_group_resource_create_api_object($group);
        }

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        return $result;

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $id
 * @return StdClass
 */
function _gd_account_group_resource_retrieve ( $id ) {
    try {
        if ( !gd_account_user_is_admin() && !gd_account_user_is_any_datasource_admin() ) {
            throw new ServicesException('Forbidden',403);
        }

        $group = gd_account_group_get($id);
        if ( empty($group) ) {
            throw new ServicesException('Not found',404);
        }

        $apiObject = _gd_account_group_resource_create_api_object($group);

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        return $apiObject;

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $input
 * @return services_error|StdClass
 */
function _gd_account_group_resource_create ( $input ) {
    try {
        if ( !gd_account_user_is_admin() ) {
            throw new ServicesException('Forbidden',403);
        }
        
        $input = (Object) $input;

        // validate name is not empty
        if ( empty($input->name) ) {
            services_error('Group name required', 406);
        }

        // validate name is unique
        if ( !gd_account_group_name_isunique($input->name) ) {
            services_error('Group Already exists', 406);
        }

        // create the group
        $group = gd_account_group_create($input);

        if ( !$group ) {
            services_error('Group creation failed', 406);
        }

        // begin assigning items to group

        // users
        if ( !empty($input->users) ) {
            foreach ( $input->users as $userId ) {
                gd_account_group_add_user($group->rid,$userId);
            }
        }

        // dashboards
        if ( !empty($input->dashboards) ) {
            foreach ( $input->dashboards as $dashboardId ) {
                gd_account_group_add_dashboard($group->rid,$dashboardId);
            }
        }

        // datasource
        if ( !empty($input->datasources) ) {
            foreach ( $input->datasources as $datasourceName ) {
                gd_account_group_add_datasource($group->rid,$datasourceName);
            }
        }

        $apiObject = _gd_account_group_resource_create_api_object($group);

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }
        
        return $apiObject;
        
    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $roleId
 * @param $input
 * @return services_error|StdClass
 */
function _gd_account_group_resource_update ( $roleId, $input ) {
    try {
        if ( !gd_account_user_is_admin() && !gd_account_user_is_any_datasource_admin() ) {
            throw new ServicesException('Forbidden',403);
        }
        
        $input = (Object) $input;

        $group = gd_account_group_get($roleId);
        if ( empty($group) ) {
            throw new ServicesException('Not found',404);
        }

        // validate name is unique
        if ( !empty($input->name) && $input->name != $group->name && !gd_account_group_name_isunique($input->name) ) {
            throw new ServicesException('Group name must be unique', 406);
        }

        gd_account_group_update($roleId,$input);

        $apiObject = _gd_account_group_resource_create_api_object(gd_account_group_get($roleId));

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        return $apiObject;

    } catch (Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $id
 * @return array
 */
function _gd_account_group_resource_delete ( $id ) {
    try {
        if ( !gd_account_user_is_admin() ) {
            throw new ServicesException('Forbidden',403);
        }

        user_role_delete((int) $id);

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        return null;

    } catch (Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * Relationship callback
 *
 * @param $id
 * @return array
 */
function _gd_account_group_resource_user_groups ( $id ) {
    try {
        $account = user_load($id);
        if ( !$account ) {
            throw new ServicesException('Not found',404);
        }
        
        if ( !gd_account_user_is_admin($account) && !gd_account_user_is_any_datasource_admin($account) ) {
            throw new ServicesException('Forbidden',403);
        }
    
        // Site admin or Drupal Admin return all the roles. Else return roles (groups) user belong to.
        // Datamart Admin has access to all roles to assign only their dashboards to.
        $result = array();
        if ( gd_account_user_is_admin($account) || gd_account_user_is_any_datasource_admin($account) ) {
            foreach ( gd_account_group_get_all() as $group ) {
                $result[] = _gd_account_group_resource_create_api_object($group);
            }
        } else {
            foreach ( gd_account_group_get_all() as $group ) {
                if ( array_key_exists($group->rid, $account->roles) ) {
                    $result[] = _gd_account_group_resource_create_api_object($group);
                }
            }
        }

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }
        
        return $result;

    } catch (Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $group
 * @return StdClass
 */
function _gd_account_group_resource_create_api_object ( $group ) {
    global $user;

    $apiObject = new StdClass();
    $apiObject->id = $group->rid;
    $apiObject->name = $group->name;
    $apiObject->description = $group->description;
    $apiObject->changed = $group->changed;


    // todo where is this being used?
    $apiObject->datamartadmin = 0;
    if ( gd_account_user_is_admin() ) {
        $apiObject->datamartadmin = 1;
    }


    // get all members for this group
    $apiObject->users = array();
    foreach ( gd_account_group_get_members($group->rid,LOAD_ENTITY) as $account ) {
        $apiObject->users[] = array(
            'id' => $account->uid,
            'email' => $account->mail,
            'firstname' => $account->firstname,
            'lastname' => $account->lastname,
            'fullname' => $account->fullname
        );

        if ( $user->uid === $account->uid ) {
            $apiObject->datamartadmin = 1;
        }
    }

    // get all datasources this group is admin of
    $apiObject->datasources = array();
    $datasources = gd_account_group_get_datasources($apiObject->id);
    if ( !empty($datasources) ) {
        foreach ( $datasources as $ds ) {
            $item = array();
            $item['name'] = $ds->name;
            $item['publicName'] = $ds->publicName;
            $item['description'] = (isset($ds->description)) ? $ds->description : '';
            $item['changed'] = (isset($ds->changed)) ? $ds->changed : '';

            $apiObject->datasources[] = $item;
        }
    }

    // get all dashboards a group has view access to
    $apiObject->dashboards = array();
    $dashboards = gd_account_group_get_dashboards($apiObject->id);
    $roles = new \GD\Common\Collections\ArrayCollection();
    $roles->add(new \GD\Security\Authorization\Role\DrupalRole($apiObject->id));
    foreach ( $dashboards as $dashboard ) {
        $item = array();
        $item['id'] = $dashboard->nid;
        $item['name'] = $dashboard->title;
        $item['description'] = get_node_field_value($dashboard,'field_dashboard_desc');
        $item['changed'] = date(GD_DATETIME_FORMAT_API, $dashboard->changed);

        if ( in_array($apiObject->id,gd_account_group_get_admin_groups()) || gd_security_is_allowed_by_role($roles,new \GD\Security\Authorization\Entity\DrupalEntity(get_node_field_value($dashboard,'field_dashboard_datasource'),'datasource'),'admin') ) {
            $item['editable'] = 1;
        } else {
            $item['editable'] = 0;
        }

        $datasource = gd_datasource_get(get_node_field_value($dashboard,'field_dashboard_datasource'));
        $item['datasource'] = new stdClass();
        $item['datasource']->name = $datasource->name;
        $item['datasource']->publicName = $datasource->publicName;

        $apiObject->dashboards[] = $item;
    }

    $apiObject->icon = "/" . drupal_get_path('module', 'gd_admin_ui') . "/images/people_icon.png";

    return $apiObject;
}