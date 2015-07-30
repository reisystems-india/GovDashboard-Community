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

use GD\Security\Authentication\Storage\Storage;
use GD\Security\Authentication\Storage\NonPersistent;
use GD\Security\Authentication\Adapter\Adapter;

class Authentication
{
    /**
     * Authentication adapter
     *
     * @var Adapter
     */
    protected $adapter = NULL;

    /**
     * Persistent storage handler
     *
     * @var Storage
     */
    protected $storage = NULL;

    /**
     * Result result
     *
     * @var Result
     */
    protected $result = NULL;

    /**
     * Constructor
     *
     * @param  Adapter $adapter
     * @param  Storage $storage
     */
    public function __construct(Adapter $adapter = NULL, Storage $storage = NULL) {
        if (NULL !== $adapter) {
            $this->setAdapter($adapter);
        }

        if (NULL !== $storage) {
            $this->setStorage($storage);
        }
    }

    /**
     * Returns the authentication adapter
     *
     * The adapter does not have a default if the storage adapter has not been set.
     *
     * @return Adapter|null
     */
    public function getAdapter() {
        return $this->adapter;
    }

    /**
     * Sets the authentication adapter
     *
     * @param  Adapter $adapter
     * @return AuthenticationService Provides a fluent interface
     */
    public function setAdapter(Adapter $adapter) {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * Returns the persistent storage handler
     *
     * Session storage is used by default unless a different storage adapter has been set.
     *
     * @return Storage
     */
    public function getStorage() {
        if (NULL === $this->storage) {
            $this->setStorage(new NonPersistent());
        }

        return $this->storage;
    }

    /**
     * Sets the persistent storage handler
     *
     * @param  Storage $storage
     * @return AuthenticationService Provides a fluent interface
     */
    public function setStorage(Storage $storage) {
        $this->storage = $storage;
        return $this;
    }

    /**
     * Authenticates against the supplied adapter
     *
     * @param  Adapter $adapter
     * @return Result
     * @throws \RuntimeException
     */
    public function authenticate(Adapter $adapter = NULL) {
        if (!$adapter) {
            if (!$adapter = $this->getAdapter()) {
                throw new \RuntimeException('An adapter must be set or passed prior to calling authenticate()');
            }
        }
        $result = $adapter->authenticate();

        /**
         * prevent multiple successive calls from storing inconsistent results
         * Ensure storage has clean state
         */
        if ($this->hasIdentity()) {
            $this->clearIdentity();
        }

        if ( $result->isValid() ) {
            $this->getStorage()->write($result->getIdentity());
        }

        $this->result = $result;

        return $result;
    }

    /**
     * Returns true if and only if an identity is available from storage
     *
     * @return bool
     */
    public function hasIdentity() {
        return !$this->getStorage()->isEmpty();
    }

    /**
     * Returns the identity from storage or null if no identity is available
     *
     * @return mixed|null
     */
    public function getIdentity() {
        $storage = $this->getStorage();

        if ($storage->isEmpty()) {
            return NULL;
        }

        return $storage->read();
    }

    /**
     * Clears the identity from persistent storage
     *
     * @return void
     */
    public function clearIdentity() {
        $this->getStorage()->clear();
    }

    /**
     * Called before authenticate() method
     *
     * @return void
     */
    public function preAuthenticate () {
        $this->getAdapter()->preAuthenticate();
    }

    /**
     * Called after authenticate() method
     *
     * @return void
     */
    public function postAuthenticate () {
        $this->getAdapter()->postAuthenticate();
    }

    /**
     * Invalidate the authentication
     *
     * @return void
     */
    public function invalidate() {
        $this->getAdapter()->invalidate();
    }

    /**
     * Called when session has timed out
     *
     * @return void
     */
    public function timeout() {
        $this->getAdapter()->timeout();
    }
}
