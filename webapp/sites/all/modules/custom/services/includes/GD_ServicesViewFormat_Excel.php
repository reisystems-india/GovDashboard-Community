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


require_once DRUPAL_ROOT.'/sites/all/libraries/phpexcel/Classes/PHPExcel.php';

class GD_ServicesViewFormat_Excel extends GD_ServicesViewFormat implements ServicesFormatterInterface {

    public function render ( $data ) {
        ob_start();

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
        header("Content-Type: application/octet-stream");
        header("Content-Transfer-Encoding: binary");
        header('Content-Disposition: attachment; filename="' . $this->filename . '.xls"');

        $objPHPExcel = new PHPExcel();

        $rowNumber = 1;
        if ( is_array($data) ) {
            foreach ( $data as $row ) {
                $col = 'A';
                foreach ( $row as $cell ) {
                    $objPHPExcel->getActiveSheet()->setCellValue($col.$rowNumber,$cell);
                    $col++;
                }
                $rowNumber++;
            }
        } else {
            LogHelper::log_error('Expecting array of data for export');
            LogHelper::log_error($data);
        }

        // Save as an Excel BIFF (xls) file
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');

        gd_get_session_messages(); // log and clear any messages

        $output = ob_get_clean();

        if ( !isset($_SERVER['HTTP_ACCEPT_ENCODING']) || empty($_SERVER['HTTP_ACCEPT_ENCODING']) ) {
            // the content length may vary if the server is using compression
            header('Content-Length: '.strlen($output));
        }

        return $output;
    }

}