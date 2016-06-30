<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD018'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$EmpID=isset($_POST['id'])?trim(strip_tags($_POST['id'])):'00000';
	$PLCT=isset($_POST['plct'])?trim(strip_tags($_POST['plct'])):'L';
	$Yr=isset($_POST['yr'])?trim(strip_tags($_POST['yr'])):date('Y');
	$Ty=isset($_POST['ty'])?trim(strip_tags($_POST['ty'])):"0";
	$Rr=isset($_POST['rr'])?trim(strip_tags($_POST['rr'])):"";
	
	$MONTHS=Array('','JANUARY','FEBRUARY','MARCH','APRIL','MAY','JUNE','JULY','AUGUST','SEPTEMBER','OCTOBER','NOVEMBER','DECEMBER');
	$thisMonth=date('m');
	$thisYear=date('Y');
	echo "1|".$_SESSION['user']."|";
	$MySQLi=new MySQLClass();
	
	if($PLCT=="L"){
?>

<div style="width:680px;height:auto;overflow:auto;"><br/>
	<form name="filter_lv" id="filter" onSubmit="viewRecordPLCT('<?php echo $EmpID; ?>','L',this.LeaveYear.value,this.LeaveTypeID.value,this.RefRem.value);return false;">
	<table class="filter_bar" cellspacing="0" cellpadding="0" style="width:640px;margin-left:20px;margin-right:20px;">
		<tr>
			<td class="form_label_l filter_bar" style="width:55px;"><label><b>FILTER:</b></label></td>
			<td class="form_label filter_bar" style="width:60px;"><label>Leave Type:</label></td>
			<td class="pds_form_input filter_bar" style="width:90px;">
				<select id="LeaveTypeID" name="LeaveTypeID" class="text_input">
					<option value="0">ALL</option>
					<?php
						$LeaveTypeDesc="";
						$result=$MySQLi->sqlQuery("SELECT `LeaveTypeID`, `LeaveTypeDesc` FROM `tblleavetypes` WHERE `LeaveTypeID` <> 'LT00' ORDER BY `LeaveTypeID`;");
						while($ltype=mysql_fetch_array($result)) {
							if($ltype['LeaveTypeID']==$Ty){echo "<option value='".$ltype['LeaveTypeID']."' selected>".$ltype['LeaveTypeDesc']."</option>";$LeaveTypeDesc=$ltype['LeaveTypeDesc'];}
							else{echo "<option value='".$ltype['LeaveTypeID']."'>".$ltype['LeaveTypeDesc']."</option>";}
						} unset($result);
					?>
				</select>
			</td>
			<td class="form_label filter_bar" style="width:35px;"><label>Year:</label></td>
				<td class="pds_form_input filter_bar" style="width:53px;">
					<select id="LeaveYear" name="LeaveYear" class="text_input" style="width:53px;">
					<option value="0">ALL</option>
					<?php for($y=2005;$y<=date('Y');$y++){if($y==$Yr){echo "<option value='$y' selected>".$y."</option>";}else{echo "<option value='$y'>".$y."</option>";}} ?>
					</select>
				</td>
			<td class="form_label filter_bar" style="width:100px;"><label>Reference/Remarks:</label></td>
				<td class="pds_form_input filter_bar">
					<input type="text" id="RefRem" name="RefRem" class="text_input" style="width:100px;" value="<?php echo $Rr; ?>">
				</td>
			<td class="filter_bar" style="width:40px;text-align:right;"><input type="submit" value="Search" class="button ui-button ui-widget ui-corner-all" style="padding: 0px 4px 0px 4px;width:55px;"/></td>
		</tr>
	</table>
	</form>
	
	<table class="i_table" style="width:640px;">
		<tr>
			<td class="i_table_header" width="120px" rowspan="2">Leave Type</td>
			<td class="i_table_header" colspan="2">Date</td>
			<td class="i_table_header" width="40px" rowspan="2">Leave<br/>Credit</td>
			<td class="i_table_header" width="40px" rowspan="2">Leave<br/>Dedit</td>
			<td class="i_table_header" width="50px" rowspan="2">Current</br>Balance</td>
			<td class="i_table_header" width="80px" rowspan="2">Reference</td>
			<td class="i_table_header" rowspan="2">Remarks</td>
			<td class="i_table_header" rowspan="2" width="13px">X</td>
		</tr>
		<tr>
			<td class="i_table_header" width="65px">From</td>
			<td class="i_table_header" width="65px">To</td>
		</tr>
	</table>
	<div style="width:640px;height:160px;overflow-y:scroll;margin-left:20px;border-bottom:1px solid #AAAABB;">
		<table style="border-left:1px solid #AAAABB;border-spacing:0px;width:622px;">
			<?php
				$records=Array();
				if($EmpID!='00000'){ $n=1;
					/* FILTER */
					$LeaveTypeFilter=($Ty!="0")?" AND `LeaveTypeID` = '".$Ty."' ":"";
					$YearFilter=($Yr!=0)?" AND `LivCredDateFrom` LIKE '".$Yr."%' ":"";
					$RRFilter=($Rr!="")?" AND (`LivCredReference` LIKE '%".$Rr."%' OR `LivCredRemarks` LIKE '%".$Rr."%')":"";
					$sql="SELECT *, DATE_FORMAT(`LivCredDateFrom`, '%Y-%m-%d') AS LivCredDateFrom, DATE_FORMAT(`LivCredDateTo`, '%Y-%m-%d') AS LivCredDateTo FROM `tblempleavecredits` WHERE `EmpID` = '".$EmpID."' ".$LeaveTypeFilter.$YearFilter.$RRFilter." ORDER BY `LivCredDateFrom` ASC;";
					$result=$MySQLi->sqlQuery($sql);
					while($records=mysql_fetch_array($result)){
						if($n%2==0){echo "<tr class='i_table_row_0'>";}
						else{echo "<tr class='i_table_row_1'>";}
						$leavetype=$MySQLi->GetArray("SELECT `LeaveTypeDesc` FROM `tblleavetypes` WHERE `LeaveTypeID`='".$records['LeaveTypeID']."';");
						echo "<td valign='top' style='padding:3px 3px 3px 3px;' width='118px'>".$leavetype['LeaveTypeDesc']."</td>";
						echo "<td class='i_table_body' width='60px' align='center'>".$records['LivCredDateFrom']."</td>";
						echo "<td class='i_table_body' width='60px' align='center'>".$records['LivCredDateTo']."</td>";
						echo "<td class='i_table_body' width='37px' align='right'>".$records['LivCredAddTo']."</td>";
						echo "<td class='i_table_body' width='37px' align='right'>".$records['LivCredDeductTo']."</td>";
						echo "<td class='i_table_body' width='45px' align='right'>".$records['LivCredBalance']."</td>";
						echo "<td class='i_table_body' width='77px'>".$records['LivCredReference']."</td>";
						echo "<td class='i_table_body'>".$records['LivCredRemarks']."</td>";
						
						echo "</tr>";
						
						$n+=1; 
					}
					
					/* Get Last System Generated Date */
					$VL_LastDate=$SL_LastDate="200500"; /* <-- Change to DATE APPOINTED/DATE HIRED as CASUAL/PERMANENT - 1 Month*/
					$sql="SELECT `LeaveTypeID`, DATE_FORMAT(`LivCredDateFrom`, '%Y%m') AS LivCredDateFrom, DATE_FORMAT(`LivCredDateTo`, '%Y-%m-%d') AS LivCredDateTo FROM `tblempleavecredits` WHERE `EmpID` = '".$EmpID."' AND `LivCredRemarks`='System Generated' ORDER BY `LivCredDateFrom` ASC;";
					$result=$MySQLi->sqlQuery($sql);
					while($records=mysql_fetch_array($result)){
						$VL_LastDate=($records['LeaveTypeID']=="LT01")?$records['LivCredDateFrom']:$VL_LastDate;
						$SL_LastDate=($records['LeaveTypeID']=="LT02")?$records['LivCredDateFrom']:$SL_LastDate;
					}
					
					/* Get Current/Last Leave Credit Balance */
					$VLcredit=$SLcredit=0;
					$sql="SELECT `LeaveTypeID`, `LivCredBalance` FROM `tblempleavecredits` WHERE `EmpID` = '".$EmpID."' ORDER BY `LivCredDateFrom` ASC;";
					$result=$MySQLi->sqlQuery($sql);
					$VL_credit=$SL_credit=$PL_credit=$OL_credit=$TL_credit=0;
					while($records=mysql_fetch_array($result)){
						$VL_credit=($records['LeaveTypeID']=="LT01")?$records['LivCredBalance']:$VL_credit;
						$SL_credit=($records['LeaveTypeID']=="LT02")?$records['LivCredBalance']:$SL_credit;
					} $TL_credit=$VL_credit+$SL_credit;
					$VLcredit=$VL_credit;$SLcredit=$SL_credit;
					
					/* Preview System Generated Leave Credits */
					$theYear=(($thisMonth-1)==0)?$thisYear-1:$thisYear;
					$theMonth=(($thisMonth-1)==0)?12:$thisMonth-1;
					$theMonth=($theMonth>9)?$theMonth:"0".$theMonth;
					if($Authorization[6]){$showSysLeaveCredToConfirm=(($theYear.$theMonth==$VL_LastDate)||($theYear.$theMonth==$VL_LastDate))?false:true;}
					else{$showSysLeaveCredToConfirm=false;}
					if($showSysLeaveCredToConfirm){
						
						$m=intval(substr($VL_LastDate,-2))+1;
						$y=substr($VL_LastDate,0,4);
						if($m>12){$y+=1;$m=1;}
						$Start=$y.($m>9?$m:"0".$m);
						
						while($Start<date('Y').date('m')){
							if(($Ty=="LT01")||($Ty=="0")){
								$VLcredit+=1.25;
								if($n%2==0){echo "<tr class='i_table_row_0'>";}
								else{echo "<tr class='i_table_row_1'>";}
								echo "<td valign='top' class='red' style='padding:3px 3px 3px 3px;' width='118px'>VACATION LEAVE</td>";
								echo "<td class='i_table_body red' width='60px' align='center'>$y-".($m>9?$m:"0".$m)."-01</td>";
								echo "<td class='i_table_body red' width='60px' align='center'>$y-".($m>9?$m:"0".$m)."-01</td>";
								echo "<td class='i_table_body red' width='37px' align='right'>1.25</td>";
								echo "<td class='i_table_body red' width='37px' align='right'>0</td>";
								echo "<td class='i_table_body red' width='45px' align='right'>$VLcredit</td>";
								echo "<td class='i_table_body red' width='77px'>Leave Credit </td>";
								echo "<td class='i_table_body red'>(For Confirmation)</td>";
								echo "</tr>";
								$n+=1;
							}
							if(($Ty=="LT02")||($Ty=="0")){
								$SLcredit+=1.25;
								if($n%2==0){echo "<tr class='i_table_row_0'>";}
								else{echo "<tr class='i_table_row_1'>";}
								echo "<td valign='top' class='red' style='padding:3px 3px 3px 3px;' width='118px'>SICK LEAVE</td>";
								echo "<td class='i_table_body red' width='60px' align='center'>$y-".($m>9?$m:"0".$m)."-01</td>";
								echo "<td class='i_table_body red' width='60px' align='center'>$y-".($m>9?$m:"0".$m)."-01</td>";
								echo "<td class='i_table_body red' width='37px' align='right'>1.25</td>";
								echo "<td class='i_table_body red' width='37px' align='right'>0</td>";
								echo "<td class='i_table_body red' width='45px' align='right'>$SLcredit</td>";
								echo "<td class='i_table_body red' width='77px'>Leave Credit </td>";
								echo "<td class='i_table_body red'>(For Confirmation)</td>";
								echo "</tr>";
								$n+=1;
							}

							$m+=1;
							if($m>12){$y+=1;$m=1;}
							$Start=$y.($m>9?$m:"0".$m);
						}
						
					}
					
					while($n<=8){
						echo "<tr class='i_table_row_1'><td colspan='8'style='padding:3px 3px 3px 3px;'>&nbsp;</td></tr>";
						$n+=1;
					}
					
					$bAddState="disabled";$bAddClass="button ui-button ui-widget ui-corner-all ui-state-disabled";$onClick="";
					if(($Authorization[2])&&(($_SESSION['user']==$EmpID)||($Authorization[0]))){$bAddState="";$bAddClass="button ui-button ui-widget ui-corner-all";$onClick="showForm('educ','$EmpID','',0);";}
				}
			?>
		</table>
	</div>
	<form name="f_leave_info" >
		<table class="ui-widget-content ui-corner-all" style="width:640px;padding:0px;border-spacing:1px;margin:7px 20px 7px 20px;">
			<tr>
				<td rowspan='2' class="form_label" style="width:60px;padding-right:3px;"><label>VACATION<br/>LEAVE </label></td>
					<td rowspan='2' class="pds_form_input"><div name="VL_credit" id="VL_credit" class="text_input ui-widget-content ui-corner-all" style="width:70px;text-align:right;font-size:1.7em;padding:5px 3px 5px 3px;text-shadow:1px 1px 0 #AAA;"><?php echo number_format($VL_credit,3); ?></div></td>
				<td rowspan='2' class="form_label" style="width:60px;padding-right:3px;"><label>SICK<br/>LEAVE </label></td>
					<td rowspan='2' class="pds_form_input"><div name="SL_credit" id="SL_credit" class="text_input ui-widget-content ui-corner-all" style="width:70px;text-align:right;font-size:1.7em;padding:5px 3px 5px 3px;text-shadow:1px 1px 0 #AAA;"><?php echo number_format($SL_credit,3); ?></div></td>
				<td rowspan='2' class="form_label" style="width:60px;padding-right:3px;"><label>TOTAL<br/>LEAVE </label></td>
					<td rowspan='2' class="pds_form_input"><div name="TL_credit" id="TL_credit" class="text_input ui-widget-content ui-corner-all" style="width:70px;text-align:right;font-size:1.7em;padding:5px 3px 5px 3px;font-weight:bold;text-shadow:1px 1px 0 #AAA;"><?php echo number_format($TL_credit,3); ?></div></td>
				
				<td class="form_label" style="padding-right:3px;"><label>PRVILEGE LEAVE: </label></td>
					<td class="pds_form_input"><div name="PL_credit" id="PL_credit" class="text_input ui-widget-content ui-corner-all" style="width:35px;text-align:right;font-size:1.1em;padding:0px 3px 0px 3px;"><?php echo number_format($PL_credit,3); ?></div></td>
				
			</tr>
			<tr>
				<td class="form_label" style="padding-right:3px;"><label>OTHER LEAVE: </label></td>
					<td class="pds_form_input"><div name="OL_credit" id="OL_credit" class="text_input ui-widget-content ui-corner-all" style="width:35px;text-align:right;font-size:1.1em;padding:0px 3px 0px 3px;"><?php echo number_format($OL_credit,3); ?></div></td>
			</tr>
		</table>
		
		<?php  /* CHECK for avaiable SYSTEM GENERATED Leave Credits */ 
			$showLeaveAppToProcess=true;
			if($showSysLeaveCredToConfirm){
		?>
		<table class="ui-state-highlight ui-corner-all" style="width:640px;padding:0px;border-spacing:0px;margin-left:20px;margin-right:20px;margin-top:3px;">
			<tr>
				<td width="20px" align="center" class=""><span class='ui-icon ui-icon-info'></span></td>
				<td>New <b>SYSTEM GENERATED Leave</b> credits. <span class="red"><b>CONFIRM UNTIL:</b></span> 
					<select id="CredMonth" name="CredMonth" class="text_input" style="width:80px;">
						<option value="0">ALL</option>
						<?php for($i=1;$i<=12;$i++){echo "<option value='".($i>9?$i:"0".$i)."'>".$MONTHS[$i]."</option>";} ?>
					</select>
					<select id="CredYear" name="CredYear" class="text_input" style="width:53px;">
						<option value="0">ALL</option>
						<?php for($y=2005;$y<=date('Y');$y++){echo "<option value='$y'>".$y."</option>";} ?>
					</select>
				</td>
				<td width="65px" align="right">
					<ul class="ui-widget ui-helper-clearfix ul-icons">
						<li id=""class="ui-state-disabled ui-corner-all" title="Deny" onClick=""><span class="ui-icon ui-icon-close"></span></li>
						<li id=""class="ui-state-default ui-corner-all" title="Confirm" onClick="processSysGenLivCred('<?php echo $EmpID; ?>',document.getElementById('CredYear').value,document.getElementById('CredMonth').value);"><span class="ui-icon ui-icon-check"></span></li>
						<li id=""class="ui-state-disabled ui-corner-all" title="Undo" onClick=""><span class="ui-icon ui-icon-arrowreturnthick-1-w"></span></li>
					</ul>
				</td>
			</tr>
		</table>
		<?php  
			}
			$docStatusFilter="";
			if($Authorization[5]){$docStatusFilter=" AND (`LivAppStatus` = '1' OR `LivAppStatus` = '2') ";}
			else if($Authorization[6]){$docStatusFilter=" AND (`LivAppStatus` = '2' OR `LivAppStatus` = '3') ";}
			else if($Authorization[7]){$docStatusFilter=" AND (`LivAppStatus` = '3' OR `LivAppStatus` = '4') ";}
			else {$showLeaveAppToProcess=false;}
			
			if($showLeaveAppToProcess){
				$result=$MySQLi->sqlQuery("SELECT `tblempleaveapplications`.`LivAppID`, `tblempleaveapplications`.`LeaveTypeID`, `tblempleaveapplications`.`LivAppDays`,DATE_FORMAT(`tblempleaveapplications`.`LivAppFiledDate`, '%Y-%m-%d') AS LivAppFiledDate,DATE_FORMAT(`tblempleaveapplications`.`LivAppIncDateFrom`, '%b %d, %Y') AS LivAppIncDateFrom,`tblempleaveapplications`.`LivAppIncDayTimeFrom`,DATE_FORMAT(`tblempleaveapplications`.`LivAppIncDateTo`, '%b %d, %Y') AS LivAppIncDateTo,`tblempleaveapplications`.`LivAppIncDayTimeTo`,`tblempleaveapplications`.`LivAppStatus`, `tblempleaveapplications`.`LivAppNotedRemarks`, `tblempleaveapplications`.`LivAppApprovedRemarks`,`LeaveTypeDesc` FROM `tblempleaveapplications` JOIN `tblleavetypes` ON `tblempleaveapplications`.`LeaveTypeID` = `tblleavetypes`.`LeaveTypeID`WHERE `EmpID` = '".$EmpID."'".$docStatusFilter." ORDER BY `LeaveTypeID` ASC,`LivAppFiledDate` ASC;");
				while($records=mysql_fetch_array($result)){
					switch ($records['LivAppStatus']){case '0':$leavestatus="NEW";break;case '1':$leavestatus="POSTED";break;case '2':$leavestatus="NOTED";break;case '3':$leavestatus="CHECKED";break;case '4':$leavestatus="APPROVED";break;default: $leavestatus="DISAPPROVED";break;}
					$LeaveDesc="<b>".$records['LeaveTypeDesc']."</b> Application. From <b>".$records['LivAppIncDateFrom']." ".$records['LivAppIncDayTimeFrom']."</b> to <b>".$records['LivAppIncDateTo']." ".$records['LivAppIncDayTimeTo']."</b>, <b>".number_format($records['LivAppDays'],2)."</b> ";
					$LeaveDesc.=($records['LivAppDays']>1)?" days.":" day.";
					$LeaveDesc.=" (Status: <span class='red'><b>".$leavestatus."</b></span>)";
					
					switch ($records['LivAppStatus']){
						case 1:
							if($Authorization[5]){
								$denyIconState="default";$denyIconAction="$('#d_input').dialog({buttons:{'YES':function(){processDocument('".$EmpID."','lv','".$records['LivAppID']."','-1',document.getElementById('respTxt').value);closeDialogWindow('d_input');},'NO':function(){closeDialogWindow('d_input');}}});getInformation('Deny/Disapprovr this leave application?<br/>Please leave comment/remark.');";
								$chekIconState="default";$chekIconAction="$('#d_confirm').dialog({buttons:{'YES':function(){processDocument('".$EmpID."','lv','".$records['LivAppID']."',".($records['LivAppStatus']+1).",'');},'NO':function(){closeDialogWindow('d_confirm');}}});showConfirmation('Note this application?');";
								$undoIconState="disabled";$undoIconAction="return false;";
							}
							break;
						case 2:
							if($Authorization[5]){
								$denyIconState="disabled";$denyIconAction="";
								$chekIconState="disabled";$chekIconAction="";
								$undoIconState="default";$undoIconAction="$('#d_confirm').dialog({buttons:{'YES':function(){processDocument('".$EmpID."','lv','".$records['LivAppID']."',".($records['LivAppStatus']-1).",'')},'NO':function(){closeDialogWindow('d_confirm');}}});showConfirmation('Undo the recording of this application?');";
							}
							if($Authorization[6]){
								$denyIconState="default";$denyIconAction="$('#d_input').dialog({buttons:{'YES':function(){processDocument('".$EmpID."','lv','".$records['LivAppID']."','-1',document.getElementById('respTxt').value);closeDialogWindow('d_input');},'NO':function(){closeDialogWindow('d_input');}}});getInformation('Deny/Disapprovr this leave application?<br/>Please leave comment/remark.');";
								$chekIconState="default";$chekIconAction="$('#d_confirm').dialog({buttons:{'YES':function(){processDocument('".$EmpID."','lv','".$records['LivAppID']."',".($records['LivAppStatus']+1).",'')},'NO':function(){closeDialogWindow('d_confirm');}}});showConfirmation('Confirm this application?');";
								$undoIconState="disabled";$undoIconAction="return false;";
							}
							break;
						case 3:
							if($Authorization[6]){
								$denyIconState="disabled";$denyIconAction="";
								$chekIconState="disabled";$chekIconAction="";
								$undoIconState="default";$undoIconAction="$('#d_confirm').dialog({buttons:{'YES':function(){processDocument('".$EmpID."','lv','".$records['LivAppID']."',".($records['LivAppStatus']-1).",'')},'NO':function(){closeDialogWindow('d_confirm');}}});showConfirmation('Undo the confirmation of this application?');";
							}
							
							if($Authorization[7]){
								$denyIconState="default";$denyIconAction="$('#d_input').dialog({buttons:{'YES':function(){processDocument('".$EmpID."','lv','".$records['LivAppID']."','-1',document.getElementById('respTxt').value)},'NO':function(){closeDialogWindow('d_input');}}});getInformation('Deny/Disapprovr this leave application?<br/>Please leave comment/remark.');";
								$chekIconState="default";$chekIconAction="$('#d_confirm').dialog({buttons:{'YES':function(){processDocument('".$EmpID."','lv','".$records['LivAppID']."',".($records['LivAppStatus']+1).",'')},'NO':function(){closeDialogWindow('d_confirm');}}});showConfirmation('Approve this application?');";
								$undoIconState="disabled";$undoIconAction="return false;";
							}
							break;
						case 4:
							if($Authorization[7]){
								$denyIconState="disabled";$denyIconAction="";
								$chekIconState="disabled";$chekIconAction="";
								$undoIconState="default";$undoIconAction="$('#d_confirm').dialog({buttons:{'YES':function(){processDocument('".$EmpID."','lv','".$records['LivAppID']."',".($records['LivAppStatus']-1).",'')},'NO':function(){closeDialogWindow('d_confirm');}}});showConfirmation('Undo the approval of this application?');";
							}
							break;
					}

		?>
		<table class="ui-state-highlight ui-corner-all" style="width:640px;padding:0px;border-spacing:0px;margin-left:20px;margin-right:20px;margin-top:3px;">
			<tr>
				<td width="20px" align="center" class=""><span class='ui-icon ui-icon-info'></span></td>
				<td><?php echo $LeaveDesc; ?></td>
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
		
		<script type='text/javaScript'>
		function testjs(){
			showMessage("Javascript is OK!!!");
		}
		</script>

		<hr class="form_bottom_line_window"/>
		<table style="width:640px;padding:0px;border-spacing:1px;margin-left:20px;margin-right:20px;margin-top:3px;">
			<tr>
				<td align="left"><input type="button" value="Help" class="button ui-button ui-widget ui-corner-all" onClick="showHelp('001');return false;" /></td>
				<td align="right"><input type="button" value="Close" class="button ui-button ui-widget ui-corner-all" onClick="closeDialogWindow('d_viewer_2');return false;" /></td>
			</tr>
		</table>
		<input type="hidden" name="mode" id="mode" value="" />
		<input type="hidden" name="LivAppID" id="LivAppID" value="" />
		<input type="hidden" name="EmpID" id="EmpID" value="<?php echo $EmpID; ?>" />
	</form>
</div>
<?php
	}
	
?>


