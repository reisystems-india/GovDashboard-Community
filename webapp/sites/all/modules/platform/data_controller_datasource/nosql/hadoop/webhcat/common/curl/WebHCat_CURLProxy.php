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


class WebHCat_CURLProxy extends CURLProxy {

    protected $datasource = NULL;

    public function __construct(DataSourceMetaData $datasource) {
        $uri = "{$datasource->protocol}://{$datasource->host}:{$datasource->services->WebHCat->port}";

        parent::__construct($uri, new WebHCat_CURLHandlerOutputFormatter());
        $this->datasource = $datasource;
    }

    protected function prepareQueryParameters(array $queryParameters = NULL) {
        $queryParameters = parent::prepareQueryParameters($queryParameters);

        $queryParameters['user.name'] = $this->datasource->username;

        return $queryParameters;
    }
}

class WebHCat_CURLHandlerOutputFormatter extends CURLHandlerOutputFormatter {

    public function format($resourceId, $output) {
        $output = parent::format($resourceId, $output);

        $result = NULL;
        if (isset($output)) {
            $result = json_decode($output, TRUE);
            if (!isset($result)) {
                throw new IllegalStateException(t('Could not parse output of %resourceId resource call', array('%resourceId' => $resourceId)));
            }

            if (isset($result['error'])) {
                throw new IllegalStateException($result['error']);
            }
        }

        return $result;
    }
}
