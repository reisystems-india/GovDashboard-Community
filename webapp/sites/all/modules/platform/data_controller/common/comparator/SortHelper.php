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


function compare_values($a, $b, $isOrderAscending = TRUE) {
    $result = 0;

    if (isset($a)) {
        if (isset($b)) {
            $isCompositeA = is_array($a) || is_object($a);
            $isCompositeB = is_array($b) || is_object($b);
            if ($isCompositeA) {
                if ($isCompositeB) {
                    $array_a = is_object($a) ? get_object_vars($a) : $a;
                    $array_b = is_object($b) ? get_object_vars($b) : $b;

                    for ($i = 0, $count = max(count($array_a), count($array_b)); ($i < $count) && ($result == 0); $i++) {
                        $recordA = each($array_a);
                        $recordB = each($array_b);
                        if ($recordA === FALSE) {
                            if ($recordB === FALSE) {
                                break;
                            }
                            else {
                                $result = -1;
                            }
                        }
                        elseif ($recordB === FALSE) {
                            $result = 1;
                        }
                        else {
                            $result = compare_values($recordA['key'], $recordB['key'], $isOrderAscending);
                            if ($result == 0) {
                                $result = compare_values($recordA['value'], $recordB['value'], $isOrderAscending);
                            }
                        }
                    }
                }
                else {
                    $result = 1;
                }
            }
            elseif ($isCompositeB) {
                $result = -1;
            }
            elseif (is_numeric($a) && is_numeric($b)) {
                $delta = $a - $b;
                $result = ($delta > 0)
                    ? 1
                    : (($delta < 0) ? -1 : 0);
            }
            else {
                $result = strcasecmp($a, $b);
            }
        }
        else {
            $result = 1;
        }
    }
    elseif (isset($b)) {
        $result = -1;
    }

    if (($result != 0) && !$isOrderAscending) {
        $result *= -1;
    }

    return $result;
}

function sort_records(array &$records = NULL, $sorting_configurations) {
    if (!isset($records)) {
        return;
    }

    if (!isset($sorting_configurations)) {
        return;
    }

    $comparator = new DefaultColumnBasedComparator();
    if (is_array($sorting_configurations)) {
        $comparator->registerSortingConfigurations($sorting_configurations);
    }
    else {
        $sortingConfiguration = $sorting_configurations;
        $comparator->registerSortingConfiguration($sortingConfiguration);
    }

    if (!usort($records, array($comparator, 'compare'))) {
        throw new Exception(t('Sort operation could not be completed'));
    }
}
