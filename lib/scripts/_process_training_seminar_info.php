<?php
	ob_start();
	session_start();
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD011'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$MySQLi=new MySQLClass();
	
	//Get POST Values
	$mode=isset($_POST['mode'])?strip_tags(trim($_POST['mode'])):'';
	$EmpID=isset($_POST['EmpID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpID'])))):'';
	$TrainID=isset($_POST['TrainID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['TrainID'])))):'';
	$TrainDesc=isset($_POST['TrainDesc'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['TrainDesc'])))):'';
	$TrainFromDay=isset($_POST['TrainFromDay'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['TrainFromDay'])))):'';
	$TrainFromMonth=isset($_POST['TrainFromMonth'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['TrainFromMonth'])))):'';
	$TrainFromYear=isset($_POST['TrainFromYear'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['TrainFromYear'])))):'';
	$TrainToDay=isset($_POST['TrainToDay'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['TrainToDay'])))):'';
	$TrainToMonth=isset($_POST['TrainToMonth'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['TrainToMonth'])))):'';
	$TrainToYear=isset($_POST['TrainToYear'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['TrainToYear'])))):'';
	$TrainHours=isset($_POST['TrainHours'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['TrainHours'])))):'0';
	$TrainSponsor=isset($_POST['TrainSponsor'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['TrainSponsor'])))):'0';
	
	$TrainFromDay=($TrainFromDay>9)?$TrainFromDay:"0".$TrainFromDay;
	$TrainFromMonth=($TrainFromMonth>9)?$TrainFromMonth:"0".$TrainFromMonth;
	$TrainToDay=($TrainToDay>9)?$TrainToDay:"0".$TrainToDay;
	$TrainToMonth=($TrainToMonth>9)?$TrainToMonth:"0".$TrainToMonth;

	
	if($mode=="0"){
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
		//Get New TrainID
		$NewTrainID="TR".$EmpID."01";
		$count=1;
		while($records=$MySQLi->GetArray("SELECT `TrainID` FROM `tblemptrainings` WHERE `TrainID`='$NewTrainID';")) {
			$count+=1;
			$ccc=($count>9)?$count:"0".$count;
			$NewTrainID="TR".$EmpID.$ccc;
		} $TrainID=$NewTrainID;
		$sql="INSERT INTO `tblemptrainings` (`TrainID`,`EmpID`,`TrainDesc`,`TrainFromDay`,`TrainFromMonth`,`TrainFromYear`,`TrainToDay`,`TrainToMonth`,`TrainToYear`,`TrainHours`,`TrainSponsor`,`RECORD_TIME`) VALUES ('$TrainID','$EmpID','$TrainDesc','$TrainFromDay','$TrainFromMonth','$TrainFromYear','$TrainToDay','$TrainToMonth','$TrainToYear','$TrainHours','$TrainSponsor',NOW());";

		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Training and Seminar record was successfully added.";}
		else{echo "0|$EmpID|ERROR ".mysql_errno().":<br/>".mysql_error();}
	}
	else if($mode=="1") {
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
		$sql="UPDATE `tblemptrainings` SET `TrainDesc`='$TrainDesc',`TrainFromDay`='$TrainFromDay',`TrainFromMonth`='$TrainFromMonth',`TrainFromYear`='$TrainFromYear',`TrainToDay`='$TrainToDay',`TrainToMonth`='$TrainToMonth',`TrainToYear`='$TrainToYear',`TrainHours`='$TrainHours',`TrainSponsor`='$TrainSponsor',`RECORD_TIME`=NOW() WHERE `EmpID`='$EmpID' AND `TrainID`='$TrainID' LIMIT 1;";
		
		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Training and Seminar record was successfully updated.";}
		else{echo "0|$EmpID|ERROR ".mysql_errno().":<br/>".mysql_error();}
	}
	else if($mode=="-1") {
		if(!$Authorization[3]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
		$sql="DELETE FROM `tblemptrainings` WHERE `EmpID`='$EmpID' AND `TrainID`='$TrainID' LIMIT 1;";
		
		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Training and Seminar record was successfully deleted.";}
		else{echo "0|$EmpID|ERROR ".mysql_errno().":<br/>".mysql_error();}
	}
	else{echo "0|$EmpID|ERROR ???:<br/>Unkown mode.";}
	ob_end_flush();
?>