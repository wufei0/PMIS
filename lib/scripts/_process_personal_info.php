<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD003'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	
	
	
	/* Get POST Values */
	$mode=isset($_POST['mode'])?strip_tags(trim($_POST['mode'])):'';
	$EmpID=isset($_POST['EmpID'])?strtoupper(strip_tags(trim($_POST['EmpID']))):'';
	$ApptStID=isset($_POST['ApptStID'])?strtoupper(strip_tags(trim($_POST['ApptStID']))):'';
	$EmpLName=isset($_POST['EmpLName'])?strtoupper(strip_tags(trim($_POST['EmpLName']))):'';
	$EmpMName=isset($_POST['EmpMName'])?strtoupper(strip_tags(trim($_POST['EmpMName']))):'';
	$EmpFName=isset($_POST['EmpFName'])?strtoupper(strip_tags(trim($_POST['EmpFName']))):'';
	$EmpExtName=isset($_POST['EmpExtName'])?strtoupper(strip_tags(trim($_POST['EmpExtName']))):'';
	$EmpBirthDay=isset($_POST['EmpBirthDay'])?strtoupper(strip_tags(trim($_POST['EmpBirthDay']))):'';
	$EmpBirthMonth=isset($_POST['EmpBirthMonth'])?strtoupper(strip_tags(trim($_POST['EmpBirthMonth']))):'';
	$EmpBirthYear=isset($_POST['EmpBirthYear'])?strtoupper(strip_tags(trim($_POST['EmpBirthYear']))):'';
	$EmpBirthPlace=isset($_POST['EmpBirthPlace'])?strtoupper(strip_tags(trim($_POST['EmpBirthPlace']))):'';
	$EmpSex=isset($_POST['EmpSex'])?strtoupper(strip_tags(trim($_POST['EmpSex']))):'';
	$EmpCivilStatus=isset($_POST['EmpCivilStatus'])?strtoupper(strip_tags(trim($_POST['EmpCivilStatus']))):'';
	$EmpCitizenship=isset($_POST['EmpCitizenship'])?strtoupper(strip_tags(trim($_POST['EmpCitizenship']))):'';
	$EmpHeight=isset($_POST['EmpHeight'])?strtoupper(strip_tags(trim($_POST['EmpHeight']))):'';
	$EmpWeight=isset($_POST['EmpWeight'])?strtoupper(strip_tags(trim($_POST['EmpWeight']))):'';
	$EmpBloodType=isset($_POST['EmpBloodType'])?strtoupper(strip_tags(trim($_POST['EmpBloodType']))):'';
	$EmpGSIS=isset($_POST['EmpGSIS'])?strtoupper(strip_tags(trim($_POST['EmpGSIS']))):'';
	$EmpHDMF=isset($_POST['EmpHDMF'])?strtoupper(strip_tags(trim($_POST['EmpHDMF']))):'';
	$EmpPH=isset($_POST['EmpPH'])?strtoupper(strip_tags(trim($_POST['EmpPH']))):'';
	$EmpSSS=isset($_POST['EmpSSS'])?strtoupper(strip_tags(trim($_POST['EmpSSS']))):'';
	$EmpResAddSt=isset($_POST['EmpResAddSt'])?strtoupper(strip_tags(trim($_POST['EmpResAddSt']))):'';
	$EmpResAddBrgy=isset($_POST['EmpResAddBrgy'])?strtoupper(strip_tags(trim($_POST['EmpResAddBrgy']))):'';
	$EmpResAddMun=isset($_POST['EmpResAddMun'])?strtoupper(strip_tags(trim($_POST['EmpResAddMun']))) :'';
	$EmpResAddProv=isset($_POST['EmpResAddProv'])?strtoupper(strip_tags(trim($_POST['EmpResAddProv']))):'';
	$EmpResZipCode=isset($_POST['EmpResZipCode'])?strtoupper(strip_tags(trim($_POST['EmpResZipCode']))):'';
	$EmpResTel=isset($_POST['EmpResTel'])?strtoupper(strip_tags(trim($_POST['EmpResTel']))):'';
	$EmpPerAddSt=isset($_POST['EmpPerAddSt'])?strtoupper(strip_tags(trim($_POST['EmpPerAddSt']))):'';
	$EmpPerAddBrgy=isset($_POST['EmpPerAddBrgy'])?strtoupper(strip_tags(trim($_POST['EmpPerAddBrgy']))) :'';
	$EmpPerAddMun=isset($_POST['EmpPerAddMun'])?strtoupper(strip_tags(trim($_POST['EmpPerAddMun']))):'';
	$EmpPerAddProv=isset($_POST['EmpPerAddProv'])?strtoupper(strip_tags(trim($_POST['EmpPerAddProv']))) :'';
	$EmpPerZipCode=isset($_POST['EmpPerZipCode'])?strtoupper(strip_tags(trim($_POST['EmpPerZipCode']))):'';
	$EmpPerTel=isset($_POST['EmpPerTel'])?strtoupper(strip_tags(trim($_POST['EmpPerTel']))):'';
	$EmpEMail=isset($_POST['EmpEMail'])?strip_tags(trim($_POST['EmpEMail'])):'';
	$EmpMobile=isset($_POST['EmpMobile'])?strtoupper(strip_tags(trim($_POST['EmpMobile']))):'';
	$EmpAgencyNo=isset($_POST['EmpAgencyNo'])?strtoupper(strip_tags(trim($_POST['EmpAgencyNo']))):'';
	$EmpTIN=isset($_POST['EmpTIN'])?strtoupper(strip_tags(trim($_POST['EmpTIN']))):'';
	$CTCID=isset($_POST['CTCID'])?strtoupper(strip_tags(trim($_POST['CTCID']))):'';
	$CTCDateDay=isset($_POST['CTCDateDay'])?strtoupper(strip_tags(trim($_POST['CTCDateDay']))):'';
	$CTCDateMonth=isset($_POST['CTCDateMonth'])?strtoupper(strip_tags(trim($_POST['CTCDateMonth']))):'';
	$CTCDateYear=isset($_POST['CTCDateYear'])?strtoupper(strip_tags(trim($_POST['CTCDateYear']))):'';
	$CTCPlace=isset($_POST['CTCPlace'])?strtoupper(strip_tags(trim($_POST['CTCPlace']))):'';
	
	/* Fix Month Value (00 - 12) */
	$EmpBirthMonth=($EmpBirthMonth>9)?$EmpBirthMonth:"0".$EmpBirthMonth;
	$CTCDateMonth=($CTCDateMonth>9) ?$CTCDateMonth:"0".$CTCDateMonth;
	
	$MySQLi=new MySQLClass();
	if ($mode=="0"){
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
		/* Get New Emp ID */
		$bYear=substr($EmpBirthYear,-2);
		$NewEID=$bYear."0115";
		$ccc=1;
		while($records=$MySQLi->GetArray("SELECT `EmpID` FROM `tblemppersonalinfo` WHERE `EmpID`='$NewEID';")) {
			$ccc+=1;
			$ccc=($ccc<10)?"0".$ccc:$ccc;
			$NewEID=$bYear.$ccc."15";
		} $EmpID=$NewEID;
		$SRecID="SR".$EmpID."000";
		$sql="INSERT INTO `tblemppersonalinfo` (`EmpID`,`EmpLName`,`EmpMName`,`EmpFName`,`EmpExtName`,`EmpBirthDay`,`EmpBirthMonth`,`EmpBirthYear`,`EmpBirthPlace`,`EmpSex`,`EmpCivilStatus`,`EmpCitizenship`,`EmpHeight`,`EmpWeight`,`EmpBloodType`,`EmpGSIS`,`EmpHDMF`,`EmpPH`,`EmpSSS`,`EmpResAddSt`,`EmpResAddBrgy`,`EmpResAddMun`,`EmpResAddProv`,`EmpResAddCtry`,`EmpResZipCode`,`EmpResTel`,`EmpPerAddSt`,`EmpPerAddBrgy`,`EmpPerAddMun`,`EmpPerAddProv`,`EmpPerAddCtry`,`EmpPerZipCode`,`EmpPerTel`,`EmpEMail`,`EmpMobile`,`EmpAgencyNo`,`EmpTIN`,`EmpSpsLName`,`EmpSpsMName`,`EmpSpsFName`,`EmpSpsExtName`,`EmpSpsAddSt`,`EmpSpsAddBrgy`,`EmpSpsAddMun`,`EmpSpsAddProv`,`EmpSpsAddCtry`,`EmpSpsZipCode`,`EmpSpsTel`,`EmpSpsBusDesc`,`EmpSpsBusAddSt`,`EmpSpsBusAddBrgy`,`EmpSpsBusAddMun`,`EmpSpsBusAddProv`,`EmpSpsBusAddCtry`,`EmpSpsBusZipCode`,`EmpSpsBusTel`,`EmpFatherLName`,`EmpFatherMName`,`EmpFatherFName`,`EmpFatherExtName`,`EmpMotherLName`,`EmpMotherMName`,`EmpMotherFName`,`EmpImgID`,`EmpStatus`,`UserGroupID`,`EmpAccessKey`) VALUES ('$EmpID','$EmpLName','$EmpMName','$EmpFName','$EmpExtName','$EmpBirthDay','$EmpBirthMonth','$EmpBirthYear','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','USRGRP000','');";
		//INSERT INTO `tblempservicerecords` (`SRecID`,`EmpID`,`SRecFromDay`,`SRecFromMonth`,`SRecFromYear`,`SRecToDay`,`SRecToMonth`,`SRecToYear`,`SRecIsGov`,`SRecEmployer`,`MotherOfficeID`,`AssignedOfficeID`,`SRecOffice`,`PosID`,`SRecPosition`,`SRecSalGradeStep`,`SRecSalary`,`ApptStID`,`SRecJobDesc`,`SalUnitID`,`SRecCurrentAppointment`,`RECORD_TIME`) VALUES ('".$SRecID."','".$EmpID."','','','','','','','YES','','SO000','SO000','','PO000','','', 0.000, 'AS000','','SU00', 0, NOW());
		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Personnel Information was successfully added.";}
	}
	
	else if($mode=="1"){
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		if(!($records=$MySQLi->GetArray("SELECT `CTCID` FROM `tblempctc` WHERE `CTCID`='$CTCID';"))){
			$sql="INSERT INTO `tblempctc` (`CTCID`,`EmpID`,`CTCAmount`,`CTCDateDay`,`CTCDateMonth`,`CTCDateYear`,`CTCPlace`,`RECORD_TIME`) VALUES ('$CTCID','$EmpID',0,'$CTCDateDay','$CTCDateMonth','$CTCDateYear','$CTCPlace',NOW());";
			$MySQLi->sqlQuery($sql);
		}
		else{
			$sql="UPDATE `tblempctc` SET `CTCAmount`=0,`CTCDateDay`='$CTCDateDay',`CTCDateMonth`='$CTCDateMonth',`CTCDateYear`='$CTCDateYear',`CTCPlace`='$CTCPlace', `RECORD_TIME`=NOW() WHERE `CTCID`='$CTCID' LIMIT 1;";
			$MySQLi->sqlQuery($sql);
		}
		$sql="UPDATE `tblemppersonalinfo` SET `EmpLName`='$EmpLName',`EmpMName`='$EmpMName',`EmpFName`='$EmpFName',`EmpExtName`='$EmpExtName',`EmpBirthDay`='$EmpBirthDay',`EmpBirthMonth`='$EmpBirthMonth',`EmpBirthYear`='$EmpBirthYear',`EmpBirthPlace`='$EmpBirthPlace',`EmpSex`='$EmpSex',`EmpCivilStatus`='$EmpCivilStatus',`EmpCitizenship`='$EmpCitizenship',`EmpHeight`='$EmpHeight',`EmpWeight`='$EmpWeight',`EmpBloodType`='$EmpBloodType',`EmpGSIS`='$EmpGSIS',`EmpHDMF`='$EmpHDMF',`EmpPH`='$EmpPH',`EmpSSS`='$EmpSSS',`EmpResAddSt`='$EmpResAddSt',`EmpResAddBrgy`='$EmpResAddBrgy',`EmpResAddMun`='$EmpResAddMun',`EmpResAddProv`='$EmpResAddProv',`EmpResZipCode`='$EmpResZipCode',`EmpResTel`='$EmpResTel',`EmpPerAddSt`='$EmpPerAddSt',`EmpPerAddBrgy`='$EmpPerAddBrgy',`EmpPerAddMun`='$EmpPerAddMun',`EmpPerAddProv`='$EmpPerAddProv',`EmpPerZipCode`='$EmpPerZipCode',`EmpPerTel`='$EmpPerTel',`EmpEMail`='$EmpEMail',`EmpMobile`='$EmpMobile',`EmpAgencyNo`='$EmpAgencyNo',`EmpTIN`='$EmpTIN' WHERE `EmpID`='$EmpID' LIMIT 1;";

		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Personnel Information was successfully updated.";}
	}
	else{echo "0|$EmpID|ERROR ???:~Unkown mode.";}
	
	ob_end_flush();
?>