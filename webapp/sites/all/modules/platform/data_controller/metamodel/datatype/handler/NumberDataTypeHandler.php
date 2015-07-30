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


abstract class AbstractNumberDataTypeHandler extends AbstractDataTypeHandler {

    public static $MAX_DIGIT_NUMBER = 14;

    public $decimalSeparatorSymbol = NULL;
    protected $numberFormatter = NULL;

    public function __construct() {
        parent::__construct();

        $this->numberFormatter = new NumberFormatter(Environment::getInstance()->getLocale(), $this->getNumberStyle());

        $this->decimalSeparatorSymbol = StringHelper::trim($this->numberFormatter->getSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL));
        if ($this->decimalSeparatorSymbol === FALSE) {
            throw new IllegalStateException(t('Cannot detect OS decimal separator symbol'));
        }
    }

    protected function getNumberStyle() {
        return NumberFormatter::DECIMAL;
    }

    protected function getNumberType() {
        return NumberFormatter::TYPE_DOUBLE;
    }

    public function parse($value) {
        $offset = 0;
        $n = $this->numberFormatter->parse($value, $this->getNumberType(), $offset);

        return ($n === FALSE) || ($offset != strlen($value))
            ? FALSE
            : $n;
    }

    protected function isParsableImpl(&$value) {
        if (!parent::isParsableImpl($value)) {
            return FALSE;
        }

        $adjustedValue = strtoupper($value);
        $adjustedValueLength = strlen($adjustedValue);

        $isNumber = ($this->parse($adjustedValue) !== FALSE);

        // GOVDB-284. It is correct number. Adding check to prevent possible rounding or converting to scientific format by PHP
        if ($isNumber && ($adjustedValueLength > self::$MAX_DIGIT_NUMBER)) {
            $count = 0;
            for ($i = 0; $i < $adjustedValueLength; $i++) {
                $char = $adjustedValue[$i];
                if (($char >= '0') && ($char <= '9')) {
                    $count++;
                }
            }
            if ($count > self::$MAX_DIGIT_NUMBER) {
                $isNumber = FALSE;
            }
        }

        return $isNumber;
    }

    protected function adjustValue($value) {
        $adjustedValue = parent::adjustValue($value);
        if (is_string($adjustedValue)) {
            $adjustedValue = str_replace(' ', '', $adjustedValue);
        }

        return $adjustedValue;
    }

    protected function errorCastValue($value) {
        throw new IllegalArgumentException(t('%value is not of %type data type', array('%type' => $this->getPublicName(), '%value' => $value)));
    }
}

abstract class AbstractIntegerDataTypeHandler extends AbstractNumberDataTypeHandler {

    const NUMBER_TYPE = NumberFormatter::TYPE_INT32;

    protected function getNumberType() {
        return self::NUMBER_TYPE;
    }

    protected function isValueOfImpl(&$value) {
        return parent::isValueOfImpl($value) && is_int($value);
    }

    protected function isParsableImpl(&$value) {
        if (!parent::isParsableImpl($value)) {
            return FALSE;
        }

        return (strpos($value, $this->decimalSeparatorSymbol) === FALSE)
            && (($value[0] != '0') || (strlen($value) === 1));
    }

    protected function castValueImpl($value) {
        $n = $this->parse($value);
        if ($n === FALSE) {
            $this->errorCastValue($value);
        }

        return $n;
    }
}

class IntegerDataTypeHandler extends AbstractIntegerDataTypeHandler {

    const DATA_TYPE = 'integer';

    public function getName() {
        return self::DATA_TYPE;
    }

    public function getPublicName() {
        return t('Integer');
    }

    public static function checkNonNegativeInteger($value) {
        if (!isset($value)) {
            return;
        }

        DataTypeFactory::getInstance()->checkValueType(self::DATA_TYPE, $value);

        if ($value < 0) {
            LogHelper::log_error(t("'@value' is a negative integer", array('@value' => $value)));
            throw new IllegalArgumentException(t('Value is a negative integer'));
        }
    }

    public static function checkPositiveInteger($value) {
        if (!isset($value)) {
            return;
        }

        DataTypeFactory::getInstance()->checkValueType(self::DATA_TYPE, $value);

        if ($value <= 0) {
            LogHelper::log_error(t("'@value' has to be positive integer", array('@value' => $value)));
            throw new IllegalArgumentException(t('Value has to be positive integer'));
        }
    }

    public function selectCompatible($datatype) {
        return ($datatype == NumberDataTypeHandler::DATA_TYPE)
            ? $datatype
            : parent::selectCompatible($datatype);
    }

    public function getStorageDataType() {
        return self::DATA_TYPE;
    }
}

class NumberDataTypeHandler extends AbstractNumberDataTypeHandler {

    const DATA_TYPE = 'number';

    public function getName() {
        return self::DATA_TYPE;
    }

    public function getPublicName() {
        return t('Number');
    }

    protected function isValueOfImpl(&$value) {
        return parent::isValueOfImpl($value) && !is_string($value) && is_numeric($value) && !is_int($value);
    }

    protected function isParsableImpl(&$value) {
        if (!parent::isParsableImpl($value)) {
            return FALSE;
        }

        $decimalSeparatorIndex = strpos($value, $this->decimalSeparatorSymbol);

        // to support integer-like numbers such as 12345678901234 which cannot be mapped to integer32 type
        $isInteger = ($decimalSeparatorIndex === FALSE)
            ? $this->numberFormatter->parse($value, IntegerDataTypeHandler::NUMBER_TYPE)
            : FALSE;

        return (!$isInteger) && (($value[0] != '0') || ($decimalSeparatorIndex === 1));
    }

    protected function castValueImpl($value) {
        $n = $this->parse($value);
        if ($n === FALSE) {
            $currency = new CurrencyDataTypeHandler();
            $n = $currency->parse($value);
        }
        if ($n === FALSE) {
            $percent = new PercentDataTypeHandler();
            $n = $percent->parse($value);
        }

        if ($n === FALSE) {
            $this->errorCastValue($value);
        }

        return $n;
    }

    public function getStorageDataType() {
        return self::DATA_TYPE;
    }
}

class CurrencyDataTypeHandler extends AbstractNumberDataTypeHandler {

    const DATA_TYPE = 'currency';

    public static $SUFFIX_THOUSANDS = 'K';
    public static $SUFFIX_MILLIONS = 'M';
    public static $SUFFIX_BILLIONS = 'B';
    public static $SUFFIX_TRILLIONS = 'T';

    protected $currencySymbol = NULL;

    public function __construct() {
        parent::__construct();
        $this->currencySymbol = StringHelper::trim($this->numberFormatter->getSymbol(NumberFormatter::CURRENCY_SYMBOL));
        if ($this->currencySymbol === FALSE) {
            throw new IllegalStateException(t('Cannot detect OS currency symbol'));
        }
    }

    public function getName() {
        return self::DATA_TYPE;
    }

    public function getPublicName() {
        return t('Currency');
    }

    protected function getSuffixConfigurations() {
        return array(
            self::$SUFFIX_THOUSANDS => pow(10, 3),
            self::$SUFFIX_MILLIONS => pow(10, 6),
            self::$SUFFIX_BILLIONS => pow(10, 9),
            self::$SUFFIX_TRILLIONS => pow(10, 12));
    }

    protected function getNumberStyle() {
        return NumberFormatter::CURRENCY;
    }

    protected function getNumberType() {
        return NumberFormatter::TYPE_CURRENCY;
    }

    public function parse($value) {
        $adjustedValue = $value;

        // checking if we have negative value sign
        // For the US correct negative format is ($10,789.34) but some systems do use -$10,789.34 or $-10,789.34 or etc. instead
        $negativeSignIndex = strpos($adjustedValue, '-');
        $isNegative = $negativeSignIndex !== FALSE;
        if ($isNegative) {
            // we replace ONLY first occurrence of '-'.
            // If the value contains several such occurrences it means the value is incorrect and the following parser will return FALSE
            $adjustedValue = substr_replace($adjustedValue, '', $negativeSignIndex, 1);
        }

        // checking for number in the thousands, millions, billions and etc.
        $detectedMultiplier = NULL;
        $suffixes = $this->getSuffixConfigurations();
        if (isset($suffixes)) {
            $selectedSuffix = NULL;

            foreach ($suffixes as $suffix => $multiplier) {
                $suffixIndex = strpos($adjustedValue, $suffix);
                if ($suffixIndex !== FALSE) {
                    // two suffixes cannot be supported
                    if (isset($selectedSuffix)) {
                        $selectedSuffix = NULL;
                        break;
                    }

                    $selectedSuffix = $suffix;
                }
            }

            if (isset($selectedSuffix)) {
                $suffixLength = strlen($selectedSuffix);
                $suffixIndex = strpos($adjustedValue, $selectedSuffix);
                // suffix should be at the end of value
                if (strlen($adjustedValue) == ($suffixIndex + $suffixLength)) {
                    // removing spaces before the suffix ... if any
                    $startingIndex = $suffixIndex;
                    while ($startingIndex > 0) {
                        if ($adjustedValue[$startingIndex - 1] == ' ') {
                            $startingIndex--;
                        }
                        else {
                            break;
                        }
                    }
                    $adjustedValue = substr_replace($adjustedValue, '', $startingIndex, $suffixIndex - $startingIndex + $suffixLength);

                    $detectedMultiplier = $suffixes[$selectedSuffix];
                }
            }
        }

        $adjustedValueLength = strlen($adjustedValue);

        $offset = 0;
        $n = $this->numberFormatter->parseCurrency($adjustedValue, $currencyName, $offset);
        if (($n === FALSE) || ($offset != $adjustedValueLength)) {
            return FALSE;
        }

        if (isset($detectedMultiplier)) {
            $n *= $detectedMultiplier;
        }

        if ($isNegative) {
            $n *= -1;
        }

        return $n;
    }

    protected function isValueOfImpl(&$value) {
        return parent::isValueOfImpl($value) && !is_string($value) && is_numeric($value) && !is_int($value);
    }

    public function selectCompatible($datatype) {
        if ($datatype === NumberDataTypeHandler::DATA_TYPE) {
            return $datatype;
        }

        return parent::selectCompatible($datatype);
    }

    protected function isParsableImpl(&$value) {
        if (!parent::isParsableImpl($value)) {
            return FALSE;
        }

        return strpos($value, $this->currencySymbol) !== FALSE;
    }

    protected function castValueImpl($value) {
        // at first we try to cast to number. If that does not work we try to cast to currency
        $nf = new NumberFormatter(Environment::getInstance()->getLocale(), NumberFormatter::DECIMAL);
        $offset = 0; // we need to use $offset because in case of error parse() returns 0 instead of FALSE
        $n = $nf->parse($value, NumberFormatter::TYPE_DOUBLE, $offset);
        if (($n === FALSE) || ($offset != strlen($value))) {
            $n = $this->parse($value);
        }
        if ($n === FALSE) {
            $this->errorCastValue($value);
        }

        return $n;
    }

    public function getStorageDataType() {
        return self::DATA_TYPE;
    }
}

class PercentDataTypeHandler extends AbstractNumberDataTypeHandler {

    const DATA_TYPE = 'percent';

    public function getName() {
        return self::DATA_TYPE;
    }

    public function getPublicName() {
        return t('Percent');
    }

    protected function getNumberStyle() {
        return NumberFormatter::PERCENT;
    }

    protected function isValueOfImpl(&$value) {
        return parent::isValueOfImpl($value) && !is_string($value) && is_numeric($value) && !is_int($value);
    }

    public function selectCompatible($datatype) {
        if ($datatype === NumberDataTypeHandler::DATA_TYPE) {
            return $datatype;
        }

        return parent::selectCompatible($datatype);
    }

    protected function castValueImpl($value) {
        // at first we try to cast to number. If that does not work we try to cast to percent
        $nf = new NumberFormatter(Environment::getInstance()->getLocale(), NumberFormatter::DECIMAL);
        $offset = 0; // we need to use $offset because in case of error parse() returns 0 instead of FALSE
        $n = $nf->parse($value, NumberFormatter::TYPE_DOUBLE, $offset);
        if (($n === FALSE) || ($offset != strlen($value))) {
            $n = $this->parse($value);
        }
        if ($n === FALSE) {
            $this->errorCastValue($value);
        }

        return $n;
    }

    public function getStorageDataType() {
        return self::DATA_TYPE;
    }
}
