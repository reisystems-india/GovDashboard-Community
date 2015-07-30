<?php

class IgbinaryDatabaseCache extends DrupalDatabaseCache {

    /**
     * Prepares a cached item.
     *
     * Checks that items are either permanent or did not expire, and unserializes
     * data as appropriate.
     *
     * @param $cache
     *   An item loaded from cache_get() or cache_get_multiple().
     *
     * @return bool|mixed
     *   The item with data unserialized as appropriate or FALSE if there is no
     *   valid item to load.
     */
    protected function prepareItem($cache) {
        global $user;

        if (!isset($cache->data)) {
            return false;
        }
        // If the cached data is temporary and subject to a per-user minimum
        // lifetime, compare the cache entry timestamp with the user session
        // cache_expiration timestamp. If the cache entry is too old, ignore it.
        if ($cache->expire != CACHE_PERMANENT && variable_get('cache_lifetime', 0) && isset($_SESSION['cache_expiration'][$this->bin]) && $_SESSION['cache_expiration'][$this->bin] > $cache->created) {
            // Ignore cache data that is too old and thus not valid for this user.
            return false;
        }

        // If the data is permanent or not subject to a minimum cache lifetime,
        // unserialize and return the cached data.
        if ($cache->serialized) {
            $cache->data = igbinary_unserialize($cache->data);
        }

        return $cache;
    }


    /**
     * Implements DrupalCacheInterface::set().
     */
    function set($cid, $data, $expire = CACHE_PERMANENT) {
        $fields = array(
            'serialized' => 0,
            'created' => REQUEST_TIME,
            'expire' => $expire,
        );
        if (!is_string($data)) {
            $fields['data'] = igbinary_serialize($data);
            $fields['serialized'] = 1;
        }
        else {
            $fields['data'] = $data;
            $fields['serialized'] = 0;
        }

        try {
            db_merge($this->bin)
              ->key(array('cid' => $cid))
              ->fields($fields)
              ->execute();
        }
        catch (Exception $e) {
            // The database may not be available, so we'll ignore cache_set requests.
        }
    }
}