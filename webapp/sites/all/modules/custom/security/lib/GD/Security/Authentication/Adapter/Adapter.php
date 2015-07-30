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

interface Adapter
{
    /**
     * Performs an authentication attempt
     *
     * @return \GD\Security\Authentication\Result
     * @throws \Exception If authentication cannot be performed
     */
    public function authenticate();

    /**
     * Returns the identity of the account being authenticated, or
     * NULL if none is set.
     *
     * @return mixed
     */
    public function getIdentity();

    /**
     * Sets the identity for binding
     *
     * @param  mixed                       $identity
     * @return Adapter
     */
    public function setIdentity($identity);

    /**
     * Returns the credential of the account being authenticated, or
     * NULL if none is set.
     *
     * @return mixed
     */
    public function getCredential();

    /**
     * Sets the credential for binding
     *
     * @param  mixed                       $credential
     * @return Adapter
     */
    public function setCredential($credential);

    /**
     * Returns the result of the account being authenticated, or
     * NULL if none is set.
     *
     * @return mixed
     */
    public function getResult();

    /**
     * Sets the result
     *
     * @param  Result                       $result
     * @return Adapter
     */
    public function setResult(Result $result);

    /**
     * Called before authenticate() method
     *
     * @return void
     */
    public function preAuthenticate();

    /**
     * Called after authenticate() method
     *
     * @return void
     */
    public function postAuthenticate();
}
