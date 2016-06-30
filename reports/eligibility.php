<?php

ob_start();
	session_start();
	//$_SESSION['theme']='blue';
	//$_SESSION['path'] = '..';
	
	require_once $_SESSION['path'].'/lib/classes/Conf.php';
	require_once $_SESSION['path'].'/lib/classes/MySQLClass.php';
	require_once $_SESSION['path'].'/lib/classes/fpdf/fpdf.php';

	$date = date("Y.m");

	$office= isset($_GET['office']) ? strtoupper(mysql_escape_string(trim(strip_tags($_GET['office'])))) : '';
	$sort= isset($_GET['OrderBy']) ? strtoupper(mysql_escape_string(trim(strip_tags($_GET['OrderBy'])))) : '';
	$course= isset($_GET['course']) ? strtoupper(mysql_escape_string(trim(strip_tags($_GET['course'])))) : '';
	$school= isset($_GET['school']) ? strtoupper(mysql_escape_string(trim(strip_tags($_GET['school'])))) : '';
	
	//Start PDF Builder
	$pdf=new FPDF();
	$pdf->AliasNbPages();
	
	if(($sort =='NAME') || ($sort == '')){
		$OrderBy = 'ORDER BY `EmpName` ASC';
	}
	else if ($sort == 'ID'){
		$OrderBy = 'ORDER BY `EmpID` ASC';
	}
	

	$MySQLi = new MySQLClass();
   
	$pdf->SetFont('Arial','',6.5);
	$pdf->SetTextColor(0,0,0);
	
	$Where="WHERE `EmpStatus`='ACTIVE'";
	//$OrderBy="";
	
	$sql="SELECT `EmpID` FROM `s_eligibility` ".$Where.";";
	$NumberOfRecords=$MySQLi->NumberOfRows($sql);
	$RecordsPerPage=25;
	$NumberOfPage=ceil($NumberOfRecords/$RecordsPerPage);
	
	$PreviuosID=0;
	$Numbering=1;
	for($Page=0;$Page<$NumberOfPage;$Page++){
		$pdf->AddPage('L', 'Long');
			
		// $pdf->Image('png/long.png', 3, 3, 205, 295, 'PNG');
		$pdf->Ln(25);
		//$pdf->Cell(0,10,date("Y/m/d"),0,0,'L');
		$pdf->Cell(0,10,'Page '.$pdf->PageNo().' of {nb}',0,1,'R');
		//$pdf->Cell(130);
		$pdf->Cell(310.2, 5, 'EILIGIBILITY', 1, 1,  'C');
		$pdf->Ln(5);
		//$pdf->Ln();		
		$pdf->Cell(7, 5, '#', 1, 0, 'C');
		$pdf->Cell(15, 5, 'ID', 1, 0, 'C');
		$pdf->Cell(45, 5, 'Name', 1, 0, 'C');
		$pdf->Cell(25, 5, 'Office', 1, 0, 'C');
		$pdf->Cell(30, 5, 'Position', 1, 0, 'C');
		$pdf->Cell(70, 5, 'CSE', 1, 0, 'C');
		$pdf->Cell(10, 5, 'Rating', 1, 0, 'C');
		$pdf->Cell(22, 5, 'Exam Date', 1, 0, 'C');
		$pdf->Cell(38, 5, 'Exam Place', 1, 0, 'C');
		$pdf->Cell(26.2, 5, 'Licensure #', 1, 0, 'C');
		$pdf->Cell(22, 5, 'Release Date', 1, 0, 'C');
		
		$pdf->Ln();
	
		$pdf->SetFont('Arial','',6.5);
		$pdf->SetTextColor(0,0,0);
		
		$result = $MySQLi->sqlQuery("SELECT * FROM `s_eligibility` ".$Where." ".$OrderBy." LIMIT ".($Page*$RecordsPerPage).", ".$RecordsPerPage.";");
		while ($row = mysqli_fetch_array($result, MYSQLI_BOTH)) {
			$positions=$MySQLi->GetArray("SELECT tblempservicerecords.PosID, tblpositions.PosID, tblpositions.PosDesc "
							. "FROM tblpositions INNER JOIN tblempservicerecords ON tblpositions.PosID = tblempservicerecords.PosID "
							. "WHERE tblempservicerecords.EmpID =  '".$row['EmpID']."'  GROUP BY tblempservicerecords.EmpID ;");
			$offices=$MySQLi->GetArray("SELECT tblempservicerecords.EmpID, tblsuboffices.SubOffID, tblsuboffices.SubOffCode, tblempservicerecords.MotherOfficeID "
							. "FROM tblsuboffices INNER JOIN tblempservicerecords ON tblsuboffices.SubOffID = tblempservicerecords.MotherOfficeID "
							. "WHERE tblempservicerecords.EmpID =  '".$row['EmpID']."'  GROUP BY tblempservicerecords.EmpID ;");

			if($PreviuosID==$row['EmpID']){
				$pdf->Cell(7, 5,  '', 'L', 0, 'C');
				$pdf->Cell(15, 5, '', 'L', 0, 'C');
				$pdf->Cell(45, 5, '', 'L', 0, 'C');
				$pdf->Cell(25, 5, '', 'L', 0, 'C');
				$pdf->Cell(30, 5, '', 'L', 0, 'C');
			}
			else{
				$pdf->Cell(7, 5,  $Numbering, 'LT', 0, 'C');
				$pdf->Cell(15, 5, $row['EmpID'], 'LT', 0, 'C');
				$pdf->Cell(45, 5, $row['EmpName'], 'LT', 0, 'L');
				$pdf->Cell(25, 5, $offices['SubOffCode'],  'LT', 0, 'L');
				$pdf->Cell(30, 5, $positions['PosDesc'], 'LT', 0, 'L');	
				$Numbering++;
			}
		
			$pdf->Cell(70, 5, $row['CSEDesc'], 'LT', 0, 'L');
			$pdf->Cell(10, 5, number_format($row['CSERating'],2), 'LT', 0, 'R');
			$pdf->Cell(22, 5, $row['CSEExamDate'], 'LT', 0, 'C');
			$pdf->Cell(38, 5, $row['CSEExamPlace'], 'LT', 0, 'L');
			$pdf->Cell(26.2, 5, $row['CSELicNum'], 'LT', 0, 'L');
			$pdf->Cell(22, 5, $row['CSELicReleaseDate'], 'LTR', 0, 'C');

			$pdf->Ln();
			
			
			$PreviuosID=$row['EmpID'];
		}
		
		$pdf->Cell(310.2, 1, '', 'T', 0, 'C');
		$pdf->Ln();
		
	}
 

  $pdf->Output();
  ob_end_flush();

?>
