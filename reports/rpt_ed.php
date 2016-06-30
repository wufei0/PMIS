<?php

ob_start();
	session_start();
	$_SESSION['theme']='blue';
	$_SESSION['path'] = '..';
	
	require_once $_SESSION['path'].'/lib/classes/MySQLClass.php';
	require_once $_SESSION['path'].'/lib/classes/fpdf/fpdf.php';
	require_once $_SESSION['path'].'/lib/classes/Functions.php';

	
	$date = date("Y.m");

	
	$office= isset($_GET['office']) ? strtoupper(mysql_escape_string(trim(strip_tags($_GET['office'])))) : '';
	$sort= isset($_GET['sortby']) ? strtoupper(mysql_escape_string(trim(strip_tags($_GET['sortby'])))) : '';
	$course= isset($_GET['course']) ? strtoupper(mysql_escape_string(trim(strip_tags($_GET['course'])))) : '';
	$school= isset($_GET['school']) ? strtoupper(mysql_escape_string(trim(strip_tags($_GET['school'])))) : '';
	
	
	
	$fPersonnel = new PersonnelFunctions();
	$MySQLi = new MySQLClass();

//Start PDF Builder
	$pdf=new FPDF();
	$pdf->AliasNbPages();
	
		if(($sort =='NAME')  || ($sort == '')){
		$sortby = 'ORDER BY `EmpLName` ASC, `EmpFName` ASC, `EmpMName` ASC';
		}else if ($sort == 'COURSE'){
		$sortby = 'ORDER BY `EducCourse` ASC';
		}else if ($sort == 'SCHOOL'){
		$sortby = 'ORDER BY `EducSchoolName` ASC';
		}
	
	if ($office=='') {

	 //  $pdf->AddPage();
		$pdf->Image('png/long.png', 3, 3, 205, 295, 'PNG');
		 
		$pdf->SetFont('Arial','',10);
		$pdf->SetTextColor(0,0,0);
		

		$result = $MySQLi -> sqlQuery("SELECT  CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName, e.`EmpID`,  e.`EducLvlID`, e.`EducCourse`, e.`EducSchoolName` FROM  ((SELECT EmpID, max(EducLvlID) as maxID FROM tblempeducbg GROUP by EmpID) as x INNER JOIN tblempeducbg as e on e.EmpID = x.EmpID and e.EducLvlID = x.maxID) INNER JOIN `tblemppersonalinfo` ON e.`EmpID` = `tblemppersonalinfo`.EmpID ". $sortby ." ;");
		
		
		$num = 1;
		while ($row = mysqli_fetch_array($result, MYSQLI_BOTH)) {

			if (($num-1) % 25 == 0) {
				$pdf->AddPage('L', 'Legal');

				// $pdf->Image('png/long.png', 3, 3, 205, 295, 'PNG');
				$pdf->Ln(25);
				$pdf->Cell(0,10,date("Y/m/d"),0,0,'L');
				$pdf->Cell(0,10,'Page '.$pdf->PageNo().' of {nb}',0,1,'R');
				$pdf->Cell(130);
				$pdf->Cell(87, 5, 'AGE SORT', 0, 1,  'C');
				$pdf->Ln(5);
				//$pdf->Ln();		
				$pdf->Cell(7, 5, '#', 1, 0, 'C');
				$pdf->Cell(70, 5, 'Name', 1, 0, 'C');
				$pdf->Cell(43, 5, 'Office', 1, 0, 'C');
				$pdf->Cell(50, 5, 'Position', 1, 0, 'C');
				$pdf->Cell(80, 5, 'Course', 1, 0, 'C');
				$pdf->Cell(85, 5, 'School', 1, 0, 'C');

				$pdf->Ln();

				$pdf->SetFont('Arial','',6.5);
				$pdf->SetTextColor(0,0,0);
			}


			$pdf->Cell(7, 5,  $num, 1, 0, 'C');
			$pdf->Cell(70, 5, $row['EmpName'], 1, 0, 'C');
			//	$pdf->Cell(55, 5, $row['EmpID'], 1, 0, 'C');

			//		$pdf->Cell(43, 5, $row['EmpID'], 1, 0, 'C');
			$pdf->Cell(43, 5, $fPersonnel->getMotherOffice($row['EmpID']), 1, 0, 'C');
			$pdf->Cell(50, 5, $fPersonnel->getEmpPosition($row['EmpID']), 1, 0, 'C');
			//	$pdf->Cell(50, 5, $row['EducLvlID'], 1, 0, 'C');
			//	$pdf->Cell(30, 5, 'Office', 1, 0, 'C');
			$pdf->Cell(80, 5, $row['EducCourse'], 1, 0, 'C');
			$pdf->Cell(85, 5, $row['EducSchoolName'], 1, 0, 'C');
			//$pdf->Cell(12, 5, 2012 - $row['EmpBirthYear'], 1, 0, 'C');


			$pdf->Ln();
			$num++;	

			 
		}
		 
  } 
	
	else if ($office!=''){

		//  $pdf->AddPage();
		$pdf->Image('png/long.png', 3, 3, 205, 295, 'PNG');

		$pdf->SetFont('Arial','',10);
		$pdf->SetTextColor(0,0,0);


		$result = $MySQLi -> sqlQuery("SELECT tblempservicerecords.PosID , `tblemppersonalinfo`.`EmpID`, MAX(CONCAT_WS(', ',tblempservicerecords.SrecFromYear, CONCAT_WS(' ',tblempservicerecords.SrecFromMonth, CONCAT_WS(' ', tblempservicerecords.SrecFromDay, '')))) AS RYear, tblsuboffices.SubOffID, tblsuboffices.SubOffCode, tblempservicerecords.MotherOfficeID, CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName  ,  `tblemppersonalinfo`.`EmpBirthDay` , `tblemppersonalinfo`.`EmpBirthMonth`, `tblemppersonalinfo`.`EmpBirthYear` FROM (tblsuboffices INNER JOIN tblempservicerecords ON tblsuboffices.SubOffID = tblempservicerecords.MotherOfficeID) INNER JOIN `tblemppersonalinfo` ON tblempservicerecords.EmpID = `tblemppersonalinfo`.EmpID where tblsuboffices.SubOffCode = '".$office."'GROUP BY tblempservicerecords.EmpID ". $sortby ." ;"); unset($sql);


		$num = 1;
		while ($row = mysql_fetch_array($result)) {
			
			$educ = mysql_fetch_array($MySQLi -> sqlQuery("SELECT  CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName, e.`EmpID`,  e.`EducLvlID`, e.`EducCourse`, e.`EducSchoolName` FROM  ((SELECT EmpID, max(EducLvlID) as maxID FROM tblempeducbg GROUP by EmpID) as x INNER JOIN tblempeducbg as e on e.EmpID = x.EmpID and e.EducLvlID = x.maxID) INNER JOIN `tblemppersonalinfo` ON e.`EmpID` = `tblemppersonalinfo`.EmpID WHERE e.EmpID = ".$row['EmpID']." ". $sortby ." ;"));


			if ( ($num-1) % 25 == 0) {
				$pdf->AddPage('L', 'Legal');

				// $pdf->Image('png/long.png', 3, 3, 205, 295, 'PNG');
				$pdf->Ln(25);
				$pdf->Cell(0,10,date("Y/m/d"),0,0,'L');
				$pdf->Cell(0,10,'Page '.$pdf->PageNo().' of {nb}',0,1,'R');
				$pdf->Cell(130);
				$pdf->Cell(87, 5, 'Record From '.$row['SubOffCode'], 0, 1,  'C');
				$pdf->Ln(5);
				//$pdf->Ln();		
				$pdf->Cell(7, 5, '#', 1, 0, 'C');
				$pdf->Cell(70, 5, 'Name', 1, 0, 'C');
				$pdf->Cell(50, 5, 'Position', 1, 0, 'C');
				$pdf->Cell(80, 5, 'Course', 1, 0, 'C');
				$pdf->Cell(125, 5, 'School', 1, 0, 'C');

				$pdf->Ln();

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);
			}


			$pdf->Cell(7, 5,  $num, 1, 0, 'C');
			$pdf->Cell(70, 5, $row['EmpName'], 1, 0, 'C');
			//	$pdf->Cell(55, 5, $row['EmpID'], 1, 0, 'C');

			//		$pdf->Cell(43, 5, $row['EmpID'], 1, 0, 'C');
			$pdf->Cell(50, 5, $fPersonnel->getEmpPosition($row['EmpID']), 1, 0, 'C');
			//	$pdf->Cell(50, 5, $row['EducLvlID'], 1, 0, 'C');
			//	$pdf->Cell(30, 5, 'Office', 1, 0, 'C');
			$pdf->Cell(80, 5, $educ['EducCourse'], 1, 0, 'C');
			$pdf->Cell(125, 5, $educ['EducSchoolName'], 1, 0, 'C');
			//$pdf->Cell(12, 5, 2012 - $row['EmpBirthYear'], 1, 0, 'C');


			$pdf->Ln();
			$num++;	


		}
  
  }
 
 

  $pdf->Output();
  
  ob_end_flush();

?>
