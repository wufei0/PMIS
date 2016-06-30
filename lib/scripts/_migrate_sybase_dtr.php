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

	
	
	$EmploymentStatus=isset($_POST['es'])?trim(strip_tags($_POST['es'])):"A";
	$StartID=isset($_POST['iid'])?trim(strip_tags($_POST['iid'])):'58001';
	$EndID=isset($_POST['lid'])?trim(strip_tags($_POST['lid'])):'59032';
	$st=isset($_POST['st'])?trim(strip_tags($_POST['st'])):-1;
	$fYr=isset($_POST['fyr'])?trim(strip_tags($_POST['fyr'])):date('Y');
	$fMo=isset($_POST['fmo'])?trim(strip_tags($_POST['fmo'])):date('m');
	$fDy=isset($_POST['fdy'])?trim(strip_tags($_POST['fdy'])):date('d');
	$tYr=isset($_POST['tyr'])?trim(strip_tags($_POST['tyr'])):date('Y');
	$tMo=isset($_POST['tmo'])?trim(strip_tags($_POST['tmo'])):date('m');
	$tDy=isset($_POST['tdy'])?trim(strip_tags($_POST['tdy'])):date('d');
	$curID=isset($_POST['cid'])?trim(strip_tags($_POST['cid'])):"00000";
	$nextID=isset($_POST['nid'])?trim(strip_tags($_POST['nid'])):"00000";
	$DoneIDs=isset($_POST['did'])?trim(strip_tags($_POST['did'])):0;
	$TotalEmployee=isset($_POST['tid'])?trim(strip_tags($_POST['tid'])):0;
	$curDate=isset($_POST['cdt'])?trim(strip_tags($_POST['cdt'])):"00000";
	$nextDate=isset($_POST['ndt'])?trim(strip_tags($_POST['ndt'])):"00000";
	$DoneRecOnThisID=isset($_POST['dtd'])?trim(strip_tags($_POST['dtd'])):0;
	$RecordsOnThisID=isset($_POST['rid'])?trim(strip_tags($_POST['rid'])):0;
	
	/* Fixed Month and Day value in 2 digits format */
	$fMo=($fMo<10)?"0".$fMo:$fMo;$tMo=($tMo<10)?"0".$tMo:$tMo;
	$fDy=($fDy<10)?"0".$fDy:$fDy;$tDy=($tDy<10)?"0".$tDy:$tDy;
	/* Make date format */
	$DateFrom="$fYr-$fMo-$fDy 00:00:00";
	$DateTo="$tYr-$tMo-$tDy 00:00:00";
	
	$cnx=odbc_pconnect('pmisdb','sa','') or die("ERROR 404:~Error Connect to Database");
	if(!$cnx){echo "-1|0|0|0|0|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned) $sql";odbc_close($cnx);exit();}
	
	switch ($st){
		case -1	:
					$sql="SELECT count(*) AS TotalEmployee FROM personal WHERE employment_status = '".$EmploymentStatus."' AND pers_id >= '".$StartID."' AND pers_id <= '".$EndID."' ORDER BY pers_id ASC";
					$cur=odbc_exec($cnx,$sql);
					if(!$cur){echo "-1|0|0|0|0|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned) $sql";odbc_close($cnx);exit();}
					$Personal=odbc_fetch_array($cur);
					$TotalEmployee=$Personal['TotalEmployee'];
					
					$sql="SELECT pers_id FROM personal WHERE employment_status = '".$EmploymentStatus."' AND pers_id >= '".$StartID."' AND pers_id <= '".$EndID."' ORDER BY pers_id ASC";
					$cur=odbc_exec($cnx,$sql);
					if(!$cur){echo "-1|0|0|0|0|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned) $sql";odbc_close($cnx);exit();}
					$Personal=odbc_fetch_array($cur);
					$curID=$nextID=$Personal['pers_id'];
					$DoneIDs=0;
					
					$sql="SELECT count(*) AS RecordsOnThisID FROM inout WHERE date >= '".$DateFrom."' AND date <= '".$DateTo."' AND pers_id = '".$curID."'";
					$cur=odbc_exec($cnx,$sql);
					if(!$cur){echo "-1|0|0|0|0|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned) $sql";odbc_close($cnx);exit();}
					$Personal=odbc_fetch_array($cur);
					$RecordsOnThisID=$Personal['RecordsOnThisID'];
					
					$curDate=$nextDate=$DateFrom;
					$DoneRecOnThisID=0;
					
					$MSG="\nMigrating DTR of all ACTIVE employees from Sybase database to MySQL.\nDate from ".substr($DateFrom,0,10)." to ".substr($DateTo,0,10).".\n\nProcessing $curID . . . ";
					$respTxt="0|$curID|$nextID|$DoneIDs|$TotalEmployee|$curDate|$nextDate|$DoneRecOnThisID|$RecordsOnThisID|$MSG";
					break;
					
		case 0	:
					$curDate=$nextDate;
					$curID=$nextID;
					
					if($RecordsOnThisID==0){$curDate=$DateTo;}
					else{
						$current_date=explode(" ",$curDate);
						$ThisDate=explode("-",$current_date[0]);
						$DTRID="DTR".$ThisDate[0].$ThisDate[1].$ThisDate[2].$curID;
						
						/* Migrate DTR of current ID at current given Date */
						$sql="SELECT date, timein_am, timeout_am, timein_pm, timeout_pm, timein_ot, timeout_ot FROM inout WHERE date = '".$curDate."' AND pers_id = '".$curID."'";
						$cur=odbc_exec($cnx, $sql);
						if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned) $sql";odbc_close($cnx);exit();}
						$DTR=odbc_fetch_array($cur);
						
						$DTRIN01=(($DTR['timein_am']=="2000-01-01 00:00:00.000")||(strlen($DTR['timein_am'])==0))?"1970-01-01 00:00:01":$DTR['timein_am'];
						$DTROUT01=(($DTR['timeout_am']=="2000-01-01 00:00:00.000")||(strlen($DTR['timeout_am'])==0))?"1970-01-01 00:00:01":$DTR['timeout_am'];
						$DTRIN02=(($DTR['timein_pm']=="2000-01-01 00:00:00.000")||(strlen($DTR['timein_pm'])==0))?"1970-01-01 00:00:01":$DTR['timein_pm'];
						$DTROUT02=(($DTR['timeout_pm']=="2000-01-01 00:00:00.000")||(strlen($DTR['timeout_pm'])==0))?"1970-01-01 00:00:01":$DTR['timeout_pm'];
						$DTRIN03=(($DTR['timein_ot']=="2000-01-01 00:00:00.000")||(strlen($DTR['timein_ot'])==0))?"1970-01-01 00:00:01":$DTR['timein_ot'];
						$DTROUT03=(($DTR['timeout_ot']=="2000-01-01 00:00:00.000")||(strlen($DTR['timeout_ot'])==0))?"1970-01-01 00:00:01":$DTR['timeout_ot'];
						$DTRIN04="1970-01-01 00:00:01";
						$DTROUT04="1970-01-01 00:00:01";
						$DTRLates="";
						$DTROverTime="";
						$DTRHrsWeek="";
						$DTRVerCode="";
						$DTRRemarks="";
						
						$MySQLi=new MySQLClass();
						$sql ="INSERT INTO `tblempdtr`(`DTRID`, `EmpID`, `DayStatusID`, `DTRIN01`, `DTROUT01`, `DTRIN02`, `DTROUT02`, `DTRIN03`, `DTROUT03`, `DTRIN04`, `DTROUT04`, `DTRLates`, `DTROverTime`, `DTRHrsWeek`, `DTRVerCode`, `DTRRemarks`, `RECORD_TIME`) VALUES('".$DTRID."', '".$curID."', '', '".$DTRIN01."', '".$DTROUT01."', '".$DTRIN02."', '".$DTROUT02."', '".$DTRIN03."', '".$DTROUT03."', '".$DTRIN04."', '".$DTROUT04."', '".$DTRLates."', '".$DTROverTime."', '".$DTRHrsWeek."', '".$DTRVerCode."', '".$DTRRemarks."', NOW());";
						$MySQLi->sqlQuery($sql,false);
						
						$DoneRecOnThisID+=1;
					}

					if($curDate==$DateTo){
						$DoneIDs+=1;
						/* Check ID */
						if($DoneIDs>=$TotalEmployee){
							$MSG="DONE\n\nMigration Complete.";
							$respTxt="1|$curID|$nextID|$DoneIDs|$TotalEmployee|$curDate|$nextDate|$DoneRecOnThisID|$RecordsOnThisID|$MSG";
						}
						else{
							/* Get Next ID */
							$sql="SELECT pers_id FROM personal WHERE employment_status = '".$EmploymentStatus."' AND pers_id > '".$curID."' AND pers_id <= '".$EndID."'  ORDER BY pers_id ASC"; 
							$cur=odbc_exec($cnx,$sql);
							if(!$cur){echo "-1|0|0|0|0|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned) $sql";odbc_close($cnx);exit();}
							if(odbc_fetch_row($cur)){$nextID=odbc_result($cur,1);}
							else{$nextID="";}
							
							if($nextID!=""){
								$nextDate=$DateFrom;$DoneRecOnThisID=0;
								$sql="SELECT count(*) AS RecordsOnThisID FROM inout WHERE date >= '".$DateFrom."' AND date <= '".$DateTo."' AND pers_id = '".$nextID."'";
								$cur=odbc_exec($cnx,$sql);
								if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned) $sql";odbc_close($cnx);exit();}
								$Personal=odbc_fetch_array($cur);
								$RecordsOnThisID=$Personal['RecordsOnThisID'];
								$respTxt="0|$curID|$nextID|$DoneIDs|$TotalEmployee|$curDate|$nextDate|$DoneRecOnThisID|$RecordsOnThisID|DONE\nProcessing $nextID . . . ";
							}
							else{
								$MSG="\nThere was an error getting the next ID.";
								$respTxt="1|$curID|$nextID|$DoneIDs|$TotalEmployee|$curDate|$nextDate|$DoneRecOnThisID|$RecordsOnThisID|$MSG";
							}
						}
					}
					else{
						/* Get Next Date */
						$ThisDate[0]=intval($ThisDate[0]);
						$ThisDate[1]=intval($ThisDate[1]);
						$ThisDate[2]=intval($ThisDate[2]);
						
						$ThisDate[2]+=1;
						if($ThisDate[2]>cal_days_in_month(CAL_GREGORIAN,$ThisDate[1],$ThisDate[0])){
							$ThisDate[2]=1;$ThisDate[1]+=1;
							if($ThisDate[1]>12){
								$ThisDate[1]=1;$ThisDate[0]+=1;
							}
						}
						$nextDate=$ThisDate[0]."-".(($ThisDate[1]<10)?"0".$ThisDate[1]:$ThisDate[1])."-".(($ThisDate[2]<10)?"0".$ThisDate[2]:$ThisDate[2])." 00:00:00";
						$MSG="";$nextID=$curID;
						$respTxt="0|$curID|$nextID|$DoneIDs|$TotalEmployee|$curDate|$nextDate|$DoneRecOnThisID|$RecordsOnThisID|$MSG";
					}
					
					break;
					
		default	:	echo "-1|0|0|0|0|0|0|0|0|ERROR 49:~!!!";exit();
	}
	
  odbc_close($cnx);
	echo $respTxt;


?>