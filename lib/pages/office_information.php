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

	
	
	$SubOffID = isset($_POST['id']) ? trim(strip_tags($_POST['id'])) : '00000';
	echo "1|$SubOffID|";
	
	$records = Array();
	if($SubOffID!='00000') {
		$Config = new Conf();
		$MySQLi = new MySQLClass($Config);
		$result = $MySQLi -> sqlQuery("SELECT * FROM `tblsuboffices` WHERE `SubOffID` = '".$SubOffID."';");
		$records = mysql_fetch_array($result); 
	}
	else { $records['SubOffCode']=$records['SubOffName']=$records['SubOffAddSt']=$records['SubOffAddBrgy']=$records['SubOffAddMun']=$records['SubOffAddProv']=$records['SubOffZipCode']=$records['SubOffTel']="";}
?>

<center>
<br/>
		<form name="Office_Info" id="Office_Info">
				
				<?php //Office ID and Office Code... ?>
				<table class="form">
					<tr>
						<td class="form_label" style="width:100px;"><label>OFFICE CODE: </label></td>
						<td class="pds_form_input"><input value="<?php echo $records['SubOffCode']; ?>" type="text" name="SubOffCode" id="SubOffCode" class="text_input" readonly /></td>
					</tr>
				</table>
				
				<?php //Office NAME... ?>
				<table class="form">
					<tr>
						<td class="form_label" style="width:100px;"><label>OFFICE FULL NAME: </label></td>
						<td class="pds_form_input"><input value="<?php echo $records['SubOffName']; ?>" type="text" name="SubOffName" id="SubOffName" class="text_input" readonly /></td>
					</tr>
				</table>
		
		<?php //Office SubOffidential Address... ?>
				<br />
				<table class="form">
					<tr>
						<td class="form_label" style="width:100px;text-align:left;"><label>OFFICE LOCATION/ADDRESS: </label></td>
					</tr>
				</table>
				
				<table class="form">
					<tr>
						<td class="form_label" style="width:100px;" rowspan="2"><label>&nbsp;</label></td>
						<td class="pds_form_input"><input value="<?php echo $records['SubOffAddSt']; ?>" type="text" name="SubOffAddSt" id="SubOffAddSt" class="text_input" readonly /></td>
						<td class="pds_form_input"><input value="<?php echo $records['SubOffAddBrgy']; ?>" type="text" name="SubOffAddBrgy" id="SubOffAddBrgy" class="text_input" readonly /></td>
					</tr>
					<tr>
						<td class="form_label_small"><label>Street</label></td>
						<td class="form_label_small"><label>Barangay</label></td>
					</tr>
				</table>
				
				<table class="form">
					<tr>
						<td class="form_label" style="width:100px;" rowspan="2"><label>&nbsp;</label></td>
						<td class="pds_form_input"><input value="<?php echo $records['SubOffAddMun']; ?>" type="text" name="SubOffAddMun" id="SubOffAddMun" class="text_input" readonly /></td>
						<td class="pds_form_input"><input value="<?php echo $records['SubOffAddProv']; ?>" type="text" name="SubOffAddProv" id="SubOffAddProv" class="text_input" readonly /></td>
						<td class="pds_form_input"><input value="<?php echo $records['SubOffZipCode']; ?>" type="text" name="SubOffZipCode" id="SubOffZipCode" class="text_input" readonly /></td>
					</tr>
					<tr>
						<td class="form_label_small"><label>Municipality</label></td>
						<td class="form_label_small"><label>Province</label></td>
						<td class="form_label_small" style="width:50px;"><label>ZIP Code</label></td>
					</tr>
				</table>	
				
				<table class="form">	<?php //Office SubOffidential Telephone Number... ?>
					<tr>
						<td class="form_label" style="width:100px;"><label>&nbsp;</label></td>
						<td class="form_label" style="width:100px;"><label>TELEPHONE NO.: </label></td>
						<td class="pds_form_input"><input value="<?php echo $records['SubOffTel']; ?>" type="text" name="SubOffTel" id="SubOffTel" class="text_input" readonly /></td>
					</tr>
				</table>
			</form>
</center>