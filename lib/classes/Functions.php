<?php

require_once $_SESSION['path'].'/lib/classes/MySQLClass.php';

class PersonnelFunctions{
	
	var $MySQLi;
	
	function PersonnelFunctions(){$this->MySQLi = new MySQLClass();}
	
	public function getEmpPosition($eid){
		$Position=$this->MySQLi->GetArray("SELECT `tblpositions`.`PosDesc` FROM (`tblempservicerecords` JOIN `tblpositions` ON `tblempservicerecords`.`PosID`=`tblpositions`.`PosID`) JOIN `tblsuboffices` ON `tblempservicerecords`.`MotherOfficeID`=`tblsuboffices`.`SubOffID` WHERE `tblempservicerecords`.`EmpID`='".$eid."' AND `tblempservicerecords`.`SRecCurrentAppointment`='1';");
		return $Position['PosDesc'];
	}
	
	public function getAppointmentStatus($eid){
		if($AppointmentStatus=$this->MySQLi->GetArray("SELECT `tblapptstatus`.`ApptStDesc` FROM (`tblempservicerecords` JOIN `tblapptstatus` ON `tblempservicerecords`.`ApptStID`=`tblapptstatus`.`ApptStID`) WHERE `tblempservicerecords`.`EmpID`='".$eid."' AND `tblempservicerecords`.`SRecCurrentAppointment`='1';")){
			return $AppointmentStatus['ApptStDesc'];
		}
		else{return false;}
	}
	
	public function getAssignedOffice($eid,$off="code"){
		$AssignedOffice=$this->MySQLi->GetArray("SELECT `tblsuboffices`.`SubOffCode`, `tblsuboffices`.`SubOffName` FROM (`tblempservicerecords` JOIN `tblsuboffices` ON `tblempservicerecords`.`AssignedOfficeID`=`tblsuboffices`.`SubOffID`) WHERE `tblempservicerecords`.`EmpID`='".$eid."' AND `tblempservicerecords`.`SRecCurrentAppointment`='1';");
		if($off=="code"){return $AssignedOffice['SubOffCode'];}
		else{return $AssignedOffice['SubOffName'];}
	}
	
	public function getMotherOffice($eid,$off="code"){
		$MotherOffice=$this->MySQLi->GetArray("SELECT `tblsuboffices`.`SubOffCode`, `tblsuboffices`.`SubOffName` FROM (`tblempservicerecords` JOIN `tblsuboffices` ON `tblempservicerecords`.`MotherOfficeID`=`tblsuboffices`.`SubOffID`) WHERE `tblempservicerecords`.`EmpID`='".$eid."' AND `tblempservicerecords`.`SRecCurrentAppointment`='1';");
		if($off=="code"){return $MotherOffice['SubOffCode'];}
		else{return $MotherOffice['SubOffName'];}
	}
	
	public function getEmpName($eid,$f="LFM",$abM=true){ /* $f -> LFM - Flores, Raymond Legaspi | FML - Raymond Legaspi Flores $abM -> true - L. | false - Legaspi */
		if($Personal=$this->MySQLi->GetArray("SELECT CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName FROM `tblemppersonalinfo` WHERE `tblemppersonalinfo`.`EmpID`='".$eid."';")){
		return $Personal['EmpName'];}else{return "$eid NOT EXIST.";}
	}
	
}

class OfficeFunctions{
	var $MySQLi;
	
	function OfficeFunctions(){$this->MySQLi = new MySQLClass();}
	
	public function getOfficeName($oid,$full=true){
		if($Office=$this->MySQLi->GetArray("SELECT `SubOffCode`, `SubOffName` FROM `tblsuboffices` WHERE `SubOffID`='".$oid."';")){return $full?$Office['SubOffName']:$Office['SubOffCode'];}else{return false;}
	}
	
}

class AppointmentFunctions{
	var $MySQLi;
	
	function AppointmentFunctions(){$this->MySQLi = new MySQLClass();}
	
	public function getAppointmentDesc($appt){
		if($Appointment=$this->MySQLi->GetArray("SELECT `ApptStDesc` FROM `tblapptstatus` WHERE `ApptStID`='".$appt."';")){return $Appointment['ApptStDesc'];}else{return false;}
	}
	
}

class LeaveFunctions extends COCFunctions {
	
	var $MySQLi;
	
	function LeaveFunctions(){$this->MySQLi = new MySQLClass();}
	
	public function GetLeaveName($lid){
		if($result=$this->MySQLi->GetArray("SELECT `LeaveTypeDesc` FROM `tblleavetypes` WHERE `LeaveTypeID` = '$lid';")){return $result['LeaveTypeDesc'];}
		return false;
	}
	
	public function GetLeaveCode($lid){
		if($result=$this->MySQLi->GetArray("SELECT `LeaveTypeCode` FROM `tblleavetypes` WHERE `LeaveTypeID` = '$lid';")){return $result['LeaveTypeCode'];}
		return false;
	}
	
	public function GetNewLivCredID($eid){$NewLivCredID="LC".date('Y').$eid."001";$count=1;while($this->MySQLi->NumberOfRows("SELECT `LivCredID` FROM `tblempleavecredits` WHERE `LivCredID`='".$NewLivCredID."';")>0){$count+=1;$ccc=($count>99)?$count:(($count>9)?"0".$count:"00".$count);$NewLivCredID="LC".date('Y').$eid.$ccc;}return $NewLivCredID;}
	
	public function checkFilingDate($fl,$ft,$tt,$lt){
		
		$fd=$fl;//date('U',mktime(00,00,00,date('m'),date('j'),date('Y')));
		if($lt=="LT01") {
			
		/*	if ($fd>($ft-(86400*1))) {
				echo "0|".$_SESSION['user']."|ERROR 406:~Late Filing. Vacation Leave shall be filed five (5) days in advance, whenever possible, of the effective date of such leave.<br/><i>- PGLU Employees Handbook (page 35)</i>";exit();
			} else {
				return false;
			} */
			if ($this->countWeekDays(date("Y-m-d",$tt)) > 15) {
				echo "0|".$_SESSION['user']."|ERROR 406:~Late Filing. Vacation Leave shall be accomodated until the 15<sup>th</sup> day after the consumption of leave.";exit();
			} else {
				return false;
			}	
			
		} else if($lt=="LT02") {
			
		/*	$fd=(date('N',$fd)==1)?($fd-86400-86400):$fd;
			if($fd>($tt+86400)){
				echo "0|".$_SESSION['user']."|ERROR 406:~Late Filing. Sick Leave shall be filed immediately upon employee's return from such leave.<br/><i>- PGLU Employees Handbook (page 36)</i>";exit();
			}
			else{
				return false;
			} */
			
			if ($this->countWeekDays(date("Y-m-d",$tt)) > 15) {				
				echo "0|".$_SESSION['user']."|ERROR 406:~Late Filing. Sick Leave shall be accomodated until the 15<sup>th</sup> day after the consumption of leave.";exit();
			} else {
				return false;
			}
			
		} // if($fd>($tt+86400))
		else if($lt=="LT03"){
			
/* 			if ($fd>($ft-(86400*1))) {
				echo "0|".$_SESSION['user']."|ERROR 406:~Late Filing. Privilege Leave shall be filed five (5) days in advance, whenever possible, of the effective date of such leave.<br/><i>- PGLU Employees Handbook (page 35)</i>";exit();
			} else {
				return false;
			} */
			
			if ($this->countWeekDays(date("Y-m-d",$tt)) > 15) {				
				echo "0|".$_SESSION['user']."|ERROR 406:~Late Filing. Privilege Leave shall be accomodated until the 15<sup>th</sup> day after the consumption of leave.";exit();
			} else {
				return false;
			}			
			
		} else if($lt=="LT04"){}
		else if($lt=="LT05"){if(($fd>($ft-(86400*1)))||($fd<($tt+(86400*1)))){echo "0|".$_SESSION['user']."|ERROR 406:~Late Filing. Paternity leave shall be filed immediately before or on the day of delivery of the legitimate wife, or immediately upon employee's return from such leave.<br/><i>- PGLU Employees Handbook (page 37)</i>";exit();}}
		else if($lt=="LT06"){}
		else if($lt=="LT07"){}
		else if($lt=="LT08"){}
		else{}
		
	}
	
	public function checkDateRange($ft,$tt){if($ft>$tt){echo "0|".$_SESSION['user']."|ERROR 406:~Invalid date range.";exit();}else{return false;}}
	
	public function NumberOfDays($ft,$tt,$fm,$tm,$LessWeekEnds=true){
		$ft=($fm=='AM')?$ft-(8*3600):$ft-(13*3600);
		$tt=($tm=='AM')?$tt-(12*3600):$tt-(17*3600);
		$LessPM=($fm=='PM')?43200:0;$LessAM=($tm=='AM')?43200:0;
		$NumOfDays=(($tt-$ft-$LessPM-$LessAM+86400)/86400);
		if($LessWeekEnds){
			for($d=$ft;$d<=$tt;$d+=86400){
				$DayNumber=date('w',$d);
				if(($DayNumber==0)||($DayNumber==6)){
					if(($d==$ft)&&($fm=='PM')){$NumOfDays-=0.5;}
					else if(($d==$tt)&&($tm=='AM')){$NumOfDays-=0.5;}
					else{$NumOfDays-=1;}
				}
			}
		}return $NumOfDays;
	}
	
	public function AvailableLeaveCredit($eid,$lid,$ldate=1970){ /* returns number of days */
		$AvailableCredit=0;
		if($this->MySQLi->NumberOfRows("SELECT `EmpID` FROM `s_servicerecord` WHERE `EmpID` = '".$eid."' AND (`ApptStID`='AS004' OR `ApptStID`='AS005' OR `ApptStID`='AS008' OR `ApptStID`='AS009' OR `ApptStID`='AS010' OR `ApptStID`='AS011' OR `ApptStID`='AS013') ;")==0){return $AvailableCredit;}
		
		if($lid=="LT08"){
			$this->UpdateCOCs($eid);
			$AvailableCredit=($this->AvailableCOCs($eid))/8;
		}
		else{
			$sql="SELECT `LivCredBalance` FROM `tblempleavecredits` WHERE `EmpID` = '".$eid."' AND `LeaveTypeID`='".$lid."'";
			if($lid=="LT03"){
				if($this->MySQLi->NumberOfRows($sql." AND `LivCredDateFrom` >= '".$ldate."-01-01 00:00:00';")==0){return 0.000;}
				else{$sql=$sql." AND `LivCredDateFrom` >= '".$ldate."-01-01 00:00:00'";}
			}
			
			$sql.=" ORDER BY `LivCredDateTo` DESC LIMIT 1;";
			$result=$this->MySQLi->sqlQuery($sql);
			$records=mysqli_fetch_array($result, MYSQLI_BOTH);
			$AvailableCredit=$records['LivCredBalance'];
			
		}
		return $AvailableCredit;
	}
	
	public function UnusedForceLeave($eid,$yy=2000){
		$sql="SELECT SUM(`LivCredDeductTo`) AS UsedLeave FROM `tblempleavecredits` WHERE `EmpID`='".$eid."' AND `LeaveTypeID`='LT01' AND `LivCredDateTo` >= '".$yy."-01-01 00:00:00' AND `LivCredDateTo` <= '".$yy."-12-31 23:59:59';";
		if($this->MySQLi->NumberOfRows($sql)==0){return 5;}
		else{
			$UsedLeave=$this->MySQLi->GetArray($sql)['UsedLeave'];
			$UnusedForceLeave=($UsedLeave>5)?0:5-$UsedLeave;
			return $UnusedForceLeave;
		}
	}
	
	function countWeekDays($dateTo) {
		
		$weekDays = 0;
		
		$day = $dateTo;
		$end = date("Y-m-d");
		while (strtotime($day) <= strtotime($end)) {
			
			// if ((date("D",strtotime($day)) != "Sat") && (date("D",strtotime($day)) != "Sun")) $weekDays++;
			$weekDays++;
			$day = date ("Y-m-d", strtotime("+1 day", strtotime($day)));			
			
		}
		
		return $weekDays-1;
		
	}
	
}

class COCFunctions {
	
	var $MySQLi;
	
	function COCFunctions(){$this->MySQLi = new MySQLClass();}
	
	public function GetNewCOCID(){$NewCOCID="COC".date('y').date('m')."0001";$ccc=1;while($this->MySQLi->NumberOfRows("SELECT `COCID` FROM `tblempcocs` WHERE `COCID`='$NewCOCID';")>0){$ccc+=1;$ccc=($ccc>999)?$ccc:(($ccc>99)?"0".$ccc:(($ccc>9)?"00".$ccc:"000".$ccc));$NewCOCID="COC".date('y').date('m').$ccc;}return $NewCOCID;}
	
	public function UpdateCOCs($eid){
		/* Check for EXPIRED COCs */
		$result=$this->MySQLi->sqlQuery("SELECT * FROM `tblempcocs` WHERE `tblempcocs`.`EmpID` = '$eid' AND `COCStatus`='4' AND `tblempcocs`.`COCExpireDate` < NOW();");
		while($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
			$this->MySQLi->sqlQuery("UPDATE `tblempcocs` SET `COCStatus`='-2', `COCApprovedRemarks`='System Auto-update.', `RECORD_TIME` = NOW() WHERE `COCID` = '".$records['COCID']."';");
			$LivCredID=LeaveFunctions::GetNewLivCredID($eid);
			$AvailableCOCs=$this->AvailableCOCs($eid);
			$this->MySQLi->sqlQuery("INSERT INTO `tblempleavecredits` (`LivCredID`, `EmpID`, `LeaveTypeID`, `LivCredDateFrom`, `LivCredDateTo`, `LivCredAddTo`, `LivCredDeductTo`, `LivCredBalance`, `LivCredReference`, `LivCredRemarks`, `RECORD_TIME`) VALUES ('".$LivCredID."', '".$eid."', 'LT08', '".$records['COCExpireDate']."', '".$records['COCExpireDate']."', '0', '".$records['COCRemainingHours']."', '".$AvailableCOCs."', '".$records['COCID']."', 'Expired COC', NOW());");
		}
	}
	
	public function AvailableCOCs($eid,$asOf=""){
		if($records=$this->MySQLi->GetArray("SELECT SUM(`COCRemainingHours`) AS AvailableHrs FROM `tblempcocs` WHERE `EmpID`='$eid' AND `COCStatus`='4';")){
		return $records['AvailableHrs'];}else{return 0;}
	}
	
}
?>