<?php
	ob_start();
	session_start();
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$LeaveAuth=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD018'));
	for($i=0;$i<=7;$i++){$LeaveAuth[$i]=$LeaveAuth[$i]==1?true:false;}
	$COCAuth=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD020'));
	for($i=0;$i<=7;$i++){$COCAuth[$i]=$COCAuth[$i]==1?true:false;}
	$TravAuth=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD019'));
	for($i=0;$i<=7;$i++){$TravAuth[$i]=$TravAuth[$i]==1?true:false;}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	
	
	require_once $_SESSION['path'].'/lib/classes/Functions.php';
	
	
	function StatusToString($st){
		switch ($st){case '0':return "NEW";break;case '1':return "POSTED";break;case '2':return "FILED";break;case '3':return "CONFIRMED";break;case '4':return "APPROVED";break;default: return "DISAPPROVED";break;}
	}
	
	
	
	$getNotificationOnly=isset($_POST['gn'])?strtoupper(strip_tags(trim($_POST['gn']))):'0';
	$getNotificationOnly=$getNotificationOnly=='1'?true:false;
	$TIMESTAMP=date('Y-m-d H:i:s');
	
	$Notify=false;$NotifVal=-1;
	$leaveAppStatus = true;
	if($LeaveAuth[5]||$LeaveAuth[6]||$LeaveAuth[7]||$TravAuth[5]||$TravAuth[6]|$TravAuth[7]||$COCAuth[5]||$COCAuth[6]|$COCAuth[7]){
		$MySQLi=new MySQLClass();
		$Personnel=new PersonnelFunctions();
		if(!$getNotificationOnly){echo "1|".$_SESSION['user']."|<div style='width:auto;height:auto;overflow:hidden;'>";}
		
		/* Leave Related Notifications --------------------------------------------------------------------------------------------------------------- */
		if($LeaveAuth[6]||$LeaveAuth[7]){
			?>
			<a href="#" class="notification_item" onClick="showLeaveApplications(); showNotifications(); return false;"><table class="ui-widget-content ui-corner-all" style="width:280px;padding:0px;border-spacing:0px;margin:3px 0px 0px 0px;"><tr valign="center"><td width="20px" align="center"><span class='ui-icon ui-icon-note'></span></td><td><strong><i>Leave Applications<?=$getNotificationOnly?></i><strong></td></tr></table></a>
			<?php
		}
		if($LeaveAuth[5]){
			if($_SESSION['usergroup']=="USRGRP004"){ /* For Administrative officer user, get Office */
				$SRecOff=$MySQLi->GetArray("SELECT `AssignedOfficeID` FROM `tblempservicerecords` WHERE `EmpID`='".$_SESSION['user']."' AND `SRecIsGov`='YES' AND `SRecCurrentAppointment`=1;");
				$AssignedOfficeID=$SRecOff['AssignedOfficeID'];
				$sql="SELECT `tblempleaveapplications`.`EmpID`, `tblempleaveapplications`.`LivAppID`, `tblempleaveapplications`.`LeaveTypeID`, `tblempleaveapplications`.`LivAppDays`,DATE_FORMAT(`tblempleaveapplications`.`LivAppFiledDate`, '%Y-%m-%d') AS LivAppFiledDate,DATE_FORMAT(`tblempleaveapplications`.`LivAppIncDateFrom`, '%b %d, %Y') AS LivAppIncDateFrom,`tblempleaveapplications`.`LivAppIncDayTimeFrom`,DATE_FORMAT(`tblempleaveapplications`.`LivAppIncDateTo`, '%b %d, %Y') AS LivAppIncDateTo,`tblempleaveapplications`.`LivAppIncDayTimeTo`,`tblempleaveapplications`.`LivAppStatus`,`tblleavetypes`.`LeaveTypeDesc`, `tblempleaveapplications`.`RECORD_TIME` FROM `tblempleaveapplications` JOIN `tblleavetypes` ON `tblempleaveapplications`.`LeaveTypeID`=`tblleavetypes`.`LeaveTypeID` WHERE (`tblempleaveapplications`.`LivAppStatus`='1') AND (`tblempleaveapplications`.`LeaveTypeID`!='LT08') AND  `tblempleaveapplications`.`EmpID` IN (SELECT `EmpID` FROM `tblempservicerecords` WHERE `AssignedOfficeID`='$AssignedOfficeID' AND `SRecIsGov`='YES' AND `SRecCurrentAppointment`=1) ORDER BY `LivAppFiledDate` DESC;";
			}
			else{
				$sql="SELECT `tblempleaveapplications`.`EmpID`, `tblempleaveapplications`.`LivAppID`, `tblempleaveapplications`.`LeaveTypeID`, `tblempleaveapplications`.`LivAppDays`,DATE_FORMAT(`tblempleaveapplications`.`LivAppFiledDate`, '%Y-%m-%d') AS LivAppFiledDate,DATE_FORMAT(`tblempleaveapplications`.`LivAppIncDateFrom`, '%b %d, %Y') AS LivAppIncDateFrom,`tblempleaveapplications`.`LivAppIncDayTimeFrom`,DATE_FORMAT(`tblempleaveapplications`.`LivAppIncDateTo`, '%b %d, %Y') AS LivAppIncDateTo,`tblempleaveapplications`.`LivAppIncDayTimeTo`,`tblempleaveapplications`.`LivAppStatus`,`tblleavetypes`.`LeaveTypeDesc`, `tblempleaveapplications`.`RECORD_TIME` FROM `tblempleaveapplications` JOIN `tblleavetypes` ON `tblempleaveapplications`.`LeaveTypeID`=`tblleavetypes`.`LeaveTypeID` WHERE (`tblempleaveapplications`.`LivAppStatus`='1') AND (`tblempleaveapplications`.`LeaveTypeID`!='LT08') ORDER BY `LivAppFiledDate` DESC;";
			}
			
			if($getNotificationOnly){
				if($MySQLi->NumberOfRows($sql)>0){
					$Notify=true;
					$records=$MySQLi->GetArray($sql);
					if(strtotime($records['RECORD_TIME'])>(strtotime($TIMESTAMP)-30)){$NotifVal=1;}
				}
			}
			else{
				$result=$MySQLi->sqlQuery($sql);
				while($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
					$EmpID=$records['EmpID'];
					switch ($records['LivAppStatus']){case '0':$leavestatus="NEW";break;case '1':$leavestatus="POSTED";break;case '2':$leavestatus="NOTED";break;case '3':$leavestatus="CHECKED";break;case '4':$leavestatus="APPROVED";break;default: $leavestatus="DISAPPROVED";break;}
					$LeaveDesc="<b>".$records['LeaveTypeDesc']."</b> Application of <b>".$Personnel->getEmpName($records['EmpID'])."</b>. From <b>".$records['LivAppIncDateFrom']." ".$records['LivAppIncDayTimeFrom']."</b> to <b>".$records['LivAppIncDateTo']." ".$records['LivAppIncDayTimeTo']."</b>, <b>".number_format($records['LivAppDays'],2)."</b> ";
					$LeaveDesc.=($records['LivAppDays']>1)?" days.":" day.";
						
				?>
					<a href="#" class="notification_item" onClick="viewRecordPLCT('<?php echo $EmpID; ?>','L'); showNotifications(); return false;"><table class="ui-widget-content ui-corner-all" style="width:280px;padding:0px;border-spacing:0px;margin:3px 0px 0px 0px;"><tr valign="center"><td width="20px" align="center"><span class='ui-icon ui-icon-calendar'></span></td><td><?php echo $LeaveDesc; ?></td></tr></table></a>
				<?php
				}
			}
		}
		
		if($LeaveAuth[6]){
			$sql="SELECT `tblempleaveapplications`.`EmpID`, `tblempleaveapplications`.`LivAppID`, `tblempleaveapplications`.`LeaveTypeID`, `tblempleaveapplications`.`LivAppDays`,DATE_FORMAT(`tblempleaveapplications`.`LivAppFiledDate`, '%Y-%m-%d') AS LivAppFiledDate,DATE_FORMAT(`tblempleaveapplications`.`LivAppIncDateFrom`, '%b %d, %Y') AS LivAppIncDateFrom,`tblempleaveapplications`.`LivAppIncDayTimeFrom`,DATE_FORMAT(`tblempleaveapplications`.`LivAppIncDateTo`, '%b %d, %Y') AS LivAppIncDateTo,`tblempleaveapplications`.`LivAppIncDayTimeTo`,`tblempleaveapplications`.`LivAppStatus`,`tblleavetypes`.`LeaveTypeDesc`, `tblempleaveapplications`.`RECORD_TIME` FROM `tblempleaveapplications` JOIN `tblleavetypes` ON `tblempleaveapplications`.`LeaveTypeID`=`tblleavetypes`.`LeaveTypeID` WHERE (`tblempleaveapplications`.`LivAppStatus`='2') AND (`tblempleaveapplications`.`LeaveTypeID`!='LT08') ORDER BY `LivAppFiledDate` DESC;";
			
			if($getNotificationOnly){
				if($MySQLi->NumberOfRows($sql)>0){
					$Notify=true;
					$records=$MySQLi->GetArray($sql);
					if(strtotime($records['RECORD_TIME'])>(strtotime($TIMESTAMP)-30)){$NotifVal=1;}
				}
			}
			else{
				$result=$MySQLi->sqlQuery($sql);
				while($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
					$EmpID=$records['EmpID'];
					switch ($records['LivAppStatus']){case '0':$leavestatus="NEW";break;case '1':$leavestatus="POSTED";break;case '2':$leavestatus="NOTED";break;case '3':$leavestatus="CHECKED";break;case '4':$leavestatus="APPROVED";break;default: $leavestatus="DISAPPROVED";break;}
					$LeaveDesc="<b>".$records['LeaveTypeDesc']."</b> Application of <b>".$Personnel->getEmpName($records['EmpID'])."</b>. From <b>".$records['LivAppIncDateFrom']." ".$records['LivAppIncDayTimeFrom']."</b> to <b>".$records['LivAppIncDateTo']." ".$records['LivAppIncDayTimeTo']."</b>, <b>".number_format($records['LivAppDays'],2)."</b> ";
					$LeaveDesc.=($records['LivAppDays']>1)?" days.":" day.";
						
				?>
					<a href="#" class="notification_item" onClick="viewRecordPLCT('<?= $EmpID; ?>','L'); showNotifications(); return false;"><table class="ui-widget-content ui-corner-all" style="width:280px;padding:0px;border-spacing:0px;margin:3px 0px 0px 0px;"><tr valign="center"><td width="20px" align="center"><span class='ui-icon ui-icon-calendar'></span></td><td><?php echo $LeaveDesc; ?></td></tr></table></a>
				<?php
				}
			}
		}
		
		if($LeaveAuth[7]){
			$sql="SELECT `tblempleaveapplications`.`EmpID`, `tblempleaveapplications`.`LivAppID`, `tblempleaveapplications`.`LeaveTypeID`, `tblempleaveapplications`.`LivAppDays`,DATE_FORMAT(`tblempleaveapplications`.`LivAppFiledDate`, '%Y-%m-%d') AS LivAppFiledDate,DATE_FORMAT(`tblempleaveapplications`.`LivAppIncDateFrom`, '%b %d, %Y') AS LivAppIncDateFrom,`tblempleaveapplications`.`LivAppIncDayTimeFrom`,DATE_FORMAT(`tblempleaveapplications`.`LivAppIncDateTo`, '%b %d, %Y') AS LivAppIncDateTo,`tblempleaveapplications`.`LivAppIncDayTimeTo`,`tblempleaveapplications`.`LivAppStatus`,`tblleavetypes`.`LeaveTypeDesc`, `tblempleaveapplications`.`RECORD_TIME` FROM `tblempleaveapplications` JOIN `tblleavetypes` ON `tblempleaveapplications`.`LeaveTypeID`=`tblleavetypes`.`LeaveTypeID` WHERE (`tblempleaveapplications`.`LivAppStatus`='3') AND (`tblempleaveapplications`.`LeaveTypeID`!='LT08') ORDER BY `LivAppFiledDate` DESC;";
			
			if($getNotificationOnly){
				if($MySQLi->NumberOfRows($sql)>0){
					$Notify=true;
					$records=$MySQLi->GetArray($sql);
					if(strtotime($records['RECORD_TIME'])>(strtotime($TIMESTAMP)-30)){$NotifVal=1;}
				}
			}
			else{
				$result=$MySQLi->sqlQuery($sql);
				while($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
					$EmpID=$records['EmpID'];
					switch ($records['LivAppStatus']){case '0':$leavestatus="NEW";break;case '1':$leavestatus="POSTED";break;case '2':$leavestatus="NOTED";break;case '3':$leavestatus="CHECKED";break;case '4':$leavestatus="APPROVED";break;default: $leavestatus="DISAPPROVED";break;}
					$LeaveDesc="<b>".$records['LeaveTypeDesc']."</b> Application of <b>".$Personnel->getEmpName($records['EmpID'])."</b>. From <b>".$records['LivAppIncDateFrom']." ".$records['LivAppIncDayTimeFrom']."</b> to <b>".$records['LivAppIncDateTo']." ".$records['LivAppIncDayTimeTo']."</b>, <b>".number_format($records['LivAppDays'],2)."</b> ";
					$LeaveDesc.=($records['LivAppDays']>1)?" days.":" day.";
						
				?>
					<a href="#" class="notification_item" onClick="viewRecordPLCT('<?= $EmpID; ?>','L'); showNotifications(); return false;"><table class="ui-widget-content ui-corner-all" style="width:280px;padding:0px;border-spacing:0px;margin:3px 0px 0px 0px;"><tr valign="center"><td width="20px" align="center"><span class='ui-icon ui-icon-calendar'></span></td><td><?php echo $LeaveDesc; ?></td></tr></table></a>
				<?php
				}
			}
		}
		
		/* COC/CTO Related Notifications --------------------------------------------------------------------------------------------------------------- */
		if($COCAuth[5]){
			/* */
			$MySQLi_COC=new MySQLClass();
			if($_SESSION['usergroup']=="USRGRP004"){ /* For Administrative officer user, get Office */
				$SRecOff=$MySQLi->GetArray("SELECT `AssignedOfficeID` FROM `tblempservicerecords` WHERE `EmpID`='".$_SESSION['user']."' AND `SRecIsGov`='YES' AND `SRecCurrentAppointment`=1;");
				$AssignedOfficeID=$SRecOff['AssignedOfficeID'];
				$sql="SELECT `tblempleaveapplications`.`EmpID`, `tblempleaveapplications`.`LivAppID`, `tblempleaveapplications`.`LeaveTypeID`, `tblempleaveapplications`.`LivAppDays`,DATE_FORMAT(`tblempleaveapplications`.`LivAppFiledDate`, '%Y-%m-%d') AS LivAppFiledDate,DATE_FORMAT(`tblempleaveapplications`.`LivAppIncDateFrom`, '%b %d, %Y') AS LivAppIncDateFrom,`tblempleaveapplications`.`LivAppIncDayTimeFrom`,DATE_FORMAT(`tblempleaveapplications`.`LivAppIncDateTo`, '%b %d, %Y') AS LivAppIncDateTo,`tblempleaveapplications`.`LivAppIncDayTimeTo`,`tblempleaveapplications`.`LivAppStatus`,`tblleavetypes`.`LeaveTypeDesc`, `tblempleaveapplications`.`RECORD_TIME` FROM `tblempleaveapplications` JOIN `tblleavetypes` ON `tblempleaveapplications`.`LeaveTypeID`=`tblleavetypes`.`LeaveTypeID` WHERE (`tblempleaveapplications`.`LivAppStatus`='1') AND (`tblempleaveapplications`.`LeaveTypeID`='LT08') AND `tblempleaveapplications`.`EmpID` IN (SELECT `EmpID` FROM `tblempservicerecords` WHERE `AssignedOfficeID`='$AssignedOfficeID' AND `SRecIsGov`='YES' AND `SRecCurrentAppointment`=1) ORDER BY `LivAppFiledDate` DESC;";
				$sql_coc="SELECT `COCID`, `EmpID`, `COCStatus`, `COCEarnedHours`, DATE_FORMAT(`COCEarnedDate`,'%b %d, %Y') AS COCEarnedDate FROM `tblempcocs` WHERE `COCStatus`='1' AND `EmpID` IN (SELECT `EmpID` FROM `tblempservicerecords` WHERE `AssignedOfficeID`='$AssignedOfficeID' AND `SRecIsGov`='YES' AND `SRecCurrentAppointment`=1) ORDER BY `COCEarnedDate` DESC;";
			}
			else{
				$sql="SELECT `tblempleaveapplications`.`EmpID`, `tblempleaveapplications`.`LivAppID`, `tblempleaveapplications`.`LeaveTypeID`, `tblempleaveapplications`.`LivAppDays`,DATE_FORMAT(`tblempleaveapplications`.`LivAppFiledDate`, '%Y-%m-%d') AS LivAppFiledDate,DATE_FORMAT(`tblempleaveapplications`.`LivAppIncDateFrom`, '%b %d, %Y') AS LivAppIncDateFrom,`tblempleaveapplications`.`LivAppIncDayTimeFrom`,DATE_FORMAT(`tblempleaveapplications`.`LivAppIncDateTo`, '%b %d, %Y') AS LivAppIncDateTo,`tblempleaveapplications`.`LivAppIncDayTimeTo`,`tblempleaveapplications`.`LivAppStatus`,`tblleavetypes`.`LeaveTypeDesc`, `tblempleaveapplications`.`RECORD_TIME` FROM `tblempleaveapplications` JOIN `tblleavetypes` ON `tblempleaveapplications`.`LeaveTypeID`=`tblleavetypes`.`LeaveTypeID` WHERE (`tblempleaveapplications`.`LivAppStatus`='1') AND (`tblempleaveapplications`.`LeaveTypeID`='LT08') ORDER BY `LivAppFiledDate` DESC;";
				$sql_coc="SELECT `COCID`, `EmpID`, `COCStatus`, `COCEarnedHours`, DATE_FORMAT(`COCEarnedDate`,'%b %d, %Y') AS COCEarnedDate FROM `tblempcocs` WHERE `COCStatus`='1' ORDER BY `COCEarnedDate` DESC;";
			}
			
			if($getNotificationOnly){
				if(($MySQLi->NumberOfRows($sql)>0)||($MySQLi_COC->NumberOfRows($sql_coc)>0)){
					$Notify=true;
					$records=$MySQLi->GetArray($sql);
					if(strtotime($records['RECORD_TIME'])>(strtotime($TIMESTAMP)-30)){$NotifVal=1;}
				}
			}
			else{
				$result=$MySQLi->sqlQuery($sql);
				while($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
					$EmpID=$records['EmpID'];
					$LeaveDesc="<b>".$records['LeaveTypeDesc']."</b> Application of <b>".$Personnel->getEmpName($records['EmpID'])."</b>. From <b>".$records['LivAppIncDateFrom']." ".$records['LivAppIncDayTimeFrom']."</b> to <b>".$records['LivAppIncDateTo']." ".$records['LivAppIncDayTimeTo']."</b>, <b>".number_format($records['LivAppDays'],2)."</b> ";
					$LeaveDesc.=($records['LivAppDays']>1)?" days.":" day.";
						
				?>
					<a href="#" class="notification_item" onClick="viewRecordPLCT('<?php echo $EmpID; ?>','C'); showNotifications(); return false;"><table class="ui-widget-content ui-corner-all" style="width:280px;padding:0px;border-spacing:0px;margin:3px 0px 0px 0px;"><tr valign="center"><td width="20px" align="center"><span class='ui-icon ui-icon-calendar'></span></td><td><?php echo $LeaveDesc; ?></td></tr></table></a>
				<?php
				}
				
				$result=$MySQLi_COC->sqlQuery($sql_coc);
				while($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
					$EmpID=$records['EmpID'];
					$COCDesc="Confirmation/Verification of <b>".number_format($records['COCEarnedHours'],2)."</b> ".(($records['COCEarnedHours']>1)?" hours":" hour")." <b>COC</b> for <b>".$Personnel->getEmpName($records['EmpID'])."</b> on <b>".$records['COCEarnedDate']."</b>.";
						
				?>
					<a href="#" class="notification_item" onClick="showCOCs('<?php echo $EmpID."','".$records['COCID']; ?>'); showNotifications(); return false;"><table class="ui-widget-content ui-corner-all" style="width:280px;padding:0px;border-spacing:0px;margin:3px 0px 0px 0px;"><tr valign="center"><td width="20px" align="center"><span class='ui-icon ui-icon-calendar'></span></td><td><?php echo $COCDesc; ?></td></tr></table></a>
				<?php
				}
				
			}
		}
		
		if($COCAuth[6]){
			$MySQLi_COC=new MySQLClass();
			$sql="SELECT `tblempleaveapplications`.`EmpID`, `tblempleaveapplications`.`LivAppID`, `tblempleaveapplications`.`LeaveTypeID`, `tblempleaveapplications`.`LivAppDays`,DATE_FORMAT(`tblempleaveapplications`.`LivAppFiledDate`, '%Y-%m-%d') AS LivAppFiledDate,DATE_FORMAT(`tblempleaveapplications`.`LivAppIncDateFrom`, '%b %d, %Y') AS LivAppIncDateFrom,`tblempleaveapplications`.`LivAppIncDayTimeFrom`,DATE_FORMAT(`tblempleaveapplications`.`LivAppIncDateTo`, '%b %d, %Y') AS LivAppIncDateTo,`tblempleaveapplications`.`LivAppIncDayTimeTo`,`tblempleaveapplications`.`LivAppStatus`,`tblleavetypes`.`LeaveTypeDesc`, `tblempleaveapplications`.`RECORD_TIME` FROM `tblempleaveapplications` JOIN `tblleavetypes` ON `tblempleaveapplications`.`LeaveTypeID`=`tblleavetypes`.`LeaveTypeID` WHERE (`tblempleaveapplications`.`LivAppStatus`='2') AND (`tblempleaveapplications`.`LeaveTypeID`='LT08') ORDER BY `LivAppFiledDate` DESC;";
			$sql_coc="SELECT `COCID`, `EmpID`, `COCStatus`, `COCEarnedHours`, DATE_FORMAT(`COCEarnedDate`,'%b %d, %Y') AS COCEarnedDate FROM `tblempcocs` WHERE `COCStatus`='2' ORDER BY `COCEarnedDate` DESC;";
			
			if($getNotificationOnly){
				if(($MySQLi->NumberOfRows($sql)>0)||($MySQLi_COC->NumberOfRows($sql_coc)>0)){
					$Notify=true;
					$records=$MySQLi->GetArray($sql);
					if(strtotime($records['RECORD_TIME'])>(strtotime($TIMESTAMP)-30)){$NotifVal=1;}
				}
			}
			else{
				$result=$MySQLi->sqlQuery($sql);
				while($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
					$EmpID=$records['EmpID'];
					switch ($records['LivAppStatus']){case '0':$leavestatus="NEW";break;case '1':$leavestatus="POSTED";break;case '2':$leavestatus="NOTED";break;case '3':$leavestatus="CHECKED";break;case '4':$leavestatus="APPROVED";break;default: $leavestatus="DISAPPROVED";break;}
					$LeaveDesc="<b>".$records['LeaveTypeDesc']."</b> Application of <b>".$Personnel->getEmpName($records['EmpID'])."</b>. From <b>".$records['LivAppIncDateFrom']." ".$records['LivAppIncDayTimeFrom']."</b> to <b>".$records['LivAppIncDateTo']." ".$records['LivAppIncDayTimeTo']."</b>, <b>".number_format($records['LivAppDays'],2)."</b> ";
					$LeaveDesc.=($records['LivAppDays']>1)?" days.":" day.";
						
				?>
					<a href="#" class="notification_item" onClick="viewRecordPLCT('<?php echo $EmpID; ?>','C'); showNotifications(); return false;"><table class="ui-widget-content ui-corner-all" style="width:280px;padding:0px;border-spacing:0px;margin:3px 0px 0px 0px;"><tr valign="center"><td width="20px" align="center"><span class='ui-icon ui-icon-calendar'></span></td><td><?php echo $LeaveDesc; ?></td></tr></table></a>
				<?php
				}
				
				$result=$MySQLi_COC->sqlQuery($sql_coc);
				while($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
					$EmpID=$records['EmpID'];
					$COCDesc="Confirmation/Verification of <b>".number_format($records['COCEarnedHours'],2)."</b> ".(($records['COCEarnedHours']>1)?" hours":" hour")." <b>COC</b> for <b>".$Personnel->getEmpName($records['EmpID'])."</b> on <b>".$records['COCEarnedDate']."</b>.";
						
				?>
					<a href="#" class="notification_item" onClick="showCOCs('<?php echo $EmpID."','".$records['COCID']; ?>'); showNotifications(); return false;"><table class="ui-widget-content ui-corner-all" style="width:280px;padding:0px;border-spacing:0px;margin:3px 0px 0px 0px;"><tr valign="center"><td width="20px" align="center"><span class='ui-icon ui-icon-calendar'></span></td><td><?php echo $COCDesc; ?></td></tr></table></a>
				<?php
				}
			}
		}
		
		if($COCAuth[7]){
			$MySQLi_COC=new MySQLClass();
			$sql="SELECT `tblempleaveapplications`.`EmpID`, `tblempleaveapplications`.`LivAppID`, `tblempleaveapplications`.`LeaveTypeID`, `tblempleaveapplications`.`LivAppDays`,DATE_FORMAT(`tblempleaveapplications`.`LivAppFiledDate`, '%Y-%m-%d') AS LivAppFiledDate,DATE_FORMAT(`tblempleaveapplications`.`LivAppIncDateFrom`, '%b %d, %Y') AS LivAppIncDateFrom,`tblempleaveapplications`.`LivAppIncDayTimeFrom`,DATE_FORMAT(`tblempleaveapplications`.`LivAppIncDateTo`, '%b %d, %Y') AS LivAppIncDateTo,`tblempleaveapplications`.`LivAppIncDayTimeTo`,`tblempleaveapplications`.`LivAppStatus`,`tblleavetypes`.`LeaveTypeDesc`, `tblempleaveapplications`.`RECORD_TIME` FROM `tblempleaveapplications` JOIN `tblleavetypes` ON `tblempleaveapplications`.`LeaveTypeID`=`tblleavetypes`.`LeaveTypeID` WHERE (`tblempleaveapplications`.`LivAppStatus`='3') AND (`tblempleaveapplications`.`LeaveTypeID`='LT08') ORDER BY `LivAppFiledDate` DESC;";
			$sql_coc="SELECT `COCID`, `EmpID`, `COCStatus`, `COCEarnedHours`, DATE_FORMAT(`COCEarnedDate`,'%b %d, %Y') AS COCEarnedDate FROM `tblempcocs` WHERE `COCStatus`='3' ORDER BY `COCEarnedDate` DESC;";
			
			if($getNotificationOnly){
				if($MySQLi->NumberOfRows($sql)>0){
					$Notify=true;
					$records=$MySQLi->GetArray($sql);
					if(strtotime($records['RECORD_TIME'])>(strtotime($TIMESTAMP)-30)){$NotifVal=1;}
				}
			}
			else{
				$result=$MySQLi->sqlQuery($sql);
				while($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
					$EmpID=$records['EmpID'];
					switch ($records['LivAppStatus']){case '0':$leavestatus="NEW";break;case '1':$leavestatus="POSTED";break;case '2':$leavestatus="NOTED";break;case '3':$leavestatus="CHECKED";break;case '4':$leavestatus="APPROVED";break;default: $leavestatus="DISAPPROVED";break;}
					$LeaveDesc="<b>".$records['LeaveTypeDesc']."</b> Application of <b>".$Personnel->getEmpName($records['EmpID'])."</b>. From <b>".$records['LivAppIncDateFrom']." ".$records['LivAppIncDayTimeFrom']."</b> to <b>".$records['LivAppIncDateTo']." ".$records['LivAppIncDayTimeTo']."</b>, <b>".number_format($records['LivAppDays'],2)."</b> ";
					$LeaveDesc.=($records['LivAppDays']>1)?" days.":" day.";
						
				?>
					<a href="#" class="notification_item" onClick="viewRecordPLCT('<?php echo $EmpID; ?>','C'); showNotifications(); return false;"><table class="ui-widget-content ui-corner-all" style="width:280px;padding:0px;border-spacing:0px;margin:3px 0px 0px 0px;"><tr valign="center"><td width="20px" align="center"><span class='ui-icon ui-icon-calendar'></span></td><td><?php echo $LeaveDesc; ?></td></tr></table></a>
				<?php
				}
				
				$result=$MySQLi_COC->sqlQuery($sql_coc);
				while($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
					$EmpID=$records['EmpID'];
					$COCDesc="Confirmation/Verification of <b>".number_format($records['COCEarnedHours'],2)."</b> ".(($records['COCEarnedHours']>1)?" hours":" hour")." <b>COC</b> for <b>".$Personnel->getEmpName($records['EmpID'])."</b> on <b>".$records['COCEarnedDate']."</b>.";
						
				?>
					<a href="#" class="notification_item" onClick="showCOCs('<?php echo $EmpID."','".$records['COCID']; ?>'); showNotifications(); return false;"><table class="ui-widget-content ui-corner-all" style="width:280px;padding:0px;border-spacing:0px;margin:3px 0px 0px 0px;"><tr valign="center"><td width="20px" align="center"><span class='ui-icon ui-icon-calendar'></span></td><td><?php echo $COCDesc; ?></td></tr></table></a>
				<?php
				}
			}
		}
		
		/* Travel Order Related Notifications --------------------------------------------------------------------------------------------------------------- */
		if($TravAuth[5]){
			if($_SESSION['usergroup']=="USRGRP004"){ /* For Administrative officer user, get Office */
				$SRecOff=$MySQLi->GetArray("SELECT `AssignedOfficeID` FROM `tblempservicerecords` WHERE `EmpID`='".$_SESSION['user']."' AND `SRecIsGov`='YES' AND `SRecCurrentAppointment`=1;");
				$AssignedOfficeID=$SRecOff['AssignedOfficeID'];
				$sql="SELECT `tblemptravelorders`.`EmpID`, `tbltravelorders`.`TOID`, DATE_FORMAT(`tbltravelorders`.`TOIncDateFrom`, '%Y-%m-%d') AS TODateFrom, `tbltravelorders`.`TOIncDayTimeFrom`, DATE_FORMAT(`tbltravelorders`.`TOIncDateTo`, '%Y-%m-%d') AS TODateTo, `tbltravelorders`.`TOIncDayTimeTo`, `tbltravelorders`.`TODestination`, `tbltravelorders`.`TOStatus`, `tbltravelorders`.`RECORD_TIME` FROM `tbltravelorders` JOIN `tblemptravelorders` ON `tbltravelorders`.`TOID` = `tblemptravelorders`.`TOID` WHERE `tbltravelorders`.`TOStatus`='1' AND `tblemptravelorders`.`TONotedBy`='' AND `tblemptravelorders`.`TONotedTime`='1970-01-01 00:00:01' AND `tblemptravelorders`.`EmpID` IN (SELECT `EmpID` FROM `tblempservicerecords` WHERE `AssignedOfficeID`='$AssignedOfficeID' AND `SRecIsGov`='YES' AND `SRecCurrentAppointment`=1);";
			}
			else{
				$sql="SELECT `TOID`, DATE_FORMAT(`TOIncDateFrom`, '%Y-%m-%d') AS TODateFrom, `TOIncDayTimeFrom`, DATE_FORMAT(`TOIncDateTo`, '%Y-%m-%d') AS TODateTo, `TOIncDayTimeTo`, `TODestination`, `tbltravelorders`.`TOStatus`, `tbltravelorders`.`RECORD_TIME` FROM `tbltravelorders` WHERE `tbltravelorders`.`TOStatus`='1';";
			}
			
			if($getNotificationOnly){
				if($MySQLi->NumberOfRows($sql)>0){
					$Notify=true;
					$records=$MySQLi->GetArray($sql);
					if(strtotime($records['RECORD_TIME'])>(strtotime($TIMESTAMP)-30)){$NotifVal=1;}
				}
			}
			else{
				$result=$MySQLi->sqlQuery($sql);
				while($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
					$EmpID=$records['EmpID'];
					switch ($records['TOStatus']){case '0':$tostatus="NEW";break;case '1':$tostatus="POSTED";break;case '2':$tostatus="NOTED";break;case '3':$tostatus="CHECKED";break;case '4':$tostatus="APPROVED";break;default: $tostatus="DISAPPROVED";break;}
					$TODesc="<b>TRAVEL ORDER</b> to <b>".$records['TODestination']."</b> From <b>".$records['TODateFrom']." ".$records['TOIncDayTimeFrom']."</b> to <b>".$records['TODateTo']." ".$records['TOIncDayTimeTo']."</b>. ";
					$TODesc.="";
						
			?>
					<a href="#" class="notification_item" onClick="showTravelOrder('<?php echo $records['TOID']; ?>'); showNotifications(); return false;">
						<table class="ui-widget-content ui-corner-all" style="width:280px;padding:0px;border-spacing:0px;margin:3px 0px 0px 0px;">
							<tr valign="center">
								<td width="20px" align="center"><span class='ui-icon ui-icon-image'></span></td>
								<td><?php echo $TODesc; ?></td>
							</tr>
						</table>
					</a>
			<?php
				}
			}
		}
		
		if($TravAuth[6]){
			$sql="SELECT `TOID`, DATE_FORMAT(`TOIncDateFrom`, '%Y-%m-%d') AS TODateFrom, `TOIncDayTimeFrom`, DATE_FORMAT(`TOIncDateTo`, '%Y-%m-%d') AS TODateTo, `TOIncDayTimeTo`, `TODestination`, `tbltravelorders`.`TOStatus`, `tbltravelorders`.`RECORD_TIME` FROM `tbltravelorders` WHERE `tbltravelorders`.`TOStatus`='2';";
			
			if($getNotificationOnly){
				if($MySQLi->NumberOfRows($sql)>0){
					$Notify=true;
					$records=$MySQLi->GetArray($sql);
					if(strtotime($records['RECORD_TIME'])>(strtotime($TIMESTAMP)-30)){$NotifVal=1;}
				}
			}
			else{
				$result=$MySQLi->sqlQuery($sql);
				while($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
					$EmpID=$records['EmpID'];
					switch ($records['TOStatus']){case '0':$tostatus="NEW";break;case '1':$tostatus="POSTED";break;case '2':$tostatus="NOTED";break;case '3':$tostatus="CHECKED";break;case '4':$tostatus="APPROVED";break;default: $tostatus="DISAPPROVED";break;}
					$TODesc="<b>TRAVEL ORDER</b> to <b>".$records['TODestination']."</b> From <b>".$records['TODateFrom']." ".$records['TOIncDayTimeFrom']."</b> to <b>".$records['TODateTo']." ".$records['TOIncDayTimeTo']."</b>. ";
					$TODesc.="";
						
			?>
					<a href="#" class="notification_item" onClick="showTravelOrder('<?php echo $records['TOID']; ?>'); showNotifications(); return false;">
						<table class="ui-widget-content ui-corner-all" style="width:280px;padding:0px;border-spacing:0px;margin:3px 0px 0px 0px;">
							<tr valign="center">
								<td width="20px" align="center"><span class='ui-icon ui-icon-image'></span></td>
								<td><?php echo $TODesc; ?></td>
							</tr>
						</table>
					</a>
			<?php
				}
			}
		}
		
		if($TravAuth[7]){
			$sql="SELECT `TOID`, DATE_FORMAT(`TOIncDateFrom`, '%Y-%m-%d') AS TODateFrom, `TOIncDayTimeFrom`, DATE_FORMAT(`TOIncDateTo`, '%Y-%m-%d') AS TODateTo, `TOIncDayTimeTo`, `TODestination`, `tbltravelorders`.`TOStatus`, `tbltravelorders`.`RECORD_TIME` FROM `tbltravelorders` WHERE `tbltravelorders`.`TOStatus`='3';";
			
			if($getNotificationOnly){
				if($MySQLi->NumberOfRows($sql)>0){
					$Notify=true;
					$records=$MySQLi->GetArray($sql);
					if(strtotime($records['RECORD_TIME'])>(strtotime($TIMESTAMP)-30)){$NotifVal=1;}
				}
			}
			else{
				$result=$MySQLi->sqlQuery($sql);
				while($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
					$EmpID=$records['EmpID'];
					switch ($records['TOStatus']){case '0':$tostatus="NEW";break;case '1':$tostatus="POSTED";break;case '2':$tostatus="NOTED";break;case '3':$tostatus="CHECKED";break;case '4':$tostatus="APPROVED";break;default: $tostatus="DISAPPROVED";break;}
					$TODesc="<b>TRAVEL ORDER</b> to <b>".$records['TODestination']."</b> From <b>".$records['TODateFrom']." ".$records['TOIncDayTimeFrom']."</b> to <b>".$records['TODateTo']." ".$records['TOIncDayTimeTo']."</b>. ";
					$TODesc.="";
						
			?>
					<a href="#" class="notification_item" onClick="showTravelOrder('<?php echo $records['TOID']; ?>'); showNotifications(); return false;">
						<table class="ui-widget-content ui-corner-all" style="width:280px;padding:0px;border-spacing:0px;margin:3px 0px 0px 0px;">
							<tr valign="center">
								<td width="20px" align="center"><span class='ui-icon ui-icon-image'></span></td>
								<td><?php echo $TODesc; ?></td>
							</tr>
						</table>
					</a>
			<?php
				}
			}
		}
		
		/* Personnel Locator Related Notifications -------------------------------------------------------------------------------------------------------------- */
	


		
		if(!$getNotificationOnly){
			echo "</div>";
			?>
				<script type='text/javaScript'>
					function showCOCs(id,cid){
						epage="pcoc";t_eid=id;eid_t=cid;
						ajaxGetEmp(id,'1');
					}
				</script>
			<?php
		}
		else{
			if($Notify){$NotifVal=$NotifVal<0?0:1;echo "1|".$_SESSION['user']."||$NotifVal";}
			else{echo "1|".$_SESSION['user']."||$NotifVal";}
		}
	}
	else{echo "1|".$_SESSION['user']."||-1";}
?>