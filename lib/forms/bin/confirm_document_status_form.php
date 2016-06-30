<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD003'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$EmpID=isset($_POST['id'])?trim(strip_tags($_POST['id'])):'00000';
	$SkillID=isset($_POST['xid']) ? trim(strip_tags($_POST['xid'])) : '';
	$mode=isset($_POST['mode']) ? trim(strip_tags($_POST['mode'])) : '0';
	echo "1|$EmpID|";
	
	$MONTHS=Array('','JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC');
	
	if($EmpID!='00000'){
?>

<center>
	<form name="f_dpnt_info" onSubmit="processForm('skil',this);return false;"><br/>
		<table>
			<tr>
				<td style="width:50px;text-align:center;vertical-align:top;">
					<div class="">&nbsp;</div>
				</td>
				<td style="text-align:left;vertical-align:middle;">
					<div style="width:300px;"><b><b/></div>
				</td>
			</tr>
			<tr>
				<td class="form_label" style="width:50px;"><label>REMARKS: </label></td>
				<td class="form_label" style="width:300px;"><label>&nbsp;</label></td>
			</tr>
			<tr>
				<td class="pds_form_input" colspan="2">
					<textarea rows="3" cols="50" name="remark" id="remark" class="text_input sml_frm_fld"></textarea>
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
		<input type="hidden" name="SkillID" id="SkillID" value="<?php echo $SkillID; ?>" />
		<input type="hidden" name="EmpID" id="EmpID" value="<?php echo $EmpID; ?>" />
	</form>
</center>
<?php } ?>