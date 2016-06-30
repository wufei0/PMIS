<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD019'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	
	
	

	$TOID=isset($_POST['to'])?trim(strip_tags($_POST['to'])):'0';
	echo "1|$TOID|";
	$MONTHS=Array('','JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC');
	
	$MySQLi=new MySQLClass();
	$records=Array();
	
	$result=$MySQLi->sqlQuery("SELECT `TOIncDayTimeFrom`, DATE_FORMAT(`TOIncDateFrom`,'%d') AS TODateFrDay, DATE_FORMAT(`TOIncDateFrom`,'%m') AS TODateFrMonth, DATE_FORMAT(`TOIncDateFrom`,'%Y') AS TODateFrYear, `TOIncDayTimeTo`, DATE_FORMAT(`TOIncDateTo`,'%d') AS TODateToDay, DATE_FORMAT(`TOIncDateTo`,'%m') AS TODateToMonth, DATE_FORMAT(`TOIncDateTo`,'%Y') AS TODateToYear, `TOSubject`, `TODestination`, `TOBody` FROM `tbltravelorders` WHERE `TOID`='".$TOID."' LIMIT 1;");
	$records=mysqli_fetch_array($result, MYSQLI_BOTH);
	$InputState="disabled";
?>

<center>
	<form name="f_dpnt_info" onSubmit="return false;">
		<table class="ui-state-default ui-widget-content ui-corner-all form_table" style="width:480px;padding:0px;border-spacing:3px;margin-top:10px;">
			<tr>
				<td>
					<fieldset style="border:1px solid skyblue">
						<legend style="font-weight:bold;font-size:12px;">INCLUSIVE DATE OF TRAVEL</legend>
						<table style="margin-left:15px;">
							<tr>
								<td class="form_label" style="width:30px;text-align:left;"><label>From:</label></td>
								<td class="pds_form_input" colspan="2">
								<select id="TODateFrMonth" name="TODateFrMonth" class="text_input" disabled>
									<?php for($m=1;$m<=12;$m++){if($m==intval($records['TODateFrMonth'])){echo "<option value='$m' selected>".$MONTHS[$m]."</option>";}else{echo "<option value='$m'>".$MONTHS[$m]."</option>";}} ?>
								</select>
								<select id="TODateFrDay" name="TODateFrDay" class="text_input" disabled>
									<?php for($d=1;$d<=31;$d++){if($d==intval($records['TODateFrDay'])){echo "<option value='$d' selected>$d</option>";}else{echo "<option value='$d'>$d</option>";}} ?>
								</select>
								<input type="text" name="TODateFrYear" id="TODateFrYear" class="text_input" disabled style="width:30px;" value="<?php echo $records['TODateFrYear']; ?>" />
								<select id="TOIncDayTimeFrom" name="TOIncDayTimeFrom" class="text_input" disabled>
									<?php if($records['TOIncDayTimeFrom']=='PM'){echo "<option value='AM'>AM</option><option value='PM' selected>PM</option>";}else{echo "<option value='AM' selected>AM</option><option value='PM'>PM</option>";} ?>
								</select></td>
								<td class="form_label" style="width:30px;"><label>To:</label></td>
								<td class="pds_form_input" colspan="2">
								<select id="TODateToMonth" name="TODateToMonth" class="text_input" disabled>
									<?php for($m=1;$m<=12;$m++){if($m==intval($records['TODateToMonth'])){echo "<option value='$m' selected>".$MONTHS[$m]."</option>";}else{echo "<option value='$m'>".$MONTHS[$m]."</option>";}} ?>
								</select>
								<select id="TODateToDay" name="TODateToDay" class="text_input" disabled>
									<?php for($d=1;$d<=31;$d++){if($d==intval($records['TODateToDay'])){echo "<option value='$d' selected>$d</option>";}else{echo "<option value='$d'>$d</option>";}} ?>
								</select>
								<input type="text" name="TODateToYear" id="TODateToYear" class="text_input" disabled style="width:30px;" value="<?php echo $records['TODateToYear']; ?>" />
								<select id="TOIncDayTimeTo" name="TOIncDayTimeTo" class="text_input" disabled>
									<?php if($records['TOIncDayTimeTo']=='AM'){echo "<option value='AM' selected>AM</option><option value='PM'>PM</option>";}else{echo "<option value='AM'>AM</option><option value='PM' selected>PM</option>";} ?>
								</select></td>
							</tr>
						</table>
					</fieldset>
				</td>
			</tr>
			
			<tr>
				<td>
					<fieldset style="border:1px solid skyblue">
						<legend style="font-weight:bold;font-size:12px;">TRAVEL ORDER TO</legend>
							<div class="ui-state-highlight ui-corner-all" style="width:190px;height:50px;float:right;padding:3px 3px 3px 5px;"><span class='ui-icon ui-icon-lightbulb'></span><i> * Names in <font style="color:#EE6666;font-weight:bold;">RED</font> are NOT yet noted by respected AOs/OICs/Department Heads.</i></div>
							<select size="5" name="ListedIDs" id="ListedIDs" class="text_input" style="width:250px;" disabled>
								<?php 
								$result=$MySQLi->sqlQuery("SELECT `tblemppersonalinfo`.`EmpID`, CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName, `TONotedBy` FROM `tblemppersonalinfo` JOIN `tblemptravelorders` ON `tblemppersonalinfo`.`EmpID`=`tblemptravelorders`.`EmpID` WHERE `tblemptravelorders`.`TOID` = '$TOID' ORDER BY EmpName ASC;");
								while($ids=mysqli_fetch_array($result, MYSQLI_BOTH)){
									$Color=($ids['TONotedBy']!="")?"":"style='color:#EE6666;'";
									echo "<option $Color value='".$ids['EmpID']."'>".$ids['EmpName']."</option>";
								}?>
							</select>
					</fieldset>
				</td>
			</tr>
			
			<tr>
				<td>
					<fieldset style="border:1px solid skyblue">
						<legend style="font-weight:bold;font-size:12px;">DESTINATION</legend>
							<textarea rows="2" cols="70" name="TODestination" id="TODestination" class="text_input sml_frm_fld" disabled ><?php echo $records['TODestination']; ?></textarea>
					</fieldset>
				</td>
			</tr>
			
			<tr>
				<td>
					<fieldset style="border:1px solid skyblue">
						<legend style="font-weight:bold;font-size:12px;">TRAVEL ORDER DETAILS</legend>
							<textarea rows="6" cols="70" name="TOBody" id="TOBody" class="text_input sml_frm_fld" disabled onClick="showMessage();"><?php echo $records['TOBody']; ?></textarea>
					</fieldset>
				</td>
			</tr>
			
		</table>
		
		<?php
			$showTOAppToProcess=true;
			$docStatusFilter="";
			if($Authorization[5]){$docStatusFilter=" AND (`TOStatus` = '1' OR `TOStatus` = '2') ";}
			else if($Authorization[6]){$docStatusFilter=" AND (`TOStatus` = '2' OR `TOStatus` = '3') ";}
			else if($Authorization[7]){$docStatusFilter=" AND (`TOStatus` = '3' OR `TOStatus` = '4') ";}
			else {$showTOAppToProcess=false;}
			
			if($showTOAppToProcess){
				$result=$MySQLi->sqlQuery("SELECT `TOID`, DATE_FORMAT(`TOIncDateFrom`, '%Y-%m-%d') AS TODateFrom, `TOIncDayTimeFrom`, DATE_FORMAT(`TOIncDateTo`, '%Y-%m-%d') AS TODateTo, `TOIncDayTimeTo`, `TODestination`, `TOStatus` FROM `tbltravelorders` WHERE `TOID` = '$TOID' ".$docStatusFilter.";");
				while($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
					switch ($records['TOStatus']){case '0':$TOstatus="NEW";break;case '1':$TOstatus="POSTED";break;case '2':$TOstatus="NOTED";break;case '3':$TOstatus="CHECKED";break;case '4':$TOstatus="APPROVED";break;default: $TOstatus="DISAPPROVED";break;}
					$TODesc="<b>TRAVEL ORDER</b> to <b>".$records['TODestination']."</b> From <b>".$records['TODateFrom']." ".$records['TOIncDayTimeFrom']."</b> to <b>".$records['TODateTo']." ".$records['TOIncDayTimeTo']."</b>. ";
					
					switch ($records['TOStatus']){
						case 1:
							if($Authorization[5]){
								$denyIconState="default";$denyIconAction="$('#d_input').dialog({buttons:{'YES':function(){processDocument('".$EmpID."','to','".$records['TOID']."','-1',document.getElementById('respTxt').value);closeDialogWindow('d_input');},'NO':function(){closeDialogWindow('d_input');}}});getInformation('Deny/Disapprovr this TO application?<br/>Please TO comment/remark.');";
								$chekIconState="default";$chekIconAction="$('#d_confirm').dialog({buttons:{'YES':function(){processDocument('".$EmpID."','to','".$records['TOID']."',".($records['TOStatus']+1).",'');},'NO':function(){closeDialogWindow('d_confirm');}}});showConfirmation('Note this application?');";
								$undoIconState="disabled";$undoIconAction="return false;";
							}
							break;
						case 2:
							if($Authorization[5]){
								$denyIconState="disabled";$denyIconAction="";
								$chekIconState="disabled";$chekIconAction="";
								$undoIconState="default";$undoIconAction="$('#d_confirm').dialog({buttons:{'YES':function(){processDocument('".$EmpID."','to','".$records['TOID']."',".($records['TOStatus']-1).",'')},'NO':function(){closeDialogWindow('d_confirm');}}});showConfirmation('Undo the recording of this application?');";
							}
							if($Authorization[6]){
								$denyIconState="default";$denyIconAction="$('#d_input').dialog({buttons:{'YES':function(){processDocument('".$EmpID."','to','".$records['TOID']."','-1',document.getElementById('respTxt').value);closeDialogWindow('d_input');},'NO':function(){closeDialogWindow('d_input');}}});getInformation('Deny/Disapprovr this TO application?<br/>Please TO comment/remark.');";
								$chekIconState="default";$chekIconAction="$('#d_confirm').dialog({buttons:{'YES':function(){processDocument('".$EmpID."','to','".$records['TOID']."',".($records['TOStatus']+1).",'')},'NO':function(){closeDialogWindow('d_confirm');}}});showConfirmation('Confirm this application?');";
								$undoIconState="disabled";$undoIconAction="return false;";
							}
							break;
						case 3:
							if($Authorization[6]){
								$denyIconState="disabled";$denyIconAction="";
								$chekIconState="disabled";$chekIconAction="";
								$undoIconState="default";$undoIconAction="$('#d_confirm').dialog({buttons:{'YES':function(){processDocument('".$EmpID."','to','".$records['TOID']."',".($records['TOStatus']-1).",'')},'NO':function(){closeDialogWindow('d_confirm');}}});showConfirmation('Undo the confirmation of this application?');";
							}
							
							if($Authorization[7]){
								$denyIconState="default";$denyIconAction="$('#d_input').dialog({buttons:{'YES':function(){processDocument('".$EmpID."','to','".$records['TOID']."','-1',document.getElementById('respTxt').value)},'NO':function(){closeDialogWindow('d_input');}}});getInformation('Deny/Disapprove this TO application?<br/>Please add reason/comment/remark.');";
								$chekIconState="default";$chekIconAction="$('#d_confirm').dialog({buttons:{'YES':function(){processDocument('".$EmpID."','to','".$records['TOID']."',".($records['TOStatus']+1).",'')},'NO':function(){closeDialogWindow('d_confirm');}}});showConfirmation('Approve this application?');";
								$undoIconState="disabled";$undoIconAction="return false;";
							}
							break;
						case 4:
							if($Authorization[7]){
								$denyIconState="disabled";$denyIconAction="";
								$chekIconState="disabled";$chekIconAction="";
								$undoIconState="default";$undoIconAction="$('#d_confirm').dialog({buttons:{'YES':function(){processDocument('".$EmpID."','to','".$records['TOID']."',".($records['TOStatus']-1).",'')},'NO':function(){closeDialogWindow('d_confirm');}}});showConfirmation('Undo the approval of this application?');";
							}
							break;
					}
		?>
		
		<table class="ui-state-highlight ui-corner-all form_table" style="width:486px;padding:0px;border-spacing:0px;margin-left:10px;margin-right:10px;margin-top:3px;">
			<tr>
				<td width="20px" align="center" class=""><span class='ui-icon ui-icon-info'></span></td>
				<td><?php echo $TODesc; ?></td>
				<td width="65px" align="right">
					<ul class="ui-widget ui-helper-clearfix ul-icons">
						<li id="" class="ui-state-<?php echo $denyIconState; ?> ui-corner-all" title="Deny" onClick="<?php echo $denyIconAction; ?>"><span class="ui-icon ui-icon-close"></span></li>
						<li id="" class="ui-state-<?php echo $chekIconState; ?> ui-corner-all" title="Confirm" onClick="<?php echo $chekIconAction; ?>"><span class="ui-icon ui-icon-check"></span></li>
						<li id="" class="ui-state-<?php echo $undoIconState; ?> ui-corner-all" title="Undo" onClick="<?php echo $undoIconAction; ?>"><span class="ui-icon ui-icon-arrowreturnthick-1-w"></span></li>
					</ul>
				</td>
			</tr>
		</table>
		<?php
				}
			}
			
		?>
		
		<hr class="form_bottom_line_window"/>
		<table style="width:486px;padding:0px;border-spacing:1px;margin:3px 10px 5px 10px;padding:1 10 1 10;">
			<tr>
				<td align="left"><input type="button" value="Help" class="button ui-button ui-widget ui-corner-all" onClick="showHelp('001');return false;" /></td>
				<td align="right"><input type="button" value="Cancel" class="button ui-button ui-widget ui-corner-all" onClick="closeDialogWindow('d_viewer_2');return false;" /></td>
			</tr>
		</table>
	</form>
</center>
