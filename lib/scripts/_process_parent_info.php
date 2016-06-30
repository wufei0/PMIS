<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD006'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$MySQLi=new MySQLClass();
	
	//Get POST Values
	$mode=isset($_POST['mode'])?strip_tags(trim($_POST['mode'])):'';
	$EmpID=isset($_POST['EmpID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpID'])))):'';
	$EmpFatherLName=isset($_POST['EmpFatherLName'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpFatherLName'])))):'';
	$EmpFatherMName=isset($_POST['EmpFatherMName'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpFatherMName'])))):'';
	$EmpFatherFName=isset($_POST['EmpFatherFName'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpFatherFName'])))):'';
	$EmpFatherExtName=isset($_POST['EmpFatherExtName'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpFatherExtName'])))):'';
	$EmpMotherLName=isset($_POST['EmpMotherLName'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpMotherLName'])))):'';
	$EmpMotherMName=isset($_POST['EmpMotherMName'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpMotherMName'])))):'';
	$EmpMotherFName=isset($_POST['EmpMotherFName'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpMotherFName'])))):'';
	$EmpMotherExtName=isset($_POST['EmpMotherExtName'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpMotherExtName'])))):'';

	
	if ($mode=="1") {
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
		$sql="UPDATE `tblemppersonalinfo` SET `EmpFatherLName`='$EmpFatherLName', `EmpFatherMName`='$EmpFatherMName', `EmpFatherFName`='$EmpFatherFName', `EmpFatherExtName`='$EmpFatherExtName', `EmpMotherLName`='$EmpMotherLName', `EmpMotherMName`='$EmpMotherMName', `EmpMotherFName`='$EmpMotherFName', `RECORD_TIME`=NOW() WHERE `EmpID`='$EmpID' LIMIT 1;";

		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Parents Information was successfully deleted.";}
	}
	else{echo "0|$EmpID|ERROR ???:<br/>Unkown mode.";}
	ob_end_flush();
?>