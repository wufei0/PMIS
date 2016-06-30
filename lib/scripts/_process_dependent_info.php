<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD005'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$MySQLi=new MySQLClass();
	//Get POST Values
	$mode=isset($_POST['mode'])?strip_tags(trim($_POST['mode'])):'';
	$EmpID=isset($_POST['EmpID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpID'])))):'';
	$DpntID=isset($_POST['DpntID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['DpntID'])))):'';
	$DpntLName=isset($_POST['DpntLName'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['DpntLName'])))):'';
	$DpntMName=isset($_POST['DpntMName'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['DpntMName'])))):'';
	$DpntFName=isset($_POST['DpntFName'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['DpntFName'])))):'';
	$DpntExtName=isset($_POST['DpntExtName'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['DpntExtName'])))):'';
	$DpntBirthDay=isset($_POST['DpntBirthDay'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['DpntBirthDay'])))):'';
	$DpntBirthMonth=isset($_POST['DpntBirthMonth'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['DpntBirthMonth'])))):'';
	$DpntBirthYear=isset($_POST['DpntBirthYear'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['DpntBirthYear'])))):'';
	$RelID=isset($_POST['RelID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['RelID'])))):'0';
	$RelDesc=isset($_POST['RelDesc'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['RelDesc'])))):'0';
	$DpntRemarks=isset($_POST['DpntRemarks'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['DpntRemarks'])))):'';
	
	$DpntBirthDay=($DpntBirthDay>9)?$DpntBirthDay:"0".$DpntBirthDay;
	$DpntBirthMonth=($DpntBirthMonth>9)?$DpntBirthMonth:"0".$DpntBirthMonth;
	
	
	if($RelID=="0"){
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
		//Get New RelID
		$NewRelID="DR001";
		$count=1;
		while($records=$MySQLi->GetArray("SELECT `RelID` FROM `tbldpntrelationships` WHERE `RelID`='$NewRelID';")) {
			$count+=1;
			$ccc=($count>9)?$count:"0".$count;
			$NewRelID="DR0".$ccc;
		} $RelID=$NewRelID;
		$sql="INSERT INTO `tbldpntrelationships` (`RelID`,`RelDesc`,`RECORD_TIME`) VALUES ('$RelID','$RelDesc',NOW());";
		$MySQLi->sqlQuery($sql);
	}
	if($mode=="0"){
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
		//Get New DpntID
		$NewDpntID="DP".$EmpID."01";
		$count=1;
		while($records=$MySQLi->GetArray("SELECT `DpntID` FROM `tblempdependents` WHERE `DpntID`='$NewDpntID';")) {
			$count+=1;
			$ccc=($count>9)?$count:"0".$count;
			$NewDpntID="DP".$EmpID.$ccc;
		} $DpntID=$NewDpntID;
		$sql="INSERT INTO `tblempdependents` (`DpntID`,`EmpID`,`DpntLName`,`DpntMName`,`DpntFName`,`DpntExtName`,`DpntBirthDay`,`DpntBirthMonth`,`DpntBirthYear`,`RelID`,`DpntRemarks`,`RECORD_TIME`) VALUES ('$DpntID','$EmpID','$DpntLName','$DpntMName','$DpntFName','$DpntExtName','$DpntBirthDay','$DpntBirthMonth','$DpntBirthYear','$RelID','$DpntRemarks',NOW());";

		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Dependent record was successfully added.";}
		else{echo "0|$EmpID|ERROR ".mysql_errno().":<br/>".mysql_error();}
	}
	else if($mode=="1") {
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
		$sql="UPDATE `tblempdependents` SET `DpntLName`='$DpntLName',`DpntMName`='$DpntMName',`DpntFName`='$DpntFName',`DpntExtName`='$DpntExtName',`DpntBirthDay`='$DpntBirthDay',`DpntBirthMonth`='$DpntBirthMonth',`DpntBirthYear`='$DpntBirthYear',`RelID`='$RelID',`DpntRemarks`='$DpntRemarks',`RECORD_TIME`=NOW() WHERE `EmpID`='$EmpID' AND `DpntID`='$DpntID' LIMIT 1;";
		
		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Dependent record was successfully updated.";}
		else{echo "0|$EmpID|ERROR ".mysql_errno().":<br/>".mysql_error();}
	}
	else if($mode=="-1") {
		if(!$Authorization[3]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
		$sql="DELETE FROM `tblempdependents` WHERE `DpntID`='$DpntID';";
		
		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Dependent record was successfully deleted.";}
		else{echo "0|$EmpID|ERROR ".mysql_errno().":<br/>".mysql_error();}
	}
	else{echo "0|$EmpID|ERROR ???:<br/>Unkown mode.";}
	ob_end_flush();
?>