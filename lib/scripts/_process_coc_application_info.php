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
	

	$MySQLi=new MySQLClass();
	
	//Get POST Values
	$mode=isset($_POST['mode'])?$MySQLi->RealEscapeString(strip_tags(trim($_POST['mode']))):'';
	$EmpID=isset($_POST['EmpID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpID'])))):'';
	$COCID=isset($_POST['COCID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['COCID'])))):'';
	$COCEarnedDateDay=isset($_POST['COCEarnedDateDay'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['COCEarnedDateDay'])))):date('d');
	$COCEarnedDateMonth=isset($_POST['COCEarnedDateMonth'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['COCEarnedDateMonth'])))):date('m');
	$COCEarnedDateYear=isset($_POST['COCEarnedDateYear'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['COCEarnedDateYear'])))):date('Y');
	$COCEarnedHours=isset($_POST['COCEarnedHours'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['COCEarnedHours'])))):'';
	$COCNotes=isset($_POST['COCNotes'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['COCNotes'])))):'';
	
	$COCEarnedDateDay=($COCEarnedDateDay>9)?$COCEarnedDateDay:"0".$COCEarnedDateDay;
	$COCEarnedDateMonth=($COCEarnedDateMonth>9)?$COCEarnedDateMonth:"0".$COCEarnedDateMonth;
	$COCEarnedDate=$COCEarnedDateYear."-".$COCEarnedDateMonth."-".$COCEarnedDateDay." 00:00:00";	
	
	$TIMESTAMP=date('Y-m-d H:i:s');
	$COCRemainingHours=$COCEarnedHours;
	$COCExpireDate=($COCEarnedDateYear+1)."-".$COCEarnedDateMonth."-".$COCEarnedDateDay." 23:59:59";
	$COCPreparedBy=$_SESSION['user'];
	

	if($mode=="0"){
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		
		$fCOC=new COCFunctions();
		$COCID=$fCOC->GetNewCOCID();
		
		$sql = "INSERT INTO `tblempcocs` (`COCID`, `EmpID`, `COCEarnedDate`, `COCEarnedHours`, `COCRemainingHours`, `COCExpireDate`, `COCStatus`, `COCPreparedBy`, `COCPreparedTime`, `COCNotes`, `COCNotedBy`, `COCNotedTime`, `COCNotedRemarks`, `COCCheckedBy`, `COCCheckedTime`, `COCCheckedRemarks`, `COCApprovedBy`, `COCApprovedTime`, `COCApprovedRemarks`, `RECORD_TIME`) VALUES ('$COCID', '$EmpID', '$COCEarnedDate', '$COCEarnedHours', '$COCRemainingHours', '$COCExpireDate', '0', '$COCPreparedBy', '$TIMESTAMP', '$COCNotes', '', '1970-01-01 00:00:01', '', '', '1970-01-01 00:00:01', '', '', '1970-01-01 00:00:01', '', NOW());";
		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|New COC information was successfully added.<br/>";}
	}
	
	else if($mode=="1"){
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		$COC=$MySQLi->GetArray("SELECT `COCStatus`, `COCPreparedBy` FROM `tblempcocs` WHERE `COCID`='$COCID';");
		if($_SESSION['user']!=$COC['COCPreparedBy']){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		if($COC['COCStatus']!='0'){echo "0|".$_SESSION['user']."|ERROR 400:~Can't update COC information.";exit();}
		else{
			$sql="UPDATE `tblempcocs` SET `COCEarnedDate`='$COCEarnedDate',`COCEarnedHours`='$COCEarnedHours',`COCRemainingHours`='$COCRemainingHours',`COCExpireDate`='$COCExpireDate',`COCNotes`='$COCNotes',`COCPreparedBy`='$COCPreparedBy',`COCPreparedTime`='$TIMESTAMP',`RECORD_TIME`=NOW() WHERE `COCID`='$COCID' LIMIT 1;";
			if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Leave application was successfully updated.";}
		}
	}
	
	else if($mode=="-1"){
		if(!$Authorization[3]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		$COC=$MySQLi->GetArray("SELECT `COCStatus`, `COCPreparedBy` FROM `tblempcocs` WHERE `COCID`='$COCID';");
		if($_SESSION['user']!=$COC['COCPreparedBy']){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		if($COC['COCStatus']>'0'){echo "0|".$_SESSION['user']."|ERROR 400:~Can't delete leave information.";exit();}
		else{
			$sql="DELETE FROM `tblempcocs` WHERE `COCID`='$COCID' LIMIT 1;";
			if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Leave application was successfully deleted.";}
		}
	}
	else {echo "0";}
	ob_end_flush();
?>