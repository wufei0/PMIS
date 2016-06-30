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
	
	
	
	$EmpID=isset($_POST['id']) ? trim(strip_tags($_POST['id'])) : '00000';
	$mode=isset($_POST['mode']) ? trim(strip_tags($_POST['mode'])) : '0';
	echo "1|$EmpID|";
	
	$MONTHS=Array('','JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC');
	
	$MySQLi=new MySQLClass();
	$records=Array();
	$InputState="";
	if($mode==-1){
		$result=$MySQLi->sqlQuery("SELECT * FROM `tblemppersonalinfo` WHERE `EmpID`='".$EmpID."' LIMIT 1;");
		$records=mysql_fetch_array($result);
		$InputState="disabled";
	}
	else if($mode==0){
		$records['EmpLName']=$records['EmpMName']=$records['EmpFName']=$records['EmpExtName']=$records['EmpBirthMonth']=$records['EmpBirthDay']=$records['RelID']=$records['EmpRemarks']="";$records['EmpBirthYear']=date('Y');
	}
	else if($mode==1){
		$result=$MySQLi->sqlQuery("SELECT * FROM `tblemppersonalinfo` WHERE `EmpID`='".$EmpID."' LIMIT 1;");
		$records=mysql_fetch_array($result);
	}
?>

<center>
	<form name="f_Emp_info" onSubmit="processForm('newp',this);return false;"><br/>
		<table class="form_window">	<?php //Spouse"s Name... ?>
			<tr>
				<td class="form_label" style="width:200px;"><label>APPOINTMENT STATUS: </label></td>
				<td class="pds_form_input">
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
				<td class="form_label" style="width:200px;"><label>PERSONNEL'S LAST NAME: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpLName']; ?>" type="text" name="EmpLName" id="EmpLName_w" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label"><label>PERSONNEL'S FIRST NAME: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpFName']; ?>" type="text" name="EmpFName" id="EmpFName_w" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>PERSONNEL'S MIDDLE NAME: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpMName']; ?>" type="text" name="EmpMName" id="EmpMName_w" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>NAME EXTENSION: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpExtName']; ?>" type="text" name="EmpExtName" id="EmpExtName" class="text_input" <?php echo $InputState; ?> style="width:30px;"/></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>DATE OF BIRTH: </label></td>
				<td class="pds_form_input">
				<select id="EmpBirthMonth" name="EmpBirthMonth" class="text_input" <?php echo $InputState; ?>>
					<?php for($m=1;$m<=12;$m++){if($m==intval($records['EmpBirthMonth'])){echo "<option value='$m' selected>".$MONTHS[$m]."</option>";}else{echo "<option value='$m'>".$MONTHS[$m]."</option>";}} ?>
				</select>
				<select id="EmpBirthDay" name="EmpBirthDay" class="text_input" <?php echo $InputState; ?>>
					<?php for($d=1;$d<=31;$d++){if($d==intval($records['EmpBirthDay'])){echo "<option value='$d' selected>$d</option>";}else{echo "<option value='$d'>$d</option>";}} ?>
				</select>
				<input type="text" name="EmpBirthYear" id="EmpBirthYear" class="text_input" <?php echo $InputState; ?> style="width:30px;" value="<?php echo $records['EmpBirthYear']; ?>" /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>SEX: </label></td>
				<td class="pds_form_input">
				<select id="EmpSex" name="EmpSex" class="text_input" <?php echo $InputState; ?>>
					<?php if("MALE"==$records['EmpSex']){echo "<option value='MALE' selected>MALE</option>";}else{echo "<option value='MALE'>MALE</option>";}
								if("FEMALE"==$records['EmpSex']){echo "<option value='FEMALE' selected>FEMALE</option>";}else{echo "<option value='FEMALE'>FEMALE</option>";} ?>
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
		<input type="hidden" name="EmpID" id="EmpID" value="<?php echo $EmpID; ?>" />
	</form>
</center>