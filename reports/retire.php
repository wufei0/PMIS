<?php

ob_start();
	session_start();
	$_SESSION['theme']='blue';
	$_SESSION['path'] = '..';
	
	require_once $_SESSION['path'].'/lib/classes/Conf.php';
	require_once $_SESSION['path'].'/lib/classes/MySQLClass.php';
	require_once $_SESSION['path'].'/lib/classes/fpdf/fpdf.php';

	
	$date = date("Y.m");

	
	$office= isset($_GET['office']) ? strtoupper(mysql_escape_string(trim(strip_tags($_GET['office'])))) : '';
	$sort= isset($_GET['sortby']) ? strtoupper(mysql_escape_string(trim(strip_tags($_GET['sortby'])))) : '';
	
	
	

//Start PDF Builder
	$pdf=new FPDF();
	$pdf->AliasNbPages();
	
		if(($sort == 'NAME')  || ($sort == '')){
		$sortby = 'ORDER BY `EmpLName` ASC, `EmpFName` ASC, `EmpMName` ASC';
	//	$sortby = 'ORDER BY  tblempservicerecords.`SrecFromYear`DESC, tblempservicerecords.`SrecFromMonth` DESC, tblempservicerecords.`SrecFromDay` DESC';
		}else if ($sort == 'ID'){
		$sortby = 'ORDER BY `EmpID` ASC';
		}
	

	if ($office=='') {
	
	$Config = new Conf();
	$MySQLi = new MySQLClass($Config);

 //  $pdf->AddPage();
   $pdf->Image('png/long.png', 3, 3, 205, 295, 'PNG');
   
	$pdf->SetFont('Arial','',10);
	$pdf->SetTextColor(0,0,0);
	
	
	
	$result = $MySQLi -> sqlQuery("SELECT  (YEAR(CURDATE())-`EmpBirthYear`)-(RIGHT(CURDATE(),5)<RIGHT(CONCAT_WS('-',`EmpBirthYear`,`EmpBirthMonth`,`EmpBirthDay`),5)), `tblemppersonalinfo`.`EmpID`, tblempservicerecords.PosID, MAX(CONCAT_WS(', ',tblempservicerecords.SrecFromYear, CONCAT_WS(' ',tblempservicerecords.SrecFromMonth, CONCAT_WS(' ', tblempservicerecords.SrecFromDay, '')))) AS RYear,  tblempservicerecords.MotherOfficeID, CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName  ,  `tblemppersonalinfo`.`EmpBirthDay` , `tblemppersonalinfo`.`EmpBirthMonth`, `tblemppersonalinfo`.`EmpBirthYear` FROM  tblempservicerecords INNER JOIN `tblemppersonalinfo` ON tblempservicerecords.EmpID = `tblemppersonalinfo`.EmpID  WHERE `tblemppersonalinfo`.EmpBirthYear = ". (date("Y") - 65)." GROUP BY tblempservicerecords.EmpID ". $sortby ." ;"); unset($sql);
	
	//where (YEAR(CURDATE())-`EmpBirthYear`)-(RIGHT(CURDATE(),5)<RIGHT(CONCAT_WS('-',`EmpBirthYear`,`EmpBirthMonth`,`EmpBirthDay`),5)) =  65
   $num = 1;
  while ($row = mysql_fetch_array($result)) {
  $offices=mysql_fetch_array($MySQLi -> sqlQuery("SELECT `SubOffCode`, `SubOffID` FROM `tblsuboffices` WHERE `SubOffID`='".$row['MotherOfficeID']."';")); 
  $positions=mysql_fetch_array($MySQLi -> sqlQuery("SELECT `PosID`, `PosDesc` FROM `tblpositions` WHERE `PosID`='".$row['PosID']."';")); 

   if ( ($num-1) % 43 == 0) {
   
   
   
	$pdf->AddPage();
		
    $pdf->Image('png/long.png', 3, 3, 205, 295, 'PNG');
	$pdf->Ln(25);
     $pdf->Cell(0,10,date("Y/m/d"),0,0,'L');
		$pdf->Cell(0,10,'Page '.$pdf->PageNo().' of {nb}',0,1,'R');
		 $pdf->Cell(60);
		 $pdf->Cell(87, 5, 'Retired Employees for '. date("Y"), 0, 1,  'C');
		 $pdf->Ln(5);
		//$pdf->Ln();		
		$pdf->Cell(7, 5, '#', 1, 0, 'C');
		$pdf->Cell(20, 5, 'ID', 1, 0, 'C');
		$pdf->Cell(60, 5, 'Name', 1, 0, 'C');
		$pdf->Cell(30, 5, 'Office', 1, 0, 'C');
		$pdf->Cell(72, 5, 'Position', 1, 0, 'C');
	
		
		$pdf->Ln();
	
		$pdf->SetFont('Arial','',10);
		$pdf->SetTextColor(0,0,0);
		 }
  
		
		$pdf->Cell(7, 5,  $num, 1, 0, 'C');
		$pdf->Cell(20, 5, $row['EmpID'], 1, 0, 'C');
		$pdf->Cell(60, 5, $row['EmpName'], 1, 0, 'C');
		$pdf->Cell(30, 5, $offices['SubOffCode'], 1, 0, 'C');
		$pdf->Cell(72, 5, $positions['PosDesc'], 1, 0, 'C');
	//	$pdf->Cell(12, 5, 2012 - $row['EmpBirthYear'], 1, 0, 'C');
		

		 $pdf->Ln();
		 $num++;	
	
		 
		 } 
  
  
  } else if ($office!=''){
  $Config = new Conf();
	$MySQLi = new MySQLClass($Config);

 //  $pdf->AddPage();
   $pdf->Image('png/long.png', 3, 3, 205, 295, 'PNG');
   
	$pdf->SetFont('Arial','',10);
	$pdf->SetTextColor(0,0,0);
	
	
	$result = $MySQLi -> sqlQuery("SELECT  (YEAR(CURDATE())-`EmpBirthYear`)-(RIGHT(CURDATE(),5)<RIGHT(CONCAT_WS('-',`EmpBirthYear`,`EmpBirthMonth`,`EmpBirthDay`),5)), `tblemppersonalinfo`.`EmpID`,  tblsuboffices.SubOffID, tblsuboffices.SubOffCode, tblempservicerecords.MotherOfficeID, CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName, tblempservicerecords.PosID  FROM (tblsuboffices INNER JOIN tblempservicerecords ON tblsuboffices.SubOffID = tblempservicerecords.MotherOfficeID) INNER JOIN `tblemppersonalinfo` ON tblempservicerecords.EmpID = `tblemppersonalinfo`.EmpID WHERE `tblemppersonalinfo`.EmpBirthYear = ". (date("Y") - 65)." and tblsuboffices.SubOffCode = '". $office ."' GROUP BY tblempservicerecords.EmpID ". $sortby ." ;"); unset($sql);
	
	
   $num = 1;
  while ($row = mysql_fetch_array($result)) {
  $offices=mysql_fetch_array($MySQLi -> sqlQuery("SELECT `SubOffCode`, `SubOffID` FROM `tblsuboffices` WHERE `SubOffID`='".$row['MotherOfficeID']."';")); 
  $positions=mysql_fetch_array($MySQLi -> sqlQuery("SELECT `PosID`, `PosDesc` FROM `tblpositions` WHERE `PosID`='".$row['PosID']."';")); 

   if ( ($num-1) % 43 == 0) {
   
   
   
	$pdf->AddPage();
		
    $pdf->Image('png/long.png', 3, 3, 205, 295, 'PNG');
	$pdf->Ln(25);
     $pdf->Cell(0,10,date("Y/m/d"),0,0,'L');
		$pdf->Cell(0,10,'Page '.$pdf->PageNo().' of {nb}',0,1,'R');
		 $pdf->Cell(60);
		 $pdf->Cell(87, 5, 'Retired Employees for '. date("Y"), 0, 1,  'C');
		 $pdf->Ln(5);
		//$pdf->Ln();		
		$pdf->Cell(7, 5, '#', 1, 0, 'C');
		$pdf->Cell(20, 5, 'ID', 1, 0, 'C');
		$pdf->Cell(60, 5, 'Name', 1, 0, 'C');
		$pdf->Cell(30, 5, 'Office', 1, 0, 'C');
		$pdf->Cell(72, 5, 'Position', 1, 0, 'C');
	
		
		$pdf->Ln();
	
		$pdf->SetFont('Arial','',10);
		$pdf->SetTextColor(0,0,0);
		 }
  
		
		$pdf->Cell(7, 5,  $num, 1, 0, 'C');
		$pdf->Cell(20, 5, $row['EmpID'], 1, 0, 'C');
		$pdf->Cell(60, 5, $row['EmpName'], 1, 0, 'C');
		$pdf->Cell(30, 5, $offices['SubOffCode'], 1, 0, 'C');
		$pdf->Cell(72, 5, $positions['PosDesc'], 1, 0, 'C');
	//	$pdf->Cell(12, 5, 2012 - $row['EmpBirthYear'], 1, 0, 'C');
		

		 $pdf->Ln();
		 $num++;	
	
		 
		 } 
  }
 
  

  $pdf->Output();
  
  ob_end_flush();

?>
