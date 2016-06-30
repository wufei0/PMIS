<?php
	ob_start();
	session_start();
	date_default_timezone_set('Asia/Taipei');
	
	require_once $_SESSION['path'].'/lib/classes/MySQLClass.php';


	
	$st=isset($_POST['st'])?trim(strip_tags($_POST['st'])):-1;
	$ActivePersonnel=isset($_POST['ap'])?trim(strip_tags($_POST['ap'])):1;
	$EmpID=isset($_POST['id'])?trim(strip_tags($_POST['id'])):"00000";
	$TotalRecordToMigrate=isset($_POST['tr'])?trim(strip_tags($_POST['tr'])):1;
	$DoneRecords=isset($_POST['dn'])?trim(strip_tags($_POST['dn'])):0;
	$DoneID=isset($_POST['dd'])?trim(strip_tags($_POST['dd'])):0;
	$LeaveType=isset($_POST['lt'])?trim(strip_tags($_POST['lt'])):"VL";	
	
	$cnx=odbc_connect('pmisdb','sa','');
	if(!$cnx){Error_handler("Error in odbc_connect",$cnx);}
	
	switch ($st){
		case -1	: 	// send a simple odbc query. returns an odbc cursor
					$sql="SELECT count(*) AS ActivePersonnel, pers_id FROM personal WHERE employment_status = 'C' ORDER BY pers_id ASC";
					$cur=odbc_exec($cnx,$sql);
					if(!$cur){Error_handler("Error in odbc_exec(no cursor returned) $sql",$cnx);}
					$records=odbc_fetch_array($cur);
					$ActivePersonnel=$records['ActivePersonnel'];
					$EmpID=$records['pers_id'];
					
					$sql="SELECT count(*) AS TotalRecordToMigrate FROM m_leave_credit_new WHERE pers_id IN (SELECT pers_id FROM personal WHERE employment_status = 'A') AND type='".$LeaveType."'";
					$cur=odbc_exec($cnx,$sql);
					if(!$cur){Error_handler("Error in odbc_exec(no cursor returned) $sql",$cnx);}
					$records=odbc_fetch_array($cur);
					$TotalRecordToMigrate=$records['TotalRecordToMigrate'];
					
					$respTxt="0|$LeaveType|0|$ActivePersonnel|0|$TotalRecordToMigrate|0|$ActivePersonnel active employees.";
					break;
					
		case 0	: 			
					$MySQLi=new MySQLClass();
					$sql="";
					if($LeaveType=="VL"){$sql="DELETE FROM `tblempleavecredits` WHERE `LeaveTypeID`='LT01' AND `EmpID`='".$EmpID."';";}
					else if($LeaveType=="SL"){$sql="DELETE FROM `tblempleavecredits` WHERE `LeaveTypeID`='LT02' AND `EmpID`='".$EmpID."';";}
					else if($LeaveType=="UL"){$sql="DELETE FROM `tblempleavecredits` WHERE `LeaveTypeID`='LTX1' AND `EmpID`='".$EmpID."';";}
					else if($LeaveType=="XL"){$sql="DELETE FROM `tblempleavecredits` WHERE `LeaveTypeID`='LT03' AND `EmpID`='".$EmpID."';";}
					$MySQLi->sqlQuery($sql);
					
					$sql="SELECT * FROM m_leave_credit_new WHERE type='".$LeaveType."' AND pers_id='".$EmpID."'";
					$cur=odbc_exec($cnx, $sql);
					if(!$cur){Error_handler("Error in odbc_exec(no cursor returned) ",$cnx);}
					
					while($records=odbc_fetch_array($cur)){
						$EmpID=mysql_escape_string($records['pers_id']);
						$LivCredDateFrom=(strlen($records['datefrom'])<10)?"1970-01-01 00:00:01":mysql_escape_string($records['datefrom']);
						$LivCredDateTo=(strlen($records['dateto'])<10)?"1970-01-01 00:00:01":mysql_escape_string($records['dateto']);
						$LivCredAddTo=is_numeric($records['addto'])?mysql_escape_string($records['addto']):0;
						$LivCredDeductTo=is_numeric($records['lessto'])?mysql_escape_string($records['lessto']):0;
						$LivCredReference=mysql_escape_string($records['reference']);
						$LivCredRemarks=mysql_escape_string($records['remark']);
						
						if($LeaveType=="VL"){$LivCredBalance=is_numeric($records['vl_bal'])?mysql_escape_string($records['vl_bal']):0;$LeaveTypeID="LT01";}
						else if($LeaveType=="SL"){$LivCredBalance=is_numeric($records['sl_bal'])?mysql_escape_string($records['sl_bal']):0;$LeaveTypeID="LT02";}
						else if($LeaveType=="UL"){$LivCredBalance=is_numeric($records['sl_bal'])?mysql_escape_string($records['sl_bal']):0;$LeaveTypeID="LTX1";}
						else if($LeaveType=="XL"){$LivCredBalance=is_numeric($records['sl_bal'])?mysql_escape_string($records['sl_bal']):0;$LeaveTypeID="LT03";}
						
						//Get New LivCredID
						$NewLivCredID="LC".$records['year'].$EmpID."001";
						$count=1;
						while($MySQLi->NumberOfRows("SELECT `LivCredID` FROM `tblempleavecredits` WHERE `LivCredID`='".$NewLivCredID."';")>0){
							$count+=1;
							$ccc=($count>99)?$count:(($count>9)?"0".$count:"00".$count);
							$NewLivCredID="LC".$records['year'].$EmpID.$ccc;
						} $LivCredID=$NewLivCredID;
						
						$sql="INSERT INTO `tblempleavecredits` (`LivCredID`, `EmpID`, `LeaveTypeID`, `LivCredDateFrom`, `LivCredDateTo`, `LivCredAddTo`, `LivCredDeductTo`, `LivCredBalance`, `LivCredReference`, `LivCredRemarks`,RECORD_TIME) VALUES ('$LivCredID', '$EmpID', '$LeaveTypeID', '$LivCredDateFrom', '$LivCredDateTo',$LivCredAddTo,$LivCredDeductTo,$LivCredBalance,'$LivCredReference', '$LivCredRemarks',RECORD_TIME);";
						$MySQLi->sqlQuery($sql,false);
						
						$DoneRecords+=1;
					}
					
					$DoneID+=1;
					
					if($DoneRecords>=$TotalRecordToMigrate){
						$respTxt="1|$LeaveType|$EmpID|$ActivePersonnel|$DoneID|$TotalRecordToMigrate|$DoneRecords|$DoneRecords / $TotalRecordToMigrate (".number_format(($DoneRecords/$TotalRecordToMigrate)*100,2)." %) Migration Complete";
					}
					else{
						$sql="SELECT pers_id FROM personal WHERE employment_status = 'C' AND pers_id > '".$EmpID."' ORDER BY pers_id ASC"; 
						$cur=odbc_exec($cnx,$sql);
						if(!$cur){Error_handler("Error in odbc_exec(no cursor returned) $sql",$cnx);}
						if(odbc_fetch_row($cur)){$Next_EmpID=odbc_result($cur,1);}
						else{$Next_EmpID="";}
						
						if($Next_EmpID!=""){$respTxt="0|$LeaveType|$Next_EmpID|$ActivePersonnel|$DoneID|$TotalRecordToMigrate|$DoneRecords|$DoneRecords / $TotalRecordToMigrate (".number_format(($DoneRecords/$TotalRecordToMigrate)*100,2)." %)";}
						else{$respTxt="|1|$LeaveType|$EmpID|$ActivePersonnel|$DoneID|$TotalRecordToMigrate|$DoneRecords|$DoneRecords / $TotalRecordToMigrate (".number_format(($DoneRecords/$TotalRecordToMigrate)*100,2)." %)";}
					}
					break;
					
		default	:	$respTxt="999|ERROR 49!!!|||";
	}
    odbc_close($cnx);
	echo $respTxt;
	
	function Error_Handler($msg,$cnx){
		echo "$msg \n";
		odbc_close($cnx);
		exit();
	}

?>