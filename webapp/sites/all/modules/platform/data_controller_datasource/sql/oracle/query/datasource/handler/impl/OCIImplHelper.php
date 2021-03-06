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


class OCIImplHelper {

    protected static function checkOCIExtension($functionName) {
        if (!function_exists($functionName)) {
            throw new IllegalStateException(t(
                'Could not access %functionName function. OCI PHP extension is not loaded',
                array('%functionName' => $functionName)));
        }
    }

    protected static function oci_error($resource = null) {
        $error = isset($resource) ? oci_error($resource) : oci_error();
        if ($error === FALSE) {
            $error = array('message' => 'No error is found');
        }

        return $error;
    }

    public static function oci_connect($username, $password, $database) {
        self::checkOCIExtension('oci_connect');

        $connection = @oci_connect($username, $password, $database);
        if ($connection === FALSE) {
            $error = self::oci_error();
            throw new IllegalStateException(t(
                'Could not establish database connection for %tnsEntryName entry from tnsnames.ora: %error',
                array('%tnsEntryName' => $database, '%error' => t($error['message']))));
        }

        return $connection;
    }

    public static function oci_parse($connection, $query) {
        self::checkOCIExtension('oci_parse');

        $statement = @oci_parse($connection, $query);
        if ($statement === FALSE) {
            $error = self::oci_error($connection);
            throw new IllegalArgumentException(t(
                'Could not parse SQL statement: %error',
                array('%error' => t($error['message']))));
        }

        return $statement;
    }

    public static function oci_set_prefetch($connection, $statement, $rows) {
        self::checkOCIExtension('oci_set_prefetch');

        $result = @oci_set_prefetch($statement, $rows);
        if ($result === FALSE) {
            $error = self::oci_error($statement);
            throw new IllegalStateException(t(
                'Could not set number of pre fetched records: %error',
                array('%error' => t($error['message']))));
        }
    }

    public static function oci_execute($connection, $statement, $mode = NULL) {
        self::checkOCIExtension('oci_execute');

        $result = @oci_execute($statement, $mode);
        if ($result === FALSE) {
            $error = self::oci_error($statement);
            throw new IllegalStateException(t(
                'Could not execute SQL statement: %error',
                array('%error' => t($error['message']))));
        }
    }

    public static function oci_num_fields($connection, $statement) {
        self::checkOCIExtension('oci_num_fields');

        $columnCount = @oci_num_fields($statement);
        if ($columnCount === FALSE) {
            $error = self::oci_error($statement);
            throw new IllegalStateException(t(
                'Could not retrieve the number of result columns in a statement: %error',
                array('%error' => t($error['message']))));
        }

        return $columnCount;
    }

    public static function oci_field_name($connection, $statement, $fieldNumber) {
        self::checkOCIExtension('oci_field_name');

        $name = @oci_field_name($statement, $fieldNumber);
        if ($name === FALSE) {
            $error = self::oci_error($statement);
            throw new IllegalStateException(t(
                'Could not retrieve the name of a field (field number: %fieldNumber) from the statement: %error',
                array('%fieldNumber' => $fieldNumber, '%error' => t($error['message']))));
        }

        return $name;
    }

    public static function oci_field_type($connection, $statement, $fieldNumber) {
        self::checkOCIExtension('oci_field_type');

        $type = @oci_field_type($statement, $fieldNumber);
        if ($type === FALSE) {
            $error = self::oci_error($statement);
            throw new IllegalStateException(t(
                "Could not retrieve field's (field number: %fieldNumber) data type: %error",
                array('%fieldNumber' => $fieldNumber, '%error' => t($error['message']))));
        }

        return $type;
    }

    public static function oci_field_size($connection, $statement, $fieldNumber) {
        self::checkOCIExtension('oci_field_size');

        $size = @oci_field_size($statement, $fieldNumber);
        if ($size === FALSE) {
            $error = self::oci_error($statement);
            throw new IllegalStateException(t(
                "Could not retrieve field's (field number: %fieldNumber) size: %error",
                array('%fieldNumber' => $fieldNumber, '%error' => t($error['message']))));
        }

        return $size;
    }

    public static function oci_field_precision($connection, $statement, $fieldNumber) {
        self::checkOCIExtension('oci_field_precision');

        $precision = @oci_field_precision($statement, $fieldNumber);
        if ($precision === FALSE) {
            $error = self::oci_error($statement);
            throw new IllegalStateException(t(
                'Could not retrieve the precision of a field (field number: %fieldNumber): %error',
                array('%fieldNumber' => $fieldNumber, '%error' => t($error['message']))));
        }

        return $precision;
    }

    public static function oci_field_scale($connection, $statement, $fieldNumber) {
        self::checkOCIExtension('oci_field_scale');

        $scale = @oci_field_scale($statement, $fieldNumber);
        if ($scale === FALSE) {
            $error = self::oci_error($statement);
            throw new IllegalStateException(t(
                'Could not retrieve the scale of a field (field number: %fieldNumber): %error',
                array('%fieldNumber' => $fieldNumber, '%error' => t($error['message']))));
        }

        return $scale;
    }

    public static function oci_num_rows($connection, $statement) {
        self::checkOCIExtension('oci_num_rows');

        $affectedRecordCount = @oci_num_rows($statement);
        if ($affectedRecordCount === FALSE) {
            $error = self::oci_error($statement);
            throw new IllegalStateException(t(
                'Could not retrieve number of rows affected during statement execution: %error',
                array('%error' => t($error['message']))));
        }

        return $affectedRecordCount;
    }

    public static function oci_fetch_array($connection, $statement, $mode = NULL) {
        self::checkOCIExtension('oci_fetch_array');

        return oci_fetch_array($statement, $mode);
    }

    public static function oci_fetch_all($connection, $statement, &$records, $offset = 0, $limit = -1, $mode = NULL) {
        self::checkOCIExtension('oci_fetch_all');

        // oci_fetch_all converts 'null' array to 'empty' array even if there are no records
        $fixNull = !isset($records);

        $recordCount = @oci_fetch_all($statement, $records, $offset, $limit, $mode);
        if ($recordCount === FALSE) {
            $error = self::oci_error($statement);
            throw new IllegalStateException(t(
                'Could not fetch rows from a query: %error',
                array('%error' => t($error['message']))));
        }

        if ($fixNull && ($recordCount == 0) && isset($records) and (count($records) == 0)) {
            $records = NULL;
        }

        return $recordCount;
    }

    public static function oci_free_statement($connection, $statement) {
        self::checkOCIExtension('oci_free_statement');

        $result = @oci_free_statement($statement);
        if ($result === FALSE) {
            $error = self::oci_error($statement);
            LogHelper::log_warn(t(
                'Could not free all resources associated with statement or cursor: @error',
                array('@error' => t($error['message']))));
        }
    }
}
