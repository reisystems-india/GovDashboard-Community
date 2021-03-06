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


class MethodsDocumentGenerator extends AbstractDocumentGenerator {

    public static $CSS_CLASS__METHODS = 'dpc_methods';

    protected function startGeneration(&$buffer) {
        $buffer .= self::startTag('div', self::$CSS_CLASS__METHODS);
    }

    protected function finishGeneration(&$buffer) {
        $buffer .= self::endTag('div');
    }

    protected function startNestedGeneration(&$buffer) {
        $buffer .= self::startTag('table', self::$CSS_CLASS__METHODS);

        $buffer .= self::startTag('tr');
        $buffer .= self::startTag('th') . 'URI' . self::endTag('th');
        $buffer .= self::startTag('th') . 'Body' . self::endTag('th');
        $buffer .= self::startTag('th') . 'Description' . self::endTag('th');
        $buffer .= self::endTag('tr');
    }

    protected function finishNestedGeneration(&$buffer) {
        $buffer .= self::endTag('table');
    }
}
