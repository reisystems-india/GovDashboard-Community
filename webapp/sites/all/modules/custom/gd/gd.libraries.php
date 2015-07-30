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


function gd_get_libs () {

    $libraries['GD_Core'] = array(
        'title'   => 'GovDashboard Core',
        'website' => 'https://govdashboard.com',
        'version' => '0.1',
        'js' => array(
            drupal_get_path('module','gd').'/js/gd/GD.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/gd/Class.js' => array('weight' => 0)
        )
    );

    $libraries['GD_App'] = array(
        'title'   => 'GovDashboard App',
        'website' => 'https://govdashboard.com',
        'version' => '0.1',
        'js' => array(
            drupal_get_path('module','gd').'/js/apps/App.js' => array('weight' => 0),
        ),
        'dependencies' => array(
            array('gd','GD_Core')
        )
    );

    $libraries['GD_Utility'] = array(
        'title'   => 'GovDashboard Utility',
        'website' => 'https://govdashboard.com',
        'version' => '0.1',
        'js' => array(
            drupal_get_path('module','gd').'/js/util/Util.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/util/UriHandler.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/util/DateFormat.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/util/NameFormatter.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/util/Service.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/util/AjaxFactory.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/util/Cookie.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/util/IdGenerator.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/util/Utility.js' => array()
        ),
        'dependencies' => array(
            array('gd','GD_Core')
        )
    );

    $libraries['GD_View'] = array(
        'title'   => 'GovDashboard Views',
        'website' => 'https://govdashboard.com',
        'version' => '0.1',
        'js' => array(
            drupal_get_path('module','gd').'/js/view/View.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/view/primitive/ViewPrimitive.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/view/primitive/ViewCheckbox.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/view/primitive/ViewRadio.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/view/primitive/ViewSlider.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/view/primitive/ViewSelect.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/view/primitive/ViewText.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/view/primitive/ViewImage.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/view/ViewFactory.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/view/composite/ViewCheckboxGroup.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/view/composite/ViewRadioGroup.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/view/custom/slider/ViewDateSlider.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/view/custom/slider/ViewMonthSlider.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/view/custom/slider/ViewQuarterSlider.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/view/custom/multiselect/ViewChosen.js' => array('weight' => 0)
        ),
        'dependencies' => array(
            array('gd','GD_Core'),
            array('gd','chosen')
        )
    );

    $libraries['GD_Components'] = array(
        'title'   => 'GovDashboard Components',
        'website' => 'https://govdashboard.com',
        'version' => '0.1',
        'js' => array(
            drupal_get_path('module','gd').'/js/component/list/ListView.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/component/tree/TreeView.js' => array('weight' => 0),
        ),
        'css' => array(
            drupal_get_path('module','gd').'/css/Component.css' => array('weight'=>1,'group'=>CSS_THEME)
        ),
        'dependencies' => array(
            array('gd','GD_View'),
            array('gd','jsTree')
        )
    );

    $libraries['GD_Breadcrumb'] = array(
        'title'   => 'GovDashboard Breadcrumbs',
        'website' => 'https://govdashboard.com',
        'version' => '0.1',
        'js' => array(
            drupal_get_path('module','gd').'/js/breadcrumb/Breadcrumb.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/breadcrumb/BreadcrumbFactory.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/breadcrumb/BreadcrumbView.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/breadcrumb/BreadcrumbSeparator.js' => array('weight' => 0)
        ),
        'dependencies' => array(
            array('gd','GD_View')
        )
    );

    $libraries['GD_Filter'] = array(
        'title'   => 'GovDashboard Filters',
        'website' => 'https://govdashboard.com',
        'version' => '0.1',
        'js' => array(
            drupal_get_path('module','gd').'/js/filter/Filter.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/filter/form/FilterForm.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/filter/form/SelectFilterForm.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/filter/form/DateMonthNameFilterForm.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/filter/form/DateQuarterNameFilterForm.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/filter/form/DateFilterForm.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/filter/form/DateTimeFilterForm.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/filter/form/TimeFilterForm.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/filter/form/SelectTextFilterForm.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/filter/form/DateMonthFilterForm.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/filter/form/DateQuarterFilterForm.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/filter/form/LookupFilterForm.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/filter/form/FilterFormFactory.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/filter/operator/FilterOperator.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/filter/operator/EqualOperator.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/filter/operator/EmptyOperator.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/filter/operator/GreaterThanOperator.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/filter/operator/LessThanOperator.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/filter/operator/CurrentOperator.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/filter/operator/LatestOperator.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/filter/operator/OldestOperator.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/filter/operator/PreviousOperator.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/filter/operator/RangeOperator.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/filter/operator/WildcardOperator.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/filter/operator/OperatorFactory.js' => array('weight' => 0),
        ),
        'dependencies' => array(
            array('gd','GD_View'),
            array('gd','GD_Utility'),
            array('gd','GD_Components'),
        )
    );

    $libraries['GD_Filter_View'] = array(
        'title'   => 'GovDashboard Filter Views',
        'website' => 'https://govdashboard.com',
        'version' => '0.1',
        'js' => array(
            drupal_get_path('module','gd').'/js/filter/ext/ViewLookupFilterForm.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/filter/FilterViewFactory.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/filter/FilterViewForm.js' => array('weight' => 0),
        ),
        'dependencies' => array(
            array('gd','GD_Filter'),
        )
    );

    $libraries['GD_Dashboard'] = array(
        'title'   => 'GovDashboard Dashboard Library',
        'website' => 'https://govdashboard.com',
        'version' => '0.1',
        'js' => array(
            drupal_get_path('module','gd').'/js/dashboard/Dashboard.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/dashboard/DashboardView.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/dashboard/dashboardPrint.js' => array('weight' => 0),
        ),
        'dependencies' => array(
            array('gd','GD_View'),
        )
    );

    $libraries['GD_Report'] = array(
        'title'   => 'GovDashboard Report Library',
        'website' => 'https://govdashboard.com',
        'version' => '0.1',
        'js' => array(
            drupal_get_path('module','gd').'/js/report/Report.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/report/view/ReportView.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/report/view/content/ReportContentViewDefault.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/report/view/content/ReportContentViewTable.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/report/menu/ReportMenu.js' => array('weight' => 0),
            drupal_get_path('module','gd').'/js/report/menu/view/ReportMenuView.js' => array('weight' => 0)
        ),
        'dependencies' => array(
            array('gd','GD_View'),
        )
    );

    $libraries['GD_JS'] = array(
        'title' => 'GD JavaScript Lib',
        'website' => 'https://govdashboard.com',
        'version' => '0.1',
        'js' => array(),
        'dependencies' => array(
            array('gd','GovDash_jQuery'),
            array('gd','GovDash_jQueryUI'),
            array('gd','highcharts'),
            array('gd','GD_PivotTable'),
            array('gd','bootstrap'),
            array('gd','placeholder'),
            array('gd','modernizr'),
            array('gd','underscore'),
            array('gd','datatables'),
            array('gd','momentjs'),
            array('gd','bootstrap-datetimepicker'),
            array('gd','GD_Components'),
            array('gd','GD_App'),
            array('gd','GD_Filter'),
            array('gd','GD_Breadcrumb'),
            array('gd','spectrum'),
            array('gd','font-awesome')
        )
    );

    $libraries['GD_EXT_JS'] = array(
        'title' => 'GD Ext Lib',
        'website' => 'https://govdashboard.com',
        'version' => '0.1',
        'js' => array(
            drupal_get_path('module','gd').'/js/apps/Ext.js' => array('weight' => 0),
        ),
        'dependencies' => array(
            array('gd','GovDash_jQuery'),
            array('gd','GovDash_jQueryUI'),
            array('gd','highcharts'),
            array('gd','GD_PivotTable'),
            array('gd','bootstrap'),
            array('gd','placeholder'),
            array('gd','modernizr'),
            array('gd','underscore'),
            array('gd','datatables'),
            array('gd','momentjs'),
            array('gd','bootstrap-datetimepicker'),
            array('gd','ace'),
            array('gd','GD_App'),
            array('gd','GD_Filter'),
            array('gd','GD_Filter_View'),
            array('gd','GD_Breadcrumb'),
            array('gd','GD_Report'),
            array('gd','GD_Dashboard')
        )
    );

    //  TODO Pull into separate Pivot Table module
    $libraries['GD_PivotTable'] = array(
        'title' => 'GD Pivot Table Integration',
        'website' => 'https://govdashboard.com',
        'version' => '0.1',
        'js' => array(
            drupal_get_path('module', 'gd_report') . "/theme/PivotTable/js/GD_PivotTable.js" => array('weight' => 0),
        ),
        'css' => array(
            drupal_get_path('module', 'gd_report') . '/theme/PivotTable/css/GD_PivotTable.css' => array('weight'=>1,'group'=>CSS_THEME)
        ),
        'dependencies' => array(
            array('gd','GovDash_jQuery'),
            array('gd','GovDash_jQueryUI'),
            array('gd','pivotTable'),
            array('gd','GD_View'),
        )
    );

    return $libraries;
}

function gd_get_contrib_libs() {

    $libraries['GovDash_jQuery'] = array(
        'title'   => 'jQuery',
        'website' => '',
        'version' => '1.11.1',
        'js' => array(
            'sites/all/libraries/jquery/jquery.min.js' => array('weight' => JS_LIBRARY)
        )
    );

    $libraries['GovDash_jQueryUI'] = array(
        'title'   => 'jQuery-UI',
        'website' => '',
        'version' => '1.10.4',
        'js' => array(
            'sites/all/libraries/jquery-ui/js/jquery-ui-1.10.4.custom.min.js' => array('weight' => JS_LIBRARY)
        ),
        'css' => array (
            'sites/all/libraries/jquery-ui/css/smoothness/jquery-ui-1.10.4.custom.min.css' => array()
        )
    );

    $libraries['highcharts'] = array(
        'title'   => 'Highcharts',
        'website' => 'http://www.highcharts.com/',
        'version' => '3.0.2',
        'js' => array(
            'sites/all/libraries/highcharts/js/highcharts.js' => array('weight' => 0),
            'sites/all/libraries/highcharts/js/highcharts-more.js' => array('weight' => 0),
            'sites/all/libraries/highcharts/js/modules/funnel.js' => array('weight' => 0),
            'sites/all/libraries/highcharts/js/modules/exporting.js' => array('weight' => 0)
        )
    );

    $libraries['pivotTable'] = array(
        'title' => 'JavaScript Pivot Table',
        'website' => 'https://github.com/nicolaskruchten/pivottable',
        'version' => '1.3.0',
        'js' => array(
            'sites/all/libraries/pivottable/js/pivot.js' => array('weight' => 0),
            'sites/all/libraries/pivottable/js/plugins/d3_renderers.min.js' => array('weight' => 0),
            'sites/all/libraries/pivottable/js/plugins/gchart_renderers.min.js' => array('weight' => 0),
        ),
        'css' => array(
            'sites/all/libraries/pivottable/css/pivot.min.css' => array('weight'=>0,'group'=>CSS_THEME)
        )
    );

    $libraries['placeholder'] = array(
        'title'   => 'Placeholder.js',
        'website' => 'http://jamesallardice.github.io/Placeholders.js',
        'version' => '2.1.0',
        'js' => array(
            'sites/all/libraries/placeholder/Placeholder.js' => array('weight' => 0)
        )
    );

    $libraries['datatables'] = array(
        'title'   => 'DataTables',
        'website' => 'http://www.datatables.net',
        'version' => '1.10.5',
        'js' => array(
            'sites/all/libraries/datatables/media/js/jquery.dataTables.js' => array('weight' => 0),
            'sites/all/libraries/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.js' => array('weight' => 0)
        ),
        'css' => array (
            'sites/all/libraries/datatables/media/css/jquery.dataTables.min.css' => array('weight'=>0),
            'sites/all/libraries/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css' => array('weight'=>0,'group'=>CSS_THEME)
        )
    );

    $libraries['spectrum'] = array(
        'title'   => 'Spectrum-colorPicker',
        'website' => 'http://bgrins.github.io/spectrum/',
        'version' => '1.0.0',
        'js' => array(
            'sites/all/libraries/spectrum-colorPicker/spectrum.js' => array('weight' => 0)
        ),
        'css' => array (
            'sites/all/libraries/spectrum-colorPicker/spectrum.css' => array('weight'=>0)
        )
    );

    $libraries['bootstrap'] = array(
        'title'   => 'Bootstrap 3',
        'website' => 'http://getbootstrap.com/',
        'version' => '3.1.1',
        'js' => array(
            'sites/all/libraries/bootstrap/js/bootstrap.min.js' => array('weight' => 0)
        ),
        'css' => array (
            'sites/all/libraries/bootstrap/css/bootstrap.css' => array('weight'=>0,'group'=>CSS_THEME),
            drupal_get_path('module','gd').'/css/gd-bootstrap.css' => array('weight'=>0,'group'=>CSS_THEME)
        ),
        'dependencies' => array(
            array('gd','GovDash_jQuery')
        )
    );

    $libraries['font-awesome'] = array(
        'title'   => 'Font Awesome',
        'website' => 'http://fortawesome.github.io/Font-Awesome/',
        'version' => '4.3.0',
        'css' => array(
            'sites/all/libraries/font-awesome/css/font-awesome.min.css' => array('weight' => 0)
        )
    );

    $libraries['d3'] = array(
        'title'   => 'd3',
        'website' => 'http://d3js.org',
        'version' => '3.4.13',
        'js' => array(
            'sites/all/libraries/d3/d3.js' => array('weight' => 0)
        )
    );

    $libraries['ace'] = array(
        'title'   => 'Ace',
        'website' => 'http://ace.ajax.org',
        'version' => '1.0',
        'js' => array(
            'sites/all/libraries/ace/ace.js' => array('weight' => 0),
            'sites/all/libraries/ace/mode-sql.js' => array('weight' => 0),
            'sites/all/libraries/ace/mode-html.js' => array('weight' => 0)
        )
    );

    $libraries['quill'] = array(
        'title'   => 'Quill',
        'website' => 'http://quilljs.com/',
        'version' => '0.18.1',
        'js' => array(
            'sites/all/libraries/quill/quill.min.js' => array('weight' => 0)
        ),
        'css' => array(
            'sites/all/libraries/quill/quill.snow.css' => array('weight' => 0)
        )
    );

    $libraries['jQueryContext'] = array(
        'title'   => 'JQuery Context Menu',
        'version' => '1.0',
        'js' => array(
            'sites/all/libraries/jquery-context/jquery.context.js' => array('weight' => 0)
        )
    );

    $libraries['q'] = array(
        'title'   => 'Q',
        'website' => 'https://github.com/kriskowal/q',
        'version' => '1.0',
        'js' => array(
            'sites/all/libraries/q/q.min.js' => array('weight' => 0)
        )
    );

    $libraries['modernizr'] = array(
        'title'   => 'Modernizr',
        'website' => 'http://modernizr.com',
        'version' => '2.6.2',
        'js' => array(
            'sites/all/libraries/modernizr/modernizr.min.js' => array('weight' => 0)
        )
    );

    $libraries['jqueryFileUploader'] = array(
        'title'   => 'jQueryFileUploader',
        'website' => 'http://blueimp.github.io/jQuery-File-Upload/',
        'version' => '8.2.1',
        'js' => array(
            'sites/all/libraries/jQueryFileUpload/js/vendor/jquery.ui.widget.js' => array('weight' => 0),
            'sites/all/libraries/jQueryFileUpload/js/jquery.iframe-transport.js' => array('weight' => 0),
            'sites/all/libraries/jQueryFileUpload/js/jquery.fileupload.js' => array('weight' => 0)
        )
    );

    $libraries['underscore'] = array(
        'title'   => 'underscore',
        'website' => 'http://documentcloud.github.io/underscore',
        'version' => '1.5.0',
        'js' => array(
            'sites/all/libraries/underscore/underscore.min.js' => array('weight' => 0)
        )
    );

    $libraries['jsTree'] = array(
        'title'   => 'jsTree',
        'website' => 'http://www.jstree.com/',
        'version' => '3.1.0',
        'js' => array(
            'sites/all/libraries/jsTree/dist/jstree.min.js' => array('weight' => 0)
        ),
        'css' => array(
            'sites/all/libraries/jsTree/dist/themes/default/style.min.css' => array('weight' => 0)
        )
    );

    $libraries['jQueryTE'] = array(
        'title'   => 'jQuery TE',
        'website' => 'http://jqueryte.com',
        'version' => '1.4.0',
        'js' => array(
            'sites/all/libraries/jquery-te/jquery-te-1.4.0.min.js' => array('weight' => 0)
        ),
        'css' => array(
            'sites/all/libraries/jquery-te/jquery-te-1.4.0.css' => array('weight' => 0)
        )
    );

    $libraries['bootstrapMultiselect'] = array(
        'title'   => 'Bootstrap Multiselect',
        'website' => 'http://davidstutz.github.io/bootstrap-multiselect/',
        'version' => '1.0.0',
        'js' => array(
            'sites/all/libraries/bootstrap-multiselect/js/bootstrap-multiselect.js' => array('weight' => 0)
        ),
        'css' => array(
            'sites/all/libraries/bootstrap-multiselect/css/bootstrap-multiselect.css' => array('weight'=>0,'group'=>CSS_THEME)
        )
    );

    $libraries['fuelux'] = array(
        'title'   => 'Fuel UX',
        'website' => 'https://github.com/ExactTarget/fuelux',
        'version' => '3.0.0',
        'js' => array(
            'sites/all/libraries/fuelux/js/fuelux.min.js' => array('weight' => 0)
        ),
        'css' => array(
            'sites/all/libraries/fuelux/css/fuelux.min.css' => array('weight'=>0,'group'=>CSS_THEME)
        )
    );

    $libraries['momentjs'] = array(
        'title'   => 'Moment.js',
        'website' => 'http://momentjs.com/',
        'version' => '2.8.3',
        'js' => array(
//            'sites/all/libraries/momentjs/js/moment.min.js' => array('weight' => 0)
            'sites/all/libraries/momentjs/js/moment-with-locales.min.js' => array('weight' => 0)
        )
    );

    $libraries['bootstrap-datetimepicker'] = array(
        'title'   => 'Bootstrap DateTimePicker',
        'website' => 'https://github.com/Eonasdan/bootstrap-datetimepicker',
        'version' => '3.1.3',
        'js' => array(
            'sites/all/libraries/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js' => array('weight' => 0)
        ),
        'css' => array(
            'sites/all/libraries/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css' => array('weight'=>0,'group'=>CSS_THEME)
        )
    );

    $libraries['chosen'] = array(
        'title'   => 'Chosen',
        'website' => 'http://harvesthq.github.io/chosen/',
        'version' => '0.10.0',
        'js' => array(
            'sites/all/libraries/jquery-ui/widgets/chosen.js' => array('weight' => 0)
        ),
        'css' => array(
            'sites/all/libraries/jquery-ui/css/ui-lightness/chosen.css' => array('weight'=>0,'group'=>CSS_THEME)
        )
    );

    $libraries['bootstrap-slider'] = array(
        'title'   => 'Bootstrap Slider',
        'website' => 'https://github.com/seiyria/bootstrap-slider',
        'version' => '4.2.0',
        'js' => array(
            'sites/all/libraries/bootstrap-slider/bootstrap-slider.min.js' => array('weight' => 0)
        ),
        'css' => array(
            'sites/all/libraries/bootstrap-slider/css/bootstrap-slider.min.css' => array('weight'=>0,'group'=>CSS_THEME)
        ),
        'dependencies' => array(
            array('gd', 'bootstrap')
        )
    );

    return $libraries;
}