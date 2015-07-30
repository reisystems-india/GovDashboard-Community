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


namespace GD\Model\Entity\Report\Factory;

use GD\Model\Entity\Factory\AbstractEntityFactory;
use GD\Model\Entity\Report\ReportEntity;

class ReportEntityFactory extends AbstractEntityFactory {

    /**
     * This will handle items returned by the api
     *
     * @param $data
     * @return ReportEntity
     */
    public static function create ( $data ) {


        return new ReportEntity();

    }

    /**
     * This will handle nodes returned by drupal
     *
     * @param $node
     * @return ReportEntity
     */
    public static function createFromNode ( $node ) {


        return new ReportEntity();

    }

} 