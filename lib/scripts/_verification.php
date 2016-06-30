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

	
	
	/* Get GET Values */
	$EmpID=isset($_GET['id'])?mysql_escape_string(trim(strip_tags($_GET['id']))):'00000';
	$EmpBirthDay=isset($_GET['bdy'])?mysql_escape_string(trim(strip_tags($_GET['bdy']))):'00';
	$EmpBirthMonth=isset($_GET['bmo'])?mysql_escape_string(trim(strip_tags($_GET['bmo']))):'00';
	$EmpBirthYear=isset($_GET['byr'])?mysql_escape_string(trim(strip_tags($_GET['byr']))):'0000';
	
	$EmpBirthDay=($EmpBirthDay<10)?"0".$EmpBirthDay:$EmpBirthDay;
	$EmpBirthMonth=($EmpBirthMonth<10)?"0".$EmpBirthMonth:$EmpBirthMonth;
	
	$MySQLi=new MySQLClass();
	if($MySQLi->NumberOfRows("SELECT `EmpID` FROM `tblemppersonalinfo` WHERE `EmpID`='".$EmpID."' AND `EmpBirthDay`='".$EmpBirthDay."' AND `EmpBirthMonth`='".$EmpBirthMonth."' AND `EmpBirthYear`='".$EmpBirthYear."';")>0){echo "1|$EmpID|";}
	else{echo "0|$EmpID|ERROR 406:~Invalid ID Number and/or Birth date.";}
	
?>