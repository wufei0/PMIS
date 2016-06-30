<?php
	ob_start();
	session_start();
		
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD002'));
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$EmploymentStatus=isset($_POST['es'])?trim(strip_tags($_POST['es'])):"I";
	$DateHired=isset($_POST['dh'])?trim(strip_tags($_POST['dh'])):'2015-01-01 00:00:00';
	$StartID=isset($_POST['iid'])?trim(strip_tags($_POST['iid'])):'0';
	$EndID=isset($_POST['lid'])?trim(strip_tags($_POST['lid'])):'99999';
	$st=isset($_POST['st'])?trim(strip_tags($_POST['st'])):-1;
	$curID=isset($_POST['cid'])?trim(strip_tags($_POST['cid'])):"0";
	$nextID=isset($_POST['nid'])?trim(strip_tags($_POST['nid'])):"0";
	$DoneIDs=isset($_POST['did'])?trim(strip_tags($_POST['did'])):0;
	$TotalRecords=isset($_POST['tid'])?trim(strip_tags($_POST['tid'])):0;	
	
	$cnx=odbc_pconnect('pmis_sybase','sa','') or die("-1|0|0|0|0|ERROR 49:~ No Database Connection.");
	if(!$cnx){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
	
	switch ($st){
		case -1	: 	// send a simple odbc query. returns an odbc cursor // employment_status = '".$EmploymentStatus."' AND // ".($EmploymentStatus=='A'?"ACTIVE":($EmploymentStatus=='D'?"DEAD FILE":($EmploymentStatus=='I'?"INACTIVE":"ON LEAVE")))."
					$sql="SELECT count(*) AS Personnels FROM personal WHERE dte_hired >= '".$DateHired."' AND pers_id >= '".$StartID."' AND pers_id <= '".$EndID."' ORDER BY pers_id ASC";
					$cur=odbc_exec($cnx,$sql);
					if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned) $sql";odbc_close($cnx);exit();}
					$Personal=odbc_fetch_array($cur);
					$TotalRecords=$Personal['Personnels'];
					$sql="SELECT pers_id FROM personal WHERE dte_hired >= '".$DateHired."' AND pers_id >= '".$StartID."' AND pers_id <= '".$EndID."' ORDER BY pers_id ASC";
					$cur=odbc_exec($cnx,$sql);
					if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned) $sql";odbc_close($cnx);exit();}
					$Personal=odbc_fetch_array($cur);
					$curID=$nextID=$Personal['pers_id'];
					$DoneIDs=0;
					$MSG="\nMigrating all personal information from Sybase database to MySQL.\nNew Employees as of ".$DateHired."\nTotal Records: $TotalRecords\nProcessing $curID . . . ";
					$respTxt="0|$curID|$nextID|$DoneIDs|$TotalRecords|$MSG";
					break;
					
		case 0	:
					$EmpID=$curID=$nextID;
					$MySQLi=new MySQLClass();
					
					$sql="SELECT pers_id, last_name, middle_name, first_name, telno, birthdate, birthplace_prov, birthplace_muni, sex, marital_status, height, weight, blood_tye, gsis_no, sss_no, address_street_no, address_bgy, address_muni, address_prov, email_address, tin, spouse, spouse_occupation, employment_status FROM personal WHERE pers_id='".$curID."'";
					$cur=odbc_exec($cnx, $sql);
					if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned) $sql";odbc_close($cnx);exit();}
					$Personal=odbc_fetch_array($cur);
					$EmpID=$EmpAgencyNo=$EmpImgID=strtoupper(trim($Personal['pers_id']));
					$EmpLName=$MySQLi->RealEscapeString(strtoupper(trim($Personal['last_name'])));
					$EmpMName=$MySQLi->RealEscapeString(strtoupper(trim($Personal['middle_name'])));
					$EmpFName=$MySQLi->RealEscapeString(strtoupper(trim($Personal['first_name'])));
					$EmpExtName="";
					$EmpBirthDay=substr($Personal['birthdate'], 8, 2);
					$EmpBirthMonth=substr($Personal['birthdate'], 5, 2);
					$EmpBirthYear=substr($Personal['birthdate'], 0, 4);
					/* Get Birth Place - MUNICIPALITY */
					$Place=odbc_exec($cnx, "SELECT municipality FROM municipality WHERE municipal_code = '".$Personal['birthplace_muni']."'");
					if(!$Place){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned) $sql";odbc_close($cnx);exit();}
					$BPlace=odbc_fetch_array($Place);
					$BirthPlaceMuni=(strlen(trim($BPlace['municipality']))>6)?substr(trim($BPlace['municipality']), 0, -5).", ":"";
					/* Get Birth Place - PROVINCIAL */
					$Place=odbc_exec($cnx, "SELECT province FROM province WHERE prov_code = '".$Personal['birthplace_prov']."'");
					if(!$Place){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned) $sql";odbc_close($cnx);exit();}
					$BPlace=odbc_fetch_array($Place);
					$BirthPlaceProv=trim($BPlace['province']);
					$EmpBirthPlace=$MySQLi->RealEscapeString(strtoupper($BirthPlaceMuni.$BirthPlaceProv));
					$EmpSex=$MySQLi->RealEscapeString(strtoupper(trim($Personal['sex'])));
					$EmpCivilStatus=$MySQLi->RealEscapeString(strtoupper(trim($Personal['marital_status'])));
					$EmpCivilStatus=($EmpCivilStatus==1?"SINGLE":($EmpCivilStatus==2?"MARRIED":($EmpCivilStatus==3?"WIDOW":"SEPARATED")));
					$EmpCitizenship="";
					$EmpHeight=number_format($Personal['height'],2);
					$EmpWeight=number_format($Personal['weight'],2);
					$EmpBloodType=$MySQLi->RealEscapeString(strtoupper(trim($Personal['blood_tye'])));
					$EmpBloodType=($EmpBloodType=='A+'?"A":($EmpBloodType=='B+'?"B":($EmpBloodType=='AB+'?"AB":"O")));
					$EmpGSIS=$MySQLi->RealEscapeString(strtoupper(trim($Personal['gsis_no'])));
					$EmpHDMF="";
					$EmpPH="";
					$EmpSSS=$MySQLi->RealEscapeString(strtoupper(trim($Personal['sss_no'])));
					$EmpPerAddSt=$EmpResAddSt=$EmpSpsAddSt=$MySQLi->RealEscapeString(strtoupper(trim($Personal['address_street_no'])));
					/* Get Address - BARANGAY */
					$Place=odbc_exec($cnx, "SELECT barangay FROM barangay WHERE bgy_code = '".$Personal['address_bgy']."'");
					if(!$Place){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned) $sql";odbc_close($cnx);exit();}
					$Add=odbc_fetch_array($Place);
					$EmpPerAddBrgy=$EmpResAddBrgy=$EmpSpsAddBrgy=$MySQLi->RealEscapeString(strtoupper(trim($Add['barangay'])));
					/* Get Address - MUNICIPALITY */
					$Place=odbc_exec($cnx, "SELECT municipality FROM municipality WHERE municipal_code = '".$Personal['address_muni']."'");
					if(!$Place){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned) $sql";odbc_close($cnx);exit();}
					$Add=odbc_fetch_array($Place);
					$Add_muni=$MySQLi->RealEscapeString(strtoupper(trim($Add['municipality'])));
					$EmpPerAddMun=$EmpResAddMun=$EmpSpsAddMun=substr(trim($Add_muni), 0, -5);
					$EmpPerZipCode=$EmpResZipCode=$EmpSpsZipCode=substr(trim($Add_muni), -4, 4);
					/* Get Address - PROVINCIAL */
					$Place=odbc_exec($cnx, "SELECT province FROM province WHERE prov_code = '".$Personal['address_prov']."'");
					if(!$Place){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned) $sql";odbc_close($cnx);exit();}
					$Add=odbc_fetch_array($Place);
					$EmpPerAddProv=$EmpResAddProv=$EmpSpsAddProv=$MySQLi->RealEscapeString(strtoupper(trim($Add['province'])));
					$EmpPerAddCtry=$EmpResAddCtry=$EmpSpsAddCtry="PHILIPPINES";
					$EmpMobile=$EmpPerTel=$EmpResTel=$EmpSpsTel=$MySQLi->RealEscapeString(strtoupper(trim($Personal['telno'])));
					$EmpEMail=$MySQLi->RealEscapeString(strtoupper(trim($Personal['email_address'])));
					$EmpTIN=$MySQLi->RealEscapeString(strtoupper(trim($Personal['tin'])));
					/* Get Spouse Name */
					$SpsName=explode(" ",$MySQLi->RealEscapeString(strtoupper(trim($Personal['spouse']))));
					$EmpSpsLName=isset($SpsName[2])?$SpsName[2]:"";
					$EmpSpsMName=isset($SpsName[1])?$SpsName[1]:"";
					$EmpSpsFName=isset($SpsName[0])?$SpsName[0]:"";
					$EmpSpsExtName="";
					$EmpSpsBusDesc=$MySQLi->RealEscapeString(strtoupper(trim($Personal['spouse_occupation'])));
					$EmpSpsBusAddSt="";
					$EmpSpsBusAddBrgy="";
					$EmpSpsBusAddMun="";
					$EmpSpsBusAddProv="";
					$EmpSpsBusAddCtry="";
					$EmpSpsBusZipCode="";
					$EmpSpsBusTel="";
					$EmpFatherLName="";
					$EmpFatherMName="";
					$EmpFatherFName="";
					$EmpFatherExtName="";
					$EmpMotherLName="";
					$EmpMotherMName="";
					$EmpMotherFName="";
					$eSt=$MySQLi->RealEscapeString(strtoupper(trim($Personal['employment_status'])));
					$EmpStatus=($eSt=='A'?"ACTIVE":($eSt=='D'?"DEAD FILE":($eSt=='I'?"INACTIVE":"RESIGNED")));
					$CGrpID="CG00";
					$UserGroupID="USRGRP000";
					$EmpAccessKey="";
					
					if($MySQLi->NumberOfRows("SELECT `EmpID` FROM `tblemppersonalinfo` WHERE `EmpID`='".$EmpID."';")==0){ $isNEW="YES";
						$sql="INSERT INTO `tblemppersonalinfo` (`EmpID`, `EmpLName`, `EmpMName`, `EmpFName`, `EmpExtName`, `EmpBirthDay`, `EmpBirthMonth`, `EmpBirthYear`, `EmpBirthPlace`, `EmpSex`, `EmpCivilStatus`, `EmpCitizenship`, `EmpHeight`, `EmpWeight`, `EmpBloodType`, `EmpGSIS`, `EmpHDMF`, `EmpPH`, `EmpSSS`, `EmpResAddSt`, `EmpResAddBrgy`, `EmpResAddMun`, `EmpResAddProv`, `EmpResAddCtry`, `EmpResZipCode`, `EmpResTel`, `EmpPerAddSt`, `EmpPerAddBrgy`, `EmpPerAddMun`, `EmpPerAddProv`, `EmpPerAddCtry`, `EmpPerZipCode`, `EmpPerTel`, `EmpEMail`, `EmpMobile`, `EmpAgencyNo`, `EmpTIN`, `EmpSpsLName`, `EmpSpsMName`, `EmpSpsFName`, `EmpSpsExtName`, `EmpSpsAddSt`, `EmpSpsAddBrgy`, `EmpSpsAddMun`, `EmpSpsAddProv`, `EmpSpsAddCtry`, `EmpSpsZipCode`, `EmpSpsTel`, `EmpSpsBusDesc`, `EmpSpsBusAddSt`, `EmpSpsBusAddBrgy`, `EmpSpsBusAddMun`, `EmpSpsBusAddProv`, `EmpSpsBusAddCtry`, `EmpSpsBusZipCode`, `EmpSpsBusTel`, `EmpFatherLName`, `EmpFatherMName`, `EmpFatherFName`, `EmpFatherExtName`, `EmpMotherLName`, `EmpMotherMName`, `EmpMotherFName`, `EmpImgID`, `EmpStatus`, `CGrpID`, `UserGroupID`, `EmpAccessKey`)
						VALUES ('".$EmpID."', '".$EmpLName."', '".$EmpMName."', '".$EmpFName."', '".$EmpExtName."', '".$EmpBirthDay."', '".$EmpBirthMonth."', '".$EmpBirthYear."', '".$EmpBirthPlace."', '".$EmpSex."', '".$EmpCivilStatus."', '".$EmpCitizenship."', '".$EmpHeight."', '".$EmpWeight."', '".$EmpBloodType."', '".$EmpGSIS."', '".$EmpHDMF."', '".$EmpPH."', '".$EmpSSS."', '".$EmpResAddSt."', '".$EmpResAddBrgy."', '".$EmpResAddMun."', '".$EmpResAddProv."', '".$EmpResAddCtry."', '".$EmpResZipCode."', '".$EmpResTel."', '".$EmpPerAddSt."', '".$EmpPerAddBrgy."', '".$EmpPerAddMun."', '".$EmpPerAddProv."', '".$EmpPerAddCtry."', '".$EmpPerZipCode."', '".$EmpPerTel."', '".$EmpEMail."', '".$EmpMobile."', '".$EmpAgencyNo."', '".$EmpTIN."', '".$EmpSpsLName."', '".$EmpSpsMName."', '".$EmpSpsFName."', '".$EmpSpsExtName."', '".$EmpSpsAddSt."', '".$EmpSpsAddBrgy."', '".$EmpSpsAddMun."', '".$EmpSpsAddProv."', '".$EmpSpsAddCtry."', '".$EmpSpsZipCode."', '".$EmpSpsTel."', '".$EmpSpsBusDesc."', '".$EmpSpsBusAddSt."', '".$EmpSpsBusAddBrgy."', '".$EmpSpsBusAddMun."', '".$EmpSpsBusAddProv."', '".$EmpSpsBusAddCtry."', '".$EmpSpsBusZipCode."', '".$EmpSpsBusTel."', '".$EmpFatherLName."', '".$EmpFatherMName."', '".$EmpFatherFName."', '".$EmpFatherExtName."', '".$EmpMotherLName."', '".$EmpMotherMName."', '".$EmpMotherFName."', '".$EmpImgID."', '".$EmpStatus."', NULL, '".$UserGroupID."', '".$EmpAccessKey."');";
						//$uc=array("\xA4", "\xA5", "\xD1", "'"); $kc=array("Ñ", "Ñ", "Ñ", "\'"); $sql=str_replace($uc, $kc, $sql);
						if($MySQLi->sqlQuery($sql,false)){$ERROR_ba="Awan met Error Na...";}
						else{$ERROR_ba="Adda Error Na!!!";}
					}
					else{ $isNEW="NO";
						$sql="UPDATE `tblemppersonalinfo` SET `EmpLName`='".$EmpLName."',`EmpMName`='".$EmpMName."',`EmpFName`='".$EmpFName."',`EmpExtName`='".$EmpExtName."',`EmpBirthDay`='".$EmpBirthDay."',`EmpBirthMonth`='".$EmpBirthMonth."',`EmpBirthYear`='".$EmpBirthYear."',`EmpBirthPlace`='".$EmpBirthPlace."',`EmpSex`='".$EmpSex."',`EmpCivilStatus`='".$EmpCivilStatus."',`EmpCitizenship`='".$EmpCitizenship."',`EmpHeight`='".$EmpHeight."',`EmpWeight`='".$EmpWeight."',`EmpBloodType`='".$EmpBloodType."',`EmpGSIS`='".$EmpGSIS."',`EmpHDMF`='".$EmpHDMF."',`EmpPH`='".$EmpPH."',`EmpSSS`='".$EmpSSS."',`EmpResAddSt`='".$EmpResAddSt."',`EmpResAddBrgy`='".$EmpResAddBrgy."',`EmpResAddMun`='".$EmpResAddMun."',`EmpResAddProv`='".$EmpResAddProv."',`EmpResAddCtry`='".$EmpResAddCtry."',`EmpResZipCode`='".$EmpResZipCode."',`EmpResTel`='".$EmpResTel."',`EmpPerAddSt`='".$EmpPerAddSt."',`EmpPerAddBrgy`='".$EmpPerAddBrgy."',`EmpPerAddMun`='".$EmpPerAddMun."',`EmpPerAddProv`='".$EmpPerAddProv."',`EmpPerAddCtry`='".$EmpPerAddCtry."',`EmpPerZipCode`='".$EmpPerZipCode."',`EmpPerTel`='".$EmpPerTel."',`EmpEMail`='".$EmpEMail."',`EmpMobile`='".$EmpMobile."',`EmpAgencyNo`='".$EmpAgencyNo."',`EmpTIN`='".$EmpTIN."',`EmpSpsLName`='".$EmpSpsLName."',`EmpSpsMName`='".$EmpSpsMName."',`EmpSpsFName`='".$EmpSpsFName."',`EmpSpsExtName`='".$EmpSpsExtName."',`EmpSpsAddSt`='".$EmpSpsAddSt."',`EmpSpsAddBrgy`='".$EmpSpsAddBrgy."',`EmpSpsAddMun`='".$EmpSpsAddMun."',`EmpSpsAddProv`='".$EmpSpsAddProv."',`EmpSpsAddCtry`='".$EmpSpsAddCtry."',`EmpSpsZipCode`='".$EmpSpsZipCode."',`EmpSpsTel`='".$EmpSpsTel."',`EmpSpsBusDesc`='".$EmpSpsBusDesc."',`EmpSpsBusAddSt`='".$EmpSpsBusAddSt."',`EmpSpsBusAddBrgy`='".$EmpSpsBusAddBrgy."',`EmpSpsBusAddMun`='".$EmpSpsBusAddMun."',`EmpSpsBusAddProv`='".$EmpSpsBusAddProv."',`EmpSpsBusAddCtry`='".$EmpSpsBusAddCtry."',`EmpSpsBusZipCode`='".$EmpSpsBusZipCode."',`EmpSpsBusTel`='".$EmpSpsBusTel."',`EmpFatherLName`='".$EmpFatherLName."',`EmpFatherMName`='".$EmpFatherMName."',`EmpFatherFName`='".$EmpFatherFName."',`EmpFatherExtName`='".$EmpFatherExtName."',`EmpMotherLName`='".$EmpMotherLName."',`EmpMotherMName`='".$EmpMotherMName."',`EmpMotherFName`='".$EmpMotherFName."',`EmpImgID`='".$EmpImgID."',`EmpStatus`='".$EmpStatus."' WHERE `EmpID`='".$EmpID."';";
						//$uc=array("\xA4", "\xA5", "\xD1", "'"); $kc=array("Ñ", "Ñ", "Ñ", "\'"); $sql=str_replace($uc, $kc, $sql);
						if($MySQLi->sqlQuery($sql,false)){$ERROR_ba="Awan met Error Na...";}
						else{$ERROR_ba="Adda Error Na!!!";}
					}
				
					
					$DoneIDs+=1;
					
					if($DoneIDs>=$TotalRecords){
						$MSG="DONE - NEW: $isNEW\nMigration Complete.\n\n";
						$respTxt="1|$curID|$nextID|$DoneIDs|$TotalRecords|$MSG";
					}
					else{
						/* Get Next ID */
						$sql="SELECT pers_id FROM personal WHERE dte_hired >= '".$DateHired."' AND pers_id > '".$curID."' AND pers_id >= '".$StartID."' AND pers_id <= '".$EndID."' ORDER BY pers_id ASC"; 
						$cur=odbc_exec($cnx,$sql);
						if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned) $sql";odbc_close($cnx);exit();}
						if(odbc_fetch_row($cur)){$nextID=odbc_result($cur,1);}
						else{$nextID="";}
						
						if($nextID!=""){
							$respTxt="0|$curID|$nextID|$DoneIDs|$TotalRecords|DONE - NEW: $isNEW\nProcessing $nextID . . .";
						}
						else{
							$MSG="\nThere was an error getting the next ID.";
							$respTxt="1|$curID|$nextID|$DoneIDs|$TotalRecords|$MSG";
						}
					}
					break;
					
		default	:	echo "-1|0|0|0|0|ERROR 49:~!!!";exit();
	}
	
  odbc_close($cnx);
	echo $respTxt;


?>