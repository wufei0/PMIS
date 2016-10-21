<?php
	ob_start();
	session_start();
	
	require_once $_SESSION['path'].'/echo-txt.php';
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD018'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	require_once $_SESSION['path'].'/lib/classes/Functions.php';

	
	$TIMESTAMP=date('Y-m-d H:i:s');
	//$LivAppFiledDay=date('d');
	//$LivAppFiledMonth=date('m');
	//$LivAppFiledYear=date('Y');

	//Get POST Values
	$mode=isset($_POST['mode'])?strip_tags(trim($_POST['mode'])):'';
	$EmpID=isset($_POST['EmpID'])?strtoupper(strip_tags(trim($_POST['EmpID']))):'';
	$LivAppID=isset($_POST['LivAppID'])?strtoupper(strip_tags(trim($_POST['LivAppID']))):'';
	$LivAppFiledDay=isset($_POST['LivAppFiledDay'])?strtoupper(strip_tags(trim($_POST['LivAppFiledDay']))):date('d');
	$LivAppFiledMonth=isset($_POST['LivAppFiledMonth'])?strtoupper(strip_tags(trim($_POST['LivAppFiledMonth']))):date('m');
	$LivAppFiledYear=isset($_POST['LivAppFiledYear'])?strtoupper(strip_tags(trim($_POST['LivAppFiledYear']))):date('Y');
	$LivAppIncDateFrDay=isset($_POST['LivAppIncDateFrDay'])?strtoupper(strip_tags(trim($_POST['LivAppIncDateFrDay']))):date('d');
	$LivAppIncDateFrMonth=isset($_POST['LivAppIncDateFrMonth'])?strtoupper(strip_tags(trim($_POST['LivAppIncDateFrMonth']))):date('m');
	$LivAppIncDateFrYear=isset($_POST['LivAppIncDateFrYear'])?strtoupper(strip_tags(trim($_POST['LivAppIncDateFrYear']))):date('Y');
	$LivAppIncDayTimeFrom=isset($_POST['LivAppIncDayTimeFrom'])?strtoupper(strip_tags(trim($_POST['LivAppIncDayTimeFrom']))):'AM';
	$LivAppIncDateToDay=isset($_POST['LivAppIncDateToDay'])?strtoupper(strip_tags(trim($_POST['LivAppIncDateToDay']))):date('d');
	$LivAppIncDateToMonth=isset($_POST['LivAppIncDateToMonth'])?strtoupper(strip_tags(trim($_POST['LivAppIncDateToMonth']))):date('m');
	$LivAppIncDateToYear=isset($_POST['LivAppIncDateToYear'])?strtoupper(strip_tags(trim($_POST['LivAppIncDateToYear']))):date('Y');
	$LivAppIncDayTimeTo=isset($_POST['LivAppIncDayTimeTo'])?strtoupper(strip_tags(trim($_POST['LivAppIncDayTimeTo']))):'PM';
	$LeaveTypeID=isset($_POST['LeaveTypeID'])?strtoupper(strip_tags(trim($_POST['LeaveTypeID']))):'';
	$LivAppTypeDetail=isset($_POST['LTypeDetail'])?strtoupper(strip_tags(trim($_POST['LTypeDetail']))):'';
	$LivAppNotes=isset($_POST['LivAppNotes'])?strtoupper(strip_tags(trim($_POST['LivAppNotes']))):'';
	
	$LivAppFiledDay=($LivAppFiledDay>9)?$LivAppFiledDay:"0".$LivAppFiledDay;
	$LivAppFiledMonth=($LivAppFiledMonth>9)?$LivAppFiledMonth:"0".$LivAppFiledMonth;
	$LivAppIncDateFrDay=($LivAppIncDateFrDay>9)?$LivAppIncDateFrDay:"0".$LivAppIncDateFrDay;
	$LivAppIncDateFrMonth=($LivAppIncDateFrMonth>9)?$LivAppIncDateFrMonth:"0".$LivAppIncDateFrMonth;
	$LivAppIncDateToDay=($LivAppIncDateToDay>9)?$LivAppIncDateToDay:"0".$LivAppIncDateToDay;
	$LivAppIncDateToMonth=($LivAppIncDateToMonth>9)?$LivAppIncDateToMonth:"0".$LivAppIncDateToMonth;
	
	
	$FlDth=date('U',mktime(00,00,00,intval($LivAppFiledMonth),intval($LivAppFiledDay),intval($LivAppFiledYear)));
	$HH=($LivAppIncDayTimeFrom=='AM')?8:13;
	$FrDth=date('U',mktime($HH,00,00,intval($LivAppIncDateFrMonth),intval($LivAppIncDateFrDay),intval($LivAppIncDateFrYear)));
	$HH=($LivAppIncDayTimeTo=='AM')?12:17;
	$ToDth=date('U',mktime($HH,00,00,intval($LivAppIncDateToMonth),intval($LivAppIncDateToDay),intval($LivAppIncDateToYear)));
	
	// logger("FrDth:".date("Y-m-d",$FrDth).",ToDth:".date("Y-m-d",$ToDth));
	
	/* ---------------- TESTING GROUND ----------------*/
	
	//echo "0|".$_SESSION['user']."|WARNING !!! ~<br/>Date of Leave: ".$LivAppIncDateFrYear.$LivAppIncDateFrMonth.$LivAppIncDateFrDay." to ".$LivAppIncDateToYear.$LivAppIncDateToMonth.$LivAppIncDateToDay."<br/>FrDth: $FrDth<br/>ToDth: $ToDth<br/>ft: ".date('U',mktime(00,00,00,intval($LivAppIncDateFrMonth),intval($LivAppIncDateFrDay),intval($LivAppIncDateFrYear)))."<br/>tt: ".date('U',mktime($HH,00,00,intval($LivAppIncDateToMonth),intval($LivAppIncDateToDay),intval($LivAppIncDateToYear)));exit();
	
	/* ------------------------------------------------*/
	
	$MySQLi=new MySQLClass();
	$fLeave=new LeaveFunctions();
	$fCOC=new COCFunctions();
	
	$LivAppFiledDate=$LivAppFiledYear."-".$LivAppFiledMonth."-".$LivAppFiledDay." ".date('H:i:s');
	
	$LivAppIncDateFrom=$LivAppIncDateFrYear."-".$LivAppIncDateFrMonth."-".$LivAppIncDateFrDay;
	$LivAppIncDateFrom.=($LivAppIncDayTimeFrom=="AM")?" 08:00:00":" 13:00:00";
	$LivAppIncDateTo=$LivAppIncDateToYear."-".$LivAppIncDateToMonth."-".$LivAppIncDateToDay;
	$LivAppIncDateTo.=($LivAppIncDayTimeTo=="AM")?" 12:00:00":" 17:00:00";
	
	$LivAppDays=$fLeave->NumberOfDays($FrDth,$ToDth,$LivAppIncDayTimeFrom,$LivAppIncDayTimeTo);
	
	if(($_SESSION['user']!=$EmpID)&&($LeaveTypeID!="LT08")&&$Authorization[6]){
		$LivAppStatus="2";
		$LivAppPreparedBy=$EmpID;
		$LivAppNotedBy="";
		$LivAppNotedTime=$LivAppCheckedTime=$TIMESTAMP;
		$LivAppNotedRemarks="NOTED: System entry by ".$_SESSION['username']." (".$_SESSION['user'].")";
		$LivAppCheckedBy=$_SESSION['user'];
		$LivAppCheckedRemarks="";
	}
	else{
		$LivAppStatus="0";
		$LivAppPreparedBy=$_SESSION['user'];
		$LivAppNotedBy="";
		$LivAppNotedTime=$LivAppCheckedTime="1970-01-01 00:00:01";
		$LivAppNotedRemarks="";
		$LivAppCheckedBy="";
		$LivAppCheckedRemarks="";
	}
	
	if($mode=="0"){
		if(!(($_SESSION['user']==$EmpID)||$Authorization[6])){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		
		$fLeave->checkDateRange($FrDth,$ToDth);
		$fLeave->checkFilingDate($FlDth,$FrDth,$ToDth,$LeaveTypeID);
		
		$lTypeName=$fLeave->GetLeaveName($LeaveTypeID);
		$aBalance=$fLeave->AvailableLeaveCredit($EmpID,$LeaveTypeID,$LivAppIncDateFrYear);
		$MSG ="Available $lTypeName: <strong>".number_format($aBalance,3)."</strong> day(s)";
		$MSG.="<br>Leave Days Applied: <strong>".number_format($LivAppDays,3)."</strong> day(s)";
		if($aBalance<$LivAppDays){
			$oBalance=$LivAppDays-$aBalance;
			$MSG ="Insufficient $lTypeName credit.";
			$MSG.="<br>Available $lTypeName: <strong>".number_format($aBalance,3)."</strong> day(s)";
			$MSG.="<br>Leave Days Applied: <strong>".number_format($LivAppDays,3)."</strong> day(s)";
			if($LeaveTypeID=="LT02"){
				$MSG.="<br>VACATION LEAVE credit will be used: <strong>".number_format($oBalance,3)."</strong> day(s)";
				$vBalance=$fLeave->AvailableLeaveCredit($EmpID,'LT01',$LivAppIncDateFrYear);
				if($oBalance>$vBalance){
					$MSG.="<br>Available VACATION LEAVE: <strong>".number_format($vBalance,3)."</strong> day(s)";
					$MSG.="<br>Leave without pay: <strong style='color:red;'>".number_format($oBalance-$vBalance,3)."</strong> day(s)";
				}
			}
			else{
				$MSG.="<br>Leave without pay: <strong style='color:red;'>".number_format($oBalance,3)."</strong> day(s)";
			}
		}
		
		/* Get New LivAppID */
		$NewLivAppID="LA".date('y').date('m')."001";
		$ccc=1;
		while($records=mysqli_fetch_array($MySQLi->sqlQuery("SELECT `LivAppID` FROM `tblempleaveapplications` WHERE `LivAppID`='$NewLivAppID';"), MYSQLI_BOTH)) {
			$ccc+=1;
			$ccc=($ccc>99)?$ccc:(($ccc>9)?"0".$ccc:"00".$ccc);
			$NewLivAppID="LA".date('y').date('m').$ccc;
		}$LivAppID=$NewLivAppID;
		
		$sql = "INSERT INTO `tblempleaveapplications` (`LivAppID`, `EmpID`, `LivAppFiledDate`, `LeaveTypeID`, `LivAppTypeDetail`, `LivAppNotes`, `LivAppDays`, `LivAppIncDayTimeFrom`, `LivAppIncDateFrom`, `LivAppIncDayTimeTo`, `LivAppIncDateTo`, `LivAppWithPay`, `LivAppStatus`, `LivAppPreparedBy`, `LivAppPreparedTime`, `LivAppNotedBy`, `LivAppNotedTime`, `LivAppNotedRemarks`, `LivAppCheckedBy`, `LivAppCheckedTime`, `LivAppCheckedRemarks`, `LivAppApprovedDays`, `LivAppApprovedBy`, `LivAppApprovedTime`, `LivAppApprovedRemarks`, `RECORD_TIME`) VALUES ('$LivAppID', '$EmpID', '$LivAppFiledDate', '$LeaveTypeID', '$LivAppTypeDetail', '$LivAppNotes', '$LivAppDays', '$LivAppIncDayTimeFrom', '$LivAppIncDateFrom', '$LivAppIncDayTimeTo', '$LivAppIncDateTo', '', '$LivAppStatus', '$LivAppPreparedBy', '$TIMESTAMP', '$LivAppNotedBy', '$LivAppNotedTime', '$LivAppNotedRemarks', '$LivAppCheckedBy', '$LivAppCheckedTime', '$LivAppCheckedRemarks', '0', '', '1970-01-01 00:00:01', '', NOW());";
		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|$MSG<br><br>Leave application was successfully added.";}
	}
	
	else if($mode=="1") {
		if(!(($_SESSION['user']==$EmpID)||$Authorization[6])){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		// logger("FrDth: $FrDth, ToDth: $ToDth");
		logger("test");
		$fLeave->checkDateRange($FrDth,$ToDth);
		$fLeave->checkFilingDate(date('U'),$FrDth,$ToDth,$LeaveTypeID);
		
		//if(($LeaveTypeID=="LT03")&&($fLeave->AvailableLeaveCredit($EmpID,"LT03",$LivAppIncDateFrYear)<$LivAppDays)){echo "0|".$_SESSION['user']."|ERROR 406:~Insufficient Privilege leave credit.";exit();}
		
		$lTypeName=$fLeave->GetLeaveName($LeaveTypeID);
		$aBalance=$fLeave->AvailableLeaveCredit($EmpID,$LeaveTypeID,$LivAppIncDateFrYear);
		$MSG ="Available $lTypeName: <strong>".number_format($aBalance,3)."</strong> day(s)";
		$MSG.="<br>Leave Days Applied: <strong>".number_format($LivAppDays,3)."</strong> day(s)";
		if($aBalance<$LivAppDays){
			$oBalance=$LivAppDays-$aBalance;
			$MSG ="Insufficient $lTypeName credit.";
			$MSG.="<br>Available $lTypeName: <strong>".number_format($aBalance,3)."</strong> day(s)";
			$MSG.="<br>Leave Days Applied: <strong>".number_format($LivAppDays,3)."</strong> day(s)";
			if($LeaveTypeID=="LT02"){
				$MSG.="<br>VACATION LEAVE credit will be used: <strong>".number_format($oBalance,3)."</strong> day(s)";
				$vBalance=$fLeave->AvailableLeaveCredit($EmpID,'LT01',$LivAppIncDateFrYear);
				if($oBalance>$vBalance){
					$MSG.="<br>Available VACATION LEAVE: <strong>".number_format($vBalance,3)."</strong> day(s)";
					$MSG.="<br>Leave without pay: <strong style='color:red;'>".number_format($oBalance-$vBalance,3)."</strong> day(s)";
				}
			}
			else{
				$MSG.="<br>Leave without pay: <strong style='color:red;'>".number_format($oBalance,3)."</strong> day(s)";
			}
		}
		
		$LeaveStatus=$MySQLi->GetArray("SELECT `LivAppStatus` FROM `tblempleaveapplications` WHERE `LivAppID`='$LivAppID';");
		if($LeaveStatus['LivAppStatus']!='0'){echo "0|".$_SESSION['user']."|ERROR 400:~Can't update leave information.<br/>";exit();}
		else {
			$sql="UPDATE `tblempleaveapplications` SET `LivAppFiledDate`='$LivAppFiledDate', `LivAppIncDayTimeFrom`='$LivAppIncDayTimeFrom',`LivAppIncDateFrom`='$LivAppIncDateFrom',`LivAppIncDayTimeTo`='$LivAppIncDayTimeTo',`LivAppIncDateTo`='$LivAppIncDateTo',`LeaveTypeID`='$LeaveTypeID', `LivAppTypeDetail`='$LivAppTypeDetail', `LivAppNotes`='$LivAppNotes',`LivAppDays`='$LivAppDays',`RECORD_TIME`=NOW() WHERE `LivAppID`='$LivAppID' LIMIT 1;";
			if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|$MSG<br><br>Leave application was successfully updated.";}
		}
	}
	
	else if($mode=="-1") {
		if(!(($_SESSION['user']==$EmpID)||$Authorization[6])){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		if(!$Authorization[3]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		
		$LeaveStatus=$MySQLi->GetArray("SELECT `LivAppStatus` FROM `tblempleaveapplications` WHERE `LivAppID`='$LivAppID';");
		if($LeaveStatus['LivAppStatus']>'0'){echo "0|".$_SESSION['user']."|ERROR 400:~Can't delete leave information.";exit();}
		else {
			$sql="DELETE FROM `tblempleaveapplications` WHERE `LivAppID`='$LivAppID' LIMIT 1;";
			if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Leave application was successfully deleted.";}
		}
	}
	else {echo "0";}
	ob_end_flush();
?>