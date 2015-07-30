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

/**
 * @file
 * gd_content_types.features.inc
 */

/**
 * Implements hook_node_info().
 */
function gd_content_types_node_info() {
  $items = array(
    'account' => array(
      'name' => t('Account'),
      'base' => 'node_content',
      'description' => '',
      'has_title' => '1',
      'title_label' => t('Name'),
      'help' => '',
    ),
    'analytics_model' => array(
      'name' => t('Analytics Model'),
      'base' => 'node_content',
      'description' => t('Analytics Model configuration page'),
      'has_title' => '1',
      'title_label' => t('Model Name'),
      'help' => '',
    ),
    'column' => array(
      'name' => t('Column'),
      'base' => 'node_content',
      'description' => '',
      'has_title' => '1',
      'title_label' => t('Name'),
      'help' => '',
    ),
    'dashboard' => array(
      'name' => t('Dashboard'),
      'base' => 'node_content',
      'description' => '',
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
    'datafile' => array(
      'name' => t('Datafile'),
      'base' => 'node_content',
      'description' => t('Uploaded files which are used as source for datasets'),
      'has_title' => '1',
      'title_label' => t('Name'),
      'help' => '',
    ),
    'datamart' => array(
      'name' => t('Datamart'),
      'base' => 'node_content',
      'description' => t('Persistence storage for application data '),
      'has_title' => '1',
      'title_label' => t('Name'),
      'help' => '',
    ),
    'dataset' => array(
      'name' => t('Dataset'),
      'base' => 'node_content',
      'description' => '',
      'has_title' => '1',
      'title_label' => t('Name'),
      'help' => '',
    ),
    'measure' => array(
      'name' => t('Measure'),
      'base' => 'node_content',
      'description' => '',
      'has_title' => '1',
      'title_label' => t('Name'),
      'help' => '',
    ),
    'reference' => array(
      'name' => t('Reference'),
      'base' => 'node_content',
      'description' => '',
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
    'reference_point' => array(
      'name' => t('Reference Point'),
      'base' => 'node_content',
      'description' => '',
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
    'report' => array(
      'name' => t('Report'),
      'base' => 'node_content',
      'description' => '',
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
  );
  return $items;
}
