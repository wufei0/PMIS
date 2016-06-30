<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD014'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$MySQLi=new MySQLClass();
	
	//Get POST Values
	$mode=isset($_POST['mode'])?strip_tags(trim($_POST['mode'])):'';
	$EmpID=isset($_POST['EmpID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpID'])))):'';
	$MemAssOrgID=isset($_POST['MemAssOrgID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['MemAssOrgID'])))):'';
	$MemAssOrgDesc=isset($_POST['MemAssOrgDesc'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['MemAssOrgDesc'])))):'';

	
	if($mode=="0"){
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
		//Get New MemAssOrgID
		$NewMemAssOrgID="MO".$EmpID."01";
		$count=1;
		while($records=$MySQLi->GetArray("SELECT `MemAssOrgID` FROM `tblempassorgmembership` WHERE `MemAssOrgID`='$NewMemAssOrgID';")) {
			$count+=1;
			$ccc=($count>9)?$count:"0".$count;
			$NewMemAssOrgID="MO".$EmpID.$ccc;
		} $MemAssOrgID=$NewMemAssOrgID;
		$sql="INSERT INTO `tblempassorgmembership` (`MemAssOrgID`,`EmpID`,`MemAssOrgDesc`,`RECORD_TIME`) VALUES ('$MemAssOrgID','$EmpID','$MemAssOrgDesc',NOW());";

		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Membership record was successfully added.";}
	}
	else if($mode=="1") {
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
		$sql="UPDATE `tblempassorgmembership` SET `MemAssOrgDesc`='$MemAssOrgDesc',`RECORD_TIME`=NOW() WHERE `EmpID`='$EmpID' AND `MemAssOrgID`='$MemAssOrgID' LIMIT 1;";
		
		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Membership record was successfully updated.";}
	}
	else if($mode=="-1") {
		if(!$Authorization[3]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
		$sql="DELETE FROM `tblempassorgmembership` WHERE `EmpID`='$EmpID' AND `MemAssOrgID`='$MemAssOrgID' LIMIT 1;";
		
		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Membership record was successfully deleted.";}
	}
	else{echo "0|$EmpID|ERROR ???:<br/>Unkown mode.";}
	ob_end_flush();
?>