<?php
	ob_start();
	session_start();
	date_default_timezone_set('Asia/Taipei');
		
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD002'));
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	function FixDateLog($ThatDate,$DateLog){$LogDate=explode(" ",$DateLog);return $ThatDate." ".$LogDate[1];}
	function isOK($DateLog){$iS=(substr($DateLog,0,4)!="2000");return $iS;}
	
	$StartID=isset($_POST['iid'])?trim(strip_tags($_POST['iid'])):"80000";
	$EndID=isset($_POST['lid'])?trim(strip_tags($_POST['lid'])):"90000";
	$st=isset($_POST['st'])?trim(strip_tags($_POST['st'])):-1;
	$curID=isset($_POST['cid'])?trim(strip_tags($_POST['cid'])):"00000";
	$nextID=isset($_POST['nid'])?trim(strip_tags($_POST['nid'])):"00000";
	$DoneLivCr=isset($_POST['did'])?trim(strip_tags($_POST['did'])):0;
	$TotalLeaveCreditInfo=isset($_POST['tid'])?trim(strip_tags($_POST['tid'])):0;
	$LeaveType=isset($_POST['lt'])?trim(strip_tags($_POST['lt'])):"VL";	
	
	$cnx=odbc_pconnect('pmis_sybase','sa','') or die("Error Connect to Database");
	if(!$cnx){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
	
	switch ($st){
		case -1	:
					if($LeaveType=="XL"){$_SESSION['YrForPLMonitor']='1970';}
					$cur=odbc_exec($cnx,"SELECT count(*) AS TotalLeaveCreditInfo FROM m_leave_credit_new JOIN personal ON m_leave_credit_new.pers_id = personal.pers_id WHERE m_leave_credit_new.type = '".$LeaveType."' AND personal.pers_id >= '".$StartID."' AND personal.pers_id <= '".$EndID."'");
					if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
					$LeaveCredit=odbc_fetch_array($cur);
					$TotalLeaveCreditInfo=$LeaveCredit['TotalLeaveCreditInfo'];
					
					$sql="SELECT pers_id FROM personal WHERE pers_id >= '".$StartID."' AND pers_id <= '".$EndID."' ORDER BY pers_id ASC";
					$cur=odbc_exec($cnx,$sql);
					if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned) $sql";odbc_close($cnx);exit();}
					$Personal=odbc_fetch_array($cur);
					$curID=$nextID=$Personal['pers_id'];
					$DoneLivCr=0;
					
					$MSG="\nMigrating of all Leave Credits ($TotalLeaveCreditInfo) from Sybase database to MySQL.\n\nProcessing $curID . . . ";
					$respTxt="0|$curID|$nextID|$DoneLivCr|$TotalLeaveCreditInfo|$MSG";
					break;
					
		case 0	:
					$EmpID=$curID=$nextID;
					$MySQLi=new MySQLClass();
					$cur=odbc_exec($cnx,"SELECT count(*) AS RecOnThisID FROM m_leave_credit_new WHERE pers_id = '".$curID."'");
					if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
					$LeaveCredit=odbc_fetch_array($cur);
					$RecOnThisID=$LeaveCredit['RecOnThisID'];
					$DoneLvCrOnID=0;
					if($RecOnThisID>0){
						if($LeaveType=="VL"){$sql="DELETE FROM `tblempleavecredits` WHERE `LeaveTypeID`='LT01' AND `EmpID`='".$EmpID."';";$MySQLi->sqlQuery($sql,false);}
						else if($LeaveType=="SL"){$sql="DELETE FROM `tblempleavecredits` WHERE `LeaveTypeID`='LT02' AND `EmpID`='".$EmpID."';";$MySQLi->sqlQuery($sql,false);}
						else if($LeaveType=="XL"){$sql="DELETE FROM `tblempleavecredits` WHERE `LeaveTypeID`='LT03' AND `EmpID`='".$EmpID."';";$MySQLi->sqlQuery($sql,false);}
						
						
						/* Migrate Leave Credit Information */
						$cur=odbc_exec($cnx,"SELECT * FROM m_leave_credit_new WHERE pers_id = '".$curID."' AND type = '".$LeaveType."'");
						if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
						
						while($LeaveCreditInfo=odbc_fetch_array($cur)){
							$LivCredDateFrom=(strlen($LeaveCreditInfo['datefrom'])<10)?"1970-01-01 00:00:01":trim($LeaveCreditInfo['datefrom']);
							$LivCredDateTo=(strlen($LeaveCreditInfo['dateto'])<10)?"1970-01-01 00:00:01":trim($LeaveCreditInfo['dateto']);
							$LivCredAddTo=is_numeric($LeaveCreditInfo['addto'])?trim($LeaveCreditInfo['addto']):0;
							$LivCredDeductTo=is_numeric($LeaveCreditInfo['lessto'])?trim($LeaveCreditInfo['lessto']):0;
							$LivCredReference=$MySQLi->RealEscapeString(trim($LeaveCreditInfo['reference']));
							$LivCredRemarks=$MySQLi->RealEscapeString(trim($LeaveCreditInfo['remark']));
							if(($LivCredReference=="Leave Credit")&&($LivCredRemarks=="System Generated")){
								$y=substr($LivCredDateTo,0,4);
								$m=substr($LivCredDateTo,5,2);
								$LivCredDateTo=$y."-".$m."-".cal_days_in_month(CAL_GREGORIAN,$m,$y)." 00:00:00";
							}
							if($LeaveType=="VL"){$LivCredBalance=is_numeric($LeaveCreditInfo['vl_bal'])?trim($LeaveCreditInfo['vl_bal']):0;$LeaveTypeID="LT01";}
							else if($LeaveType=="SL"){$LivCredBalance=is_numeric($LeaveCreditInfo['sl_bal'])?trim($LeaveCreditInfo['sl_bal']):0;$LeaveTypeID="LT02";}
							else if($LeaveType=="UL"){$LivCredBalance=is_numeric($LeaveCreditInfo['vl_bal'])?trim($LeaveCreditInfo['vl_bal']):0;$LeaveTypeID="LT01";$LivCredRemarks="UNDERTIME";}
							else if($LeaveType=="WL"){$LivCredBalance=is_numeric($LeaveCreditInfo['vl_bal'])?trim($LeaveCreditInfo['vl_bal']):0;$LeaveTypeID="LT01";$LivCredRemarks=" MONETIZATION";}
							else if($LeaveType=="FL"){$LivCredBalance=is_numeric($LeaveCreditInfo['vl_bal'])?trim($LeaveCreditInfo['vl_bal']):0;$LeaveTypeID="LT01";$LivCredRemarks="FORCE LEAVE";$LivCredDateFrom=$LivCredDateTo=substr($LivCredDateFrom,0,4)."-12-31 23:59:59";}
							else if($LeaveType=="XL"){
								if($_SESSION['YrForPLMonitor']!=substr($LeaveCreditInfo['datefrom'],0,4)){
									$_SESSION['YrForPLMonitor']=substr($LeaveCreditInfo['datefrom'],0,4);
									$_SESSION['BalForPLMonitor']=$LivCredBalance=3;
								}
								//else{$_SESSION['BalForPLMonitor']=$_SESSION['BalForPLMonitor']-$LivCredDeductTo;}
								$_SESSION['BalForPLMonitor']=$_SESSION['BalForPLMonitor']-$LivCredDeductTo;
								$LivCredBalance=$_SESSION['BalForPLMonitor'];
								$LeaveTypeID="LT03";
							}
							
							//Get New LivCredID
							$NewLivCredID="LC".$LeaveCreditInfo['year'].$EmpID."001";
							$count=1;
							while($MySQLi->NumberOfRows("SELECT `LivCredID` FROM `tblempleavecredits` WHERE `LivCredID`='".$NewLivCredID."';")>0){
								$count+=1;
								$ccc=($count>99)?$count:(($count>9)?"0".$count:"00".$count);
								$NewLivCredID="LC".$LeaveCreditInfo['year'].$EmpID.$ccc;
							} $LivCredID=$NewLivCredID;
							
							$sql="INSERT INTO `tblempleavecredits` (`LivCredID`, `EmpID`, `LeaveTypeID`, `LivCredDateFrom`, `LivCredDateTo`, `LivCredAddTo`, `LivCredDeductTo`, `LivCredBalance`, `LivCredReference`, `LivCredRemarks`,RECORD_TIME) VALUES ('$LivCredID', '$EmpID', '$LeaveTypeID', '$LivCredDateFrom', '$LivCredDateTo',$LivCredAddTo,$LivCredDeductTo,$LivCredBalance,'$LivCredReference', '$LivCredRemarks',RECORD_TIME);";
							$MySQLi->sqlQuery($sql,false);
							
							$DoneLvCrOnID+=1;
							$DoneLivCr+=1;
						}
					}
					
					/* Check ID */
					if($DoneLivCr>=$TotalLeaveCreditInfo){
						$MSG="$DoneLvCrOnID DONE\n\nMigration Complete.";
						$respTxt="1|$curID|$nextID|$DoneLivCr|$TotalLeaveCreditInfo|$MSG";
					}
					else{
						/* Get Next ID */ 
						$cur=odbc_exec($cnx,"SELECT pers_id FROM personal WHERE pers_id > '".$curID."' AND pers_id >= '".$StartID."' AND pers_id <= '".$EndID."' ORDER BY pers_id ASC");
						if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
						if(odbc_fetch_row($cur)){$nextID=odbc_result($cur,1);}
						else{$nextID="";}
						
						if($nextID!=""){
							$respTxt="0|$curID|$nextID|$DoneLivCr|$TotalLeaveCreditInfo|$DoneLvCrOnID DONE\nProcessing $nextID . . . ";
						}
						else{
							$MSG="$DoneLvCrOnID DONE\n\nThere was an error getting the next ID.";
							$respTxt="1|$curID|$nextID|$DoneLivCr|$TotalLeaveCreditInfo|$MSG";
						}
					}
					
					break;
					
		default	:	echo "-1|0|0|0|0|ERROR 49:~!!!";exit();
	}
	
  odbc_close($cnx);
	echo $respTxt;


?>