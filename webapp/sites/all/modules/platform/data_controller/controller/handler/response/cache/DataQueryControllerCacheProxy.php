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

class DataQueryControllerCacheProxy extends AbstractObject {

    protected $cache = NULL;
    protected $cacheEntryName = NULL;

    public function __construct(AbstractQueryRequest $request) {
        parent::__construct();

        $this->cacheEntryName = $this->prepareCacheEntryName($request);
        $this->cache = isset($this->cacheEntryName) ? new SharedCacheFactoryProxy($this, 'data', FALSE) : NULL;
    }

    protected function prepareCacheEntryName(AbstractQueryRequest $request) {
        $serializer = NULL;
        if ($request instanceof DatasetQueryRequest) {
            $serializer = new DatasetQueryUIRequestSerializer();
        }
        elseif ($request instanceof DatasetCountRequest) {
            $serializer = new DatasetCountUIRequestSerializer();
        }
        elseif ($request instanceof CubeQueryRequest) {
            $serializer = new CubeQueryUIRequestSerializer();
        }
        elseif ($request instanceof CubeCountRequest) {
            $serializer = new CubeQueryUIRequestSerializer();
        }
        if (!isset($serializer)) {
            return NULL;
        }

        $parameters = $serializer->serialize($request);
        $parameters['request'] = get_class($request);
        $parameters['src'] = $request->sourceName;

        // adding version of each formula
        $versions = NULL;
        $formulaNames = $request->getFormulaNames();
        if (isset($formulaNames)) {
            foreach ($formulaNames as $formulaName) {
                $formula = $request->getFormula($formulaName);
                if (isset($formula->version)) {
                    $versions[] = $formula->version;
                }
            }
        }
        if (isset($versions)) {
            sort($versions);
            ArrayHelper::merge($parameters, $serializer->serializeValue('ver', $versions));
        }

        ksort($parameters);

        $entryName = '';
        foreach ($parameters as $name => $value) {
            if ($entryName != '') {
                $entryName .= '&';
            }
            $entryName .= $name . '=' . $value;
        }

        if (($request instanceof AbstractCubeQueryRequest) && isset($request->referencedRequests)) {
            foreach ($request->referencedRequests as $referencedRequest) {
                $referencedEntryName = $this->prepareCacheEntryName($referencedRequest);
                $entryName .= '{' . $referencedEntryName . '}';
            }
        }

        return $entryName;
    }

    public function getCachedResult() {
        $envelope = isset($this->cacheEntryName) ? $this->cache->getCachedEntry($this->cacheEntryName) : NULL;
        if (!isset($envelope)) {
            return array(NULL, FALSE);
        }

        $data = $envelope->data;

        LogHelper::log_info(t('Loaded %count record(s) from cache', array('%count' => count($data))));
        LogHelper::log_debug($data);

        return array($data, TRUE);
    }

    public function cacheResult($data) {
        if (isset($this->cacheEntryName)) {
            $envelope = new __DataQueryControllerCache_DataEnvelope();
            $envelope->data = $data;

            $this->cache->cacheEntry($this->cacheEntryName, $envelope);
        }
    }
}


class __DataQueryControllerCache_DataEnvelope extends AbstractObject {

    public $data = NULL;
}
