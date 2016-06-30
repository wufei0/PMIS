<?php
ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD001'));
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	
	$MySQLi=new MySQLClass();
	
	function updateRecord($tbl, $fld, $oldID, $newID){}
	
	$pfx=isset($_POST['pfx'])?strtoupper(mysql_escape_string(strip_tags(trim($_POST['pfx'])))):date(' ');
	$fld=isset($_POST['fld'])?strtoupper(mysql_escape_string(strip_tags(trim($_POST['fld'])))):date(' ');
	$tbl=isset($_POST['tbl'])?strtoupper(mysql_escape_string(strip_tags(trim($_POST['tbl'])))):date(' ');
	$cmd=isset($_POST['cmd'])?strtoupper(mysql_escape_string(strip_tags(trim($_POST['cmd'])))):date('0');
	
	switch ($cmd) {
		case 0:
			$NumberOfRecords=$MySQLi->NumberOfRows("SELECT `$fld` FROM `$tbl` WHERE `$fld` NOT LIKE '$pfx%';");
			$sets=ceil($NumberOfRecords/100); $done=0;
			$respTxt="1|$sets|$done|";
			break;
		case 1:
			$done+=1;
			if($done<=$sets){
				/* Get New ID */
				$NewID="$pfx".$EmpID."001";
				while($records=mysql_fetch_array($MySQLi->sqlQuery("SELECT `$fld` FROM `$tbl` WHERE `$fld` NOT LIKE '$pfx%' LIMIT 100;"))) {
					$ccc=1;
					$oldID=$records[$fld];
					$NewID="$pfx".$records['EmpID']."001";
					while($isOnRec=mysql_fetch_array($MySQLi->sqlQuery("SELECT `$fld` FROM `$tbl` WHERE `$fld` = '$NewID';"))) {
						$ccc+=1;
						$ccc=($ccc>99)?$ccc:(($ccc>9)?"0".$ccc:"00".$ccc);
						$NewID="$pfx".$EmpID.$ccc;
					}
					if(!($MySQLi->sqlQuery("UPDATE `$fld` SET `$fld`='$newID' WHERE `$fld`='$oldID' LIMIT 1;"))){$respTxt="-1|$oldID|ERROR ".mysql_errno().":<br/>".mysql_error();}
				}
			}
			$respTxt=$sets;
			break;
		default:
			break;
	}
	echo $respTxt;
	
	
?>