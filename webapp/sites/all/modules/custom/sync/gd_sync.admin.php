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
 * @return string
 */
function gd_sync_admin_page () {

    return drupal_get_form('gd_sync_admin_datamart_form');

}

/**
 * @return string
 */
function gd_sync_admin_page_export () {
    return 'Stub page for export forms';
}

/**
 * @return string
 */
function gd_sync_admin_page_import () {
    return array(drupal_get_form('gd_sync_import_create_form') , drupal_get_form('gd_sync_import_update_form'));
}

function gd_sync_admin_datamart_form ( $form, &$form_state ) {
    // Enable language column if translation module is enabled or if we have any
    // node with language.
    $multilanguage = (module_exists('translation') || db_query_range("SELECT 1 FROM {node} WHERE language <> :language", 0, 1, array(':language' => LANGUAGE_NONE))->fetchField());

    // Build the sortable table header.
    $header = array(
        'title' => array('data' => t('Title'), 'field' => 'n.title'),
        'author' => t('Author'),
        'changed' => array('data' => t('Updated'), 'field' => 'n.changed', 'sort' => 'desc'),
        'datasets' => t('Datasets'),
        'reports' => t('Reports'),
        'dashboards' => t('Dashboards')
    );
    if ($multilanguage) {
        $header['language'] = array('data' => t('Language'), 'field' => 'n.language');
    }
    $header['operations'] = array('data' => t('Operations'));

    $nodes = gd_datamart_get_datamarts(LOAD_ENTITY);

    // Prepare the list of nodes.
    $languages = language_list();
    $destination = drupal_get_destination();
    $options = array();
    foreach ($nodes as $node) {
        $langcode = entity_language('node', $node);
        $l_options = $langcode != LANGUAGE_NONE && isset($languages[$langcode]) ? array('language' => $languages[$langcode]) : array();
        $options[$node->nid] = array(
            'title' => array(
                'data' => array(
                    '#type' => 'link',
                    '#title' => $node->title,
                    '#href' => 'admin/structure/govdashboard/sync/datamart/' . $node->nid,
                    '#options' => $l_options,
                    '#suffix' => ' ' . theme('mark', array('type' => node_mark($node->nid, $node->changed))),
                ),
            ),
            'author' => theme('username', array('account' => $node)),
            'changed' => format_date($node->changed, 'short'),
            'datasets' => count(gd_dataset_findall_by_datasource(!LOAD_ENTITY,get_node_field_value($node,'field_datamart_sysname'))),
            'reports' => count(gd_report_findall_by_datasource(!LOAD_ENTITY,get_node_field_value($node,'field_datamart_sysname'))),
            'dashboards' => count(gd_dashboard_findall_by_datasource(!LOAD_ENTITY,get_node_field_value($node,'field_datamart_sysname')))
        );
        if ($multilanguage) {
            if ($langcode == LANGUAGE_NONE || isset($languages[$langcode])) {
                $options[$node->nid]['language'] = $langcode == LANGUAGE_NONE ? t('Language neutral') : t($languages[$langcode]->name);
            }
            else {
                $options[$node->nid]['language'] = t('Undefined language (@langcode)', array('@langcode' => $langcode));
            }
        }
        // Build a list of all the accessible operations for the current node.
        $operations = array();
        if (node_access('update', $node)) {
            $operations['edit'] = array(
                'title' => t('edit'),
                'href' => 'node/' . $node->nid . '/edit',
                'query' => $destination,
            );
        }
        $options[$node->nid]['operations'] = array();
        if (count($operations) > 1) {
            // Render an unordered list of operations links.
            $options[$node->nid]['operations'] = array(
                'data' => array(
                    '#theme' => 'links__node_operations',
                    '#links' => $operations,
                    '#attributes' => array('class' => array('links', 'inline')),
                ),
            );
        }
        elseif (!empty($operations)) {
            // Render the first and only operation as a link.
            $link = reset($operations);
            $options[$node->nid]['operations'] = array(
                'data' => array(
                    '#type' => 'link',
                    '#title' => $link['title'],
                    '#href' => $link['href'],
                    '#options' => array('query' => $link['query']),
                ),
            );
        }
    }

    $form['nodes'] = array(
        '#type' => 'tableselect',
        '#header' => $header,
        '#options' => $options,
        '#empty' => t('No content available.'),
    );

    return $form;
}


function gd_sync_admin_entity_export_select_form ( $form, &$form_state ) {

    $handlers = module_invoke_all('gd_sync_entities');
    $exportHandlers = array();
    foreach ( $handlers as $h ) {
        $exportHandlers[] = $h['export'];
    }

    // sort by operation weight
    usort($exportHandlers,function($a,$b){
        if ($a['weight'] == $b['weight']) {
            return 0;
        }
        return ($a['weight'] < $b['weight']) ? -1 : 1;
    });

    $nodes = array();
    foreach ( $exportHandlers as $handler ) {
        $result = $handler['class']::getExportables($form_state['build_info']['args'][0]->nid);
        $nodes = array_merge($nodes,$result);
    }

    // Enable language column if translation module is enabled or if we have any
    // node with language.
    $multilanguage = (module_exists('translation') || db_query_range("SELECT 1 FROM {node} WHERE language <> :language", 0, 1, array(':language' => LANGUAGE_NONE))->fetchField());

    // Build the sortable table header.
    $header = array(
        'title' => array('data' => t('Title'), 'field' => 'n.title'),
        'type' => array('data' => t('Type'), 'field' => 'n.type'),
        'author' => t('Author'),
        'status' => array('data' => t('Status'), 'field' => 'n.status'),
        'changed' => array('data' => t('Updated'), 'field' => 'n.changed', 'sort' => 'desc')
    );
    if ($multilanguage) {
        $header['language'] = array('data' => t('Language'), 'field' => 'n.language');
    }
    $header['operations'] = array('data' => t('Operations'));

    // Prepare the list of nodes.
    $languages = language_list();
    $destination = drupal_get_destination();
    $options = array();
    foreach ($nodes as $node) {
        $langcode = entity_language('node', $node);
        $l_options = $langcode != LANGUAGE_NONE && isset($languages[$langcode]) ? array('language' => $languages[$langcode]) : array();
        $options[$node->nid] = array(
            'title' => array(
                'data' => array(
                    '#type' => 'link',
                    '#title' => $node->title,
                    '#href' => 'node/' . $node->nid,
                    '#options' => $l_options,
                    '#suffix' => ' ' . theme('mark', array('type' => node_mark($node->nid, $node->changed))),
                ),
            ),
            'type' => $node->type,
            'author' => theme('username', array('account' => $node)),
            'status' => $node->status ? t('published') : t('not published'),
            'changed' => format_date($node->changed, 'short'),
        );
        if ($multilanguage) {
            if ($langcode == LANGUAGE_NONE || isset($languages[$langcode])) {
                $options[$node->nid]['language'] = $langcode == LANGUAGE_NONE ? t('Language neutral') : t($languages[$langcode]->name);
            }
            else {
                $options[$node->nid]['language'] = t('Undefined language (@langcode)', array('@langcode' => $langcode));
            }
        }
        // Build a list of all the accessible operations for the current node.
        $operations = array();
        if (node_access('update', $node)) {
            $operations['edit'] = array(
                'title' => t('edit'),
                'href' => 'node/' . $node->nid . '/edit',
                'query' => $destination,
            );
        }
        if (node_access('delete', $node)) {
            $operations['delete'] = array(
                'title' => t('delete'),
                'href' => 'node/' . $node->nid . '/delete',
                'query' => $destination,
            );
        }
        $options[$node->nid]['operations'] = array();
        if (count($operations) > 1) {
            // Render an unordered list of operations links.
            $options[$node->nid]['operations'] = array(
                'data' => array(
                    '#theme' => 'links__node_operations',
                    '#links' => $operations,
                    '#attributes' => array('class' => array('links', 'inline')),
                ),
            );
        }
        elseif (!empty($operations)) {
            // Render the first and only operation as a link.
            $link = reset($operations);
            $options[$node->nid]['operations'] = array(
                'data' => array(
                    '#type' => 'link',
                    '#title' => $link['title'],
                    '#href' => $link['href'],
                    '#options' => array('query' => $link['query']),
                ),
            );
        }
    }

    $form['nodes'] = array(
        '#type' => 'tableselect',
        '#header' => $header,
        '#options' => $options,
        '#empty' => t('No content available.'),
    );

    return $form;
}


function gd_sync_admin_page_datasource ( $datasource ) {

    $output = array();

    $output[] = array('#markup'=>'<h1>'.$datasource->publicName.'</h1>');

    $output[] = drupal_get_form('gd_sync_admin_entity_export_select_form',$datasource);

    return $output;
}

/**
 * @param $form
 * @param $form_state
 * @return mixed
 */
function gd_sync_export_form ( $form, &$form_state ) {

    if ( !empty($form_state['values']['datasource']) ) {

        $exportContext = new GD\Sync\Export\ExportContext(array('datasourceName'=>$form_state['values']['datasource']));
        $exportStream = new GD\Sync\Export\ExportStream();

        $exportController = new \GD\Sync\Export\ExportController();
        $exportController->export($exportStream,$exportContext);

        $export = json_encode($exportStream->flush());

    } else {
        $export = null;
    }

    $form['export'] = array(
        '#type' => 'fieldset',
        '#title' => t('Datasource Export'),
        '#description' => 'I will create a a dump of a datasource'
    );

    $form['export']['datasource'] = array(
        '#type' => 'select',
        '#title' => t('Select Datasource'),
        '#description' => 'The datasource to export.',
        '#options' => gd_sync_get_datasource_options()
    );

    $form['export']['actions'] = array(
        '#type' => 'fieldset',
        '#weight' => 0,
        '#collapsible' => false,
        '#collapsed' => false
    );

    $form['export']['actions']['action'] = array(
        '#type' => 'button',
        '#value' => t('Export'),
        '#ajax' => array(
            'callback' => 'gd_sync_export_ajax_callback',
            'wrapper' => 'sync-export-wrapper',
            'method' => 'replace',
            'effect' => 'fade'
        )
    );

    $form['export']['actions']['reset'] = array(
        '#type' => 'button',
        '#value' => t('Reset'),
        '#attributes' => array('onclick' => 'location.href=\'/admin/structure/govdashboard/sync\';')
    );

    $form['export']['data'] = array(
        '#markup' => '<div id="sync-export-wrapper" style="margin-top: 20px;"><strong>Result:</strong><br/><textarea style="background-color: #eee; border: 2px dashed #aaa; padding: 10px;" cols="160" rows="10">'.check_plain($export).'</textarea></div>'
    );

    return $form;
}

/**
 * @param $form
 * @param $form_state
 * @return mixed
 */
function gd_sync_export_ajax_callback ( $form, $form_state ) {
    return $form['export']['data'];
}

/**
 * @param $form
 * @param $form_state
 */
function gd_sync_export_form_submit ( $form, &$form_state ) {
    drupal_set_message('Entities Exported Successfully');
}

/**
 * @param $form
 * @param $form_state
 * @return mixed
 */
function gd_sync_import_create_form ( $form, &$form_state ) {

    $form['import'] = array(
        '#type' => 'fieldset',
        '#title' => t('Create Datasource'),
        '#description' => 'I will import a new datasource'
    );

    $form['import']['name'] = array(
        '#type' => 'textfield',
        '#title' => 'Datasource Name',
        '#required' => true
    );

    $form['import']['content'] = array(
        '#type' => 'textarea',
        '#title' => 'Export',
        '#description' => 'Paste export above.',
        '#required' => true
    );

    $form['import']['actions'] = array(
        '#type' => 'fieldset',
        '#weight' => 5,
        '#collapsible' => false,
        '#collapsed' => false
    );

    $form['import']['actions']['action'] = array(
        '#type' => 'submit',
        '#value' => t('Create')
    );

    return $form;
}

/**
 * @param $form
 * @param $form_state
 * @throws Exception
 */
function gd_sync_import_create_form_submit ( $form, &$form_state ) {
    try {
        $content = json_decode($form_state['values']['content']);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON');
        }

        $datasourceName = gd_datasource_create(array(
            'publicName' => $form_state['values']['name'],
            'description' => 'Imported Datamart.'
        ));

        gd_datasource_set_active($datasourceName);
        $importContext = new GD\Sync\Import\ImportContext(array('datasourceName'=>$datasourceName,'operation'=>'create'));
        $importStream = new GD\Sync\Import\ImportStream();
        $importStream->set(null,$content);

        $importController = new \GD\Sync\Import\ImportController();
        $importController->import($importStream,$importContext);

        drupal_set_message('Datamart Created Successfully');
    } catch ( Exception $e ) {
        LogHelper::log_error($e);
        drupal_set_message($e->getMessage(),'error');
    }
}

/**
 * @param $form
 * @param $form_state
 * @return mixed
 */
function gd_sync_import_update_form ( $form, &$form_state ) {

    $form['import'] = array(
        '#type' => 'fieldset',
        '#title' => t('Update Datasource'),
        '#description' => 'I will update an existing datasource'
    );

    $form['import']['datasourceName'] = array(
        '#type' => 'select',
        '#title' => t('Select Datasource'),
        '#description' => 'The datasource to update.',
        '#options' => gd_sync_get_datasource_options(),
        '#required' => true
    );

    $form['import']['content'] = array(
        '#type' => 'textarea',
        '#title' => 'Export',
        '#description' => 'Paste export above.',
        '#required' => true
    );

    $form['import']['actions'] = array(
        '#type' => 'fieldset',
        '#weight' => 5,
        '#collapsible' => false,
        '#collapsed' => false
    );

    $form['import']['actions']['action'] = array(
        '#type' => 'submit',
        '#value' => t('Update')
    );

    return $form;
}

/**
 * @param $form
 * @param $form_state
 * @throws Exception
 */
function gd_sync_import_update_form_submit ( $form, &$form_state ) {
    try {
        $content = json_decode($form_state['values']['content']);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON');
        }

        $importContext = new GD\Sync\Import\ImportContext(array('datasourceName'=>$form_state['values']['datasourceName'],'operation'=>'update'));
        $importStream = new GD\Sync\Import\ImportStream();
        $importStream->set(null,$content);

        $importController = new \GD\Sync\Import\ImportController();
        $importController->import($importStream,$importContext);

        drupal_set_message('Datasource Updated Successfully');
    } catch ( Exception $e ) {
        LogHelper::log_error($e);
        drupal_set_message($e->getMessage(),'error');
    }
}

/**
 * @return array
 */
function gd_sync_get_datasource_options () {
    $options = array();
    $datasources = gd_datasource_get_all();
    foreach ( $datasources as $datasource ) {
        $options[$datasource->name] = $datasource->publicName;
    }
    return $options;
}