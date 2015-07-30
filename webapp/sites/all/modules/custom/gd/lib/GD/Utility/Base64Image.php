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


namespace GD\Utility;

class Base64Image {

    protected $basePath;
    protected $filePath;

    public function __construct($basePath, $filePath) {
        $this->filePath = $filePath;
    }

    public function replacePathWithBase64($matches) {
        $path = $matches[1];
        $parts = explode('/', $path);

        $fileParts = explode('/', $this->filePath);
        unset($fileParts[count($fileParts) - 1]);

        //  Find the actual location of the image
        foreach ($parts as $part) {
            if ($part == '..') {
                unset($fileParts[count($fileParts) - 1]);
            } else {
                $fileParts[] = $part;
            }
        }
        return 'url(' . $this->getBase64Image(DRUPAL_ROOT . implode('/', $fileParts)) . ')';
    }

    protected function loadBase64Image($path) {
        if (file_exists($path)) {
            return base64_encode(file_get_contents($path));
        } else {
            return null;
        }
    }

    protected function getBase64Image($path) {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        return 'data:image/' . $type . ';base64,' . $this->loadBase64Image($path);
    }
}