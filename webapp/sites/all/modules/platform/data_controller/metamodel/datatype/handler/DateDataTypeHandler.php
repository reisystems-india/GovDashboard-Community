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


class DateTimeProxy extends AbstractObject {

    private $datetime = NULL;

    private $year = NULL;
    private $quarter = NULL;
    private $month = NULL;
    private $day = NULL;

    public function __construct(DateTime $datetime = NULL) {
        parent::__construct();

        $this->datetime = isset($datetime) ? $datetime : new DateTime();
    }

    public function getYear() {
        if (!isset($this->year)) {
            $this->year = (int) $this->datetime->format('Y');
        }

        return $this->year;
    }

    public function getQuarter() {
        if (!isset($this->quarter)) {
            $this->quarter = self::getQuarterByMonth($this->getMonth());
        }

        return $this->quarter;
    }

    public static function getQuarterByMonth($month) {
        return (int) (($month - 1) / 3 + 1);
    }

    public static function getFirstMonthOfQuarter($quarter) {
        return ($quarter - 1) * 3 + 1;
    }

    public static function getLastMonthOfQuarter($quarter) {
        return $quarter * 3;
    }

    public function getMonth() {
        if (!isset($this->month)) {
            $this->month = (int) $this->datetime->format('m');
        }

        return $this->month;
    }

    public function getDay() {
        if (!isset($this->day)) {
            $this->day = (int) $this->datetime->format('j');
        }

        return $this->day;
    }
}


abstract class AbstractDateTimeDataTypeHandler extends AbstractDataTypeHandler {

    protected function isValueOfImpl(&$value) {
        // PHP does not support neither 'date' or 'time' natively
        return FALSE;
    }

    protected function isSeparatorPresent($characterUsage, $separator, $minimumCount) {
        $separatorCode = ord($separator);

        // the separator should be present for at least specified number of times
        return isset($characterUsage[$separatorCode]) && ($characterUsage[$separatorCode] >= $minimumCount);
    }

    protected function checkCharacterUsage($characterUsage) {
        return TRUE;
    }

    protected function isParsableImpl(&$value) {
        if (!parent::isParsableImpl($value)) {
            return FALSE;
        }

        // We need at least two '/', '-', '.' or ' ' to proceed
        $characterUsage = count_chars($value, 1);
        if (!$this->checkCharacterUsage($characterUsage)) {
            return FALSE;
        }

        return TRUE;
    }
}

abstract class AbstractDateDataTypeHandler extends AbstractDateTimeDataTypeHandler {

    protected function checkCharacterUsage($characterUsage) {
        return parent::checkCharacterUsage($characterUsage)
            && ($this->isSeparatorPresent($characterUsage, ' ', 2)
                || $this->isSeparatorPresent($characterUsage, '/', 2)
                || $this->isSeparatorPresent($characterUsage, '-', 2)
                || $this->isSeparatorPresent($characterUsage, '.', 2));
    }

    protected function castToStorageValueImpl($value) {
        $dt = DateTime::createFromFormat($this->getFormat(), $value);

        return $dt->format($this->getStorageFormat());
    }
}

abstract class AbstractTimeDataTypeHandler extends AbstractDateTimeDataTypeHandler {

    protected function checkCharacterUsage($characterUsage) {
        return parent::checkCharacterUsage($characterUsage) && $this->isSeparatorPresent($characterUsage, ':', 1);
    }
}

class DateDataTypeHandler extends AbstractDateDataTypeHandler {

    const DATA_TYPE = 'date2';

    public static $FORMAT_DEFAULT = 'm/d/Y';
    public static $FORMAT_CUSTOM = NULL;
    public static $FORMAT_STORAGE = 'm/d/Y';

    public function getName() {
        return self::DATA_TYPE;
    }

    public function getPublicName() {
        return t('Date');
    }

    public function getFormat() {
        return isset(self::$FORMAT_CUSTOM) ? self::$FORMAT_CUSTOM : self::$FORMAT_DEFAULT;
    }

    public function selectCompatible($datatype) {
        return ($datatype == DateTimeDataTypeHandler::DATA_TYPE)
            ? DateTimeDataTypeHandler::DATA_TYPE
            : parent::selectCompatible($datatype);
    }

    protected function isParsableImpl(&$value) {
        if (!parent::isParsableImpl($value)) {
            return FALSE;
        }

        $minDateLength = 6; // at least: day[1] + separator + month[1] + separator + year[2]
        if (strlen($value) < $minDateLength) {
            return FALSE;
        }

        // do not use class style. We do not need an exception to be thrown
        $info = date_parse($value);
        return ($info !== FALSE)
            // errors
            && (count($info['warnings']) == 0)
            && ($info['error_count'] == 0)
            && (count($info['errors']) == 0)
            // date
            && ($info['year'] !== FALSE)
            && ($info['month'] !== FALSE)
            && ($info['day'] !== FALSE)
            // time
            && ($info['hour'] === FALSE)
            && ($info['minute'] === FALSE)
            && ($info['second'] === FALSE)
            && ($info['fraction'] === FALSE);
    }

    protected function castValueImpl($value) {
        // do not use procedural style. We need an exception in case of error
        try  {
            $dt = new DateTime($value);
        }
        catch (Exception $e) {
            LogHelper::log_error($e);
            throw new IllegalArgumentException(t('Failed to parse date string: %value', array('%value' => $value)));
        }

        return $dt->format($this->getFormat());
    }

    public function getStorageDataType() {
        return self::DATA_TYPE;
    }

    public function getStorageFormat() {
        return self::$FORMAT_STORAGE;
    }
}

class TimeDataTypeHandler extends AbstractTimeDataTypeHandler {

    const DATA_TYPE = 'time';

    public static $FORMAT_DEFAULT = 'h:i:s a';
    public static $FORMAT_CUSTOM = NULL;
    public static $FORMAT_STORAGE = 'H:i:s';

    public function getName() {
        return self::DATA_TYPE;
    }

    public function getPublicName() {
        return t('Time');
    }

    public function getFormat() {
        return isset(self::$FORMAT_CUSTOM) ? self::$FORMAT_CUSTOM : self::$FORMAT_DEFAULT;
    }

    public function selectCompatible($datatype) {
        return ($datatype == DateTimeDataTypeHandler::DATA_TYPE)
            ? DateTimeDataTypeHandler::DATA_TYPE
            : parent::selectCompatible($datatype);
    }

    protected function isParsableImpl(&$value) {
        if (!parent::isParsableImpl($value)) {
            return FALSE;
        }

        $minDateLength = 3; // at least: hour[1] + separator + minute[1]
        if (strlen($value) < $minDateLength) {
            return FALSE;
        }

        // do not use class style. We do not need an exception to be thrown
        $info = date_parse($value);
        return ($info !== FALSE)
            // errors
            && (count($info['warnings']) == 0)
            && ($info['error_count'] == 0)
            && (count($info['errors']) == 0)
            // date
            && ($info['year'] === FALSE)
            && ($info['month'] === FALSE)
            && ($info['day'] === FALSE)
            // time
            && ($info['hour'] !== FALSE)
            && ($info['minute'] !== FALSE)
            && ($info['second'] !== FALSE)
            && ($info['fraction'] !== FALSE);
    }

    protected function castValueImpl($value) {
        // do not use procedural style. We need an exception in case of error
        try  {
            $dt = new DateTime($value);
        }
        catch (Exception $e) {
            LogHelper::log_error($e);
            throw new IllegalArgumentException(t('Failed to parse time string: %value', array('%value' => $value)));
        }

        return $dt->format($this->getFormat());
    }

    public function getStorageDataType() {
        return self::DATA_TYPE;
    }

    public function getStorageFormat() {
        return self::$FORMAT_STORAGE;
    }
}

class DateTimeDataTypeHandler extends AbstractDateDataTypeHandler {

    const DATA_TYPE = 'datetime';

    public static $FORMAT_DEFAULT = 'm/d/Y h:i:s a';
    public static $FORMAT_CUSTOM = NULL;
    public static $FORMAT_STORAGE = 'm/d/Y H:i:s';

    public function getName() {
        return self::DATA_TYPE;
    }

    public function getPublicName() {
        return t('Date & Time');
    }

    public function getFormat() {
        return isset(self::$FORMAT_CUSTOM) ? self::$FORMAT_CUSTOM : self::$FORMAT_DEFAULT;
    }

    protected function checkCharacterUsage($characterUsage) {
        return parent::checkCharacterUsage($characterUsage) && $this->isSeparatorPresent($characterUsage, ':', 1);
    }

    protected function isParsableImpl(&$value) {
        if (!parent::isParsableImpl($value)) {
            return FALSE;
        }

        $minDateLength = 10; // at least: day[1] + separator + month[1] + separator + year[2] + delimiter(space) + hour[1] + separator + minute[1]
        if (strlen($value) < $minDateLength) {
            return FALSE;
        }

        // do not use class style. We do not need an exception to be thrown
        $info = date_parse($value);
        return ($info !== FALSE)
            // errors
            && (count($info['warnings']) == 0)
            && ($info['error_count'] == 0)
            && (count($info['errors']) == 0)
            // date
            && ($info['year'] !== FALSE)
            && ($info['month'] !== FALSE)
            && ($info['day'] !== FALSE)
            // time
            && ($info['hour'] !== FALSE)
            && ($info['minute'] !== FALSE)
            && ($info['second'] !== FALSE)
            && ($info['fraction'] !== FALSE);
    }

    protected function castValueImpl($value) {
        // do not use procedural style. We need an exception in case of error
        try  {
            $dt = new DateTime($value);
        }
        catch (Exception $e) {
            LogHelper::log_error($e);
            throw new IllegalArgumentException(t('Failed to parse datetime string: %value', array('%value' => $value)));
        }

        return $dt->format($this->getFormat());
    }

    public function getStorageDataType() {
        return self::DATA_TYPE;
    }

    public function getStorageFormat() {
        return self::$FORMAT_STORAGE;
    }
}
