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

	
	
	
	
	function checkFilingDate($ft,$tt,$lt){
		$fd=date('U',mktime(00,00,00,date('m'),date('j'),date('Y')));
		if($fd>$ft){echo "0|".$_SESSION['user']."|ERROR 406:~Late Filing. Application of Travel Orders shall be filed on or before the day, whenever possible, of the effective date of such travel.";exit();}else{return false;}
	}
	
	function checkDateRange($ft,$tt){if($ft>$tt){echo "0|".$_SESSION['user']."|ERROR 406:~Invalid date range.";exit();}else{return false;}}
	
	function NumberOfDays($ft,$tt,$fm,$tm,$LessWeekEnds=true){
		$ft=($fm=='AM')?$ft-(8*3600):$ft-(13*3600);
		$tt=($tm=='AM')?$tt-(12*3600):$tt-(17*3600);
		$LessPM=($fm=='PM')?43200:0;$LessAM=($tm=='AM')?43200:0;
		$NumOfDays=(($tt-$ft-$LessPM-$LessAM+86400)/86400);
		if($LessWeekEnds){
			for($d=$ft;$d<=$tt;$d+=86400){
				$DayNumber=date('w',$d);
				if(($DayNumber==0)||($DayNumber==6)){
					if(($d==$ft)&&($fm=='PM')){$NumOfDays-=0.5;}
					else if(($d==$tt)&&($tm=='AM')){$NumOfDays-=0.5;}
					else{$NumOfDays-=1;}
				}$thisDay++;
			}
		}return $NumOfDays;
	}
	
	
	
	
	
	
	$TIMESTAMP=date('Y-m-d H:i:s');

	//Get POST Values
	$mode=isset($_POST['mode'])?strip_tags(trim($_POST['mode'])):'';
	$EmpID=isset($_POST['EmpID'])?strtoupper(strip_tags(trim($_POST['EmpID']))):'';
	$TOID=isset($_POST['TOID'])?strtoupper(strip_tags(trim($_POST['TOID']))):'';
	$TOIncDayTimeFrom=isset($_POST['TODayTimeFrom'])?strtoupper(strip_tags(trim($_POST['TODayTimeFrom']))):"AM";
	$TODateFrDay=isset($_POST['TODateFrDay'])?strtoupper(strip_tags(trim($_POST['TODateFrDay']))):date('d');
	$TODateFrMonth=isset($_POST['TODateFrMonth'])?strtoupper(strip_tags(trim($_POST['TODateFrMonth']))):date('m');
	$TODateFrYear=isset($_POST['TODateFrYear'])?strtoupper(strip_tags(trim($_POST['TODateFrYear']))):date('Y');
	$TOIncDayTimeTo=isset($_POST['TODayTimeTo'])?strtoupper(strip_tags(trim($_POST['TODayTimeTo']))):"PM";
	$TODateToDay=isset($_POST['TODateToDay'])?strtoupper(strip_tags(trim($_POST['TODateToDay']))):date('d');
	$TODateToMonth=isset($_POST['TODateToMonth'])?strtoupper(strip_tags(trim($_POST['TODateToMonth']))):date('m');
	$TODateToYear=isset($_POST['TODateToYear'])?strtoupper(strip_tags(trim($_POST['TODateToYear']))):date('Y');
	$TOTo=isset($_POST['TOTo'])?strtoupper(strip_tags(trim($_POST['TOTo']))):'';
	$TOOutsideLU=isset($_POST['TOOutsideLU'])?strtoupper(strip_tags(trim($_POST['TOOutsideLU']))):'0';
	$TODestination=isset($_POST['TODestination'])?strtoupper(strip_tags(trim($_POST['TODestination']))):'';
	$TOSubject=isset($_POST['TOSubject'])?strtoupper(strip_tags(trim($_POST['TOSubject']))):'';
	$TOBody=isset($_POST['TOBody'])?strip_tags(trim($_POST['TOBody'])):'';
	
	$TODateFrDay=($TODateFrDay>9)?$TODateFrDay:"0".$TODateFrDay;
	$TODateFrMonth=($TODateFrMonth>9)?$TODateFrMonth:"0".$TODateFrMonth;
	$TODateToDay=($TODateToDay>9)?$TODateToDay:"0".$TODateToDay;
	$TODateToMonth=($TODateToMonth>9)?$TODateToMonth:"0".$TODateToMonth;
	
	$HH=($TOIncDayTimeFrom=='AM')?8:13;
	$FrDth=date('U',mktime($HH,00,00,intval($TODateFrMonth),intval($TODateFrDay),intval($TODateFrYear)));
	$HH=($TOIncDayTimeTo=='AM')?12:17;
	$ToDth=date('U',mktime($HH,00,00,intval($TODateToMonth),intval($TODateToDay),intval($TODateToYear)));
	
	
	
	/* COUNT NUMBER OF DAYS OF TRAVEL */
	if($TODateFrYear!=$TODateToYear){ /* Different year */
		$FrDth=date('z',mktime(00,00,00,12,31,intval($TODateFrYear)))-date('z',mktime(00,00,00,intval($TODateFrMonth),intval($TODateFrDay),intval($TODateFrYear)));
		$ToDth=date('z',mktime(00,00,00,intval($TODateToMonth),intval($TODateToDay),intval($TODateToYear)));
		$TravelDays=$ToDth+$FrDth;
	}
	else{ /* The same year */
		$FrDth=date('z',mktime(00,00,00,intval($TODateFrMonth),intval($TODateFrDay),intval($TODateFrYear)));
		$ToDth=date('z',mktime(00,00,00,intval($TODateToMonth),intval($TODateToDay),intval($TODateToYear)));
		$TravelDays=($ToDth-$FrDth)+1;
	}
	
	if($TOIncDayTimeFrom=='PM'){$TravelDays-=0.5;}
	if($TOIncDayTimeTo=='AM'){$TravelDays-=0.5;}
	
	$TODateFrDay=($TODateFrDay>9)?$TODateFrDay:"0".$TODateFrDay;
	$TODateFrMonth=($TODateFrMonth>9)?$TODateFrMonth:"0".$TODateFrMonth;
	$TODateToDay=($TODateToDay>9)?$TODateToDay:"0".$TODateToDay;
	$TODateToMonth=($TODateToMonth>9)?$TODateToMonth:"0".$TODateToMonth;
	
	$TOIncDateFrom=$TODateFrYear."-".$TODateFrMonth."-".$TODateFrDay;
	$TOIncDateFrom.=($TOIncDayTimeFrom=="AM")?" 08:00:00":" 13:00:00";
	
	$TOIncDateTo=$TODateToYear."-".$TODateToMonth."-".$TODateToDay;
	$TOIncDateTo.=($TOIncDayTimeTo=="AM")?" 12:00:00":" 17:00:00";
	
	$Config=new Conf();
	$MySQLi=new MySQLClass($Config);
	if($mode=="0"){
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		//Get New TOID
		$NewTOID="TRV".date('Ym')."001";
		$count=1;
		while($records=$MySQLi->GetArray("SELECT `TOID` FROM `tbltravelorders` WHERE `TOID`='$NewTOID';")) {
			$count+=1;
			$ccc=($count>99)?$count:(($count>9)?"0".$count:"00".$count);
			$NewTOID="TRV".date('Ym').$ccc;
		}$TOID=$NewTOID;

		$TOStatus="0";
		$TOPreparedBy=$_SESSION['user'];
		$TOPreparedTime=$TIMESTAMP;
		$TONotedBy="";
		$TONotedTime="1970-01-01 00:00:01";
		$TONotedRemarks="";
		$TOCheckedBy="";
		$TOCheckedTime="1970-01-01 00:00:01";
		$TOCheckedRemarks="";
		$TOApprovedBy="";
		$TOApprovedTime="1970-01-01 00:00:01";
		$TOApprovedRemarks="";

		$sql="INSERT INTO `tbltravelorders` (`TOID`,`TOIncDayTimeFrom`,`TOIncDateFrom`,`TOIncDayTimeTo`,`TOIncDateTo`,`TODays`,`TOSubject`,`TOOutsideLU`,`TODestination`,`TOBody`,`TOStatus`,`TOPreparedBy`,`TOPreparedTime`,`TONotedBy`,`TONotedTime`,`TONotedRemarks`,`TOCheckedBy`,`TOCheckedTime`,`TOCheckedRemarks`,`TOApprovedBy`,`TOApprovedTime`,`TOApprovedRemarks`,`RECORD_TIME`) VALUES ('$TOID','$TOIncDayTimeFrom','$TOIncDateFrom','$TOIncDayTimeTo','$TOIncDateTo','$TravelDays','$TOSubject','$TOOutsideLU','$TODestination','$TOBody','$TOStatus','$TOPreparedBy','$TOPreparedTime','$TONotedBy','$TONotedTime','$TONotedRemarks','$TOCheckedBy','$TOCheckedTime','$TOCheckedRemarks','$TOApprovedBy','$TOApprovedTime','$TOApprovedRemarks',NOW());";

		if($MySQLi->sqlQuery($sql)){
			$TOs=explode(",",$TOTo);
			foreach ($TOs as $key => &$value){
				if($key!=0){
					//Get New EmpTOID
					$NewEmpTOID="TO".date('Ym').$value."01";
					$count=1;
					while($records=$MySQLi->GetArray("SELECT `EmpTOID` FROM `tblemptravelorders` WHERE `EmpTOID`='$NewEmpTOID';")){
						$count+=1;
						$cc=($count>9)?$count:"0".$count;
						$NewEmpTOID="TO".date('Ym').$value.$cc;
					} $EmpTOID=$NewEmpTOID;
					$sql="INSERT INTO `pmis`.`tblemptravelorders` (`EmpTOID`, `EmpID`, `TOID`, `TONotedBy`, `TONotedTime`, `TONotedRemarks`, `RECORD_TIME`) VALUES ('$EmpTOID', '$value', '$TOID', '', '1970-01-01 00:00:01', '', NOW());";
					$MySQLi->sqlQuery($sql);
				}
			}
			echo "1|$EmpID|Travel Order information was successfully added.";
		}
	}
	
	else if($mode=="1") {
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		$TOStatus=$MySQLi->GetArray("SELECT `TOStatus` FROM `tbltravelorders` WHERE `TOID`='$TOID';");
		if($TOStatus['TOStatus']!='0'){echo "0|".$_SESSION['user']."|ERROR 400:~Can't modify travel order information.";exit();}
		
		$TOStatus="0";
		$TOPreparedBy=$_SESSION['user'];
		$TOPreparedTime=$TIMESTAMP;
		
		$sql="UPDATE `tbltravelorders` SET `TOIncDateFrom`='$TOIncDateFrom',`TOIncDateTo`='$TOIncDateTo',`TODays`='$TravelDays',`TOSubject`='$TOSubject',`TOOutsideLU`='$TOOutsideLU',`TODestination`='$TODestination',`TOBody`='$TOBody',`TOPreparedTime`='$TOPreparedTime',`TOPreparedBy`='$TOPreparedBy',`RECORD_TIME`=NOW() WHERE `TOID`='$TOID' LIMIT 1;";

		if($MySQLi->sqlQuery($sql)) {
			$sql="DELETE FROM `tblemptravelorders` WHERE `TOID`='$TOID';";
			$MySQLi->sqlQuery($sql);
			$TOs=explode(",",$TOTo);
			foreach ($TOs as $key => &$value) {
				if($key!=0){
					//Get New EmpTOID
					$NewEmpTOID="TO".date('Ym').$value."01";
					$count=1;
					while($records=$MySQLi->GetArray("SELECT `EmpTOID` FROM `tblemptravelorders` WHERE `EmpTOID`='$NewEmpTOID';")){
						$count+=1;
						$cc=($count>9)?$count:"0".$count;
						$NewEmpTOID="TO".date('Ym').$value.$cc;
					} $EmpTOID=$NewEmpTOID;
					$sql="INSERT INTO `pmis`.`tblemptravelorders` (`EmpTOID`, `EmpID`, `TOID`, `TONotedBy`, `TONotedTime`, `TONotedRemarks`, `RECORD_TIME`) VALUES ('$EmpTOID', '$value', '$TOID', '', '1970-01-01 00:00:01', '', NOW());";
					$MySQLi->sqlQuery($sql);
				}
			}
			echo "1|$EmpID|Travel Order information was successfully updated.";
		}
	}
	
	else if($mode=="-1") {
		if(!$Authorization[3]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		$TOStatus=$MySQLi->GetArray("SELECT `TOStatus` FROM `tbltravelorders` WHERE `TOID`='$TOID';");
		if($TOStatus['TOStatus']!='0'){echo "0|".$_SESSION['user']."|ERROR 400:~Can't delete travel order information.";exit();}
		
		$sql="DELETE FROM `tblemptravelorders` WHERE `TOID`='$TOID';";
		if($MySQLi->sqlQuery($sql)){
			$sql="DELETE FROM `tbltravelorders` WHERE `TOID`='$TOID' LIMIT 1;";
			if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Travel Order information was successfully deleted.";}
		}
	}
	else {echo "X";}
	ob_end_flush();
?>