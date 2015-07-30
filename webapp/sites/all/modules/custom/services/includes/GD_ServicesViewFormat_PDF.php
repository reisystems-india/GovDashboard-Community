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


require_once DRUPAL_ROOT.'/sites/all/libraries/tcpdf/config/lang/eng.php';

// use alternative config

define ('K_PATH_MAIN', DRUPAL_ROOT.'/sites/all/libraries/tcpdf/');

// Automatic calculation for the following K_PATH_URL constant
$k_path_url = K_PATH_MAIN; // default value for console mode
if (isset($_SERVER['SERVER_NAME']) AND (!empty($_SERVER['SERVER_NAME']))) {
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) {
        $k_path_url = 'https://';
    } else {
        $k_path_url = 'http://';
    }
    $k_path_url .= $_SERVER['SERVER_NAME'];
    $k_path_url .= str_replace( '\\', '/', substr(K_PATH_MAIN, (strlen($_SERVER['DOCUMENT_ROOT']) - 1)));
}

/**
 * URL path to tcpdf installation folder (http://localhost/tcpdf/).
 * By default it is automatically calculated but you can also set it as a fixed string to improve performances.
 */
define ('K_PATH_URL', $k_path_url);

/**
 * path for PDF fonts
 * use K_PATH_MAIN.'fonts/old/' for old non-UTF8 fonts
 */
define ('K_PATH_FONTS', K_PATH_MAIN.'fonts/');

/**
 * cache directory for temporary files (full path)
 */
define ('K_PATH_CACHE', 'public://tcpdf/cache/');

/**
 * cache directory for temporary files (url path)
 */
define ('K_PATH_URL_CACHE', file_create_url(K_PATH_CACHE));

/**
 *images directory
 */
define ('K_PATH_IMAGES', drupal_get_path('theme', variable_get('theme_default', NULL)).'/images');

/**
 * blank image
 */
define ('K_BLANK_IMAGE', K_PATH_MAIN.'images/_blank.png');

/**
 * page format
 */
define ('PDF_PAGE_FORMAT', 'USLETTER');

/**
 * page orientation (P=portrait, L=landscape)
 */
define ('PDF_PAGE_ORIENTATION', 'P');

/**
 * document creator
 */
define ('PDF_CREATOR', 'Govdashboard');

/**
 * document author
 */
define ('PDF_AUTHOR', 'Govdashboard');

/**
 * header title
 */
define ('PDF_HEADER_TITLE', 'Generated PDF');

/**
 * header description string
 */
define ('PDF_HEADER_STRING', "Govdashboard - www.govdashboard.com");

/**
 * image logo
 */
define ('PDF_HEADER_LOGO', 'gd-logo.png');

/**
 * header logo image width [mm]
 */
define ('PDF_HEADER_LOGO_WIDTH', 0);

/**
 *  document unit of measure [pt=point, mm=millimeter, cm=centimeter, in=inch]
 */
define ('PDF_UNIT', 'mm');

/**
 * header margin
 */
define ('PDF_MARGIN_HEADER', 5);

/**
 * footer margin
 */
define ('PDF_MARGIN_FOOTER', 10);

/**
 * top margin
 */
define ('PDF_MARGIN_TOP', 27);

/**
 * bottom margin
 */
define ('PDF_MARGIN_BOTTOM', 25);

/**
 * left margin
 */
define ('PDF_MARGIN_LEFT', 15);

/**
 * right margin
 */
define ('PDF_MARGIN_RIGHT', 15);

/**
 * default main font name
 */
define ('PDF_FONT_NAME_MAIN', 'helvetica');

/**
 * default main font size
 */
define ('PDF_FONT_SIZE_MAIN', 10);

/**
 * default data font name
 */
define ('PDF_FONT_NAME_DATA', 'helvetica');

/**
 * default data font size
 */
define ('PDF_FONT_SIZE_DATA', 8);

/**
 * default monospaced font name
 */
define ('PDF_FONT_MONOSPACED', 'courier');

/**
 * ratio used to adjust the conversion of pixels to user units
 */
define ('PDF_IMAGE_SCALE_RATIO', 1.25);

/**
 * magnification factor for titles
 */
define('HEAD_MAGNIFICATION', 1.1);

/**
 * height of cell repect font height
 */
define('K_CELL_HEIGHT_RATIO', 1.25);

/**
 * title magnification respect main font size
 */
define('K_TITLE_MAGNIFICATION', 1.3);

/**
 * reduction factor for small font
 */
define('K_SMALL_RATIO', 2/3);

/**
 * set to true to enable the special procedure used to avoid the overlappind of symbols on Thai language
 */
define('K_THAI_TOPCHARS', true);

/**
 * if true allows to call TCPDF methods using HTML syntax
 * IMPORTANT: For security reason, disable this feature if you are printing user HTML content.
 */
define('K_TCPDF_CALLS_IN_HTML', false);


define("K_TCPDF_EXTERNAL_CONFIG", true);
require_once DRUPAL_ROOT.'/sites/all/libraries/tcpdf/tcpdf.php';

if ( !is_dir(K_PATH_CACHE) ) {
    @mkdir(K_PATH_CACHE,0777,true);
}

class GD_ServicesViewFormat_PDF extends GD_ServicesViewFormat implements ServicesFormatterInterface {

    protected $svg = null;
    protected $footer = null;

    public function __construct () {
        parent::__construct();
        $this->svg = preg_replace("/(<image)(.*?)(opacity=\".25\")(.*?)(<\/image>)/", "$1$2$4$5", $_REQUEST['svg']);
        $this->footer = $_REQUEST['footer'];
    }

    public function render ( $data ) {
        if ( isset($_REQUEST['cache']) ) {
            header('Cache-Control: no-transform,public,max-age=300,s-maxage=900');
            header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 3600));
        } else {
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private",false);
        }
        header('Content-Description: File Transfer');
        if (strpos(php_sapi_name(), 'cgi') === false) {
            header('Content-Type: application/force-download');
            header('Content-Type: application/octet-stream', false);
            header('Content-Type: application/download', false);
            header('Content-Type: application/pdf', false);
        } else {
            header('Content-Type: application/pdf');
        }
        header('Content-Disposition: attachment; filename="' . $this->filename . '.pdf"');
        header('Content-Transfer-Encoding: binary');

        $pdf = $this->buildPDF($data);
        $output = $pdf->Output($this->filename.'.pdf','S');

        if ( !isset($_SERVER['HTTP_ACCEPT_ENCODING']) || empty($_SERVER['HTTP_ACCEPT_ENCODING']) ) {
            // the content length may vary if the server is using compression
            header('Content-Length: '.strlen($output));
        }

        gd_get_session_messages(); // log and clear any messages
        return $output;
    }

    protected function buildPDF ( $data ) {

        $pdf = new GD_Export_TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator('GovDashboard');
        if ( isset($this->node->author->fullname) ) {
            $pdf->SetAuthor($this->node->author->fullname);
        } else if ( isset($this->node->author->name) ) {
            $pdf->SetAuthor($this->node->author->name);
        }
        $pdf->SetTitle($this->node->title);
        $pdf->SetSubject('');
        $pdf->SetKeywords('');

        // set default header data
        $pdf->SetHeaderData('',0,$this->node->title,'');

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        //set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        //set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        //set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // ---------------------------------------------------------

        $pdf->startPageGroup();
        // add a page
        $pdf->AddPage();

        $pdf->ImageSVG($file='@'.$this->svg, $x=15, $y=25, $w=1080, $h=540, $link='', $align='', $palign='', $border=0, $fitonpage=true);
        $pdf->writeHTMLCell(175, 540, 15, 125, $this->footer, 0, 0, false, true, '', true);

        // --------------------------

        $pdf->startPageGroup();
        $pdf->AddPage();

        $pdf->writeHTML($data, true, false, false, false, '');

        return $pdf;
    }

}

class GD_Export_TCPDF extends TCPDF {

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}
 
