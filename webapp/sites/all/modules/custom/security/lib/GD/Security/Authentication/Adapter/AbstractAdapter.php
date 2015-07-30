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

abstract class AbstractAdapter implements Adapter
{
    /**
     * @var mixed
     */
    protected $disabled;

    /**
     * @var mixed
     */
    protected $credential;

    /**
     * @var mixed
     */
    protected $identity;

    /**
     * @var Result
     */
    protected $result;

    /**
     * Returns the credential of the account being authenticated, or
     * NULL if none is set.
     *
     * @return mixed
     */
    public function getCredential() {
        return $this->credential;
    }

    /**
     * Sets the credential for binding
     *
     * @param  mixed           $credential
     * @return AbstractAdapter
     */
    public function setCredential($credential) {
        $this->credential = $credential;

        return $this;
    }

    /**
     * Returns the identity of the account being authenticated, or
     * NULL if none is set.
     *
     * @return mixed
     */
    public function getIdentity() {
        return $this->identity;
    }

    /**
     * Sets the identity for binding
     *
     * @param  mixed          $identity
     * @return AbstractAdapter
     */
    public function setIdentity($identity) {
        $this->identity = $identity;

        return $this;
    }

    /**
     * Returns the result of the account being authenticated, or
     * NULL if none is set.
     *
     * @return mixe
     */
    public function getResult() {
        return $this->result;
    }

    /**
     * Sets the result
     *
     * @param  Result          $result
     * @return AbstractAdapter
     */
    public function setResult(Result $result) {
        $this->result = $result;

        return $this;
    }

    /**
     * Called before authenticate() method
     *
     * @return void
     */
    public function preAuthenticate () {}

    /**
     * Called after authenticate() method
     *
     * @return void
     */
    public function postAuthenticate () {}

    /**
     * Invalidate the authentication
     *
     * @return void
     */
    public function invalidate() {}

    /**
     * Called when session has timed out
     *
     * @return void
     */
    public function timeout() {}
}
