<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD001'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[0]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	


	$UserID=isset($_POST['uid'])?trim(strip_tags($_POST['uid'])):'00000';
	$mode=isset($_POST['mode'])?trim(strip_tags($_POST['mode'])):'0';
	
	echo "1|$UserID|";
	
	$MONTHS=Array('','JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC');
	
	if($UserID!='00000'){
		$MySQLi=new MySQLClass();
		$records=Array();
		$InputState="";
		$result=$MySQLi->sqlQuery("SELECT `UserGroupID` FROM `tblemppersonalinfo` WHERE `EmpID`='".$_SESSION['user']."' LIMIT 1;");
		$usrgrpid=mysqli_fetch_array($result, MYSQLI_BOTH);
		if($mode!=0){
			$result=$MySQLi->sqlQuery("SELECT * FROM `tblemppersonalinfo` WHERE `EmpID`='".$UserID."' LIMIT 1;");
			$records=mysqli_fetch_array($result, MYSQLI_BOTH);
			if($mode==-1){$InputState="disabled";}
		}
		else{$records['EmpID']="";}

?>

<center>
	<form name="f_user_info" onSubmit="processUserInfo(this);return false;"><br/>
		<table class="form_window">
			<tr>
				<td class="form_label" style="width:200px;"><label>ID NUMBER: </label></td>
				<td class="pds_form_input" colspan="2"><input value="<?php echo $records['EmpID']; ?>" type="text" name="UsrID" id="UsrID" class="text_input" style="width:40px"<?php echo $InputState; ?> <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>PASSWORD: </label></td>
				<td class="pds_form_input"><input value="" type="password" name="NewKey1" id="NewKey1" class="text_input sml_frm_fld" <?php echo $InputState; ?> <?php echo $InputState; ?> /></td><td><ul class="ui-widget ui-helper-clearfix ul-icons"><li id="" class="ui-state-default ui-corner-all" title="Password must be atleast 6 characters." style="cursor:help;" onClick="showMessage(this.title);"><span class="ui-icon ui-icon-help"></span></li></ul></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>CONFIRM PASSWORD: </label></td>
				<td class="pds_form_input"><input value="" type="password" name="NewKey2" id="NewKey2" class="text_input sml_frm_fld" <?php echo $InputState; ?> <?php echo $InputState; ?> /></td><td><ul class="ui-widget ui-helper-clearfix ul-icons"><li id="" class="ui-state-default ui-corner-all" title="Retype new password." style="cursor:help;" onClick="showMessage(this.title);"><span class="ui-icon ui-icon-help"></span></li></ul></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>USER ACCESS GROUP: </label></td>
				<td class="pds_form_input" colspan="2">
					<select name="UsrGrpID" id="UsrGrpID" class="text_input">
						<?php
							if($usrgrpid['UserGroupID']=="USRGRP004"){echo "<option value='USRGRP003'>Employees</option>";}
							else{
								$result=$MySQLi->sqlQuery("SELECT `UserGroupID`,`UserGroupName` FROM `tblsystemusergroups` ORDER BY `UserGroupName`;");
								while($usrgrps=mysqli_fetch_array($result, MYSQLI_BOTH)) {
									echo "<option value='".$usrgrps['UserGroupID']."'>".$usrgrps['UserGroupName']."</option>";
								} unset($result);
							}
						?>
					</select>
				</td>
			</tr>
		</table>
		<br/>
		<hr class="form_bottom_line_window"/>
		<table class="form_window">
			<tr>
				<td align="left"><input type="button" value="Help" class="button ui-button ui-widget ui-corner-all" onClick="showHelp('001');return false;" /></td>
				<td align="right"><input type="submit" value="<?php if($mode==-1){echo'Confirm Delete';}else{echo'Save';} ?>" class="button ui-button ui-widget ui-corner-all"/>&nbsp;<input type="button" value="Cancel" class="button ui-button ui-widget ui-corner-all" onClick="closeDialogWindow('d_form_input');return false;" /></td>
			</tr>
		</table>
		<input type="hidden" name="mode" id="mode" value="<?php echo $mode; ?>" />
	</form>
</center>
<?php } ?>