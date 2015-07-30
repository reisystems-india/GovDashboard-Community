<?php
//============================================================+
// File name   : example_058.php
// Begin       : 2010-04-22
// Last Update : 2010-08-08
//
// Description : Example 058 for TCPDF class
//               SVG Image
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               Manor Coach House, Church Hill
//               Aldershot, Hants, GU12 4RQ
//               UK
//               www.tecnick.com
//               info@tecnick.com
//============================================================+

/**
 * Creates an example PDF TEST document using TCPDF
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: SVG Image
 * @author Nicola Asuni
 * @since 2010-05-02
 */

require_once('../config/lang/eng.php');
require_once('../tcpdf.php');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

    //Page header
    public function Header() {
        // Logo
        $image_file = K_PATH_IMAGES.'govdash.png';
        $this->Image($image_file, 10, 10, 50, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('helvetica', 'B', 20);
        // Title
        $this->Cell(0, 15, 'Simple Report', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

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

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Chris Edwards');
$pdf->SetTitle('Simple Report');
$pdf->SetSubject('');
$pdf->SetKeywords('');

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 058', PDF_HEADER_STRING);

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

//set some language-dependent strings
$pdf->setLanguageArray($l);

// ---------------------------------------------------------

// set font
$pdf->SetFont('helvetica', '', 10);

// add a page
$pdf->AddPage();

// NOTE: Uncomment the following line to rasterize SVG image using the ImageMagick library.
//$pdf->setRasterizeVectorImages(true);

$svg = <<<EOD
<svg xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.w3.org/2000/svg" version="1.1" width="419" height="269"><defs><clipPath id="highcharts-6"><rect rx="0" ry="0" fill="none" x="0" y="0" width="288" 
height="115" stroke-width="0.000001"></rect></clipPath></defs><rect rx="5" ry="5" fill="#FFFFFF" x="1" y="1" width="417" height="267" stroke-width="2" stroke="#eeeeee"></rect><text x="202" y="25" 
style="font-family:'lucida grande', 'lucida sans unicode', verdana, arial, helvetica, sans-serif;font-size:16px;color:#3e576f;fill:#3e576f;" text-anchor="middle" class="highcharts-title" ><tspan x="202">Simple 
Report</tspan></text><g class="highcharts-grid" ></g><g class="highcharts-grid" ><path d="M 106 155.5 L 394 155.5" fill="none" stroke="#C0C0C0" stroke-width="1" ></path><path d="M 106 117.5 L 394 117.5" fill="none" 
stroke="#C0C0C0" stroke-width="1" ></path><path d="M 106 79.5 L 394 79.5" fill="none" stroke="#C0C0C0" stroke-width="1" ></path><path d="M 106 40.5 L 394 40.5" fill="none" stroke="#C0C0C0" stroke-width="1" ></path></g><g 
class="highcharts-series-group" ><g class="highcharts-series" clip-path="url(#highcharts-6)" visibility="visible" transform="translate(106,40)"><rect rx="0" ry="0" fill="#FF0000" x="11" y="111" width="11" height="4" 
stroke-width="0.000001" stroke="#FFFFFF" r="0"></rect><rect rx="0" ry="0" fill="#FF0000" x="59" y="98" width="11" height="17" stroke-width="0.000001" stroke="#FFFFFF" r="0"></rect><rect rx="0" ry="0" fill="#FF0000" 
x="107" y="39" width="11" height="76" stroke-width="0.000001" stroke="#FFFFFF" r="0"></rect><rect rx="0" ry="0" fill="#FF0000" x="155" y="110" width="11" height="5" stroke-width="0.000001" stroke="#FFFFFF" 
r="0"></rect><rect rx="0" ry="0" fill="#FF0000" x="203" y="88" width="11" height="27" stroke-width="0.000001" stroke="#FFFFFF" r="0"></rect><rect rx="0" ry="0" fill="#FF0000" x="251" y="106" width="11" height="9" 
stroke-width="0.000001" stroke="#FFFFFF" r="0"></rect></g><g class="highcharts-series" clip-path="url(#highcharts-6)" visibility="visible" transform="translate(106,40)"><rect rx="0" ry="0" fill="#FF9900" x="25" y="113" 
width="11" height="2" stroke-width="0.000001" stroke="#FFFFFF" r="0"></rect><rect rx="0" ry="0" fill="#FF9900" x="73" y="98" width="11" height="17" stroke-width="0.000001" stroke="#FFFFFF" r="0"></rect><rect rx="0" ry="0" 
fill="#FF9900" x="121" y="81" width="11" height="34" stroke-width="0.000001" stroke="#FFFFFF" r="0"></rect><rect rx="0" ry="0" fill="#FF9900" x="169" y="112" width="11" height="3" stroke-width="0.000001" stroke="#FFFFFF" 
r="0"></rect><rect rx="0" ry="0" fill="#FF9900" x="217" y="110" width="11" height="5" stroke-width="0.000001" stroke="#FFFFFF" r="0"></rect><rect rx="0" ry="0" fill="#FF9900" x="265" y="106" width="11" height="9" 
stroke-width="0.000001" stroke="#FFFFFF" r="0"></rect></g></g><g class="highcharts-legend"  transform="translate(69,227)"><rect rx="5" ry="5" fill="none" x="0.5" y="0.5" width="266" height="26" stroke-width="1" 
stroke="#909090" visibility="visible"></rect><text x="30" y="18" style="font-family:'lucida grande', 'lucida sans unicode', verdana, arial, helvetica, sans-serif;font-size:14px;cursor:pointer;color:#3e576f;fill:#3e576f;" 
><tspan x="30">Amount (MAX)</tspan></text><text x="154" y="18" style="font-family:'lucida grande', 'lucida sans unicode', verdana, arial, helvetica, sans-serif;font-size:14px;cursor:pointer;color:#3e576f;fill:#3e576f;" 
><tspan x="154">Amount (AVG)</tspan></text><rect rx="2" ry="2" fill="#FF0000" x="9" y="7" width="16" height="12" stroke-width="0.000001"  stroke="#FFFFFF" r="0"></rect><rect rx="2" ry="2" fill="#FF9900" x="133" y="7" 
width="16" height="12" stroke-width="0.000001"  stroke="#FFFFFF" r="0"></rect></g><g class="highcharts-axis" ><text x="130" y="169.23044737829952" style="font-family:'lucida grande', 'lucida sans unicode', verdana, arial, 
helvetica, sans-serif;font-size:14px;color:#666;line-height:14px;fill:#666;" text-anchor="end" transform="rotate(-45 130 169)"><tspan x="130">Glo</tspan></text><text x="178" y="169.23044737829952" style="font-family:'lucida 
grande', 'lucida sans unicode', verdana, arial, helvetica, sans-serif;font-size:14px;color:#666;line-height:14px;fill:#666;" text-anchor="end" transform="rotate(-45 178 169)"><tspan x="178">Flo</tspan></text><text x="226" 
y="169.23044737829952" style="font-family:'lucida grande', 'lucida sans unicode', verdana, arial, helvetica, sans-serif;font-size:14px;color:#666;line-height:14px;fill:#666;" text-anchor="end" transform="rotate(-45 226 
169)"><tspan x="226">Jim</tspan></text><text x="274" y="169.23044737829952" style="font-family:'lucida grande', 'lucida sans unicode', verdana, arial, helvetica, 
sans-serif;font-size:14px;color:#666;line-height:14px;fill:#666;" text-anchor="end" transform="rotate(-45 274 169)"><tspan x="274">Moe</tspan></text><text x="322" y="169.23044737829952" style="font-family:'lucida grande', 
'lucida sans unicode', verdana, arial, helvetica, sans-serif;font-size:14px;color:#666;line-height:14px;fill:#666;" text-anchor="end" transform="rotate(-45 322 169)"><tspan x="322">Joe</tspan></text><text x="370" 
y="169.23044737829952" style="font-family:'lucida grande', 'lucida sans unicode', verdana, arial, helvetica, sans-serif;font-size:14px;color:#666;line-height:14px;fill:#666;" text-anchor="end" transform="rotate(-45 370 
169)"><tspan x="370">Hello</tspan></text><path d="M 154.5 155.23044737829952 L 154.5 160.23044737829952" fill="none" stroke="#C0D0E0" stroke-width="1"></path><path d="M 202.5 155.23044737829952 L 202.5 160.23044737829952" 
fill="none" stroke="#C0D0E0" stroke-width="1"></path><path d="M 250.5 155.23044737829952 L 250.5 160.23044737829952" fill="none" stroke="#C0D0E0" stroke-width="1"></path><path d="M 298.5 155.23044737829952 L 298.5 
160.23044737829952" fill="none" stroke="#C0D0E0" stroke-width="1"></path><path d="M 346.5 155.23044737829952 L 346.5 160.23044737829952" fill="none" stroke="#C0D0E0" stroke-width="1"></path><path d="M 394.5 
155.23044737829952 L 394.5 160.23044737829952" fill="none" stroke="#C0D0E0" stroke-width="1"></path></g><text x="250" y="212.76955262170048" style="font-family:'lucida grande', 'lucida sans unicode', verdana, arial, 
helvetica, sans-serif;font-size:16px;color:#6d869f;font-weight:bold;fill:#6d869f;"  text-anchor="middle"><tspan x="250">Name</tspan></text><g class="highcharts-axis" ><text x="98" y="159.3304473782995" 
style="font-family:'lucida grande', 'lucida sans unicode', verdana, arial, helvetica, sans-serif;font-size:14px;width:124px;color:#666;line-height:14px;fill:#666;" text-anchor="end"><tspan x="98">0.00</tspan></text><text 
x="98" y="120.99711404496621" style="font-family:'lucida grande', 'lucida sans unicode', verdana, arial, helvetica, sans-serif;font-size:14px;width:124px;color:#666;line-height:14px;fill:#666;" text-anchor="end"><tspan 
x="98">500.00</tspan></text><text x="98" y="82.66378071163287" style="font-family:'lucida grande', 'lucida sans unicode', verdana, arial, helvetica, 
sans-serif;font-size:14px;width:124px;color:#666;line-height:14px;fill:#666;" text-anchor="end"><tspan x="98">1000.00</tspan></text><text x="98" y="44.33044737829953" style="font-family:'lucida grande', 'lucida sans 
unicode', verdana, arial, helvetica, sans-serif;font-size:14px;width:124px;color:#666;line-height:14px;fill:#666;" text-anchor="end"><tspan x="98">1500.00</tspan></text></g><text x="30" y="97.5" style="font-family:'lucida 
grande', 'lucida sans unicode', verdana, arial, helvetica, sans-serif;font-size:16px;color:#6d869f;font-weight:bold;fill:#6d869f;"  transform="rotate(270 30 97)" text-anchor="middle"><tspan x="30">Amount 
(MAX)</tspan></text><path d="M 106 155.5 L 394 155.5" fill="none" stroke="#C0D0E0" stroke-width="1" ></path><g class="highcharts-tooltip"  visibility="hidden"><rect rx="5" ry="5" fill="none" x="2" y="2" width="0" height="0" 
stroke-width="5" fill-opacity="0.85"  stroke="rgb(0, 0, 0)" stroke-opacity="0.05" transform="translate(1,1)"></rect><rect rx="5" ry="5" fill="none" x="2" y="2" width="0" height="0" stroke-width="3" fill-opacity="0.85"  
stroke="rgb(0, 0, 0)" stroke-opacity="0.1" transform="translate(1,1)"></rect><rect rx="5" ry="5" fill="none" x="2" y="2" width="0" height="0" stroke-width="1" fill-opacity="0.85"  stroke="rgb(0, 0, 0)" 
stroke-opacity="0.15000000000000002" transform="translate(1,1)"></rect><rect rx="5" ry="5" fill="rgb(255,255,255)" x="2" y="2" width="0" height="0" stroke-width="2" fill-opacity="0.85"></rect><text x="2" y="14" 
style="font-family:'lucida grande', 'lucida sans unicode', verdana, arial, helvetica, sans-serif;font-size:12px;color:#333333;padding:0;white-space:nowrap;fill:#333333;" ><tspan x="2"> </tspan></text></g><g 
class="highcharts-tracker"  transform="translate(106,40)"><rect rx="0" ry="0" fill="rgb(192,192,192)" x="11" y="111" width="11" height="4" stroke-width="0.000001"  fill-opacity="0.000001" visibility="visible"  
style="cursor:pointer;"></rect><rect rx="0" ry="0" fill="rgb(192,192,192)" x="59" y="98" width="11" height="17" stroke-width="0.000001"  fill-opacity="0.000001" visibility="visible"  style="cursor:pointer;"></rect><rect 
rx="0" ry="0" fill="rgb(192,192,192)" x="107" y="39" width="11" height="76" stroke-width="0.000001"  fill-opacity="0.000001" visibility="visible"  style="cursor:pointer;"></rect><rect rx="0" ry="0" fill="rgb(192,192,192)" 
x="155" y="110" width="11" height="5" stroke-width="0.000001"  fill-opacity="0.000001" visibility="visible"  style="cursor:pointer;"></rect><rect rx="0" ry="0" fill="rgb(192,192,192)" x="203" y="88" width="11" height="27" 
stroke-width="0.000001"  fill-opacity="0.000001" visibility="visible"  style="cursor:pointer;"></rect><rect rx="0" ry="0" fill="rgb(192,192,192)" x="251" y="106" width="11" height="9" stroke-width="0.000001"  
fill-opacity="0.000001" visibility="visible"  style="cursor:pointer;"></rect><rect rx="0" ry="0" fill="rgb(192,192,192)" x="25" y="113" width="11" height="2" stroke-width="0.000001"  fill-opacity="0.000001" 
visibility="visible"  style="cursor:pointer;"></rect><rect rx="0" ry="0" fill="rgb(192,192,192)" x="73" y="98" width="11" height="17" stroke-width="0.000001"  fill-opacity="0.000001" visibility="visible"  
style="cursor:pointer;"></rect><rect rx="0" ry="0" fill="rgb(192,192,192)" x="121" y="81" width="11" height="34" stroke-width="0.000001"  fill-opacity="0.000001" visibility="visible"  style="cursor:pointer;"></rect><rect 
rx="0" ry="0" fill="rgb(192,192,192)" x="169" y="112" width="11" height="3" stroke-width="0.000001"  fill-opacity="0.000001" visibility="visible"  style="cursor:pointer;"></rect><rect rx="0" ry="0" fill="rgb(192,192,192)" 
x="217" y="110" width="11" height="5" stroke-width="0.000001"  fill-opacity="0.000001" visibility="visible"  style="cursor:pointer;"></rect><rect rx="0" ry="0" fill="rgb(192,192,192)" x="265" y="106" width="11" height="9" 
stroke-width="0.000001"  fill-opacity="0.000001" visibility="visible"  style="cursor:pointer;"></rect></g></svg>
EOD;

$pdf->ImageSVG($file='@'.$svg, $x=10, $y=25, $w=200, $h=100, $link='', $align='', $palign='', $border=0, $fitonpage=false);

$tbl = <<<EOD
<table report="9080" id="dataTable_9080" class="simpleTable sticky-enabled" width="100%" border="1" cellspacing="0" cellpadding="0">
 <thead><tr><th><div class="advTableHeaderVal" column="attr:c_name.c_name.value">Name</div></th><th><div class="advTableHeaderVal" column="measure:c_amount__avg">Amount (AVG)</div></th> </tr></thead>
<tbody>
 <tr class="odd"><td>Glo</td><td>35</td> </tr>
 <tr class="even"><td>Flo</td><td>222</td> </tr>
 <tr class="odd"><td>Jim</td><td>452</td> </tr>
 <tr class="even"><td>Moe</td><td>49</td> </tr>
 <tr class="odd"><td>Joe</td><td>68</td> </tr>
 <tr class="even"><td>Hello</td><td>123</td> </tr>
</tbody>
</table>
EOD;

$pdf->SetY(150);
$pdf->writeHTML($tbl, true, false, false, false, '');



// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('example_058.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
