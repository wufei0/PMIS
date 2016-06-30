<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD008'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$MySQLi=new MySQLClass();
	
	//Get POST Values
	$mode=isset($_POST['mode'])?strip_tags(trim($_POST['mode'])):'';
	$EmpID=isset($_POST['EmpID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpID'])))):'';
	$CSEID=isset($_POST['CSEID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['CSEID'])))):'';
	$CSEDesc=isset($_POST['CSEDesc'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['CSEDesc'])))):'';
	$CSERating=isset($_POST['CSERating'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['CSERating'])))):'';
	$CSEExamDay=isset($_POST['CSEExamDay'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['CSEExamDay'])))):'';
	$CSEExamMonth=isset($_POST['CSEExamMonth'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['CSEExamMonth'])))):'';
	$CSEExamYear=isset($_POST['CSEExamYear'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['CSEExamYear'])))):'';
	$CSEExamPlace=isset($_POST['CSEExamPlace'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['CSEExamPlace'])))):'';
	$CSELicNum=isset($_POST['CSELicNum'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['CSELicNum'])))):'';
	$CSELicReleaseDay=isset($_POST['CSELicReleaseDay'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['CSELicReleaseDay'])))):'';
	$CSELicReleaseMonth=isset($_POST['CSELicReleaseMonth'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['CSELicReleaseMonth'])))):'';
	$CSELicReleaseYear=isset($_POST['CSELicReleaseYear'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['CSELicReleaseYear'])))):'';
	
	$CSEExamDay=($CSEExamDay>9)?$CSEExamDay:"0".$CSEExamDay;
	$CSEExamMonth=($CSEExamMonth>9)?$CSEExamMonth:"0".$CSEExamMonth;
	$CSELicReleaseDay=($CSELicReleaseDay>9)?$CSELicReleaseDay:"0".$CSELicReleaseDay;
	$CSELicReleaseMonth=($CSELicReleaseMonth>9)?$CSELicReleaseMonth:"0".$CSELicReleaseMonth;
	
	
	if($mode=="0"){
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
		//Get New CSEID
		$NewCSEID="CS".$EmpID."01";
		$count=1;
		while($records=$MySQLi->GetArray("SELECT `CSEID` FROM `tblempcse` WHERE `CSEID`='$NewCSEID';")) {
			$count+=1;
			$ccc=($count>9)?$count:"0".$count;
			$NewCSEID="CS".$EmpID.$ccc;
		} $CSEID=$NewCSEID;
		$sql="INSERT INTO `tblempcse` (`CSEID`,`EmpID`,`CSEDesc`,`CSERating`,`CSEExamDay`,`CSEExamMonth`,`CSEExamYear`,`CSEExamPlace`,`CSELicNum`,`CSELicReleaseDay`,`CSELicReleaseMonth`,`CSELicReleaseYear`,`RECORD_TIME`) VALUES ('$CSEID','$EmpID','$CSEDesc','$CSERating','$CSEExamDay','$CSEExamMonth','$CSEExamYear','$CSEExamPlace','$CSELicNum','$CSELicReleaseDay','$CSELicReleaseMonth','$CSELicReleaseYear',NOW());";
		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Eligibility record was successfully added.";}
		else{echo "0|$EmpID|ERROR ".mysql_errno().":<br/>".mysql_error();}
	}
	else if($mode=="1") {
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
		$sql="UPDATE `tblempcse` SET `CSEDesc`='$CSEDesc',`CSERating`='$CSERating',`CSEExamDay`='$CSEExamDay',`CSEExamMonth`='$CSEExamMonth',`CSEExamYear`='$CSEExamYear',`CSEExamPlace`='$CSEExamPlace',`CSELicNum`='$CSELicNum',`CSELicReleaseDay`='$CSELicReleaseDay',`CSELicReleaseMonth`='$CSELicReleaseMonth',`CSELicReleaseYear`='$CSELicReleaseYear',`RECORD_TIME`=NOW() WHERE `EmpID`='$EmpID' AND `CSEID`='$CSEID' LIMIT 1;";
		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Eligibility record was successfully updated.";}
		else{echo "0|$EmpID|ERROR ".mysql_errno().":<br/>".mysql_error();}
	}
	else if($mode=="-1") {
		if(!$Authorization[3]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
		$sql="DELETE FROM `tblempcse` WHERE `EmpID`='$EmpID' AND `CSEID`='$CSEID' LIMIT 1;";
		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Eligibility record was successfully deleted.";}
		else{echo "0|$EmpID|ERROR ".mysql_errno().":<br/>".mysql_error();}
	}
	else{echo "0|$EmpID|ERROR ???:<br/>Unkown mode.";}
	ob_end_flush();
?>