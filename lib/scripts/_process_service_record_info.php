<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD009'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$MySQLi=new MySQLClass();
	
	//Get POST Values
	$mode=isset($_POST['mode'])?strip_tags(trim($_POST['mode'])):'';
	$EmpID=isset($_POST['EmpID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpID'])))):'';
	$SRecID=isset($_POST['SRecID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['SRecID'])))):'';
	$SRecFromDay=isset($_POST['SRecFromDay'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['SRecFromDay'])))):'';
	$SRecFromMonth=isset($_POST['SRecFromMonth'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['SRecFromMonth'])))):'';
	$SRecFromYear=isset($_POST['SRecFromYear'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['SRecFromYear'])))):'';
	$SRecToDay=isset($_POST['SRecToDay'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['SRecToDay'])))):'';
	$SRecToMonth=isset($_POST['SRecToMonth'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['SRecToMonth'])))):'';
	$SRecToYear=isset($_POST['SRecToYear'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['SRecToYear'])))):'';
	$SRecEmployer=isset($_POST['SRecEmployer'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['SRecEmployer'])))):'';
	$SRecIsGov=isset($_POST['SRecIsGov'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['SRecIsGov'])))):'';
	$MotherOfficeID=isset($_POST['MotherOfficeID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['MotherOfficeID'])))):'SOOF00000';
	$AssignedOfficeID=isset($_POST['AssignedOfficeID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['AssignedOfficeID'])))):'SOOF00000';
	$PosID=isset($_POST['PosID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['PosID'])))):"PO000";
	$ApptStID=isset($_POST['ApptStID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['ApptStID'])))):"AS000";
	$SRecJobDesc=isset($_POST['SRecJobDesc'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['SRecJobDesc'])))):'';
	//$SalGrdYear=isset($_POST['SalGrdYear'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['SalGrdYear'])))):'2012';
	$SalStep=isset($_POST['SalStep'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['SalStep'])))):'0';
	
	$SRecSalary=isset($_POST['SRecSalary'])?strip_tags(trim($MySQLi->RealEscapeString($_POST['SRecSalary']))):0;
	$SRecSalary=number_format(floatval(($SRecSalary>0?$SRecSalary:0)), 2, '.', '');
	$SalUnitID=isset($_POST['SalUnitID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['SalUnitID'])))):'U00';
	$SRecCurrentAppointment=isset($_POST['SRecCurAppt'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['SRecCurAppt'])))):0;
	
	$SRecFromDay=($SRecFromDay>9)?$SRecFromDay:"0".$SRecFromDay;
	$SRecFromMonth=($SRecFromMonth>9)?$SRecFromMonth:"0".$SRecFromMonth;
	$SRecToDay=($SRecToDay>9)?$SRecToDay:"0".$SRecToDay;
	$SRecToMonth=($SRecToMonth>9)?$SRecToMonth:"0".$SRecToMonth;
	
	$SalStep=intval($SalStep);
	
	if($mode=="0"){
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		/* Get New SRecID */
		$NewSRecID="SR".$EmpID."001";
		$count=1;
		
		while($records=$MySQLi->GetArray("SELECT `SRecID` FROM `tblempservicerecords` WHERE `SRecID`='$NewSRecID';")) {
			$count+=1;
			$ccc=($count>99)?$count:(($count>9)?"0".$count:"00".$count);
			$NewSRecID="SR".$EmpID.$ccc;
		} $SRecID=$NewSRecID;

		$sql="INSERT INTO `tblempservicerecords` (`SRecID`,`EmpID`,`SRecFromDay`,`SRecFromMonth`,`SRecFromYear`,`SRecToDay`,`SRecToMonth`,`SRecToYear`,`SRecIsGov`,`SRecEmployer`,`MotherOfficeID`,`AssignedOfficeID`,`SRecOffice`,`PosID`,`SRecPosition`,`SRecSalGradeStep`,`ApptStID`,`SRecJobDesc`,`SRecSalary`,`SalUnitID`,`SRecCurrentAppointment`,`RECORD_TIME`) VALUES ('$SRecID','$EmpID','$SRecFromDay','$SRecFromMonth','$SRecFromYear','$SRecToDay','$SRecToMonth','$SRecToYear','$SRecIsGov','$SRecEmployer','$MotherOfficeID','$AssignedOfficeID','','$PosID','','$SalStep','$ApptStID','$SRecJobDesc','$SRecSalary','$SalUnitID','$SRecCurrentAppointment',NOW());";
		if($MySQLi->sqlQuery($sql)){
			$SerRec=$MySQLi->GetArray("SELECT `SRecID` FROM `tblempservicerecords` WHERE `EmpID`='$EmpID' ORDER BY `SRecFromYear` DESC, `SRecFromMonth` DESC, `SRecFromDay` DESC LIMIT 1;");
			$MySQLi->sqlQuery("UPDATE `tblempservicerecords` SET `SRecCurrentAppointment`='1' WHERE `EmpID`='$EmpID' AND `SRecID`='".$SerRec['SRecID']."' LIMIT 1;");
			$MySQLi->sqlQuery("UPDATE `tblempservicerecords` SET `SRecCurrentAppointment`='0' WHERE `EmpID`='$EmpID' AND `SRecID`<>'".$SerRec['SRecID']."';");
			//$ApptStID
			echo "1|$EmpID|Service record was successfully added.";
		}
	}
	else if($mode=="1"){
		if(!$Authorization[4]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		$sql="UPDATE `tblempservicerecords` SET `SRecFromDay`='$SRecFromDay',`SRecFromMonth`='$SRecFromMonth',`SRecFromYear`='$SRecFromYear',`SRecToDay`='$SRecToDay',`SRecToMonth`='$SRecToMonth',`SRecToYear`='$SRecToYear',`SRecIsGov`='$SRecIsGov',`SRecEmployer`='$SRecEmployer',`MotherOfficeID`='$MotherOfficeID',`AssignedOfficeID`='$AssignedOfficeID',`PosID`='$PosID',`SRecSalGradeStep`='$SalStep',`ApptStID`='$ApptStID',`SRecJobDesc`='$SRecJobDesc',`SRecSalary`='$SRecSalary',`SalUnitID`='$SalUnitID',`SRecCurrentAppointment`='$SRecCurrentAppointment',`RECORD_TIME`=NOW() WHERE `EmpID`='$EmpID' AND `SRecID`='$SRecID' LIMIT 1;";
		
		if($MySQLi->sqlQuery($sql)){
			$SerRec=$MySQLi->GetArray("SELECT `SRecID` FROM `tblempservicerecords` WHERE `EmpID`='$EmpID' ORDER BY `SRecFromYear` DESC, `SRecFromMonth` DESC, `SRecFromDay` DESC LIMIT 1;");
			$MySQLi->sqlQuery("UPDATE `tblempservicerecords` SET `SRecCurrentAppointment`='1' WHERE `EmpID`='$EmpID' AND `SRecID`='".$SerRec['SRecID']."' LIMIT 1;");
			$MySQLi->sqlQuery("UPDATE `tblempservicerecords` SET `SRecCurrentAppointment`='0' WHERE `EmpID`='$EmpID' AND `SRecID`<>'".$SerRec['SRecID']."';");
			echo "1|$EmpID|Service record was successfully updated.";
		}
	}
	else if($mode=="-1"){
		if(!$Authorization[3]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		/* Check this record if most recent before deleting */
		$sql="DELETE FROM `tblempservicerecords` WHERE `EmpID`='$EmpID' AND `SRecID`='$SRecID' LIMIT 1;";
		
		if($MySQLi->sqlQuery($sql)){
			$SerRec=$MySQLi->GetArray("SELECT `SRecID` FROM `tblempservicerecords` WHERE `EmpID`='$EmpID' ORDER BY `SRecFromYear` DESC, `SRecFromMonth` DESC, `SRecFromDay` DESC LIMIT 1;");
			$MySQLi->sqlQuery("UPDATE `tblempservicerecords` SET `SRecCurrentAppointment`='1' WHERE `EmpID`='$EmpID' AND `SRecID`='".$SerRec['SRecID']."' LIMIT 1;");
			$MySQLi->sqlQuery("UPDATE `tblempservicerecords` SET `SRecCurrentAppointment`='0' WHERE `EmpID`='$EmpID' AND `SRecID`<>'".$SerRec['SRecID']."';");
			echo "1|$EmpID|Service record was successfully deleted.";
		}
	}
	else{echo "0|$EmpID|ERROR ???:~Unkown mode.";}
	ob_end_flush();
?>