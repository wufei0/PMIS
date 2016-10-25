 <?php
	ob_start();
	session_start();
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD015'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	// require_once $_SESSION['path'].'/lib/classes/pdo-db.php';
	
	$MySQLi=new MySQLClass();	
	
	$sql = "ALTER TABLE tbldisleavenotif AUTO_INCREMENT = 1;";
	$MySQLi->sqlQuery($sql);
	
	$sql = "INSERT INTO tbldisleavenotif (LivAppID,notifiedEmp) VALUES ('$_POST[LivAppID]','$_POST[notifiedEmp]');";
	$MySQLi->sqlQuery($sql);
	
	ob_end_flush();	
?>