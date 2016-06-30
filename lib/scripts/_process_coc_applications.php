<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD020'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	require_once $_SESSION['path'].'/lib/classes/Functions.php';
	
	
	$TIMESTAMP=date('Y-m-d H:i:s');
	$MONTHS=Array('','JANUARY','FEBRUARY','MARCH','APRIL','MAY','JUNE','JULY','AUGUST','SEPTEMBER','OCTOBER','NOVEMBER','DECEMBER');
	$MSG="";
	
	/* Get POST Values */
	$docAction=isset($_POST['act'])?strtoupper(strip_tags(trim($_POST['act']))):'';
	$COCID=isset($_POST['did'])?strtoupper(strip_tags(trim($_POST['did']))):'';
	$Remark=isset($_POST['rm'])?strtoupper(strip_tags(trim($_POST['rm']))):'';
	
	$MySQLi=new MySQLClass();
	
	/* Check document if existing and get current status */
	if($COC=$MySQLi->GetArray("SELECT `EmpID`,`COCEarnedDate`,`COCEarnedHours`,`COCStatus`,`COCPreparedBy` FROM `tblempcocs` WHERE `COCID`='$COCID';")){
		$EmpID=$COC['EmpID'];
		$COCEarnedDate=$COC['COCEarnedDate'];
		$COCEarnedHours=$COC['COCEarnedHours'];
		$COCCurrentStatus=$COC['COCStatus'];
	}
	else{echo "0|".$_SESSION['user']."|ERROR 404:~Can't find requested record.";exit();}
	
	/* No action for disapproved applications */
	if($COCCurrentStatus==-1){echo "0|".$_SESSION['user']."|ERROR 400:~COC Application was already DISAPPROVED.";exit();}
	
	
	if($docAction=="1"){ /* NEW, for posting */
		$MSG="COC Application was successfully posted.";
		if(!$Authorization[5]){
			if(!$Authorization[4]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
			if($_SESSION['user']!=$COC['COCPreparedBy']){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
			if($COCCurrentStatus==1){echo "0|".$_SESSION['user']."|ERROR 400:~COC Application is already POSTED.";exit();}
		}
		else{
			if($COCCurrentStatus==1){$MSG="COC Application was cancelled.";}
		}
		/* Change document status to (1) - POSTED */
		$sql="UPDATE `tblempcocs` SET `COCStatus`='1',`RECORD_TIME`=NOW() WHERE `COCID`='$COCID' LIMIT 1;";
		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|$MSG";}
	}
	
	
	else if($docAction=="2") { /* POSTED, for noting */
		if($COCCurrentStatus==0){echo "0|".$_SESSION['user']."|ERROR 400:~COC Application is not yet POSTED.";exit();}
		$MSG="COC Application was successfully noted.";
		if(!$Authorization[6]){
			if(!$Authorization[5]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
			if($COCCurrentStatus==2){echo "0|".$_SESSION['user']."|ERROR 400:~COC Application is already NOTED.";exit();}
		}
		else{
			if($COCCurrentStatus==2){$MSG="Confirmation of COC Application was revoked.";}
		}
		/* Change document status to (2) - NOTED */
		$sql="UPDATE `tblempcocs` SET `COCNotedBy`='".$_SESSION['user']."',`COCNotedTime`='$TIMESTAMP',`COCNotedRemarks`='$Remark',`COCStatus`='2',`RECORD_TIME`=NOW() WHERE `COCID`='$COCID' LIMIT 1;";
		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|$MSG";}
	}
	
	
	else if($docAction=="3") { /* NOTED, for checking */
		if($COCCurrentStatus<2){echo "0|".$_SESSION['user']."|ERROR 400:~COC Application is not yet NOTED by respected AO or Department Head.";exit();}
		$MSG="COC Application was confirmed.";
		if(!$Authorization[7]){
			if(!$Authorization[6]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
			if($COCCurrentStatus==3){echo "0|".$_SESSION['user']."|ERROR 400:~COC Application is already CHECKED.";exit();}
		}
		else{
			if($COCCurrentStatus==3){$MSG="Approval of COC Application was revoked.";}
		}
		/* Change document status to (3) - CHECKED */
		$sql="UPDATE `tblempcocs` SET `COCCheckedBy`='".$_SESSION['user']."',`COCCheckedTime`='$TIMESTAMP',`COCCheckedRemarks`='$Remark',`COCStatus`='3',`RECORD_TIME`=NOW() WHERE `COCID`='$COCID' LIMIT 1;";
		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|$MSG";}
	}
	
	
	else if($docAction=="4") { /* CHECKED, for approval */
		if(!$Authorization[7]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		if($COCCurrentStatus==4){echo "0|".$_SESSION['user']."|ERROR 400:~COC Application is already APPROVED.";exit();}
		if($COCCurrentStatus<3){echo "0|".$_SESSION['user']."|ERROR 400:~COC Application is not yet CHECKED.";exit();}
		$sql="UPDATE `tblempcocs` SET `COCApprovedBy`='".$_SESSION['user']."',`COCApprovedTime`='$TIMESTAMP',`COCApprovedRemarks`='$Remark',`COCStatus`='4',`RECORD_TIME`=NOW() WHERE `COCID`='$COCID' LIMIT 1;";
		if($MySQLi->sqlQuery($sql)){
			/* Update EmpLeaveCredit */
			$fCOC=new COCFunctions();
			$AvailableCOCs=$fCOC->AvailableCOCs($EmpID);
			$fCOC->UpdateCOCs($EmpID);
			$LeaveTypeID="LT08";
			$fLeave=new LeaveFunctions();
			$LivCredID=$fLeave->GetNewLivCredID($EmpID);
			$sql="INSERT INTO `tblempleavecredits` (`LivCredID`, `EmpID`, `LeaveTypeID`, `LivCredDateFrom`, `LivCredDateTo`, `LivCredAddTo`, `LivCredDeductTo`, `LivCredBalance`, `LivCredReference`, `LivCredRemarks`, `RECORD_TIME`) VALUES ('".$LivCredID."', '".$EmpID."', '".$LeaveTypeID."', '".$COCEarnedDate."', '".$COCEarnedDate."', '".$COCEarnedHours."', '0', '".$AvailableCOCs."', '".$COCID."', 'Approved COC', NOW());";
			$MySQLi->sqlQuery($sql);
			
			echo "1|$EmpID|COC Application was approved.";
		}
	}
	
	
	else if($docAction=="-1") { /* disapproval */
		if(!$Authorization[7]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		$sql="UPDATE `tblempcocs` SET `COCApprovedBy`='".$_SESSION['user']."',`COCApprovedTime`='$TIMESTAMP',`COCApprovedRemarks`='$Remark',`COCStatus`='-1',`RECORD_TIME`=NOW() WHERE `COCID`='$COCID' LIMIT 1;";
		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|COC Application was disapproved.";}
	}
	
	
	else {echo "0";}
	ob_end_flush();
?>