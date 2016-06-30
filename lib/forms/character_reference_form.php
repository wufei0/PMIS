<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD015'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$EmpID=isset($_POST['id'])?trim(strip_tags($_POST['id'])):'00000';
	$RefID=isset($_POST['xid'])?trim(strip_tags($_POST['xid'])):'';
	$mode=isset($_POST['mode'])?trim(strip_tags($_POST['mode'])):'0';
	echo "1|$EmpID|";
	
	$MONTHS=Array('','JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC');
	
	if($EmpID!='00000'){
		$MySQLi=new MySQLClass();
		$records=Array();
		$InputState="";
		if($mode==-1){
			$result=$MySQLi->sqlQuery("SELECT * FROM `tblempreferences` WHERE `EmpID`='".$EmpID."' AND `RefID`='".$RefID."' LIMIT 1;");
			$records=mysqli_fetch_array($result, MYSQLI_BOTH);
			$InputState="disabled";
		}
		else if($mode==0){
			$records['RefLName']=$records['RefFName']=$records['RefMName']=$records['RefExtName']=$records['RefAddSt']=$records['RefAddMun']=$records['RefAddBrgy']=$records['RefAddProv']=$records['RefZipCode']=$records['RefTel']="";
		}
		else if($mode==1){
			$result=$MySQLi->sqlQuery("SELECT * FROM `tblempreferences` WHERE `EmpID`='".$EmpID."' AND `RefID`='".$RefID."' LIMIT 1;");
			$records=mysqli_fetch_array($result, MYSQLI_BOTH);
		}
?>
<center>
	<form name="f_Ref_info" onSubmit="processForm('chrf',this);return false;"><br/>
		<table class="form_window">	<?php //Spouse"s Name... ?>
			<tr>
				<td class="form_label" style="width:200px;"><label>LAST NAME: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['RefLName']; ?>" type="text" id="RefLName" name="RefLName" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>FIRST NAME: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['RefFName']; ?>" type="text" name="RefFName" id="RefFName" class="text_input sml_frm_fld" <?php echo $InputState; ?> <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label"><label>MIDDLE NAME: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['RefMName']; ?>" type="text" name="RefMName" id="RefMName" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>NAME EXTENSION: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['RefExtName']; ?>" type="text" name="RefExtName" id="RefExtName" class="text_input" <?php echo $InputState; ?> style="width:30px;"/></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>ADDRESS: </label></td>
				<td class="form_label" style="width:200px;"><label>&nbsp;</label></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>Street: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['RefAddSt']; ?>" type="text" name="RefAddSt" id="RefAddSt" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>Barangay: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['RefAddBrgy']; ?>" type="text" name="RefAddBrgy" id="RefAddBrgy" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>Municipality: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['RefAddMun']; ?>" type="text" name="RefAddMun" id="RefAddMun" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>Province: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['RefAddProv']; ?>" type="text" name="RefAddProv" id="RefAddProv" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>Zip Code: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['RefZipCode']; ?>" type="text" name="RefZipCode" id="RefZipCode" class="text_input" <?php echo $InputState; ?> style="width:30px;" /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>CONTACT NUMBER: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['RefTel']; ?>" type="text" name="RefTel" id="RefTel" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
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
		<input type="hidden" name="RefID" id="RefID" value="<?php echo $RefID; ?>" />
		<input type="hidden" name="EmpID" id="EmpID" value="<?php echo $EmpID; ?>" />
	</form>
</center>
<?php } ?>
