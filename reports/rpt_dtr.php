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
	
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD017'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	
	require_once $_SESSION['path'].'/lib/classes/fpdf/fpdf.php';
	
	/* Get GET Values for Individual DTR Processing */
	$EmpID=isset($_GET['id'])?trim(strip_tags($_GET['id'])):'00000';
	$Year=isset($_GET['yr'])?trim(strip_tags($_GET['yr'])):date('Y');
	$Month=isset($_GET['mo'])?trim(strip_tags($_GET['mo'])):date('m');
	$PayPeriod=isset($_GET['pr'])?trim(strip_tags($_GET['pr'])):0;
	/* Get Values for per Office/SubOffice DTR Processing */
	$isPerOff=isset($_GET['spo'])?trim(strip_tags($_GET['spo'])):'0';
	$SubOffID=isset($_GET['sof'])?trim(strip_tags($_GET['sof'])):'SOOF00101';
	$ApptStID =isset($_GET['aps'])?trim(strip_tags($_GET['aps'])):'AS001';
	
	$MySQLi=new MySQLClass();
	/* Start PDF Builder */
	$pdf=new FPDF();
	$pdf->SetMargins(19.05,12.7);
	/* Configurations */
	$curX=0;$curY=0;$CellHeight=4.8;
	$LogCellWidth=18;
	
	if($isPerOff=="0"){
		$pdf->AddPage('P','letter');
		$pdf->Image('png/DTR_form_001.png',0,0,215.9,279.4,'PNG');
		/* DTR Header */
		
		$pdf->Ln(32);
		
		$Emp=$MySQLi->GetArray("SELECT `EmpID`, CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName FROM `tblemppersonalinfo` WHERE `EmpID`='".$EmpID."';");
		$EmpName=$Emp['EmpName'];
		$pdf->SetFont('Helvetica','B',12);$pdf->SetTextColor(0,0,0);
		$pdf->Cell(195,5,$EmpName." (".$EmpID.")",0,1,'L',false);
		$pdf->Cell(195,5,date('F Y',mktime(0,0,0,$Month,1,$Year)),0,1,'L',false);
		$pdf->Ln(2);
		
		/* DTR Table Header */
		$pdf->SetFont('Helvetica','B',8);$pdf->SetFillColor(150,150,250);$pdf->SetTextColor(255,255,255);
		$pdf->Cell(8,10,'Date',1,0,'C',true);
		$pdf->Cell(9,10,'Day',1,0,'C',true);
		$pdf->Cell(36,$CellHeight,'AM',1,0,'C',true);
		$pdf->Cell(36,$CellHeight,'PM',1,0,'C',true);
		$pdf->Cell(36,$CellHeight,'OT',1,0,'C',true);
		$pdf->Cell($LogCellWidth,$CellHeight,'Lates','LTR',0,'C',true);
		$pdf->Cell($LogCellWidth,$CellHeight,'OT Hrs','LTR',0,'C',true);
		$pdf->Cell($LogCellWidth,$CellHeight,'HrsWrk','LTR',1,'C',true);
		
		$pdf->Cell(17,$CellHeight,'',0,0,'C',false); /*  <--- SPACER  */
			$pdf->Cell($LogCellWidth,$CellHeight,'Time IN',1,0,'C',true);$pdf->Cell($LogCellWidth,$CellHeight,'Time OUT',1,0,'C',true);
			$pdf->Cell($LogCellWidth,$CellHeight,'Time IN',1,0,'C',true);$pdf->Cell($LogCellWidth,$CellHeight,'Time OUT',1,0,'C',true);
			$pdf->Cell($LogCellWidth,$CellHeight,'Time IN',1,0,'C',true);$pdf->Cell($LogCellWidth,$CellHeight,'Time OUT',1,0,'C',true);
			$pdf->Cell($LogCellWidth,$CellHeight,'(in Mins)','LRB',0,'C',true);
			$pdf->Cell($LogCellWidth,$CellHeight,'(in Hrs.)','LRB',0,'C',true);
			$pdf->Cell($LogCellWidth,$CellHeight,'(in Hrs.)','LRB',1,'C',true);
		
		/* Fix Month */
		$Month=($Month > 9) ? $Month : '0'.$Month;
		$DTRstartDate=1;
		$DaysOfMonth=cal_days_in_month(CAL_GREGORIAN, $Month, $Year);
		switch ($PayPeriod) {
			case 1	:	// 1 to 15
						$DTRstartDate=1;
						$DaysOfMonth=15;
						break;
			case 2	:	// 16 to EOM
						$DTRstartDate=16;
						$DaysOfMonth=cal_days_in_month(CAL_GREGORIAN, $Month, $Year);
						break;
			default	:	// 1 to EOM
						$DTRstartDate=1;
						$DaysOfMonth=cal_days_in_month(CAL_GREGORIAN, $Month, $Year);
						break;
		}
		
		$DD=$DaysOfMonth;
		
		$TotalAbsents=$TotalTardiness=$TotalLSec=$TotalHrsWk=$TotalOtHrs=0;
		$TotalLates="00m 00s";
		for($date=$DTRstartDate;$date<=$DD;$date+=1) {
			$LogTime=Array('AMIN' => '', 'AMOUT' => '', 'PMIN' => '', 'PMOUT' => '', 'OTIN' => '', 'OTOUT' => '', 'Lates' => '', 'HrsWrk' => '', 'OTHrs' => '');
			$Date=$date>9?$date:"0".$date;
			$thisDay=$MySQLi->GetArray("SELECT * FROM `tblempdtr` WHERE `DTRID`='DTR".$Year.$Month.$Date.$EmpID."';");
			
			if($isHoliday=$MySQLi->GetArray("SELECT `HoliDescription` FROM `tblholidays` WHERE `HoliDate`='1970-$Month-$Date';")){$LogTime['AMIN']=$LogTime['PMIN']=$isHoliday['HoliDescription'];}
			elseif($isHoliday=$MySQLi->GetArray("SELECT `HoliDescription` FROM `tblholidays` WHERE `HoliDate`='$Year-$Month-$Date';")){$LogTime['AMIN']=$LogTime['PMIN']=$isHoliday['HoliDescription'];}
			else{
				if(substr($thisDay['DTRIN01'],0,4)=="1980"){
					$LeaveDesc=$MySQLi->GetArray("SELECT * FROM `tbldtrdaystatus` WHERE `DayStatusID`='".$thisDay['DayStatusID']."';");
					$LogTime['AMIN']=$LeaveDesc['DayStatusDesc'];$LogTime['AMOUT']="";
				}
				else{
					$LogTime['AMIN']=($thisDay['DTRIN01']!="1970-01-01 00:00:01")?substr($thisDay['DTRIN01'],-8):"";
					$LogTime['AMOUT']=($thisDay['DTROUT01']!="1970-01-01 00:00:01")?substr($thisDay['DTROUT01'],-8):"";
					if($LogTime['AMIN']>"08:10:00"){$TotalTardiness+=1;}
					if(($thisDay['DTRIN01']=="1970-01-01 00:00:01")||($thisDay['DTROUT01']=="1970-01-01 00:00:01")){$TotalAbsents+=0.5;}
				}
				if(substr($thisDay['DTRIN02'],0,4)=="1980"){
					$LeaveDesc=$MySQLi->GetArray("SELECT * FROM `tbldtrdaystatus` WHERE `DayStatusID`='".$thisDay['DayStatusID']."';");
					$LogTime['PMIN']=$LeaveDesc['DayStatusDesc'];$LogTime['PMOUT']="";
				}
				else{
					$LogTime['PMIN']=($thisDay['DTRIN02']!="1970-01-01 00:00:01")?substr($thisDay['DTRIN02'],-8):"";
					$LogTime['PMOUT']=($thisDay['DTROUT02']!="1970-01-01 00:00:01")?substr($thisDay['DTROUT02'],-8):"";
					if($LogTime['PMIN']>"13:10:00"){$TotalTardiness+=1;}
					if(($thisDay['DTRIN02']=="1970-01-01 00:00:01")||($thisDay['DTROUT02']=="1970-01-01 00:00:01")){$TotalAbsents+=0.5;}
				}
				$LogTime['OTIN']=($thisDay['DTRIN03']!="1970-01-01 00:00:01")?substr($thisDay['DTRIN03'],-8):"";
				$LogTime['OTOUT']=($thisDay['DTROUT03']!="1970-01-01 00:00:01")?substr($thisDay['DTROUT03'],-8):"";
			}	
			
		
			$LogTime['Lates']=$thisDay['DTRLates'];
			$Lates=explode(" ",$thisDay['DTRLates']);
			$Lates[0]=isset($Lates[0])?$Lates[0]:0;
			$Lates[1]=isset($Lates[1])?$Lates[1]:0;
			$Lm=intval($Lates[0]);$TotalLSec+=intval($Lates[1])+($Lm*60);
			$LogTime['HrsWrk']=$thisDay['DTRHrsWeek'];$TotalHrsWk+=$thisDay['DTRHrsWeek'];
			$LogTime['OTHrs']=$thisDay['DTROverTime'];$TotalOtHrs+=$thisDay['DTROverTime'];
		
			/* Print Logs per Day  */
			$pdf->SetFont('Courier','',9);$pdf->SetTextColor(0,0,0);
			$Day=strtoupper(date("D", mktime(0, 0, 0, $Month, $Date, $Year)));
			if(($Day=="SAT")||($Day=="SUN")){$pdf->SetFillColor(255,220,220);$pdf->SetTextColor(0,0,0);}
			else{$pdf->SetFillColor(255,255,255);$pdf->SetTextColor(0,0,0);}
			$pdf->Cell(8,$CellHeight,$Date,'LRB',0,'C',true);$pdf->Cell(9,$CellHeight,$Day,'LRB',0,'L',true);
			
			if((strlen($LogTime['AMIN'])>8)&&(strlen($LogTime['PMIN'])>8)){$pdf->Cell($LogCellWidth*6,$CellHeight,$LogTime['AMIN'],'LRB',0,'C',true);}
			else{
				if(strlen($LogTime['AMIN'])>8){$pdf->Cell($LogCellWidth*2,$CellHeight,$LogTime['AMIN'],'LRB',0,'C',true);}
				else{
					$pdf->Cell($LogCellWidth,$CellHeight,$LogTime['AMIN'],'LRB',0,'C',true);
					$pdf->Cell($LogCellWidth,$CellHeight,$LogTime['AMOUT'],'LRB',0,'C',true);
				}
				if(strlen($LogTime['PMIN'])>8){$pdf->Cell($LogCellWidth*2,$CellHeight,$LogTime['PMIN'],'LRB',0,'C',true);}
				else{
					$pdf->Cell($LogCellWidth,$CellHeight,$LogTime['PMIN'],'LRB',0,'C',true);
					$pdf->Cell($LogCellWidth,$CellHeight,$LogTime['PMOUT'],'LRB',0,'C',true);
				}
				$pdf->Cell($LogCellWidth,$CellHeight,$LogTime['OTIN'],'LRB',0,'C',true);
				$pdf->Cell($LogCellWidth,$CellHeight,$LogTime['OTOUT'],'LRB',0,'C',true);
			}
			
			$pdf->Cell($LogCellWidth,$CellHeight,$LogTime['Lates'],'LRB',0,'R',true);
			$pdf->Cell($LogCellWidth,$CellHeight,$LogTime['HrsWrk'],'LRB',0,'C',true);
			$pdf->Cell($LogCellWidth,$CellHeight,$LogTime['OTHrs'],'LRB',1,'C',true);
		}	
		
		$pdf->Ln(2);
		$pdf->SetFont('Helvetica','B',9);$pdf->SetTextColor(0,0,0);
		$pdf->Cell($LogCellWidth-10,$CellHeight,'',0,0,'C');
		$pdf->Cell($LogCellWidth+7,$CellHeight,'Days Absent:',0,0,'C');$pdf->Cell($LogCellWidth,$CellHeight,$TotalAbsents,1,0,'C');
		$pdf->Cell(10,$CellHeight,'',0,0,'C');
		$pdf->Cell($LogCellWidth,$CellHeight,'Tardiness:',0,0,'C');$pdf->Cell($LogCellWidth,$CellHeight,$TotalTardiness,1,0,'C');
		$pdf->Cell(10,$CellHeight,'',0,0,'C');
		$TotalLates=((floor($TotalLSec/60))<9?"0".(floor($TotalLSec/60)):(floor($TotalLSec/60)))."m ".(($TotalLSec%60)<9?"0".($TotalLSec%60):($TotalLSec%60))."s";
		$pdf->Cell($LogCellWidth,$CellHeight,'TOTAL:',0,0,'C');
		$pdf->Cell($LogCellWidth,$CellHeight,$TotalLates,1,0,'C');
		$pdf->Cell($LogCellWidth,$CellHeight,$TotalOtHrs,1,0,'C');
		$pdf->Cell($LogCellWidth,$CellHeight,$TotalHrsWk,1,1,'C');
		
		$pdf->Ln(10);
		$pdf->SetFont('Helvetica','I',9);$pdf->SetTextColor(0,0,0);
		$pdf->MultiCell(0,3.5,'I hereby CERTIFY on my honor that the above is true and correct report of the hours of work performed, record of which was made daily at the time of arrival and departure from Office.',0,'J',false);
		
		$SRecOff=$MySQLi->GetArray("SELECT `AssignedOfficeID` FROM `tblempservicerecords` WHERE `EmpID`='$EmpID' AND `SRecIsGov`='YES' AND `SRecCurrentAppointment`=1;");
		$AssignedOfficeID=$SRecOff['AssignedOfficeID'];
		$Boss=$MySQLi->GetArray("SELECT `tblemppersonalinfo`.`EmpID`, CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ', `tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS BossName, `tblpositions`.`PosDesc` FROM (`tblemppersonalinfo` JOIN `tblempservicerecords` ON `tblemppersonalinfo`.`EmpID`=`tblempservicerecords`.`EmpID`) JOIN `tblpositions` ON `tblempservicerecords`.`PosID`=`tblpositions`.`PosID` WHERE `tblemppersonalinfo`.`UserGroupID`='USRGRP009' AND `tblempservicerecords`.`AssignedOfficeID`='$AssignedOfficeID' AND `tblempservicerecords`.`SRecIsGov`='YES' AND `tblempservicerecords`.`SRecCurrentAppointment`=1;");
		
		$pdf->Ln(4);
		$pdf->SetFont('Helvetica','B',9);$pdf->SetTextColor(0,0,0);
		$pdf->Cell(5,$CellHeight,'',0,0,'C');$pdf->Cell(75,$CellHeight,'','B',0,'C');$pdf->Cell(19,$CellHeight,'',0,0,'C');$pdf->Cell(75,$CellHeight,'','B',1,'C');
		$pdf->Cell(5,$CellHeight,'',0,0,'C');$pdf->Cell(75,$CellHeight,$EmpName,0,0,'C');$pdf->Cell(19,$CellHeight,'',0,0,'C');$pdf->Cell(75,$CellHeight,$Boss['BossName'],0,1,'C');
		$pdf->SetFont('Helvetica','I',8);$pdf->SetTextColor(0,0,0);
		$pdf->Cell(5,3,'',0,0,'C');$pdf->Cell(75,3,getEmpPosition($EmpID),0,0,'C');$pdf->Cell(19,3,'',0,0,'C');$pdf->Cell(75,3,$Boss['PosDesc'],0,1,'C');
			
	}

	else{}
	
	$pdf->Output();
	ob_end_flush();
?>