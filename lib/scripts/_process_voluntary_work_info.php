<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD010'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$MySQLi=new MySQLClass();
	
	//Get POST Values
	$mode=isset($_POST['mode'])?strip_tags(trim($_POST['mode'])):'';
	$EmpID=isset($_POST['EmpID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpID'])))):'';
	$VolOrgID=isset($_POST['VolOrgID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['VolOrgID'])))):'';
	$VolOrgName=isset($_POST['VolOrgName'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['VolOrgName'])))):'';
	$EducLvlDesc=isset($_POST['EducLvlDesc'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EducLvlDesc'])))):'';
	$VolOrgAddSt=isset($_POST['VolOrgAddSt'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['VolOrgAddSt'])))):'';
	$VolOrgAddBrgy=isset($_POST['VolOrgAddBrgy'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['VolOrgAddBrgy'])))):'0';
	$VolOrgAddMun=isset($_POST['VolOrgAddMun'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['VolOrgAddMun'])))):'0';
	$VolOrgAddProv=isset($_POST['VolOrgAddProv'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['VolOrgAddProv'])))):'0';
	$VolOrgZipCode=isset($_POST['VolOrgZipCode'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['VolOrgZipCode'])))):'0';
	$VolOrgFromDay=isset($_POST['VolOrgFromDay'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['VolOrgFromDay'])))):'';
	$VolOrgFromMonth=isset($_POST['VolOrgFromMonth'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['VolOrgFromMonth'])))):'';
	$VolOrgFromYear=isset($_POST['VolOrgFromYear'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['VolOrgFromYear'])))):'';
	$VolOrgToDay=isset($_POST['VolOrgToDay'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['VolOrgToDay'])))):'';
	$VolOrgToMonth=isset($_POST['VolOrgToMonth'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['VolOrgToMonth'])))):'';
	$VolOrgToYear=isset($_POST['VolOrgToYear'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['VolOrgToYear'])))):'';
	$VolOrgHours=isset($_POST['VolOrgHours'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['VolOrgHours'])))):'0';
	$VolOrgDetails=isset($_POST['VolOrgDetails'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['VolOrgDetails'])))):'0';
	
	$VolOrgFromDay=($VolOrgFromDay>9)?$VolOrgFromDay:"0".$VolOrgFromDay;
	$VolOrgFromMonth=($VolOrgFromMonth>9)?$VolOrgFromMonth:"0".$VolOrgFromMonth;
	$VolOrgToDay=($VolOrgToDay>9)?$VolOrgToDay:"0".$VolOrgToDay;
	$VolOrgToMonth=($VolOrgToMonth>9)?$VolOrgToMonth:"0".$VolOrgToMonth;
	
	if($mode=="0"){
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
		//Get New VolOrgID
		$NewVolOrgID="VO".$EmpID."01";
		$count=1;
		while($records=$MySQLi->GetArray("SELECT `VolOrgID` FROM `tblempvoluntaryorg` WHERE `VolOrgID`='$NewVolOrgID';")) {
			$count+=1;
			$ccc=($count>9)?$count:"0".$count;
			$NewVolOrgID="VO".$EmpID.$ccc;
		} $VolOrgID=$NewVolOrgID;
		$sql="INSERT INTO `tblempvoluntaryorg` (`VolOrgID`,`EmpID`,`VolOrgName`,`VolOrgAddSt`,`VolOrgAddBrgy`,`VolOrgAddMun`,`VolOrgAddProv`,`VolOrgZipCode`,`VolOrgFromDay`,`VolOrgFromMonth`,`VolOrgFromYear`,`VolOrgToDay`,`VolOrgToMonth`,`VolOrgToYear`,`VolOrgHours`,`VolOrgDetails`,`RECORD_TIME`) VALUES ('$VolOrgID','$EmpID','$VolOrgName','$VolOrgAddSt','$VolOrgAddBrgy','$VolOrgAddMun','$VolOrgAddProv','$VolOrgZipCode','$VolOrgFromDay','$VolOrgFromMonth','$VolOrgFromYear','$VolOrgToDay','$VolOrgToMonth','$VolOrgToYear','$VolOrgHours','$VolOrgDetails',NOW());";

		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Voluntary Work record was successfully added.";}
	}
	else if($mode=="1") {
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
		$sql="UPDATE `tblempvoluntaryorg` SET `VolOrgName`='$VolOrgName',`VolOrgAddSt`='$VolOrgAddSt',`VolOrgAddBrgy`='$VolOrgAddBrgy',`VolOrgAddMun`='$VolOrgAddMun',`VolOrgAddProv`='$VolOrgAddProv',`VolOrgZipCode`='$VolOrgZipCode',`VolOrgFromDay`='$VolOrgFromDay',`VolOrgFromMonth`='$VolOrgFromMonth',`VolOrgFromYear`='$VolOrgFromYear',`VolOrgToDay`='$VolOrgToDay',`VolOrgToMonth`='$VolOrgToMonth',`VolOrgToYear`='$VolOrgToYear',`VolOrgHours`='$VolOrgHours',`VolOrgDetails`='$VolOrgDetails',`RECORD_TIME`=NOW() WHERE `EmpID`='$EmpID' AND `VolOrgID`='$VolOrgID' LIMIT 1;";
		
		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Voluntary Work record was successfully updated.";}
	}
	else if($mode=="-1") {
		if(!$Authorization[3]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
		$sql="DELETE FROM `tblempvoluntaryorg` WHERE `EmpID`='$EmpID' AND `VolOrgID`='$VolOrgID' LIMIT 1;";
		
		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Voluntary Work record was successfully deleted.";}
	}
	else{echo "0|$EmpID|ERROR ???:<br/>Unkown mode.";}
	ob_end_flush();
?>