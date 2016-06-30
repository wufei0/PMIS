<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD007'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$EmpID=isset($_POST['id'])?trim(strip_tags($_POST['id'])):'00000';
	$EducBgID=isset($_POST['xid'])?trim(strip_tags($_POST['xid'])):'';
	$mode=isset($_POST['mode'])?trim(strip_tags($_POST['mode'])):'0';
	echo "1|$EmpID|";
	$MONTHS=Array('','JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC');
	
	if($EmpID!='00000'){
		$MySQLi=new MySQLClass();
		$records=Array();
		$InputState="";
		if($mode==-1){
			$result=$MySQLi->sqlQuery("SELECT * FROM `tblempeducbg` WHERE `EmpID`='".$EmpID."' AND `EducBgID`='".$EducBgID."' LIMIT 1;");
			$records=mysqli_fetch_array($result, MYSQLI_BOTH);
			$InputState="disabled";
		}
		else if($mode==0){
			$records['EducLvlID']=$records['EducSchoolName']=$records['EducYrGrad']=$records['EducCourse']=$records['EducGradeLvlUnits']=$records['EducAwards']="";
			$records['EducIncAttDateFromMonth']=$records['EducIncAttDateToMonth']=date('m');
			$records['EducIncAttDateFromDay']=$records['EducIncAttDateToDay']=date('j');
			$records['EducIncAttDateFromYear']=$records['EducIncAttDateToYear']=date('Y');
		}
		else if($mode==1){
			$result=$MySQLi->sqlQuery("SELECT * FROM `tblempeducbg` WHERE `EmpID`='".$EmpID."' AND `EducBgID`='".$EducBgID."' LIMIT 1;");
			$records=mysqli_fetch_array($result, MYSQLI_BOTH);
		}
?>
<center>
	<form name="f_dpnt_info" onSubmit="processForm('educ',this);return false;"><br/>
		<table class="form_window">	<?php //Spouse"s Name... ?>
			<tr>
				<td class="form_label" style="width:200px;"><label>LEVEL: </label></td>
				<td class="pds_form_input">
					<select id="EducLvlID" name="EducLvlID" class="text_input" <?php echo $InputState; ?> onChange="if(this.value!='0'){document.getElementById('EducLvlDesc').value='';document.getElementById('EducLvlDesc').disabled=true;}else{document.getElementById('EducLvlDesc').disabled=false;}">
						<?php
							$EducLvlDesc="";
							$result=$MySQLi->sqlQuery("SELECT * FROM `tbleduclevels` ORDER BY `EducLvlDesc`;");
							while($lvls=mysqli_fetch_array($result, MYSQLI_BOTH)) {
								if($lvls['EducLvlID']==$records['EducLvlID']){echo "<option value='".$lvls['EducLvlID']."' selected>".$lvls['EducLvlDesc']."</option>";$EducLvlDesc=$lvls['EducLvlDesc'];}
								else{echo "<option value='".$lvls['EducLvlID']."'>".$lvls['EducLvlDesc']."</option>";}
							} unset($result);
						?>
						<option value="0">Other, Specify</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;">&nbsp;</td>
				<td class="pds_form_input"><input value="" type="text" id="EducLvlDesc" name="EducLvlDesc" class="text_input sml_frm_fld" <?php echo $InputState; ?> disabled /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>NAME OF SCHOOL: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EducSchoolName']; ?>" type="text" name="EducSchoolName" id="EducSchoolName" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>DEGREE COURSE: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EducCourse']; ?>" type="text" name="EducCourse" id="EducCourse" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>YEAR GRADUATED: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EducYrGrad']; ?>" type="text" name="EducYrGrad" id="EducYrGrad" class="text_input sml_frm_fld" <?php echo $InputState; ?> style="width:30px;" /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>HIGHEST GRADE/LEVEL/UNITS: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EducGradeLvlUnits']; ?>" type="text" name="EducGradeLvlUnits" id="EducGradeLvlUnits" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>INCLUSIVE DATE FROM: </label></td>
				<td class="pds_form_input">
				<select id="EducIncAttDateFromMonth" name="EducIncAttDateFromMonth" class="text_input" <?php echo $InputState; ?>>
					<?php for($m=1;$m<=12;$m++){if($m==intval($records['EducIncAttDateFromMonth'])){echo "<option value='$m' selected>".$MONTHS[$m]."</option>";}else{echo "<option value='$m'>".$MONTHS[$m]."</option>";}} ?>
				</select>
				<select id="EducIncAttDateFromDay" name="EducIncAttDateFromDay" class="text_input" <?php echo $InputState; ?>>
					<?php for($d=1;$d<=31;$d++){if($d==intval($records['EducIncAttDateFromDay'])){echo "<option value='$d' selected>$d</option>";}else{echo "<option value='$d'>$d</option>";}} ?>
				</select>
				<input type="text" name="EducIncAttDateFromYear" id="EducIncAttDateFromYear" class="text_input sml_frm_fld" <?php echo $InputState; ?> style="width:30px;" value="<?php echo $records['EducIncAttDateFromYear']; ?>" /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>INCLUSIVE DATE TO: </label></td>
				<td class="pds_form_input">
				<select id="EducIncAttDateToMonth" name="EducIncAttDateToMonth" class="text_input" <?php echo $InputState; ?>>
					<?php for($m=1;$m<=12;$m++){if($m==intval($records['EducIncAttDateToMonth'])){echo "<option value='$m' selected>".$MONTHS[$m]."</option>";}else{echo "<option value='$m'>".$MONTHS[$m]."</option>";}} ?>
				</select>
				<select id="EducIncAttDateToDay" name="EducIncAttDateToDay" class="text_input" <?php echo $InputState; ?>>
					<?php for($d=1;$d<=31;$d++){if($d==intval($records['EducIncAttDateToDay'])){echo "<option value='$d' selected>$d</option>";}else{echo "<option value='$d'>$d</option>";}} ?>
				</select>
				<input type="text" name="EducIncAttDateToYear" id="EducIncAttDateToYear" class="text_input" <?php echo $InputState; ?> style="width:30px;" value="<?php echo $records['EducIncAttDateToYear']; ?>" /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>AWARDS/HONORS/SCHOOLARSHIPS: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['EducAwards']; ?>" type="text" name="EducAwards" id="EducAwards" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
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
		<input type="hidden" name="EducBgID" id="EducBgID" value="<?php echo $EducBgID; ?>" />
		<input type="hidden" name="EmpID" id="EmpID" value="<?php echo $EmpID; ?>" />
	</form>
</center>
<?php } ?>
