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
	


	$EmpID=isset($_POST['uid'])?trim(strip_tags($_POST['uid'])):'00000';
	$mode=isset($_POST['mode'])?trim(strip_tags($_POST['mode'])):'0';
	
	echo "1|$EmpID|";
	
	$MONTHS=Array('','JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC');
	
	if($EmpID!='00000'){
		$MySQLi=new MySQLClass();
		$records=Array();
		$sql="SELECT `EmpID`, `EmpName`, `CGrpID` FROM `s_personnel` WHERE `EmpID`='$EmpID' LIMIT 1;";
		$records=$MySQLi->GetArray($sql);
?>

<center>
	<form name="f_pers_cgroup" onSubmit="processPersCGroup(this);return false;"><br/>
		<table class="form_window">
			<tr>
				<td class="form_label" style="width:200px;"><label>ID NUMBER: </label></td>
				<td class="pds_form_input" colspan="2"><input value="<?php echo $records['EmpID']; ?>" type="text" name="EmpID" id="EmpID" class="text_input" style="width:40px" readonly /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>PERSONNEL: </label></td>
				<td class="pds_form_input" colspan="2"><input value="<?php echo $records['EmpName']; ?>" type="text" name="EmpName" id="EmpName" class="text_input sml_frm_fld" readonly /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>OFFICE/GROUP: </label></td>
				<td class="pds_form_input" colspan="2">
					<select name="CGrpID" id="CGrpID" class="text_input">
						<?php
							$result=$MySQLi->sqlQuery("SELECT `CGrpID`,`CGrpCode` FROM `tblempcgroups` WHERE `CGrpID` <> 'CG00' ORDER BY `CGrpCode`;");
							while($usrgrps=mysqli_fetch_array($result, MYSQLI_BOTH)) {
								echo "<option value='".$usrgrps['CGrpID']."' ".(($records['CGrpID']==$usrgrps['CGrpID'])?"selected":"").">".$usrgrps['CGrpCode']."</option>";
							} unset($result);
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
<?php 
	}
?>