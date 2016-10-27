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
	
	$EmploymentStatus=isset($_POST['es'])?trim(strip_tags($_POST['es'])):"A";
	$StartID=isset($_POST['iid'])?trim(strip_tags($_POST['iid'])):'0';
	$EndID=isset($_POST['lid'])?trim(strip_tags($_POST['lid'])):'ZZZZZ';
	$st=isset($_POST['st'])?trim(strip_tags($_POST['st'])):-1;
	$curID=isset($_POST['cid'])?trim(strip_tags($_POST['cid'])):"00000";
	$nextID=isset($_POST['nid'])?trim(strip_tags($_POST['nid'])):"00000";
	$DoneElig=isset($_POST['did'])?trim(strip_tags($_POST['did'])):0;
	$TotalRecords=isset($_POST['tid'])?trim(strip_tags($_POST['tid'])):0;
	
	$cnx=odbc_pconnect('pmisdb','sa','') or die("Error Connect to Database");
	if(!$cnx){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
	
	switch ($st){
		case -1	:
					$cur=odbc_exec($cnx,"SELECT count(*) AS TotalRecords FROM employee_eligibility WHERE pers_id >= '".$StartID."' AND pers_id <= '".$EndID."' ORDER BY pers_id ASC");
					if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
					$Eligibility=odbc_fetch_array($cur);
					$TotalRecords=$Eligibility['TotalRecords'];
					
					//$sql="SELECT pers_id FROM personal WHERE employment_status = '".$EmploymentStatus."' AND pers_id >= '".$StartID."' AND pers_id <= '".$EndID."' ORDER BY pers_id ASC";
					//$cur=odbc_exec($cnx,$sql);
					//if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned) $sql";odbc_close($cnx);exit();}
					//$Personal=odbc_fetch_array($cur);
					
					$MySQLi=new MySQLClass();
					$curID=$nextID=$MySQLi->GetArray("SELECT `EmpID` FROM `tblemppersonalinfo` ORDER BY `EmpID` ASC LIMIT 1,1;")['EmpID'];//$Personal['pers_id'];
					$DoneElig=0;
					
					$MSG="\nMigrating of all ELEGIBILITY Information from Sybase database to MySQL.\n\nProcessing $curID . . . ";
					$respTxt="0|$curID|$nextID|$DoneElig|$TotalRecords|$MSG";
					break;
					
		case 0	:
					$curID=$nextID;
					$MySQLi=new MySQLClass();
					
					$cur=odbc_exec($cnx,"SELECT count(*) AS IDsOnThisEligibility FROM employee_eligibility WHERE pers_id = '".$curID."'");
					if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
					$Eligibility=odbc_fetch_array($cur);
					$IDsOnThisEligibility=$Eligibility['IDsOnThisEligibility'];
					$DoneOnID=0;
					if($IDsOnThisEligibility>0){
						$MySQLi->sqlQuery("DELETE FROM `tblempcse` WHERE `EmpID`='".$curID."'",false);
						/* Migrate Eligibility Information */
						$cur=odbc_exec($cnx,"SELECT * FROM employee_eligibility WHERE pers_id = '".$curID."'");
						if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
						
						while($CSEInfo=odbc_fetch_array($cur)){
							$EmpID=(strlen($curID)==4)?"0".$curID:$curID;
							/* Get New CSEID */
							$NewCSEID="CS".$EmpID."01";
							$count=1;
							while($records=$MySQLi->GetArray("SELECT `CSEID` FROM `tblempcse` WHERE `CSEID`='$NewCSEID';")) {
								$count+=1;
								$ccc=($count>9)?$count:"0".$count;
								$NewCSEID="CS".$EmpID.$ccc;
							} $CSEID=$NewCSEID;
							
							$eli=odbc_exec($cnx,"SELECT * FROM eligibility WHERE eligibility_code = ".$CSEInfo['eligibility_code']." ");
							if(!$eli){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
							$EligInfo=odbc_fetch_array($eli);

							$CSEDesc=$MySQLi->RealEscapeString(strtoupper(trim($EligInfo['eligibility'])));
							$CSERating=($CSEInfo['grade']<1)?$CSEInfo['grade']*100:$CSEInfo['grade'];
							$CSEExamDay="";
							$CSEExamMonth="";
							$CSEExamYear="";
							if(strpbrk($CSEInfo['date_exam'],' ')){
								$EDate=explode(" ",$CSEInfo['date_exam']);
								if(strpbrk($EDate[0],'-')){
									$Examdate=explode("-",$EDate[0]);
									$CSEExamDay=$Examdate[2];
									$CSEExamMonth=$Examdate[1];
									$CSEExamYear=$Examdate[0];
								}
							}
							$CSEExamPlace=$MySQLi->RealEscapeString(strtoupper(trim($CSEInfo['place'])));
							$CSELicNum="";
							$CSELicReleaseDay="";
							$CSELicReleaseMonth="";
							$CSELicReleaseYear="";
							$CSEHighest=($MySQLi->RealEscapeString(strtoupper(trim($CSEInfo['highest_elig'])))=='Y')?'1':'0';
							
							$sql='INSERT INTO `tblempcse`(`CSEID`, `EmpID`, `CSEDesc`, `CSERating`, `CSEExamDay`, `CSEExamMonth`, `CSEExamYear`, `CSEExamPlace`, `CSELicNum`, `CSELicReleaseDay`, `CSELicReleaseMonth`, `CSELicReleaseYear`, `CSEHighest`, `RECORD_TIME`) VALUES("'.$CSEID.'","'.$EmpID.'","'.$CSEDesc.'","'.$CSERating.'","'.$CSEExamDay.'","'.$CSEExamMonth.'","'.$CSEExamYear.'","'.$CSEExamPlace.'","'.$CSELicNum.'","'.$CSELicReleaseDay.'","'.$CSELicReleaseMonth.'","'.$CSELicReleaseYear.'","'.$CSEHighest.'", NOW());';
							/*
							**	Check if EmpID has entry in tblemppersonalinfo	
							*/
							$row=$MySQLi->NumberOfRows("SELECT * FROM tblemppersonalinfo WHERE EmpID = '$EmpID'");							
							if ($row>0) {
								if($MySQLi->sqlQuery($sql,false)){$DoneOnID+=1;$DoneElig+=1;}
							} else {
								$DoneOnID+=1;$DoneElig+=1;
							}

						}
					}

					/* Check ID */
					if($DoneElig>=$TotalRecords){
						$MSG="$DoneOnID DONE\n\nMigration Complete.";
						$respTxt="1|$curID|$nextID|$DoneElig|$TotalRecords|$MSG";
					}
					else{
						/* Get Next ID */ 
						$MySQLi=new MySQLClass();
						$nID=$MySQLi->GetArray("SELECT `EmpID` FROM `tblemppersonalinfo` WHERE EmpID > '".$curID."' AND EmpID >= '".$StartID."' AND EmpID <= '".$EndID."' ORDER BY `EmpID` ASC LIMIT 1,1;")['EmpID'];
						$nextID=$nID;
						//$cur=odbc_exec($cnx,"SELECT pers_id FROM personal WHERE pers_id > '".$curID."' AND pers_id >= '".$StartID."' AND pers_id <= '".$EndID."' ORDER BY pers_id ASC");
						//if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
						//if(odbc_fetch_row($cur)){$nextID=odbc_result($cur,1);}
						//else{$nextID="";}
						
						if($nextID!=""){
							$MSG="$DoneOnID DONE\nProcessing $nextID . . . ";
							$respTxt="0|$curID|$nextID|$DoneElig|$TotalRecords|$MSG";
						}
						else{
							$MSG="DONE\n\nThere was an error getting the next ID.";
							$respTxt="1|$curID|$nextID|$DoneElig|$TotalRecords|$MSG";
						}
					}
					
					break;
					
		default	:	echo "-1|0|0|0|0|ERROR 49:~!!!";exit();
	}
	
  odbc_close($cnx);
	echo $respTxt;


?>