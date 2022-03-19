<?php


/** Error reporting */
error_reporting(0);
ini_set('display_errors', FALSE);
ini_set('display_startup_errors', FALSE);


/**
 * PHPExcel
 *
 * Copyright (c) 2006 - 2015 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2015 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    ##VERSION##, ##DATE##
 */


require_once("./inc/connect_pdo.php");

function badCharacters2 ($thestring) {
		
	// First, replace UTF-8 characters.
	$thestring = str_replace(
	array("\xe2\x80\x98", "\xe2\x80\x99", "\xe2\x80\x9c", "\xe2\x80\x9d", "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x80\xa6"),
	array("'", "'", '"', '"', '-', '-', '...'),
	$thestring);
	
	// Next, replace their Windows-1252 equivalents.
	$thestring = str_replace(
	array(chr(145), chr(146), chr(147), chr(148), chr(150), chr(151), chr(133)),
	array("'", "'", '"', '"', '-', '-', '...'),
	$thestring);
		
	return $thestring;
}


// Set timezone
date_default_timezone_set('America/Toronto');

// end script if being run in a command line
if (PHP_SAPI == 'cli'){   
	die('This example should only be run from a Web Browser');
}

/** Include PHPExcel */
require_once("./classes/PHPExcel.php");


//title variable for use in various places.
$title = "Borrowers Data";

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()
	->setCreator("Jaideep")
	->setLastModifiedBy("Jaideep")
	->setTitle("Office 2007 XLSX $title")
	->setSubject("Office 2007 XLSX $title")
	->setDescription("A glance at the borrower's database")
	->setKeywords("office 2007 openxml php $title")
	->setCategory("$title");


// Define variable to track current Row in Excel
$line = 1;

// Add some data
$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue("A$line", "$title");

//Add +1 to the $line, which will create a new row in Excel
++$line;

//Assign the Headings to each column
$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue("A$line", "BORROWER ID")
	->setCellValue("B$line", "LAST NAME")
	->setCellValue("C$line", "FIRST NAME")
	->setCellValue("D$line", "EMAIL DC")
	->setCellValue("E$line", "EMAIL OTHER")
	->setCellValue("F$line", "PROGRAM ID")
	->setCellValue("G$line", "PROGRAM YEAR")
	->setCellValue("H$line", "PHONE NUMBER")
	->setCellValue("I$line", "NOTES")
	->setCellValue("J$line", "AGREEMENT SIGNED")
	->setCellValue("K$line", "STRIKES")
	->setCellValue("L$line", "IMAGES");



// Query the table for all wanted information

$query = "SELECT borrower_id, name_last, name_first, email_dc, email_other, program_id, 
          program_year, phone, notes, agreement_signed, strikes, image
		  FROM borrower
		  ORDER BY name_last";

		  

// Loop through results
foreach($dbo->query($query) as $row) {

    // Store current data in variables to place in Excel row
	$borrower_id = stripslashes($row[0]);
	$name_last = stripslashes($row[1]);
	$name_first = stripslashes($row[2]);
	$email_dc = stripslashes($row[3]);
	$email_other = stripslashes($row[4]);
	$program_id = stripslashes($row[5]);
	$program_year = stripslashes($row[6]);
	$phone = stripslashes($row[7]);
	$notes = stripslashes($row[8]);
	$agreement_signed = stripslashes($row[9]);
	$strikes = stripslashes($row[10]);
	$image = stripslashes($row[11]);
    
    // Clean up strings
	$name_last = badCharacters2($name_last);
	$name_first = badCharacters2($name_first);
	$notes = badCharacters2($notes);
	$email_other = badCharacters2($email_other);
	$email_dc= badCharacters2($email_dc);
	$image = badCharacters2($image);


	// if $image store the full path
	if ($image){
		$image = "images/" . $borrower_id . "/" . $image;
	}

    //Add +1 to the $line, which will create a new row in Excel
 	++$line;
    
    // Place the database info into the current row ($line)
    $objPHPExcel->setActiveSheetIndex(0)
		->setCellValue("A$line", "$borrower_id")
		->setCellValue("B$line", "$name_last")
		->setCellValue("C$line", "$name_first")
		->setCellValue("D$line", "$email_dc")
		->setCellValue("E$line", "$email_other")
		->setCellValue("F$line", "$program_id")
		->setCellValue("G$line", "$program_year")
		->setCellValue("H$line", "$phone")
		->setCellValue("I$line", "$notes")
		->setCellValue("J$line", "$agreement_signed")
		->setCellValue("K$line", "$strikes")
		->setCellValue("L$line", "$image");
    
	// Set the Biography column to wrap text
	$objPHPExcel->getActiveSheet()->getStyle("I$line")->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getStyle("L$line")->getAlignment()->setWrapText(true);
	// Align column text to the top.     
	$objPHPExcel->getActiveSheet()->getStyle("A$line")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
	$objPHPExcel->getActiveSheet()->getStyle("B$line")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
	$objPHPExcel->getActiveSheet()->getStyle("C$line")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
	$objPHPExcel->getActiveSheet()->getStyle("D$line")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
	$objPHPExcel->getActiveSheet()->getStyle("E$line")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
	$objPHPExcel->getActiveSheet()->getStyle("F$line")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
	$objPHPExcel->getActiveSheet()->getStyle("G$line")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
	$objPHPExcel->getActiveSheet()->getStyle("H$line")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
	$objPHPExcel->getActiveSheet()->getStyle("I$line")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
	$objPHPExcel->getActiveSheet()->getStyle("J$line")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
	$objPHPExcel->getActiveSheet()->getStyle("K$line")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
	$objPHPExcel->getActiveSheet()->getStyle("L$line")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

}// end foreach loop 

// Define the widths of the columns
$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(30);
$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(30);
$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth(50);
$objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension("L")->setWidth(30);

// Set the title of the Sheet in Excel
$objPHPExcel->getActiveSheet()->setTitle("$title");


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Define a date that will be added to the file name
$addDate = date('d-M-Y_H_i_s');



// Redirect output to a client�s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$title.'_'.$addDate.'.xlsx"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
ob_end_clean();
$objWriter->save('php://output');


exit;



?>

