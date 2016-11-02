<?php
	ob_start();
	session_start();
		
	
	
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
	$DoneIDs=isset($_POST['did'])?trim(strip_tags($_POST['did'])):0;
	$TotalRecords=isset($_POST['tid'])?trim(strip_tags($_POST['tid'])):0;
	
	$cnx=odbc_pconnect('pmisdb','sa','') or die("Error Connect to Database");
	if(!$cnx){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
	
	switch ($st){
		case -1	:
					$cur=odbc_exec($cnx,"SELECT count(*) AS TotalRecords FROM position WHERE pos_code >= '".$StartID."' AND pos_code <= '".$EndID."' ORDER BY pos_code ASC");
					if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
					$Position=odbc_fetch_array($cur);
					$TotalRecords=$Position['TotalRecords'];
					
					$cur=odbc_exec($cnx,"SELECT pos_code FROM position WHERE pos_code >= '".$StartID."' AND pos_code <= '".$EndID."' ORDER BY pos_code ASC");
					if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
					$Position=odbc_fetch_array($cur);
					$curID=$nextID=$Position['pos_code'];
					$DoneIDs=0;
					
					$MSG="\nMigrating of all USED POSITION Information from Sybase database to MySQL.\n\nProcessing $curID . . . ";
					$respTxt="0|$curID|$nextID|$DoneIDs|$TotalRecords|$MSG";
					break;
					
		case 0	:
					$curID=$nextID;
					$MySQLi=new MySQLClass();
					$cur=odbc_exec($cnx,"SELECT count(*) AS IDsOnThisPosition FROM personal WHERE pos_code = '".$curID."'");
					if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
					$Personal=odbc_fetch_array($cur);
					$IDsOnThisPosition=$Personal['IDsOnThisPosition'];
					
					if($IDsOnThisPosition>0){
						/* Migrate Position Information */
						$cur=odbc_exec($cnx,"SELECT * FROM position WHERE pos_code = '".$curID."'");
						if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
						
						while($PosInfo=odbc_fetch_array($cur)){
							//Get New PosID
							$NewPosID="PO001";
							$count=1;
							while($records=$MySQLi->GetArray("SELECT `PosID` FROM `tblpositions` WHERE `PosID`='$NewPosID';")) {
								$count+=1;
								$ccc=($count>99)?$count:(($count>9)?"0".$count:"00".$count);
								$NewPosID="PO".$ccc;
							} $PosID=$NewPosID;
							$PosCode=$MySQLi->RealEscapeString(strtoupper(trim($PosInfo['short_name'])));
							$PosDesc=$MySQLi->RealEscapeString(strtoupper(trim($PosInfo['pos_name'])));
							$PosSalGrade=$MySQLi->RealEscapeString(strtoupper(trim($PosInfo['salary_grade'])));
							
							//$MySQLi->sqlQuery("DELETE FROM `tblempservicerecords` WHERE `PosID` = '".$PosID."';",false);
							//$MySQLi->sqlQuery("DELETE FROM `tblpositions` WHERE `PosID` = '".$PosID."';",false);
							$MySQLi->sqlQuery("INSERT INTO `tblpositions`(`PosID`, `PosCode`, `PosDesc`, `PosSalGrade`, `RECORD_TIME`) VALUES('".$PosID."', '".$PosCode."', '".$PosDesc."', '".$PosSalGrade."', NOW());",false);
						}
						
						/* Update All Employee to this position information */ 
						$cur=odbc_exec($cnx,"SELECT pers_id, appt_status, dte_hired, sg_step FROM personal WHERE employment_status = 'A' AND pos_code = '".$curID."' ORDER BY pos_code ASC");
						if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
						while($PosInfo=odbc_fetch_array($cur)){
							$EmpID=(strlen($PosInfo['pers_id'])==4)?"0".$PosInfo['pers_id']:$PosInfo['pers_id'];
							$SRecID="SR".$EmpID."001";
							switch (trim($PosInfo['appt_status'])) {
								case "Permanent": $SRecFromDay="01";$SRecFromMonth="01";$SRecFromYear="2014";$SRecToDay="31";$SRecToMonth="12";$SRecToYear="2014";$ApptStID="AS005";$SalUnitID="SU04";break;
								case "Elected":$SRecFromDay="01";$SRecFromMonth="07";$SRecFromYear="2013";$SRecToDay="30";$SRecToMonth="06";$SRecToYear="2016";$ApptStID="AS011";$SalUnitID="SU04";break;
								case "Coterminous":$SRecFromDay="01";$SRecFromMonth="07";$SRecFromYear="2013";$SRecToDay="30";$SRecToMonth="06";$SRecToYear="2016";$ApptStID="AS010";$SalUnitID="SU04";break;
								case "Casual":$SRecFromDay="01";$SRecFromMonth="04";$SRecFromYear="2013";$SRecToDay="30";$SRecToMonth="06";$SRecToYear="2013";$ApptStID="AS004";$SalUnitID="SU02";break;
								case "Contractual":$SRecFromDay="01";$SRecFromMonth="10";$SRecFromYear="2014";$SRecToDay="31";$SRecToMonth="12";$SRecToYear="2014";$ApptStID="AS008";$SalUnitID="SU02";break;
								case "Job Order":$SRecFromDay="07";$SRecFromMonth="10";$SRecFromYear="2014";$SRecToDay="31";$SRecToMonth="12";$SRecToYear="2014";$ApptStID="AS003";$SalUnitID="SU02";break;
								default:
									$dte_hired=explode(" ",$PosInfo['dte_hired']);
									$SRecFrom=explode("-",$dte_hired[0]);
									$SRecFromDay=$SRecFrom[2];$SRecFromMonth=$SRecFrom[1];$SRecFromYear=$SRecFrom[0];$SRecToDay="30";$SRecToMonth="06";$SRecToYear="2016";
									$ApptStID=($PosInfo['appt_status']=="OJT")?"AS012":(($PosInfo['appt_status']=="Temporary")?"AS006":(($PosInfo['appt_status']=="Consultant")?"AS009":"AS007"));
									$SalUnitID="SU01";
									break;
							}
							
							$SRecIsGov="YES";
							$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";
							$MotherOfficeID="";
							$AssignedOfficeID="";
							$SRecOffice="";
							//$PosID=$PosID;
							if(strpbrk($PosInfo['sg_step'],'/')){$SalGradeStep=explode("/",$PosInfo['sg_step']);$SRecSalGradeStep=$SalGradeStep[1];}
							else{$SRecSalGradeStep="0";}
							$SRecJobDesc="";
							$SRecCurrentAppointment="0";
							$SRecLivNoPay="";
							$SRecRemarks="";
							
							//$MySQLi->sqlQuery("DELETE FROM `tblempservicerecords` WHERE `SRecID` = '".$SRecID."';",false);
							
							//`SRecID`, `EmpID`, `SRecFromDay`, `SRecFromMonth`, `SRecFromYear`, `SRecToDay`, `SRecToMonth`, `SRecToYear`, `SRecIsGov`, `SRecEmployer`, `MotherOfficeID`, `AssignedOfficeID`, `SRecOffice`, `PosID`, `SRecPosition`, `SRecSalGradeStep`, `SRecSalary`, `ApptStID`, `SRecJobDesc`, `SalUnitID`, `SRecCurrentAppointment`, `SRecLivNoPay`, `SRecRemarks`, `RECORD_TIME`
							/*
							**	Check if EmpID has entry in tblemppersonalinfo	
							*/
							$row=$MySQLi->NumberOfRows("SELECT * FROM tblemppersonalinfo WHERE EmpID = '$EmpID'");							
							if ($row>0) {
								$sql = "INSERT INTO `tblempservicerecords` ";
								$sql .= "(`SRecID`, `EmpID`, `SRecFromDay`, `SRecFromMonth`, `SRecFromYear`, `SRecToDay`, `SRecToMonth`, `SRecToYear`, `SRecIsGov`, `SRecEmployer`, `MotherOfficeID`, `AssignedOfficeID`, `SRecOffice`, `PosID`, `SRecPosition`, `SRecSalGradeStep`, `SRecSalary`, `ApptStID`, `SRecJobDesc`, `SalUnitID`, `SRecCurrentAppointment`, `SRecLivNoPay`, `SRecRemarks`, `RECORD_TIME`) VALUES";
								$sql .= "('".$SRecID."', '".$EmpID."', '".$SRecFromDay."', '".$SRecFromMonth."', '".$SRecFromYear."', '".$SRecToDay."', '".$SRecToMonth."', '".$SRecToYear."', '".$SRecIsGov."', '".$SRecEmployer."', '".$MotherOfficeID."', '".$AssignedOfficeID."', '".$SRecOffice."', '".$PosID."', '', '".$SRecSalGradeStep."', '0.00', '".$ApptStID."', '".$SRecJobDesc."', '".$SalUnitID."',  '".$SRecCurrentAppointment."', '".$SRecLivNoPay."', '".$SRecRemarks."', NOW())";
								$isExisting = $MySQLi->NumberOfRows("SELECT * FROM tblempservicerecords WHERE SRecID = '$SRecID'");
								if ($isExisting == 0) $MySQLi->sqlQuery($sql,false);
							}
						}
						
					}
					
					$DoneIDs+=1;
					/* Check ID */
					if($DoneIDs>=$TotalRecords){
						$MSG="DONE\n\nMigration Complete.";
						$respTxt="1|$curID|$nextID|$DoneIDs|$TotalRecords|$MSG";
					}
					else{
						/* Get Next ID */ 
						$cur=odbc_exec($cnx,"SELECT pos_code FROM position WHERE pos_code > '".$curID."' AND pos_code >= '".$StartID."' AND pos_code <= '".$EndID."' ORDER BY pos_code ASC");
						if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
						if(odbc_fetch_row($cur)){$nextID=odbc_result($cur,1);}
						else{$nextID="";}
						
						if($nextID!=""){
							$respTxt="0|$curID|$nextID|$DoneIDs|$TotalRecords|DONE\nProcessing $nextID . . . ";
						}
						else{
							$MSG="DONE\n\nThere was an error getting the next ID.";
							$respTxt="1|$curID|$nextID|$DoneIDs|$TotalRecords|$MSG";
						}
					}
					
					break;
					
		default	:	echo "-1|0|0|0|0|ERROR 49:~!!!";exit();
	}
	
  odbc_close($cnx);
	echo $respTxt;


?>