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


class BreadcrumbFactory {
    public static function parseBreadcrumbs(GD_DashboardConfig $currentConfig) {
        $breadcrumbs = array();

        $bcInfo = (!empty($_REQUEST['bc'])) ? $_REQUEST['bc'] : $currentConfig->id;

        $queries = array();
        if ( !empty($_REQUEST['t']) ) {
            $queries = $_REQUEST['t'];
        }

        $dashboardIds = array_reverse(explode(',', $bcInfo));

        $nodes = node_load_multiple($dashboardIds);
        foreach ( $nodes as $node ) {
            $link = '';
            if ($node->nid != $currentConfig->id) {
                $link = '?id=' . $node->nid;
                foreach ($queries as $dashboard => $query) {
                    $q = '&t[' . $dashboard . ']';
                    foreach ($query as $filter => $values) {
                        $f = $q . '[' . $filter . ']';
                        foreach ($values as $k => $v) {
                            if (is_array($v)) {
                                foreach ($v as $index => $val) {
                                    $val = rawurlencode($val);
                                    $link .= $f . '[' . $k . ']['.$index.']=' . $val;
                                }
                            }
                            else
                                $link .= $f . '[' . $k . ']=' . $v;
                        }
                    }
                }


                if ($bcInfo != '') {
                    $link .= '&bc=' . $bcInfo;
                }
            }

            $drilldownFilterInfo = array();
            if ( !empty($_REQUEST['t']) ) {
                foreach ($_REQUEST['t'] as $dashboard => $dashboardFilters) {
                    if ($dashboard == $node->nid) {
                        foreach ($dashboardFilters as $filterName => $filter) {
                            if (isset($filter['ddf']) && $filter['ddf'] == 1) {
                                $filterInfo = new stdClass();
                                $filterInfo->name = $filterName;
                                $filterInfo->value = $filter['v'];
                                $drilldownFilterInfo[] = $filterInfo;
                            }
                        }
                        break;
                    }
                }
            }

            unset($queries[$node->nid]);
            $bcInfo = str_replace(','.$node->nid, '',$bcInfo);
            foreach ( $_GET as $k => $v ) {
                if (!in_array($k, array('datasource', 'q', 'bc', 'id', 't', 'callback', '_', 'origin'))) {
                    $q = is_array($_GET[$k]) ? $_GET[$k] : array($k => $v);
                    $q = http_build_query($q);
                    $q = str_replace('amp;', '&', $q);
                    $link .= ($q[0] != '&' ? '&' : '') . $q;
                }
            }
            $breadcrumbs[] = new Breadcrumb($node->title, $link, $drilldownFilterInfo);
        }

        return array_reverse($breadcrumbs);
    }
}


class Breadcrumb {
    public $text;
    public $link;
    public $ddf;

    public function __construct($text = NULL, $link = NULL, $ddf = NULL) {
        $this->text = $text;
        $this->link = $link;
        $this->ddf = $ddf;
    }

    public function getText() {
        return $this->text;
    }

    public function getLink() {
        return $this->link;
    }

    public function getDrilldownFilters() {
        return $this->ddf;
    }

    public function setText($value) {
        $this->text = $value;
    }

    public function setLink($value) {
        $this->link = $value;
    }

    public function setDrilldownFilters($value) {
        $this->ddf = $value;
    }
}
