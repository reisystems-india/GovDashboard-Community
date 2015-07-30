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


function _gd_account_user_resource_definition() {
    return array(
        'account_user' => array(
            'create' => array(
                'file' => array('file' => 'inc', 'module' => 'gd_account_user', 'name' => 'resources/user_resource'),
                'help' => 'Create an account user',
                'callback' => '_gd_account_user_resource_create',
                'access callback' => '_gd_account_user_resource_access',
                'access arguments' => array('create'),
                'access arguments append' => TRUE,
                'args' => array(
                    array(
                        'name' => 'user',
                        'description' => 'The user to create',
                        'source' => array('data' => 'user'),
                        'optional' => FALSE
                    )
                )
            ),
            'retrieve' => array(
                'file' => array('file' => 'inc', 'module' => 'gd_account_user', 'name' => 'resources/user_resource'),
                'help' => 'Retrieves an account user',
                'callback' => '_gd_account_user_resource_retrieve',
                'access callback' => '_gd_account_user_resource_access',
                'access arguments' => array('retrieve'),
                'access arguments append' => TRUE,
                'args' => array(
                    array(
                        'name' => 'id',
                        'type' => 'int',
                        'description' => 'The id of the user to get',
                        'source' => array('path' => '0'),
                        'optional' => FALSE
                    )
                )
            ),
            'update' => array(
                'file' => array('file' => 'inc', 'module' => 'gd_account_user', 'name' => 'resources/user_resource'),
                'help' => 'Update an account user',
                'callback' => '_gd_account_user_resource_update',
                'access callback' => '_gd_account_user_resource_access',
                'access arguments' => array('update'),
                'access arguments append' => TRUE,
                'args' => array(
                    array(
                        'name' => 'id',
                        'type' => 'int',
                        'description' => 'The id of the user to update',
                        'source' => array('path' => '0'),
                        'optional' => FALSE
                    ),
                    array(
                        'name' => 'user',
                        'description' => 'The updated user',
                        'source' => array('data' => 'user'),
                        'optional' => FALSE
                    )
                )
            ),
            'delete' => array(
                'file' => array('file' => 'inc', 'module' => 'gd_account_user', 'name' => 'resources/user_resource'),
                'help' => 'Deletes an account user',
                'callback' => '_gd_account_user_resource_delete',
                'access callback' => '_gd_account_user_resource_access',
                'access arguments' => array('delete'),
                'access arguments append' => TRUE,
                'args' => array(
                    array(
                        'name' => 'id',
                        'type' => 'int',
                        'description' => 'The id of the user to delete',
                        'source' => array('path' => '0'),
                        'optional' => FALSE,
                    )
                )
            ),
            'index' => array(
                'file' => array('file' => 'inc', 'module' => 'gd_account_user', 'name' => 'resources/user_resource'),
                'help' => 'Lists users in an account',
                'callback' => '_gd_account_user_resource_index',
                'access callback' => '_gd_account_user_resource_access',
                'access arguments' => array('index'),
                'access arguments append' => TRUE,
                'args' => array()
            ),
            'relationships' => array(
                'groups' => array(
                    'file' => array('type' => 'inc', 'module' => 'gd_account_user', 'name' => 'resources/user_resource'),
                    'help' => 'Lists of Groups a Users can view',
                    'callback' => '_gd_account_user_resource_groups',
                    'access callback' => '_gd_account_user_resource_access',
                    'access arguments append' => true,
                    'args' => array(
                        array(
                            'name' => 'id',
                            'type' => 'int',
                            'description' => 'User Id',
                            'source' => array('path' => '0'),
                            'optional' => true
                        )
                    )
                )
            ),
            'actions' => array(
                'login' => array(
                    'file' => array('type' => 'inc', 'module' => 'gd_account_user', 'name' => 'resources/user_resource'),
                    'help' => 'Login a user for a new session',
                    'callback' => '_gd_account_user_resource_login',
                    'args' => array(
                        array(
                            'name' => 'username',
                            'type' => 'string',
                            'description' => 'A valid username',
                            'source' => 'data',
                            'optional' => FALSE
                        ),
                        array(
                            'name' => 'password',
                            'type' => 'string',
                            'description' => 'A valid password',
                            'source' => 'data',
                            'optional' => FALSE
                        )
                    ),
                    'access callback' => TRUE
                ),
                'logout' => array(
                    'file' => array('type' => 'inc', 'module' => 'gd_account_user', 'name' => 'resources/user_resource'),
                    'help' => 'Logout a user session',
                    'callback' => '_gd_account_user_resource_logout',
                    'access callback' => TRUE
                )
            )
        )
    );

}

define('GD_ADMIN_UI_ICON_PERSON',"/".drupal_get_path('module','gd_admin_ui')."/images/people_icon.png");

/**
 * @return bool
 */
function _gd_account_user_resource_access () {
    return user_is_logged_in();
}

/**
 * Login user
 *
 * FIXME - needs to check for account access
 *
 * @param $username
 * @param $password
 * @return mixed|StdClass
 */
function _gd_account_user_resource_login ( $username, $password ) {
    global $user;

    if ( !user_is_anonymous() ) {
        // user is already logged in
        return services_error(t('Already logged in as !user.', array('!user' => $user->name)), 406);
    }

    $user = user_load(user_authenticate($username, $password));
    if ( $user->uid ) {
        // Regenerate the session ID to prevent against session fixation attacks.
        drupal_session_regenerate();
        module_invoke_all('user', 'login', NULL, $user);

        return $user;
        //return _gd_account_user_resource_create_api_object($user);
    }

    session_destroy();
    return services_error(t('Wrong username or password.'), 401);
}

/**
 * Logout user
 *
 * @return bool|mixed
 */
function _gd_account_user_resource_logout() {
    global $user;

    if ( user_is_anonymous() ) {
        // User is not logged in
        return services_error(t('User is not logged in.'), 406);
    }

    // Destroy the current session:
    module_invoke_all('user_logout', $user);
    drupal_session_destroy_uid($user->uid);

    // Load the anonymous user
    $user = drupal_anonymous_user();

    return true;
}

/**
 * @param $account
 * @return services_error|StdClass
 */
function _gd_account_user_resource_create ( $account ) {
    try {

        if ( !gd_account_user_is_admin() ) {
            throw new ServicesException('Forbidden',403);
        }

        $account = (Object) $account;

        $username_exists = (bool) db_query('SELECT 1 FROM {users} WHERE name = :name', array(':name' => $account->email))->fetchField();
        if ( $username_exists ) {
            throw new Exception('User already exists');
        }

        $create = array();
        $create['name'] = $account->email;
        $create['mail'] = $account->email;
        $create['status'] = 1;
        //$create['pass'] = $account->password;

        $create['field_gd_user_first_name'][LANGUAGE_NONE][0] = array(
            'value' => $account->firstname,
            'safe_value' => $account->firstname
        );

        $create['field_gd_user_last_name'][LANGUAGE_NONE][0] = array(
            'value' => $account->lastname,
            'safe_value' => $account->lastname
        );

        $create['roles'] = array();
        if ( !empty($account->roles) ) {
            foreach ( $account->roles as $rid ) {
                $role = user_role_load($rid);
                $create['roles'][$rid] = $role->name;
            }
        } else {
            throw new Exception('User requires at least one group.');
        }

        // add data field so we can detect first_time user in gd_login
        $create['data'] = array('first_time' => '1');

        $new_account = user_save(NULL,$create);

        if ( !$new_account ) {
            throw new Exception('Could not create user');
        }

        $new_account = user_load($new_account->uid,true); // forcing to get field info
        gd_account_user_add_fields($new_account);

        $bypass = false;
        drupal_alter('gd_account_create_notification_bypass', $bypass);

        if ( !$bypass ) {
            $notify = 1;
            $op = $notify ? 'register_admin_created' : 'register_no_approval_required';
            _user_mail_notify($op, $new_account);

            // Send email notifying sales team and admin of the account
            $to = variable_get('account_settings_email', '');
            $subject = "New User Registered";
            $body = "A new user has been registered for ".str_replace(array('https://','http://','/'),'',GOVDASH_HOST)." \n\n Name:" . $new_account->firstname . " " . $new_account->lastname ." \n Email:" . $new_account->mail;
            $key = "govdash_new_user_registration";
            gd_account_notify($key, $to, $subject, $body);

            // If Account User Limit is crossed or reached send an email to Sales team
            $user_count = db_query('SELECT COUNT(uid) FROM {users} WHERE uid NOT IN (0,1)')->fetchField();
            if ( $user_count >= variable_get('account_settings_maxusers', '') ) {
                $to = variable_get('account_settings_sales_email', '');
                $subject = "User Limit has been reached or crossed for the Account of " . variable_get('account_settings_fname', '') . " " . variable_get('account_settings_lname', '');
                $body = "The account user limit for the account " . variable_get('account_settings_fname', '') . " " . variable_get('account_settings_lname', '') . "(" . variable_get('account_settings_email', '') . ") has been reached. \n Allowed number of Users:" . variable_get('account_settings_maxusers', '') . " \n Current number of users:" . count(_gd_account_user_resource_index());
                $key = "govdash_account_user_limit_reached";
                gd_account_notify($key, $to, $subject, $body);
            }
        }

        $apiObject = _gd_account_user_resource_create_api_object($new_account);

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        return $apiObject;

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $uid
 * @param $updates
 * @return services_error|StdClass
 */
function _gd_account_user_resource_update ( $uid, $updates ) {
    try {

        if ( !gd_account_user_is_admin() && !gd_account_user_is_any_datasource_admin() ) {
            throw new ServicesException('Forbidden',403);
        }

        $updates = (Object) $updates;

        $account = user_load($uid);

        if ( !$account ) {
            services_error(t('User not found'), 404);
        }

        $update = array();

        if ( !empty($updates->firstname) ) {
            $update['field_gd_user_first_name'][LANGUAGE_NONE][0] = array(
                'value' => $updates->firstname,
                'safe_value' => $updates->firstname
            );
        }

        if ( !empty($updates->lastname) ) {
            $update['field_gd_user_last_name'][LANGUAGE_NONE][0] = array(
                'value' => $updates->lastname,
                'safe_value' => $updates->lastname
            );
        }

        if ( isset($updates->status) ) {
            $update['status'] = (int) $updates->status;

            if ( $update['status'] == 0 ) {
                drupal_session_destroy_uid($account->uid);
            }
        }

        if ( isset($updates->groups) ) {

            $update['roles'] = array();

            // map groups array to role array
            foreach ( $updates->groups as $group ) {
                $update['roles'][$group['id']] = $group['name'];
            }

            // compare original roles to updated roles
            // only concerned with missing roles
            foreach ( $account->roles as $role_id => $role_name ) {
                $role_was_removed = true; // assuming it was removed unless found below
                foreach ( $update['roles'] as $update_role_id => $update_role_name ) {
                    if ( $role_id == $update_role_id ) {
                        // found the original role
                        $role_was_removed = false;
                    }
                }

                // original role was removed
                if ( $role_was_removed ) {
                    // need to make sure it wasn't one of the following
                    if ( in_array($role_id,array(DRUPAL_ANONYMOUS_RID,DRUPAL_AUTHENTICATED_RID,GOVDASHBOARD_INSTANCE_ADMIN_RID,GOVDASHBOARD_SITE_ADMIN_RID)) ) {
                        // add it back
                        $update['roles'][$role_id] = $role_name;
                    }
                }
            }
        }

        if ( !empty($update) ) {

            user_save($account,$update);
            $account = user_load($uid);
            if ( !$account ) {
                throw new Exception('Could not update user');
            }
        }

        $apiObject = _gd_account_user_resource_create_api_object($account);

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        return $apiObject;

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $uid
 * @return services_error|StdClass
 */
function _gd_account_user_resource_retrieve ( $uid ) {
    try {
        if ( !gd_account_user_is_admin() && !gd_account_user_is_any_datasource_admin() ) {
            throw new ServicesException('Forbidden',403);
        }

        $account = user_load($uid);

        if ( !$account ) {
            throw new ServicesException(t('User not found'), 404);
        }

        $apiObject = _gd_account_user_resource_create_api_object($account);

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        return $apiObject;

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $uid
 * @return services_error|StdClass
 */
function _gd_account_user_resource_delete ( $uid ) {
    try {
        if ( !gd_account_user_is_admin() ) {
            throw new ServicesException('Forbidden',403);
        }

        $account = user_load($uid);

        if ( !$account ) {
            throw new ServicesException(t('User not found'), 404);
        }

        user_save($account, array('status' => 0));
        drupal_session_destroy_uid($account->uid);

        $apiObject = _gd_account_user_resource_create_api_object($account);

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        return $apiObject;

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @return array|services_error
 */
function _gd_account_user_resource_index () {
    try {
        if ( !gd_account_user_is_admin() && !gd_account_user_is_any_datasource_admin() ) {
            throw new ServicesException('Forbidden',403);
        }

        $query = new EntityFieldQuery();
        $query->entityCondition('entity_type', 'user');
        $query->addTag('DANGEROUS_ACCESS_CHECK_OPT_OUT');

        $entities = $query->execute();

        $result = array();
        if ( !empty($entities['user']) ) {

            $accountIds = array();
            foreach ( $entities['user'] as $u ) {
                // skip admin and anon user
                if ( !in_array($u->uid,array(0,1)) ) {
                    $accountIds[] = $u->uid;
                }
            }

            foreach ( user_load_multiple($accountIds) as $account ) {
                $result[] = _gd_account_user_resource_create_api_object($account);
            }
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
 * @param $uid
 * @return array
 */
function _gd_account_user_resource_groups ( $uid ) {
    try {
        if ( $uid != null ) {
            $account = user_load($uid);
        } else {
            global $user;
            $account = $user;
        }

        $result = array();

        // include shared resource function: _gd_account_group_resource_create_api_object()
        module_load_include('inc', 'gd_account_group', 'resources/group_resource');
        // Site admin or Drupal Admin return all the roles. Else return roles (groups) user belong to.
        // Datamart Admin has access to all roles to assign only their dashboards to.
        if ( gd_account_user_is_admin($account) ) {
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

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $account
 * @return StdClass
 */
function _gd_account_user_resource_create_api_object ( $account ) {

    // TODO put this somewhere else
    gd_account_user_add_fields($account);

    $api_object = new StdClass();

    if ( isset($account->uid) ) {
        $api_object->id = $account->uid;
    }

    $api_object->email = $account->mail;
    $api_object->firstname = $account->firstname;
    $api_object->lastname = $account->lastname;
    $api_object->fullname = $account->fullname;

    $api_object->status = $account->status;
    $api_object->created = date(GD_DATETIME_FORMAT_API, $account->created);

    $api_object->icon = GD_ADMIN_UI_ICON_PERSON;

    // groups
    $api_object->groups = array();
    if ( !empty($account->roles) ) {

        // pre fetching for later
        $role_details = array();
        $result = db_query('SELECT rid, role_description, role_changed FROM {role_info}');
        while ( $details = $result->fetchObject() ) {
            $role_details[$details->rid] = array('changed'=>$details->role_changed,'description'=>$details->role_description);
        }

        foreach ( $account->roles as $role_id => $role_name ) {
            // don't include authenticated user group
            if ( !in_array($role_id,array(DRUPAL_AUTHENTICATED_RID)) ) {

                $item = array();
                $item['id'] = $role_id;
                $item['name'] = $role_name;

                // get more info
                if ( isset($role_details[$role_id]) ) {
                    if ( isset($role_details[$role_id]['description']) ) {
                        $item['description'] = $role_details[$role_id]['description'];
                    }

                    if ( isset($role_details[$role_id]['changed']) ) {
                        $item['changed'] = $role_details[$role_id]['changed'];
                    }
                }

                $api_object->groups[] = $item;
            }
        }
    }

    // get all datasources a user is admin of
    $api_object->datasources = array();
    $datasources = gd_account_user_get_datasources($account);
    if ( !empty($datasources) ) {
        foreach ( $datasources as $ds ) {
            $item = array();
            $item['name'] = $ds->name;
            $item['publicName'] = $ds->publicName;
            $item['description'] = (isset($ds->description)) ? $ds->description : '';
            $item['changed'] = (isset($ds->changed)) ? $ds->changed : '';

            $api_object->datasources[] = $item;
        }
    }

    // get all dashboards a user has view access to
    $api_object->dashboards = array();
    $dashboards = gd_account_user_get_dashboards($account);
    foreach ( $dashboards as $dashboard ) {
        $item = array();
        $item['id'] = $dashboard->nid;
        $item['name'] = $dashboard->title;
        $item['description'] = get_node_field_value($dashboard,'field_dashboard_desc');
        $item['changed'] = date(GD_DATETIME_FORMAT_API, $dashboard->changed);

        if ( gd_account_user_is_admin($account) || gd_security_is_allowed($account,new \GD\Security\Authorization\Entity\DrupalEntity(get_node_field_value($dashboard,'field_dashboard_datasource'),'datasource'),'admin') ) {
            $item['editable'] = 1;
        } else {
            $item['editable'] = 0;
        }

        $datasource = gd_datasource_get(get_node_field_value($dashboard,'field_dashboard_datasource'));
        $item['datasource'] = new stdClass();
        $item['datasource']->name = $datasource->name;
        $item['datasource']->publicName = $datasource->publicName;

        // this is here for the smartclient list grid so it can sort
        $item['datasourcePublicName'] = $datasource->publicName;

        $api_object->dashboards[] = $item;
    }

    return $api_object;
}
