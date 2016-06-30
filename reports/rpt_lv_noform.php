<?php 
	ob_start();
	session_start();
	
	
	function getEmpPosition($id){
		$MySQLi=new MySQLClass();
		$Position=$MySQLi->GetArray("SELECT `tblpositions`.`PosDesc` FROM (`tblempservicerecords` JOIN `tblpositions` ON `tblempservicerecords`.`PosID`=`tblpositions`.`PosID`) JOIN `tblsuboffices` ON `tblempservicerecords`.`MotherOfficeID`=`tblsuboffices`.`SubOffID` WHERE `tblempservicerecords`.`EmpID`='".$id."' AND `tblempservicerecords`.`SRecCurrentAppointment`='1';");
		return $Position['PosDesc'];
	}
	
	function getEmpName($id){
		$MySQLi=new MySQLClass();
		$Personal=$MySQLi->GetArray("SELECT CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName FROM `tblemppersonalinfo` WHERE `tblemppersonalinfo`.`EmpID`='".$id."';");
		return $Personal['EmpName'];
	}
	
	
	
	
	
	
	require_once $_SESSION['path'].'/lib/classes/fpdf/fpdf.php';
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD019'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	
	
	
	$LivAppID=isset($_GET['id'])?trim(strip_tags($_GET['id'])):'00000';
	
	
	
	class PDF extends FPDF{
	// Page header
		function Header(){
			//$this->Image('png/Leave_form_001.png',0,0,216,330,'PNG');
		}

	// Page footer
		function Footer(){
			// Position at 1.8 cm from bottom
			$this->SetY(-18);
			$this->SetFont('Times','BI',10);$this->SetTextColor(0,0,0);
			$this->MultiCell(0,3.5,'',0,'C',false);
			$this->SetFont('Helvetica','',8.5);
			$this->MultiCell(0,3,'',0,'C',false);
			$this->MultiCell(0,3,'',0,'C',false);
		}
	}


	$pdf = new PDF();
	$pdf->SetMargins(25.4,25.4,25.4);
	$pdf->AddPage('P','long');
	$pdf->SetTextColor(0,0,0);
	$MySQLi=new MySQLClass();
	
	$LivInfo=$MySQLi->GetArray("SELECT `EmpID`, `LeaveTypeID`, `LivAppTypeDetail`, `LivAppDays`, DATE_FORMAT(`LivAppFiledDate`,'%b %d, %Y') AS LivAppFiledDate, DATE_FORMAT(`LivAppIncDateFrom`,'%b %d, %Y') AS LivAppDateFrom, `LivAppIncDateFrom`, `LivAppIncDayTimeFrom`,DATE_FORMAT(`LivAppIncDateTo`,'%b %d, %Y') AS LivAppDateTo, `LivAppIncDayTimeTo`,`LivAppStatus`, `LivAppNotedRemarks`, `LivAppNotedBy`, `LivAppCheckedBy`, `LivAppApprovedBy`, `LivAppApprovedDays`, `LivAppApprovedRemarks`, `LivCredID` FROM `tblempleaveapplications` WHERE `LivAppID` = '".$LivAppID."' ;");
	$ApptInfo=$MySQLi->GetArray("SELECT `tblpositions`.`PosDesc`, `tblpositions`.`PosSalGrade`, `tblempservicerecords`.`SRecSalGradeStep`, `tblsuboffices`.`SubOffCode`, `tblsuboffices`.`SubOffName`, `tblempservicerecords`.`SRecFromYear` FROM (`tblempservicerecords` JOIN `tblpositions` ON `tblempservicerecords`.`PosID`=`tblpositions`.`PosID`) JOIN `tblsuboffices` ON `tblempservicerecords`.`MotherOfficeID`=`tblsuboffices`.`SubOffID` WHERE `tblempservicerecords`.`EmpID`='".$LivInfo['EmpID']."' AND `tblempservicerecords`.`SRecCurrentAppointment`='1';");
	// $SalGrdID=($ApptInfo['SRecFromYear'].($ApptInfo['PosSalGrade']<10?"0".$ApptInfo['PosSalGrade']:$ApptInfo['PosSalGrade'])."0".$ApptInfo['SRecSalGradeStep']);
	// $SalGrd=$MySQLi->GetArray("SELECT `SalGrdValue` FROM `tblsalgrade` WHERE `SalGrdID`='".$SalGrdID."' LIMIT 1;");
	//Employee details
	// $pdf->SetFont('Arial','B',10);
		// $pdf->SetXY(27,42.5);$pdf->Cell(80,4,$ApptInfo['SubOffCode'],0,1,'L',false);
		// $pdf->SetXY(117,42.5);$pdf->Cell(80,4,getEmpName($LivInfo['EmpID']),0,1,'L',false);
		// $pdf->SetXY(27,57);$pdf->Cell(55,4,strtoupper($LivInfo['LivAppFiledDate']),0,1,'L',false);
		// $pdf->SetXY(89,57);$pdf->Cell(77,4,getEmpPosition($LivInfo['EmpID']),0,1,'L',false);
		// $pdf->SetXY(176,57);$pdf->Cell(20,4,number_format($SalGrd['SalGrdValue'],2),0,1,'L',false);
	//Leave Type and details
	// if($LivInfo['LeaveTypeID']=="LT01"){
		// if($LivInfo['LivAppTypeDetail']=="IN"){$pdf->SetXY(123.3,103);$pdf->SetFont('Arial','B',12);$pdf->Cell(5,4,'X',0,1,'L',false);}
		// else{$pdf->SetXY(123.3,107.8);$pdf->SetFont('Arial','B',12);$pdf->Cell(5,4,'X',0,1,'L',false);}
		// $pdf->SetXY(27,98);
	// }
	// elseif($LivInfo['LeaveTypeID']=="LT02"){
		// if($LivInfo['LivAppTypeDetail']=="IN"){$pdf->SetXY(123.3,121.7);$pdf->SetFont('Arial','B',12);$pdf->Cell(5,4,'X',0,1,'L',false);}
		// else{$pdf->SetXY(123.3,126.3);$pdf->SetFont('Arial','B',12);$pdf->Cell(5,4,'X',0,1,'L',false);}
		// $pdf->SetXY(27,103);
	// }
	// else{$pdf->SetXY(34,120);$pdf->SetFont('Arial','B',10);$pdf->Cell(60,4,'Extra Leave','B',1,'C',false);$pdf->SetXY(27,108);}
	// $pdf->SetFont('Arial','B',12);$pdf->Cell(5,4,'X',0,1,'L',false);
	//Number of Days and Inclusive dates
	// $pdf->SetFont('Arial','B',10);
		// $pdf->SetXY(50,146);$pdf->Cell(15,5,$LivInfo['LivAppDays'],1,1,'C',false);
	// if($LivInfo['LivAppDays']==1){$InclusiveDate=strtoupper($LivInfo['LivAppDateFrom']);}
	// elseif($LivInfo['LivAppDays']<1){$InclusiveDate=strtoupper($LivInfo['LivAppDateFrom'])." ".$LivInfo['LivAppIncDayTimeFrom'];}
	// else{$InclusiveDate=strtoupper($LivInfo['LivAppDateFrom'])." ".$LivInfo['LivAppIncDayTimeFrom']." to ".strtoupper($LivInfo['LivAppDateTo'])." ".$LivInfo['LivAppIncDayTimeTo'];}
		// $pdf->SetXY(29,162);$pdf->Cell(70,4,$InclusiveDate,'B',1,'C',false);
	// $pdf->SetFont('Arial','B',12);$pdf->SetXY(116.7,151.5);$pdf->Cell(5,4,'X',0,1,'L',false);
	// $pdf->SetFont('Arial','B',10);
		//$pdf->SetXY(132.5,167);$pdf->Image('../signatures/'.$LivInfo['EmpID'].'.png',116,155,88,20,'PNG');$pdf->Cell(55,4,getEmpName($LivInfo['EmpID']),'T',0,'C',false);
	// $pdf->SetFont('Arial','I',7);
		// $pdf->SetXY(132.5,170);$pdf->Cell(55,4,getEmpPosition($LivInfo['EmpID']),0,0,'C',false);
	// $pdf->SetFont('Arial','',7);
		// $pdf->SetXY(132.5,173);$pdf->Cell(55,4,'(Applicant)',0,0,'C',false);
	//Approval
	// $pdf->SetFont('Arial','B',12);$pdf->SetXY(116.7,209);$pdf->Cell(5,4,'X',0,1,'L',false);
	$pdf->SetFont('Arial','',8);
	
	$LivCred=$MySQLi->GetArray("SELECT `LivCredDateFrom` FROM `tblempleavecredits` WHERE `LivCredID`='".$LivInfo['LivCredID']."';");
	/* Get Current/Last Leave Credit Balance */
	$VLCredit=$SLCredit=0;
	$result=$MySQLi->sqlQuery("SELECT `LeaveTypeID`, `LivCredBalance`, DATE_FORMAT(`LivCredDateFrom`,'%b %d, %Y') AS AsOfDate FROM `tblempleavecredits` WHERE `EmpID` = '".$LivInfo['EmpID']."' AND `LivCredDateFrom`<'".$LivCred['LivCredDateFrom']."' ORDER BY `LivCredDateFrom` ASC;");
	$VL_credit=$SL_credit=$PL_credit=$OL_credit=$TL_credit=0;
	while($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
		$VL_credit=($records['LeaveTypeID']=="LT01")?$records['LivCredBalance']:$VL_credit;
		$SL_credit=($records['LeaveTypeID']=="LT02")?$records['LivCredBalance']:$SL_credit;
		$AsOfDate=$records['AsOfDate'];
	}$TLCredit=$VL_credit+$SL_credit;
	$VLCredit=$VL_credit;$SLCredit=$SL_credit;
	
		$pdf->SetXY(25,206);$pdf->Cell(75,4,'Certification of Leave Credits as of',0,0,'C',false);
	$pdf->SetFont('Arial','B',10);
		$pdf->SetXY(40,210);$pdf->Cell(45,4,$AsOfDate,'B',0,'C',false);
	$pdf->SetFont('Arial','BI',8);
		$pdf->SetXY(25,216);$pdf->Cell(25,4,'Vacation Leave','B',0,'C',false);$pdf->Cell(25,4,'Sick Leave','B',0,'C',false);$pdf->Cell(25,4,'TOTAL','B',0,'C',false);
	
	$ApprovedVL=(($LivInfo['LeaveTypeID']=='LT01')?number_format($LivInfo['LivAppApprovedDays'],2):'0.00');
	$ApprovedSL=(($LivInfo['LeaveTypeID']=='LT02')?number_format($LivInfo['LivAppApprovedDays'],2):'0.00');
	$pdf->SetFont('Courier','',8);
		$pdf->SetXY(25,220);$pdf->Cell(20,4,number_format($VLCredit,2),0,0,'R',false);$pdf->Cell(5,4,'',0,0,'R',false);$pdf->Cell(20,4,number_format($SLCredit,2),0,0,'R',false);$pdf->Cell(5,4,'',0,0,'R',false);$pdf->Cell(20,4,number_format($TLCredit,2),0,0,'R',false);$pdf->Cell(5,4,'',0,0,'R',false);
		$pdf->SetXY(25,224);$pdf->Cell(20,3.5,$ApprovedVL,0,0,'R',false);$pdf->Cell(5,3.5,'',0,0,'R',false);$pdf->Cell(20,3.5,$ApprovedSL,0,0,'R',false);$pdf->Cell(5,3.5,'',0,0,'R',false);$pdf->Cell(20,3.5,number_format($ApprovedVL+$ApprovedSL,2),0,0,'R',false);$pdf->Cell(5,3.5,'',0,0,'R',false);
	
	$pdf->SetFont('Courier','B',8);
		$pdf->SetXY(25,228);$pdf->Cell(20,4,number_format($VLCredit-$ApprovedVL,2),'TB',0,'R',false);$pdf->Cell(5,4,'','TB',0,'R',false);$pdf->Cell(20,4,number_format($SLCredit-$ApprovedSL,2),'TB',0,'R',false);$pdf->Cell(5,4,'','TB',0,'R',false);$pdf->Cell(20,4,number_format(($VLCredit-$ApprovedVL)+($SLCredit-$ApprovedSL),2),'TB',0,'R',false);$pdf->Cell(5,4,'','TB',0,'R',false);
	// $pdf->SetFont('Helvetica','',8);	
		// $pdf->SetXY(125,218);$pdf->Cell(70,4,'','B',0,'R',false);
		// $pdf->SetXY(125,222);$pdf->Cell(70,4,'','B',0,'R',false);
		// $pdf->SetXY(125,226);$pdf->Cell(70,4,'','B',0,'R',false);
	// $pdf->SetFont('Arial','B',10);
		//$pdf->SetXY(35,240);$pdf->Image('../signatures/'.$LivInfo['LivAppCheckedBy'].'.png',18.5,226,88,20,'PNG');$pdf->Cell(55,4,getEmpName($LivInfo['LivAppCheckedBy']),'T',0,'C',false);$pdf->SetXY(132.5,240);$pdf->Image('../signatures/'.$LivInfo['LivAppNotedBy'].'.png',116,228,88,20,'PNG');$pdf->Cell(55,4,getEmpName($LivInfo['LivAppNotedBy']),'T',0,'C',false);
	// $pdf->SetFont('Arial','I',7);
		// $pdf->SetXY(35,243);$pdf->Cell(55,4,getEmpPosition($LivInfo['LivAppCheckedBy']),0,0,'C',false);$pdf->SetXY(132.5,243);$pdf->Cell(55,4,getEmpPosition($LivInfo['LivAppNotedBy']),0,0,'C',false);
	
		$pdf->SetXY(30,261);$pdf->SetFont('Courier','B',10);$pdf->Cell(15,4,number_format($LivInfo['LivAppApprovedDays'],2),'B',0,'C',false);$pdf->SetFont('Helvetica','',8);$pdf->Cell(20,4,'day(s) with pay',0,0,'L',false);
		$pdf->SetXY(30,265);$pdf->SetFont('Courier','B',10);$pdf->Cell(15,4,number_format($LivInfo['LivAppDays']-$LivInfo['LivAppApprovedDays'],2),'B',0,'C',false);$pdf->SetFont('Helvetica','',8);$pdf->Cell(20,4,'day(s) without pay',0,0,'L',false);
		$pdf->SetXY(30,269);$pdf->SetFont('Courier','B',10);$pdf->Cell(15,4,'','B',0,'C',false);$pdf->SetFont('Helvetica','',8);$pdf->Cell(20,4,'others (specify)',0,0,'L',false);
		// $pdf->SetXY(125,261);$pdf->Cell(70,4,'','B',0,'R',false);
		// $pdf->SetXY(125,265);$pdf->Cell(70,4,'','B',0,'R',false);
		// $pdf->SetXY(125,269);$pdf->Cell(70,4,'','B',0,'R',false);	
	// $pdf->SetFont('Arial','B',10);
		//$pdf->SetXY(132.5,303);$pdf->Image('../signatures/'.$LivInfo['LivAppApprovedBy'].'.png',116,291,88,20,'PNG');$pdf->Cell(55,4,getEmpName($LivInfo['LivAppApprovedBy']),'T',0,'C',false);
	// $pdf->SetFont('Arial','I',7);
		// $pdf->SetXY(132.5,306);$pdf->Cell(55,4,getEmpPosition($LivInfo['LivAppApprovedBy']),0,0,'C',false);
	
	
	
	
	
	
	
	$pdf->Output();
	
	ob_end_flush();
?>