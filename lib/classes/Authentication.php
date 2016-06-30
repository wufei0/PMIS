<?php

require_once $_SESSION['path'].'/lib/classes/MySQLClass.php';

class Authentication{
	
	var $OnlineTimeLimit=1800;	/* Set User Inactivity Time Limit */
	
	public function Authenticate($UserID,$Password,$RemoteIP,$RemoteHost,$RemoteMacAdd,$FingerPrint){
		$MySQLi=new MySQLClass();
		if($MySQLi->NumberOfRows("SELECT `EmpID` FROM `tblemppersonalinfo` WHERE `EmpID`='".$UserID."' AND `EmpAccessKey`='".md5($Password)."';")!=0){
			$isOnline=$this->checkMultipleLogin($UserID,$RemoteIP);
			$OnlineStatus=explode("|",$isOnline);
			if($OnlineStatus[0]==1){return "-1|".$OnlineStatus[1];}
			else{
				/* Get New UsrLogID */
				$NewUsrLogID="UL".date('ymd').$UserID."001";
				$count=1;
				while($Check=$MySQLi->GetArray("SELECT `UsrLogID` FROM `tbluserlogs` WHERE `UserID`='$UserID' AND `UsrLogID`='$NewUsrLogID';")){
					$count+=1;
					$ccc=($count>99)?$count:(($count>9)?"0".$count:"00".$count);
					$NewUsrLogID="UL".date('ymd').$UserID.$ccc;
				} $UsrLogID=$NewUsrLogID;
				
				$TimeNow=time();
				$UsrLogTimeIN=$TimeNow;						/* Get UsrLogTimeIN			*/
				$UsrLogTimeActive=$TimeNow;				/* Get UsrLogTimeActive	*/
				$UsrLogTimeOUT=$TimeNow+$this->OnlineTimeLimit;			/* Get UsrLogTimeOUT 		*/
				$UsrLogMacAddress=strtoupper($RemoteMacAdd);	/* Get UsrLogMacAddress */
				$UsrLogHostName=$RemoteHost;			/* Get UsrLogHostName 	*/
				
				$sql="INSERT INTO `tbluserlogs` (`UsrLogID`, `UserID`, `UsrLogTimeIN`, `UsrLogTimeActive`, `UsrLogTimeOUT`, `UsrLogIPAddress`, `UsrLogHostName`, `UsrLogMacAddress`, `UsrLogFingerPrint`, `RECORD_TIME`) VALUES ('$UsrLogID', '$UserID', '$UsrLogTimeIN', '$UsrLogTimeActive', '$UsrLogTimeOUT', '$RemoteIP', '$UsrLogHostName', '$UsrLogMacAddress', '$FingerPrint', NOW());";
				if($MySQLi->sqlQuery($sql,false)){return "1| ";}
			}
		}
		else{return "0|ERROR 401:~Invalid User Name or Password.";}
	}	
	
	public function checkMultipleLogin($UserID){
		$TimeNow=time();
		$MySQLi=new MySQLClass();
		if($OnlineInfo=$MySQLi->GetArray("SELECT `UsrLogIPAddress`, `UsrLogHostName`, `UsrLogMacAddress`, `UsrLogTimeActive` FROM `tbluserlogs` WHERE `UserID`='$UserID' AND `UsrLogTimeActive`<'$TimeNow' AND `UsrLogTimeOUT`>'$TimeNow' LIMIT 1;")){return "1|ERROR 409:~User ID $UserID is currently logged in at <br/>IP Address: ".$OnlineInfo['UsrLogIPAddress']."<br/>Computer Name: ".$OnlineInfo['UsrLogHostName']."<br/>Mac Address: ".$OnlineInfo['UsrLogMacAddress']."<br/>Time: ".date('Y-m-d H:i:s',$OnlineInfo['UsrLogTimeActive']);}
		else{return "0| ";}
	}
	
	public function isUserActive($UserID,$FingerPrint){
		$TimeNow=time();
		$MySQLi=new MySQLClass();
		$sql="SELECT `UsrLogTimeActive` FROM `tbluserlogs` WHERE `UsrLogTimeActive`>'".($TimeNow - $this->OnlineTimeLimit)."' AND `UsrLogTimeOUT`>'".$TimeNow."' AND `UserID`='$UserID' AND `UsrLogFingerPrint`='$FingerPrint';";
		if($MySQLi->NumberOfRows($sql)>0){$this->setUserActiveTime($UserID,$FingerPrint);return "1|Session active.";}
		else{return "-1|ERROR 401:~Session expired.";}
	}
	
	public function setUserActiveTime($UserID,$FingerPrint){
		$TimeNow=time();
		$UsrLogTimeActive=$TimeNow;				/* Get UsrLogTimeActive	*/
		$UsrLogTimeOUT=$TimeNow+$this->OnlineTimeLimit;			/* Get UsrLogTimeOUT 		*/
		$MySQLi=new MySQLClass();
		$MySQLi->sqlQuery("UPDATE `tbluserlogs` SET `UsrLogTimeActive`='$UsrLogTimeActive', `UsrLogTimeOUT`='$UsrLogTimeOUT' WHERE `UserID`='$UserID' AND `UsrLogFingerPrint`='$FingerPrint';",false);
	}
	
	public function logoutUser($UserID,$FingerPrint){
		$MySQLi=new MySQLClass();
		$TimeNow=time();
		$MySQLi->sqlQuery("UPDATE `tbluserlogs` SET `UsrLogTimeActive`='0', `UsrLogTimeOUT`='$TimeNow' WHERE `UsrLogID` LIKE 'UL".date('ymd').$UserID."%' AND `UsrLogFingerPrint`='$FingerPrint';",false);
	}
	
	public function getAuthorization($UserID,$ModuleID){
		$MySQLi=new MySQLClass();
		$sql="SELECT `tblsystemusergroups`.`UserGroupAccess` FROM `tblsystemusergroups` JOIN `tblemppersonalinfo` ON `tblsystemusergroups`.`UserGroupID` = `tblemppersonalinfo`.`UserGroupID` WHERE `tblemppersonalinfo`.`EmpID`='$UserID';";
		if(!($records=$MySQLi->GetArray($sql))){return "-1|ERROR 401:~User ID not active.";}
		else{
			$Module=$MySQLi->GetArray("SELECT `ModuleIndex` FROM `tblsystemmodules` WHERE `ModuleID`='".$ModuleID."';");
			$AccessCodes=explode(':',$records['UserGroupAccess']);
			$ModuleAccessCode_x=$AccessCodes[intval($Module['ModuleIndex'])];
			$ModuleAccessCode_b=$this->hex2bin_($ModuleAccessCode_x);
			return $ModuleAccessCode_b;
		}
	}
	
	private function hex2bin_($hx){$b="";$h=str_split($hx);foreach($h as $_b16){switch($_b16){case"0":$b.="0000";break;case"1":$b.="0001";break;case"2":$b.="0010";break;case"3":$b.="0011";break;case"4":$b.="0100";break;case"5":$b.="0101";break;case"6":$b.="0110";break;case"7":$b.="0111";break;case"8":$b.="1000";break;case"9":$b.="1001";break;case"A":$b.="1010";break;case"B":$b.="1011";break;case"C":$b.="1100";break;case"D":$b.="1101";break;case"E":$b.="1110";break;case"F":$b.="1111";break;}}return $b;}
}
?>