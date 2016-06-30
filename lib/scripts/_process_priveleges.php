<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD001'));
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	function bin2hex_($bn){$h="";$b=str_split($bn,4);foreach($b as $_b2){switch($_b2){case"0000":$h.="0";break;case"0001":$h.="1";break;case"0010":$h.="2";break;case"0011":$h.="3";break;case"0100":$h.="4";break;case"0101":$h.="5";break;case"0110":$h.="6";break;case"0111":$h.="7";break;case"1000":$h.="8";break;case"1001":$h.="9";break;case"1010":$h.="A";break;case"1011":$h.="B";break;case"1100":$h.="C";break;case"1101":$h.="D";break;case"1110":$h.="E";break;case"1111":$h.="F";break;}}return $h;}
	
	//Get POST Values
	$UserID=isset($_POST['uid'])?trim(strip_tags($_POST['uid'])):"0";
	$UserGroupID=isset($_POST['gid'])?trim(strip_tags($_POST['gid'])):"USRGRP000";
	$UserGroupCode=isset($_POST['gcd'])?trim(strip_tags($_POST['gcd'])):"";
	$UserGroupName=isset($_POST['ugn'])?trim(strip_tags($_POST['ugn'])):"";
	$CheckBoxes=isset($_POST['cbs'])?trim(strip_tags($_POST['cbs'])):"0";
	$UpdUsrGrp=isset($_POST['uug'])?trim(strip_tags($_POST['uug'])):'x';
	
	if(!(($Authorization[0])||($_SESSION['user']==$UserID))){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	
	$AccessCodes=array();
	for($i=0;$i<=49;$i+=1){$AccessCodes[$i]="00000000";}$i=-1;
	$CheckBox=explode(",",$CheckBoxes);
	foreach($CheckBox as $bit){
		$el=explode(":",$bit);
		if($i!=intval($el[0])){$i=intval($el[0]);$AccessCodes[$i]="";}
		$AccessCodes[$i].=isset($el[1])?$el[1]:"";
	}
	
	ksort($AccessCodes);
	foreach($AccessCodes as $k => $v){$AccessCodes[$k]=bin2hex_($v);}
	$UserGroupAccess=implode(":",$AccessCodes);

	$MySQLi=new MySQLClass();
	
	if($UpdUsrGrp==0){ /* Update USER Privilege */
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		if($records=$MySQLi->GetArray("SELECT `UserGroupID` FROM `tblsystemusergroups` WHERE `UserGroupAccess`='$UserGroupAccess';")){ 
			$sql="UPDATE `tblemppersonalinfo` SET `UserGroupID`='".$records['UserGroupID']."',`RECORD_TIME`=NOW() WHERE `EmpID`='$UserID' LIMIT 1;";
			if($MySQLi->sqlQuery($sql)){echo "1|$UserID|User privilege was updated.";}
			else{echo "0|$UserID|ERROR ".mysql_errno().":~".mysql_error();}
			unset($sql);
		}
		else{
			/* Get New UserGroupID */
			$NewUserGroupID="USRGRP001";
			$count=1;
			while($MySQLi->GetArray("SELECT `UserGroupID` FROM `tblsystemusergroups` WHERE `UserGroupID`='$NewUserGroupID';")){
				$count+=1;
				$ccc=($count>99)?$count:(($count>9)?"0".$count:"00".$count);
				$NewUserGroupID="USRGRP".$ccc;
			} $UserGroupID=$NewUserGroupID;unset($count);unset($ccc);
			/* Get New UserGroupName */
			$NewUserGroupName="PRIVATE GROUP 01";$UserGroupCode="P01";
			$count=1;
			while($MySQLi->GetArray("SELECT `UserGroupName` FROM `tblsystemusergroups` WHERE `UserGroupName`='$NewUserGroupName';")){
				$count+=1;
				$ccc=(($count>9)?$count:"0".$count);
				$NewUserGroupName="PRIVATE GROUP ".$ccc;
				$UserGroupCode="P".$ccc;
			} $UserGroupName=$NewUserGroupName;unset($count);unset($ccc);
			
			/* Add new costum group */
			$sql="INSERT INTO `tblsystemusergroups` (`UserGroupID`, `UserGroupCode`, `UserGroupName`, `UserGroupAccess`, `RECORD_TIME`) VALUES ('$UserGroupID', '$UserGroupCode', '$UserGroupName', '$UserGroupAccess', NOW());";
			if($MySQLi->sqlQuery($sql)){ unset($sql);
				$sql="UPDATE `tblemppersonalinfo` SET `UserGroupID`='$UserGroupID',`RECORD_TIME`=NOW() WHERE `EmpID`='$UserID' LIMIT 1;";
				if($MySQLi->sqlQuery($sql)){ unset($sql);
					echo "1|$UserID|User privilege was updated.<br/>Added new group $UserGroupName ($UserGroupCode).";
				}
				else{echo "0|$UserID|ERROR ".mysql_errno().":~".mysql_error();}
			}
			else{echo "0|$UserID|ERROR ".mysql_errno().":~".mysql_error();}
		}
	}
	else if($UpdUsrGrp==1){ /* Update GROUP Privilege */
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		$sql="UPDATE `tblsystemusergroups` SET `UserGroupCode`='$UserGroupCode',`UserGroupName`='$UserGroupName',`UserGroupAccess`='$UserGroupAccess',`RECORD_TIME`=NOW() WHERE `UserGroupID`='$UserGroupID' LIMIT 1;";
		if($MySQLi->sqlQuery($sql)) {echo "1|$UserGroupID|Group privelege was successfully updated.";}
		else{echo "0|$UserGroupID|ERROR ".mysql_errno().":~".mysql_error();}
		unset($sql);
	}
	else if($UpdUsrGrp==-1){ /* Delete GROUP Privilege */
		if(!$Authorization[3]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		$sql="UPDATE `tblemppersonalinfo` SET `UserGroupID`='USRGRP000',`RECORD_TIME`=NOW() WHERE `UserGroupID`='$UserGroupID';";
		if($MySQLi->sqlQuery($sql)){
			$sql="DELETE FROM `tblsystemusergroups` WHERE `UserGroupID`='$UserGroupID';";
			if($MySQLi->sqlQuery($sql)){echo "1|$UserGroupID|User Group was deleted.";}
		}
		
	}
	else{echo "0|$UserID|ERROR ???:~Unkown mode.";}

	ob_end_flush();
?>