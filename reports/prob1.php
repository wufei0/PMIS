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
	
	if(($sort =='NAME') || ($sort == '')){
		$sortby = 'ORDER BY `EmpLName` ASC, `EmpFName` ASC, `EmpMName` ASC';
	}
	else if ($sort == 'ID'){
		$sortby = 'ORDER BY `tblemppersonalinfo`.`EmpID` ASC';
	}
	
	
	$Config = new Conf();
	$MySQLi = new MySQLClass($Config);
	$pdf->SetFont('Arial','',6.5);
	$pdf->SetTextColor(0,0,0);
	
	
	if ($office=='') {
	
		$sql="SELECT `tblempcse`.`EmpID` FROM `tblempcse` INNER JOIN `tblemppersonalinfo` ON `tblempcse`.`EmpID` = `tblemppersonalinfo`.EmpID";
		$NumberOfRecords=mysql_num_rows($MySQLi->sqlQuery($sql));
		$RecordsPerPage=25;
		$NumberOfPage=ceil($NumberOfRecords/$RecordsPerPage);
		
		$PreviuosID=0;
		$Numbering=1;
	
		for($Page=0;$Page<$NumberOfPage;$Page++){
			$pdf->AddPage('L', 'Long');
				
			// $pdf->Image('png/long.png', 3, 3, 205, 295, 'PNG');
			$pdf->Ln(25);
			$pdf->Cell(0,10,date("Y/m/d"),0,0,'L');
			$pdf->Cell(0,10,'Page '.$pdf->PageNo().' of {nb}',0,1,'R');
			$pdf->Cell(130);
			$pdf->Cell(87, 5, 'EILIGIBILITY', 0, 1,  'C');
			$pdf->Ln(5);
			//$pdf->Ln();		
			$pdf->Cell(7, 5, '#', 1, 0, 'C');
			$pdf->Cell(15, 5, 'ID', 1, 0, 'C');
			$pdf->Cell(50, 5, 'Name', 1, 0, 'C');
			$pdf->Cell(27, 5, 'Office', 1, 0, 'C');
			$pdf->Cell(27, 5, 'Position', 1, 0, 'C');
			$pdf->Cell(70, 5, 'CSE', 1, 0, 'C');
			$pdf->Cell(20, 5, 'Rating', 1, 0, 'C');
			$pdf->Cell(20, 5, 'Exam Date', 1, 0, 'C');
			$pdf->Cell(35, 5, 'Exam Place', 1, 0, 'C');
			$pdf->Cell(15, 5, 'Licensure #', 1, 0, 'C');
			$pdf->Cell(23, 5, 'Release Date', 1, 0, 'C');
			
			$pdf->Ln();
		
			$pdf->SetFont('Arial','',6.5);
			$pdf->SetTextColor(0,0,0);
			
			$result = $MySQLi -> sqlQuery("SELECT  CONCAT_WS('/',tblempcse.CSEExamMonth, CONCAT_WS('/',tblempcse.CSEExamDay, CONCAT_WS(' ', tblempcse.CSEExamYear, ''))) as EDate, CONCAT_WS('/',tblempcse.CSELicReleaseMonth, CONCAT_WS('/',tblempcse.CSELicReleaseDay, CONCAT_WS(' ', tblempcse.CSELicReleaseYear, ''))) as RDate,`tblempcse`.`EmpID`, `tblempcse`.`CSERating`, `tblempcse`.`CSELicNum`, `tblempcse`.`CSEExamPlace`, `tblempcse`.`CSEDesc`, `tblemppersonalinfo`.EmpID, CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName FROM `tblempcse` INNER JOIN `tblemppersonalinfo` ON `tblempcse`.`EmpID` = `tblemppersonalinfo`.EmpID $sortby LIMIT ".($Page*25).",$RecordsPerPage;");
			
			
			while ($row = mysql_fetch_array($result)) {
				$positions =mysql_fetch_array($MySQLi -> sqlQuery("SELECT tblempservicerecords.PosID, tblpositions.PosID, tblpositions.PosDesc FROM tblpositions INNER JOIN tblempservicerecords ON tblpositions.PosID = tblempservicerecords.PosID WHERE tblempservicerecords.EmpID =  '".$row['EmpID']."'  GROUP BY tblempservicerecords.EmpID ;"));

				$offices =mysql_fetch_array($MySQLi -> sqlQuery("SELECT tblempservicerecords.EmpID, tblsuboffices.SubOffID, tblsuboffices.SubOffCode, tblempservicerecords.MotherOfficeID FROM tblsuboffices INNER JOIN tblempservicerecords ON tblsuboffices.SubOffID = tblempservicerecords.MotherOfficeID WHERE tblempservicerecords.EmpID =  '".$row['EmpID']."'  GROUP BY tblempservicerecords.EmpID ;"));

				if($PreviuosID==$row['EmpID']){
					$pdf->Cell(7, 5,  '', 1, 0, 'C');
					$pdf->Cell(15, 5, '', 1, 0, 'C');
					$pdf->Cell(50, 5, '', 1, 0, 'C');
					$pdf->Cell(27, 5, '', 1, 0, 'C');
					$pdf->Cell(27, 5, '', 1, 0, 'C');
				}
				else{
					$pdf->Cell(7, 5,  $Numbering, 1, 0, 'C');
					$pdf->Cell(15, 5, $row['EmpID'], 1, 0, 'C');
					$pdf->Cell(50, 5, $row['EmpName'], 1, 0, 'C');
					$pdf->Cell(27, 5, $offices['SubOffCode'],  1, 0, 'C');
					$pdf->Cell(27, 5, $positions['PosDesc'], 1, 0, 'C');	
					$Numbering++;
				}
			
				$pdf->Cell(70, 5, $row['CSEDesc'], 1, 0, 'C');
				$pdf->Cell(20, 5, $row['CSERating'], 1, 0, 'C');
				$pdf->Cell(20, 5, $row['EDate'], 1, 0, 'C');
				$pdf->Cell(35, 5, $row['CSEExamPlace'], 1, 0, 'C');
				$pdf->Cell(15, 5, $row['CSELicNum'], 1, 0, 'C');
				$pdf->Cell(23, 5, $row['RDate'], 1, 0, 'C');

				$pdf->Ln();
				
				$PreviuosID=$row['EmpID'];
			}
		}
 
	 } else if ($office!=''){
	 
			if ($office=='OPG'){
			$officecheck='SOOF00100';
			}else if ($office=='SSD') {
			$officecheck='SOOF00101';
			}else if ($office=='MISD') {
			$officecheck='SOOF00102';
			}else if ($office=='JAIL') {
			$officecheck='SOOF00103';
			}else if ($office=='HRMD') {
			$officecheck='SOOF00104';
			}else if ($office=='PYSDO') {
			$officecheck='SOOF00105';
			}else if ($office=='VICE-GOV') {
			$officecheck='SOOF00200';
			}else if ($office=='SP') {
			$officecheck='SOOF00300';
			}else if ($office=='ADMIN') {
			$officecheck='SOOF00400';
			}else if ($office=='PPDC') {
			$officecheck='SOOF00500';
			}else if ($office=='BUDGET') {
			$officecheck='SOOF00600';
			}else if ($office=='GSO') {
			$officecheck='SOOF00700';
			}else if ($office=='PITO') {
			$officecheck='SOOF00800';
			}else if ($office=='LEGAL') {
			$officecheck='SOOF00900';
			}else if ($office=='ACCOUNTING') {
			$officecheck='SOOF01000';
			}else if ($office=='AUDIT') {
			$officecheck='SOOF01100';
			}else if ($office=='PTO') {
			$officecheck='SOOF01200';
			}else if ($office=='ASSESOR') {
			$officecheck='SOOF01300';
			}else if ($office=='PHO') {
			$officecheck='SOOF01400';
			}else if ($office=='NUTRITION') {
			$officecheck='SOOF01401';
			}else if ($office=='BDH') {
			$officecheck='SOOF01402';
			}else if ($office=='NDH') {
			$officecheck='SOOF01403';
			}else if ($office=='NLUMCH') {
			$officecheck='SOOF01404';
			}else if ($office=='LUMED') {
			$officecheck='SOOF01405';
			}else if ($office=='RDH') {
			$officecheck='SOOF01406';
			}else if ($office=='PSWD') {
			$officecheck='SOOF01500';
			}else if ($office=='PESO') {
			$officecheck='SOOF01501';
			}else if ($office=='ENGINEERING') {
			$officecheck='SOOF01600';
			}else if ($office=='VERENARY') {
			$officecheck='SOOF01700';
			}else if ($office=='AGRICULTURE') {
			$officecheck='SOOF01800';
			}

	 
		$sql="SELECT `tblempservicerecords`.`EmpID` FROM `tblempservicerecords` INNER JOIN `tblemppersonalinfo` ON `tblempservicerecords`.`EmpID` = `tblemppersonalinfo`.EmpID  GROUP BY tblempservicerecords.EmpID";
		$NumberOfRecords=mysql_num_rows($MySQLi->sqlQuery($sql));
		$RecordsPerPage=25;
		$NumberOfPage=ceil($NumberOfRecords/$RecordsPerPage);
		
		$PreviuosID=0;
		$Numbering=1;
	 
	 
		for($Page=0;$Page<$NumberOfPage;$Page++){
			$pdf->AddPage('L', 'Long');
				
			// $pdf->Image('png/long.png', 3, 3, 205, 295, 'PNG');
			$pdf->Ln(25);
			$pdf->Cell(0,10,date("Y/m/d"),0,0,'L');
			$pdf->Cell(0,10,'Page '.$pdf->PageNo().' of {nb}',0,1,'R');
			$pdf->Cell(130);
			$pdf->Cell(87, 5, 'EILIGIBILITY', 0, 1,  'C');
			$pdf->Ln(5);
			//$pdf->Ln();		
			$pdf->Cell(7, 5, '#', 1, 0, 'C');
			$pdf->Cell(15, 5, 'ID', 1, 0, 'C');
			$pdf->Cell(50, 5, 'Name', 1, 0, 'C');
			$pdf->Cell(27, 5, 'Office', 1, 0, 'C');
			$pdf->Cell(27, 5, 'Position', 1, 0, 'C');
			$pdf->Cell(70, 5, 'CSE', 1, 0, 'C');
			$pdf->Cell(20, 5, 'Rating', 1, 0, 'C');
			$pdf->Cell(20, 5, 'Exam Date', 1, 0, 'C');
			$pdf->Cell(35, 5, 'Exam Place', 1, 0, 'C');
			$pdf->Cell(15, 5, 'Licensure #', 1, 0, 'C');
			$pdf->Cell(23, 5, 'Release Date', 1, 0, 'C');
			
			$pdf->Ln();
		
			$pdf->SetFont('Arial','',6.5);
			$pdf->SetTextColor(0,0,0);
			
			$result = $MySQLi -> sqlQuery("SELECT  CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName, e.`EmpID`,  e.`MotherOfficeID`, e.`PosID` FROM  ((SELECT EmpID, MAX(SRecFromYear) AS RYear FROM tblempservicerecords GROUP by EmpID) as x INNER JOIN tblempservicerecords as e on e.EmpID = x.EmpID and e.SRecFromYear = x.RYear) INNER JOIN `tblemppersonalinfo` ON e.`EmpID` = `tblemppersonalinfo`.EmpID   $sortby LIMIT ".($Page*25).",$RecordsPerPage;");
			
			
			//$result = $MySQLi -> sqlQuery("SELECT  CONCAT_WS('/',tblempcse.CSEExamMonth, CONCAT_WS('/',tblempcse.CSEExamDay, CONCAT_WS(' ', tblempcse.CSEExamYear, ''))) as EDate, CONCAT_WS('/',tblempcse.CSELicReleaseMonth, CONCAT_WS('/',tblempcse.CSELicReleaseDay, CONCAT_WS(' ', tblempcse.CSELicReleaseYear, ''))) as RDate,`tblempcse`.`EmpID`, `tblempcse`.`CSERating`, `tblempcse`.`CSELicNum`, `tblempcse`.`CSEExamPlace`, `tblempcse`.`CSEDesc`, `tblemppersonalinfo`.EmpID, CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName FROM `tblempcse` INNER JOIN `tblemppersonalinfo` ON `tblempcse`.`EmpID` = `tblemppersonalinfo`.EmpID $sortby LIMIT ".($Page*25).",$RecordsPerPage;");
			
			
			while ($row = mysql_fetch_array($result)) {
				$positions =mysql_fetch_array($MySQLi -> sqlQuery("SELECT tblempservicerecords.PosID, tblpositions.PosID, tblpositions.PosDesc FROM tblpositions INNER JOIN tblempservicerecords ON tblpositions.PosID = tblempservicerecords.PosID WHERE tblempservicerecords.EmpID =  '".$row['EmpID']."'  GROUP BY tblempservicerecords.EmpID ;"));

				$offices =mysql_fetch_array($MySQLi -> sqlQuery("SELECT tblempservicerecords.EmpID, tblsuboffices.SubOffID, tblsuboffices.SubOffCode, tblempservicerecords.MotherOfficeID FROM tblsuboffices INNER JOIN tblempservicerecords ON tblsuboffices.SubOffID = tblempservicerecords.MotherOfficeID WHERE tblempservicerecords.EmpID =  '".$row['EmpID']."'  GROUP BY tblempservicerecords.EmpID ;"));
				
				$cse=mysql_fetch_array($MySQLi -> sqlQuery("SELECT  CONCAT_WS('/',tblempcse.CSEExamMonth, CONCAT_WS('/',tblempcse.CSEExamDay, CONCAT_WS(' ', tblempcse.CSEExamYear, ''))) as EDate, CONCAT_WS('/',tblempcse.CSELicReleaseMonth, CONCAT_WS('/',tblempcse.CSELicReleaseDay, CONCAT_WS(' ', tblempcse.CSELicReleaseYear, ''))) as RDate,`tblempcse`.`EmpID`, `tblempcse`.`CSERating`, `tblempcse`.`CSELicNum`, `tblempcse`.`CSEExamPlace`, `tblempcse`.`CSEDesc`, `tblemppersonalinfo`.EmpID, CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName FROM `tblempcse` INNER JOIN `tblemppersonalinfo` ON `tblempcse`.`EmpID` = `tblemppersonalinfo`.EmpID  WHERE tblempcse.EmpID =  '".$row['EmpID']."'  ;"));
				

				$try= $MySQLi -> sqlQuery("SELECT CONCAT_WS('/',tblempcse.CSEExamMonth, CONCAT_WS('/',tblempcse.CSEExamDay, CONCAT_WS(' ', tblempcse.CSEExamYear, ''))) as EDate, CONCAT_WS('/',tblempcse.CSELicReleaseMonth, CONCAT_WS('/',tblempcse.CSELicReleaseDay, CONCAT_WS(' ', tblempcse.CSELicReleaseYear, ''))) as RDate, `tblempcse`.`EmpID`, `tblempcse`.`CSEDesc`, `tblempcse`.`CSERating`, `tblempcse`.`CSEExamPlace`, `tblempcse`.`CSELicNum`, `tblemppersonalinfo`.EmpID FROM `tblempcse` INNER JOIN `tblemppersonalinfo` ON `tblempcse`.`EmpID` = `tblemppersonalinfo`.EmpID  WHERE tblempcse.EmpID =  '".$row['EmpID']."'  ;");
				
				while ($csedesc=mysql_fetch_array($try)) {
				
				if($PreviuosID==$row['EmpID']){
					$pdf->Cell(7, 5,  '', 1, 0, 'C');
					$pdf->Cell(15, 5, '', 1, 0, 'C');
					$pdf->Cell(50, 5, '', 1, 0, 'C');
					$pdf->Cell(27, 5, '', 1, 0, 'C');
					$pdf->Cell(27, 5, '', 1, 0, 'C');
				}
				else{
					$pdf->Cell(7, 5,  $Numbering, 1, 0, 'C');
					$pdf->Cell(15, 5, $row['EmpID'], 1, 0, 'C');
					$pdf->Cell(50, 5, $row['EmpName'], 1, 0, 'C');
					$pdf->Cell(27, 5, $offices['SubOffCode'],  1, 0, 'C');
					$pdf->Cell(27, 5, $positions['PosDesc'], 1, 0, 'C');	
					$Numbering++;
				}
			
				$pdf->Cell(70, 5, $csedesc['CSEDesc'], 1, 0, 'C');
				$pdf->Cell(20, 5, $csedesc['CSERating'], 1, 0, 'C');
				$pdf->Cell(20, 5, $csedesc['EDate'], 1, 0, 'C');
				$pdf->Cell(35, 5, $csedesc['CSEExamPlace'], 1, 0, 'C');
				$pdf->Cell(15, 5, $csedesc['CSELicNum'], 1, 0, 'C');
				$pdf->Cell(23, 5, $csedesc['RDate'], 1, 0, 'C');

				$pdf->Ln();
				
				$PreviuosID=$row['EmpID'];
			}
			}
		}	 	
	}
  $pdf->Output();
  ob_end_flush();

?>
