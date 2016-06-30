<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD008'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$EmpID=isset($_POST['id'])?trim(strip_tags($_POST['id'])):'00000';
	$CSEID=isset($_POST['xid'])?trim(strip_tags($_POST['xid'])):'';
	$mode=isset($_POST['mode'])?trim(strip_tags($_POST['mode'])):'0';
	
	$MONTHS=Array('','JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC');
	
	if($EmpID!='00000'){
		$MySQLi=new MySQLClass();
		$records=Array();
		$InputState="";
		if($mode==-1){
			$result=$MySQLi->sqlQuery("SELECT * FROM `tblempcse` WHERE `EmpID`='".$EmpID."' AND `CSEID`='".$CSEID."' LIMIT 1;");
			$records=mysqli_fetch_array($result, MYSQLI_BOTH);
			$InputState="disabled";
		}
		else if($mode==0){
			$records['CSEDesc']=$records['CSEExamPlace']=$records['CSERating']=$records['CSELicNum']=$records['CSEHighest']="";
			$records['CSEExamMonth']=$records['CSELicReleaseMonth']=date('m');
			$records['CSEExamDay']=$records['CSELicReleaseDay']=date('j');
			$records['CSEExamYear']=$records['CSELicReleaseYear']=date('Y');
		}
		else if($mode==1){
			$result=$MySQLi->sqlQuery("SELECT * FROM `tblempcse` WHERE `EmpID`='".$EmpID."' AND `CSEID`='".$CSEID."' LIMIT 1;");
			$records=mysqli_fetch_array($result, MYSQLI_BOTH);
		}
		
		echo "1|$EmpID|";
?>
<center>
	<form name="f_dpnt_info" onSubmit="processForm('csel',this);return false;"><br/>
		<table class="form_window">
			<tr>
				<td class="form_label" style="width:200px;"><label>ELIGIBILITY/LICENCE TITLE: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['CSEDesc']; ?>" type="text" name="CSEDesc" id="CSEDesc" class="text_input sml_frm_fld" <?php echo $InputState; ?> <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label"><label>RATING: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['CSERating']; ?>" type="text" name="CSERating" id="CSERating" class="text_input" <?php echo $InputState; ?> style="width:30px;" /> %</td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>DATE OF EXAMINATION: </label></td>
				<td class="pds_form_input">
				<select id="CSEExamMonth" name="CSEExamMonth" class="text_input" <?php echo $InputState; ?>>
					<?php for($m=1;$m<=12;$m++){if($m==intval($records['CSEExamMonth'])){echo "<option value='$m' selected>".$MONTHS[$m]."</option>";}else{echo "<option value='$m'>".$MONTHS[$m]."</option>";}} ?>
				</select>
				<select id="CSEExamDay" name="CSEExamDay" class="text_input" <?php echo $InputState; ?>>
					<?php for($d=1;$d<=31;$d++){if($d==intval($records['CSEExamDay'])){echo "<option value='$d' selected>$d</option>";}else{echo "<option value='$d'>$d</option>";}} ?>
				</select>
				<input type="text" name="CSEExamYear" id="CSEExamYear" class="text_input" <?php echo $InputState; ?> style="width:30px;" value="<?php echo $records['CSEExamYear']; ?>" /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>PLACE OF EXAMINATION: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['CSEExamPlace']; ?>" type="text" name="CSEExamPlace" id="CSEExamPlace" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>LICENCE NUMBER: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['CSELicNum']; ?>" type="text" name="CSELicNum" id="CSELicNum" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>DATE OF RELEASED: </label></td>
				<td class="pds_form_input">
				<select id="CSELicReleaseMonth" name="CSELicReleaseMonth" class="text_input" <?php echo $InputState; ?>>
					<?php for($m=1;$m<=12;$m++){if($m==intval($records['CSELicReleaseMonth'])){echo "<option value='$m' selected>".$MONTHS[$m]."</option>";}else{echo "<option value='$m'>".$MONTHS[$m]."</option>";}} ?>
				</select>
				<select id="CSELicReleaseDay" name="CSELicReleaseDay" class="text_input" <?php echo $InputState; ?>>
					<?php for($d=1;$d<=31;$d++){if($d==intval($records['CSELicReleaseDay'])){echo "<option value='$d' selected>$d</option>";}else{echo "<option value='$d'>$d</option>";}} ?>
				</select>
				<input type="text" name="CSELicReleaseYear" id="CSELicReleaseYear" class="text_input" <?php echo $InputState; ?> style="width:30px;" value="<?php echo $records['CSELicReleaseYear']; ?>" /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>HIGHEST ELIGIBILITY: </label></td>
				<td class="pds_form_input"><input type="checkbox" name="CSEHighest" id="CSEHighest" <?php echo $InputState; if($records['CSEHighest']==1){echo " checked";} ?>/></td>
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
		<input type="hidden" name="CSEID" id="CSEID" value="<?php echo $CSEID; ?>" />
		<input type="hidden" name="EmpID" id="EmpID" value="<?php echo $EmpID; ?>" />
	</form>
</center>
<?php } ?>
