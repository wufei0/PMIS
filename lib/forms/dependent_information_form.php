<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD005'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$EmpID=isset($_POST['id']) ? trim(strip_tags($_POST['id'])) : '00000';
	$DpntID=isset($_POST['xid']) ? trim(strip_tags($_POST['xid'])) : '';
	$mode=isset($_POST['mode']) ? trim(strip_tags($_POST['mode'])) : '0';
	echo "1|$EmpID|";
	
	$MONTHS=Array('','JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC');
	
	if($EmpID!='00000'){
		$MySQLi=new MySQLClass();
		$records=Array();
		$InputState="";
		if($mode==-1){
			$result=$MySQLi->sqlQuery("SELECT * FROM `tblempdependents` WHERE `EmpID`='".$EmpID."' AND `DpntID`='".$DpntID."' LIMIT 1;");
			$records=mysqli_fetch_array($result, MYSQLI_BOTH);
			$InputState="disabled";
		}
		else if($mode==0){
			$records['DpntLName']=$records['DpntMName']=$records['DpntFName']=$records['DpntExtName']=$records['DpntBirthMonth']=$records['DpntBirthDay']=$records['RelID']=$records['DpntRemarks']="";$records['DpntBirthYear']=date('Y');
		}
		else if($mode==1){
			$result=$MySQLi->sqlQuery("SELECT * FROM `tblempdependents` WHERE `EmpID`='".$EmpID."' AND `DpntID`='".$DpntID."' LIMIT 1;");
			$records=mysqli_fetch_array($result, MYSQLI_BOTH);
		}
?>

<center>
	<form name="f_dpnt_info" onSubmit="processForm('dpnt',this);return false;"><br/>
		<table class="form_window">	<?php //Spouse"s Name... ?>
			<tr>
				<td class="form_label" style="width:200px;"><label>DEPENDENT'S LAST NAME: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['DpntLName']; ?>" type="text" name="DpntLName" id="DpntLName" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label"><label>DEPENDENT'S FIRST NAME: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['DpntFName']; ?>" type="text" name="DpntFName" id="DpntFName" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>DEPENDENT'S MIDDLE NAME: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['DpntMName']; ?>" type="text" name="DpntMName" id="DpntMName" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>NAME EXTENSION: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['DpntExtName']; ?>" type="text" name="DpntExtName" id="DpntExtName" class="text_input" <?php echo $InputState; ?> style="width:30px;"/></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>DATE OF BIRTH: </label></td>
				<td class="pds_form_input">
				<select id="DpntBirthMonth" name="DpntBirthMonth" class="text_input" <?php echo $InputState; ?>>
					<?php for($m=1;$m<=12;$m++){if($m==intval($records['DpntBirthMonth'])){echo "<option value='$m' selected>".$MONTHS[$m]."</option>";}else{echo "<option value='$m'>".$MONTHS[$m]."</option>";}} ?>
				</select>
				<select id="DpntBirthDay" name="DpntBirthDay" class="text_input" <?php echo $InputState; ?>>
					<?php for($d=1;$d<=31;$d++){if($d==intval($records['DpntBirthDay'])){echo "<option value='$d' selected>$d</option>";}else{echo "<option value='$d'>$d</option>";}} ?>
				</select>
				<input type="text" name="DpntBirthYear" id="DpntBirthYear" class="text_input" <?php echo $InputState; ?> style="width:30px;" value="<?php echo $records['DpntBirthYear']; ?>" /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>SEX: </label></td>
				<td class="pds_form_input">
				<select id="DpntSex" name="DpntSex" class="text_input" <?php echo $InputState; ?>>
					<?php if("MALE"==$records['DpntSex']){echo "<option value='MALE' selected>MALE</option>";}else{echo "<option value='MALE'>MALE</option>";}
								if("FEMALE"==$records['DpntSex']){echo "<option value='FEMALE' selected>FEMALE</option>";}else{echo "<option value='FEMALE'>FEMALE</option>";} ?>
				</select>
				</td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>RELATIONSHIP: </label></td>
				<td class="pds_form_input">
					<select id="RelID" name="RelID" class="text_input" <?php echo $InputState; ?> onChange="if(this.value!='0'){document.getElementById('RelDesc').value='';document.getElementById('RelDesc').disabled=true;}else{document.getElementById('RelDesc').disabled=false;}">
						<?php
							$RelDesc="";
							$result=$MySQLi->sqlQuery("SELECT * FROM `tbldpntrelationships` WHERE `RelID`<>'R000' ORDER BY `RelDesc`;");
							while($rels=mysqli_fetch_array($result, MYSQLI_BOTH)) {
								if($rels['RelID']==$records['RelID']){echo "<option value='".$rels['RelID']."' selected>".$rels['RelDesc']."</option>";$RelDesc=$rels['RelDesc'];}
								else{echo "<option value='".$rels['RelID']."'>".$rels['RelDesc']."</option>";}
							} unset($result);
						?>
						<option value="0">Other, Specify</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;">&nbsp;</td>
				<td class="pds_form_input"><input value="" type="text" id="RelDesc" name="RelDesc" class="text_input sml_frm_fld" <?php echo $InputState; ?> disabled /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>REMARKS: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['DpntRemarks']; ?>" type="text" name="DpntRemarks" id="DpntRemarks" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
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
		<input type="hidden" name="DpntID" id="DpntID" value="<?php echo $DpntID; ?>" />
		<input type="hidden" name="EmpID" id="EmpID" value="<?php echo $EmpID; ?>" />
	</form>
</center>
<?php } ?>