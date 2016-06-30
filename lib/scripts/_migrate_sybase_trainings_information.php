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
	
	$EmploymentStatus=isset($_POST['es'])?trim(strip_tags($_POST['es'])):"I";
	$StartID=isset($_POST['iid'])?trim(strip_tags($_POST['iid'])):'0';
	$EndID=isset($_POST['lid'])?trim(strip_tags($_POST['lid'])):'99999';
	$st=isset($_POST['st'])?trim(strip_tags($_POST['st'])):-1;
	$curID=isset($_POST['cid'])?trim(strip_tags($_POST['cid'])):"00000";
	$nextID=isset($_POST['nid'])?trim(strip_tags($_POST['nid'])):"00000";
	$DoneTrain=isset($_POST['did'])?trim(strip_tags($_POST['did'])):0;
	$TotalRecords=isset($_POST['tid'])?trim(strip_tags($_POST['tid'])):0;
	
	$cnx=odbc_pconnect('pmisdb','sa','') or die("Error Connect to Database");
	if(!$cnx){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
	
	switch ($st){
		case -1	:
					$cur=odbc_exec($cnx,"SELECT count(*) AS TotalRecords FROM employee_trainings_seminars WHERE pers_id >= '".$StartID."' AND pers_id <= '".$EndID."' ORDER BY pers_id ASC");
					if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
					$Training=odbc_fetch_array($cur);
					$TotalRecords=$Training['TotalRecords'];
					
					$sql="SELECT pers_id FROM personal WHERE pers_id >= '".$StartID."' AND pers_id <= '".$EndID."' ORDER BY pers_id ASC";
					$cur=odbc_exec($cnx,$sql);
					if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned) $sql";odbc_close($cnx);exit();}
					$Personal=odbc_fetch_array($cur);
					$curID=$nextID=$Personal['pers_id'];
					$DoneTrain=0;
					
					$MSG="\nMigrating of all TRAININGS and SEMINARS from Sybase database to MySQL.\n\nProcessing $curID . . . ";
					$respTxt="0|$curID|$nextID|$DoneTrain|$TotalRecords|$MSG";
					break;
					
		case 0	:
					$curID=$nextID;
					$MySQLi=new MySQLClass();
					$cur=odbc_exec($cnx,"SELECT count(*) AS IDsOnThisTraining FROM employee_trainings_seminars WHERE pers_id = '".$curID."'");
					if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
					$Training=odbc_fetch_array($cur);
					$IDsOnThisTraining=$Training['IDsOnThisTraining'];
					$DoneOnID=0;
					if($IDsOnThisTraining>0){
						$MySQLi->sqlQuery("DELETE FROM `tblemptrainings` WHERE `EmpID`='".$curID."'",false);
						/* Migrate Training Information */
						$cur=odbc_exec($cnx,"SELECT * FROM employee_trainings_seminars WHERE pers_id = '".$curID."'");
						if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
						
						while($TrainInfo=odbc_fetch_array($cur)){
							$EmpID=(strlen($curID)==4)?"0".$curID:$curID;
							/* Get New TrainID */
							$NewTrainID="TR".$EmpID."01";
							$count=1;
							while($records=$MySQLi->GetArray("SELECT `TrainID` FROM `tblemptrainings` WHERE `TrainID`='$NewTrainID';")){
								$count+=1;
								$ccc=($count>9)?$count:"0".$count;
								$NewTrainID="TR".$EmpID.$ccc;
							} $TrainID=$NewTrainID;
							/* Get Trainingal Level Info */
							$ts=odbc_exec($cnx,"SELECT training FROM training WHERE training_code = ".$TrainInfo['training_code']." ");
							if(!$ts){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
							$Train=odbc_fetch_array($ts);
							$TrainDesc=$MySQLi->RealEscapeString(strtoupper($Train['training']));
							
							$TrainFromDay="";
							$TrainFromMonth="";
							$TrainFromYear="";
							if(strpbrk($TrainInfo['date_from'],' ')){
								$EDate=explode(" ",$TrainInfo['date_from']);
								if(strpbrk($EDate[0],'-')){
									$TrainDate=explode("-",$EDate[0]);
									$TrainFromDay=$TrainDate[2];
									$TrainFromMonth=$TrainDate[1];
									$TrainFromYear=$TrainDate[0];
								}
							}
							$TrainToDay="";
							$TrainToMonth="";
							$TrainToYear="";
							if(strpbrk($TrainInfo['date_to'],' ')){
								$EDate=explode(" ",$TrainInfo['date_to']);
								if(strpbrk($EDate[0],'-')){
									$TrainDate=explode("-",$EDate[0]);
									$TrainToDay=$TrainDate[2];
									$TrainToMonth=$TrainDate[1];
									$TrainToYear=$TrainDate[0];
								}
							}
							$TrainHours="";
							$TrainSponsor=$MySQLi->RealEscapeString(strtoupper($Train['conducted_by']));
							
							$sql='INSERT INTO `tblemptrainings` (`TrainID`,`EmpID`,`TrainDesc`,`TrainFromDay`,`TrainFromMonth`,`TrainFromYear`,`TrainToDay`,`TrainToMonth`,`TrainToYear`,`TrainHours`,`TrainSponsor`,`RECORD_TIME`) VALUES ("'.$TrainID.'","'.$EmpID.'","'.$TrainDesc.'","'.$TrainFromDay.'","'.$TrainFromMonth.'","'.$TrainFromYear.'","'.$TrainToDay.'","'.$TrainToMonth.'","'.$TrainToYear.'","'.$TrainHours.'","'.$TrainSponsor.'",NOW());';
							if($MySQLi->sqlQuery($sql,false)){$DoneOnID+=1;$DoneTrain+=1;}
						}
						
					}
					
					/* Check ID */
					if($DoneTrain>=$TotalRecords){
						$MSG="$DoneOnID DONE\n\nMigration Complete.";
						$respTxt="1|$curID|$nextID|$DoneTrain|$TotalRecords|$MSG";
					}
					else{
						/* Get Next ID */ 
						$cur=odbc_exec($cnx,"SELECT pers_id FROM personal WHERE pers_id > '".$curID."' AND pers_id >= '".$StartID."' AND pers_id <= '".$EndID."' ORDER BY pers_id ASC");
						if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
						if(odbc_fetch_row($cur)){$nextID=odbc_result($cur,1);}
						else{$nextID="";}
						
						if($nextID!=""){
							$respTxt="0|$curID|$nextID|$DoneTrain|$TotalRecords|$DoneOnID DONE\nProcessing $nextID . . . ";
						}
						else{
							$MSG="DONE\n\nThere was an error getting the next ID.";
							$respTxt="1|$curID|$nextID|$DoneTrain|$TotalRecords|$MSG";
						}
					}
					
					break;
					
		default	:	echo "-1|0|0|0|0|ERROR 49:~!!!";exit();
	}
	
  odbc_close($cnx);
	echo $respTxt;


?>