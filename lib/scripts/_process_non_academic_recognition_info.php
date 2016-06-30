<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD013'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$MySQLi=new MySQLClass();
	
	//Get POST Values
	$mode=isset($_POST['mode'])?strip_tags(trim($_POST['mode'])):'';
	$EmpID=isset($_POST['EmpID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpID'])))):'';
	$NonAcadRecID=isset($_POST['NonAcadRecID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['NonAcadRecID'])))):'';
	$NonAcadRecDetails=isset($_POST['NonAcadRecDetails'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['NonAcadRecDetails'])))):'';

	
	if($mode=="0"){
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
		//Get New NonAcadRecID
		$NewNonAcadRecID="NR".$EmpID."01";
		$count=1;
		while($records=$MySQLi->GetArray("SELECT `NonAcadRecID` FROM `tblempnonacadrecognitions` WHERE `NonAcadRecID`='$NewNonAcadRecID';")) {
			$count+=1;
			$ccc=($count>9)?$count:"0".$count;
			$NewNonAcadRecID="NR".$EmpID.$ccc;
		} $NonAcadRecID=$NewNonAcadRecID;
		$sql="INSERT INTO `tblempnonacadrecognitions` (`NonAcadRecID`,`EmpID`,`NonAcadRecDetails`,`RECORD_TIME`) VALUES ('$NonAcadRecID','$EmpID','$NonAcadRecDetails',NOW());";

		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Reward/Recognition record was successfully added.";}
		else{echo "0|$EmpID|ERROR ".mysql_errno().":<br/>".mysql_error();}
	}
	else if($mode=="1") {
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
		$sql="UPDATE `tblempnonacadrecognitions` SET `NonAcadRecDetails`='$NonAcadRecDetails',`RECORD_TIME`=NOW() WHERE `EmpID`='$EmpID' AND `NonAcadRecID`='$NonAcadRecID' LIMIT 1;";
		
		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Reward/Recognition record was successfully updated.";}
		else{echo "0|$EmpID|ERROR ".mysql_errno().":<br/>".mysql_error();}
	}
	else if($mode=="-1") {
		if(!$Authorization[3]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
		$sql="DELETE FROM `tblempnonacadrecognitions` WHERE `EmpID`='$EmpID' AND `NonAcadRecID`='$NonAcadRecID' LIMIT 1;";
		
		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Reward/Recognition record was successfully deleted.";}
		else{echo "0|$EmpID|ERROR ".mysql_errno().":<br/>".mysql_error();}
	}
	else{echo "0|$EmpID|ERROR ???:<br/>Unkown mode.";}
	ob_end_flush();
?>