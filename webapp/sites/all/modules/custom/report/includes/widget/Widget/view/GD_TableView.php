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


class GD_TableView extends AbstractObject {
    private $tableData;
    private $table = NULL;
    private $header = NULL;
    private $body = NULL;
    private $attributes = NULL;

    public function __construct( $data, $attributes ) {
        $this->tableData = $data;
        $this->attributes = $attributes;
    }

    protected function getTableAttributes() {
        return isset($this->attributes) ? drupal_attributes($this->attributes) : '';
    }

    protected function buildTable() {
        if ( !isset($this->header) ) {
            $this->buildHeader();
        }

        if ( !isset($this->body) ) {
            $this->buildBody();
        }

        $pieces = array(
            '<table ' . $this->getTableAttributes() . '>',
            $this->header,
            $this->body,
            '</table>'
        );

        $this->table = implode($pieces);
    }

    protected function buildHeader() {
        $this->header = '<thead><tr>';

        if ( isset($this->tableData) ) {
            $fields = $this->tableData['fields'];
            $index = 0;
            foreach ( $fields as $field ) {
                $this->header .= '<th column-title="' . $field['title'] .'" column-index="' . $index . '" column-name="' . $field['name'] . '" sort-column="' . (isset($field['column']) ? $field['column'] : $field['name']) . '">' . $field['title'] . '</th>';
                ++$index;
            }
        }

        $this->header .= '</tr></thead>';
    }

    protected function buildBody() {
        $this->body = '<tbody>';
        if ( isset($this->tableData) ) {
            $data = $this->tableData['data'];
            if ( !empty($data) ) {
                foreach ($data as $row) {
                    $this->body .= '<tr>';
                    foreach ( $row->record as $cell ) {
                        $this->body .= '<td>' . $cell . '</td>';
                    }
                    $this->body .= '</tr>';
                }
            }
        }
        $this->body .= '</tbody>';
    }

    public function getHtml() {
        if ( !isset($this->table) ) {
            $this->buildTable();
        }

        return $this->table;
    }
}
