<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD019'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	
	
	
	$EmpID=isset($_POST['id'])?trim(strip_tags($_POST['id'])):'00000';
	$TOID=isset($_POST['xid'])?trim(strip_tags($_POST['xid'])):'';
	$mode=isset($_POST['mode'])?trim(strip_tags($_POST['mode'])):'0';
	echo "1|$EmpID|";
	$MONTHS=Array('','JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC');
	
	if($EmpID!='00000'){
		$Config=new Conf();
		$MySQLi=new MySQLClass($Config);
		$records=Array();
		$InputState="";
		
		if($mode==0){
			$records['TOOutsideLU']=0;
			$records['TOSubject']=$records['TOBody']=$records['TODestination']=$records['TORemarks']="";
			$records['TODateToYear']=date('Y');$records['TODateToMonth']=date('m');$records['TODateToDay']=date('d');
			$records['TODateFrYear']=date('Y');$records['TODateFrMonth']=date('m');$records['TODateFrDay']=date('d');
		}
		else {
			$result=$MySQLi->sqlQuery("SELECT `TOIncDayTimeFrom`, DATE_FORMAT(`TOIncDateFrom`,'%d') AS TODateFrDay, DATE_FORMAT(`TOIncDateFrom`,'%m') AS TODateFrMonth, DATE_FORMAT(`TOIncDateFrom`,'%Y') AS TODateFrYear, `TOIncDayTimeTo`, DATE_FORMAT(`TOIncDateTo`,'%d') AS TODateToDay, DATE_FORMAT(`TOIncDateTo`,'%m') AS TODateToMonth, DATE_FORMAT(`TOIncDateTo`,'%Y') AS TODateToYear, `TOSubject`, `TOOutsideLU`, `TODestination`, `TOBody` FROM `tbltravelorders` WHERE `TOID`='".$TOID."' LIMIT 1;");
			$records=mysqli_fetch_array($result, MYSQLI_BOTH);
			if($mode==-1){$InputState="disabled";}
		}
?>

<center>
	<form name="f_dpnt_info" onSubmit="processForm('trav',this);return false;"><br/>
		<table class="form_window">	<?php //Spouse"s Name... ?>
			<tr>
				<td class="form_label" style="width:200px;"><label>INCLUSIVE DATE OF TRAVEL </label></td><td colspan="2">&nbsp;</td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>From:</label></td>
				<td class="pds_form_input" colspan="2">
				<select id="TODateFrMonth" name="TODateFrMonth" class="text_input" <?php echo $InputState; ?>>
					<?php for($m=1;$m<=12;$m++){if($m==intval($records['TODateFrMonth'])){echo "<option value='$m' selected>".$MONTHS[$m]."</option>";}else{echo "<option value='$m'>".$MONTHS[$m]."</option>";}} ?>
				</select>
				<select id="TODateFrDay" name="TODateFrDay" class="text_input" <?php echo $InputState; ?>>
					<?php for($d=1;$d<=31;$d++){if($d==intval($records['TODateFrDay'])){echo "<option value='$d' selected>$d</option>";}else{echo "<option value='$d'>$d</option>";}} ?>
				</select>
				<input type="text" name="TODateFrYear" id="TODateFrYear" class="text_input" <?php echo $InputState; ?> style="width:30px;" value="<?php echo $records['TODateFrYear']; ?>" />
				<select id="TOIncDayTimeFrom" name="TOIncDayTimeFrom" class="text_input" <?php echo $InputState; ?>>
					<?php if($records['TOIncDayTimeFrom']=='PM'){echo "<option value='AM'>AM</option><option value='PM' selected>PM</option>";}else{echo "<option value='AM' selected>AM</option><option value='PM'>PM</option>";} ?>
				</select></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>To:</label></td>
				<td class="pds_form_input" colspan="2">
				<select id="TODateToMonth" name="TODateToMonth" class="text_input" <?php echo $InputState; ?>>
					<?php for($m=1;$m<=12;$m++){if($m==intval($records['TODateToMonth'])){echo "<option value='$m' selected>".$MONTHS[$m]."</option>";}else{echo "<option value='$m'>".$MONTHS[$m]."</option>";}} ?>
				</select>
				<select id="TODateToDay" name="TODateToDay" class="text_input" <?php echo $InputState; ?>>
					<?php for($d=1;$d<=31;$d++){if($d==intval($records['TODateToDay'])){echo "<option value='$d' selected>$d</option>";}else{echo "<option value='$d'>$d</option>";}} ?>
				</select>
				<input type="text" name="TODateToYear" id="TODateToYear" class="text_input" <?php echo $InputState; ?> style="width:30px;" value="<?php echo $records['TODateToYear']; ?>" />
				<select id="TOIncDayTimeTo" name="TOIncDayTimeTo" class="text_input" <?php echo $InputState; ?>>
					<?php if($records['TOIncDayTimeTo']=='AM'){echo "<option value='AM' selected>AM</option><option value='PM'>PM</option>";}else{echo "<option value='AM'>AM</option><option value='PM' selected>PM</option>";} ?>
				</select></td>
			</tr>
			<tr valign="top">
				<td class="form_label"><label>TRAVEL ORDER TO: </label></td>
				<td class="pds_form_input">
					<select size="3" name="ListedIDs" id="ListedIDs" class="text_input" style="width:230px;" <?php echo $InputState; ?> onClick="IDtoRemoveFrList=this.value;">
						<?php 
						if($mode==0){
							$TOTo=($EmpID!="")?"TO,$EmpID":"TO";
							$result=$MySQLi->sqlQuery("SELECT `tblemppersonalinfo`.`EmpID`, CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName FROM `tblemppersonalinfo` WHERE `EmpID` = '$EmpID';");
							$ids=mysqli_fetch_array($result, MYSQLI_BOTH);
							echo "<option value='".$ids['EmpID']."'>".$ids['EmpName']."</option>";
						}
						else{
							$TOTo="TO";
							$result=$MySQLi->sqlQuery("SELECT `tblemppersonalinfo`.`EmpID`, CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName FROM `tblemppersonalinfo` JOIN `tblemptravelorders` ON `tblemppersonalinfo`.`EmpID`=`tblemptravelorders`.`EmpID` WHERE `tblemptravelorders`.`TOID` = '$TOID';");
							while($ids=mysqli_fetch_array($result, MYSQLI_BOTH)){
								echo "<option value='".$ids['EmpID']."'>".$ids['EmpName']."</option>";
								$TOTo.=",".$ids['EmpID'];
							}
						}?>
					</select>
					<input type="hidden" name="ListOfID" id="ListOfID" value="<?php echo $TOTo; ?>" />
				</td>
				<td><ul class="ui-widget ui-helper-clearfix ul-icons"><li id="sEmployee" class="ui-state-default ui-corner-all" title="Add Employee" onClick="selectWindow(this)"><span class="ui-icon ui-icon-plus <?php if($mode==-1){echo "ui-state-diasabled";} ?>"></span></li><li class="ui-state-default ui-corner-all" title="Remove Employee" onClick="RemoveIDfrList(document.getElementById('ListOfID'),document.getElementById('ListedIDs'));"><span class="ui-icon ui-icon-minus <?php if($mode==-1){echo "ui-state-diasabled";} ?>"></span></li></ul></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>SUBJECT: </label></td>
				<td class="pds_form_input" colspan="2"><input value="<?php echo $records['TOSubject']; ?>" type="text" name="TOSubject" id="TOSubject" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
			</tr>
			<tr valign="top">
				<td class="form_label"><label>DESTINATION: </label></td>
				<td colspan="2" valign="center"><table style="margin:0;padding:0;border-spacing:0;cell-spacing:0;"><tr><td class="pds_form_input" style="margin:0;padding:0;"><input type='checkbox' id='TOOutsideLU' name='TOOutsideLU' <?php if($records['TOOutsideLU']==1){echo " checked ";} echo $InputState; ?> /></td><td class="form_label_small" style="margin:0;padding:0;"><label>Outside La Union</label></td><tr></table></td>
			</tr>
			<tr valign="top">
				<td class="form_label"><label>&nbsp;</label></td>
				<td class="pds_form_input" colspan="2"><textarea rows="1" cols="36" name="TODestination" id="TODestination" class="text_input sml_frm_fld" <?php echo $InputState; ?> ><?php echo $records['TODestination']; ?></textarea></td>
			</tr>
			<tr valign="top">
				<td class="form_label" style="width:200px;"><label>TRAVEL ORDER DETAILS: </label></td>
				<td class="pds_form_input" colspan="2">
					<textarea rows="5" cols="36" name="TOBody" id="TOBody" class="text_input sml_frm_fld" <?php echo $InputState; ?> ><?php echo $records['TOBody']; ?></textarea>
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
		<input type="hidden" name="TOID" id="TOID" value="<?php echo $TOID; ?>" />
		<input type="hidden" name="EmpID" id="EmpID" value="<?php echo $EmpID; ?>" />
	</form>
</center>
<?php } ?>