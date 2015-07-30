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


class WebHDFS_CURLProxy extends CURLProxy {

    public function __construct(DataSourceMetaData $datasource) {
        $uri = "{$datasource->protocol}://{$datasource->host}:{$datasource->services->WebHDFS->port}";

        parent::__construct($uri, new WebHDFS_CURLHandlerOutputFormatter());
    }
}

class WebHDFS_CURLHandlerOutputFormatter extends CURLHandlerOutputFormatter {

    public function format($resourceId, $output) {
        $output = parent::format($resourceId, $output);

        if (isset($output)) {
            $parsedOutput = json_decode($output, TRUE);
            if (isset($parsedOutput)) {
                if (isset($parsedOutput['RemoteException'])) {
                    $message = isset($parsedOutput['RemoteException']['message'])
                        ? $parsedOutput['RemoteException']['message']
                        : 'Error message is not provided';

                    throw new IllegalStateException(t(
                        'Resource %resourceId executed with error: %error',
                        array(
                            '%resourceId' => $resourceId,
                            '%error' => $message)));
                }
            }
        }

        return $output;
    }
}
