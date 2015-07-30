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


// Runtime config for a report

class GD_ReportConfig {

    private $id = null;

    public $title;
    public $description;
    public $datasets = array();
    public $drilldowns = array();
    public $options = array();

    public $columns = array();
    public $filters = array();
    public $sort = array();
    public $formulas = array();
    public $limit = null;
    public $offset = 0;

    public $orderBy = array(); // need to remove this and use sort, might be because of UI limitations that it is still used

    public $dashboard = null;

    public $count;
    public $url_params;

    private $reportNode = null;
    private $metamodel = null;
    private $data = null;
    private $config = null;
    private $customView;

    private $displaySize = null;

    private $isDataTruncated = false;

    public function __construct ( $config ) {
        if ( empty($config) ) {
            $config = array('config' => array(), 'visual' => array(), 'model' => array());
        } else if ( empty($config['config']) ) {
            $config['config'] = array();
        } else if ( empty($config['model']) ) {
            $config['model'] = array();
        } else if ( empty($config['visual']) ) {
            $config['visual'] = array();
        }

        if ( !empty($config['id']) ) {
            $this->id = $config['id'];
        }

        $this->config = $config;

        if (!empty($config['dashboard'])) {
            $this->dashboard = $config['dashboard'];
        }

        if (!empty($config['customView'])) {
            $this->customView = $config['customView'];
        }

        // datasets to use
        if ( !empty($config['model']['datasets']) ) {
            $this->datasets = $config['model']['datasets'];
        } else if ( !empty($config['model']['dataset']) ) {
            // TODO remove, backwards compatability check
            $this->datasets = array($config['model']['dataset']);
        }

        // query filters
        if ( !empty($config['model']['filters']) ) {
            $this->addFilter($config['model']['filters']);
        }

        if ( isset($_REQUEST['filters']) ) {
            $this->addFilter(json_decode($_REQUEST['filters']));
        }

        // drilldowns
        if ( isset($_REQUEST['drilldowns']) ) {
            $this->drilldowns = json_decode($_REQUEST['drilldowns']);
        }

        // query order by
        if ( isset($config['model']['orderBy']) && !empty($config['model']['orderBy']) ) {
            foreach ( $config['model']['orderBy'] as $item ) {
                $this->addOrderBy($item);
            }
            $this->orderBy = $config['model']['orderBy'];
        }

        if (!empty($config['model']['formulas'])) {
            $this->formulas = $config['model']['formulas'];
        }

        // query columns
        if ( isset($config['model']['columns']) ) {
            $this->setColumns($config['model']['columns']);
        }

        if ( isset($config['model']['limit']) && $config['model']['limit'] != 0 ) {
            $this->limit = $config['model']['limit'];
        }

        if ( isset($config['model']['offset']) ) {
            $this->offset = $config['model']['offset'];
        }

        // temp adv table sorting
        if ( isset($_REQUEST['order']) ) {
            $this->sort = explode(',', $_REQUEST['sort']);
        }

        // column order
        if ( !empty($config['model']['columnOrder']) ) {
            $this->columnOrder = $config['model']['columnOrder'];
        } else {
            $this->columnOrder = isset($config['model']['columns']) ? $config['model']['columns'] : null;
        }

        // display options
        if ( !empty($config['config']) ) {
            $this->options['config'] = $config['config'];
        } else {
            $this->options['config'] = array();
        }

        if ( !empty($config['visual']) ) {
            $this->options['visual'] = $config['visual'];
        } else {
            $this->options['visual'] = array();
        }

        if (!empty($config['columnConfigs'])) {
            $this->setColumnConfigs($config['columnConfigs']);
        } else {
            $this->setColumnConfigs(array());
        }

        // display type override
        if (!empty($_REQUEST['type'])) {
            $this->setDisplayType($_REQUEST['type']);
        }

        // todo: may not need this after report refresh refactoring
        if (!empty($_REQUEST['style'])) {
            $this->options['style'] = $_REQUEST['style'];
        }

        if ( !empty($config['url_params']) ) {
            $this->url_params = $config['url_params'];
        }

        // origin param to capture base path for drilldowns
        if ( !empty($_REQUEST['origin']) ) {
            $this->origin = $_REQUEST['origin'];

            // if previewing from dashboard builder
        } else if ( $_GET['q'] == 'dashboard/report/preview' ) { /* TODO: how else can we check for this??? */
            $this->origin = 'dashboards';

        } else {
            $url_info = parse_url($_SERVER['REQUEST_URI']);
            $this->origin = $url_info['path'];
        }

        // advanced table traffic light properties
        $this->trafficColumnId = null;

        $this->traffic = null;
        if ( isset($config['visual']['trafficColumn']) && !empty($config['visual']['trafficColumn']) ) {
            $traffic = new stdClass();


            $traffic->trafficColumnId = $config['visual']['trafficColumn'];

            $traffic->displayTrafficLightImage = false;
            if ( isset($config['visual']['trafficDisplayImage']) && !empty($config['visual']['trafficDisplayImage']) ) {
                $traffic->displayTrafficLightImage = true;
            }

            $traffic->displayTrafficLightRowColor = false;
            if ( isset($config['visual']['trafficDisplayRowColor']) && $config['visual']['trafficDisplayRowColor'] ) {
                $traffic->displayTrafficLightRowColor = true;
            }

            $traffic->trafficLightImagePosition = 'replace';
            if ( isset($config['visual']['trafficDisplayImagePosition']) ) {
                $traffic->trafficLightImagePosition = $config['visual']['trafficDisplayImagePosition'];
            }

            $traffic->trafficLightImageTitle = '&nbsp;';
            if ( isset($config['visual']['trafficDisplayImageTitle']) && !empty($config['visual']['trafficDisplayImageTitle']) ) {
                $traffic->trafficLightImageTitle = $config['visual']['trafficDisplayImageTitle'];
            }

            $options = array('trafficRedFrom','trafficRedTo','trafficYellowFrom','trafficYellowTo','trafficGreenFrom','trafficGreenTo','trafficRedValue','trafficYellowValue','trafficGreenValue');
            foreach ( $options as $o ) {
                if ( isset($config['visual'][$o]) ) {
                    $traffic->$o = $config['visual'][$o];
                }
            }

            $this->traffic[$traffic->trafficColumnId] = $traffic;
        }

        if ( isset($config['visual']['traffic']) ) {
            foreach ( $config['visual']['traffic'] as $columnId => $t ) {
                $traffic = new stdClass();

                // backwards compatible check, update hook should be done to fix this
                if ( !empty($t['trafficColumn']) ) {
                    $traffic->trafficColumnId = $t['trafficColumn'];
                } else {
                    $traffic->trafficColumnId = $columnId;
                }

                $traffic->displayTrafficLightImage = false;
                if ( isset($t['trafficDisplayImage']) && !empty($t['trafficDisplayImage']) && $t['trafficDisplayImage'] !== 0) {
                    $traffic->displayTrafficLightImage = true;
                }else{
                    $traffic->displayTrafficLightImage = false;
                }
                $traffic->displayTrafficLightRowColor = false;
                if ( isset($t['trafficDisplayRowColor']) && $t['trafficDisplayRowColor'] ) {
                    $traffic->displayTrafficLightRowColor = true;
                }

                $traffic->trafficLightImagePosition = 'replace';
                //  Force Traffic light to be replace for Pivot Tables
                if ( isset($t['trafficDisplayImagePosition']) && $this->getDisplayType() != 'pivot_table' ) {
                    $traffic->trafficLightImagePosition = $t['trafficDisplayImagePosition'];
                }

                $traffic->trafficLightImageTitle = '&nbsp;';
                if ( isset($t['trafficDisplayImageTitle']) && !empty($t['trafficDisplayImageTitle']) ) {
                    $traffic->trafficLightImageTitle = $t['trafficDisplayImageTitle'];
                }

                $options = array('trafficRedFrom','trafficRedTo','trafficYellowFrom','trafficYellowTo','trafficGreenFrom','trafficGreenTo','trafficRedValue','trafficYellowValue','trafficGreenValue');
                foreach ( $options as $o ) {
                    if ( isset($t[$o]) ) {
                        $traffic->$o = $t[$o];
                    }
                }

                $this->traffic[$traffic->trafficColumnId] = $traffic;
            }
        }
    }

    public function setColumnConfigs($columnConfigs) {
        // check for backwards compatibility
        foreach ( $columnConfigs as $key => $columnConfig ) {
            if (isset($columnConfig['formatter']['scale']) && is_string($columnConfig['formatter']['scale']) && trim($columnConfig['formatter']['scale']) === '') {
                $columnConfigs[$key]['formatter']['scale'] = NULL;
            }
        }
        $this->options['column_configs'] = $columnConfigs;
    }

    public function setColumns($columns) {
        if (count(array_diff($this->columns, $columns)) !== 0) {
            unset($this->data);
        }

        $this->columns = $columns;
        $this->setColumnDetails($columns);
    }

    public function setColumnDetails($columns) {
        $this->options['column_details'] = array();
        $metamodel = $this->getMetamodel();
        if ( !empty($columns) && isset($metamodel)) {    //  Talk to Viktor
            foreach ( $columns as $c ) {
                $element = $metamodel->findElement($c);
                if ( $element ) {
                    $e = new stdClass();
                    $e->name = $element->name;
                    $e->publicName = $element->publicName;
                    $e->type = new stdClass();
                    $e->type->applicationType = $element->type->applicationType;
                    $e->type->scale = $element->type->scale;
                    while ( isset($element->parentName) ) {
                        $parent = $metamodel->findElement($element->parentName);
                        if ( $parent && $parent->publicName != $element->publicName ) {
                            $e->publicName = $parent->publicName . '/' . $e->publicName;
                        }
                        $element = $parent;
                    }
                    $this->options['column_details'][] = $e;
                } else if ($f = $this->getFormula($c)) {
                    $this->options['column_details'][] = $f;
                }
            }
        }
    }

    public function setStyle($style) {
        $this->options['style'] = $style;
    }

    public function setTitle($title) {
        $this->title = $title;
        $this->options['config']['title'] = $title;
    }

    public function setId($id) {
        $this->id = $id;
        $this->options['config']['id'] = $id;
    }

    public function setNode($node) {
        $this->reportNode = $node;
    }

    public function setDescription($desc) {
        $this->description = $desc;
    }

    /**
     * @return int|null
     */
    public function getId () {
        return $this->id;
    }

    public function getDatasource () {
        if ( $this->reportNode ) {
            return get_node_field_value($this->reportNode,'field_report_datasource');
        }
        return null;
    }

    public function isLimitSet () {
        return (bool) isset($this->limit);
    }

    public function setLimit ( $limit ) {
        if ( !$this->limit ) {
            $this->limit = $limit;
        }
    }

    public function getLimit () {
        return $this->limit;
    }

    public function getQueryLimit() {
        if ( !$this->limit ) {
            return $this->getChartMaxLimit();
        } else {
            // override limit for performance reasons
            $maxLimit = $this->getChartMaxLimit();
            if ( $maxLimit && $this->limit > $maxLimit ) {
                $this->isDataTruncated = true;
                return $maxLimit;
            } else {
                return $this->limit;
            }
        }
    }

    public function setOffset ( $offset ) {
        $this->offset = $offset;
    }

    public function getOffset () {
        return $this->offset;
    }

    public function getSort() {
        return $this->sort;
    }

    public function setSort ( $sort ) {
        $this->sort = $sort;
    }

    public function addSort ( $item ) {
        if ( !in_array($item,$this->sort) ) {
            $this->sort[] = $item;
        }
    }

    // TODO fix all places that rely on this function to send/store sort info
    public function addOrderBy ( $item ) {
        if ($item['order'] == 'asc') {
            $this->addSort($item['column']);
        } else {
            $this->addSort('-'.$item['column']);
        }
    }

    /**
     * @return DatasetUIMetaData|null
     */
    public function loadMetamodel () {
        if ( !$this->metamodel ) {
            $connectedDatasetNames = (count($this->datasets) > 1) ? array_slice($this->datasets, 1) : null;
            $metamodel = data_controller_get_metamodel();
            $metamodel->getDataset($this->datasets[0]); // ensure the dataset exists
            $this->metamodel = gd_data_controller_ui_metadata_get_dataset_ui_metadata($this->datasets[0], $connectedDatasetNames);
        }
        return $this->metamodel;
    }

    /**
     * @return DatasetUIMetaData|null
     */
    public function getMetamodel () {
        $this->loadMetamodel();
        return $this->metamodel;
    }

    public function getNode () {
        return $this->reportNode;
    }

    public function isNodeConfig () {
        if ( $this->reportNode === null ) {
            return false;
        } else {
            return true;
        }
    }

    public function getDisplaySize () {
        return $this->displaySize;
    }

    public function validate() {
        $valid = true;
        foreach ($this->columns as $column) {
            if (!$this->getColumn($column)) {
                $valid = false;
                break;
            }
        }

        //  TODO Add more validations
        return $valid;
    }

    public function setDisplaySize ( $width, $height ) {
        $size = new stdClass();
        $size->width = $width;
        $size->height = $height;

        $this->displaySize = $size;
    }

    public function setDisplayType($type) {
        $this->options['config']['chartType'] = $type;
    }

    public function getDisplayType() {
        if (!empty($this->options['config']['chartType'])) {
            return $this->options['config']['chartType'];
        } else {
            return null;
        }
    }

    public function getNumericColumnCount () {
        $count = 0;
        foreach ( $this->getColumns(true) as $column ) {
            if (in_array($column->type->applicationType,array('integer','number','currency','percent'))) {
                $count++;
            }
        }
        return $count;
    }

    public function getNonNumericColumnCount () {
        $count = 0;
        foreach ( $this->getColumns(true) as $column ) {
            if (!in_array($column->type->applicationType,array('integer','number','currency','percent'))) {
                $count++;
            }
        }
        return $count;
    }

    public function getMeasureCount () {
        $count = 0;
        foreach ( $this->getColumns(true) as $column ) {
            $parts = explode(':',$column->name);
            if ($parts[0] == 'measure') {
                $count++;
            } else if ( isset($column->source) ) {
                $expression = strtolower($column->source);
                if ( strpos($expression,'count(') !== false || strpos($expression,'sum(') !== false || strpos($expression,'max(') !== false || strpos($expression,'min(') !== false || strpos($expression,'avg(') !== false ) {
                    $count++;
                }
            }
        }
        return $count;
    }

    public function getColumnOrder() {
        return $this->columnOrder;
    }

    public function getCount () {
        if (!isset($this->count)) {
            try {
                if (isset($this->data) && isset($this->limit)) {
                    $count = count($this->data);
                    if ($this->limit > $count) {
                        $this->count = $count;
                    }
                }
                if (!isset($this->count)) {
                    $this->count = gd_data_controller_count_records_by_ui_metadata($this->datasets[0], $this->columns, $this->getQueryFilters(), $this->sort, $this->getQueryFormulas());
                }
            } catch (Exception $e) {
                LogHelper::log_error($e);
            }
        }

        return $this->count;
    }

    public function getFormula($name) {
        $formula = NULL;
        $formulas = $this->getFormulas();

        if (!empty($formulas)) {
            foreach($formulas as $f) {
                if ($f['name'] === $name) {
                    $formula = new FormulaMetaData();
                    $formula->name = $f['name'];
                    $formula->publicName = $f['publicName'];
                    $formula->type->applicationType = $f['type'];
                    $formula->source = $f['expression'];
                    $formula->expressionLanguage = $f['expressionLanguage'];
                    if (isset($f['version'])) {
                        $formula->version = $f['version'];
                    }
                    break;
                }
            }
        }

        return $formula;
    }

    public function getFormulas() {
        return $this->formulas;
    }

    public function getQueryFormulas() {
        $formulas = $this->getFormulas();
        $f = array();

        if (!empty($formulas)) {
            foreach($formulas as $formula) {
                $form = $this->getFormula($formula['name']);
                $form->name = NameSpaceHelper::removeNameSpace($form->name);
                $f[] = $form;
            }
        }

        return array(AbstractQueryRequest::OPTION__FORMULA_DEF => $f);
    }

    public function getData ( $force = false, $all = false ) {

        if ( (!isset($this->data) || $force) && !empty($this->datasets) ) {

            $columns = $this->columns;
            if ( !empty($this->options['visual']['useColumnDataForColor']) && $this->hasColumn($this->options['visual']['useColumnDataForColor']) ) {
                $columns[] = $this->options['visual']['useColumnDataForColor'];
            }

            $limit = $all ? NULL : $this->getQueryLimit();

            $event = new DefaultEvent();
            $event->owner = $this->getId();
            $offset = $this->offset;
            $sort = $this->sort;
            if ($this->options['config']['chartType'] == 'pivot_table') {
                $offset = 0;
                $limit = null;
                $sort = NULL;
            }

            $this->data = gd_data_controller_query_data_by_ui_metadata($this->datasets[0], $columns, $this->getQueryFilters(), $sort, $offset, $limit, $this->getQueryFormulas());
            $this->count = NULL;
            if (isset($event->owner)) {
                $event->type = 10; // see gd_health_monitoring_database_install() for more details
                EventRecorderFactory::getInstance()->record($event);
            }

            if ( $this->isDataTruncated && $this->getDisplayType() !== 'table' ) {
                drupal_add_http_header('gd_warnings', 'Result truncated to maximum '.$this->getChartMaxLimit().' records.', false);
            }
        }

        return $this->data;
    }

    public function getRawData () {
        return gd_data_controller_query_rawdata_by_ui_metadata($this->datasets[0], $this->getRawColumnList(), $this->getQueryFilters());
    }

    public function getChartMaxLimit () {
        switch ( $this->options['config']['chartType'] ) {
            case 'line' :
            case 'scatter' :
            case 'area' :
            case 'bar' :
            case 'column' :
            case 'sparkline' :
                $limit = GD_HIGHCHART_LIMIT;
                break;

            case 'pie' :
                $limit = GD_HIGHCHART_PIE_LIMIT;
                break;

            // Need this for performance when there is a lot of data; Gauge isn't rendered for more than one record
            case 'gauge' :
                $limit = GD_HIGHCHART_GUAGE_LIMIT;
                break;

            // Need this for performance when there is a lot of data; Dynamic Text isn't rendered for more than one record
            case 'dynamic_text' :
                $limit = GD_DYNAMIC_TEXT_LIMIT;
                break;

            case 'customview' :
                $limit = GD_HIGHCHART_LIMIT;
                break;

            case 'table' :
                $limit = GD_TABLE_ROWS_PER_PAGE;
                break;

            case 'map' :
                $limit = GD_MAP_LIMIT;
                break;

            default:
                $limit = null;
                break;
        }

        return $limit;
    }

    /**
     * @param $filters array
     */
    public function addFilter ( $filters ) {
        foreach ( $filters as $f ) {
            $this->filters[] = (object) $f;
        }
    }

    public function getFilters () {
        return $this->filters;
    }

    public function updateFilterValue($name, $exposed, $f) {
        foreach ( $this->filters as $filter ) {
            if ( $filter->name == $name && $filter->exposed == $exposed ) {
                $filter->value = isset($f->value) ? $f->value : null;
                $filter->operator = isset($f->operator) ? $f->operator : null;
                $filter->exposed = $exposed;
            }
        }
    }

    public function getUsedFilters() {
        $filters = array();
        foreach ( $this->getFilters() as $f ) {
            //  Don't want to modify the original filter object
            $filter = clone $f;
            $column = $this->getColumn($filter->column);
            if ( !$column ) {
                continue;
            }
            $filter->type = $column->type->applicationType;
            if ( !empty($f->name) && !empty($_REQUEST['t']) ) {
                foreach ( $_REQUEST['t'] as $dashboard => $queryFilter ) {
                    if ($dashboard == $this->dashboard) {
                        foreach ($queryFilter as $name => $t) {
                            if ( $filter->name == $name ) {
                                if (is_array($t)) {
                                    $filter->operator = $t['o'];
                                    if (isset($t['v'])) {
                                        $filter->value = array($this->formatFilterValue($t['v'], $this->getColumn($filter->column)));
                                    }
                                }
                            }
                        }
                    }
                }
            } else if ($f->exposed == 0) {
                $filter->operator = $f->operator;
                $filter->value = array($this->formatFilterValue((!isset($f->value)?null:$f->value), $this->getColumn($filter->column)));
            }
            $filters[] = $filter;
        }
        return $filters;
    }

    protected function getQueryFilters () {
        $query_filters = array();
        foreach ( $this->filters as $f ) {

            if ( empty($f->column) || empty($f->name) ) {
                LogHelper::log_warn('Missing filter name or column.');
                continue;
            }

            //  if a filter is pre-apply, generate an operator for it.
            if ( !$f->exposed ) {
                //  If there is a value, then format it otherwise leave it as null
                $value = isset($f->value) ? $this->formatFilterValue($f->value, $this->getColumn($f->column)) : null;
                $query_filters[$f->column][] = OperatorFactory::getInstance()->initiateHandler($f->operator,$value);
            }

            if ( $f->exposed ) {
                $requestFilter = FALSE;
                //  Check for query filters next
                if ( !empty($_REQUEST['t']) ) {
                    foreach ( $_REQUEST['t'] as $dashboardId => $requestFilters ) {
                        if ($dashboardId == $this->dashboard) {
                            foreach ($requestFilters as $filterName => $filterParams) {
                                if ( $f->name == $filterName && is_array($filterParams) ) {
                                    $requestFilter = TRUE;
                                    //  If there is a value, then format it otherwise leave it as null
                                    $value = isset($filterParams['v']) ? $this->formatFilterValue($filterParams['v'], $this->getColumn($f->column)) : null;
                                    $query_filters[$f->column][] = OperatorFactory::getInstance()->initiateHandler($filterParams['o'],$value);
                                }
                            }
                        }
                    }
                }

                if ( !empty($f->operator) && !$requestFilter) {
                    //  If a filter is exposed and has a default value for it, set it
                    $value = isset($f->value) ? $this->formatFilterValue($f->value, $this->getColumn($f->column)) : null;
                    $query_filters[$f->column][] = OperatorFactory::getInstance()->initiateHandler($f->operator,$value);
                }
            }
        }

        return $query_filters;
    }

    protected function formatFilterValue($value, $column) {
        if (isset($column)) {
            if ($this->isPercentColumn($column)) {
                if (is_array($value)) {
                    $values = array();
                    foreach($value as $v) {
                        $values[] = $v / 100;
                    }
                    return $values;
                } else {
                    return $value / 100;
                }
            }
        }

        return $value;
    }

    public function isPercentColumn($column) {
        $ret = $column->type->applicationType == 'percent';
        foreach ($this->options['column_configs'] as $config) {
            if ($config['columnId'] == $column->name) {
                $ret = $config['formatter']['format'] == 'percent';
            }
        }
        return $ret;
    }

    public function setDashboard ( $id ) {
        $this->dashboard = $id;
    }

    public function addDrilldown ( $drilldown ) {
        $this->drilldowns[] = $drilldown;
    }

    public function setDrilldowns ( $drilldowns ) {
        $this->drilldowns = $drilldowns;
    }

    public function getDrilldowns () {
        if ( !is_array($this->drilldowns) ) {
            $this->drilldowns = array();
        }
        return $this->drilldowns;
    }

    public function hasColumn($name) {
        if ( $this->getColumn($name) ) {
            return true;
        }
        return false;
    }

    /**
     * @param $name
     * @return null|AbstractMetaData
     */
    public function getColumnByFilterName ( $name ) {
        foreach ( $this->filters as $f ) {
            if ( $name == $f->name ) {
                return $this->getColumn($f->column);
            }
        }
        return null;
    }

    public function getColumn ( $name ) {
        //  Talk to Viktor
        $metamodel = $this->getMetamodel();
        if (isset($metamodel)) {
            $column = $metamodel->findElement($name);
            //  If no column, try checking formulas
            if (!isset($column)) {
                $column = $this->getFormula($name);
            }
            return $column;
        } else {
            return null;
        }
    }

    public function getColumns ( $extendedInfo = false ) {
        if (!$extendedInfo) {
            return $this->columns;
        } else {
            return $this->options['column_details'];
        }
    }

    public function canEdit () {
        if ( gd_account_user_is_admin() || gd_account_user_is_datasource_admin(null,$this->getDatasource()) ) {
            return true;
        }
        return false;
    }

    public function showTitle() {
        // extra conditions for backwards compatibility
        if ( !isset($this->options['visual']['displayChartTitle'])
            || is_null($this->options['visual']['displayChartTitle'])
            || $this->options['visual']['displayChartTitle'] == true
        ) {
            return true;
        }

        return false;
    }

    public function getConfig() {
        return $this->config;
    }

    public function showFilterOverlay() {
        if (!isset($this->options['visual']['displayFilterOverlay']) || $this->options['visual']['displayFilterOverlay'] == false ) {
            return false;
        }

        return true;
    }

    public function showMenu() {
        // extra conditions for backwards compatibility
        if ( !isset($this->options['visual']['displayReportMenu'])
            || is_null($this->options['visual']['displayReportMenu'])
            || $this->options['visual']['displayReportMenu'] == true
        ) {
            return true;
        }

        return false;
    }

    protected function getRawColumnList () {
        return $this->columns;
    }

    public function getExport ( $raw = false ) {

        // flag to query in the raw
        if ( !$raw ) {
            $result = $this->getData(FALSE, TRUE);
        } else {
            $result = $this->getRawData();
        }

        /*
         * If there is no data returned, building the fields will be incomplete.
         * Seems useless to build fields for data that isn't there anyways so
         * just return nothing.
         */
        if ( empty($result) ) {
            return null;
        }

        // need to build the header
        $fields = array();
        foreach ( $result[0] as $key => $value ) {

            // look through column configs
            foreach ( $this->options['column_configs'] as $column_config ) {
                if ( $column_config['columnId'] == $key ) {
                    $fields[$key] = $column_config['displayName'];
                }
            }

            if ( isset($fields[$key]) ) {
                continue;
            }

            // for columns that are for raw data, display names will not be
            // in the column configs
            $column = $this->getColumn($key);
            if ( $column ) {
                $fields[$key] = $column->publicName;
            }

            if ( !isset($fields[$key]) ) {
                log_debug('Missing column ('.$key.') information for export.');
                log_debug($fields);
                throw new Exception(t('Missing column information for export.'));
            }

        }

        /**
         * TODO
         * If we need column ordering to match the report config, then there needs
         * to be another loop over column configs to reorder the fields array.
         */

        $data = array();
        $data[] = array_values($fields);

        /* This was here to match column ordering, but since we are looping over
         * results to build the field list, ordering from column_details is ignored.
         * Now this is simply replacing the assoc index with numeric which seems to be
         * what we want for an export.
         */
        foreach ( $result as $row ) {
            $tmp = array();
            foreach ( $fields as $field_index => $field_name ) {
                $tmp[] = $row[$field_index];
            }
            $data[] = $tmp;
        }

        return $data;
    }

    public function setCustomView($markup) {
        $this->customView = $markup;
    }
    public function getCustomView() {
        return $this->customView;
    }

}
