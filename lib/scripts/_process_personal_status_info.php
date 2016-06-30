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
	$EmpStatus=isset($_POST['EmpStatus'])?strtoupper(strip_tags(trim($_POST['EmpStatus']))):'INACTIVE';
	
	
	$MySQLi=new MySQLClass();
	if ($mode=="0"){
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
	}
	
	else if($mode=="1"){
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
			$sql="UPDATE `tblemppersonalinfo` SET `EmpStatus`='$EmpStatus',`RECORD_TIME`=NOW() WHERE `EmpID`='$EmpID' LIMIT 1;";

		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Personnel Status was successfully updated.";}
	}
	else{echo "0|$EmpID|ERROR ???:~Unkown mode.";}
	
	ob_end_flush();
?>