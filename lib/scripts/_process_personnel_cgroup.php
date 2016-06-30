<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD001'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	//Get POST Values
	$EmpID=isset($_POST['eid'])?strtoupper(strip_tags(trim($_POST['eid']))):'';
	$CGrpID=isset($_POST['CGrpID'])?strtoupper(strip_tags(trim($_POST['CGrpID']))):'';
	
	
	$MySQLi=new MySQLClass();
	
	$sql="UPDATE `tblemppersonalinfo` SET `CGrpID`='".$CGrpID."' WHERE `EmpID`='".$EmpID."' LIMIT 1;";
	if($MySQLi->sqlQuery($sql)) {echo "1|$EmpID|Personnel custom group updated.";}

	ob_end_flush();
?>