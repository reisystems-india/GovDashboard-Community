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


function data_controller_seq_procedure_create_mysql(){

    $module_path = drupal_get_path('module','data_controller_dml');
    $procedure = file_get_contents($module_path.'/_db/sequence/sql/MySQL/sequence_procs.sql');

    //remove delimiter from SQL
    $procedure = str_replace("DELIMITER","",$procedure);

    //remove $$ from procedure
    $procedure = str_replace("$$","",$procedure);

    db_query('SET GLOBAL log_bin_trust_function_creators = 1;');

    db_query($procedure);
}