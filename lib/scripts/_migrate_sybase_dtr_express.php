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
	
	$StartID=isset($_POST['iid'])?trim(strip_tags($_POST['iid'])):'0';
	$EndID=isset($_POST['lid'])?trim(strip_tags($_POST['lid'])):'99999';
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
	
	/* Fixed Month and Day value in 2 digits format */
	$fMo=($fMo<10)?"0".$fMo:$fMo;$tMo=($tMo<10)?"0".$tMo:$tMo;
	$fDy=($fDy<10)?"0".$fDy:$fDy;$tDy=($tDy<10)?"0".$tDy:$tDy;
	/* Make date format */
	$DateFrom="$fYr-$fMo-$fDy 00:00:00";
	$DateTo="$tYr-$tMo-$tDy 00:00:00";
	
	$cnx=odbc_pconnect('pmis_sybase','sa','') or die("Error Connect to Database");
	if(!$cnx){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned) $sql";odbc_close($cnx);exit();}
	
	switch ($st){
		case -1	:
					$sql="SELECT count(*) AS TotalEmployee FROM personal WHERE pers_id >= '".$StartID."' AND pers_id <= '".$EndID."' ORDER BY pers_id ASC";
					$cur=odbc_exec($cnx,$sql);
					if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned) $sql";odbc_close($cnx);exit();}
					$Personal=odbc_fetch_array($cur);
					$TotalEmployee=$Personal['TotalEmployee'];
					
					$sql="SELECT pers_id FROM personal WHERE pers_id >= '".$StartID."' AND pers_id <= '".$EndID."' ORDER BY pers_id ASC";
					$cur=odbc_exec($cnx,$sql);
					if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned) $sql";odbc_close($cnx);exit();}
					$Personal=odbc_fetch_array($cur);
					$curID=$nextID=$Personal['pers_id'];
					$DoneIDs=0;
					
					// LogTrail("Migrating DTR of all employees from Sybase database to MySQL. Date from 2015-11-09 to 2015-11-12.","ONGOING","");
					$MSG="\nMigrating DTR of all ACTIVE employees from Sybase database to MySQL.\nDate from ".substr($DateFrom,0,10)." to ".substr($DateTo,0,10).".\n\nProcessing $curID . . . ";
					$respTxt="0|$curID|$nextID|$DoneIDs|$TotalEmployee|$MSG";
					break;
					
		case 0	:
					$curID=$nextID;
					$sql="SELECT count(*) AS RecordsOnThisID FROM inout WHERE date >= '".$DateFrom."' AND date <= '".$DateTo."' AND pers_id = '".$curID."'";
					$cur=odbc_exec($cnx,$sql);
					if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned) $sql";odbc_close($cnx);exit();}
					$Personal=odbc_fetch_array($cur);
					$RecordsOnThisID=$Personal['RecordsOnThisID'];
					
					if($RecordsOnThisID>0){
						/* Migrate DTR of current ID at current given Date */
						$sql="SELECT date, timein_am, timeout_am, timein_pm, timeout_pm, timein_ot, timeout_ot FROM inout WHERE date >= '".$DateFrom."' AND date <= '".$DateTo."' AND pers_id = '".$curID."'";
						$cur=odbc_exec($cnx, $sql);
						if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned) $sql";odbc_close($cnx);exit();}
						
						$LAMs=$LPMs=$UAMs=$UPMs=0;
						while($DTR=odbc_fetch_array($cur)){
							if(isOK($DTR['timein_am'])||isOK($DTR['timeout_am'])||isOK($DTR['timein_pm'])||isOK($DTR['timeout_pm'])||isOK($DTR['timein_ot'])||isOK($DTR['timeout_ot'])){
								$current_date=explode(" ",$DTR['date']);
								$ThisDate=explode("-",$current_date[0]);
								$DTRID="DTR".$ThisDate[0].$ThisDate[1].$ThisDate[2].$curID;
								
								if(($DTR['timein_am']=="2000-01-01 00:00:00.000")||(strlen($DTR['timein_am'])==0)){$DTRIN01="1970-01-01 00:00:01";}
								else{
									$DTRIN01=FixDateLog($current_date[0],$DTR['timein_am']);
									$LogTimeAM1=explode(" ",$DTR['timein_am']);
									$TimeAM1=explode(":",$LogTimeAM1[1]);
									if(intval($TimeAM1[0])<8){$LAMs=0;}
									else{$LAMh=intval($TimeAM1[0])-8;$LAMm=intval($TimeAM1[1])+($LAMh*60);$LAMs=intval($TimeAM1[2])+($LAMm*60);}
								}
								
								if(($DTR['timeout_am']=="2000-01-01 00:00:00.000")||(strlen($DTR['timeout_am'])==0)){$DTROUT01="1970-01-01 00:00:01";}
								else{
									$DTROUT01=FixDateLog($current_date[0],$DTR['timeout_am']);
									$LogTimeAM2=explode(" ",$DTR['timeout_am']);
									$TimeAM2=explode(":",$LogTimeAM2[1]);
									if(intval($TimeAM2[0])>=12){$UAMs=0;}
									else{$UAMh=12-intval($TimeAM2[0]);$UAMm=($UAMh*60)-intval($TimeAM2[1]);$UAMs=($UAMm*60)-intval($TimeAM2[2]);}
								}
								
								if(($DTR['timein_pm']=="2000-01-01 00:00:00.000")||(strlen($DTR['timein_pm'])==0)){$DTRIN02="1970-01-01 00:00:01";}
								else{
									$DTRIN02=FixDateLog($current_date[0],$DTR['timein_pm']);
									$LogTimePM1=explode(" ",$DTR['timein_pm']);
									$TimePM1=explode(":",$LogTimePM1[1]);
									if(intval($TimePM1[0])<13){$LPMs=0;}
									else{$LPMh=intval($TimePM1[0])-13;$LPMm=intval($TimePM1[1])+($LPMh*60);$LPMs=intval($TimePM1[2])+($LPMm*60);}
								}
								
								if(($DTR['timeout_pm']=="2000-01-01 00:00:00.000")||(strlen($DTR['timeout_pm'])==0)){$DTROUT02="1970-01-01 00:00:01";}
								else{
									$DTROUT02=FixDateLog($current_date[0],$DTR['timeout_pm']);
									$LogTimePM2=explode(" ",$DTR['timeout_pm']);
									$TimePM2=explode(":",$LogTimePM2[1]);
									if(intval($TimePM2[0])>=17){$UPMs=0;}
									else{$UPMh=17-intval($TimePM2[0]);$UPMm=($UPMh*60)-intval($TimePM2[1]);$UPMs=($UPMm*60)-intval($TimePM2[2]);}	
								}
								
								if(($DTR['timein_ot']=="2000-01-01 00:00:00.000")||(strlen($DTR['timein_ot'])==0)){$DTRIN03="1970-01-01 00:00:01";}
								else{
									$DTRIN03=FixDateLog($current_date[0],$DTR['timein_ot']);
								}
								
								if(($DTR['timeout_ot']=="2000-01-01 00:00:00.000")||(strlen($DTR['timeout_ot'])==0)){$DTROUT03="1970-01-01 00:00:01";$Os=0;}
								else{
									$DTROUT03=FixDateLog($current_date[0],$DTR['timeout_ot']);
									$LogTimeOT2=explode(" ",$DTR['timeout_ot']);
									$TimeOT2=explode(":",$LogTimeOT2[1]);
									if(intval($TimeOT2[0])-17<0){$Os=0;}
									else{
										$Oh=intval($TimeOT2[0])-17;
										$Om=$TimeOT2[1]+(60*$Oh);
										$Os=$TimeOT2[2]+($Om*60);
									}
									$Date=explode("-",$LogTimeOT2[0]);
									$Day=date("D", mktime(0, 0, 0, $Date[1], $Date[2], $Date[0]));
									if($Day=='Sat'||$Day=='Sun'){$Os=$Os+(28800-($LAMs+$LPMs+$UAMs+$UPMs));}
								}
								
								$DTRIN04="1970-01-01 00:00:01";
								$DTROUT04="1970-01-01 00:00:01";
								/* Lates and Undertime */
								$Lm=floor(($LAMs+$LPMs+$UAMs+$UPMs)/60);
								$Ls=(($LAMs+$LPMs+$UAMs+$UPMs)-($Lm*60));
								$DTRLates=($Lm<9?"0".$Lm:$Lm)."m ".($Ls<9?"0".$Ls:$Ls)."s";
								/* Hours Week */
								$SWk=28800-($LAMs+$LPMs+$UAMs+$UPMs);
								$HWk=floor($SWk/3600);
								$DTRHrsWeek=$HWk.".".((($SWk%3600)/60)>9?number_format((($SWk%3600)/60),0):"0".number_format((($SWk%3600)/60),0));
								/* Overtime */
								if($Os>0){
									$HOt=floor($Os/3600);
									$DTROverTime=($HOt>9?$HOt:"0".$HOt).".".((($Os%3600)/60)>9?number_format((($Os%3600)/60),0):"0".number_format((($Os%3600)/60),0));
								}
								else{$DTROverTime="";}
								
								
								$DTRVerCode="";
								$DTRRemarks="";
								
								$MySQLi=new MySQLClass();
								$sql ="DELETE FROM `tblempdtr` WHERE `DTRID` = '".$DTRID."';";
								$MySQLi->sqlQuery($sql,false);
								
								$sql ="INSERT INTO `tblempdtr`(`DTRID`, `EmpID`, `DayStatusID`, `DTRIN01`, `DTROUT01`, `DTRIN02`, `DTROUT02`, `DTRIN03`, `DTROUT03`, `DTRIN04`, `DTROUT04`, `DTRLates`, `DTROverTime`, `DTRHrsWeek`, `DTRVerCode`, `DTRRemarks`, `RECORD_TIME`) VALUES('".$DTRID."', '".$curID."', '', '".$DTRIN01."', '".$DTROUT01."', '".$DTRIN02."', '".$DTROUT02."', '".$DTRIN03."', '".$DTROUT03."', '".$DTRIN04."', '".$DTROUT04."', '".$DTRLates."', '".$DTROverTime."', '".$DTRHrsWeek."', '".$DTRVerCode."', '".$DTRRemarks."', NOW());";
								$MySQLi->sqlQuery($sql,false);
							}
						}
					}
					
					$DoneIDs+=1;
					/* Check ID */
					if($DoneIDs>=$TotalEmployee){
						$MSG="DONE\n\nMigration Complete.";
						$respTxt="1|$curID|$nextID|$DoneIDs|$TotalEmployee|$MSG";
					}
					else{
						/* Get Next ID */
						$cur=odbc_exec($cnx,"SELECT pers_id FROM personal WHERE pers_id > '".$curID."' AND pers_id >= '".$StartID."' AND pers_id <= '".$EndID."' ORDER BY pers_id ASC");
						if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned) $sql";odbc_close($cnx);exit();}
						if(odbc_fetch_row($cur)){$nextID=odbc_result($cur,1);}
						else{$nextID="";}
						
						if($nextID!=""){
							$respTxt="0|$curID|$nextID|$DoneIDs|$TotalEmployee|DONE\nProcessing $nextID . . . ";
						}
						else{
							$MSG="DONE\n\nThere was an error getting the next ID.";
							$respTxt="1|$curID|$nextID|$DoneIDs|$TotalEmployee|$MSG";
						}
					}
					
					break;
					
		default	:	echo "-1|0|0|0|0|ERROR 49:~!!!";exit();
	}
	
  odbc_close($cnx);
	echo $respTxt;


?>