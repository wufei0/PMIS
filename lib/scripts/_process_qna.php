<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD016'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if((!$Authorization[1])||(!$Authorization[2])){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$MySQLi=new MySQLClass();
	
	$EmpID=isset($_POST['EmpID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpID'])))):'';
	//Get Question IDs for POST indexes
	$QIDs=array_keys($_POST);
	$isERROR=false;
	foreach ($QIDs as $key => $QID) {
		if (strlen($QID) == 4) {
			$AnsIsYes=strtoupper(strip_tags(trim($_POST[$QID])));
			$AnsDetails=$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['d_'.$QID]))));
			$AnsID="A".$QID.$EmpID;
			if($MySQLi->NumberOfRows("SELECT * FROM `tblempanswers` WHERE `AnsID`='$AnsID' AND `QuesID`='$QID' AND `EmpID`='$EmpID';")>0){
				if($AnsIsYes){ $sql="UPDATE `tblempanswers` SET `AnsIsYes`='$AnsIsYes', `AnsDetails`='$AnsDetails', `RECORD_TIME`=NOW() WHERE `tblempanswers`.`AnsID`='$AnsID' AND `tblempanswers`.`QuesID`='$QID' AND `tblempanswers`.`EmpID`='$EmpID' LIMIT 1;"; }
				else { $sql="UPDATE `tblempanswers` SET `AnsIsYes`='$AnsIsYes', `AnsDetails`='', `RECORD_TIME`=NOW() WHERE `tblempanswers`.`AnsID`='$AnsID' AND `tblempanswers`.`QuesID`='$QID' AND `tblempanswers`.`EmpID`='$EmpID' LIMIT 1;"; }
			}
			else {
				$NewAID="A".$QID.$EmpID;
				if($AnsIsYes){$sql="INSERT INTO `tblempanswers` (`AnsID`, `EmpID`, `QuesID`, `AnsIsYes`, `AnsDetails`, `RECORD_TIME`) VALUES ('$NewAID', '$EmpID', '$QID', '$AnsIsYes', '".($MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['d_'.$QID])))))."', NOW());";}
				else{$sql="INSERT INTO `tblempanswers` (`AnsID`, `EmpID`, `QuesID`, `AnsIsYes`, `AnsDetails`, `RECORD_TIME`) VALUES ('$NewAID', '$EmpID', '$QID', '$AnsIsYes', '', NOW());";}
			}
			if(!$MySQLi->sqlQuery($sql)){$isERROR=true;}
		}
	} unset ($key); unset ($QID);
	if(!$isERROR){echo "1|$EmpID|Answers were successfully updated.";}
	ob_end_flush();
?>