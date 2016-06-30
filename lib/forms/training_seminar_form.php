<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD011'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$EmpID=isset($_POST['id'])?trim(strip_tags($_POST['id'])):'00000';
	$TrainID=isset($_POST['xid'])?trim(strip_tags($_POST['xid'])):'';
	$mode=isset($_POST['mode'])?trim(strip_tags($_POST['mode'])):'0';
	echo "1|$EmpID|";
	
	$MONTHS=Array('','JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC');
	
	if($EmpID!='00000'){
		$MySQLi=new MySQLClass();
		$records=Array();
		$InputState="";
		if($mode==-1){
			$result=$MySQLi->sqlQuery("SELECT * FROM `tblemptrainings` WHERE `EmpID`='".$EmpID."' AND `TrainID`='".$TrainID."' LIMIT 1;");
			$records=mysqli_fetch_array($result, MYSQLI_BOTH);
			$InputState="disabled";
		}
		else if($mode==0){
			$records['TrainDesc']=$records['TrainHours']=$records['TrainSponsor']="";
			$records['TrainFromMonth']=$records['TrainToMonth']=date('m');
			$records['TrainFromDay']=$records['TrainToDay']=date('j');
			$records['TrainFromYear']=$records['TrainToYear']=date('Y');
		}
		else if($mode==1){
			$result=$MySQLi->sqlQuery("SELECT * FROM `tblemptrainings` WHERE `EmpID`='".$EmpID."' AND `TrainID`='".$TrainID."' LIMIT 1;");
			$records=mysqli_fetch_array($result, MYSQLI_BOTH);
		}
?>
<center>
	<form name="f_dpnt_info" onSubmit="processForm('trai',this);return false;"><br/>
		<table class="form_window">	<?php //Spouse"s Name... ?>
			<tr>
				<td class="form_label" style="width:200px;"><label>TRAINING/SEMINAR TITLE: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['TrainDesc']; ?>" type="text" id="TrainDesc" name="TrainDesc" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>INCLUSIVE DATE FROM: </label></td>
				<td class="pds_form_input">
				<select id="TrainFromMonth" name="TrainFromMonth" class="text_input" <?php echo $InputState; ?>>
					<?php for($m=1;$m<=12;$m++){if($m==intval($records['TrainFromMonth'])){echo "<option value='$m' selected>".$MONTHS[$m]."</option>";}else{echo "<option value='$m'>".$MONTHS[$m]."</option>";}} ?>
				</select>
				<select id="TrainFromDay" name="TrainFromDay" class="text_input" <?php echo $InputState; ?>>
					<?php for($d=1;$d<=31;$d++){if($d==intval($records['TrainFromDay'])){echo "<option value='$d' selected>$d</option>";}else{echo "<option value='$d'>$d</option>";}} ?>
				</select>
				<input type="text" name="TrainFromYear" id="TrainFromYear" class="text_input" <?php echo $InputState; ?> style="width:30px;" value="<?php echo $records['TrainFromYear']; ?>" /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>INCLUSIVE DATE TO: </label></td>
				<td class="pds_form_input">
				<select id="TrainToMonth" name="TrainToMonth" class="text_input" <?php echo $InputState; ?>>
					<?php for($m=1;$m<=12;$m++){if($m==intval($records['TrainToMonth'])){echo "<option value='$m' selected>".$MONTHS[$m]."</option>";}else{echo "<option value='$m'>".$MONTHS[$m]."</option>";}} ?>
				</select>
				<select id="TrainToDay" name="TrainToDay" class="text_input" <?php echo $InputState; ?>>
					<?php for($d=1;$d<=31;$d++){if($d==intval($records['TrainToDay'])){echo "<option value='$d' selected>$d</option>";}else{echo "<option value='$d'>$d</option>";}} ?>
				</select>
				<input type="text" name="TrainToYear" id="TrainToYear" class="text_input" <?php echo $InputState; ?> style="width:30px;" value="<?php echo $records['TrainToYear']; ?>" /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>NUMBER OF HOURS: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['TrainHours']; ?>" type="text" name="TrainHours" id="TrainHours" class="text_input" <?php echo $InputState; ?> style="width:30px;" /></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>CONDUCTED/SPONSORED BY: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['TrainSponsor']; ?>" type="text" name="TrainSponsor" id="TrainSponsor" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
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
		<input type="hidden" name="TrainID" id="TrainID" value="<?php echo $TrainID; ?>" />
		<input type="hidden" name="EmpID" id="EmpID" value="<?php echo $EmpID; ?>" />
	</form>
</center>
<?php } ?>
