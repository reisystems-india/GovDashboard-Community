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


class CURLHandlerOutputFormatter extends AbstractObject {

    public function format($resourceId, $output) {
        return (!isset($output) || is_bool($output))
            ? NULL
            : StringHelper::trim($output);
    }
}


class CURLHandler extends AbstractObject {

    public $resourceId = NULL;
    public $ch = NULL;
    /**
     * @var CURLHandlerOutputFormatter
     */
    public $outputFormatter = NULL;
}


class CURLProxy extends AbstractObject {

    protected $uri = NULL;
    protected $defaultOutputFormatter = NULL;

    public function __construct($uri, CURLHandlerOutputFormatter $defaultOutputFormatter = NULL) {
        parent::__construct();
        $this->uri = $uri;

        $this->defaultOutputFormatter = $defaultOutputFormatter;
    }

    protected function prepareQueryParameters(array $queryParameters = NULL) {
        return $queryParameters;
    }

    protected function prepareQueryString(array $queryParameters = NULL) {
        $queryString = NULL;

        if (isset($queryParameters)) {
            foreach ($queryParameters as $name => $value) {
                if (isset($queryString)) {
                    $queryString .= '&';
                }
                else {
                    $queryString = '';
                }
                $queryString .= $name . '=' . $value;
            }
        }

        return $queryString;
    }

    protected function addQueryString($uri, $queryString) {
        if (!isset($queryString)) {
            return $uri;
        }

        $p = parse_url($uri);

        $query = isset($p['query']) ? $p['query'] : NULL;
        $query = isset($query) ? ($query . '&' . $queryString) : $queryString;

        return (isset($p['scheme']) ? $p['scheme'] . '://' : '')
            . (isset($p['host']) ? $p['host'] : '')
            . (isset($p['port']) ? ':' . $p['port'] : '')
            . (isset($p['path']) ? $p['path'] : '')
            . (isset($query) ? '?' . $query : '')
            . (isset($p['fragment']) ? '#' . $p['fragment'] : '');
    }

    protected function initializeGetHandlerOptions($ch, $uri, array $queryParameters = NULL) {
        curl_setopt($ch, CURLOPT_URL, $this->addQueryString($uri, $this->prepareQueryString($queryParameters)));
    }

    protected function initializePostHandlerOptions($ch, $uri, array $queryParameters = NULL) {
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        if (isset($queryParameters)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $queryParameters);
        }
    }

    protected function initializeDeleteHandlerOptions($ch, $uri, array $queryParameters = NULL) {
        curl_setopt($ch, CURLOPT_URL, $this->addQueryString($uri, $this->prepareQueryString($queryParameters)));
    }

    public function initializeHandler($method, $resourceId, array $queryParameters = NULL) {
        $uri = $this->uri . $resourceId;
        LogHelper::log_debug("$method $uri");

        $ch = curl_init();
        try {
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

            // adding support for following location header
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 50);

            // updating list of parameters, if it is necessary
            $queryParameters = $this->prepareQueryParameters($queryParameters);

            $optionMethodName = "initialize{$method}HandlerOptions";
            $this->$optionMethodName($ch, $uri, $queryParameters);
        }
        catch (Exception $e) {
            try {
                curl_close($ch);
            }
            catch (Exception $ne) {
                LogHelper::log_error($ne);
            }

            throw $e;
        }

        $handler = new CURLHandler();
        $handler->resourceId = $resourceId;
        $handler->ch = $ch;
        $handler->outputFormatter = $this->defaultOutputFormatter;

        return $handler;
    }
}


abstract class AbstractCURLHandlerExecutor extends AbstractObject {

    public function __destruct() {
        $this->release();
        parent::__destruct();
    }

    abstract public function release();

    protected function errorResourceExecution($resourceId, $message) {
        throw new IllegalStateException(t(
            "Resource '@resourceId' executed with error: @error",
            array('@resourceId' => $resourceId, '@error' => $message)));
    }
}


class SingleCURLHandlerExecutor extends AbstractCURLHandlerExecutor {

    protected $handler = NULL;

    public function __construct(CURLHandler $handler) {
        parent::__construct();
        $this->handler = $handler;
    }

    public function execute() {
        $output = curl_exec($this->handler->ch);
        $isOutputRequired = isset($this->handler->outputFormatter);

        $error = curl_error($this->handler->ch);
        if ($error != '') {
            $this->errorResourceExecution($this->handler->resourceId, $error);
        }

        // storing some information about the execution into log
        $preparedExecutionInfo = NULL;
        ObjectHelper::copySelectedProperties(
            $preparedExecutionInfo, curl_getinfo($this->handler->ch),
            array(
                'url', 'content_type', 'redirect_url', 'http_code', 'redirect_count',
                'namelookup_time', 'connect_time', 'pretransfer_time', 'starttransfer_time', 'redirect_time', 'total_time',
                'size_upload', 'upload_content_length', 'speed_upload',
                'speed_download', 'download_content_length'));
        LogHelper::log_debug($preparedExecutionInfo);

        if ($isOutputRequired) {
            try {
                $output = $this->handler->outputFormatter->format($this->handler->resourceId, $output);
            }
            catch (Exception $e) {
                LogHelper::log_debug(new PreservedTextMessage($output));
                throw $e;
            }
        }

        if ($preparedExecutionInfo->http_code != 200) {
            // only if formatting completed successfully we will reach this point
            $this->errorResourceExecution($this->handler->resourceId, $preparedExecutionInfo->http_code);
        }

        return $isOutputRequired ? $output : NULL;
    }

    public function release() {
        if (isset($this->handler)) {
            try {
                curl_close($this->handler->ch);
            }
            catch (Exception $ne) {
                LogHelper::log_error($ne);
            }

            $this->handler = NULL;
        }
    }
}

class MultipleCURLHandlerExecutor extends AbstractCURLHandlerExecutor {

    protected $handlers = NULL;
    protected $mh = NULL;

    protected $executionState = NULL;

    /**
     * @param CURLHandler[] $handlers
     */
    public function __construct(array $handlers) {
        parent::__construct();
        $this->handlers = $handlers;
    }

    public function start() {
        if (isset($this->mh)) {
            throw new IllegalStateException(t('Multi- execution has already started'));
        }

        // creating threads
        $this->mh = curl_multi_init();
        foreach ($this->handlers as $handler) {
            curl_multi_add_handle($this->mh, $handler->ch);
        }

        $this->executionState = TRUE;
        LogHelper::log_info(t('Starting execution of %threadCount thread(s)', array('%threadCount' => count($this->handlers))));
    }

    protected function checkInitiation() {
        if (!isset($this->mh)) {
            throw new IllegalStateException(t('Multi-thread execution has not been started'));
        }
    }

    public function findCompletedHandler() {
        $this->checkInitiation();

        while (TRUE) {
            $info = curl_multi_info_read($this->mh);
            if ($info === FALSE) {
                if ($this->executionState) {
                    curl_multi_select($this->mh);
                    do {
                        $mrc = curl_multi_exec($this->mh, $this->executionState);
                    }
                    while ($mrc == CURLM_CALL_MULTI_PERFORM);
                    if ($mrc != CURLM_OK) {
                        throw new IllegalStateException(t(
                            'Multi-thread execution could not be completed successfully: %errorCode',
                            array('%errorCode' => $mrc)));
                    }
                }
                else {
                    // there is nothing else to process
                    return FALSE;
                }
            }
            else {
                $ch = $info['handle'];

                $completedHandler = NULL;
                foreach ($this->handlers as $handler) {
                    if ($handler->ch === $ch) {
                        $completedHandler = $handler;
                        break;
                    }
                }
                if (!isset($completedHandler)) {
                    throw new IllegalStateException(t('Could not recognize completed thread'));
                }

                LogHelper::log_info(t('Completed execution of thread for %resourceId resource', array('%resourceId' => $completedHandler->resourceId)));

                if ($info['result'] !== CURLE_OK) {
                    $error = 'Error message is not provided';
                    $this->errorResourceExecution($completedHandler->resourceId, $error);
                }

                return $completedHandler;
            }
        }

        return FALSE;
    }

    public function processResponse(CURLHandler $handler) {
        $output = curl_multi_getcontent($handler->ch);
        $isOutputRequired = isset($handler->outputFormatter);

        if ($isOutputRequired) {
            try {
                $output = $handler->outputFormatter->format($handler->resourceId, $output);
            } catch (Exception $e) {
                LogHelper::log_debug(new PreservedTextMessage($output));
                throw $e;
            }
        }

        $this->releaseHandler($handler);

        return $isOutputRequired ? $output : NULL;
    }

    protected function releaseHandler(CURLHandler $handler) {
        $this->checkInitiation();

        $index = array_search($handler, $this->handlers);
        if ($index === FALSE) {
            throw new IllegalStateException(t('The handler is not recognized'));
        }

        curl_multi_remove_handle($this->mh, $handler->ch);
        unset($this->handlers[$index]);
        curl_close($handler->ch);
    }

    public function release() {
        if (isset($this->mh)) {
            while (($handler = reset($this->handlers)) !== FALSE) {
                try {
                    $this->releaseHandler($handler);
                }
                catch (Exception $ne) {
                    LogHelper::log_error($ne);
                }
            }

            curl_multi_close($this->mh);
            $this->mh = NULL;
        }
    }
}