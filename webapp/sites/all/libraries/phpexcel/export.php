<?php
error_reporting(E_ALL);

require_once 'Classes/PHPExcel.php';

$header = array('ID','Name','Title');

$data = array(
	array(100,'Jimmy Bob','Pooer'),
	array(101,'Chris Piss','Dooer'),
	array(102,'JoAnne Hippo','Mooer'),
	array(103,'Sarah Drizzle','Pooer'),
	array(110,'Johnny Shot','Dooer'),
	array(111,'Ginger Fart','Pooer'),
	array(115,'Flippy Flam','Mooer')
);

$objPHPExcel = new PHPExcel();
$objPHPExcel->getActiveSheet()->setTitle('List of Users');

$rowNumber = 1;
$col = 'A';
foreach ( $header as $heading ) {
	$objPHPExcel->getActiveSheet()->setCellValue($col.$rowNumber,$heading);
    $col++;
}

// Loop through the result set
$rowNumber = 2;
foreach ( $data as $row ) {
	$col = 'A';
    foreach ( $row as $cell ) {
		$objPHPExcel->getActiveSheet()->setCellValue($col.$rowNumber,$cell);
        $col++;
    }
    $rowNumber++;
}

// Freeze pane so that the heading line won't scroll
$objPHPExcel->getActiveSheet()->freezePane('A2');

// Save as an Excel BIFF (xls) file
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="userList.xls"');
header('Cache-Control: max-age=0');

$objWriter->save('php://output');
exit();
