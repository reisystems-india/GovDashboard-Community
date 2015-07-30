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


namespace GD\Js;

use GD\Common\Pattern\Singleton\AbstractSingleton;

class Registry extends AbstractSingleton {

    protected static $instance = null;
    
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new Registry();
        }

        return self::$instance;
    }
    
    protected $files = array();
    protected $vendorFiles = array();
    
    protected function __construct() {
        $path = drupal_get_path('module', 'gd');

        $this->files[] = $path.'/js/gd/GD.js';
        $this->files[] = $path.'/js/gd/Class.js';

        $this->files[] = $path.'/js/util/Util.js';
        $this->files[] = $path.'/js/util/Utility.js';
        $this->files[] = $path.'/js/util/UriHandler.js';
        $this->files[] = $path.'/js/util/DateFormat.js';
        $this->files[] = $path.'/js/util/NameFormatter.js';
        $this->files[] = $path.'/js/util/Service.js';
        $this->files[] = $path.'/js/util/AjaxFactory.js';
        $this->files[] = $path.'/js/util/Cookie.js';
        $this->files[] = $path.'/js/util/IdGenerator.js';

        $this->files[] = $path.'/js/apps/App.js';
        $this->files[] = $path.'/js/apps/Ext.js';

        $this->files[] = $path.'/js/view/View.js';
        $this->files[] = $path.'/js/view/primitive/ViewPrimitive.js';
        $this->files[] = $path.'/js/view/primitive/ViewCheckbox.js';
        $this->files[] = $path.'/js/view/primitive/ViewRadio.js';
        $this->files[] = $path.'/js/view/primitive/ViewSlider.js';
        $this->files[] = $path.'/js/view/primitive/ViewSelect.js';
        $this->files[] = $path.'/js/view/primitive/ViewText.js';
        $this->files[] = $path.'/js/view/primitive/ViewImage.js';
        $this->files[] = $path.'/js/view/ViewFactory.js';
        $this->files[] = $path.'/js/view/composite/ViewCheckboxGroup.js';
        $this->files[] = $path.'/js/view/composite/ViewRadioGroup.js';
        $this->files[] = $path.'/js/view/custom/slider/ViewDateSlider.js';
        $this->files[] = $path.'/js/view/custom/slider/ViewMonthSlider.js';
        $this->files[] = $path.'/js/view/custom/slider/ViewQuarterSlider.js';

        $this->files[] = $path.'/js/breadcrumb/Breadcrumb.js';
        $this->files[] = $path.'/js/breadcrumb/BreadcrumbFactory.js';
        $this->files[] = $path.'/js/breadcrumb/BreadcrumbView.js';
        $this->files[] = $path.'/js/breadcrumb/BreadcrumbSeparator.js';

        $this->files[] = $path.'/js/component/list/ListView.js';
        $this->files[] = $path.'/js/component/tree/TreeView.js';

        $this->files[] = $path.'/js/filter/Filter.js';
        $this->files[] = $path.'/js/filter/form/FilterForm.js';
        $this->files[] = $path.'/js/filter/form/SelectFilterForm.js';
        $this->files[] = $path.'/js/filter/form/DateMonthNameFilterForm.js';
        $this->files[] = $path.'/js/filter/form/DateQuarterNameFilterForm.js';
        $this->files[] = $path.'/js/filter/form/DateFilterForm.js';
        $this->files[] = $path.'/js/filter/form/DateTimeFilterForm.js';
        $this->files[] = $path.'/js/filter/form/TimeFilterForm.js';
        $this->files[] = $path.'/js/filter/form/SelectTextFilterForm.js';
        $this->files[] = $path.'/js/filter/form/DateMonthFilterForm.js';
        $this->files[] = $path.'/js/filter/form/DateQuarterFilterForm.js';
        $this->files[] = $path.'/js/filter/form/LookupFilterForm.js';
        $this->files[] = $path.'/js/filter/form/FilterFormFactory.js';
        $this->files[] = $path.'/js/filter/operator/FilterOperator.js';
        $this->files[] = $path.'/js/filter/operator/EqualOperator.js';
        $this->files[] = $path.'/js/filter/operator/EmptyOperator.js';
        $this->files[] = $path.'/js/filter/operator/GreaterThanOperator.js';
        $this->files[] = $path.'/js/filter/operator/LessThanOperator.js';
        $this->files[] = $path.'/js/filter/operator/CurrentOperator.js';
        $this->files[] = $path.'/js/filter/operator/LatestOperator.js';
        $this->files[] = $path.'/js/filter/operator/OldestOperator.js';
        $this->files[] = $path.'/js/filter/operator/PreviousOperator.js';
        $this->files[] = $path.'/js/filter/operator/RangeOperator.js';
        $this->files[] = $path.'/js/filter/operator/WildcardOperator.js';
        $this->files[] = $path.'/js/filter/operator/OperatorFactory.js';
        $this->files[] = $path.'/js/filter/ext/ViewLookupFilterForm.js';
        $this->files[] = $path.'/js/filter/FilterViewFactory.js';
        $this->files[] = $path.'/js/filter/FilterViewForm.js';

        $this->files[] = $path.'/js/dashboard/Dashboard.js';
        $this->files[] = $path.'/js/dashboard/DashboardView.js';

        $this->files[] = $path.'/js/report/Report.js';
        $this->files[] = $path.'/js/report/view/ReportView.js';
        $this->files[] = $path.'/js/report/view/content/ReportContentViewDefault.js';
        $this->files[] = $path.'/js/report/view/content/ReportContentViewTable.js';
        $this->files[] = $path.'/js/report/menu/ReportMenu.js';
        $this->files[] = $path.'/js/report/menu/view/ReportMenuView.js';

        // Vendor Files
        $this->vendorFiles[] = DRUPAL_ROOT.'/sites/all/libraries/jquery/jquery.min.js';
        $this->vendorFiles[] = DRUPAL_ROOT.'/sites/all/libraries/momentjs/js/moment-with-locales.min.js';
        $this->vendorFiles[] = DRUPAL_ROOT.'/sites/all/libraries/highcharts/js/highcharts.js';
        $this->vendorFiles[] = DRUPAL_ROOT.'/sites/all/libraries/highcharts/js/highcharts-more.js';
        $this->vendorFiles[] = DRUPAL_ROOT.'/sites/all/libraries/highcharts/js/modules/funnel.js';
        $this->vendorFiles[] = DRUPAL_ROOT.'/sites/all/libraries/highcharts/js/modules/exporting.js';
        $this->vendorFiles[] = DRUPAL_ROOT.'/sites/all/libraries/sparkline/jquery.sparkline.min.js';
        $this->vendorFiles[] = DRUPAL_ROOT.'/sites/all/libraries/gd_print/jquery.gd_print.js';
        $this->vendorFiles[] = DRUPAL_ROOT.'/sites/all/libraries/gd_print/jquery.gd_print_table.js';
        $this->vendorFiles[] = DRUPAL_ROOT.'/sites/all/libraries/datatables/media/js/jquery.dataTables.min.js';
        $this->vendorFiles[] = DRUPAL_ROOT.'/sites/all/libraries/bootstrap/js/bootstrap.min.js';
    }

    public function getFiles() {
        return $this->files;
    }

    public function getVendorFiles() {
        return $this->vendorFiles;
    }

    public function addVendorFile($path) {
        $this->vendorFiles[] = $path;
    }
}
