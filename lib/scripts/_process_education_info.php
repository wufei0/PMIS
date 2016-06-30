<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD007'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$MySQLi=new MySQLClass();
	
	//Get POST Values
	$mode=isset($_POST['mode'])?strip_tags(trim($_POST['mode'])):'';
	$EmpID=isset($_POST['EmpID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpID'])))):'';
	$EducBgID=isset($_POST['EducBgID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EducBgID'])))):'';
	$EducLvlID=isset($_POST['EducLvlID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EducLvlID'])))):'';
	$EducLvlDesc=isset($_POST['EducLvlDesc'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EducLvlDesc'])))):'';
	$EducSchoolName=isset($_POST['EducSchoolName'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EducSchoolName'])))):'';
	$EducCourse=isset($_POST['EducCourse'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EducCourse'])))):'';
	$EducYrGrad=isset($_POST['EducYrGrad'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EducYrGrad'])))):'';
	$EducGradeLvlUnits=isset($_POST['EducGradeLvlUnits'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EducGradeLvlUnits'])))):'';
	$EducIncAttDateFromDay=isset($_POST['EducIncAttDateFromDay'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EducIncAttDateFromDay'])))):'';
	$EducIncAttDateFromMonth=isset($_POST['EducIncAttDateFromMonth'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EducIncAttDateFromMonth'])))):'';
	$EducIncAttDateFromYear=isset($_POST['EducIncAttDateFromYear'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EducIncAttDateFromYear'])))):'';
	$EducIncAttDateToDay=isset($_POST['EducIncAttDateToDay'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EducIncAttDateToDay'])))):'';
	$EducIncAttDateToMonth=isset($_POST['EducIncAttDateToMonth'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EducIncAttDateToMonth'])))):'';
	$EducIncAttDateToYear=isset($_POST['EducIncAttDateToYear'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EducIncAttDateToYear'])))):'';
	$EducAwards=isset($_POST['EducAwards'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EducAwards'])))):'';
	
	$EducIncAttDateFromDay=($EducIncAttDateFromDay>9)?$EducIncAttDateFromDay:"0".$EducIncAttDateFromDay;
	$EducIncAttDateFromMonth=($EducIncAttDateFromMonth>9)?$EducIncAttDateFromMonth:"0".$EducIncAttDateFromMonth;
	$EducIncAttDateToDay=($EducIncAttDateToDay>9)?$EducIncAttDateToDay:"0".$EducIncAttDateToDay;
	$EducIncAttDateToMonth=($EducIncAttDateToMonth>9)?$EducIncAttDateToMonth:"0".$EducIncAttDateToMonth;
	
	
	if($mode=="0"){
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
		//Get New EducBgID
		$NewEducBgID="ED".$EmpID."01";
		$count=1;
		while($records=$MySQLi->GetArray("SELECT `EducBgID` FROM `tblempeducbg` WHERE `EducBgID`='$NewEducBgID';")) {
			$count+=1;
			$ccc=($count>9)?$count:"0".$count;
			$NewEducBgID="ED".$EmpID.$ccc;
		} $EducBgID=$NewEducBgID;
		$sql="INSERT INTO `tblempeducbg` (`EducBgID`,`EmpID`,`EducLvlID`,`EducSchoolName`,`EducCourse`,`EducYrGrad`,`EducGradeLvlUnits`,`EducIncAttDateFromDay`,`EducIncAttDateFromMonth`,`EducIncAttDateFromYear`,`EducIncAttDateToDay`,`EducIncAttDateToMonth`,`EducIncAttDateToYear`,`EducAwards`,`RECORD_TIME`) VALUES ('$EducBgID','$EmpID','$EducLvlID','$EducSchoolName','$EducCourse','$EducYrGrad','$EducGradeLvlUnits','$EducIncAttDateFromDay','$EducIncAttDateFromMonth','$EducIncAttDateFromYear','$EducIncAttDateToDay','$EducIncAttDateToMonth','$EducIncAttDateToYear','$EducAwards',NOW());";

		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Educational background record was successfully added.";}
	}
	else if($mode=="1") {
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
		$sql="UPDATE `tblempeducbg` SET `EducLvlID`='$EducLvlID',`EducSchoolName`='$EducSchoolName',`EducCourse`='$EducCourse',`EducYrGrad`='$EducYrGrad',`EducGradeLvlUnits`='$EducGradeLvlUnits',`EducIncAttDateFromDay`='$EducIncAttDateFromDay',`EducIncAttDateFromMonth`='$EducIncAttDateFromMonth',`EducIncAttDateFromYear`='$EducIncAttDateFromYear',`EducIncAttDateToDay`='$EducIncAttDateToDay',`EducIncAttDateToMonth`='$EducIncAttDateToMonth',`EducIncAttDateToYear`='$EducIncAttDateToYear',`EducAwards`='$EducAwards',`RECORD_TIME`=NOW() WHERE `EmpID`='$EmpID' AND `EducBgID`='$EducBgID' LIMIT 1;";
		
		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Educational background record was successfully updated.";}
		else{echo "0|$EmpID|ERROR ".mysql_errno().":~".mysql_error();}
	}
	else if($mode=="-1") {
		if(!$Authorization[3]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
		$sql="DELETE FROM `tblempeducbg` WHERE `EmpID`='$EmpID' AND `EducBgID`='$EducBgID' LIMIT 1;";
		
		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Educational background record was successfully deleted.";}
	}
	else{echo "0|$EmpID|ERROR ???:~Unkown mode.";}
	ob_end_flush();
?>