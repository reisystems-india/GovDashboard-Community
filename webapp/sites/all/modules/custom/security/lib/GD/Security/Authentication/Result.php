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


namespace GD\Security\Authentication;

class Result
{
    /**
     * General Failure
     */
    const FAILURE                        =  0;

    /**
     * Failure due to identity not being found.
     */
    const FAILURE_IDENTITY_NOT_FOUND     = -1;

    /**
     * Failure due to identity being ambiguous.
     */
    const FAILURE_IDENTITY_AMBIGUOUS     = -2;

    /**
     * Failure due to invalid credential being supplied.
     */
    const FAILURE_CREDENTIAL_INVALID     = -3;

    /**
     * Failure due to uncategorized reasons.
     */
    const FAILURE_UNCATEGORIZED          = -4;

    /**
     * Authentication success.
     */
    const SUCCESS                        =  1;

    /**
     * Authentication result code
     *
     * @var int
     */
    protected $code;

    /**
     * The identity used in the authentication attempt
     *
     * @var mixed
     */
    protected $identity;

    /**
     * An array of string reasons why the authentication attempt was unsuccessful
     *
     * If authentication was successful, this should be an empty array.
     *
     * @var array
     */
    protected $messages;

    /**
     * Sets the result code, identity, and failure messages
     *
     * @param  int     $code
     * @param  mixed   $identity
     * @param  array   $messages
     */
    public function __construct($code, $identity, array $messages = array())
    {
        $this->code     = (int) $code;
        $this->identity = $identity;
        $this->messages = $messages;
    }

    /**
     * Returns whether the result represents a successful authentication attempt
     *
     * @return bool
     */
    public function isValid()
    {
        return ($this->code > 0) ? true : false;
    }

    /**
     * getCode() - Get the result code for this authentication attempt
     *
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Returns the identity used in the authentication attempt
     *
     * @return mixed
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * Returns an array of string reasons why the authentication attempt was unsuccessful
     *
     * If authentication was successful, this method returns an empty array.
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
