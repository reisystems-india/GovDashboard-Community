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


abstract class AbstractColumnNameGenerator extends AbstractObject {

    protected function validateCharacter($c) {
        $d = ord($c);

        return ($d >= 32) && ($d <= 127);
    }

    protected function replaceIneligibleCharacters($columnName, $replacement) {
        $updatedColumnName = '';

        // preg_replace('#\W+#', $replacement, $columnName) does not work as we would like it to
        for ($i = 0, $l = strlen($columnName); $i < $l; $i++) {
            $c = $columnName[$i];
            if (($c == $replacement) || $this->validateCharacter($c)) {
                // acceptable character
            }
            else {
                $c = $replacement;
            }
            $updatedColumnName .= $c;
        }

        return $updatedColumnName;
    }

    abstract public function generate($columnName);
}


class ColumnNameGenerator extends AbstractColumnNameGenerator {

    private $columnPrefixName = NULL;

    public function __construct($columnPrefixName = NULL) {
        parent::__construct();
        $this->columnPrefixName = $columnPrefixName;
    }


    protected function replaceSpecialCharactersWithCorrespondingText($columnName, $wordSeparator) {
        return str_replace(
            array(
                '#',
                '&'),
            array(
                $wordSeparator .'number' . $wordSeparator,
                $wordSeparator . 'and' . $wordSeparator),
            $columnName);
    }

    protected function validateCharacter($c) {
        $valid = parent::validateCharacter($c);
        if ($valid) {
            $valid = ($c == '_') || ($c >= '0' && $c <= '9') || ($c >= 'A' && $c <= 'Z') || ($c >= 'a' && $c <= 'z');
        }

        return $valid;
    }

    protected function moveLeadingDigitToEnd($columnName) {
        $columnNameParts = explode('_', $columnName);
        if ($columnNameParts === FALSE) {
            return $columnName;
        }

        $updatedColumnName = $columnNameSuffix = '';
        foreach ($columnNameParts as $part) {
            if ($part == '') {
                continue;
            }

            if (($updatedColumnName == '') && is_numeric($part)) {
                if ($columnNameSuffix != '') {
                    $columnNameSuffix .= '_';
                }
                $columnNameSuffix .= $part;
            }
            else {
                if ($updatedColumnName != '') {
                    $updatedColumnName .= '_';
                }
                $updatedColumnName .= $part;
            }
        }

        if ($updatedColumnName == '') {
            if (isset($this->columnPrefixName)) {
                $updatedColumnName = $columnNameSuffix;
            }
        }
        else {
            if ($columnNameSuffix != '') {
                $updatedColumnName .= '_' . $columnNameSuffix;
            }
        }

        return $updatedColumnName;
    }

    public function generate($columnName) {
        if (!isset($columnName)) {
            return NULL;
        }

        $adjustedColumnName = $columnName;

        // replacing well-known characters with corresponding text
        $adjustedColumnName = $this->replaceSpecialCharactersWithCorrespondingText($adjustedColumnName, '_');
        // replacing non-word characters with '_'
        $adjustedColumnName = $this->replaceIneligibleCharacters($adjustedColumnName, '_');
        // adding additional '_' between words
        $words = preg_split('/([A-Z][a-z]+)/', $adjustedColumnName, NULL, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $adjustedColumnName = implode('_', $words);
        // moving leading digits to end of the name (indirectly removing leading and trailing '_'(s), and several '_'(s) in a row)
        $adjustedColumnName = $this->moveLeadingDigitToEnd($adjustedColumnName);

        // adding column prefix
        if (isset($adjustedColumnName) && isset($this->columnPrefixName)) {
            $adjustedColumnName = $this->columnPrefixName . $adjustedColumnName;
        }

        return ($adjustedColumnName == '') ? NULL : strtolower($adjustedColumnName);
    }
}


class ColumnPublicNameGenerator extends AbstractColumnNameGenerator {

    protected function capitalizeWords($columnPublicName) {
        $columnPublicNameParts = explode(' ', $columnPublicName);
        if ($columnPublicNameParts === FALSE) {
            return $columnPublicName;
        }

        // do not include 'the'. It could be 'The US'
        $lowercasedWords = array('a', 'an', 'is', 'are', 'do', 'does', 'and', 'or');

        $updatedColumnPublicName = '';
        foreach ($columnPublicNameParts as $part) {
            if ($part == '') {
                continue;
            }

            // we should try to capitalize only those words where all characters are in lower case
            if (($part == strtolower($part)) && ($updatedColumnPublicName == '') || !in_array($part, $lowercasedWords)) {
                $part = ucfirst($part);
            }

            if ($updatedColumnPublicName != '') {
                $updatedColumnPublicName .= ' ';
            }
            $updatedColumnPublicName .= $part;
        }

        return $updatedColumnPublicName;
    }

    public function generate($columnPublicName) {
        if (!isset($columnPublicName)) {
            return NULL;
        }

        $adjustedColumnPublicName = $columnPublicName;

        // replacing non-word characters with space
        $adjustedColumnPublicName = $this->replaceIneligibleCharacters($adjustedColumnPublicName, ' ');
        // adding additional space between words
        $words = preg_split('/([A-Z][a-z]+)/', $adjustedColumnPublicName, NULL, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $adjustedColumnPublicName = implode(' ', $words);
        // capitalizing first letter in each word (indirectly removes several space characters in a row)
        $adjustedColumnPublicName = $this->capitalizeWords($adjustedColumnPublicName);

        return $adjustedColumnPublicName;
    }
}


class ColumnCommentGenerator extends AbstractColumnNameGenerator {

    public function generate($columnComment) {
        if (!isset($columnComment)) {
            return NULL;
        }

        $adjustedColumnComment = $columnComment;

        // replacing non-word characters with space
        $adjustedColumnComment = $this->replaceIneligibleCharacters($adjustedColumnComment, '?');

        return $adjustedColumnComment;
    }
}


class ColumnNameTruncator {

    // internal cache
    protected static $shortenedNames = NULL; // [$shreddableCharacterCount][$originalColumnName]

    protected static function removeVowels($columnName, &$shreddableCharacterCount) {
        // removing vowels (except first character and words shorter than 4 characters) starting from the longest word
        $columnNameParts = explode('_', $columnName);

        $processableIndexes = array();
        foreach ($columnNameParts as $index => $part) {
            $length = strlen($part);
            if ($length <= 3) {
                continue;
            }

            $processableIndexes[$index] = $length;
        }

        arsort($processableIndexes, SORT_NUMERIC);

        foreach ($processableIndexes as $processableIndex => $length) {
            $part = $columnNameParts[$processableIndex];

            $updatedPart = NULL;
            for ($i = strlen($part) - 1; (($shreddableCharacterCount > 0) && ($i > 0 /* excluding first character*/)); $i--) {
                $c = $part[$i];
                switch ($c) {
                    case 'a':
                    case 'e':
                    case 'i':
                    case 'o':
                    case 'u':
                    case 'y': // because it is in the middle of the word it is in most cases is vowel
                        if (!isset($updatedPart)) {
                            $updatedPart = $part;
                        }
                        $updatedPart = substr_replace($updatedPart, '', $i, 1);
                        $shreddableCharacterCount--;
                        break;
                }
            }
            if (isset($updatedPart)) {
                $columnNameParts[$processableIndex] = $updatedPart;
            }

            if ($shreddableCharacterCount == 0) {
                break;
            }
        }

        return implode('_', $columnNameParts);
    }

    public static function shortenName($columnName, $shreddableCharacterCount) {
        if (!isset(self::$shortenedNames[$shreddableCharacterCount][$columnName])) {
            $shortenedColumnName = self::removeVowels($columnName, $shreddableCharacterCount);

            self::$shortenedNames[$shreddableCharacterCount][$columnName] = $shortenedColumnName;
        }

        return self::$shortenedNames[$shreddableCharacterCount][$columnName];
    }
}
