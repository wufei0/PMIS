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
	
	
	
	
	$EmpID=isset($_GET['id'])?trim(strip_tags($_GET['id'])):'00000';
	$MONTHS=Array('','JANUARY','FEBRUARY','MARCH','APRIL','MAY','JUNE','JULY','AUGUST','SEPTEMBER','OCTOBER','NOVEMBER','DECEMBER');
	
	$fPersonnel = new PersonnelFunctions();
	$MySQLi=new MySQLClass();
	$Emp=$MySQLi->GetArray("SELECT CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName, `EmpBirthDay`, `EmpBirthMonth`, `EmpBirthYear`, `EmpBirthPlace` FROM `tblemppersonalinfo` WHERE `EmpID` = '".$EmpID."' ;");
	$EmpName=$Emp['EmpName'];
	$EmpBirthDate=$MONTHS[intval($Emp['EmpBirthMonth'])]." ".$Emp['EmpBirthDay'].", ".$Emp['EmpBirthYear'];
	$EmpBirthPlace=$Emp['EmpBirthPlace'];
	
	
	
	
	class PDF extends FPDF{
	// Page header
		function Header(){
			$this->Image('png/pglulogo.jpg',25.4,12.7,30,30,'JPG');
			$this->Image((file_exists('../photos/'.$GLOBALS['EmpID'].'.jpg')?'../photos/'.$GLOBALS['EmpID'].'.jpg':'../photos/no_photo.jpg'),244.8,12.7,30,30,'JPG');
			//$this->Image('png/pglulogo.jpg',285.2,15,30,30,'JPG');
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
			$this->MultiCell(0,6,'SERVICE RECORD',0,'C',false);
			$this->MultiCell(0,10,'',0,'C',false); // Spacer
			$this->SetFont('Helvetica','B',11);
			$this->Cell(90,5,$GLOBALS['EmpName'],1,0,'L',false);$this->Cell(4.63,5,'',0,0,'L',false);
			$this->Cell(90,5,$GLOBALS['EmpBirthDate'],1,0,'L',false);$this->Cell(4.63,5,'',0,0,'L',false);
			$this->Cell(90,5,$GLOBALS['EmpBirthPlace'],1,1,'L',false);
			$this->SetFont('Helvetica','IB',8);
			$this->Cell(90,4,'Name of Employee',0,0,'L',false);$this->Cell(4.63,6,'',0,0,'L',false);$this->Cell(90,4,'Date of Birth',0,0,'L',false);$this->Cell(4.63,4.5,'',0,0,'L',false);$this->Cell(90,4,'Place of Birth',0,1,'L',false);
			$this->MultiCell(0,2,'',0,'C',false); // Spacer
			$this->SetFont('Helvetica','',8);
			$this->MultiCell(0,3.5,'This is to certify that the employee herein above actually rendered services in this Office as shown by the service record below each line of which is supported by appointment and other papers actually issued by this Office and approved by the authorities concerned.',0,'L',false);
			$this->MultiCell(0,1,'',0,'C',false); // Spacer
			//$this->Cell(279.38,1,'',1,1,'C',false);
			$this->SetFont('Helvetica','B',8);
			$this->Cell(40,7,'SERVICE','LTRB',0,'C',false);
			$this->Cell(60,10.5,'POSITION',1,0,'C',false);
			$this->Cell(23,10.5,'STATUS',1,0,'C',false);
			$this->Cell(33,10.5,'SALARY (In Peso)',1,0,'C',false);
			$this->Cell(80,10.5,'OFFICE/ENTITY/STATION/PLACE OF ASSIGNMENT',1,0,'C',false);
			$this->Cell(18,3.5,'LEAVE','TLR',2,'C',false);
			$this->Cell(18,3.5,'WITHOUT','LR',2,'C',false);
			$this->Cell(18,3.5,'PAY','LRB',0,'C',false);
			$this->SetXY($this->GetX(),$this->GetY()-7);
			$this->Cell(25.38,10.5,'REMARKS',1,1,'C',false);
			$this->SetY($this->GetY()-3.5);
			$this->Cell(20,3.5,'From','LRB',0,'C',false);
			$this->Cell(20,3.5,'To','LRB',1,'C',false);
			
			$this->SetFont('Helvetica','B',22);
			$this->SetXY(274.8,22.4);$this->Cell(30,10,$GLOBALS['EmpID'],'TLRB',2,'C',false);
			$this->SetFont('Helvetica','IB',8);
			$this->Cell(30,3.5,'ID Number','TLRB',1,'C',false);
			
			$this->SetXY(25.4,76.2);
		}

	// Page footer
		function Footer(){
			// Position at 1.8 cm from bottom
			$this->SetY(-35);
			$this->MultiCell(0,1,'','B','C',false); // Spacer
			$this->SetFont('Helvetica','',8);$this->SetTextColor(0,0,0);
			$this->MultiCell(0,3.5,'Issued upon request of Mr./Mrs./Ms. '.$GLOBALS['EmpName'].' this '.date('d F, Y').' at San Fernando City, La Union.',0,'L',false);
			$this->SetX(219.7);
			$this->Cell(60,4,'',0,2,'C',false);
			$this->SetFont('Helvetica','B',8);
			$this->Cell(60,4,'CERTIFIED CORRECT:',0,2,'L',false);
			$this->SetX(244.8);
			$this->SetFont('Helvetica','B',10);
			$this->Cell(60,4,'','B',2,'C',false);
			$this->Cell(60,4,'MARY JANE S. BALANCIO',0,2,'C',false);
			$this->SetFont('Helvetica','I',8);
			$this->Cell(60,3,'Administrative Officer V',0,1,'C',false);
		}
	}


	$pdf = new PDF();
	$pdf->SetTitle('PMIS - Service Record ('.$GLOBALS['EmpName'].' '.$GLOBALS['EmpID'].')');
	$pdf->SetAuthor('PMIS - '.$fPersonnel->getEmpName($_SESSION['user']).'');
	$pdf->SetMargins(25.4,25.4,25.4);
	$pdf->AddPage('L','long');
	$pdf->SetAutoPageBreak('ON',35);
	$pdf->SetTextColor(0,0,50);
	$MySQLi=new MySQLClass();
	
	$result=$MySQLi->sqlQuery("SELECT `SRecID`, `SRecFromYear`, CONCAT_WS('-',`SRecFromDay`, UPPER(MID(MONTHNAME(CONCAT_WS('-', `SRecFromYear`,`SRecFromMonth`,`SRecFromDay`)), 1, 3)),`SRecFromYear`) AS SRecFrom, CONCAT_WS('-',`SRecToDay`, UPPER(MID(MONTHNAME(CONCAT_WS('-', `SRecToYear`,`SRecToMonth`,`SRecToDay`)), 1, 3)),`SRecToYear`) AS SRecTo,`SRecToYear`,`SRecToMonth`,`SRecToDay`, `PosID`,`SRecSalGradeStep`,`SRecEmployer`,`MotherOfficeID`,`SRecOffice`,`SRecJobDesc`,`SalUnitID`,`SRecSalary`,`ApptStID`,`SRecPosition`,`SRecIsGov`,`SRecRemarks` FROM `tblempservicerecords` WHERE `EmpID`='".$EmpID."' ORDER BY `SRecFromYear` ASC, `SRecFromMonth` ASC;");
	$n=1;
	$CurrX=$pdf->GetX();$CurrY=$pdf->GetY();
	$pdf->SetFont('cOURIER','',7);
	$RowHt=4.5;
	while($records=mysqli_fetch_array($result, MYSQLI_BOTH)) {
		// Inclusive Dates
		$SRecTo=$records['SRecToYear'].$records['SRecToMonth'].$records['SRecToDay'];
		$pdf->Cell(20,$RowHt,$records['SRecFrom'],'BL',0,'C',false);
		$pdf->Cell(20,$RowHt,(date('Ymd')<$SRecTo)?"PRESENT":$records['SRecTo'],'BL',0,'C',false);
		// Get Position
		if($records['SRecIsGov']=="YES"){
			$positions=mysqli_fetch_array($MySQLi->sqlQuery("SELECT `PosDesc`, `PosSalGrade` FROM `tblpositions` WHERE `PosID`='".$records['PosID']."';"), MYSQLI_BOTH);
			$PosSalGrade=$positions['PosSalGrade']>9?$positions['PosSalGrade']:"0".$positions['PosSalGrade'];
			$SRecSalGradeStep=(($records['SRecSalGradeStep']>0)&&($records['SRecSalGradeStep']<9))?trim($records['SRecSalGradeStep']):'X';
			//$SRecSalGradeStep=intval($records['SRecSalGradeStep']);
			// if($records['PosID']!="PO000"){
				// $PosTitleJD=$positions['PosDesc'];
				// $salary=mysqli_fetch_array($MySQLi->sqlQuery("SELECT `SalGrdValue` FROM `tblsalgrade` WHERE `SalGrdID`='".$records['SRecFromYear'].$PosSalGrade."0".$SRecSalGradeStep."';"), MYSQLI_BOTH);
				// $SRecSalary=$salary['SalGrdValue'];
			// }
			if($records['PosID']!="PO000"){
				$PosTitleJD=$positions['PosDesc'];
				$SalGrdID="SG".$records['SRecFromYear'].$PosSalGrade;
				$SRecSalary=$MySQLi->GetArray("SELECT `Step".$SRecSalGradeStep."` FROM `tblsalarygrade` WHERE `SGID`='".$SalGrdID."' LIMIT 1;")[0];
			}
			else{$PosTitleJD=$records['SRecPosition'];$SRecSalary=$records['SRecSalary'];$PosSalGrade="";$SRecSalGradeStep="";}
		}
		else{$PosTitleJD=$records['SRecJobDesc'];}
		$pdf->Cell(60,$RowHt,$PosTitleJD,'BL',0,'L',false);
		// Get Appointment Status
		$appstatuses=mysqli_fetch_array($MySQLi->sqlQuery("SELECT `ApptStDesc` FROM `tblapptstatus` WHERE `ApptStID`='".$records['ApptStID']."';"), MYSQLI_BOTH);
		$pdf->Cell(23,$RowHt,$appstatuses['ApptStDesc'],'BL',0,'L',false);
		// Get Salary
		$UnitCode=mysqli_fetch_array($MySQLi->sqlQuery("SELECT `SalUnitCode` FROM `tblsalaryunits` WHERE `SalUnitID`='".$records['SalUnitID']."';"), MYSQLI_BOTH);
		$SalUnitCode=($UnitCode['SalUnitCode']!="")?$UnitCode['SalUnitCode']:"";
		$pdf->Cell(18,$RowHt,number_format($SRecSalary,2),'BL',0,'R',false);$pdf->Cell(15,$RowHt,$SalUnitCode,'B',0,'L',false);
		// Get Employer/Office
		if($records['MotherOfficeID']!="SO000"){
			$offices=mysqli_fetch_array($MySQLi->sqlQuery("SELECT `SubOffCode` FROM `tblsuboffices` WHERE `SubOffID`='".$records['MotherOfficeID']."';"), MYSQLI_BOTH);
			$SRecEmployer=isset($offices['SubOffCode'])?$records['SRecEmployer']." - ".$offices['SubOffCode']:$records['SRecEmployer'];
		}
		else{$SRecEmployer=$records['SRecOffice'];}
		$pdf->Cell(80,$RowHt,$SRecEmployer,'BLR',0,'L',false);
		
		$pdf->Cell(18,$RowHt,'','BLR',0,'L',false);
		$pdf->Cell(25.38,$RowHt,$records['SRecRemarks'],'BLR',1,'L',false);
	}
	
	$pdf->SetFont('Helvetica','BI',7);
	$pdf->Cell(279.38,$RowHt,'*  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *   N  O  T  H  I  N  G     F  O  L  L  O  W  S   *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *',1,1,'C',false);
	
	
	
	
	$pdf->Output('PMIS_Service_Record_'.str_replace(array(',','.',' '), "",$EmpName).'_'.$EmpID.'.pdf','I');
	
	ob_end_flush();
?>