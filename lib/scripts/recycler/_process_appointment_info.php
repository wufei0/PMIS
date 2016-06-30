<?php
	ob_start();
	session_start();
	$_SESSION['theme']='blue';
	
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';
	
	if ($_SESSION['fingerprint']==md5($_SESSION['user']." ".$_SERVER['HTTP_USER_AGENT']." ".$_SERVER['REMOTE_ADDR']." ".$_SESSION['fprinttime'])){
		/* Check user activity within the last ? minutes*/
		$Authentication=new Authentication();
		$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));
		if($ActiveStatus[0]==1){$Authentication->setUserActiveTime($_SESSION['user'],$_SESSION['fingerprint']);}
		else{echo "-1|00000|".$ActiveStatus[1];exit();}
		/* Check user access to this module */
		
		
	}
	else{
		echo "-1|00000|ERROR 401:<br/>You are not authorized to access this section.<br/>Please login.";
		exit();
	}

	//Get POST Values
	$mode=isset($_POST['mode'])?mysql_escape_string(strip_tags(trim($_POST['mode']))):'';
	$SRecID=isset($_POST['SRecID'])?strtoupper(mysql_escape_string(strip_tags(trim($_POST['SRecID'])))):'';
	$EmpID=isset($_POST['EmpID'])?strtoupper(mysql_escape_string(strip_tags(trim($_POST['EmpID'])))):'';
	$SRecFromDay=isset($_POST['SRecFromDay'])? strtoupper(mysql_escape_string(strip_tags(trim($_POST['SRecFromDay'])))):'';
	$SRecFromMonth=isset($_POST['SRecFromMonth'])?strtoupper(mysql_escape_string(strip_tags(trim($_POST['SRecFromMonth'])))):'';
	$SRecFromYear=isset($_POST['SRecFromYear'])?strtoupper(mysql_escape_string(strip_tags(trim($_POST['SRecFromYear'])))):'';
	$SRecToDay=isset($_POST['SRecToDay'])?strtoupper(mysql_escape_string(strip_tags(trim($_POST['SRecToDay'])))):'';
	$SRecToMonth=isset($_POST['SRecToMonth'])?strtoupper(mysql_escape_string(strip_tags(trim($_POST['SRecToMonth'])))):'';
	$SRecToYear=isset($_POST['SRecToYear'])?strtoupper(mysql_escape_string(strip_tags(trim($_POST['SRecToYear'])))):'';
	$MotherOfficeID=isset($_POST['MotherOfficeID'])?strtoupper(mysql_escape_string(strip_tags(trim($_POST['MotherOfficeID'])))):'';
	$AssignedOfficeID=isset($_POST['AssignedOfficeID'])?strtoupper(mysql_escape_string(strip_tags(trim($_POST['AssignedOfficeID'])))):'';
	$PosID=isset($_POST['PosID'])?strtoupper(mysql_escape_string(strip_tags(trim($_POST['PosID'])))):"PO000";
	$ApptStID=isset($_POST['ApptStID'])?strtoupper(mysql_escape_string(strip_tags(trim($_POST['ApptStID'])))):"AS003";
	$SalGrdYear=isset($_POST['SalGrdYear'])?strtoupper(mysql_escape_string(strip_tags(trim($_POST['SalGrdYear'])))):'2011';
	$SalGrade=isset($_POST['SalGrade'])?strtoupper(mysql_escape_string(strip_tags(trim($_POST['SalGrade'])))):'0';
	$SalStep=isset($_POST['SalStep'])?strtoupper(mysql_escape_string(strip_tags(trim($_POST['SalStep'])))):'0';
	$SalUnitID=isset($_POST['SalUnitID'])?strtoupper(mysql_escape_string(strip_tags(trim($_POST['SalUnitID'])))):'U05';
	$Add2SRec=isset($_POST['Add2SRec'])?strtoupper(mysql_escape_string(strip_tags(trim($_POST['Add2SRec'])))):0;
	
	$SRecFromDay=($SRecFromDay>9)?$SRecFromDay:"0".$SRecFromDay;
	$SRecFromMonth=($SRecFromMonth>9)?$SRecFromMonth:"0".$SRecFromMonth;
	$SRecToDay=($SRecToDay>9)?$SRecToDay:"0".$SRecToDay;
	$SRecToMonth=($SRecToMonth>9)?$SRecToMonth:"0".$SRecToMonth;
	
	$SalGrade=($SalGrade>9)?$SalGrade:"0".$SalGrade;
	$SalStep=($SalStep>9)?$SalStep:"0".$SalStep;
	$SalGrdID=$SalGrdYear.$SalGrade.$SalStep;
	
	$Config=new Conf();
	$MySQLi=new MySQLClass($Config);
	/* Get Salary Value from Salary Grade Table */
	$result=$MySQLi->sqlQuery("SELECT `SalGrdValue` FROM `tblsalgrade` WHERE `SalGrdID`='$SalGrdID' LIMIT 1;");
	$salgrd=mysql_fetch_array($result);
	$SRecSalary=$salgrd['SalGrdValue'];
	
	if($Add2SRec==1){
		/* Get New SRecID */
		$NewSRecID="ER".$EmpID."001";
		$count=1;
		while($records=mysql_fetch_array($MySQLi->sqlQuery("SELECT `SRecID` FROM `tblempservicerecords` WHERE `EmpID`='$EmpID' AND `SRecID`='$NewSRecID';"))) {
			$count+=1;
			$ccc=($count>99)?$count:(($count>9)?"0".$count:"00".$count);
			$NewSRecID="ER".$EmpID.$ccc;
		} $SRecID=$NewSRecID;

		$sql="INSERT INTO `tblempservicerecords` (`SRecID`,`EmpID`,`SRecFromDay`,`SRecFromMonth`,`SRecFromYear`,`SRecToDay`,`SRecToMonth`,`SRecToYear`,`SRecIsGov`,`SRecEmployer`,`MotherOfficeID`,`AssignedOfficeID`,`PosID`,`ApptStID`,`SRecJobDesc`,`SalGrdID`,`SRecSalary`,`SalUnitID`,`RECORD_TIME`) VALUES ('$SRecID','$EmpID','$SRecFromDay','$SRecFromMonth','$SRecFromYear','$SRecToDay','$SRecToMonth','$SRecToYear','YES','PROVINCIAL GOVERNMENT OF LA UNION','$MotherOfficeID','$AssignedOfficeID','$PosID','$ApptStID','','$SalGrdID','$SRecSalary','$SalUnitID',NOW());";

		if($MySQLi->sqlQuery($sql)) {echo "1";}
	}

	else{
		$sql="UPDATE `tblempservicerecords` SET `SRecFromDay`='$SRecFromDay',`SRecFromMonth`='$SRecFromMonth',`SRecFromYear`='$SRecFromYear',`SRecToDay`='$SRecToDay',`SRecToMonth`='$SRecToMonth',`SRecToYear`='$SRecToYear',`SRecIsGov`='YES',`SRecEmployer`='PROVINCIAL GOVERNMENT OF LA UNION',`MotherOfficeID`='$MotherOfficeID',`AssignedOfficeID`='$AssignedOfficeID',`PosID`='$PosID',`ApptStID`='$ApptStID',`SalGrdID`='$SalGrdID',`SRecSalary`='$SRecSalary',`SalUnitID`='$SalUnitID',`RECORD_TIME`=NOW() WHERE `EmpID`='$EmpID' AND `SRecID`='$SRecID' LIMIT 1;";
		if($MySQLi->sqlQuery($sql)) {echo "1";}
	}
	
	ob_end_flush();
?>