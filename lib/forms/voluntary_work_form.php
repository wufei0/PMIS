<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD0010'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$EmpID=isset($_POST['id'])?trim(strip_tags($_POST['id'])):'00000';
	$VolOrgID=isset($_POST['xid'])?trim(strip_tags($_POST['xid'])):'';
	$mode=isset($_POST['mode'])?trim(strip_tags($_POST['mode'])):'0';
	echo "1|$EmpID|";
	
	$MONTHS=Array('','JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC');
	
	if($EmpID!='00000'){
		$MySQLi=new MySQLClass();
		$records=Array();
		$InputState="";
		if($mode==-1){
			$result=$MySQLi->sqlQuery("SELECT * FROM `tblempvoluntaryorg` WHERE `EmpID`='".$EmpID."' AND `VolOrgID`='".$VolOrgID."' LIMIT 1;");
			$records=mysqli_fetch_array($result, MYSQLI_BOTH);
			$InputState="disabled";
		}
		else if($mode==0){
			$records['VolOrgName']=$records['VolOrgAddSt']=$records['VolOrgAddMun']=$records['VolOrgAddBrgy']=$records['VolOrgAddProv']=$records['VolOrgZipCode']=$records['VolOrgHours']=$records['VolOrgDetails']="";
			$records['VolOrgFromMonth']=$records['VolOrgToMonth']=date('m');
			$records['VolOrgFromDay']=$records['VolOrgToDay']=date('j');
			$records['VolOrgFromYear']=$records['VolOrgToYear']=date('Y');
		}
		else if($mode==1){
			$result=$MySQLi->sqlQuery("SELECT * FROM `tblempvoluntaryorg` WHERE `EmpID`='".$EmpID."' AND `VolOrgID`='".$VolOrgID."' LIMIT 1;");
			$records=mysqli_fetch_array($result, MYSQLI_BOTH);
		}
?>
<center>
	<form name="f_dpnt_info" onSubmit="processForm('vwor',this);return false;"><br/>
		<table class="form_window">	<?php //Spouse"s Name... ?>
			<tr>
				<td class="form_label" style="width:200px;"><label>VOLUNTARY ORG/ASS/JOB:</label></td>
				<td class="pds_form_input"><input value="<?php echo $records['VolOrgName']; ?>" type="text" id="VolOrgName" name="VolOrgName" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>ADDRESS: </label></td>
				<td class="form_label" style="width:200px;"><label>&nbsp;</label></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>Street: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['VolOrgAddSt']; ?>" type="text" name="VolOrgAddSt" id="VolOrgAddSt" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>Barangay: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['VolOrgAddBrgy']; ?>" type="text" name="VolOrgAddBrgy" id="VolOrgAddBrgy" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>Municipality: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['VolOrgAddMun']; ?>" type="text" name="VolOrgAddMun" id="VolOrgAddMun" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>Province: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['VolOrgAddProv']; ?>" type="text" name="VolOrgAddProv" id="VolOrgAddProv" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>Zip Code: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['VolOrgZipCode']; ?>" type="text" name="VolOrgZipCode" id="VolOrgZipCode" class="text_input" <?php echo $InputState; ?> style="width:30px;" /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>INCLUSIVE DATE FROM: </label></td>
				<td class="pds_form_input">
				<select id="VolOrgFromMonth" name="VolOrgFromMonth" class="text_input" <?php echo $InputState; ?>>
					<?php for($m=1;$m<=12;$m++){if($m==intval($records['VolOrgFromMonth'])){echo "<option value='$m' selected>".$MONTHS[$m]."</option>";}else{echo "<option value='$m'>".$MONTHS[$m]."</option>";}} ?>
				</select>
				<select id="VolOrgFromDay" name="VolOrgFromDay" class="text_input" <?php echo $InputState; ?>>
					<?php for($d=1;$d<=31;$d++){if($d==intval($records['VolOrgFromDay'])){echo "<option value='$d' selected>$d</option>";}else{echo "<option value='$d'>$d</option>";}} ?>
				</select>
				<input type="text" name="VolOrgFromYear" id="VolOrgFromYear" class="text_input" <?php echo $InputState; ?> style="width:30px;" value="<?php echo $records['VolOrgFromYear']; ?>" /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>INCLUSIVE DATE TO: </label></td>
				<td class="pds_form_input">
				<select id="VolOrgToMonth" name="VolOrgToMonth" class="text_input" <?php echo $InputState; ?>>
					<?php for($m=1;$m<=12;$m++){if($m==intval($records['VolOrgToMonth'])){echo "<option value='$m' selected>".$MONTHS[$m]."</option>";}else{echo "<option value='$m'>".$MONTHS[$m]."</option>";}} ?>
				</select>
				<select id="VolOrgToDay" name="VolOrgToDay" class="text_input" <?php echo $InputState; ?>>
					<?php for($d=1;$d<=31;$d++){if($d==intval($records['VolOrgToDay'])){echo "<option value='$d' selected>$d</option>";}else{echo "<option value='$d'>$d</option>";}} ?>
				</select>
				<input type="text" name="VolOrgToYear" id="VolOrgToYear" class="text_input" <?php echo $InputState; ?> style="width:30px;" value="<?php echo $records['VolOrgToYear']; ?>" /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>NUMBER OF HOURS: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['VolOrgHours']; ?>" type="text" name="VolOrgHours" id="VolOrgHours" class="text_input" <?php echo $InputState; ?> style="width:30px;" /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>POSITION/NATURE OF WORK: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['VolOrgDetails']; ?>" type="text" name="VolOrgDetails" id="VolOrgDetails" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
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
		<input type="hidden" name="VolOrgID" id="VolOrgID" value="<?php echo $VolOrgID; ?>" />
		<input type="hidden" name="EmpID" id="EmpID" value="<?php echo $EmpID; ?>" />
	</form>
</center>
<?php } ?>
