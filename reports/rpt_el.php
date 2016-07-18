<?php

ob_start();
	session_start();

	require_once $_SESSION['path'].'/lib/classes/fpdf/fpdf.php';
	require_once $_SESSION['path'].'/lib/classes/Functions.php';

	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD009'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[0]){echo "ERROR 401: Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	

	$OfficeID=isset($_GET['off'])?strtoupper(trim(strip_tags($_GET['off']))):'0';
	$DevisionID=isset($_GET['dev'])?strtoupper(trim(strip_tags($_GET['dev']))):'0';
	$Sex=isset($_GET['sex'])?strtoupper(trim(strip_tags($_GET['sex']))):'0';
	$ApptStID=isset($_GET['sta'])?strtoupper(trim(strip_tags($_GET['sta']))):'0';
	$EmpStatus=isset($_GET['est'])?strtoupper(trim(strip_tags($_GET['est']))):'0';
	$Sort=isset($_GET['s'])?strtoupper(trim(strip_tags($_GET['s']))):'0';

	
	$fPersonnel=new PersonnelFunctions();
	$fAppointment=new AppointmentFunctions();
	$fOffice=new OfficeFunctions();
	$MySQLi=new MySQLClass();
	
	if(!($OffName=$fOffice->getOfficeName($OfficeID))){$OffName="ALL OFFICES";}
	$Filter="*FILTER: Office = ".(($OfficeID=='0')?"ALL":$fOffice->getOfficeName($OfficeID,false)).", Appointment Status = ".(($ApptStID=='0')?"ALL":$fAppointment->getAppointmentDesc($ApptStID)).", Employee Status = ".(($EmpStatus=='0')?"ALL":$EmpStatus).", Sex = ".(($Sex=='0')?"ALL":$Sex)."";
	
	class PDF extends FPDF{
		
		// Page header
		function Header(){
			$this->Image('png/pglulogo.jpg',25.4,12.7,30,30,'JPG');
			$this->SetY(12.7);
			$this->SetTextColor(0,0,0);
			$this->SetFont('Helvetica','B',10);
			$this->MultiCell(0,4,'Republic of the Philippines',0,'C',false);
			$this->SetFont('Helvetica','B',12);
			$this->MultiCell(0,5,'Provincial Government of La Union',0,'C',false);
			$this->SetFont('Helvetica','B',10);
			$this->MultiCell(0,4,'City of San Fernando',0,'C',false);
			$this->MultiCell(0,5,'',0,'C',false); // Spacer
			$this->SetFont('Helvetica','B',16);
			$this->MultiCell(0,6,'ELIGIBILITIES ',0,'C',false);
			$this->SetFont('Helvetica','B',12);
			$this->MultiCell(0,6,$GLOBALS['OffName'],0,'C',false);
			$this->MultiCell(0,10,'','B','C',false); // Spacer
			$this->SetFont('Arial','I',6);
			$this->Cell(311,4,$GLOBALS['Filter'],0,1,'L');
			$this->SetFont('Arial','B',6.5);
			$this->SetFillColor(100,100,100);$this->SetTextColor(255,255,255);
			foreach($GLOBALS['Header'] as $h){$this->Cell($h[0],6,$h[1],$h[2],$h[3],$h[4],true);}
			$this->Ln();
		}

		// Page footer
		function Footer(){
			// Position at 1.8 cm from bottom
			$this->SetY(-30);
			$this->MultiCell(0,10,'','B','C',false); // Spacer
			$this->SetFont('Arial','I',6);
			$this->Cell(155.5,4,'Report Generated '.date('l d M, Y').' by '.$GLOBALS['fPersonnel']->getEmpName($_SESSION['user']).' ('.$_SESSION['user'].')',0,0,'L'); $this->Cell(155.5,4,'Page '.$this->PageNo().' of {nb}',0,1,'R');
		}
	}
	
	
	/* Headers */
	$Header = [
		[ 7,"#","LTRB",0,"C",],
		[10,"ID","LTRB",0,"C",],
		[45,"NAME","LTRB",0,"C",],
		//[20,"OFFICE","LTRB",0,"C",],
		[50,"POSITION","LTRB",0,"C",],
		[75,"ELIGIBILITY","LTRB",0,"C",],
		[10,"RATING","LTRB",0,"C",],
		[22,"DATE TAKEN","LTRB",0,"C",],
		[50,"PLACE TAKEN","LTRB",0,"C",],
		[19,"LICENCE #","LTRB",0,"C",],
		[22,"DATE RELEASED","LTRB",0,"C",],
	];
	
	/* Build Query */
	$Where=" WHERE '1'='1'";
	if($Sex!="0"){$Where.=" AND `EmpSex`='".$MySQLi->RealEscapeString($Sex)."'";}
	if($EmpStatus!="0"){$Where.=" AND `EmpStatus`='".$MySQLi->RealEscapeString($EmpStatus)."'";}
	if($ApptStID!="0"){$Where.=" AND `ApptStID`='".$MySQLi->RealEscapeString($ApptStID)."'";}
	/* Filter Per Office */
	if($OfficeID!="0"){$Where.=" AND `MotherOfficeID`='".$MySQLi->RealEscapeString($OfficeID)."'";}
	$OrderBy=" ORDER BY ".(($OfficeID=="0")?"`MotherOfficeID` ASC,":"")." `EmpName` ASC";
	
	$Query="SELECT * FROM `s_eligibility`".$Where; echo $Query;
	
	// echo $Query; exit();
	
	/* Initializations */
	//$RecordsPerPage=30;
	$hHeight = 6;
	$rHeight = 4.2;
	$PreviuosID = 0;
	$PrevOffID = 0;
	$Numbering = 1;
	
	//Start PDF Builder
	$pdf=new PDF();
	$pdf->AliasNbPages();
  
	/* Cell Look & Feel */
	$pdf->SetFont('Arial','',6.5);
	$pdf->SetTextColor(0,0,0);
	
	
	//$NumberOfRecords=$MySQLi->NumberOfRows($Query.";");
	
	//$NumberOfPage=ceil($NumberOfRecords/$RecordsPerPage);
	
	//for($Page=0;$Page<$NumberOfPage;$Page++){
		$pdf->AddPage('L', 'Long');
		$pdf->SetDisplayMode('fullwidth');
	
		$pdf->SetFont('Arial','',6.5);$pdf->SetTextColor(0,0,0);
		$pdf->SetFillColor(150,150,150);
		
		$result=$MySQLi->sqlQuery($Query.$OrderBy.";");//." LIMIT ".($Page*$RecordsPerPage).", ".$RecordsPerPage.";");
		while ($row=mysqli_fetch_array($result, MYSQLI_BOTH)) {
			if($PrevOffID!=$row['MotherOfficeID']){
				$pdf->SetFont('Arial','B',6.5);
				$pdf->SetFillColor(150,150,150);$pdf->SetTextColor(255,255,255);
				$pdf->Cell(310, $rHeight, $fOffice->getOfficeName($row['MotherOfficeID']), 'LTRB', 1, 'L', true);
			}
			if($PreviuosID==$row['EmpID']){
				for($i=0;$i<=3;$i++){$pdf->Cell($Header[$i][0], $rHeight, '', 'LR', 0, 'C');}
			}
			else{
				$pdf->SetFont('Arial','',6.5);$pdf->SetTextColor(0,0,0);
				$pdf->Cell($Header[0][0], $rHeight, $Numbering, 'LT', 0, 'C');
				$pdf->Cell($Header[1][0], $rHeight, $row['EmpID'], 'LT', 0, 'C');
				$pdf->Cell($Header[2][0], $rHeight, $row['EmpName'], 'LT', 0, 'L');
				//$pdf->Cell($Header[3][0], $rHeight, $fPersonnel->getMotherOffice($row['EmpID']), 'LT', 0, 'L');
				$pdf->Cell($Header[3][0], $rHeight, $fPersonnel->getEmpPosition($row['EmpID']), 'LT', 0, 'L');	//$fPersonnel->getEmpPosition($row['EmpID'])
				$Numbering++;
			}
			$pdf->SetFont('Arial','',6.5);$pdf->SetTextColor(0,0,0);
			$pdf->Cell($Header[4][0], $rHeight, $row['CSEDesc'], 'LB', 0, 'L');
			$pdf->Cell($Header[5][0], $rHeight, number_format($row['CSERating'],2), 'LB', 0, 'R');
			$pdf->Cell($Header[6][0], $rHeight, $row['CSEExamDate'], 'LB', 0, 'C');
			$pdf->Cell($Header[7][0], $rHeight, $row['CSEExamPlace'], 'LB', 0, 'L');
			$pdf->Cell($Header[8][0], $rHeight, $row['CSELicNum'], 'LB', 0, 'L');
			$pdf->Cell($Header[9][0], $rHeight, $row['CSELicReleaseDate'], 'LBR', 0, 'C');

			$pdf->Ln();
			
			$PreviuosID=$row['EmpID'];
			$PrevOffID=$row['MotherOfficeID'];
		}
		
		$pdf->Cell(310.2, 1, '', 'T', 0, 'C');
	//	$pdf->Ln();
		
	//}
 

  $pdf->Output();
  ob_end_flush();

?>
