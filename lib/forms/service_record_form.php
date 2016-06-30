<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD009'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$EmpID=isset($_POST['id'])?trim(strip_tags($_POST['id'])):'00000';
	$SRecID=isset($_POST['xid'])?trim(strip_tags($_POST['xid'])):'';
	$mode=isset($_POST['mode'])?trim(strip_tags($_POST['mode'])):'0';
	echo "1|$EmpID|";
	
	$MONTHS=Array('','JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC');
	
	if($EmpID!='00000'){
		$MySQLi=new MySQLClass();
		$records=Array();
		$InputState="";
		if($mode==-1){
			$result=$MySQLi->sqlQuery("SELECT * FROM `tblempservicerecords` WHERE `EmpID`='".$EmpID."' AND `SRecID`='".$SRecID."' LIMIT 1;");
			$records=mysqli_fetch_array($result, MYSQLI_BOTH);
			$records['SRecSalary']=number_format((float)$records['SRecSalary'],2,'.',',');
			
			$result=$MySQLi->sqlQuery("SELECT `PosDesc` FROM `tblpositions` WHERE `PosID`='".$records['PosID']."' LIMIT 1;");
			$positions=mysqli_fetch_array($result, MYSQLI_BOTH);
			$records['PosDesc']=$positions['PosDesc'];
			
			$result=$MySQLi->sqlQuery("SELECT `SubOffName` FROM `tblsuboffices` WHERE `SubOffID`='".$records['MotherOfficeID']."' LIMIT 1;");
			$moffices=mysqli_fetch_array($result, MYSQLI_BOTH);
			$records['MotherOfficeDesc']=$moffices['SubOffName'];
			
			$result=$MySQLi->sqlQuery("SELECT `SubOffName` FROM `tblsuboffices` WHERE `SubOffID`='".$records['AssignedOfficeID']."' LIMIT 1;");
			$aoffices=mysqli_fetch_array($result, MYSQLI_BOTH);
			$records['AssignedOfficeDesc']=$aoffices['SubOffName'];
			$InputState="disabled";
		}
		else if($mode==0){
			$records['AssignedOfficeDesc']=$records['MotherOfficeDesc']=$records['PosDesc']=$records['SRecJobDesc']=$records['SRecLivNoPay']=$records['SRecRemarks']="";
			$records['SRecEmployer']="PROVINCIAL GOVERNMENT OF LA UNION";
			$records['ApptStID']="AS006";
			$records['AssignedOfficeID']=$records['MotherOfficeID']="SOOF00000";
			$records['PosID']="PO000";
			$records['SalUnitID']="U05";
			$records['SalGrdID']="20110000";
			$records['SRecSalary']="0.00";
			$records['SRecFromMonth']=$records['SRecToMonth']=date('m');
			$records['SRecFromDay']=$records['SRecToDay']=date('j');
			$records['SRecFromYear']=$records['SRecToYear']=date('Y');
		}
		else if($mode==1){
			$result=$MySQLi->sqlQuery("SELECT * FROM `tblempservicerecords` WHERE `EmpID`='".$EmpID."' AND `SRecID`='".$SRecID."' LIMIT 1;");
			$records=mysqli_fetch_array($result, MYSQLI_BOTH);
			$records['SRecSalary']=number_format((float)$records['SRecSalary'],2,'.',',');
			
			$result=$MySQLi->sqlQuery("SELECT `PosDesc` FROM `tblpositions` WHERE `PosID`='".$records['PosID']."' LIMIT 1;");
			$positions=mysqli_fetch_array($result, MYSQLI_BOTH);
			$records['PosDesc']=$positions['PosDesc'];
			
			$result=$MySQLi->sqlQuery("SELECT `SubOffName` FROM `tblsuboffices` WHERE `SubOffID`='".$records['MotherOfficeID']."' LIMIT 1;");
			$moffices=mysqli_fetch_array($result, MYSQLI_BOTH);
			$records['MotherOfficeDesc']=$moffices['SubOffName'];
			
			$result=$MySQLi->sqlQuery("SELECT `SubOffName` FROM `tblsuboffices` WHERE `SubOffID`='".$records['AssignedOfficeID']."' LIMIT 1;");
			$aoffices=mysqli_fetch_array($result, MYSQLI_BOTH);
			$records['AssignedOfficeDesc']=$aoffices['SubOffName'];
		}
?>
<div id="pos_reference" style="position:absolute;">.</div>
<center>
	<form name="f_dpnt_info" onSubmit="processForm('srec',this);return false;"><br/>
		<table class="form_window">	<?php //Spouse"s Name... ?>
			<tr>
				<td class="form_label" style="width:200px;"><label>INCLUSIVE DATE FROM: </label></td>
				<td class="pds_form_input" colspan='2'>
				<select id="SRecFromMonth" name="SRecFromMonth" class="text_input" <?php echo $InputState; ?>>
					<?php for($m=1;$m<=12;$m++){if($m==intval($records['SRecFromMonth'])){echo "<option value='$m' selected>".$MONTHS[$m]."</option>";}else{echo "<option value='$m'>".$MONTHS[$m]."</option>";}} ?>
				</select>
				<select id="SRecFromDay" name="SRecFromDay" class="text_input" <?php echo $InputState; ?>>
					<?php for($d=1;$d<=31;$d++){if($d==intval($records['SRecFromDay'])){echo "<option value='$d' selected>$d</option>";}else{echo "<option value='$d'>$d</option>";}} ?>
				</select>
				<input type="text" name="SRecFromYear" id="SRecFromYear" class="text_input sml_frm_fld" <?php echo $InputState; ?> style="width:30px;" value="<?php echo $records['SRecFromYear']; ?>" /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>INCLUSIVE DATE TO: </label></td>
				<td class="pds_form_input" colspan='2'>
				<select id="SRecToMonth" name="SRecToMonth" class="text_input" <?php echo $InputState; ?>>
					<?php for($m=1;$m<=12;$m++){if($m==intval($records['SRecToMonth'])){echo "<option value='$m' selected>".$MONTHS[$m]."</option>";}else{echo "<option value='$m'>".$MONTHS[$m]."</option>";}} ?>
				</select>
				<select id="SRecToDay" name="SRecToDay" class="text_input" <?php echo $InputState; ?>>
					<?php for($d=1;$d<=31;$d++){if($d==intval($records['SRecToDay'])){echo "<option value='$d' selected>$d</option>";}else{echo "<option value='$d'>$d</option>";}} ?>
				</select>
				<input type="text" name="SRecToYear" id="SRecToYear" class="text_input" <?php echo $InputState; ?> style="width:30px;" value="<?php echo $records['SRecToYear']; ?>" /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>EMPLOYER: </label></td>
				<td class="pds_form_input" colspan='2'><input value="<?php echo $records['SRecEmployer']; ?>" type="text" name="SRecEmployer" id="SRecEmployer" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>GOV'T SERVICE: </label></td>
				<td class="pds_form_input" colspan='2'>
				<select id="SRecIsGov" name="SRecIsGov" class="text_input" onChange="isGov(this.value);" <?php echo $InputState; ?>>
					<?php if("YES"==$records['SRecIsGov']){echo "<option value='YES' selected>YES</option>";}else{echo "<option value='YES'>YES</option>";}
								if("NO"==$records['SRecIsGov']){echo "<option value='NO' selected>NO</option>";}else{echo "<option value='NO'>NO</option>";} ?>
				</select>
				</td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>MOTHER OFFICE: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['MotherOfficeDesc']; ?>" type="text" name="MotherOfficeDesc" id="MotherOfficeDesc" class="text_input sml_frm_fld_x" <?php if($mode==-1){echo $InputState;}else{echo "readonly";} ?> /><input value="<?php echo $records['MotherOfficeID']; ?>" type="hidden" name="MotherOfficeID" id="MotherOfficeID" /></td><td><ul class="ui-widget ui-helper-clearfix ul-icons"><li id="sMotherOffice" name="sMotherOffice" class="ui-state-default ui-corner-all" title="Select Mother Office" onClick="if(document.getElementById('SRecIsGov').value=='YES'){selectWindow(this);}"><span class="ui-icon ui-icon-clipboard"></span></li></ul></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>ASSIGNED OFFICE: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['AssignedOfficeDesc']; ?>" type="text" name="AssignedOfficeDesc" id="AssignedOfficeDesc" class="text_input sml_frm_fld_x" <?php if($mode==-1){echo $InputState;}else{echo "readonly";} ?> /><input value="<?php echo $records['AssignedOfficeID']; ?>" type="hidden" name="AssignedOfficeID" id="AssignedOfficeID" /></td><td><ul class="ui-widget ui-helper-clearfix ul-icons"><li id="sAssignedOffice" name="sAssignedOffice" class="ui-state-default ui-corner-all" title="Select Assigned Office" onClick="if(document.getElementById('SRecIsGov').value=='YES'){selectWindow(this);}"><span class="ui-icon ui-icon-clipboard"></span></li></ul></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>POSITION: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['PosDesc']; ?>" type="text" name="PosDesc" id="PosDesc" class="text_input sml_frm_fld_x" <?php if($mode==-1){echo $InputState;}else{echo "readonly";} ?> /><input value="<?php echo $records['PosID']; ?>" type="hidden" name="PosID" id="PosID" /></td><td><ul class="ui-widget ui-helper-clearfix ul-icons"><li id="sPosition" name="sPosition" class="ui-state-default ui-corner-all" title="Select Position" onClick="if(document.getElementById('SRecIsGov').value=='YES'){selectWindow(this);}"><span class="ui-icon ui-icon-clipboard"></span></li></ul></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>SALARY GRADE STEP: </label></td>
				<td class="pds_form_input" colspan='2'> 
				<select id="SalStep" name="SalStep" class="text_input" <?php echo $InputState; ?>>
					<?php for($SalStep=1;$SalStep<=8;$SalStep++){if($SalStep==$records['SRecSalGradeStep']){echo "<option value='$SalStep' selected>$SalStep</option>";}else{echo "<option value='$SalStep'>$SalStep</option>";}} ?>
				</select>
				</td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>APPOINTMENT STATUS: </label></td>
				<td class="pds_form_input" colspan='2'>
					<select id="ApptStID" name="ApptStID" class="text_input" <?php echo $InputState; ?> >
						<?php
							$result=$MySQLi->sqlQuery("SELECT `ApptStID`,`ApptStDesc` FROM `tblapptstatus` WHERE `ApptStID` <> 'AS000' ORDER BY `ApptStDesc`;");
							while($appstatuses=mysqli_fetch_array($result, MYSQLI_BOTH)) {
								if($appstatuses['ApptStID']==$records['ApptStID']){echo "<option value='".$appstatuses['ApptStID']."' selected>".$appstatuses['ApptStDesc']."</option>";}
								else{echo "<option value='".$appstatuses['ApptStID']."'>".$appstatuses['ApptStDesc']."</option>";}
							} unset($result);
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>JOB DESCRIPTION: </label></td>
				<td class="pds_form_input" colspan='2'><input value="<?php echo $records['SRecJobDesc']; ?>" type="text" name="SRecJobDesc" id="SRecJobDesc" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>SALARY: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['SRecSalary']; ?>" type="text" name="SRecSalary" id="SRecSalary" class="text_input sml_frm_fld" <?php echo $InputState; ?> style="width:70px;text-align:right;" disabled /> PHP</td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>SALARY UNIT: </label></td>
				<td class="pds_form_input" colspan='2'>
					<select id="SalUnitID" name="SalUnitID" class="text_input" <?php echo $InputState; ?> >
						<?php
							$result=$MySQLi->sqlQuery("SELECT `SalUnitID`,`SalUnitCode` FROM `tblsalaryunits` WHERE `SalUnitID` <> 'SU00' ORDER BY `SalUnitCode`;");
							while($salunits=mysqli_fetch_array($result, MYSQLI_BOTH)){
								if($salunits['SalUnitID']==$records['SalUnitID']){echo "<option value='".$salunits['SalUnitID']."' selected>".$salunits['SalUnitCode']."</option>";}
								else{echo "<option value='".$salunits['SalUnitID']."'>".$salunits['SalUnitCode']."</option>";}
							} unset($result);
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>LEAVE/ABSENCES WITHOUT PAY: </label></td>
				<td class="pds_form_input" colspan='2'><input value="<?php echo $records['SRecLivNoPay']; ?>" type="text" name="SRecLivNoPay" id="SRecLivNoPay" class="text_input sml_frm_fld" style="width:30px;text-align:right;" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>REMARKS: </label></td>
				<td class="pds_form_input" colspan='2'><input value="<?php echo $records['SRecRemarks']; ?>" type="text" name="SRecRemarks" id="SRecRemarks" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
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
		<input type="hidden" name="SRecID" id="SRecID" value="<?php echo $SRecID; ?>" />
		<input type="hidden" name="EmpID" id="EmpID" value="<?php echo $EmpID; ?>" />
	</form>
</center>
<?php } ?>
