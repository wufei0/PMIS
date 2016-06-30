<?php
	ob_start();
	session_start();
	
	
	
	
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
		/* DTR Header */
		$pdf->SetFont('Helvetica','B',16);$pdf->SetTextColor(0,0,0);
		$pdf->Cell(179,5,'Provincial Covernment of La Union',0,1,'C',false);
		$pdf->SetFont('Helvetica','B',12);$pdf->SetTextColor(0,0,0);
		$pdf->Cell(179,5,'City of San Fernando, La Union',0,1,'C',false);
		$pdf->SetFont('Helvetica','B',16);$pdf->SetTextColor(0,0,0);
		$pdf->Cell(179,10,'Daily Time Record','B',1,'C',false);
		$pdf->Ln(4);
		
		$result=$MySQLi->sqlQuery("SELECT `EmpID`, CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName FROM `tblemppersonalinfo` WHERE `EmpID`='".$EmpID."';");
		$emp=mysqli_fetch_array($result, MYSQLI_BOTH);
		$pdf->SetFont('Helvetica','B',12);$pdf->SetTextColor(0,0,0);
		$pdf->Cell(195,5,$emp['EmpName']." (".$EmpID.")",0,1,'L',false);
		$pdf->Cell(195,5,date('F Y',mktime(0,0,0,$Month,1,$Year)),0,1,'L',false);
		$pdf->Ln(4);
		
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
			$pdf->Cell($LogCellWidth,$CellHeight,'(in Mins)','LRB',0,'C',true);$pdf->Cell($LogCellWidth,$CellHeight,'(HH:MM:SS)','LRB',0,'C',true);$pdf->Cell($LogCellWidth,$CellHeight,'(HH:MM:SS)','LRB',1,'C',true);
		
		/* Fix Month */
		$Month=($Month > 9) ? $Month : '0'.$Month;
		$DTRstartDate=1;
		$DaysOfMonth=cal_days_in_month(CAL_GREGORIAN, $Month, $Year);
		switch ($PayPeriod) {
			case 1	:	$DTRstartDate=1;
						$DaysOfMonth=15;
						break;
			case 2	:	$DTRstartDate=16;
						$DaysOfMonth=cal_days_in_month(CAL_GREGORIAN, $Month, $Year);
						break;
			default	:	$DTRstartDate=1;
						$DaysOfMonth=cal_days_in_month(CAL_GREGORIAN, $Month, $Year);
						break;
		}
		
		$DD=$DaysOfMonth;
		/* if (($Year == date('Y'))&&($Month == date('m'))) { $DD=date('j'); } */
		$td_att_am1=$td_att_am2=$td_att_pm1=$td_att_pm2=$td_att_ot1=$td_att_ot2="";
		
		for($Date=$DTRstartDate;$Date<=$DD;$Date+=1) {
			$LogTime=Array('AMIN' => '', 'AMOUT' => '', 'PMIN' => '', 'PMOUT' => '', 'OTIN' => '', 'OTOUT' => '', 'HrsWrk' => '', 'OTHrs' => '');
			/*  YYYY-MM-DD HH:MM:SS --->  $Year-$Month-$Date $Hour:$Minutes:$Second */
			$sql="SELECT SQL_NO_CACHE `BioLogTime` FROM `tblbiometrics` WHERE (`BioLogTime` LIKE '".$Year."-".$Month."-".($Date > 9 ? $Date : "0".$Date)."%'  AND `EmpID`='".$EmpID."');";/*  `BioLogTime` ASC;"; */
			$result=$MySQLi->sqlQuery($sql);
			$computeLateUnderTime=false;
			$LateAM=$UnderAM=$LatePM=$UnderPM=0;
			
			while ($logs=mysqli_fetch_array($result, MYSQLI_BOTH)) {
				$log_date_time=explode(" ",$logs['BioLogTime']);
				$log_date=explode("-",$log_date_time[0]);
				$Y=$log_date[0]; $n=$log_date[1]; $j=$log_date[2];
				$log_time=explode(":",$log_date_time[1]);
				$H=$log_time[0]; $i=$log_time[1]; $s=$log_time[2];
				
				$BioLogTime=mktime($H,$i,$s,intval($n),intval($j),intval($Y));
				
				/* $BioLogTime=strtotime($logs['BioLogTime']); */
				if(($BioLogTime >= mktime(0,0,0,intval($n),intval($j),intval($Y))) && ($BioLogTime <= mktime(10,59,59,intval($n),intval($j),intval($Y)))) { 
					$LogTime['AMIN']=date('h:i:s', $BioLogTime);
					$td_att_am1='';
					$LateAM=$BioLogTime - mktime(8,0,0,intval($n),intval($j),intval($Y));
					$LateAM=($LateAM < 0) ? 0 : $LateAM;
					$computeLateUnderTime=true;
				}
				else if (($BioLogTime >= mktime(11,0,0,intval($n),intval($j),intval($Y))) && ($BioLogTime <= mktime(12,29,59,intval($n),intval($j),intval($Y)))) { 
					$LogTime['AMOUT']=date('h:i:s', $BioLogTime);
					$td_att_am2='';
					$UnderAM=mktime(12,0,0,intval($n),intval($j),intval($Y)) - $BioLogTime;
					$UnderAM=($UnderAM < 0) ? 0 : $UnderAM;
					$computeLateUnderTime=true;
				}
				else if (($BioLogTime >= mktime(12,30,0,intval($n),intval($j),intval($Y))) && ($BioLogTime <= mktime(15,59,59,intval($n),intval($j),intval($Y)))) { 
					$LogTime['PMIN']=date('h:i:s', $BioLogTime);
					$td_att_pm1='';
					$LatePM=$BioLogTime - mktime(13,0,0,intval($n),intval($j),intval($Y));
					$LatePM=($LatePM < 0) ? 0 : $LatePM;
					$computeLateUnderTime=true;
				}
				else if (($BioLogTime >= mktime(16,0,0,intval($n),intval($j),intval($Y))) && ($BioLogTime <= mktime(23,59,59,intval($n),intval($j),intval($Y)))) { 
					if ($LogTime['PMOUT'] == '') { 
						$LogTime['PMOUT']=date('h:i:s', $BioLogTime); 
						$td_att_pm2='';
						$UnderPM=mktime(17,0,0,intval($n),intval($j),intval($Y)) - $BioLogTime;
						$UnderPM=($UnderPM < 0) ? 0 : $UnderPM;
						$computeLateUnderTime=true;
					}
					else if ($LogTime['OTIN'] == '') {
						$LogTime['OTIN']=date('h:i:s', $BioLogTime);
						$td_att_ot1='';
					}
					else {
						$LogTime['OTOUT']=date('h:i:s', $BioLogTime);
						$td_att_ot2='';
					} 
				}
			}
			
			/* If AMIN is Blank check edited biolog table */
			if($LogTime['AMIN'] == '') {
				$sql="SELECT * FROM `tblebiologs` WHERE (`eBioLogTime` > '".$Year."-".$Month."-".(($Date-1) > 9 ? ($Date-1) : "0".($Date-1))." 23:59:59' AND `eBioLogTime` < '".$Year."-".$Month."-".($Date > 9 ? $Date : "0".$Date)." 11:00:00' AND `EmpID`='".$EmpID."') ORDER BY `eBioLogTime` DESC LIMIT 0,1;";
				$eBioResult=$MySQLi->sqlQuery($sql);
				if ($elogs=mysqli_fetch_array($eBioResult, MYSQLI_BOTH)) {
					$log_date_time=explode(" ",$elogs['eBioLogTime']);
					$log_date=explode("-",$log_date_time[0]);
					$Y=$log_date[0]; $n=$log_date[1]; $j=$log_date[2];
					$log_time=explode(":",$log_date_time[1]);
					$H=$log_time[0]; $i=$log_time[1]; $s=$log_time[2];
					$eBioLogTime=mktime($H,$i,$s,intval($n),intval($j),intval($Y));
					 
					$LogTime['AMIN']=date('h:i:s', $eBioLogTime);
					$LateAM=$eBioLogTime - mktime(8,0,0,intval($n),intval($j),intval($Y));
					$LateAM=($LateAM < 0) ? 0 : $LateAM;
					$computeLateUnderTime=true;
				}
				else {
					$LogTime['AMIN']="-";
				}
			}
			/* If AMOUT is Blank check edited biolog table */
			if ($LogTime['AMOUT'] == '') {
				$sql="SELECT * FROM `tblebiologs` WHERE (`eBioLogTime` > '".$Year."-".$Month."-".($Date > 9 ? $Date : "0".$Date)." 10:59:59' AND `eBioLogTime` < '".$Year."-".$Month."-".($Date > 9 ? $Date : "0".$Date)." 12:30:00' AND `EmpID`='".$EmpID."') ORDER BY `eBioLogTime` DESC LIMIT 0,1;";
				$eBioResult=$MySQLi->sqlQuery($sql);
				if ($elogs=mysqli_fetch_array($eBioResult, MYSQLI_BOTH)) {
					$log_date_time=explode(" ",$elogs['eBioLogTime']);
					$log_date=explode("-",$log_date_time[0]);
					$Y=$log_date[0]; $n=$log_date[1]; $j=$log_date[2];
					$log_time=explode(":",$log_date_time[1]);
					$H=$log_time[0]; $i=$log_time[1]; $s=$log_time[2];
					$eBioLogTime=mktime($H,$i,$s,intval($n),intval($j),intval($Y));
					 
					$LogTime['AMOUT']=date('h:i:s', $eBioLogTime);
					$UnderAM=mktime(12,0,0,intval($n),intval($j),intval($Y)) - $eBioLogTime;
					$UnderAM=($UnderAM < 0) ? 0 : $UnderAM;
					$computeLateUnderTime=true;
				}
				else {
					$LogTime['AMOUT']="-";
				}
			}
			/* If PMIN is Blank check edited biolog table */
			if ($LogTime['PMIN'] == '') {
				$sql="SELECT * FROM `tblebiologs` WHERE (`eBioLogTime` > '".$Year."-".$Month."-".($Date > 9 ? $Date : "0".$Date)." 12:29:59' AND `eBioLogTime` < '".$Year."-".$Month."-".($Date > 9 ? $Date : "0".$Date)." 16:00:00' AND `EmpID`='".$EmpID."') ORDER BY `eBioLogTime` DESC LIMIT 0,1;";
				$eBioResult=$MySQLi->sqlQuery($sql);
				if ($elogs=mysqli_fetch_array($eBioResult, MYSQLI_BOTH)) {
					$log_date_time=explode(" ",$elogs['eBioLogTime']);
					$log_date=explode("-",$log_date_time[0]);
					$Y=$log_date[0]; $n=$log_date[1]; $j=$log_date[2];
					$log_time=explode(":",$log_date_time[1]);
					$H=$log_time[0]; $i=$log_time[1]; $s=$log_time[2];
					$eBioLogTime=mktime($H,$i,$s,intval($n),intval($j),intval($Y));
					 
					$LogTime['PMIN']=date('h:i:s', $eBioLogTime);

					$LatePM=$eBioLogTime - mktime(13,0,0,intval($n),intval($j),intval($Y));
					$LatePM=($LatePM < 0) ? 0 : $LatePM;
					$computeLateUnderTime=true;
				}
				else {
					$LogTime['PMIN']="-";
				}
			}
			/* If PMOUT is Blank check edited biolog table */
			if ($LogTime['PMOUT'] == '') {
				$sql="SELECT * FROM `tblebiologs` WHERE (`eBioLogTime` > '".$Year."-".$Month."-".($Date > 9 ? $Date : "0".$Date)." 15:59:59' AND `eBioLogTime` < '".$Year."-".$Month."-".(($Date+1) > 9 ? ($Date+1) : "0".($Date+1))." 00:00:00' AND `EmpID`='".$EmpID."') ORDER BY `eBioLogTime` ASC LIMIT 0,1;";
				$eBioResult=$MySQLi->sqlQuery($sql);
				if ($elogs=mysqli_fetch_array($eBioResult, MYSQLI_BOTH)) {
					$log_date_time=explode(" ",$elogs['eBioLogTime']);
					$log_date=explode("-",$log_date_time[0]);
					$Y=$log_date[0]; $n=$log_date[1]; $j=$log_date[2];
					$log_time=explode(":",$log_date_time[1]);
					$H=$log_time[0]; $i=$log_time[1]; $s=$log_time[2];
					$eBioLogTime=mktime($H,$i,$s,intval($n),intval($j),intval($Y)); 
							
					$LogTime['PMOUT']=date('h:i:s', $eBioLogTime);
					$UnderPM=mktime(17,0,0,intval($n),intval($j),intval($Y)) - $eBioLogTime;
					$UnderPM=($UnderPM < 0) ? 0 : $UnderPM;
					$computeLateUnderTime=true;
				}
				else {
					$LogTime['PMOUT']="-";
				}
			}
			/* If OTIN is Blank check edited biolog table */
			if ($LogTime['OTIN'] == '') {
				$sql="SELECT * FROM `tblebiologs` WHERE (`eBioLogTime` > '".$Year."-".$Month."-".($Date > 9 ? $Date : "0".$Date)." 15:59:59' AND `eBioLogTime` < '".$Year."-".$Month."-".(($Date+1) > 9 ? ($Date+1) : "0".($Date+1))." 00:00:00' AND `EmpID`='".$EmpID."') ORDER BY `eBioLogTime` ASC LIMIT 1,1;";
				$eBioResult=$MySQLi->sqlQuery($sql);
				if ($elogs=mysqli_fetch_array($eBioResult, MYSQLI_BOTH)) {
					$log_date_time=explode(" ",$elogs['eBioLogTime']);
					$log_date=explode("-",$log_date_time[0]);
					$Y=$log_date[0]; $n=$log_date[1]; $j=$log_date[2];
					$log_time=explode(":",$log_date_time[1]);
					$H=$log_time[0]; $i=$log_time[1]; $s=$log_time[2];
					$eBioLogTime=mktime($H,$i,$s,intval($n),intval($j),intval($Y)); 
							
					$LogTime['OTIN']=date('h:i:s', $eBioLogTime);
					$UnderPM=mktime(17,0,0,intval($n),intval($j),intval($Y)) - $eBioLogTime;
					$UnderPM=($UnderPM < 0) ? 0 : $UnderPM;
					$computeLateUnderTime=true;
				}
				else {
					$LogTime['OTIN']="-";
				}
			}
			/* If OTOUT is Blank check edited biolog table */
			if ($LogTime['OTOUT'] == '') {
				$sql="SELECT * FROM `tblebiologs` WHERE (`eBioLogTime` > '".$Year."-".$Month."-".($Date > 9 ? $Date : "0".$Date)." 16:59:59' AND `eBioLogTime` < '".$Year."-".$Month."-".(($Date+1) > 9 ? ($Date+1) : "0".($Date+1))." 00:00:00' AND `EmpID`='".$EmpID."') ORDER BY `eBioLogTime` DESC;";
				if($MySQLi->NumberOfRows($sql)>=3) {
					$eBioResult=$MySQLi->sqlQuery($sql);
					$elogs=mysqli_fetch_array($eBioResult, MYSQLI_BOTH);
					$log_date_time=explode(" ",$elogs['eBioLogTime']);
					$log_date=explode("-",$log_date_time[0]);
					$Y=$log_date[0]; $n=$log_date[1]; $j=$log_date[2];
					$log_time=explode(":",$log_date_time[1]);
					$H=$log_time[0]; $i=$log_time[1]; $s=$log_time[2];
					$eBioLogTime=mktime($H,$i,$s,intval($n),intval($j),intval($Y)); 
							
					$LogTime['OTOUT']=date('h:i:s', $eBioLogTime);
					$UnderPM=mktime(17,0,0,intval($n),intval($j),intval($Y)) - $eBioLogTime;
					$UnderPM=($UnderPM < 0) ? 0 : $UnderPM;
					$computeLateUnderTime=true;
				}
				else {
					$LogTime['OTOUT']="-";
				}
			}

			/* Late and Under Time Computation */
			if ($computeLateUnderTime) {
				$sWrk=28800 - ($LateAM + $UnderAM + $LatePM + $UnderPM);
				$SecWrk=$sWrk % 60;
				$SecWrk=$SecWrk > 9 ? $SecWrk:"0".$SecWrk;
				$MinWrk=floor($sWrk / 60) % 60;
				$MinWrk=$MinWrk > 9 ? $MinWrk:"0".$MinWrk;
				$HrsWrk=floor($sWrk / 3600);
				$HrsWrk=$HrsWrk > 9 ? $HrsWrk:"0".$HrsWrk;
				$LogTime['HrsWrk']=$HrsWrk.":".$MinWrk.":".$SecWrk;
			}
			else { $LogTime['HrsWrk']=""; }
		
			/* Print Logs per Day  */
			$pdf->SetFont('Courier','',9);$pdf->SetTextColor(0,0,0);
			$Day=strtoupper(date("D", mktime(0, 0, 0, $Month, $Date, $Year)));
			if(($Day=="SAT")||($Day=="SUN")){$pdf->SetFillColor(255,220,220);$pdf->SetTextColor(0,0,0);}
			else{$pdf->SetFillColor(255,255,255);$pdf->SetTextColor(0,0,0);}
			$pdf->Cell(8,$CellHeight,$Date,1,0,'C',true);$pdf->Cell(9,$CellHeight,$Day,1,0,'L',true);
				$pdf->Cell($LogCellWidth,$CellHeight,$LogTime['AMIN'],1,0,'C',true);$pdf->Cell($LogCellWidth,$CellHeight,$LogTime['AMOUT'],1,0,'C',true);
				$pdf->Cell($LogCellWidth,$CellHeight,$LogTime['PMIN'],1,0,'C',true);$pdf->Cell($LogCellWidth,$CellHeight,$LogTime['PMOUT'],1,0,'C',true);
				$pdf->Cell($LogCellWidth,$CellHeight,$LogTime['OTIN'],1,0,'C',true);$pdf->Cell($LogCellWidth,$CellHeight,$LogTime['OTOUT'],1,0,'C',true);
				$pdf->Cell($LogCellWidth,$CellHeight,'',1,0,'C',true);$pdf->Cell($LogCellWidth,$CellHeight,$LogTime['OTHrs'],1,0,'C',true);$pdf->Cell($LogCellWidth,$CellHeight,$LogTime['HrsWrk'],1,1,'C',true);
		}	
		
		$pdf->Ln(2);
		$pdf->SetFont('Helvetica','B',9);$pdf->SetTextColor(0,0,0);
		$pdf->Cell($LogCellWidth-10,$CellHeight,'',0,0,'C');
		$pdf->Cell($LogCellWidth+7,$CellHeight,'Days Absent:',0,0,'C');$pdf->Cell($LogCellWidth,$CellHeight,'0.00',1,0,'C');
		$pdf->Cell(10,$CellHeight,'',0,0,'C');
		$pdf->Cell($LogCellWidth,$CellHeight,'Tardiness:',0,0,'C');$pdf->Cell($LogCellWidth,$CellHeight,'0.00',1,0,'C');
		$pdf->Cell(10,$CellHeight,'',0,0,'C');
		$pdf->Cell($LogCellWidth,$CellHeight,'TOTAL:',0,0,'C');$pdf->Cell($LogCellWidth,$CellHeight,'00:00:00',1,0,'C');$pdf->Cell($LogCellWidth,$CellHeight,'00:00:00',1,0,'C');$pdf->Cell($LogCellWidth,$CellHeight,'00:00:00',1,1,'C');
		
		$pdf->SetY(-55);
		$pdf->SetFont('Helvetica','I',9);$pdf->SetTextColor(0,0,0);
		$pdf->MultiCell(0,3.5,'I hereby CERTIFY on my honor that the above is true and correct report of the hours of work performed, record of which was made daily at the time of arrival and departure from Office.',0,'J',false);
		
		$pdf->Ln(4);
		$pdf->SetFont('Helvetica','B',9);$pdf->SetTextColor(0,0,0);
		$pdf->Cell(5,$CellHeight,'',0,0,'C');$pdf->Cell(75,$CellHeight,'','B',0,'C');$pdf->Cell(19,$CellHeight,'',0,0,'C');$pdf->Cell(75,$CellHeight,'','B',1,'C');
		$pdf->Cell(5,$CellHeight,'',0,0,'C');$pdf->Cell(75,$CellHeight,$EmpName,0,0,'C');$pdf->Cell(19,$CellHeight,'',0,0,'C');$pdf->Cell(75,$CellHeight,'In Charge',0,1,'C');
		
		$pdf->Ln(10);
		$pdf->SetFont('Courier','',7);$pdf->SetTextColor(100,100,100);
		$pdf->MultiCell(0,4,'Verified by MIS. 03 JANUARY, 2012 - 13SFH18SH61H68SR4H3FG13SDF34SRHJ4SR45RS','B','R',false);
	}

	else{
		$result=$MySQLi->sqlQuery("SELECT `EmpID` FROM (`tblempappointments` JOIN `tblapptstatus` ON `tblempappointments`.`ApptStID`=`tblapptstatus`.`ApptStID`) WHERE (`AssignedOfficeID`='".$SubOffID."' AND `tblapptstatus`.`ApptStID`='".$ApptStID."') ORDER BY `EmpID`;");
		if (mysql_num_rows($result)) {
			while($emp=mysqli_fetch_array($result, MYSQLI_BOTH)) {
				//======================================================
				$pdf->AddPage('P','letter');
				/* DTR Header */
				$pdf->SetFont('Helvetica','B',16);$pdf->SetTextColor(0,0,0);
				$pdf->Cell(179,5,'Provincial Covernment of La Union',0,1,'C',false);
				$pdf->SetFont('Helvetica','B',12);$pdf->SetTextColor(0,0,0);
				$pdf->Cell(179,5,'City of San Fernando, La Union',0,1,'C',false);
				$pdf->SetFont('Helvetica','B',16);$pdf->SetTextColor(0,0,0);
				$pdf->Cell(179,10,'Daily Time Record','B',1,'C',false);
				$pdf->Ln(4);
				
				$result=$MySQLi->sqlQuery("SELECT `EmpID`, `EmpFName`, `EmpMName`, `EmpLName` FROM `tblemppersonalinfo` WHERE `EmpID`='".$EmpID."';");
				$emp=mysqli_fetch_array($result, MYSQLI_BOTH);
				$EmpName=$emp['EmpLName'].", ".$emp['EmpFName']." ".$emp['EmpMName'];
				$pdf->SetFont('Helvetica','B',12);$pdf->SetTextColor(0,0,0);
				$pdf->Cell(195,5,$EmpName." (".$EmpID.")",0,1,'L',false);
				$pdf->Cell(195,5,date('F Y',mktime(0,0,0,$Month,1,$Year)),0,1,'L',false);
				$pdf->Ln(4);
				
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
					$pdf->Cell($LogCellWidth,$CellHeight,'(in Mins)','LRB',0,'C',true);$pdf->Cell($LogCellWidth,$CellHeight,'(HH:MM:SS)','LRB',0,'C',true);$pdf->Cell($LogCellWidth,$CellHeight,'(HH:MM:SS)','LRB',1,'C',true);
				
				/* Fix Month */
				$Month=($Month > 9) ? $Month : '0'.$Month;
				$DTRstartDate=1;
				$DaysOfMonth=cal_days_in_month(CAL_GREGORIAN, $Month, $Year);
				switch ($PayPeriod) {
					case 1	:	$DTRstartDate=1;
								$DaysOfMonth=15;
								break;
					case 2	:	$DTRstartDate=16;
								$DaysOfMonth=cal_days_in_month(CAL_GREGORIAN, $Month, $Year);
								break;
					default	:	$DTRstartDate=1;
								$DaysOfMonth=cal_days_in_month(CAL_GREGORIAN, $Month, $Year);
								break;
				}
				
				$DD=$DaysOfMonth;
				/* if (($Year == date('Y'))&&($Month == date('m'))) { $DD=date('j'); } */
				$td_att_am1=$td_att_am2=$td_att_pm1=$td_att_pm2=$td_att_ot1=$td_att_ot2="";
				
				for($Date=$DTRstartDate;$Date<=$DD;$Date+=1) {
					$LogTime=Array('AMIN' => '', 'AMOUT' => '', 'PMIN' => '', 'PMOUT' => '', 'OTIN' => '', 'OTOUT' => '', 'HrsWrk' => '', 'OTHrs' => '');
					/*  YYYY-MM-DD HH:MM:SS --->  $Year-$Month-$Date $Hour:$Minutes:$Second */
					$sql="SELECT SQL_NO_CACHE `BioLogTime` FROM `tblbiometrics` WHERE (`BioLogTime` LIKE '".$Year."-".$Month."-".($Date > 9 ? $Date : "0".$Date)."%'  AND `EmpID`='".$EmpID."');";/*  `BioLogTime` ASC;"; */
					$result=$MySQLi->sqlQuery($sql);
					$computeLateUnderTime=false;
					$LateAM=$UnderAM=$LatePM=$UnderPM=0;
					
					while ($logs=mysqli_fetch_array($result, MYSQLI_BOTH)) {
						$log_date_time=explode(" ",$logs['BioLogTime']);
						$log_date=explode("-",$log_date_time[0]);
						$Y=$log_date[0]; $n=$log_date[1]; $j=$log_date[2];
						$log_time=explode(":",$log_date_time[1]);
						$H=$log_time[0]; $i=$log_time[1]; $s=$log_time[2];
						
						$BioLogTime=mktime($H,$i,$s,intval($n),intval($j),intval($Y));
						
						/* $BioLogTime=strtotime($logs['BioLogTime']); */
						if(($BioLogTime >= mktime(0,0,0,intval($n),intval($j),intval($Y))) && ($BioLogTime <= mktime(10,59,59,intval($n),intval($j),intval($Y)))) { 
							$LogTime['AMIN']=date('h:i:s', $BioLogTime);
							$td_att_am1='';
							$LateAM=$BioLogTime - mktime(8,0,0,intval($n),intval($j),intval($Y));
							$LateAM=($LateAM < 0) ? 0 : $LateAM;
							$computeLateUnderTime=true;
						}
						else if (($BioLogTime >= mktime(11,0,0,intval($n),intval($j),intval($Y))) && ($BioLogTime <= mktime(12,29,59,intval($n),intval($j),intval($Y)))) { 
							$LogTime['AMOUT']=date('h:i:s', $BioLogTime);
							$td_att_am2='';
							$UnderAM=mktime(12,0,0,intval($n),intval($j),intval($Y)) - $BioLogTime;
							$UnderAM=($UnderAM < 0) ? 0 : $UnderAM;
							$computeLateUnderTime=true;
						}
						else if (($BioLogTime >= mktime(12,30,0,intval($n),intval($j),intval($Y))) && ($BioLogTime <= mktime(15,59,59,intval($n),intval($j),intval($Y)))) { 
							$LogTime['PMIN']=date('h:i:s', $BioLogTime);
							$td_att_pm1='';
							$LatePM=$BioLogTime - mktime(13,0,0,intval($n),intval($j),intval($Y));
							$LatePM=($LatePM < 0) ? 0 : $LatePM;
							$computeLateUnderTime=true;
						}
						else if (($BioLogTime >= mktime(16,0,0,intval($n),intval($j),intval($Y))) && ($BioLogTime <= mktime(23,59,59,intval($n),intval($j),intval($Y)))) { 
							if ($LogTime['PMOUT'] == '') { 
								$LogTime['PMOUT']=date('h:i:s', $BioLogTime); 
								$td_att_pm2='';
								$UnderPM=mktime(17,0,0,intval($n),intval($j),intval($Y)) - $BioLogTime;
								$UnderPM=($UnderPM < 0) ? 0 : $UnderPM;
								$computeLateUnderTime=true;
							}
							else if ($LogTime['OTIN'] == '') {
								$LogTime['OTIN']=date('h:i:s', $BioLogTime);
								$td_att_ot1='';
							}
							else {
								$LogTime['OTOUT']=date('h:i:s', $BioLogTime);
								$td_att_ot2='';
							} 
						}
					}
					
					/* If AMIN is Blank check edited biolog table */
					if($LogTime['AMIN'] == '') {
						$sql="SELECT * FROM `tblebiologs` WHERE (`eBioLogTime` > '".$Year."-".$Month."-".(($Date-1) > 9 ? ($Date-1) : "0".($Date-1))." 23:59:59' AND `eBioLogTime` < '".$Year."-".$Month."-".($Date > 9 ? $Date : "0".$Date)." 11:00:00' AND `EmpID`='".$EmpID."') ORDER BY `eBioLogTime` DESC LIMIT 0,1;";
						$eBioResult=$MySQLi->sqlQuery($sql);
						if ($elogs=mysqli_fetch_array($eBioResult, MYSQLI_BOTH)) {
							$log_date_time=explode(" ",$elogs['eBioLogTime']);
							$log_date=explode("-",$log_date_time[0]);
							$Y=$log_date[0]; $n=$log_date[1]; $j=$log_date[2];
							$log_time=explode(":",$log_date_time[1]);
							$H=$log_time[0]; $i=$log_time[1]; $s=$log_time[2];
							$eBioLogTime=mktime($H,$i,$s,intval($n),intval($j),intval($Y));
							 
							$LogTime['AMIN']=date('h:i:s', $eBioLogTime);
							$LateAM=$eBioLogTime - mktime(8,0,0,intval($n),intval($j),intval($Y));
							$LateAM=($LateAM < 0) ? 0 : $LateAM;
							$computeLateUnderTime=true;
						}
						else {
							$LogTime['AMIN']="-";
						}
					}
					/* If AMOUT is Blank check edited biolog table */
					if ($LogTime['AMOUT'] == '') {
						$sql="SELECT * FROM `tblebiologs` WHERE (`eBioLogTime` > '".$Year."-".$Month."-".($Date > 9 ? $Date : "0".$Date)." 10:59:59' AND `eBioLogTime` < '".$Year."-".$Month."-".($Date > 9 ? $Date : "0".$Date)." 12:30:00' AND `EmpID`='".$EmpID."') ORDER BY `eBioLogTime` DESC LIMIT 0,1;";
						$eBioResult=$MySQLi->sqlQuery($sql);
						if ($elogs=mysqli_fetch_array($eBioResult, MYSQLI_BOTH)) {
							$log_date_time=explode(" ",$elogs['eBioLogTime']);
							$log_date=explode("-",$log_date_time[0]);
							$Y=$log_date[0]; $n=$log_date[1]; $j=$log_date[2];
							$log_time=explode(":",$log_date_time[1]);
							$H=$log_time[0]; $i=$log_time[1]; $s=$log_time[2];
							$eBioLogTime=mktime($H,$i,$s,intval($n),intval($j),intval($Y));
							 
							$LogTime['AMOUT']=date('h:i:s', $eBioLogTime);
							$UnderAM=mktime(12,0,0,intval($n),intval($j),intval($Y)) - $eBioLogTime;
							$UnderAM=($UnderAM < 0) ? 0 : $UnderAM;
							$computeLateUnderTime=true;
						}
						else {
							$LogTime['AMOUT']="-";
						}
					}
					/* If PMIN is Blank check edited biolog table */
					if ($LogTime['PMIN'] == '') {
						$sql="SELECT * FROM `tblebiologs` WHERE (`eBioLogTime` > '".$Year."-".$Month."-".($Date > 9 ? $Date : "0".$Date)." 12:29:59' AND `eBioLogTime` < '".$Year."-".$Month."-".($Date > 9 ? $Date : "0".$Date)." 16:00:00' AND `EmpID`='".$EmpID."') ORDER BY `eBioLogTime` DESC LIMIT 0,1;";
						$eBioResult=$MySQLi->sqlQuery($sql);
						if ($elogs=mysqli_fetch_array($eBioResult, MYSQLI_BOTH)) {
							$log_date_time=explode(" ",$elogs['eBioLogTime']);
							$log_date=explode("-",$log_date_time[0]);
							$Y=$log_date[0]; $n=$log_date[1]; $j=$log_date[2];
							$log_time=explode(":",$log_date_time[1]);
							$H=$log_time[0]; $i=$log_time[1]; $s=$log_time[2];
							$eBioLogTime=mktime($H,$i,$s,intval($n),intval($j),intval($Y));
							 
							$LogTime['PMIN']=date('h:i:s', $eBioLogTime);

							$LatePM=$eBioLogTime - mktime(13,0,0,intval($n),intval($j),intval($Y));
							$LatePM=($LatePM < 0) ? 0 : $LatePM;
							$computeLateUnderTime=true;
						}
						else {
							$LogTime['PMIN']="-";
						}
					}
					/* If PMOUT is Blank check edited biolog table */
					if ($LogTime['PMOUT'] == '') {
						$sql="SELECT * FROM `tblebiologs` WHERE (`eBioLogTime` > '".$Year."-".$Month."-".($Date > 9 ? $Date : "0".$Date)." 15:59:59' AND `eBioLogTime` < '".$Year."-".$Month."-".(($Date+1) > 9 ? ($Date+1) : "0".($Date+1))." 00:00:00' AND `EmpID`='".$EmpID."') ORDER BY `eBioLogTime` ASC LIMIT 0,1;";
						$eBioResult=$MySQLi->sqlQuery($sql);
						if ($elogs=mysqli_fetch_array($eBioResult, MYSQLI_BOTH)) {
							$log_date_time=explode(" ",$elogs['eBioLogTime']);
							$log_date=explode("-",$log_date_time[0]);
							$Y=$log_date[0]; $n=$log_date[1]; $j=$log_date[2];
							$log_time=explode(":",$log_date_time[1]);
							$H=$log_time[0]; $i=$log_time[1]; $s=$log_time[2];
							$eBioLogTime=mktime($H,$i,$s,intval($n),intval($j),intval($Y)); 
									
							$LogTime['PMOUT']=date('h:i:s', $eBioLogTime);
							$UnderPM=mktime(17,0,0,intval($n),intval($j),intval($Y)) - $eBioLogTime;
							$UnderPM=($UnderPM < 0) ? 0 : $UnderPM;
							$computeLateUnderTime=true;
						}
						else {
							$LogTime['PMOUT']="-";
						}
					}
					/* If OTIN is Blank check edited biolog table */
					if ($LogTime['OTIN'] == '') {
						$sql="SELECT * FROM `tblebiologs` WHERE (`eBioLogTime` > '".$Year."-".$Month."-".($Date > 9 ? $Date : "0".$Date)." 15:59:59' AND `eBioLogTime` < '".$Year."-".$Month."-".(($Date+1) > 9 ? ($Date+1) : "0".($Date+1))." 00:00:00' AND `EmpID`='".$EmpID."') ORDER BY `eBioLogTime` ASC LIMIT 1,1;";
						$eBioResult=$MySQLi->sqlQuery($sql);
						if ($elogs=mysqli_fetch_array($eBioResult, MYSQLI_BOTH)) {
							$log_date_time=explode(" ",$elogs['eBioLogTime']);
							$log_date=explode("-",$log_date_time[0]);
							$Y=$log_date[0]; $n=$log_date[1]; $j=$log_date[2];
							$log_time=explode(":",$log_date_time[1]);
							$H=$log_time[0]; $i=$log_time[1]; $s=$log_time[2];
							$eBioLogTime=mktime($H,$i,$s,intval($n),intval($j),intval($Y)); 
									
							$LogTime['OTIN']=date('h:i:s', $eBioLogTime);
							$UnderPM=mktime(17,0,0,intval($n),intval($j),intval($Y)) - $eBioLogTime;
							$UnderPM=($UnderPM < 0) ? 0 : $UnderPM;
							$computeLateUnderTime=true;
						}
						else {
							$LogTime['OTIN']="-";
						}
					}
					/* If OTOUT is Blank check edited biolog table */
					if ($LogTime['OTOUT'] == '') {
						$sql="SELECT * FROM `tblebiologs` WHERE (`eBioLogTime` > '".$Year."-".$Month."-".($Date > 9 ? $Date : "0".$Date)." 16:59:59' AND `eBioLogTime` < '".$Year."-".$Month."-".(($Date+1) > 9 ? ($Date+1) : "0".($Date+1))." 00:00:00' AND `EmpID`='".$EmpID."') ORDER BY `eBioLogTime` DESC;";
						$eBioResult=$MySQLi->sqlQuery($sql);
						if (mysql_num_rows($eBioResult) >= 3) {
							$elogs=mysqli_fetch_array($eBioResult, MYSQLI_BOTH);
							$log_date_time=explode(" ",$elogs['eBioLogTime']);
							$log_date=explode("-",$log_date_time[0]);
							$Y=$log_date[0]; $n=$log_date[1]; $j=$log_date[2];
							$log_time=explode(":",$log_date_time[1]);
							$H=$log_time[0]; $i=$log_time[1]; $s=$log_time[2];
							$eBioLogTime=mktime($H,$i,$s,intval($n),intval($j),intval($Y)); 
									
							$LogTime['OTOUT']=date('h:i:s', $eBioLogTime);
							$UnderPM=mktime(17,0,0,intval($n),intval($j),intval($Y)) - $eBioLogTime;
							$UnderPM=($UnderPM < 0) ? 0 : $UnderPM;
							$computeLateUnderTime=true;
						}
						else {
							$LogTime['OTOUT']="-";
						}
					}

					/* Late and Under Time Computation */
					if ($computeLateUnderTime) {
						$sWrk=28800 - ($LateAM + $UnderAM + $LatePM + $UnderPM);
						$SecWrk=$sWrk % 60;
						$SecWrk=$SecWrk > 9 ? $SecWrk:"0".$SecWrk;
						$MinWrk=floor($sWrk / 60) % 60;
						$MinWrk=$MinWrk > 9 ? $MinWrk:"0".$MinWrk;
						$HrsWrk=floor($sWrk / 3600);
						$HrsWrk=$HrsWrk > 9 ? $HrsWrk:"0".$HrsWrk;
						$LogTime['HrsWrk']=$HrsWrk.":".$MinWrk.":".$SecWrk;
					}
					else { $LogTime['HrsWrk']=""; }
				
					/* Print Logs per Day  */
					$pdf->SetFont('Courier','',9);$pdf->SetTextColor(0,0,0);
					$Day=strtoupper(date("D", mktime(0, 0, 0, $Month, $Date, $Year)));
					if(($Day=="SAT")||($Day=="SUN")){$pdf->SetFillColor(255,220,220);$pdf->SetTextColor(0,0,0);}
					else{$pdf->SetFillColor(255,255,255);$pdf->SetTextColor(0,0,0);}
					$pdf->Cell(8,$CellHeight,$Date,1,0,'C',true);$pdf->Cell(9,$CellHeight,$Day,1,0,'L',true);
						$pdf->Cell($LogCellWidth,$CellHeight,$LogTime['AMIN'],1,0,'C',true);$pdf->Cell($LogCellWidth,$CellHeight,$LogTime['AMOUT'],1,0,'C',true);
						$pdf->Cell($LogCellWidth,$CellHeight,$LogTime['PMIN'],1,0,'C',true);$pdf->Cell($LogCellWidth,$CellHeight,$LogTime['PMOUT'],1,0,'C',true);
						$pdf->Cell($LogCellWidth,$CellHeight,$LogTime['OTIN'],1,0,'C',true);$pdf->Cell($LogCellWidth,$CellHeight,$LogTime['OTOUT'],1,0,'C',true);
						$pdf->Cell($LogCellWidth,$CellHeight,'',1,0,'C',true);$pdf->Cell($LogCellWidth,$CellHeight,$LogTime['OTHrs'],1,0,'C',true);$pdf->Cell($LogCellWidth,$CellHeight,$LogTime['HrsWrk'],1,1,'C',true);
				}	
				
				$pdf->Ln(2);
				$pdf->SetFont('Helvetica','B',9);$pdf->SetTextColor(0,0,0);
				$pdf->Cell($LogCellWidth-10,$CellHeight,'',0,0,'C');
				$pdf->Cell($LogCellWidth+7,$CellHeight,'Days Absent:',0,0,'C');$pdf->Cell($LogCellWidth,$CellHeight,'0.00',1,0,'C');
				$pdf->Cell(10,$CellHeight,'',0,0,'C');
				$pdf->Cell($LogCellWidth,$CellHeight,'Tardiness:',0,0,'C');$pdf->Cell($LogCellWidth,$CellHeight,'0.00',1,0,'C');
				$pdf->Cell(10,$CellHeight,'',0,0,'C');
				$pdf->Cell($LogCellWidth,$CellHeight,'TOTAL:',0,0,'C');$pdf->Cell($LogCellWidth,$CellHeight,'00:00:00',1,0,'C');$pdf->Cell($LogCellWidth,$CellHeight,'00:00:00',1,0,'C');$pdf->Cell($LogCellWidth,$CellHeight,'00:00:00',1,1,'C');
				
				$pdf->SetY(-55);
				$pdf->SetFont('Helvetica','I',9);$pdf->SetTextColor(0,0,0);
				$pdf->MultiCell(0,3.5,'I hereby CERTIFY on my honor that the above is true and correct report of the hours of work performed, record of which was made daily at the time of arrival and departure from Office.',0,'J',false);
				
				$pdf->Ln(4);
				$pdf->SetFont('Helvetica','B',9);$pdf->SetTextColor(0,0,0);
				$pdf->Cell(5,$CellHeight,'',0,0,'C');$pdf->Cell(75,$CellHeight,'','B',0,'C');$pdf->Cell(19,$CellHeight,'',0,0,'C');$pdf->Cell(75,$CellHeight,'','B',1,'C');
				$pdf->Cell(5,$CellHeight,'',0,0,'C');$pdf->Cell(75,$CellHeight,$EmpName,0,0,'C');$pdf->Cell(19,$CellHeight,'',0,0,'C');$pdf->Cell(75,$CellHeight,'In Charge',0,1,'C');
				
				$pdf->Ln(10);
				$pdf->SetFont('Courier','',7);$pdf->SetTextColor(100,100,100);
				$pdf->MultiCell(0,4,'Verified by MIS. 03 JANUARY, 2012 - 13SFH18SH61H68SR4H3FG13SDF34SRHJ4SR45RS','B','R',false);

				//======================================================
			}
		}
		else{$pdf->AddPage('P','letter');$pdf->SetFont('Helvetica','I',9);$pdf->Cell($LogCellWidth+7,$CellHeight,'No Records found.',0,0,'C');}
	}
	
	$pdf->Output();
	ob_end_flush();
?>