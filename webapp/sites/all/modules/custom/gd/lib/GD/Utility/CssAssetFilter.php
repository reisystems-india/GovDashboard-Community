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

class CssAssetFilter {

    private $filePath = null;
    private $css = null;

    public function __construct ( $filePath ) {
        $this->filePath = $filePath;
    }

    public function getCss () {
        return $this->css;
    }

    public function embedImages () {
        $this->initialize();
        $this->css = preg_replace_callback($this->getImagePathRegex(), array(new Base64Image(DRUPAL_ROOT, $this->filePath), 'replacePathWithBase64'), $this->css);
        return $this;
    }

    public function fixFontPath () {
        $this->initialize();
        $this->css = preg_replace_callback($this->getFontPathRegex(), array($this, 'replaceFontPath'), $this->css);
        return $this;
    }

    private function initialize() {
        if ( !$this->css ) {
            $this->css = file_get_contents(DRUPAL_ROOT.$this->filePath);
        }
    }

    protected function getFontPathRegex() {
        return '/url\(\s*[\'"]?(\S*\.(?:eot|woff|ttf|svg)([\?\_\#a-z]*))[\'"]?\)[^;}]*?/i';
    }

    protected function replaceFontPath ($matches) {
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
        return 'url(\'' . GOVDASH_HOST.implode('/', $fileParts) . '\')';
    }

    protected function getImagePathRegex() {
        return '/url\(\s*[\'"]?(\S*\.(?:jpe?g|gif|png))[\'"]?\s*\)[^;}]*?/i';
    }


}