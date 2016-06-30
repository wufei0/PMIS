<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD004'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$MySQLi=new MySQLClass();
	//Get POST Values
	$mode=isset($_POST['mode'])?strip_tags(trim($_POST['mode'])):'';
	$EmpID=isset($_POST['EmpID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpID'])))):'';
	$EmpSpsLName=isset($_POST['EmpSpsLName'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpSpsLName'])))):'';
	$EmpSpsMName=isset($_POST['EmpSpsMName'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpSpsMName'])))):'';
	$EmpSpsFName=isset($_POST['EmpSpsFName'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpSpsFName'])))):'';
	$EmpSpsExtName=isset($_POST['EmpSpsExtName'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpSpsExtName'])))):'';
	$EmpSpsAddSt=isset($_POST['EmpSpsAddSt'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpSpsAddSt'])))):'';
	$EmpSpsAddBrgy=isset($_POST['EmpSpsAddBrgy'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpSpsAddBrgy'])))):'';
	$EmpSpsAddMun=isset($_POST['EmpSpsAddMun'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpSpsAddMun'])))):'';
	$EmpSpsAddProv=isset($_POST['EmpSpsAddProv'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpSpsAddProv'])))):'';
	$EmpSpsZipCode=isset($_POST['EmpSpsZipCode'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpSpsZipCode'])))):'';
	$EmpSpsTel=isset($_POST['EmpSpsTel'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpSpsTel'])))):'';
	$EmpSpsJob=isset($_POST['EmpSpsJob'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpSpsJob'])))):'';
	$EmpSpsBusDesc=isset($_POST['EmpSpsBusDesc'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpSpsBusDesc'])))):'';
	$EmpSpsBusAddSt=isset($_POST['EmpSpsBusAddSt'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpSpsBusAddSt'])))):'';
	$EmpSpsBusAddBrgy=isset($_POST['EmpSpsBusAddBrgy'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpSpsBusAddBrgy'])))):'';
	$EmpSpsBusAddMun=isset($_POST['EmpSpsBusAddMun'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpSpsBusAddMun'])))):'';
	$EmpSpsBusAddProv=isset($_POST['EmpSpsBusAddProv'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpSpsBusAddProv'])))):'';
	$EmpSpsBusZipCode=isset($_POST['EmpSpsBusZipCode'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpSpsBusZipCode'])))):'';
	$EmpSpsBusTel=isset($_POST['EmpSpsBusTel'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpSpsBusTel'])))):'';
	
	
	if($mode=="0"){}
	else if ($mode=="1") {
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
		$sql="UPDATE `pmis`.`tblemppersonalinfo` SET `EmpSpsLName`='$EmpSpsLName',`EmpSpsMName`='$EmpSpsMName',`EmpSpsFName`='$EmpSpsFName',`EmpSpsExtName`='$EmpSpsExtName',`EmpSpsAddSt`='$EmpSpsAddSt',`EmpSpsAddBrgy`='$EmpSpsAddBrgy',`EmpSpsAddMun`='$EmpSpsAddMun',`EmpSpsAddProv`='$EmpSpsAddProv',`EmpSpsZipCode`='$EmpSpsZipCode',`EmpSpsTel`='$EmpSpsTel',`EmpSpsJob`='$EmpSpsJob',`EmpSpsBusDesc`='$EmpSpsBusDesc',`EmpSpsBusAddSt`='$EmpSpsBusAddSt',`EmpSpsBusAddBrgy`='$EmpSpsBusAddBrgy',`EmpSpsBusAddMun`='$EmpSpsBusAddMun',`EmpSpsBusAddProv`='$EmpSpsBusAddProv',`EmpSpsBusZipCode`='$EmpSpsBusZipCode',`EmpSpsBusTel`='$EmpSpsBusTel',`RECORD_TIME`=NOW() WHERE `EmpID`='$EmpID' LIMIT 1;";

		if($MySQLi->sqlQuery($sql)) {echo "1|$EmpID|Spouse information was successfully updated.";}
	}
	else{echo "0|$EmpID|ERROR ???:<br/>Unkown mode.";}
	ob_end_flush();
?>