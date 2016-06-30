<?php

ob_start();
	session_start();
	$_SESSION['theme']='blue';
	$_SESSION['path'] = '..';
	
	require_once $_SESSION['path'].'/lib/classes/Conf.php';
	require_once $_SESSION['path'].'/lib/classes/MySQLClass.php';
	require_once $_SESSION['path'].'/lib/classes/fpdf/fpdf.php';

	
	$month= isset($_GET['month']) ? strtoupper(mysql_escape_string(trim(strip_tags($_GET['month'])))) : '01';
	$office= isset($_GET['office']) ? strtoupper(mysql_escape_string(trim(strip_tags($_GET['office'])))) : '';
	$sort= isset($_GET['sortby']) ? strtoupper(mysql_escape_string(trim(strip_tags($_GET['sortby'])))) : '';
	
//Start PDF Builder
	$pdf=new FPDF();
	
	
	
	

	if(($month!='') && ($office=='')) {
	
	$Config = new Conf();
	$MySQLi = new MySQLClass($Config);
	
	$bday = array('', 'January','February','March','April','May','June','July','August','September','October','November','December');

 //  $pdf->AddPage();
   $pdf->Image('png/long.png', 3, 3, 205, 295, 'PNG');
   
	$pdf->SetFont('Arial','',10);
	$pdf->SetTextColor(0,0,0);
	
		if(($sort =='BDAY')  || ($sort == '')){
		$sortby = 'ORDER BY `EmpBirthMonth` , `EmpBirthDay` ,  `EmpBirthYear`, `EmpLName` DESC';
	//	$sortby = 'ORDER BY  tblempservicerecords.`SrecFromYear`DESC, tblempservicerecords.`SrecFromMonth` DESC, tblempservicerecords.`SrecFromDay` DESC';
		}else if ($sort == 'NAME'){
		$sortby = 'ORDER BY `EmpLName` , `EmpFName`, `EmpMName` ';
		}
	/*
	$result = $MySQLi -> sqlQuery("SELECT  `tblemppersonalinfo`.`EmpID`, MAX(tblempservicerecords.SrecFromYear) AS 'RYear', tblempservicerecords.SrecFromDay, tblempservicerecords.SrecFromMonth, tblsuboffices.SubOffID, tblsuboffices.SubOffCode, tblempservicerecords.MotherOfficeID, CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName  ,  `tblemppersonalinfo`.`EmpBirthDay` , `tblemppersonalinfo`.`EmpBirthMonth`, `tblemppersonalinfo`.`EmpBirthYear` FROM (tblsuboffices INNER JOIN tblempservicerecords ON tblsuboffices.SubOffID = tblempservicerecords.MotherOfficeID) INNER JOIN `tblemppersonalinfo` ON tblempservicerecords.EmpID = `tblemppersonalinfo`.EmpID where `tblemppersonalinfo`.EmpBirthMonth  = " . $month . " GROUP BY tblempservicerecords.EmpID ". $sortby ." ;"); unset($sql);
	*/
	
	$result = $MySQLi -> sqlQuery("SELECT  `tblemppersonalinfo`.`EmpID`, MAX(CONCAT_WS(', ',tblempservicerecords.SrecFromYear, CONCAT_WS(' ',tblempservicerecords.SrecFromMonth, CONCAT_WS(' ', tblempservicerecords.SrecFromDay, '')))) AS RYear, tblsuboffices.SubOffID, tblsuboffices.SubOffCode, tblempservicerecords.MotherOfficeID, CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName  ,  `tblemppersonalinfo`.`EmpBirthDay` , `tblemppersonalinfo`.`EmpBirthMonth`, `tblemppersonalinfo`.`EmpBirthYear` FROM (tblsuboffices INNER JOIN tblempservicerecords ON tblsuboffices.SubOffID = tblempservicerecords.MotherOfficeID) INNER JOIN `tblemppersonalinfo` ON tblempservicerecords.EmpID = `tblemppersonalinfo`.EmpID where `tblemppersonalinfo`.EmpBirthMonth  = " . $month . " GROUP BY tblempservicerecords.EmpID ". $sortby ." ;"); unset($sql);
	
	/*
	
	$result = $MySQLi -> sqlQuery("SELECT * FROM `tblemppersonalinfo` where EmpBirthMonth  = " . $month . "  ". $sortby ." ;"); unset($sql);
	*/
	
	/*
	$result = $MySQLi -> sqlQuery("SELECT EmpID, SrecFromMonth, SrecFromDay, MAX(SrecFromYear) AS 'RecentYear' FROM tblempservicerecords GROUP BY EmpID"); unset($sql);
	
	*/
	
   $num = 1;
  while ($row = mysql_fetch_array($result)) {

   if ( ($num-1) % 45 == 0) {
		$pdf->AddPage();
       $pdf->Image('png/long.png', 3, 3, 205, 295, 'PNG');
	  $pdf->Ln(25);

		 $pdf->Cell(60);
		 $pdf->Cell(87, 5, 'Birthday Celebrants of '. $bday[(int)$month], 0, 1,  'C');
		 $pdf->Ln(5);
		//$pdf->Ln();		
		$pdf->Cell(7, 5, '#', 1, 0, 'C');
		$pdf->Cell(80, 5, 'Name', 1, 0, 'C');
		$pdf->Cell(50, 5, 'Office', 1, 0, 'C');
		$pdf->Cell(50, 5, 'Birthday', 1, 0, 'C');
		
		$pdf->Ln();
	
		$pdf->SetFont('Arial','',10);
		$pdf->SetTextColor(0,0,0);
		 }
  
		$pdf->Cell(7, 5,  $num, 1, 0, 'C');
      	$pdf->Cell(80, 5,  $row['EmpName'], 1, 0, 'L');
		$pdf->Cell(50, 5, $row['SubOffCode'], 1, 0, 'C');
		$pdf->Cell(50, 5, $row['EmpBirthMonth'] . " / ". $row['EmpBirthDay'] . " / " . $row['EmpBirthYear'] , 1, 0, 'C');
		//	$pdf->Cell(50, 5, $row['SrecFromMonth'] . " / ". $row['SrecFromDay'] . " / " . $row['RYear'] , 1, 0, 'C');
		//$pdf->Cell(50, 5, $row['RYear'] , 1, 0, 'C');
		 $pdf->Ln();
		 $num++;	
		 
		 }
		 
  //} 
 
  } else  if(($month!='') && ($office!='')) {
	
	$Config = new Conf();
	$MySQLi = new MySQLClass($Config);
	
	$bday = array('', 'January','February','March','April','May','June','July','August','September','October','November','December');

 //  $pdf->AddPage();
   $pdf->Image('png/long.png', 3, 3, 205, 295, 'PNG');
   
	$pdf->SetFont('Arial','',10);
	$pdf->SetTextColor(0,0,0);
	
		if(($sort =='BDAY')  || ($sort == '')){
		$sortby = 'ORDER BY `EmpBirthMonth` , `EmpBirthDay` ,  `EmpBirthYear`, `EmpLName` DESC';
	//	$sortby = 'ORDER BY  tblempservicerecords.`SrecFromYear`DESC, tblempservicerecords.`SrecFromMonth` DESC, tblempservicerecords.`SrecFromDay` DESC';
		}else if ($sort == 'NAME'){
		$sortby = 'ORDER BY `EmpLName` , `EmpFName`, `EmpMName` ';
		}
	/*
	$result = $MySQLi -> sqlQuery("SELECT  `tblemppersonalinfo`.`EmpID`, MAX(tblempservicerecords.SrecFromYear) AS 'RYear', tblempservicerecords.SrecFromDay, tblempservicerecords.SrecFromMonth, tblsuboffices.SubOffID, tblsuboffices.SubOffCode, tblempservicerecords.MotherOfficeID, CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName  ,  `tblemppersonalinfo`.`EmpBirthDay` , `tblemppersonalinfo`.`EmpBirthMonth`, `tblemppersonalinfo`.`EmpBirthYear` FROM (tblsuboffices INNER JOIN tblempservicerecords ON tblsuboffices.SubOffID = tblempservicerecords.MotherOfficeID) INNER JOIN `tblemppersonalinfo` ON tblempservicerecords.EmpID = `tblemppersonalinfo`.EmpID where `tblemppersonalinfo`.EmpBirthMonth  = " . $month . " GROUP BY tblempservicerecords.EmpID ". $sortby ." ;"); unset($sql);
	*/
	
	$result = $MySQLi -> sqlQuery("SELECT  `tblemppersonalinfo`.`EmpID`, MAX(CONCAT_WS(', ',tblempservicerecords.SrecFromYear, CONCAT_WS(' ',tblempservicerecords.SrecFromMonth, CONCAT_WS(' ', tblempservicerecords.SrecFromDay, '')))) AS RYear, tblsuboffices.SubOffID, tblsuboffices.SubOffCode, tblempservicerecords.MotherOfficeID, CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName  ,  `tblemppersonalinfo`.`EmpBirthDay` , `tblemppersonalinfo`.`EmpBirthMonth`, `tblemppersonalinfo`.`EmpBirthYear` FROM (tblsuboffices INNER JOIN tblempservicerecords ON tblsuboffices.SubOffID = tblempservicerecords.MotherOfficeID) INNER JOIN `tblemppersonalinfo` ON tblempservicerecords.EmpID = `tblemppersonalinfo`.EmpID where `tblemppersonalinfo`.EmpBirthMonth  = " . $month . " and tblsuboffices.SubOffCode = '". $office ."' GROUP BY tblempservicerecords.EmpID ". $sort ." ;"); unset($sql);
	
	/*
	
	$result = $MySQLi -> sqlQuery("SELECT * FROM `tblemppersonalinfo` where EmpBirthMonth  = " . $month . "  ". $sortby ." ;"); unset($sql);
	*/
	
	/*
	$result = $MySQLi -> sqlQuery("SELECT EmpID, SrecFromMonth, SrecFromDay, MAX(SrecFromYear) AS 'RecentYear' FROM tblempservicerecords GROUP BY EmpID"); unset($sql);
	
	*/
	
   $num = 1;
  while ($row = mysql_fetch_array($result)) {

   if ( ($num-1) % 45 == 0) {
		$pdf->AddPage();
       $pdf->Image('png/long.png', 3, 3, 205, 295, 'PNG');
	  $pdf->Ln(25);

		 $pdf->Cell(60);
		 $pdf->Cell(87, 5, $bday[(int)$month] .  ' Birthday Celebrants of '. $office , 0, 1,  'C');
		 $pdf->Ln(5);
		//$pdf->Ln();		
		$pdf->Cell(7, 5, '#', 1, 0, 'C');
		$pdf->Cell(90, 5, 'Name', 1, 0, 'C');
	//	$pdf->Cell(50, 5, 'Office', 1, 0, 'C');
		$pdf->Cell(90, 5, 'Birthday', 1, 0, 'C');
		
		$pdf->Ln();
	
		$pdf->SetFont('Arial','',10);
		$pdf->SetTextColor(0,0,0);
		 }
  
		$pdf->Cell(7, 5,  $num, 1, 0, 'C');
      	$pdf->Cell(90, 5,  $row['EmpName'], 1, 0, 'L');
	//	$pdf->Cell(50, 5, $row['SubOffCode'], 1, 0, 'C');
		$pdf->Cell(90, 5, $row['EmpBirthMonth'] . " / ". $row['EmpBirthDay'] . " / " . $row['EmpBirthYear'] , 1, 0, 'C');
		//	$pdf->Cell(50, 5, $row['SrecFromMonth'] . " / ". $row['SrecFromDay'] . " / " . $row['RYear'] , 1, 0, 'C');
		//$pdf->Cell(50, 5, $row['RYear'] , 1, 0, 'C');
		 $pdf->Ln();
		 $num++;	
		 
		 }
		 
  //} 
 
  } 
 
  

  $pdf->Output();
  
  ob_end_flush();

?>
