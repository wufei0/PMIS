<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD001'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	//Get POST Values
	$mode=isset($_POST['mode'])?strip_tags(trim($_POST['mode'])):'x';
	$UserID=isset($_POST['UsrID'])?strtoupper(strip_tags(trim($_POST['UsrID']))):'';
	$OldKey=isset($_POST['OldKey'])?strip_tags(trim($_POST['OldKey'])):'0';
	$EmpAccessKey=isset($_POST['NewKey2'])?strip_tags(trim($_POST['NewKey2'])):'0';
	$UserGroupID=isset($_POST['UsrGrpID'])?strtoupper(strip_tags(trim($_POST['UsrGrpID']))):'';
	
	
	$MySQLi=new MySQLClass();
	
	if($mode=="0"){
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		$sql="SELECT `EmpID`, `UserGroupID` FROM `tblemppersonalinfo` WHERE `EmpID` = '$UserID' AND EmpAccessKey<>'' AND `UserGroupID`<>'USRGRP000' LIMIT 1;";
		if($MySQLi->NumberOfRows($sql)>0){echo "0|$UserID|ERROR 405:~Can't activate user $UserID. User ID already in the user list.";}
		else{
			$sql="UPDATE `tblemppersonalinfo` SET `UserGroupID`='$UserGroupID',`EmpAccessKey`='".md5($EmpAccessKey)."',`RECORD_TIME`=NOW() WHERE `EmpID`='$UserID' LIMIT 1;";
			if($MySQLi->sqlQuery($sql)) {echo "1|$UserID|User ID $UserID is now activated.";}
		}
	}
	else if($mode=="1") {
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		$sql="SELECT `EmpID`, `UserGroupID` FROM `tblemppersonalinfo` WHERE `EmpID` = '$UserID' AND EmpAccessKey='".md5($OldKey)."' LIMIT 1;";
		if($MySQLi->NumberOfRows($sql)<1){echo "0|$UserID|ERROR 406:~Can't update user $UserID<br/>Invalid Current Password.";}
		else{
			$sql="UPDATE `tblemppersonalinfo` SET `UserGroupID`='$UserGroupID',`EmpAccessKey`='".md5($EmpAccessKey)."',`RECORD_TIME`=NOW() WHERE `EmpID`='$UserID' LIMIT 1;";
			if($MySQLi->sqlQuery($sql)) {echo "1|$UserID|User ID $UserID was updated.";}
		}
	}
	else if($mode=="2") {
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		$sql="SELECT `EmpID`, `UserGroupID` FROM `tblemppersonalinfo` WHERE `EmpID` = '$UserID' AND EmpAccessKey='".md5($OldKey)."' LIMIT 1;";
		if($MySQLi->NumberOfRows($sql)<1){echo "0|$UserID|ERROR 406:~Can't update user $UserID<br/>Invalid Current Password.";}
		else{
			$sql="UPDATE `tblemppersonalinfo` SET `EmpAccessKey`='".md5($EmpAccessKey)."',`RECORD_TIME`=NOW() WHERE `EmpID`='$UserID' LIMIT 1;";
			if($MySQLi->sqlQuery($sql)) {echo "1|$UserID|User ID $UserID was updated.";}
		}
	}
	else if($mode=="-1") {
		if(!$Authorization[3]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		$sql="UPDATE `tblemppersonalinfo` SET `UserGroupID`='USRGRP000',`EmpAccessKey`=NULL,`RECORD_TIME`=NOW() WHERE `EmpID`='$UserID' LIMIT 1;";
		if($MySQLi->sqlQuery($sql)) {echo "1|$UserID|User ID $UserID was deactivated.";}
	}
	ob_end_flush();
?>