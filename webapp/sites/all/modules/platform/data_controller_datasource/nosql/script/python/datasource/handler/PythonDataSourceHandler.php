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


class PythonDataSourceHandler extends ScriptDataSourceHandler {

    protected function initiateCURLProxy($uri) {
        return new CURLProxy($uri, new PythonDataSourceHandler_CURLHandlerOutputFormatter());
    }
}


class PythonDataSourceHandler_CURLHandlerOutputFormatter extends CURLHandlerOutputFormatter {

    public function format($resourceId, $output) {
        $output = parent::format($resourceId, $output);

        if (isset($output)) {
            $this->validateResponse($output);
        }

        return $output;
    }

    protected function removeTraceFilePath($message) {
        $prefix = 'File "';
        $suffix = '"';

        $index = 0;
        while (($startIndex = strpos($message, $prefix, $index)) !== FALSE) {
            $startIndex += strlen($prefix);
            $endIndex = strpos($message, $suffix, $startIndex);

            $fileName = substr($message, $startIndex, $endIndex - $startIndex);

            $p1 = strrpos($fileName, '\\');
            if ($p1 === FALSE) {
                $p1 = NULL;
            }
            $p2 = strrpos($fileName, '/');
            if ($p2 === FALSE) {
                $p2 = NULL;
            }
            $p = MathHelper::max($p1, $p2);
            if (isset($p)) {
                $message = substr_replace($message, '', $startIndex, $p + 1);
            }

            $index = $endIndex + 1;
        }

        return $message;
    }

    protected function validateResponse($responseBody) {
        $prefixes = array(
            "<!--",
            "The above is a description of an error in a Python program, formatted",
            "for a Web browser because the 'cgitb' module was enabled.  In case you",
            "are not reading this in a Web browser, here is the original traceback:");

        $suffix = "-->";

        $startIndex = 0;
        // checking prefixes
        foreach ($prefixes as $prefix) {
            $prefixIndex = strpos($responseBody, $prefix, $startIndex);
            if ($prefixIndex === FALSE) {
                return TRUE;
            }
            $startIndex = $prefixIndex + strlen($prefix);
        }
        // checking suffix
        $endIndex = strpos($responseBody, $suffix, $startIndex);
        if ($endIndex === FALSE) {
            return TRUE;
        }

        // error is found. Trying to get message
        $message = substr($responseBody, $startIndex, $endIndex - $startIndex);
        $message = $this->removeTraceFilePath($message);
        $message = ltrim($message, "\r\n");
        $message = rtrim($message);
        $message = htmlspecialchars_decode($message);

        throw new IllegalStateException($message);
    }
}