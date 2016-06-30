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
	
	
	$MySQLi=new MySQLClass();
	$EmpStatus=Array();
	$InputState="";
	if($mode==-1){
		$EmpStatus=$MySQLi->GetArray("SELECT `EmpStatus` FROM `tblemppersonalinfo` WHERE `EmpID`='".$EmpID."' LIMIT 1;");
		$InputState="disabled";
	}
	else if($mode==0){$EmpStatus['EmpStatus']="";}
	else if($mode==1){$EmpStatus=$MySQLi->GetArray("SELECT `EmpStatus` FROM `tblemppersonalinfo` WHERE `EmpID`='".$EmpID."' LIMIT 1;");}
	$EmpStatus['EmpStatus']=($EmpStatus['EmpStatus']=="")?"INACTIVE":$EmpStatus['EmpStatus'];
	//echo "0|".$_SESSION['user']."|$EmpID - $mode: [".$EmpStatus['EmpStatus']."]";exit();
	echo "1|$EmpID|";
?>

<center><br/>
	<form name="f_Emp_status" onSubmit="processForm('chst',this);return false;"><br/>
		<table class="form_window">	<?php //Spouse"s Name... ?>
			<tr>
				<td class="form_label" style="width:200px;"><label>PERSONNEL STATUS: </label></td>
				<td class="pds_form_input">
				<select id="EmpStatus" name="EmpStatus" class="text_input" <?php echo $InputState; ?> style="width:100px;">
					<option value="ACTIVE" <?php echo (($EmpStatus['EmpStatus']=="ACTIVE")?"selected":""); ?> >ACTIVE</option>
					<option value="INACTIVE" <?php echo (($EmpStatus['EmpStatus']=="INACTIVE")?"selected":""); ?> >INACTIVE</option>
					<option value="ON LEAVE" <?php echo (($EmpStatus['EmpStatus']=="ON LEAVE")?"selected":""); ?> >ON LEAVE</option>
					<option value="DEAD DILE" <?php echo (($EmpStatus['EmpStatus']=="DEAD FILE")?"selected":""); ?> >DEAD DILE</option>
				</select>
				</td>
			</tr>
		</table>
		<br/><br/>
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