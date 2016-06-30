<?php

ob_start();
	session_start();

	require_once $_SESSION['path'].'/lib/classes/MySQLClass.php';
	require_once $_SESSION['path'].'/lib/classes/fpdf/fpdf.php';
	require_once $_SESSION['path'].'/lib/classes/Functions.php';

	
	

	$OfficeID=isset($_GET['off'])?strtoupper(trim(strip_tags($_GET['off']))):'0';
	$DevisionID=isset($_GET['dev'])?strtoupper(trim(strip_tags($_GET['dev']))):'0';
	$RepSG=isset($_GET['salgrd'])?strtoupper(trim(strip_tags($_GET['salgrd']))):'0';
	$RepPo=isset($_GET['lvlpos'])?strtoupper(trim(strip_tags($_GET['lvlpos']))):'0';
	$RepSt=isset($_GET['empst'])?strtoupper(trim(strip_tags($_GET['empst']))):'0';
	$RepAR=isset($_GET['agern'])?strtoupper(trim(strip_tags($_GET['agern']))):'0';
	
	//echo $RepAR; exit();
	
	$fPersonnel=new PersonnelFunctions();
	
	class PDF extends FPDF{
		
		// Page header
		function Header(){
			$this->Image('png/pglulogo.jpg',25.4,10,30,30,'JPG');
			$this->SetY(10);
			$this->SetTextColor(0,0,0);
			$this->SetFont('Helvetica','B',10);
			$this->MultiCell(0,4,'Republic of the Philippines',0,'C',false);
			$this->SetFont('Helvetica','B',12);
			$this->MultiCell(0,5,'Provincial Government of La Union',0,'C',false);
			$this->SetFont('Helvetica','B',10);
			$this->MultiCell(0,4,'City of San Fernando',0,'C',false);
			$this->MultiCell(0,5,'',0,'C',false); // Spacer
			$this->SetFont('Helvetica','B',16);
			$this->MultiCell(0,6,'HR PROFILE ',0,'C',false);
			$this->SetFont('Helvetica','B',12);
			$this->MultiCell(0,6,"OFFICE OF THE PROVINCIAL ADMINISTRATOR",0,'C',false);
			// $this->SetFillColor(50,50,50);
			// for($g=1;$g<=196;$g++){$this->Cell(1, 5, '', 1, 0, 'C',($g%10==0));}$this->Ln();
			$this->MultiCell(0,3,'','B','C',false); // Spacer
			$this->Ln(10);
		}

		// Page footer
		function Footer(){
			// Position at 1.8 cm from bottom
			$this->SetY(-20);
			$this->MultiCell(0,10,'','B','C',false); // Spacer
			$this->SetFont('Arial','I',6);$this->SetTextColor(0,0,0);
			$this->Cell(98,4,'Report Generated '.date('l d M, Y').' by '.$GLOBALS['fPersonnel']->getEmpName($_SESSION['user']).' ('.$_SESSION['user'].')',0,0,'L'); $this->Cell(98,4,'Page '.$this->PageNo().' of {nb}',0,1,'R');
		}
	}
	
	function genEmploymentStatusReport($pdf){
		$hHeight = 6;
		$rHeight = 4.2;
		$pdf->SetX(47);
		/* EMPLOYMENT STATUS */
		$pdf->SetFont('Arial','B',9);$pdf->SetTextColor(0,0,0);
		$pdf->Cell(120,4,'NUMBER OF EMPLOYEE BASED ON EMPLOYMENT STATUS',0,1,'L');
		$Header = [
			[ 7,"#","LTRB",0,"C",],
			[53,"EMPLOYMENT STATUS","LTRB",0,"C",],
			[20,"MALE","LTRB",0,"C",],
			[20,"FEMALE","LTRB",0,"C",],
			[20,"TOTAL","LTRB",0,"C",],
		];
		$pdf->SetX(48);
		$pdf->SetFillColor(50,50,50);$pdf->SetFont('Arial','',7);$pdf->SetTextColor(255,255,255);
		foreach($Header as $h){$pdf->Cell($h[0],$hHeight,$h[1],$h[2],$h[3],$h[4],true);}
		$pdf->Ln();
		
		$i=1;$TotalMale=0;$TotalFema=0;
		$pdf->SetFont('Arial','',6.5);$pdf->SetTextColor(0,0,0);
		$MySQLi=new MySQLClass();
		$result=$MySQLi->sqlQuery("SELECT * FROM `tblapptstatus` WHERE `ApptStID` <> 'AS000' ORDER BY `ApptStDesc`;");
		$sql="SELECT count(*) FROM `s_servicerecord` WHERE `SRecCurrentAppointment` = '1' AND `EmpStatus` = 'ACTIVE'";
		while ($row=mysqli_fetch_array($result, MYSQLI_BOTH)) {
			$countMale=$MySQLi->GetArray($sql." AND `ApptStID` = '".$row['ApptStID']."' AND `EmpSex` = 'MALE'")[0];
			$countFema=$MySQLi->GetArray($sql." AND `ApptStID` = '".$row['ApptStID']."' AND `EmpSex` = 'FEMALE'")[0];
			$Total=$countMale+$countFema;
			$TotalMale=$TotalMale+$countMale;
			$TotalFema=$TotalFema+$countFema;
			$pdf->SetX(48);
			$pdf->Cell($Header[0][0], $rHeight, $i.". ", 'LB', 0, 'R');
			$pdf->Cell($Header[1][0], $rHeight, $row['ApptStDesc'], 'LB', 0, 'L');
			$pdf->Cell($Header[2][0], $rHeight, $countMale, 'LB', 0, 'C');
			$pdf->Cell($Header[3][0], $rHeight, $countFema, 'LB', 0, 'C');
			$pdf->Cell($Header[4][0], $rHeight, $Total, 'LBR', 0, 'C');
			$pdf->Ln();
			$i++;
		}
		$pdf->SetFont('Arial','B',7);
		$pdf->SetFillColor(120,120,120);$pdf->SetTextColor(255,255,255);
		$pdf->SetX(48);
		$pdf->Cell($Header[0][0] + $Header[1][0], $hHeight, "GRAND TOTAL", 'LB', 0, 'C', true);
		$pdf->Cell($Header[2][0], $hHeight, $TotalMale, 'LB', 0, 'C', true);
		$pdf->Cell($Header[3][0], $hHeight, $TotalFema, 'LB', 0, 'C', true);
		$pdf->Cell($Header[4][0], $hHeight, $TotalMale+$TotalFema, 'LBR', 0, 'C', true);
		$pdf->Ln();
		$pdf->Ln();
	}
	
	
	function genSalaryGradeReport($pdf){
		$hHeight = 6;
		$rHeight = 4.2;
		$pdf->SetX(47);
		/* SALARY GRADES */
		$pdf->SetFont('Arial','B',9);$pdf->SetTextColor(0,0,0);
		$pdf->Cell(120,4,'NUMBER OF EMPLOYEE BASED ON SALARY GRADES',0,1,'L');
		$Header = [
			[ 7,"#","LTRB",0,"C",],
			[53,"SALARY GRADE","LTRB",0,"C",],
			[20,"MALE","LTRB",0,"C",],
			[20,"FEMALE","LTRB",0,"C",],
			[20,"TOTAL","LTRB",0,"C",],
		];
		$pdf->SetX(48);
		$pdf->SetFillColor(50,50,50);$pdf->SetFont('Arial','',7);$pdf->SetTextColor(255,255,255);
		foreach($Header as $h){$pdf->Cell($h[0],$hHeight,$h[1],$h[2],$h[3],$h[4],true);}
		$pdf->Ln();
		
		$i=1;$TotalMale=0;$TotalFema=0;
		$pdf->SetFont('Arial','',6.5);$pdf->SetTextColor(0,0,0);
		$MySQLi=new MySQLClass();
		$sql="SELECT count(*) FROM (`s_servicerecord` JOIN `tblpositions` ON `s_servicerecord`.`PosID` = `tblpositions`.`PosID`) WHERE `s_servicerecord`.`SRecCurrentAppointment` = '1' AND `s_servicerecord`.`EmpStatus` = 'ACTIVE'";
		for($SG=1;$SG<=31;$SG++){
			$pdf->SetX(48);
			$countMale=$MySQLi->GetArray($sql." AND `tblpositions`.`PosSalGrade` = '".$SG."' AND `s_servicerecord`.`EmpSex` = 'MALE'")[0];
			$countFema=$MySQLi->GetArray($sql." AND `tblpositions`.`PosSalGrade` = '".$SG."' AND `s_servicerecord`.`EmpSex` = 'FEMALE'")[0];
			
			$Total=$countMale+$countFema;
			$TotalMale=$TotalMale+$countMale;
			$TotalFema=$TotalFema+$countFema;
			$pdf->Cell($Header[0][0], $rHeight, $SG.". ", 'LB', 0, 'R');
			$pdf->Cell($Header[1][0], $rHeight, "SG ".$SG, 'LB', 0, 'C');
			$pdf->Cell($Header[2][0], $rHeight, $countMale, 'LB', 0, 'C');
			$pdf->Cell($Header[3][0], $rHeight, $countFema, 'LB', 0, 'C');
			$pdf->Cell($Header[4][0], $rHeight, $Total, 'LBR', 0, 'C');
			$pdf->Ln();
		}
		$pdf->SetFont('Arial','B',7);
		$pdf->SetFillColor(120,120,120);$pdf->SetTextColor(255,255,255);
		$pdf->SetX(48);
		$pdf->Cell($Header[0][0] + $Header[1][0], $hHeight, "GRAND TOTAL", 'LB', 0, 'C', true);
		$pdf->Cell($Header[2][0], $hHeight, $TotalMale, 'LB', 0, 'C', true);
		$pdf->Cell($Header[3][0], $hHeight, $TotalFema, 'LB', 0, 'C', true);
		$pdf->Cell($Header[4][0], $hHeight, $TotalMale+$TotalFema, 'LBR', 0, 'C', true);
		$pdf->Ln();
		$pdf->Ln();
	}
	
	function genAgeGroupReport($pdf){
		$hHeight = 6;
		$rHeight = 4.2;
		$pdf->SetX(47);
		/* AGE GROUP */
		$pdf->SetFont('Arial','B',9);$pdf->SetTextColor(0,0,0);
		$pdf->Cell(120,4,'NUMBER OF EMPLOYEE BASED ON AGE',0,1,'L');
		$Header = [
			[ 7,"#","LTRB",0,"C",],
			[53,"AGE RANGE","LTRB",0,"C",],
			[20,"MALE","LTRB",0,"C",],
			[20,"FEMALE","LTRB",0,"C",],
			[20,"TOTAL","LTRB",0,"C",],
		];
		$pdf->SetX(48);
		$pdf->SetFillColor(50,50,50);$pdf->SetFont('Arial','',7);$pdf->SetTextColor(255,255,255);
		foreach($Header as $h){$pdf->Cell($h[0],$hHeight,$h[1],$h[2],$h[3],$h[4],true);}
		$pdf->Ln();
		
		$i=1;$TotalMale=0;$TotalFema=0;
		$pdf->SetFont('Arial','',6.5);$pdf->SetTextColor(0,0,0);
		$MySQLi=new MySQLClass();
		$sql="SELECT count(*) FROM `s_hrprofile` WHERE `EmpStatus` = 'ACTIVE' AND `SRecCurrentAppointment` = '1'";
		
			
			$countMale=$MySQLi->GetArray($sql." AND `EmpAge` < '20' AND `EmpSex` = 'MALE'")[0];
			$countFema=$MySQLi->GetArray($sql." AND `EmpAge` < '20' AND `EmpSex` = 'FEMALE'")[0];
			$Total=$countMale+$countFema;
			$TotalMale=$TotalMale+$countMale;
			$TotalFema=$TotalFema+$countFema;
			$pdf->SetX(48);
			$pdf->Cell($Header[0][0], $rHeight, "1. ", 'LB', 0, 'R');
			$pdf->Cell($Header[1][0], $rHeight, '20 & Below', 'LB', 0, 'C');
			$pdf->Cell($Header[2][0], $rHeight, $countMale, 'LB', 0, 'C');
			$pdf->Cell($Header[3][0], $rHeight, $countFema, 'LB', 0, 'C');
			$pdf->Cell($Header[4][0], $rHeight, $Total, 'LBR', 0, 'C');
			$pdf->Ln();
			
			
			$countMale=$MySQLi->GetArray($sql." AND `EmpAge` >= '21' AND `EmpAge` <= '35' AND `EmpSex` = 'MALE'")[0];
			$countFema=$MySQLi->GetArray($sql." AND `EmpAge` >= '21' AND `EmpAge` <= '35' AND `EmpSex` = 'FEMALE'")[0];
			$Total=$countMale+$countFema;
			$TotalMale=$TotalMale+$countMale;
			$TotalFema=$TotalFema+$countFema;
			$pdf->SetX(48);
			$pdf->Cell($Header[0][0], $rHeight, "2. ", 'LB', 0, 'R');
			$pdf->Cell($Header[1][0], $rHeight, '21 to 35', 'LB', 0, 'C');
			$pdf->Cell($Header[2][0], $rHeight, $countMale, 'LB', 0, 'C');
			$pdf->Cell($Header[3][0], $rHeight, $countFema, 'LB', 0, 'C');
			$pdf->Cell($Header[4][0], $rHeight, $Total, 'LBR', 0, 'C');
			$pdf->Ln();
		
		
			$countMale=$MySQLi->GetArray($sql." AND `EmpAge` >= '36' AND `EmpAge` <= '45' AND `EmpSex` = 'MALE'")[0];
			$countFema=$MySQLi->GetArray($sql." AND `EmpAge` >= '36' AND `EmpAge` <= '45' AND `EmpSex` = 'FEMALE'")[0];
			$Total=$countMale+$countFema;
			$TotalMale=$TotalMale+$countMale;
			$TotalFema=$TotalFema+$countFema;
			$pdf->SetX(48);
			$pdf->Cell($Header[0][0], $rHeight, "3. ", 'LB', 0, 'R');
			$pdf->Cell($Header[1][0], $rHeight, '36 to 45', 'LB', 0, 'C');
			$pdf->Cell($Header[2][0], $rHeight, $countMale, 'LB', 0, 'C');
			$pdf->Cell($Header[3][0], $rHeight, $countFema, 'LB', 0, 'C');
			$pdf->Cell($Header[4][0], $rHeight, $Total, 'LBR', 0, 'C');
			$pdf->Ln();
		
		
			$countMale=$MySQLi->GetArray($sql." AND `EmpAge` >= '46' AND `EmpAge` <= '55' AND `EmpSex` = 'MALE'")[0];
			$countFema=$MySQLi->GetArray($sql." AND `EmpAge` >= '46' AND `EmpAge` <= '55' AND `EmpSex` = 'FEMALE'")[0];
			$Total=$countMale+$countFema;
			$TotalMale=$TotalMale+$countMale;
			$TotalFema=$TotalFema+$countFema;
			$pdf->SetX(48);
			$pdf->Cell($Header[0][0], $rHeight, "4. ", 'LB', 0, 'R');
			$pdf->Cell($Header[1][0], $rHeight, '46 to 55', 'LB', 0, 'C');
			$pdf->Cell($Header[2][0], $rHeight, $countMale, 'LB', 0, 'C');
			$pdf->Cell($Header[3][0], $rHeight, $countFema, 'LB', 0, 'C');
			$pdf->Cell($Header[4][0], $rHeight, $Total, 'LBR', 0, 'C');
			$pdf->Ln();
		
		
			$countMale=$MySQLi->GetArray($sql." AND `EmpAge` >= '56' AND `EmpAge` <= '65' AND `EmpSex` = 'MALE'")[0];
			$countFema=$MySQLi->GetArray($sql." AND `EmpAge` >= '56' AND `EmpAge` <= '65' AND `EmpSex` = 'FEMALE'")[0];
			$Total=$countMale+$countFema;
			$TotalMale=$TotalMale+$countMale;
			$TotalFema=$TotalFema+$countFema;
			$pdf->SetX(48);
			$pdf->Cell($Header[0][0], $rHeight, "5. ", 'LB', 0, 'R');
			$pdf->Cell($Header[1][0], $rHeight, '56 to 65', 'LB', 0, 'C');
			$pdf->Cell($Header[2][0], $rHeight, $countMale, 'LB', 0, 'C');
			$pdf->Cell($Header[3][0], $rHeight, $countFema, 'LB', 0, 'C');
			$pdf->Cell($Header[4][0], $rHeight, $Total, 'LBR', 0, 'C');
			$pdf->Ln();
		
		
			$countMale=$MySQLi->GetArray($sql." AND `EmpAge` >= '66' AND `EmpSex` = 'MALE'")[0];
			$countFema=$MySQLi->GetArray($sql." AND `EmpAge` >= '66' AND `EmpSex` = 'FEMALE'")[0];
			$Total=$countMale+$countFema;
			$TotalMale=$TotalMale+$countMale;
			$TotalFema=$TotalFema+$countFema;
			$pdf->SetX(48);
			$pdf->Cell($Header[0][0], $rHeight, "6. ", 'LB', 0, 'R');
			$pdf->Cell($Header[1][0], $rHeight, '66 & Above', 'LB', 0, 'C');
			$pdf->Cell($Header[2][0], $rHeight, $countMale, 'LB', 0, 'C');
			$pdf->Cell($Header[3][0], $rHeight, $countFema, 'LB', 0, 'C');
			$pdf->Cell($Header[4][0], $rHeight, $Total, 'LBR', 0, 'C');
			$pdf->Ln();
			
			
		$pdf->SetFont('Arial','B',7);
		$pdf->SetFillColor(120,120,120);$pdf->SetTextColor(255,255,255);
		$pdf->SetX(48);
		$pdf->Cell($Header[0][0] + $Header[1][0], $hHeight, "GRAND TOTAL", 'LB', 0, 'C', true);
		$pdf->Cell($Header[2][0], $hHeight, $TotalMale, 'LB', 0, 'C', true);
		$pdf->Cell($Header[3][0], $hHeight, $TotalFema, 'LB', 0, 'C', true);
		$pdf->Cell($Header[4][0], $hHeight, $TotalMale+$TotalFema, 'LBR', 0, 'C', true);
		$pdf->Ln();
		$pdf->Ln();
	}
	
	
	//Start PDF Builder
	$pdf=new PDF();
	$pdf->AliasNbPages();
  
	/* Cell Look & Feel */
	$pdf->SetFont('Arial','',6.5);
	$pdf->SetTextColor(0,0,0);
	

	$pdf->AddPage('P', 'Long');
	$pdf->SetDisplayMode('fullwidth');
	
	
	
	if($RepSt=="on"){genEmploymentStatusReport($pdf);}genEmploymentStatusReport($pdf);
	if($RepSG=="on"){genSalaryGradeReport($pdf);}genSalaryGradeReport($pdf);
	if($RepAR=="on"){genAgeGroupReport($pdf);}genAgeGroupReport($pdf);

  $pdf->Output();
  ob_end_flush();

?>
