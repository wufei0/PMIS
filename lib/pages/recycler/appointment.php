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
	$mode=isset($_POST['mode'])?mysql_escape_string(trim(strip_tags($_POST['mode']))):0;  /* 0 - VIEW, 1 - UPDATE */
	
	if(!(($Authorization[0])||($_SESSION['user']==$EmpID))){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	
	echo "1|$EmpID|";
	$MONTHS=Array('','JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC');
	
	$SRecSalary="0.00";
	$InputState="disabled";
	$records = Array();
	if($EmpID!='00000'){
		if($mode==0){
			$Config = new Conf();
			$MySQLi = new MySQLClass($Config);
			$result=$MySQLi->sqlQuery("SELECT * FROM `tblempservicerecords` WHERE `EmpID`='".$EmpID."' AND `SRecIsGov`='YES' ORDER BY `SRecFromYear` DESC, `SRecFromMonth` DESC, `SRecFromDay` DESC LIMIT 1;");
			if($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
				$SRecSalary=number_format((float)$records['SRecSalary'],2,'.',',');
				
				$result=$MySQLi->sqlQuery("SELECT `PosDesc` FROM `tblpositions` WHERE `PosID`='".$records['PosID']."' LIMIT 1;");
				$positions=mysql_fetch_array($result); 
				$records['PosDesc']=$positions['PosDesc'];
				
				$result=$MySQLi->sqlQuery("SELECT `SubOffName` FROM `tblsuboffices` WHERE `SubOffID`='".$records['MotherOfficeID']."' LIMIT 1;");
				$moffices=mysql_fetch_array($result); 
				$records['MotherOfficeDesc']=$moffices['SubOffName'];
				
				$result=$MySQLi->sqlQuery("SELECT `SubOffName` FROM `tblsuboffices` WHERE `SubOffID`='".$records['AssignedOfficeID']."' LIMIT 1;");
				$aoffices=mysql_fetch_array($result); 
				$records['AssignedOfficeDesc']=$aoffices['SubOffName'];
			}
			$InputState="disabled";
		}
		else if($mode==1){
			$Config = new Conf();
			$MySQLi = new MySQLClass($Config);
			$result=$MySQLi->sqlQuery("SELECT * FROM `tblempservicerecords` WHERE `EmpID`='".$EmpID."' AND `SRecIsGov`='YES' ORDER BY `SRecFromYear` DESC, `SRecFromMonth` DESC, `SRecFromDay` DESC LIMIT 1;");
			if($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
				$SRecSalary=number_format((float)$records['SRecSalary'],2,'.',',');
				
				$result=$MySQLi->sqlQuery("SELECT `PosDesc` FROM `tblpositions` WHERE `PosID`='".$records['PosID']."' LIMIT 1;");
				$positions=mysql_fetch_array($result); 
				$records['PosDesc']=$positions['PosDesc'];
				
				$result=$MySQLi->sqlQuery("SELECT `SubOffName` FROM `tblsuboffices` WHERE `SubOffID`='".$records['MotherOfficeID']."' LIMIT 1;");
				$moffices=mysql_fetch_array($result); 
				$records['MotherOfficeDesc']=$moffices['SubOffName'];
				
				$result=$MySQLi->sqlQuery("SELECT `SubOffName` FROM `tblsuboffices` WHERE `SubOffID`='".$records['AssignedOfficeID']."' LIMIT 1;");
				$aoffices=mysql_fetch_array($result); 
				$records['AssignedOfficeDesc']=$aoffices['SubOffName'];
			}
			$InputState="";
		}
?>
<center>
	<form name="PDS_Appointment" id="PDS_Appointment" onSubmit="processApptInfo(this,-1);return false;"><br/>
		<?php //Inclusive date... ?>
		<table class="form">
			<tr>
				<td class="form_label" style="width:110px;"><label>INCLUSIVE DATES:</label></td>
				<td class="form_label" style="width:50px;"><label>FROM: </label></td>
				<td class="pds_form_input">
					<select id="SRecFromMonth" name="SRecFromMonth" class="text_input" <?php echo $InputState; ?>>
					<?php for($m=1;$m<=12;$m++){if($m==intval($records['SRecFromMonth'])){echo "<option value='$m' selected>".$MONTHS[$m]."</option>";}else{echo "<option value='$m'>".$MONTHS[$m]."</option>";}} ?>
					</select>
					<select id="SRecFromDay" name="SRecFromDay" class="text_input" <?php echo $InputState; ?>>
						<?php for($d=1;$d<=31;$d++){if($d==intval($records['SRecFromDay'])){echo "<option value='$d' selected>$d</option>";}else{echo "<option value='$d'>$d</option>";}} ?>
					</select>
					<input type="text" name="SRecFromYear" id="SRecFromYear" class="text_input sml_frm_fld" <?php echo $InputState; ?> style="width:30px;" value="<?php echo $records['SRecFromYear']; ?>" />
				</td>
				
				<td class="form_label" style="width:50px;"><label>TO: </label></td>
				<td class="pds_form_input">
					<select id="SRecToMonth" name="SRecToMonth" class="text_input" <?php echo $InputState; ?>>
					<?php for($m=1;$m<=12;$m++){if($m==intval($records['SRecToMonth'])){echo "<option value='$m' selected>".$MONTHS[$m]."</option>";}else{echo "<option value='$m'>".$MONTHS[$m]."</option>";}} ?>
					</select>
					<select id="SRecToDay" name="SRecToDay" class="text_input" <?php echo $InputState; ?>>
						<?php for($d=1;$d<=31;$d++){if($d==intval($records['SRecToDay'])){echo "<option value='$d' selected>$d</option>";}else{echo "<option value='$d'>$d</option>";}} ?>
					</select>
					<input type="text" name="SRecToYear" id="SRecToYear" class="text_input" <?php echo $InputState; ?> style="width:30px;" value="<?php echo $records['SRecToYear']; ?>" />
				</td>
			</tr>
		</table>
		
		<table class="form">
			<tr>
				<td class="form_label" style="width:110px;"><label>POSITION: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['PosDesc']; ?>" type="text" name="PosDesc" id="PosDesc" class="text_input" style="width:225px;" <?php if($mode!=1){echo "disabled";}else{echo "readonly";} ?> /><input value="<?php echo $records['PosID']; ?>" type="hidden" name="PosID" id="PosID" /></td><td><ul class="ui-widget ui-helper-clearfix ul-icons"><li id="sPosition" class="ui-state-default ui-corner-all" title="Select Position" onClick="selectWindow(this);"><span class="ui-icon ui-icon-clipboard"></span></li></ul></td>
				<td class="form_label" style="width:155px;"><label>STATUS OF APPOINTMENT: </label></td>
				<td class="pds_form_input">
					<select type="text" name="ApptStID" id="ApptStID" class="text_input" <?php echo $InputState; ?>>
					<?php
						$result = $MySQLi -> sqlQuery("SELECT * FROM `tblapptstatus` ORDER BY `ApptStDesc`;");
						while($appstatuses = mysql_fetch_array($result)) {
							if($appts['ApptStID']==$appstatuses['ApptStID']) { echo "\n\t<option value='".$appstatuses['ApptStID']."' selected>".$appstatuses['ApptStDesc']."</option>"; }
							else { echo "\n\t<option value='".$appstatuses['ApptStID']."'>".$appstatuses['ApptStDesc']."</option>"; }
						} unset($result);
					?>
					</select>
				</td>
			</tr>
		</table>
		
		<table class="form">	
			<tr>
				<td class="form_label" style="width:110px;"><label>MOTHER OFFICE: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['MotherOfficeDesc']; ?>" type="text" name="MotherOfficeDesc" id="MotherOfficeDesc" class="text_input" style="width:470px;" <?php if($mode!=1){echo "disabled";}else{echo "readonly";} ?> /><input value="<?php echo $records['MotherOfficeID']; ?>" type="hidden" name="MotherOfficeID" id="MotherOfficeID" /></td><td><ul class="ui-widget ui-helper-clearfix ul-icons"><li id="sMotherOffice" class="ui-state-default ui-corner-all" title="Select Mother Office" onClick="selectWindow(this);"><span class="ui-icon ui-icon-clipboard"></span></li></ul></td>
			</tr>	
			<tr>
				<td class="form_label" style="width:110px;"><label>ASSIGNED OFFICE: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['AssignedOfficeDesc']; ?>" type="text" name="AssignedOfficeDesc" id="AssignedOfficeDesc" class="text_input" style="width:470px;" <?php if($mode!=1){echo "disabled";}else{echo "readonly";} ?> /><input value="<?php echo $records['AssignedOfficeID']; ?>" type="hidden" name="AssignedOfficeID" id="AssignedOfficeID" /></td><td><ul class="ui-widget ui-helper-clearfix ul-icons"><li id="sAssignedOffice" class="ui-state-default ui-corner-all" title="Select Assigned Office" onClick="selectWindow(this);"><span class="ui-icon ui-icon-clipboard"></span></li></ul></td>
			</tr>
		</table>
		
		<table class="form">	<?php //Monthly Salary, Salary Grade and Government Service... ?>
			<tr>
				<td class="form_label" style="width:110px;"><label>SALARY GRADE: </label></td>
				<td class="pds_form_input">
					<select id="SalGrade" name="SalGrade" class="text_input" <?php echo $InputState; ?>>
					<?php for($SalGrade=0;$SalGrade<=35;$SalGrade++){if($SalGrade==intval(substr($records['SalGrdID'],4,2))){echo "<option value='$SalGrade' selected>$SalGrade</option>";}else{echo "<option value='$SalGrade'>$SalGrade</option>";}} ?>
					</select> - 
					<select id="SalStep" name="SalStep" class="text_input" <?php echo $InputState; ?>>
						<?php for($SalStep=0;$SalStep<=10;$SalStep++){if($SalStep==intval(substr($records['SalGrdID'],6,2))){echo "<option value='$SalStep' selected>$SalStep</option>";}else{echo "<option value='$SalStep'>$SalStep</option>";}} ?>
					</select>
				</td>
				<td class="form_label" style="width:90px;"><label>MONTHLY SALARY: </label></td>
				<td class="pds_form_input"><input type="text" name="SRecSalary" id="SRecSalary" class="text_input" value="<?php echo $SRecSalary; ?>" style="text-align:right;" disabled /></td>
			</tr>
		</table>
		<?php //Clear and Save/Submit Button... ?>
		<br />
<?php if($mode==1){echo '<input type="submit" value="SAVE">';} ?>
		<input type="hidden" name="mode" id="mode" value="1"/>
		<input type="hidden" name="SRecID" id="SRecID" value="<?php echo $records['SRecID']; ?>" />
		<input type="hidden" name="EmpID" id="EmpID" value="<?php echo $EmpID; ?>"/><br/><br/>
	</form>
</center>
<?php } ?>
