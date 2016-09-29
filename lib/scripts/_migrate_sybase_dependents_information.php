<?php
	ob_start();
	session_start();
	date_default_timezone_set('Asia/Taipei');
		
	require_once $_SESSION['path'].'/echo-txt.php';		
	
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
	$EndID=isset($_POST['lid'])?trim(strip_tags($_POST['lid'])):'99999';
	$st=isset($_POST['st'])?trim(strip_tags($_POST['st'])):-1;
	$curID=isset($_POST['cid'])?trim(strip_tags($_POST['cid'])):"00000";
	$nextID=isset($_POST['nid'])?trim(strip_tags($_POST['nid'])):"00000";
	$DoneDpnt=isset($_POST['did'])?trim(strip_tags($_POST['did'])):0;
	$TotalRecords=isset($_POST['tid'])?trim(strip_tags($_POST['tid'])):0;
	
	$cnx=odbc_pconnect('pmisdb','sa','') or die("Error Connect to Database");
	if(!$cnx){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
	
	switch ($st){
		case -1	:
					$cur=odbc_exec($cnx,"SELECT count(*) AS TotalRecords FROM dependent WHERE pers_id >= '".$StartID."' AND pers_id <= '".$EndID."' ORDER BY pers_id ASC");
					if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
					$Dependent=odbc_fetch_array($cur);
					$TotalRecords=$Dependent['TotalRecords'];
					// ALL employment_status = '".$EmploymentStatus."' AND 
					$sql="SELECT pers_id FROM personal WHERE pers_id >= '".$StartID."' AND pers_id <= '".$EndID."' ORDER BY pers_id ASC";
					$cur=odbc_exec($cnx,$sql);
					if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned) $sql";odbc_close($cnx);exit();}
					$Personal=odbc_fetch_array($cur);
					$curID=$nextID=$Personal['pers_id'];
					$DoneDpnt=0;
					
					$MSG="\nMigrating of all DEPENDENTS Information from Sybase database to MySQL.\n\nProcessing $curID . . . ";
					$respTxt="0|$curID|$nextID|$DoneDpnt|$TotalRecords|$MSG";
					break;
					
		case 0	:
					$curID=$nextID;
					$MySQLi=new MySQLClass();
					$cur=odbc_exec($cnx,"SELECT count(*) AS IDsOnThisDependent FROM dependent WHERE pers_id = '".$curID."'");
					if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
					$Dependent=odbc_fetch_array($cur);
					$IDsOnThisDependent=$Dependent['IDsOnThisDependent'];
					$DoneOnID=0;
					if($IDsOnThisDependent>0){
						$MySQLi->sqlQuery("DELETE FROM `tblempdependents` WHERE `EmpID`='".$curID."'",false);
						/* Migrate Dependent Information */
						$cur=odbc_exec($cnx,"SELECT * FROM dependent WHERE pers_id = '".$curID."'");
						if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
						
						while($DpntInfo=odbc_fetch_array($cur)){
							$EmpID=(strlen($curID)==4)?"0".$curID:$curID;
							/* Get New DpntID */
							$NewDpntID="DP".$EmpID."001";
							$count=1;
							while($records=$MySQLi->GetArray("SELECT `DpntID` FROM `tblempdependents` WHERE `DpntID`='$NewDpntID';")) {
								$count+=1;
								$ccc=($count>99)?$count:(($count>9)?"0".$count:"00".$count);
								$NewDpntID="DP".$EmpID.$ccc;
							} $DpntID=$NewDpntID;
							$DpntLName=$MySQLi->RealEscapeString(strtoupper(trim($DpntInfo['dependent'])));
							$DpntMName=$MySQLi->RealEscapeString(strtoupper(trim($DpntInfo['dependent'])));
							$DpntFName=$MySQLi->RealEscapeString(strtoupper(trim($DpntInfo['dependent'])));
							$DpntExtName="";
							$DpntBirthDay="";
							$DpntBirthMonth="";
							$DpntBirthYear="";
							if(strpbrk($DpntInfo['birthdate'],' ')){
								$BDate=explode(" ",$DpntInfo['birthdate']);
								if(strpbrk($BDate[0],'-')){
									$Birthdate=explode("-",$BDate[0]);
									$DpntBirthDay=$Birthdate[2];
									$DpntBirthMonth=$Birthdate[1];
									$DpntBirthYear=$Birthdate[0];
								}
							}
							$DpntSex="";
							switch (strtoupper(trim($DpntInfo['relationship']))){
								case '1':$RelID="R001";break;
								case '2':$RelID="R002";break;
								case '3':$RelID="R003";break;
								case '4':$RelID="R004";break;
								case '5':$RelID="R005";break;
								case '6':$RelID="R006";break;
								case '7':$RelID="R007";break;
								case '8':$RelID="R008";break;
								case '9':$RelID="R009";break;
								case 'A':$RelID="R010";break;
								case 'B':$RelID="R011";break;
								case 'C':$RelID="R012";break;
								case 'D':$RelID="R013";break;
								case 'Z':$RelID="R000";break;
								default	:$RelID="R000";break;
							}
							$DpntRemarks=$MySQLi->RealEscapeString(strtoupper(trim($DpntInfo['remarks'])));
							$sql="INSERT INTO `tblempdependents`(`DpntID`, `EmpID`, `DpntLName`, `DpntMName`, `DpntFName`, `DpntExtName`, `DpntBirthDay`, `DpntBirthMonth`, `DpntBirthYear`, `DpntSex`, `RelID`, `DpntRemarks`, `RECORD_TIME`) VALUES('".$DpntID."', '".$EmpID."', '".$DpntLName."', '".$DpntMName."', '".$DpntFName."', '".$DpntExtName."', '".$DpntBirthDay."', '".$DpntBirthMonth."', '".$DpntBirthYear."', '".$DpntSex."', '".$RelID."', '".$DpntRemarks."', NOW());";
							if($MySQLi->sqlQuery($sql,false)){$DoneOnID+=1;$DoneDpnt+=1;}
						}
					}
					
					/* Check ID */
					if($DoneDpnt>=$TotalRecords){
						$MSG="DONE\n\nMigration Complete.";
						$respTxt="1|$curID|$nextID|$DoneDpnt|$TotalRecords|$MSG";
					}
					else{
						/* Get Next ID */ 
						$cur=odbc_exec($cnx,"SELECT pers_id FROM personal WHERE pers_id > '".$curID."' AND pers_id >= '".$StartID."' AND pers_id <= '".$EndID."' ORDER BY pers_id ASC");
						if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
						if(odbc_fetch_row($cur)){$nextID=odbc_result($cur,1);}
						else{$nextID="";}
						
						if($nextID!=""){
							$respTxt="0|$curID|$nextID|$DoneDpnt|$TotalRecords|DONE\nProcessing $nextID . . . ";
						}
						else{
							$MSG="DONE\n\nThere was an error getting the next ID.";
							$respTxt="1|$curID|$nextID|$DoneDpnt|$TotalRecords|$MSG";
						}
					}
					
					break;
					
		default	:	echo "-1|0|0|0|0|ERROR 49:~!!!";exit();
	}
	
  odbc_close($cnx);
	echo $respTxt;


?>