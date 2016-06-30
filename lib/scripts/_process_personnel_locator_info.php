<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD021'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	//Get POST Values
	$mode=isset($_POST['mode'])?mysql_escape_string(strip_tags(trim($_POST['mode']))):'';
	$EmpID=isset($_POST['EmpID'])?strtoupper(mysql_escape_string(strip_tags(trim($_POST['EmpID'])))):'';
	$PLID=isset($_POST['PLID'])?strtoupper(mysql_escape_string(strip_tags(trim($_POST['PLID'])))):'';
	$PLFor=isset($_POST['PLFor'])?strtoupper(mysql_escape_string(strip_tags(trim($_POST['PLFor'])))):'';
	$PLDestination=isset($_POST['PLDestination'])?strtoupper(mysql_escape_string(strip_tags(trim($_POST['PLDestination'])))):'';
	$PLPurpose=isset($_POST['PLPurpose'])?strtoupper(mysql_escape_string(strip_tags(trim($_POST['PLPurpose'])))):'0';

	$PLDateDay=date('d');
	$PLDateMonth=date('m');
	$PLDateYear=date('Y');
	$PLPreparedBy=$_SESSION['user'];
	$PLPreparedTime=$LogTIME;

	$Config=new Conf();
	$MySQLi=new MySQLClass($Config);
	if($mode=="0"){
		/* Get New PLID */
		$NewPLID="LOC".date('Ymd')."001";
		$count=1;
		while($records=mysql_fetch_array($MySQLi->sqlQuery("SELECT `PLID` FROM `tblpersonnellocators` WHERE `PLID`='$NewPLID';"))) {
			$count+=1;
			$ccc=($count>99)?$count:(($count>9)?"0".$count:"00".$count);
			$NewPLID="LOC".date('Ymd').$ccc;
		} $PLID=$NewPLID;
		$sql="INSERT INTO `tblpersonnellocators` (`PLID`,`PLDateDay`,`PLDateMonth`,`PLDateYear`,`PLDestination`,`PLPurpose`,`PLPreparedBy`,`PLPreparedTime`,`PLApprovedBy`,`PLApprovedTime`,`PLTimeOUT`,`PLCheckedOUTby`,`PLTimeIN`,`PLCheckedINby`,`PLStatus`,`PLRemarks`,`RECORD_TIME`) VALUES ('$PLID','$PLDateDay','$PLDateMonth','$PLDateYear','$PLDestination','$PLPurpose','$PLPreparedBy','$PLPreparedTime','','','','','','','','','$LogTIME');";

		if($MySQLi->sqlQuery($sql)) {
			$PLids=explode(",",$PLFor);
			foreach ($PLids as $key => &$value) {
				if($key!=0){
					/* Get New EmpPLID */
					$NewEmpPLID="PL".date('Ym').$value."01";
					$count=1;
					while($records=mysql_fetch_array($MySQLi->sqlQuery("SELECT `EmpPLID` FROM `tblemplocator` WHERE `EmpPLID`='$NewEmpPLID';"))) {
						$count+=1;
						$cc=($count>9)?$count:"0".$count;
						$NewEmpPLID="PL".date('Ym').$value.$cc;
					} $EmpPLID=$NewEmpPLID;
					$sql="INSERT INTO `pmis`.`tblemplocator` (`EmpPLID`, `EmpID`, `PLID`, `RECORD_TIME`) VALUES ('$EmpPLID', '$value', '$PLID', NOW());";
					$MySQLi->sqlQuery($sql);
				}
			}
			echo "1";
		}
	}
	else if($mode=="1") {
		$sql="UPDATE `tblpersonnellocators` SET `PLDestination`='$PLDestination',`PLPurpose`='$PLPurpose',`RECORD_TIME`=$LogTIME WHERE `PLID`='$PLID' LIMIT 1;";
		if($MySQLi->sqlQuery($sql)) {
			$sql="DELETE FROM `tblemplocator` WHERE `PLID`='$PLID';";
			$MySQLi->sqlQuery($sql);
			$PLids=explode(",",$PLFor);
			foreach ($PLids as $key => &$value) {
				if($key!=0){
					//Get New EmpPLID
					$NewEmpPLID="PL".date('Ym').$value."01";
					$count=1;
					while($records=mysql_fetch_array($MySQLi->sqlQuery("SELECT `EmpPLID` FROM `tblemplocator` WHERE `EmpPLID`='$NewEmpPLID';"))) {
						$count+=1;
						$cc=($count>9)?$count:"0".$count;
						$NewEmpPLID="PL".date('Ym').$value.$cc;
					} $EmpPLID=$NewEmpPLID;
					$sql="INSERT INTO `pmis`.`tblemplocator` (`EmpPLID`, `EmpID`, `PLID`, `RECORD_TIME`) VALUES ('$EmpPLID', '$value', '$PLID', NOW());";
					$MySQLi->sqlQuery($sql);
				}
			}
			echo "1";
		}
	}
	else if($mode=="-1") {
		$sql="DELETE FROM `tblpersonnellocators` WHERE `EmpID`='$EmpID' AND `PLID`='$PLID' LIMIT 1;";
		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Personnel Locator Information record was successfully deleted.";}
		else{echo "0|$EmpID|ERROR ".mysql_errno().":<br/>".mysql_error();}
	}
	else{echo "0|$EmpID|ERROR ???:<br/>Unkown mode.";}
	ob_end_flush();
?>