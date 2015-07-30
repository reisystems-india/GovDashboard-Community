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


function gd_accessibility_players() {
    $imgSrc = GOVDASH_HOST . '/sites/all/modules/custom/gd/';
    $viewers = array(
        'Adobe Reader' => array(
            'img' => $imgSrc . 'images/accessibility/pdf.gif',
            'url' => 'http://get.adobe.com/reader/',
            'file_types' => array('PDF')
        ),
        'Microsoft Excel Viewer' => array(
            'img' => $imgSrc . 'images/accessibility/excel.png',
            'url' => 'http://www.microsoft.com/downloads/details.aspx?familyid=1CD6ACF9-CE06-4E1C-8DCF-F33F669DBC3A',
            'file_types' => array('XLS', 'XLSX')
        ),
        'Microsoft PowerPoint Viewer 2007' => array(
            'img' => $imgSrc . 'images/accessibility/powerpoint.png',
            'url' => 'http://www.microsoft.com/downloads/details.aspx?FamilyID=048DC840-14E1-467D-8DCA-19D2A8FD7485',
            'file_types' => array('PPT', 'PPTX')
        ),
        'Microsoft Word Viewer' => array(
            'img' => $imgSrc . 'images/accessibility/word.png',
            'url' => 'http://www.microsoft.com/downloads/details.aspx?FamilyID=3657ce88-7cfa-457a-9aec-f4f827f20cac',
            'file_types' => array('DOC', 'DOCX', 'WPD')
        ),
    );
    ob_start();
    drupal_add_library('gd', 'bootstrap');
    print '<h1 class="accessibility-title" tabindex="3000">Viewers & Players</h1>';
    print '<div class="viewers-container">';
    print gd_accessibility_get_viewers_table($viewers);
    print '</div>';

    $page = array(
        '#show_messages' => false,
        '#theme' => 'page',
        '#type' => 'page',
        'content' => array(
            'system_main' => array(
                '#markup' => ob_get_clean()
            )
        ),
        'post_header' => array(
            '#markup' => ''
        ),
        'pre_content' => array(
            '#markup' => ''
        )
    );

    return $page;
}

function gd_accessibility_info() {
    drupal_add_library('gd', 'bootstrap');

    ob_start();
    print '<div class="container">';
    print '<h1 tabindex="3000" class="accessibility-title">GovDashboard Accessibility Information</h1>';
    print '<p tabindex="3000">GovDashboard  provides lightweight, easy to use, elegant dashboard solutions to  a large audience which includes individuals with disabilities.  GovDashboard follows Section 508 and Web Content Accessibility Guidelines (WCAG) 2.0 Level A standard requirements and guidelines to cater to a wide variety of audience.</p>';
    print '<p tabindex="3000">GovDashboard viewer tool is routinely tested for compatibility on Microsoft Internet Explorer 8 and 9. In addition JAWS 13 screen reader test are conducted on GovDashboard viewer tool to ensure compliance in accordance with Section 508 of the Rehabilitation Act. The Section 508 Technical Standards checklist along with policy experts and in-depth test plans are used as a basis for conducting these compliance tests. For more information on Section 508 technical standards please visit www.Section508.gov</p>';
    print '<h3 tabindex="3000">GovDashboard and Accessibility Requirements</h3>';
    print '<ul tabindex="3000">';
    print '<li>GovDashboard contain visualization tools such as charts and graphs that are not entirely accessible  to individuals with disabilities. For individuals with disabilities data tables are provided as an alternate to charts and graphs.. Individuals can access this option by selecting View Table option from the widget menu.</li>';
    print '<li>JavaScript support must be turned on and made available to support accessibility requirements.</li>';
    print '</ul>';
    print '<p tabindex="3000">The following areas of GovDashboard are not completely accessible for individuals with disabilities:</p>';
    print '<ul tabindex="3000">';
    print '<li>Designer: Some of the rich interactive interfaces for designer, such as drag-and-drop charts and filters are  currently not keyboard accessible.</li>';
    print '<li> Account Management is another area on the dashboard that is not accessible to individuals with disability.</li>';
    print '</ul>';
    print '<p tabindex="3000">GovDashboard will provide accessible documentation and support for all users. Please contact the support team if individuals using assistive technology (such as a screen reader, eye tracking device, voice recognition software, etc.)  are experiencing  difficulty in accessing information on GovDashboard, . In addition please provide the URL (web address) of the material  being accessed, the problem in detail, and contact information. A GovDashboard support team member will contact you and provide support for the issue.</p>';
    print '</div>';

    $page = array(
        '#show_messages' => false,
        '#theme' => 'page',
        '#type' => 'page',
        'content' => array(
            'system_main' => array(
                '#markup' => ob_get_clean()
            )
        ),
        'post_header' => array(
            '#markup' => ''
        ),
        'pre_content' => array(
            '#markup' => ''
        )
    );

    return $page;
}

function gd_accessibility_get_viewers_table($viewers) {
    $table = '<table tabindex="3000" id="viewersTable" class="table table-striped table-bordered dataTable">';
    $table .= '<thead class="viewers-header"><th scope="col" tabindex="3000" style="width:75px;" class="viewers-th">File Type</th><th scope="col" tabindex="3000" class="viewers-th">Name of Viewers</th><th scope="col" tabindex="3000" class="viewers-th">URL of Viewers</th></thead>';
    $table .= '<tbody class="viewers-body">';
    foreach ($viewers as $name => $info) {
        foreach ($info['file_types'] as $fileType) {
            $row = '<tr class="viewers-row">';

            $row .= '<td scope="row" tabindex="3000" class="viewers-cell">' . '<img style="width:20px;height:20px;" src="' . $info['img'] . '" alt="' . $name . '" />';
            $row .= $fileType . '</td>';
            $row .= '<td scope="row" tabindex="3000" class="viewers-cell">' . $name . '</td>';
            $row .= '<td scope="row" tabindex="3000" class="viewers-cell"><a href="' . $info['url'] . '" tabindex="3000" target="_blank">' . $info['url'] . '</a></td>';
            $row .= '</tr>';
            $table .= $row;
        }
    }
    $table .= '</tbody>';
    $table .= '</table>';
    return $table;
}