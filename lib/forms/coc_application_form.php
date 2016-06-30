<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD020'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$EmpID=isset($_POST['id'])?trim(strip_tags($_POST['id'])):'00000';
	$COCID=isset($_POST['xid'])?trim(strip_tags($_POST['xid'])):'';
	$mode=isset($_POST['mode'])?trim(strip_tags($_POST['mode'])):'0';
	
	$MONTHS=Array('','JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC');
	echo "1|$EmpID|";
	if($EmpID!='00000'){
		$MySQLi=new MySQLClass();
		$records=Array();
		$InputState="";
		if($mode==0){
			$records['COCEarnedHours']="0";
			$records['COCID']=$records['COCNotes']="";
			$records['COCEarnedDateYear']=date('Y');$records['COCEarnedDateMonth']=date('m');$records['COCEarnedDateDay']=date('d');
		}
		else{
			$records=$MySQLi->GetArray("SELECT `COCID`, `COCEarnedHours`, `COCNotes`, DATE_FORMAT(`COCEarnedDate`, '%Y') AS COCEarnedDateYear, DATE_FORMAT(`COCEarnedDate`, '%m') AS COCEarnedDateMonth, DATE_FORMAT(`COCEarnedDate`, '%d') AS COCEarnedDateDay FROM `tblempcocs` WHERE `COCID`='".$COCID."' LIMIT 1;");
			if($mode==-1){$InputState="disabled";}
		}
?>

<center>
	<form name="f_cto_info" onSubmit="processForm('pcoc',this);return false;"><br/>
		<table class="form_window">	
			<tr>
				<td class="form_label" style="width:200px;"><label>DATE:</label></td>
				<td class="pds_form_input">
				<select id="COCEarnedDateMonth" name="COCEarnedDateMonth" class="text_input" <?php echo $InputState; ?>>
					<?php for($m=1;$m<=12;$m++){if($m==intval($records['COCEarnedDateMonth'])){echo "<option value='$m' selected>".$MONTHS[$m]."</option>";}else{echo "<option value='$m'>".$MONTHS[$m]."</option>";}} ?>
				</select>
				<select id="COCEarnedDateDay" name="COCEarnedDateDay" class="text_input" <?php echo $InputState; ?>>
					<?php for($d=1;$d<=31;$d++){if($d==intval($records['COCEarnedDateDay'])){echo "<option value='$d' selected>$d</option>";}else{echo "<option value='$d'>$d</option>";}} ?>
				</select>
				<input type="text" name="COCEarnedDateYear" id="COCEarnedDateYear" class="text_input" <?php echo $InputState; ?> style="width:30px;" value="<?php echo $records['COCEarnedDateYear']; ?>" />
				</td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>HOURS EARNED:</label></td>
				<td class="pds_form_input"><input value="<?php echo number_format($records['COCEarnedHours'],3); ?>" type="text" name="COCEarnedHours" id="COCEarnedHours" class="text_input sml_frm_fld" <?php echo $InputState; ?> style="width:70px;text-align:right;" /></td>
			</tr>
			<tr valign="top">
				<td class="form_label"><label>NOTES: </label></td>
				<td class="pds_form_input"><textarea rows="2" cols="36" name="COCNotes" id="COCNotes" class="text_input sml_frm_fld" <?php echo $InputState; ?> ><?php echo $records['COCNotes']; ?></textarea></td>
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
		<input type="hidden" name="COCID" id="COCID" value="<?php echo $COCID; ?>" />
		<input type="hidden" name="EmpID" id="EmpID" value="<?php echo $EmpID; ?>" />
	</form>
</center>
<?php } ?>