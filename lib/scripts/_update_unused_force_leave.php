<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD018'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	
	
	//if(date('m')!='01'){echo "0|".$_SESSION['user']."|ERROR 401:~Only on January XX.";exit();}
	require_once $_SESSION['path'].'/lib/classes/Functions.php';
	
	
	$Control="S"; // (S)tart | (C)ontinue | (E)nd
	$Control=isset($_GET['ctr'])?trim(strip_tags($_GET['ctr'])):'S';
	$StartID=isset($_GET['sid'])?trim(strip_tags($_GET['sid'])):'00000';
	$StopID=isset($_GET['eid'])?trim(strip_tags($_GET['eid'])):'99999';
	$CGrpID=isset($_GET['cgr'])?trim(strip_tags($_GET['cgr'])):'99999';
	$PrevID=isset($_GET['pid'])?trim(strip_tags($_GET['pid'])):'00000';
	$CurrID=isset($_GET['cid'])?trim(strip_tags($_GET['cid'])):'00000';
	$NextID=isset($_GET['nid'])?trim(strip_tags($_GET['nid'])):'00000';
	$IDs=isset($_GET['ids'])?trim(strip_tags($_GET['ids'])):'00000';
	$Done=isset($_GET['did'])?trim(strip_tags($_GET['did'])):'0';
	
	
	$MySQLi=new MySQLClass();
	$fLeave=new LeaveFunctions();
	
	switch ($Control){
		case "S":
			$sql="SELECT P.`EmpID` FROM `pmis`.`tblemppersonalinfo` P JOIN `pmis`.`tblempservicerecords` S ON P.`EmpID` = S.`EmpID` WHERE P.`CGrpID` = '".$CGrpID."' AND P.`EmpID` >= '".$StartID."' AND P.`EmpID` <= '".$StopID."' AND P.`EmpStatus`='ACTIVE' AND S.`SRecCurrentAppointment`='1' AND (S.ApptStID='AS004' OR S.ApptStID='AS005' OR S.ApptStID='AS006' OR S.ApptStID='AS010')";
			//Get Total Number of Employees with Leave Credits
			$IDs=$MySQLi->NumberOfRows($sql.";");
			//Get First ID (CurrID)
			$CurrID=$MySQLi->GetArray($sql." ORDER BY `EmpID` ASC LIMIT 1;")['EmpID'];
			$Control="C";
			$Done=0;
			echo "$Control|$IDs|$CurrID|$NextID|$Done|>Total Number of Personnel to proccess: $IDs\n>Initializing...\n>Starting...\n>Processing $CurrID...";
			break;
		case "C":
			//Process CurrID
			$TheYear=(intval(date('Y'))-1);
			$LivCredDeductTo=$fLeave->UnusedForceLeave($CurrID,$TheYear);
			$Info="";
			$sql="SELECT `FLCID`, `EmpID` FROM `tblempforceleavecert` WHERE `FLCYear` = '".$TheYear."' AND `EmpID` = '".$CurrID."';";
			if($MySQLi->NumberOfRows($sql)>0){
				$DaysCert=$MySQLi->GetArray($sql);
				$LivCredReference=$DaysCert['FLCID'];
				$LivCredDeductTo=0;
			}
			else{
				$LivCredReference="(No Certification)";
			}
			if($LivCredDeductTo>0){
				//Get New LivCredID
				$NewLivCredID="LC".$TheYear.$CurrID."001";
				$count=1;
				while($MySQLi->NumberOfRows("SELECT `LivCredID` FROM `tblempleavecredits` WHERE `LivCredID`='".$NewLivCredID."';")>0){
					$count+=1;
					$ccc=($count>99)?$count:(($count>9)?"0".$count:"00".$count);
					$NewLivCredID="LC".$TheYear.$CurrID.$ccc;
				} $LivCredID=$NewLivCredID;
				
				$LivCredBalance=$fLeave->AvailableLeaveCredit($CurrID,'LT01')-$LivCredDeductTo;
				
				$LivCredDateFrom=$TheYear."-12-31 23:59:59";
				$LivCredDateTo=$TheYear."-12-31 23:59:59";
				$sql="INSERT INTO `tblempleavecredits` (`LivCredID`, `EmpID`, `LeaveTypeID`, `LivCredDateFrom`, `LivCredDateTo`, `LivCredAddTo`, `LivCredDeductTo`, `LivCredBalance`, `LivCredReference`, `LivCredRemarks`) VALUES ('$LivCredID', '$CurrID', 'LT01', '$LivCredDateFrom', '$LivCredDateTo', 0, $LivCredDeductTo, $LivCredBalance,'$LivCredReference', 'FORCE LEAVE');";
				$MySQLi->sqlQuery($sql,false);
				$Info=" ($LivCredDeductTo Force Leave Deducted)";
			}
			
			
			// Special Privilege Leave (+3 days)
			$curYr=date('Y');
			$EPLID="EPL01".$CurrID.$curYr;
			$ValidFrom=$curYr."-01-01 00:00:00";
			$ValidTo=$curYr."-12-31 23:59:59";
			if($MySQLi->sqlQuery("INSERT INTO `tblempprivilegeleaves` (`EPLID`, `EmpID`, `PLID`, `ValidFrom`, `ValidTo`) VALUES ('".$EPLID."', '".$CurrID."', 'PL001', '".$ValidFrom."', '".$ValidTo."');")){
				//Get New LivCredID
				$LivCredID="LC".$curYr.$CurrID."001";
				$LivCredBalance=$fLeave->AvailableLeaveCredit($CurrID,'LT03',$curYr)+3;
				$LivCredReference=$EPLID;
				$MySQLi->sqlQuery("INSERT INTO `tblempleavecredits` (`LivCredID`, `EmpID`, `LeaveTypeID`, `LivCredDateFrom`, `LivCredDateTo`, `LivCredAddTo`, `LivCredDeductTo`, `LivCredBalance`, `LivCredReference`, `LivCredRemarks`) VALUES ('".$LivCredID."', '".$CurrID."', 'LT03', '".$ValidFrom."', '".$ValidFrom."', 3, 0, ".$LivCredBalance.",'".$LivCredReference."', 'SPECIAL PRIVILEGE');");
			}
			// ---------------------------------
			
			
			$Done+=1;
			$PrevID=$CurrID;
			//Get Next ID
			$sql="SELECT P.`EmpID` FROM `pmis`.`tblemppersonalinfo` P JOIN `pmis`.`tblempservicerecords` S ON P.`EmpID` = S.`EmpID` WHERE P.`CGrpID` = '".$CGrpID."' AND P.`EmpID` > '".$PrevID."' AND P.`EmpID` >= '".$StartID."' AND P.`EmpID` <= '".$StopID."' AND P.`EmpStatus`='ACTIVE' AND S.`SRecCurrentAppointment`='1' AND (S.ApptStID='AS004' OR S.ApptStID='AS005' OR S.ApptStID='AS006' OR S.ApptStID='AS010')";
			if($NextID=$MySQLi->GetArray($sql." ORDER BY `EmpID` ASC LIMIT 1;")['EmpID']){
				$Control="C";
				$CurrID=$NextID;
				echo "$Control|$IDs|$CurrID|$NextID|$Done| DONE $Info\n>Processing $CurrID...";
			}
			else{
				$Control="E";
				echo "$Control|$IDs|$CurrID|$NextID|$Done| DONE $Info\n\n>Update Complete\n\n\n";
			}
			break;
		case "E":
			$Control="E";
			echo "$Control|$IDs|$CurrID|$NextID|$Done| DONE";
			break;
	}
	
	function x(){
	//Get two consecutive EmpIDs to Update
	
	//Process EmpID
	
	//Get New LivCredID
	$NewLivCredID="LC".$LeaveCreditInfo['year'].$EmpID."001";
	$count=1;
	while($MySQLi->NumberOfRows("SELECT `LivCredID` FROM `tblempleavecredits` WHERE `LivCredID`='".$NewLivCredID."';")>0){
		$count+=1;
		$ccc=($count>99)?$count:(($count>9)?"0".$count:"00".$count);
		$NewLivCredID="LC".$LeaveCreditInfo['year'].$EmpID.$ccc;
	} $LivCredID=$NewLivCredID;
	
	$sql="INSERT INTO `tblempleavecredits` (`LivCredID`, `EmpID`, `LeaveTypeID`, `LivCredDateFrom`, `LivCredDateTo`, `LivCredAddTo`, `LivCredDeductTo`, `LivCredBalance`, `LivCredReference`, `LivCredRemarks`) VALUES ('$LivCredID', '$EmpID', 'LT01', '$LivCredDateFrom', '$LivCredDateTo', $LivCredAddTo, $LivCredDeductTo, $LivCredBalance,'$LivCredReference', '$LivCredRemarks');";
	$MySQLi->sqlQuery($sql,false);
	
	//Send feedback and next EmpID
	}
?>