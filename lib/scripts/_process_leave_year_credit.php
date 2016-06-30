<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD001'));
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	require_once $_SESSION['path'].'/lib/classes/Functions.php';
	
	//Get POST Values
	$PerID=isset($_POST['pid'])?trim(strip_tags($_POST['pid'])):"0";
	$CheckBoxes_pl=isset($_POST['cb_pl'])?trim(strip_tags($_POST['cb_pl'])):"";
	
	//echo "1|".$_SESSION['user']."|CheckBoxes_pl: $CheckBoxes_pl <br/>PerID: $PerID";exit();
	//if(!(($Authorization[0])||($_SESSION['user']==$PerID))){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	$MySQLi=new MySQLClass();
	$fLeave=new LeaveFunctions();
	
	$Msg="";
	if($CheckBoxes_pl!=""){
		$PLs=explode(",",$CheckBoxes_pl);
		foreach($PLs as $PL){
			$PL_=explode(":",$PL);
			$PLID=$PL_[0];$ValidTo=$PL_[1];
			$ValidFrom=($PLID=="PL001")?date('Y')."-01-01 00:00:00":date('Y-m-d H:i:s');
			$TheYear=substr($ValidFrom, 0, 4);
			
			if($PLinfo=$MySQLi->GetArray("SELECT `EPLID` FROM tblempprivilegeleaves WHERE `PLID` = '".$PLID."' AND `EPLID` LIKE 'EPL%".$PerID.date('Y')."';")){
				
				if(($PLID=="PL001")&&($fLeave->AvailableLeaveCredit($PerID,'LT03',$TheYear)!=3)){
					echo "0|".$_SESSION['user']."|ERROR 401:~Special Privilege Leave cannot be modified. Employee (".$PerID.") has already filed privilege leave this current year.";exit();
				}
				$EPLID=$PLinfo['EPLID'];
				if($MySQLi->sqlQuery("UPDATE `tblempprivilegeleaves` SET `ValidFrom`='".$ValidFrom."', `ValidTo`='".$ValidTo."'  WHERE `EPLID`='".$EPLID."' LIMIT 1;")){
					$MySQLi->sqlQuery("UPDATE `tblempleavecredits` SET `LivCredDateFrom`='".$ValidFrom."', `LivCredDateTo`='".$ValidFrom."' WHERE `LivCredReference`='".$EPLID."' LIMIT 1;");
				}
			}
			else{
				/* Get New EPLID */
				$NewEPLID="EPL01".$PerID.date('Y');
				$count=1;
				while($records=$MySQLi->GetArray("SELECT `EPLID` FROM tblempprivilegeleaves WHERE `EPLID` = '".$NewEPLID."';")){
					$count+=1;
					$cc=($count>9)?$count:"0".$count;
					$NewEPLID="EPL".$cc.$PerID.date('Y');
				} $EPLID=$NewEPLID;
			
				if($MySQLi->sqlQuery("INSERT INTO `tblempprivilegeleaves` (`EPLID`, `EmpID`, `PLID`, `ValidFrom`, `ValidTo`) VALUES ('".$EPLID."', '".$PerID."', '".$PLID."', '".$ValidFrom."', '".$ValidTo."');")){
					//Get New LivCredID
					$NewLivCredID="LC".$TheYear.$PerID."001";
					$count=1;
					while($MySQLi->NumberOfRows("SELECT `LivCredID` FROM `tblempleavecredits` WHERE `LivCredID`='".$NewLivCredID."';")>0){
						$count+=1;
						$ccc=($count>99)?$count:(($count>9)?"0".$count:"00".$count);
						$NewLivCredID="LC".$TheYear.$PerID.$ccc;
					} $LivCredID=$NewLivCredID;
					
					$PLinfo=$MySQLi->GetArray("SELECT `PLName`, `PLNumberOfDays` FROM `tblprivilegeleaves` WHERE `PLID`='".$PLID."';");
					$LivCredDateFrom=$ValidFrom;
					$LivCredDateTo=$ValidFrom;
					$LivCredAddTo=$PLinfo['PLNumberOfDays'];
					$LivCredBalance=$fLeave->AvailableLeaveCredit($PerID,'LT03',$TheYear)+floatval($PLinfo['PLNumberOfDays']);
					$LivCredReference=$EPLID;
					$Remarks=$PLinfo['PLName'];
					$MySQLi->sqlQuery("INSERT INTO `tblempleavecredits` (`LivCredID`, `EmpID`, `LeaveTypeID`, `LivCredDateFrom`, `LivCredDateTo`, `LivCredAddTo`, `LivCredDeductTo`, `LivCredBalance`, `LivCredReference`, `LivCredRemarks`) VALUES ('".$LivCredID."', '".$PerID."', 'LT03', '".$LivCredDateFrom."', '".$LivCredDateTo."', ".$LivCredAddTo.", 0, ".$LivCredBalance.",'".$LivCredReference."', '".$Remarks."');");
				}
			}
			
			
		}
		$Msg.="Privilege leave credit was updated.<br/>";
		//echo "1|".$_SESSION['user']."|sql: $sql";exit();
	}
	
	if(date('m')=="12"){// Set Month Only to December -> 12
		$CGrpID=isset($_POST['cgp'])?trim(strip_tags($_POST['cgp'])):"0";
		$CheckBoxes_fl=isset($_POST['cb_fl'])?trim(strip_tags($_POST['cb_fl'])):"";
		$SelectDays=isset($_POST['dys'])?trim(strip_tags($_POST['dys'])):"";
		
		$thisYear=date('Y');
		$DateSubmitted=date('Y-m-d H:i:s');
		
		$PerDays=array();
		$sds=explode(",",$SelectDays);
		foreach($sds as $sd){
			$do=explode(":",$sd);
			$PerDays[$do[0]]=$do[1];
		}
		//print_r($sds);
		//echo "0|".$_SESSION['user']."|CheckBoxes_fl -> ".substr($CheckBoxes_fl, 0, -1)."<br/>SelectDays -> ".substr($SelectDays, 0, -1)."<br/>PerDays -> ".$PerDays;exit();
		
		//$CertON=array();
		$cbs=explode(",",$CheckBoxes_fl);
		foreach($cbs as $cb){
			$co=explode(":",$cb);
			if($MySQLi->NumberOfRows("SELECT `FLCID` FROM `tblempforceleavecert` WHERE `FLCYear`='".$thisYear."' AND `EmpID`='".$co[0]."';")>0){
				if($co[1]==1){ // With Certification
					$MySQLi->sqlQuery("UPDATE `tblempforceleavecert` SET `NumberOfDays`='".$PerDays[$co[0]]."' WHERE `EmpID`='".$co[0]."' LIMIT 1;");
				}
				else{				// Without Certification
					$MySQLi->sqlQuery("DELETE FROM `tblempforceleavecert` WHERE `FLCYear`='".$thisYear."' AND `EmpID`='".$co[0]."' LIMIT 1;");
				}
			}
			else{
				if($co[1]==1){ // With Certification
					/* Get New UserGroupID */
					$FLCID="FL".substr($thisYear, -2, 2).$co[0];
					$MySQLi->sqlQuery("INSERT INTO `tblempforceleavecert` (`FLCID`, `EmpID`, `FLCYear`, `NumberOfDays`, `DateSubmitted`) VALUES ('".$FLCID."', '".$co[0]."', '".$thisYear."', '".$PerDays[$co[0]]."', '".$DateSubmitted."');");
				}
			}
		}
		$Msg.="Certification of Unused force leave was updated.<br/>".$CheckBoxes_fl;
	}
	
	echo "1|".$_SESSION['user']."|".$Msg;
	ob_end_flush();
?>