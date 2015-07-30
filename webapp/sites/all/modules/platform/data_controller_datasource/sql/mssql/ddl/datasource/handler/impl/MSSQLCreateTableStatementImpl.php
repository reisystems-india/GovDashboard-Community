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


class MSSQLCreateTableStatementImpl extends AbstractCreateTableStatementImpl {

  protected function maxLength4VariableLengthString() {
        return 4000;
    }

    protected function assembleVariableLengthString(ColumnMetaData $column) {
        $column->type->databaseType = "nvarchar({$column->type->length})";
    }

    protected function assembleLongString(ColumnMetaData $column) {
        $column->type->databaseType = 'nvarchar(max)';
    }

    protected function assembleFixedLengthString(ColumnMetaData $column, $selectedLength) {
    	$column->type->databaseType = 'nchar';
    
    	if (isset($selectedLength)) {
    		$column->type->databaseType .= "($selectedLength)";
    	}
    }
    
    protected function assembleInteger(ColumnMetaData $column) {
        $column->type->databaseType = 'int';
    }

    protected function assembleBigInteger(ColumnMetaData $column) {
        $column->type->databaseType = 'bigint';
    }
   
    protected function assembleTinyInteger(ColumnMetaData $column) {
    	$column->type->databaseType = 'tinyint';
    }
    
    protected function assembleSmallInteger(ColumnMetaData $column) {
    	$column->type->databaseType = 'smallint';
    }
    
    protected function assembleNumber(ColumnMetaData $column, $selectedPrecision, $selectedScale) {
        $column->type->databaseType = 'decimal';
    }
    
    protected function assembleDateTime(ColumnMetaData $column) {
    	$column->type->databaseType = 'datetime';
    }    

    protected function getUpdateClauseDelimiter() {
        return '';
    }
}
