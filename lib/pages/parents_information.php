<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD006'));
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$EmpID=isset($_POST['id'])?trim(strip_tags($_POST['id'])):'00000';
	$mode=isset($_POST['mode'])?trim(strip_tags($_POST['mode'])):0;  /* 0 - VIEW, 1 - UPDATE */
	
	if(!(($Authorization[0])||($_SESSION['user']==$EmpID))){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	
	echo "1|$EmpID|";
	
	$InputState="disabled";
	$records=Array();
	if($EmpID!='00000'){
		$MySQLi=new MySQLClass();
		$result=$MySQLi->sqlQuery("SELECT `EmpFatherLName`,`EmpFatherFName`,`EmpFatherMName`,`EmpFatherExtName`,`EmpMotherLName`,`EmpMotherFName`,`EmpMotherMName` FROM `tblemppersonalinfo` WHERE `EmpID`='".$EmpID."';");
		$records=mysqli_fetch_array($result, MYSQLI_BOTH); 
		if($mode==0){$InputState="disabled";$bEditClass="button ui-button ui-widget ui-corner-all";$bSaveClass="button ui-button ui-widget ui-corner-all ui-state-disabled";}
		else if($mode==1){$InputState="";$bEditClass="button ui-button ui-widget ui-corner-all ui-state-disabled";$bSaveClass="button ui-button ui-widget ui-corner-all";}
		
?>

<center>

	<form name="f_emp_info" onSubmit="processForm('prnt',this);return false;"><br/>
		<?php //Father's NAME... ?>
		<table class="form">
			<tr>
				<td class="form_label" style="width:100px;text-align:left;"><label>FATHER'S NAME: </label></td>
			</tr>
		</table>
		<table class="form">
			<tr>
				<td class="form_label"  style="width:100px;"><label>SURNAME: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpFatherLName']; ?>" type="text" name="EmpFatherLName" id="EmpFatherLName" class="text_input" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label"  style="width:100px;"><label>FIRST NAME: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpFatherFName']; ?>" type="text" name="EmpFatherFName" id="EmpFatherFName" class="text_input" <?php echo $InputState; ?> /></td>
			</tr>
		</table>
		<table class="form">
			<tr>
				<td class="form_label"  style="width:100px;"><label>MIDDLE NAME: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpFatherMName']; ?>" type="text" name="EmpFatherMName" id="EmpFatherMName" class="text_input" <?php echo $InputState; ?> /></td>
				<td class="form_label" style="width:85px;"><label>NAME EXTENSION: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpFatherExtName']; ?>" type="text" name="EmpFatherExtName" id="EmpFatherExtName" class="text_input" <?php echo $InputState; ?> /></td>
			</tr>
		</table>
		
		<br />
		<?php //Mother"s Maiden NAME... ?>
		<table class="form">
			<tr>
				<td class="form_label" style="width:100px;text-align:left;"><label>MOTHER'S MAIDEN NAME: </label></td>
			</tr>
		</table>
		<table class="form">
			<tr>
				<td class="form_label"  style="width:100px;"><label>SURNAME: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpMotherLName']; ?>" type="text" name="EmpMotherLName" id="EmpMotherLName" class="text_input" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label"  style="width:100px;"><label>FIRST NAME: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpMotherFName']; ?>" type="text" name="EmpMotherFName" id="EmpMotherFName" class="text_input" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label"  style="width:100px;"><label>MIDDLE NAME: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EmpMotherMName']; ?>" type="text" name="EmpMotherMName" id="EmpMotherMName" class="text_input" <?php echo $InputState; ?> /></td>
			</tr>
		</table>
		<br/><hr class="form_bottom_line"/>
		
		<?php 
			$bEditState="disabled";$bEditClass="button ui-button ui-widget ui-corner-all ui-state-disabled";$onClick="";
			if($Authorization[0]&&$Authorization[2]){$bEditState="";$bEditClass="button ui-button ui-widget ui-corner-all";$onClick="getEmpPage('prnt',$EmpID,1);";}
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
	</form><br/>
</center>
<?php } ?>