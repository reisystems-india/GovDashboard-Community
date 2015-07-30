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


namespace GD\Sync\Import;
use \GD\Sync\Import\Exception\UnsupportedImportOperationException;

abstract class AbstractEntityImport extends \AbstractObject implements EntityImport {

    public function import(ImportStream $stream, ImportContext $context) {
        $operation = $context->get('operation');
        switch ($operation) {
            case 'create' :
                $this->create($stream,$context);
                break;
            case 'update' :
                $this->update($stream,$context);
                break;
            default:
                throw new UnsupportedImportOperationException('Unsupported import operation "'.$operation.'" requested.');
                break;
        }
    }

    protected function create(ImportStream $stream, ImportContext $context) {}

    protected function update(ImportStream $stream, ImportContext $context) {}
}