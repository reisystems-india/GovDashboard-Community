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


abstract class AbstractCommentStripper extends AbstractDataConverter {

    abstract protected function getSingleLineCommentRegularExpression();

    protected function getMultiLineCommentRegularExpression() {
        // removing /* ... */
        return '#/\*(.|[\r\n])*?\*/#';
    }

    public function convert($input) {
        $output = $input;

        if (isset($output)) {
            $output = preg_replace($this->getSingleLineCommentRegularExpression(), ' ', $output);
            $output = preg_replace($this->getMultiLineCommentRegularExpression(), ' ', $output);

            $output = StringHelper::trim($output);
        }

        return $output;
    }
}


class CommentStripper extends AbstractCommentStripper {

    protected function getSingleLineCommentRegularExpression() {
        // lines marked with //
        return '#(^|[\r\n|\x20+])//.*#';
    }
}
