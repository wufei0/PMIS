<?php
	session_start();
	define('ROOT_PATH', $_SESSION['path']);
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD001'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[0]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */


	
	/* Get GET Values */
	$mod=isset($_GET['mod'])?trim(strip_tags($_GET['mod'])):"srch";
	$grp=isset($_GET['grp'])?trim(strip_tags($_GET['grp'])):"0";
	$opt=isset($_GET['opt'])?trim(strip_tags($_GET['opt'])):"EmpLName";
	$uin=isset($_GET['uin'])?trim(strip_tags($_GET['uin'])):"%";
	
	$MySQLi=new MySQLClass();
	
	$thisOffice="";
	if($_SESSION['usergroup']=="USRGRP004"){ /* For Administrative officer user, get Office */
		$sql="SELECT `AssignedOfficeID` FROM `tblempservicerecords` WHERE `EmpID`='".$_SESSION['user']."' AND `SRecIsGov`='YES' AND `SRecCurrentAppointment` = 1;";
		$SRecOff=$MySQLi->GetArray($sql);
		$AssignedOfficeID=$SRecOff['AssignedOfficeID'];
		$thisOffice=" AND `AssignedOfficeID` = '".$AssignedOfficeID."' ";
	}
	
	if ($mod=="srch"){
		$sql ="SELECT `tblemppersonalinfo`.`EmpID`, `tblemppersonalinfo`.`UserGroupID`, "; 
		if($grp=="0"){ $byusrgrp=" `tblemppersonalinfo`.`UserGroupID` <> 'USRGRP000' AND ";}
		else{ $byusrgrp=" `tblemppersonalinfo`.`UserGroupID` = '$grp' AND ";}
		
		switch ($opt){
			case 'EmpID':
				$sql.="CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName FROM (`tblemppersonalinfo` JOIN `tblempservicerecords` ON `tblemppersonalinfo`.`EmpID` = `tblempservicerecords`.`EmpID`) ";
				$sql.="WHERE $byusrgrp `tblemppersonalinfo`.`EmpID` LIKE '".$uin."%' AND `tblempservicerecords`.`SRecCurrentAppointment` = 1 $thisOffice AND `tblemppersonalinfo`.`EmpID` > '1000' AND `tblemppersonalinfo`.`EmpID` < '99999' ORDER BY `tblemppersonalinfo`.`EmpID` ASC;"; 
				break;
			case 'EmpLName':
				$sql.="CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName FROM (`tblemppersonalinfo` JOIN `tblempservicerecords` ON `tblemppersonalinfo`.`EmpID` = `tblempservicerecords`.`EmpID`) ";
				$sql.="WHERE $byusrgrp CONCAT_WS(' ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) LIKE '%".$uin."%' AND `tblempservicerecords`.`SRecCurrentAppointment` = 1 $thisOffice AND `tblemppersonalinfo`.`EmpID` > '1000' AND `tblemppersonalinfo`.`EmpID` < '99999' ORDER BY `tblemppersonalinfo`.`EmpLName`, `tblemppersonalinfo`.`EmpFName`, `tblemppersonalinfo`.`EmpMName` ASC;"; 
				break;
			case 'EmpMName':
				$sql.="CONCAT_WS(' ',`tblemppersonalinfo`.`EmpMName`, `tblemppersonalinfo`.`EmpFName`, `tblemppersonalinfo`.`EmpLName`) AS EmpName FROM (`tblemppersonalinfo` JOIN `tblempservicerecords` ON `tblemppersonalinfo`.`EmpID` = `tblempservicerecords`.`EmpID`) ";
				$sql.="WHERE $byusrgrp `tblemppersonalinfo`.`EmpMName`LIKE '".$uin."%' AND `tblempservicerecords`.`SRecCurrentAppointment` = 1 $thisOffice AND `tblemppersonalinfo`.`EmpID` > '1000' AND `tblemppersonalinfo`.`EmpID` < '99999' ORDER BY `EmpMName`, `tblemppersonalinfo`.`EmpFName`, `tblemppersonalinfo`.`EmpLName` ASC;"; 
				break;
			case 'EmpFName':
				$sql.="CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS(' ', CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''), `tblemppersonalinfo`.`EmpLName`)) AS EmpName FROM (`tblemppersonalinfo` JOIN `tblempservicerecords` ON `tblemppersonalinfo`.`EmpID` = `tblempservicerecords`.`EmpID`) ";
				$sql.="WHERE $byusrgrp CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, `tblemppersonalinfo`.`EmpLName`) LIKE '%".$uin."%' AND `tblempservicerecords`.`SRecCurrentAppointment` = 1 $thisOffice AND `tblemppersonalinfo`.`EmpID` > '1000' AND `tblemppersonalinfo`.`EmpID` < '99999' ORDER BY `tblemppersonalinfo`.`EmpFName`, `tblemppersonalinfo`.`EmpLName`, `tblemppersonalinfo`.`EmpMName` ASC;";
				break;
			case 'SubOffCode':
				$sql.="CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName FROM ((`tblemppersonalinfo` JOIN `tblempservicerecords` ON `tblemppersonalinfo`.`EmpID`=`tblempservicerecords`.`EmpID`) JOIN `tblsuboffices` ON `tblempservicerecords`.`MotherOfficeID`=`tblsuboffices`.`SubOffID`) ";
				$sql.="WHERE $byusrgrp (`tblsuboffices`.`SubOffName` LIKE '%".$uin."%' OR `tblsuboffices`.`SubOffCode` LIKE '%".$uin."%') AND `tblemppersonalinfo`.`EmpID` > '1000' AND `tblemppersonalinfo`.`EmpID` < '99999' ORDER BY `tblemppersonalinfo`.`EmpLName`, `tblemppersonalinfo`.`EmpFName`, `tblemppersonalinfo`.`EmpMName` ASC;"; 
				break;
			default: break;
		}
		
		$result=$MySQLi->sqlQuery($sql);
		if($MySQLi->NumberOfRows($sql)>0) {
			$lines ="<table class='usr_search_body'>";
			$lines.="<tbody>";
			$ison=true;
			while($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
				if ($ison){$lines.="<tr id='r_".$records[0]."' class='search_result_row_0' onClick='showUser(\"".$records[0]."\",\"".$records[1]."\"); return false'>";}
				else{$lines.="<tr id='r_".$records[0]."' class='search_result_row_1' onClick='showUser(\"".$records[0]."\",\"".$records[1]."\"); return false'>";}
				$lines.="<td class='search_result' align='center'  width='40px'>".$records[0]."</td>";
				$lines.="<td class='search_result'>".$records[2]."</td>";
				$lines.="</tr>";
				$ison=!$ison;
			}
			$lines.="</tbody>";
			$lines.="</table><span id='r_00000'></span>";
			echo "1|".$_SESSION['user']."|".$lines;
		}
		else{
			echo "1|".$_SESSION['user']."|<table width='100%' height='100%'><tr valign='center'><td align='center'><i>No record found.</i></td></tr></table>";
		}
	}
?>