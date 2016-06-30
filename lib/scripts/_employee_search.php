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
	
	
	
	
	$MySQLi=new MySQLClass();
	
	/* Get GET Values */
	$mod=isset($_GET['mod'])?$MySQLi->RealEscapeString(trim(strip_tags($_GET['mod']))):'x';
	$opt=isset($_GET['opt'])?$MySQLi->RealEscapeString(trim(strip_tags($_GET['opt']))):'EmpLName';
	$key=isset($_GET['key'])?$MySQLi->RealEscapeString(strtoupper(trim(strip_tags($_GET['key'])))):"A";
	
	$thisOffice="";
	if($_SESSION['usergroup']=="USRGRP004"){ /* For Administrative officer user, get Office */
		$sql="SELECT `AssignedOfficeID` FROM `tblempservicerecords` WHERE `EmpID`='".$_SESSION['user']."' AND `SRecIsGov`='YES' AND `SRecCurrentAppointment` = 1;";
		$SRecOff=$MySQLi->GetArray($sql);
		$AssignedOfficeID=$SRecOff['AssignedOfficeID'];
		$thisOffice=" AND `EmpID` IN (SELECT `EmpID` FROM `tblempservicerecords` WHERE `AssignedOfficeID`='$AssignedOfficeID' AND `SRecIsGov`='YES' AND `SRecCurrentAppointment`=1)";
	}
	
	if ($mod=="srch"){
		$sql ="SELECT `tblemppersonalinfo`.`EmpID`, `tblemppersonalinfo`.`EmpStatus`, "; 
		switch ($opt){
			case 'EmpID':
				$sql.="CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName FROM `tblemppersonalinfo` ";
				$sql.="WHERE `tblemppersonalinfo`.`EmpID` LIKE '".$key."%' AND `tblemppersonalinfo`.`EmpID` > '1000' AND `tblemppersonalinfo`.`EmpID` < '99999' $thisOffice ORDER BY `tblemppersonalinfo`.`EmpID` ASC;"; 
				break;
			case 'EmpLName':
				$sql.="CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName FROM `tblemppersonalinfo` ";
				$sql.="WHERE CONCAT_WS(' ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) LIKE '".$key."%' AND `tblemppersonalinfo`.`EmpID` > '1000' AND `tblemppersonalinfo`.`EmpID` < '99999' $thisOffice ORDER BY `tblemppersonalinfo`.`EmpLName`, `tblemppersonalinfo`.`EmpFName`, `tblemppersonalinfo`.`EmpMName` ASC;"; 
				break;
			case 'EmpMName':
				$sql.="CONCAT_WS(' ',`tblemppersonalinfo`.`EmpMName`, `tblemppersonalinfo`.`EmpFName`, `tblemppersonalinfo`.`EmpLName`) AS EmpName FROM `tblemppersonalinfo` ";
				$sql.="WHERE `tblemppersonalinfo`.`EmpMName`LIKE '".$key."%' AND `tblemppersonalinfo`.`EmpID` > '1000' AND `tblemppersonalinfo`.`EmpID` < '99999' $thisOffice ORDER BY `EmpMName`, `tblemppersonalinfo`.`EmpFName`, `tblemppersonalinfo`.`EmpLName` ASC;"; 
				break;
			case 'EmpFName':
				$sql.="CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS(' ', CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''), `tblemppersonalinfo`.`EmpLName`)) AS EmpName FROM `tblemppersonalinfo` ";
				$sql.="WHERE CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, `tblemppersonalinfo`.`EmpLName`) LIKE '%".$key."%' AND `tblemppersonalinfo`.`EmpID` > '1000' AND `tblemppersonalinfo`.`EmpID` < '99999' $thisOffice ORDER BY `tblemppersonalinfo`.`EmpFName`, `tblemppersonalinfo`.`EmpLName`, `tblemppersonalinfo`.`EmpMName` ASC;";
				break;
			case 'SubOffCode':
				$sql.="CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName FROM `tblemppersonalinfo` ";
				$sql.="WHERE `tblemppersonalinfo`.`EmpID` > '1000' AND `tblemppersonalinfo`.`EmpID` < '99999' AND `EmpID` IN (SELECT `EmpID` FROM (`tblempservicerecords` JOIN `tblsuboffices` ON `tblempservicerecords`.`AssignedOfficeID` = `tblsuboffices`.`SubOffID`) WHERE (`tblsuboffices`.`SubOffName` LIKE '%".$key."%' OR `tblsuboffices`.`SubOffCode` LIKE '%".$key."%') AND `SRecIsGov`='YES' AND `SRecCurrentAppointment`=1) ORDER BY `tblemppersonalinfo`.`EmpLName`, `tblemppersonalinfo`.`EmpFName`, `tblemppersonalinfo`.`EmpMName` ASC;"; 
				break;
			default: break; /* $Builder=new Builder(); */
		}
		
		$result=$MySQLi->sqlQuery($sql);
		if($MySQLi->NumberOfRows($sql)>0){
			$lines="<table class='search_body'";
			$lines.="<tbody>";
			$ison=true;
			$get1stID=true;
			$FirstID="";
			while($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
				if($get1stID){$FirstID=$records['EmpID'];$get1stID=false;}
				$fColor=($records['EmpStatus']!="ACTIVE")?"color:#AA9999":"font-weight:bold;";
				if ($ison){
					$lines.="<tr id='r_".$records['EmpID']."' class='search_result_row_0' onClick='selectEmployee(\"".$records['EmpID']."\"); return false'>";
					$lines.="<td class='search_result' align='center'  width='40px' style='$fColor'>".$records['EmpID']."</td>";
					$lines.="<td class='search_result' style='$fColor'>".$records['EmpName']."</td>";
				}
				else{
					$lines.="<tr id='r_".$records['EmpID']."' class='search_result_row_1' onClick='selectEmployee(\"".$records['EmpID']."\"); return false'>";
					$lines.="<td class='search_result' align='center'  width='40px' style='$fColor'>".$records['EmpID']."</td>";
					$lines.="<td class='search_result' style='$fColor'>".$records['EmpName']."</td>";
				}
				$lines.="</tr>";
				$ison=!$ison;
			}
			$lines.="</tbody>";
			$lines.="</table><span id='r_00000'></span>";
			echo "1|".$FirstID."|".$lines;
		}
		else{
			echo "1|00000|<table width='100%' height='100%'><tr valign='center'><td align='center'><i>No record found.</i></td></tr></table>";
		}
	}

	if ($mod=="get"){
		if(!$Authorization[0]&&($_SESSION['user']!=$key)){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		$lines="1";
		$records=Array("00000","","","");
		
		$sql="SELECT `EmpID`, `EmpLName`, `EmpFName`, `EmpMName` FROM `tblemppersonalinfo` WHERE `EmpID`='$key';";
		$records=$MySQLi->GetArray($sql);
		
		if(!isset($records[0])){$records[0]="00000";}$EmpID=$records[0];
		if(!isset($records[1])){$records[1]="";}
		if(!isset($records[2])){$records[2]="";}
		$lines.="|".$records[0]."|".$records[1]."|".$records[2]."|".$records[3];
		
		$CurSRec=Array("");
		$sql="SELECT * FROM `tblempservicerecords` WHERE EmpID='$EmpID' AND `SRecCurrentAppointment`='1';";
		$CurSRec=$MySQLi->GetArray($sql);
		
		$records=Array("");
		$sql="SELECT `PosDesc`, `PosSalGrade` FROM `tblpositions` WHERE `PosID`='".$CurSRec['PosID']."' LIMIT 1;";
		$records=$MySQLi->GetArray($sql);
		
		if(!isset($records[0])){$records[0]=$CurSRec['SRecPosition'];}
		$lines.="|".$records[0]; /* PosDesc */
		if(!isset($records[1])){$records[1]="";} $PosSalGrade=$records[1];
		
		$records=Array("");
		$sql="SELECT `ApptStDesc` FROM `tblapptstatus` WHERE `ApptStID`='".$CurSRec['ApptStID']."' LIMIT 1;";
		$records=$MySQLi->GetArray($sql);
		if(!isset($records[0])){$records[0]="";}
		$lines.=" (".$records[0].")"; /* ApptStatus */
		
		$lines.="|".$PosSalGrade; /* PosSalGrade */ 

		$records=Array("");
		$sql="SELECT `SubOffName` FROM `tblsuboffices` WHERE `SubOffID`='".$CurSRec['MotherOfficeID']."' LIMIT 1;"; 
		$records=$MySQLi->GetArray($sql);

		$lines.="|0".$CurSRec['SRecSalGradeStep'];
		if(!isset($records['SubOffName'])){$records['SubOffName']="";}
		$lines.="|".$records['SubOffName']; /* Mother Office */
		
		$records=Array("");
		$sql="SELECT `SubOffName` FROM `tblsuboffices` WHERE `SubOffID`='".$CurSRec['AssignedOfficeID']."' LIMIT 1;"; 
		$records=$MySQLi->GetArray($sql);
		
		if(!isset($records['SubOffName'])){$records['SubOffName']="";}
		$lines.="|".$records['SubOffName']; /* Assigned Office */
		
		$records=Array("0","0","0");
		// $SalGrdID=($CurSRec['SRecFromYear'].($PosSalGrade<10?"0".$PosSalGrade:$PosSalGrade)."0".$CurSRec['SRecSalGradeStep']);
		// $sql="SELECT `SalGrdValue` FROM `tblsalgrade` WHERE `SalGrdID`='".$SalGrdID."' LIMIT 1;";
		$SalGrdID="SG".$CurSRec['SRecFromYear'].($PosSalGrade<10?"0".$PosSalGrade:$PosSalGrade);
		$Step=(($CurSRec['SRecSalGradeStep']>0)&&($CurSRec['SRecSalGradeStep']<9))?trim($CurSRec['SRecSalGradeStep']):'X';
		$sql="SELECT `Step".$Step."` FROM `tblsalarygrade` WHERE `SGID`='".$SalGrdID."' LIMIT 1;";
		
		$records=$MySQLi->GetArray($sql);
		if(!isset($records[0])){$records[0]="0";}

		$lines.="|".number_format($records[0],2); /* SalGrdValue */
		echo $lines;
	}
?>