<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$LeaveAuth=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD018'));
	for($i=0;$i<=7;$i++){$LeaveAuth[$i]=$LeaveAuth[$i]==1?true:false;}
	$COCAuth=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD020'));
	for($i=0;$i<=7;$i++){$COCAuth[$i]=$COCAuth[$i]==1?true:false;}
	if(!($LeaveAuth[1]||$COCAuth[1])){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	require_once $_SESSION['path'].'/lib/classes/Functions.php';
	
	
	$TIMESTAMP=date('Y-m-d H:i:s');
	$MONTHS=Array('','JANUARY','FEBRUARY','MARCH','APRIL','MAY','JUNE','JULY','AUGUST','SEPTEMBER','OCTOBER','NOVEMBER','DECEMBER');
	$thisMonth=date('m');
	$thisYear=date('Y');
	$MSG="";
	
	/* Get POST Values */
	$docAction=isset($_POST['act'])?strtoupper(strip_tags(trim($_POST['act']))):'';
	$LivAppID=isset($_POST['did'])?strtoupper(strip_tags(trim($_POST['did']))):'';
	$Remark=isset($_POST['rm'])?strtoupper(strip_tags(trim($_POST['rm']))):'';
	
	$MySQLi=new MySQLClass();
	$fLeave=new LeaveFunctions();
	$fCOC=new COCFunctions();
	
	
	/* Check document if existing and confirm current status */
	if($LivApp=$MySQLi->GetArray("SELECT `LivAppID`, `EmpID`, `LeaveTypeID`, `LivAppDays`, `LivAppStatus`, DATE_FORMAT(`LivAppIncDateFrom`,'%d') AS LivAppDay, DATE_FORMAT(`LivAppIncDateFrom`,'%m') AS LivAppMonth, DATE_FORMAT(`LivAppIncDateFrom`,'%Y') AS LivAppYear, DATE_FORMAT(`LivAppIncDateFrom`,'%Y%m%d') AS LivFromYMD, DATE_FORMAT(`LivAppIncDateTo`,'%Y%m%d') AS LivToYMD,`LivAppIncDateFrom`, `LivAppIncDayTimeFrom`, `LivAppIncDateTo`, `LivAppIncDayTimeTo`, `LivAppApprovedDays` FROM `tblempleaveapplications` WHERE `LivAppID`='$LivAppID';")){
		$EmpID=$LivApp['EmpID'];
		$LivAppCurrentStatus=$LivApp['LivAppStatus'];
	}
	else{echo "0|".$_SESSION['user']."|ERROR 404:~Can't find requested record.";exit();}
	
	if($docAction=="1"){ /* NEW, for posting */
		if(($LeaveAuth[5])||(($LivApp['LeaveTypeID']=='LT08')&&($COCAuth[5]))){$MSG="Confirmation of leave application was revoked.";}
		else{
			if($LivApp['LeaveTypeID']=='LT08'){if(!$COCAuth[4]||$_SESSION['user']!=$EmpID){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!! ".$_SESSION['user']." - ".$EmpID;exit();}}
			else{if(!$LeaveAuth[4]||$_SESSION['user']!=$EmpID){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}}
			if($LivAppCurrentStatus==1){echo "0|".$_SESSION['user']."|ERROR 400:~Leave Application is already POSTED.";exit();}
			if($LivAppCurrentStatus!=0){echo "0|".$_SESSION['user']."|ERROR 400:~Leave Application was already DISAPPROVED.";exit();}
			$MSG="Leave application was successfully posted.";
		}
		$sql="UPDATE `tblempleaveapplications` SET `LivAppStatus`='1',`RECORD_TIME`=NOW() WHERE `LivAppID`='$LivAppID' LIMIT 1;";
		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|$MSG";}
	}
	
	else if($docAction=="2") { /* POSTED, for noting */
		if(($LeaveAuth[6])||(($LivApp['LeaveTypeID']=='LT08')&&($COCAuth[6]))){$MSG="Confirmation of leave application was revoked.";}
		else{
			if($LivApp['LeaveTypeID']=='LT08'){if(!$COCAuth[5]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}}
			else{if(!$LeaveAuth[5]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}}
			if($LivAppCurrentStatus==2){echo "0|".$_SESSION['user']."|ERROR 400:~Leave Application is already NOTED.";exit();}
			if($LivAppCurrentStatus<1){echo "0|".$_SESSION['user']."|ERROR 400:~Leave Application is not yet POSTED.";exit();}
			$MSG="Leave application was successfully noted.";
		}
		
		$sql="UPDATE `tblempleaveapplications` SET `LivAppNotedBy`='".$_SESSION['user']."',`LivAppNotedTime`='$TIMESTAMP',`LivAppNotedRemarks`='$Remark',`LivAppCheckedRemarks`='',`LivAppStatus`='2',`RECORD_TIME`=NOW() WHERE `LivAppID`='$LivAppID' LIMIT 1;";
		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|$MSG";}
	}
	
	else if($docAction=="3") { /* NOTED, for checking */
		if($LeaveAuth[7]||$COCAuth[7]){
			$sql="DELETE FROM `tblempleavecredits` WHERE `EmpID`='".$EmpID."' AND `LivCredReference`='".$LivApp['LivAppID']."';";
			if($MySQLi->sqlQuery($sql)){
				/* Revoke COC */
				if($LivApp['LeaveTypeID']=='LT08'){
					$LivAppHours=($LivApp['LivAppDays']*8);
					$result=$MySQLi->sqlQuery("SELECT `tblempcocs`.`COCID`, `tblempcocs`.`EmpID`, `tblempcocs`.`COCEarnedHours`, `tblempcocs`.`COCRemainingHours` FROM `tblempcocs` WHERE `tblempcocs`.`EmpID`='".$EmpID."' AND `tblempcocs`.`COCStatus`='4' ORDER BY `COCEarnedDate` DESC;");
					while($COC=mysqli_fetch_array($result, MYSQLI_BOTH)){
						if($LivAppHours>0){
							if($COC['COCRemainingHours']<$COC['COCEarnedHours']){
								$SpentHours=$COC['COCEarnedHours']-$COC['COCRemainingHours'];
								if($SpentHours<=$LivAppHours){$MySQLi->sqlQuery("UPDATE `tblempcocs` SET `COCRemainingHours`='".$COC['COCEarnedHours']."', `RECORD_TIME`=NOW() WHERE `COCID`='".$COC['COCID']."' LIMIT 1;");$LivAppHours=$LivAppHours-$SpentHours;}
								else{$MySQLi->sqlQuery("UPDATE `tblempcocs` SET `COCRemainingHours`='".$LivAppHours."', `RECORD_TIME`=NOW() WHERE `COCID`='".$COC['COCID']."' LIMIT 1;");$LivAppHours=0;}
							}
						}
						else{break;}
					}
				}
				
				/* Update Employee DTR */
				$theDay=$LivApp['LivAppDay'];
				$theMonth=$LivApp['LivAppMonth'];
				$theYear=$LivApp['LivAppYear'];
				$lf=intval($LivApp['LivFromYMD']);
				$DTRID="DTR".$LivApp['LivFromYMD'].$EmpID;
				while($lf<=intval($LivApp['LivToYMD'])){
					$DTRID="DTR".$theYear.$theMonth.$theDay.$EmpID;
					$BCKID="BCK".$theYear.$theMonth.$theDay.$EmpID;
					$MySQLi->sqlQuery("DELETE FROM `tblempdtr` WHERE `DTRID`='$DTRID' LIMIT 1;");
					$MySQLi->sqlQuery("UPDATE `tblempdtr` SET `DTRID`='$DTRID',`RECORD_TIME`=NOW() WHERE `DTRID`='$BCKID' LIMIT 1;");
					$theDay+=1;
					$DaysOfMonth=cal_days_in_month(CAL_GREGORIAN,$theMonth,$theYear);
					if($theDay>$DaysOfMonth){$theDay=1;$theMonth+=1;}
					if($theMonth>12){$theMonth=1;$theYear+=1;}
					$theDay=($theDay>9)?intval($theDay):'0'.intval($theDay);
					$theMonth=($theMonth>9)?intval($theMonth):'0'.intval($theMonth);
					$lf=intval($theYear.$theMonth.$theDay);
				}
			}
			$MSG="Approval of leave application was revoked.";
		}
		else{
			if($LivAppCurrentStatus==3){echo "0|".$_SESSION['user']."|ERROR 400:~Leave Application is already CHECKED.";exit();}
			if($LivAppCurrentStatus<2){echo "0|".$_SESSION['user']."|ERROR 400:~Leave Application is not yet NOTED.";exit();}
			if($LivApp['LeaveTypeID']=='LT08'){
				if(!$COCAuth[6]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
				if($LivApp['LivAppDays']>$fLeave->AvailableLeaveCredit($EmpID,'LT08',$LivApp['LivAppYear'])){echo "0|".$_SESSION['user']."|ERROR 406:~Insufficient Privilege leave credit.";exit();}
			}
			else{if(!$LeaveAuth[6]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}}
		
			$theYear=intval($LivApp['LivAppYear']);
			$theMonth=intval($LivApp['LivAppMonth'])-1;
			if($theMonth==0){$theMonth=12;$theYear-=1;}
			$theMonth=$theMonth>9?$theMonth:"0".$theMonth;
			while($theYear.$theMonth>=$thisYear.$thisMonth){$theMonth-=1;if($theMonth==0){$theMonth=12;$theYear-=1;}}
			if(($LivApp['LeaveTypeID']=="LT01")||($LivApp['LeaveTypeID']=="LT02")){
				/* Check for unconfirmed system generated credits */
				if(!($MySQLi->NumberOfRows("SELECT `LivCredID` FROM `tblempleavecredits` WHERE `EmpID` = '".$EmpID."' AND `LivCredRemarks`='System Generated' AND DATE_FORMAT(`LivCredDateFrom`,'%Y%m') = '".$theYear.$theMonth."' ORDER BY `LivCredDateFrom` DESC;")>0)){echo "0|$EmpID|ERROR 405:~System generated leave credits of the preceeding months before the date applied for, on this application is not yet confirmed. Please confirm system generated leave credits atleast until <b>".$MONTHS[intval($theMonth)].", ".$theYear.".</b> <br>";exit();}
			}
			$MSG="Leave application was confirmed.<br>";//.$theYear.$theMonth;
			/* Sick Leave - Check available balance, use VL instead */
			if($LivApp['LeaveTypeID']=="LT02"){
				$SLbal=$fLeave->AvailableLeaveCredit($EmpID,'LT02',$LivApp['LivAppYear']);
				if($LivApp['LivAppDays']>$SLbal){
					$MSG.="Insufficient Sick Leave credit balance.<br>";
					$minusFromVL=$LivApp['LivAppDays']-$SLbal;
					$VLbal=$fLeave->AvailableLeaveCredit($EmpID,'LT01',$LivApp['LivAppYear']);
					if($minusFromVL>$VLbal){
						$LeaveNoPay=$minusFromVL-$VLbal;
						$MSG.="$minusFromVL day(s) Vacation Leave credit will be used.<br>";
					}
					else{$MSG.="$minusFromVL day(s) Vacation Leave credit will be used instead.<br>";}
				}
			}
			if(($LivApp['LivAppDays']>$fLeave->AvailableLeaveCredit($EmpID,'LT02',$LivApp['LivAppYear']))&&($LivApp['LeaveTypeID']=="LT02")){
				$MSG.="Insufficient Sick Leave credit balance. Vacation Leave credit will be used.<br>";
			}
		}
		
		/* Change document status to (3) - CHECKED */
		$sql="UPDATE `tblempleaveapplications` SET `LivAppCheckedBy`='".$_SESSION['user']."',`LivAppCheckedTime`='$TIMESTAMP',`LivAppCheckedRemarks`='$Remark',`LivAppApprovedDays`='0',`LivAppApprovedRemarks`='',`LivAppStatus`='3',`RECORD_TIME`=NOW() WHERE `LivAppID`='$LivAppID' LIMIT 1;";
		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|$MSG";}
	}
	
	else if($docAction=="4"){ /* CHECKED, for approval */
		if($LivAppCurrentStatus==4){echo "0|".$_SESSION['user']."|ERROR 400:~Leave Application is already APPROVED.";exit();}
		if($LivAppCurrentStatus<3){echo "0|".$_SESSION['user']."|ERROR 400:~Leave Application is not yet CHECKED.";exit();}
		if($LivApp['LeaveTypeID']=='LT08'){
			if(!$COCAuth[7]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
			if($LivApp['LivAppDays']>$fLeave->AvailableLeaveCredit($EmpID,'LT08',$LivApp['LivAppYear'])){echo "0|".$_SESSION['user']."|ERROR 406:~Insufficient Privilege leave credit.";exit();}
		}
		else{if(!$LeaveAuth[7]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}}
		
		$Remarks="";
		$AvailableCredit=0;
		$AvailableCredit=$fLeave->AvailableLeaveCredit($EmpID,$LivApp['LeaveTypeID'],$LivApp['LivAppYear']);
		if($LivApp['LeaveTypeID']=='LT08'){
			$LivAppHours=($LivApp['LivAppDays']*8);
			$result=$MySQLi->sqlQuery("SELECT `tblempcocs`.`COCID`, `tblempcocs`.`EmpID`, `tblempcocs`.`COCRemainingHours` FROM `tblempcocs` WHERE `tblempcocs`.`EmpID`='".$EmpID."' AND `tblempcocs`.`COCStatus`='4' ORDER BY `COCEarnedDate`;");
			while($COC=mysqli_fetch_array($result, MYSQLI_BOTH)){
				if($LivAppHours>0){
					$COCRemainingHours=$COC['COCRemainingHours'];
					if($LivAppHours>=$COC['COCRemainingHours']){$COCRemainingHours=0;$LivAppHours=$LivAppHours-$COC['COCRemainingHours'];}
					else{$COCRemainingHours=$COC['COCRemainingHours']-$LivAppHours;$LivAppHours=0;}
					$MySQLi->sqlQuery("UPDATE `tblempcocs` SET`COCRemainingHours`='$COCRemainingHours', `RECORD_TIME`=NOW() WHERE `COCID`='".$COC['COCID']."' LIMIT 1;");
				}
				else{break;}
			}
			$LeaveNoPay=0;
			$NewCredBalance=($fLeave->AvailableLeaveCredit($EmpID,'LT08',$LivApp['LivAppYear'])*8)-$LivAppHours;
			$LivAppApprovedDays=$LivApp['LivAppDays']*8;
			$Remarks="Filed CTO";
		}
		else{
			if($LivApp['LeaveTypeID']=='LT03'){if($LivApp['LivAppDays']>$fLeave->AvailableLeaveCredit($EmpID,'LT03',$LivApp['LivAppYear'])){echo "0|".$_SESSION['user']."|ERROR 406:~Insufficient Privilege leave credit.";exit();}}
			$LeaveNoPay=($LivApp['LivAppDays']-$AvailableCredit)<0?0:($records['LivAppDays']-$AvailableCredit);
			$NewCredBalance=($AvailableCredit-$LivApp['LivAppDays'])<0?0:($AvailableCredit-$LivApp['LivAppDays']);
			$LivAppApprovedDays=($LeaveNoPay==0)?$LivApp['LivAppDays']:$LivApp['LivAppDays']-$LeaveNoPay; 
		}
		/* Sick Leave - Check available balance, use VL instead */
		if($LivApp['LeaveTypeID']=="LT02"){
			$SLbal=$fLeave->AvailableLeaveCredit($EmpID,'LT02',$LivApp['LivAppYear']);
			if($LivApp['LivAppDays']>$SLbal){
				$minusFromVL=$LivApp['LivAppDays']-$SLbal;
				$VLbal=$fLeave->AvailableLeaveCredit($EmpID,'LT01',$LivApp['LivAppYear']);
				if($minusFromVL>$VLbal){$LeaveNoPay=$minusFromVL-$VLbal;}
				$MSG.="Insufficient Sick Leave credit balance. $minusFromVL day(s) Vacation Leave credit will be used.<br>";
			}
		}
		
		
		$LivCredID=$fLeave->GetNewLivCredID($EmpID); 
		$sql="UPDATE `tblempleaveapplications` SET `LivAppApprovedDays`='$LivAppApprovedDays',`LivAppApprovedBy`='".$_SESSION['user']."',`LivAppApprovedTime`='$TIMESTAMP',`LivAppApprovedRemarks`='$Remark',`LivAppStatus`='4',`LivCredID`='$LivCredID',`RECORD_TIME`=NOW() WHERE `LivAppID`='$LivAppID' LIMIT 1;";
		
		if($MySQLi->sqlQuery($sql)){
			
			/* Update EmpLeaveCredit */
			$sql="INSERT INTO `tblempleavecredits` (`LivCredID`, `EmpID`, `LeaveTypeID`, `LivCredDateFrom`, `LivCredDateTo`, `LivCredAddTo`, `LivCredDeductTo`, `LivCredBalance`, `LivCredReference`, `LivCredRemarks`, `RECORD_TIME`) VALUES ('".$LivCredID."', '".$EmpID."', '".$LivApp['LeaveTypeID']."', '".$LivApp['LivAppIncDateFrom']."', '".$LivApp['LivAppIncDateTo']."', '0', '".$LivAppApprovedDays."', '".$NewCredBalance."', '".$LivApp['LivAppID']."', '".$Remarks."', NOW());";
			$MySQLi->sqlQuery($sql);
			
			/* Update Employee DTR */
			$theDay=$LivApp['LivAppDay'];
			$theMonth=$LivApp['LivAppMonth'];
			$theYear=$LivApp['LivAppYear'];
			$lf=intval($LivApp['LivFromYMD']);
			$DTRID="DTR".$LivApp['LivFromYMD'].$EmpID;
			$DayStatusID="DS000";
			if($LivApp['LeaveTypeID']=="LT01"){$DayStatusID="DS003";}
			if($LivApp['LeaveTypeID']=="LT02"){$DayStatusID="DS004";}
			if($LivApp['LeaveTypeID']=="LT03"){$DayStatusID="DS002";}
			if($LivApp['LeaveTypeID']=="LT08"){$DayStatusID="DS006";}
			while($lf<=intval($LivApp['LivToYMD'])){
				$DTRID="DTR".$theYear.$theMonth.$theDay.$EmpID;
				if($MySQLi->NumberOfRows("SELECT `DTRID` FROM `tblempdtr` WHERE `DTRID`='$DTRID';")==0){
					$MySQLi->sqlQuery("INSERT INTO `tblempdtr` (`DTRID`, `EmpID`, `DayStatusID`, `DTRIN01`, `DTROUT01`, `DTRIN02`, `DTROUT02`, `DTRIN03`, `DTROUT03`, `DTRIN04`, `DTROUT04`, `DTRLates`, `DTROverTime`, `DTRHrsWeek`, `DTRVerCode`, `DTRRemarks`, `RECORD_TIME`) VALUES ('$DTRID', '$EmpID', '', '1970-01-01 00:00:01', '1970-01-01 00:00:01', '1970-01-01 00:00:01', '1970-01-01 00:00:01', '1970-01-01 00:00:01', '1970-01-01 00:00:01', '1970-01-01 00:00:01', '1970-01-01 00:00:01', '', '', '', '', '', NOW());");
				}
				else{
					$BCKID="BCK".$theYear.$theMonth.$theDay.$EmpID;
					if($MySQLi->NumberOfRows("SELECT `DTRID` FROM `tblempdtr` WHERE `DTRID`='$BCKID';")==0){
						$records=$MySQLi->GetArray("SELECT * FROM `tblempdtr` WHERE `DTRID`='$DTRID';");
						$MySQLi->sqlQuery("INSERT INTO `tblempdtr` (`DTRID`, `EmpID`, `DayStatusID`, `DTRIN01`, `DTROUT01`, `DTRIN02`, `DTROUT02`, `DTRIN03`, `DTROUT03`, `DTRIN04`, `DTROUT04`, `DTRLates`, `DTROverTime`, `DTRHrsWeek`, `DTRVerCode`, `DTRRemarks`, `RECORD_TIME`) VALUES ('$BCKID', '$EmpID', '', '".$records['DTRIN01']."', '".$records['DTROUT01']."', '".$records['DTRIN02']."', '".$records['DTROUT02']."', '".$records['DTRIN03']."', '".$records['DTROUT03']."', '".$records['DTRIN04']."', '".$records['DTROUT04']."', '".$records['DTRLates']."', '".$records['DTROverTime']."', '".$records['DTRHrsWeek']."', '".$records['DTRVerCode']."', '".$records['DTRRemarks']."', NOW());");
					}
				}
				
				if(($lf==intval($LivApp['LivFromYMD']))&&($lf==intval($LivApp['LivToYMD']))){
					if(($LivApp['LivAppIncDayTimeFrom']=='AM')&&($LivApp['LivAppIncDayTimeTo']=='AM')){$sql="UPDATE `tblempdtr` SET `DayStatusID`='$DayStatusID',`DTRIN01`='1980-01-01 00:00:01',`DTROUT01`='1980-01-01 00:00:01',`RECORD_TIME`=NOW() WHERE `DTRID`='$DTRID' LIMIT 1;";}
					elseif(($LivApp['LivAppIncDayTimeFrom']=='AM')&&($LivApp['LivAppIncDayTimeTo']=='PM')){$sql="UPDATE `tblempdtr` SET `DayStatusID`='$DayStatusID',`DTRIN01`='1980-01-01 00:00:01',`DTROUT01`='1980-01-01 00:00:01',`DTRIN02`='1980-01-01 00:00:01',`DTROUT02`='1980-01-01 00:00:01',`DTRRemarks`='$LivCredID',`RECORD_TIME`=NOW() WHERE `DTRID`='$DTRID' LIMIT 1;";}
					else{$sql="UPDATE `tblempdtr` SET `DayStatusID`='$DayStatusID',`DTRIN02`='1980-01-01 00:00:01',`DTROUT02`='1980-01-01 00:00:01',`DTRRemarks`='$LivCredID',`RECORD_TIME`=NOW() WHERE `DTRID`='$DTRID' LIMIT 1;";}
					$MySQLi->sqlQuery($sql);
				}
				else{
					if($lf==intval($LivApp['LivFromYMD'])){
						if($LivApp['LivAppIncDayTimeFrom']=='AM'){$sql="UPDATE `tblempdtr` SET `DayStatusID`='$DayStatusID',`DTRIN01`='1980-01-01 00:00:01',`DTROUT01`='1980-01-01 00:00:01',`RECORD_TIME`=NOW() WHERE `DTRID`='$DTRID' LIMIT 1;";}
						else{$sql="UPDATE `tblempdtr` SET `DayStatusID`='$DayStatusID',`DTRIN02`='1980-01-01 00:00:01',`DTROUT02`='1980-01-01 00:00:01',`DTRRemarks`='$LivCredID',`RECORD_TIME`=NOW() WHERE `DTRID`='$DTRID' LIMIT 1;";}
						$MySQLi->sqlQuery($sql);
					}
					
					if(($lf>intval($LivApp['LivFromYMD']))&&($lf<intval($LivApp['LivToYMD']))){
						$DTRIN01=$DTROUT01=$DTRIN02=$DTROUT02="1980-01-01 00:00:01";
						$sql="UPDATE `tblempdtr` SET `DayStatusID`='$DayStatusID',`DTRIN01`='$DTRIN01',`DTROUT01`='$DTROUT01',`DTRIN02`='$DTRIN02',`DTROUT02`='$DTROUT02',`DTRRemarks`='$LivCredID',`RECORD_TIME`=NOW() WHERE `DTRID`='$DTRID' LIMIT 1;";
						$MySQLi->sqlQuery($sql);
					}
					
					if($lf==intval($LivApp['LivToYMD'])){
						if($LivApp['LivAppIncDayTimeTo']=='AM'){$sql="UPDATE `tblempdtr` SET `DayStatusID`='$DayStatusID',`DTRIN01`='1980-01-01 00:00:01',`DTROUT01`='1980-01-01 00:00:01',`RECORD_TIME`=NOW() WHERE `DTRID`='$DTRID' LIMIT 1;";}
						else{$sql="UPDATE `tblempdtr` SET `DayStatusID`='$DayStatusID',`DTRIN01`='1980-01-01 00:00:01',`DTROUT01`='1980-01-01 00:00:01',`DTRIN02`='1980-01-01 00:00:01',`DTROUT02`='1980-01-01 00:00:01',`DTRRemarks`='$LivCredID',`RECORD_TIME`=NOW() WHERE `DTRID`='$DTRID' LIMIT 1;";}
						$MySQLi->sqlQuery($sql);
					}
				}
				
				$theDay+=1;
				$DaysOfMonth=cal_days_in_month(CAL_GREGORIAN,$theMonth,$theYear);
				if($theDay>$DaysOfMonth){$theDay=1;$theMonth+=1;}
				if($theMonth>12){$theMonth=1;$theYear+=1;}
				$theDay=($theDay>9)?intval($theDay):'0'.intval($theDay);
				$theMonth=($theMonth>9)?intval($theMonth):'0'.intval($theMonth);
				$lf=intval($theYear.$theMonth.$theDay);
			}
			echo "1|$EmpID|Leave application was approved.<br/><br/>Leave Days Applied: ".number_format($LivApp['LivAppDays'],2)."<br/>Approved for day(s) with pay: ".number_format($LivAppApprovedDays,2)."<br/>Approved for day(s) without pay: ".($LeaveNoPay>0?"<font color=red>".number_format($LeaveNoPay,2)."</font>":number_format($LeaveNoPay,2));
		}
	}
	
	else if($docAction=="-1") { /* disapproval */
		//if(!$LeaveAuth[7]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		$sqlNote=$sqlChck=$sqlAppv="";
		$Remark="DISAPPROVED: ".$Remark;
		if(($LivAppCurrentStatus==1)&&$LeaveAuth[5]){$sqlNote=", `LivAppNotedBy`='".$_SESSION['user']."', `LivAppNotedTime`='".$TIMESTAMP."', `LivAppNotedRemarks`='".$Remark."'";}
		else if(($LivAppCurrentStatus==2)&&$LeaveAuth[6]){$sqlChck=", `LivAppCheckedBy`='".$_SESSION['user']."', `LivAppCheckedTime`='".$TIMESTAMP."', `LivAppCheckedRemarks`='".$Remark."'";}
		else if(($LivAppCurrentStatus==3)&&$LeaveAuth[7]){$sqlAppv=", `LivAppApprovedBy`='".$_SESSION['user']."', `LivAppApprovedTime`='".$TIMESTAMP."', `LivAppApprovedRemarks`='".$Remark."'";}
		else{echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		$LivAppApprovedDays="";
		$LivAppStatus="-1";
		$sql="UPDATE `tblempleaveapplications` SET `LivAppApprovedDays`='$LivAppApprovedDays',`LivAppStatus`='$LivAppStatus',`RECORD_TIME`=NOW()".$sqlNote.$sqlChck.$sqlAppv." WHERE `LivAppID`='$LivAppID' LIMIT 1;";
		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Leave application was disapproved.";}
	}
	else {echo "0";}
	ob_end_flush();
?>