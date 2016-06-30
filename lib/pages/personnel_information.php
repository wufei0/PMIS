<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD003'));
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	
	
	
	$EmpID=isset($_POST['id'])?trim(strip_tags($_POST['id'])):'00000';
	$mode=isset($_POST['mode'])?trim(strip_tags($_POST['mode'])):0;  /* 0 - VIEW, 1 - UPDATE */
	if(!(($Authorization[0])||($_SESSION['user']==$EmpID))){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	
	echo "1|$EmpID|";
	$MONTHS=Array('','JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC');
	$InputState="disabled";
	$records=Array();
	if($EmpID!='00000'){
		$MySQLi=new MySQLClass();
		$result=$MySQLi -> sqlQuery("SELECT * FROM `tblemppersonalinfo` WHERE `EmpID`='".$EmpID."';");
		$records=mysqli_fetch_array($result, MYSQLI_BOTH);
		$result=$MySQLi -> sqlQuery("SELECT * FROM `tblempctc` WHERE `EmpID`='".$EmpID."' ORDER BY `CTCDateYear` DESC, `CTCDateMonth` DESC LIMIT 1;");
		$ctc=mysqli_fetch_array($result, MYSQLI_BOTH);
		
		if($mode==0){
			if((!$Authorization[2])||($_SESSION['user']!=$EmpID)){$bEditState="disabled";$bEditClass="button ui-button ui-widget ui-corner-all ui-state-disabled";}
			else{$bEditState="";$bEditClass="button ui-button ui-widget ui-corner-all";}
			$InputState="disabled";
			$bSaveClass="button ui-button ui-widget ui-corner-all ui-state-disabled";
		}
		else if($mode==1){$InputState="";$bEditState="disabled";$bEditClass="button ui-button ui-widget ui-corner-all ui-state-disabled";$bSaveClass="button ui-button ui-widget ui-corner-all";}
		
?>	

<center>

	<form name="f_emp_info" onSubmit="processForm('pinfo',this);return false;"><br/>
		<?php /* Employee NAME... */ ?>
		<table class="form">
			<tr>
				<td class="form_label" style="width:100px;"><label>LAST NAME: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpLName']; ?>" type="text" name="EmpLName" id="EmpLName" class="text_input" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:100px;"><label>FIRST NAME: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpFName']; ?>" type="text" name="EmpFName" id="EmpFName" class="text_input" <?php echo $InputState; ?> /></td>
			</tr>
		</table>
		
		<table class="form">
			<tr>
				<td class="form_label" style="width:100px;"><label>MIDDLE NAME: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpMName']; ?>" type="text" name="EmpMName" id="EmpMName" class="text_input" <?php echo $InputState; ?> /></td>
				<td class="form_label" style="width:85px;"><label>NAME EXTENSION: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpExtName']; ?>" type="text" name="EmpExtName" id="EmpExtName" class="text_input" <?php echo $InputState; ?> /></td>
			</tr>
		</table>
		
		<?php /* Employee Birthday and Birth Place... */ ?>
		<table class="form">
			<tr>
				<td class="form_label" style="width:100px;"><label>DATE OF BIRTH: </label></td>
				<td class="pds_form_input">
				<select id="EmpBirthMonth" name="EmpBirthMonth" class="text_input" <?php echo $InputState; ?>>
					<?php for($m=1;$m<=12;$m++){
						if($m==intval($records['EmpBirthMonth'])){echo "<option value='$m' selected>".$MONTHS[$m]."</option>";}
						else{echo "<option value='$m'>".$MONTHS[$m]."</option>";}
					} ?></select>
				<select id="EmpBirthDay" name="EmpBirthDay" class="text_input" <?php echo $InputState; ?>>
				<?php for($d=1;$d<=31;$d++){
					if($d==intval($records['EmpBirthDay'])){echo "<option value='$d' selected>$d</option>";}
					else{echo "<option value='$d'>$d</option>";}
				} ?></select>
				<input type="text" name="EmpBirthYear" id="EmpBirthYear" class="text_input" style="width:30px;" value="<?php echo $records['EmpBirthYear']; ?>" <?php echo $InputState; ?> /></td>
				<td class="form_label" style="width:90px;"><label>PLACE OF BIRTH: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpBirthPlace']; ?>" type="text" name="EmpBirthPlace" id="EmpBirthPlace" class="text_input" style="width:250px;" <?php echo $InputState; ?> /></td>
			</tr>
		</table>
		
		<?php /* Employee Sex and Civil Status... */ ?>
		<table class="form">
			<tr>
				<td class="form_label" style="width:100px;"><label>SEX: </label></td>
				<td class="pds_form_input">
				<select id="EmpSex" name="EmpSex" class="text_input" <?php echo $InputState; ?>>
					<?php if($records['EmpSex']=="MALE"){echo "<option value='MALE' selected>MALE</option>";}else{echo "<option value='MALE'>MALE</option>";}
						if($records['EmpSex']=="FEMALE"){echo "<option value='FEMALE' selected>FEMALE</option>";}else{echo "<option value='FEMALE'>FEMALE</option>";}
					?></select>
				</td>
				<td class="form_label" style="width:80px;"><label>CIVIL STATUS: </label></td>
				<td class="pds_form_input">
					<select name="CivilStatus" id="CivilStatus" class="text_input"  onChange="if (this.value != 'Other, Specify') { document.getElementById('EmpCivilStatus').value=this.value; document.getElementById('EmpCivilStatus').style.visibility='hidden'; } else { document.getElementById('EmpCivilStatus').value=''; document.getElementById('EmpCivilStatus').style.visibility='visible'; }" <?php echo $InputState; ?> >
						<option value="SINGLE" <?php if(($records['EmpCivilStatus']=="SINGLE")||($records['EmpCivilStatus']=="")) {echo " selected ";} ?>>Single</option>
						<option value="MARRIED" <?php if($records['EmpCivilStatus']=="MARRIED") {echo " selected ";} ?>>Married</option>
						<option value="ANNULLED" <?php if($records['EmpCivilStatus']=="ANNULLED") {echo " selected ";} ?>>Annulled</option>
						<option value="WIDOWED" <?php if($records['EmpCivilStatus']=='WIDOWED') {echo " selected ";} ?>>Widowed</option>
						<option value="SEPARATED" <?php if($records['EmpCivilStatus']=='SEPARATED') {echo " selected ";} ?>>Separated</option>
						<option value="Other, Specify" <?php if(($records['EmpCivilStatus']!="SINGLE")&&($records['EmpCivilStatus']!="MARRIED")&&($records['EmpCivilStatus']!='ANNULLED')&&($records['EmpCivilStatus']!="WIDOWED")&&($records['EmpCivilStatus']!="SEPARATED")) {echo " selected ";} ?>>Other, Specify</option>
					</select>
					<input value="<?php echo $records['EmpCivilStatus']; ?>" type="text" name="EmpCivilStatus" id="EmpCivilStatus" class="text_input" <?php if(($records['EmpCivilStatus']!="SINGLE")&&($records['EmpCivilStatus']!="MARRIED")&&($records['EmpCivilStatus']!="ANNULLED")&&($records['EmpCivilStatus']!='WIDOWED')&&($records['EmpCivilStatus']!="SEPARATED")) {echo "style=\"width:180px;visibility:visible;\"";} else {echo "style=\"width:180px;visibility:hidden;\"";} ?> <?php echo $InputState; ?> />
				</td>
			</tr>
		</table>
		
		<?php /* Employee Citizenship... */ ?>
		<table class="form">
			<tr>
				<td class="form_label" style="width:100px;"><label>CITIZENSHIP: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpCitizenship']; ?>" type="text" name="EmpCitizenship" id="EmpCitizenship" class="text_input" <?php echo $InputState; ?> /></td>
				<td class="form_label" style="width:75px;"><label>HEIGHT (m): </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpHeight']; ?>" type="text" name="EmpHeight" id="EmpHeight" class="text_input" <?php echo $InputState; ?> /></td>
				<td class="form_label" style="width:75px;"><label>WEIGHT (kg): </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpWeight']; ?>" type="text" name="EmpWeight" id="EmpWeight" class="text_input" <?php echo $InputState; ?> /></td>
				<td class="form_label" style="width:70px;"><label>BLOOD TYPE: </label></td>
				<td class="pds_form_input">
					<select name="EmpBloodType" id="EmpBloodType" class="text_input" <?php echo $InputState; ?> >
						<option value="O" <?php if(($records['EmpBloodType']=="O")||($records['EmpBloodType']=="")) {echo " selected ";} ?>>O</option>
						<option value="A" <?php if($records['EmpBloodType']=="A") {echo " selected ";} ?>>A</option>
						<option value="B" <?php if($records['EmpBloodType']=="B") {echo " selected ";} ?>>B</option>
						<option value="AB" <?php if($records['EmpBloodType']=="AB") {echo " selected ";} ?>>AB</option>
					</select>
				</td>
			</tr>
		</table>
		
		<?php /* Employee GSIS and Pag-ibig Number... */ ?>
		<table class="form">
			<tr>
				<td class="form_label" style="width:100px;"><label>GSIS ID NO.: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpGSIS']; ?>" type="text" name="EmpGSIS" id="EmpGSIS" class="text_input" <?php echo $InputState; ?> /></td>
				<td class="form_label" style="width:100px;"><label>PAG-IBIG ID NO.: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpHDMF']; ?>" type="text" name="EmpHDMF" id="EmpHDMF" class="text_input" <?php echo $InputState; ?> /></td>
			</tr>
		</table>
		
		<?php /* Employee Philhealth and SSS Number... */ ?>
		<table class="form">
			<tr>
				<td class="form_label" style="width:100px;"><label>PHILHEALTH NO.: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpPH']; ?>" type="text" name="EmpPH" id="EmpPH" class="text_input" <?php echo $InputState; ?> /></td>
				<td class="form_label" style="width:100px;"><label>SSS NO.: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpSSS']; ?>" type="text" name="EmpSSS" id="EmpSSS" class="text_input" <?php echo $InputState; ?> /></td>
			</tr>
		</table>
		
		<?php /* Employee Residential Address... */ ?>
		<br />
		<table class="form">
			<tr>
				<td class="form_label" style="width:100px;text-align:left;"><label>RESIDENTIAL ADDRESS: </label></td>
			</tr>
		</table>
		
		<table class="form">
			<tr>
				<td class="form_label" style="width:100px;" rowspan="2"><label>&nbsp;</label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpResAddSt']; ?>" type="text" name="EmpResAddSt" id="EmpResAddSt" class="text_input"  onkeyUp="if (document.getElementById('isTheSame').checked) {document.getElementById('EmpPerAddSt').value=document.getElementById('EmpResAddSt').value; }" <?php echo $InputState; ?> /></td>
				<td class="pds_form_input"><input value="<?php echo $records["EmpResAddBrgy"]; ?>" type="text" name="EmpResAddBrgy" id="EmpResAddBrgy" class="text_input"  onkeyUp="if (document.getElementById('isTheSame').checked) {document.getElementById('EmpPerAddBrgy').value=document.getElementById('EmpResAddBrgy').value; }" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label_small"><label>Street</label></td>
				<td class="form_label_small"><label>Barangay</label></td>
			</tr>
		</table>
		
		<table class="form">
			<tr>
				<td class="form_label" style="width:100px;" rowspan="2"><label>&nbsp;</label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpResAddMun']; ?>" type="text" name="EmpResAddMun" id="EmpResAddMun" class="text_input" <?php echo $InputState; ?> /></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpResAddProv']; ?>" type="text" name="EmpResAddProv" id="EmpResAddProv" class="text_input" <?php echo $InputState; ?> /></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpResZipCode']; ?>" type="text" name="EmpResZipCode" id="EmpResZipCode" class="text_input" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label_small"><label>Municipality</label></td>
				<td class="form_label_small"><label>Province</label></td>
				<td class="form_label_small" style="width:50px;"><label>ZIP Code</label></td>
			</tr>
		</table>	
		
		<table class="form">	<?php /* Employee Residential Telephone Number... */ ?>
			<tr>
				<td class="form_label" style="width:100px;"><label>&nbsp;</label></td>
				<td class="form_label" style="width:100px;"><label>TELEPHONE NO.: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpResTel']; ?>" type="text" name="EmpResTel" id="EmpResTel" class="text_input" onkeyUp="if (document.getElementById('isTheSame').checked) {document.getElementById('EmpPerTel').value=document.getElementById('EmpResTel').value; }" <?php echo $InputState; ?> /></td>
			</tr>
		</table>
		
		<?php /* Employee Permanent Address... */ ?>
		<br />
		<table class="form">
			<tr>
				<td  class="form_label" style="width:150px;text-align:left;"><label>PERMANENT ADDRESS: </label></td><td  style="width:20px;"><input type="checkbox" name="isTheSame" id="isTheSame" checked  onClick="if (this.checked) { document.getElementById('EmpPerAddSt').value=document.getElementById('EmpResAddSt').value; document.getElementById('EmpPerAddBrgy').value=document.getElementById('EmpResAddBrgy').value; document.getElementById('EmpPerAddMun').value=document.getElementById('EmpResAddMun').value; document.getElementById('EmpPerAddProv').value=document.getElementById('EmpResAddProv').value; document.getElementById('EmpPerZipCode').value=document.getElementById('EmpResZipCode').value; document.getElementById('EmpPerTel').value=document.getElementById('EmpResTel').value; } else { document.getElementById('EmpPerAddSt').value=''; document.getElementById('EmpPerAddBrgy').value=''; document.getElementById('EmpPerAddMun').value=''; document.getElementById('EmpPerAddProv').value=''; document.getElementById('EmpPerZipCode').value=''; document.getElementById('EmpPerTel').value=''; } return true;" <?php echo $InputState; ?> /></td><td class="form_label_small" style="width:430px;text-align:left;"><label>Check if the same as residential address.</label></td>
			</tr>
		</table>
		
		<table class="form">
			<tr>
				<td class="form_label" style="width:100px;" rowspan="2"><label>&nbsp;</label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpPerAddSt']; ?>" type="text" name="EmpPerAddSt" id="EmpPerAddSt" class="text_input" <?php echo $InputState; ?> /></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpPerAddBrgy']; ?>" type="text" name="EmpPerAddBrgy" id="EmpPerAddBrgy" class="text_input" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label_small"><label>Street</label></td>
				<td class="form_label_small"><label>Barangay</label></td>
			</tr>
		</table>
		
		<table class="form">
			<tr>
				<td class="form_label" style="width:100px;" rowspan="2"><label>&nbsp;</label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpPerAddMun']; ?>" type="text" name="EmpPerAddMun" id="EmpPerAddMun" class="text_input" <?php echo $InputState; ?> /></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpPerAddProv']; ?>" type="text" name="EmpPerAddProv" id="EmpPerAddProv" class="text_input" <?php echo $InputState; ?> /></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpPerZipCode']; ?>" type="text" name="EmpPerZipCode" id="EmpPerZipCode" class="text_input" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label_small"><label>Municipality</label></td>
				<td class="form_label_small"><label>Province</label></td>
				<td class="form_label_small" style="width:50px;"><label>ZIP Code</label></td>
			</tr>
		</table>	
		
		<table class="form">	<?php /* Employee Permanent Telephone Number... */ ?>
			<tr>
				<td class="form_label" style="width:100px;"><label>&nbsp;</label></td>
				<td class="form_label" style="width:100px;"><label>TELEPHONE NO.: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpPerTel']; ?>" type="text" name="EmpPerTel" id="EmpPerTel" class="text_input" <?php echo $InputState; ?> /></td>
			</tr>
		</table>
		
		<?php /* Employee E-Mail and Mobile Number... */ ?>
		<br />
		<table class="form">
			<tr>
				<td class="form_label" style="width:100px;"><label>E-MAIL (if any): </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpEMail']; ?>" type="text" name="EmpEMail" id="EmpEMail" class="text_input" <?php echo $InputState; ?> /></td>
				<td class="form_label" style="width:150px;"><label>MOBILE NO. (if any): </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpMobile']; ?>" type="text" name="EmpMobile" id="EmpMobile" class="text_input" <?php echo $InputState; ?> /></td>
			</tr>
		</table>
		
		<?php /* Employee TIN and Agency Employee Number... */ ?>
		<table class="form">
			<tr>
				<td class="form_label" style="width:100px;"><label>TIN: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpTIN']; ?>" type="text" name="EmpTIN" id="EmpTIN" class="text_input" <?php echo $InputState; ?> /></td>
				<td class="form_label" style="width:150px;"><label>AGENCY EMPLOYEE NO.: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpAgencyNo']; ?>" type="text" name="EmpAgencyNo" id="EmpAgencyNo" class="text_input" <?php echo $InputState; ?> /></td>
			</tr>
		</table>
		
		<?php /* Employee CTC... */ ?>
		<br />
		<table class="form">
			<tr>
				<td class="form_label" style="width:100px;"><label>CTC Number: </label></td>
				<td class="pds_form_input"><input value="<?php echo $ctc['CTCID']; ?>" type="text" name="CTCID" id="CTCID" class="text_input" <?php echo $InputState; ?> /></td>
				<td class="form_label" style="width:100px;"><label>ISSUED ON: </label></td>
				<td class="pds_form_input">
				<select id="CTCDateMonth" name="CTCDateMonth" class="text_input" <?php echo $InputState; ?>>
					<?php for($m=1;$m<=12;$m++){
						if($m==intval($ctc['CTCDateMonth'])){echo "<option value='$m' selected>".$MONTHS[$m]."</option>";}
						else{echo "<option value='$m'>".$MONTHS[$m]."</option>";}
					} ?></select>
				<select id="CTCDateDay" name="CTCDateDay" class="text_input" <?php echo $InputState; ?>>
				<?php for($d=1;$d<=31;$d++){
					if($d==intval($ctc['CTCDateDay'])){echo "<option value='$d' selected>$d</option>";}
					else{echo "<option value='$d'>$d</option>";}
				} ?></select>
				<input type="text" name="CTCDateYear" id="CTCDateYear" class="text_input" style="width:30px;" value="<?php echo $ctc['CTCDateYear']; ?>" <?php echo $InputState; ?> /></td>
			</tr>
		</table>
		
		<table class="form">	<?php /* CTC Place... */ ?>
			<tr>
				<td class="form_label" style="width:100px;"><label>&nbsp;</label></td>
				<td class="form_label" style="width:100px;"><label>ISSUED AT: </label></td>
				<td class="pds_form_input"><input value="<?php echo $ctc['CTCPlace']; ?>" type="text" name="CTCPlace" id="CTCPlace" class="text_input" <?php echo $InputState; ?> /></td>
			</tr>
		</table>
		
		<br/><hr class="form_bottom_line"/>
		<?php 
			$bEditState="disabled";$bEditClass="button ui-button ui-widget ui-corner-all ui-state-disabled";$onClick="";
			if($Authorization[0]&&$Authorization[2]){$bEditState="";$bEditClass="button ui-button ui-widget ui-corner-all";$onClick="getEmpPage('pinfo',$EmpID,1);";}
		?>
		<table class="form">
			<tr>
				<td align="left"><input type="button" value="Help" class="button_help button ui-button ui-widget ui-corner-all" onClick="showHelp('001');return false;" /></td>
				<td align="right">
					<input type="button" value="Edit" class="<?php echo $bEditClass; ?>" onClick="<?php echo $onClick; ?>" <?php echo $bEditState; ?>/>&nbsp;
					<input type="submit" value="Save" class="<?php echo $bSaveClass; ?>" <?php echo $InputState; ?>/>
				</td>
			</tr>
		</table>
		<br/>
		<input type="hidden" name="mode" id="mode" value="1"/>
		<input type="hidden" name="EmpID" id="EmpID" value="<?php echo $EmpID; ?>"/><br/>
	</form>
</center>

<?php
	}
?>