<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD018'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	
	
	
	/* Access by specific User Group only */
	if(!(($_SESSION['usergroup']=='USRGRP001')||($_SESSION['usergroup']=='USRGRP002')||($_SESSION['usergroup']=='USRGRP004')||($_SESSION['usergroup']=='USRGRP005')||($_SESSION['usergroup']=='USRGRP006'))){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$EmpID=isset($_POST['id'])?trim(strip_tags($_POST['id'])):'00000';
	$Lt=isset($_POST['Lt'])?trim(strip_tags($_POST['Lt'])):date('X');
	$Yr=isset($_POST['Yr'])?trim(strip_tags($_POST['Yr'])):date('Y');
	$Mo=isset($_POST['Mo'])?trim(strip_tags($_POST['Mo'])):date('m');
	$St=isset($_POST['St'])?trim(strip_tags($_POST['St'])):'X';
	
	$MONTHS=Array('','JANUARY','FEBRUARY','MARCH','APRIL','MAY','JUNE','JULY','AUGUST','SEPTEMBER','OCTOBER','NOVEMBER','DECEMBER');
	echo "1|".$_SESSION['user']."|";
	$MySQLi=new MySQLClass();
	
	$StStr="";
	//if($Authorization[5]){$St="2";}
?>

<center><br/>
	<form name="filter_lv" id="filter" onSubmit="FilterQuery(this);return false;">
	<table class="filter_bar" cellspacing="0" cellpadding="0" style="width:1100px;margin-left:20px;margin-right:20px;">
		<tr>
			<td class="form_label_l filter_bar" style="width:55px;"><label><b>FILTER:</b></label></td>
			<td class="form_label filter_bar"><label>Type:</label></td>
				<td class="pds_form_input filter_bar" style="width:110px;">
					<select id="flt_ltype" name="flt_ltype" class="text_input" style="width:110px;" >
					<option value="X" <?php if($Lt=="X"){echo "selected";}?>>ALL</option>
					<?php
						$Ty="";
						$result=$MySQLi->sqlQuery("SELECT `LeaveTypeID`, `LeaveTypeDesc` FROM `tblleavetypes` WHERE `LeaveTypeID` <> 'LT00' ORDER BY `LeaveTypeID`;");
						while($ltype=mysqli_fetch_array($result, MYSQLI_BOTH)) {
							if($ltype['LeaveTypeID']==$Lt){echo "<option value='".$ltype['LeaveTypeID']."' selected>".$ltype['LeaveTypeDesc']."</option>"; $StStr.=" AND `LeaveTypeID` = '".$Lt."' ";}
							else{echo "<option value='".$ltype['LeaveTypeID']."'>".$ltype['LeaveTypeDesc']."</option>";}
						} unset($result);
					?>
					</select>
				</td>
			<td class="form_label filter_bar"><label>Year:</label></td>
				<td class="pds_form_input filter_bar" style="width:53px;">
					<select id="flt_year" name="flt_year" class="text_input" style="width:53px;" >
					<option value="X" <?php if($St=="X"){echo "selected";}?>>ALL</option>
					<?php for($y=2010;$y<=date('Y');$y++){if($y==$Yr){echo "<option value='$y' selected>".$y."</option>"; $StStr.=" AND `LivAppIncDateFrom` LIKE '".$Yr."%' ";}else{echo "<option value='$y'>".$y."</option>";}} ?>
					</select>
				</td>
			<td class="form_label filter_bar"><label>Month:</label></td>
				<td class="pds_form_input filter_bar" style="width:90px;">
					<select id="flt_month" name="flt_month" class="text_input" style="width:85px;" >
					<option value="X">ALL</option>
					<?php for($m=1;$m<=12;$m++){if($m==$Mo){echo "<option value='$m' selected>".$MONTHS[$m]."</option>";  $StStr.=" AND `LivAppIncDateFrom` LIKE '%-".($Mo>9?$Mo:"0".$Mo)."-%' ";}else{echo "<option value='$m'>".$MONTHS[$m]."</option>";}} ?>
					</select>
				</td>
			<td class="form_label filter_bar"><label>Status:</label></td>
				<td align="right" style="width:35px;">
					<input type="radio" id="st_show_all" name="flt_status" value="X" <?php if($St=="X"){echo "checked='checked'";}?> />
				</td><td class="form_label_l" style="width:65px;"><label>SHOW ALL</label></td>
				
				<td align="right" style="width:35px;">
					<input type="radio" id="st_posted" name="flt_status" value="1" <?php if($St=="1"){echo "checked='checked'";$StStr.=" AND `LivAppStatus` = '1'";}?> />
				</td><td class="form_label_l" style="width:40px;"><label>POSTED</label></td>
				
				<td align="right" style="width:35px;">
					<input type="radio" id="st_posted" name="flt_status" value="2" <?php if($St=="2"){echo "checked='checked'";$StStr.=" AND `LivAppStatus` = '2'";}?> />
				</td><td class="form_label_l" style="width:40px;"><label>NOTED</label></td>
				
				<td align="right" style="width:35px;">
					<input type="radio" id="st_checked" name="flt_status" value="3" <?php if($St=="3"){echo "checked='checked'";$StStr.=" AND `LivAppStatus` = '3'";}?> />
				</td><td class="form_label_l" style="width:50px;"><label>CHECKED</label></td>
				
				<td align="right" style="width:35px;">
					<input type="radio" id="st_approved" name="flt_status" value="4" <?php if($St=="4"){echo "checked='checked'";$StStr.=" AND `LivAppStatus` = '4'";}?> />
				</td><td class="form_label_l" style="width:55px;"><label>APPROVED</label></td>
			<td class="form_label filter_bar"><input type="submit" value="Search" class="button ui-button ui-widget ui-corner-all" style="padding: 0px 4px 0px 4px;width:55px;"/></td>
		</tr>
	</table>
	</form>
	<table class="i_table" style="width:1100px;" cellspacing="0">
		<tr>
			<td class="i_table_header_1st" rowspan="2" width="33px">#</td>
			<td class="i_table_header" style="padding:0px 3px 0px 3px;" rowspan="2" width="86px">Date of Application</td>
			<td class="i_table_header" style="padding:0px 3px 0px 3px;" rowspan="2" width="158px">Emplyoyee Name</td>
			<td class="i_table_header" style="padding:0px 3px 0px 3px;" rowspan="2" width="42px">Leave<br/>Type</td>
			<td class="i_table_header" style="padding:0px 3px 0px 3px;" rowspan="2" width="56px">Number of Days</td>
			<td class="i_table_header" colspan="2" width="208px">Inclusive Dates</td>
			<td class="i_table_header" style="padding:0px 3px 0px 3px;" rowspan="2" width="100px">Leave Notes</td>
			<td class="i_table_header" style="padding:0px 3px 0px 3px;" rowspan="2" width="92px">Status</td>
			<td class="i_table_header" style="padding:0px 3px 0px 3px;" rowspan="2" width="94px">Status Details</td>
			<td class="i_table_header" style="padding:0px 3px 0px 3px;" rowspan="2" width="98px">Remarks</td>
			<td class="i_table_header" style="padding:0px 3px 0px 3px;" colspan="2" rowspan="2" width="41px">&nbsp;</td>
		</tr>
		<tr>
			<td class="i_table_header" style="padding:0px 3px 0px 3px;" width="104px">From</td>
			<td class="i_table_header" style="padding:0px 3px 0px 3px;" width="104px">To</td>
		</tr>
	</table>
	<div id="pending_loading_1" class="loading_div brief_info_emp_1" style="left:27px;width:1095px;height:270px;">
		<table width='100%' height='100%'><tr valign='center'><td align='center'><img src="css/<?php echo $_SESSION['theme']; ?>/images/loader.gif"/><br/><span class="loading_text">Loading...</span></td></tr></table>
	</div>
	<div style="height:270px;width:1100px;border:1px dotted #6D84B4;margin-left:20px;margin-right:20px;padding:0px;overflow-x:hidden;overflow-y:scroll;">
		<table style="width:1100px;" cellspacing="0">
			<?php
				//echo"<tr><td colspan='10'>$St<br>$sql</td></tr>";
				$records=Array();
				$sql="SELECT `EmpID`, `LivAppNotes`, `LivAppID`, `LeaveTypeID`, `LivAppDays`, DATE_FORMAT(`LivAppFiledDate`, '%b %d, %Y') AS LivAppFiledDate, DATE_FORMAT(`LivAppIncDateFrom`, '%b %d, %Y') AS LivAppIncDateFrom,`LivAppIncDayTimeFrom`,DATE_FORMAT(`LivAppIncDateTo`, '%b %d, %Y') AS LivAppIncDateTo, `LivAppIncDayTimeTo`, (SELECT CONCAT(EmpFName, ' ', EmpLName) FROM tblemppersonalinfo WHERE EmpID = LivAppNotedBy) noted_by, LivAppNotedBy, `LivAppNotedTime`, (SELECT CONCAT(EmpFName, ' ', EmpLName) FROM tblemppersonalinfo WHERE EmpID = LivAppCheckedBy) check_by, LivAppCheckedBy, `LivAppCheckedTime`, (SELECT CONCAT(EmpFName, ' ', EmpLName) FROM tblemppersonalinfo WHERE EmpID = LivAppApprovedBy) approved_by, LivAppApprovedBy, `LivAppApprovedTime`, `LivAppStatus`, `LivAppCheckedRemarks`, `LivAppNotedRemarks`, `LivAppApprovedRemarks`  FROM `tblempleaveapplications` WHERE `LivAppStatus` <> '0' ".$StStr." ORDER BY `LivAppIncDateFrom` DESC;";
				$view_records=$MySQLi->sqlQuery($sql);
				$n=1;
				while($records=mysqli_fetch_array($view_records, MYSQLI_BOTH)){
					if($n%2==0){echo "<tr class='i_table_row_1'>";}
					else{echo "<tr class='i_table_row_0'>";}
					echo "<td align='right' width='25px' valign='top' style='padding:4px 5px 3px 0px;'>".$n.".</td>";
					echo "<td class='i_table_body' align='center' width='70px'>".$records['LivAppFiledDate']."</td>";
					$LeaveName=$MySQLi->GetArray("SELECT CONCAT_WS(', ',`EmpLName`, CONCAT_WS(' ',`EmpFName`, CONCAT_WS('.', SUBSTRING(`EmpMName`, 1, 1), ''))) AS EmpName FROM `tblemppersonalinfo` WHERE `EmpID`='".$records['EmpID']."';");
					echo "<td class='i_table_body' width='130px'>".$LeaveName['EmpName']."</td>";
					$LeaveType=$MySQLi->GetArray("SELECT `LeaveTypeCode`, `LeaveTypeDesc` FROM `tblleavetypes` WHERE `LeaveTypeID`='".$records['LeaveTypeID']."';");
					echo "<td class='i_table_body' align='center' width='35px' alt='".$LeaveType['LeaveTypeDesc']."'>".$LeaveType['LeaveTypeCode']."</td>";
					echo "<td class='i_table_body' align='center' width='45px'>".number_format($records['LivAppDays'],2)."</td>";
					echo "<td class='i_table_body' align='center' width='85px'>".$records['LivAppIncDateFrom']." ".$records['LivAppIncDayTimeFrom']."</td>";
					echo "<td class='i_table_body' align='center' width='85px'>".$records['LivAppIncDateTo']." ".$records['LivAppIncDayTimeTo']."</td>";
					echo "<td class='i_table_body' width='80px'>".$records['LivAppNotes']."</td>";
					switch ($records['LivAppStatus']){case '0':$leavestatus="NEW";break;case '1':$leavestatus="POSTED";break;case '2':$leavestatus="NOTED";break;case '3':$leavestatus="CHECKED";break;case '4':$leavestatus="APPROVED";break;default: $leavestatus="DISAPPROVED";break;}
					echo "<td class='i_table_body' align='center' width='75px'>$leavestatus</td>";
										
					echo "<td class='i_table_body' align='left' width='75px' style='font-size: 11px;'>";
					
					if ($records['LivAppNotedBy'] != "") {
						echo "<span style=\"display: inline-block; padding-bottom: 3px!important;\">";
						echo "Noted by: <strong>$records[noted_by]</strong><br>";					
						echo "On: <strong>".date("M j, Y",strtotime($records['LivAppNotedTime']))."</strong><br>";
						echo "</span>";
					}

					if ($records['LivAppCheckedBy'] != "") {
						echo "<span style=\"display: inline-block; padding-bottom: 3px!important;\">";
						echo "Checked by: <strong>$records[check_by]</strong><br>";					
						echo "On: <strong>".date("M j, Y",strtotime($records['LivAppCheckedTime']))."</strong><br>";
						echo "</span>";						
					}					

					if ($records['LivAppApprovedBy'] != "") {					
						echo "Approved by: <strong>$records[approved_by]</strong><br>";
						echo "On: <strong>".date("M j, Y",strtotime($records['LivAppApprovedTime']))."</strong>";						
					}
					
					echo "</td>";
					
					
					$LivAppRemarks=strlen($records['LivAppNotedRemarks'])>0?$records['LivAppNotedRemarks']."<br/>":"";
					$LivAppRemarks.=strlen($records['LivAppCheckedRemarks'])>0?$records['LivAppCheckedRemarks']."<br/>":"";
					$LivAppRemarks.=strlen($records['LivAppApprovedRemarks'])>0?$records['LivAppApprovedRemarks']:"";
					echo "<td class='i_table_body' width='80px'>".$LivAppRemarks."</td>";
					
					/*
					switch ($records['LivAppStatus']){
						case '1': 
							echo "<td style='width:20px;text-align:center;border-left:1px dotted #6D84B4;padding:2px 0px 1px 3px;' valign='top'><ul class='ui-widget ui-helper-clearfix ul-icons'><li id=''class='ui-state-default ui-corner-all' title='Confirm' onClick='$(\"#d_confirm\").dialog({buttons:{\"YES\":function(){processDocument(\"lv\",\"".$records['LivAppID']."\",2,1)},\"NO\":function(){processDocument(0,0,0,-1);},\"Cancel\":function(){processDocument(0,0,0,-1);}}});showConfirmation(\"Confirm this application?\");'><span class='ui-icon ui-icon-check'></span></li></ul></td>";break;
						case '2': 
							echo "<td style='width:20px;text-align:center;border-left:1px dotted #6D84B4;padding:2px 0px 1px 3px;' valign='top'><ul class='ui-widget ui-helper-clearfix ul-icons'><li id=''class='ui-state-default ui-corner-all' title='Check' onClick='$(\"#d_confirm\").dialog({buttons:{\"YES\":function(){processDocument(\"lv\",\"".$records['LivAppID']."\",3,1)},\"NO\":function(){processDocument(0,0,0,-1);},\"Cancel\":function(){processDocument(0,0,0,-1);}}});showConfirmation(\"Certify this application correct?\");'><span class='ui-icon ui-icon-check'></span></li></ul></td>";break;
						case '3': 
							echo "<td style='width:20px;text-align:center;border-left:1px dotted #6D84B4;padding:2px 0px 1px 3px;' valign='top'><ul class='ui-widget ui-helper-clearfix ul-icons'><li id=''class='ui-state-default ui-corner-all' title='Approve' onClick='$(\"#d_confirm\").dialog({buttons:{\"YES\":function(){processDocument(\"lv\",\"".$records['LivAppID']."\",4,1)},\"NO\":function(){processDocument(0,0,0,-1);},\"Cancel\":function(){processDocument(0,0,0,-1);}}});showConfirmation(\"Approve this application?\");'><span class='ui-icon ui-icon-check'></span></li></ul></td>";break;
						case '4': 
							echo "<td style='width:20px;text-align:center;border-left:1px dotted #6D84B4;padding:2px 0px 1px 3px;' valign='top'><ul class='ui-widget ui-helper-clearfix ul-icons'><li id=''class='ui-state-disabled ui-corner-all' title='Approve' onClick=''><span class='ui-icon ui-icon-check'></span></li></ul></td>";break;
					}
					*/
					if($records['LivAppStatus']==4){
						echo "<td style='width:20px;text-align:center;border-left:1px dotted #6D84B4;padding:2px 3px 1px 0px;' valign='top'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-disabled ui-corner-all' title='' onClick=''><span class='ui-icon ui-icon-extlink'></span></li></ul></td>";
						echo "<td style='width:20px;text-align:center;border-left:0px dotted #6D84B4;padding:2px 3px 1px 0px;' valign='top'><ul class='ui-widget ui-helper-clearfix ul-icons'><li id=''class='ui-state-default ui-corner-all' title='Print' onClick='window.open(\"reports/rpt_lv.php?id=".$records['LivAppID']."\",\"mywindow\",\"width=800,height=600\");'><span class='ui-icon ui-icon-print'></span></li></ul></td>";
					}
					else{
						echo "<td style='width:20px;text-align:center;border-left:1px dotted #6D84B4;padding:2px 0px 1px 3px;' valign='top'><ul class='ui-widget ui-helper-clearfix ul-icons'><li id=''class='ui-state-default ui-corner-all' title='Details' onClick='eName=\"".$LeaveName['EmpName']."\";viewRecordPLCT(\"".$records['EmpID']."\",\"L\")'><span class='ui-icon ui-icon-extlink'></span></li></ul></td>";
						echo "<td style='width:20px;text-align:center;border-left:0px dotted #6D84B4;padding:2px 3px 1px 0px;' valign='top'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-disabled ui-corner-all' title='Print' onClick=''><span class='ui-icon ui-icon-print'></span></li></ul></td>";
					}
					echo "</tr>";
					$n++;
				}
				$bAddState="disabled";$bAddClass="button ui-button ui-widget ui-corner-all ui-state-disabled";$onClick="";
				if(($Authorization[2])&&(($_SESSION['user']==$EmpID)||($Authorization[0]))){$bAddState="";$bAddClass="button ui-button ui-widget ui-corner-all";$onClick="showForm('leav','','',0);";}
			?>
		</table>
	</div>

	<table style="width:1100px;padding:0px;border-spacing:1px;margin:3px 10px 10px 10px;">
		<tr>
			<td align="left">
				<input type="button" value="Help" class="button ui-button ui-widget ui-corner-all" onClick="showHelp('001');return false;" />
			</td>
			<td align="right">
			<?php 
			if($Authorization[6]){ ?>
				<input type="button" value="File New Leave Application" class="button ui-button ui-widget ui-corner-all" onClick="showForm('leav','X','',0);return false;" />
				<?php
			} ?>
				<input type="button" value="Close" class="button ui-button ui-widget ui-corner-all" onClick="closeDialogWindow('d_viewer_1');return false;" />
			</td>
		</tr>
	</table>
</center>

	

