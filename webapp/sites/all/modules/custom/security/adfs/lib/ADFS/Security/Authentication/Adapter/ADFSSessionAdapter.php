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


namespace ADFS\Security\Authentication\Adapter;


use GD\Security\Authentication\Adapter\AbstractAdapter;
use GD\Security\Authentication\Result;

define('ADFS_EMAIL_SCHEMA', 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress');
define('ADFS_GROUP_SCHEMA', 'http://schemas.xmlsoap.org/claims/Group');
define('ADFS_COMMON_NAME_SCHEMA', 'http://schemas.xmlsoap.org/claims/CommonName');
define('ADFS_SURNAME_SCHEMA', 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname');

class ADFSSessionAdapter extends AbstractAdapter {
    protected $authSource;
    protected $dsMappings;
    protected $roleMappings;
    protected $requiredGroups;
    protected $returnUrl;
    protected $autoCreate;
    protected $auth;

    public function __construct () {
        foreach ( variable_get('gd_adfs_config',array()) as $key => $value ) {
            $this->$key = $value;
        }

        if (!isset($this->authSource)) {
            //  If no configuration is set, fail gracefully
            $this->disabled = true;
        } else {
            require_once(DRUPAL_ROOT . libraries_get_path('simplesamlphp', true) . '/lib/_autoload.php');
            $this->auth = new \SimpleSAML_Auth_Simple($this->authSource);
        }
    }

    public function authenticate() {
        if ($this->disabled) return new Result(Result::FAILURE, NULL, array());

        try {
            $this->auth->requireAuth();
            $this->setIdentity($this->auth->getAttributes());
            $this->result = new Result(Result::SUCCESS, $this->getIdentity(), array());
        } catch (\Exception $e) {
            \LogHelper::log_error($e);
            $this->result = new Result(Result::FAILURE, NULL, array($e->getMessage()));
        }

        return $this->result;
    }

    public function postAuthenticate() {
        if ($this->disabled) return;

        $attributes = $this->getIdentity();
        \LogHelper::log_debug('ADFS Attributes');
        \LogHelper::log_debug($attributes);

        if ( $attributes ) {
            global $user;
            $roles = array();
            $r = user_roles(true);

            $db_user = db_select('users')
              ->fields('users', array('uid'))
              ->condition('name', db_like($attributes[ADFS_EMAIL_SCHEMA][0]), 'LIKE')
              ->range(0, 1)
              ->execute()
              ->fetchField();

            if (isset($attributes[ADFS_GROUP_SCHEMA])) {
                $groups = $attributes[ADFS_GROUP_SCHEMA];
                $defaultDatasource = null;
                foreach ($groups as $group) {
                    if (isset($this->roleMappings[$group])) {
                        foreach ($this->roleMappings[$group] as $role) {
                            $roles[array_search($role, $r)] = TRUE;
                        }
                    }
                    if (!isset($defaultDatasource) && isset($this->dsMappings[$group])) {
                        $defaultDatasource = $this->dsMappings[$group][0];
                    }
                }

                foreach ($this->requiredGroups as $requiredGroup) {
                    if (!in_array($requiredGroup, $groups)) {
                        drupal_goto('forbidden');
                    }
                }
            }

            if (isset($defaultDatasource)) {
                $datasources = gd_datasource_get_all();
                foreach ($datasources as $ds) {
                    if ($ds->publicName == $defaultDatasource) {
                        $defaultDatasource = $ds->name;
                        break;
                    }
                }
            }

            //  Load user if it exists
            if ((bool) $db_user) {
                $u = user_load($db_user);

                //  If user is blocked
                if ($u->status == 0) {
                    drupal_goto('forbidden');
                }

                foreach ($u->roles as $role) {
                    if (in_array($role, $r)) {
                        $roles[array_search($role, $r)] = TRUE;
                    }
                }

                //  Keep user roles the same. Sync the first and last name from ADFS
                $info = array(
                    'roles' => $roles,
                    'mail' => $attributes[ADFS_EMAIL_SCHEMA][0],
                    'field_gd_user_first_name' => array(
                        LANGUAGE_NONE => array(
                            0 => array(
                                'value' => $attributes[ADFS_COMMON_NAME_SCHEMA][0]
                            )
                        )
                    ),
                    'field_gd_user_last_name' => array(
                        LANGUAGE_NONE => array(
                            0 => array(
                                'value' => $attributes[ADFS_SURNAME_SCHEMA][0]
                            )
                        )
                    )
                );
                $user = user_save($u, $info);
            } else if ($this->autoCreate) {
                //  Always give new users the authenticated user role
                $roles[array_search('authenticated user', $r)] = TRUE;

                $info = array(
                    'name' => $attributes[ADFS_EMAIL_SCHEMA][0],
                    'pass' => user_password(),
                    'mail' => $attributes[ADFS_EMAIL_SCHEMA][0],
                    'status' => 1,
                    'roles' => $roles,
                    'field_gd_user_first_name' => array(
                        LANGUAGE_NONE => array(
                            0 => array(
                                'value' => $attributes[ADFS_COMMON_NAME_SCHEMA][0]
                            )
                        )
                    ),
                    'field_gd_user_last_name' => array(
                        LANGUAGE_NONE => array(
                            0 => array(
                                'value' => $attributes[ADFS_SURNAME_SCHEMA][0]
                            )
                        )
                    )
                );
                $user = user_save(drupal_anonymous_user(), $info);
            } else {
                $message = t('Unauthorized account: @email', array('@email' => $attributes[ADFS_EMAIL_SCHEMA][0]));
                \LogHelper::log_error($message);
                drupal_goto('forbidden');
            }

            user_login_finalize($info);

            if (isset($defaultDatasource)) {
                gd_datasource_set_active($defaultDatasource);
            }
        }
    }

    public function invalidate() {
        if ($this->disabled) return;

        session_destroy();
        //  TODO Figure out why ADFS is throwing errors
//        $this->auth->logout($this->returnUrl ? $this->returnUrl : '/');
        unset($_GET['destination']);
        drupal_goto($this->returnUrl ? $this->returnUrl : '/');
    }

    public function timeout() {
        $this->invalidate();
    }
}
