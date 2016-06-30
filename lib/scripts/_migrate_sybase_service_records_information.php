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

	
	
	function isStringPresent($string, array $search, $caseInsensitive=false){
    $exp = '/'.implode('|',array_map('preg_quote',$search)).($caseInsensitive?'/i':'/');
    return preg_match($exp, $string)?true:false;
	}
	
	$StartID=isset($_POST['iid'])?trim(strip_tags($_POST['iid'])):'0';
	$EndID=isset($_POST['lid'])?trim(strip_tags($_POST['lid'])):'99999';
	$DateHired=isset($_POST['dh'])?trim(strip_tags($_POST['dh'])):'2015-01-01 00:00:00';
	$st=isset($_POST['st'])?trim(strip_tags($_POST['st'])):-1;
	$curID=isset($_POST['cid'])?trim(strip_tags($_POST['cid'])):"00000";
	$nextID=isset($_POST['nid'])?trim(strip_tags($_POST['nid'])):"00000";
	$DoneSRec=isset($_POST['did'])?trim(strip_tags($_POST['did'])):0;
	$TotalRecords=isset($_POST['tid'])?trim(strip_tags($_POST['tid'])):0;
	
	$cnx=odbc_pconnect('pmis_sybase','sa','') or die("Error Connect to Database");
	if(!$cnx){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
	
	switch ($st){
		case -1	:
					$cur=odbc_exec($cnx,"SELECT count(*) AS TotalRecords FROM employee_service_record JOIN personal ON employee_service_record.pers_id = personal.pers_id WHERE personal.dte_hired >= '".$DateHired."' AND personal.pers_id >= '".$StartID."' AND personal.pers_id <= '".$EndID."' ORDER BY personal.pers_id ASC");
					if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
					$SRec=odbc_fetch_array($cur);
					$TotalRecords=$SRec['TotalRecords'];
					
					$sql="SELECT pers_id FROM personal WHERE dte_hired >= '".$DateHired."' AND pers_id >= '".$StartID."' AND pers_id <= '".$EndID."' ORDER BY pers_id ASC";
					$cur=odbc_exec($cnx,$sql);
					if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned) $sql";odbc_close($cnx);exit();}
					$Personal=odbc_fetch_array($cur);
					$curID=$nextID=$Personal['pers_id'];
					$DoneSRec=0;
					
					$MSG="\nMigrating of all employee SERVICE RECORDS from Sybase database to MySQL.\n\nProcessing $curID . . . ";
					$respTxt="0|$curID|$nextID|$DoneSRec|$TotalRecords|$MSG";
					break;
					
		case 0	:
					$curID=$nextID;
					$MySQLi=new MySQLClass();
					$cur=odbc_exec($cnx,"SELECT count(*) AS RecOnThisID FROM employee_service_record WHERE pers_id = '".$curID."'");
					if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
					$SRec=odbc_fetch_array($cur);
					$RecOnThisID=$SRec['RecOnThisID'];
					$DoneOnID=0;
					if($RecOnThisID>0){
						$EmpID=(strlen($curID)==4)?"0".$curID:$curID;
						$MySQLi->sqlQuery("DELETE FROM `tblempservicerecords` WHERE `EmpID`='".$EmpID."';",false);//AND `SRecID` <> 'SR".$EmpID."001'
						/* Migrate SRec Information */
						$cur=odbc_exec($cnx,"SELECT * FROM employee_service_record WHERE pers_id = '".$EmpID."'");
						if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
						
						while($SRecInfo=odbc_fetch_array($cur)){
							$CGrpID="CG00";
							/* Get New SRecID */
							$NewSRecID="SR".$EmpID."001";$count=1;while($records=$MySQLi->GetArray("SELECT `SRecID` FROM `tblempservicerecords` WHERE `SRecID`='$NewSRecID';")){$count+=1;$ccc=($count>99)?$count:(($count>9)?"0".$count:"00".$count);$NewSRecID="SR".$EmpID.$ccc;}$SRecID=$NewSRecID;        
							
							$SRecFromDay=""; $SRecFromMonth=""; $SRecFromYear="";
							if(strpbrk($SRecInfo['service_from'],' ')){
								$FDate=explode(" ",$SRecInfo['service_from']);
								if(strpbrk($FDate[0],'-')){ $SRecDate=explode("-",$FDate[0]); $SRecFromDay=$SRecDate[2]; $SRecFromMonth=$SRecDate[1]; $SRecFromYear=$SRecDate[0]; }
							}
							else{$SRecFromDay="01"; $SRecFromMonth="01"; $SRecFromYear="1900";}
							$SRecToDay=""; $SRecToMonth=""; $SRecToYear="";
							if(strpbrk($SRecInfo['service_to'],' ')){
								$TDate=explode(" ",$SRecInfo['service_to']);
								if(strpbrk($TDate[0],'-')){ $SRecDate=explode("-",$TDate[0]); $SRecToDay=$SRecDate[2]; $SRecToMonth=$SRecDate[1]; $SRecToYear=$SRecDate[0]; }
							}
							else{$SRecToDay="31"; $SRecToMonth="12"; $SRecToYear="2014";}
							$SRecCurrentAppointment=0;
							$SRecIsGov="YES";
							
							//$SRecInfo['office_assign']
							$SRecOffice=$MySQLi->RealEscapeString(strtoupper(trim($SRecInfo['office_assign'])));
							$SRecEmployer="";
							$MotherOfficeID="SO000";
							$AssignedOfficeID="SO000";
							$Opisina=$MySQLi->RealEscapeString(strtoupper(trim($SRecInfo['office_assign'])));
							if(isStringPresent($Opisina, array("OPG"))){$CGrpID="CG01";$MotherOfficeID="SO001";$AssignedOfficeID="SO001";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							if(isStringPresent($Opisina, array("SSD"))){$CGrpID="CG02";$MotherOfficeID="SO002";$AssignedOfficeID="SO002";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							if(isStringPresent($Opisina, array("MISD","MISO"))){$CGrpID="CG03";$MotherOfficeID="SO003";$AssignedOfficeID="SO003";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							if(isStringPresent($Opisina, array("JAIL"))){$CGrpID="CG04";$MotherOfficeID="SO004";$AssignedOfficeID="SO004";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							if(isStringPresent($Opisina, array("HRMD"))){$CGrpID="CG05";$MotherOfficeID="SO005";$AssignedOfficeID="SO005";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							if(isStringPresent($Opisina, array("ADMIN"))){$CGrpID="CG07";$MotherOfficeID="SO007";$AssignedOfficeID="SO007";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							if(isStringPresent($Opisina, array("VICE GOV","VICE-GOV","PGLU - VGO"))){$CGrpID="CG08";$MotherOfficeID="SO008";$AssignedOfficeID="SO008";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							if(isStringPresent($Opisina, array("SP","SANGUNIANG PANLALAWIGAN"))){$CGrpID="CG09";$MotherOfficeID="SO009";$AssignedOfficeID="SO009";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							if(isStringPresent($Opisina, array("OPAG"))){$CGrpID="CG10";$MotherOfficeID="SO010";$AssignedOfficeID="SO010";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							if(isStringPresent($Opisina, array("PPDC","PPDO"))){$CGrpID="CG11";$MotherOfficeID="SO011";$AssignedOfficeID="SO011";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							if(isStringPresent($Opisina, array("PBO"))){$CGrpID="CG12";$MotherOfficeID="SO012";$AssignedOfficeID="SO012";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							if(isStringPresent($Opisina, array("PGSO","PGLU-GSO","PGLU - GSO"))){$CGrpID="CG13";$MotherOfficeID="SO013";$AssignedOfficeID="SO013";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							if(isStringPresent($Opisina, array("PITO"))){$CGrpID="CG14";$MotherOfficeID="SO014";$AssignedOfficeID="SO014";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							if(isStringPresent($Opisina, array("PLO"))){$CGrpID="CG15";$MotherOfficeID="SO015";$AssignedOfficeID="SO015";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							if(isStringPresent($Opisina, array("ACC"))){$CGrpID="CG16";$MotherOfficeID="SO016";$AssignedOfficeID="SO016";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							//if(isStringPresent($Opisina, array("",""))){$MotherOfficeID="SO017";$AssignedOfficeID="SO017";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							if(isStringPresent($Opisina, array("PTO"))){$CGrpID="CG18";$MotherOfficeID="SO018";$AssignedOfficeID="SO018";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							if(isStringPresent($Opisina, array("ASSESOR","ASSESSOR"))){$CGrpID="CG19";$MotherOfficeID="SO019";$AssignedOfficeID="SO019";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							if(isStringPresent($Opisina, array("PHO"))){$CGrpID="CG20";$MotherOfficeID="SO020";$AssignedOfficeID="SO020";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							if(isStringPresent($Opisina, array("NUTRITION","NUTRIRION"))){$CGrpID="CG21";$MotherOfficeID="SO021";$AssignedOfficeID="SO021";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							if(isStringPresent($Opisina, array("RDH"))){$CGrpID="CG22";$MotherOfficeID="SO022";$AssignedOfficeID="SO022";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							if(isStringPresent($Opisina, array("BDH","BACNOTAN"))){$CGrpID="CG23";$MotherOfficeID="SO023";$AssignedOfficeID="SO023";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							if(isStringPresent($Opisina, array("NDH","NAGUILIAN"))){$CGrpID="CG24";$MotherOfficeID="SO024";$AssignedOfficeID="SO024";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							if(isStringPresent($Opisina, array("NLUMCH"))){$CGrpID="CG25";$MotherOfficeID="SO025";$AssignedOfficeID="SO025";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							if(isStringPresent($Opisina, array("LUMC"))){$CGrpID="CG26";$MotherOfficeID="SO026";$AssignedOfficeID="SO026";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							if(isStringPresent($Opisina, array("CMCH","CABA"))){$CGrpID="CG27";$MotherOfficeID="SO027";$AssignedOfficeID="SO027";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							if(isStringPresent($Opisina, array("PSWD"))){$CGrpID="CG29";$MotherOfficeID="SO029";$AssignedOfficeID="SO029";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							if(isStringPresent($Opisina, array("PESO"))){$CGrpID="CG28";$MotherOfficeID="SO028";$AssignedOfficeID="SO028";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							if(isStringPresent($Opisina, array("PEO"))){$CGrpID="CG30";$MotherOfficeID="SO030";$AssignedOfficeID="SO030";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							if(isStringPresent($Opisina, array("OPVET","VET"))){$CGrpID="CG31";$MotherOfficeID="SO031";$AssignedOfficeID="SO031";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							if(isStringPresent($Opisina, array("POPCOM"))){$CGrpID="CG32";$MotherOfficeID="SO032";$AssignedOfficeID="SO032";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							if(isStringPresent($Opisina, array("SEF"))){$CGrpID="CG33";$MotherOfficeID="SO033";$AssignedOfficeID="SO033";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							if(isStringPresent($Opisina, array("MOTORPOOL"))){$CGrpID="CG30";$MotherOfficeID="SO034";$AssignedOfficeID="SO034";$SRecEmployer="PROVINCIAL GOVERNMENT OF LA UNION";$SRecOffice="";}
							
							$PosID="PO000";
							$SRecSalGradeStep="1";
							$SRecPosition=$MySQLi->RealEscapeString(strtoupper(trim($SRecInfo['position'])));
							$SRecJobDesc=$MySQLi->RealEscapeString(strtoupper(trim($SRecInfo['position'])));
							//if(isStringPresent($SRecPosition, array("PROB","PROV"))){$ApptStID="AS002";}
							
							$SRecRemarks=$MySQLi->RealEscapeString(strtoupper(trim($SRecInfo['separation_cause'])));

							$SRecSalary=($SRecInfo['salary']=='')?0.00:$SRecInfo['salary'];
							$ModeSalary=$MySQLi->RealEscapeString(strtoupper(trim($SRecInfo['mode_salary'])));
							$ApptStatus=$MySQLi->RealEscapeString(strtoupper(trim($SRecInfo['status'])));
							if(isStringPresent($ApptStatus, array("PROB","PROV"))){$ApptStID="AS002";}
							else if(isStringPresent($ApptStatus, array("JOB"))){$ApptStID="AS003";}
							else if(isStringPresent($ApptStatus, array("CASUAL","CAUSAL","EMER"))){$ApptStID="AS004";$PosID="PO001";$SRecSalGradeStep="1";$ModeSalary="MO";}//$SRecPosition="";
							else if(isStringPresent($ApptStatus, array("PERM","ANENT","MIDWIFE"))){$ApptStID="AS005";$ModeSalary="MO";}//$SRecPosition="";
							else if(isStringPresent($ApptStatus, array("TEMPO","TERMPO","SUBS"))){$ApptStID="AS006";}
							else if(isStringPresent($ApptStatus, array("VOLUNT"))){$ApptStID="AS007";}
							else if(isStringPresent($ApptStatus, array("ACTUAL","CONTR"))){$ApptStID="AS008";}
							else if(isStringPresent($ApptStatus, array("CONSULT"))){$ApptStID="AS009";}
							else if(isStringPresent($ApptStatus, array("CO-TER","MINOUS"))){$ApptStID="AS010";}
							else if(isStringPresent($ApptStatus, array("ELECTED"))){$ApptStID="AS011";}
							else if(isStringPresent($ApptStatus, array("APPOINTED"))){$ApptStID="AS013";}
							else if(isStringPresent($ApptStatus, array("SPES","OJT"))){$ApptStID="AS014";}
							else {$ApptStID="AS000";}
							
							$SalUnitID=($ModeSalary=='A'?'SU07':($ModeSalary=='MO'?'SU04':($ModeSalary=='DA'?'SU02':'SU00')));
							
							$sql='INSERT INTO `tblempservicerecords` (`SRecID`, `EmpID`, `SRecFromDay`, `SRecFromMonth`, `SRecFromYear`, `SRecToDay`, `SRecToMonth`, `SRecToYear`, `SRecIsGov`, `SRecEmployer`, `MotherOfficeID`, `AssignedOfficeID`, `SRecOffice`, `PosID`, `SRecPosition`, `SRecSalGradeStep`, `SRecSalary`, `ApptStID`, `SRecJobDesc`, `SalUnitID`, `SRecCurrentAppointment`, `SRecRemarks`, `RECORD_TIME`) VALUES ("'.$SRecID.'","'.$EmpID.'","'.$SRecFromDay.'","'.$SRecFromMonth.'","'.$SRecFromYear.'","'.$SRecToDay.'","'.$SRecToMonth.'","'.$SRecToYear.'","'.$SRecIsGov.'","'.$SRecEmployer.'","'.$MotherOfficeID.'","'.$AssignedOfficeID.'","'.$SRecOffice.'","'.$PosID.'","'.$SRecPosition.'","'.$SRecSalGradeStep.'","'.$SRecSalary.'","'.$ApptStID.'","'.$SRecJobDesc.'","'.$SalUnitID.'", "'.$SRecCurrentAppointment.'", "'.$SRecRemarks.'", NOW());';
							//$uc=array("\xA4", "\xA5"); $kc=array("Ñ", "Ñ"); $sql=str_replace($uc, $kc, $sql);
							$sql_u="";
							if($MySQLi->sqlQuery($sql,false)){
								$sql_u="UPDATE `tblemppersonalinfo` SET `CGrpID`='".$CGrpID."' WHERE `EmpID`='".$EmpID."';";
								$MySQLi->sqlQuery($sql_u,false);
								$DoneOnID+=1;$DoneSRec+=1;
							}
							//$DoneOnID+=1;$DoneSRec+=1;
						}
						$cur=odbc_exec($cnx,"SELECT sg_step FROM personal WHERE pers_id = '".$curID."'");
						if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
						$EmpSG=odbc_fetch_array($cur);
						$Step=explode("/",$EmpSG['sg_step']);
						
						$SerRec=$MySQLi->GetArray("SELECT `SRecID` FROM `tblempservicerecords` WHERE `EmpID`='$EmpID' ORDER BY `SRecFromYear` DESC, `SRecFromMonth` DESC, `SRecFromDay` DESC LIMIT 1;");
						$MySQLi->sqlQuery("UPDATE `tblempservicerecords` SET `SRecCurrentAppointment`='1', `SRecSalGradeStep`='".$Step[1]."' WHERE `EmpID`='$EmpID' AND `SRecID`='".$SerRec['SRecID']."' LIMIT 1;");
						$MySQLi->sqlQuery("UPDATE `tblempservicerecords` SET `SRecCurrentAppointment`='0' WHERE `EmpID`='$EmpID' AND `SRecID`<>'".$SerRec['SRecID']."';");
					}
					
					/* Check ID */
					if($DoneSRec>=$TotalRecords){
						$MSG="$DoneOnID DONE\n\nMigration Complete.";
						$respTxt="1|$curID|$nextID|$DoneSRec|$TotalRecords|$MSG";
					}
					else{
						/* Get Next ID */ 
						$cur=odbc_exec($cnx,"SELECT pers_id FROM personal WHERE dte_hired >= '".$DateHired."' AND pers_id > '".$curID."' AND pers_id >= '".$StartID."' AND pers_id <= '".$EndID."' ORDER BY pers_id ASC");
						if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
						if(odbc_fetch_row($cur)){$nextID=odbc_result($cur,1);}
						else{$nextID="";}
						
						if($nextID!=""){
							$respTxt="0|$curID|$nextID|$DoneSRec|$TotalRecords|$DoneOnID Records DONE\nProcessing $nextID . . . ";
						}
						else{
							$MSG="DONE\n\nThere was an error getting the next ID.";
							$respTxt="1|$curID|$nextID|$DoneSRec|$TotalRecords|$MSG";
						}
					}
					
					break;
					
		default	:	echo "-1|0|0|0|0|ERROR 49:~!!!";exit();
	}
	
  odbc_close($cnx);
	echo $respTxt;


?>