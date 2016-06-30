<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD019'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$TIMESTAMP=date('Y-m-d H:i:s');
	$MONTHS=Array('','JANUARY','FEBRUARY','MARCH','APRIL','MAY','JUNE','JULY','AUGUST','SEPTEMBER','OCTOBER','NOVEMBER','DECEMBER');
	
	/* Get POST Values */
	$docAction=isset($_POST['act'])?strtoupper(strip_tags(trim($_POST['act']))):'';
	$TOID=isset($_POST['did'])?strtoupper(strip_tags(trim($_POST['did']))):'';
	$Remark=isset($_POST['rm'])?strtoupper(strip_tags(trim($_POST['rm']))):'';
	
	$MySQLi=new MySQLClass();
	
	/* Check document if existing and confirm current status */
	if($Trav=$MySQLi->GetArray("SELECT `TOID`, `TOPreparedBy`, `TOStatus`, DATE_FORMAT(`TOIncDateFrom`,'%d') AS TravDay, DATE_FORMAT(`TOIncDateFrom`,'%m') AS TravMonth, DATE_FORMAT(`TOIncDateFrom`,'%Y') AS TravYear, DATE_FORMAT(`TOIncDateFrom`,'%Y%m%d') AS TravFromYMD, DATE_FORMAT(`TOIncDateTo`,'%Y%m%d') AS TravToYMD,`TOIncDateFrom`, `TOIncDayTimeFrom`, `TOIncDateTo`, `TOIncDayTimeTo` FROM `tbltravelorders` WHERE `TOID`='$TOID';")){
		$TOPreparedBy=$Trav['TOPreparedBy'];
		$TOCurrentStatus=$Trav['TOStatus'];
	}
	else{echo "0|".$_SESSION['user']."|ERROR 404:~Can't find requested record.";exit();}
	
	if($docAction=="1"){ /* NEW, for posting */
		if($TOCurrentStatus==-1){echo "0|".$_SESSION['user']."|ERROR 400:~Travel Order Application was already DISAPPROVED.";exit();}
		if($TOCurrentStatus==1){echo "0|".$_SESSION['user']."|ERROR 400:~Travel Order Application is already POSTED.";exit();}
		if(($TOCurrentStatus==2)&&(!$Authorization[5])){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		if(!$Authorization[4]||$_SESSION['user']!=$TOPreparedBy){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		
		$TONotedRemarks="";
		$TOStatus="1";
		$sql="UPDATE `tbltravelorders` SET `TOStatus`='$TOStatus',`RECORD_TIME`=NOW() WHERE `TOID`='$TOID' LIMIT 1;";
		if($MySQLi->sqlQuery($sql)){echo "1|$TOPreparedBy|Travel Order Application was successfully posted.";}
		else{echo "0|$TOPreparedBy|ERROR ".mysql_errno().":~".mysql_error();}
	}
	
	else if($docAction=="2") { /* POSTED, for noting */
		if($TOCurrentStatus==-1){echo "0|".$_SESSION['user']."|ERROR 400:~Travel Order Application was already DISAPPROVED.";exit();}
		if($TOCurrentStatus==2){echo "0|".$_SESSION['user']."|ERROR 400:~Travel Order Application is already NOTED.";exit();}
		if(($TOCurrentStatus==3)&&(!$Authorization[6])){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		if(!$Authorization[5]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		
		if($_SESSION['usergroup']=="USRGRP004"){ /* For Administrative officer user and Department Heads, get Office */
			$sql="SELECT `AssignedOfficeID` FROM `tblempservicerecords` WHERE `EmpID`='".$_SESSION['user']."' AND `SRecIsGov`='YES' AND `SRecCurrentAppointment` = 1;";
			$SRecOff=$MySQLi->GetArray($sql);
			$AssignedOfficeID=$SRecOff['AssignedOfficeID'];
		} //tblemptravelorders
		
		$TONotedRemarks="";
		$TOStatus="2";
		$sql="UPDATE `tblemptravelorders` SET `TONotedBy`='".$_SESSION['user']."',`TONotedTime`='$TIMESTAMP',`TONotedRemarks`='$TONotedRemarks',`RECORD_TIME`=NOW() WHERE `TOID`='$TOID' AND `EmpID` IN (SELECT `EmpID` FROM `tblempservicerecords` WHERE `AssignedOfficeID`='$AssignedOfficeID' AND `SRecIsGov`='YES' AND `SRecCurrentAppointment`=1);";
		$MySQLi->sqlQuery($sql);
		
		if($MySQLi->NumberOfRows("SELECT `EmpID` FROM `tblemptravelorders` WHERE `TOID`='$TOID' AND `TONotedBy`='' AND `TONotedTime`='1970-01-01 00:00:01';")==0){
			$sql="UPDATE `tbltravelorders` SET `TONotedBy`='".$_SESSION['user']."',`TONotedTime`='$TIMESTAMP',`TONotedRemarks`='$TONotedRemarks',`TOCheckedRemarks`='',`TOStatus`='$TOStatus',`RECORD_TIME`=NOW() WHERE `TOID`='$TOID' LIMIT 1;";
			if($MySQLi->sqlQuery($sql)){echo "1|$TOPreparedBy|Travel Order information was noted.";}
		}
		else{echo "1|$TOPreparedBy|Travel Order information was noted.";}
	}
	
	else if($docAction=="3") { /* NOTED, for checking */
		$TOCheckedRemarks="";
		$TOStatus="3";
		if(!$Authorization[7]){
			if(!$Authorization[6]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
			if($TOCurrentStatus==3){echo "0|".$_SESSION['user']."|ERROR 400:~Travel Order Application is already CHECKED.";exit();}
			if($TOCurrentStatus<2){echo "0|".$_SESSION['user']."|ERROR 400:~Travel Order Application is not yet NOTED.";exit();}
			
			/* Change document status to (3) - CHECKED */
			$sql="UPDATE `tbltravelorders` SET `TOCheckedBy`='".$_SESSION['user']."',`TOCheckedTime`='$TIMESTAMP',`TOCheckedRemarks`='$TOCheckedRemarks',`TOStatus`='$TOStatus',`RECORD_TIME`=NOW() WHERE `TOID`='$TOID' LIMIT 1;";
			if($MySQLi->sqlQuery($sql)){echo "1|$TOPreparedBy|Travel Order information was checked.";}
		}
		else{
			/* Change document status to (3) - CHECKED */
			$sql="UPDATE `tbltravelorders` SET `TOStatus`='$TOStatus',`RECORD_TIME`=NOW() WHERE `TOID`='$TOID' LIMIT 1;";
			if($MySQLi->sqlQuery($sql)){
				/* Update Employee DTR */
				$theDay=$Trav['TravDay'];
				$theMonth=$Trav['TravMonth'];
				$theYear=$Trav['TravYear'];
				$lf=intval($Trav['TravFromYMD']);
				while($lf<=intval($LivApp['TravToYMD'])){
					$EmpTO=$MySQLi->sqlQuery("SELECT `EmpID` FROM `tblemptravelorders` WHERE `TOID`='$TOID' AND `TONotedBy`<>'' AND `TONotedTime`<>'1970-01-01 00:00:01';");
					while($EmpIDs=mysqli_fetch_array($EmpTO, MYSQLI_BOTH)){
						$DTRID="DTR".$theYear.$theMonth.$theDay.$EmpIDs['EmpID'];
						$BCKID="BCK".$theYear.$theMonth.$theDay.$EmpIDs['EmpID'];
						$MySQLi->sqlQuery("DELETE FROM `tblempdtr` WHERE `DTRID`='$DTRID' LIMIT 1;");
						$MySQLi->sqlQuery("UPDATE `tblempdtr` SET `DTRID`='$DTRID',`RECORD_TIME`=NOW() WHERE `DTRID`='$BCKID' LIMIT 1;");
					}
					$theDay+=1;
					$DaysOfMonth=cal_days_in_month(CAL_GREGORIAN,$theMonth,$theYear);
					if($theDay>$DaysOfMonth){$theDay=1;$theMonth+=1;}
					if($theMonth>12){$theMonth=1;$theYear+=1;}
					$theDay=($theDay>9)?intval($theDay):'0'.intval($theDay);
					$theMonth=($theMonth>9)?intval($theMonth):'0'.intval($theMonth);
					$lf=intval($theYear.$theMonth.$theDay);
				}
				echo "1|$TOPreparedBy|Travel Order application was revoked.";
			}
		}
	}
	
	else if($docAction=="4") { /* CHECKED, for approval */
		if(!$Authorization[7]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		if($TOCurrentStatus==4){echo "0|".$_SESSION['user']."|ERROR 400:~Travel Order Application is already APPROVED.";exit();}
		if($TOCurrentStatus<3){echo "0|".$_SESSION['user']."|ERROR 400:~Travel Order Application is not yet CHECKED.";exit();}
		
		$TOApprovedDays=$TO['TODays']; /* Approved leave days, any further process? */
		$TOApprovedRemarks="";
		$TOStatus="4";
		$sql="UPDATE `tbltravelorders` SET `TOApprovedBy`='".$_SESSION['user']."',`TOApprovedTime`='$TIMESTAMP',`TOApprovedRemarks`='$TOApprovedRemarks',`TOStatus`='$TOStatus',`RECORD_TIME`=NOW() WHERE `TOID`='$TOID' LIMIT 1;";
		if($MySQLi->sqlQuery($sql)){
		
			/* Update Employee DTR */
			$theDay=$Trav['TravDay'];
			$theMonth=$Trav['TravMonth'];
			$theYear=$Trav['TravYear'];
			$lf=intval($Trav['TravFromYMD']);
			//$DTRID="DTR".$Trav['TravFromYMD'].$EmpID;
			$employeeid="";
			$DayStatusID="DS005";
			while($lf<=intval($Trav['TravToYMD'])){
				/* Get EmpIDs on this TOID */
				$EmpTO=$MySQLi->sqlQuery("SELECT `EmpID` FROM `tblemptravelorders` WHERE `TOID`='$TOID' AND `TONotedBy`<>'' AND `TONotedTime`<>'1970-01-01 00:00:01';");
				while($EmpIDs=mysqli_fetch_array($EmpTO, MYSQLI_BOTH)){
					$EmpID=$EmpIDs['EmpID'];
					$DTRID="DTR".$theYear.$theMonth.$theDay.$EmpID;
					$employeeid.=$EmpID."-".$DTRID.", ";
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
					
					if($lf==intval($Trav['TravFromYMD'])){
						if($Trav['TOIncDayTimeFrom']=='AM'){$sql="UPDATE `tblempdtr` SET `DayStatusID`='$DayStatusID',`DTRIN01`='1980-01-01 00:00:01',`DTROUT01`='1980-01-01 00:00:01',`RECORD_TIME`=NOW() WHERE `DTRID`='$DTRID' LIMIT 1;";}
						else{$sql="UPDATE `tblempdtr` SET `DayStatusID`='$DayStatusID',`DTRIN02`='1980-01-01 00:00:01',`DTROUT02`='1980-01-01 00:00:01',`DTRRemarks`='$TOID',`RECORD_TIME`=NOW() WHERE `DTRID`='$DTRID' LIMIT 1;";}
						$MySQLi->sqlQuery($sql);
					}
					
					if(($lf>intval($Trav['TravFromYMD']))&&($lf<intval($Trav['TravToYMD']))){
						$DTRIN01=$DTROUT01=$DTRIN02=$DTROUT02="1980-01-01 00:00:01";
						$sql="UPDATE `tblempdtr` SET `DayStatusID`='$DayStatusID',`DTRIN01`='$DTRIN01',`DTROUT01`='$DTROUT01',`DTRIN02`='$DTRIN02',`DTROUT02`='$DTROUT02',`DTRRemarks`='$TOID',`RECORD_TIME`=NOW() WHERE `DTRID`='$DTRID' LIMIT 1;";
						$MySQLi->sqlQuery($sql);
					}
					
					if($lf==intval($Trav['TravToYMD'])){
						if($Trav['TOIncDayTimeTo']=='AM'){$sql="UPDATE `tblempdtr` SET `DayStatusID`='$DayStatusID',`DTRIN01`='1980-01-01 00:00:01',`DTROUT01`='1980-01-01 00:00:01',`DTRRemarks`='$TOID',`RECORD_TIME`=NOW() WHERE `DTRID`='$DTRID' LIMIT 1;";}
						else{$sql="UPDATE `tblempdtr` SET `DayStatusID`='$DayStatusID',`DTRIN02`='1980-01-01 00:00:01',`DTROUT02`='1980-01-01 00:00:01',`DTRRemarks`='$TOID',`RECORD_TIME`=NOW() WHERE `DTRID`='$DTRID' LIMIT 1;";}
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
			echo "1|$TOPreparedBy|Travel Order Application was approved.";
		}
	}
	
	else if($docAction=="-1") {
		if(!$Authorization[7]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		$TOApprovedRemarks=$Remark;
		$TOStatus="-1";
		$sql="UPDATE `tbltravelorders` SET `TOApprovedBy`='".$_SESSION['user']."',`TOApprovedTime`='$TIMESTAMP',`TOApprovedRemarks`='$TOApprovedRemarks',`TOStatus`='$TOStatus',`RECORD_TIME`=NOW() WHERE `TOID`='$TOID' LIMIT 1;";
		if($MySQLi->sqlQuery($sql)){echo "1|$TOPreparedBy|Travel Order Application was disapproved.";}
	}
	else {echo "0";}
	ob_end_flush();
?>