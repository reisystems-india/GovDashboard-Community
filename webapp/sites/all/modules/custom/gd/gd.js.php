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


function gd_get_js_library() {
    ob_start();
    header('Content-Type: text/javascript; charset=UTF-8');
    echo gd_get_js_library_contents();
    drupal_exit();
}

function gd_get_js_library_contents () {
    ob_start();

    foreach ( \GD\Js\Registry::getInstance()->getFiles() as $file ) {
        echo "\n".file_get_contents($file).";\n";
    }

    echo 'GD.options.host = "'.GOVDASH_HOST.'";';

    return ob_get_clean();
}