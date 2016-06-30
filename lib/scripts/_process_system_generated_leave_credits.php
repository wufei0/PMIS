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


	
	$TIMESTAMP=date('Y-m-d H:i:s');
	$thisMonth=date('m');
	$thisYear=date('Y');
	$MONTHS=Array('','JANUARY','FEBRUARY','MARCH','APRIL','MAY','JUNE','JULY','AUGUST','SEPTEMBER','OCTOBER','NOVEMBER','DECEMBER');
	
	/* Get POST Values */
	$EmpID=isset($_POST['id'])?trim(strip_tags($_POST['id'])):'00000';
	$Yr=isset($_POST['yr'])?trim(strip_tags($_POST['yr'])):date('Y');
	$Mo=isset($_POST['mo'])?trim(strip_tags($_POST['mo'])):date('m');
	$Yr=($Yr=="0")?$thisYear:$Yr; $Mo=($Mo=="0")?$thisMonth:$Mo;

	$MySQLi=new MySQLClass();
	
	/* Get Current/Last Leave Credit Balance */
	$VL_credit=$SL_credit=$PL_credit=$OL_credit=0;
	$result=$MySQLi->sqlQuery("SELECT `LeaveTypeID`, `LivCredBalance` FROM `tblempleavecredits` WHERE `EmpID` = '".$EmpID."' ORDER BY `LivCredDateFrom` ASC;");
	while($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
		$VL_credit=($records['LeaveTypeID']=="LT01")?$records['LivCredBalance']:$VL_credit;
		$SL_credit=($records['LeaveTypeID']=="LT02")?$records['LivCredBalance']:$SL_credit;
	}
	
	$VL_LastDate=$SL_LastDate="2005-01-01"; /* <-- Change to DATE APPOINTED/DATE HIRED as CASUAL/PERMANENT */
	$result=$MySQLi->sqlQuery("SELECT `EmpID`, `SRecFromDate`, `SRecToDate` FROM `s_servicerecord` WHERE `EmpID` = '".$EmpID."' AND (`ApptStID`='AS004' OR `ApptStID`='AS005' OR `ApptStID`='AS008' OR `ApptStID`='AS009' OR `ApptStID`='AS010' OR `ApptStID`='AS011' OR `ApptStID`='AS013') ORDER BY `SRecFromDate` ASC LIMIT 1;");
	if($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
		$VL_LastDate=$SL_LastDate=substr((str_replace('-','',$records['SRecFromDate'])),0,6);
		//$records['SRecFromDate']=str_replace('-','',$records['SRecFromDate']);$records['SRecFromDate']=substr((str_replace('-','',$records['SRecFromDate'])),0,6);
		$b_balance=1.250;
		$dateStart=intval(substr($records['SRecFromDate'],-2));
		if($dateStart>1){
			$b_balance=round(floatval((31-$dateStart)*0.041667),3);
			
		}
		//echo "0|$EmpID|ERROR 49:~".$VL_LastDate;exit();
	}
	else{echo "0|$EmpID|ERROR 49:~Can't find appropriate appointment status. Please check service record."; exit();}
	//echo "0|$EmpID|EmpID: $EmpID<br/>VL_LastDate: $VL_LastDate<br/>SRecFromDate: ".$records['SRecFromDate']."<br/>dateStart: ".$dateStart."<br/>b_balance: ".$b_balance;exit();
	
	/* Get Last System Generated Date */
	$result=$MySQLi->sqlQuery("SELECT `LeaveTypeID`, `LivCredAddTo`, `LivCredBalance`, DATE_FORMAT(`LivCredDateFrom`, '%Y%m') AS `LivCredDateFrom`, DATE_FORMAT(`LivCredDateTo`, '%Y-%m-%d') AS `LivCredDateTo`, `LivCredRemarks` FROM `tblempleavecredits` WHERE `EmpID` = '".$EmpID."' AND `LivCredAddTo`='1.25' AND `LivCredRemarks`='System Generated' ORDER BY `LivCredDateFrom` ASC;");
	while($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
		$VL_LastDate=($records['LeaveTypeID']=="LT01")?$records['LivCredDateFrom']:$VL_LastDate;
		$SL_LastDate=($records['LeaveTypeID']=="LT02")?$records['LivCredDateFrom']:$SL_LastDate;
	}

	$refDate=($VL_LastDate<=$SL_LastDate)?$VL_LastDate:$SL_LastDate;
	$m=intval(substr($refDate,-2))+1;
	$y=substr($refDate,0,4);
	if($m>12){$y+=1;$m=1;}
	$Start=$y.($m>9?$m:"0".$m);
	
	$Until=$Yr.$Mo;
	$Until=($Until>=$thisYear.$thisMonth)?$thisYear.(($thisMonth-1)>9?($thisMonth-1):"0".($thisMonth-1)):$Until;
	//echo "0|$EmpID|VL_LastDate: $VL_LastDate<br/>SL_LastDate: $SL_LastDate<br/>refDate: $refDate<br/>Start: $Start<br/>Until: $Until<br/>";exit();
	while($Start<=$Until){
		$LivCredDateFrom=$y."-".($m>9?$m:"0".$m)."-01 00:00:00";
		$LivCredDateTo=$y."-".($m>9?$m:"0".$m)."-".cal_days_in_month(CAL_GREGORIAN,$m,$y)." 00:00:00";
		
		/* Get New LivCredID - VACATION LEAVE */
		if($Start>$VL_LastDate){
			$NewLivCredID="LC".$y.$EmpID."001";
			$count=1;
			while($MySQLi->NumberOfRows("SELECT `LivCredID` FROM `tblempleavecredits` WHERE `LivCredID`='".$NewLivCredID."';")>0){
				$count+=1;
				$ccc=($count>99)?$count:(($count>9)?"0".$count:"00".$count);
				$NewLivCredID="LC".$y.$EmpID.$ccc;
			} $LivCredID=$NewLivCredID;
			
			$VL_credit+=1.25;
			$MySQLi->sqlQuery("INSERT INTO `tblempleavecredits` (`LivCredID`, `EmpID`, `LeaveTypeID`, `LivCredDateFrom`, `LivCredDateTo`, `LivCredAddTo`, `LivCredDeductTo`, `LivCredBalance`, `LivCredReference`, `LivCredRemarks`, `RECORD_TIME`) VALUES ('".$LivCredID."', '".$EmpID."', 'LT01', '".$LivCredDateFrom."', '".$LivCredDateTo."', '1.25', '0', '".$VL_credit."', 'Leave Credit', 'System Generated', NOW());");
		}
		/* Get New LivCredID - SICK LEAVE */
		if($Start>$SL_LastDate){
			$count+=1;
			$ccc=($count>99)?$count:(($count>9)?"0".$count:"00".$count);
			$NewLivCredID="LC".$y.$EmpID.$ccc;
			while($MySQLi->NumberOfRows("SELECT `LivCredID` FROM `tblempleavecredits` WHERE `LivCredID`='".$NewLivCredID."';")>0){
				$count+=1;
				$ccc=($count>99)?$count:(($count>9)?"0".$count:"00".$count);
				$NewLivCredID="LC".$y.$EmpID.$ccc;
			} $LivCredID=$NewLivCredID;
			
			$SL_credit+=1.25;
			$MySQLi->sqlQuery("INSERT INTO `tblempleavecredits` (`LivCredID`, `EmpID`, `LeaveTypeID`, `LivCredDateFrom`, `LivCredDateTo`, `LivCredAddTo`, `LivCredDeductTo`, `LivCredBalance`, `LivCredReference`, `LivCredRemarks`, `RECORD_TIME`) VALUES ('".$LivCredID."', '".$EmpID."', 'LT02', '".$LivCredDateFrom."', '".$LivCredDateTo."', '1.25', '0', '".$SL_credit."', 'Leave Credit', 'System Generated', NOW());");
		}
		
		$m+=1;
		if($m>12){$y+=1;$m=1;}
		$Start=$y.($m>9?$m:"0".$m);
	}
	echo "1|$EmpID|System generated leave credits were successfully confirmed and updated.";
	// echo "1|$EmpID|System generated leave credits were successfully confirmed and updated.<br/><br/>EmpID: $EmpID<br>Yr: $Yr<br>Mo: $Mo<br>y: $y<br>m: $m<br>VL_LastDate: $VL_LastDate<br>SL_LastDate: $SL_LastDate<br>VL_credit: $VL_credit<br>SL_credit: $SL_credit<br>Start: $Start<br>Until: $Until";
	exit();
	
	
	ob_end_flush();
?>