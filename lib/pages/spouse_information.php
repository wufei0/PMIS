<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD004'));
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$EmpID=isset($_POST['id'])?trim(strip_tags($_POST['id'])):'00000';
	$mode=isset($_POST['mode'])?trim(strip_tags($_POST['mode'])):0;  /* 0 - VIEW, 1 - UPDATE */
	
	if(!(($Authorization[0])||($_SESSION['user']==$EmpID))){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	
	echo "1|$EmpID|";
	
	$InputState="disabled";
	$records=Array();
	if($EmpID!='00000'){
		if($mode==0){
			$MySQLi=new MySQLClass();
			$result = $MySQLi -> sqlQuery("SELECT `EmpSpsLName`, `EmpSpsFName`, `EmpSpsMName`, `EmpSpsExtName`, `EmpSpsAddSt`, `EmpSpsAddBrgy`, `EmpSpsAddMun`, `EmpSpsAddProv`, `EmpSpsZipCode`, `EmpSpsTel`, `EmpSpsJob`, `EmpSpsBusDesc`, `EmpSpsBusAddSt`, `EmpSpsBusAddBrgy`, `EmpSpsBusAddMun`, `EmpSpsBusAddProv`, `EmpSpsBusZipCode`, `EmpSpsBusTel` FROM `tblemppersonalinfo` WHERE `EmpID` = '".$EmpID."';");
			$records=mysqli_fetch_array($result, MYSQLI_BOTH);
			
			if((!$Authorization[2])||($_SESSION['user']!=$EmpID)){$bEditState="disabled";$bEditClass="button ui-button ui-widget ui-corner-all ui-state-disabled";}
			else{$bEditState="";$bEditClass="button ui-button ui-widget ui-corner-all";}
			
			$InputState="disabled";
			$bSaveClass="button ui-button ui-widget ui-corner-all ui-state-disabled";
			$InputState="disabled";
			$bSaveClass="button ui-button ui-widget ui-corner-all ui-state-disabled";
		}
		else if($mode==1){
			$MySQLi=new MySQLClass();
			$result = $MySQLi -> sqlQuery("SELECT `EmpSpsLName`, `EmpSpsFName`, `EmpSpsMName`, `EmpSpsExtName`, `EmpSpsAddSt`, `EmpSpsAddBrgy`, `EmpSpsAddMun`, `EmpSpsAddProv`, `EmpSpsZipCode`, `EmpSpsTel`, `EmpSpsJob`, `EmpSpsBusDesc`, `EmpSpsBusAddSt`, `EmpSpsBusAddBrgy`, `EmpSpsBusAddMun`, `EmpSpsBusAddProv`, `EmpSpsBusZipCode`, `EmpSpsBusTel` FROM `tblemppersonalinfo` WHERE `EmpID` = '".$EmpID."';");
			$records=mysqli_fetch_array($result, MYSQLI_BOTH); 
			$InputState="";
			$bEditClass="button ui-button ui-widget ui-corner-all ui-state-disabled";
			$bSaveClass="button ui-button ui-widget ui-corner-all";
		}
		
?>

<center>
	<form name="f_emp_info" onSubmit="processForm('spsi',this);return false;"><br/>
		<table class="form">	<?php //Spouse"s Name... ?>
			<tr>
				<td class="form_label" style="width:100px;"><label>SPOUSE'S SURNAME: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpSpsLName']; ?>" type="text" name="EmpSpsLName" id="EmpSpsLName" class="text_input" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label"><label>SPOUSE'S FIRST NAME: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpSpsFName']; ?>" type="text" name="EmpSpsFName" id="EmpSpsFName" class="text_input" <?php echo $InputState; ?> /></td>
			</tr>
		</table>
		
		<table class="form">
			<tr>
				<td class="form_label" style="width:100px;"><label>SPOUSE'S MIDDLE NAME: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpSpsMName']; ?>" type="text" name="EmpSpsMName" id="EmpSpsMName" class="text_input" <?php echo $InputState; ?> /></td>
				<td class="form_label" style="width:85px;"><label>NAME EXTENSION: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpSpsExtName']; ?>" type="text" name="EmpSpsExtName" id="EmpSpsExtName" class="text_input" <?php echo $InputState; ?> /></td>
			</tr>
		</table>

		<table class="form">	<?php //Spouse"s Address... ?>
			<tr>
				<td class="form_label" style="width:100px;text-align:left;"><label>SPOUSE'S ADDRESS: </label></td><td  width="20px"><input type="checkbox" name="isTheSame" id="isTheSame" onClick="FetchSpouseAddress(this)" <?php echo $InputState; ?> /></td><td class="form_label_small_l"><label>Check if the same address as spouse.</label></td>
			</tr>
		</table>
		<table class="form">
			<tr>
				<td class="form_label" style="width:100px;" rowspan="2"><label>&nbsp;</label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpSpsAddSt']; ?>" type="text" name="EmpSpsAddSt" id="EmpSpsAddSt" class="text_input" <?php echo $InputState; ?> /></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpSpsAddBrgy']; ?>" type="text" name="EmpSpsAddBrgy" id="EmpSpsAddBrgy" class="text_input" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label_small"><label>Street</label></td>
				<td class="form_label_small"><label>Barangay</label></td>
			</tr>
		</table>
		
		<table class="form">
			<tr>
				<td class="form_label" style="width:100px;" rowspan="2"><label>&nbsp;</label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpSpsAddMun']; ?>" type="text" name="EmpSpsAddMun" id="EmpSpsAddMun" class="text_input" <?php echo $InputState; ?> /></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpSpsAddProv']; ?>" type="text" name="EmpSpsAddProv" id="EmpSpsAddProv" class="text_input" <?php echo $InputState; ?> /></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpSpsZipCode']; ?>" type="text" name="EmpSpsZipCode" id="EmpSpsZipCode" class="text_input" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label_small"><label>Municipality</label></td>
				<td class="form_label_small"><label>Province</label></td>
				<td class="form_label_small" style="width:50px;"><label>ZIP Code</label></td>
			</tr>
		</table>	
		
		<table class="form">
			<tr>
				<td class="form_label"><label>&nbsp;</label></td>
				<td class="form_label"><label>TELEPHONE NO.: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpSpsTel']; ?>" type="text" name="EmpSpsTel" id="EmpSpsTel" class="text_input" <?php echo $InputState; ?> /></td>
			</tr>
		</table><br/>
		
		<table class="form">	<?php //Spouse's Job Description... ?>
			<tr>
				<td class="form_label" style="width:100px;"><label>OCCUPATION: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpSpsJob']; ?>" type="text" name="EmpSpsJob" id="EmpSpsJob" class="text_input" <?php echo $InputState; ?> /></td>
			</tr>
		</table>
		
		<table class="form">	<?php //Spouse's Business... ?>
			<tr>
				<td class="form_label" style="width:100px;"><label>BUSINESS/EMPLOYER: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpSpsBusDesc']; ?>" type="text" name="EmpSpsBusDesc" id="EmpSpsBusDesc" class="text_input" <?php echo $InputState; ?> /></td>
			</tr>
		</table>
		
		<table class="form">	<?php //Spouse's Business Address... ?>
			<tr>
				<td class="form_label" style="width:100px;text-align:left;"><label> </label></td>
			</tr>
		</table>
		<table class="form">
			<tr>
				<td class="form_label" style="width:100px;"><label>BUSINESS ADDRESS:</label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpSpsBusAddSt']; ?>" type="text" name="EmpSpsBusAddSt" id="EmpSpsBusAddSt" class="text_input" <?php echo $InputState; ?> /></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpSpsBusAddBrgy']; ?>" type="text" name="EmpSpsBusAddBrgy" id="EmpSpsBusAddBrgy" class="text_input" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label_small"></td>
				<td class="form_label_small"><label>Street</label></td>
				<td class="form_label_small"><label>Barangay</label></td>
			</tr>
		</table>
		
		<table class="form">
			<tr>
				<td class="form_label" style="width:100px;" rowspan="2"><label>&nbsp;</label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpSpsBusAddMun']; ?>" type="text" name="EmpSpsBusAddMun" id="EmpSpsBusAddMun" class="text_input" <?php echo $InputState; ?> /></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpSpsBusAddProv']; ?>" type="text" name="EmpSpsBusAddProv" id="EmpSpsBusAddProv" class="text_input" <?php echo $InputState; ?> /></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpSpsBusZipCode']; ?>" type="text" name="EmpSpsBusZipCode" id="EmpSpsBusZipCode" class="text_input" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label_small"><label>Municipality</label></td>
				<td class="form_label_small"><label>Province</label></td>
				<td class="form_label_small" style="width:50px;"><label>ZIP Code</label></td>
			</tr>
		</table>	
		
		<table class="form">
			<tr>
				<td class="form_label"><label>&nbsp;</label></td>
				<td class="form_label"><label>TELEPHONE NO.: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpSpsBusTel']; ?>" type="text" name="EmpSpsBusTel" id="EmpSpsBusTel" class="text_input" <?php echo $InputState; ?> /></td>
			</tr>
		</table>
		
		<br/><hr class="form_bottom_line"/>
		
		<?php 
			$bEditState="disabled";$bEditClass="button ui-button ui-widget ui-corner-all ui-state-disabled";$onClick="";
			if($Authorization[0]&&$Authorization[2]){$bEditState="";$bEditClass="button ui-button ui-widget ui-corner-all";$onClick="getEmpPage('spsi',$EmpID,1);";}
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
		
		<input type="hidden" name="mode" id="mode" value="1"/>
		<input type="hidden" name="EmpID" id="EmpID" value="<?php echo $EmpID; ?>"/><br/><br/>
	</form>
</center>
<?php } ?>