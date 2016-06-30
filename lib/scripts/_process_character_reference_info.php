<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD015'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	
	
	
	$MySQLi=new MySQLClass();
	
	//Get POST Values
	$mode=isset($_POST['mode'])?strip_tags(trim($_POST['mode'])):'';
	$EmpID=isset($_POST['EmpID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpID'])))):'';
	$RefID=isset($_POST['RefID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['RefID'])))):'';
	$RefLName=isset($_POST['RefLName'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['RefLName'])))):'';
	$RefMName=isset($_POST['RefMName'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['RefMName'])))):'';
	$RefFName=isset($_POST['RefFName'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['RefFName'])))):'';
	$RefExtName=isset($_POST['RefExtName'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['RefExtName'])))):'';
	$RefAddSt=isset($_POST['RefAddSt'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['RefAddSt'])))):'';
	$RefAddBrgy=isset($_POST['RefAddBrgy'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['RefAddBrgy'])))):'0';
	$RefAddMun=isset($_POST['RefAddMun'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['RefAddMun'])))):'0';
	$RefAddProv=isset($_POST['RefAddProv'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['RefAddProv'])))):'0';
	$RefZipCode=isset($_POST['RefZipCode'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['RefZipCode'])))):'0';
	$RefTel=isset($_POST['RefTel'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['RefTel'])))):'';

	
	if($mode=="0"){
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		//Get New RefID
		$NewRefID="CR".$EmpID."01";
		$count=1;
		while($records=$MySQLi->GetArray("SELECT `RefID` FROM `tblempreferences` WHERE `RefID`='$NewRefID';")){
			$count+=1;
			$ccc=($count>9)?$count:"0".$count;
			$NewRefID="CR".$EmpID.$ccc;
		} $RefID=$NewRefID;
		$sql="INSERT INTO `tblempreferences` (`RefID`,`EmpID`,`RefLName`,`RefMName`,`RefFName`,`RefExtName`,`RefAddSt`,`RefAddBrgy`,`RefAddMun`,`RefAddProv`,`RefZipCode`,`RefTel`,`RECORD_TIME`) VALUES ('$RefID','$EmpID','$RefLName','$RefMName','$RefFName','$RefExtName','$RefAddSt','$RefAddBrgy','$RefAddMun','$RefAddProv','$RefZipCode','$RefTel',NOW());";
		
		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Membership record was successfully added.";}
	}
	else if($mode=="1") {
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		$sql="UPDATE `tblempreferences` SET `RefLName`='$RefLName',`RefMName`='$RefMName',`RefFName`='$RefFName',`RefExtName`='$RefExtName',`RefAddSt`='$RefAddSt',`RefAddBrgy`='$RefAddBrgy',`RefAddMun`='$RefAddMun',`RefAddProv`='$RefAddProv',`RefZipCode`='$RefZipCode',`RefTel`='$RefTel',`RECORD_TIME`=NOW() WHERE `EmpID`='$EmpID' AND `RefID`='$RefID' LIMIT 1;";
		
		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Membership record was successfully updated.";}
	}
	else if($mode=="-1") {
		if(!$Authorization[3]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		$sql="DELETE FROM `tblempreferences` WHERE `EmpID`='$EmpID' AND `RefID`='$RefID' LIMIT 1;";
		
		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Membership record was successfully deleted.";}
	}
	else{echo "0|$EmpID|ERROR ???:<br/>Unkown mode.";}
	ob_end_flush();
?>