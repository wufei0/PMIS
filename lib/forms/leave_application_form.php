<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD018'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$EmpID=isset($_POST['id'])?trim(strip_tags($_POST['id'])):'00000';
	$LivAppID=isset($_POST['xid'])?trim(strip_tags($_POST['xid'])):'';
	$mode=isset($_POST['mode'])?trim(strip_tags($_POST['mode'])):'0';
	
	$MONTHS=Array('','JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC');
	echo "1|$EmpID|";
	if($EmpID!='00000'){
		$MySQLi=new MySQLClass();
		$records=Array();
		$InputState="";
		
		if($mode==0){
			$records['LeaveTypeID']="LT01";$records['LivAppTypeDetail']="IN";
			$records['LivAppNotes']="";
			$records['LivAppIncDateToYear']=date('Y');$records['LivAppIncDateToMonth']=date('m');$records['LivAppIncDateToDay']=date('d');
			$records['LivAppIncDateFrYear']=date('Y');$records['LivAppIncDateFrMonth']=date('m');$records['LivAppIncDateFrDay']=date('d');
		}
		else{
			$result=$MySQLi->sqlQuery("SELECT `LeaveTypeID`, `LivAppTypeDetail`, `LivAppNotes`, DATE_FORMAT(`LivAppIncDateFrom`, '%Y') AS LivAppIncDateFrYear, DATE_FORMAT(`LivAppIncDateFrom`, '%m') AS LivAppIncDateFrMonth, DATE_FORMAT(`LivAppIncDateFrom`, '%d') AS LivAppIncDateFrDay, `LivAppIncDayTimeFrom`, DATE_FORMAT(`LivAppIncDateTo`, '%Y') AS LivAppIncDateToYear, DATE_FORMAT(`LivAppIncDateTo`, '%m') AS LivAppIncDateToMonth, DATE_FORMAT(`LivAppIncDateTo`, '%d') AS LivAppIncDateToDay, `LivAppIncDayTimeTo` FROM `tblempleaveapplications` WHERE `LivAppID`='".$LivAppID."' LIMIT 1;");
			$records=mysqli_fetch_array($result, MYSQLI_BOTH);
			if($mode==-1){$InputState="disabled";}
		}
?>
	
<center>
	<form name="f_leave_info" onSubmit="processForm('leav',this);return false;"><br/>
		<table class="form_window">	
			<?php 
				if($Authorization[6]&&($EmpID=="X")){ ?>
			<tr>
				<td class="form_label" style="width:200px;"><label>EMPLOYEE: </label></td>
				<td class="pds_form_input">
					<input type="text" name="EmpName" id="EmpName" value="" class="text_input sml_frm_fld_x" disabled /><td><ul class="ui-widget ui-helper-clearfix ul-icons"><li id="sPersonnel" name="sPersonnel" class="ui-state-default ui-corner-all" title="Select Employee" onClick="selectWindow(this); document.getElementById('sml_srch_win_em').focus();"><span class="ui-icon ui-icon-clipboard"></span></li></ul></td>
				</td>
			
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>DATE OF FILING:</label></td>
				<td class="pds_form_input" colspan='2'>
				<select id="LivAppFiledMonth" name="LivAppFiledMonth" class="text_input">
					<?php for($m=1;$m<=12;$m++){if($m==date('m')){echo "<option value='$m' selected>".$MONTHS[$m]."</option>";}else{echo "<option value='$m'>".$MONTHS[$m]."</option>";}} ?>
				</select>
				<select id="LivAppFiledDay" name="LivAppFiledDay" class="text_input">
					<?php for($d=1;$d<=31;$d++){if($d==date('d')){echo "<option value='$d' selected>$d</option>";}else{echo "<option value='$d'>$d</option>";}} ?>
				</select>
				<input type="text" name="LivAppFiledYear" id="LivAppFiledYear" class="text_input" style="width:30px;" value="<?= date('Y')?>"/>
				</td>
			</tr>
			<?php 
				}	?>
			<tr>
				<td class="form_label" style="width:200px;"><label>INCLUSIVE DATE OF LEAVE </label></td><td colspan="2">&nbsp;</td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>From:</label></td>
				<td class="pds_form_input" colspan='2'>
				<select id="LivAppIncDateFrMonth" name="LivAppIncDateFrMonth" class="text_input" <?php echo $InputState; ?>>
					<?php for($m=1;$m<=12;$m++){if($m==intval($records['LivAppIncDateFrMonth'])){echo "<option value='$m' selected>".$MONTHS[$m]."</option>";}else{echo "<option value='$m'>".$MONTHS[$m]."</option>";}} ?>
				</select>
				<select id="LivAppIncDateFrDay" name="LivAppIncDateFrDay" class="text_input" <?php echo $InputState; ?>>
					<?php for($d=1;$d<=31;$d++){if($d==intval($records['LivAppIncDateFrDay'])){echo "<option value='$d' selected>$d</option>";}else{echo "<option value='$d'>$d</option>";}} ?>
				</select>
				<input type="text" name="LivAppIncDateFrYear" id="LivAppIncDateFrYear" class="text_input" <?php echo $InputState; ?> style="width:30px;" value="<?php echo $records['LivAppIncDateFrYear']; ?>" />
				<select id="LivAppIncDayTimeFrom" name="LivAppIncDayTimeFrom" class="text_input" <?php echo $InputState; ?>>
					<?php if($records['LivAppIncDayTimeFrom']=='AM'){echo "<option value='AM' selected>AM</option>";}else{echo "<option value='AM'>AM</option>";}if($records['LivAppIncDayTimeFrom']=='PM'){echo "<option value='PM' selected>PM</option>";}else{echo "<option value='PM'>PM</option>";} ?>
				</select></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>To:</label></td>
				<td class="pds_form_input" colspan='2'>
				<select id="LivAppIncDateToMonth" name="LivAppIncDateToMonth" class="text_input" <?php echo $InputState; ?>>
					<?php for($m=1;$m<=12;$m++){if($m==intval($records['LivAppIncDateToMonth'])){echo "<option value='$m' selected>".$MONTHS[$m]."</option>";}else{echo "<option value='$m'>".$MONTHS[$m]."</option>";}} ?>
				</select>
				<select id="LivAppIncDateToDay" name="LivAppIncDateToDay" class="text_input" <?php echo $InputState; ?>>
					<?php for($d=1;$d<=31;$d++){if($d==intval($records['LivAppIncDateToDay'])){echo "<option value='$d' selected>$d</option>";}else{echo "<option value='$d'>$d</option>";}} ?>
				</select>
				<input type="text" name="LivAppIncDateToYear" id="LivAppIncDateToYear" class="text_input" <?php echo $InputState; ?> style="width:30px;" value="<?php echo $records['LivAppIncDateToYear']; ?>" />
				<select id="LivAppIncDayTimeTo" name="LivAppIncDayTimeTo" class="text_input" <?php echo $InputState; ?>>
					<?php if($records['LivAppIncDayTimeTo']=='AM'){echo "<option value='AM' selected>AM</option>";}else{echo "<option value='AM'>AM</option>";}if($records['LivAppIncDayTimeTo']=='PM'){echo "<option value='PM' selected>PM</option>";}else{echo "<option value='PM' selected>PM</option>";} ?>
				</select></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>LEAVE TYPE: </label></td>
				<td class="pds_form_input" colspan='2'>
					<select id="LeaveTypeID" name="LeaveTypeID" class="text_input sml_frm_fld" <?php echo $InputState; ?> onChange="if(this.value=='LT01'){$('#vDetails').show();$('#VacIn').prop('checked', true);}else{$('#vDetails').hide();}if(this.value=='LT02'){$('#sDetails').show();$('#SickOut').prop('checked', true);}else{$('#sDetails').hide();}">
						<?php
							$LeaveTypeDesc="";
							$result=$MySQLi->sqlQuery("SELECT * FROM `tblleavetypes` WHERE `LeaveTypeID`<>'LT00' ORDER BY `LeaveTypeID`;");
							while($ltype=mysqli_fetch_array($result, MYSQLI_BOTH)) {
								if($ltype['LeaveTypeID']=="LT08"){$ltype['LeaveTypeDesc']="CTO";}
								if($ltype['LeaveTypeID']==$records['LeaveTypeID']){echo "<option value='".$ltype['LeaveTypeID']."' selected>".$ltype['LeaveTypeDesc']."</option>";$LeaveTypeDesc=$ltype['LeaveTypeDesc'];}
								else{echo "<option value='".$ltype['LeaveTypeID']."'>".$ltype['LeaveTypeDesc']."</option>";}
							} unset($result);
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>&nbsp;</label></td>
				<td class="form_label" colspan='2'>
				<div id="vDetails" name="vDetails">
					<table border="0" cellspacing="0" cellpadding="0"><tr>
						<td><input type="radio" id="VacIn" name="LTypeDetail" value="IN" <?php echo $InputState; ?> /></td>
						<td><label>Within Philippines</label></td>
						<td><input type="radio" id="VacOut" name="LTypeDetail" value="OUT" <?php echo $InputState; ?> /></td>
						<td><label>Abroad</label></td>
					</tr></table>
				</div>
				<div id="sDetails" name="sDetails">
					<table border="0" cellspacing="0" cellpadding="0"><tr>
						<td><input type="radio" id="SickIn" name="LTypeDetail" value="IN" <?php echo $InputState; ?> /></td>
						<td><label>In Patient</label></td>
						<td><input type="radio" id="SickOut" name="LTypeDetail" value="OUT" <?php echo $InputState; ?> /></td>
						<td><label>Out Patient</label></td>
					</tr></table>
				</div>
				<script type='text/javaScript'>
					$(function(){
					<?php 
						if($records['LeaveTypeID']=="LT01"){echo "$('#vDetails').show();";if($records['LivAppTypeDetail']=="IN"){echo"$('#VacIn').prop('checked', true);";}else{echo"$('#VacOut').prop('checked', true);";}}else{echo "$('#vDetails').hide();";}
						if($records['LeaveTypeID']=="LT02"){echo "$('#sDetails').show();";if($records['LivAppTypeDetail']=="IN"){echo"$('#SickIn').prop('checked', true);";}else{echo"$('#SickOut').prop('checked', true);";}}else{echo "$('#sDetails').hide();";}
					?>
					});
				</script>
				</td>
			</tr>
			<tr valign="top">
				<td class="form_label"><label>LEAVE NOTES: </label></td>
				<td class="pds_form_input" colspan='2'><textarea rows="2" cols="38" name="LivAppNotes" id="LivAppNotes" class="text_input sml_frm_fld" <?php echo $InputState; ?> ><?php echo $records['LivAppNotes']; ?></textarea></td>
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
		<input type="hidden" name="LivAppID" id="LivAppID" value="<?php echo $LivAppID; ?>" />
		<input type="hidden" name="EmpID" id="ID" value="<?php echo $EmpID; ?>" />
	</form>
</center>
<?php } ?>