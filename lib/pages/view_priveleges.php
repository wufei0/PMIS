<?php
	ob_start();
	session_start();
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD002'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	
	

	$UserID=isset($_POST['uid'])?trim(strip_tags($_POST['uid'])):"0";
	$UpdUsrGrp=isset($_POST['uug'])?trim(strip_tags($_POST['uug'])):"0";
	$UserGroupID=isset($_POST['gid'])?trim(strip_tags($_POST['gid'])):"USRGRP000";
	$isReadOnly=isset($_POST['ro'])?trim(strip_tags($_POST['ro'])):'1';
	
	$isReadOnly=($isReadOnly==1)?true:false;
	$ReadOnly=($isReadOnly)?"disabled":"";
	
	$UpdUsrGrp=($UpdUsrGrp==1)?true:false;
	
	function hex2bin_($hx){$b="";$h=str_split($hx);foreach($h as $_b16){switch($_b16){case"0":$b.="0000";break;case"1":$b.="0001";break;case"2":$b.="0010";break;case"3":$b.="0011";break;case"4":$b.="0100";break;case"5":$b.="0101";break;case"6":$b.="0110";break;case"7":$b.="0111";break;case"8":$b.="1000";break;case"9":$b.="1001";break;case"A":$b.="1010";break;case"B":$b.="1011";break;case"C":$b.="1100";break;case"D":$b.="1101";break;case"E":$b.="1110";break;case"F":$b.="1111";break;}}return $b;}
	
	echo "1|$UserID|";
	?> <form name="user_priveleges" id="user_priveleges" onSubmit="processPriveleges(this); return false;"><?php
	
	$MySQLi=new MySQLClass();
	
	if(!$UpdUsrGrp){
		$sql="SELECT `EmpID`, CONCAT_WS(', ',`EmpLName`, CONCAT_WS(' ',`EmpFName`, CONCAT_WS('.', SUBSTRING(`EmpMName`, 1, 1), ''))) AS EmpName, `UserGroupID` FROM `tblemppersonalinfo` WHERE `EmpID`='$UserID' LIMIT 1;";
		if(!$usrinfo=$MySQLi->GetArray($sql)){$usrinfo['EmpID']=$usrinfo['EmpName']=$usrinfo['UserGroupID']="";}
		if($isReadOnly){$UserGroupID=$usrinfo['UserGroupID'];}
	}
	?>
	<!-- User small info -->
	<div style="background:#E6EFFF;width:auto;margin-bottom:4px;<?php if($UpdUsrGrp){echo"display:none;";} ?>" id="user_div" class="ui-widget ui-widget-content ui-corner-all" >
		<table style="border-spacing:0px;border:0px solid #6D84B4;width:450px;">
			<tr>
				<td class="form_label" style="border-left:0px solid #6D84B4;padding:3px 3px 3px 3px;width:65px"><label>USER NAME:</label></td>	
				<td colspan="3" style="padding:3px 0px 0px 0px;"><input value="<?php echo $usrinfo['EmpName']; ?>" name="UserName" id="UserName" class="text_input" style="width:375px" disabled type="text"></td>
			</tr>
			<tr>
				<td class="form_label" style="border-left:0px solid #6D84B4;padding:3px 3px 3px 3px;width:65px"><label>USER ID:</label></td>	
				<td style="padding:3px 0px 3px 0px;"><input value="<?php echo $usrinfo['EmpID']; ?>" name="UserID" id="UserID" class="text_input" style="width:40px" readonly type="text"></td>
				
				<td class="form_label" style="border-left:0px solid #6D84B4;padding:3px 3px 3px 3px;width:70px"><label>USER GROUP:</label></td>
				<td style="padding:3px 0px 3px 0px;width:200px">
					<select name="UserGroup" id="UserGroup" class="text_input search_select" style="width:200px" onChange="showPriveleges('<?php echo $usrinfo['EmpID']; ?>',this.value,0);" <?php echo $ReadOnly; ?>>
						<?php
							$result=$MySQLi->sqlQuery("SELECT `UserGroupID`,`UserGroupName` FROM `tblsystemusergroups` ORDER BY `UserGroupName`;");
							while($usrgrps=mysqli_fetch_array($result, MYSQLI_BOTH)){
								if($UserGroupID==$usrgrps['UserGroupID']){echo "<option value='".$usrgrps['UserGroupID']."' selected>".$usrgrps['UserGroupName']."</option>";}
								else{echo "<option value='".$usrgrps['UserGroupID']."'>".$usrgrps['UserGroupName']."</option>";}
							} unset($result);
						?>
					</select>
				</td>
			</tr>
		</table>
	</div>
	<?php

	$sql ="SELECT `UserGroupCode`, `UserGroupName` FROM `tblsystemusergroups` WHERE `UserGroupID` = '$UserGroupID'";
	if(!$grpinfo=$MySQLi->GetArray($sql)){$grpinfo['UserGroupCode']=$grpinfo['UserGroupName']="";}
	
	?>
		<!-- Group small info -->
		<div style="background:#E6EFFF;width:auto;margin-bottom:4px;<?php if(!$UpdUsrGrp){echo"display:none;";} ?>" id="user_group_div" class="ui-widget ui-widget-content ui-corner-all" >
			<table style="border-spacing:0px;border:0px solid #6D84B4;width:450px;">
				<tr>
					<td class="form_label" style="border-left:0px solid #6D84B4;padding:3px 3px 3px 3px;width:80px"><label>GROUP ID:</label></td>	
					<td style="padding:3px 0px 0px 0px;"><input value="<?php echo $UserGroupID; ?>" name="GroupID" id="GroupID" class="text_input" style="width:150px" disabled type="text"/></td>
					<td class="form_label" style="border-left:0px solid #6D84B4;padding:3px 3px 3px 3px;width:80px"><label>CODE:</label></td>
					<td style="padding:3px 0px 0px 0px;width:100px"><input value="<?php echo $grpinfo['UserGroupCode']; ?>" name="GroupCode" id="GroupCode" class="text_input" style="width:100px" <?php echo $ReadOnly; ?> type="text"/></td>
				</tr>
				<tr>
					<td class="form_label" style="border-left:0px solid #6D84B4;padding:3px 3px 3px 3px;width:100px"><label>GROUP NAME:</label></td>	
					<td colspan="3" style="padding:3px 0px 3px 0px;"><input value="<?php echo $grpinfo['UserGroupName']; ?>" name="GroupName" id="GroupName" class="text_input" style="width:360px" <?php echo $ReadOnly; ?> type="text"/></td>
				</tr>
			</table>
		</div>
	<?php
	

	$sql="SELECT `UserGroupID`,`UserGroupAccess` FROM `tblsystemusergroups` WHERE `UserGroupID`='$UserGroupID';";
	if($MySQLi->NumberOfRows($sql)==0){echo "Awan, padasem jay sabali....<br/>SQL= $sql";exit;}
	$usraccss=$MySQLi->GetArray($sql);
	$usr_access=explode(":",$usraccss['UserGroupAccess']);
	
	$privelege=array();
	$_i=0;
	foreach($usr_access as $hex){
		$bin=hex2bin_($hex);
		$module_access=str_split($bin);
		$MOD=($_i>99)?"MOD".$_i:($_i>9)?"MOD0".$_i:"MOD00".$_i;
		$privelege[$MOD]['O']=$module_access[0];
		$privelege[$MOD]['R']=$module_access[1];
		$privelege[$MOD]['W']=$module_access[2];
		$privelege[$MOD]['D']=$module_access[3];
		$privelege[$MOD]['P']=$module_access[4];
		$privelege[$MOD]['N']=$module_access[5];
		$privelege[$MOD]['C']=$module_access[6];
		$privelege[$MOD]['A']=$module_access[7];
		$_i+=1;
	}

?>
	<table style="border-spacing:0px;border:1px solid #6D84B4;width:460px;">
		<tr><td class="search_header" style="text-align:center;">MODULE</td><td class="search_header" style="text-align:center;width:180px;">Privelege</td></tr>
	</table>
	<table style="border-spacing:0px;border:0px solid #6D84B4;width:460px;">
		<tr><td class="search_result" style="text-align:center;border-left:1px solid #6D84B4;"></td>
			<td class="search_result chkbox" title="All Record">O</td>
			<td class="search_result chkbox" title="Read">R</td>
			<td class="search_result chkbox" title="Write">W</td>
			<td class="search_result chkbox" title="Delete">D</td>
			<td class="search_result chkbox" title="Post">P</td>
			<td class="search_result chkbox" title="Note">N</td>
			<td class="search_result chkbox" title="Check">C</td>
			<td class="search_result chkbox" title="Approve">A</td>
			<td class="search_result chkbox" style="border-right:1px solid #6D84B4;">&nbsp;</td>
		</tr>
	</table>
				
	<div class="" style="border:1px solid #6D84B4;width:458px;height:330px;overflow:auto;">			
	<table style="border-spacing:0px;border:0px solid #6D84B4;width:438px;">
<?php
	
	
	
	$ticked="";
	
	$sql="SELECT `UserGroupID`,`UserGroupAccess` FROM `tblsystemusergroups` WHERE `UserGroupID`='$UserGroupID';";
	if($MySQLi->NumberOfRows($sql)==0){echo "Awan, padasem jay sabali....<br/>SQL= $sql";exit;}
	$usraccss=$MySQLi->GetArray($sql);
	$usr_access=explode(":",$usraccss['UserGroupAccess']);
	
	$ModuleCategory="-1";
	$result=$MySQLi->sqlQuery("SELECT `ModuleID`,`ModuleName`,`ModuleIndex`,`ModuleCategory` FROM `tblsystemmodules` ORDER BY `ModuleCategory` ASC, `ModuleID` ASC;");
	while($modules=mysqli_fetch_array($result, MYSQLI_BOTH)){
		if($modules['ModuleCategory']!=$ModuleCategory){
			if($modules['ModuleCategory']=="0"){echo "<tr><td colspan='9' style='padding:7px 0px 0px 3px;font-weight:bold;font-size:1.1em;'>SYSTEM ADMINISTRATION</td></tr>";}
			else if($modules['ModuleCategory']=="1"){echo "<tr><td colspan='9' style='padding:7px 0px 0px 3px;font-weight:bold;font-size:1.1em;'>PERSONAL DATA SHEET (PDS)</td></tr>";}
			else if($modules['ModuleCategory']=="2"){echo "<tr><td colspan='9' style='padding:7px 0px 0px 3px;font-weight:bold;font-size:1.1em;'>PERSONNEL ATTENDANCE MONITORING</td></tr>";}
			$ModuleCategory=$modules['ModuleCategory'];
		}
		
		$ModIndex=($modules['ModuleIndex']>9)?"M".$modules['ModuleIndex']:"M0".$modules['ModuleIndex'];
		$hex=$usr_access[$modules['ModuleIndex']];
		$bin=hex2bin_($hex);
		$module_access=str_split($bin);
		
		echo "<tr class='search_result_row_1'><td class='search_result' style='padding:0px 0px 0px 9px;'>".$modules['ModuleName']."</td>";
		$ticked=($module_access[0]==1)?"checked":"";	/*	Overall		*/
		echo "<td class='search_result chkbox'><input id='".$modules['ModuleID']."O"."' name='".$ModIndex."' type='checkbox' $ticked $ReadOnly title='Overall'/></td>";
		$ticked=($module_access[1]==1)?"checked":"";	/*	Read			*/
		echo "<td class='search_result chkbox'><input id='".$modules['ModuleID']."R"."' name='".$ModIndex."' type='checkbox' $ticked $ReadOnly title='Read'/></td>";
		$ticked=($module_access[2]==1)?"checked":"";	/*	Write			*/
		echo "<td class='search_result chkbox'><input id='".$modules['ModuleID']."W"."' name='".$ModIndex."' type='checkbox' $ticked $ReadOnly title='Write'/></td>";
		$ticked=($module_access[3]==1)?"checked":"";	/*	Delete		*/
		echo "<td class='search_result chkbox'><input id='".$modules['ModuleID']."D"."' name='".$ModIndex."' type='checkbox' $ticked $ReadOnly title='Delete'/></td>";
		$ticked=($module_access[4]==1)?"checked":"";	/*	Post			*/
		echo "<td class='search_result chkbox'><input id='".$modules['ModuleID']."P"."' name='".$ModIndex."' type='checkbox' $ticked $ReadOnly title='Post'/></td>";
		$ticked=($module_access[5]==1)?"checked":"";	/*	Note			*/
		echo "<td class='search_result chkbox'><input id='".$modules['ModuleID']."N"."' name='".$ModIndex."' type='checkbox' $ticked $ReadOnly title='Note'/></td>";
		$ticked=($module_access[6]==1)?"checked":"";	/*	Check			*/
		echo "<td class='search_result chkbox'><input id='".$modules['ModuleID']."C"."' name='".$ModIndex."' type='checkbox' $ticked $ReadOnly title='Check'/></td>";
		$ticked=($module_access[7]==1)?"checked":"";	/*	Approve		*/
		echo "<td class='search_result chkbox'><input id='".$modules['ModuleID']."A"."' name='".$ModIndex."' type='checkbox' $ticked $ReadOnly title='Approve'/></td>";
	} unset($result);

?>
	</table>
	</div>
	</form>
