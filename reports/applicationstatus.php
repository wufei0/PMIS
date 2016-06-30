<?php

ob_start();
	session_start();
	$_SESSION['theme']='blue';
	$_SESSION['path'] = '..';
	
	require_once $_SESSION['path'].'/lib/classes/Conf.php';
	require_once $_SESSION['path'].'/lib/classes/MySQLClass.php';
	require_once $_SESSION['path'].'/lib/classes/fpdf/fpdf.php';
	
	$status= isset($_GET['status']) ? strtoupper(mysql_escape_string(trim(strip_tags($_GET['status'])))) : '';
	$office= isset($_GET['office']) ? strtoupper(mysql_escape_string(trim(strip_tags($_GET['office'])))) : '';
		
//Start PDF Builder
	$pdf=new FPDF();
	$pdf->AliasNbPages();
	
	
	if(($status!='') && ($office=='')) {
	$Config = new Conf();
	$MySQLi = new MySQLClass($Config);
	
 //  $pdf->AddPage();
   $pdf->Image('png/long.png', 3, 3, 205, 295, 'PNG');
	$pdf->SetFont('Arial','',10);
	$pdf->SetTextColor(0,0,0);
	
	
	$result = $MySQLi -> sqlQuery("SELECT  `tblemppersonalinfo`.`EmpID`, MAX(CONCAT_WS(', ',tblempservicerecords.SrecFromYear, CONCAT_WS(' ',tblempservicerecords.SrecFromMonth, CONCAT_WS(' ', tblempservicerecords.SrecFromDay, '')))) AS RYear, tblsuboffices.SubOffID, tblsuboffices.SubOffCode, tblempservicerecords.MotherOfficeID, CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName  ,  `tblemppersonalinfo`.`EmpBirthDay` , `tblemppersonalinfo`.`EmpBirthMonth`, `tblemppersonalinfo`.`EmpBirthYear`, tblempservicerecords.PosID, tblempservicerecords.ApptStID FROM (tblsuboffices INNER JOIN tblempservicerecords ON tblsuboffices.SubOffID = tblempservicerecords.MotherOfficeID) INNER JOIN `tblemppersonalinfo` ON tblempservicerecords.EmpID = `tblemppersonalinfo`.EmpID where `tblempservicerecords`.ApptStID  =  '".$status."' GROUP BY tblempservicerecords.EmpID ORDER BY `EmpLName` , `EmpFName`, `EmpMName`  ;"); unset($sql);
	
	
  
  $num = 1;
  while ($row = mysql_fetch_array($result)) {
  $positions=mysql_fetch_array($MySQLi -> sqlQuery("SELECT `PosID`, `PosDesc` FROM `tblpositions` WHERE `PosID`='".$row['PosID']."';")); 
  $stat=mysql_fetch_array($MySQLi -> sqlQuery("SELECT `ApptStID`,`ApptStDesc` FROM `tblapptstatus` WHERE `ApptStID`='".$row['ApptStID']."';"));
  
 

   if ( ($num-1) % 45 == 0) {
		$pdf->AddPage();
		$pdf->Image('png/long.png', 3, 3, 205, 295, 'PNG');
		$pdf->Ln(25);
		$pdf->Cell(0,10,date("m/d/Y"),0,0,'L');
		$pdf->Cell(0,10,'Page '.$pdf->PageNo().' of {nb}',0,1,'R');
		$pdf->Cell(60);
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell(87, 5, $stat['ApptStDesc'].' EMPLOYEES', 0, 1,  'C');
		$pdf->Ln(5);
		//$pdf->Ln();		
		$pdf->Cell(7, 5, '#', 1, 0, 'C');
		$pdf->Cell(20, 5, 'ID Number', 1, 0, 'C');
		$pdf->Cell(71, 5, 'Name', 1, 0, 'C');
		$pdf->Cell(20, 5, 'Office', 1, 0, 'C');
		$pdf->Cell(71, 5, 'Position', 1, 0, 'C');

		
		$pdf->Ln();
	
		$pdf->SetFont('Arial','',10);
		$pdf->SetTextColor(0,0,0);
		 }
  
		$pdf->Cell(7, 5,  $num, 1, 0, 'C');
		$pdf->Cell(20, 5,  $row['EmpID'], 1, 0, 'C');
      	$pdf->Cell(71, 5,  $row['EmpName'], 1, 0, 'C');
		$pdf->Cell(20, 5, $row['SubOffCode'], 1, 0, 'C');
		$pdf->Cell(71, 5, $positions['PosDesc'], 1, 0, 'C');
		 $pdf->Ln();
		 $num++;	
		 }

  }  else  if(($status!='') && ($office!='')) {
	
	$Config = new Conf();
	$MySQLi = new MySQLClass($Config);
	
	
 //  $pdf->AddPage();
   $pdf->Image('png/long.png', 3, 3, 205, 295, 'PNG');
   
	$pdf->SetFont('Arial','',10);
	$pdf->SetTextColor(0,0,0);
		
	$result = $MySQLi -> sqlQuery("SELECT  `tblemppersonalinfo`.`EmpID`, MAX(CONCAT_WS(', ',tblempservicerecords.SrecFromYear, CONCAT_WS(' ',tblempservicerecords.SrecFromMonth, CONCAT_WS(' ', tblempservicerecords.SrecFromDay, '')))) AS RYear, tblsuboffices.SubOffID, tblsuboffices.SubOffCode, tblempservicerecords.MotherOfficeID, CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName  ,  `tblemppersonalinfo`.`EmpBirthDay` , `tblemppersonalinfo`.`EmpBirthMonth`, `tblemppersonalinfo`.`EmpBirthYear`, tblempservicerecords.PosID, tblempservicerecords.ApptStID FROM (tblsuboffices INNER JOIN tblempservicerecords ON tblsuboffices.SubOffID = tblempservicerecords.MotherOfficeID) INNER JOIN `tblemppersonalinfo` ON tblempservicerecords.EmpID = `tblemppersonalinfo`.EmpID where `tblempservicerecords`.ApptStID  =  '".$status."' and tblsuboffices.SubOffCode = '". $office ."' GROUP BY tblempservicerecords.EmpID ORDER BY `EmpLName` , `EmpFName`, `EmpMName`  ;"); unset($sql);
	
		
   $num = 1;
  while ($row = mysql_fetch_array($result)) {
  $position=mysql_fetch_array($MySQLi -> sqlQuery("SELECT `PosID`,`PosDesc` FROM `tblpositions` WHERE `PosID`='".$row['PosID']."';")); 
  $stat=mysql_fetch_array($MySQLi -> sqlQuery("SELECT `ApptStID`,`ApptStDesc` FROM `tblapptstatus` WHERE `ApptStID`='".$row['ApptStID']."';"));

   if ( ($num-1) % 45 == 0) {
		$pdf->AddPage();
       $pdf->Image('png/long.png', 3, 3, 205, 295, 'PNG');
	  $pdf->Ln(25);
$pdf->Cell(0,10,date("m/d/Y"),0,0,'L');
    $pdf->Cell(0,10,'Page '.$pdf->PageNo().' of {nb}',0,1,'R');
		 $pdf->Cell(60);
		 $pdf->SetFont('Arial','B',10);
		 $pdf->Cell(87, 5, $stat['ApptStDesc']. ' EMPLOYEES OF '. $office , 0, 1,  'C');
		 $pdf->Ln(5);
		//$pdf->Ln();		
		$pdf->Cell(7, 5, '#', 1, 0, 'C');
		$pdf->Cell(20, 5, 'ID Number', 1, 0, 'C');
		$pdf->Cell(81, 5, 'Name', 1, 0, 'C');
		$pdf->Cell(81, 5, 'Position', 1, 0, 'C');
		
		$pdf->Ln();
	
		$pdf->SetFont('Arial','',10);
		$pdf->SetTextColor(0,0,0);
		 }
  		$pdf->Cell(7, 5,  $num, 1, 0, 'C');
		$pdf->Cell(20, 5,  $row['EmpID'], 1, 0, 'C');
      	$pdf->Cell(81, 5,  $row['EmpName'], 1, 0, 'C');
		$pdf->Cell(81, 5,  $position['PosDesc'], 1, 0, 'C');
		 $pdf->Ln();
		 $num++;	
		 }
  } 
  $pdf->Output();
    ob_end_flush();

?>
