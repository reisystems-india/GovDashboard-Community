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


namespace GD\Security\Authentication\Adapter;

use GD\Security\Authentication\Result;
use DrupalOAuthConsumer;

class OAuthAdapter extends AbstractAdapter {

    protected $bypass = FALSE;

    public function authenticate() {
        if ( $this->bypass ) {
            $consumer = DrupalOAuthConsumer::loadProviderByKey($_GET['oauth_consumer_key']);
            if (isset($consumer)) {
                $this->setIdentity($consumer);
                $this->setResult(new Result(Result::SUCCESS,$this->getIdentity(),array()));
            } else {
                $this->setResult(new Result(Result::FAILURE,NULL,array()));
            }
        } else {
            $this->setResult(new Result(Result::FAILURE,NULL,array()));
        }

        return $this->getResult();
    }

    public function postAuthenticate() {
        $consumer = $this->getIdentity();
        if (isset($consumer)) {
            global $user;
            $user = user_load($consumer->uid);
            user_login_finalize();
        }
    }

    public function preAuthenticate() {
        if ( isset($_GET['oauth_consumer_key']) ) {
            $this->bypass = TRUE;
        }
    }

}
