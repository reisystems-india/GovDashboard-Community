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


class DefaultTruncateTableStatementImpl extends AbstractTruncateTableStatementImpl {

    public function generate(DataSourceHandler $handler, DatasetMetaData $dataset) {
        $assembledTableName = assemble_database_entity_name($handler, $dataset->datasourceName, $dataset->source);

        // NOTE: we cannot use TRUNCATE TABLE because this operation has to be part of a transaction
        // Also truncate does to work in some databases when there are FOREIGN KEYs pointing to this table
        return "DELETE FROM $assembledTableName";
    }
}
