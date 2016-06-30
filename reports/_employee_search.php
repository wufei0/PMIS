<?php
session_start();
date_default_timezone_set('Asia/Taipei');
define('ROOT_PATH', $_SESSION['path']);
require_once ROOT_PATH.'/lib/classes/Conf.php';
require_once ROOT_PATH.'/lib/classes/MySQLClass.php';

/* Get GET Values */
$mod=isset($_GET['mod'])?mysql_escape_string(trim(strip_tags($_GET['mod']))):'x';
$opt=isset($_GET['opt'])?mysql_escape_string(trim(strip_tags($_GET['opt']))):'EmpLName';
$key=isset($_GET['key'])?strtoupper(mysql_escape_string(trim(strip_tags($_GET['key'])))):"A";

$Config=new Conf();
$MySQLi=new MySQLClass($Config);

if ($mod=="srch"){
	$sql ="SELECT `tblemppersonalinfo`.`EmpID`, "; 
	switch ($opt){
		case 'EmpID':
			$sql.="CONCAT_WS(', ',`EmpLName`, CONCAT_WS(' ',`EmpFName`, CONCAT_WS('.', SUBSTRING(`EmpMName`, 1, 1), ''))) AS EmpName FROM `tblemppersonalinfo` ";
			$sql.="WHERE `EmpID` LIKE '".$key."%' ORDER BY `EmpID` ASC;"; 
			break;
		case 'EmpLName':
			$sql.="CONCAT_WS(', ',`EmpLName`, CONCAT_WS(' ',`EmpFName`, CONCAT_WS('.', SUBSTRING(`EmpMName`, 1, 1), ''))) AS EmpName FROM `tblemppersonalinfo` ";
			$sql.="WHERE CONCAT_WS(' ',`EmpLName`, CONCAT_WS(' ',`EmpFName`, CONCAT_WS('.', SUBSTRING(`EmpMName`, 1, 1), ''))) LIKE '%".$key."%' ORDER BY `EmpLName`, `EmpFName`, `EmpMName` ASC;"; 
			break;
		case 'EmpMName':
			$sql.="CONCAT_WS(' ',`EmpMName`, `EmpFName`, `EmpLName`) AS EmpName FROM `tblemppersonalinfo` ";
			$sql.="WHERE `EmpMName`LIKE '".$key."%' ORDER BY `EmpMName`, `EmpFName`, `EmpLName` ASC;"; 
			break;
		case 'EmpFName':$sql.="CONCAT_WS(' ',`EmpFName`, CONCAT_WS(' ', CONCAT_WS('.', SUBSTRING(`EmpMName`, 1, 1), ''), `EmpLName`)) AS EmpName FROM `tblemppersonalinfo` ";
			$sql.="WHERE CONCAT_WS(' ',`EmpFName`, `EmpLName`) LIKE '%".$key."%' ORDER BY `EmpFName`, `EmpLName`, `EmpMName` ASC;";
			break;
		case 'SubOffCode':
			$sql.="CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName FROM ((`tblemppersonalinfo` JOIN `tblempservicerecords` ON `tblemppersonalinfo`.`EmpID`=`tblempservicerecords`.`EmpID`) JOIN `tblsuboffices` ON `tblempservicerecords`.`MotherOfficeID`=`tblsuboffices`.`SubOffID`) ";
			$sql.="WHERE `tblsuboffices`.`SubOffID` LIKE '".$key."%' ORDER BY `EmpLName`, `EmpFName`, `EmpMName` ASC;"; 
			break;
		default:/* $Builder=new Builder(); */
	}
	
	$result=$MySQLi->sqlQuery($sql);
	if(mysql_num_rows($result)>0){
		$lines ="<table class='search_body' id='ajax_search_result'>";
		$lines.="<tbody>";
		$ison=true;
		while($records=mysql_fetch_row($result)){
			if ($ison){
				$lines.="<tr id='r_".$records[0]."' class='search_result_row_0' onClick='selectEmployee(\"".$records[0]."\"); return false'>";
				$lines.="<td class='search_result' align='center'  width='40px'>".$records[0]."</td>";
				$lines.="<td class='search_result'>".$records[1]."</td>";
			}
			else{
				$lines.="<tr id='r_".$records[0]."' class='search_result_row_1' onClick='selectEmployee(\"".$records[0]."\"); return false'>";
				$lines.="<td class='search_result' align='center'  width='40px'>".$records[0]."</td>";
				$lines.="<td class='search_result'>".$records[1]."</td>";
			}
			$lines.="</tr>";
			$ison=!$ison;
		}
		$lines.="</tbody>";
		$lines.="</table><span id='r_00000'></span>";
		echo $lines;
	}
	else{
		echo "<table width='100%' height='100%'><tr valign='center'><td align='center'><i>No record found.</i></td></tr></table>";
	}
}

if ($mod=="get"){$lines="1";$records=Array("00000","","","");$sql="SELECT `EmpID`, `EmpLName`, `EmpFName`, `EmpMName` FROM `tblemppersonalinfo` WHERE `EmpID`='$key';"; $result=$MySQLi->sqlQuery($sql);$records=mysql_fetch_row($result);if(!isset($records[0])){$records[0]="00000";}if(!isset($records[1])){$records[1]="";}if(!isset($records[2])){$records[2]="";}$lines.="|".$records[0]."|".$records[1]."|".$records[2]."|".$records[3];$records=Array("");$sql="SELECT `tblpositions`.`PosDesc` FROM (`tblpositions` JOIN `tblempservicerecords` ON `tblpositions`.`PosID`=`tblempservicerecords`.`PosID`) WHERE `tblempservicerecords`.`EmpID`='$key' AND `tblempservicerecords`.`SRecIsGov`='YES' ORDER BY `tblempservicerecords`.`SRecFromYear` DESC, `tblempservicerecords`.`SRecFromMonth` DESC, `tblempservicerecords`.`SRecFromDay` DESC LIMIT 1;"; $result=$MySQLi->sqlQuery($sql);$records=mysql_fetch_row($result);if(!isset($records[0])){$records[0]="";}$lines.="|".$records[0];

$records=Array("");
$sql="SELECT `tblsuboffices`.`SubOffName` FROM (`tblsuboffices` JOIN `tblempservicerecords` ON `tblsuboffices`.`SubOffID`=`tblempservicerecords`.`MotherOfficeID`) WHERE `tblempservicerecords`.`EmpID`='$key' AND `tblempservicerecords`.`SRecIsGov`='YES' ORDER BY `tblempservicerecords`.`SRecFromYear` DESC, `tblempservicerecords`.`SRecFromMonth` DESC, `tblempservicerecords`.`SRecFromDay` DESC LIMIT 1;"; 
$result=$MySQLi->sqlQuery($sql);
$records=mysql_fetch_row($result);

if(!isset($records[0])){$records[0]="";}$lines.="|".$records[0];$records=Array("");

$sql="SELECT `tblsuboffices`.`SubOffName` FROM (`tblsuboffices` JOIN `tblempservicerecords` ON `tblsuboffices`.`SubOffID`=`tblempservicerecords`.`AssignedOfficeID`) WHERE `tblempservicerecords`.`EmpID`='$key' LIMIT 1;"; $result=$MySQLi->sqlQuery($sql);$records=mysql_fetch_row($result);if(!isset($records[0])){$records[0]="";}$lines.="|".$records[0];$records=Array("0","0","0");$sql="SELECT `tblsalgrade`.`SalGrade`, `tblsalgrade`.`SalStep`, `tblsalgrade`.`SalGrdValue` FROM (`tblsalgrade` JOIN `tblempservicerecords` ON `tblsalgrade`.`SalGrdID`=`tblempservicerecords`.`SalGrdID`) WHERE `tblempservicerecords`.`EmpID`='$key' AND `tblempservicerecords`.`SRecIsGov`='YES' ORDER BY `tblempservicerecords`.`SRecFromYear` DESC, `tblempservicerecords`.`SRecFromMonth` DESC, `tblempservicerecords`.`SRecFromDay` DESC LIMIT 1;"; $result=$MySQLi->sqlQuery($sql);$records=mysql_fetch_row($result);if(!isset($records[0])){$records[0]="0";}if(!isset($records[1])){$records[1]="0";}if(!isset($records[2])){$records[2]="0";}$records[0]=$records[0]>9?$records[0]:"0".$records[0];$records[1]=$records[1]>9?$records[1]:"0".$records[1];$lines.="|".$records[0]."|".$records[1]."|".number_format($records[2],2);echo $lines;}
?>