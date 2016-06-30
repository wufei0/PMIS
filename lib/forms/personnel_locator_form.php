<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD021'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$EmpID=isset($_POST['id'])?trim(strip_tags($_POST['id'])):'00000';
	$PLID=isset($_POST['xid']) ? trim(strip_tags($_POST['xid'])) : '';
	$mode=isset($_POST['mode']) ? trim(strip_tags($_POST['mode'])) : '0';
	echo "1|$EmpID|";

	if($EmpID!='00000'){
		$Config=new Conf();
		$MySQLi=new MySQLClass($Config);
		$records=Array();
		$InputState="";
		
		if($mode==0){
			$records['PLPurpose']=$records['PLDestination']=$records['PLRemarks']="";
			$records['PLDateYear']=date('Y');$records['PLDateMonth']=date('m');$records['PLDateoDay']=date('d');
		}
		else {
			$result=$MySQLi->sqlQuery("SELECT * FROM `tblpersonnellocators` WHERE `PLID`='".$PLID."' LIMIT 1;");
			$records=mysql_fetch_array($result);
			if($mode==-1){$InputState="disabled";}
		}
?>

<center>
	<form name="f_dpnt_info" onSubmit="processPLSInfo(this);return false;"><br/>
		<table class="form_window">	<?php //Spouse"s Name... ?>
			<tr valign="top">
				<td class="form_label"><label>PERSONNEL LOCATOR FOR: </label></td>
				<td class="pds_form_input">
					<select size="3" name="ListedIDs" id="ListedIDs" class="text_input" style="width:230px;" <?php echo $InputState; ?> onClick="IDtoRemoveFrTO=this.value;">
						<?php 
						if($mode==0){
							$PLFor=($EmpID!="")?"PL,$EmpID":"PL";
							$result=$MySQLi->sqlQuery("SELECT `tblemppersonalinfo`.`EmpID`, CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName FROM `tblemppersonalinfo` WHERE `EmpID` = '$EmpID';");
							$ids=mysql_fetch_array($result);
							echo "<option value='".$ids['EmpID']."'>".$ids['EmpName']."</option>";
						}
						else{
							$PLFor="PL";
							$result=$MySQLi->sqlQuery("SELECT `tblemppersonalinfo`.`EmpID`, CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName FROM `tblemppersonalinfo` JOIN `tblemplocator` ON `tblemppersonalinfo`.`EmpID`=`tblemplocator`.`EmpID` WHERE `tblemplocator`.`PLID` = '$PLID';");
							while($ids=mysql_fetch_array($result)){
								echo "<option value='".$ids['EmpID']."'>".$ids['EmpName']."</option>";
								$PLFor.=",".$ids['EmpID'];
							}
						}?>
					</select>
					<input type="hidden" name="ListOfID" id="ListOfID" value="<?php echo $PLFor; ?>" />
				</td>
				<td><ul class="ui-widget ui-helper-clearfix ul-icons"><li id="sEmployee" class="ui-state-default ui-corner-all" title="Add Employee" onClick="selectWindow(this)"><span class="ui-icon ui-icon-plus <?php if($mode==-1){echo "ui-state-diasabled";} ?>"></span></li><li class="ui-state-default ui-corner-all" title="Remove Employee" onClick="RemoveIDfrList(document.getElementById('ListOfID').value,document.getElementById('ListedIDs'));"><span class="ui-icon ui-icon-minus <?php if($mode==-1){echo "ui-state-diasabled";} ?>"></span></li></ul></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>DESTINATION: </label></td>
				<td class="pds_form_input" colspan="2"><input value="<?php echo $records['PLDestination']; ?>" type="text" name="PLDestination" id="PLDestination" class="text_input sml_frm_fld" <?php echo $InputState; ?> /></td>
			</tr>
			<tr valign="top">
				<td class="form_label"><label>PURPOSE: </label></td>
				<td class="pds_form_input" colspan="2"><textarea rows="2" cols="43" name="PLPurpose" id="PLPurpose" class="text_input sml_frm_fld" <?php echo $InputState; ?> ><?php echo $records['PLPurpose']; ?></textarea></td>
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
		<input type="hidden" name="PLID" id="PLID" value="<?php echo $PLID; ?>" />
		<input type="hidden" name="EmpID" id="EmpID" value="<?php echo $EmpID; ?>" />
	</form>
</center>
<?php } ?>